<?php

namespace Core;

/**
 * Base Controller
 * Tất cả controller kế thừa class này để dùng các helper chung
 */
abstract class Controller
{
    private ?array $decodedJsonBody = null;
    private bool $jsonBodyDecoded = false;

    // ─── Legacy page fallback ────────────────────────────────────────────────

    /**
     * Keep legacy controller actions pointed at their migrated Next.js pages.
     *
     * @param string $view Retained for compatibility with existing controller calls.
     * @param array $data Retained for compatibility with existing controller calls.
     * @param string $layout Retained for compatibility with existing controller calls.
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $this->redirectToNextPage();
    }

    protected function redirectToNextPage(): never
    {
        $legacyRoute = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && str_starts_with($legacyRoute, $scriptName)) {
            $legacyRoute = substr($legacyRoute, strlen($scriptName));
        }
        $legacyRoute = trim(strtok((string)$legacyRoute, '?'), '/');
        $targetPath = LegacyPageRedirects::targetPath($legacyRoute, $_GET);
        if ($targetPath === null) {
            throw new \RuntimeException("Không tìm thấy trang Next.js tương ứng với route: {$legacyRoute}");
        }

        $nextBaseUrl = $_ENV['NEXT_APP_URL'] ?? getenv('NEXT_APP_URL');
        if (!is_string($nextBaseUrl) || trim($nextBaseUrl) === '') {
            $nextBaseUrl = null;
        }

        header('Location: ' . LegacyPageRedirects::location($targetPath, $_GET, $nextBaseUrl), true, 302);
        exit;
    }

    /**
     * Render JSON (dùng cho API endpoints)
     */
    protected function json(mixed $data, int $status = 200): void
    {
        // Vứt bỏ mọi output rác (warning/notice/deprecated) đã lỡ in ra trước đó
        // để JSON luôn sạch — tránh client parse lỗi (vd deprecated trên PHP 8.5)
        PostResponseJson::cleanOutputBuffers();
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Trả JSON cho client NGAY (kèm Content-Length để client kết thúc sớm), rồi
     * chạy $task ở nền — dùng cho các tác vụ chậm (Pusher, gọi AI...) để người
     * dùng không phải chờ. Trên PHP-FPM dùng fastcgi_finish_request.
     */
    protected function jsonThenRun(mixed $data, callable $task, int $status = 200): void
    {
        PostResponseJson::cleanOutputBuffers();
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Length: ' . strlen($json));
            header('Connection: close');
        }
        echo $json;
        if (PostResponseJson::isActive()) {
            PostResponseJson::flush();
        }
        if (function_exists('fastcgi_finish_request')) {
            @session_write_close();
            @fastcgi_finish_request();
        } else {
            @ob_flush(); @flush();
        }
        try { $task(); } catch (\Throwable $e) { error_log('jsonThenRun: ' . $e->getMessage()); }
        exit;
    }

    // ─── Redirect ────────────────────────────────────────────────────────────

    /**
     * Redirect sang URL khác
     * Nên dùng path tương đối, VD: '/login', '/products'
     */
    protected function redirect(string $url): void
    {
        // Nếu URL không bắt đầu bằng http thì thêm base URL
        if (!str_starts_with($url, 'http')) {
            $base = rtrim($_ENV['APP_URL'] ?? '', '/');
            $url  = $base . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Xuất một ảnh (bytes + mime) lấy từ DB ra response.
     * Nếu không có ảnh → trả placeholder SVG. Dùng cho các route phục vụ ảnh.
     */
    protected function serveImageBlob(?array $img): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($img && !empty($img['data'])) {
            header('Content-Type: ' . $img['mime']);
            header('Cache-Control: private, max-age=86400');
            header('Content-Length: ' . strlen($img['data']));
            echo $img['data'];
            exit;
        }
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=3600');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
           . '<rect width="400" height="400" fill="#e5e7eb"/>'
           . '<text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="#9ca3af" '
           . 'text-anchor="middle" dominant-baseline="middle">Chưa có ảnh</text></svg>';
        exit;
    }

    /**
     * Redirect tới $url, đẩy response cho client NGAY, rồi mới chạy $task
     * (vd gửi email qua SMTP) ở "nền" để người dùng không phải chờ.
     * Chỉ dùng được khi server hỗ trợ fastcgi_finish_request (PHP-FPM).
     */
    protected function redirectAndRun(string $url, callable $task): void
    {
        if (PostResponseJson::isActive()) {
            header('Location: ' . $url);
            @session_write_close();
            PostResponseJson::flush();
            try {
                $task();
            } catch (\Throwable $e) {
                error_log('Deferred task error: ' . $e->getMessage());
            }
            exit;
        }

        if (!str_starts_with($url, 'http')) {
            $base = rtrim($_ENV['APP_URL'] ?? '', '/');
            $url  = $base . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        @session_write_close();        // lưu session trước khi ngắt response
        @fastcgi_finish_request();     // trả response cho client ngay lập tức
        try {
            $task();
        } catch (\Throwable $e) {
            error_log('Deferred task error: ' . $e->getMessage());
        }
        exit;
    }

    // ─── Session helpers ─────────────────────────────────────────────────────

    /**
     * Lấy user hiện tại từ session
     */
    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Kiểm tra đã đăng nhập chưa
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * Kiểm tra có phải admin không
     */
    protected function isAdmin(): bool
    {
        return ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    // ─── Input helpers ───────────────────────────────────────────────────────

    /**
     * Lấy giá trị POST đã được trim và escape XSS
     */
    protected function input(string $key, string $default = ''): string
    {
        $value = $this->requestData()[$key] ?? $default;
        return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Lấy giá trị GET đã được trim và escape XSS
     */
    protected function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Lấy giá trị POST dạng số nguyên
     */
    protected function inputInt(string $key, int $default = 0): int
    {
        return (int)($this->requestData()[$key] ?? $default);
    }

    /**
     * Read ordinary form data or a JSON object body. Multipart uploads remain
     * available through $_FILES and form fields continue to work unchanged.
     */
    protected function requestData(): array
    {
        if (str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
            if (!$this->jsonBodyDecoded) {
                $decoded = json_decode((string)file_get_contents('php://input'), true);
                $this->decodedJsonBody = is_array($decoded) ? $decoded : [];
                $this->jsonBodyDecoded = true;
            }
            return $this->decodedJsonBody ?? [];
        }

        return $_POST;
    }

    /**
     * Lấy giá trị GET dạng số nguyên
     */
    protected function queryInt(string $key, int $default = 0): int
    {
        return (int)($_GET[$key] ?? $default);
    }

    // ─── CSRF ────────────────────────────────────────────────────────────────

    /**
     * Tạo CSRF token và lưu vào session
     */
    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Xác thực CSRF token từ POST
     */
    protected function verifyCsrf(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($this->requestData()['_csrf'] ?? '');
        $expected = $_SESSION['csrf_token'] ?? '';
        if (!is_string($token) || $token === '' || !is_string($expected) || $expected === '') {
            return false;
        }
        return hash_equals($expected, $token);
    }
}
