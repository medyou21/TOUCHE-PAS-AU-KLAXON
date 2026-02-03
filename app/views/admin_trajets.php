<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-4 text-primary">
        <i class="bi bi-truck" aria-hidden="true"></i>
        <span class="visually-hidden">Icône trajets</span>
        Gestion des trajets
    </h2>

    <div id="trajets-container" role="status" aria-live="polite">
        <div class="text-center text-muted">
            Chargement des trajets...
        </div>
    </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

/* ===============================
 * UTILITAIRES MESSAGES MODAL
 * =============================== */
function showModalMessage(id, message, type = 'danger') {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = `alert alert-${type}`;
    el.innerText = message;
    el.classList.remove('d-none');
}

function clearModalMessage(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('d-none');
}

/* ===============================
 * Chargement des trajets (ADMIN)
 * =============================== */
async function loadTrajets() {
    const container = document.getElementById('trajets-container');

    try {
        const response = await fetch(`${BASE_URL}/trajets/json`);
        const trajets = await response.json();

        if (!trajets.length) {
            container.innerHTML =
                `<div class="alert alert-info text-center" role="alert">
                    Aucun trajet disponible.
                 </div>`;
            return;
        }

        let html = `
        <table class="table trajet-table text-center align-middle">
            <caption class="visually-hidden">Liste des trajets disponibles avec dates, horaires, places et actions</caption>
            <thead>
                <tr>
                    <th scope="col">Départ</th>
                    <th scope="col">Date départ</th>
                    <th scope="col">Heure</th>
                    <th scope="col">Arrivée</th>
                    <th scope="col">Date arrivée</th>
                    <th scope="col">Heure</th>
                    <th scope="col">Places</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>`;

        trajets.forEach(t => {
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
                <td><strong>${t.nb_places_disponibles}</strong> / ${t.nb_places_totales}</td>
                <td class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-sm btn-primary"
                        aria-label="Voir le conducteur du trajet ${t.depart} → ${t.arrivee}"
                        data-bs-toggle="modal"
                        data-bs-target="#trajetModal${t.id}">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>

                    <button class="btn btn-sm btn-danger"
                        aria-label="Supprimer le trajet ${t.depart} → ${t.arrivee}"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteTrajetModal${t.id}">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>

            <!-- Modal infos conducteur -->
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
            </div>

            <!-- Modal suppression trajet -->
            <div class="modal fade" id="deleteTrajetModal${t.id}" tabindex="-1" aria-labelledby="deleteTrajetModalLabel${t.id}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteTrajetModalLabel${t.id}">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <div id="delete-trajet-message-${t.id}" class="alert d-none" role="alert"></div>
                            Voulez-vous vraiment supprimer ce trajet ?
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteTrajet(${t.id})">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;

    } catch (e) {
        console.error(e);
        container.innerHTML =
            `<div class="alert alert-danger text-center" role="alert">
                Erreur de chargement des trajets.
             </div>`;
    }
}

/* ===============================
 * Suppression trajet (ADMIN)
 * =============================== */
async function deleteTrajet(id) {
    const messageId = `delete-trajet-message-${id}`;
    clearModalMessage(messageId);

    try {
        const resp = await fetch(`${BASE_URL}/admin/delete-trajet/${id}`, {
            method: 'POST'
        });

        const result = await resp.json();

        if (!result.success) {
            showModalMessage(messageId, result.message, 'danger');
            return;
        }

        showModalMessage(messageId, result.message, 'success');

        setTimeout(() => {
            bootstrap.Modal.getInstance(
                document.getElementById(`deleteTrajetModal${id}`)
            ).hide();
            loadTrajets();
        }, 800);

    } catch (e) {
        showModalMessage(messageId, 'Erreur serveur. Veuillez réessayer.', 'danger');
        console.error(e);
    }
}

/* ===============================
 * Initialisation
 * =============================== */
loadTrajets();
</script>
