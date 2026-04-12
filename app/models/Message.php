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
    public function updateOfferStatus(int $messageId, string $status): void
    {
        // status có thể là 'accepted' hoặc 'rejected'
        $this->execute(
            "UPDATE messages SET offer_status = ? WHERE id = ? AND msg_type = 'offer'",
            [$status, $messageId]
        );
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
