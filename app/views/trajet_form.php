<?php require_once __DIR__ . '/templates/header.php'; ?>
<?php $user = $_SESSION['user'] ?? []; // Récupère les informations de l'utilisateur connecté ?>

<div class="container my-5">

    <!-- Titre de la page -->
    <h2 class="mb-4 text-primary-dark">Créer un trajet</h2>

    <!-- Formulaire de création de trajet -->
    <form id="trajet-form" class="card p-4 shadow-sm" novalidate>

        <!-- Informations personnelles du conducteur (readonly) -->
        <h5 class="mb-3">Informations du conducteur</h5>
        <div class="row mb-3">
            <div class="col">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" id="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly>
            </div>
            <div class="col">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" id="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly>
            </div>
        </div>

        <!-- Contact du conducteur -->
        <div class="row mb-3">
            <div class="col">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
            </div>
            <div class="col">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" id="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" readonly>
            </div>
        </div>

        <!-- Informations du trajet -->
        <h5 class="mb-3">Informations du trajet</h5>
        <div class="row mb-3">
            <!-- Sélection de l'agence de départ -->
            <div class="col">
                <label for="depart" class="form-label">Agence départ</label>
                <select id="depart" name="depart" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?= $agence['id'] ?>"><?= htmlspecialchars($agence['nom_agence']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sélection de l'agence d'arrivée -->
            <div class="col">
                <label for="arrivee" class="form-label">Agence arrivée</label>
                <select id="arrivee" name="arrivee" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?= $agence['id'] ?>"><?= htmlspecialchars($agence['nom_agence']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Dates et heures de départ/arrivée -->
        <div class="row mb-3">
            <div class="col">
                <label for="date_depart" class="form-label">Date départ</label>
                <input type="datetime-local" id="date_depart" name="date_depart" class="form-control" required>
            </div>
            <div class="col">
                <label for="date_arrivee" class="form-label">Date arrivée</label>
                <input type="datetime-local" id="date_arrivee" name="date_arrivee" class="form-control" required>
            </div>
        </div>

        <!-- Nombre de places totales et disponibles -->
        <div class="row mb-3">
            <div class="col">
                <label for="nb_places_totales" class="form-label">Nombre total de places</label>
                <input type="number" id="nb_places_totales" name="nb_places_totales" class="form-control" min="1" required>
            </div>
            <div class="col">
                <label for="nb_places_disponibles" class="form-label">Nombre de places disponibles</label>
                <input type="number" id="nb_places_disponibles" name="nb_places_disponibles" class="form-control" min="1" required>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="text-end">
            <a href="<?= BASE_URL ?>/" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer le trajet</button>
        </div>

    </form>

</div>

<!-- =========================
     MODAL MESSAGE
     Affiche les messages de succès ou d'erreur après soumission
========================= -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="messageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="messageModalBody" aria-live="polite"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * Gestion de la soumission du formulaire de création de trajet
 */
document.getElementById('trajet-form').addEventListener('submit', async e => {
    e.preventDefault(); // Empêche le rechargement de la page
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries()); // Transforme en objet JS

    // Réinitialisation des classes de validation
    ['depart','arrivee','date_depart','date_arrivee','nb_places_totales','nb_places_disponibles'].forEach(name => {
        form[name].classList.remove('is-invalid','is-valid');
    });

    // Validation simple côté client
    let valid = true;

    // Vérifie que départ et arrivée sont différents
    if (data.depart === data.arrivee) {
        form.depart.classList.add('is-invalid');
        form.arrivee.classList.add('is-invalid');
        valid = false;
    } else {
        form.depart.classList.add('is-valid');
        form.arrivee.classList.add('is-valid');
    }

    // Vérifie que la date d'arrivée est après la date de départ
    if (data.date_arrivee <= data.date_depart) {
        form.date_arrivee.classList.add('is-invalid');
        valid = false;
    } else {
        form.date_depart.classList.add('is-valid');
        form.date_arrivee.classList.add('is-valid');
    }

    // Vérifie que le nombre de places disponibles n'excède pas le total
    if (parseInt(data.nb_places_disponibles) > parseInt(data.nb_places_totales)) {
        form.nb_places_disponibles.classList.add('is-invalid');
        valid = false;
    } else {
        form.nb_places_totales.classList.add('is-valid');
        form.nb_places_disponibles.classList.add('is-valid');
    }

    // Si invalid, on stoppe la soumission
    if (!valid) return;

    try {
        // Envoi des données au serveur
        const resp = await fetch('<?= BASE_URL ?>/trajet/create', {
            method: 'POST',
            body: new URLSearchParams(data)
        });

        // Lecture de la réponse
        const text = await resp.text();
        let result;
        try { 
            result = JSON.parse(text); 
        } catch { 
            result = { success: false, message: 'Réponse serveur invalide' }; 
        }

        // Affiche le message dans le modal
        const modalEl = document.getElementById('messageModal');
        document.getElementById('messageModalBody').innerText = result.message || 'Message inconnu';
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Redirection après succès
        if (result.success) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                window.location.href = '<?= BASE_URL ?>/';
            }, { once: true });
        }

    } catch (err) {
        console.error(err);
        const modalEl = document.getElementById('messageModal');
        document.getElementById('messageModalBody').innerText = 'Erreur lors de la création du trajet';
        new bootstrap.Modal(modalEl).show();
    }
});
</script>
