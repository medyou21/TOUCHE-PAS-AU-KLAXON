<?php
/**
 * ----------------------------------------------------
 * Script d'installation de la base de données
 * Projet : TOUCHE PAS AU KLAXON
 * À exécuter une seule fois
 * ----------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';

try {
    // Connexion MySQL sans base (pour CREATE DATABASE)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "<h2>Installation de la base de données</h2>";

    // -------------------------------------------------
    // 1. Création de la base
    // -------------------------------------------------
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci
    ");
    echo "✔ Base de données créée<br>";

    $pdo->exec("USE `" . DB_NAME . "`");

    // -------------------------------------------------
    // 2. Nettoyage
    // -------------------------------------------------
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS trajets");
    $pdo->exec("DROP TABLE IF EXISTS utilisateurs");
    $pdo->exec("DROP TABLE IF EXISTS agences");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✔ Tables nettoyées<br>";

    // -------------------------------------------------
    // 3. Tables
    // -------------------------------------------------

    // Agences
    $pdo->exec("
        CREATE TABLE agences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom_agence VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Utilisateurs
    $pdo->exec("
        CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(50) NOT NULL,
            nom VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            telephone VARCHAR(20) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Trajets
    $pdo->exec("
        CREATE TABLE trajets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agence_depart_id INT NOT NULL,
            agence_arrivee_id INT NOT NULL,
            date_depart DATETIME NOT NULL,
            date_arrivee DATETIME NOT NULL,
            nb_places_totales INT NOT NULL,
            nb_places_disponibles INT NOT NULL,
            contact_id INT NOT NULL,
            auteur_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (agence_depart_id) REFERENCES agences(id) ON DELETE CASCADE,
            FOREIGN KEY (agence_arrivee_id) REFERENCES agences(id) ON DELETE CASCADE,
            FOREIGN KEY (contact_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "✔ Tables créées<br>";

    // -------------------------------------------------
    // 4. Données de base
    // -------------------------------------------------

    // Agences
    $agences = [
        'Paris','Lyon','Marseille','Toulouse','Nice','Nantes',
        'Strasbourg','Montpellier','Bordeaux','Lille','Rennes','Reims'
    ];

    $stmt = $pdo->prepare("INSERT INTO agences (nom_agence) VALUES (:nom)");
    foreach ($agences as $agence) {
        $stmt->execute(['nom' => $agence]);
    }
    echo "✔ Agences insérées<br>";

    // Utilisateurs
    $users = [
        ['Martin','Alexandre','0612345678','alexandre.martin@email.fr'],
        ['Dubois','Sophie','0698765432','sophie.dubois@email.fr'],
        ['Bernard','Julien','0622446688','julien.bernard@email.fr'],
        ['Moreau','Camille','0611223344','camille.moreau@email.fr'],
        ['Lefèvre','Lucie','0777889900','lucie.lefevre@email.fr'],
        ['Leroy','Thomas','0655443322','thomas.leroy@email.fr'],
        ['Roux','Chloé','0633221199','chloe.roux@email.fr'],
        ['Petit','Maxime','0766778899','maxime.petit@email.fr'],
        ['Garnier','Laura','0688776655','laura.garnier@email.fr'],
        ['Dupuis','Antoine','0744556677','antoine.dupuis@email.fr'],
        ['Lefebvre','Emma','0699887766','emma.lefebvre@email.fr'],
        ['Fontaine','Louis','0655667788','louis.fontaine@email.fr'],
        ['Chevalier','Clara','0788990011','clara.chevalier@email.fr'],
        ['Robin','Nicolas','0644332211','nicolas.robin@email.fr'],
        ['Gauthier','Marine','0677889922','marine.gauthier@email.fr'],
        ['Fournier','Pierre','0722334455','pierre.fournier@email.fr'],
        ['Girard','Sarah','0688665544','sarah.girard@email.fr'],
        ['Lambert','Hugo','0611223366','hugo.lambert@email.fr'],
        ['Masson','Julie','0733445566','julie.masson@email.fr'],
        ['Henry','Arthur','0666554433','arthur.henry@email.fr']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, telephone, email)
        VALUES (:nom, :prenom, :telephone, :email)
    ");

    foreach ($users as $u) {
        $stmt->execute([
            'nom' => $u[0],
            'prenom' => $u[1],
            'telephone' => $u[2],
            'email' => $u[3]
        ]);
    }
    echo "✔ Utilisateurs insérés<br>";

    // -------------------------------------------------
    // 5. Trajets aléatoires
    // -------------------------------------------------
    $agences_ids = $pdo->query("SELECT id FROM agences")->fetchAll(PDO::FETCH_COLUMN);
    $users_ids   = $pdo->query("SELECT id FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        INSERT INTO trajets
        (agence_depart_id, agence_arrivee_id, date_depart, date_arrivee,
         nb_places_totales, nb_places_disponibles, contact_id, auteur_id)
        VALUES (:dep, :arr, :dpt, :arrv, :total, :dispo, :contact, :auteur)
    ");

    for ($i = 0; $i < 20; $i++) {
        do {
            $dep = $agences_ids[array_rand($agences_ids)];
            $arr = $agences_ids[array_rand($agences_ids)];
        } while ($dep === $arr);

        $user = $users_ids[array_rand($users_ids)];

        $date_depart  = date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days'));
        $date_arrivee = date('Y-m-d H:i:s', strtotime($date_depart . ' +' . rand(1, 5) . ' hours'));

        $total = rand(2, 6);
        $dispo = rand(1, $total);

        $stmt->execute([
            'dep'     => $dep,
            'arr'     => $arr,
            'dpt'     => $date_depart,
            'arrv'    => $date_arrivee,
            'total'   => $total,
            'dispo'   => $dispo,
            'contact' => $user,
            'auteur'  => $user
        ]);
    }

    echo "✔ Trajets générés<br>";

    echo "<hr><strong>🎉 Installation terminée avec succès !</strong>";

} catch (PDOException $e) {
    die("<strong>Erreur DB :</strong> " . $e->getMessage());
}
