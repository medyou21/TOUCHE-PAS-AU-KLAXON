# TOUCHE PAS AU KLAXON 🚗💨

Application web de **covoiturage interne pour entreprises**, développée en PHP selon une architecture **MVC**.  
Elle permet la création de trajets inter-sites, la réservation par les collaborateurs et la gestion globale par un administrateur.

---

## 📁 Arborescence du projet

touche_pas_au_klaxon/
│
├── public/                      # Dossier accessible depuis le web
│   ├── index.php                # Point d’entrée de l’application
│   ├── assets/
│   │   ├── css/
│   │   │   └── main.css         # CSS compilé depuis Sass
│   │   └── js/                  # JavaScript (Bootstrap, modales, etc.)
│
├── app/
│   ├── Controllers/             # Contrôleurs MVC
│   │   ├── AuthController.php
│   │   ├── TrajetController.php
│   │   └── AdminController.php
│   │
│   ├── Models/                  # Modèles de données
│   │   ├── Trajet.php
│   │   ├── Agence.php
│   │   ├── Utilisateur.php
│   │   └── Reservation.php
│   │
│   ├── Views/                   # Vues (HTML/PHP)
│   │   ├── templates/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── admin_agences.php
│   │   ├── admin_dashboard.php
│   │   ├── admin_trajets.php
│   │   ├── admin_users.php
│   │   ├── edit_trajet.php
│   │   ├── home.php
│   │   ├── login.php
│   │   ├── my_reservations.php
│   │   ├── reservation_form.php
│   │   └── trajet_form.php
│   │
│   └── Core/                    # Classes cœur (framework maison)
│       ├── Router.php
│       ├── Controller.php
│       └── Database.php
│
├── Database/
│   └── install.php              # Script d’installation de la base (3FN)
│
├── config/
│   └── config.php               # Configuration globale & base de données
│
├── tests/                       # Tests unitaires PHPUnit
│   ├── TrajetTest.php
│   ├── ReservationTest.php
│   └── TrajetFormTest.php
│
├── vendor/                      # Dépendances Composer
│
├── sass/                        # Fichiers Sass
│   └── main.scss
│
├── phpstan.neon                 # Configuration PHPStan
├── phpunit.xml                  # Configuration PHPUnit
├── composer.json
└── README.md

⚙️ Prérequis
PHP >= 8.2

MySQL ou MariaDB

Composer

Serveur web (Apache / Nginx / XAMPP / WAMP)

Node.js + npm (pour compiler le SCSS)

🚀 Installation

1️⃣ Cloner le dépôt
git clone https://github.com/<votre-utilisateur>/touche_pas_au_klaxon.git
cd touche_pas_au_klaxon

2️⃣ Installer les dépendances PHP
composer install

3️⃣ Configurer la base de données

Modifier le fichier config/config.php :

define('DB_HOST', 'localhost');
define('DB_NAME', 'touche_pas_au_klaxonn');
define('DB_USER', 'root');
define('DB_PASS', '');

4️⃣ Créer la base et les tables

php Database/install.php
✔ Crée les tables en 3e forme normale (3FN)
✔ Ajoute des données de test (agences, utilisateurs, trajets)

5️⃣ Compiler le SCSS
sass sass/main.scss public/assets/css/main.css

6️⃣ Lancer l’application
Accéder à :

http://localhost/touche_pas_au_klaxon/public

📝 Fonctionnalités
Authentification utilisateurs (login / logout)

Création, modification et suppression de trajets

Réservation de trajets avec gestion des places disponibles

Gestion des agences

Dashboard administrateur avec statistiques

Interfaces responsives (Bootstrap 5)

Validation métier côté backend et base de données

🧪 Qualité & Tests
✅ Tests unitaires (PHPUnit)
vendor/bin/phpunit --testdox
Tests des modèles (Trajet, Reservation)

Tests des règles métiers (formulaires)

Base de données réelle utilisée pour les tests

✅ Analyse statique (PHPStan)
vendor/bin/phpstan analyse app
Niveau strict

Typage fort

Aucune erreur bloquante

📌 Technologies utilisées
PHP 8 (architecture MVC)

MySQL / MariaDB

Composer (autoload PSR-4)

PHPUnit (tests unitaires)

PHPStan (analyse statique)

SCSS (Sass)

Bootstrap 5

JavaScript (vanilla)

📖 Notes techniques
Les contraintes métiers sont validées :

agences de départ / arrivée différentes

dates cohérentes

places disponibles ≤ places totales

Les règles sont appliquées à la fois côté PHP et base de données

Le code respecte les bonnes pratiques :

encapsulation

séparation des responsabilités

testabilité

👤 Auteur
Projet réalisé par Mohamed Hamdi
