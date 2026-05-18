<?php
/**
 * Script kiểm tra và gửi thông báo khi giá sản phẩm yêu thích (Wishlist) giảm.
 * Cần được chạy định kỳ qua Cron (VD: mỗi 30 phút hoặc 1 tiếng).
 * 
 * Command: php scripts/check_price_drops.php
 */

require __DIR__ . '/../.env';
// Tự load env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
        $_ENV[trim($name)] = trim($value);
    }
}

// Bắt đầu load core
define('ROOT', dirname(__DIR__));
define('APP_PATH', ROOT . '/app');
require ROOT . '/vendor/autoload.php';

use App\Models\Wishlist;
use App\Models\Auction;
use App\Services\NotificationService;

echo "Bắt đầu kiểm tra giảm giá Wishlist...\n";

$wishlistModel = new Wishlist();
$pdo = $wishlistModel->pdo();

// Lấy tất cả wishlist items kèm thông tin sản phẩm và đấu giá
$sql = "SELECT w.*, u.email AS user_email, u.name AS user_name, 
               p.title, p.type, p.price AS static_price, p.status AS product_status,
               a.start_price, a.floor_price, a.decrease_amount, a.step_minutes, a.started_at
        FROM wishlists w
        JOIN products p ON p.id = w.product_id
        JOIN users u ON u.id = w.user_id
        LEFT JOIN auctions a ON a.product_id = p.id
        WHERE p.status = 'active'";

$stmt = $pdo->query($sql);
$wishlists = $stmt->fetchAll();

$notifiedCount = 0;

foreach ($wishlists as $w) {
    // Giá cũ lúc lưu (hoặc lúc thông báo lần cuối)
    $oldPrice = (int)$w['price_at_save'];
    if ($oldPrice <= 0) {
        // Nếu lúc lưu chưa có giá (Lỗi data cũ), cập nhật giá hiện tại và bỏ qua
        $currentP = ($w['type'] === 'auction') ? Auction::calculateCurrentPrice($w)['current_price'] : (int)$w['static_price'];
        $wishlistModel->updateSavedPrice($w['user_id'], $w['product_id'], $currentP);
        continue;
    }

    $currentPrice = 0;
    if ($w['type'] === 'sale') {
        $currentPrice = (int)$w['static_price'];
    } elseif ($w['type'] === 'auction' && !empty($w['started_at'])) {
        $priceData = Auction::calculateCurrentPrice($w);
        $currentPrice = $priceData['current_price'];
    }

    if ($currentPrice <= 0) continue;

    // Nếu giá giảm từ 10% trở lên so với giá đã lưu
    if ($currentPrice <= $oldPrice * 0.9) {
        echo "=> Gửi thông báo cho user {$w['user_email']} (Product: {$w['title']}, Giảm từ $oldPrice xuống $currentPrice)\n";
        
        NotificationService::notifyWishlistDrop(
            $w['user_id'], 
            $w['user_email'], 
            $w['user_name'], 
            $w['product_id'], 
            $w['title'], 
            $oldPrice, 
            $currentPrice
        );

        // Cập nhật lại giá mới trong wishlist để không thông báo lặp lại liên tục cho cùng 1 mức giá
        $wishlistModel->updateSavedPrice($w['user_id'], $w['product_id'], $currentPrice);
        $notifiedCount++;
    }
}

echo "Hoàn thành! Đã gửi $notifiedCount thông báo giảm giá.\n";
