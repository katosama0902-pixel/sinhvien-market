-- Week 9-12 additions: residence finance/notices and local marketplace matching.
-- Apply after the existing KTX foundation and operations migrations.
-- This migration is additive and is intentionally not run by application code.

INSERT IGNORE INTO `permissions` (`permission_key`, `name`) VALUES
    ('residence.finance.read', 'Xem hóa đơn lưu trú'),
    ('residence.finance.manage', 'Quản lý danh mục phí và hóa đơn lưu trú'),
    ('residence.finance.pay', 'Ghi nhận thanh toán hóa đơn lưu trú'),
    ('residence.notice.read', 'Xem thông báo lưu trú'),
    ('residence.notice.manage', 'Quản lý thông báo lưu trú'),
    ('marketplace.matching.search', 'Tìm kiếm ghép nối sản phẩm'),
    ('marketplace.matching.manage', 'Quản lý chỉ mục và đánh giá ghép nối');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'residence.finance.read', 'residence.finance.pay',
    'residence.notice.read', 'marketplace.matching.search'
)
WHERE r.role_key = 'student';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'residence.finance.read', 'residence.finance.manage',
    'residence.notice.read', 'residence.notice.manage',
    'marketplace.matching.search', 'marketplace.matching.manage'
)
WHERE r.role_key = 'dorm_manager';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'residence.finance.read', 'residence.finance.manage',
    'residence.notice.read', 'residence.notice.manage',
    'marketplace.matching.search', 'marketplace.matching.manage'
)
WHERE r.role_key = 'admin';

CREATE TABLE IF NOT EXISTS `residence_fee_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_fee_categories_name` (`name`),
    CONSTRAINT `fk_residence_fee_categories_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_invoices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_number` VARCHAR(40) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `contract_id` INT UNSIGNED DEFAULT NULL,
    `description` VARCHAR(255) NOT NULL,
    `issued_at` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `total_amount` DECIMAL(14,0) NOT NULL DEFAULT 0,
    `status` ENUM('open', 'partial', 'paid', 'void') NOT NULL DEFAULT 'open',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_invoices_number` (`invoice_number`),
    KEY `idx_residence_invoices_user_status` (`user_id`, `status`, `due_date`),
    CONSTRAINT `fk_residence_invoices_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_invoices_contract` FOREIGN KEY (`contract_id`) REFERENCES `residence_contracts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_invoices_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_invoice_lines` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id` BIGINT UNSIGNED NOT NULL,
    `fee_category_id` INT UNSIGNED DEFAULT NULL,
    `category_name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(14,0) NOT NULL,
    `line_total` DECIMAL(14,0) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_residence_invoice_lines_invoice` (`invoice_id`),
    CONSTRAINT `fk_residence_invoice_lines_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `residence_invoices` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_invoice_lines_category` FOREIGN KEY (`fee_category_id`) REFERENCES `residence_fee_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id` BIGINT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(14,0) NOT NULL,
    `method` ENUM('cash', 'bank_transfer', 'other') NOT NULL,
    `reference` VARCHAR(120) DEFAULT NULL,
    `note` VARCHAR(500) DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'pending',
    `recorded_by` INT UNSIGNED NOT NULL,
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_residence_payments_invoice_status` (`invoice_id`, `status`),
    CONSTRAINT `fk_residence_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `residence_invoices` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_payments_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_payments_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_notices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `notice_type` ENUM('announcement', 'event', 'rule') NOT NULL,
    `title` VARCHAR(180) NOT NULL,
    `body` TEXT NOT NULL,
    `audience` ENUM('all', 'residents') NOT NULL DEFAULT 'all',
    `starts_at` DATETIME DEFAULT NULL,
    `ends_at` DATETIME DEFAULT NULL,
    `status` ENUM('published', 'archived') NOT NULL DEFAULT 'published',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_residence_notices_status_dates` (`status`, `starts_at`, `ends_at`),
    CONSTRAINT `fk_residence_notices_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Store versioned, locally-generated feature vectors as JSON; no vector DB required.
CREATE TABLE IF NOT EXISTS `marketplace_product_embeddings` (
    `product_id` INT UNSIGNED NOT NULL,
    `model_key` VARCHAR(80) NOT NULL,
    `embedding` JSON NOT NULL,
    `content_hash` CHAR(64) NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`product_id`, `model_key`),
    KEY `idx_marketplace_embeddings_model` (`model_key`),
    CONSTRAINT `fk_marketplace_embeddings_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `marketplace_matching_evaluations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `query_text` VARCHAR(1000) NOT NULL,
    `relevant_product_ids` JSON NOT NULL,
    `result_product_ids` JSON NOT NULL,
    `precision_at_5` DECIMAL(8,6) NOT NULL,
    `recall_at_5` DECIMAL(8,6) NOT NULL,
    `mrr_at_5` DECIMAL(8,6) NOT NULL,
    `ndcg_at_5` DECIMAL(8,6) NOT NULL,
    `model_key` VARCHAR(80) NOT NULL,
    `evaluated_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_matching_evaluations_created` (`created_at`),
    CONSTRAINT `fk_matching_evaluations_user` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
