ALTER TABLE comments ADD COLUMN images TEXT NULL COMMENT 'JSON array of image URLs' AFTER content;
