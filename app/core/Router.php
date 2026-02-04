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
     * @var array<string, array<string, string>> Méthode HTTP → (URL → Action)
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
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $requestUri = $_SERVER['REQUEST_URI'];
        $uri = str_replace($scriptDir, '', $requestUri);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $route => $action) {
            $route = '/' . trim($route, '/');
            $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controller, $methodName] = explode('@', $action);
                $controller = "App\\Controllers\\$controller";
                call_user_func_array([new $controller(), $methodName], $matches);
                return;
            }
        }

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
