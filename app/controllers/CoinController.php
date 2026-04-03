<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\Product;

/**
 * CoinController — Quản lý Xu & Đẩy Tin (Feature 2)
 *
 * Routes:
 *   POST /coins/checkin  → nhận 10 xu check-in hàng ngày
 *   POST /coins/bump     → đẩy tin lên đầu (-50 xu)
 */
class CoinController extends Controller
{
    private const BUMP_COST      = 50;  // Xu cần để đẩy tin
    private const CHECKIN_REWARD = 10;  // Xu nhận được khi check-in
    // Thời gian khóa đẩy tin: cùng 1 bài không thể đẩy 2 lần trong 6 giờ
    private const BUMP_COOLDOWN_HOURS = 6;

    private User    $userModel;
    private Product $productModel;

    public function __construct()
    {
        $this->userModel    = new User();
        $this->productModel = new Product();
    }

    // ─── Check-in hàng ngày (+10 xu) ─────────────────────────────────────

    public function checkIn(): void
    {
        Middleware::requireAuth();

        $user = $this->currentUser();
        $userId = (int)$user['id'];

        if (!$this->userModel->canCheckin($userId)) {
            Flash::set('warning', '⏰ Bạn đã check-in hôm nay rồi! Hẹn gặp lại ngày mai.');
            $this->redirect('dashboard');
            return;
        }

        $this->userModel->doCheckin($userId);

        // Cập nhật session tiếp theo với coins mới
        $coins = $this->userModel->getCoins($userId);
        $_SESSION['user']['coins'] = $coins;

        Flash::set('success', '🪙 Check-in thành công! Bạn nhận được +' . self::CHECKIN_REWARD . ' xu. Tổng: ' . $coins . ' xu.');
        $this->redirect('dashboard');
    }

    // ─── Đẩy Tin (-50 xu) ─────────────────────────────────────────────────

    public function bump(): void
    {
        Middleware::requireAuth();

        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên hết hạn, thử lại.');
            $this->redirect('products/my');
            return;
        }

        $user      = $this->currentUser();
        $userId    = (int)$user['id'];
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            Flash::set('danger', 'Sản phẩm không hợp lệ.');
            $this->redirect('products/my');
            return;
        }

        // Kiểm tra sản phẩm thuộc về user này không
        $product = $this->productModel->findById($productId);
        if (!$product || (int)$product['user_id'] !== $userId) {
            Flash::set('danger', 'Bạn không có quyền đẩy tin này.');
            $this->redirect('products/my');
            return;
        }

        // Chỉ đẩy sản phẩm đang active
        if ($product['status'] !== 'active') {
            Flash::set('warning', 'Chỉ có thể đẩy tin đang ở trạng thái "Đang bán".');
            $this->redirect('products/my');
            return;
        }

        // Kiểm tra cooldown — không đẩy 2 lần trong vòng 6 giờ
        $lastBumped = $this->productModel->getLastBumped($productId);
        if ($lastBumped) {
            $diffHours = (time() - strtotime($lastBumped)) / 3600;
            if ($diffHours < self::BUMP_COOLDOWN_HOURS) {
                $remaining = ceil(self::BUMP_COOLDOWN_HOURS - $diffHours);
                Flash::set('warning', "⏰ Bài đăng này vừa được đẩy. Vui lòng chờ thêm {$remaining} giờ nữa.");
                $this->redirect('products/my');
                return;
            }
        }

        // Trừ xu
        if (!$this->userModel->spendCoins($userId, self::BUMP_COST)) {
            $coins = $this->userModel->getCoins($userId);
            Flash::set('danger', "💸 Không đủ xu! Bạn cần {self::BUMP_COST} xu, hiện có {$coins} xu. Hãy check-in mỗi ngày để nhận xu!");
            $this->redirect('products/my');
            return;
        }

        // Đẩy tin
        $this->productModel->bump($productId);

        // Cập nhật coins trong session
        $coins = $this->userModel->getCoins($userId);
        $_SESSION['user']['coins'] = $coins;

        Flash::set('success', '🚀 Đẩy tin thành công! Sản phẩm đã lên đầu danh sách. Còn lại: ' . $coins . ' xu.');
        $this->redirect('products/my');
    }
}
