<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\LoginAttempt;

/**
 * AuthController - Đăng ký, đăng nhập, đăng xuất
 * Bảo mật:
 *   - bcrypt (cost 12) cho password
 *   - Rate limiting: max 5 lần thất bại / 15 phút / IP
 *   - CSRF token trên mọi form POST
 *   - Validate + sanitize đầu vào
 */
class AuthController extends Controller
{
    private const MAX_ATTEMPTS  = 5;   // số lần thất bại tối đa
    private const LOCK_MINUTES  = 15;  // khoảng khóa (phút)

    private User         $userModel;
    private LoginAttempt $attemptModel;

    public function __construct()
    {
        $this->userModel    = new User();
        $this->attemptModel = new LoginAttempt();
    }

    // ─── Trang chủ → redirect ─────────────────────────────────────────────

    public function index(): void
    {
        $this->redirect('products');
    }

    // ─── Login ────────────────────────────────────────────────────────────

    /**
     * GET /login - Hiển thị form đăng nhập
     */
    public function loginForm(): void
    {
        Middleware::requireGuest();
        // Tạo CSRF token
        $csrf = $this->csrfToken();
        // Render trực tiếp view (view tự render full HTML, không dùng layout)
        include APP_PATH . '/views/auth/login.php';
    }

    /**
     * POST /login - Xử lý đăng nhập
     */
    public function login(): void
    {
        Middleware::requireGuest();

        // CSRF check
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('login');
        }

        $ip     = $this->getClientIp();
        $errors = [];
        $old    = ['email' => $this->input('email')];

        // ── 1. Rate limiting ───────────────────────────────────────────
        $attempts = $this->attemptModel->getCount($ip, self::LOCK_MINUTES);
        if ($attempts >= self::MAX_ATTEMPTS) {
            $minsLeft = $this->attemptModel->getMinutesLeft($ip, self::LOCK_MINUTES);
            $errors['rate_limit'] = "Quá nhiều lần thất bại. Thử lại sau {$minsLeft} phút.";
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/login.php';
            return;
        }

        // ── 2. Validate input ──────────────────────────────────────────
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        if (!empty($errors)) {
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/login.php';
            return;
        }

        // ── 3. Kiểm tra user tồn tại ──────────────────────────────────
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            // Tăng số lần thất bại
            $this->attemptModel->increment($ip);
            $attemptsLeft = self::MAX_ATTEMPTS - ($attempts + 1);

            if ($attemptsLeft <= 0) {
                $errors['rate_limit'] = 'Tài khoản bị tạm khóa ' . self::LOCK_MINUTES . ' phút do quá nhiều lần sai.';
            } else {
                Flash::set('danger', "Email hoặc mật khẩu không đúng. Còn {$attemptsLeft} lần thử.");
            }
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/login.php';
            return;
        }

        // ── 4. Tài khoản bị khóa ─────────────────────────────────────
        if ((int)$user['is_locked'] === 1) {
            Flash::set('danger', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.');
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/login.php';
            return;
        }

        // ── 5. Đăng nhập thành công ───────────────────────────────────
        $this->attemptModel->reset($ip);

        // Regenerate session ID để tránh session fixation attack
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'is_locked' => $user['is_locked'],
        ];

        Flash::set('success', 'Chào mừng trở lại, ' . $user['name'] . '!');

        // Admin → admin panel; User thường → trang chủ
        if ($user['role'] === 'admin') {
            $this->redirect('admin');
        } else {
            $this->redirect('products');
        }
    }

    // ─── Register ─────────────────────────────────────────────────────────

    /**
     * GET /register - Hiển thị form đăng ký
     */
    public function registerForm(): void
    {
        Middleware::requireGuest();
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/register.php';
    }

    /**
     * POST /register - Xử lý đăng ký
     */
    public function register(): void
    {
        Middleware::requireGuest();

        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('register');
        }

        // ── Lấy và sanitize dữ liệu ────────────────────────────────────
        $name            = trim($_POST['name'] ?? '');
        $email           = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone           = trim($_POST['phone'] ?? '');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $old    = compact('name', 'email', 'phone');
        $errors = [];

        // ── Validate ───────────────────────────────────────────────────
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Họ tên phải có ít nhất 2 ký tự.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Họ tên không được quá 100 ký tự.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        } elseif ($this->userModel->findByEmail($email)) {
            $errors['email'] = 'Email này đã được sử dụng. Vui lòng dùng email khác.';
        }

        if ($phone && !preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp.';
        }

        if (!empty($errors)) {
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/register.php';
            return;
        }

        // ── Tạo tài khoản ─────────────────────────────────────────────
        $userId = $this->userModel->create($name, $email, $password, $phone);

        if (!$userId) {
            Flash::set('danger', 'Không thể tạo tài khoản. Vui lòng thử lại.');
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/register.php';
            return;
        }

        // ── Auto-login sau khi đăng ký ────────────────────────────────
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => $userId,
            'name'      => $name,
            'email'     => $email,
            'role'      => 'student',
            'is_locked' => 0,
        ];

        Flash::set('success', "Chào mừng đến SinhVienMarket, {$name}! Tài khoản đã được tạo thành công.");
        $this->redirect('products');
    }

    // ─── Logout ───────────────────────────────────────────────────────────

    public function logout(): void
    {
        // Xóa toàn bộ session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        Flash::set('success', 'Đã đăng xuất thành công. Hẹn gặp lại!');
        $this->redirect('login');
    }

    // ─── Helper private ───────────────────────────────────────────────────

    /**
     * Lấy IP thực của client (hỗ trợ proxy/load balancer)
     */
    private function getClientIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
