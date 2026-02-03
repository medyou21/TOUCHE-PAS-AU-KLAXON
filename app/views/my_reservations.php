<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5">

    <!-- Titre -->
    <h2 class="text-center mb-4 text-primary">Mes réservations</h2>

    <!-- Si l'utilisateur n'a aucune réservation -->
    <?php if (empty($reservations)): ?>
        <div class="alert alert-info text-center" role="status">
            Vous n'avez réservé aucun trajet.
        </div>
    <?php else: ?>

        <!-- Tableau des réservations -->
        <table class="table trajet-table text-center align-middle">
            <thead>
                <tr class="table-primary">
                    <th scope="col">Agence départ</th>
                    <th scope="col">Date départ</th>
                    <th scope="col">Heure</th>
                    <th scope="col">Agence arrivée</th>
                    <th scope="col">Date arrivée</th>
                    <th scope="col">Heure</th>
                    <th scope="col">Places réservées</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($reservations as $r): ?>
                <?php
                    [$dateD, $heureD] = explode(' ', $r['date_depart']);
                    [$dateA, $heureA] = explode(' ', $r['date_arrivee']);

                    $nbReservees = (int) ($r['nb_places'] ?? 0);
                    $nbTotal = (int) ($r['nb_places_totales'] ?? 0);
                    $nbDispo = (int) ($r['nb_places_disponibles'] ?? 0);

                    // Badge couleur selon disponibilité
                    $badgeClass = $nbDispo > 0 ? 'bg-success text-white' : 'bg-danger text-white';
                    $badgeText = $nbReservees; // Affiche le nombre réservé
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['depart']) ?></td>
                    <td><?= date('d/m/Y', strtotime($dateD)) ?></td>
                    <td><?= substr($heureD, 0, 5) ?></td>
                    <td><?= htmlspecialchars($r['arrivee']) ?></td>
                    <td><?= date('d/m/Y', strtotime($dateA)) ?></td>
                    <td><?= substr($heureA, 0, 5) ?></td>
                    <td>
                        <span class="badge <?= $badgeClass ?>">
                            <?= $badgeText ?>
                        </span> / <?= $nbTotal ?>
                    </td>
                    <td class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-sm btn-warning"
                                aria-label="Modifier réservation"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= $r['id'] ?>"
                                data-places="<?= $nbReservees ?>"
                                data-max="<?= $nbReservees + $nbDispo ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger"
                                aria-label="Annuler réservation"
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
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="cancelForm">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="cancelModalLabel">Annuler la réservation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body text-center" role="alert">
          Voulez-vous vraiment annuler cette réservation ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Retour
          </button>
          <button type="submit" class="btn btn-danger">
            Confirmer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL MODIFICATION ================= -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="editForm">
        <div class="modal-header bg-warning">
          <h5 class="modal-title" id="editModalLabel">Modifier la réservation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
          <label class="form-label" for="editNbPlaces">Nombre de places</label>
          <input type="number"
                 name="nb_places"
                 id="editNbPlaces"
                 class="form-control"
                 min="1"
                 required
                 aria-describedby="maxPlacesInfo">
          <div id="maxPlacesInfo" class="form-text">Maximum possible selon la disponibilité</div>
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
/**
 * Dynamique des modals : 
 * - Annulation d'une réservation
 * - Modification du nombre de places réservées
 */
document.getElementById('cancelModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; // Bouton qui a déclenché le modal
    const reservationId = button.getAttribute('data-id');
    // Mise à jour de l'action du formulaire selon la réservation
    document.getElementById('cancelForm').action =
        '<?= BASE_URL ?>/reservation/cancel/' + reservationId;
});

document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const reservationId = button.getAttribute('data-id');
    const places = button.getAttribute('data-places');
    const maxPlaces = button.getAttribute('data-max');

    const input = document.getElementById('editNbPlaces');
    input.value = places;   // Remplir le champ avec le nombre actuel de places
    input.max = maxPlaces;  // Définir le max selon la disponibilité

    // Mise à jour de l'action du formulaire selon la réservation
    document.getElementById('editForm').action =
        '<?= BASE_URL ?>/reservation/update/' + reservationId;
});
</script>
