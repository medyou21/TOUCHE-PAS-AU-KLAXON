<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Utilisateur;
use App\Models\Agence;
use App\Models\Trajet;

/**
 * ----------------------------------------------------
 * Contrôleur ADMIN
 * Gère toutes les fonctionnalités du back-office :
 *  - Dashboard
 *  - Utilisateurs
 *  - Agences
 *  - Trajets
 *  - Statistiques
 * ----------------------------------------------------
 */
class AdminController extends Controller
{
    /** @var Utilisateur */
    private $userModel;

    /** @var Agence */
    private $agenceModel;

    /** @var Trajet */
    private $trajetModel;

    /**
     * Constructeur
     * - Démarre la session si nécessaire
     * - Vérifie que l'utilisateur est ADMIN
     * - Initialise les modèles
     */
    public function __construct()
    {
        // Démarrage de la session si non active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔐 Sécurité : accès réservé aux administrateurs
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . '/login');
            exit;
        }

        // Initialisation des modèles
        $this->userModel   = new Utilisateur();
        $this->agenceModel = new Agence();
        $this->trajetModel = new Trajet();
    }

    /**
     * Vérifie si l'utilisateur connecté est ADMIN
     *
     * @return bool
     */
    private function isAdmin(): bool
    {
        return !empty($_SESSION['user'])
            && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    /* =====================================================
     * DASHBOARD
     * ===================================================== */

    /**
     * Page principale du tableau de bord admin
     */
    public function index(): void
    {
        $this->render('admin_dashboard');
    }

    /* =====================================================
     * UTILISATEURS
     * ===================================================== */

    /**
     * Page HTML de gestion des utilisateurs
     */
    public function users(): void
    {
        $this->render('admin_users');
    }

    /**
     * Retourne la liste des utilisateurs en JSON
     * (utilisé par AJAX)
     */
    public function usersJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->userModel->getAll());
    }

    /* =====================================================
     * AGENCES
     * ===================================================== */

    /**
     * Page HTML de gestion des agences
     */
    public function agences(): void
    {
        $this->render('admin_agences');
    }

    /**
     * Retourne la liste des agences en JSON
     */
    public function agencesJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->agenceModel->getAll());
    }

    /**
     * Création d'une nouvelle agence (AJAX)
     */
    public function createAgence(): void
    {
        header('Content-Type: application/json');

        try {
            // Récupération et nettoyage du nom
            $name = trim($_POST['name'] ?? '');

            // Insertion en base
            $this->agenceModel->create([
                'name' => $name
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Agence créée avec succès'
            ]);

        } catch (\Exception $e) {

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mise à jour d'une agence existante
     */
    public function updateAgence(): void
    {
        header('Content-Type: application/json');

        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        // Validation des données
        if ($id <= 0 || $name === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Données invalides'
            ]);
            return;
        }

        // Mise à jour
        $success = $this->agenceModel->update($id, [
            'name' => $name
        ]);

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Agence modifiée avec succès'
                : 'Erreur lors de la modification'
        ]);
    }

    /**
     * Suppression d'une agence
     *
     * @param int $id
     */
    public function deleteAgence(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $this->agenceModel->delete($id);

            echo json_encode([
                'success' => true,
                'message' => 'Agence supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /* =====================================================
     * TRAJETS
     * ===================================================== */

    /**
     * Page HTML de gestion des trajets
     */
    public function trajets(): void
    {
        $this->render('admin_trajets');
    }

    /**
     * Liste des trajets avec conducteur (JSON)
     */
    public function trajetsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->trajetModel->getAllWithConducteur());
    }

    /**
     * Suppression d'un trajet
     *
     * @param int $id
     */
    public function deleteTrajet(int $id): void
    {
        header('Content-Type: application/json');

        if ($id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID trajet invalide'
            ]);
            return;
        }

        $success = $this->trajetModel->delete($id);

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? 'Trajet supprimé avec succès'
                : 'Erreur lors de la suppression du trajet'
        ]);
    }

    /* =====================================================
     * STATISTIQUES DASHBOARD
     * ===================================================== */

    /**
     * Retourne toutes les statistiques du dashboard admin
     * (utilisé par Chart.js)
     */
    public function statsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Statistiques globales
        $usersCount        = count($this->userModel->getAll());
        $agencesCount      = count($this->agenceModel->getAll());
        $trajetsCount      = count($this->trajetModel->getAvailableTrajets());
        $trajetsActifsCount = $this->trajetModel->countActifs();

        // Trajets par jour (7 derniers jours)
        $trajetsParJourRaw = $this->trajetModel->getTrajetsLastDays(7);
        $trajetsParJour = [
            'labels' => array_map(
                fn($t) => date('d/m', strtotime($t['date'])),
                $trajetsParJourRaw
            ),
            'data' => array_map(
                fn($t) => $t['count'],
                $trajetsParJourRaw
            )
        ];

        // Répartition des utilisateurs par rôle
        $usersByRoleRaw = $this->userModel->countByRole();
        $usersByRole = [
            'labels' => array_map(
                fn($r) => ucfirst($r['role']),
                $usersByRoleRaw
            ),
            'data' => array_map(
                fn($r) => $r['count'],
                $usersByRoleRaw
            )
        ];

        echo json_encode([
            'users'          => $usersCount,
            'agences'        => $agencesCount,
            'trajets'        => $trajetsCount,
            'trajets_actifs' => $trajetsActifsCount,
            'trajets_jour'   => $trajetsParJour,
            'users_role'     => $usersByRole
        ]);
    }
}
