CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_key` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_role_key` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `permission_key` VARCHAR(100) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_permissions_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED DEFAULT NULL,
    `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `idx_user_roles_role_id` (`role_id`),
    CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_role_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `actor_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_role_events_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_user_role_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_user_role_events_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_user_role_events_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`role_key`, `name`) VALUES
    ('student', 'Sinh viên'),
    ('dorm_manager', 'Ban quản lý KTX'),
    ('service_staff', 'Nhân viên dịch vụ'),
    ('tenant', 'Đối tác / người thuê mặt bằng'),
    ('admin', 'Quản trị viên');

INSERT IGNORE INTO `permissions` (`permission_key`, `name`) VALUES
    ('student.profile.read', 'Xem hồ sơ sinh viên'),
    ('student.profile.update', 'Cập nhật hồ sơ sinh viên'),
    ('student.records.read', 'Xem hồ sơ sinh viên khác'),
    ('residence.application.create', 'Gửi hồ sơ lưu trú'),
    ('residence.application.read', 'Xem hồ sơ lưu trú'),
    ('residence.application.review', 'Xét duyệt hồ sơ lưu trú'),
    ('residence.contract.read', 'Xem hợp đồng lưu trú'),
    ('residence.contract.manage', 'Quản lý hợp đồng lưu trú'),
    ('building.manage', 'Quản lý tòa nhà và phòng'),
    ('service.request.read', 'Xem yêu cầu dịch vụ'),
    ('service.request.manage', 'Xử lý yêu cầu dịch vụ'),
    ('rental.space.read', 'Xem mặt bằng cho thuê'),
    ('rental.application.create', 'Gửi đề nghị thuê mặt bằng'),
    ('rental.contract.read', 'Xem hợp đồng thuê mặt bằng'),
    ('system.roles.manage', 'Phân quyền tài khoản');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
JOIN `permissions` p ON p.`permission_key` IN (
    'student.profile.read',
    'student.profile.update',
    'residence.application.create',
    'residence.application.read',
    'residence.contract.read'
)
WHERE r.`role_key` = 'student';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
JOIN `permissions` p ON p.`permission_key` IN (
    'student.records.read',
    'residence.application.read',
    'residence.application.review',
    'residence.contract.read',
    'residence.contract.manage',
    'building.manage'
)
WHERE r.`role_key` = 'dorm_manager';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
JOIN `permissions` p ON p.`permission_key` IN (
    'service.request.read',
    'service.request.manage'
)
WHERE r.`role_key` = 'service_staff';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
JOIN `permissions` p ON p.`permission_key` IN (
    'rental.space.read',
    'rental.application.create',
    'rental.contract.read'
)
WHERE r.`role_key` = 'tenant';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
JOIN `permissions` p ON p.`permission_key` IN (
    'student.profile.read',
    'student.profile.update',
    'student.records.read',
    'residence.application.create',
    'residence.application.read',
    'residence.application.review',
    'residence.contract.read',
    'residence.contract.manage',
    'building.manage',
    'service.request.read',
    'service.request.manage',
    'rental.space.read',
    'rental.application.create',
    'rental.contract.read',
    'system.roles.manage'
)
WHERE r.`role_key` = 'admin';

CREATE TABLE IF NOT EXISTS `student_profiles` (
    `user_id` INT UNSIGNED NOT NULL,
    `student_number` VARCHAR(30) DEFAULT NULL,
    `faculty` VARCHAR(150) DEFAULT NULL,
    `program` VARCHAR(150) DEFAULT NULL,
    `academic_year` VARCHAR(20) DEFAULT NULL,
    `date_of_birth` DATE DEFAULT NULL,
    `emergency_contact_name` VARCHAR(100) DEFAULT NULL,
    `emergency_contact_phone` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_student_profiles_student_number` (`student_number`),
    CONSTRAINT `fk_student_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `student_profiles` (`user_id`, `student_number`)
SELECT `id`, NULLIF(TRIM(`student_id`), '')
FROM `users`
WHERE `role` = 'student';

CREATE TABLE IF NOT EXISTS `residence_applications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_number` VARCHAR(32) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `preferred_room_type` VARCHAR(30) NOT NULL DEFAULT 'any',
    `note` VARCHAR(1000) DEFAULT NULL,
    `status` ENUM('submitted', 'under_review', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'submitted',
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `decision_note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_applications_number` (`application_number`),
    KEY `idx_residence_applications_user_status` (`user_id`, `status`),
    KEY `idx_residence_applications_status_created` (`status`, `created_at`),
    CONSTRAINT `fk_residence_applications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_applications_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_application_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `application_id` INT UNSIGNED NOT NULL,
    `actor_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(30) NOT NULL,
    `from_status` VARCHAR(30) DEFAULT NULL,
    `to_status` VARCHAR(30) NOT NULL,
    `note` VARCHAR(1000) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_residence_events_application` (`application_id`, `created_at`),
    CONSTRAINT `fk_residence_events_application` FOREIGN KEY (`application_id`) REFERENCES `residence_applications` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_events_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `residence_contracts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_number` VARCHAR(32) NOT NULL,
    `application_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('pending_assignment', 'active', 'expired', 'terminated') NOT NULL DEFAULT 'pending_assignment',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_residence_contracts_number` (`contract_number`),
    UNIQUE KEY `uq_residence_contracts_application` (`application_id`),
    KEY `idx_residence_contracts_user_status` (`user_id`, `status`),
    CONSTRAINT `fk_residence_contracts_application` FOREIGN KEY (`application_id`) REFERENCES `residence_applications` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_residence_contracts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
