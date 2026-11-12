-- Conditional index creation keeps fresh-schema setup and existing databases compatible.
SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'messages' AND index_name = 'idx_conversation_id'
);
SET @index_sql = IF(@index_exists = 0, 'ALTER TABLE messages ADD INDEX idx_conversation_id (conversation_id)', 'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'transactions' AND index_name = 'idx_buyer_id'
);
SET @index_sql = IF(@index_exists = 0, 'ALTER TABLE transactions ADD INDEX idx_buyer_id (buyer_id)', 'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'transactions' AND index_name = 'idx_seller_id'
);
SET @index_sql = IF(@index_exists = 0, 'ALTER TABLE transactions ADD INDEX idx_seller_id (seller_id)', 'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'products' AND index_name = 'idx_status'
);
SET @index_sql = IF(@index_exists = 0, 'ALTER TABLE products ADD INDEX idx_status (status)', 'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;

SET @index_exists = (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'products' AND index_name = 'idx_created_at'
);
SET @index_sql = IF(@index_exists = 0, 'ALTER TABLE products ADD INDEX idx_created_at (created_at)', 'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;
