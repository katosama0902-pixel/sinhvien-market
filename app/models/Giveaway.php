<?php

namespace App\Models;

use Core\Model;

class Giveaway extends Model
{
    /**
     * Lấy các sự kiện đang diễn ra
     */
    public function getActive(): array
    {
        return $this->query(
            'SELECT * FROM giveaways WHERE status = "active" AND end_time > NOW() ORDER BY end_time ASC'
        );
    }

    /**
     * Lấy tất cả sự kiện (cho Admin)
     */
    public function getAll(): array
    {
        return $this->query('SELECT g.*, u.name as winner_name FROM giveaways g LEFT JOIN users u ON g.winner_id = u.id ORDER BY g.created_at DESC');
    }

    /**
     * Lấy chi tiết một Giveaway
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM giveaways WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Tham gia Giveaway
     */
    public function join(int $giveawayId, int $userId): bool
    {
        try {
            $this->insert(
                'INSERT INTO giveaway_participants (giveaway_id, user_id) VALUES (?, ?)',
                [$giveawayId, $userId]
            );
            return true;
        } catch (\PDOException $e) {
            // Có thể bị lỗi duplicate entry nếu đã tham gia
            return false;
        }
    }

    /**
     * Kiểm tra user đã tham gia chưa
     */
    public function hasJoined(int $giveawayId, int $userId): bool
    {
        $count = $this->count('SELECT COUNT(*) FROM giveaway_participants WHERE giveaway_id = ? AND user_id = ?', [$giveawayId, $userId]);
        return $count > 0;
    }

    /**
     * Đếm số lượt người tham gia
     */
    public function countParticipants(int $giveawayId): int
    {
        return $this->count('SELECT COUNT(*) FROM giveaway_participants WHERE giveaway_id = ?', [$giveawayId]);
    }

    /**
     * Lấy danh sách người tham gia
     */
    public function getParticipants(int $giveawayId): array
    {
        return $this->query(
            'SELECT u.id, u.name, u.email, u.avatar, gp.joined_at 
             FROM giveaway_participants gp 
             JOIN users u ON gp.user_id = u.id 
             WHERE gp.giveaway_id = ? 
             ORDER BY gp.joined_at ASC',
            [$giveawayId]
        );
    }

    /**
     * Quay số chọn winner
     */
    public function setWinner(int $giveawayId, int $winnerId): void
    {
        $this->execute('UPDATE giveaways SET winner_id = ?, status = "ended" WHERE id = ?', [$winnerId, $giveawayId]);
    }

    /**
     * Tạo Giveaway mới
     */
    public function create(array $data): int
    {
        return $this->insert(
            'INSERT INTO giveaways (title, description, image, end_time) VALUES (?, ?, ?, ?)',
            [$data['title'], $data['description'], $data['image'], $data['end_time']]
        );
    }
}
