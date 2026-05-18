<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Rating;
use App\Models\Banner;
use App\Models\Giveaway;
use App\Services\NotificationService;

class AdminUserController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Category $categoryModel;
    private Transaction $txModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->txModel = new Transaction();
        $this->auditModel = new AuditLog();
    }

    // ─── Dashboard ───────────────────────────────────────────────────────

    public function users(): void
    {
        Middleware::requireAdmin();
        $users = $this->userModel->all();
        $this->render('admin/users', ['title' => 'Quản lý người dùng', 'users' => $users], 'admin');
    }

    public function userDetail(): void
    {
        Middleware::requireAdmin();
        $userId = (int)($_GET['id'] ?? 0);
        $user   = $this->userModel->findByIdFull($userId);

        if (!$user) {
            Flash::set('danger', 'Người dùng không tồn tại.');
            $this->redirect('admin/users');
            return;
        }

        // Sản phẩm của user
        $products = $this->productModel->getByUser($userId);

        // Giao dịch của user (mua + bán)
        $transactions = $this->txModel->getByUser($userId);

        // Các báo cáo nhắm vào user này (Nhật ký vi phạm)
        $reportModel = new \App\Models\Report();
        $reports = $reportModel->getByTargetUser($userId);

        // Rating stats
        $ratingModel = new \App\Models\Rating();
        $ratingStats = $ratingModel->getStats($userId);

        $this->render('admin/user_detail', [
            'title'        => 'Chi tiết người dùng: ' . $user['name'],
            'profile'      => $user,
            'products'     => $products,
            'transactions' => $transactions,
            'reports'      => $reports,
            'ratingStats'  => $ratingStats,
        ], 'admin');
    }

    public function toggleUser(): void
    {
        Middleware::requireAdmin();

        // CSRF validation — dùng chuẩn từ base Controller
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Token bảo mật không hợp lệ. Vui lòng tải lại trang và thử lại.');
            $this->redirect('admin/users');
            return;
        }

        $admin  = $this->currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            Flash::set('danger', 'ID người dùng không hợp lệ.');
            $this->redirect('admin/users');
            return;
        }

        $user = $this->userModel->findById($userId);

        if (!$user) {
            Flash::set('danger', 'Không tìm thấy người dùng này.');
            $this->redirect('admin/users');
            return;
        }
        if ($user['role'] === 'admin') {
            Flash::set('danger', 'Không thể khóa tài khoản Admin.');
            $this->redirect('admin/users');
            return;
        }
        if ((int)$user['id'] === (int)$admin['id']) {
            Flash::set('danger', 'Không thể tự khóa tài khoản của mình.');
            $this->redirect('admin/users');
            return;
        }

        // ── Xử lý KHÓA ──────────────────────────────────────
        if (!$user['is_locked']) {
            $reason   = trim($_POST['lock_reason'] ?? '');
            $duration = $_POST['lock_duration'] ?? '';

            if (empty($reason)) {
                Flash::set('danger', 'Vui lòng nhập lý do khóa tài khoản.');
                $this->redirect('admin/users');
                return;
            }

            // Tính locked_until dựa trên cấp độ
            $durationMap = [
                '3days'    => '+3 days',
                '1week'    => '+1 week',
                '2weeks'   => '+2 weeks',
                '1month'   => '+1 month',
                '3months'  => '+3 months',
                '6months'  => '+6 months',
                'forever'  => null,   // vĩnh viễn
            ];

            if (!array_key_exists($duration, $durationMap)) {
                Flash::set('danger', 'Thời hạn khóa không hợp lệ.');
                $this->redirect('admin/users');
                return;
            }

            $lockedUntil = $durationMap[$duration] !== null
                ? date('Y-m-d H:i:s', strtotime($durationMap[$duration]))
                : null;

            $this->userModel->toggleLock($userId, 1, $reason, $lockedUntil);

            $note = "Khóa User: {$user['name']} | Lý do: $reason | Hạn: " . ($lockedUntil ?? 'Vĩnh viễn');
            $this->auditModel->log($admin['id'], 'lock_user', 'user', $userId, $note);

            $durationLabel = [
                '3days' => '3 ngày', '1week' => '1 tuần', '2weeks' => '2 tuần',
                '1month' => '1 tháng', '3months' => '3 tháng', '6months' => '6 tháng',
                'forever' => 'Vĩnh viễn',
            ][$duration];

            Flash::set('success', "✅ Đã khóa tài khoản {$user['name']} trong $durationLabel.");
        }
        // ── Xử lý MỞ KHÓA ───────────────────────────────────
        else {
            $this->userModel->toggleLock($userId, 0);
            $note = "Mở khóa User: {$user['name']} ({$user['email']})";
            $this->auditModel->log($admin['id'], 'unlock_user', 'user', $userId, $note);
            Flash::set('success', "✅ Đã mở khóa tài khoản {$user['name']}.");
        }

        $this->redirect('admin/users');
    }

    // ─── HỆ THỐNG STRIKE & BAN ──────────────────────────────────────────────

    public function strikeUser(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/users');
            return;
        }

        $admin = $this->currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $evidenceUrl = trim($_POST['evidence_url'] ?? '');

        if ($userId <= 0 || empty($reason)) {
            Flash::set('danger', 'Dữ liệu không hợp lệ. Vui lòng nhập lý do.');
            $this->redirect('admin/users');
            return;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            Flash::set('danger', 'Người dùng không tồn tại.');
            $this->redirect('admin/users');
            return;
        }

        if ($user['role'] === 'admin') {
            Flash::set('danger', 'Không thể cảnh cáo quản trị viên khác.');
            $this->redirect('admin/users');
            return;
        }

        // Tặng gậy
        $result = $this->userModel->addStrike($userId, $admin['id'], $reason, $evidenceUrl);

        if (!$result['success']) {
            Flash::set('danger', $result['message']);
            $this->redirect('admin/users');
            return;
        }

        // Ghi log
        $this->auditModel->log($admin['id'], 'strike_user', 'user', $userId, "Gậy thứ {$result['strike_count']}: $reason");

        // Gửi thông báo & email
        if ($result['action'] === 'warning') {
            \App\Services\NotificationService::notifyStrikeReceived($userId, $user['email'], $user['name'], $reason);
            Flash::set('warning', "✅ Đã tặng Gậy 1 cho {$user['name']}. Đã gửi email cảnh cáo.");
        } elseif ($result['action'] === 'suspend_7_days') {
            \App\Services\NotificationService::notifyAccountSuspended($userId, $user['email'], $user['name'], $reason);
            Flash::set('danger', "🚨 Đã tặng Gậy 2 cho {$user['name']}. Tài khoản bị KHÓA 7 NGÀY.");
        } else {
            \App\Services\NotificationService::notifyAccountBanned($userId, $user['email'], $user['name'], $reason);
            Flash::set('danger', "☠️ Đã tặng Gậy 3+ cho {$user['name']}. Tài khoản bị KHÓA VĨNH VIỄN.");
        }

        $this->redirect('admin/users');
    }

    public function userViolations(): void
    {
        Middleware::requireAdmin();
        $userId = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Flash::set('danger', 'Không tìm thấy người dùng.');
            $this->redirect('admin/users');
            return;
        }

        $violations = $this->userModel->getViolationHistory($userId);

        $this->render('admin/user_violations', [
            'title' => 'Lịch sử vi phạm - ' . htmlspecialchars($user['name']),
            'user' => $user,
            'violations' => $violations
        ], 'admin');
    }

    // ─── Kiểm duyệt sản phẩm ────────────────────────────────────────────

}
