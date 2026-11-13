<?php

namespace App\Models;

use Core\Model;

class ResidenceNotice extends Model
{
    /**
     * Rules exposed to the local assistant. Select only rule content, never author
     * identity or other resident data, and apply the same audience filter as notices.
     */
    public function activeRulesForAssistant(?int $userId): array
    {
        $sql = "SELECT n.id, n.title, n.body
                FROM residence_notices n
                WHERE n.notice_type = 'rule' AND n.status = 'published'
                  AND (n.starts_at IS NULL OR n.starts_at <= CURRENT_TIMESTAMP)
                  AND (n.ends_at IS NULL OR n.ends_at >= CURRENT_TIMESTAMP)";
        $params = [];
        if ($userId !== null) {
            $sql .= " AND (n.audience = 'all' OR EXISTS (
                SELECT 1 FROM residence_contracts rc
                WHERE rc.user_id = ? AND rc.status = 'active'
                  AND rc.start_date <= CURRENT_DATE AND rc.end_date >= CURRENT_DATE
            ))";
            $params[] = $userId;
        }
        $sql .= ' ORDER BY n.id DESC LIMIT 100';
        return $this->query($sql, $params);
    }

    public function list(bool $includeArchived = false, ?int $userId = null): array
    {
        $sql = 'SELECT n.*, u.name AS author_name FROM residence_notices n
                JOIN users u ON u.id = n.created_by';
        if (!$includeArchived) {
            $sql .= " WHERE n.status = 'published'
                      AND (n.starts_at IS NULL OR n.starts_at <= CURRENT_TIMESTAMP)
                      AND (n.ends_at IS NULL OR n.ends_at >= CURRENT_TIMESTAMP)";
        }
        if ($userId !== null) {
            $sql .= ($includeArchived ? ' WHERE ' : ' AND ')
                . "(n.audience = 'all' OR EXISTS (
                    SELECT 1 FROM residence_contracts rc
                    WHERE rc.user_id = ? AND rc.status = 'active'
                      AND rc.start_date <= CURRENT_DATE AND rc.end_date >= CURRENT_DATE
                ))";
            $params = [$userId];
        } else {
            $params = [];
        }
        $sql .= ' ORDER BY COALESCE(n.starts_at, n.created_at) DESC, n.id DESC';
        return $this->query($sql, $params);
    }

    public function create(
        string $type,
        string $title,
        string $body,
        string $audience,
        ?string $startsAt,
        ?string $endsAt,
        int $createdBy
    ): int {
        return $this->insert(
            "INSERT INTO residence_notices
                (notice_type, title, body, audience, starts_at, ends_at, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, 'published', ?)",
            [$type, $title, $body, $audience, $startsAt, $endsAt, $createdBy]
        );
    }

    public function archive(int $id): bool
    {
        return $this->execute(
            "UPDATE residence_notices SET status = 'archived' WHERE id = ? AND status = 'published'",
            [$id]
        ) > 0;
    }
}
