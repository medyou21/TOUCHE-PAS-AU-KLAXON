<?php 
// Inclure le header commun (navigation, CSS, SEO, etc.)
require_once __DIR__ . '/templates/header.php'; 
?>

<div class="container my-5">

    <!-- Titre de la page avec icône -->
    <h2 class="text-center mb-4 text-primary">
        <i class="bi bi-people" aria-hidden="true"></i>
        <span class="visually-hidden">Icône utilisateurs</span> <!-- Accessibilité -->
        Gestion des utilisateurs
    </h2>

    <!-- Conteneur pour la liste des utilisateurs -->
    <div id="users-container" 
         class="text-center text-muted" 
         role="status" 
         aria-live="polite">
        <!-- Message initial pendant le chargement -->
        Chargement...
    </div>
</div>

<script>
// Base URL du site (définie dans config PHP)
const BASE_URL = "<?= BASE_URL ?>";

/**
 * Fonction pour charger la liste des utilisateurs via AJAX
 */
async function loadUsers() {
    const container = document.getElementById('users-container');

    try {
        // Requête GET vers l'API JSON des utilisateurs
        const response = await fetch(`${BASE_URL}/admin/users/json`);
        const users = await response.json();

        // Si aucun utilisateur, afficher un message informatif
        if (!users.length) {
            container.innerHTML =
                `<div class="alert alert-info" role="alert">Aucun utilisateur</div>`;
            return;
        }

        // Construction de la table HTML
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

        // Parcours des utilisateurs
        users.forEach(u => {
            // Définir un badge coloré selon le rôle (admin = rouge, autre = gris)
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
        container.innerHTML = html; // Injecter la table dans le DOM

    } catch (error) {
        // Afficher un message d'erreur si la requête échoue
        console.error('Erreur chargement utilisateurs:', error);
        container.innerHTML =
            `<div class="alert alert-danger" role="alert">Erreur chargement</div>`;
    }
}

// Appel initial pour afficher les utilisateurs au chargement de la page
loadUsers();
</script>
