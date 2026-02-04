<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * ----------------------------------------------------
 * Modèle Reservation
 * ----------------------------------------------------
 * Gère toutes les opérations liées aux réservations :
 *  - Création sécurisée (transaction + verrou SQL)
 *  - Suppression avec restauration des places
 *  - Modification du nombre de places
 *  - Récupération des réservations
 * ----------------------------------------------------
 */
class Reservation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Crée une réservation
     *
     * @param int $trajetId
     * @param int $userId
     * @param int $nbPlaces
     * @return bool
     */
    public function create(int $trajetId, int $userId, int $nbPlaces): bool
    {
        if ($nbPlaces < 1) return false;

        $this->db->beginTransaction();

        try {
            if ($this->hasUserReserved($trajetId, $userId)) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                SELECT nb_places_disponibles
                FROM trajets
                WHERE id = :trajet
                FOR UPDATE
            ");
            $stmt->execute([':trajet' => $trajetId]);
            $placesDispo = (int)$stmt->fetchColumn();

            if ($placesDispo < $nbPlaces) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO reservations (trajet_id, utilisateur_id, nb_places)
                VALUES (:trajet, :user, :places)
            ");
            $stmt->execute([
                ':trajet' => $trajetId,
                ':user'   => $userId,
                ':places' => $nbPlaces
            ]);

            $stmt = $this->db->prepare("
                UPDATE trajets
                SET nb_places_disponibles = nb_places_disponibles - :places
                WHERE id = :trajet
            ");
            $stmt->execute([
                ':places' => $nbPlaces,
                ':trajet' => $trajetId
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur a déjà réservé
     *
     * @param int $trajetId
     * @param int $userId
     * @return bool
     */
    public function hasUserReserved(int $trajetId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM reservations
            WHERE trajet_id = :trajet AND utilisateur_id = :user
        ");
        $stmt->execute([':trajet' => $trajetId, ':user' => $userId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Récupère toutes les réservations d'un utilisateur
     *
     * @param int $userId
     * @return array<int, array<string, mixed>>
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   t.date_depart, t.date_arrivee,
                   t.nb_places_totales, t.nb_places_disponibles, 
                   a1.nom_agence AS depart,
                   a2.nom_agence AS arrivee
            FROM reservations r
            JOIN trajets t ON r.trajet_id = t.id
            JOIN agences a1 ON t.agence_depart_id = a1.id
            JOIN agences a2 ON t.agence_arrivee_id = a2.id
            WHERE r.utilisateur_id = :user
            ORDER BY t.date_depart ASC
        ");
        $stmt->execute([':user' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une réservation par son ID
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   t.date_depart, t.date_arrivee,
                   t.nb_places_totales, t.nb_places_disponibles, 
                   a1.nom_agence AS depart,
                   a2.nom_agence AS arrivee
            FROM reservations r
            JOIN trajets t ON r.trajet_id = t.id
            JOIN agences a1 ON t.agence_depart_id = a1.id
            JOIN agences a2 ON t.agence_arrivee_id = a2.id
            WHERE r.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Supprime une réservation et restaure les places
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT trajet_id, nb_places
                FROM reservations
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $stmt = $this->db->prepare("
                UPDATE trajets
                SET nb_places_disponibles = nb_places_disponibles + :places
                WHERE id = :trajet
            ");
            $stmt->execute([
                ':places' => $reservation['nb_places'],
                ':trajet' => $reservation['trajet_id']
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Modifie le nombre de places d’une réservation
     *
     * @param int $reservationId
     * @param int $userId
     * @param int $newNbPlaces
     * @return bool
     */
    public function updatePlaces(int $reservationId, int $userId, int $newNbPlaces): bool
    {
        if ($newNbPlaces < 1) return false;

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT r.nb_places, r.trajet_id, t.nb_places_disponibles
                FROM reservations r
                JOIN trajets t ON r.trajet_id = t.id
                WHERE r.id = :id AND r.utilisateur_id = :user
                FOR UPDATE
            ");
            $stmt->execute([':id' => $reservationId, ':user' => $userId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$res) {
                $this->db->rollBack();
                return false;
            }

            $diff = $newNbPlaces - (int)$res['nb_places'];

            if ($diff > 0 && (int)$res['nb_places_disponibles'] < $diff) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE reservations SET nb_places = :new WHERE id = :id");
            $stmt->execute([':new' => $newNbPlaces, ':id' => $reservationId]);

            $stmt = $this->db->prepare("
                UPDATE trajets
                SET nb_places_disponibles = nb_places_disponibles - :diff
                WHERE id = :trajet
            ");
            $stmt->execute([
                ':diff'   => $diff,
                ':trajet' => $res['trajet_id']
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
