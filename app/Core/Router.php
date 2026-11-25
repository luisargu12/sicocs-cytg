<?php

class Router
{
    private $routes = [];

    public function any($path, array $handler)
    {
        $path = trim($path, '/');
        $this->routes['GET'][$path] = $handler;
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($uri === null || $uri === false) {
            $uri = '/';
        }

        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = rtrim($base, '/');
        if ($base === '/' || $base === '.') {
            $base = '';
        }

        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        $uri = trim($uri, '/');
        if ($uri === 'index.php') {
            $uri = '';
        }

        if (substr($uri, -4) === '.php') {
            $uri = substr($uri, 0, -4);
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo 'Página no encontrada';
            return;
        }

        [$class, $action] = $handler;
        $controller = new $class();
        $controller->$action();
    }
}
