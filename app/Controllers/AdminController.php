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
    /** @var Utilisateur $userModel */
    private Utilisateur $userModel;

    /** @var Agence $agenceModel */
    private Agence $agenceModel;

    /** @var Trajet $trajetModel */
    private Trajet $trajetModel;

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

        try {
            $users = $this->userModel->getAll();
            echo json_encode($users);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

        try {
            $agences = $this->agenceModel->getAll();
            echo json_encode($agences);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Création d'une nouvelle agence (AJAX)
     */
    public function createAgence(): void
    {
        header('Content-Type: application/json');

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                throw new \Exception('Nom d’agence vide');
            }

            $this->agenceModel->create(['name' => $name]);

            echo json_encode([
                'success' => true,
                'message' => 'Agence créée avec succès'
            ]);
        } catch (\Throwable $e) {
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

        if ($id <= 0 || $name === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Données invalides'
            ]);
            return;
        }

        try {
            $success = $this->agenceModel->update($id, ['name' => $name]);

            echo json_encode([
                'success' => $success,
                'message' => $success
                    ? 'Agence modifiée avec succès'
                    : 'Erreur lors de la modification'
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
            $success = $this->agenceModel->delete($id);

            echo json_encode([
                'success' => $success,
                'message' => $success
                    ? 'Agence supprimée avec succès'
                    : 'Erreur lors de la suppression'
            ]);
        } catch (\Throwable $e) {
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
     * Liste des trajets disponibles (JSON)
     */
    public function trajetsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // On utilise la méthode existante du modèle Trajet
            $trajets = $this->trajetModel->getAvailableTrajets();

            echo json_encode($trajets);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ]);
        }
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

        try {
            $success = $this->trajetModel->delete($id);

            echo json_encode([
                'success' => $success,
                'message' => $success
                    ? 'Trajet supprimé avec succès'
                    : 'Erreur lors de la suppression du trajet'
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

        try {
            // Statistiques globales
            $usersCount        = count($this->userModel->getAll());
            $agencesCount      = count($this->agenceModel->getAll());
            $trajetsCount      = count($this->trajetModel->getAvailableTrajets());
            $trajetsActifsCount = $this->trajetModel->countActifs();

            // Trajets par jour (7 derniers jours)
            $trajetsParJourRaw = $this->trajetModel->getTrajetsLastDays(7);
            $trajetsParJour = [
                'labels' => array_map(
                    fn(array $t) => date('d/m', strtotime($t['date'])),
                    $trajetsParJourRaw
                ),
                'data' => array_map(
                    fn(array $t) => $t['count'],
                    $trajetsParJourRaw
                )
            ];

            // Répartition des utilisateurs par rôle
            $usersByRoleRaw = $this->userModel->countByRole();
            $usersByRole = [
                'labels' => array_map(
                    fn(array $r) => ucfirst($r['role']),
                    $usersByRoleRaw
                ),
                'data' => array_map(
                    fn(array $r) => $r['count'],
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
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ]);
        }
    }
}
