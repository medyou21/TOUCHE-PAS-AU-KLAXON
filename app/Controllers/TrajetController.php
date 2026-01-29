<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Trajet;
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

    // =========================
    // Page d'accueil
    // =========================
    public function home(): void
    {
        $user = $_SESSION['user'] ?? null;
        $this->render('home', ['user' => $user]);
    }

    // =========================
    // JSON pour AJAX (home)
    // =========================
    public function listJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode($this->trajetModel->getAvailableTrajets());
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur'
            ]);
        }
    }

    // =========================
    // Formulaire création
    // =========================
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

    // =========================
    // Création du trajet (POST)
    // =========================
    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Sécurité : utilisateur connecté
            if (!isset($_SESSION['user']['id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Connexion requise'
                ]);
                return;
            }

            // Champs requis
            $required = [
                'depart',
                'arrivee',
                'date_depart',
                'date_arrivee',
                'nb_places_totales'
            ];

            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Champ manquant : ' . $field
                    ]);
                    return;
                }
            }

            // Données normalisées
            $data = [
                'depart'            => (int) $_POST['depart'],   // agence_depart_id
                'arrivee'           => (int) $_POST['arrivee'],  // agence_arrivee_id
                'date_depart'       => $_POST['date_depart'],
                'date_arrivee'      => $_POST['date_arrivee'],
                'nb_places_totales' => (int) $_POST['nb_places_totales'],
                'conducteur_id'     => (int) $_SESSION['user']['id']
            ];

            // Règles métier
            if ($data['depart'] === $data['arrivee']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Les agences doivent être différentes'
                ]);
                return;
            }

            if ($data['date_arrivee'] <= $data['date_depart']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La date d’arrivée doit être après le départ'
                ]);
                return;
            }

            // Insertion
            $success = $this->trajetModel->create($data);

            echo json_encode([
                'success' => $success,
                'message' => $success
                    ? 'Trajet créé avec succès'
                    : 'Erreur lors de la création'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur'
            ]);
        }
    }
}
