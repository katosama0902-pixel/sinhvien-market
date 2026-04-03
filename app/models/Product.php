<?php

namespace App\Models;

use Core\Model;

/**
 * Product Model
 * Sẽ được triển khai đầy đủ trong Phase 4
 */
class Product extends Model
{
    /**
     * Lấy 1 bản ghi bằng ID cơ bản
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Lấy danh sách sản phẩm đang active (có phân trang)
     */
    public function getActive(int $limit = 12, int $offset = 0, int $categoryId = 0, string $condition = '', int $priceMin = 0, int $priceMax = 0): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, u.name AS seller_name,
                       u.is_student_verified AS seller_verified,
                       a.id AS auction_id, a.start_price, a.floor_price,
                       a.decrease_amount, a.step_minutes, a.started_at, a.status AS auction_status
                FROM products p
                JOIN categories c  ON c.id = p.category_id
                JOIN users u       ON u.id = p.user_id
                LEFT JOIN auctions a ON a.product_id = p.id
                WHERE p.status = "active"';
        $params = [];

        if ($categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($condition !== '' && in_array($condition, ['new', 'like_new', 'used', 'worn'])) {
            $sql .= ' AND p.condition = ?';
            $params[] = $condition;
        }
        if ($priceMin > 0) {
            $sql .= ' AND p.price >= ?';
            $params[] = $priceMin;
        }
        if ($priceMax > 0) {
            $sql .= ' AND p.price <= ?';
            $params[] = $priceMax;
        }

        // Sắp xếp theo thời điểm mới nhất giữa created_at và bumped_at
        $sql .= ' ORDER BY GREATEST(p.created_at, COALESCE(p.bumped_at, \'2000-01-01\')) DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Tìm kiếm FULLTEXT theo từ khóa
     */
    public function search(string $keyword, int $categoryId = 0, int $limit = 12, int $offset = 0, string $condition = '', int $priceMin = 0, int $priceMax = 0): array
    {
        $sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name,
                       u.is_student_verified AS seller_verified,
                       a.id AS auction_id, a.start_price, a.floor_price,
                       a.decrease_amount, a.step_minutes, a.started_at, a.status AS auction_status,
                       MATCH(p.title, p.description) AGAINST(? IN NATURAL LANGUAGE MODE) AS score
                FROM products p
                JOIN categories c  ON c.id = p.category_id
                JOIN users u       ON u.id = p.user_id
                LEFT JOIN auctions a ON a.product_id = p.id
                WHERE MATCH(p.title, p.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                  AND p.status = 'active'";

        $params = [$keyword, $keyword];

        if ($categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($condition !== '' && in_array($condition, ['new', 'like_new', 'used', 'worn'])) {
            $sql .= ' AND p.condition = ?';
            $params[] = $condition;
        }
        if ($priceMin > 0) {
            $sql .= ' AND p.price >= ?';
            $params[] = $priceMin;
        }
        if ($priceMax > 0) {
            $sql .= ' AND p.price <= ?';
            $params[] = $priceMax;
        }

        $sql .= ' ORDER BY score DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Lấy chi tiết sản phẩm kèm thông tin auction (nếu có)
     */
    public function findWithAuction(int $id): ?array
    {
        return $this->queryOne(
            'SELECT p.*, c.name AS category_name, u.name AS seller_name, u.phone AS seller_phone,
                    a.start_price, a.floor_price, a.decrease_amount, a.step_minutes,
                    a.started_at, a.status AS auction_status, a.id AS auction_id
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN users u      ON u.id = p.user_id
             LEFT JOIN auctions a ON a.product_id = p.id
             WHERE p.id = ? LIMIT 1',
        [$id]
        );
    }

    /**
     * Lấy sản phẩm của một user cụ thể
     */
    public function getByUser(int $userId): array
    {
        return $this->query(
            'SELECT p.*, c.name AS category_name,
                    a.start_price, a.floor_price, a.status AS auction_status
             FROM products p
             JOIN categories c  ON c.id = p.category_id
             LEFT JOIN auctions a ON a.product_id = p.id
             WHERE p.user_id = ? ORDER BY p.created_at DESC',
        [$userId]
        );
    }

    /**
     * Tạo bài đăng sản phẩm mới
     */
    public function create(array $data): int
    {
        return $this->insert(
            'INSERT INTO products (user_id, category_id, title, description, image, type, price, `condition`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $data['user_id'],
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['image'] ?? null,
            $data['type'],
            $data['price'] ?? null,
            $data['condition'] ?? 'used',
        ]
        );
    }

    /**
     * Đẩy tin lên đầu: cập nhật bumped_at = NOW()
     */
    public function bump(int $productId): void
    {
        $this->execute(
            'UPDATE products SET bumped_at = NOW() WHERE id = ?',
            [$productId]
        );
    }

    /**
     * Lấy thời điểm được đẩy cuối cùng của một sản phẩm
     */
    public function getLastBumped(int $productId): ?string
    {
        $row = $this->queryOne('SELECT bumped_at FROM products WHERE id = ?', [$productId]);
        return $row['bumped_at'] ?? null;
    }

    /**
     * Cập nhật trạng thái sản phẩm
     */
    public function updateStatus(int $id, string $status): void
    {
        $this->execute('UPDATE products SET status = ? WHERE id = ?', [$status, $id]);
    }

    /**
     * Đếm tổng sản phẩm đang active
     */
    public function countActive(): int
    {
        return $this->count('SELECT COUNT(*) FROM products WHERE status = "active"');
    }

    /**
     * Lấy sản phẩm cần kiểm duyệt (admin)
     */
    public function getPending(): array
    {
        return $this->query(
            'SELECT p.*, c.name AS category_name, u.name AS seller_name, u.email AS seller_email
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN users u ON u.id = p.user_id
             WHERE p.status = "pending" ORDER BY p.created_at ASC'
        );
    }

    /**
     * Lấy tất cả sản phẩm (Admin — kể cả pending, cancelled)
     */
    public function getAllForAdmin(): array
    {
        return $this->query(
            'SELECT p.*, c.name AS category_name, u.name AS seller_name, u.email AS seller_email
             FROM products p
             JOIN categories c ON c.id = p.category_id
             JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT 300'
        );
    }

    /**
     * Lấy các phiên đấu giá ngược đang active (cho trang chủ)
     */
    public function getActiveAuctions(int $limit = 6): array
    {
        return $this->query(
            'SELECT p.*, c.name AS category_name, u.name AS seller_name,
                    a.id AS auction_id, a.start_price, a.floor_price,
                    a.decrease_amount, a.step_minutes, a.started_at, a.status AS auction_status
             FROM products p
             JOIN categories c  ON c.id = p.category_id
             JOIN users u       ON u.id = p.user_id
             JOIN auctions a    ON a.product_id = p.id
             WHERE p.status = "active" AND a.status = "active"
             ORDER BY p.created_at DESC
             LIMIT ?',
        [$limit]
        );
    }
}
