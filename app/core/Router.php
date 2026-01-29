<?php
namespace App\Core;

class Router {
    private array $routes = [];

    // Ajouter une route GET
    public function get(string $url, string $action): void {
        $this->routes['GET'][$url] = $action;
    }

    // Ajouter une route POST
    public function post(string $url, string $action): void {
        $this->routes['POST'][$url] = $action;
    }

    // Lancer le router
    public function run(): void
{
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']); 
    $requestUri = $_SERVER['REQUEST_URI'];

    // Nettoyer l'URI
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

private function notFound(): void
{
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Page non trouvée</h1>";
}

}
