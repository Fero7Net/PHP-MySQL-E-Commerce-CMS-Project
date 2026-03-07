CREATE TABLE IF NOT EXISTS slider_slides (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sort_order INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO slider_slides (sort_order, image_url, alt_text) VALUES
(1, 'img/Banner1.png', 'Banner 1'),
(2, 'img/Banner2.png', 'Banner 2'),
(3, 'img/Banner3.png', 'Banner 3');
