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

class AdminGiveawayController extends Controller
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

    public function giveaways(): void
    {
        Middleware::requireAdmin();
        $model = new \App\Models\Giveaway();
        $items = $model->getAll();

        $this->render('admin/giveaways', [
            'title'     => 'Quản lý Giveaways',
            'giveaways' => $items,
        ], 'admin');
    }

    public function storeGiveaway(): void
    {
        Middleware::requireAdmin();
        // NEW-005 fix: đảo pattern CSRF thành chuẩn if(!valid) early-return
        if (!$this->verifyCsrf()) {
            \Core\Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('admin/giveaways');
            return;
        }

        $data = [
            'title'       => $this->input('title'),
            'description' => $this->input('description'),
            'image'       => '',
            'end_time'    => $this->input('end_time'),
        ];

        // Đọc ảnh để lưu vào DB (bền qua redeploy Railway)
        $imageBytes = null;
        $imageMime  = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageBytes   = file_get_contents($_FILES['image']['tmp_name']);
            $imageMime    = mime_content_type($_FILES['image']['tmp_name']);
            $data['image'] = 'db'; // marker: ảnh nằm trong giveaway_images
        }

        $model = new \App\Models\Giveaway();
        $giveawayId = $model->create($data);
        if ($imageBytes !== null) {
            $model->saveImage($giveawayId, $imageBytes, $imageMime);
        }
        \Core\Flash::set('success', 'Tạo Giveaway thành công!');
        $this->redirect('admin/giveaways');
    }

    public function spinGiveaway(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $model = new \App\Models\Giveaway();
        $giveaway = $model->findById($id);

        if (!$giveaway || $giveaway['status'] === 'ended') {
            \Core\Flash::set('danger', 'Sự kiện không tồn tại hoặc đã kết thúc!');
            $this->redirect('admin/giveaways');
            return;
        }

        $participants = $model->getParticipants($id);
        
        $this->render('admin/giveaway_spin', [
            'title'        => 'Vòng Quay May Mắn - ' . htmlspecialchars($giveaway['title'], ENT_QUOTES),
            'giveaway'     => $giveaway,
            'participants' => json_encode($participants, JSON_UNESCAPED_UNICODE),
            'csrf'         => $this->csrfToken(), // BUG-005 fix: truyền token vào view đúng cách
        ], 'admin');
    }

    public function deleteGiveaway(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            \Core\Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('admin/giveaways');
            return;
        }
        $id = (int)$this->input('id');
        (new \App\Models\Giveaway())->delete($id);
        \Core\Flash::set('success', 'Đã xóa sự kiện giveaway.');
        $this->redirect('admin/giveaways');
    }

    public function spinGiveawayApi(): void
    {
        Middleware::requireAdmin();

        // BUG-005 fix: kiểm tra CSRF trước khi xử lý
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => ['code' => 'CSRF_INVALID', 'message' => 'Phiên làm việc hết hạn. Vui lòng tải lại trang.'],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id       = $this->inputInt('id');       // BUG-018 fix: dùng inputInt() cho số nguyên
        $winnerId = $this->inputInt('winner_id'); // BUG-018 fix: dùng inputInt() cho số nguyên

        if ($id > 0 && $winnerId > 0) {
            $model = new \App\Models\Giveaway();
            $model->setWinner($id, $winnerId);
            
            // Lấy thông tin người trúng giải và sự kiện để gửi thông báo
            $userModel = new \App\Models\User();
            $winner = $userModel->findById($winnerId);
            $giveaway = $model->findById($id);
            
            if ($winner && $giveaway) {
                // Sử dụng hàm notify tùy chỉnh cho Giveaway (nếu chưa có thì tạm dùng notify qua Mailer trực tiếp ở đây)
                $link = rtrim($_ENV['APP_URL'] ?? '', '/') . '/giveaways';
                (new \App\Models\Notification())->create(
                    $winnerId,
                    'giveaway_win',
                    '🎉 Xin chúc mừng, bạn đã trúng Giveaway!',
                    'Bạn là người may mắn trúng giải sự kiện "' . $giveaway['title'] . '". Vui lòng liên hệ Ban Quản Trị Ký túc xá để nhận giải.',
                    $link
                );
                
                \Core\Mailer::send(
                    $winner['email'],
                    '🎉 Bạn đã trúng giải sự kiện SinhVienMarket',
                    "Xin chào {$winner['name']},<br><br>
                    Xin chúc mừng! Bạn là người may mắn trúng giải thưởng trong sự kiện <strong>\"{$giveaway['title']}\"</strong>.<br><br>
                    Vui lòng liên hệ với Ban Quản Trị hoặc Admin tại văn phòng KTX Khu B để nhận phần thưởng của mình.<br><br>
                    <a href='{$link}'>➡️ Xem chi tiết trên trang chủ</a><br><br>
                    Trân trọng,<br>Đội ngũ SinhVienMarket"
                );
            }

            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'data'    => null,
                'message' => 'Đã lưu người trúng giải thành công!',
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => ['code' => 'VALIDATION_ERROR', 'message' => 'Dữ liệu không hợp lệ.'],
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ─── Xuất Báo Cáo (Feature 5) ──────────────────────────────────────────

}
