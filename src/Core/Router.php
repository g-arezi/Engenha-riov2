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
        
        // Add debugging log
        error_log("Trying to match path: " . $path . " with method: " . $requestMethod);
        
        // First, try to find exact matches (routes without parameters)
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && !preg_match('/\{([^}]+)\}/', $route['path'])) {
                if ($route['path'] === $path) {
                    error_log("Exact match found: " . $route['path']);
                    $this->executeRoute($route, $path);
                    return;
                }
            }
        }
        
        // Sort routes to ensure more specific routes are checked first
        usort($this->routes, function($a, $b) {
            // Count segments (parts separated by /)
            $aSegments = substr_count($a['path'], '/');
            $bSegments = substr_count($b['path'], '/');
            
            // More segments = more specific route
            if ($aSegments !== $bSegments) {
                return $bSegments - $aSegments; // Descending order
            }
            
            // Count parameters (parts like {id})
            $aParams = substr_count($a['path'], '{');
            $bParams = substr_count($b['path'], '{');
            
            // Fewer parameters = more specific route
            return $aParams - $bParams; // Ascending order
        });
        
        // Then check parameterized routes
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match('/\{([^}]+)\}/', $route['path'])) {
                if ($this->matchRoute($route, $path, $requestMethod)) {
                    error_log("Parameterized match found: " . $route['path'] . " for " . $path);
                    $this->executeRoute($route, $path);
                    return;
                }
            }
        }

        // Log the 404 error
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

        // Convert route path to regex pattern
        $routePath = $route['path'];
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        // Check if path matches pattern
        if (preg_match($pattern, $path)) {
            error_log("Route matched: " . $routePath . " => " . $path);
            return true;
        }
        
        return false;
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
        // Convert route path to regex pattern with capturing groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        $matches = [];
        preg_match($pattern, $actualPath, $matches);
        
        // First match is the full string, remove it
        array_shift($matches);
        
        // Log the parameters
        error_log("Extracted parameters: " . json_encode($matches));
        
        return $matches;
    }
}
