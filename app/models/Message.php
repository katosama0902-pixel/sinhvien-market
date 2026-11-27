<?php

namespace App\Models;

use Core\Model;

/**
 * Message Model — Quản lý tin nhắn trong các cuộc hội thoại
 */
class Message extends Model
{
    /**
     * Lấy tất cả tin nhắn của một cuộc hội thoại (sắp xếp từ cũ đến mới)
     */
    public function getByConversation(int $convId): array
    {
        return $this->query(
            'SELECT m.*, u.name AS sender_name
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ?
             ORDER BY m.created_at ASC',
            [$convId]
        );
    }

    /**
     * Lấy tin nhắn mới hơn một ID nhất định (dùng cho polling)
     */
    public function getAfter(int $convId, int $afterId): array
    {
        return $this->query(
            'SELECT m.*, u.name AS sender_name
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC',
            [$convId, $afterId]
        );
    }

    /**
     * Gửi tin nhắn mới
     */
    public function send(int $convId, int $senderId, string $body): int
    {
        return $this->insert(
            'INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)',
            [$convId, $senderId, trim($body)]
        );
    }

    /**
     * Gửi tin nhắn Offer (Hệ thống Mặc cả)
     */
    public function sendOffer(int $convId, int $senderId, int $offerPrice): int
    {
        return $this->insert(
            "INSERT INTO messages (conversation_id, sender_id, msg_type, offer_status, offer_price, body) VALUES (?, ?, 'offer', 'pending', ?, ?)",
            [$convId, $senderId, $offerPrice, "Đã gửi đề nghị trả giá: " . number_format($offerPrice) . "đ"]
        );
    }

    /**
     * Cập nhật trạng thái của Offer
     */
    /**
     * Chốt một đề nghị giá đang chờ ('accepted' | 'rejected').
     * Chỉ áp dụng cho đề nghị thuộc đúng hội thoại và chưa được xử lý;
     * trả về false nếu không có đề nghị nào như vậy.
     */
    public function updateOfferStatus(int $messageId, int $conversationId, string $status): bool
    {
        return $this->execute(
            "UPDATE messages SET offer_status = ?
             WHERE id = ? AND conversation_id = ? AND msg_type = 'offer' AND offer_status = 'pending'",
            [$status, $messageId, $conversationId]
        ) === 1;
    }

    /** Giá đã được người bán chấp nhận gần nhất cho cặp (sản phẩm, người mua), hoặc null. */
    public function acceptedOfferPrice(int $productId, int $buyerId): ?int
    {
        $row = $this->queryOne(
            "SELECT m.offer_price FROM messages m
             JOIN conversations c ON c.id = m.conversation_id
             WHERE c.product_id = ? AND c.buyer_id = ? AND m.sender_id = c.buyer_id
               AND m.msg_type = 'offer' AND m.offer_status = 'accepted'
             ORDER BY m.id DESC LIMIT 1",
            [$productId, $buyerId]
        );
        return $row && (int)$row['offer_price'] > 0 ? (int)$row['offer_price'] : null;
    }

    /**
     * Đánh dấu tất cả tin nhắn của đối phương trong cuộc hội thoại là đã đọc
     */
    public function markAsRead(int $convId, int $currentUserId): void
    {
        $this->execute(
            'UPDATE messages SET is_read = 1
             WHERE conversation_id = ? AND sender_id != ? AND is_read = 0',
            [$convId, $currentUserId]
        );
    }

    /**
     * Lấy thời gian của tin nhắn gần nhất từ một người dùng cụ thể trong hội thoại.
     * Tùy chọn: chỉ tính các tin nhắn TRƯỚC một ID nhất định (để chống dội lệnh gõ liên tiếp).
     */
    public function getLastMessageTimeBySender(int $convId, int $senderId, ?int $beforeId = null): ?string
    {
        if ($beforeId) {
            $sql = 'SELECT created_at FROM messages WHERE conversation_id = ? AND sender_id = ? AND id < ? ORDER BY id DESC LIMIT 1';
            $result = $this->queryOne($sql, [$convId, $senderId, $beforeId]);
        } else {
            $sql = 'SELECT created_at FROM messages WHERE conversation_id = ? AND sender_id = ? ORDER BY id DESC LIMIT 1';
            $result = $this->queryOne($sql, [$convId, $senderId]);
        }
        return $result ? $result['created_at'] : null;
    }
}
