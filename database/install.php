<?php
/**
 * ----------------------------------------------------
 * Installation base de données - 3FN
 * Projet : TOUCHE PAS AU KLAXON
 * ----------------------------------------------------
 * Ce script crée la base, les tables, les contraintes et insère des données
 * de test pour l'application.
 */

require_once __DIR__ . '/../config/config.php';

try {
    // -------------------------------------------------
    // 1️⃣ Connexion MySQL (sans base spécifique)
    // -------------------------------------------------
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gestion des erreurs
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Retour des résultats en tableau associatif
        ]
    );

    echo "<h2>Installation de la base de données</h2>";

    // -------------------------------------------------
    // 2️⃣ Création de la base si elle n'existe pas
    // -------------------------------------------------
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci
    ");
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "✔ Base créée<br>";

    // -------------------------------------------------
    // 3️⃣ Nettoyage des tables existantes
    // -------------------------------------------------
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0"); // Désactivation temporaire des FK
    $pdo->exec("DROP TABLE IF EXISTS reservations");
    $pdo->exec("DROP TABLE IF EXISTS trajets");
    $pdo->exec("DROP TABLE IF EXISTS utilisateurs");
    $pdo->exec("DROP TABLE IF EXISTS agences");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); // Réactivation des FK
    echo "✔ Tables nettoyées<br>";

    // -------------------------------------------------
    // 4️⃣ Création des tables
    // -------------------------------------------------

    // 🔹 Table agences
    $pdo->exec("
        CREATE TABLE agences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom_agence VARCHAR(100) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 🔹 Table utilisateurs
    $pdo->exec("
        CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(50) NOT NULL,
            nom VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            telephone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') NOT NULL DEFAULT 'user'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 🔹 Table trajets
    $pdo->exec("
        CREATE TABLE trajets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agence_depart_id INT NOT NULL,
            agence_arrivee_id INT NOT NULL,
            date_depart DATETIME NOT NULL,
            date_arrivee DATETIME NOT NULL,
            nb_places_totales INT NOT NULL,
            nb_places_disponibles INT NOT NULL,
            conducteur_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            -- Clés étrangères
            CONSTRAINT fk_trajet_depart FOREIGN KEY (agence_depart_id)
                REFERENCES agences(id) ON DELETE RESTRICT,
            CONSTRAINT fk_trajet_arrivee FOREIGN KEY (agence_arrivee_id)
                REFERENCES agences(id) ON DELETE RESTRICT,
            CONSTRAINT fk_trajet_conducteur FOREIGN KEY (conducteur_id)
                REFERENCES utilisateurs(id) ON DELETE CASCADE,
            
            -- Contraintes métiers
            CONSTRAINT chk_places CHECK (nb_places_disponibles <= nb_places_totales),
            CONSTRAINT chk_dates CHECK (date_arrivee > date_depart)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 🔹 Table reservations
    $pdo->exec("
        CREATE TABLE reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trajet_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            nb_places INT NOT NULL,
            date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP,

            -- Clés étrangères
            CONSTRAINT fk_res_trajet FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
            CONSTRAINT fk_res_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            
            -- Unicité et contraintes
            CONSTRAINT uq_reservation_unique UNIQUE (trajet_id, utilisateur_id),
            CONSTRAINT chk_nb_places CHECK (nb_places > 0),

            -- Index pour performance
            INDEX idx_res_trajet (trajet_id),
            INDEX idx_res_user (utilisateur_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "✔ Tables créées<br>";

    // -------------------------------------------------
    // 5️⃣ Insertion des données de base
    // -------------------------------------------------

    // 🔹 Agences
    $agences = ['Paris','Lyon','Marseille','Toulouse','Nice','Nantes','Strasbourg','Montpellier','Bordeaux','Lille','Rennes','Reims'];
    $stmt = $pdo->prepare("INSERT INTO agences (nom_agence) VALUES (:nom)");
    foreach ($agences as $agence) {
        $stmt->execute(['nom' => $agence]);
    }
    echo "✔ Agences insérées<br>";

    // 🔹 Utilisateurs (password hashé)
    $users = [
        ['Martin','Alexandre','0612345678','alexandre.martin@email.fr','user'],
        ['Dubois','Sophie','0698765432','sophie.dubois@email.fr','user'],
        ['Bernard','Julien','0622446688','julien.bernard@email.fr','user'],
        ['Moreau','Camille','0611223344','camille.moreau@email.fr','user'],
        ['Lefèvre','Lucie','0777889900','lucie.lefevre@email.fr','user'],
        ['Leroy','Thomas','0655443322','thomas.leroy@email.fr','user'],
        ['Roux','Chloé','0633221199','chloe.roux@email.fr','user'],
        ['Petit','Maxime','0766778899','maxime.petit@email.fr','user'],
        ['Garnier','Laura','0688776655','laura.garnier@email.fr','user'],
        ['Dupuis','Antoine','0744556677','antoine.dupuis@email.fr','user'],
        // ADMIN
        ['Admin','Super','0600000000','admin@entreprise.com','admin']
    ];
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, telephone, email, password, role)
        VALUES (:nom, :prenom, :telephone, :email, :password, :role)
    ");
    foreach ($users as $u) {
        $stmt->execute([
            'nom'       => $u[0],
            'prenom'    => $u[1],
            'telephone' => $u[2],
            'email'     => $u[3],
            'password'  => $passwordHash,
            'role'      => $u[4]
        ]);
    }
    echo "✔ Utilisateurs insérés (password = password123, un admin inclus)<br>";

    // 🔹 Trajets de test
    $agences_ids = $pdo->query("SELECT id FROM agences")->fetchAll(PDO::FETCH_COLUMN);
    $users_ids   = $pdo->query("SELECT id FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        INSERT INTO trajets (
            agence_depart_id,
            agence_arrivee_id,
            date_depart,
            date_arrivee,
            nb_places_totales,
            nb_places_disponibles,
            conducteur_id
        ) VALUES (:dep, :arr, :dpt, :arrv, :total, :dispo, :conducteur)
    ");
    for ($i = 0; $i < 20; $i++) {
        do {
            $dep = $agences_ids[array_rand($agences_ids)];
            $arr = $agences_ids[array_rand($agences_ids)];
        } while ($dep === $arr); // Assurer que départ ≠ arrivée
        $conducteur = $users_ids[array_rand($users_ids)];
        $date_depart  = date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days'));
        $date_arrivee = date('Y-m-d H:i:s', strtotime($date_depart . ' +' . rand(1, 5) . ' hours'));
        $total = rand(2, 6);
        $stmt->execute([
            'dep'        => $dep,
            'arr'        => $arr,
            'dpt'        => $date_depart,
            'arrv'       => $date_arrivee,
            'total'      => $total,
            'dispo'      => $total,
            'conducteur' => $conducteur
        ]);
    }

    echo "✔ Trajets générés<br>";
    echo "<hr><strong>🎉 Installation terminée avec succès !</strong>";

} catch (PDOException $e) {
    die("<strong>Erreur DB :</strong> " . $e->getMessage());
}
