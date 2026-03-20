<?php
namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\Giveaway;

class GiveawayController extends Controller
{
    public function join(): void
    {
        Middleware::requireAuth();
        header('Content-Type: application/json');

        if (!$this->verifyCsrf()) {
            echo json_encode(['status' => 'error', 'msg' => 'Token không hợp lệ.']);
            return;
        }

        $user = $this->currentUser();
        $giveawayId = (int)$this->input('giveaway_id');

        // Phải là user verified mới được tham gia
        if (empty($user['is_verified'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Tài khoản chưa được xác minh (Email/SDT) nên không thể tham gia quay thưởng.']);
            return;
        }

        $model = new Giveaway();
        $ga = $model->findById($giveawayId);

        if (!$ga || $ga['status'] !== 'active' || strtotime($ga['end_time']) < time()) {
            echo json_encode(['status' => 'error', 'msg' => 'Sự kiện đã kết thúc hoặc không tồn tại.']);
            return;
        }

        if ($model->join($giveawayId, $user['id'])) {
            echo json_encode(['status' => 'success', 'msg' => 'Đăng ký tham gia thành công! Chúc bạn may mắn.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Bạn đã tham gia sự kiện này rồi!']);
        }
    }
}
