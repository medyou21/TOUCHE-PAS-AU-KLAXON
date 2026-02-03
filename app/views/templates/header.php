<?php
// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration globale
require_once __DIR__ . '/../../../config/config.php';

// Récupérer l'utilisateur connecté (s'il y en a)
$user = $_SESSION['user'] ?? null;

// Lien du logo : vers l'accueil ou le dashboard admin si admin connecté
$logoLink = BASE_URL . '/';
if ($user && ($user['role'] ?? '') === 'admin') {
    $logoLink = BASE_URL . '/admin/dashboard';
}

// Métadonnées SEO
$pageTitle = APP_NAME;
$pageDescription = "Application de covoiturage pour partager vos trajets facilement.";
$pageKeywords = "covoiturage, trajet, transport, partage, voyage";
$author = APP_NAME;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO -->
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($author) ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph (partage réseaux sociaux) -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL ?>">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/images/og-image.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>

<body>

<!-- Navigation principale -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm bg-light" role="navigation">
    <div class="container">
        <!-- Logo / Nom de l'application -->
        <a class="navbar-brand fw-bold text-primary" href="<?= $logoLink ?>">
            <?= APP_NAME ?>
        </a>

        <!-- Bouton burger pour mobiles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Menu principal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu principal -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarMain">
            <ul class="navbar-nav align-items-center gap-2">

                <?php if ($user): ?>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <!-- Menu pour les admins -->
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-warning btn-sm text-dark">
                                <i class="bi bi-people" aria-hidden="true"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/admin/agences" class="btn btn-warning btn-sm text-dark">
                                <i class="bi bi-building" aria-hidden="true"></i> Agences
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/admin/trajets" class="btn btn-warning btn-sm text-dark">
                                <i class="bi bi-truck" aria-hidden="true"></i> Trajets
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Menu pour les utilisateurs classiques -->
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/trajet/create" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Proposer un trajet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/reservation/mine" class="btn btn-secondary btn-sm">
                                <i class="bi bi-card-checklist" aria-hidden="true"></i> Mes réservations
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Affiche le nom de l'utilisateur -->
                    <li class="nav-item small d-flex align-items-center text-light">
                        <i class="bi bi-person-circle me-1" aria-hidden="true"></i>
                        Bonjour <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                    </li>

                    <!-- Bouton Déconnexion -->
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm" aria-label="Déconnexion">
                            Déconnexion
                        </a>
                    </li>

                <?php else: ?>
                    <!-- Utilisateur non connecté -->
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

<!-- Contenu principal -->
<main class="container my-4">
