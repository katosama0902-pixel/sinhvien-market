<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;

/**
 * AuctionController - Xử lý đấu giá ngược và API cập nhật giá realtime
 * Sẽ được triển khai đầy đủ trong Phase 4/5
 */
class AuctionController extends Controller
{
    /** API: Trả về giá hiện tại của phiên đấu giá (dùng cho polling JS) */
    public function apiPrice(): void
    {
        $auctionId = $this->queryInt('id');
        // TODO: Phase 4 — tính giá realtime
        $this->json([
            'current_price'       => 0,
            'floor_price'         => 0,
            'status'              => 'active',
            'next_drop_in_seconds'=> 0,
            'message'             => 'TODO: implement in Phase 4',
        ]);
    }

    /** POST: Chốt đơn mua với xử lý Race Condition */
    public function buy(): void
    {
        Middleware::requireAuth();
        // TODO: Phase 4 — SELECT FOR UPDATE + transaction
        $this->redirect('products');
    }
}
