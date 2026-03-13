-- ============================================================
--  SinhVienMarket - Schema Cơ Sở Dữ Liệu
--  Môi trường: MySQL 5.7+ / MariaDB 10.3+ (Laragon)
--  Charset: utf8mb4 (hỗ trợ emoji & tiếng Việt đầy đủ)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- Tạo database nếu chưa có
CREATE DATABASE IF NOT EXISTS `sinhvien_market`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `sinhvien_market`;

-- ============================================================
--  1. USERS - Tài khoản sinh viên & admin
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL COMMENT 'Họ tên đầy đủ',
    `email`      VARCHAR(150) NOT NULL COMMENT 'Email đăng ký (duy nhất)',
    `password`   VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    `phone`      VARCHAR(20)  DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
    `avatar`     VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
    `role`       ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `is_locked`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = bị khóa bởi Admin',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_is_locked` (`is_locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tài khoản người dùng hệ thống';

-- ============================================================
--  2. CATEGORIES - Danh mục sản phẩm
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL COMMENT 'Tên danh mục',
    `slug`       VARCHAR(120) NOT NULL COMMENT 'URL-friendly name',
    `icon`       VARCHAR(60)  DEFAULT NULL COMMENT 'Bootstrap Icon class, VD: bi-book',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh mục sản phẩm (giáo trình, đồ dùng, thiết bị...)';

-- ============================================================
--  3. PRODUCTS - Bài đăng sản phẩm
-- ============================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL COMMENT 'Người đăng bán',
    `category_id` INT UNSIGNED NOT NULL COMMENT 'Danh mục',
    `title`       VARCHAR(200) NOT NULL COMMENT 'Tiêu đề bài đăng',
    `description` TEXT         NOT NULL COMMENT 'Mô tả chi tiết sản phẩm',
    `image`       VARCHAR(255) DEFAULT NULL COMMENT 'Tên file ảnh trong /public/uploads/',
    `type`        ENUM('sale', 'exchange', 'auction') NOT NULL DEFAULT 'sale'
                  COMMENT 'Loại: bán thường / trao đổi / đấu giá ngược',
    `status`      ENUM('pending', 'active', 'sold', 'cancelled') NOT NULL DEFAULT 'pending'
                  COMMENT 'pending=chờ duyệt, active=đang bán, sold=đã bán, cancelled=bị từ chối',
    `price`       DECIMAL(12, 0) DEFAULT NULL COMMENT 'Giá cố định (chỉ dùng với type=sale)',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id`     (`user_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_type`        (`type`),
    KEY `idx_status`      (`status`),
    KEY `idx_created_at`  (`created_at`),
    -- FULLTEXT index cho tìm kiếm nhanh theo title và description
    FULLTEXT KEY `ft_search` (`title`, `description`),
    CONSTRAINT `fk_products_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bài đăng sản phẩm của sinh viên';

