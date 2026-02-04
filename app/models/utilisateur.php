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

    /* ==================================================
     * RÉCUPÉRER UN UTILISATEUR PAR EMAIL
     * ================================================== */

    /**
     * @param string $email
     * @return array<string, mixed>|null
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
                password,
                role
            FROM utilisateurs
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /* ==================================================
     * RÉCUPÉRER UN UTILISATEUR PAR ID
     * ================================================== */

    /**
     * @param int $id
     * @return array<string, mixed>|null
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

    /* ==================================================
     * RÉCUPÉRER TOUS LES UTILISATEURS
     * ================================================== */

    /**
     * @return array<int, array<string, mixed>>
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

    /* ==================================================
     * COMPTER LES UTILISATEURS PAR RÔLE
     * ================================================== */

    /**
     * @return array<int, array<string, mixed>> Exemple :
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

    /* ==================================================
     * AJOUTER UN UTILISATEUR
     * ================================================== */

    /**
     * @param array<string, mixed> $data
     * @return bool
     */
    public function create(array $data): bool
    {
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

    /* ==================================================
     * METTRE À JOUR UN UTILISATEUR
     * ================================================== */

    /**
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
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
