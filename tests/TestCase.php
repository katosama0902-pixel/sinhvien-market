<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Config\Database;

/**
 * Lớp Base TestCase cho mọi bài test trong dự án.
 * Tự động bắt đầu một transaction trước mỗi bài test và rollback sau khi hoàn tất
 * để tránh làm thay đổi dữ liệu trong CSDL chính.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var \PDO
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Khởi tạo kết nối DB và bắt đầu Transaction
        $this->db = Database::getInstance();
        
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        // Phục hồi lại dữ liệu như cũ (Rollback)
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        // Clear session data if any
        $_SESSION = [];
        
        parent::tearDown();
    }
}
