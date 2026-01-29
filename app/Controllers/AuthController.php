<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Utilisateur;

class AuthController extends Controller
{
    public function showLoginForm(): void
    {
        $this->render('login');
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Veuillez remplir tous les champs.'
            ];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $userModel = new Utilisateur();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {

            // Stockage COMPLET des infos utilisateur
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'prenom'    => $user['prenom'],
                'nom'       => $user['nom'],
                'email'     => $user['email'],
                'telephone' => $user['telephone'] ?? null,
                'role'      => $user['role'] ?? 'user'
            ];

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Connexion réussie !'
            ];

            $this->redirect(BASE_URL . '/');
            return;
        }

        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Identifiant ou mot de passe incorrect.'
        ];
        $this->redirect(BASE_URL . '/login');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        $this->redirect(BASE_URL . '/login');
    }
}
