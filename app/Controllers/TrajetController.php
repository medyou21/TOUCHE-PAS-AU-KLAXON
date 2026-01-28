<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Trajet;

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

    /**
     * Page d'accueil
     * Affiche les trajets même si l'utilisateur n'est pas connecté
     */
    public function home(): void
    {
        $trajets = $this->trajetModel->getAvailableTrajets();

        // On récupère l'utilisateur connecté si disponible
        $user = $_SESSION['user'] ?? null;

        $this->render('home', [
            'trajets' => $trajets,
            'user' => $user, // null si non connecté
        ]);
    }

    public function create(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $this->render('trajet_form');
    }

    public function store(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $data = $_POST;
        $data['auteur_id'] = $_SESSION['user']['id'];

        $this->trajetModel->create($data);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Trajet créé avec succès'
        ];

        $this->redirect(BASE_URL . '/');
    }
}
