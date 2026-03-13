<?php

namespace App\Models;

use Core\Model;

/**
 * LoginAttempt Model - Xử lý rate limiting đăng nhập (chặn brute-force)
 * Lưu số lần thất bại theo IP, reset khi đăng nhập thành công.
 */
class LoginAttempt extends Model
{
    /**
     * Đếm số lần thất bại của IP trong N phút gần nhất
     */
    public function getCount(string $ip, int $minutes = 15): int
    {
        $result = $this->queryOne(
            "SELECT attempts FROM login_attempts
             WHERE ip = ?
               AND last_attempt >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
             LIMIT 1",
            [$ip, $minutes]
        );
        return (int)($result['attempts'] ?? 0);
    }

    /**
     * Tăng số lần thất bại cho IP (INSERT hoặc UPDATE)
     */
    public function increment(string $ip): void
    {
        $this->execute(
            "INSERT INTO login_attempts (ip, attempts, last_attempt)
             VALUES (?, 1, NOW())
             ON DUPLICATE KEY UPDATE
               attempts = IF(last_attempt < DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, attempts + 1),
               last_attempt = NOW()",
            [$ip]
        );
    }

    /**
     * Xoá/reset record của IP sau khi đăng nhập thành công
     */
    public function reset(string $ip): void
    {
        $this->execute('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
    }

    /**
     * Thời gian còn lại (phút) trước khi IP được mở lại
     */
    public function getMinutesLeft(string $ip, int $lockMinutes = 15): int
    {
        $result = $this->queryOne(
            "SELECT CEILING(? - TIMESTAMPDIFF(MINUTE, last_attempt, NOW())) AS mins_left
             FROM login_attempts WHERE ip = ? LIMIT 1",
            [$lockMinutes, $ip]
        );
        return max(1, (int)($result['mins_left'] ?? 1));
    }
}
