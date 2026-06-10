<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\LoginSession;

/**
 * ProfileController — Hồ sơ cá nhân người dùng
 */
class ProfileController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ─── Hiển thị trang hồ sơ ────────────────────────────────────────────────

    public function show(): void
    {
        Middleware::requireAuth();
        $sessionUser = $this->currentUser();
        $user = $this->userModel->findById((int)$sessionUser['id']);

        // Sync avatar to session if it changed or was missing
        if ($user) {
            $_SESSION['user']['avatar']     = $user['avatar'];
            $_SESSION['user']['avatar_url'] = $user['avatar_url'];
        }

        // Phân rã cấp bậc người bán
        $rank = ['name' => 'Tân binh', 'color' => 'secondary', 'icon' => 'person'];
        if ($user) {
            $rank = $this->userModel->getRankLevel($user['id']);
        }

        // Lịch sử đăng nhập (Feature 3B)
        $loginHistory = [];
        try {
            $loginHistory = (new LoginSession())->getByUser((int)$sessionUser['id'], 15);
        } catch (\Throwable $e) {
            // Bảng chưa tồn tại — bỏ qua
        }

        // Lấy thông tin tài khoản ngân hàng (VietQR)
        $bankAccount = null;
        try {
            $bankAccount = (new \App\Models\BankAccount())->findByUserId((int)$sessionUser['id']);
        } catch (\Throwable $e) {}

        $this->render('profile/edit', [
            'title'        => 'Hồ sơ của tôi',
            'user'         => $user,
            'rank'         => $rank,
            'loginHistory' => $loginHistory,
            'bankAccount'  => $bankAccount,
        ]);
    }

    // ─── Cập nhật thông tin cá nhân ──────────────────────────────────────────

    public function update(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('profile');
            return;
        }

        $sessionUser = $this->currentUser();
        $userId = (int)$sessionUser['id'];

        $name = trim($this->input('name'));
        if (empty($name)) {
            Flash::set('danger', 'Họ tên không được để trống.');
            $this->redirect('profile');
            return;
        }

        $this->userModel->updateProfile($userId, [
            'name'              => $name,
            'phone'             => trim($this->input('phone')),
            'university'        => trim($this->input('university')),
            'student_id'        => trim($this->input('student_id')),
            'dormitory_address' => trim($this->input('dormitory_address')),
            'social_contact'    => trim($this->input('social_contact')),
            'bio'               => trim($this->input('bio')),
            'available_time'    => trim($this->input('available_time')),
        ]);

        // Cập nhật lại session
        $_SESSION['user']['name'] = $name;

        Flash::set('success', '✅ Cập nhật hồ sơ thành công!');
        $this->redirect('profile');
    }

    // ─── Đổi mật khẩu ────────────────────────────────────────────────────────

    public function changePassword(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('profile?tab=security');
            return;
        }

        $sessionUser = $this->currentUser();
        $userId = (int)$sessionUser['id'];

        $user = $this->userModel->findById($userId);
        $oldPass = $this->input('old_password');
        $newPass = $this->input('new_password');
        $confirm = $this->input('confirm_password');

        if (!password_verify($oldPass, $user['password'])) {
            Flash::set('danger', '❌ Mật khẩu hiện tại không đúng.');
            $this->redirect('profile?tab=security');
            return;
        }

        if (strlen($newPass) < 8) {
            Flash::set('danger', '❌ Mật khẩu mới phải có ít nhất 8 ký tự.');
            $this->redirect('profile?tab=security');
            return;
        }

        // BUG-019 fix: đồng bộ policy với đăng ký — yêu cầu chữ hoa + số
        if (!preg_match('/[A-Z]/', $newPass)) {
            Flash::set('danger', '❌ Mật khẩu phải có ít nhất 1 chữ hoa.');
            $this->redirect('profile?tab=security');
            return;
        }
        if (!preg_match('/[0-9]/', $newPass)) {
            Flash::set('danger', '❌ Mật khẩu phải có ít nhất 1 chữ số.');
            $this->redirect('profile?tab=security');
            return;
        }

        if ($newPass !== $confirm) {
            Flash::set('danger', '❌ Mật khẩu xác nhận không khớp.');
            $this->redirect('profile?tab=security');
            return;
        }

        $this->userModel->updatePassword($userId, $newPass);
        Flash::set('success', '🔒 Đổi mật khẩu thành công!');
        $this->redirect('profile?tab=security');
    }

    // ─── Upload ảnh đại diện ──────────────────────────────────────────────────

    public function uploadAvatar(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('profile');
            return;
        }

        $sessionUser = $this->currentUser();
        $userId = (int)$sessionUser['id'];

        if (empty($_FILES['avatar']['tmp_name'])) {
            Flash::set('danger', 'Vui lòng chọn ảnh để tải lên.');
            $this->redirect('profile');
            return;
        }

        $file     = $_FILES['avatar'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            Flash::set('danger', 'Chỉ chấp nhận ảnh JPG, PNG, WEBP, GIF.');
            $this->redirect('profile');
            return;
        }

        // BUG-A04 fix: validate MIME type thực tế (không tin extension từ tên file)
        $actualMime  = mime_content_type($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($actualMime, $allowedMime, true)) {
            Flash::set('danger', 'File không phải ảnh hợp lệ. Vui lòng chọn ảnh JPG, PNG, WEBP, GIF.');
            $this->redirect('profile');
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB
            Flash::set('danger', 'Ảnh không được vượt quá 10MB.');
            $this->redirect('profile');
            return;
        }

        // Lưu avatar vào DB (bền qua redeploy Railway) thay vì ghi file
        $this->userModel->saveAvatarImage($userId, file_get_contents($file['tmp_name']), $actualMime);
        $this->userModel->changeAvatar($userId, 'db'); // 'db' = marker: avatar nằm trong DB

        // Sync to session immediately
        $_SESSION['user']['avatar'] = 'db';

        Flash::set('success', '🖼️ Cập nhật ảnh đại diện thành công!');
        $this->redirect('profile');
    }

    /** Phục vụ avatar từ DB (public — avatar hiển thị khắp nơi). */
    public function avatar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $this->serveImageBlob($id > 0 ? $this->userModel->getAvatarBlob($id) : null);
    }

    // ─── Lưu thông tin ngân hàng ──────────────────────────────────────────────

    public function saveBankAccount(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Lỗi bảo mật (CSRF).');
            $this->redirect('profile?tab=bank'); return;
        }

        $bankName = trim($this->input('bank_name'));
        $bankCode = trim($this->input('bank_code'));
        $accountNo = trim($this->input('account_no'));
        $accountName = trim($this->input('account_name'));

        if (empty($bankName) || empty($bankCode) || empty($accountNo) || empty($accountName)) {
            Flash::set('danger', 'Vui lòng điền đầy đủ thông tin tài khoản ngân hàng.');
            $this->redirect('profile?tab=bank'); return;
        }

        try {
            $bankModel = new \App\Models\BankAccount();
            $bankModel->save((int)$this->currentUser()['id'], $bankName, $bankCode, $accountNo, $accountName);
            Flash::set('success', '✅ Đã lưu thông tin tài khoản ngân hàng. Giờ đây bạn có thể nhận thanh toán qua VietQR!');
        } catch (\Exception $e) {
            Flash::set('danger', 'Lỗi khi lưu thông tin. Vui lòng thử lại sau.');
        }

        $this->redirect('profile?tab=bank');
    }

    public function saveSecurityQuestion(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Lỗi bảo mật (CSRF).');
            $this->redirect('profile'); return;
        }
        $question = $this->input('security_question');
        $answer   = trim($_POST['security_answer'] ?? '');
        if (!in_array($question, ['q1', 'q2', 'q3'], true) || $answer === '') {
            Flash::set('danger', 'Vui lòng chọn câu hỏi và nhập câu trả lời.');
            $this->redirect('profile'); return;
        }
        $this->userModel->setSecurityQuestion((int)$this->currentUser()['id'], $question, $answer);
        Flash::set('success', 'Đã cập nhật câu hỏi bảo mật. Giờ bạn có thể dùng nó để khôi phục mật khẩu.');
        $this->redirect('profile');
    }
}

