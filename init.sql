CREATE DATABASE IF NOT EXISTS mini_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mini_shop;

DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS slider_slides;
DROP TABLE IF EXISTS site_visits;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS site_users;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    author VARCHAR(200),
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50),
    address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Hazırlanıyor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE site_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    phone VARCHAR(50),
    address TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    role VARCHAR(50) DEFAULT 'standard'
);

CREATE TABLE user_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    address_title VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES site_users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

CREATE TABLE comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    author_name VARCHAR(150) NOT NULL,
    author_email VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    images TEXT NULL COMMENT 'JSON array of image URLs',
    rating TINYINT UNSIGNED NULL COMMENT '1-5 puan',
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE slider_slides (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sort_order INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE site_visits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_visit_date (visit_date)
);

-- Admin Kullanıcıları
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@gmail.com', '$2y$12$s7wXmcFVx8EZ9yBq88jvyeT73JSa.nZa56/rJxFvG3U9UYgH9suJK', 'admin');

-- Site Kullanıcıları
INSERT INTO site_users (username, email, password_hash, full_name, phone, status, role) VALUES
('User', 'user@gmail.com', '$2y$10$brDKpQsPqOlKjfspdefYeu9lwm//y1ERzLYC6XsMNguO8O8zA/MvW', 'User', '0555 555 55 55', 'active', 'standard');

-- Site Ayarları
INSERT INTO settings (setting_key, setting_value) VALUES
('comments_enabled', '1'),
('site_theme', 'dark'),
('site_name', 'ManRoMa');

-- Statik Sayfalar
INSERT INTO pages (title, slug, content) VALUES
('Hakkımızda', 'hakkimizda', 'ManRoMan; çizgi roman ve manga tutkunları için özel hazırlanmış bir çevrim içi vitrinidir.'),
('İletişim', 'iletisim', 'Bize 0 212 000 00 00 numarasından veya info@ManRoMan.local adresinden ulaşabilirsiniz.');

-- Kategoriler
INSERT INTO categories (name, slug, description) VALUES
('Manga', 'manga', 'Shonen, seinen ve shojo mangalar.'),
('Süper Kahraman Çizgi Romanları', 'super-kahraman', 'Marvel ve DC evrenlerinden klasik ve modern seriler.'),
('Bağımsız Çizgi Roman', 'bagimsiz-cizgi-roman', 'Bağımsız yayınevlerinden grafik romanlar ve tek sayılar.');

