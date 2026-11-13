<?php

namespace Tests\Unit;

use App\Models\AccessControl;
use App\Models\ResidenceApplication;
use Config\Database;
use PHPUnit\Framework\TestCase;

class ResidenceApplicationTest extends TestCase
{
    private \PDO $db;
    private int $studentId;
    private int $managerId;
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getInstance();
        $this->studentId = $this->createUser('student');
        $this->managerId = $this->createUser('student');
        $this->adminId = $this->createUser('admin');
        self::assertTrue((new AccessControl())->assignRole(
            $this->managerId,
            'dorm_manager',
            $this->adminId
        ));
    }

    protected function tearDown(): void
    {
        $userIds = [$this->studentId, $this->managerId, $this->adminId];
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        $stmt = $this->db->prepare(
            "SELECT id FROM residence_applications WHERE user_id IN ({$placeholders})"
        );
        $stmt->execute($userIds);
        $applicationIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));

        if ($applicationIds !== []) {
            $applicationPlaceholders = implode(',', array_fill(0, count($applicationIds), '?'));
            $deleteEvents = $this->db->prepare(
                "DELETE FROM residence_application_events WHERE application_id IN ({$applicationPlaceholders})"
            );
            $deleteEvents->execute($applicationIds);
            $deleteContracts = $this->db->prepare(
                "DELETE FROM residence_contracts WHERE application_id IN ({$applicationPlaceholders})"
            );
            $deleteContracts->execute($applicationIds);
            $deleteApplications = $this->db->prepare(
                "DELETE FROM residence_applications WHERE id IN ({$applicationPlaceholders})"
            );
            $deleteApplications->execute($applicationIds);
        }

        $deleteRoleEvents = $this->db->prepare(
            "DELETE FROM user_role_events
             WHERE user_id IN ({$placeholders}) OR actor_id IN ({$placeholders})"
        );
        $deleteRoleEvents->execute([...$userIds, ...$userIds]);
        $deleteAssignments = $this->db->prepare(
            "DELETE FROM user_roles WHERE user_id IN ({$placeholders})"
        );
        $deleteAssignments->execute($userIds);
        $deleteProfiles = $this->db->prepare(
            "DELETE FROM student_profiles WHERE user_id IN ({$placeholders})"
        );
        $deleteProfiles->execute($userIds);
        $deleteUsers = $this->db->prepare(
            "DELETE FROM users WHERE id IN ({$placeholders})"
        );
        $deleteUsers->execute($userIds);

        parent::tearDown();
    }

    public function test_manager_reviews_application_and_creates_one_pending_contract(): void
    {
        $model = new ResidenceApplication();
        $application = $model->submit(
            $this->studentId,
            '2030-08-01',
            '2031-07-31',
            'standard',
            'First year application'
        );

        self::assertSame('submitted', $application['status']);
        self::assertSame(
            'under_review',
            $model->review((int)$application['id'], $this->managerId, 'under_review', 'Documents checked')['status']
        );
        $approved = $model->review(
            (int)$application['id'],
            $this->managerId,
            'approve',
            'Approved for the requested period'
        );

        self::assertSame('approved', $approved['status']);
        self::assertNotEmpty($approved['contract_number']);
        self::assertSame('pending_assignment', $approved['contract_status']);
        $contractCount = $this->db->prepare(
            'SELECT COUNT(*) FROM residence_contracts WHERE application_id = ?'
        );
        $contractCount->execute([(int)$application['id']]);
        self::assertSame(1, (int)$contractCount->fetchColumn());

        $eventCount = $this->db->prepare(
            'SELECT COUNT(*) FROM residence_application_events WHERE application_id = ?'
        );
        $eventCount->execute([(int)$application['id']]);
        self::assertSame(3, (int)$eventCount->fetchColumn());

        self::assertFalse(
            (new AccessControl())->hasPermission($this->studentId, 'residence.application.review')
        );
        self::assertTrue(
            (new AccessControl())->hasPermission($this->managerId, 'residence.application.review')
        );
    }

    public function test_student_cannot_submit_overlapping_active_applications(): void
    {
        $model = new ResidenceApplication();
        $model->submit($this->studentId, '2030-08-01', '2031-07-31', 'any', null);

        try {
            $model->submit($this->studentId, '2031-01-01', '2031-12-31', 'standard', null);
            self::fail('An overlapping active application must be rejected.');
        } catch (\DomainException $e) {
            self::assertSame('APPLICATION_OVERLAP', $e->getMessage());
        }
    }

    public function test_rejected_application_cannot_be_approved_later(): void
    {
        $model = new ResidenceApplication();
        $application = $model->submit($this->studentId, '2030-08-01', '2031-07-31', 'any', null);
        $rejected = $model->review(
            (int)$application['id'],
            $this->managerId,
            'reject',
            'Missing documents'
        );
        self::assertSame('rejected', $rejected['status']);
        self::assertNull($rejected['contract_number']);

        $this->expectException(\DomainException::class);
        $model->review((int)$application['id'], $this->managerId, 'approve', 'Changed decision');
    }

    private function createUser(string $role): int
    {
        $email = 'ktx-test-' . bin2hex(random_bytes(6)) . '@example.test';
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role, is_verified) VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            'KTX Test User',
            $email,
            password_hash('TestPass123', PASSWORD_DEFAULT),
            $role,
        ]);
        return (int)$this->db->lastInsertId();
    }
}
