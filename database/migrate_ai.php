<?php
require __DIR__ . '/../.env';
// Try to load env manually since we don't have full bootstrap
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'sinhvien_market';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Thêm cột cho AI moderation
    $sql = "ALTER TABLE products
            ADD COLUMN `ai_review_status` ENUM('pending','safe','suspicious','violation') DEFAULT 'pending' COMMENT 'Kết quả kiểm duyệt của AI',
            ADD COLUMN `ai_review_note` TEXT DEFAULT NULL COMMENT 'Lý do AI đưa ra (Việt hoá cho Admin đọc)'";
    
    $pdo->exec($sql);
    echo "Migration thành công!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Cột đã tồn tại.\n";
    } else {
        echo "Lỗi: " . $e->getMessage() . "\n";
    }
}
