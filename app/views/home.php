<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php $user = $_SESSION['user'] ?? null; ?>

<div class="container my-5">

    <!-- Titre de la page -->
    <h2 class="mb-4 text-primary text-center">
        Liste des trajets disponibles
    </h2>

    <!-- Conteneur des trajets -->
    <div id="trajets-container" data-user='<?= json_encode($user) ?>'></div>

</div>

<!-- =========================
     MODAL MESSAGE
========================= -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="messageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="messageModalBody" role="alert"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- =========================
     MODAL SUPPRESSION
========================= -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        Êtes-vous sûr de vouloir supprimer ce trajet ?
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-danger" id="confirmDeleteBtn">
            <span id="deleteSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Supprimer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =========================
     MODAL CONDUCTEUR UNIQUE
========================= -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="driverModalLabel">Conducteur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="driverModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('trajets-container');
    const user = JSON.parse(container.dataset.user || 'null'); // Vérifie si utilisateur connecté
    const userLoggedIn = user !== null;
    const BASE_URL = '<?= BASE_URL ?>';
    let trajetToDelete = null;

    /* =========================
       CHARGEMENT DES TRAJETS
       ========================= */
    async function loadTrajets() {
        try {
            const response = await fetch(`${BASE_URL}/trajets/json`);
            const trajets = await response.json();

            if (!trajets.length) {
                container.innerHTML = `<div class="alert alert-info text-center">Aucun trajet disponible.</div>`;
                return;
            }

            // Début du tableau
            let html = `
            <table class="table trajet-table text-center align-middle" aria-describedby="trajets-caption">
                <caption id="trajets-caption">Tableau des trajets disponibles avec agences, dates et places</caption>
                <thead>
                    <tr class="table-primary">
                        <th scope="col">Agence départ</th>
                        <th scope="col">Date départ</th>
                        <th scope="col">Heure</th>
                        <th scope="col">Agence arrivée</th>
                        <th scope="col">Date arrivée</th>
                        <th scope="col">Heure</th>
                        <th scope="col">Places</th>
                        ${userLoggedIn ? '<th scope="col">Actions</th>' : ''}
                    </tr>
                </thead>
                <tbody>
            `;

            trajets.forEach(t => {
                const nbDispo = t.nb_places_disponibles ?? 0;
                const nbTotal = t.nb_places_totales ?? 0;
                const [dD, hD] = t.date_depart.split(' ');
                const [dA, hA] = t.date_arrivee.split(' ');

                // Badge couleur selon disponibilité
                const badgeClass = nbDispo > 0 ? 'bg-success text-white' : 'bg-danger text-white';
                const badgeText = nbDispo > 0 ? nbDispo : 'Complet';

                html += `
                <tr>
                    <td>${t.depart}</td>
                    <td>${dD.split('-').reverse().join('/')}</td>
                    <td>${hD.slice(0,5)}</td>
                    <td>${t.arrivee}</td>
                    <td>${dA.split('-').reverse().join('/')}</td>
                    <td>${hA.slice(0,5)}</td>
                    <td><span class="badge ${badgeClass}">${badgeText}</span> / ${nbTotal}</td>
                `;

                // Actions si utilisateur connecté
                if (userLoggedIn) {
                    html += `<td class="d-flex gap-1 justify-content-center">`;

                    // Voir conducteur
                    html += `<button class="btn btn-sm btn-primary" aria-label="Voir le conducteur du trajet" onclick='showDriverModal(${JSON.stringify(t)})'>
                                <i class="bi bi-eye" aria-hidden="true"></i>
                             </button>`;

                    // Réserver si pas conducteur et places disponibles
                    if (user.id != t.conducteur_id && nbDispo > 0) {
                        html += `<a href="${BASE_URL}/trajet/reserve/${t.id}" class="btn btn-sm btn-success" aria-label="Réserver ce trajet">
                                    <i class="bi bi-calendar-check" aria-hidden="true"></i>
                                 </a>`;
                    }

                    // Modifier / supprimer si conducteur
                    if (user.id == t.conducteur_id) {
                        html += `<a href="${BASE_URL}/trajet/edit/${t.id}" class="btn btn-sm btn-warning" aria-label="Modifier ce trajet">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                 </a>
                                 <button class="btn btn-sm btn-danger" onclick="confirmDelete(${t.id})" aria-label="Supprimer ce trajet">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                 </button>`;
                    }

                    html += `</td>`;
                }

                html += `</tr>`;
            });

            html += `</tbody></table>`;
            container.innerHTML = html;

        } catch (e) {
            console.error(e);
            container.innerHTML = `<div class="alert alert-danger text-center">Erreur de chargement des trajets.</div>`;
        }
    }

    /* =========================
       MODAL CONDUCTEUR
       ========================= */
    window.showDriverModal = function(trajet) {
        const modalBody = document.getElementById('driverModalBody');
        modalBody.innerHTML = `
            <p><strong>Nom :</strong> ${trajet.prenom} ${trajet.nom}</p>
            <p><strong>Téléphone :</strong> ${trajet.telephone}</p>
            <p><strong>Email :</strong> ${trajet.email}</p>
        `;
        new bootstrap.Modal(document.getElementById('driverModal')).show();
    };

    /* =========================
       SUPPRESSION D’UN TRAJET
       ========================= */
    window.confirmDelete = function(id) {
        trajetToDelete = id;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    };

    document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
        if (!trajetToDelete) return;

        const btn = document.getElementById('confirmDeleteBtn');
        const spinner = document.getElementById('deleteSpinner');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const res = await fetch(`${BASE_URL}/trajet/delete/${trajetToDelete}`, { method: 'POST' });
            const result = await res.json();

            document.getElementById('messageModalBody').innerText = result.message || 'Action terminée';
            new bootstrap.Modal(document.getElementById('messageModal')).show();

        } catch (err) {
            console.error(err);
            document.getElementById('messageModalBody').innerText = 'Erreur serveur. Veuillez réessayer.';
            new bootstrap.Modal(document.getElementById('messageModal')).show();
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
            trajetToDelete = null;
            loadTrajets();
        }
    });

    // Chargement initial
    loadTrajets();

});
</script>
