<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

/**
 * HomeController — Phase 8
 * Trang chủ thực sự với hero section, featured products, stats
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $productModel  = new Product();
        $categoryModel = new Category();
        $txModel       = new Transaction();
        $userModel     = new User();

        // Lấy 6 sản phẩm mới nhất (active)
        $featuredProducts = $productModel->getActive(6, 0, 0);

        // Tính giá realtime cho auction products
        foreach ($featuredProducts as &$p) {
            if ($p['type'] === 'auction' && !empty($p['started_at'])
                && $p['auction_status'] === 'active') {
                $priceData = \App\Models\Auction::calculateCurrentPrice($p);
                $p['current_price'] = $priceData['current_price'];
                $p['is_at_floor']   = $priceData['is_at_floor'];
            }
        }
        unset($p);

        // Lấy 3 sản phẩm đấu giá đang hot (active auction)
        $auctionProducts = $productModel->getActiveAuctions(3);
        foreach ($auctionProducts as &$a) {
            $priceData = \App\Models\Auction::calculateCurrentPrice($a);
            $a['current_price'] = $priceData['current_price'];
            $a['is_at_floor']   = $priceData['is_at_floor'];
        }
        unset($a);

        $categories = $categoryModel->all();

        // Stats nhỏ
        $stats = [
            'products' => $productModel->countActive(),
            'users'    => $userModel->countAll(),
            'tx'       => $txModel->countToday(),
        ];

        // Giveaways
        $giveawayModel = new \App\Models\Giveaway();
        $activeGiveaways = $giveawayModel->getActive();
        $giveaway = !empty($activeGiveaways) ? $activeGiveaways[0] : null;
        $hasJoinedGiveaway = false;
        if ($giveaway && isset($_SESSION['user'])) {
            $hasJoinedGiveaway = $giveawayModel->hasJoined($giveaway['id'], $_SESSION['user']['id']);
        }

        $this->render('home/index', [
            'title'             => 'Trang chủ',
            'featuredProducts'  => $featuredProducts,
            'auctionProducts'   => $auctionProducts,
            'categories'        => $categories,
            'stats'             => $stats,
            'giveaway'          => $giveaway,
            'hasJoinedGiveaway' => $hasJoinedGiveaway
        ]);
    }    public function dashboard(): void
    {
        \Core\Middleware::requireAuth();
        $user = $this->currentUser();

        $productModel = new Product();
        $txModel      = new Transaction();

        $products     = $productModel->getByUser($user['id']);
        $transactions = $txModel->getByUser($user['id']);

        $this->render('home/dashboard', [
            'title'        => 'Dashboard của tôi',
            'products'     => $products,
            'transactions' => $transactions,
        ]);
    }
}
