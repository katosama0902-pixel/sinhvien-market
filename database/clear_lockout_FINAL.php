<?php
define('ROOT', dirname(__DIR__));
require_once __DIR__ . '/../config/Database.php';
try {
    $pdo = \Config\Database::getInstance();
    $pdo->exec("TRUNCATE TABLE login_attempts");
    echo "Login attempts cleared FINAL.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
