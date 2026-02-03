<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * ----------------------------------------------------
 * Modèle Utilisateur
 * ----------------------------------------------------
 * Table : utilisateurs
 *
 * Responsabilités :
 *  - Authentification (recherche par email)
 *  - Gestion des informations utilisateurs
 *  - Statistiques par rôle
 * ----------------------------------------------------
 */
class Utilisateur
{
    /**
     * Instance PDO pour les requêtes
     *
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructeur
     * Récupère l'instance PDO depuis Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ==================================================
     * RÉCUPÉRER UN UTILISATEUR PAR EMAIL
     * ==================================================
     * Utilisé pour l'authentification
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                password,  -- hashé pour auth
                role
            FROM utilisateurs
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null; // retourne null si aucun utilisateur
    }

    /**
     * ==================================================
     * RÉCUPÉRER UN UTILISATEUR PAR ID
     * ==================================================
     * Utilisé pour les sessions et profils
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                role
            FROM utilisateurs
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * ==================================================
     * RÉCUPÉRER TOUS LES UTILISATEURS
     * ==================================================
     * Admin uniquement
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT 
                id,
                nom,
                prenom,
                email,
                telephone,
                role
            FROM utilisateurs
            ORDER BY nom ASC, prenom ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ==================================================
     * COMPTER LES UTILISATEURS PAR RÔLE
     * ==================================================
     * Pour les statistiques / dashboard admin
     *
     * @return array Exemple :
     * [
     *   ['role' => 'admin', 'count' => 3],
     *   ['role' => 'user',  'count' => 15]
     * ]
     */
    public function countByRole(): array
    {
        $sql = "
            SELECT role, COUNT(*) AS count
            FROM utilisateurs
            GROUP BY role
            ORDER BY role ASC
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows ?: [];
    }

    /**
     * ==================================================
     * (Optionnel) Ajouter un nouvel utilisateur
     * ==================================================
     * Bonnes pratiques :
     *  - Hacher le mot de passe avant insertion
     *  - Vérifier unicité de l'email
     */
    public function create(array $data): bool
    {
        // Exemple de hachage du mot de passe
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, telephone, password, role)
            VALUES (:nom, :prenom, :email, :telephone, :password, :role)
        ");

        return $stmt->execute([
            ':nom'       => $data['nom'],
            ':prenom'    => $data['prenom'],
            ':email'     => $data['email'],
            ':telephone' => $data['telephone'],
            ':password'  => $passwordHash,
            ':role'      => $data['role'] ?? 'user'
        ]);
    }

    /**
     * ==================================================
     * (Optionnel) Mettre à jour un utilisateur
     * ==================================================
     */
    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE utilisateurs SET
                nom = :nom,
                prenom = :prenom,
                email = :email,
                telephone = :telephone,
                role = :role
            WHERE id = :id
        ";

        return $this->db->prepare($sql)->execute([
            ':nom'       => $data['nom'],
            ':prenom'    => $data['prenom'],
            ':email'     => $data['email'],
            ':telephone' => $data['telephone'],
            ':role'      => $data['role'],
            ':id'        => $id
        ]);
    }
}
