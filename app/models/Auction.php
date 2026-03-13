<?php

namespace App\Models;

use Core\Model;

/**
 * Auction Model - Tính toán giá đấu giá ngược realtime
 * Sẽ được triển khai đầy đủ trong Phase 4
 */
class Auction extends Model
{
    /**
     * Tính giá hiện tại theo công thức đấu giá ngược:
     * current_price = start_price - (steps_elapsed * decrease_amount)
     * Không bao giờ thấp hơn floor_price
     */
    public static function calculateCurrentPrice(array $auction): array
    {
        $startPrice     = (int)$auction['start_price'];
        $floorPrice     = (int)$auction['floor_price'];
        $decreaseAmount = (int)$auction['decrease_amount'];
        $stepMinutes    = (int)$auction['step_minutes'];
        $startedAt      = strtotime($auction['started_at']);
        $now            = time();

        $elapsedSeconds = $now - $startedAt;
        $elapsedMinutes = $elapsedSeconds / 60;
        $stepsElapsed   = (int)floor($elapsedMinutes / $stepMinutes);

        $currentPrice = $startPrice - ($stepsElapsed * $decreaseAmount);
        $currentPrice = max($currentPrice, $floorPrice); // Không thấp hơn giá sàn

        // Thời gian đến lần giảm tiếp theo (giây)
        $minutesIntoStep      = fmod($elapsedMinutes, $stepMinutes);
        $nextDropInSeconds    = (int)(($stepMinutes - $minutesIntoStep) * 60);

        return [
            'current_price'        => $currentPrice,
            'floor_price'          => $floorPrice,
            'start_price'          => $startPrice,
            'steps_elapsed'        => $stepsElapsed,
            'next_drop_in_seconds' => $nextDropInSeconds,
            'is_at_floor'          => $currentPrice <= $floorPrice,
        ];
    }

    /**
     * Lấy thông tin auction theo product_id
     */
    public function findByProduct(int $productId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM auctions WHERE product_id = ? LIMIT 1',
            [$productId]
        );
    }

    /**
     * Chốt đơn mua (với SELECT FOR UPDATE để tránh race condition)
     * Trả về true nếu thành công, false nếu đã có người mua trước
     */
    public function lockAndBuy(int $auctionId, int $buyerId, int $currentPrice): bool
    {
        $pdo = $this->pdo();
        try {
            $pdo->beginTransaction();

            // Khóa dòng auction lại để tránh race condition
            $stmt = $pdo->prepare(
                'SELECT * FROM auctions WHERE id = ? AND status = "active" FOR UPDATE'
            );
            $stmt->execute([$auctionId]);
            $auction = $stmt->fetch();

            if (!$auction) {
                // Đã có người mua trước hoặc phiên đã kết thúc
                $pdo->rollBack();
                return false;
            }

            // Cập nhật auction: đánh dấu sold
            $pdo->prepare(
                'UPDATE auctions SET status = "sold", winner_id = ?, final_price = ?, ended_at = NOW()
                 WHERE id = ?'
            )->execute([$buyerId, $currentPrice, $auctionId]);

            // Cập nhật product: đánh dấu sold
            $pdo->prepare(
                'UPDATE products SET status = "sold" WHERE id = ?'
            )->execute([$auction['product_id']]);

            // Ghi lịch sử giao dịch
            $pdo->prepare(
                'INSERT INTO transactions (product_id, buyer_id, seller_id, amount, type)
                 SELECT p.id, ?, p.user_id, ?, "auction"
                 FROM products p WHERE p.id = ?'
            )->execute([$buyerId, $currentPrice, $auction['product_id']]);

            $pdo->commit();
            return true;

        } catch (\PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
