<?php

namespace App\Models;

use Core\Model;

class AccessControl extends Model
{
    /**
     * The legacy users.role value remains the default role for compatibility;
     * user_roles adds any additional KTX roles.
     */
    public function hasPermission(int $userId, string $permissionKey): bool
    {
        return $this->queryOne(
            'SELECT 1
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN roles r ON r.id = rp.role_id
             WHERE p.permission_key = ?
               AND (
                    r.role_key = (SELECT u.role FROM users u WHERE u.id = ?)
                    OR EXISTS (
                        SELECT 1
                        FROM user_roles ur
                        WHERE ur.user_id = ?
                          AND ur.role_id = r.id
                    )
               )
             LIMIT 1',
            [$permissionKey, $userId, $userId]
        ) !== null;
    }

    /**
     * @return list<string>
     */
    public function rolesForUser(int $userId): array
    {
        $rows = $this->query(
            'SELECT r.role_key
             FROM roles r
             WHERE r.role_key = (SELECT u.role FROM users u WHERE u.id = ?)
                OR EXISTS (
                    SELECT 1
                    FROM user_roles ur
                    WHERE ur.user_id = ?
                      AND ur.role_id = r.id
                )
             ORDER BY r.role_key',
            [$userId, $userId]
        );

        return array_values(array_map(
            static fn(array $row): string => (string)$row['role_key'],
            $rows
        ));
    }

    /**
     * @return list<array{key: string, name: string}>
     */
    public function assignableRoles(): array
    {
        $rows = $this->query(
            "SELECT role_key, name FROM roles
             WHERE role_key IN ('dorm_manager', 'service_staff', 'tenant')
             ORDER BY name"
        );

        return array_map(
            static fn(array $row): array => [
                'key' => (string)$row['role_key'],
                'name' => (string)$row['name'],
            ],
            $rows
        );
    }

    public function assignRole(int $userId, string $roleKey, int $assignedBy): bool
    {
        $role = $this->queryOne(
            "SELECT id FROM roles
             WHERE role_key = ?
               AND role_key IN ('dorm_manager', 'service_staff', 'tenant')
             LIMIT 1",
            [$roleKey]
        );
        if ($role === null) {
            return false;
        }

        $this->pdo()->beginTransaction();
        try {
            $inserted = $this->execute(
                'INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)',
                [$userId, (int)$role['id'], $assignedBy]
            );
            if ($inserted > 0) {
                $this->execute(
                    'INSERT INTO user_role_events (user_id, role_id, actor_id, event_type) VALUES (?, ?, ?, ?)',
                    [$userId, (int)$role['id'], $assignedBy, 'assigned']
                );
            }
            $this->pdo()->commit();
        } catch (\Throwable $e) {
            if ($this->pdo()->inTransaction()) {
                $this->pdo()->rollBack();
            }
            throw $e;
        }

        return true;
    }
}
