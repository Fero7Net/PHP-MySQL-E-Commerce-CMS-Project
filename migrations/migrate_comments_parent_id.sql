ALTER TABLE comments ADD COLUMN parent_id INT UNSIGNED NULL DEFAULT NULL AFTER product_id;
ALTER TABLE comments ADD INDEX idx_parent_id (parent_id);
