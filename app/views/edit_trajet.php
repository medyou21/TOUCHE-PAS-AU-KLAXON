<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php 
// Récupération de l'utilisateur connecté
$user = $_SESSION['user'] ?? [];

// Le trajet à modifier, supposé passé depuis le contrôleur
$trajet = $trajet ?? null;

// Vérification que le trajet existe et que l'utilisateur est bien le conducteur
if (!$trajet || $trajet['conducteur_id'] != $user['id']): ?>
<div class="alert alert-danger text-center mt-5" role="alert">
    Vous n'êtes pas autorisé à modifier ce trajet.
</div>
<?php return; endif; ?>

<div class="container my-5">
    <h2 class="mb-4 text-primary-dark">Modifier le trajet</h2>

    <!-- Formulaire d'édition du trajet -->
    <form id="edit-trajet-form" class="card p-4 shadow-sm" aria-describedby="editInfo">

        <!-- Informations du conducteur -->
        <h5 class="mb-3">Informations du conducteur</h5>
        <div class="row mb-3">
            <div class="col">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" id="prenom" class="form-control" 
                       value="<?= htmlspecialchars($user['prenom']) ?>" readonly>
            </div>
            <div class="col">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" id="nom" class="form-control" 
                       value="<?= htmlspecialchars($user['nom']) ?>" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" class="form-control" 
                       value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="col">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" id="telephone" class="form-control" 
                       value="<?= htmlspecialchars($user['telephone']) ?>" readonly>
            </div>
        </div>

        <!-- Informations du trajet -->
        <h5 class="mb-3">Informations du trajet</h5>

        <!-- Sélection des agences de départ et arrivée -->
        <div class="row mb-3">
            <div class="col">
                <label for="depart" class="form-label">Agence départ</label>
                <select id="depart" name="depart" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?= $agence['id'] ?>" 
                            <?= $agence['id']==$trajet['agence_depart_id']?'selected':'' ?>>
                            <?= htmlspecialchars($agence['nom_agence']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label for="arrivee" class="form-label">Agence arrivée</label>
                <select id="arrivee" name="arrivee" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?= $agence['id'] ?>" 
                            <?= $agence['id']==$trajet['agence_arrivee_id']?'selected':'' ?>>
                            <?= htmlspecialchars($agence['nom_agence']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Dates et heures du trajet -->
        <div class="row mb-3">
            <div class="col">
                <label for="date_depart" class="form-label">Date départ</label>
                <input type="datetime-local" id="date_depart" name="date_depart" class="form-control" 
                       value="<?= date('Y-m-d\TH:i', strtotime($trajet['date_depart'])) ?>" required>
            </div>
            <div class="col">
                <label for="date_arrivee" class="form-label">Date arrivée</label>
                <input type="datetime-local" id="date_arrivee" name="date_arrivee" class="form-control" 
                       value="<?= date('Y-m-d\TH:i', strtotime($trajet['date_arrivee'])) ?>" required>
            </div>
        </div>

        <!-- Nombre de places totales et disponibles -->
        <div class="row mb-3">
            <div class="col">
                <label for="nb_places_totales" class="form-label">Nombre total de places</label>
                <input type="number" id="nb_places_totales" name="nb_places_totales" class="form-control" min="1" 
                       value="<?= $trajet['nb_places_totales'] ?>" required>
            </div>
            <div class="col">
                <label for="nb_places_disponibles" class="form-label">Nombre de places disponibles</label>
                <input type="number" id="nb_places_disponibles" name="nb_places_disponibles" class="form-control" 
                       min="1" max="<?= $trajet['nb_places_totales'] ?>" 
                       value="<?= $trajet['nb_places_disponibles'] ?>" required
                       aria-describedby="placesInfo">
                <div id="placesInfo" class="form-text">
                    Ne peut pas dépasser le nombre total de places
                </div>
            </div>
        </div>

        <!-- Boutons actions -->
        <div class="text-end">
            <a href="<?= BASE_URL ?>/" class="btn btn-secondary" aria-label="Annuler modification">Annuler</a>
            <button type="submit" class="btn btn-primary" aria-label="Modifier le trajet">Modifier le trajet</button>
        </div>
    </form>
</div>

<!-- Modal pour messages de retour -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="messageModalLabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="messageModalBody" role="alert"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Fermer le message">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('edit-trajet-form').addEventListener('submit', async e => {
    e.preventDefault(); // Empêche le submit classique
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries()); // Récupère les champs du formulaire

    // Réinitialisation des classes de validation
    ['depart','arrivee','date_depart','date_arrivee','nb_places_totales','nb_places_disponibles'].forEach(name=>{
        form[name].classList.remove('is-invalid','is-valid');
    });

    let valid = true;

    // Validation départ/arrivée
    if(data.depart === data.arrivee){
        form.depart.classList.add('is-invalid'); 
        form.arrivee.classList.add('is-invalid'); 
        valid=false;
    } else {
        form.depart.classList.add('is-valid'); 
        form.arrivee.classList.add('is-valid');
    }

    // Validation dates
    if(data.date_arrivee <= data.date_depart){
        form.date_arrivee.classList.add('is-invalid'); 
        valid=false;
    } else {
        form.date_depart.classList.add('is-valid'); 
        form.date_arrivee.classList.add('is-valid');
    }

    // Validation places disponibles
    if(parseInt(data.nb_places_disponibles) > parseInt(data.nb_places_totales)){
        form.nb_places_disponibles.classList.add('is-invalid'); 
        valid=false;
    } else {
        form.nb_places_totales.classList.add('is-valid'); 
        form.nb_places_disponibles.classList.add('is-valid');
    }

    if(!valid) return; // Stop si erreur

    try {
        // Envoi des données via fetch POST
        const resp = await fetch(window.location.href, {
            method:'POST',
            body:new URLSearchParams(data)
        });

        const result = await resp.json();

        // Affichage message modal
        const modalEl = document.getElementById('messageModal');
        document.getElementById('messageModalBody').innerText = result.message || 'Message inconnu';
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Si succès, redirection après fermeture modal
        if(result.success){
            modalEl.addEventListener('hidden.bs.modal', () => {
                window.location.href = '<?= BASE_URL ?>/';
            }, {once:true});
        }
    } catch(err){
        console.error(err);
        const modalEl = document.getElementById('messageModal');
        document.getElementById('messageModalBody').innerText='Erreur serveur lors de la modification';
        new bootstrap.Modal(modalEl).show();
    }
});
</script>
