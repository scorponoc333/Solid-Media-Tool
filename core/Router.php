<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'action' => $action];
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'action' => $action];
    }

    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if running in subdirectory
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = $uri ?: '/';

        // Check for direct route match
        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            $this->dispatch($route['controller'], $route['action']);
            return;
        }

        // Check for parameterized routes (e.g., /posts/{id})
        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->dispatch($route['controller'], $route['action'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
    }

    private function dispatch(string $controllerName, string $action, array $params = []): void
    {
        $controllerFile = APP_ROOT . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            throw new RuntimeException("Controller not found: {$controllerName}");
        }

        require_once $controllerFile;

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Action not found: {$controllerName}::{$action}");
        }

        call_user_func_array([$controller, $action], $params);
    }
}
