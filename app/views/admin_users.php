<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-4">
        <i class="bi bi-people"></i> Gestion des utilisateurs
    </h2>

    <div id="users-container" class="text-center text-muted">
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
                `<div class="alert alert-info">Aucun utilisateur</div>`;
            return;
        }

        let html = `
        <table class="table trajet-table text-center align-middle">
            <thead>
                <tr>
                   
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                </tr>
            </thead>
            <tbody>
        `;

        users.forEach(u => {
            html += `
            <tr>
               
                <td>${u.prenom} ${u.nom}</td>
                <td>${u.email}</td>
                <td>
                    <span class="badge ${u.role === 'admin' ? 'bg-danger' : 'bg-secondary'}">
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
            `<div class="alert alert-danger">Erreur chargement</div>`;
    }
}

loadUsers();
</script>


