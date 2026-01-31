<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Trajet
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère les trajets disponibles (date future + places disponibles)
     */
    public function getAvailableTrajets(): array
    {
        $sql = "
            SELECT 
                t.*,
                a1.nom_agence AS depart,
                a2.nom_agence AS arrivee,
                u.prenom,
                u.nom,
                u.email,
                u.telephone,
                t.conducteur_id
            FROM trajets t
            INNER JOIN agences a1 ON t.agence_depart_id = a1.id
            INNER JOIN agences a2 ON t.agence_arrivee_id = a2.id
            INNER JOIN utilisateurs u ON t.conducteur_id = u.id
            WHERE t.nb_places_disponibles > 0
              AND t.date_depart > NOW()
            ORDER BY t.date_depart ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau trajet
     */
    public function create(array $data): bool
    {
        $places_disponibles = $data['nb_places_disponibles'] ?? $data['nb_places_totales'];
        // S'assurer que nb_places_disponibles <= nb_places_totales
        $places_disponibles = min($places_disponibles, $data['nb_places_totales']);

        $sql = "
            INSERT INTO trajets (
                agence_depart_id,
                agence_arrivee_id,
                date_depart,
                date_arrivee,
                nb_places_totales,
                nb_places_disponibles,
                conducteur_id
            ) VALUES (
                :depart,
                :arrivee,
                :date_depart,
                :date_arrivee,
                :places_totales,
                :places_disponibles,
                :conducteur
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':depart'             => $data['depart'],
            ':arrivee'            => $data['arrivee'],
            ':date_depart'        => $data['date_depart'],
            ':date_arrivee'       => $data['date_arrivee'],
            ':places_totales'     => $data['nb_places_totales'],
            ':places_disponibles' => $places_disponibles,
            ':conducteur'         => $data['conducteur_id']
        ]);
    }

    /**
     * Récupère toutes les agences
     */
    public function getAgences(): array
    {
        $stmt = $this->db->query("
            SELECT id, nom_agence
            FROM agences
            ORDER BY nom_agence ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un trajet par son ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM trajets
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

        return $trajet ?: null;
    }

    /**
     * Met à jour un trajet
     */
  public function update(int $id, array $data): bool
{
    $places_disponibles = min($data['nb_places_disponibles'], $data['nb_places_totales']);

    $sql = "
        UPDATE trajets SET
            agence_depart_id = :depart,
            agence_arrivee_id = :arrivee,
            date_depart = :date_depart,
            date_arrivee = :date_arrivee,
            nb_places_totales = :places_totales,
            nb_places_disponibles = :places_disponibles
        WHERE id = :id
    ";

    $stmt = $this->db->prepare($sql);
    $success = $stmt->execute([
        ':depart'             => $data['depart'],
        ':arrivee'            => $data['arrivee'],
        ':date_depart'        => $data['date_depart'],
        ':date_arrivee'       => $data['date_arrivee'],
        ':places_totales'     => $data['nb_places_totales'],
        ':places_disponibles' => $places_disponibles,
        ':id'                 => $id
    ]);

    if(!$success){
        $errorInfo = $stmt->errorInfo();
        error_log("Update trajet failed: " . implode(' | ', $errorInfo));
    }

    return $success;
}

    /**
     * Supprime un trajet
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM trajets WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    /**
 * Récupère un trajet avec agences + conducteur
 */
public function getByIdWithAgences(int $id): ?array
{
    $sql = "
        SELECT
            t.*,
            a1.nom_agence AS depart,
            a2.nom_agence AS arrivee
        FROM trajets t
        JOIN agences a1 ON t.agence_depart_id = a1.id
        JOIN agences a2 ON t.agence_arrivee_id = a2.id
        WHERE t.id = ?
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]); // ✅ CORRECT

    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
    return $trajet ?: null;
}
/**
 * Récupère un trajet par ID et verrouille la ligne pour update (SELECT ... FOR UPDATE)
 */
public function getByIdForUpdate(int $id): ?array
{
    $sql = "
        SELECT *
        FROM trajets
        WHERE id = :id
        FOR UPDATE
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);

    $trajet = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $trajet ?: null;
}

/**
 * Met à jour uniquement le nombre de places disponibles
 */
public function updateAvailablePlaces(int $id, int $newPlaces): bool
{
    $sql = "UPDATE trajets SET nb_places_disponibles = :places WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        ':places' => $newPlaces,
        ':id' => $id
    ]);
}

}
