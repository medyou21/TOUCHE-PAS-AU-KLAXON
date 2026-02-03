<?php

namespace App\Core;

/**
 * ----------------------------------------------------
 * Classe Controller (classe de base)
 * ----------------------------------------------------
 * Tous les contrôleurs de l'application héritent
 * de cette classe.
 *
 * Elle fournit des méthodes communes :
 *  - chargement des vues
 *  - rendu avec header / footer
 *  - redirections HTTP
 * ----------------------------------------------------
 */
class Controller
{
    /**
     * Charge une vue simple (sans header ni footer)
     * Utile pour :
     *  - appels AJAX
     *  - pages partielles
     *  - modales
     *
     * @param string $view Nom de la vue (relatif à app/Views)
     * @param array  $data Données à transmettre à la vue
     */
    protected function view(string $view, array $data = []): void
    {
        // Rend les clés du tableau $data accessibles comme variables
        extract($data);

        // Chemin complet vers le fichier de vue
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        // Inclusion de la vue si elle existe
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            // Message d'erreur explicite en cas de vue manquante
            echo "Erreur : la vue $viewFile n'existe pas";
        }
    }

    /**
     * Charge une vue complète avec header et footer
     * Méthode la plus utilisée pour les pages HTML classiques
     *
     * @param string $view Nom de la vue (relatif à app/Views)
     * @param array  $data Données à transmettre à la vue
     */
    protected function render(string $view, array $data = []): void
    {
        // Rend les données accessibles dans la vue
        extract($data);

        // Définition des chemins des fichiers
        $header   = __DIR__ . '/../Views/templates/header.php';
        $footer   = __DIR__ . '/../Views/templates/footer.php';
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        // Inclusion du header
        if (file_exists($header)) {
            require $header;
        } else {
            echo "Erreur : le header $header n'existe pas";
        }

        // Inclusion de la vue principale
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Erreur : la vue $viewFile n'existe pas";
        }

        // Inclusion du footer
        if (file_exists($footer)) {
            require $footer;
        } else {
            echo "Erreur : le footer $footer n'existe pas";
        }
    }

    /**
     * Effectue une redirection HTTP
     * Stoppe immédiatement l'exécution du script
     *
     * @param string $url URL de destination
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
