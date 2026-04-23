<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use Core\Mailer;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\LoginSession;
use App\Services\EmailTemplate;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS  = 5;   
    private const LOCK_MINUTES  = 15;  

    private \App\Models\User         $userModel;
    private \App\Models\LoginAttempt $attemptModel;

    public function __construct()
    {
        $this->userModel    = new User();
        $this->attemptModel = new LoginAttempt();
    }

    public function index(): void
    {
        $this->redirect('products');
    }

    public function loginRole(): void
    {
        Middleware::requireGuest();
        include APP_PATH . '/views/auth/login_role.php';
    }

    public function loginForm(): void
    {
        Middleware::requireGuest();
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/login.php';
    }

    public function login(): void
    {
        $this->processLogin('student', 'login', '/views/auth/login.php', 'products');
    }

    public function adminLoginForm(): void
    {
        Middleware::requireGuest();
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/admin_login.php';
    }

    public function adminLogin(): void
    {
        $this->processLogin('admin', 'admin-login', '/views/auth/admin_login.php', 'admin');
    }
    public function accountLocked(): void
    {
        // Nếu chưa đăng nhập hoặc không bị khóa, redirect
        if (!isset($_SESSION['user'])) {
            $this->redirect('login-role');
            return;
        }
        if (!(($_SESSION['user']['is_locked'] ?? 0) == 1)) {
            $this->redirect('');
            return;
        }

        // Làm mới thông tin khóa từ DB vào session
        $fresh = $this->userModel->findById($_SESSION['user']['id']);
        if ($fresh && (int)$fresh['is_locked'] === 1) {
            $_SESSION['user']['lock_reason']  = $fresh['lock_reason']  ?? null;
            $_SESSION['user']['locked_at']    = $fresh['locked_at']    ?? null;
            $_SESSION['user']['locked_until'] = $fresh['locked_until'] ?? null;
        } elseif ($fresh && (int)$fresh['is_locked'] === 0) {
            // Admin đã mở khóa — cập nhật session và cho vào bình thường
            $_SESSION['user']['is_locked'] = 0;
            $this->redirect('');
            return;
        }

        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        include APP_PATH . '/views/auth/account_locked.php';
    }

    private function processLogin(string $expectedRole, string $redirectBack, string $viewFile, string $redirectSuccess): void
    {
        Middleware::requireGuest();

        if (!$this->verifyCsrf()) {

            $this->redirect($redirectBack);
            return;
        }

        $ip     = $this->getClientIp();
        $errors = [];
        $old    = ['email' => $this->input('email')];

        $attempts = $this->attemptModel->getCount($ip, self::LOCK_MINUTES);
        if ($attempts >= self::MAX_ATTEMPTS) {
            $minsLeft = $this->attemptModel->getMinutesLeft($ip, self::LOCK_MINUTES);
            $errors['rate_limit'] = "Quá nhiều lần thất bại. Thử lại sau {$minsLeft} phút.";
            $csrf = $this->csrfToken();
            include APP_PATH . $viewFile;
            return;
        }

        $email    = filter_var($this->input('email'), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        if (!empty($errors)) {
            $csrf = $this->csrfToken();
            include APP_PATH . $viewFile;
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->attemptModel->increment($ip);
            $attemptsLeft = self::MAX_ATTEMPTS - ($attempts + 1);

            if ($attemptsLeft <= 0) {
                $errors['rate_limit'] = 'Tài khoản bị cấm ' . self::LOCK_MINUTES . ' phút do sai quá nhiều.';
            } else {
                Flash::set('danger', "Sai email hoặc mật khẩu. Còn {$attemptsLeft} lần thử.");
            }
            $csrf = $this->csrfToken();
            include APP_PATH . $viewFile;
            return;
        }

        if ($user['role'] !== $expectedRole) {
            Flash::set('danger', "Email này không có quyền hạn truy cập cổng đăng nhập này.");
            $csrf = $this->csrfToken();
            include APP_PATH . $viewFile;
            return;
        }

        if ((int)$user['is_locked'] === 1) {
            // Lưu thông tin khóa vào session để hiển thị trên trang thông báo
            $_SESSION['user'] = [
                'id'           => $user['id'],
                'name'         => $user['name'],
                'email'        => $user['email'],
                'avatar'       => $user['avatar']       ?? null,
                'avatar_url'   => $user['avatar_url']   ?? null,
                'role'         => $user['role'],
                'is_locked'    => 1,
                'lock_reason'  => $user['lock_reason']  ?? null,
                'locked_at'    => $user['locked_at']    ?? null,
                'locked_until' => $user['locked_until'] ?? null,
            ];
            $this->redirect('account-locked');
            return;
        }

        // Phase 11.2 - Verify OTP & 3-day policy for Admin
        $needsOtp = false;
        
        // Bỏ qua OTP vĩnh viễn cho tài khoản admin ảo này
        if ($email === 'admin@market.com' || $email === 'admin@sinhvienmarket.com') {
            $needsOtp = false;
        } else {
            if ((int)$user['is_verified'] === 0) {
                $needsOtp = true;
            } else {
                // Kiểm tra last_verified_at > 3 ngày
                $lastVerified = $user['last_verified_at'] ? strtotime($user['last_verified_at']) : 0;
                if ((time() - $lastVerified) > (3 * 24 * 60 * 60)) {
                    $needsOtp    = true;
                    $needsUnverify = true; // Đánh dấu để unverify SAU khi gửi mail thành công
                }
            }
        }

        $needsUnverify = $needsUnverify ?? false;

        if ($needsOtp) {
            $otp    = sprintf("%06d", mt_rand(100000, 999999));
            $otpExp = date('Y-m-d H:i:s', time() + 15 * 60);
            $this->userModel->updateOtp($user['id'], $otp, $otpExp);
            file_put_contents(__DIR__ . '/../../debug_otp.log', date('Y-m-d H:i:s') . " - Generated OTP $otp for user {$user['id']}\n", FILE_APPEND);

            try {
                Mailer::send($email, "Xác minh tài khoản SinhVienMarket", EmailTemplate::otpVerify($user['name'], $otp, 15));

                // BUG-11 FIX: Chỉ unverify SAU KHI gửi mail thành công
                // Nếu gửi mail lỗi, user không bị khóa khỏi tài khoản của chính mình
                if ($needsUnverify) {
                    $this->userModel->unverify($user['id']);
                }

                $_SESSION['verify_email'] = $email;
                Flash::set('info', 'Xác minh bảo mật: Chúng tôi đã gửi mã OTP vào email của bạn.');
                $this->redirect('verify-otp');
                return;

            } catch (\Throwable $e) {
                // Gửi mail thất bại → KHÔNG unverify, cho đăng nhập bình thường
                error_log('OTP mail failed for user ' . $user['id'] . ': ' . $e->getMessage());
                Flash::set('warning', 'Hệ thống không thể gửi OTP lúc này. Bạn vẫn được đăng nhập bình thường.');
                // Cho luồng tiếp tục tới phần đăng nhập bình thường bên dưới
            }
        }

        $this->attemptModel->reset($ip);
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                  => $user['id'],
            'name'                => $user['name'],
            'email'               => $user['email'],
            'avatar'              => $user['avatar']              ?? null,
            'avatar_url'          => $user['avatar_url']          ?? null,
            'role'                => $user['role'],
            'is_locked'           => $user['is_locked'],
            'is_student_verified' => $user['is_student_verified'] ?? 0,
            'coins'               => $this->userModel->getCoins($user['id']),
        ];

        // ─── Feature 3A: Ghi lịch sử đăng nhập + cảnh báo thiết bị lạ ─────────────
        try {
            $ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ip        = explode(',', $ip)[0]; // proxy trước
            $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $sessionMdl = new LoginSession();
            $isNewDevice = $sessionMdl->record($user['id'], $ip, $ua);

            if ($isNewDevice) {
                $time = date('d/m/Y H:i:s');
                $device = $_SERVER['HTTP_USER_AGENT'] ?? 'Không rõ';
                Mailer::send(
                    $user['email'],
                    'Cảnh báo bảo mật: Đăng nhập từ thiết bị lạ — SinhVienMarket',
                    EmailTemplate::newDeviceLogin($user['name'], $ip, $time, $device)
                );
            }
        } catch (\Throwable $e) {
            // Không để lỗi ghi log phá vỡ luồng đăng nhập
            error_log('LoginSession error: ' . $e->getMessage());
        }

        Flash::set('success', 'Chào mừng trở lại, ' . $user['name'] . '!');
        $this->redirect($redirectSuccess);
    }

    public function registerForm(): void
    {
        Middleware::requireGuest();
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/register.php';
    }

    public function register(): void
    {
        Middleware::requireGuest();

        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('register');
            return;
        }

        $name            = $this->input('name');
        $email           = filter_var($this->input('email'), FILTER_SANITIZE_EMAIL);
        $phone           = $this->input('phone');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        $question        = $this->input('security_question');
        $answer          = $this->input('security_answer');

        $old    = ['name' => $name, 'email' => $email, 'phone' => $phone, 'security_question' => $question, 'security_answer' => $answer];
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) { $errors['name'] = 'Họ tên từ 2 - 100 ký tự.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Email không hợp lệ.'; }
        elseif ($this->userModel->findByEmail($email)) { $errors['email'] = 'Email này đã tồn tại.'; }
        
        if ($phone && !preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) { $errors['phone'] = 'Số điện thoại không hợp lệ.'; }
        if (strlen($password) < 8) { $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.'; }
        if ($password !== $passwordConfirm) { $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp.'; }
        if (empty($question) || empty($answer)) { $errors['security_question'] = 'Vui lòng điền câu hỏi và câu trả lời bảo mật.'; }

        if (!empty($errors)) {
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/register.php';
            return;
        }

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $otpExp = date('Y-m-d H:i:s', time() + 15 * 60);

        $userId = $this->userModel->create($name, $email, $password, $phone, $question, $answer, $otp, $otpExp);

        if (!$userId) {
            Flash::set('danger', 'Không thể tạo tài khoản lúc này.');
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/register.php';
            return;
        }

        Mailer::send($email, "Xác minh tài khoản SinhVienMarket",
            EmailTemplate::otpVerify($name, $otp, 15));

        $_SESSION['verify_email'] = $email;
        Flash::set('success', "Đăng ký thành công! Vui lòng nhập mã OTP đã được gửi đến email $email.");
        $this->redirect('verify-otp');
    }

    // ─── OTP / 2FA ────────────────────────────────────────────────────────

    public function verifyOtpForm(): void
    {
        Middleware::requireGuest();
        if (empty($_SESSION['verify_email'])) {
            $this->redirect('login'); return;
        }
        $email = $_SESSION['verify_email'];
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/verify_otp.php';
    }

    public function verifyOtp(): void
    {
        Middleware::requireGuest();
        if (!$this->verifyCsrf() || empty($_SESSION['verify_email'])) {
            $this->redirect('login'); return;
        }

        // Rate limiting: tối đa 5 lần nhập sai OTP / session
        $failCount = (int)($_SESSION['otp_fail_count'] ?? 0);
        if ($failCount >= 5) {
            unset($_SESSION['verify_email'], $_SESSION['otp_fail_count'], $_SESSION['otp_resend_count'], $_SESSION['otp_resend_first_at']);
            Flash::set('danger', 'Quá nhiều lần nhập sai OTP. Phiên xác minh đã bị hủy viên an toàn. Đăng nhập lại.');
            $this->redirect('login'); return;
        }

        $email = $_SESSION['verify_email'];
        $otp   = $this->input('otp_code');
        $user  = $this->userModel->findByEmail($email);

        if (!$user || $user['otp_code'] !== $otp) {
            // Tăng bộ đếm sai
            $_SESSION['otp_fail_count'] = ($failCount + 1);
            $remaining = 5 - $_SESSION['otp_fail_count'];
            if ($remaining <= 0) {
                unset($_SESSION['verify_email'], $_SESSION['otp_fail_count']);
                Flash::set('danger', 'Quá nhiều lần nhập sai. Phiên xác minh bị hủy.');
                $this->redirect('login'); return;
            }
            Flash::set('danger', "Mã OTP không chính xác. Còn {$remaining} lần thử.");
            $this->redirect('verify-otp'); return;
        }

        if (strtotime($user['otp_expires_at']) < time()) {
            Flash::set('danger', 'Mã OTP đã hết hạn. Vui lòng nhấn Gửi lại mã.');
            $this->redirect('verify-otp'); return;
        }

        // Thành công!
        file_put_contents(__DIR__ . '/../../debug_otp.log', date('Y-m-d H:i:s') . " - Verifying OTP for user {$user['id']}\n", FILE_APPEND);
        $this->userModel->verifyOtp($user['id']);

        // Feature 1: Tự động xác thực sinh viên nếu email có domain edu
        if (User::isStudentEmail($user['email'])) {
            $this->userModel->verifyStudent($user['id']);
            $user['is_student_verified'] = 1;
        }

        unset($_SESSION['verify_email']);

        // Cho đăng nhập luôn
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'                  => $user['id'],
            'name'                => $user['name'],
            'email'               => $user['email'],
            'avatar'              => $user['avatar']              ?? null,
            'avatar_url'          => $user['avatar_url']          ?? null,
            'role'                => $user['role'],
            'is_locked'           => $user['is_locked'],
            'is_student_verified' => $user['is_student_verified'] ?? 0,
            'coins'               => $this->userModel->getCoins($user['id']),
        ];

        Flash::set('success', 'Xác minh thành công! Chào mừng bạn.');
        $this->redirect('products');
    }

    public function resendOtp(): void
    {
        Middleware::requireGuest();
        if (empty($_SESSION['verify_email'])) {
            $this->redirect('login'); return;
        }

        // Rate limiting: tối đa 3 lần gửi lại / 10 phút
        $resendCount   = (int)($_SESSION['otp_resend_count']   ?? 0);
        $resendFirstAt = (int)($_SESSION['otp_resend_first_at'] ?? 0);
        $windowSeconds = 10 * 60; // 10 phút

        // Reset cửa sổ nếu đã qua 10 phút
        if ($resendFirstAt > 0 && (time() - $resendFirstAt) >= $windowSeconds) {
            $resendCount = 0;
            $_SESSION['otp_resend_count']   = 0;
            $_SESSION['otp_resend_first_at'] = 0;
        }

        if ($resendCount >= 3) {
            $minsLeft = max(1, (int)ceil(($windowSeconds - (time() - $resendFirstAt)) / 60));
            Flash::set('danger', "Bạn đã gửi lại OTP quá nhiều lần. Vui lòng đợi {$minsLeft} phút rồi thử lại.");
            $this->redirect('verify-otp'); return;
        }
        $email = $_SESSION['verify_email'];
        $user  = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Tăng bộ đếm resend
            if ($resendCount === 0) {
                $_SESSION['otp_resend_first_at'] = time();
            }
            $_SESSION['otp_resend_count'] = $resendCount + 1;
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $otpExp = date('Y-m-d H:i:s', time() + 15 * 60);
            $this->userModel->updateOtp($user['id'], $otp, $otpExp);
            Mailer::send($email, "Xác minh tài khoản SinhVienMarket", EmailTemplate::otpVerify($user['name'] ?? 'bạn', $otp, 15));
            Flash::set('success', 'Đã gửi lại mã OTP. Vui lòng kiểm tra email.');
        }
        $this->redirect('verify-otp');
    }

    // ─── Forgot/Reset Password ─────────────────────────────────────────────

    public function forgotPasswordForm(): void
    {
        Middleware::requireGuest();
        $csrf = $this->csrfToken();
        // BUG-10 FIX: Đọc email từ session thay vì từ URL query string
        $question = '';
        if (isset($_SESSION['forgot_email'])) {
            $email = filter_var($_SESSION['forgot_email'], FILTER_SANITIZE_EMAIL);
            $user  = $this->userModel->findByEmail($email);
            if ($user && !empty($user['security_question'])) {
                $questions = [
                    'q1' => 'Tên trường cấp 1 của bạn là gì?',
                    'q2' => 'Tên thú cưng đầu tiên của bạn?',
                    'q3' => 'Bạn thân thời thơ ấu của bạn tên gì?'
                ];
                $question     = $questions[$user['security_question']] ?? 'Câu hỏi bảo mật của bạn';
                $old['email'] = $email;
            } else {
                Flash::set('danger', 'Email không tồn tại hoặc chưa cài đặt câu hỏi bảo mật.');
                unset($_SESSION['forgot_email']);
            }
        }
        include APP_PATH . '/views/auth/forgot_password.php';
    }

    public function forgotPassword(): void
    {
        Middleware::requireGuest();
        if (!$this->verifyCsrf()) {
            $this->redirect('forgot-password'); return;
        }

        $email  = $this->input('email');
        $answer = $this->input('security_answer');
        $user   = $this->userModel->findByEmail($email);

        if (!$user) {
            Flash::set('danger', 'Email không tồn tại.');
            $this->redirect('forgot-password'); return;
        }

        // Chuyển sang bước 2 (trả lời bảo mật)
        if (empty($answer)) {
            // BUG-10 FIX: Lưu email vào session, không đưa vào URL
            $_SESSION['forgot_email'] = $email;
            $this->redirect('forgot-password'); return;
        }

        // Kiểm tra câu trả lời
        if (!password_verify(trim(mb_strtolower($answer)), $user['security_answer'])) {
            Flash::set('danger', 'Câu trả lời bảo mật không chính xác!');
            $_SESSION['forgot_email'] = $email;
            $this->redirect('forgot-password'); return;
        }

        // Trả lời đúng -> Gen link reset pass
        $otp = bin2hex(random_bytes(16)); // Dùng OTP code để lưu Token Reset Pass
        $otpExp = date('Y-m-d H:i:s', time() + 15 * 60);
        $this->userModel->updateOtp($user['id'], $otp, $otpExp);

        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost/sinhvien-market';
        $resetLink = "$appUrl/reset-password?token=$otp&email=".urlencode($email);

        Mailer::send($email, "Khôi phục mật khẩu SinhVienMarket",
            EmailTemplate::resetPassword($user['name'], $resetLink));

        Flash::set('success', 'Hãy kiểm tra email để lấy liên kết đổi mật khẩu.');
        unset($_SESSION['forgot_email']); // Dọn session sau khi dùng xong
        $this->redirect('login');
    }

    public function resetPasswordForm(): void
    {
        Middleware::requireGuest();
        if (empty($_GET['token']) || empty($_GET['email'])) {
            Flash::set('danger', 'Liên kết không hợp lệ.');
            $this->redirect('login'); return;
        }
        $csrf = $this->csrfToken();
        include APP_PATH . '/views/auth/reset_password.php';
    }

    public function resetPassword(): void
    {
        Middleware::requireGuest();
        if (!$this->verifyCsrf()) {
            $this->redirect('login'); return;
        }

        $email    = $this->input('email');
        $token    = $this->input('token');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        $user = $this->userModel->findByEmail($email);
        if (!$user || $user['otp_code'] !== $token || strtotime($user['otp_expires_at']) < time()) {
            Flash::set('danger', 'Liên kết hết hạn hoặc sai.');
            $this->redirect('login'); return;
        }

        if (strlen($password) < 8 || $password !== $confirm) {
            Flash::set('danger', 'Mật khẩu không hợp lệ hoặc không khớp.');
            // Re-render
            $csrf = $this->csrfToken();
            include APP_PATH . '/views/auth/reset_password.php';
            return;
        }

        $this->userModel->updatePassword($user['id'], $password);
        Flash::set('success', 'Đổi mật khẩu thành công! Bạn có thể đăng nhập ngay.');
        $this->redirect('login');
    }

    public function logout(): void
    {
        $role = $_SESSION['user']['role'] ?? 'student';
        
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        
        session_start();
        Flash::set('success', 'Đã đăng xuất thành công.');
        
        if ($role === 'admin') {
            $this->redirect('admin-login');
        } else {
            $this->redirect('login-role');
        }
    }

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
