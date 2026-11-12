<?php

namespace App\Models;

use Core\Model;

class StudentProfile extends Model
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->queryOne(
            'SELECT u.id AS user_id, u.name, u.email, u.phone, u.university,
                    sp.student_number, sp.faculty, sp.program, sp.academic_year,
                    sp.date_of_birth, sp.emergency_contact_name, sp.emergency_contact_phone
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.id = ?
             LIMIT 1',
            [$userId]
        );
    }

    /**
     * @param array<string, string|null> $data
     */
    public function save(int $userId, array $data): void
    {
        $this->pdo()->beginTransaction();
        try {
            $this->execute(
                'UPDATE users SET name = ?, phone = ?, university = ?, student_id = ? WHERE id = ?',
                [
                    $data['name'],
                    $data['phone'],
                    $data['university'],
                    $data['student_number'],
                    $userId,
                ]
            );
            $this->execute(
                'INSERT INTO student_profiles (
                    user_id, student_number, faculty, program, academic_year,
                    date_of_birth, emergency_contact_name, emergency_contact_phone
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    student_number = VALUES(student_number),
                    faculty = VALUES(faculty),
                    program = VALUES(program),
                    academic_year = VALUES(academic_year),
                    date_of_birth = VALUES(date_of_birth),
                    emergency_contact_name = VALUES(emergency_contact_name),
                    emergency_contact_phone = VALUES(emergency_contact_phone)',
                [
                    $userId,
                    $data['student_number'],
                    $data['faculty'],
                    $data['program'],
                    $data['academic_year'],
                    $data['date_of_birth'],
                    $data['emergency_contact_name'],
                    $data['emergency_contact_phone'],
                ]
            );
            $this->pdo()->commit();
        } catch (\Throwable $e) {
            if ($this->pdo()->inTransaction()) {
                $this->pdo()->rollBack();
            }
            throw $e;
        }
    }
}
