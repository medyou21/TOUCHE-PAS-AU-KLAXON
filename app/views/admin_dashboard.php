<?php
require_once __DIR__ . '/templates/header.php';
?>

<div class="container my-5">

    <h2 class="text-center mb-5 text-primary">
        <i class="bi bi-speedometer2" aria-hidden="true"></i> Tableau de bord Administrateur
    </h2>

    <div id="dashboardLoading" class="text-center mb-4">
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        <span class="ms-2">Chargement des statistiques...</span>
    </div>

    <div id="dashboardContent" class="d-none">

        <div class="row g-4 mb-5">

            <!-- UTILISATEURS -->
            <div class="col-md-3">
                <div class="card shadow-sm text-center border-0" role="region" aria-labelledby="statUsersLabel">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-warning" aria-hidden="true"></i>
                        <h5 id="statUsersLabel" class="mt-3">Utilisateurs</h5>
                        <h2 class="stat-counter" id="stat-users">0</h2>
                    </div>
                </div>
            </div>

            <!-- AGENCES -->
            <div class="col-md-3">
                <div class="card shadow-sm text-center border-0" role="region" aria-labelledby="statAgencesLabel">
                    <div class="card-body">
                        <i class="bi bi-building fs-1 text-success" aria-hidden="true"></i>
                        <h5 id="statAgencesLabel" class="mt-3">Agences</h5>
                        <h2 class="stat-counter" id="stat-agences">0</h2>
                    </div>
                </div>
            </div>

            <!-- TRAJETS -->
            <div class="col-md-3">
                <div class="card shadow-sm text-center border-0" role="region" aria-labelledby="statTrajetsLabel">
                    <div class="card-body">
                        <i class="bi bi-truck fs-1 text-primary" aria-hidden="true"></i>
                        <h5 id="statTrajetsLabel" class="mt-3">Trajets</h5>
                        <h2 class="stat-counter" id="stat-trajets">0</h2>
                    </div>
                </div>
            </div>

            <!-- TRAJETS ACTIFS -->
            <div class="col-md-3">
                <div class="card shadow-sm text-center border-0" role="region" aria-labelledby="statTrajetsActifsLabel">
                    <div class="card-body">
                        <i class="bi bi-calendar-check fs-1 text-danger" aria-hidden="true"></i>
                        <h5 id="statTrajetsActifsLabel" class="mt-3">Trajets actifs</h5>
                        <h2 class="stat-counter" id="stat-trajets-actifs">0</h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- Graphiques -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <canvas id="trajetsChart" aria-label="Graphique des trajets par jour" role="img"></canvas>
            </div>
            <div class="col-md-6 mb-4">
                <canvas id="usersChart" aria-label="Graphique des utilisateurs par rôle" role="img"></canvas>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const BASE_URL = '<?= BASE_URL ?>';

async function fetchStats() {
    try {
        const res = await fetch(`${BASE_URL}/admin/stats/json`);
        if (!res.ok) throw new Error('Statistiques non disponibles');
        return await res.json();
    } catch(e) {
        console.error('Erreur stats:', e);
        alert('Impossible de récupérer les statistiques. Vérifiez le serveur.');
        return {};
    }
}

// Animation compteur
function animateCounter(el, target) {
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 50));
    const interval = setInterval(() => {
        current += step;
        if(current >= target) {
            el.innerText = target;
            clearInterval(interval);
        } else {
            el.innerText = current;
        }
    }, 20);
}

// Initialisation dashboard
async function updateDashboard() {
    const stats = await fetchStats();

    // Masquer spinner, afficher contenu
    document.getElementById('dashboardLoading').classList.add('d-none');
    document.getElementById('dashboardContent').classList.remove('d-none');

    // Compteurs animés
    animateCounter(document.getElementById('stat-users'), stats.users ?? 0);
    animateCounter(document.getElementById('stat-agences'), stats.agences ?? 0);
    animateCounter(document.getElementById('stat-trajets'), stats.trajets ?? 0);
    animateCounter(document.getElementById('stat-trajets-actifs'), stats.trajets_actifs ?? 0);

    // Graphiques
    const trajetsChartCtx = document.getElementById('trajetsChart').getContext('2d');
    const usersChartCtx = document.getElementById('usersChart').getContext('2d');

    // Détruire anciens charts si existent
    if(window.trajetsChartInstance) window.trajetsChartInstance.destroy();
    if(window.usersChartInstance) window.usersChartInstance.destroy();

    // Trajets par jour
    window.trajetsChartInstance = new Chart(trajetsChartCtx, {
        type: 'bar',
        data: {
            labels: stats.trajets_jour?.labels || [],
            datasets: [{
                label: 'Trajets par jour',
                data: stats.trajets_jour?.data || [],
                backgroundColor: 'rgba(13, 110, 253, 0.7)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // Utilisateurs par rôle
    window.usersChartInstance = new Chart(usersChartCtx, {
        type: 'doughnut',
        data: {
            labels: stats.users_role?.labels || [],
            datasets: [{
                label: 'Utilisateurs par rôle',
                data: stats.users_role?.data || [],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(25, 135, 84, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// Premier chargement + refresh toutes les 30s
updateDashboard();
setInterval(updateDashboard, 30000);
</script>
