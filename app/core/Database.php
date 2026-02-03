<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * ----------------------------------------------------
 * Classe Database
 * ----------------------------------------------------
 * Gère la connexion à la base de données via PDO
 * en utilisant le pattern Singleton.
 *
 * Objectifs :
 *  - Une seule connexion PDO pour toute l'application
 *  - Centraliser la configuration de la base de données
 *  - Simplifier l'accès à PDO depuis les modèles
 * ----------------------------------------------------
 */
class Database
{
    /**
     * Instance unique de la classe Database (Singleton)
     *
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Instance PDO active
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Constructeur privé
     * Empêche l'instanciation directe
     * Initialise la connexion PDO
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                // DSN MySQL
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                // Identifiants
                DB_USER,
                DB_PASS,
                [
                    // Mode d'erreur : exceptions
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
        } catch (PDOException $e) {
            // Arrêt immédiat de l'application en cas d'erreur critique
            die("Erreur DB : " . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance PDO unique
     * Crée la connexion si elle n'existe pas encore
     *
     * @return PDO
     */
    public static function getInstance()
    {
        // Création de l'instance si elle n'existe pas
        if (!self::$instance) {
            self::$instance = new Database();
        }

        // Retour de l'objet PDO
        return self::$instance->pdo;
    }
}
