<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Debug logging
        error_log("Dispatching: Method=$requestMethod, Path=$path");
        
        foreach ($this->routes as $route) {
            error_log("Checking route: {$route['path']} (method: {$route['method']})");
            if ($this->matchRoute($route, $path, $requestMethod)) {
                error_log("Route matched: {$route['path']}");
                $this->executeRoute($route, $path);
                return;
            }
        }

        // 404 Not Found
        error_log("No route matched for $path");
        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }

    private function matchRoute(array $route, string $path, string $method): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }

        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['path']);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $path);
    }

    private function executeRoute(array $route, string $path): void
    {
        // Execute middlewares
        foreach ($route['middlewares'] as $middleware) {
            $middlewareInstance = new $middleware();
            if (!$middlewareInstance->handle()) {
                return;
            }
        }

        // Execute handler
        if (is_array($route['handler'])) {
            [$controllerClass, $method] = $route['handler'];
            $controller = new $controllerClass();
            
            // Extract parameters
            $params = $this->extractParams($route['path'], $path);
            call_user_func_array([$controller, $method], $params);
        } else {
            call_user_func($route['handler']);
        }
    }

    private function extractParams(string $routePath, string $actualPath): array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $pathParts = explode('/', trim($actualPath, '/'));
        $params = [];

        foreach ($routeParts as $index => $part) {
            if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                $params[] = $pathParts[$index] ?? null;
            }
        }

        return $params;
    }
}
