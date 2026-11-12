-- Weeks 13-14: private local triage, rules assistant permissions and aggregate dashboard.
-- Additive only. Apply after the KTX foundation, operations, and week 9-12 migrations.

INSERT IGNORE INTO permissions (permission_key, name) VALUES
    ('residence.ai.repair.classify', 'Phân loại cục bộ yêu cầu sửa chữa'),
    ('residence.ai.rules_assistant.use', 'Sử dụng trợ lý nội quy KTX'),
    ('residence.operations.dashboard.read', 'Xem dashboard vận hành KTX');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'residence.ai.repair.classify',
    'residence.ai.rules_assistant.use'
)
WHERE r.role_key IN ('student', 'tenant', 'service_staff', 'dorm_manager', 'admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key = 'residence.operations.dashboard.read'
WHERE r.role_key IN ('dorm_manager', 'admin');

CREATE TABLE IF NOT EXISTS residence_service_request_classifications (
    request_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(40) NOT NULL,
    confidence DECIMAL(5,4) NOT NULL,
    recommended_priority ENUM('normal', 'high', 'urgent') NOT NULL,
    reason VARCHAR(255) NOT NULL,
    model_key VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (request_id),
    CONSTRAINT fk_service_request_classification_request
        FOREIGN KEY (request_id) REFERENCES residence_service_requests (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
