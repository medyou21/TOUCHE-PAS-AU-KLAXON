<?php

namespace App\Core;

/**
 * ----------------------------------------------------
 * Classe Router
 * ----------------------------------------------------
 * Gère le routage HTTP de l'application :
 *  - Association URL → Contrôleur@Méthode
 *  - Support des méthodes GET et POST
 *  - Gestion des paramètres dynamiques
 *  - Page 404 si aucune route ne correspond
 * ----------------------------------------------------
 */
class Router
{
    /**
     * Tableau des routes
     * Format :
     * [
     *   'GET'  => ['/url' => 'Controller@method'],
     *   'POST' => ['/url' => 'Controller@method']
     * ]
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Ajoute une route GET
     *
     * @param string $url    URL de la route
     * @param string $action Action sous forme Controller@method
     */
    public function get(string $url, string $action): void
    {
        $this->routes['GET'][$url] = $action;
    }

    /**
     * Ajoute une route POST
     *
     * @param string $url    URL de la route
     * @param string $action Action sous forme Controller@method
     */
    public function post(string $url, string $action): void
    {
        $this->routes['POST'][$url] = $action;
    }

    /**
     * Lance le routeur
     * - Analyse l'URL demandée
     * - Compare avec les routes déclarées
     * - Appelle le contrôleur correspondant
     */
    public function run(): void
    {
        // Dossier du script (utile si projet dans un sous-dossier)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

        // URI complète demandée
        $requestUri = $_SERVER['REQUEST_URI'];

        // Nettoyage de l'URI (suppression du dossier racine)
        $uri = str_replace($scriptDir, '', $requestUri);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        // Méthode HTTP utilisée (GET ou POST)
        $method = $_SERVER['REQUEST_METHOD'];

        // Aucune route définie pour cette méthode
        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        // Parcours des routes correspondant à la méthode HTTP
        foreach ($this->routes[$method] as $route => $action) {

            // Normalisation de la route
            $route = '/' . trim($route, '/');

            // Conversion des paramètres dynamiques {id} en regex
            $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            // Vérification si l'URI correspond à la route
            if (preg_match($pattern, $uri, $matches)) {

                // Suppression de l'URI complète du tableau des matches
                array_shift($matches);

                // Extraction du contrôleur et de la méthode
                [$controller, $methodName] = explode('@', $action);

                // Namespace complet du contrôleur
                $controller = "App\\Controllers\\$controller";

                // Appel dynamique de la méthode avec paramètres
                call_user_func_array(
                    [new $controller(), $methodName],
                    $matches
                );
                return;
            }
        }

        // Aucune route trouvée
        $this->notFound();
    }

    /**
     * Affiche une page 404
     */
    private function notFound(): void
    {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 - Page non trouvée</h1>";
    }
}
