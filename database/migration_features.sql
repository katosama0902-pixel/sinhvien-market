-- ============================================================
--  SinhVienMarket — Migration: Thêm tính năng mới (Features v2)
--  Tương thích: MySQL 8.x & MariaDB 10.x
--  ⚠️  KHÔNG DROP TABLE — An toàn với dữ liệu hiện có
-- ============================================================

SET NAMES utf8mb4;
USE `sinhvien_market`;

-- ============================================================
--  Dùng Stored Procedure để kiểm tra cột trước khi ADD
--  (tương thích cả MySQL 8.x lẫn MariaDB)
-- ============================================================

DROP PROCEDURE IF EXISTS sp_add_column_safe;

DELIMITER $$
CREATE PROCEDURE sp_add_column_safe(
    IN tbl_name VARCHAR(64),
    IN col_name VARCHAR(64),
    IN col_def  TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl_name
          AND COLUMN_NAME  = col_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl_name, '` ADD COLUMN `', col_name, '` ', col_def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Đã thêm cột: ', tbl_name, '.', col_name) AS result;
    ELSE
        SELECT CONCAT('⏭️  Cột đã tồn tại, bỏ qua: ', tbl_name, '.', col_name) AS result;
    END IF;
END$$
DELIMITER ;

-- ============================================================
--  Thêm index an toàn
-- ============================================================

DROP PROCEDURE IF EXISTS sp_add_index_safe;

DELIMITER $$
CREATE PROCEDURE sp_add_index_safe(
    IN tbl_name VARCHAR(64),
    IN idx_name VARCHAR(64),
    IN col_name VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl_name
          AND INDEX_NAME   = idx_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl_name, '` ADD INDEX `', idx_name, '` (`', col_name, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('✅ Đã thêm index: ', idx_name) AS result;
    ELSE
        SELECT CONCAT('⏭️  Index đã tồn tại, bỏ qua: ', idx_name) AS result;
    END IF;
END$$
DELIMITER ;

-- ============================================================
--  BẢNG users — Feature 1: Huy hiệu Sinh Viên Đã Xác Thực
-- ============================================================

CALL sp_add_column_safe(
    'users', 'is_student_verified',
    "TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = đã xác thực là sinh viên (@edu.vn / @ac.vn)' AFTER `available_time`"
);

-- ============================================================
--  BẢNG users — Feature 2: Xu & Check-in hàng ngày
-- ============================================================

CALL sp_add_column_safe(
    'users', 'coins',
    "INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số xu hiện có' AFTER `is_student_verified`"
);

CALL sp_add_column_safe(
    'users', 'last_checkin',
    "DATE DEFAULT NULL COMMENT 'Ngày check-in gần nhất để chặn 2 lần/ngày' AFTER `coins`"
);

CALL sp_add_index_safe('users', 'idx_student_verified', 'is_student_verified');

-- ============================================================
--  BẢNG products — Feature 3: Tình trạng sản phẩm
-- ============================================================

CALL sp_add_column_safe(
    'products', 'condition',
    "ENUM('new','like_new','used','worn') NOT NULL DEFAULT 'used' COMMENT 'Tình trạng: new=Mới 100%, like_new=Như mới, used=Đã dùng, worn=Cũ & có dấu vết' AFTER `price`"
);

-- ============================================================
--  BẢNG products — Feature 2: Đẩy Tin
-- ============================================================

CALL sp_add_column_safe(
    'products', 'bumped_at',
    "TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời điểm đẩy tin gần nhất (NULL = chưa từng đẩy)' AFTER `condition`"
);

CALL sp_add_index_safe('products', 'idx_condition', 'condition');
CALL sp_add_index_safe('products', 'idx_bumped_at', 'bumped_at');

-- ============================================================
--  Dọn dẹp helper procedures
-- ============================================================

DROP PROCEDURE IF EXISTS sp_add_column_safe;
DROP PROCEDURE IF EXISTS sp_add_index_safe;

-- ============================================================
--  Kiểm tra kết quả cuối
-- ============================================================

SELECT '━━━━━━━━━━━━━━━━━━━━━━━━ KẾT QUẢ MIGRATION ━━━━━━━━━━━━━━━━━━━━━━━━' AS '';

SELECT
    COLUMN_NAME                           AS `Tên cột`,
    TABLE_NAME                            AS `Bảng`,
    COLUMN_TYPE                           AS `Kiểu dữ liệu`,
    COLUMN_DEFAULT                        AS `Mặc định`,
    COLUMN_COMMENT                        AS `Mô tả`
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'sinhvien_market'
  AND TABLE_NAME IN ('users', 'products')
  AND COLUMN_NAME IN ('is_student_verified', 'coins', 'last_checkin', 'condition', 'bumped_at')
ORDER BY TABLE_NAME, ORDINAL_POSITION;
