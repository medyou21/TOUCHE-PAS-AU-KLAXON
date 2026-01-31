<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5">

    <h2 class="text-center mb-4 text-primary">Mes réservations</h2>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info text-center">
            Vous n'avez réservé aucun trajet.
        </div>
    <?php else: ?>

        <table class="table trajet-table text-center align-middle">
            <thead>
                <tr class="table-primary">
                    <th>Agence départ</th>
                    <th>Date départ</th>
                    <th>Heure</th>
                    <th>Agence arrivée</th>
                    <th>Date arrivée</th>
                    <th>Heure</th>
                    <th>Places réservées</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($reservations as $r): ?>
                <?php
                    [$dateD, $heureD] = explode(' ', $r['date_depart']);
                    [$dateA, $heureA] = explode(' ', $r['date_arrivee']);

                    $nbReservees = (int) $r['nb_places'];
                    $nbTotal = (int) $r['nb_places_totales'];
                    $nbDispo = (int) $r['nb_places_disponibles'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['depart']) ?></td>
                    <td><?= date('d/m/Y', strtotime($dateD)) ?></td>
                    <td><?= substr($heureD, 0, 5) ?></td>
                    <td><?= htmlspecialchars($r['arrivee']) ?></td>
                    <td><?= date('d/m/Y', strtotime($dateA)) ?></td>
                    <td><?= substr($heureA, 0, 5) ?></td>
                    <td>
                        <strong><?= $nbReservees ?></strong> / <?= $nbTotal ?>
                    </td>
                    <td class="d-flex gap-1 justify-content-center">

                        <!-- Modifier réservation -->
                        <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= $r['id'] ?>"
                                data-places="<?= $nbReservees ?>"
                                data-max="<?= $nbReservees + $nbDispo ?>">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!-- Annuler réservation -->
                        <button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#cancelModal"
                                data-id="<?= $r['id'] ?>">
                            <i class="bi bi-x-circle"></i>
                        </button>

                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</div>

<!-- ================= MODAL ANNULATION ================= -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="cancelForm">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Annuler la réservation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          Voulez-vous vraiment annuler cette réservation ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
          <button type="submit" class="btn btn-danger">Confirmer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL MODIFICATION ================= -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="editForm">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier la réservation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Nombre de places</label>
          <input type="number"
                 name="nb_places"
                 id="editNbPlaces"
                 class="form-control"
                 min="1"
                 required>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Annuler
          </button>
          <button type="submit" class="btn btn-warning">
            Enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
// Modal annulation
document.getElementById('cancelModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const reservationId = button.getAttribute('data-id');

    document.getElementById('cancelForm').action =
        '<?= BASE_URL ?>/reservation/cancel/' + reservationId;
});

// Modal modification
document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const reservationId = button.getAttribute('data-id');
    const places = button.getAttribute('data-places');
    const maxPlaces = button.getAttribute('data-max');

    const input = document.getElementById('editNbPlaces');
    input.value = places;
    input.max = maxPlaces;

    document.getElementById('editForm').action =
        '<?= BASE_URL ?>/reservation/update/' + reservationId;
});
</script>
