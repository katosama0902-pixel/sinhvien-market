<?php

namespace Core;

use Config\Database;

/**
 * Base Model
 * Tất cả Model kế thừa class này để có sẵn PDO instance và các helper query
 */
abstract class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Query helpers ───────────────────────────────────────────────────────

    /**
     * Thực thi prepared statement và trả về tất cả kết quả
     *
     * @param string $sql
     * @param array  $params  Tham số binding
     * @return array<int, array<string, mixed>>
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Thực thi và trả về 1 dòng duy nhất
     *
     * @return array<string, mixed>|null
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Thực thi INSERT / UPDATE / DELETE
     * Trả về số dòng bị ảnh hưởng
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Thực thi INSERT và trả về lastInsertId
     */
    protected function insert(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Đếm số dòng thỏa mãn điều kiện
     */
    protected function count(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy PDO instance để dùng transaction thủ công
     * (dùng khi cần SELECT ... FOR UPDATE để tránh race condition)
     */
    protected function pdo(): \PDO
    {
        return $this->db;
    }
}
