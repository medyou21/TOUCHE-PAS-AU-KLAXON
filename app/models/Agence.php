<?php

namespace App\Models;

use PDO;
use PDOException;

class Agence
{
    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            // En production → log fichier
            throw new PDOException("Erreur de connexion à la base de données");
        }
    }

    /**
     * =========================
     * Récupération
     * =========================
     */

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, nom_agence FROM agences ORDER BY id, nom_agence ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nom_agence FROM agences WHERE id = :id");
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
        throw new \Exception("Cette agence existe déjà");
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
    
      if ($this->existsByName($nom)) {
        throw new \Exception("Cette agence existe déjà");
    }

    $stmt = $this->db->prepare(
        "UPDATE agences SET nom_agence = :nom WHERE id = :id"
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
     * ❗ Interdite si l’agence est utilisée
     */
   public function delete(int $id): bool
{
    if ($id <= 0) {
        throw new \Exception("ID d'agence invalide");
    }

    $check = $this->db->prepare(
        "SELECT COUNT(*) FROM trajets
         WHERE agence_depart_id = :id
            OR agence_arrivee_id = :id"
    );
    $check->execute(['id' => $id]);

    if ($check->fetchColumn() > 0) {
        throw new \Exception(
            "Impossible de supprimer : l'agence est utilisée par un trajet"
        );
    }

    $stmt = $this->db->prepare("DELETE FROM agences WHERE id = :id");
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
}


public function existsByName(string $name): bool
{
    $stmt = $this->db->prepare(
        "SELECT COUNT(*) FROM agences WHERE nom_agence = :nom"
    );
    $stmt->execute(['nom' => $name]);
    return $stmt->fetchColumn() > 0;
}

}
