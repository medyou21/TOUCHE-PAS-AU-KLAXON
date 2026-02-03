<?php
// --------------------------------------------------
// admin_dashboard.php (STATISTIQUES)
// --------------------------------------------------
require_once __DIR__ . '/templates/header.php';
?>

<div class="container my-5">

    <h2 class="text-center mb-5 text-primary">
        <i class="bi bi-speedometer2"></i> Tableau de bord Administrateur
    </h2>

    <div class="row g-4">

        <!-- UTILISATEURS -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center border-0">
                <div class="card-body">
                    <i class="bi bi-people fs-1 text-warning"></i>
                    <h5 class="mt-3">Utilisateurs</h5>
                    <h2 id="stat-users">–</h2>
                </div>
            </div>
        </div>

        <!-- AGENCES -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center border-0">
                <div class="card-body">
                    <i class="bi bi-building fs-1 text-success"></i>
                    <h5 class="mt-3">Agences</h5>
                    <h2 id="stat-agences">–</h2>
                </div>
            </div>
        </div>

        <!-- TRAJETS -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center border-0">
                <div class="card-body">
                    <i class="bi bi-truck fs-1 text-primary"></i>
                    <h5 class="mt-3">Trajets</h5>
                    <h2 id="stat-trajets">–</h2>
                </div>
            </div>
        </div>

        <!-- TRAJETS ACTIFS -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center border-0">
                <div class="card-body">
                    <i class="bi bi-calendar-check fs-1 text-danger"></i>
                    <h5 class="mt-3">Trajets actifs</h5>
                    <h2 id="stat-trajets-actifs">–</h2>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const BASE_URL = '<?= BASE_URL ?>';

    try {
        const res = await fetch(`${BASE_URL}/admin/stats/json`);
        const stats = await res.json();

        document.getElementById('stat-users').innerText          = stats.users ?? 0;
        document.getElementById('stat-agences').innerText        = stats.agences ?? 0;
        document.getElementById('stat-trajets').innerText        = stats.trajets ?? 0;
        document.getElementById('stat-trajets-actifs').innerText = stats.trajets_actifs ?? 0;

    } catch (e) {
        console.error(e);
    }
});
</script>


