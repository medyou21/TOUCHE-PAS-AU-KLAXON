<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Reservation
{
    private PDO $db;

    public function __construct()
    {
        // Récupération de l'instance PDO depuis la classe Database
        $this->db = Database::getInstance();
    }

    /**
     * Créer une réservation
     * Retourne true si succès, false si échec (double réservation ou places insuffisantes)
     */
    public function create(int $trajetId, int $userId, int $nbPlaces): bool
    {
        // Vérifier si l'utilisateur a déjà réservé ce trajet
        if ($this->hasUserReserved($trajetId, $userId)) {
            return false;
        }

        // Vérifier le nombre de places disponibles
        $stmt = $this->db->prepare("SELECT nb_places_disponibles FROM trajets WHERE id = :trajet");
        $stmt->execute([':trajet' => $trajetId]);
        $placesDispo = (int)$stmt->fetchColumn();

        if ($placesDispo < $nbPlaces) {
            return false; // Pas assez de places disponibles
        }

        // Démarrer une transaction pour sécuriser l'opération
        $this->db->beginTransaction();

        try {
            // Insérer la réservation
            $stmt = $this->db->prepare("
                INSERT INTO reservations (trajet_id, utilisateur_id, nb_places)
                VALUES (:trajet, :user, :places)
            ");
            $stmt->execute([
                ':trajet' => $trajetId,
                ':user'   => $userId,
                ':places' => $nbPlaces
            ]);

            // Mettre à jour le nombre de places disponibles
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
     * Vérifie si un utilisateur a déjà réservé un trajet
     */
    public function hasUserReserved(int $trajetId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM reservations 
            WHERE trajet_id = :trajet AND utilisateur_id = :user
        ");
        $stmt->execute([
            ':trajet' => $trajetId,
            ':user'   => $userId
        ]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupérer toutes les réservations d'un utilisateur
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, t.date_depart, t.date_arrivee, t.nb_places_totales, t.nb_places_disponibles,
                   a1.nom_agence AS depart, a2.nom_agence AS arrivee
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
     * Supprimer une réservation
     */
    public function delete(int $id): bool
    {
        // Commencer transaction
        $this->db->beginTransaction();

        try {
            // Récupérer la réservation pour connaître le trajet et le nombre de places
            $stmt = $this->db->prepare("SELECT trajet_id, nb_places FROM reservations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                $this->db->rollBack();
                return false;
            }

            // Supprimer la réservation
            $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Restaurer les places dans le trajet
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
     * Récupérer une réservation par ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupérer toutes les réservations pour un trajet
     */
    public function getByTrajetId(int $trajetId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.prenom, u.nom, u.email
            FROM reservations r
            JOIN utilisateurs u ON r.utilisateur_id = u.id
            WHERE r.trajet_id = :trajet
        ");
        $stmt->execute([':trajet' => $trajetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
 * Modifier le nombre de places d’une réservation
 */
public function updatePlaces(int $reservationId, int $userId, int $newNbPlaces): bool
{
    $this->db->beginTransaction();

    try {
        // Récupérer la réservation
        $stmt = $this->db->prepare("
            SELECT r.nb_places, r.trajet_id, t.nb_places_disponibles
            FROM reservations r
            JOIN trajets t ON r.trajet_id = t.id
            WHERE r.id = :id AND r.utilisateur_id = :user
        ");
        $stmt->execute([
            ':id' => $reservationId,
            ':user' => $userId
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            $this->db->rollBack();
            return false;
        }

        $oldNb = (int)$res['nb_places'];
        $trajetId = (int)$res['trajet_id'];
        $placesDispo = (int)$res['nb_places_disponibles'];

        if ($newNbPlaces < 1) {
            $this->db->rollBack();
            return false;
        }

        $diff = $newNbPlaces - $oldNb;

        // Si on augmente → vérifier disponibilité
        if ($diff > 0 && $placesDispo < $diff) {
            $this->db->rollBack();
            return false;
        }

        // Mettre à jour la réservation
        $stmt = $this->db->prepare("
            UPDATE reservations SET nb_places = :new WHERE id = :id
        ");
        $stmt->execute([
            ':new' => $newNbPlaces,
            ':id' => $reservationId
        ]);

        // Mettre à jour les places du trajet
        $stmt = $this->db->prepare("
            UPDATE trajets
            SET nb_places_disponibles = nb_places_disponibles - :diff
            WHERE id = :trajet
        ");
        $stmt->execute([
            ':diff' => $diff,
            ':trajet' => $trajetId
        ]);

        $this->db->commit();
        return true;

    } catch (\Throwable $e) {
        $this->db->rollBack();
        return false;
    }
}

}
