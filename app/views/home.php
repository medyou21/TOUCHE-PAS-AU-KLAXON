<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php
$user = $_SESSION['user'] ?? null;
?>

<div class="container my-5">

    <h2 class="mb-4 text-primary text-center">
        Liste des trajets disponibles
    </h2>

    <div id="trajets-container" data-user='<?= json_encode($user) ?>'></div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('trajets-container');
    const user = JSON.parse(container.dataset.user || 'null');
    const userLoggedIn = user !== null;

    async function loadTrajets() {
        try {
            const response = await fetch('<?= BASE_URL ?>/trajets/json');
            const trajets = await response.json();

            if (!trajets.length) {
                container.innerHTML = `<div class="alert alert-info text-center">Aucun trajet disponible.</div>`;
                return;
            }

            let html = `
            <table class="table trajet-table text-center align-middle">
                <thead>
                    <tr>
                        <th>Agence départ</th>
                        <th>Date départ</th>
                        <th>Heure départ</th>
                        <th>Agence arrivée</th>
                        <th>Date arrivée</th>
                        <th>Heure arrivée</th>
                        <th>Places disponibles</th>
                        ${userLoggedIn ? '<th>Actions</th>' : ''}
                    </tr>
                </thead>
                <tbody>
            `;

            let modals = '';

            trajets.forEach(t => {
                const prenom = t.prenom ?? '';
                const nom = t.nom ?? '';
                const telephone = t.telephone ?? '';
                const email = t.email ?? '';
                const nbPlacesDisponibles = t.nb_places_disponibles ?? 0;
                const nbPlacesTotales = t.nb_places_totales ?? 0;

                // Séparer date et heure (robuste pour MySQL)
                let dateDepartStr = '', heureDepartStr = '', dateArriveeStr = '', heureArriveeStr = '';
                if (t.date_depart) {
                    const [dateD, timeD] = t.date_depart.split(' ');
                    dateDepartStr = dateD.split('-').reverse().join('/');
                    heureDepartStr = timeD.slice(0,5);
                }
                if (t.date_arrivee) {
                    const [dateA, timeA] = t.date_arrivee.split(' ');
                    dateArriveeStr = dateA.split('-').reverse().join('/');
                    heureArriveeStr = timeA.slice(0,5);
                }

                html += `
                <tr class="trajet-row">
                    <td>${t.depart ?? ''}</td>
                    <td>${dateDepartStr}</td>
                    <td>${heureDepartStr}</td>
                    <td>${t.arrivee ?? ''}</td>
                    <td>${dateArriveeStr}</td>
                    <td>${heureArriveeStr}</td>
                    <td class="places-dispo">${nbPlacesDisponibles}</td>
                `;

                if (userLoggedIn) {
                    html += `<td>
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#trajetModal${t.id}">
                            <i class="bi bi-eye"></i>
                        </button>`;
                    if (t.conducteur_id == user.id) {
                        html += `
                            <a href="<?= BASE_URL ?>/trajet/edit/${t.id}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <a href="<?= BASE_URL ?>/trajet/delete/${t.id}" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce trajet ?');"><i class="bi bi-trash"></i></a>`;
                    }
                    html += `</td>`;
                }

                html += `</tr>`;

                if (userLoggedIn) {
                    modals += `
                    <div class="modal fade" id="trajetModal${t.id}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Informations du conducteur</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <p><strong>Nom :</strong> ${prenom} ${nom}</p>
                                    <p><strong>Téléphone :</strong> ${telephone}</p>
                                    <p><strong>Email :</strong> ${email}</p>
                                    <p><strong>Places totales :</strong> ${nbPlacesTotales}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }
            });

            html += `</tbody></table>`;
            container.innerHTML = html + modals;

        } catch (error) {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger text-center">Erreur lors du chargement des trajets.</div>`;
        }
    }

    loadTrajets();
});
</script>
