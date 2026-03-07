ALTER TABLE users ADD COLUMN phone VARCHAR(50) NULL AFTER email;
CREATE TABLE IF NOT EXISTS admin_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    address_title VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id)
);
