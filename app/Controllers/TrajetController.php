<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Trajet;
use App\Models\Reservation; // <-- obligatoire !
use App\Core\Database;
use Throwable;


class TrajetController extends Controller
{
    private Trajet $trajetModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->trajetModel = new Trajet();
    }

    // Page d'accueil
    public function home(): void
    {
        $user = $_SESSION['user'] ?? null;
        $this->render('home', ['user' => $user]);
    }

    // JSON pour AJAX
    public function listJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode($this->trajetModel->getAvailableTrajets());
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Erreur serveur : '.$e->getMessage()]);
        }
    }

    // Formulaire création
    public function createForm(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $this->render('trajet_form', [
            'user' => $_SESSION['user'],
            'agences' => $this->trajetModel->getAgences()
        ]);
    }

    // Création (POST)
    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_SESSION['user']['id'])) {
                echo json_encode(['success'=>false,'message'=>'Connexion requise']); exit;
            }

            $required = ['depart','arrivee','date_depart','date_arrivee','nb_places_totales'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success'=>false,'message'=>"Champ manquant : $field"]); exit;
                }
            }

            $data = [
                'depart' => (int) $_POST['depart'],
                'arrivee' => (int) $_POST['arrivee'],
                'date_depart' => $_POST['date_depart'],
                'date_arrivee' => $_POST['date_arrivee'],
                'nb_places_totales' => (int) $_POST['nb_places_totales'],
                'conducteur_id' => (int) $_SESSION['user']['id']
            ];

            if ($data['depart'] === $data['arrivee']) {
                echo json_encode(['success'=>false,'message'=>'Les agences doivent être différentes']); exit;
            }
            if ($data['date_arrivee'] <= $data['date_depart']) {
                echo json_encode(['success'=>false,'message'=>'La date d’arrivée doit être après le départ']); exit;
            }

            $success = $this->trajetModel->create($data);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Trajet créé avec succès' : 'Erreur lors de la création'
            ]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Erreur serveur : '.$e->getMessage()]); exit;
        }
    }

    // =========================
    // Edition du trajet (GET + POST)
    // =========================
   public function edit(int $id): void
{
    if (!isset($_SESSION['user']['id'])) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $trajet = $this->trajetModel->getById($id);

    if (!$trajet || $trajet['conducteur_id'] != $userId) {
        echo "Trajet introuvable ou accès refusé";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST AJAX
        header('Content-Type: application/json; charset=utf-8');

        $required = ['depart','arrivee','date_depart','date_arrivee','nb_places_totales','nb_places_disponibles'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                echo json_encode(['success'=>false,'message'=>"Champ manquant : $field"]);
                exit;
            }
        }

        $data = [
            'depart' => (int) $_POST['depart'],
            'arrivee' => (int) $_POST['arrivee'],
            'date_depart' => $_POST['date_depart'],
            'date_arrivee' => $_POST['date_arrivee'],
            'nb_places_totales' => (int) $_POST['nb_places_totales'],
            'nb_places_disponibles' => (int) $_POST['nb_places_disponibles']
        ];

        if ($data['depart'] === $data['arrivee']) {
            echo json_encode(['success'=>false,'message'=>'Les agences doivent être différentes']);
            exit;
        }
        if ($data['date_arrivee'] <= $data['date_depart']) {
            echo json_encode(['success'=>false,'message'=>'La date d’arrivée doit être après le départ']);
            exit;
        }

        try {
            $success = $this->trajetModel->update($id, $data);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Trajet modifié avec succès' : 'Erreur lors de la modification'
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Erreur serveur']);
            exit;
        }
    }

    // GET → afficher le formulaire
    $this->render('edit_trajet', [
        'user' => $_SESSION['user'],
        'trajet' => $trajet,
        'agences' => $this->trajetModel->getAgences()
    ]);
}


    // =========================
    // Suppression
    // =========================
    public function delete(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_SESSION['user']['id'])) {
                echo json_encode(['success'=>false,'message'=>'Connexion requise']); exit;
            }

            $trajet = $this->trajetModel->getById($id);
            if (!$trajet) {
                echo json_encode(['success'=>false,'message'=>'Trajet introuvable']); exit;
            }

            if ($trajet['conducteur_id'] != $_SESSION['user']['id']) {
                echo json_encode(['success'=>false,'message'=>'Vous n’êtes pas autorisé à supprimer ce trajet']); exit;
            }

            $success = $this->trajetModel->delete($id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Trajet supprimé avec succès' : 'Erreur lors de la suppression'
            ]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Erreur serveur']); exit;
        }
    }

    // =========================
    // Réservations
    // =========================
    public function reserveForm(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $trajet = $this->trajetModel->getByIdWithAgences($id);
        $message = null;

        if (!$trajet || $trajet['nb_places_disponibles'] <= 0) {
            $message = "Trajet introuvable ou complet";
        }

        $reservationModel = new Reservation();
        if ($reservationModel->hasUserReserved($id, $_SESSION['user']['id'])) {
            $message = "Vous avez déjà réservé ce trajet.";
        }

        $this->render('reservation_form', [
            'user' => $_SESSION['user'],
            'trajet' => $trajet,
            'message' => $message
        ]);
    }

    public function reserve(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success'=>false,'message'=>'Connexion requise']); return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $nbPlaces = (int)($_POST['nb_places'] ?? 0);

        if ($nbPlaces < 1) {
            echo json_encode(['success'=>false,'message'=>'Nombre de places invalide']); return;
        }

        try {
            $reservationModel = new Reservation();
            $success = $reservationModel->create($id, $userId, $nbPlaces);

            if (!$success) {
                echo json_encode(['success'=>false,'message'=>'Réservation impossible (déjà réservée ou places insuffisantes)']);
                return;
            }

            echo json_encode(['success'=>true,'message'=>"Réservation confirmée pour $nbPlaces place(s)"]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Erreur serveur']);
        }
    }

    public function myReservations(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $reservationModel = new Reservation();
        $reservations = $reservationModel->getByUserId($_SESSION['user']['id']);

        $this->render('my_reservations', ['reservations' => $reservations]);
    }

    public function cancelReservation(int $id): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $reservationModel = new Reservation();
        $reservation = $reservationModel->getById($id);

        if (!$reservation || $reservation['utilisateur_id'] != $_SESSION['user']['id']) {
            header('Location: ' . BASE_URL . '/reservation/mine'); exit;
        }

        $reservationModel->delete($id);
        header('Location: ' . BASE_URL . '/reservation/mine'); exit;
    }

    public function updateReservation(int $id): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $newNb = (int)($_POST['nb_places'] ?? 0);
        $reservationModel = new Reservation();
        $reservationModel->updatePlaces($id, $_SESSION['user']['id'], $newNb);

        header('Location: ' . BASE_URL . '/reservation/mine'); exit;
    }
}