-- Ürünler (82 ürün)
INSERT INTO products (category_id, name, slug, description, author, price, stock, image_url) VALUES
(1, 'Akame Ga Kill! Cilt 1', 'akame-ga-kill-cilt-1', 'Köyünü yoksulluktan kurtarmak ve kahraman olmak hayaliyle Başkent\'e gelen genç savaşçı Tatsumi, şehrin ışıltılı görünümünün altındaki korkunç yozlaşmayla yüzleşir. Aldatılmış ve parasız kalmışken, gecenin karanlığında adalet dağıtan acımasız suikastçı grubu Night Raid ile yolları kesişir.', 'Takahiro & Tetsuya Tashiro', 135.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_1.jpg'),
(1, 'Akame Ga Kill! Cilt 4', 'akame-ga-kill-cilt-4', 'Başkentteki gerilim zirveye ulaşıyor! Night Raid\'in karşısında artık İmparatorluğun en korkulan generali Esdeath ve onun yeni kurduğu özel polis birliği \'Jaegers\' (Avcılar) var.', 'Takahiro & Tetsuya Tashiro', 149.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_4.jpg'),
(1, 'Akame Ga Kill! Cilt 5', 'akame-ga-kill-cilt-5', 'Night Raid için kaçacak yer kalmadı! Jaegers ekibinin çılgın bilim insanı Dr. Stylish, Night Raid\'in gizli sığınağının yerini tespit etmeyi başarıyor.', 'Takahiro & Tetsuya Tashiro', 164.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_5.jpg'),
(1, 'Akame Ga Kill! Cilt 7', 'akame-ga-kill-cilt-7', 'Devrim ateşi tüm ülkeye yayılırken, Night Raid\'in yeni hedefi Başkent\'ten çok uzakta! Dini örgüt \'Barış Yolu\'nu (Path of Peace) içten çökertmeye çalışan İmparatorluk ajanı Bolic\'in ortadan kaldırılması gerekiyor.', 'Takahiro & Tetsuya Tashiro', 148.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_7.jpg'),
(1, 'Akame Ga Kill! Cilt 10', 'akame-ga-kill-cilt-10', 'Başkent\'te sular durulmuyor! İmparatorluk, Night Raid\'i tuzağa düşürmek için şeytani bir plan hazırlar ve tüm şehri sarsacak büyük bir infaz duyurusu yapar.', 'Takahiro & Tetsuya Tashiro', 199.99, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_10.jpg'),
(1, 'Akame Ga Kill! Cilt 15', 'akame-ga-kill-cilt-15', 'Efsanevi serinin büyük finali! İmparatorluk ve Devrim Ordusu arasındaki kanlı savaş artık son perdesine ulaşıyor.', 'Takahiro & Tetsuya Tashiro', 249.99, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_15.jpg'),
(1, 'Berserk Cilt 1', 'berserk-cilt-1', 'Sırtında insan boyunda devasa bir kılıç, demirden bir takma el ve boynunda kanayan lanetli bir damga... O, Kara Kılıç Ustası Guts.', 'Kentaro Miura', 149.99, 100, '/mini_shop/products_img/Berserk_Cilt_1.jpg'),
(1, 'Bleach Cilt 25', 'bleach-cilt-25', 'Karakura Kenti saldırı altında! Aizen\'in korkunç ordusu Arrancarlar, dünyaya inerek Ichigo ve arkadaşlarını hedef alıyor.', 'Tite Kubo', 210.00, 100, '/mini_shop/products_img/Bleach_Cilt_25.jpg'),
(1, 'Bleach Cilt 27', 'bleach-cilt-27', 'Aizen\'in karanlık planları işlemeye devam ederken, hedefte bu kez Orihime Inoue var!', 'Tite Kubo', 220.00, 100, '/mini_shop/products_img/Bleach_Cilt_27.webp'),
(1, 'Bleach Cilt 49', 'bleach-cilt-49', 'Büyük savaştan 17 ay sonra... Karakura Kenti\'nde hayat normale dönmüş gibi görünse de, Ichigo Kurosaki için sessizlik hakim.', 'Tite Kubo', 285.00, 100, '/mini_shop/products_img/Bleach_Cilt_49.jpg'),
(1, 'Bleach Cilt 54', 'bleach-cilt-54', 'Umutsuzluğun en derin olduğu anda, karanlığı yaracak bir ışık beliriyor!', 'Tite Kubo', 249.90, 100, '/mini_shop/products_img/Bleach_Cilt_54.jpg'),
(1, 'Dragon Ball Super Cilt 1', 'dragon-ball-super-cilt-1', 'Majin Buu\'nun yenilgiye uğratılmasının üzerinden zaman geçmiş, Dünya nihayet hak ettiği barışa kavuşmuştur.', 'Akira Toriyama & Toyotarou', 175.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_1.jpg'),
(1, 'Dragon Ball Super Cilt 14', 'dragon-ball-super-cilt-14', 'Gezegen yiyen büyücü Moro, sonunda Dünya\'ya ayak basıyor!', 'Akira Toriyama & Toyotarou', 180.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_14.jpg'),
(1, 'Dragon Ball Super Cilt 15', 'dragon-ball-super-cilt-15', 'Galaktik Devriye Tutuklusu hikayesinin (Moro Arc) nefes kesen finali!', 'Akira Toriyama & Toyotarou', 189.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_15.webp'),
(1, 'Dragon Ball Super Cilt 17', 'dragon-ball-super-cilt-17', 'Evrenin en güçlüsü kim olacak? Cereal gezegeninin son hayatta kalan üyesi Granolah, halkının intikamını almak için Dragon Ball\'ları kullanarak bedeli ağır bir dilek diler.', 'Akira Toriyama & Toyotarou', 199.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_17.jpg'),
(1, 'Dragon Ball Super Cilt 20', 'dragon-ball-super-cilt-20', 'Cereal Gezegeni\'ndeki savaşta limitler zorlanıyor!', 'Akira Toriyama & Toyotarou', 194.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_20.jpg'),
(1, 'Dragon Ball Super Cilt 21', 'dragon-ball-super-cilt-21', 'Evrenin en güçlüsü olma savaşı sona erdi, şimdi sahne yeni neslin!', 'Akira Toriyama & Toyotarou', 210.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_21.jpg'),
(1, 'Jujutsu Kaisen Cilt 1', 'jujutsu-kaisen-cilt-1', 'İnsanların negatif duygularından doğan korkunç \'Lanetler\' aramızda dolaşıyor!', 'Gege Akutami', 149.90, 100, '/mini_shop/products_img/Jujutsu_Kaisen_Cilt1.jpg'),
(1, 'Jujutsu Kaisen Cilt 2', 'jujutsu-kaisen-cilt-2', 'Özel Seviye bir lanetle ilk karşılaşma!', 'Gege Akutami', 164.90, 100, '/mini_shop/products_img/Jujutsu_Kaisen_Cilt2.jpg'),
(1, 'Naruto Cilt 1', 'naruto-cilt-1', 'Konoha Köyü\'nde yaramazlık denince akla gelen tek bir isim var: Naruto Uzumaki!', 'Masashi Kishimoto', 110.00, 100, '/mini_shop/products_img/Naruto_Cilt1.jpg'),
(1, 'Naruto Cilt 3', 'naruto-cilt-3', 'Köprü üzerindeki savaş başlıyor!', 'Masashi Kishimoto', 120.00, 100, '/mini_shop/products_img/Naruto_Cilt3.jpg'),
(1, 'Naruto Cilt 35', 'naruto-cilt-35', 'Naruto, Kakashi ve Yamato\'nun gözetiminde, Rasengan\'ı tamamlamak için insanüstü bir antrenman sürecine giriyor.', 'Masashi Kishimoto', 180.00, 100, '/mini_shop/products_img/Naruto_Cilt35.jpg'),
(1, 'Naruto Cilt 46', 'naruto-cilt-46', 'Konoha tarihinin en karanlık saati!', 'Masashi Kishimoto', 184.99, 100, '/mini_shop/products_img/Naruto_Cilt46.jpg'),
(1, 'Naruto Cilt 67', 'naruto-cilt-67', 'Savaş alanında dengeler tamamen değişiyor!', 'Masashi Kishimoto', 199.90, 100, '/mini_shop/products_img/Naruto_Cilt67.jpg'),
(1, 'Naruto Cilt 72', 'naruto-cilt-72', 'Bir efsanenin sonu!', 'Masashi Kishimoto', 204.99, 100, '/mini_shop/products_img/Naruto_Cilt72.jpg'),
(1, 'One Piece Cilt 1', 'one-piece-cilt-1', 'Zenginlik, şöhret, güç... Korsanlar Kralı Gold Roger, idam edilmeden önce tüm dünyaya meydan okuyan o sözleri söyledi ve Büyük Korsanlar Çağı\'nı başlattı!', 'Eiichiro Oda', 89.90, 100, '/mini_shop/products_img/One_Piece_Cilt1.jpg'),
(1, 'One Piece Cilt 3', 'one-piece-cilt-3', 'Palyaço Buggy ile olan mücadele sona eriyor!', 'Eiichiro Oda', 94.99, 100, '/mini_shop/products_img/One_Piece_Cilt3.jpg'),
(1, 'One Piece Cilt 9', 'one-piece-cilt-9', 'Nami\'nin ihaneti mi, yoksa çaresizliği mi?', 'Eiichiro Oda', 109.90, 100, '/mini_shop/products_img/One_Piece_Cilt9.jpg'),
(1, 'One Piece Cilt 100', 'one-piece-cilt-100', 'Efsanevi destanda tarihi bir an!', 'Eiichiro Oda', 189.90, 100, '/mini_shop/products_img/One_Piece_Cilt100.jpg'),
(1, 'One Piece Cilt 105', 'one-piece-cilt-105', 'Wano Ülkesi\'nde nihayet şafak söküyor!', 'Eiichiro Oda', 199.90, 100, '/mini_shop/products_img/One_Piece_Cilt105.jpg'),
(1, 'One Piece Cilt 107', 'one-piece-cilt-107', 'Geleceğin Adası Egghead\'de işler çığırından çıkıyor!', 'Eiichiro Oda', 204.99, 100, '/mini_shop/products_img/One_Piece_Cilt107.jpg'),
(1, 'One Piece Cilt 108', 'one-piece-cilt-108', 'Dünya sarsılmaya devam ediyor!', 'Eiichiro Oda', 209.99, 100, '/mini_shop/products_img/One_Piece_Cilt108.jpg'),
(1, 'One Piece Cilt 110', 'one-piece-cilt-110', 'Dünyanın kaderini değiştirecek yayın başladı!', 'Eiichiro Oda', 219.90, 100, '/mini_shop/products_img/One_Piece_Cilt110.jpg'),
(1, 'One Piece Cilt 111', 'one-piece-cilt-111', 'Tarihe \'Egghead Olayı\' olarak geçecek o büyük gün sona eriyor!', 'Eiichiro Oda', 224.99, 100, '/mini_shop/products_img/One_Piece_Cilt111.jpg'),
(1, 'Solo Leveling Cilt 1 Özel Kapak', 'solo-leveling-cilt-1-ozel-kapak', 'Dünyanın En Zayıf Avcısı lakabıyla tanınan E-Seviye avcı Sung Jinwoo ile tanışın.', 'Chugong & DUBU (Redice Studio)', 349.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt1_Ozel_Kapak.jpg'),
(1, 'Solo Leveling Cilt 1', 'solo-leveling-cilt-1', '10 yıl önce, dünyayı canavarlarla dolu başka bir boyuta bağlayan \'Kapı\'lar açıldı ve insanüstü güçlere sahip \'Avcılar\' ortaya çıktı.', 'Chugong & DUBU (Redice Studio)', 174.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt1.jpg'),
(1, 'Solo Leveling Cilt 2', 'solo-leveling-cilt-2', 'Çifte Zindan faciasından mucizevi bir şekilde sağ kurtulan Sung Jinwoo, hastane odasında uyandığında hiçbir şeyin eskisi gibi olmadığını fark eder.', 'Chugong & DUBU (Redice Studio)', 179.99, 100, '/mini_shop/products_img/Solo_Leveling_Cilt2.jpg'),
(1, 'Solo Leveling Cilt 3', 'solo-leveling-cilt-3', 'Sung Jinwoo, Sistem sayesinde her geçen gün güçlenmeye devam ediyor ancak avcı dünyasının acımasız kuralları henüz onun peşini bırakmış değil.', 'Chugong & DUBU (Redice Studio)', 184.99, 100, '/mini_shop/products_img/Solo_Leveling_Cilt3.jpg'),
(1, 'Solo Leveling Cilt 5', 'solo-leveling-cilt-5', 'Basit bir eğitim görevi, aniden ölümcül bir kabusa dönüşüyor!', 'Chugong & DUBU (Redice Studio)', 189.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt5.jpg'),
(1, 'Solo Leveling Cilt 7', 'solo-leveling-cilt-7', 'Sung Jinwoo\'nun en zorlu ve en kişisel görevi başlıyor!', 'Chugong & DUBU (Redice Studio)', 199.99, 98, '/mini_shop/products_img/Solo_Leveling_Cilt7.jpg'),
(1, 'Solo Leveling Cilt 8', 'solo-leveling-cilt-8', 'Beklenen an nihayet geldi!', 'Chugong & DUBU (Redice Studio)', 209.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt8.jpg'),
(1, 'The World After the Fall Cilt 1', 'the-world-after-the-fall-cilt-1', 'Dünya ansızın beliren devasa kulelerin gölgesinde yıkımla yüzleştiğinde, insanlığın tek umudu kulelere çağrılan \'Yürüyüşçüler\' oldu.', 'singNsong & Undead Gamja', 149.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt1.jpg'),
(1, 'The World After the Fall Cilt 2', 'the-world-after-the-fall-cilt-2', 'Kule\'nin zirvesine ulaşmak bir son değil, sadece korkunç bir başlangıçtı!', 'singNsong & Undead Gamja', 159.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt2.jpg'),
(1, 'The World After the Fall Cilt 3', 'the-world-after-the-fall-cilt-3', 'Kaos\'un kalbine, Gorgon Kalesi\'ne yolculuk!', 'singNsong & Undead Gamja', 164.99, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt3.jpg'),
(1, 'The World After the Fall Cilt 4', 'the-world-after-the-fall-cilt-4', 'Gorgon Kalesi\'nde ipler geriliyor!', 'singNsong & Undead Gamja', 169.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt4.jpg'),
(1, 'The World After the Fall Cilt 9', 'the-world-after-the-fall-cilt-9', 'Kaos\'un sınırlarını aşan Jaehwan için artık hedef çok daha yükseklerde: Büyük Topraklar.', 'singNsong & Undead Gamja', 189.95, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt9.jpg'),
(1, 'Vinland Saga Cilt 1', 'vinland-saga-cilt-1', '11. yüzyılın başlarında, Kuzey Avrupa buzlu denizlerin hakimi Vikinglerin istilası altındadır.', 'Makoto Yukimura', 139.99, 100, '/mini_shop/products_img/Vinland_Saga_Cilt1.jpg'),
(1, 'Vinland Saga Cilt 6', 'vinland-saga-cilt-6', 'York\'ta zafer kutlamaları sürerken, Kral Sweyn\'in nihai planı ortaya çıkıyor.', 'Makoto Yukimura', 159.90, 100, '/mini_shop/products_img/Vinland_Saga_Cilt6.jpg'),
(1, 'Vinland Saga Cilt 18', 'vinland-saga-cilt-18', 'Baltık Denizi\'nde sular durulmuyor!', 'Makoto Yukimura', 164.90, 100, '/mini_shop/products_img/Vinland_Saga_Cilt18.jpg'),
(1, 'Vinland Saga Cilt 21', 'vinland-saga-cilt-21', 'Bir kafes kuşu olmayı reddeden Gudrid, özgürlüğe kanat açıyor!', 'Makoto Yukimura', 179.99, 100, '/mini_shop/products_img/Vinland_Saga_Cilt21.jpg'),
(2, 'Amazing Spider-Man Cilt 27 Kör Uçuş', 'amazing-spider-man-cilt-27-kor-ucus', 'Örümcek Hissi (Spider-Sense) tamamen KÖR!', 'Nick Spencer', 115.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Cilt_27_Kor_Ucus.jpg'),
(2, 'Amazing Spider-Man Vol. 5 Cilt 02 - Dostlar ve Düşmanlar', 'amazing-spider-man-vol-5-cilt-02-dostlar-ve-dusmanlar', 'Dostlar ve Düşmanlar! Peter Parker\'ın hayatı, Mary Jane Watson ile yeniden bir araya gelerek yavaşça düzene girmeye başlamıştır.', 'Nick Spencer', 120.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_02_-_Dostlar_ve_Dusmanlar.jpg'),
(2, 'Amazing Spider-Man Vol. 5 Cilt 06 - Absolute Carnage', 'amazing-spider-man-vol-5-cilt-06-absolute-carnage', 'Mutlak Katliam Başlıyor!', 'Nick Spencer', 140.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_06_-_Absolute_Carnage.jpg'),
(2, 'Amazing Spider-Man Vol. 5 Cilt 07 - 2099', 'amazing-spider-man-vol-5-cilt-07-2099', 'Gelecek Çöküyor!', 'Nick Spencer', 155.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_07_-_2099.jpg'),
(2, 'Amazing Spider-Man Vol. 5 Cilt 08 - Tehditler ve Belalar', 'amazing-spider-man-vol-5-cilt-08-tehditler-ve-belalar', 'Tehditler ve Belalar!', 'Nick Spencer', 170.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_08_-_Tehditler_ve_Belalar.jpg'),
(2, 'Flash - Yaşayan En Hızlı Adam', 'flash-yasayan-en-hizli-adam', 'Barry Allen\'ın hayatı, Central City Polis Departmanı\'nda adli tıp bilimcisi olarak devam ederken, evrenin en saf ve güçlü enerjisi olan Hız Gücü (Speed Force) ile birleşerek onu dünyanın en hızlı adamına dönüştürdü.', 'Joshua Williamson', 180.00, 100, '/mini_shop/products_img/Flash_-_Yasayan_En_Hizli_Adam.jpg'),
(2, 'Flash Cilt 3 Goril Savaşı', 'flash-cilt-3-goril-savasi', 'Central City, zekasıyla insanlığı bile aşan Goril Grodd ve devasa ordusunun kuşatması altında!', 'Francis Manapul & Brian Buccellato', 195.00, 99, '/mini_shop/products_img/Flash_Cilt_3_Goril_Savasi.jpg'),
(2, 'Flash Cilt 8 - Flash Savaşı', 'flash-cilt-8-flash-savasi', 'Hızın Son Savaşı!', 'Joshua Williamson', 210.00, 100, '/mini_shop/products_img/Flash_Cilt_8_-_Flash_Savasi.jpg'),
(2, 'İç Savaş 2 Amazing Spider-Man X-Men', 'ic-savas-2-amazing-spider-man-x-men', 'Geleceği Durdurmak mı, Kontrol Etmek mi?', 'Dan Slott & Cullen Bunn', 245.00, 100, '/mini_shop/products_img/Ic_Savas_2_Amazing_Spider-Man_X-Men.jpg'),
(2, 'Moon Knight (2014) Cilt 1 Ölümden Geriye', 'moon-knight-2014-cilt-1-olumden-geriye', 'Marc Spector, aldığı ağır darbelerden sonra ölümden geri döndü.', 'Warren Ellis', 225.00, 100, '/mini_shop/products_img/Moon_Knight_2014_Cilt_1_Olumden_Geriye.jpg'),
(2, 'Moon Knight (2016) Cilt 2 Reenkarnasyonlar', 'moon-knight-2016-cilt-2-reenkarnasyonlar', 'Moon Knight\'ın mistik ve psikolojik yolculuğu daha da derinleşiyor!', 'Jeff Lemire', 205.00, 100, '/mini_shop/products_img/Moon_Knight_2016_Cilt_2_Reenkarnasyonlar.jpg'),
(2, 'Rebirth Flash 2 - Karanlık Hız', 'rebirth-flash-2-karanlik-hiz', 'Hızın Karanlık Yüzü!', 'Joshua Williamson', 145.00, 100, '/mini_shop/products_img/Rebirth_Flash_2_-_Karanlik_Hiz.jpg'),
(2, 'Spider-Man - Kuantum Macerası', 'spider-man-kuantum-macerasi', 'Zamanın Sınırlarında Bir Ağ!', 'Dan Slott', 140.00, 100, '/mini_shop/products_img/Spider-Man_-_Kuantum_Macerasi.jpg'),
(2, 'Spider-Man George Ve Gwen Stacy\'nin Ölümü', 'spider-man-george-ve-gwen-stacy-nin-olumu', 'Çizgi Roman Tarihinin En Büyük Trajedileri!', 'Stan Lee & Gerry Conway', 150.00, 100, '/mini_shop/products_img/Spider-Man_George_Ve_Gwen_Stacy_nin_Olumu.jpg'),
(2, 'Spider-Punk - Yasaklananların Savaşı', 'spider-punk-yasaklananlarin-savasi', 'Anarşinin Örümcek Ağı!', 'Cody Ziglar', 160.00, 100, '/mini_shop/products_img/Spider-Punk_-_Yasaklananlarin_Savasi.jpg'),
(2, 'Venom (2018) Cilt 3 Absolute Carnage Cilt 1', 'venom-2018-cilt-3-absolute-carnage-cilt-1', 'Mutlak Dehşet Başlıyor!', 'Donny Cates', 170.00, 100, '/mini_shop/products_img/Venom_2018_Cilt_3_Absolute_Carnage_Cilt_1.jpg'),
(2, 'Venom (2018) Cilt 6 - Venom Alternatif Dünyada', 'venom-2018-cilt-6-venom-alternatif-dunyada', 'Çoklu Evrenin Venom\'ları!', 'Donny Cates', 172.50, 100, '/mini_shop/products_img/Venom_2018_Cilt_6_-_Venom_Alternatif_Dunyada.jpg'),
(3, 'Rick And Morty Sayı 57', 'rick-and-morty-sayi-57', 'Rick\'in kaotik dehası bir kez daha zincirlerinden boşanıyor!', 'Zac Gorman & Kyle Starks', 100.00, 100, '/mini_shop/products_img/Rick_And_Morty_Sayi_57.jpg'),
(3, 'Rick And Morty Sayı 59', 'rick-and-morty-sayi-59', 'Rick\'in kaotik dehası bir kez daha zincirlerinden boşanıyor ve Morty\'nin hayatı bir saniyede altüst oluyor!', 'Zac Gorman & Kyle Starks', 110.00, 100, '/mini_shop/products_img/Rick_And_Morty_Sayi_59.jpg'),
(3, 'Ay Polisi', 'ay-polisi', 'Ay üzerinde kanun ve düzeni sağlamanın nasıl bir iş olduğunu merak ettiniz mi?', 'Tom Gauld', 95.00, 100, '/mini_shop/products_img/Ay_Polisi.jpg'),
(3, 'KUDÜS GÜNLÜKLERİ', 'kudus-gunlukleri', 'Belgesel çizgi roman türünün usta isimlerinden Guy Delisle, bu kez rotayı Kudüs\'e çeviriyor.', 'Guy Delisle', 75.00, 100, '/mini_shop/products_img/KUDUS_GUNLUKLERI.jpg'),
(3, 'Görünmez Krallık 1 - O Yolda Yürümek', 'gorunmez-krallik-1-o-yolda-yurumek', 'Kozmik bir bilim kurgu ve mistisizm şöleni!', 'G. Willow Wilson', 99.00, 100, '/mini_shop/products_img/Gorunmez_Krallik_1_-_O_Yolda_Yurumek.jpg'),
(3, 'Müjdeci 1. Cilt Teneke Yıldızlar', 'mujdeci-1-cilt-teneke-yildizlar', 'Ay\'da doğan, distopik ve sürükleyici bir bilim kurgu masalı!', 'Jeff Lemire', 65.00, 100, '/mini_shop/products_img/Mujdeci_1_Cilt_Teneke_Yildizlar.jpg'),
(3, 'Komançi 4', 'komanci-4', 'Vahşi Batı\'nın sert ve acımasız atmosferinde geçen, Avrupa çizgi romanının en önemli Western serilerinden biri!', 'Greg', 95.00, 100, '/mini_shop/products_img/Komanci_4_1.jpg'),
(3, 'Blankets - Örtüler', 'blankets-ortuler', 'Otobiyografik çizgi roman türünün mihenk taşlarından biri!', 'Craig Thompson', 90.00, 100, '/mini_shop/products_img/Blankets_-_Ortuler.jpg'),
(3, 'Kim Korkar Hain Tilkiden', 'kim-korkar-hain-tilkiden', 'Bazen tilki, tilki olmaktan vazgeçer; bazen de anne horoz olur!', 'Benjamin Renner', 85.00, 100, '/mini_shop/products_img/Kim_Korkar_Hain_Tilkiden.jpg'),
(3, 'Çizgi Romanı Anlamak', 'cizgi-romani-anlamak', 'Çizgi romanları okumayı seviyor musunuz? Peki onları \'anlamayı\' denediniz mi?', 'Scott McCloud', 49.90, 100, '/mini_shop/products_img/Cizgi_Romani_Anlamak.jpg'),
(3, 'Bu Bizim Anlaşmamız', 'bu-bizim-anlasmamiz', 'Bazen bazı sırlar, sadece en yakın arkadaşların arasında kalmalıdır.', 'Ryan Andrews', 75.00, 100, '/mini_shop/products_img/Bu_Bizim_Anlasmamiz.jpg'),
(3, 'Gece Kütüphanecisi', 'gece-kutuphanecisi', 'Kütüphaneler sadece kitapların saklandığı yerler değildir...', 'Christopher Lincoln', 60.00, 100, '/mini_shop/products_img/Gece_Kutuphanecisi.jpg'),
(3, 'Zaman Makinesi', 'zaman-makinesi', 'Bilim kurgu edebiyatının babası H.G. Wells\'in zamana meydan okuyan klasik eseri, görsel bir şölenle yeniden canlanıyor!', 'Dobbs', 69.90, 100, '/mini_shop/products_img/Zaman_Makinesi.jpg'),
(3, 'Zenobia-Bir Göçmen Hikayesi', 'zenobia-bir-gocmen-hikayesi', 'Savaşın, ayrılığın ve umudun hikayesi...', 'Dürr', 115.00, 100, '/mini_shop/products_img/Zenobia-Bir_Gocmen_Hikayesi.jpg'),
(3, 'Üç Gölge', 'uc-golge', 'Mutluluğun ve pastoral yaşamın aniden tehdit altına alınışının lirik ve masalsı hikayesi...', 'Cyril Pedrosa', 130.00, 100, '/mini_shop/products_img/Uc_Golge.jpg');

