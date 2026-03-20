<?php
require __DIR__ . '/../config/Database.php';

// Cần load .env trước khi thiết lập PDO
$_envFile = __DIR__ . '/../.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#') || !str_contains($_line, '=')) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_k = trim($_k); $_v = trim(trim($_v), '"\'');
        if (!isset($_ENV[$_k])) { $_ENV[$_k] = $_v; putenv("{$_k}={$_v}"); }
    }
}

try {
    $pdo = \Config\Database::getInstance();
    echo "Connected to DB.\n";

    // MySQL syntax cho IF NOT EXISTS column không được hỗ trợ chính thức trong ALTER TABLE ở MySQL < 8.
    // Lấy list columns để check:
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $userCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('phone', $userCols)) {
        $pdo->exec("ALTER TABLE users 
            ADD COLUMN phone VARCHAR(20) NULL AFTER email,
            ADD COLUMN security_question VARCHAR(255) NULL AFTER is_locked,
            ADD COLUMN security_answer VARCHAR(255) NULL AFTER security_question,
            ADD COLUMN otp_code VARCHAR(10) NULL AFTER security_answer,
            ADD COLUMN otp_expires_at TIMESTAMP NULL AFTER otp_code,
            ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER otp_expires_at
        ");
        echo "Users table updated.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM transactions");
    $txCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('payment_method', $txCols)) {
        $pdo->exec("ALTER TABLE transactions
            ADD COLUMN payment_method ENUM('cod','banking','zalopay') DEFAULT 'cod' AFTER type,
            ADD COLUMN payment_status ENUM('pending','paid','failed') DEFAULT 'pending' AFTER payment_method,
            ADD COLUMN shipping_address TEXT NULL AFTER payment_status
        ");
        echo "Transactions table updated.\n";
    }

    echo "Phase 11 Schema update successful.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
