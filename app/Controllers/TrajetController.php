<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Trajet;
use App\Models\Reservation; // Gestion des réservations
use App\Core\Database;
use Throwable;

/**
 * ----------------------------------------------------
 * Contrôleur Trajet
 * Gère :
 *  - l'affichage des trajets
 *  - la création / modification / suppression
 *  - les réservations
 *  - les trajets de l'utilisateur
 * ----------------------------------------------------
 */
class TrajetController extends Controller
{
    /** @var Trajet */
    private Trajet $trajetModel;

    /**
     * Constructeur
     * - Démarre la session si nécessaire
     * - Initialise le modèle Trajet
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->trajetModel = new Trajet();
    }

    /* =====================================================
     * PAGE D'ACCUEIL
     * ===================================================== */

    /**
     * Page d'accueil
     * Affiche la liste des trajets disponibles
     */
    public function home(): void
    {
        $user = $_SESSION['user'] ?? null;
        $this->render('home', ['user' => $user]);
    }

    /**
     * Liste des trajets disponibles (JSON)
     * Utilisée pour les appels AJAX
     */
    public function listJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode($this->trajetModel->getAvailableTrajets());
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ]);
        }
    }

    /* =====================================================
     * CRÉATION DE TRAJET
     * ===================================================== */

    /**
     * Affiche le formulaire de création d’un trajet
     * Accessible uniquement aux utilisateurs connectés
     */
    public function createForm(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->render('trajet_form', [
            'user'    => $_SESSION['user'],
            'agences' => $this->trajetModel->getAgences()
        ]);
    }

    /**
     * Création d’un trajet (POST / AJAX)
     */
    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Vérification connexion
            if (!isset($_SESSION['user']['id'])) {
                echo json_encode(['success' => false, 'message' => 'Connexion requise']);
                exit;
            }

            // Vérification des champs obligatoires
            $required = ['depart', 'arrivee', 'date_depart', 'date_arrivee', 'nb_places_totales'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Champ manquant : $field"]);
                    exit;
                }
            }

            // Préparation des données
            $data = [
                'depart'               => (int) $_POST['depart'],
                'arrivee'              => (int) $_POST['arrivee'],
                'date_depart'          => $_POST['date_depart'],
                'date_arrivee'         => $_POST['date_arrivee'],
                'nb_places_totales'    => (int) $_POST['nb_places_totales'],
                'conducteur_id'        => (int) $_SESSION['user']['id']
            ];

            // Validation métier
            if ($data['depart'] === $data['arrivee']) {
                echo json_encode(['success' => false, 'message' => 'Les agences doivent être différentes']);
                exit;
            }

            if ($data['date_arrivee'] <= $data['date_depart']) {
                echo json_encode(['success' => false, 'message' => 'La date d’arrivée doit être après le départ']);
                exit;
            }

            // Création du trajet
            $success = $this->trajetModel->create($data);

            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Trajet créé avec succès' : 'Erreur lors de la création'
            ]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /* =====================================================
     * ÉDITION DE TRAJET (GET + POST)
     * ===================================================== */

    /**
     * Modification d’un trajet
     * - GET : affiche le formulaire
     * - POST : met à jour le trajet (AJAX)
     */
    public function edit(int $id): void
    {
        // Vérification connexion
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $trajet = $this->trajetModel->getById($id);

        // Sécurité : seul le conducteur peut modifier
        if (!$trajet || $trajet['conducteur_id'] != $userId) {
            echo "Trajet introuvable ou accès refusé";
            exit;
        }

        // -----------------
        // POST (AJAX)
        // -----------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            header('Content-Type: application/json; charset=utf-8');

            $required = [
                'depart', 'arrivee', 'date_depart',
                'date_arrivee', 'nb_places_totales', 'nb_places_disponibles'
            ];

            foreach ($required as $field) {
                if (!isset($_POST[$field]) || $_POST[$field] === '') {
                    echo json_encode(['success' => false, 'message' => "Champ manquant : $field"]);
                    exit;
                }
            }

            $data = [
                'depart'               => (int) $_POST['depart'],
                'arrivee'              => (int) $_POST['arrivee'],
                'date_depart'          => $_POST['date_depart'],
                'date_arrivee'         => $_POST['date_arrivee'],
                'nb_places_totales'    => (int) $_POST['nb_places_totales'],
                'nb_places_disponibles'=> (int) $_POST['nb_places_disponibles']
            ];

            // Validation métier
            if ($data['depart'] === $data['arrivee']) {
                echo json_encode(['success' => false, 'message' => 'Les agences doivent être différentes']);
                exit;
            }

            if ($data['date_arrivee'] <= $data['date_depart']) {
                echo json_encode(['success' => false, 'message' => 'La date d’arrivée doit être après le départ']);
                exit;
            }

            try {
                $success = $this->trajetModel->update($id, $data);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Trajet modifié avec succès' : 'Erreur lors de la modification'
                ]);
                exit;
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
                exit;
            }
        }

        // -----------------
        // GET : affichage formulaire
        // -----------------
        $this->render('edit_trajet', [
            'user'    => $_SESSION['user'],
            'trajet'  => $trajet,
            'agences' => $this->trajetModel->getAgences()
        ]);
    }

    /* =====================================================
     * SUPPRESSION DE TRAJET
     * ===================================================== */

    /**
     * Supprime un trajet
     * Accessible uniquement au conducteur
     */
    public function delete(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_SESSION['user']['id'])) {
                echo json_encode(['success' => false, 'message' => 'Connexion requise']);
                exit;
            }

            $trajet = $this->trajetModel->getById($id);
            if (!$trajet) {
                echo json_encode(['success' => false, 'message' => 'Trajet introuvable']);
                exit;
            }

            if ($trajet['conducteur_id'] != $_SESSION['user']['id']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vous n’êtes pas autorisé à supprimer ce trajet'
                ]);
                exit;
            }

            $success = $this->trajetModel->delete($id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Trajet supprimé avec succès' : 'Erreur lors de la suppression'
            ]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
            exit;
        }
    }

    /* =====================================================
     * RÉSERVATIONS
     * ===================================================== */

    /**
     * Formulaire de réservation d’un trajet
     */
    public function reserveForm(int $id): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $trajet  = $this->trajetModel->getByIdWithAgences($id);
        $message = null;

        if (!$trajet || $trajet['nb_places_disponibles'] <= 0) {
            $message = "Trajet introuvable ou complet";
        }

        $reservationModel = new Reservation();
        if ($reservationModel->hasUserReserved($id, $_SESSION['user']['id'])) {
            $message = "Vous avez déjà réservé ce trajet.";
        }

        $this->render('reservation_form', [
            'user'    => $_SESSION['user'],
            'trajet'  => $trajet,
            'message' => $message
        ]);
    }

    /**
     * Création d’une réservation (AJAX)
     */
    public function reserve(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'Connexion requise']);
            return;
        }

        $userId   = (int) $_SESSION['user']['id'];
        $nbPlaces = (int) ($_POST['nb_places'] ?? 0);

        if ($nbPlaces < 1) {
            echo json_encode(['success' => false, 'message' => 'Nombre de places invalide']);
            return;
        }

        try {
            $reservationModel = new Reservation();
            $success = $reservationModel->create($id, $userId, $nbPlaces);

            if (!$success) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Réservation impossible (déjà réservée ou places insuffisantes)'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => "Réservation confirmée pour $nbPlaces place(s)"
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
        }
    }

    /**
     * Liste des réservations de l'utilisateur connecté
     */
    public function myReservations(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $reservationModel = new Reservation();
        $reservations = $reservationModel->getByUserId($_SESSION['user']['id']);

        $this->render('my_reservations', ['reservations' => $reservations]);
    }

    /**
     * Annulation d’une réservation
     */
    public function cancelReservation(int $id): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $reservationModel = new Reservation();
        $reservation = $reservationModel->getById($id);

        if (!$reservation || $reservation['utilisateur_id'] != $_SESSION['user']['id']) {
            header('Location: ' . BASE_URL . '/reservation/mine');
            exit;
        }

        $reservationModel->delete($id);
        header('Location: ' . BASE_URL . '/reservation/mine');
        exit;
    }

    /**
     * Modification du nombre de places réservées
     */
    public function updateReservation(int $id): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $newNb = (int) ($_POST['nb_places'] ?? 0);

        $reservationModel = new Reservation();
        $reservationModel->updatePlaces($id, $_SESSION['user']['id'], $newNb);

        header('Location: ' . BASE_URL . '/reservation/mine');
        exit;
    }
}
