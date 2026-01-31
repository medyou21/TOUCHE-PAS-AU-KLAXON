<?php
session_start();

// Racine du projet
$rootDir = dirname(__DIR__);

// Autoload Composer
require_once $rootDir . '/vendor/autoload.php';

// Config globale
require_once $rootDir . '/config/config.php';

use App\Core\Router;

// Initialisation du router
$router = new Router();

// =======================
// Routes publiques
// =======================
$router->get('/', 'TrajetController@home');
$router->get('/trajets/json', 'TrajetController@listJson');

$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// =======================
// Trajets
// =======================
$router->get('/trajet/create', 'TrajetController@createForm'); // formulaire
$router->post('/trajet/create', 'TrajetController@create');    // création

$router->get('/trajet/edit/{id}', 'TrajetController@edit');    // GET -> formulaire prérempli
$router->post('/trajet/edit/{id}', 'TrajetController@edit'); // POST -> mise à jour
$router->post('/trajet/delete/{id}', 'TrajetController@delete'); // suppression
// =======================
// Réservation
// =======================
$router->get('/trajet/reserve/{id}', 'TrajetController@reserveForm'); // formulaire réservation (GET)
$router->post('/trajet/reserve/{id}', 'TrajetController@reserve');    // réservation (POST)
// Route pour afficher les réservations de l'utilisateur connecté
$router->get('/reservation/mine', 'TrajetController@myReservations');
$router->post('/reservation/cancel/{id}', 'TrajetController@cancelReservation');
$router->post('/reservation/update/{id}', 'TrajetController@updateReservation');


// =======================
// Administration
// =======================
$router->get('/admin/dashboard', 'AdminController@index');

// Lancer le router
$router->run();
