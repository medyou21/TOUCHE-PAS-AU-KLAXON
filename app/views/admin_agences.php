<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5 text-primary">
    <!-- Titre de la page -->
    <h2 class="text-center mb-4">
        <i class="bi bi-building"></i> Gestion des agences
    </h2>

    <!-- Bouton pour ajouter une nouvelle agence -->
    <div class="text-end mb-3">
        <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#agenceModal"
                onclick="openCreateAgence()">
            <i class="bi bi-plus-circle"></i> Nouvelle agence
        </button>
    </div>

    <!-- Conteneur où la liste des agences sera injectée dynamiquement -->
    <div id="agences-container" class="text-center text-muted">
        Chargement...
    </div>
</div>

<!-- =========================
     MODAL CREATE / UPDATE
========================= -->
<div class="modal fade" id="agenceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Header modal avec titre dynamique -->
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="agenceModalTitle">
                    Ajouter une agence
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Corps modal -->
            <div class="modal-body">
                <!-- Message d'alerte (success / erreur) -->
                <div id="agence-modal-message" class="alert d-none" role="alert"></div>

                <!-- ID caché pour modification -->
                <input type="hidden" id="agence-id">

                <!-- Nom de l'agence -->
                <div class="mb-3">
                    <label class="form-label">Nom de l’agence</label>
                    <input type="text"
                           id="agence-name"
                           class="form-control"
                           placeholder="Ex : Nantes">
                </div>
            </div>

            <!-- Footer modal -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button class="btn btn-success" onclick="saveAgence()">
                    Enregistrer
                </button>
            </div>

        </div>
    </div>
</div>

<!-- =========================
     MODAL DELETE
========================= -->
<div class="modal fade" id="deleteAgenceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Message d'alerte (success / erreur) -->
                <div id="delete-agence-message" class="alert d-none" role="alert"></div>

                <p>Voulez-vous vraiment supprimer cette agence ?</p>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="delete-agence-id">

                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button class="btn btn-danger" onclick="confirmDeleteAgence()">
                    Supprimer
                </button>
            </div>

        </div>
    </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

/* =========================
 * FONCTIONS UTILITAIRES POUR LES MESSAGES
 * ========================= */

/**
 * Affiche un message dans une modal (success / danger / info)
 * @param {string} id - ID de l'élément alert dans la modal
 * @param {string} message - Texte du message
 * @param {string} type - Type Bootstrap (success, danger, info)
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
 * @param {string} id - ID de l'élément alert
 */
function clearModalMessage(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('d-none');
}

/* =========================
 * CHARGEMENT DES AGENCES
 * ========================= */

/**
 * Charge les agences depuis l'API JSON et affiche le tableau
 */
async function loadAgences() {
    const container = document.getElementById('agences-container');

    try {
        const response = await fetch(`${BASE_URL}/admin/agences/json`);
        const agences = await response.json();

        // Aucun résultat
        if (!agences.length) {
            container.innerHTML =
                `<div class="alert alert-info">Aucune agence</div>`;
            return;
        }

        // Construction du tableau
        let html = `
        <table class="table trajet-table text-center align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        `;

        agences.forEach(a => {
            html += `
            <tr>
                <td>${a.id}</td>
                <td>${a.nom_agence}</td>
                <td>
                    <!-- Modifier agence -->
                    <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#agenceModal"
                        onclick="openEditAgence(${a.id}, '${a.nom_agence.replace(/'/g, "\\'")}')">
                        <i class="bi bi-pencil"></i>
                    </button>

                    <!-- Supprimer agence -->
                    <button class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteAgenceModal"
                        onclick="openDeleteAgence(${a.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;

    } catch (e) {
        container.innerHTML =
            `<div class="alert alert-danger">Erreur chargement</div>`;
        console.error(e);
    }
}

/* =========================
 * CREATE / EDIT AGENCE
 * ========================= */

/**
 * Ouvre la modal pour créer une nouvelle agence
 */
function openCreateAgence() {
    clearModalMessage('agence-modal-message');
    document.getElementById('agenceModalTitle').innerText = 'Ajouter une agence';
    document.getElementById('agence-id').value = '';
    document.getElementById('agence-name').value = '';
}

/**
 * Ouvre la modal pour modifier une agence existante
 * @param {number} id - ID de l'agence
 * @param {string} name - Nom de l'agence
 */
function openEditAgence(id, name) {
    clearModalMessage('agence-modal-message');
    document.getElementById('agenceModalTitle').innerText = 'Modifier l’agence';
    document.getElementById('agence-id').value = id;
    document.getElementById('agence-name').value = name;
}

/* =========================
 * SAVE AGENCE (CREATE / UPDATE)
 * ========================= */
async function saveAgence() {
    const id   = document.getElementById('agence-id').value;
    const name = document.getElementById('agence-name').value.trim();

    clearModalMessage('agence-modal-message');

    if (!name) {
        showModalMessage('agence-modal-message', 'Nom obligatoire');
        return;
    }

    const url = id
        ? `${BASE_URL}/admin/update-agence`
        : `${BASE_URL}/admin/create-agence`;

    const formData = new FormData();
    if (id) formData.append('id', id);
    formData.append('name', name);

    const resp = await fetch(url, { method: 'POST', body: formData });
    const result = await resp.json();

    if (!result.success) {
        showModalMessage('agence-modal-message', result.message, 'danger');
        return;
    }

    showModalMessage('agence-modal-message', result.message, 'success');

    // Ferme la modal et recharge la liste
    setTimeout(() => {
        bootstrap.Modal.getInstance(
            document.getElementById('agenceModal')
        ).hide();
        loadAgences();
    }, 800);
}

/* =========================
 * DELETE AGENCE
 * ========================= */

/**
 * Ouvre la modal de suppression avec ID
 * @param {number} id - ID de l'agence
 */
function openDeleteAgence(id) {
    clearModalMessage('delete-agence-message');
    document.getElementById('delete-agence-id').value = id;
}

/**
 * Confirme et supprime l'agence via API
 */
async function confirmDeleteAgence() {
    const id = document.getElementById('delete-agence-id').value;
    clearModalMessage('delete-agence-message');

    const resp = await fetch(`${BASE_URL}/admin/delete-agence/${id}`, {
        method: 'POST'
    });
    const result = await resp.json();

    if (!result.success) {
        showModalMessage('delete-agence-message', result.message, 'danger');
        return;
    }

    showModalMessage('delete-agence-message', result.message, 'success');

    setTimeout(() => {
        bootstrap.Modal.getInstance(
            document.getElementById('deleteAgenceModal')
        ).hide();
        loadAgences();
    }, 800);
}

// Chargement initial des agences
loadAgences();
</script>
