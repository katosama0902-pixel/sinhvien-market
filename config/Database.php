<?php

namespace Config;

/**
 * Database - PDO Singleton
 *
 * Chỉ tạo kết nối PDO DUY NHẤT cho toàn bộ request,
 * tránh overhead khi mỗi Model tự tạo kết nối riêng.
 *
 * Đọc credentials từ file .env ở thư mục gốc project.
 */
class Database
{
    private static ?\PDO $instance = null;

    // Không cho phép khởi tạo trực tiếp
    private function __construct() {}
    private function __clone() {}

    /**
     * Lấy PDO instance (tạo mới nếu chưa có)
     */
    public static function getInstance(): \PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Load .env nếu chưa load
        self::loadEnv();

        $host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port    = $_ENV['DB_PORT'] ?? '3306';
        $dbName  = $_ENV['DB_NAME'] ?? 'sinhvien_market';
        $user    = $_ENV['DB_USER'] ?? 'root';
        $pass    = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,   // Throw exceptions thay vì silent fail
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,         // Mặc định fetch theo key
            \PDO::ATTR_EMULATE_PREPARES   => false,                     // Dùng native prepared statements
            \PDO::MYSQL_ATTR_FOUND_ROWS   => true,                      // rowCount() trả về số dòng tìm thấy
        ];

        try {
            self::$instance = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Hiển thị lỗi kết nối (môi trường dev)
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            if ($debug) {
                die('<h1>Lỗi kết nối Database</h1><pre>' . $e->getMessage() . '</pre>
                     <p>Đảm bảo Laragon đang chạy và thông tin trong .env chính xác.</p>');
            } else {
                die('<h1>Lỗi hệ thống</h1><p>Không thể kết nối cơ sở dữ liệu.</p>');
            }
        }

        return self::$instance;
    }

    /**
     * Load file .env vào $_ENV nếu chưa có
     */
    private static function loadEnv(): void
    {
        // Nếu đã load rồi thì bỏ qua
        if (isset($_ENV['DB_HOST'])) {
            return;
        }

        $envFile = ROOT . '/.env';

        if (!file_exists($envFile)) {
            die('<h1>Thiếu file .env</h1>
                 <p>Copy file <code>.env.example</code> thành <code>.env</code> và điền thông tin database.</p>');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Bỏ qua comment
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value);

                // Bỏ dấu nháy nếu có
                $value = trim($value, '"\'');

                $_ENV[$key]    = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Reset instance (dùng trong testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
