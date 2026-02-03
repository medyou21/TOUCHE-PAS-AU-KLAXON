<?php 
// Inclusion du header commun (navigation, CSS, SEO, etc.)
require_once __DIR__ . '/templates/header.php'; 
?>

<div class="container my-5">
    <!-- Titre de la page avec icône -->
    <h2 class="text-center mb-4 text-primary">
        <i class="bi bi-truck" aria-hidden="true"></i>
        <span class="visually-hidden">Icône trajets</span> <!-- Accessibilité -->
        Gestion des trajets
    </h2>

    <!-- Conteneur pour la liste des trajets -->
    <div id="trajets-container" role="status" aria-live="polite">
        <div class="text-center text-muted">
            Chargement des trajets...
        </div>
    </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

/* ===============================
 * FONCTIONS UTILES POUR LES MESSAGES DANS LES MODALS
 * =============================== */

/**
 * Affiche un message dans une modal (success / danger / info)
 * @param {string} id - ID de l'élément alert dans la modal
 * @param {string} message - Texte du message
 * @param {string} type - Type de message Bootstrap (success, danger, info)
 */
function showModalMessage(id, message, type = 'danger') {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = `alert alert-${type}`;
    el.innerText = message;
    el.classList.remove('d-none');
}

/**
 * Cache le message d'une modal
 * @param {string} id - ID de l'élément alert dans la modal
 */
function clearModalMessage(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('d-none');
}

/* ===============================
 * CHARGEMENT DES TRAJETS (ADMIN)
 * =============================== */

/**
 * Charge les trajets via API JSON et affiche dans un tableau
 */
async function loadTrajets() {
    const container = document.getElementById('trajets-container');

    try {
        const response = await fetch(`${BASE_URL}/trajets/json`);
        const trajets = await response.json();

        // Si aucun trajet, afficher message informatif
        if (!trajets.length) {
            container.innerHTML =
                `<div class="alert alert-info text-center" role="alert">
                    Aucun trajet disponible.
                 </div>`;
            return;
        }

        // Construction du tableau HTML
        let html = `
        <table class="table trajet-table text-center align-middle">
            <caption class="visually-hidden">
                Liste des trajets disponibles avec dates, horaires, places et actions
            </caption>
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
            // Séparation date et heure pour un affichage clair
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
                    <!-- Bouton pour voir infos conducteur -->
                    <button class="btn btn-sm btn-primary"
                        aria-label="Voir le conducteur du trajet ${t.depart} → ${t.arrivee}"
                        data-bs-toggle="modal"
                        data-bs-target="#trajetModal${t.id}">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>

                    <!-- Bouton pour supprimer le trajet -->
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
                            <!-- Conteneur pour afficher message success / erreur -->
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
        // Affichage message d'erreur en cas de problème serveur
        console.error(e);
        container.innerHTML =
            `<div class="alert alert-danger text-center" role="alert">
                Erreur de chargement des trajets.
             </div>`;
    }
}

/* ===============================
 * SUPPRESSION D'UN TRAJET (ADMIN)
 * =============================== */

/**
 * Supprime un trajet et met à jour la liste
 * @param {number} id - ID du trajet
 */
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

        // Affichage message succès
        showModalMessage(messageId, result.message, 'success');

        // Masquer modal et recharger la liste après 0.8s
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
 * INITIALISATION AU CHARGEMENT DE LA PAGE
 * =============================== */
loadTrajets();
</script>
