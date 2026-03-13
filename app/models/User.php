<?php

namespace App\Models;

use Core\Model;

/**
 * User Model - Quản lý tài khoản người dùng
 * Sẽ được triển khai đầy đủ trong Phase 3
 */
class User extends Model
{
    /**
     * Tìm user theo email
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /**
     * Tìm user theo ID
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Tạo tài khoản mới
     */
    public function create(string $name, string $email, string $password, string $phone = ''): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->insert(
            'INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)',
            [$name, $email, $hash, $phone]
        );
    }

    /**
     * Lấy tất cả user (cho Admin)
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->query('SELECT id, name, email, role, is_locked, created_at FROM users ORDER BY created_at DESC');
    }

    /**
     * Toggle trạng thái khóa tài khoản
     */
    public function toggleLock(int $userId): void
    {
        $this->execute('UPDATE users SET is_locked = 1 - is_locked WHERE id = ?', [$userId]);
    }

    /**
     * Đếm tổng số user
     */
    public function countAll(): int
    {
        return $this->count('SELECT COUNT(*) FROM users WHERE role = "student"');
    }
}
