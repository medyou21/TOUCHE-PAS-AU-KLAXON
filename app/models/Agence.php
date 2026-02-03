<?php

namespace App\Models;

use PDO;
use Exception;
use App\Core\Database;

/**
 * ----------------------------------------------------
 * Modèle Agence
 * ----------------------------------------------------
 * Gère les opérations CRUD sur la table `agences`
 * ----------------------------------------------------
 */
class Agence
{
    private PDO $db;

    public function __construct()
    {
        // Utilisation du singleton Database
        $this->db = Database::getInstance();
    }

    /**
     * =========================
     * Lecture
     * =========================
     */

    /**
     * Récupère toutes les agences
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nom_agence 
             FROM agences 
             ORDER BY nom_agence ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Récupère une agence par ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nom_agence 
             FROM agences 
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * =========================
     * Création
     * =========================
     */

    public function create(array $data): bool
    {
        $nom = trim($data['name'] ?? '');

        if ($nom === '') {
            return false;
        }

        if ($this->existsByName($nom)) {
            throw new Exception("Cette agence existe déjà");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO agences (nom_agence) VALUES (:nom)"
        );

        return $stmt->execute(['nom' => $nom]);
    }

    /**
     * =========================
     * Mise à jour
     * =========================
     */

    public function update(int $id, array $data): bool
    {
        $nom = trim($data['name'] ?? '');

        if ($id <= 0 || $nom === '') {
            return false;
        }

        // ⚠️ Vérifie le doublon en excluant l'agence actuelle
        if ($this->existsByName($nom, $id)) {
            throw new Exception("Cette agence existe déjà");
        }

        $stmt = $this->db->prepare(
            "UPDATE agences 
             SET nom_agence = :nom 
             WHERE id = :id"
        );

        return $stmt->execute([
            'nom' => $nom,
            'id'  => $id
        ]);
    }

    /**
     * =========================
     * Suppression
     * =========================
     * ❗ Interdite si l’agence est liée à un trajet
     */

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            throw new Exception("ID d'agence invalide");
        }

        // Vérification des dépendances
        $check = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM trajets
             WHERE agence_depart_id = :id
                OR agence_arrivee_id = :id"
        );
        $check->execute(['id' => $id]);

        if ($check->fetchColumn() > 0) {
            throw new Exception(
                "Impossible de supprimer : l'agence est utilisée par un trajet"
            );
        }

        $stmt = $this->db->prepare(
            "DELETE FROM agences WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * =========================
     * Méthodes utilitaires
     * =========================
     */

    /**
     * Vérifie si une agence existe déjà
     * (optionnellement en excluant un ID)
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM agences WHERE nom_agence = :nom";
        $params = ['nom' => $name];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }
}
