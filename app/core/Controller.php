<?php
namespace App\Core;

/**
 * Classe de base pour tous les contrôleurs
 */
class Controller
{
    /**
     * Charge une vue simple (sans header/footer)
     *
     * @param string $view Nom de la vue (relatif à app/Views)
     * @param array $data Données à passer à la vue
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Erreur : la vue $viewFile n'existe pas";
        }
    }

    /**
     * Charge une vue avec header et footer automatiquement
     *
     * @param string $view Nom de la vue (relatif à app/Views)
     * @param array $data Données à passer à la vue
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $header = __DIR__ . '/../Views/templates/header.php';
        $footer = __DIR__ . '/../Views/templates/footer.php';
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        // Inclure header
        if (file_exists($header)) {
            require $header;
        } else {
            echo "Erreur : le header $header n'existe pas";
        }

        // Inclure la vue
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Erreur : la vue $viewFile n'existe pas";
        }

        // Inclure footer
        if (file_exists($footer)) {
            require $footer;
        } else {
            echo "Erreur : le footer $footer n'existe pas";
        }
    }

    /**
     * Redirige vers une URL donnée
     *
     * @param string $url
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
