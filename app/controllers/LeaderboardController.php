<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

/**
 * LeaderboardController — Bảng Xếp Hạng Top Seller
 */
class LeaderboardController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Trang Bảng Xếp Hạng
     */
    public function index(): void
    {
        $topSellers = $this->userModel->getLeaderboard(10);
        $myRank     = 0;
        $myStats    = null;

        // Nếu đang đăng nhập, tìm rank của bản thân
        if (!empty($_SESSION['user']['id'])) {
            $myRank  = $this->userModel->getMyRank((int)$_SESSION['user']['id']);
            // Lấy stats của bản thân từ top list hoặc tính riêng
            foreach ($topSellers as $s) {
                if ((int)$s['id'] === (int)$_SESSION['user']['id']) {
                    $myStats = $s;
                    break;
                }
            }
            // Nếu không nằm trong top 10, vẫn query stats
            if ($myStats === null) {
                $allStats = $this->userModel->getLeaderboard(9999);
                foreach ($allStats as $s) {
                    if ((int)$s['id'] === (int)$_SESSION['user']['id']) {
                        $myStats = $s;
                        break;
                    }
                }
            }
        }

        $this->render('leaderboard/index', [
            'title'      => 'Bảng Xếp Hạng',
            'topSellers' => $topSellers,
            'myRank'     => $myRank,
            'myStats'    => $myStats,
            'appUrl'     => rtrim($_ENV['APP_URL'] ?? '', '/'),
        ]);
    }
}
