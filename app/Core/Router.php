<?php
/**
 * Simple Router
 * Maps URLs to controller actions
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): self
    {
        $this->routes[$method][$path] = $handler;
        return $this;
    }

    public function middleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/') ?: '/';

        // Run middleware
        foreach ($this->middleware as $middleware) {
            $result = $middleware();
            if ($result === false) {
                return;
            }
        }

        // Find route
        $handler = $this->routes[$method][$path] ?? null;

        // Try pattern matching for /resource/{id}
        if (!$handler) {
            $handler = $this->matchPattern($method, $path, $params);
        }

        if (!$handler) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        // Execute handler
        if (is_array($handler)) {
            [$controller, $action] = $handler;
            $controller = new $controller();
            $response = $controller->$action(...($params ?? []));
        } else {
            $response = $handler(...($params ?? []));
        }

        if ($response !== null) {
            echo $response;
        }
    }

    private function matchPattern(string $method, string $path, ?array &$params = []): callable|array|null
    {
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            if (strpos($route, '{') === false) {
                continue;
            }

            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $params = $matches;
                return $handler;
            }
        }

        return null;
    }
}
