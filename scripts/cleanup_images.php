<?php
/**
 * Script dọn dẹp các tệp ảnh bị "mồ côi" trong thư mục public/uploads.
 * Dùng để chạy qua Cronjob (VD: 0 0 * * * php /path/to/script/cleanup_images.php)
 */

// Giả lập môi trường để dùng được Core/Database
define('ROOT', dirname(__DIR__));
require ROOT . '/config/Database.php';

use Config\Database;

echo "===============================================\n";
echo "Bắt đầu dọn dẹp ảnh mồ côi lúc " . date('Y-m-d H:i:s') . "\n";
echo "===============================================\n";

try {
    $db = Database::getInstance();

    // 1. Thu thập tất cả tên ảnh đang được sử dụng trong DB (Products, Giveaways, Users)
    $activeImages = [];

    // Ảnh sản phẩm
    $stmt = $db->query("SELECT image FROM products WHERE image IS NOT NULL AND image != ''");
    while ($row = $stmt->fetch()) {
        $activeImages[] = basename($row['image']);
    }

    // Ảnh giveaways
    $stmt = $db->query("SELECT image FROM giveaways WHERE image IS NOT NULL AND image != ''");
    while ($row = $stmt->fetch()) {
        $activeImages[] = basename($row['image']);
    }

    // Ảnh avatar user (thường lưu kiểu 'avatars/filename.jpg' hoặc URL)
    $stmt = $db->query("SELECT avatar FROM users WHERE avatar IS NOT NULL AND avatar != '' AND avatar NOT LIKE 'http%'");
    while ($row = $stmt->fetch()) {
        $activeImages[] = basename($row['avatar']);
    }

    // Xóa trùng lặp
    $activeImages = array_unique($activeImages);
    $activeCount = count($activeImages);
    
    echo "- Tìm thấy {$activeCount} ảnh đang được sử dụng trong Database.\n";

    // 2. Quét thư mục public/uploads
    $uploadsDir = ROOT . '/public/uploads';
    $avatarsDir = $uploadsDir . '/avatars';
    
    $deletedCount = 0;
    
    // Hàm quét đệ quy hoặc quét riêng 2 folder
    $foldersToScan = [$uploadsDir, $avatarsDir];
    
    foreach ($foldersToScan as $folder) {
        if (!is_dir($folder)) continue;
        
        $files = scandir($folder);
        foreach ($files as $file) {
            // Bỏ qua thư mục và file hệ thống/ẩn
            if ($file === '.' || $file === '..' || is_dir($folder . '/' . $file) || $file === '.gitkeep') {
                continue;
            }

            // Nếu file không nằm trong danh sách activeImages -> XÓA
            if (!in_array($file, $activeImages)) {
                $filePath = $folder . '/' . $file;
                if (unlink($filePath)) {
                    echo "  [DELETED] {$file}\n";
                    $deletedCount++;
                } else {
                    echo "  [ERROR] Không thể xóa {$file}\n";
                }
            }
        }
    }

    echo "===============================================\n";
    echo "Hoàn tất! Đã xóa {$deletedCount} ảnh mồ côi.\n";

} catch (Exception $e) {
    echo "LỖI HỆ THỐNG: " . $e->getMessage() . "\n";
}
