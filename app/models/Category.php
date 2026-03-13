<?php

namespace App\Models;

use Core\Model;

/**
 * Category Model
 */
class Category extends Model
{
    public function all(): array
    {
        return $this->query('SELECT * FROM categories ORDER BY name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM categories WHERE id = ? LIMIT 1', [$id]);
    }

    public function create(string $name, string $slug, string $icon = ''): int
    {
        return $this->insert(
            'INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)',
            [$name, $slug, $icon]
        );
    }

    public function update(int $id, string $name, string $slug, string $icon = ''): void
    {
        $this->execute(
            'UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?',
            [$name, $slug, $icon, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM categories WHERE id = ?', [$id]);
    }

    /** Tạo slug từ tên danh mục */
    public static function makeSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        return trim($slug, '-');
    }
}
