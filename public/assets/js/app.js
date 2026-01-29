document.addEventListener('DOMContentLoaded', () => {

    // Utilisateur connecté
    window.user = <?= json_encode($user ?? null) ?>;
    window.userLoggedIn = !!window.user;

    // ---------------------------------
    // Fonction pour charger les trajets
    // ---------------------------------
    window.loadTrajets = async function() {
        const container = document.getElementById('trajets-container');

        try {
            const res = await fetch('<?= BASE_URL ?>/trajet/listJson');
            const trajets = await res.json();

            if (!trajets.length) {
                container.innerHTML = '<div class="alert alert-info text-center">Aucun trajet disponible.</div>';
                return;
            }

            let html = `
                <table class="table table-striped text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Départ</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Destination</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Places</th>
                            ${window.userLoggedIn ? '<th>Actions</th>' : ''}
                        </tr>
                    </thead>
                    <tbody>
            `;

            let modals = '';

            trajets.forEach(t => {

                // Séparer date et heure
                let dateDepart = '', heureDepart = '', dateArrivee = '', heureArrivee = '';
                if (t.date_depart) {
                    const [d, h] = t.date_depart.split(' ');
                    dateDepart = d.split('-').reverse().join('/');
                    heureDepart = h.slice(0,5);
                }
                if (t.date_arrivee) {
                    const [d, h] = t.date_arrivee.split(' ');
                    dateArrivee = d.split('-').reverse().join('/');
                    heureArrivee = h.slice(0,5);
                }

                html += `
                    <tr>
                        <td>${t.depart}</td>
                        <td>${dateDepart}</td>
                        <td>${heureDepart}</td>
                        <td>${t.arrivee}</td>
                        <td>${dateArrivee}</td>
                        <td>${heureArrivee}</td>
                        <td>${t.nb_places_disponibles} / ${t.nb_places_totales}</td>
                `;

                if (window.userLoggedIn) {
                    html += `<td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#trajetModal${t.id}">
                            <i class="bi bi-eye"></i>
                        </button>
                    `;

                    if (t.conducteur_id == window.user.id) {
                        html += `
                            <a href="<?= BASE_URL ?>/trajet/edit/${t.id}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="deleteTrajet(${t.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }

                    html += `</td>`;
                }

                html += `</tr>`;

                // Modal conducteur
                if (window.userLoggedIn) {
                    modals += `
                        <div class="modal fade" id="trajetModal${t.id}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Infos conducteur</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Nom :</strong> ${t.prenom} ${t.nom}</p>
                                        <p><strong>Téléphone :</strong> ${t.telephone}</p>
                                        <p><strong>Email :</strong> ${t.email}</p>
                                        <p><strong>Places totales :</strong> ${t.nb_places_totales}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

            });

            html += `</tbody></table>`;
            container.innerHTML = html + modals;

        } catch (err) {
            console.error(err);
            container.innerHTML = '<div class="alert alert-danger text-center">Erreur lors du chargement des trajets.</div>';
        }
    };

    // ---------------------------------
    // Formulaire création trajet (AJAX)
    // ---------------------------------
    const form = document.getElementById('trajetForm');
    if (form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data = new FormData(form);

            try {
                const res = await fetch('<?= BASE_URL ?>/trajet/create', {
                    method: 'POST',
                    body: data
                });
                const result = await res.json();
                alert(result.message);
                if (result.success) {
                    form.reset();
                    window.loadTrajets();
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // ---------------------------------
    // Supprimer un trajet (AJAX)
    // ---------------------------------
    window.deleteTrajet = async function(id) {
        if (!confirm('Supprimer ce trajet ?')) return;
        try {
            const res = await fetch(`<?= BASE_URL ?>/trajet/delete/${id}`, { method: 'POST' });
            const result = await res.json();
            alert(result.message);
            if (result.success) {
                window.loadTrajets();
            }
        } catch (err) {
            console.error(err);
        }
    };

    // Chargement initial
    window.loadTrajets();
});
