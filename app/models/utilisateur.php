<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modèle Utilisateur
 * Table : utilisateurs
 */
class Utilisateur
{
    private PDO $db;

    /**
     * Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer un utilisateur par email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                password,
                role
             FROM utilisateurs
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Récupérer un utilisateur par ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                role
             FROM utilisateurs
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Récupérer tous les utilisateurs
     * (admin uniquement)
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                role
             FROM utilisateurs
             ORDER BY nom ASC, prenom ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
