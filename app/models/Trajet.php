<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * ----------------------------------------------------
 * Modèle Trajet
 * ----------------------------------------------------
 * Responsabilités :
 *  - Création / modification / suppression des trajets
 *  - Récupération des trajets disponibles
 *  - Récupération avec agences et conducteur
 *  - Gestion du nombre de places
 *  - Statistiques (dashboard admin)
 * ----------------------------------------------------
 */
class Trajet
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * @return array<int, array<string, mixed>>
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
                u.telephone
            FROM trajets t
            JOIN agences a1 ON t.agence_depart_id = a1.id
            JOIN agences a2 ON t.agence_arrivee_id = a2.id
            JOIN utilisateurs u ON t.conducteur_id = u.id
            WHERE t.nb_places_disponibles > 0
              AND t.date_depart > NOW()
            ORDER BY t.date_depart ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): bool
    {
        $placesDispo = $data['nb_places_disponibles'] ?? $data['nb_places_totales'];
        $placesDispo = min($placesDispo, $data['nb_places_totales']);

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
                :totales,
                :disponibles,
                :conducteur
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':depart'      => $data['depart'],
            ':arrivee'     => $data['arrivee'],
            ':date_depart' => $data['date_depart'],
            ':date_arrivee'=> $data['date_arrivee'],
            ':totales'     => $data['nb_places_totales'],
            ':disponibles' => $placesDispo,
            ':conducteur'  => $data['conducteur_id']
        ]);
    }

    /**
     * @return array<int, array{id:int, nom_agence:string}>
     */
    public function getAgences(): array
    {
        $stmt = $this->db->query("
            SELECT id, nom_agence
            FROM agences
            ORDER BY nom_agence ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM trajets
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $placesDispo = min($data['nb_places_disponibles'], $data['nb_places_totales']);

        $sql = "
            UPDATE trajets SET
                agence_depart_id = :depart,
                agence_arrivee_id = :arrivee,
                date_depart = :date_depart,
                date_arrivee = :date_arrivee,
                nb_places_totales = :totales,
                nb_places_disponibles = :disponibles
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([
            ':depart'       => $data['depart'],
            ':arrivee'      => $data['arrivee'],
            ':date_depart'  => $data['date_depart'],
            ':date_arrivee' => $data['date_arrivee'],
            ':totales'      => $data['nb_places_totales'],
            ':disponibles'  => $placesDispo,
            ':id'           => $id
        ]);

        if (!$success) {
            error_log("Erreur update trajet ID=$id");
        }

        return $success;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM trajets WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByIdWithAgences(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, a1.nom_agence AS depart, a2.nom_agence AS arrivee
            FROM trajets t
            JOIN agences a1 ON t.agence_depart_id = a1.id
            JOIN agences a2 ON t.agence_arrivee_id = a2.id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByIdForUpdate(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateAvailablePlaces(int $id, int $newPlaces): bool
    {
        $stmt = $this->db->prepare("
            UPDATE trajets
            SET nb_places_disponibles = :places
            WHERE id = :id
        ");

        return $stmt->execute([
            ':places' => $newPlaces,
            ':id'     => $id
        ]);
    }

    public function countActifs(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM trajets WHERE date_depart > NOW()")
            ->fetchColumn();
    }

    /**
     * @return array<int, array{date:string, count:int}>
     */
    public function getTrajetsLastDays(int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(date_depart) AS date, COUNT(*) AS count
            FROM trajets
            WHERE date_depart >= CURDATE() - INTERVAL :days DAY
            GROUP BY DATE(date_depart)
            ORDER BY date ASC
        ");
        $stmt->execute([':days' => $days]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
