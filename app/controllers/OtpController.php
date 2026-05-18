<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use Core\Mailer;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Services\NotificationService;
use App\Services\EmailTemplate;

class OtpController extends Controller
{
    private User         $userModel;
    private LoginAttempt  $attemptModel;

    public function __construct()
    {
        $this->userModel    = new User();
        $this->attemptModel = new LoginAttempt();
    }

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
        // BUG-011 fix: ghi log vào storage/logs/ (blocked by .htaccess), không ghi OTP code thực
        file_put_contents(__DIR__ . '/../../storage/logs/otp_debug.log', date('Y-m-d H:i:s') . " - OTP verified for user {$user['id']}\n", FILE_APPEND);
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
            $otp = sprintf("%06d", random_int(100000, 999999)); // V4-001 fix: mt_rand → random_int (CSPRNG)
            $otpExp = date('Y-m-d H:i:s', time() + 15 * 60);
            $this->userModel->updateOtp($user['id'], $otp, $otpExp);
            Mailer::send($email, "Xác minh tài khoản SinhVienMarket", EmailTemplate::otpVerify($user['name'] ?? 'bạn', $otp, 15));
            Flash::set('success', 'Đã gửi lại mã OTP. Vui lòng kiểm tra email.');
        }
        $this->redirect('verify-otp');
    }

    // ─── Forgot/Reset Password ─────────────────────────────────────────────

}
