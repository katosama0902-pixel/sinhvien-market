INSERT IGNORE INTO `permissions` (`permission_key`, `name`) VALUES
    ('service.request.create', 'Tạo yêu cầu dịch vụ'),
    ('rental.space.manage', 'Quản lý mặt bằng cho thuê'),
    ('rental.application.review', 'Xét duyệt đề nghị thuê mặt bằng');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'service.request.create', 'service.request.read', 'rental.space.read', 'rental.application.create'
)
WHERE r.role_key = 'student';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'building.manage', 'residence.contract.manage', 'service.request.read',
    'service.request.manage', 'rental.space.read', 'rental.space.manage',
    'rental.application.review', 'rental.contract.read'
)
WHERE r.role_key = 'dorm_manager';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'service.request.create', 'service.request.read', 'rental.space.read', 'rental.application.create',
    'rental.contract.read'
)
WHERE r.role_key = 'tenant';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'service.request.create', 'rental.space.manage', 'rental.application.review'
)
WHERE r.role_key = 'admin';

CREATE TABLE IF NOT EXISTS `residence_buildings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(30) NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_buildings_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_floors` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `building_id` INT UNSIGNED NOT NULL,
    `floor_number` SMALLINT NOT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_floor` (`building_id`, `floor_number`),
    CONSTRAINT `fk_residence_floors_building` FOREIGN KEY (`building_id`) REFERENCES `residence_buildings` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_rooms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `floor_id` INT UNSIGNED NOT NULL,
    `room_number` VARCHAR(30) NOT NULL,
    `room_type` ENUM('standard', 'private', 'accessible') NOT NULL DEFAULT 'standard',
    `capacity` SMALLINT UNSIGNED NOT NULL,
    `status` ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_room` (`floor_id`, `room_number`),
    CONSTRAINT `fk_residence_rooms_floor` FOREIGN KEY (`floor_id`) REFERENCES `residence_floors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_beds` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `bed_number` VARCHAR(30) NOT NULL,
    `status` ENUM('available', 'occupied', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_bed` (`room_id`, `bed_number`),
    CONSTRAINT `fk_residence_beds_room` FOREIGN KEY (`room_id`) REFERENCES `residence_rooms` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_bed_allocations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bed_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `contract_id` INT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'transferred', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    `assigned_by` INT UNSIGNED NOT NULL,
    `note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bed_allocations_bed_dates` (`bed_id`, `status`, `start_date`, `end_date`),
    KEY `idx_bed_allocations_user_dates` (`user_id`, `start_date`, `end_date`),
    CONSTRAINT `fk_bed_allocations_bed` FOREIGN KEY (`bed_id`) REFERENCES `residence_beds` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bed_allocations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bed_allocations_contract` FOREIGN KEY (`contract_id`) REFERENCES `residence_contracts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_bed_allocations_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_allocation_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `allocation_id` BIGINT UNSIGNED NOT NULL,
    `actor_id` INT UNSIGNED NOT NULL,
    `event_type` ENUM('allocated', 'transferred', 'completed', 'cancelled') NOT NULL,
    `from_bed_id` INT UNSIGNED DEFAULT NULL,
    `to_bed_id` INT UNSIGNED DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_allocation_events_allocation` (`allocation_id`, `created_at`),
    CONSTRAINT `fk_allocation_events_allocation` FOREIGN KEY (`allocation_id`) REFERENCES `residence_bed_allocations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_allocation_events_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_allocation_events_from_bed` FOREIGN KEY (`from_bed_id`) REFERENCES `residence_beds` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_allocation_events_to_bed` FOREIGN KEY (`to_bed_id`) REFERENCES `residence_beds` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_service_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `request_type` ENUM('service', 'repair', 'feedback') NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    `description` VARCHAR(2000) NOT NULL,
    `room_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('submitted', 'assigned', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'submitted',
    `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `staff_note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_service_requests_status` (`status`, `created_at`),
    KEY `idx_service_requests_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_service_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_service_requests_room` FOREIGN KEY (`room_id`) REFERENCES `residence_rooms` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_service_requests_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_spaces` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `space_code` VARCHAR(30) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `area_sqm` DECIMAL(10,2) NOT NULL,
    `monthly_price` DECIMAL(14,2) NOT NULL,
    `description` VARCHAR(2000) DEFAULT NULL,
    `status` ENUM('available', 'reserved', 'occupied', 'inactive') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rental_spaces_code` (`space_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_applications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_number` VARCHAR(32) NOT NULL,
    `space_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `business_name` VARCHAR(150) NOT NULL,
    `note` VARCHAR(1000) DEFAULT NULL,
    `status` ENUM('submitted', 'under_review', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'submitted',
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `decision_note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rental_applications_number` (`application_number`),
    KEY `idx_rental_applications_space_status` (`space_id`, `status`),
    CONSTRAINT `fk_rental_applications_space` FOREIGN KEY (`space_id`) REFERENCES `rental_spaces` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rental_applications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rental_applications_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_contracts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_number` VARCHAR(32) NOT NULL,
    `application_id` INT UNSIGNED NOT NULL,
    `space_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `monthly_price` DECIMAL(14,2) NOT NULL,
    `status` ENUM('active', 'expired', 'terminated') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rental_contracts_number` (`contract_number`),
    UNIQUE KEY `uq_rental_contracts_application` (`application_id`),
    KEY `idx_rental_contracts_user_status` (`user_id`, `status`),
    CONSTRAINT `fk_rental_contracts_application` FOREIGN KEY (`application_id`) REFERENCES `rental_applications` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rental_contracts_space` FOREIGN KEY (`space_id`) REFERENCES `rental_spaces` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_rental_contracts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
