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
     * Récupère les trajets disponibles
     * (date future + places disponibles)
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
            ':places_disponibles' => $data['nb_places_totales'], // initialement = total
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
}
