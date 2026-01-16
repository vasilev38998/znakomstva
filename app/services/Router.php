<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $requestUri): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo 'Страница не найдена';
            return;
        }

        [$class, $methodName] = $this->routes[$method][$path];
        $controller = new $class();
        $controller->{$methodName}();
    }
}
