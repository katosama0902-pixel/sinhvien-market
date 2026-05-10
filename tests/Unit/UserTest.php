<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }

    public function test_can_find_user_by_email()
    {
        // 1. Tạo dữ liệu mẫu (Sẽ bị rollback sau khi test)
        $email = 'test_phpunit_' . time() . '@student.ctu.edu.vn';
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role, is_verified) VALUES (?, ?, ?, 'student', 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['Test User', $email, $hashedPassword]);
        
        // 2. Chạy hàm cần test
        $user = $this->userModel->findByEmail($email);
        
        // 3. Khẳng định (Assert)
        $this->assertNotNull($user, "Phải tìm thấy user với email vừa tạo");
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals($email, $user['email']);
    }

    public function test_find_by_email_returns_null_if_not_found()
    {
        $user = $this->userModel->findByEmail('nonexistent@student.ctu.edu.vn');
        $this->assertNull($user, "Hàm phải trả về null nếu không tìm thấy user");
    }
}
