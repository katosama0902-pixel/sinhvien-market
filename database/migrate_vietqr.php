<?php
require __DIR__ . '/../.env';
// Tự load env
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

    // Bảng bank_accounts
    $sql1 = "CREATE TABLE IF NOT EXISTS `bank_accounts` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id`      INT UNSIGNED NOT NULL,
        `bank_name`    VARCHAR(100) NOT NULL COMMENT 'Tên ngân hàng (VD: Vietcombank)',
        `bank_code`    VARCHAR(20)  NOT NULL COMMENT 'Mã ngân hàng BIN (dùng cho VietQR)',
        `account_no`   VARCHAR(30)  NOT NULL COMMENT 'Số tài khoản',
        `account_name` VARCHAR(100) NOT NULL COMMENT 'Tên chủ tài khoản (IN HOA)',
        `is_verified`  TINYINT(1)   NOT NULL DEFAULT 0,
        `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user_bank` (`user_id`),
        CONSTRAINT `fk_bank_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql1);
    echo "Tạo bảng bank_accounts thành công!\n";

    // Cập nhật bảng transactions
    try {
        $sql2 = "ALTER TABLE `transactions`
                 ADD COLUMN `payment_proof` VARCHAR(255) DEFAULT NULL COMMENT 'Ảnh chụp màn hình xác nhận CK',
                 ADD COLUMN `payment_confirmed_at` TIMESTAMP DEFAULT NULL COMMENT 'Thời điểm người bán xác nhận'";
        $pdo->exec($sql2);
        echo "Cập nhật transactions thành công!\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Cột trong transactions đã tồn tại.\n";
        } else {
            echo "Lỗi khi sửa transactions: " . $e->getMessage() . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage() . "\n";
}
