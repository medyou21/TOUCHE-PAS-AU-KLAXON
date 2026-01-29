<?php
// On suppose que session_start() a déjà été appelé
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Vérifier si l'utilisateur est connecté
$isLoggedIn = !empty($_SESSION['user']);
?>

<?php if ($isLoggedIn): ?>
    <?php header('Location: ' . BASE_URL . '/'); exit; ?>
<?php endif; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <h3 class="text-center mb-4">Connexion</h3>

            <!-- Flash message -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="email@entreprise.com"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Mot de passe"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Se connecter
                </button>
            </form>

            <!-- Retour à l'accueil -->
            <div class="text-center">
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary w-100">
                    ← Retour à l’accueil
                </a>
            </div>

        </div>
    </div>
</div>