-- ============================================================
--  4. AUCTIONS - Cấu hình đấu giá ngược
--  Mỗi sản phẩm type='auction' sẽ có đúng 1 record ở đây
-- ============================================================
DROP TABLE IF EXISTS `auctions`;
CREATE TABLE `auctions` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`      INT UNSIGNED NOT NULL COMMENT 'Sản phẩm đấu giá',
    `start_price`     DECIMAL(12, 0) NOT NULL COMMENT 'Giá khởi điểm (VND)',
    `floor_price`     DECIMAL(12, 0) NOT NULL COMMENT 'Giá sàn tối thiểu (VND)',
    `decrease_amount` DECIMAL(12, 0) NOT NULL COMMENT 'Mức giảm mỗi bước (VND)',
    `step_minutes`    SMALLINT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Chu kỳ giảm giá (phút)',
    `started_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm bắt đầu đấu giá',
    `ended_at`        TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời điểm kết thúc (NULL = vẫn đang chạy)',
    `winner_id`       INT UNSIGNED DEFAULT NULL COMMENT 'ID người thắng đấu giá',
    `final_price`     DECIMAL(12, 0) DEFAULT NULL COMMENT 'Giá chốt cuối cùng',
    `status`          ENUM('active', 'sold', 'cancelled') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_id` (`product_id`) COMMENT 'Mỗi sản phẩm chỉ có 1 auction',
    KEY `idx_status`    (`status`),
    KEY `idx_winner_id` (`winner_id`),
    CONSTRAINT `fk_auctions_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_auctions_winner`  FOREIGN KEY (`winner_id`)  REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cấu hình và trạng thái đấu giá ngược';

-- ============================================================
--  5. TRANSACTIONS - Lịch sử giao dịch
-- ============================================================
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL COMMENT 'Sản phẩm được giao dịch',
    `buyer_id`   INT UNSIGNED NOT NULL COMMENT 'Người mua',
    `seller_id`  INT UNSIGNED NOT NULL COMMENT 'Người bán',
    `amount`     DECIMAL(12, 0) NOT NULL COMMENT 'Giá giao dịch (VND)',
    `type`       ENUM('auction', 'direct') NOT NULL DEFAULT 'direct'
                 COMMENT 'auction = đấu giá ngược, direct = mua thường',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_buyer_id`   (`buyer_id`),
    KEY `idx_seller_id`  (`seller_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_transactions_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_transactions_buyer`   FOREIGN KEY (`buyer_id`)   REFERENCES `users`(`id`)    ON DELETE RESTRICT,
    CONSTRAINT `fk_transactions_seller`  FOREIGN KEY (`seller_id`)  REFERENCES `users`(`id`)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Lịch sử giao dịch mua bán';

-- ============================================================
--  6. LOGIN_ATTEMPTS - Rate limiting đăng nhập (chặn brute-force)
-- ============================================================
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip`           VARCHAR(45)  NOT NULL COMMENT 'IP người dùng (hỗ trợ IPv6)',
    `attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Số lần thất bại',
    `last_attempt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ip` (`ip`),
    KEY `idx_last_attempt` (`last_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Theo dõi số lần đăng nhập thất bại theo IP để chặn brute-force';

-- ============================================================
--  7. AUDIT_LOGS - Lịch sử hành động Admin
-- ============================================================
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`    INT UNSIGNED NOT NULL COMMENT 'Admin thực hiện hành động',
    `action`      VARCHAR(100) NOT NULL COMMENT 'Loại hành động: delete_product, lock_user...',
    `target_type` VARCHAR(50)  NOT NULL COMMENT 'Đối tượng bị tác động: product, user, category',
    `target_id`   INT UNSIGNED NOT NULL COMMENT 'ID đối tượng bị tác động',
    `note`        TEXT DEFAULT NULL COMMENT 'Thông tin bổ sung (tên sản phẩm, email user...)',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_admin_id`    (`admin_id`),
    KEY `idx_action`      (`action`),
    KEY `idx_created_at`  (`created_at`),
    CONSTRAINT `fk_audit_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Nhật ký hành động của Admin — dùng để kiểm toán';

-- ============================================================
--  RESET FOREIGN KEY CHECKS
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  SEED DATA - Dữ liệu mẫu ban đầu
-- ============================================================

-- Tài khoản Admin mặc định — Password: Admin@123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Quản Trị Viên', 'admin@market.com',
 '$2y$12$SgUUG97QUauWA2EuHa/iXufwMcJ6JDRX0irQZ5252MTsc0u9yZEfe',
 'admin');

-- Tài khoản sinh viên mẫu — Password: Admin@123 (dùng chung cho demo)
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Nguyễn Văn An', 'an@student.edu.vn',
 '$2y$12$SgUUG97QUauWA2EuHa/iXufwMcJ6JDRX0irQZ5252MTsc0u9yZEfe',
 'student'),
('Trần Thị Bình', 'binh@student.edu.vn',
 '$2y$12$SgUUG97QUauWA2EuHa/iXufwMcJ6JDRX0irQZ5252MTsc0u9yZEfe',
 'student');

-- Danh mục sản phẩm
INSERT INTO `categories` (`name`, `slug`, `icon`) VALUES
('Giáo trình & Sách',    'giao-trinh',    'bi-book'),
('Điện tử & Máy tính',   'dien-tu',       'bi-laptop'),
('Đồ dùng học tập',      'do-dung',       'bi-pencil'),
('Quần áo & Phụ kiện',   'quan-ao',       'bi-bag'),
('Đồ gia dụng KTX',      'gia-dung',      'bi-house'),
('Thể thao & Giải trí',  'the-thao',      'bi-bicycle'),
('Khác',                 'khac',          'bi-grid');

-- Sản phẩm mẫu (type=sale thường)
INSERT INTO `products` (`user_id`, `category_id`, `title`, `description`, `type`, `status`, `price`) VALUES
(2, 1, 'Giáo trình Toán Cao Cấp A1 - Nguyễn Đình Trí',
 'Sách còn mới 90%, không ghi chú bên trong. Phù hợp sinh viên năm 1 khoa Kỹ thuật.',
 'sale', 'active', 35000),
(3, 2, 'Tai nghe JBL T110 còn BH',
 'Tai nghe JBL chính hãng, còn bảo hành 6 tháng. Âm thanh cực hay, dây không bị đứt.',
 'sale', 'active', 180000);

-- Sản phẩm mẫu (type=auction - đấu giá ngược)
INSERT INTO `products` (`user_id`, `category_id`, `title`, `description`, `type`, `status`) VALUES
(2, 1, 'Bộ sách Vật Lý Đại Cương (tập 1+2) - Lương Duyên Bình',
 'Dùng 1 học kỳ, còn sạch. Cần bán gấp trước khi ra trường. Giá giảm dần theo thời gian!',
 'auction', 'active');

-- Cấu hình đấu giá ngược cho sản phẩm trên
-- Giá khởi điểm: 80.000đ, giá sàn: 30.000đ, giảm 5.000đ mỗi 10 phút
INSERT INTO `auctions`
    (`product_id`, `start_price`, `floor_price`, `decrease_amount`, `step_minutes`, `started_at`)
VALUES
    (3, 80000, 30000, 5000, 10, NOW());
