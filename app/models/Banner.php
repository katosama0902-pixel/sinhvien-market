<?php

namespace App\Models;

use Core\Model;

/**
 * Banner Model - Quản lý Banner trên trang chủ
 */
class Banner extends Model
{
    /**
     * Lấy tất cả banners cho Admin
     */
    public function getAllForAdmin(): array
    {
        return $this->query('SELECT * FROM banners ORDER BY display_order ASC, created_at DESC');
    }

    /**
     * Lấy các banners đang active (Hiển thị trên trang chủ)
     */
    public function getActiveBanners(): array
    {
        return $this->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC');
    }

    /**
     * Lấy banner theo ID
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM banners WHERE id = ? LIMIT 1', [$id]);
    }

    /**
     * Tạo mới banner
     */
    public function create(string $image, string $link, string $title, int $displayOrder, int $isActive): int
    {
        return $this->insert(
            'INSERT INTO banners (title, image, link, is_active, display_order) VALUES (?, ?, ?, ?, ?)',
            [$title, $image, $link, $isActive, $displayOrder]
        );
    }

    /**
     * Cập nhật banner
     */
    public function update(int $id, string $link, string $title, int $displayOrder): void
    {
        $this->execute(
            'UPDATE banners SET title = ?, link = ?, display_order = ? WHERE id = ?',
            [$title, $link, $displayOrder, $id]
        );
    }

    /**
     * Cập nhật ảnh của banner
     */
    public function updateImage(int $id, string $image): void
    {
        $this->execute('UPDATE banners SET image = ? WHERE id = ?', [$image, $id]);
    }

    /**
     * Lưu ảnh banner vào DB (bảng banner_images) — bền qua redeploy Railway
     */
    public function saveImage(int $bannerId, string $data, string $mime): void
    {
        $this->execute(
            'INSERT INTO banner_images (banner_id, data, mime) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), mime = VALUES(mime)',
            [$bannerId, $data, $mime]
        );
    }

    /**
     * Lấy bytes ảnh + mime của banner (dùng cho route phục vụ ảnh)
     */
    public function getImageBlob(int $bannerId): ?array
    {
        return $this->queryOne(
            'SELECT data, mime FROM banner_images WHERE banner_id = ?',
            [$bannerId]
        );
    }

    /**
     * Đổi trạng thái hiển thị của banner
     */
    public function toggleStatus(int $id): void
    {
        $this->execute('UPDATE banners SET is_active = NOT is_active WHERE id = ?', [$id]);
    }

    /**
     * Xóa banner
     */
    public function delete(int $id): void
    {
        $this->execute('DELETE FROM banners WHERE id = ?', [$id]);
    }
}
