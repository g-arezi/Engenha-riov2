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
        
        // Primeiro, tentar encontrar rotas exatas (sem parâmetros)
        foreach ($this->routes as $route) {
            // Se é uma rota exata (sem parâmetros {})
            if ($route['method'] === $requestMethod && !preg_match('/\{([^}]+)\}/', $route['path']) && $route['path'] === $path) {
                $this->executeRoute($route, $path);
                return;
            }
        }

        // Depois, procurar por rotas com parâmetros
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $path, $requestMethod)) {
                $this->executeRoute($route, $path);
                return;
            }
        }

        // Registrar em log o erro 404 para debug
        error_log("404 Not Found: " . $path);
        
        // 404 Not Found
        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }

    private function matchRoute(array $route, string $path, string $method): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }

        // Não fazer match em rotas exatas
        if (!preg_match('/\{([^}]+)\}/', $route['path'])) {
            return false;
        }

        // Converter partes variáveis da rota para regex
        $routeParts = explode('/', trim($route['path'], '/'));
        $pathParts = explode('/', trim($path, '/'));
        
        // Se o número de partes não corresponde, não é match
        if (count($routeParts) !== count($pathParts)) {
            return false;
        }
        
        // Verificar cada parte da rota
        foreach ($routeParts as $index => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                // Esta é uma parte variável, pular
                continue;
            }
            
            // Esta é uma parte fixa, deve corresponder exatamente
            if ($routePart !== $pathParts[$index]) {
                return false;
            }
        }
        
        return true;
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
        // Dividir as partes da rota e do caminho real
        $routeParts = explode('/', ltrim($routePath, '/'));
        $pathParts = explode('/', ltrim($actualPath, '/'));
        
        $params = [];
        
        // Iterar sobre cada parte da rota para encontrar parâmetros
        foreach ($routeParts as $index => $part) {
            // Se é um parâmetro (dentro de { })
            if (preg_match('/^\{([^}]+)\}$/', $part)) {
                // Verificar se existe um valor correspondente no caminho real
                if (isset($pathParts[$index])) {
                    // Decodificar o valor do parâmetro da URL
                    $params[] = urldecode($pathParts[$index]);
                }
            }
        }
        
        return $params;
    }
}
