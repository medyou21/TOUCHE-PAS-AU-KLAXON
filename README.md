# TOUCHE PAS AU KLAXON 🚗💨

Application web de gestion de covoiturage interne pour entreprises.  
Permet la création de trajets, la réservation par les utilisateurs et la gestion par un administrateur.

---

## 📁 Arborescence du projet

touche_pas_au_klaxon/
│
├── public/ # Dossier accessible depuis le web
│ ├── index.php # Point d’entrée de l’application
│ ├── assets/
│ │ └── css/ # CSS compilé depuis Sass
│ │ └── main.css
│ └── js/ # JavaScript (Bootstrap, modals, etc.)
│
├── app/
│ ├── Controllers/ # Contrôleurs MVC
│ │ ├── AuthController.php
│ │ ├── TrajetController.php
│ │ └── AdminController.php
│ │
│ ├── Models/ # Modèles de données
│ │ ├── Trajet.php
│ │ ├── Agence.php
│ │ ├── Utilisateur.php
│ │ └── Reservation.php
│ │
│ ├── Views/ # Vues de l’application
│ │ ├── templates/
│ │ │ ├── header.php
│ │ │ └── footer.php
│ │ ├── admin_agences.php
│ │ ├── admin_dashboard.php
│ │ ├── admin_trajets.php
│ │ ├── admin_users.php
│ │ ├── edit_trajet.php
│ │ ├── home.php
│ │ ├── login.php
│ │ ├── my_reservations.php
│ │ ├── reservation_form.php
│ │ └── trajet_form.php
│ │
│ └── Core/ # Classes de base (Router, Controller, DB)
│ ├── Router.php
│ ├── Controller.php
│ └── Database.php
│
├── Database/
│ └── install.php # Script d’installation de la base de données (3FN)
│
├── config/
│ └── config.php # Variables globales et configuration DB
│
├── vendor/ # Librairies Composer
│
├── sass/ # Fichiers Sass à compiler
│ └── main.scss
│
└── composer.json # Configuration Composer


---

## ⚙️ Prérequis

- PHP >= 8.0
- MySQL / MariaDB
- Composer
- Serveur web (Apache, Nginx, ou XAMPP/WAMP pour local)
- Node.js + npm (si compilation SCSS via `sass`)

---

## 🚀 Installation

1. **Cloner le dépôt**

```bash
git clone https://github.com/<votre-utilisateur>/touche_pas_au_klaxon.git
cd touche_pas_au_klaxon
Installer les dépendances PHP

composer install
Configurer la base de données

Copier config/config.php et adapter les valeurs :

define('DB_HOST', 'localhost');
define('DB_NAME', 'touche_pas_au_klaxon');
define('DB_USER', 'root');
define('DB_PASS', '');
Créer la base et les tables

php Database/install.php
Ce script crée les tables, les contraintes et insère des données de test (utilisateurs, agences, trajets).

Compiler le Sass en CSS

sass sass/main.scss public/assets/css/main.css
Lancer l’application

Accéder à http://localhost/touche_pas_au_klaxon/public via votre serveur web local.

📝 Fonctionnalités
Gestion des utilisateurs et authentification (login/logout)

Création, modification et suppression de trajets

Réservation de trajets par les utilisateurs

Gestion des agences et affichage des disponibilités

Dashboard administrateur avec statistiques

Interfaces responsives et animées (Bootstrap + SCSS)

🔐 Identifiants de test
Admin
Email : admin@entreprise.com
Mot de passe : password123

Utilisateur type
Email : alexandre.martin@email.fr
Mot de passe : password123

📌 Technologies utilisées
PHP (MVC)

MySQL / MariaDB

Composer (autoloader)

SCSS (Sass)

Bootstrap 5

JavaScript (vanilla + modals)

📖 Notes
Toutes les dates de trajets sont générées aléatoirement lors de l’installation.

Les contraintes métiers sont appliquées via MySQL (places disponibles, date de départ/arrivée).

Le CSS est modulable grâce aux variables SCSS.

Les modales et notifications utilisent Bootstrap 5.



