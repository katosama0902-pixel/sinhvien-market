<?php

namespace Core;

/**
 * Middleware - Kiểm soát quyền truy cập các route
 */
class Middleware
{
    /**
     * Yêu cầu đăng nhập — nếu chưa login, redirect về /login
     */
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            Flash::set('error', 'Bạn cần đăng nhập để truy cập trang này.');
            self::redirect('login-role');
            return;
        }

        // Kiểm tra tài khoản có bị khóa không
        if (($_SESSION['user']['is_locked'] ?? 0) == 1) {
            // Lấy thông tin khóa mới nhất từ DB và cập nhật session
            // (Không destroy session để giữ thông tin hiển thị ở trang thông báo)
            self::redirect('account-locked');
            return;
        }

        // 1 tài khoản chỉ 1 phiên: đăng nhập ở nơi mới sẽ đẩy phiên cũ ra
        self::enforceSingleSession();
    }

    /**
     * Đảm bảo mỗi tài khoản chỉ có 1 phiên hoạt động (đăng nhập mới nhất thắng).
     * Phiên nào không khớp token trong DB sẽ bị đăng xuất.
     */
    private static function enforceSingleSession(): void
    {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $userModel = new \App\Models\User();
        $dbToken   = (string)($userModel->getSessionToken($userId) ?? '');
        $myToken   = (string)($_SESSION['session_token'] ?? '');

        // Phiên này chưa gắn token → chiếm quyền làm phiên hiện hành
        if ($myToken === '') {
            $myToken = bin2hex(random_bytes(16));
            $_SESSION['session_token'] = $myToken;
            $userModel->setSessionToken($userId, $myToken);
            return;
        }

        // Token khác với DB → tài khoản đã đăng nhập ở nơi khác → đá phiên này
        if ($dbToken !== '' && $dbToken !== $myToken) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            session_start();
            Flash::set('warning', 'Tài khoản của bạn vừa đăng nhập ở nơi khác nên phiên này đã kết thúc.');
            self::redirect('login-role');
        }
    }

    /**
     * Yêu cầu quyền Admin — nếu không phải admin, redirect về trang chủ
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            Flash::set('error', 'Bạn không có quyền truy cập khu vực quản trị.');
            self::redirect('');
        }
    }

    /**
     * Chặn user đã đăng nhập truy cập trang login/register
     */
    public static function requireGuest(): void
    {
        if (isset($_SESSION['user'])) {
            self::redirect('products');
        }
    }

    // ─── Helper private ──────────────────────────────────────────────────────

    private static function redirect(string $path): void
    {
        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost/sinhvien-market', '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }
}
