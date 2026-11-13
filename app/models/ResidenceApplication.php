<?php

namespace App\Models;

use Core\Model;

class ResidenceApplication extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForStudent(int $userId): array
    {
        return $this->query(
            'SELECT a.*, c.id AS contract_id, c.contract_number, c.status AS contract_status
             FROM residence_applications a
             LEFT JOIN residence_contracts c ON c.application_id = a.id
             WHERE a.user_id = ?
             ORDER BY a.created_at DESC, a.id DESC',
            [$userId]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForReview(): array
    {
        return $this->query(
            'SELECT a.*, u.name AS student_name, u.email AS student_email,
                    sp.student_number, u.university,
                    c.id AS contract_id, c.contract_number, c.status AS contract_status
             FROM residence_applications a
             JOIN users u ON u.id = a.user_id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             LEFT JOIN residence_contracts c ON c.application_id = a.id
             ORDER BY FIELD(a.status, "submitted", "under_review", "approved", "rejected", "withdrawn"),
                      a.created_at ASC, a.id ASC'
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listContractsForStudent(int $userId): array
    {
        return $this->query(
            'SELECT c.*, a.application_number
             FROM residence_contracts c
             JOIN residence_applications a ON a.id = c.application_id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC, c.id DESC',
            [$userId]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listContractsForManagers(): array
    {
        return $this->query(
            'SELECT c.*, a.application_number, u.name AS student_name, sp.student_number
             FROM residence_contracts c
             JOIN residence_applications a ON a.id = c.application_id
             JOIN users u ON u.id = c.user_id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             ORDER BY c.created_at DESC, c.id DESC'
        );
    }

    public function hasOverlappingActiveApplication(int $userId, string $startDate, string $endDate): bool
    {
        return $this->queryOne(
            "SELECT id
             FROM residence_applications
             WHERE user_id = ?
               AND status IN ('submitted', 'under_review', 'approved')
               AND start_date <= ?
               AND end_date >= ?
             LIMIT 1",
            [$userId, $endDate, $startDate]
        ) !== null;
    }

    public function submit(int $userId, string $startDate, string $endDate, string $roomType, ?string $note): array
    {
        $this->pdo()->beginTransaction();
        try {
            $this->queryOne('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);
            if ($this->hasOverlappingActiveApplication($userId, $startDate, $endDate)) {
                $this->pdo()->rollBack();
                throw new \DomainException('APPLICATION_OVERLAP');
            }

            $number = 'KTX-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $applicationId = $this->insert(
                'INSERT INTO residence_applications (
                    application_number, user_id, start_date, end_date, preferred_room_type, note
                 ) VALUES (?, ?, ?, ?, ?, ?)',
                [$number, $userId, $startDate, $endDate, $roomType, $note]
            );
            $this->execute(
                'INSERT INTO residence_application_events (
                    application_id, actor_id, event_type, from_status, to_status, note
                 ) VALUES (?, ?, ?, ?, ?, ?)',
                [$applicationId, $userId, 'submitted', null, 'submitted', $note]
            );
            $this->pdo()->commit();

            $created = $this->findById($applicationId);
            if ($created === null) {
                throw new \RuntimeException('Submitted residence application could not be reloaded.');
            }
            return $created;
        } catch (\Throwable $e) {
            if ($this->pdo()->inTransaction()) {
                $this->pdo()->rollBack();
            }
            throw $e;
        }
    }

    public function review(int $applicationId, int $reviewerId, string $decision, ?string $note): array
    {
        $this->pdo()->beginTransaction();
        try {
            $application = $this->queryOne(
                'SELECT * FROM residence_applications WHERE id = ? FOR UPDATE',
                [$applicationId]
            );
            if ($application === null) {
                $this->pdo()->rollBack();
                throw new \OutOfBoundsException('APPLICATION_NOT_FOUND');
            }

            $fromStatus = (string)$application['status'];
            if (!in_array($fromStatus, ['submitted', 'under_review'], true)) {
                $this->pdo()->rollBack();
                throw new \DomainException('INVALID_STATE_TRANSITION');
            }
            if ($decision === 'under_review' && $fromStatus !== 'submitted') {
                $this->pdo()->rollBack();
                throw new \DomainException('INVALID_STATE_TRANSITION');
            }

            $toStatus = match ($decision) {
                'under_review' => 'under_review',
                'approve' => 'approved',
                'reject' => 'rejected',
                default => throw new \InvalidArgumentException('INVALID_DECISION'),
            };
            $this->execute(
                'UPDATE residence_applications
                 SET status = ?, reviewed_by = ?, reviewed_at = NOW(), decision_note = ?
                 WHERE id = ?',
                [$toStatus, $reviewerId, $note, $applicationId]
            );

            if ($toStatus === 'approved') {
                $contractNumber = 'HD-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(4)));
                $this->execute(
                    'INSERT INTO residence_contracts (
                        contract_number, application_id, user_id, start_date, end_date, status
                     ) VALUES (?, ?, ?, ?, ?, "pending_assignment")',
                    [
                        $contractNumber,
                        $applicationId,
                        (int)$application['user_id'],
                        $application['start_date'],
                        $application['end_date'],
                    ]
                );
            }

            $this->execute(
                'INSERT INTO residence_application_events (
                    application_id, actor_id, event_type, from_status, to_status, note
                 ) VALUES (?, ?, ?, ?, ?, ?)',
                [$applicationId, $reviewerId, $decision, $fromStatus, $toStatus, $note]
            );
            $this->pdo()->commit();

            $reviewed = $this->findById($applicationId);
            if ($reviewed === null) {
                throw new \RuntimeException('Reviewed residence application could not be reloaded.');
            }
            return $reviewed;
        } catch (\Throwable $e) {
            if ($this->pdo()->inTransaction()) {
                $this->pdo()->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findById(int $applicationId): ?array
    {
        return $this->queryOne(
            'SELECT a.*, c.id AS contract_id, c.contract_number, c.status AS contract_status
             FROM residence_applications a
             LEFT JOIN residence_contracts c ON c.application_id = a.id
             WHERE a.id = ?
             LIMIT 1',
            [$applicationId]
        );
    }
}
