<?php

namespace Core;

/**
 * Router - Ánh xạ URL sang Controller@Action
 * Hỗ trợ GET và POST, tham số query string (?id=)
 */
class Router
{
    /** @var array<string, array{controller: string, action: string}> */
    private array $routes = [];

    // ─── Đăng ký routes ──────────────────────────────────────────────────────

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = compact('controller', 'action');
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = compact('controller', 'action');
    }

    // ─── Dispatch ────────────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url    = $this->parseUrl();

        // Tìm route khớp (bao gồm prefix matching)
        $route = $this->findRoute($method, $url);

        if ($route === null) {
            $this->notFound();
            return;
        }

        $controllerClass = 'App\\Controllers\\' . $route['controller'] . 'Controller';
        $action          = $route['action'];

        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        // Truyền tham số từ query string vào action
        $controller->$action();
    }

    // ─── Helpers private ─────────────────────────────────────────────────────

    /**
     * Lấy URL path sạch từ request
     */
    private function parseUrl(): string
    {
        $url = $_GET['url'] ?? '';
        // Bỏ query string nếu có trong url param
        $url = strtok($url, '?');
        $url = trim($url, '/');
        // Sanitize cơ bản
        return htmlspecialchars(strip_tags($url), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Tìm route khớp với method + url
     * Hỗ trợ exact match và prefix match (để dùng query string)
     */
    private function findRoute(string $method, string $url): ?array
    {
        // Exact match
        if (isset($this->routes[$method][$url])) {
            return $this->routes[$method][$url];
        }

        // Prefix match (vd: 'products/show' match 'products/show')
        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            if ($url === $pattern) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Trang 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        $viewFile = ROOT . '/app/views/errors/404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<h1>404 - Không tìm thấy trang</h1>';
        }
    }
}
