<?php

namespace App\Controllers;

use Core\Controller;
use Core\Flash;
use Core\Middleware;

/**
 * AdminAuthController — Xử lý xác thực Admin bằng mã PIN
 *
 * Hoàn toàn độc lập với hệ thống user thông thường.
 * Session admin: $_SESSION['admin_auth'] = true
 * PIN được lưu dạng bcrypt hash trong .env (ADMIN_PIN_HASH)
 */
class AdminAuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 15 * 60; // 15 phút

    public function loginForm(): void
    {
        // Nếu đã auth admin rồi thì vào thẳng dashboard
        if (!empty($_SESSION['admin_auth'])) {
            $this->redirect('admin');
            return;
        }

        $csrf       = $this->csrfToken();
        $isLocked   = $this->isLockedOut();
        $attemptsLeft = self::MAX_ATTEMPTS - (int)($_SESSION['admin_pin_attempts'] ?? 0);

        include APP_PATH . '/views/admin/login.php';
    }

    public function login(): void
    {
        if (!empty($_SESSION['admin_auth'])) {
            $this->redirect('admin'); return;
        }

        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên hết hạn. Vui lòng thử lại.');
            $this->redirect('admin/login'); return;
        }

        // ─── Rate limiting ────────────────────────────────────────────────────
        if ($this->isLockedOut()) {
            $remaining = (int)($_SESSION['admin_pin_lockout'] ?? 0) - time();
            Flash::set('danger', "Quá nhiều lần thử. Vui lòng chờ " . ceil($remaining / 60) . " phút.");
            $this->redirect('admin/login'); return;
        }

        $pin     = trim($_POST['pin'] ?? '');
        $storedHash = $_ENV['ADMIN_PIN_HASH'] ?? '';

        if (empty($pin) || empty($storedHash)) {
            Flash::set('danger', 'Cấu hình hệ thống lỗi. Liên hệ quản trị viên.');
            $this->redirect('admin/login'); return;
        }

        if (password_verify($pin, $storedHash)) {
            // ─── Đăng nhập thành công ─────────────────────────────────────────
            session_regenerate_id(true);
            $_SESSION['admin_auth']        = true;
            $_SESSION['admin_logged_at']   = time();
            unset($_SESSION['admin_pin_attempts'], $_SESSION['admin_pin_lockout']);

            Flash::set('success', 'Chào mừng Admin! Bạn đã đăng nhập thành công.');
            $this->redirect('admin');
        } else {
            // ─── Sai PIN ──────────────────────────────────────────────────────
            $_SESSION['admin_pin_attempts'] = ($_SESSION['admin_pin_attempts'] ?? 0) + 1;

            if ((int)$_SESSION['admin_pin_attempts'] >= self::MAX_ATTEMPTS) {
                $_SESSION['admin_pin_lockout'] = time() + self::LOCKOUT_SECONDS;
                Flash::set('danger', 'Sai quá nhiều lần! Tài khoản bị khóa 15 phút.');
            } else {
                $left = self::MAX_ATTEMPTS - (int)$_SESSION['admin_pin_attempts'];
                Flash::set('danger', "Mã PIN không đúng. Còn {$left} lần thử.");
            }

            $this->redirect('admin/login');
        }
    }

    public function logout(): void
    {
        unset($_SESSION['admin_auth'], $_SESSION['admin_logged_at'],
              $_SESSION['admin_pin_attempts'], $_SESSION['admin_pin_lockout']);
        Flash::set('info', 'Bạn đã đăng xuất khỏi Admin Panel.');
        $this->redirect('admin/login');
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function isLockedOut(): bool
    {
        if (empty($_SESSION['admin_pin_lockout'])) return false;
        if (time() < (int)$_SESSION['admin_pin_lockout']) return true;

        // Lockout đã hết hạn — reset
        unset($_SESSION['admin_pin_lockout'], $_SESSION['admin_pin_attempts']);
        return false;
    }
}
