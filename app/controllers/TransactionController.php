<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;

/**
 * TransactionController - Lịch sử giao dịch mua/bán
 * Sẽ được triển khai đầy đủ trong Phase 5
 */
class TransactionController extends Controller
{
    public function history(): void
    {
        Middleware::requireAuth();
        // TODO: Phase 5
        $this->render('transactions/history', [
            'title'        => 'Lịch sử giao dịch',
            'transactions' => [],
        ]);
    }
}
