<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Utilisateur;

/**
 * ----------------------------------------------------
 * Contrôleur d'authentification
 * Gère :
 *  - l'affichage du formulaire de connexion
 *  - le traitement de la connexion
 *  - la déconnexion
 * ----------------------------------------------------
 */
class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     * - Démarre la session si nécessaire
     * - Redirige l'utilisateur s'il est déjà connecté
     */
    public function showLoginForm(): void
    {
        // Démarrage de la session si elle n'est pas active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔐 Si l'utilisateur est déjà connecté
        if (!empty($_SESSION['user'])) {

            // Redirection selon le rôle
            if (($_SESSION['user']['role'] ?? '') === 'admin') {
                $this->redirect(BASE_URL . '/admin/dashboard');
            } else {
                $this->redirect(BASE_URL . '/');
            }
            return;
        }

        // Affichage de la vue login
        $this->render('login');
    }

    /**
     * Traite la soumission du formulaire de connexion
     * - Vérifie les champs
     * - Vérifie l'existence de l'utilisateur
     * - Vérifie le mot de passe
     * - Initialise la session utilisateur
     */
    public function login(): void
    {
        // Démarrage de la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupération et nettoyage des données du formulaire
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ❌ Validation : champs obligatoires
        if ($email === '' || $password === '') {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Veuillez remplir tous les champs.'
            ];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // Recherche de l'utilisateur par email
        $userModel = new Utilisateur();
        $user      = $userModel->findByEmail($email);

        // ❌ Vérification utilisateur + mot de passe
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Identifiant ou mot de passe incorrect.'
            ];
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // ✅ Connexion réussie : stockage des infos utiles en session
        $_SESSION['user'] = [
            'id'        => (int) $user['id'],
            'prenom'    => $user['prenom'],
            'nom'       => $user['nom'],
            'email'     => $user['email'],
            'telephone' => $user['telephone'] ?? null,
            'role'      => $user['role'] ?? 'user'
        ];

        // Message flash de succès
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Connexion réussie.'
        ];

        // 🔀 Redirection selon le rôle utilisateur
        if ($_SESSION['user']['role'] === 'admin') {
            $this->redirect(BASE_URL . '/admin/dashboard');
        } else {
            $this->redirect(BASE_URL . '/');
        }
    }

    /**
     * Déconnecte l'utilisateur
     * - Vide la session
     * - Détruit la session
     * - Redirige vers la page de connexion
     */
    public function logout(): void
    {
        // Démarrage de la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Suppression des données de session
        $_SESSION = [];
        session_destroy();

        // Redirection vers la page de connexion
        $this->redirect(BASE_URL . '/login');
    }
}
