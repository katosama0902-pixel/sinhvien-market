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
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
