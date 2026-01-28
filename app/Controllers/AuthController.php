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
        session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Veuillez remplir tous les champs.'
            ];
            $this->redirect(BASE_URL . '/login');
        }

        $userModel = new Utilisateur();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'prenom' => $user['prenom'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user'
            ];

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Connexion réussie !'
            ];
            $this->redirect(BASE_URL . '/'); // accueil
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Identifiant ou mot de passe incorrect.'
            ];
            $this->redirect(BASE_URL . '/login');
        }
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        $this->redirect(BASE_URL . '/login');
    }
}
