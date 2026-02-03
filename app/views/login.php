<?php
// -----------------------------
// Gestion de la redirection si déjà connecté
// -----------------------------

// La session est déjà démarrée dans index.php

// Récupération d'un éventuel message flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']); // On supprime le flash après lecture

// Vérifier si l'utilisateur est connecté
$isLoggedIn = !empty($_SESSION['user']);

if ($isLoggedIn) {

    // Si l'utilisateur est ADMIN → redirection vers le dashboard admin
    if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard');
        exit;
    }

    // Sinon, utilisateur normal → redirection vers la page d'accueil
    header('Location: ' . BASE_URL . '/');
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <!-- Titre -->
            <h3 class="text-center mb-4">Connexion</h3>

            <!-- =========================
                 MESSAGE FLASH
            ========================= -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- =========================
                 FORMULAIRE DE CONNEXION
            ========================= -->
            <form action="<?= BASE_URL ?>/login" method="post" aria-label="Formulaire de connexion">
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="email@entreprise.com"
                           required
                           aria-required="true">
                </div>

                <!-- Mot de passe -->
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Mot de passe"
                           required
                           aria-required="true">
                </div>

                <!-- Bouton connexion -->
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Se connecter
                </button>
            </form>

            <!-- Lien retour accueil -->
            <div class="text-center">
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary w-100">
                    ← Retour à l’accueil
                </a>
            </div>

        </div>
    </div>
</div>
