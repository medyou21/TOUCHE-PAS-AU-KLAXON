<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php
/** 
 * @var array<string,mixed> $trajet   // Informations du trajet à réserver
 * @var array<string,mixed> $user     // Données de l'utilisateur connecté
 * @var string|null $message          // Message à afficher (trajet complet, erreur, etc.)
 */
?>

<div class="container my-5">

    <h2 class="text-center text-success mb-4">Réserver un trajet</h2>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Détails du trajet
        </div>
        <div class="card-body">

            <p><strong>Départ :</strong> <span id="trajetDepart"><?= htmlspecialchars($trajet['depart'] ?? '') ?></span></p>
            <p><strong>Arrivée :</strong> <span id="trajetArrivee"><?= htmlspecialchars($trajet['arrivee'] ?? '') ?></span></p>
            <p><strong>Date départ :</strong> <span id="trajetDateDepart"><?= !empty($trajet['date_depart']) ? date('d/m/Y H:i', strtotime($trajet['date_depart'])) : '' ?></span></p>
            <p><strong>Date arrivée :</strong> <span id="trajetDateArrivee"><?= !empty($trajet['date_arrivee']) ? date('d/m/Y H:i', strtotime($trajet['date_arrivee'])) : '' ?></span></p>
            <p>
                <strong>Places disponibles :</strong>
                <span class="badge bg-success" id="trajetPlaces" role="status"><?= (int)($trajet['nb_places_disponibles'] ?? 0) ?></span>
            </p>

            <hr>

            <form id="reservationForm" aria-describedby="reservationInfo">
                <div class="mb-3">
                    <label for="nb_places" class="form-label">Nombre de places à réserver</label>
                    <input
                        type="number"
                        id="nb_places"
                        name="nb_places"
                        class="form-control"
                        min="1"
                        max="<?= (int)($trajet['nb_places_disponibles'] ?? 1) ?>"
                        required
                        aria-describedby="maxPlacesInfo"
                    >
                    <div id="maxPlacesInfo" class="form-text">
                        Maximum <?= (int)($trajet['nb_places_disponibles'] ?? 1) ?> places disponibles
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/" class="btn btn-secondary" aria-label="Annuler la réservation">Annuler</a>
                    <button type="submit" class="btn btn-success" aria-label="Réserver le trajet">
                        <i class="bi bi-calendar-check"></i> Réserver
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="messageModalLabel">Réservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="messageModalBody" role="alert"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Fermer le message">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reservationForm = document.getElementById('reservationForm');
    if (!reservationForm) return;

    const inputNbPlaces = reservationForm.querySelector('input[name="nb_places"]');
    const trajetPlacesBadge = document.getElementById('trajetPlaces');
    const BASE_URL = '<?= BASE_URL ?>';
    const trajetId = <?= (int)($trajet['id'] ?? 0) ?>;

    // Affiche le message initial si nécessaire
    <?php if(!empty($message)): ?>
        const modalBody = document.getElementById('messageModalBody');
        modalBody.innerText = <?= json_encode($message) ?>;
        const modal = new bootstrap.Modal(document.getElementById('messageModal'));
        modal.show();

        if(inputNbPlaces) inputNbPlaces.disabled = true;
        const submitBtn = reservationForm.querySelector('button[type="submit"]');
        if(submitBtn) submitBtn.disabled = true;
    <?php endif; ?>

    reservationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if(!inputNbPlaces) return;

        const formData = new FormData(this);

        try {
            const response = await fetch(`${BASE_URL}/trajet/reserve/${trajetId}`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            const modalBody = document.getElementById('messageModalBody');
            modalBody.innerText = result.message || 'Message inconnu';
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();

            if (result.success) {
                setTimeout(() => window.location.href = `${BASE_URL}/`, 1500);
            }

        } catch (err) {
            const modalBody = document.getElementById('messageModalBody');
            modalBody.innerText = 'Erreur serveur';
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        }
    });
});
</script>
