<?php
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_NAME'] = 'sinhvien_market';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
require_once __DIR__ . '/../config/Database.php';
try {
    $pdo = \Config\Database::getInstance();
    $pdo->exec("TRUNCATE TABLE login_attempts");
    echo "Login attempts cleared.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
