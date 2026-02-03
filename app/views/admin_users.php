<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5">

    <h2 class="text-center mb-4 text-primary">
        <i class="bi bi-people" aria-hidden="true"></i>
        <span class="visually-hidden">Icône utilisateurs</span>
        Gestion des utilisateurs
    </h2>

    <div id="users-container" class="text-center text-muted" role="status" aria-live="polite">
        Chargement...
    </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

async function loadUsers() {
    const container = document.getElementById('users-container');

    try {
        const response = await fetch(`${BASE_URL}/admin/users/json`);
        const users = await response.json();

        if (!users.length) {
            container.innerHTML =
                `<div class="alert alert-info" role="alert">Aucun utilisateur</div>`;
            return;
        }

        let html = `
        <table class="table trajet-table text-center align-middle">
            <caption class="visually-hidden">
                Liste des utilisateurs du système avec nom, email et rôle
            </caption>
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Email</th>
                    <th scope="col">Rôle</th>
                </tr>
            </thead>
            <tbody>
        `;

        users.forEach(u => {
            // Choix de couleur avec contraste suffisant
            const badgeClass = u.role === 'admin' ? 'bg-danger text-white' : 'bg-secondary text-white';
            html += `
            <tr>
                <td>${u.prenom} ${u.nom}</td>
                <td>${u.email}</td>
                <td>
                    <span class="badge ${badgeClass}">
                        ${u.role}
                    </span>
                </td>
            </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;

    } catch {
        container.innerHTML =
            `<div class="alert alert-danger" role="alert">Erreur chargement</div>`;
    }
}

loadUsers();
</script>
