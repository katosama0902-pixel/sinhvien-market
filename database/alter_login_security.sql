-- Feature 3A/3B: Login Sessions (login history + new device alert)
-- Chạy file này trên phpMyAdmin hoặc MySQL CLI

CREATE TABLE IF NOT EXISTS `login_sessions` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED     NOT NULL,
    `ip_address` VARCHAR(45)      NOT NULL,
    `user_agent` TEXT             NOT NULL,
    `device_info` VARCHAR(255)    NOT NULL DEFAULT '',
    `is_new_device` TINYINT(1)   NOT NULL DEFAULT 0,
    `logged_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_logged_at` (`logged_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature 3C: 2FA (thêm cột vào bảng users)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `twofa_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_student_verified`;
