<?php

namespace App\Models;

use Core\Model;

/**
 * Report Model - Quản lý tố cáo vi phạm
 */
class Report extends Model
{
    /**
     * Lấy tất cả báo cáo (Dành cho Admin)
     */
    public function getAll(string $status = ''): array
    {
        $sql = 'SELECT r.*, 
                       reporter.name AS reporter_name, 
                       target.name AS target_name,
                       p.title AS product_title
                FROM reports r
                JOIN users reporter ON r.reporter_id = reporter.id
                LEFT JOIN users target ON r.target_user_id = target.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE 1=1';

        $params = [];
        if ($status !== '') {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY r.created_at DESC';

        return $this->query($sql, $params);
    }

    /**
     * Đếm số báo cáo mới (chưa xử lý)
     */
    public function countPending(): int
    {
        return $this->count('SELECT COUNT(*) FROM reports WHERE status = "pending"');
    }

    /**
     * Tìm báo cáo theo ID
     */
    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM reports WHERE id = ?', [$id]);
    }

    /**
     * Tạo báo cáo mới
     */
    public function createReport(
        int $reporterId, 
        int $targetUserId, 
        int $productId, 
        string $reason, 
        string $description
    ): int {
        return $this->insert(
            'INSERT INTO reports (reporter_id, target_user_id, product_id, reason, description, status) 
             VALUES (?, ?, ?, ?, ?, "pending")',
            [
                $reporterId, 
                $targetUserId > 0 ? $targetUserId : null, 
                $productId > 0 ? $productId : null, 
                $reason, 
                $description
            ]
        );
    }

    /**
     * Lấy các báo cáo nhắm vào một người dùng cụ thể
     */
    public function getByTargetUser(int $userId): array
    {
        return $this->query(
            'SELECT r.*, 
                    reporter.name AS reporter_name, 
                    p.title AS product_title
             FROM reports r
             JOIN users reporter ON r.reporter_id = reporter.id
             LEFT JOIN products p ON r.product_id = p.id
             WHERE r.target_user_id = ?
             ORDER BY r.created_at DESC',
            [$userId]
        );
    }

    /**
     * Cập nhật trạng thái báo cáo (Dành cho Admin)
     */
    public function updateStatus(int $id, string $status, ?string $adminNote = null, ?string $evidenceUrl = null): void
    {
        $sql = 'UPDATE reports SET status = ?';
        $params = [$status];

        if ($adminNote !== null) {
            $sql .= ', admin_note = ?';
            $params[] = $adminNote;
        }

        if ($evidenceUrl !== null) {
            $sql .= ', evidence_url = ?';
            $params[] = $evidenceUrl;
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $this->execute($sql, $params);
    }

    /** Lưu ảnh bằng chứng vào DB (report_evidence) — bền qua redeploy Railway */
    public function saveEvidenceImage(int $reportId, string $data, string $mime): void
    {
        $this->execute(
            'INSERT INTO report_evidence (report_id, data, mime) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), mime = VALUES(mime)',
            [$reportId, $data, $mime]
        );
    }

    /** Lấy bytes ảnh bằng chứng + mime của report */
    public function getEvidenceBlob(int $reportId): ?array
    {
        return $this->queryOne('SELECT data, mime FROM report_evidence WHERE report_id = ?', [$reportId]);
    }
}
