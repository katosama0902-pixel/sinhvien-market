<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use Core\Mailer;
use App\Models\User;
use App\Services\EmailTemplate;
use App\Services\NotificationService;

class PasswordResetController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

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

}
