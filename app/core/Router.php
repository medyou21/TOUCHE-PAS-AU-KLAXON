<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function get($url, $action) {
        $this->routes['GET'][$url] = $action;
    }

    public function post($url, $action) {
        $this->routes['POST'][$url] = $action;
    }

    public function run() {
        $baseUri = '/TOUCHE-PAS-AU-KLAXON-HAMDI-Mohamed/public'; // à adapter selon URL
        $uri = str_replace($baseUri, '', $_SERVER['REQUEST_URI']);
        $uri = explode('?', $uri)[0];
        $method = $_SERVER['REQUEST_METHOD'];

        if(isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            [$controller, $controllerMethod] = explode('@', $action);
            $controller = "App\\Controllers\\$controller";
            $controllerObj = new $controller();
            $controllerObj->$controllerMethod();
        } else {
            echo "404 - Page non trouvée";
        }
    }
}
