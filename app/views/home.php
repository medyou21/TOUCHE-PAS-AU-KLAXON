<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php $user = $_SESSION['user'] ?? null; ?>

<div class="container my-5">

    <h2 class="mb-4 text-primary text-center">
        Liste des trajets disponibles
    </h2>

    <div id="trajets-container" data-user='<?= json_encode($user) ?>'></div>

</div>

<!-- Modal message -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="messageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="messageModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal suppression -->
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
        <button class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('trajets-container');
    const user = JSON.parse(container.dataset.user || 'null');
    const userLoggedIn = user !== null;
    const BASE_URL = '<?= BASE_URL ?>';
    let trajetToDelete = null;

    async function loadTrajets() {
        try {
            const response = await fetch(`${BASE_URL}/trajets/json`);
            const trajets = await response.json();

            if (!trajets.length) {
                container.innerHTML = `<div class="alert alert-info text-center">Aucun trajet disponible.</div>`;
                return;
            }

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

            let modals = '';

            trajets.forEach(t => {
                const nbDispo = t.nb_places_disponibles ?? 0;
                const nbTotal = t.nb_places_totales ?? 0;
                const [dD, hD] = t.date_depart.split(' ');
                const [dA, hA] = t.date_arrivee.split(' ');

                html += `
                <tr>
                    <td>${t.depart}</td>
                    <td>${dD.split('-').reverse().join('/')}</td>
                    <td>${hD.slice(0,5)}</td>
                    <td>${t.arrivee}</td>
                    <td>${dA.split('-').reverse().join('/')}</td>
                    <td>${hA.slice(0,5)}</td>
                    <td><strong>${nbDispo}</strong> / ${nbTotal}</td>
                `;

                if (userLoggedIn) {
                    html += `<td class="d-flex gap-1 justify-content-center">`;

                    html += `<button class="btn btn-sm btn-primary" aria-label="Voir le conducteur du trajet" data-bs-toggle="modal" data-bs-target="#trajetModal${t.id}">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                             </button>`;

                    if (user.id != t.conducteur_id && nbDispo > 0) {
                        html += `<a href="${BASE_URL}/trajet/reserve/${t.id}" class="btn btn-sm btn-success" aria-label="Réserver ce trajet">
                                    <i class="bi bi-calendar-check" aria-hidden="true"></i>
                                 </a>`;
                    }

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

                modals += `
                <div class="modal fade" id="trajetModal${t.id}" tabindex="-1" aria-labelledby="trajetModalLabel${t.id}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="trajetModalLabel${t.id}">Conducteur</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body text-start">
                                <p><strong>Nom :</strong> ${t.prenom} ${t.nom}</p>
                                <p><strong>Téléphone :</strong> ${t.telephone}</p>
                                <p><strong>Email :</strong> ${t.email}</p>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            html += `</tbody></table>`;
            container.innerHTML = html + modals;

        } catch (e) {
            container.innerHTML = `<div class="alert alert-danger text-center">Erreur de chargement</div>`;
        }
    }

    window.confirmDelete = function(id) {
        trajetToDelete = id;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    };

    document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
        if (!trajetToDelete) return;
        const res = await fetch(`${BASE_URL}/trajet/delete/${trajetToDelete}`, { method: 'POST' });
        const result = await res.json();
        document.getElementById('messageModalBody').innerText = result.message;
        new bootstrap.Modal(document.getElementById('messageModal')).show();
        trajetToDelete = null;
        loadTrajets();
    });

    loadTrajets();
});
</script>
