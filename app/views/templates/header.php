<?php
// --------------------------------------------------
// header.php
// --------------------------------------------------

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la config UNE seule fois
require_once __DIR__ . '/../../../config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container">
        <!-- Logo / Nom -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/">
            <?= APP_NAME ?>
        </a>

        <!-- Burger mobile -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMain"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarMain">
            <ul class="navbar-nav align-items-center gap-2">

                <?php if (isset($_SESSION['user'])): 
                    $user = $_SESSION['user'];
                ?>
                    

                    <!-- Bouton proposer trajet -->
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/trajet/create" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Proposer un trajet
                        </a>
                    </li>

                    <!-- Bouton Mes réservations -->
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/reservation/mine" class="btn btn-secondary btn-sm">
                            <i class="bi bi-card-checklist"></i> Mes réservations
                        </a>
                    </li>
                    <!-- Bonjour <prenom nom> -->
                    <li class="nav-item text-light small">
                        <i class="bi bi-person-circle"></i>
                        Bonjour <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                    </li> 
                    
                    <!-- Bouton déconnexion -->
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">
                            Déconnexion
                        </a>
                    </li>

                <?php else: ?>
                    <!-- Connexion si non connecté -->
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm">
                            Connexion
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
