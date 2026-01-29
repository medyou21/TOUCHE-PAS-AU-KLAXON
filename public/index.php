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

$router->get('/trajet/edit/{id}', 'TrajetController@edit');
$router->post('/trajet/update/{id}', 'TrajetController@update');
$router->post('/trajet/delete/{id}', 'TrajetController@delete');

// =======================
// Administration
// =======================
$router->get('/admin/dashboard', 'AdminController@index');

// Lancer le router
$router->run();
