<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Utilisateur;
use App\Models\Agence;
use App\Models\Trajet;

class AdminController extends Controller
{
    private $userModel;
    private $agenceModel;
    private $trajetModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔐 Accès ADMIN uniquement
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . '/login');
            exit;
        }

        $this->userModel   = new Utilisateur();
        $this->agenceModel = new Agence();
        $this->trajetModel = new Trajet();
    }

    private function isAdmin(): bool
    {
        return !empty($_SESSION['user'])
            && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    /* =====================================================
     * DASHBOARD
     * ===================================================== */
    public function index(): void
    {
        $this->render('admin_dashboard');
    }

    /* =====================================================
     * UTILISATEURS
     * ===================================================== */

    // Page HTML
    public function users(): void
    {
        $this->render('admin_users');
    }

    // JSON
    public function usersJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->userModel->getAll());
    }

    /* =====================================================
     * AGENCES
     * ===================================================== */

    // Page HTML
    public function agences(): void
    {
        $this->render('admin_agences');
    }

    // JSON
    public function agencesJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->agenceModel->getAll());
    }

   public function createAgence(): void
{
    header('Content-Type: application/json');

    try {
        $name = trim($_POST['name'] ?? '');

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

    // Page HTML
    public function trajets(): void
    {
        $this->render('admin_trajets');
    }

    // JSON (pour loadTrajets())
    public function trajetsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->trajetModel->getAllWithConducteur());
    }

    public function deleteTrajet(int $id): void
    {
        header('Content-Type: application/json');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID trajet invalide']);
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
       /**
 * =========================
 * STATISTIQUES DASHBOARD
 * =========================
 */
public function statsJson(): void
{
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'users'            => count($this->userModel->getAll()),
        'agences'          => count($this->agenceModel->getAll()),
        'trajets'          => count($this->trajetModel->getAvailableTrajets()),
        'trajets_actifs'   => $this->trajetModel->countActifs()
    ]);
}

}
