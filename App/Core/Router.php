<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][$this->normalize($path)] = [$controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][$this->normalize($path)] = [$controller, $method];
    }

    public function dispatch(string $method, string $path): void
    {
        $path = $this->normalize(parse_url($path, PHP_URL_PATH) ?: '/');
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo 'Página no encontrada.';
            return;
        }

        [$controller, $action] = $route;

        $controllerInstance = new $controller();
        $controllerInstance->$action();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}