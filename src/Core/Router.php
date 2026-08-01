<?php
// Ministry Ops PHP - Router Class

class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => '/' . trim($path, '/'),
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Detect route from query string ?route= or URI
        $route = $_GET['route'] ?? $_GET['r'] ?? null;
        if ($route === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
                $uri = substr($uri, strlen($scriptDir));
            }
            $route = $uri;
        }

        $route = '/' . trim($route, '/');

        foreach ($this->routes as $r) {
            if ($r['method'] === $method && $r['path'] === $route) {
                [$controllerClass, $methodName] = $r['handler'];
                require_once __DIR__ . "/../Controllers/{$controllerClass}.php";
                $controller = new $controllerClass();
                $controller->$methodName();
                return;
            }
        }

        // 404 Not Found fallback
        http_response_code(404);
        require_once __DIR__ . '/Helpers.php';
        Helpers::setFlash('warning', 'Página não encontrada.');
        Helpers::redirect('dashboard');
    }
}
