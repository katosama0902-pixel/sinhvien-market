<?php
opcache_reset();
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_NAME'] = 'sinhvien_market';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $pdo = \Config\Database::getInstance();

    // Table giveaways
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS giveaways (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            image VARCHAR(255) NULL,
            end_time DATETIME NOT NULL,
            status ENUM('active', 'ended') DEFAULT 'active',
            winner_id INT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Table giveaway_participants
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS giveaway_participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            giveaway_id INT NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_participation (giveaway_id, user_id),
            FOREIGN KEY (giveaway_id) REFERENCES giveaways(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Giveaways tables created successfully!";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
