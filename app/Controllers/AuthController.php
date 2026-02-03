<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Utilisateur;

class AuthController extends Controller
{
    /**
     * Affichage du formulaire de connexion
     */
    public function showLoginForm(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si déjà connecté → redirection selon rôle
        if (!empty($_SESSION['user'])) {
            if (($_SESSION['user']['role'] ?? '') === 'admin') {
                $this->redirect(BASE_URL . '/admin/dashboard');
            } else {
                $this->redirect(BASE_URL . '/');
            }
            return;
        }

        $this->render('login');
    }

    /**
     * Traitement de la connexion
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if ($email === '' || $password === '') {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Veuillez remplir tous les champs.'
            ];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $userModel = new Utilisateur();
        $user      = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Identifiant ou mot de passe incorrect.'
            ];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // ✅ Connexion réussie
        $_SESSION['user'] = [
            'id'        => (int) $user['id'],
            'prenom'    => $user['prenom'],
            'nom'       => $user['nom'],
            'email'     => $user['email'],
            'telephone' => $user['telephone'] ?? null,
            'role'      => $user['role'] ?? 'user'
        ];

        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Connexion réussie.'
        ];

        // 🔀 Redirection selon rôle
        if ($_SESSION['user']['role'] === 'admin') {
            $this->redirect(BASE_URL . '/admin/dashboard');
        } else {
            $this->redirect(BASE_URL . '/');
        }
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        $this->redirect(BASE_URL . '/login');
    }
}
