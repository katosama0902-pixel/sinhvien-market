<?php

namespace App\Models;

use Core\Model;

/**
 * Transaction Model - Lịch sử giao dịch
 */
class Transaction extends Model
{
    /**
     * Lấy giao dịch của người mua hoặc người bán
     */
    public function getByUser(int $userId): array
    {
        return $this->query(
            'SELECT t.*, p.title AS product_title, p.image AS product_image,
                    buyer.name AS buyer_name, seller.name AS seller_name
             FROM transactions t
             JOIN products p       ON p.id = t.product_id
             JOIN users buyer      ON buyer.id = t.buyer_id
             JOIN users seller     ON seller.id = t.seller_id
             WHERE t.buyer_id = ? OR t.seller_id = ?
             ORDER BY t.created_at DESC',
            [$userId, $userId]
        );
    }

    /**
     * Lấy tất cả giao dịch (Admin)
     */
    public function getAll(string $fromDate = '', string $toDate = ''): array
    {
        $sql    = 'SELECT t.*, p.title AS product_title, buyer.name AS buyer_name,
                          seller.name AS seller_name
                   FROM transactions t
                   JOIN products p   ON p.id = t.product_id
                   JOIN users buyer  ON buyer.id = t.buyer_id
                   JOIN users seller ON seller.id = t.seller_id
                   WHERE 1';
        $params = [];

        if ($fromDate) { $sql .= ' AND DATE(t.created_at) >= ?'; $params[] = $fromDate; }
        if ($toDate)   { $sql .= ' AND DATE(t.created_at) <= ?'; $params[] = $toDate; }

        $sql .= ' ORDER BY t.created_at DESC';
        return $this->query($sql, $params);
    }

    /**
     * Đếm giao dịch hôm nay (cho admin dashboard)
     */
    public function countToday(): int
    {
        return $this->count(
            'SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = CURDATE()'
        );
    }
}
