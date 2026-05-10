-- Thêm index để tối ưu truy vấn
ALTER TABLE messages ADD INDEX idx_conversation_id (conversation_id);
ALTER TABLE transactions ADD INDEX idx_buyer_id (buyer_id);
ALTER TABLE transactions ADD INDEX idx_seller_id (seller_id);
ALTER TABLE products ADD INDEX idx_status (status);
ALTER TABLE products ADD INDEX idx_created_at (created_at);
