<?php
// --------------------------------------------------
// public/index.php
// --------------------------------------------------

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

// Page d'accueil : redirige vers login si non connecté
$router->get('/', 'TrajetController@home');

// Authentification
$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// =======================
// Trajets
// =======================

// Création d'un trajet
$router->get('/trajet/create', 'TrajetController@create');
$router->post('/trajet/create', 'TrajetController@store');

// Édition / Mise à jour / Suppression
$router->get('/trajet/edit', 'TrajetController@edit');
$router->post('/trajet/update', 'TrajetController@update');
$router->post('/trajet/delete', 'TrajetController@delete');

// =======================
// Administration
// =======================
$router->get('/admin/dashboard', 'AdminController@index');

// =======================
// Lancer le router
// =======================
$router->run();
