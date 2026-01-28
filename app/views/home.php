<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php
// ID de l'utilisateur connecté
$user_id = $_SESSION['user']['id'] ?? null;
$isLoggedIn = !empty($user_id);
?>

<h2 class="mb-4 text-primary-dark">
    <?php if ($isLoggedIn): ?>
        Trajets proposés
    <?php else: ?>
        Pour obtenir plus d'informations sur les trajets, veuillez vous connecter
    <?php endif; ?>
</h2>

<?php if (!empty($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?>">
        <?= htmlspecialchars($flash['message'] ?? '') ?>
    </div>
<?php endif; ?>

<?php if (empty($trajets)): ?>
    <p>Aucun trajet disponible pour le moment.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table trajet-table">
        <thead class="text-center">
            <tr>
                <th>Départ</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Destination</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Places</th>
                <?php if ($isLoggedIn): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($trajets as $trajet): ?>
            <tr class="trajet-row">
                <td class="trajet-info"><?= htmlspecialchars($trajet['depart']) ?></td>
                <td class="trajet-info"><?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></td>
                <td class="trajet-info"><?= date('H:i', strtotime($trajet['date_depart'])) ?></td>
                <td class="trajet-info"><?= htmlspecialchars($trajet['arrivee']) ?></td>
                <td class="trajet-info"><?= date('d/m/Y', strtotime($trajet['date_arrivee'])) ?></td>
                <td class="trajet-info"><?= date('H:i', strtotime($trajet['date_arrivee'])) ?></td>
                <td class="places-dispo"><?= (int)$trajet['nb_places_disponibles'] ?> / <?= (int)$trajet['nb_places_totales'] ?></td>

                <?php if ($isLoggedIn): ?>
                <td>
                    <div class="d-flex justify-content-center gap-2">

                        <!-- Bouton Détails -->
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#trajetModal<?= $trajet['id'] ?>">
                            <i class="bi bi-eye"></i>
                        </button>

                        <!-- Actions auteur -->
                        <?php if ($trajet['auteur_id'] == $user_id): ?>
                            <a href="<?= BASE_URL ?>/trajet/edit/<?= $trajet['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="<?= BASE_URL ?>/trajet/delete/<?= $trajet['id'] ?>" method="POST" class="d-inline">
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce trajet ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </td>
                <?php endif; ?>

            </tr>

            <!-- MODALE DÉTAILS TRAJET -->
            <?php if ($isLoggedIn): ?>
            <div class="modal fade" id="trajetModal<?= $trajet['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Infos sur le conducteur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p><strong>Nom :</strong> <?= htmlspecialchars($trajet['prenom'] . ' ' . $trajet['nom']) ?></p>
                            <p><strong>Téléphone :</strong> <?= htmlspecialchars($trajet['telephone']) ?></p>
                            <p><strong>Email :</strong> <?= htmlspecialchars($trajet['email']) ?></p>
                            <p><strong>Nombre total de places :</strong> <?= (int)$trajet['nb_places_totales'] ?></p>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
