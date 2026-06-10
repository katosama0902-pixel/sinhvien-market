<?php

namespace Core;

/**
 * Base Controller
 * Tất cả controller kế thừa class này để dùng các helper chung
 */
abstract class Controller
{
    // ─── Render view ─────────────────────────────────────────────────────────

    /**
     * Render một view file với dữ liệu
     *
     * @param string $view  Đường dẫn tương đối, VD: 'products/index'
     * @param array  $data  Dữ liệu truyền vào view (extract thành biến)
     * @param string $layout Layout wrapper, mặc định 'main'; dùng 'admin' cho admin panel
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extract data thành biến cục bộ trong view
        extract($data, EXTR_SKIP);

        $viewFile   = APP_PATH . '/views/' . $view . '.php';
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View không tồn tại: {$view}");
        }

        // Buffer nội dung view
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Wrap vào layout
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render JSON (dùng cho API endpoints)
     */
    protected function json(mixed $data, int $status = 200): void
    {
        // Vứt bỏ mọi output rác (warning/notice/deprecated) đã lỡ in ra trước đó
        // để JSON luôn sạch — tránh client parse lỗi (vd deprecated trên PHP 8.5)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
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
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Length: ' . strlen($json));
            header('Connection: close');
        }
        echo $json;
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
        $value = $_POST[$key] ?? $default;
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
        return (int)($_POST[$key] ?? $default);
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
        $token = $_POST['_csrf'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
