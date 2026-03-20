<?php
require __DIR__ . '/../config/Database.php';
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
    $cols = [
        'phone' => 'VARCHAR(20) NULL',
        'security_question' => 'VARCHAR(255) NULL',
        'security_answer' => 'VARCHAR(255) NULL',
        'otp_code' => 'VARCHAR(255) NULL',
        'otp_expires_at' => 'TIMESTAMP NULL',
        'is_verified' => 'TINYINT(1) DEFAULT 0'
    ];
    foreach($cols as $col => $type) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $type");
            echo "Added $col<br>";
        } catch(Exception $e) { echo "$col already exists or error<br>"; }
    }
    
    $colsT = [
        'payment_method' => "ENUM('cod','banking','zalopay') DEFAULT 'cod'",
        'payment_status' => "ENUM('pending','paid','failed') DEFAULT 'pending'",
        'shipping_address' => 'TEXT NULL'
    ];
    foreach($colsT as $col => $type) {
        try {
            $pdo->exec("ALTER TABLE transactions ADD COLUMN $col $type");
            echo "Added Tx $col<br>";
        } catch(Exception $e) { echo "Tx $col exists or error<br>"; }
    }
} catch(Exception $e) { echo $e->getMessage(); }
