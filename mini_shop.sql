-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 07 Mar 2026, 00:30:32
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `mini_shop`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_addresses`
--

CREATE TABLE `admin_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `address_title` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Manga', 'manga', 'Shonen, seinen ve shojo mangalar.', '2025-11-26 15:39:06'),
(2, 'Süper Kahraman Çizgi Romanları', 'super-kahraman', 'Marvel ve DC evrenlerinden klasik ve modern seriler.', '2025-11-26 15:39:06'),
(3, 'Bağımsız Çizgi Roman', 'bagimsiz-cizgi-roman', 'Bağımsız yayınevlerinden grafik romanlar ve tek sayılar.', '2025-11-26 15:39:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `author_name` varchar(150) NOT NULL,
  `author_email` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `images` text DEFAULT NULL COMMENT 'JSON array of image URLs',
  `rating` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1-5 puan',
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Hazırlanıyor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `created_at`) VALUES
(1, 'Hakkımızda', 'hakkimizda', 'ManRoMan; çizgi roman ve manga tutkunları için özel hazırlanmış bir çevrim içi vitrinidir.', '2025-11-26 15:39:06'),
(2, 'İletişim', 'iletisim', 'Bize 0 212 000 00 00 numarasından veya info@ManRoMan.local adresinden ulaşabilirsiniz.', '2025-11-26 15:39:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `author`, `price`, `stock`, `image_url`, `created_at`) VALUES
(1, 1, 'Akame Ga Kill! Cilt 1', 'akame-ga-kill-cilt-1', '\"Köyünü yoksulluktan kurtarmak ve kahraman olmak hayaliyle Başkent\'e gelen genç savaşçı Tatsumi, şehrin ışıltılı görünümünün altındaki korkunç yozlaşmayla yüzleşir. Aldatılmış ve parasız kalmışken, gecenin karanlığında adalet dağıtan acımasız suikastçı grubu Night Raid ile yolları kesişir.\r\n\r\nGrubun en ölümcül kılıç ustası Akame ile tanışması, Tatsumi\'yi geri dönüşü olmayan bir yola sokacaktır. İmparatorluğun karanlık sırlarına, nefes kesen düellolara ve etik ikilemlere hazır olun. Karanlık fantezi türünün en çarpıcı örneklerinden biri olan Akame ga Kill!, 1. Cildi ile okuyucuyu soluksuz bir maceraya davet ediyor.\"', 'Takahiro & Tetsuya Tashiro', 135.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_1.jpg', '2025-11-29 06:26:01'),
(2, 1, 'Akame Ga Kill! Cilt 4', 'akame-ga-kill-cilt-4', '\"Başkentteki gerilim zirveye ulaşıyor! Night Raid\'in karşısında artık İmparatorluğun en korkulan generali Esdeath ve onun yeni kurduğu özel polis birliği \'Jaegers\' (Avcılar) var.\r\n\r\nGücünü sınamak için bir dövüş turnuvasına gizlice katılan Tatsumi, şampiyon olmayı başarır ancak hiç beklemediği bir ödül kazanır: General Esdeath\'in takıntılı aşkı! Düşmanın tam kalbine, hem de en tehlikeli kadının \'sevgilisi\' olarak sürüklenen Tatsumi için hayatta kalmak hiç bu kadar zor olmamıştı. Aksiyonun ve kara mizahın iç içe geçtiği Akame ga Kill! 4. Cilt, sizi ters köşeye yatıracak olaylarla dolu.\"', 'Takahiro & Tetsuya Tashiro', 149.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_4.jpg', '2025-11-29 06:28:55'),
(3, 1, 'Akame Ga Kill! Cilt 5', 'akame-ga-kill-cilt-5', '\"Night Raid için kaçacak yer kalmadı! Jaegers ekibinin çılgın bilim insanı Dr. Stylish, Night Raid\'in gizli sığınağının yerini tespit etmeyi başarıyor. Ansızın gerçekleşen bu baskında, Tatsumi ve arkadaşları sadece Dr. Stylish ile değil, onun korkunç deney ürünü olan askerleriyle de yüzleşmek zorunda.\r\n\r\nSığınak alevler içindeyken ve mühimmat tükenirken, Night Raid köşeye sıkışmıştır. Ancak bu umutsuz savaşın ortasında, devrimin kaderini değiştirecek yeni müttefikler ve efsanevi Teigu\'lar sahneye çıkıyor. Akame ga Kill! 5. Cilt, sürpriz katılımlar ve nefes kesen bir hayatta kalma mücadelesiyle geliyor.\"', 'Takahiro & Tetsuya Tashiro', 164.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_5.jpg', '2025-11-29 06:31:57'),
(4, 1, 'Akame Ga Kill! Cilt 7', 'akame-ga-kill-cilt-7', '\"Devrim ateşi tüm ülkeye yayılırken, Night Raid\'in yeni hedefi Başkent\'ten çok uzakta! Dini örgüt \'Barış Yolu\'nu (Path of Peace) içten çökertmeye çalışan İmparatorluk ajanı Bolic\'in ortadan kaldırılması gerekiyor. Ancak bu suikast görevi, ekibin şimdiye kadar karşılaştığı en büyük engelle korunuyor: General Esdeath ve Jaegers birliği.\r\n\r\nKyoroch şehrinde gerçekleşecek bu büyük operasyon için Night Raid ikiye bölünmek zorunda kalıyor. Avcı ve avın sürekli yer değiştirdiği, Esdeath\'in korkutucu gücünün gölgesinde geçen nefes kesici bir kedi-fare oyunu başlıyor. Akame ga Kill! 7. Cilt, strateji ve aksiyonun tavan yaptığı bir hayatta kalma mücadelesi sunuyor.\"', 'Takahiro & Tetsuya Tashiro', 148.90, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_7.jpg', '2025-11-29 06:33:53'),
(5, 1, 'Akame Ga Kill! Cilt 10', 'akame-ga-kill-cilt-10', '\"Başkent\'te sular durulmuyor! İmparatorluk, Night Raid\'i tuzağa düşürmek için şeytani bir plan hazırlar ve tüm şehri sarsacak büyük bir infaz duyurusu yapar. Bu, sadece bir idam değil, aynı zamanda İmparatorluğun en güçlü generalleri Esdeath ve Budo\'nun gövde gösterisidir.\r\n\r\nNight Raid, imkansız bir ikilemle karşı karşıyadır: Ya bu açık tuzağa bile bile girecekler ya da arkadaşlarını kaderine terk edecekler. Duyguların, sadakatin ve tehlikenin zirve yaptığı Akame ga Kill! 10. Cilt, serinin en gergin kurtarma operasyonuna sahne oluyor.\"', 'Takahiro & Tetsuya Tashiro', 199.99, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_10.jpg', '2025-11-29 06:37:21'),
(6, 1, 'Akame Ga Kill! Cilt 15', 'akame-ga-kill-cilt-15', '\"Efsanevi serinin büyük finali! İmparatorluk ve Devrim Ordusu arasındaki kanlı savaş artık son perdesine ulaşıyor. Başkent alevler içindeyken, Night Raid\'in hayatta kalan üyeleri son görevlerini tamamlamak için harekete geçiyor.\r\n\r\nKarşılarında ise İmparatorluğun mutlak gücü ve yenilmez General Esdeath duruyor. Yeni bir çağın başlaması için geçmişin tamamen yok edilmesi gerekecek. Kılıçların son kez çekildiği, duygusal vedaların ve epik düelloların yaşandığı Akame ga Kill! 15. Cilt, bu karanlık fanteziye unutulmaz bir nokta koyuyor.\"', 'Takahiro & Tetsuya Tashiro', 249.99, 100, '/mini_shop/products_img/Akame_Ga_Kill_Cilt_15.jpg', '2025-11-29 06:39:40'),
(7, 1, 'Berserk Cilt 1', 'berserk-cilt-1', '\"Sırtında insan boyunda devasa bir kılıç, demirden bir takma el ve boynunda kanayan lanetli bir damga... O, Kara Kılıç Ustası Guts.\r\n\r\nİnsanlığın en karanlık arzularıyla beslenen ve dünyayı yöneten iblislerin peşine düşen Guts\'ın tek bir amacı var: İntikam. Gittiği her yere ölüm ve yıkım getiren bu yalnız savaşçının yolu, beklenmedik bir şekilde geveze elf Puck ile kesişir. Şeytani güçlere, yozlaşmış krallara ve bizzat kaderin kendisine karşı açılan bu savaşta, Guts\'ın öfkesi sınır tanımayacak. Kentaro Miura\'nın detaylı çizimleri ve sarsıcı hikayesiyle bir başyapıt olan Berserk 1. Cilt, sizi karanlığın en derin noktasına davet ediyor.\"', 'Kentaro Miura', 149.99, 100, '/mini_shop/products_img/Berserk_Cilt_1.jpg', '2025-11-29 06:45:18'),
(8, 1, 'Bleach Cilt 25', 'bleach-cilt-25', '\"Karakura Kenti saldırı altında! Aizen\'in korkunç ordusu Arrancarlar, dünyaya inerek Ichigo ve arkadaşlarını hedef alıyor. Soul Society\'den gelen destek ekibi Hitsugaya, Renji, Ikkaku ve diğerleri, güçlerinin sınırlarını zorlayan bu yeni düşmanlarla ölümüne bir savaşa tutuşuyor.\r\n\r\nÖzellikle 11. Takım\'dan Ikkaku Madarame\'nin sahneye çıktığı ve gücünü kanıtlamak zorunda kaldığı anlar nefes kesici. Kılıçların çarpıştığı ve ruhsal baskının (Reiatsu) gökyüzünü kapladığı Bleach 25. Cilt, serinin en heyecanlı sokak çatışmalarına ev sahipliği yapıyor.\"', 'Tite Kubo', 210.00, 100, '/mini_shop/products_img/Bleach_Cilt_25.jpg', '2025-11-29 06:46:52'),
(9, 1, 'Bleach Cilt 27', 'bleach-cilt-27', '\"Aizen\'in karanlık planları işlemeye devam ederken, hedefte bu kez Orihime Inoue var! Aizen\'in ilgisini çeken gizemli güçleri nedeniyle Orihime, soğukkanlı Espada Ulquiorra Cifer tarafından korkunç bir ültimatomla yüzleşmek zorunda kalır. Arkadaşlarının hayatı ile kendi özgürlüğü arasında bir seçim yapmak zorunda kalan Orihime için hüzünlü bir veda zamanı gelmiştir.\r\n\r\nKarakura Kenti\'nden Hueco Mundo\'nun tekinsiz çöllerine uzanacak bu yolculukta, Ichigo Kurosaki yasakları ve emirleri çiğneyip arkadaşını kurtarmayı göze alabilecek mi? Serinin en dokunaklı sahnelerinden birine ev sahipliği yapan Bleach 27. Cilt, Hueco Mundo istilasının fitilini ateşliyor.\"', 'Tite Kubo', 220.00, 100, '/mini_shop/products_img/Bleach_Cilt_27.webp', '2025-11-29 06:48:51'),
(10, 1, 'Bleach Cilt 49', 'bleach-cilt-49', '\"Büyük savaştan 17 ay sonra... Karakura Kenti\'nde hayat normale dönmüş gibi görünse de, Ichigo Kurosaki için sessizlik hakim. Ancak bu sakinlik, gölgelerin içinden çıkan gizemli bir adamın belirmesiyle bozulmak üzere.\r\n\r\nIchigo\'nun bildiği dünya değişirken, karşısına çıkan bu yabancı ona reddetmesi zor bir teklif ve bilinmezliklerle dolu yeni bir yol sunuyor. Dostun ve düşmanın birbirine karıştığı, Bleach evreninde kartların yeniden dağıtıldığı \'Kaybolan Ajan\' hikayesi başlıyor.\"', 'Tite Kubo', 285.00, 99, '/mini_shop/products_img/Bleach_Cilt_49.jpg', '2025-11-29 06:51:22'),
(11, 1, 'Bleach Cilt 54', 'bleach-cilt-54', '\"Umutsuzluğun en derin olduğu anda, karanlığı yaracak bir ışık beliriyor! Ichigo Kurosaki, kime güveneceğini şaşırmış ve köşeye sıkışmışken, yardım hiç beklemediği bir yerden, tanıdık bir kılıcın ucunda geliyor.\r\n\r\nXcution üyeleri ile Soul Society arasındaki gerilim artık saklanamaz boyutta. Karakura Kenti bir kez daha savaş alanına dönüşürken, Shinigami\'ler ve Fullbringer\'lar karşı karşıya geliyor. Kaybedilen güçlerin, sarsılan güvenlerin ve eski dostların sahneye çıktığı Bleach 54. Cilt, serinin hayranlarının uzun zamandır beklediği o \'geri dönüş\' atmosferini sonuna kadar hissettiriyor.\"', 'Tite Kubo', 249.90, 100, '/mini_shop/products_img/Bleach_Cilt_54.jpg', '2025-11-29 06:52:48'),
(12, 1, 'Dragon Ball Super Cilt 1', 'dragon-ball-super-cilt-1', '\"Majin Buu\'nun yenilgiye uğratılmasının üzerinden zaman geçmiş, Dünya nihayet hak ettiği barışa kavuşmuştur. Goku, Chi-Chi\'nin zoruyla turp çiftçiliği yaparak \'sıradan\' bir hayat sürmeye çalışsa da, aklı fikri hala sınırlarını zorlamaktadır.\r\n\r\nAncak evrenin dengesini sağlayan korkutucu bir güç, uzun uykusundan uyanmak üzeredir. Yıkım Tanrısı Beerus, rüyasında gördüğü gizemli bir kehanetin, \'Süper Saiyan Tanrısı\'nın peşine düşerek Dünya\'ya doğru yola çıkar. Goku ve Vegeta için güç seviyelerinin bildikleri her şeyin ötesine geçeceği, tanrısal boyutlarda yepyeni bir macera başlıyor. Efsanevi seri, Dragon Ball Super 1. Cilt ile muhteşem bir dönüş yapıyor!\"', 'Akira Toriyama & Toyotarou', 175.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_1.jpg', '2025-11-29 09:17:51'),
(13, 1, 'Dragon Ball Super Cilt 14', 'dragon-ball-super-cilt-14', '\"Gezegen yiyen büyücü Moro, sonunda Dünya\'ya ayak basıyor! Piccolo, Gohan ve diğer Z Savaşçıları, Moro\'nun ordusuna karşı kahramanca dirense de, düşmanın korkunç gücü karşısında zaman daralmaktadır.\r\n\r\nTam her şey bitti denildiği anda, beklenen kurtarıcılar uzaydan dönüyor! Goku, Merus ile yaptığı zorlu antrenmanla Ultra İçgüdü\'nün (Ultra Instinct) sırrına erişebilecek mi? Diğer yanda Vegeta, Yardrat gezegeninde öğrendiği ve Moro\'nun antitezi olabilecek gizemli yeni tekniğini sergilemeye hazır. Saiyan gururunun ve stratejinin ön plana çıktığı Dragon Ball Super 14. Cilt, soluksuz okunacak bir güç gösterisine sahne oluyor.\"', 'Akira Toriyama & Toyotarou', 180.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_14.jpg', '2025-11-29 09:31:27'),
(14, 1, 'Dragon Ball Super Cilt 15', 'dragon-ball-super-cilt-15', '\"Galaktik Devriye Tutuklusu hikayesinin (Moro Arc) nefes kesen finali! Goku, Merus\'un mirasını devralarak \'Mükemmel Ultra İçgüdü\' seviyesine ulaşıyor ve Moro\'yu köşeye sıkıştırıyor.\r\n\r\nAncak büyücü Moro\'nun pes etmeye niyeti yok. Köşeye sıkışan canavar, hayatta kalmak için Dünya\'nın kendisiyle birleşerek gezegeni devasa bir bombaya dönüştürüyor! Goku\'nun artık sadece Moro\'yu yenmesi yetmez; aynı zamanda Dünya\'yı da yok etmeden kurtarması gerekir. Tanrısal güçlerin çarpıştığı ve hiç beklenmedik bir yardımın savaşın seyrini değiştirdiği Dragon Ball Super 15. Cilt, aksiyonun zirve yaptığı unutulmaz bir son sunuyor.\"', 'Akira Toriyama & Toyotarou', 189.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_15.webp', '2025-11-29 09:33:29'),
(15, 1, 'Dragon Ball Super Cilt 17', 'dragon-ball-super-cilt-17', '\"Evrenin en güçlüsü kim olacak? Cereal gezegeninin son hayatta kalan üyesi Granolah, halkının intikamını almak için Dragon Ball\'ları kullanarak bedeli ağır bir dilek diler ve \'Evrenin En Güçlü Savaşçısı\'na dönüşür!\r\n\r\nGoku ve Vegeta, Heeters çetesinin kurnaz planlarıyla Cereal gezegenine çekilir ve kendilerini öfkeden gözü dönmüş Granolah ile amansız bir savaşın içinde bulurlar. Ancak bu dövüş sırasında Saiyanların geçmişine dair şok edici bir gerçek ve Goku\'nun babası Bardock\'a uzanan gizemli bir bağlantı gün yüzüne çıkmak üzeredir. Gücün, intikamın ve geçmişin kesiştiği Dragon Ball Super 17. Cilt, aksiyon dozunu zirveye taşıyor.\"', 'Akira Toriyama & Toyotarou', 199.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_17.jpg', '2025-11-29 09:36:57'),
(16, 1, 'Dragon Ball Super Cilt 21', 'dragon-ball-super-cilt-21', '\"Evrenin en güçlüsü olma savaşı sona erdi, şimdi sahne yeni neslin! Goku ve Vegeta uzayda eğitimlerine devam ederken, Dünya\'yı koruma görevi artık gençlerin omuzlarında.\r\n\r\nArtık birer lise öğrencisi olan Trunks ve Goten, okul hayatının zorluklarıyla boğuşurken bir yandan da \'Saiyaman X-1 ve X-2\' olarak gizlice süper kahramanlık yapmaya başlarlar. Ancak şehirdeki huzur, geçmişin hayaletlerinin uyanmasıyla bozulmak üzeredir. Kötü şöhretli Red Ribbon Ordusu\'nun kalıntıları ve çılgın dahi Dr. Hedo\'nun gizemli planları gölgelerde şekilleniyor. Aksiyonun, komedinin ve gençlik heyecanının iç içe geçtiği Dragon Ball Super 21. Cilt, \'Super Hero\' hikayesine muhteşem bir giriş yapıyor!\"', 'Akira Toriyama & Toyotarou', 210.00, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt_21.jpg', '2025-11-29 09:38:14'),
(17, 1, 'Jujutsu Kaisen Cilt 1', 'jujutsu-kaisen-cilt-1', '\"İnsanların negatif duygularından doğan korkunç \'Lanetler\' aramızda dolaşıyor! Lise öğrencisi Yuji Itadori, dedesinin son vasiyetini yerine getirmek isterken kendini bir kabusun ortasında bulur. Arkadaşlarını mutlak bir ölümden kurtarmak için yaptığı delice fedakarlık Lanetlerin Kralı Sukuna\'nın parmağını yutmak onu geri dönüşü olmayan bir yola sokar. Artık o, içinde tarihin en tehlikeli varlığını taşıyan canlı bir hapishanedir. Ölüm cezası kesinleşmiş olsa da, Itadori\'nin yapacak son bir işi var: Tüm parmakları bulup bu laneti sonsuza dek bitirmek!\"', 'Gege Akutami', 149.90, 100, '/mini_shop/products_img/Jujutsu_Kaisen_Cilt1.jpg', '2025-11-29 09:47:47'),
(18, 1, 'Jujutsu Kaisen Cilt 2', 'jujutsu-kaisen-cilt-2', '\"Özel Seviye bir lanetle ilk karşılaşma! Görev için gittikleri yerde tuzağa düşen Itadori ve arkadaşları, mutlak bir güç farkıyla yüzleşmek zorunda kalır.\r\n\r\nArkadaşlarnı korumak için her şeyi göze alan Itadori, içindeki canavarla, Sukuna ile tehlikeli bir kumar oynar. Ancak Lanetlerin Kralı serbest kaldığında, asıl tehdit o mu yoksa savaştıkları lanet mi olacaktır? Gege Akutami\'nin karanlık ve sürükleyici hikayesi, bu ciltte nefes kesen bir hayatta kalma mücadelesine dönüşüyor.\"', 'Gege Akutami', 164.90, 100, '/mini_shop/products_img/Jujutsu_Kaisen_Cilt2.jpg', '2025-11-29 09:49:24'),
(19, 1, 'Naruto Cilt 1', 'naruto-cilt-1', '\"Konoha Köyü\'nde yaramazlık denince akla gelen tek bir isim var: Naruto Uzumaki! Köyün en büyük ninjası, yani \'Hokage\' olma hayaliyle yanıp tutuşan Naruto\'nun önünde büyük bir engel var: O, Ninja Akademisi\'nin en başarısız öğrencisi!\r\n\r\nAncak Naruto\'nun bu başarısızlığının ve köylüler tarafından dışlanmasının ardında, kendisinin bile bilmediği karanlık bir sır yatıyor. 12 yıl önce köye saldıran efsanevi Dokuz Kuyruklu Tilki ile Naruto\'nun kaderi arasındaki bağ ne? Arkadaşlık, azim ve ninjutsu dolu bu destansı yolculuğun ilk adımı burada atılıyor. Bir efsanenin doğuşuna tanıklık edin!\"', 'Masashi Kishimoto', 110.00, 100, '/mini_shop/products_img/Naruto_Cilt1.jpg', '2025-11-29 09:51:20'),
(20, 1, 'Naruto Cilt 3', 'naruto-cilt-3', '\"Köprü üzerindeki savaş başlıyor! Takım 7\'nin basit bir koruma görevi olarak başlayan yolculuğu, ölümcül bir hayatta kalma mücadelesine dönüşüyor. \'Sis\'in Şeytanı\' Zabuza Momochi geri dönüyor, ama bu sefer yalnız değil.\r\n\r\nKakashi Sensei, Zabuza ile kozlarını paylaşırken; Naruto ve Sasuke, korkutucu bir Kekkei Genkai\'ye (Kan Bağı Yeteneği) sahip olan maskeli ninja Haku ile yüzleşmek zorunda. Haku\'nun \'Şeytani Buz Aynaları\' tekniği karşısında kapana kısılan ikili için zaman daralıyor. Sasuke\'nin dehası ve Naruto\'nun bitmek bilmeyen azmi, arkadaşlarını korumak için yeterli olacak mı? Naruto 3. Cilt, serinin en unutulmaz fedakarlıklarından birine sahne oluyor.\"', 'Masashi Kishimoto', 120.00, 100, '/mini_shop/products_img/Naruto_Cilt3.jpg', '2025-11-29 09:53:00'),
(21, 1, 'Naruto Cilt 35', 'naruto-cilt-35', '\"Naruto, Kakashi ve Yamato\'nun gözetiminde, Rasengan\'ı tamamlamak ve yeni bir seviyeye taşımak için insanüstü bir antrenman sürecine giriyor. Ancak o gücünü artırmaya çalışırken, Akatsuki\'nin en korkulan ve en gizemli ikilisi Ateş Ülkesi sınırlarına dayanıyor.\r\n\r\n\'Ölümsüz İkili\' olarak bilinen Hidan ve Kakuzu, önlerine çıkan her şeyi yok ederek ilerlerken, Konoha\'nın elit ninjaları onları durdurmak için harekete geçer. Asuma Sarutobi ve ekibi, Hidan ile karşılaştıklarında mantığın sınırlarını zorlayan, ürkütücü bir güçle yüzleşmek zorunda kalırlar: Asla ölmeyen bir düşman nasıl yenilebilir? Shikamaru\'nun zekasının bile sınırlarını zorlayacağı, ölümcül bir satranç maçı Naruto 35. Cilt ile başlıyor.\"', 'Masashi Kishimoto', 180.00, 100, '/mini_shop/products_img/Naruto_Cilt35.jpg', '2025-11-29 09:53:51'),
(22, 1, 'Naruto Cilt 46', 'naruto-cilt-46', '\"Konoha tarihinin en karanlık saati! Akatsuki\'nin lideri Pain, kendi adaletini ve \'barışı\' getirmek adına köye mutlak bir yıkım indiriyor. Kakashi, Tsunade ve Konoha\'nın diğer cesur ninjaları, \'Tanrı\' olduğunu iddia eden bu güce karşı varlarını yoklarını ortaya koysalar da, Pain\'in ezici gücü karşısında umutlar tükenmek üzeredir.\r\n\r\nAncak yıkımın, tozun ve dumanın ortasında, Myoboku Dağı\'ndaki eğitimini tamamlayan bir kahraman beliriyor. Yeni öğrendiği \'Senjutsu\' (Doğa Gücü) ve ulaştığı Bilge Modu (Sage Mode) ile Naruto Uzumaki, köyünü korumak ve ustasının mirasına sahip çıkmak için Pain\'in altı yoluna meydan okuyor. Manga tarihinin en epik karşılaşmalarından biri olan Naruto vs. Pain savaşı, Naruto 46. Cilt ile başlıyor!\"', 'Masashi Kishimoto', 184.99, 100, '/mini_shop/products_img/Naruto_Cilt46.jpg', '2025-11-29 09:55:40'),
(23, 1, 'Naruto Cilt 67', 'naruto-cilt-67', '\"Savaş alanında dengeler tamamen değişiyor! Obito Uchiha, On Kuyruklu\'yu (Juubi) mühürleyerek korkunç bir dönüşüm geçiriyor ve \'Altı Yolun Bilgesi\'ne denk, tanrısal bir güce kavuşuyor.\r\n\r\nArtık ne Ninjutsu ne de strateji ona işliyor; efsanevi Hokage\'lerin saldırıları bile bu mutlak güç karşısında etkisiz kalıyor. Müttefik Shinobi Kuvvetleri umutsuzluğun kıyısına sürüklenirken, babası Minato ve eski rakibi Sasuke ile omuz omuza veren Naruto, imkansızı başarmak ve arkadaşlarını korumak için bir yol bulmak zorunda. Yıkımın ve umudun çarpıştığı Naruto 67. Cilt, serinin en büyük güç gösterisine sahne oluyor.\"', 'Masashi Kishimoto', 199.90, 100, '/mini_shop/products_img/Naruto_Cilt67.jpg', '2025-11-29 09:56:51'),
(24, 1, 'Naruto Cilt 72', 'naruto-cilt-72', '\"Bir efsanenin sonu! Çakranın atası Kaguya Otsutsuki\'ye karşı verilen savaş, Takım 7\'nin son bir kez omuz omuza vermesiyle doruk noktasına ulaşıyor. Ancak dünya yaklaşan felaketten kurtulsa bile, çözülmesi gereken son bir düğüm, verilmesi gereken son bir hesaplaşma var.\r\n\r\nNaruto ve Sasuke... Birbirine zıt iki kader, iki farklı ninja yolu. Tarihin akışını belirlemek ve aralarındaki o kopmaz ama karmaşık bağı nihayete erdirmek için, her şeyin başladığı yere, Sonun Vadisi\'ne dönüyorlar. Masashi Kishimoto\'nun başyapıtı Naruto 72. Cilt, gözyaşları, kan ve umudun birbirine karıştığı unutulmaz bir finalle veda ediyor.\"', 'Masashi Kishimoto', 204.99, 100, '/mini_shop/products_img/Naruto_Cilt72.jpg', '2025-11-29 09:57:43'),
(25, 1, 'One Piece Cilt 1', 'one-piece-cilt-1', '\"Zenginlik, şöhret, güç... Korsanlar Kralı Gold Roger, idam edilmeden önce tüm dünyaya meydan okuyan o sözleri söyledi ve Büyük Korsanlar Çağı\'nı başlattı!\r\n\r\nYıllar sonra, Doğu Mavi\'den yola çıkan genç ve çılgın bir adam var: Monkey D. Luffy. Yediği Şeytan Meyvesi sayesinde lastik adama dönüşen Luffy\'nin tek bir hayali var: Efsanevi hazine \'One Piece\'i bulmak ve yeni Korsanlar Kralı olmak! Ancak bu devasa okyanusta tek başına hayatta kalamaz. Luffy\'nin güvenilir nakamalar (yoldaşlar) bulmak için çıktığı yolculukta ilk durağı, korkusuz \'Korsan Avcısı\' Roronoa Zoro ile yollarının kesiştiği yer olacak. Bir efsanenin doğuşuna tanıklık edeceğiniz One Piece 1. Cilt, sizi bitmeyen bir maceraya davet ediyor.\"', 'Eiichiro Oda', 89.90, 100, '/mini_shop/products_img/One_Piece_Cilt1.jpg', '2025-11-29 09:59:45'),
(26, 1, 'One Piece Cilt 3', 'one-piece-cilt-3', '\"Palyaço Buggy ile olan mücadele sona eriyor! Luffy, Zoro ve Nami; Grand Line\'a giden yolda ihtiyaçları olan haritayı ve rotayı belirleseler de, hala çok önemli bir eksikleri var: Gerçek bir gemi.\r\n\r\nBu arayış onları huzurlu görünen bir adaya ve köyün meşhur yalancısı Usopp ile tanışmaya götürür. Ancak Usopp\'un \'kurt geliyor\' hikayeleri bu sefer gerçeğe dönüşmek üzeredir. Köyün zengin kızı Kaya\'yı hedef alan korkunç bir komplo ve gölgelerde saklanan acımasız bir korsan kaptan (Kuro) ortaya çıkıyor. Yeni dostlukların temellerinin atıldığı One Piece 3. Cilt, yalanların ardındaki cesur gerçeği ortaya çıkarıyor.\"', 'Eiichiro Oda', 94.99, 100, '/mini_shop/products_img/One_Piece_Cilt3.jpg', '2025-11-29 10:00:48'),
(27, 1, 'One Piece Cilt 9', 'one-piece-cilt-9', '\"Nami\'nin ihaneti mi, yoksa çaresizliği mi? Going Merry\'yi alıp kaçan Nami\'nin peşine düşen Luffy ve tayfası, kendilerini Balıkadam Arlong\'un demir yumrukla yönettiği Cocoyasi Köyü\'nde bulurlar.\r\n\r\nBurada, Nami\'nin geçmişindeki karanlık sırlar ve \'cadı\' maskesinin ardında sakladığı gözyaşları gün yüzüne çıkmaya başlar. Arlong\'un zulmü sınır tanımazken, Nami\'nin omuzlarındaki yük artık taşıyamayacağı kadar ağırdır. Luffy\'nin şapkasını emanet ettiği o efsanevi an ve Hasır Şapka Tayfası\'nın Arlong Park\'a doğru yaptığı o ikonik yürüyüş... One Piece 9. Cilt, dostluğun anlamını yeniden tanımlıyor.\"', 'Eiichiro Oda', 109.90, 100, '/mini_shop/products_img/One_Piece_Cilt9.jpg', '2025-11-29 10:02:26'),
(28, 1, 'One Piece Cilt 100', 'one-piece-cilt-100', '\"Efsanevi destanda tarihi bir an! Eiichiro Oda\'nın başyapıtı, 100. Cilt ile yüzlerce bölümlük macerayı taçlandırıyor.\r\n\r\nOnigashima baskınının zirve noktası olan \'Çatı Katı\'nda, Luffy ve Kaido arasındaki mücadele artık boyut değiştiriyor. Luffy, art arda aldığı darbelerin ardından \'Kralın Hakisi\'nin (Haoshoku Haki) gerçek potansiyelini kavrayarak, İmparatorlara karşı durabilmek için gereken o nihai gücün anahtarını keşfediyor. Yumrukların birbirine değmeden gökyüzünü yardığı bu seviyede, Hasır Şapka artık sadece bir korsan değil, bir efsane adayıdır. Aşağıda ise \'Korsanlar Kralı\'nın Kanatları\' Sanji ve Zoro, Canavar Korsanları\'nın en güçlü komutanlarına karşı sınırlarını zorluyor. One Piece 100. Cilt, aksiyonun ve epikliğin zirvesi!\"', 'Eiichiro Oda', 189.90, 100, '/mini_shop/products_img/One_Piece_Cilt100.jpg', '2025-11-29 10:03:59'),
(29, 1, 'One Piece Cilt 105', 'one-piece-cilt-105', '\"Wano Ülkesi\'nde nihayet şafak söküyor! Onigashima\'daki o uzun ve yıkıcı savaşın ardından, samuraylar ve korsanlar hak ettikleri özgürlüğün tadını çıkarıyor. Ancak Wano\'nun sınırları dışındaki dünya, bu büyük güç değişimine sessiz kalmayacak.\r\n\r\nHaber kuşları dünyanın dört bir yanına dağılırken, Denizciler ve Dünya Hükümeti teyakkuzda. Gazeteler, dünyayı sarsacak o manşeti atıyor: Yeni \'Dört İmparator\' (Yonko) belirlendi! Luffy ve tayfası için ödüllerin, rütbelerin ve tehlikenin boyutu tamamen değişiyor. Bir devrin kapandığı, dünyanın kaderinin yeniden yazıldığı ve \'Son Saga\'nın ayak seslerinin duyulduğu One Piece 105. Cilt, tarihi bir dönüm noktası!\"', 'Eiichiro Oda', 199.90, 100, '/mini_shop/products_img/One_Piece_Cilt105.jpg', '2025-11-29 10:04:51'),
(30, 1, 'One Piece Cilt 107', 'one-piece-cilt-107', '\"Geleceğin Adası Egghead\'de işler çığırından çıkıyor! Hasır Şapka Tayfası ve Dr. Vegapunk, laboratuvarın içinde kilitli kalmışken, içerideki hainin kim olduğu sorusu ölümcül bir kovalamacaya dönüşüyor. Durdurulamaz Seraphim\'ler kontrolden çıkınca, Luffy ve Lucci gibi eski düşmanlar bile hayatta kalmak için omuz omuza vermek zorunda kalıyor.\r\n\r\nAncak asıl kıyamet başka bir yerde kopuyor! Denizcilerin efsanevi kahramanı Monkey D. Garp, kaçırılan öğrencisi Koby\'yi kurtarmak için Arıkovanı (Hachinosu) Adası\'na, yani Karasakal Korsanları\'nın merkezine tek başına baskın düzenliyor. Efsanevi yumrukların konuştuğu ve \'Adalet\' kavramının sorgulandığı One Piece 107. Cilt, Garp\'ın gücünü tüm dünyaya kanıtladığı tarihi anlara ev sahipliği yapıyor.\"', 'Eiichiro Oda', 204.99, 100, '/mini_shop/products_img/One_Piece_Cilt107.jpg', '2025-11-29 10:05:39'),
(31, 1, 'One Piece Cilt 108', 'one-piece-cilt-108', '\"Dünya sarsılmaya devam ediyor! Korsan Adası Arıkovanı\'nda (Hachinosu), efsanevi Denizci Garp ile eski öğrencisi Kuzan (Aokiji) arasındaki \'Adalet\' savaşı, beklenmedik ve sarsıcı bir sonuca doğru ilerliyor.\r\n\r\nDiğer tarafta ise Geleceğin Adası Egghead, tarihinin en büyük kuşatması altında! Amiral Kizaru önderliğindeki devasa Denizci filosu ve Dünya Hükümeti\'nin en karanlık güçlerinden biri olan Aziz Jaygarcia Saturn, Hasır Şapka Tayfası ile Dr. Vegapunk\'ı yok etmek için adaya ayak basıyor. Luffy ve Kizaru, Sabaody\'den yıllar sonra tekrar karşı karşıya! Işık hızında tekmeler ile \'Özgürlük Savaşçısı\'nın çarpıştığı One Piece 108. Cilt, Bartholomew Kuma\'nın trajik geçmişine açılan kapıyı da aralıyor.\"', 'Eiichiro Oda', 209.99, 100, '/mini_shop/products_img/One_Piece_Cilt108.jpg', '2025-11-29 10:06:26'),
(32, 1, 'One Piece Cilt 110', 'one-piece-cilt-110', '\"Dünyanın kaderini değiştirecek yayın başladı! Dr. Vegapunk\'ın tüm dünyaya duyurmak istediği \'o gerçek\', Hükümet\'in en büyük kabusuna dönüşüyor. Bu yayını durdurmak için Kutsal Topraklar Mary Geoise\'den bizzat inen Beş Kadim (Gorosei), korkunç ve şeytani formlarıyla Egghead Adası\'nı cehenneme çeviriyor.\r\n\r\nLuffy ve Dev Savaşçılar, bu mutlak güce karşı imkansız bir direniş gösterirken, 900 yıldır uykuda olan Efsanevi Demir Dev (Emeth) nihayet gözlerini açıyor! Tarihin en büyük savaşlarından birinin ortasında, Bonney ve Luffy\'nin hayalleri geleceğe uzanırken, Hasır Şapka Tayfası için Elbaf\'a giden yol hiç bu kadar zorlu olmamıştı. One Piece 110. Cilt, \'Boşluk Yüzyılı\'nın perdesini aralıyor!\"', 'Eiichiro Oda', 219.90, 100, '/mini_shop/products_img/One_Piece_Cilt110.jpg', '2025-11-29 10:07:26'),
(33, 1, 'One Piece Cilt 111', 'one-piece-cilt-111', '\"Tarihe \'Egghead Olayı\' olarak geçecek o büyük gün sona eriyor! Beş Kadim\'in (Gorosei) mutlak ablukası altında, Hasır Şapka Tayfası ve Dev Savaşçılar için kaçış imkansız görünmektedir. Ancak 900 yıllık uykusundan uyanan Efsanevi Demir Dev Emeth, Luffy\'nin \'özgürlüğü\' için son kozunu oynuyor!\r\n\r\n    Geçmişten gelen o gizemli düğüm çözüldüğünde, Joy Boy\'un yüzyılları aşan o muazzam Hakisi tüm adayı sarsar ve Beş Kadim\'i bile geri püskürtür. Kaosun, gözyaşının ve umudun iç içe geçtiği bu kaçışın sonunda, Hasır Şapkalar nihayet o rüyalarındaki adaya, Savaşçıların Ülkesi Elbaf\'a doğru yelken açıyor! One Piece 111. Cilt, yeni bir maceranın şafağını müjdeliyor.\"', 'Eiichiro Oda', 224.99, 100, '/mini_shop/products_img/One_Piece_Cilt111.jpg', '2025-11-29 10:08:35'),
(34, 1, 'Solo Leveling Cilt 1 Özel Kapak', 'solo-leveling-cilt-1-ozel-kapak', '\"Dünyanın En Zayıf Avcısı\" lakabıyla tanınan E-Seviye avcı Sung Jinwoo ile tanışın. Gücü o kadar azdır ki, en düşük seviyeli zindanlarda bile yaralanmadan çıkamaz. Annesinin hastane masraflarını ödeyebilmek için hayatını riske atarak bu zindanlara girmeye mecburdur.\r\n\r\nAncak sıradan bir D-Seviye zindanın derinliklerinde keşfettikleri gizli bir tapınak, her şeyi değiştirir. Takım arkadaşları birer birer katledilirken ve Jinwoo ölümü kabullenmişken, gözlerini bir hastane odasında açar. Ama bir farkla: Önünde sadece kendisinin görebildiği, oyun benzeri bir \"Sistem\" ekranı süzülmektedir. Herkesin gücünün sabit kaldığı bu dünyada, o artık \"Seviye Atlayabilen\" tek kişidir! Efsanenin doğuşunu anlatan Solo Leveling 1. Cilt, şimdi sınırlı sayıdaki Özel Kapak baskısıyla koleksiyonunuzda yerini almayı bekliyor.\"', 'Chugong & DUBU (Redice Studio)', 349.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt1_Ozel_Kapak.jpg', '2025-11-29 10:12:11'),
(35, 1, 'Solo Leveling Cilt 1', 'solo-leveling-cilt-1', '\"10 yıl önce, dünyayı canavarlarla dolu başka bir boyuta bağlayan \'Kapı\'lar açıldı ve insanüstü güçlere sahip \'Avcılar\' ortaya çıktı. Ancak herkes güçlü doğmaz. E-Seviye Avcı Sung Jinwoo, o kadar güçsüzdür ki en basit zindanlarda bile ölümden döner ve \'Dünyanın En Zayıfı\' olarak alay konusu olur.\r\n\r\nAnnesinin hastane masraflarını ödemek için girdiği sıradan bir zindanda, ekibiyle birlikte korkunç bir tuzağa düşerler. Ölümle burun buruna geldiği o anda, Jinwoo\'nun karşısında sadece onun görebildiği gizemli bir görev ekranı belirir. Herkesin gücünün sabit olduğu bu dünyada, artık o kuralları yıkacak tek kişidir. Seviye atlayarak en tepeye tırmanacağı efsanevi yolculuk, Solo Leveling 1. Cilt ile başlıyor!\"', 'Chugong & DUBU (Redice Studio)', 174.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt1.jpg', '2025-11-29 10:13:47'),
(36, 1, 'Solo Leveling Cilt 2', 'solo-leveling-cilt-2', '\"Çifte Zindan faciasından mucizevi bir şekilde sağ kurtulan Sung Jinwoo, hastane odasında uyandığında hiçbir şeyin eskisi gibi olmadığını fark eder. Gözlerinin önünde beliren gizemli \'Sistem\' pencereleri, ona reddedemeyeceği tuhaf \'Günlük Görevler\' vermektedir.\r\n\r\nEğer görevleri yaparsa ödül alacak, yapmazsa cezalandırılacaktır! Bu garip oyunun kurallarını çözmeye çalışan Jinwoo, vücudunun ve gücünün hızla değiştiğini hisseder. Yeni yeteneklerini test etmek için metro istasyonunda açılan ve sadece kendisinin girebildiği özel bir \'Zindan\'a adım atar. Kana susamış Lycan\'lar ve bataklığın hakimi ile tek başına yüzleşirken, \'Dünyanın En Zayıfı\' lakabını tarihe gömmek üzeredir. Solo Leveling 2. Cilt, asıl seviye atlama macerasını başlatıyor!\"', 'Chugong & DUBU (Redice Studio)', 179.99, 100, '/mini_shop/products_img/Solo_Leveling_Cilt2.jpg', '2025-11-29 10:14:42'),
(37, 1, 'Solo Leveling Cilt 3', 'solo-leveling-cilt-3', '\"Sung Jinwoo, Sistem sayesinde her geçen gün güçlenmeye devam ediyor ancak avcı dünyasının acımasız kuralları henüz onun peşini bırakmış değil. Para kazanmak ve seviye atlamak için katıldığı bir C-Seviye zindan baskınında, işler hiç beklemediği bir yöne sapıyor.\r\n\r\nGrup lideri Hwang Dong-Suk ve ekibi, zindanın derinliklerinde gerçek niyetlerini ortaya çıkardığında, Jinwoo ve yanındaki acemi avcı Yoo Jinho ölümcül bir tuzağın ortasında kalır. Zindan boss\'u dev bir örümcek bir yanda, ihanet eden avcılar diğer yanda... Köşeye sıkışan Jinwoo\'nun önünde Sistem yeni ve tüyler ürpertici bir \'Acil Görev\' penceresi açar. Hayatta kalmak için ne kadar ileri gidebilir? Avın avcıya dönüştüğü Solo Leveling 3. Cilt, nefes kesen bir hayatta kalma savaşına sahne oluyor.\"', 'Chugong & DUBU (Redice Studio)', 184.99, 100, '/mini_shop/products_img/Solo_Leveling_Cilt3.jpg', '2025-11-29 10:15:31'),
(38, 1, 'Solo Leveling Cilt 5', 'solo-leveling-cilt-5', '\"Basit bir eğitim görevi, aniden ölümcül bir kabusa dönüşüyor! Sung Jinwoo, arkadaşının kız kardeşi Han Song-Yi\'ye göz kulak olmak için Beyaz Kaplan Loncası\'nın düzenlediği sıradan bir zindan baskınına katılır. Ancak kapıdan içeri girdikleri anda geçit kapanır ve rengi kızıla döner.\r\n\r\nArtık nadir görülen ve girenlerin sağ çıkma ihtimalinin neredeyse imkansız olduğu bir \'Kızıl Kapı\'nın içindedirler. Dış dünyayla bağlantı tamamen kesilmiş, etraf dondurucu kar fırtınaları ve kana susamış Buz Elfleri ile çevrilmiştir. A-Seviye avcı Kim Chul liderliğindeki grup, Jinwoo\'yu \'yük\' olarak görüp terk ederken, Jinwoo bir kez daha zayıfları korumak için gölgelerin içindeki gerçek gücünü ortaya çıkarmak zorundadır. Solo Leveling 5. Cilt, soğuk ve karanlık bir hayatta kalma savaşı sunuyor!\"', 'Chugong & DUBU (Redice Studio)', 189.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt5.jpg', '2025-11-29 10:16:29'),
(39, 1, 'Solo Leveling Cilt 7', 'solo-leveling-cilt-7', '\"Sung Jinwoo\'nun en zorlu ve en kişisel görevi başlıyor! Annesini pençesine alan \'Ebedi Uyku\' hastalığının tedavisini tamamlamak için gereken son bileşen, İblis Kalesi\'nin en tepesinde saklı. Ancak oraya ulaşmak ve \'Hayat Suyu\'nu elde etmek için kulenin mutlak hakimi, korkunç İblis Kral Baran ile yüzleşmek zorunda.\r\n\r\nJinwoo ve sadık Gölgeler Ordusu, İblis Kral\'ın ve onun gökyüzünü kaplayan Wyvern sürüsünün karşısına dikiliyor. Alevlerin ve şimşeklerin çarpıştığı bu destansı savaşta, Jinwoo sadece seviye atlamak için değil, ailesini kurtarmak için savaşıyor. Serinin en görkemli boss savaşlarından birine ve yepyeni bir yoldaşın katılımına sahne olan Solo Leveling 7. Cilt, aksiyonu gökyüzüne taşıyor!\"', 'Chugong & DUBU (Redice Studio)', 199.99, 98, '/mini_shop/products_img/Solo_Leveling_Cilt7.jpg', '2025-11-29 10:17:24'),
(40, 1, 'Solo Leveling Cilt 8', 'solo-leveling-cilt-8', '\"Beklenen an nihayet geldi! İblis Kalesi\'nden dönen Sung Jinwoo, gücünün sınırlarını test etmek için Avcılar Birliği\'ne giderek yeniden değerlendirme talep ediyor. Ölçüm cihazları hata verip sonuçlar açıklandığında, sadece Kore değil tüm dünya şoka girecek: \'Dünyanın En Zayıfı\' lakabı artık tarih oldu, Kore\'nin 10. S-Seviye Avcısı resmen doğdu!\r\n\r\nAncak Jinwoo\'nun bu yeni statüsünü kutlayacak zamanı yok. Kore\'nin yıllardır çözülemeyen en büyük kabusu, karıncaların istila ettiği Jeju Adası\'ndaki tehdit giderek büyüyor. Kore ve Japonya\'nın en elit avcıları, adayı geri almak için tarihin en büyük ortak operasyonunu başlatmak üzere bir araya geliyor. Gölgelerin Hükümdarı bu savaşın neresinde yer alacak? Solo Leveling 8. Cilt, güç dengelerinin tamamen değiştiği yeni bir çağı başlatıyor!\"', 'Chugong & DUBU (Redice Studio)', 209.90, 100, '/mini_shop/products_img/Solo_Leveling_Cilt8.jpg', '2025-11-29 10:18:49'),
(41, 1, 'The World After the Fall Cilt 1', 'the-world-after-the-fall-cilt-1', '\"Dünya ansızın beliren devasa kulelerin gölgesinde yıkımla yüzleştiğinde, insanlığın tek umudu kulelere çağrılan \'Yürüyüşçüler\' oldu. Ancak 77. kata geldiklerinde, önlerine reddedilmesi zor bir fırsat çıktı: \'Geri Dönüş Taşı\'.\r\n\r\nBu taşı kullanan herkes geçmişe dönebilir ve her şeye sıfırdan, güvenli bir şekilde başlayabilirdi. Neredeyse herkes bu cazip teklifi kabul edip kuleyi terk etti. Biri hariç: Jaehwan. O, geçmişe kaçmayı ve zamanı geriye sarmayı reddetti. Herkesin terk ettiği o kulede tek başına kalan Jaehwan, insanlığın gerçek kurtuluşu için 100. kata ulaşmaya ve bu sistemin sonunu getirmeye yemin etti. \'Herkes geçmişe dönerken, dünya düşüşten sonra nasıl görünür?\' sorusunun cevabı, The World After the Fall 1. Cilt ile veriliyor.\"', 'singNsong & Undead Gamja', 149.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt1.jpg', '2025-11-29 10:28:31'),
(42, 1, 'The World After the Fall Cilt 2', 'the-world-after-the-fall-cilt-2', '\"Kule\'nin zirvesine ulaşmak bir son değil, sadece korkunç bir başlangıçtı! Jaehwan, insanlığın kurtuluşu sandığı o sistemin aslında devasa bir yalan olduğunu keşfeder. 100. katın duvarlarını yıktığında onu bekleyen şey huzur değil, \'Kaos\' adı verilen ve bildiği tüm kuralların geçersiz olduğu vahşi bir dünyadır.\r\n\r\nSistemin boyunduruğunu reddeden ve kendi geliştirdiği \'Saplama\' gücüyle gerçekliğin dokusunu yırtan Jaehwan, şimdi Kaos\'un tehlikeli topraklarına adım atıyor. Burada ne seviyeler var ne de yardımcı statü pencereleri; sadece saf irade ve güç hayatta kalmayı belirliyor. Jaehwan, bu yıkılmış dünyanın ardındaki asıl kuklacıyı, \'Büyük Birader\'i bulmak için Kaos\'u sarsmaya geliyor. The World After the Fall 2. Cilt, kalıpları yıkan o eşsiz yolculuğuna hız kesmeden devam ediyor.\"', 'singNsong & Undead Gamja', 159.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt2.jpg', '2025-11-29 10:29:24'),
(43, 1, 'The World After the Fall Cilt 3', 'the-world-after-the-fall-cilt-3', '\"Kaos\'un kalbine, Gorgon Kalesi\'ne yolculuk! Jaehwan, Sistemin kurallarına boyun eğmiş \'Adaptörler\'in yönettiği bu devasa şehirde, kendi \'Uyanmış\' felsefesini ve varoluşunu kanıtlamak zorunda.\r\n\r\nSistemin sunduğu hazır seviyeler, skiller ve statüler yerine; kulede geçirdiği binlerce yıl boyunca bilediği o tek bir \'Saplama\' hareketiyle Gorgon\'un yerleşik hiyerarşisine ve Lordlarına meydan okuyor. \'Büyük Birader\'in gözleri her an üzerindeyken, Jaehwan sadece güçlü düşmanlarıyla değil, bu sahte dünyanın dayattığı gerçeklerle de savaşıyor. Saf iradenin ve inancın çarpıştığı The World After the Fall 3. Cilt, Gorgon Kalesi\'ni temellerinden sarsacak bir güç gösterisine sahne oluyor.\"', 'singNsong & Undead Gamja', 164.99, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt3.jpg', '2025-11-29 10:30:16'),
(44, 1, 'The World After the Fall Cilt 4', 'the-world-after-the-fall-cilt-4', '\"Gorgon Kalesi\'nde ipler geriliyor! Jaehwan\'ın kuralları hiçe sayan tavrı ve anlaşılmaz gücü, Kaos\'un yerleşik düzenini tehdit etmeye başlıyor. Şehrin yöneticileri ve güçlü Generalleri, bu \'Sistemsiz\' yabancıyı durdurmak için harekete geçmek zorunda.\r\n\r\nAdaptörlerin seviyeleri ve gösterişli yetenekleri ile Jaehwan\'ın saf iradesi ve basit \'Saplama\'sı arasındaki savaş şiddetleniyor. Jaehwan, sadece gücünü kanıtlamak için değil, bu dünyanın insanlarına unuttukları bir gerçeği, \'Uyanış\'ı hatırlatmak için kılıcını çekiyor. Gorgon\'un en güçlüleriyle yüzleşme vaktinin geldiği The World After the Fall 4. Cilt, inançların çarpıştığı bir arenaya dönüşüyor.\"', 'singNsong & Undead Gamja', 169.90, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt4.jpg', '2025-11-29 10:31:15'),
(45, 1, 'The World After the Fall Cilt 9', 'the-world-after-the-fall-cilt-9', '\"Kaos\'un sınırlarını aşan Jaehwan için artık hedef çok daha yükseklerde: Büyük Topraklar. Sistemin sadece bir oyun alanı değil, devasa bir yanılsama olduğunu kanıtlayan Jaehwan, \'Büyük Birader\'e ulaşmak için gerçekliğin en derin katmanlarına, \'Derinlikler\'e iniyor.\r\n\r\nBurada kurallar, statüler veya seviyeler yok; sadece kavramların ve iradenin savaşı var. Eski dostların ve yeni, kudretli düşmanların (Lordlar ve Yüksek Varlıklar) sahneye çıktığı bu noktada, Jaehwan\'ın kılıcı sadece eti değil, bizzat \'Dünya\'nın kendisini delmeye hazırlanıyor. Evrenin ardındaki o korkunç çıplak gerçeğe bir adım daha yaklaşılan The World After the Fall 9. Cilt, felsefi derinliği ve patlayıcı aksiyonu birleştiriyor.\"', 'singNsong & Undead Gamja', 189.95, 100, '/mini_shop/products_img/The_World_After_the_Fall_Cilt9.jpg', '2025-11-29 10:32:07'),
(46, 1, 'Vinland Saga Cilt 1', 'vinland-saga-cilt-1', '\"11. yüzyılın başlarında, Kuzey Avrupa buzlu denizlerin hakimi Vikinglerin istilası altındadır. Savaşın, yağmanın ve vahşetin sıradan olduğu bu çağda, genç Thorfinn\'in yaşamak için tek bir amacı var: İntikam.\r\n\r\nEfsanevi bir savaşçının oğlu olan Thorfinn, babasının kalleşçe bir pusu sonucu öldürülmesine tanıklık eder. Babasının katili ise kurnaz ve acımasız paralı asker lideri Askeladd\'dır. Thorfinn, Askeladd\'ı adil bir düelloda öldürebilmek için yemin eder; ancak bu fırsatı kazanmak uğruna bizzat düşmanının emrine girmek ve savaş alanlarında büyümek zorundadır. Bir çocuğun masumiyetini yitirip soğukkanlı bir savaşçıya dönüşmesini konu alan tarihi bir başyapıt. Vinland Saga 1. Cilt, sizi Valhalla\'nın kapılarına kadar götürecek.\"', 'Makoto Yukimura', 139.99, 100, '/mini_shop/products_img/Vinland_Saga_Cilt1.jpg', '2025-11-29 10:33:52'),
(47, 1, 'Vinland Saga Cilt 6', 'vinland-saga-cilt-6', '\"York\'ta zafer kutlamaları sürerken, Kral Sweyn\'in nihai planı ortaya çıkıyor ve Askeladd hayatının en zorlu satranç oyunuyla yüzleşiyor. Kral, Askeladd\'ı imkansız bir tercihe zorluyor: Ya canı pahasına koruduğu anavatanı Galler yanacak ya da Prens Canute ölecek.\r\n\r\nKöşeye sıkışan ve tüm kozları tükenen Askeladd, bu çıkmazdan kurtulmak için tarihin akışını değiştirecek, delice ve kanlı bir kumar oynamak zorunda. Thorfinn ise yıllardır beklediği o intikam anının, bu devasa siyasi kaosun ortasında kaybolmak üzere olduğundan habersiz. Bir devrin kapanışına zemin hazırlayan Vinland Saga 6. Cilt, sadakatin ve ihanetin en keskin halini sunuyor.\"', 'Makoto Yukimura', 159.90, 100, '/mini_shop/products_img/Vinland_Saga_Cilt6.jpg', '2025-11-29 10:34:50'),
(48, 1, 'Vinland Saga Cilt 18', 'vinland-saga-cilt-18', '\"Baltık Denizi\'nde sular durulmuyor! Jomsvikinglerin liderlik mücadelesinin tam ortasında kalan Thorfinn, şiddet döngüsünü kırmak ve Vinland\'a giden yolu açmak için imkansız bir görevi üstleniyor. Ancak barışçıl çözüm arayışında karşısına çıkan engel, savaşın neşesiyle yanıp tutuşan eski bir tanıdık: \'Yenilmez\' Thorkell.\r\n\r\nThorkell, Thorfinn\'i kaçamayacağı bir düelloya zorlarken; Thorfinn, babasının öğretisi olan \'gerçek savaşçı\'nın anlamını, eline kılıç almadan kanıtlamak zorunda. Bir yanda Jomsvikinglerin acımasız gelenekleri, diğer yanda Thorfinn\'in şiddetsizlik yemini... Stratejinin, gücün ve iradenin çarpıştığı Vinland Saga 18. Cilt, serinin en beklenen yüzleşmelerinden birine sahne oluyor.\"', 'Makoto Yukimura', 164.90, 100, '/mini_shop/products_img/Vinland_Saga_Cilt18.jpg', '2025-11-29 10:36:06'),
(49, 1, 'Vinland Saga Cilt 21', 'vinland-saga-cilt-21', '\"Bir kafes kuşu olmayı reddeden Gudrid, özgürlüğe kanat açıyor! Leif Ericson\'un efsanevi hikayeleriyle büyüyen ve bilinmeyen ufukları merak eden Gudrid, Thorfinn\'in \'savaşsız dünya\' hayaline ortak olarak kaçak bir yolcuya dönüşür. Ancak geçmişi onu bu kadar kolay bırakmayacaktır.\r\n\r\nOnu geri getirmekle görevlendirilen kocası Sigurd, kırılan gururunu tamir etmek için Kuzey Denizi\'ni aşıp peşlerine düşer. Thorfinn ve ekibi, hem doğanın zorluklarıyla hem de Sigurd\'un bitmek bilmeyen takibiyle başa çıkmak zorunda. Hayallerin bedelinin ödendiği Vinland Saga 21. Cilt, zincirlerini kıranların hikayesi!\"', 'Makoto Yukimura', 179.99, 100, '/mini_shop/products_img/Vinland_Saga_Cilt21.jpg', '2025-11-29 10:38:24'),
(50, 1, 'Dragon Ball Super Cilt 20', 'dragon-ball-super-cilt-20', '\"Cereal Gezegeni\'ndeki savaşta limitler zorlanıyor! Evrenin en güçlüsü haline gelen Gas, Goku ve Vegeta\'yı çaresiz bırakacak bir güç sergiliyor. Ancak bu umutsuzluğun ortasında, savaşın seyrini değiştirecek anahtar geçmişin tozlu sayfalarından çıkıyor.\r\n\r\nGoku\'nun babası Bardock\'un yıllar önce Gas ile yaptığı efsanevi dövüşe ait, hasar görmüş bir scouter\'daki ses kaydı ortaya çıkıyor! Babasının sesini ve savaşma iradesini ilk kez bu kadar yakından hisseden Goku, \'Saiyan Gururu\'nun ve kendi gücünün gerçek doğasını kavramaya başlıyor. Geçmişin mirasının bugünün savaşını şekillendirdiği Dragon Ball Super 20. Cilt, Saiyanların neden evrenin en korkulan savaşçıları olduğunu bir kez daha kanıtlıyor.\"', 'Akira Toriyama & Toyotarou', 194.90, 100, '/mini_shop/products_img/Dragon_Ball_Super_Cilt20.jpg', '2025-11-29 10:42:01'),
(51, 2, 'Amazing Spider-Man Cilt 27 Kör Uçuş', 'amazing-spider-man-cilt-27-k-or-ucus', '\"Örümcek Hissi (Spider-Sense) tamamen KÖR! Örümcek Adam\'ın en önemli erken uyarı sistemi tamamen ortadan kalktı. Peter Parker, artık tehlikenin yaklaştığını hissedemeyen, en kritik gücünü kaybetmiş bir kahraman olarak kötülükle savaşmak zorunda.\r\n\r\nTamamen kör uçuş yaptığı bu tehlikeli dönemde, Kindred\'ın yarattığı karmaşa New York\'u sarsmaya devam ederken, eski tanıdıklar (Jamie Madrox / Multiple Man) ve yeni, kurnaz düşmanlar bu zayıflıktan faydalanmak için sıraya giriyor. Spider-Man, bu zorlu dönemde sadece düşmanlarıyla değil, kendi yetersizlikleriyle de savaşmak zorunda kalacak. Gerilimin ve aksiyonun tavan yaptığı Amazing Spider-Man 27. Cilt, Peter Parker\'ın sınırlarını test ediyor.\"', 'Nick Spencer', 115.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Cilt_27_Kor_Ucus.jpg', '2025-11-29 15:07:22'),
(52, 2, 'Amazing Spider-Man Vol. 5 Cilt 02 - Dostlar ve Düşmanlar', 'amazing-spider-man-vol-5-cilt-02-dostlar-ve-d-usmanlar', '\"Dostlar ve Düşmanlar! Peter Parker\'ın hayatı, Mary Jane Watson ile yeniden bir araya gelerek yavaşça düzene girmeye başlamıştır. Ancak Örümcek Adam\'ın dünyasında mutluluk asla uzun sürmez.\r\n\r\nTaskmaster ve Black Ant gibi tehlikeli paralı askerlerin ve gölgelerdeki Foreigner\'ın da dahil olduğu gizemli bir görev başlar. Bu yeni tehditler, sadece Spider-Man\'i değil, Peter Parker\'ın yeniden canlanan kişisel hayatını da hedef alır. Örümcek Adam, hem en yakın dostluklarını hem de en tehlikeli düşmanlarını aynı anda yönetmek zorunda kalacak. Kişisel drama, mizah ve soluksuz aksiyonun birleştiği bu cilt, Peter Parker\'ın sınırlarını zorluyor.\"', 'Nick Spencer', 120.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_02_-_Dostlar_ve_Dusmanlar.jpg', '2025-11-29 15:09:21'),
(53, 2, 'Amazing Spider-Man Vol. 5 Cilt 06 - Absolute Carnage', 'amazing-spider-man-vol-5-cilt-06-absolute-carnage', '\"Mutlak Katliam Başlıyor! Örümcek Adam\'ın en korkunç düşmanlarından Carnage (Katliam), Cletus Kasady, yeni ve kanlı bir görevle geri döndü. Amacı basit: Geçmişte simbiyota ev sahipliği yapmış olan herkesin peşine düşmek ve bedenlerinden simbiyot kalıntılarını (kodislerini) zorla sökmek!\r\n\r\nBu görev, sadece Eddie Brock\'u (Venom) değil, geçmişte Venom\'a ev sahipliği yapmış olan Peter Parker\'ı da Carnage\'ın kişisel hedefi haline getirir. New York, yayılan bir dehşet virüsünün pençesinde çaresiz kalırken, Örümcek Adam, Carnage\'ın mutlak güce ulaşmasını engellemek için hayatının en büyük tehdidiyle yüzleşmek zorundadır. Korku, gerilim ve yoğun aksiyon dolu Amazing Spider-Man Cilt 06, Marvel Evreni\'ni sarsan bu olayın tam kalbine dalıyor.\"', 'Nick Spencer', 140.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_06_-_Absolute_Carnage.jpg', '2025-11-29 15:09:57'),
(54, 2, 'Amazing Spider-Man Vol. 5 Cilt 07 - 2099', 'amazing-spider-man-vol-5-cilt-07-2099', '\"Gelecek Çöküyor! 2099 yılından gelen Örümcek Adam, Miguel O\'Hara, günümüze acil bir uyarı ve yıkım getiriyor. Kendi zaman çizelgesinin kaderi, günümüzdeki Örümcek Adam\'ın alacağı kararlara bağlıdır ve bu kriz, Peter Parker\'ın en önemli başarılarını tehdit etmektedir.\r\n\r\nHorizon Labs\'ın kaderi ve Miguel\'in beklenmedik gelişi, iki Spider-Man\'i zorunlu bir işbirliğine iter. Peter Parker ve Miguel O\'Hara, geleceği kurtarmak için geçmişin ve şimdinin düşmanlarıyla aynı anda savaşmak zorunda! Zamana, teknolojiye ve kaderin kendisine karşı verilen bu destansı yarış, Amazing Spider-Man Cilt 07 ile başlıyor.\"', 'Nick Spencer', 155.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_07_-_2099.jpg', '2025-11-29 15:10:39'),
(55, 2, 'Amazing Spider-Man Vol. 5 Cilt 08 - Tehditler ve Belalar', 'amazing-spider-man-vol-5-cilt-08-tehditler-ve-belalar', '\"Tehditler ve Belalar! Örümcek Adam\'ın kişisel hayatı ve kariyeri, gölgelerdeki gizemli düşman Kindred\'ın şeytani oyunlarıyla tamamen altüst olmuş durumdadır. Kindred\'ın etkisiyle, Mysterio gibi eski ve tanıdık düşmanlar bile kendi karanlık amaçları için zorla sahneye geri dönüyor.\r\n\r\nPeter Parker, Kindred\'ın kim olduğunu ve ondan ne istediğini çözmeye çalışırken, bir yandan da geri dönen klasik düşmanların yarattığı kaosla başa çıkmak zorundadır. Hem fiziksel hem de psikolojik baskının zirveye ulaştığı bu cilt, serinin ana gizemini derinleştiriyor ve Amazing Spider-Man Cilt 08, Örümcek Adam\'ın sınırlarını bir kez daha test ediyor.\"', 'Nick Spencer', 170.00, 100, '/mini_shop/products_img/Amazing_Spider-Man_Vol_5_Cilt_08_-_Tehditler_ve_Belalar.jpg', '2025-11-29 15:11:25'),
(56, 2, 'Flash - Yaşayan En Hızlı Adam', 'flash-yasayan-en-hizli-adam', '\"Barry Allen\'ın hayatı, Central City Polis Departmanı\'nda adli tıp bilimcisi olarak devam ederken, evrenin en saf ve güçlü enerjisi olan Hız Gücü (Speed Force) ile birleşerek onu dünyanın en hızlı adamına dönüştürdü. Ancak bu hız, tek başına Barry\'nin değil, tüm zaman çizelgesinin kaderini etkilemektedir.\r\n\r\nBu cilt, Flash\'ın sadece süper kahramanlık görevleriyle değil, aynı zamanda hızının bilimsel gizemleriyle de yüzleştiği maceraları içerir. Hız Gücü\'nün sınırlarını zorlayan, yeni düşmanlar ve beklenmedik ittifaklarla (kapakta gördüğünüz Batman gibi) dolu olan bu seri, DC Rebirth döneminin temelini atan hikayelere odaklanır. Kılıçların ve bilimin çarpıştığı, aksiyon ve bilim kurgu dolu bir Flash destanı!\"', 'Joshua Williamson', 180.00, 100, '/mini_shop/products_img/Flash_-_Yasayan_En_Hizli_Adam.jpg', '2025-11-29 15:15:02'),
(57, 2, 'Flash Cilt 3 Goril Savaşı', 'flash-cilt-3-goril-savasi', '\"Central City, zekasıyla insanlığı bile aşan Goril Grodd ve devasa ordusunun kuşatması altında! Hız Gücü\'ne (Speed Force) ulaşmanın ve bu gücü ele geçirmenin yollarını arayan Grodd, sadece fiziksel gücüyle değil, aynı zamanda güçlü telepati yeteneğiyle de Flash\'ı zorluyor.\r\n\r\nFlash, Grodd\'un akıl oyunlarına karşı kendi hızını ve zekasını kullanmak zorunda. Üstelik bu kaosa, Flash\'ın en azılı düşmanlarından oluşan Rogues (Kötüler) de katılıyor! Flash, sadece Goril ordusunu değil, aynı zamanda zihnini ele geçirmeye çalışan Grodd\'u durdurmak için hem fiziksel hem de zihinsel olarak en zorlu savaşına giriyor. Flash Cilt 3: Goril Savaşı, soluksuz bir aksiyon ve strateji sunuyor.\"', 'Francis Manapul & Brian Buccellato', 195.00, 99, '/mini_shop/products_img/Flash_Cilt_3_Goril_Savasi.jpg', '2025-11-29 15:15:57'),
(58, 2, 'Flash Cilt 8 - Flash Savaşı', 'flash-cilt-8-flash-savasi', '\"Hızın Son Savaşı! DC Evreni\'nin en hızlı iki kahramanı, Barry Allen ve Wally West, zaman çizelgesindeki büyük bir kırılma noktası ve kaderle ilgili hayati bir anlaşmazlık yüzünden karşı karşıya geliyor. Bu iç savaş o kadar şiddetli ki, hızlarının gücü sadece Central City\'yi değil, Hız Gücü\'nün (Speed Force) kendisini ve tüm zaman-uzay sürekliliğini tehdit ediyor.\r\n\r\nWally\'nin kaybettiği anıları geri alma çabası ve Barry\'nin onu durdurma çaresizliği... Sadece kimin daha hızlı olduğu değil, dostluğun ve zamanın kaderinin belirlendiği Flash Cilt 8: Flash Savaşı, DC Evreni\'ni temelinden sarsan o destansı olayı sunuyor.\"', 'Joshua Williamson', 210.00, 100, '/mini_shop/products_img/Flash_Cilt_8_-_Flash_Savasi.jpg', '2025-11-29 15:16:46');
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `author`, `price`, `stock`, `image_url`, `created_at`) VALUES
(59, 2, 'İç Savaş 2 Amazing Spider-Man X-Men', 'ic-savas-2-amazing-spider-man-x-men', '\"Geleceği Durdurmak mı, Kontrol Etmek mi? Marvel Evreni, geleceği görme gücüne sahip yeni bir Inhuman\'ın ortaya çıkmasıyla ikiye bölünen kahramanların savaşına tanıklık ediyor. Kaptan Marvel (Carol Danvers), kehanetleri kullanarak suçları önceden durdurmayı savunurken; Iron Man (Tony Stark), bu durumun özgür iradeyi yok ettiğini iddia ediyor.\r\n\r\nÖrümcek Adam, kendi şirketi Parker Industries\'in bu gücü kullanmak zorunda kalması nedeniyle etik savaşın tam ortasına düşer ve zorlu bir seçim yapmak zorundadır. X-Men ise Terrigen Misti tehdidiyle kendi türlerinin geleceği için savaşırken, kehanetlere güvenmektense kendi kaderlerini çizmeye kararlıdır. İki büyük serinin de kaderini belirleyen bu çatışmaya, kahramanların vicdanlarından bakacağınız aksiyon yüklü bir tie-in hikayesi!\"', 'Dan Slott & Cullen Bunn', 245.00, 100, '/mini_shop/products_img/Ic_Savas_2_Amazing_Spider-Man_X-Men.jpg', '2025-11-29 15:21:18'),
(60, 2, 'Moon Knight (2014) Cilt 1 Ölümden Geriye', 'moon-knight-2014-cilt-1-ol-umden-geriye', '\"Marc Spector, aldığı ağır darbelerden sonra ölümden geri döndü. Ancak artık sadece intikam arayan maskeli bir savaşçı değil; o, New York gecelerinin yüzü: Bay Şövalye (Mr. Knight).\r\n\r\nYazar Warren Ellis ve çizer Declan Shalvey\'nin imzasını taşıyan bu çığır açıcı seri, Ay Şövalyesi\'nin karmaşık çoklu kişiliklerine odaklanıyor. Her biri kendi içinde tamamlanmış, birbirinden tuhaf, yüksek konseptli suçları araştıran Bay Şövalye\'nin yeni görevi, sadece New York\'u değil, kendi paramparça zihninin sınırlarını da korumaktır. Sürreal, sert ve modern bir süper kahraman noir\'ı. Moon Knight (2014) Cilt 1, karakterin en iyi hikayelerinden biri kabul edilmektedir.\"', 'Warren Ellis', 225.00, 100, '/mini_shop/products_img/Moon_Knight_2014_Cilt_1_Olumden_Geriye.jpg', '2025-11-29 15:22:08'),
(61, 2, 'Moon Knight (2016) Cilt 2 Reenkarnasyonlar', 'moon-knight-2016-cilt-2-reenkarnasyonlar', '\"Moon Knight\'ın mistik ve psikolojik yolculuğu daha da derinleşiyor! Marc Spector, sadece kendi çoklu kişilikleriyle değil, aynı zamanda Khonshu\'nun binlerce yıllık tarihiyle de yüzleşmek zorunda kalıyor. Ay Tanrısı Khonshu\'nun rehberliğinde, Marc Spector, Khonshu\'nun geçmişteki bedenleri (avatarları) ve Reenkarnasyonları aracılığıyla kendi akıl sağlığının bu kadim döngüdeki yerini sorguluyor.\r\n\r\nGeçmişin sırlarının bugünün savaşını körüklediği bu gerilim dolu ciltte, Moon Knight aynı zamanda Khonshu\'ya tapan fanatik bir tarikatla da yüzleşmek zorunda kalır. Mistik, psikolojik ve aksiyon dolu bu macera, Jeff Lemire\'in imzasını taşıyor. Moon Knight Cilt 2: Reenkarnasyonlar.\"', 'Jeff Lemire', 205.00, 100, '/mini_shop/products_img/Moon_Knight_2016_Cilt_2_Reenkarnasyonlar.jpg', '2025-11-29 15:22:55'),
(62, 2, 'Rebirth Flash 2 - Karanlık Hız', 'rebirth-flash-2-karanlik-hiz', '\"Hızın Karanlık Yüzü! Barry Allen\'ın dünyası, Hız Gücü\'nün (Speed Force) karanlık ve tehlikeli bir yansıması olan Negatif Hız Gücü ile tanışıyor. Bu yeni ve yıkıcı güç, Flash\'ın sadece fiziksel değil, zihinsel sınırlarını da zorluyor.\r\n\r\nÜstelik bu karmaşa yetmezmiş gibi, Flash\'ın en büyük düşmanı Reverse-Flash (Eobard Thawne) beklenmedik bir anda geri dönüyor. Thawne, Barry\'nin en hassas noktasına, annesinin ölümüne dair sırları ortaya dökerek, kahramanımızı acı bir hesaplaşmaya zorluyor. Hızın karanlık yüzünün keşfedildiği bu cilt, Flash Rebirth 2 - Karanlık Hız ile geliyor.\"', 'Joshua Williamson', 145.00, 100, '/mini_shop/products_img/Rebirth_Flash_2_-_Karanlik_Hiz.jpg', '2025-11-29 15:23:41'),
(63, 3, 'Rick And Morty Sayı 57', 'rick-and-morty-sayi-57', '\"Rick\'in kaotik dehası bir kez daha zincirlerinden boşanıyor! Morty, nihayet lisede işlerin yoluna girdiğini düşünürken, kendini yine Rick\'in çılgın, boyutlar arası krizlerinin ortasında bulur. Bu sayıda, Rick\'in en son deneyi veya galaksi çapındaki kişisel intikam görevi, sadece Morty\'nin okul hayatını değil, tüm zaman ve uzay kurallarını hiçe saymak üzeredir. Absürt bilim kurgu, varoluşsal krizler ve kara mizahın harmanlandığı Rick And Morty Sayı 57, sizi yine bilincin sınırlarına götürecek, eğlenceli ve derin bir maceraya davet ediyor.\"', 'Zac Gorman & Kyle Starks', 100.00, 100, '/mini_shop/products_img/Rick_And_Morty_Sayi_57.jpg', '2025-11-29 15:24:58'),
(64, 3, 'Rick And Morty Sayı 59', 'rick-and-morty-sayi-59', '\"Rick\'in kaotik dehası bir kez daha zincirlerinden boşanıyor ve Morty\'nin hayatı bir saniyede altüst oluyor! Bir bilimsel deney, bir boyutlar arası kaçış ya da sadece büyükbabanın can sıkıntısı... Sebebi ne olursa olsun, bu sayıda Rick, Morty ve bazen Summer\'ı da yanına alarak galaksinin en saçma ve tehlikeli köşesine doğru yola çıkıyor.\r\n\r\nRick\'in dahiyane ama ahlaki kuralları hiçe sayan bir kararıyla tüm ailenin zorlu bir sınavdan geçtiği Rick And Morty Sayı 59, yüksek konseptli bilim kurgu, varoluşsal krizler ve Rick\'in o bildiğimiz alaycı tavrıyla dolu, tam tadında bir macera sunuyor.\"', 'Zac Gorman & Kyle Starks', 110.00, 100, '/mini_shop/products_img/Rick_And_Morty_Sayi_59.jpg', '2025-11-29 15:26:04'),
(65, 2, 'Spider-Man - Kuantum Macerası', 'spider-man-kuantum-macerasi', '\"Zamanın Sınırlarında Bir Ağ! Örümcek Adam\'ın bu kez düşmanı fizik kuralları! Yeni ve tehlikeli bir kuantum teknolojisi, boyutlar arası geçitleri açarak zamanın ve uzayın dokusunu bozmaya başlıyor.\r\n\r\nPeter Parker, sadece kahramanlık yeteneklerini değil, aynı zamanda bilimsel dehasını da kullanarak bu kuantum krizini durdurmak zorunda kalır. Ağ fırlatmanın, akıl almaz bilim kurguyla buluştuğu bu macera, sizi zamanın ve boyutların sınırlarında nefes kesici bir yolculuğa çıkarıyor. Spider-Man - Kuantum Macerası, serinin en zihin bükücü hikayelerinden biridir.\"', 'Dan Slott', 140.00, 100, '/mini_shop/products_img/Spider-Man_-_Kuantum_Macerasi.jpg', '2025-11-29 15:27:24'),
(66, 2, 'Spider-Man George Ve Gwen Stacy\'nin Ölümü', 'spider-man-george-ve-gwen-stacy-nin-ol-um-u', '\"Çizgi Roman Tarihinin En Büyük Trajedileri! Bu cilt, Peter Parker\'ın masumiyetini ve iyimserliğini sonsuza dek elinden alan, bir dönemin sonunu getiren iki büyük kaybı içerir.\r\n\r\nÖnce, Peter\'ın sevdiği kadının babası Kaptan George Stacy\'nin, Örümcek Adam\'ın düşmanlarından birine karşı verilen bir savaşta hayatını kaybetmesine tanık olun. Bu, Peter\'ı kahramanlığın bedeliyle acı bir şekilde yüzleştirir. Ancak en büyük darbe, Yeşil Goblin\'in hain planıyla Peter\'ın ilk aşkı Gwen Stacy\'nin kaderinin belirlendiği o meşhur köprü sahnesiyle gelecektir. Duygusallığın, pişmanlığın ve çizgi roman dünyasının kurallarını yeniden yazan unutulmaz anların birleştiği bu cilt, bir efsanenin acı dolu büyümesini anlatıyor.\"', 'Stan Lee & Gerry Conway', 150.00, 100, '/mini_shop/products_img/Spider-Man_George_Ve_Gwen_Stacy_nin_Olumu.jpg', '2025-11-29 15:28:24'),
(67, 2, 'Spider-Punk - Yasaklananların Savaşı', 'spider-punk-yasaklananlarin-savasi', '\"Anarşinin Örümcek Ağı! Dünya 138\'in anarşist Örümcek Adam\'ı Hobie Brown (Spider-Punk), zalim otoriteye ve faşist rejime karşı isyanı yönetmeye devam ediyor. O sadece ağ fırlatmakla kalmıyor, aynı zamanda elektro gitarını silah olarak kullanıp müziğiyle devrim yapıyor.\r\n\r\nBu ciltte, yeni ve beklenmedik bir tehdit ortaya çıkarken, Spider-Punk, yanına en güvendiği müttefiklerini (muhtemelen Leopardon ve Captain Anarchy gibi diğer isyancı kahramanları) alarak \"Yasaklananların Savaşı\"nı başlatmak zorunda kalır. Punk rock estetiği, politik aksiyon ve soluksuz dövüşlerin birleştiği bu cilt, özgürlük için verilen savaşın gürültüsünü kulaklarınıza getiriyor!\"', 'Cody Ziglar', 160.00, 100, '/mini_shop/products_img/Spider-Punk_-_Yasaklananlarin_Savasi.jpg', '2025-11-29 15:29:12'),
(68, 2, 'Venom (2018) Cilt 3 Absolute Carnage Cilt 1', 'venom-2018-cilt-3-absolute-carnage-cilt-1', '\"Mutlak Dehşet Başlıyor! Cletus Kasady (Carnage) geri döndü ve intikam ateşiyle yanıp tutuşuyor. Amacı basit: Geçmişte herhangi bir simbiyota ev sahipliği yapmış olan herkesin bedenindeki kalıntıları (kodisleri) toplayarak Karanlık Tanrı\'ya (Knull) hizmet etmek.\r\n\r\nBu görev, Carnage\'ı doğrudan simbiyot soy ağacının en kritik halkası olan Eddie Brock ve oğlu Dylan\'a yönlendirir! New York, yayılan bir dehşet ve katliam virüsünün pençesindeyken, Venom sadece bir kahraman olarak değil, aynı zamanda oğlunu korumak zorunda olan çaresiz bir baba olarak da savaşmak zorundadır. Korku, yoğun aksiyon ve kişisel dramın iç içe geçtiği Venom Cilt 3, büyük olayın en kanlı cephesini sunuyor.\"', 'Donny Cates', 170.00, 99, '/mini_shop/products_img/Venom_2018_Cilt_3_Absolute_Carnage_Cilt_1.jpg', '2025-11-29 15:29:56'),
(69, 2, 'Venom (2018) Cilt 6 - Venom Alternatif Dünyada', 'venom-2018-cilt-6-venom-alternatif-d-unyada', '\"Çoklu Evrenin Venom\'ları! Symbiote Tanrısı Knull\'un tehdidi giderek büyürken, Eddie Brock (Venom) ve oğlu Dylan için Dünya artık güvenli bir yer değil. Çaresiz kalan Eddie, oğluna bir gelecek sağlamak umuduyla farklı bir boyuta kaçar.\r\n\r\nAncak sığındığı alternatif dünya, düşündüğü gibi bir sığınak değil, aksine Venom\'un çok daha korkutucu, tehlikeli ve acımasız versiyonlarıyla doludur. Eddie, hem oğlu için güvenli bir yer bulmak hem de yaklaşan kozmik savaşa hazırlanmak için, kendi alternatifleriyle ölümcül bir ittifak kurmak zorundadır. Yazar Donny Cates, bu ciltte çoklu evreni keşfe çıkarak, Venom Cilt 6 ile Knull\'un nihai savaşından önceki son duraklardan birini sunuyor!\"', 'Donny Cates', 172.50, 99, '/mini_shop/products_img/Venom_2018_Cilt_6_-_Venom_Alternatif_Dunyada.jpg', '2025-11-29 15:30:57'),
(70, 3, 'Ay Polisi', 'ay-polisi', '\"Ay üzerinde kanun ve düzeni sağlamanın nasıl bir iş olduğunu merak ettiniz mi? Günlük hayatın sıkıntıları ve meslek dertleri, Ay\'a taşınırsa ne olur?\r\n\r\nMinimalist çizgileri ve benzersiz zekasıyla tanınan Tom Gauld, bu eserinde Ay kolonilerinde devriye gezen, işleri pek de yolunda gitmeyen, yalnız bir polis memurunun absürt ve melankolik maceralarını anlatıyor. Ay\'daki bürokrasi, uzayda geçen küçük suçlar ve varoluşsal sorunlar... Zekice kurgulanmış diyaloglar ve kara mizahla dolu Ay Polisi, bilim kurgu klişelerini alıp bambaşka bir komediye dönüştürüyor.\"', 'Tom Gauld', 95.00, 100, '/mini_shop/products_img/Ay_Polisi.jpg', '2025-11-29 15:34:44'),
(71, 3, 'KUDÜS GÜNLÜKLERİ', 'kud-us-g-unl-ukleri', '\"Belgesel çizgi roman türünün usta isimlerinden Guy Delisle, bu kez rotayı Kudüs\'e çeviriyor. Eşi ve çocuklarıyla birlikte bir yıllığına bu tarihi ve karmaşık şehirde yaşayan Delisle, günlük hayatın sıradan anlarını, kültürel çatışmaları ve politik gerilimleri sade, mizahi ve dürüst bir dille aktarıyor.\r\n\r\nKudüs Günlükleri, sadece bir şehrin duvarlarının ardındaki yaşamı değil, aynı zamanda farklı inançların ve günlük rutinin kesiştiği noktaları gözlemliyor. Önyargılardan arınmış, kişisel gözlemlere dayanan bu eser, okuyucuyu hem güldüren hem de düşündüren eşsiz bir deneyim sunuyor. Angoulême Uluslararası Çizgi Roman Festivali ödüllü bir başyapıt.\"', 'Guy Delisle', 75.00, 100, '/mini_shop/products_img/KUDUS_GUNLUKLERI.jpg', '2025-11-29 15:35:38'),
(72, 3, 'Görünmez Krallık 1 - O Yolda Yürümek', 'g-or-unmez-krallik-1-o-yolda-y-ur-umek', '\"Kozmik bir bilim kurgu ve mistisizm şöleni! Yakın gelecekte, dünya üzerindeki iki büyük güç olan din ve ticaret birleşmiş, evrenin kontrolünü ele geçirmiştir. Ancak bu baskıcı düzen, iki sıradan insanın beklenmedik yollarda karşılaşmasıyla altüst olmak üzeredir.\r\n\r\nBiri, yeni bir keşiş olmak için eğitim alan inançlı bir kadın olan Vella. Diğeri ise bu evrenin en güçlü silahlarından biri olan yeni keşfedilmiş bir gezegende görevli, yorgun bir casus olan Brandel. Kaderleri kesişen bu iki karakter, kuralları hiçe sayarak evrenin görmezden geldiği gizli bir gücün peşine düşerler. Görsel açıdan çarpıcı, akıllıca yazılmış bu destan, inanç, güç ve isyan hakkında yepyeni bir hikaye sunuyor.\"', 'G. Willow Wilson', 99.00, 100, '/mini_shop/products_img/Gorunmez_Krallik_1_-_O_Yolda_Yurumek.jpg', '2025-11-29 15:36:37'),
(73, 3, 'Müjdeci 1. Cilt Teneke Yıldızlar', 'm-ujdeci-1-cilt-teneke-yildizlar', '\"Ay\'da doğan, distopik ve sürükleyici bir bilim kurgu masalı! Yazar Jeff Lemire ve çizer Dustin Nguyen\'den, görkemli görsellerle desteklenen bir başyapıt. On yıl boyunca, insanlığın Ay\'daki son kolonisinde yaşayan genç Ezra, Ay\'ın yüzeyinde tuhaf ve gizemli bir şey keşfeder. Bu keşif, sadece koloninin geleceğini değil, aynı zamanda annesinin ona söylediği yalanları da altüst edecektir. Ezra, bu soğuk, gri ve teknolojiyle dolu dünyada umudu, özgürlüğü ve ailesinin geçmişini aramaktadır. Müjdeci (Descender), modern bilim kurgu çizgi romanlarının en iyi örneklerinden biridir.\"', 'Jeff Lemire', 65.00, 100, '/mini_shop/products_img/Mujdeci_1_Cilt_Teneke_Yildizlar.jpg', '2025-11-29 15:37:54'),
(74, 3, 'Komançi 4', 'komanci-4', '\"Vahşi Batı\'nın sert ve acımasız atmosferinde geçen, Avrupa çizgi romanının en önemli Western serilerinden biri! Komançi, Pine Creek Çiftliği\'nin güzel ve inatçı sahibi Red Dust ile koruyucusu aksiyon adamı Jeremiah\'ın hikayesini anlatır.\r\n\r\nBu cilt, Red Dust\'ın Pine Creek üzerindeki haklarını savunmak için girdiği zorlu mücadeleleri ve etraflarındaki düşmanlarla yüzleşmelerini içeriyor. Hermann\'ın gerçekçi ve çarpıcı çizimleri, Greg\'in akılcı senaryosuyla birleştiğinde; okuyucuyu toz, kan ve onur dolu bir kovboy macerasının tam ortasına çekiyor. Avrupa Western\'i sevenler için bir klasik.\"', 'Greg', 95.00, 100, '/mini_shop/products_img/Komanci_4_1.jpg', '2025-11-29 15:38:49'),
(75, 3, 'Blankets - Örtüler', 'blankets-ort-uler', '\"Otobiyografik çizgi roman türünün mihenk taşlarından biri! Yazar ve çizer Craig Thompson, gençlik yıllarının zorlu ve samimi anılarını okuyucuya sunuyor.\r\n\r\nHikaye, Craig\'in dindar bir ailede büyümesini, küçük erkek kardeşiyle olan karmaşık ama güçlü bağını ve ilk aşkının keşfiyle gelen duygusal uyanışını konu alır. Thompson\'ın büyüleyici siyah beyaz çizimleri, masumiyetin, inancın, şüphenin ve gençliğin getirdiği çalkantılı duyguların yoğunluğunu ustalıkla yansıtır. Gençlik, aşk ve kendini keşfetme üzerine yazılmış, içten ve derinlikli bir başyapıt.\"', 'Craig Thompson', 90.00, 100, '/mini_shop/products_img/Blankets_-_Ortuler.jpg', '2025-11-29 15:40:22'),
(76, 3, 'Kim Korkar Hain Tilkiden', 'kim-korkar-hain-tilkiden', '\"Bazen tilki, tilki olmaktan vazgeçer; bazen de anne horoz olur! Benjamin Renner\'in kendine özgü, sulu boya estetiğiyle çizilmiş bu eserinde, sevimli ama beceriksiz bir tilkinin, bir tavuk sürüsüyle ve korumacı horozla yaşadığı absürt maceralara tanık oluyoruz.\r\n\r\nAmacı tavukları yemek olan bu tilki, bir dizi komik olay sonucunda kendini üç civcivin annesi rolünde bulur! Hainliği elinden giden tilki, bu beklenmedik ebeveynlik görevinin altından kalkabilecek mi? Mizah, sevimlilik ve beklenmedik olaylarla dolu bu eser, her yaştan okuyucuya hitap eden neşeli bir okuma sunuyor.\"', 'Benjamin Renner', 85.00, 100, '/mini_shop/products_img/Kim_Korkar_Hain_Tilkiden.jpg', '2025-11-29 15:41:04'),
(77, 3, 'Çizgi Romanı Anlamak', 'cizgi-romani-anlamak', '\"Çizgi romanları okumayı seviyor musunuz? Peki onları \'anlamayı\' denediniz mi? Ünlü yazar Neil Gaiman\'ın \'Bu kitabı okumak zorundasınız\' dediği bu eser, çizgi roman medyasının ne olduğu, nasıl çalıştığı ve gücünü nereden aldığına dair yazılmış en kapsamlı rehberdir.\r\n\r\nScott McCloud, bu eserde çizgi romanın tarihini, panelden panele geçişin sinemasal dilini, zamanın, mekanın ve rengin nasıl algılandığını bizzat çizgi roman formatında, mizahi ve görsel bir dille anlatıyor. Sadece çizgi roman okurları için değil, görsel sanatlara ilgi duyan herkes için ufuk açıcı, başucu niteliğinde teorik bir başyapıttır.\"', 'Scott McCloud', 49.90, 100, '/mini_shop/products_img/Cizgi_Romani_Anlamak.jpg', '2025-11-29 15:41:55'),
(78, 3, 'Bu Bizim Anlaşmamız', 'bu-bizim-anlasmamiz', '\"Bazen bazı sırlar, sadece en yakın arkadaşların arasında kalmalıdır. Bisikletlerine atlayıp gece yarısı yola çıkan bir grup gencin hikayesi... Onlar için bu, sıradan bir yaz macerası değil, çözülmesi gereken büyük bir gizemdir.\r\n\r\nBu Bizim Anlaşmamız, bir grup gencin, yaşadıkları kasabada gizlenen doğaüstü bir sırrı ortaya çıkarmak için çıktıkları bu yolculuğu, nostaljik ve görsel açıdan büyüleyici bir dille anlatıyor. Ryan Andrews\'un kendine has, karanlık ama sıcak çizimleri, dostluğun, yaz gecelerinin ve bilinmeyene duyulan merakın mükemmel bir harmanını sunuyor.\"', 'Ryan Andrews', 75.00, 100, '/mini_shop/products_img/Bu_Bizim_Anlasmamiz.jpg', '2025-11-29 15:42:55'),
(79, 3, 'Gece Kütüphanecisi', 'gece-k-ut-uphanecisi', '\"Kütüphaneler sadece kitapların saklandığı yerler değildir... Gece olduğunda, kütüphaneler bambaşka bir dünyaya açılır! Bu hikaye, babası gizemli bir şekilde ortadan kaybolan küçük Spencer\'ın macerasını konu alıyor. Spencer, babasının bir zamanlar çalıştığı kütüphanenin, gece vakti canlanan, sihirli bir yer olduğunu keşfeder.\r\n\r\nKitapların raflardan indiği, kahramanların canlandığı ve kötülerin kol gezdiği bu gizli dünyada, Spencer, iki yeni arkadaşı ile birlikte babasının kayboluşunun ardındaki sırrı çözmek zorunda kalır. Fantastik macera, gizem ve kütüphane sevgisinin iç içe geçtiği Gece Kütüphanecisi, genç okuyucular için unutulmaz bir deneyim.\"', 'Christopher Lincoln', 60.00, 100, '/mini_shop/products_img/Gece_Kutuphanecisi.jpg', '2025-11-29 15:43:42'),
(80, 3, 'Zaman Makinesi', 'zaman-makinesi', '\"Bilim kurgu edebiyatının babası H.G. Wells\'in zamana meydan okuyan klasik eseri, görsel bir şölenle yeniden canlanıyor! Zaman Yolcusu adıyla bilinen mucit, icat ettiği makine ile insanlığın geleceğini görmek için yüzyıllar atlayarak yolculuğa çıkar. Ancak vardığı yıl, beklediği ütopya yerine, insan ırkının iki farklı, ürkütücü türe ayrıldığı distopik bir dünya ile karşılaşır. Evrim, teknoloji ve insanlık üzerine yazılmış bu felsefi eserin gerilim ve aksiyon dolu çizgi roman uyarlaması, okuyucuyu bilinmeyene doğru bir yolculuğa çıkarıyor.\"', 'Dobbs', 69.90, 100, '/mini_shop/products_img/Zaman_Makinesi.jpg', '2025-11-29 15:45:53'),
(81, 3, 'Zenobia-Bir Göçmen Hikayesi', 'zenobia-bir-g-ocmen-hikayesi', '\"Savaşın, ayrılığın ve umudun hikayesi... Küçük Zenobia, ailesiyle birlikte Akdeniz\'in suları üzerinde, yaşamla ölüm arasındaki ince çizgide bir yolculuğa çıkar. Bu eser, sadece bir mültecinin zorlu yolculuğunu değil, aynı zamanda çocukluk masumiyetinin büyük trajediler karşısındaki direncini anlatır. Zenobia\'nın gözünden, terk edilen bir evin, geride bırakılan anıların ve bilinmeyene doğru atılan her adımın ağırlığını hissediyoruz. Çarpıcı çizimler ve dokunaklı anlatımıyla, günümüzün en büyük insani dramlarından birine sade ve etkileyici bir pencere açan, unutulmaz bir eser.\"', 'Dürr', 115.00, 98, '/mini_shop/products_img/Zenobia-Bir_Gocmen_Hikayesi.jpg', '2025-11-29 15:46:45'),
(82, 3, 'Üç Gölge', 'uc-g-olge', '\"Mutluluğun ve pastoral yaşamın aniden tehdit altına alınışının lirik ve masalsı hikayesi... Küçük Louis, babası ve annesiyle birlikte ormanın kenarında huzurlu bir hayat sürmektedir. Ancak bir gün, uzaktan yaklaşan gizemli ve ürkütücü \'Üç Gölge\', ailenin tüm huzurunu altüst eder. Louis\'nin babası, bu gölgelerin çocuğunu ondan alacağını anlar ve onu korumak için umutsuz, son bir yolculuğa çıkar. Kayıp, sevgi, fedakarlık ve macera üzerine çizilmiş bu eser, çizimleri ve dokunaklı anlatımıyla bir masal atmosferi yaratır. Cyril Pedrosa\'dan, kalbe dokunan, hüzünlü bir başyapıt.\"', 'Cyril Pedrosa', 130.00, 100, '/mini_shop/products_img/Uc_Golge.jpg', '2025-11-29 15:47:26');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_theme', 'dark', '2025-11-26 18:01:41'),
(2, 'comments_enabled', '1', '2025-11-29 19:20:29'),
(3, 'site_name', 'ManRoMa', '2025-11-26 18:24:07'),
(28, 'page_footer_deneme', '1', '2026-03-05 16:02:08'),
(30, 'low_stock_threshold', '5', '2026-03-06 20:58:56'),
(33, 'content_page_deneme_paragraphs', '[\"denem\",\"de\",\"d\"]', '2026-03-06 21:49:17'),
(38, 'page_footer_denemee', '1', '2026-03-06 21:49:37');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `site_users`
--

CREATE TABLE `site_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `role` varchar(50) DEFAULT 'standard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `site_users`
--

INSERT INTO `site_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `address`, `status`, `created_at`, `last_login`, `role`) VALUES
(1, 'User', 'user@gmail.com', '$2y$10$brDKpQsPqOlKjfspdefYeu9lwm//y1ERzLYC6XsMNguO8O8zA/MvW', '', '', NULL, 'active', '2025-11-29 19:16:10', '2026-03-06 22:10:20', 'standard');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `site_visits`
--

CREATE TABLE `site_visits` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `site_visits`
--

INSERT INTO `site_visits` (`id`, `ip_address`, `user_agent`, `page_url`, `referrer`, `visit_date`, `visit_time`, `created_at`) VALUES
(1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/orders.php', '2025-11-30', '10:18:02', '2025-11-30 07:18:02'),
(2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/index.php', '2025-11-30', '10:18:03', '2025-11-30 07:18:03'),
(3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/index.php', '2025-11-30', '10:18:41', '2025-11-30 07:18:41'),
(4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:18:42', '2025-11-30 07:18:42'),
(5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:43', '2025-11-30 07:18:43'),
(6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:44', '2025-11-30 07:18:44'),
(7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:45', '2025-11-30 07:18:45'),
(8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:45', '2025-11-30 07:18:45'),
(9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:45', '2025-11-30 07:18:45'),
(10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:45', '2025-11-30 07:18:45'),
(11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(13, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(14, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(15, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(16, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:46', '2025-11-30 07:18:46'),
(17, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(18, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(19, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(20, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(21, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(22, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:47', '2025-11-30 07:18:47'),
(23, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:48', '2025-11-30 07:18:48'),
(24, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:48', '2025-11-30 07:18:48'),
(25, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:48', '2025-11-30 07:18:48'),
(26, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:18:48', '2025-11-30 07:18:48'),
(27, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:19:04', '2025-11-30 07:19:04'),
(28, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:19:06', '2025-11-30 07:19:06'),
(29, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:19:20', '2025-11-30 07:19:20'),
(30, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:20:07', '2025-11-30 07:20:07'),
(31, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:21:05', '2025-11-30 07:21:05'),
(32, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:27:24', '2025-11-30 07:27:24'),
(33, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:05', '2025-11-30 07:29:05'),
(34, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:06', '2025-11-30 07:29:06'),
(35, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:07', '2025-11-30 07:29:07'),
(36, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:07', '2025-11-30 07:29:07'),
(37, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:07', '2025-11-30 07:29:07'),
(38, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:08', '2025-11-30 07:29:08'),
(39, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:15', '2025-11-30 07:29:15'),
(40, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:15', '2025-11-30 07:29:15'),
(41, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:16', '2025-11-30 07:29:16'),
(42, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:17', '2025-11-30 07:29:17'),
(43, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:29:17', '2025-11-30 07:29:17'),
(44, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:30:47', '2025-11-30 07:30:47'),
(45, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:32:31', '2025-11-30 07:32:31'),
(46, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:32:53', '2025-11-30 07:32:53'),
(47, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:33:34', '2025-11-30 07:33:34'),
(48, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:33:48', '2025-11-30 07:33:48'),
(49, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:34:28', '2025-11-30 07:34:28'),
(50, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:34:49', '2025-11-30 07:34:49'),
(51, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/register.php', '2025-11-30', '10:35:10', '2025-11-30 07:35:10'),
(52, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:35:11', '2025-11-30 07:35:11'),
(53, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php', '2025-11-30', '10:35:39', '2025-11-30 07:35:39'),
(54, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:35:41', '2025-11-30 07:35:41'),
(55, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/contact.php', '2025-11-30', '10:35:42', '2025-11-30 07:35:42'),
(56, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php', '2025-11-30', '10:35:56', '2025-11-30 07:35:56'),
(57, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php', '2025-11-30', '10:36:08', '2025-11-30 07:36:08'),
(58, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:36:19', '2025-11-30 07:36:19'),
(59, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_Shop/', '', '2025-11-30', '10:50:01', '2025-11-30 07:50:01'),
(60, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_Shop/', '2025-11-30', '10:50:02', '2025-11-30 07:50:02'),
(61, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/contact.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '10:52:30', '2025-11-30 07:52:30'),
(62, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/', '', '2025-11-30', '15:41:15', '2025-11-30 12:41:15'),
(63, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/', '', '2025-11-30', '15:42:03', '2025-11-30 12:42:03'),
(64, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:42:41', '2025-11-30 12:42:41'),
(65, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:51:49', '2025-11-30 12:51:49'),
(66, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:51:50', '2025-11-30 12:51:50'),
(67, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:11', '2025-11-30 12:52:11'),
(68, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:11', '2025-11-30 12:52:11'),
(69, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:12', '2025-11-30 12:52:12'),
(70, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:12', '2025-11-30 12:52:12'),
(71, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:12', '2025-11-30 12:52:12'),
(72, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:12', '2025-11-30 12:52:12'),
(73, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:12', '2025-11-30 12:52:12'),
(74, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:13', '2025-11-30 12:52:13'),
(75, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:13', '2025-11-30 12:52:13'),
(76, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:13', '2025-11-30 12:52:13'),
(77, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:52:13', '2025-11-30 12:52:13'),
(78, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:55:14', '2025-11-30 12:55:14'),
(79, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:55:16', '2025-11-30 12:55:16'),
(80, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:55:17', '2025-11-30 12:55:17'),
(81, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/admin/products.php', '2025-11-30', '15:55:18', '2025-11-30 12:55:18'),
(82, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/index.php', '2025-11-30', '15:55:37', '2025-11-30 12:55:37'),
(83, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:25', '2025-11-30 12:57:25'),
(84, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:56', '2025-11-30 12:57:56'),
(85, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(86, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(87, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(88, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(89, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(90, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:57', '2025-11-30 12:57:57'),
(91, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:58', '2025-11-30 12:57:58'),
(92, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:58', '2025-11-30 12:57:58'),
(93, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:59', '2025-11-30 12:57:59'),
(94, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:59', '2025-11-30 12:57:59'),
(95, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:59', '2025-11-30 12:57:59'),
(96, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:57:59', '2025-11-30 12:57:59'),
(97, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:00', '2025-11-30 12:58:00'),
(98, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:02', '2025-11-30 12:58:02'),
(99, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:02', '2025-11-30 12:58:02'),
(100, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:02', '2025-11-30 12:58:02'),
(101, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:02', '2025-11-30 12:58:02'),
(102, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:02', '2025-11-30 12:58:02'),
(103, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:03', '2025-11-30 12:58:03'),
(104, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/about.php', 'http://localhost/mini_shop/admin/pages.php?edit_id=1', '2025-11-30', '15:58:03', '2025-11-30 12:58:03'),
(105, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:04', '2025-11-30 12:58:04'),
(106, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:08', '2025-11-30 12:58:08'),
(107, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:08', '2025-11-30 12:58:08'),
(108, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(109, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(110, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(111, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(112, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(113, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:09', '2025-11-30 12:58:09'),
(114, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(115, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(116, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(117, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(118, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(119, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:10', '2025-11-30 12:58:10'),
(120, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:11', '2025-11-30 12:58:11'),
(121, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:11', '2025-11-30 12:58:11'),
(122, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:11', '2025-11-30 12:58:11'),
(123, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:11', '2025-11-30 12:58:11'),
(124, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2025-11-30', '15:58:11', '2025-11-30 12:58:11'),
(125, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/product.php?slug=kim-korkar-hain-tilkiden', 'http://localhost/mini_shop/index.php', '2025-11-30', '15:59:13', '2025-11-30 12:59:13'),
(126, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '15:59:17', '2025-11-30 12:59:17'),
(127, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:13', '2025-11-30 13:00:13'),
(128, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:13', '2025-11-30 13:00:13'),
(129, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(130, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(131, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(132, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(133, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(134, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:14', '2025-11-30 13:00:14'),
(135, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(136, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(137, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(138, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(139, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(140, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:15', '2025-11-30 13:00:15'),
(141, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:16', '2025-11-30 13:00:16'),
(142, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:16', '2025-11-30 13:00:16'),
(143, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:16', '2025-11-30 13:00:16'),
(144, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:16', '2025-11-30 13:00:16'),
(145, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:16', '2025-11-30 13:00:16'),
(146, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:17', '2025-11-30 13:00:17'),
(147, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:17', '2025-11-30 13:00:17'),
(148, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:17', '2025-11-30 13:00:17'),
(149, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:17', '2025-11-30 13:00:17'),
(150, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:17', '2025-11-30 13:00:17'),
(151, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(152, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(153, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(154, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(155, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(156, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(157, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:18', '2025-11-30 13:00:18'),
(158, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:19', '2025-11-30 13:00:19'),
(159, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:19', '2025-11-30 13:00:19'),
(160, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:19', '2025-11-30 13:00:19'),
(161, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:19', '2025-11-30 13:00:19'),
(162, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:20', '2025-11-30 13:00:20'),
(163, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:20', '2025-11-30 13:00:20'),
(164, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:20', '2025-11-30 13:00:20'),
(165, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:20', '2025-11-30 13:00:20'),
(166, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(167, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(168, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(169, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(170, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(171, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(172, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:21', '2025-11-30 13:00:21'),
(173, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(174, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(175, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(176, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(177, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(178, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(179, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(180, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:22', '2025-11-30 13:00:22'),
(181, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:23', '2025-11-30 13:00:23'),
(182, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:23', '2025-11-30 13:00:23'),
(183, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:23', '2025-11-30 13:00:23'),
(184, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:23', '2025-11-30 13:00:23'),
(185, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2025-11-30', '16:00:23', '2025-11-30 13:00:23'),
(186, '127.0.0.1', NULL, NULL, NULL, '2026-03-03', '04:08:24', '2026-03-03 01:08:24'),
(187, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:25:42', '2026-03-05 14:25:42'),
(188, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:35:19', '2026-03-05 14:35:19'),
(189, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:35:20', '2026-03-05 14:35:20'),
(190, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:45:48', '2026-03-05 14:45:48'),
(191, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:47:19', '2026-03-05 14:47:19'),
(192, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:47:46', '2026-03-05 14:47:46'),
(193, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:47:48', '2026-03-05 14:47:48'),
(194, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:00', '2026-03-05 14:48:00'),
(195, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:04', '2026-03-05 14:48:04'),
(196, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:08', '2026-03-05 14:48:08'),
(197, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:15', '2026-03-05 14:48:15'),
(198, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:21', '2026-03-05 14:48:21'),
(199, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:22', '2026-03-05 14:48:22'),
(200, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:28', '2026-03-05 14:48:28'),
(201, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:53', '2026-03-05 14:48:53'),
(202, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:48:54', '2026-03-05 14:48:54'),
(203, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:22', '2026-03-05 14:50:22'),
(204, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:27', '2026-03-05 14:50:27'),
(205, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:31', '2026-03-05 14:50:31'),
(206, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:32', '2026-03-05 14:50:32'),
(207, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:33', '2026-03-05 14:50:33'),
(208, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:33', '2026-03-05 14:50:33'),
(209, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:34', '2026-03-05 14:50:34'),
(210, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:34', '2026-03-05 14:50:34'),
(211, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:34', '2026-03-05 14:50:34'),
(212, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:36', '2026-03-05 14:50:36'),
(213, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:38', '2026-03-05 14:50:38'),
(214, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:41', '2026-03-05 14:50:41'),
(215, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:41', '2026-03-05 14:50:41'),
(216, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(217, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(218, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(219, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(220, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(221, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:42', '2026-03-05 14:50:42'),
(222, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:50:47', '2026-03-05 14:50:47'),
(223, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:01', '2026-03-05 14:51:01'),
(224, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:04', '2026-03-05 14:51:04'),
(225, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:19', '2026-03-05 14:51:19'),
(226, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:21', '2026-03-05 14:51:21'),
(227, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:24', '2026-03-05 14:51:24'),
(228, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:26', '2026-03-05 14:51:26'),
(229, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:26', '2026-03-05 14:51:26'),
(230, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:27', '2026-03-05 14:51:27'),
(231, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:27', '2026-03-05 14:51:27'),
(232, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:28', '2026-03-05 14:51:28'),
(233, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:28', '2026-03-05 14:51:28'),
(234, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:28', '2026-03-05 14:51:28'),
(235, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:29', '2026-03-05 14:51:29'),
(236, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:30', '2026-03-05 14:51:30'),
(237, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:31', '2026-03-05 14:51:31'),
(238, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:33', '2026-03-05 14:51:33'),
(239, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:33', '2026-03-05 14:51:33'),
(240, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:34', '2026-03-05 14:51:34'),
(241, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:36', '2026-03-05 14:51:36'),
(242, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:40', '2026-03-05 14:51:40'),
(243, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:40', '2026-03-05 14:51:40'),
(244, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:41', '2026-03-05 14:51:41'),
(245, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:41', '2026-03-05 14:51:41'),
(246, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:42', '2026-03-05 14:51:42'),
(247, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:43', '2026-03-05 14:51:43'),
(248, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:43', '2026-03-05 14:51:43'),
(249, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:44', '2026-03-05 14:51:44'),
(250, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:44', '2026-03-05 14:51:44'),
(251, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:46', '2026-03-05 14:51:46'),
(252, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:49', '2026-03-05 14:51:49'),
(253, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:51', '2026-03-05 14:51:51'),
(254, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:53', '2026-03-05 14:51:53'),
(255, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:54', '2026-03-05 14:51:54'),
(256, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:54', '2026-03-05 14:51:54'),
(257, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:54', '2026-03-05 14:51:54'),
(258, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:55', '2026-03-05 14:51:55'),
(259, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:51:58', '2026-03-05 14:51:58'),
(260, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(261, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(262, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(263, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(264, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(265, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:18', '2026-03-05 14:56:18'),
(266, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:19', '2026-03-05 14:56:19'),
(267, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:21', '2026-03-05 14:56:21'),
(268, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:22', '2026-03-05 14:56:22'),
(269, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:30', '2026-03-05 14:56:30'),
(270, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:30', '2026-03-05 14:56:30'),
(271, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:56:46', '2026-03-05 14:56:46'),
(272, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:57:24', '2026-03-05 14:57:24'),
(273, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:57:24', '2026-03-05 14:57:24'),
(274, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:57:33', '2026-03-05 14:57:33'),
(275, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:20', '2026-03-05 14:59:20'),
(276, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:20', '2026-03-05 14:59:20'),
(277, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:21', '2026-03-05 14:59:21'),
(278, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:21', '2026-03-05 14:59:21'),
(279, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:23', '2026-03-05 14:59:23'),
(280, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:24', '2026-03-05 14:59:24'),
(281, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:24', '2026-03-05 14:59:24'),
(282, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:24', '2026-03-05 14:59:24'),
(283, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:26', '2026-03-05 14:59:26'),
(284, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:29', '2026-03-05 14:59:29'),
(285, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:30', '2026-03-05 14:59:30'),
(286, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:30', '2026-03-05 14:59:30'),
(287, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:31', '2026-03-05 14:59:31'),
(288, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:34', '2026-03-05 14:59:34'),
(289, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:35', '2026-03-05 14:59:35'),
(290, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:35', '2026-03-05 14:59:35'),
(291, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:35', '2026-03-05 14:59:35'),
(292, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:35', '2026-03-05 14:59:35'),
(293, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:42', '2026-03-05 14:59:42'),
(294, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:46', '2026-03-05 14:59:46'),
(295, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:48', '2026-03-05 14:59:48'),
(296, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:48', '2026-03-05 14:59:48'),
(297, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:48', '2026-03-05 14:59:48'),
(298, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:48', '2026-03-05 14:59:48');
INSERT INTO `site_visits` (`id`, `ip_address`, `user_agent`, `page_url`, `referrer`, `visit_date`, `visit_time`, `created_at`) VALUES
(299, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:49', '2026-03-05 14:59:49'),
(300, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:52', '2026-03-05 14:59:52'),
(301, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:52', '2026-03-05 14:59:52'),
(302, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:58', '2026-03-05 14:59:58'),
(303, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '17:59:59', '2026-03-05 14:59:59'),
(304, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:01', '2026-03-05 15:00:01'),
(305, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:01', '2026-03-05 15:00:01'),
(306, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:02', '2026-03-05 15:00:02'),
(307, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:50', '2026-03-05 15:00:50'),
(308, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:50', '2026-03-05 15:00:50'),
(309, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:51', '2026-03-05 15:00:51'),
(310, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:54', '2026-03-05 15:00:54'),
(311, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:00:59', '2026-03-05 15:00:59'),
(312, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:06', '2026-03-05 15:01:06'),
(313, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:11', '2026-03-05 15:01:11'),
(314, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:13', '2026-03-05 15:01:13'),
(315, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:25', '2026-03-05 15:01:25'),
(316, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:26', '2026-03-05 15:01:26'),
(317, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:28', '2026-03-05 15:01:28'),
(318, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:29', '2026-03-05 15:01:29'),
(319, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:30', '2026-03-05 15:01:30'),
(320, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:31', '2026-03-05 15:01:31'),
(321, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:32', '2026-03-05 15:01:32'),
(322, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:33', '2026-03-05 15:01:33'),
(323, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:34', '2026-03-05 15:01:34'),
(324, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:35', '2026-03-05 15:01:35'),
(325, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:36', '2026-03-05 15:01:36'),
(326, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:37', '2026-03-05 15:01:37'),
(327, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:37', '2026-03-05 15:01:37'),
(328, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:38', '2026-03-05 15:01:38'),
(329, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:42', '2026-03-05 15:01:42'),
(330, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:45', '2026-03-05 15:01:45'),
(331, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:48', '2026-03-05 15:01:48'),
(332, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:49', '2026-03-05 15:01:49'),
(333, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:50', '2026-03-05 15:01:50'),
(334, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:50', '2026-03-05 15:01:50'),
(335, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:51', '2026-03-05 15:01:51'),
(336, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:52', '2026-03-05 15:01:52'),
(337, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:53', '2026-03-05 15:01:53'),
(338, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:54', '2026-03-05 15:01:54'),
(339, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:55', '2026-03-05 15:01:55'),
(340, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:56', '2026-03-05 15:01:56'),
(341, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:01:57', '2026-03-05 15:01:57'),
(342, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:02:04', '2026-03-05 15:02:04'),
(343, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:02:09', '2026-03-05 15:02:09'),
(344, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:09:56', '2026-03-05 15:09:56'),
(345, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:09:56', '2026-03-05 15:09:56'),
(346, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:09:56', '2026-03-05 15:09:56'),
(347, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:09:57', '2026-03-05 15:09:57'),
(348, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:09:58', '2026-03-05 15:09:58'),
(349, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:10:13', '2026-03-05 15:10:13'),
(350, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:10:13', '2026-03-05 15:10:13'),
(351, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:10:23', '2026-03-05 15:10:23'),
(352, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:10:52', '2026-03-05 15:10:52'),
(353, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:10:53', '2026-03-05 15:10:53'),
(354, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:24', '2026-03-05 15:11:24'),
(355, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:30', '2026-03-05 15:11:30'),
(356, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:36', '2026-03-05 15:11:36'),
(357, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:37', '2026-03-05 15:11:37'),
(358, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:44', '2026-03-05 15:11:44'),
(359, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:44', '2026-03-05 15:11:44'),
(360, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:11:58', '2026-03-05 15:11:58'),
(361, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:12:26', '2026-03-05 15:12:26'),
(362, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:12:29', '2026-03-05 15:12:29'),
(363, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:10', '2026-03-05 15:13:10'),
(364, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:11', '2026-03-05 15:13:11'),
(365, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:11', '2026-03-05 15:13:11'),
(366, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:18', '2026-03-05 15:13:18'),
(367, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:19', '2026-03-05 15:13:19'),
(368, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:20', '2026-03-05 15:13:20'),
(369, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:21', '2026-03-05 15:13:21'),
(370, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:22', '2026-03-05 15:13:22'),
(371, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:23', '2026-03-05 15:13:23'),
(372, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:24', '2026-03-05 15:13:24'),
(373, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:25', '2026-03-05 15:13:25'),
(374, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:26', '2026-03-05 15:13:26'),
(375, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:26', '2026-03-05 15:13:26'),
(376, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:28', '2026-03-05 15:13:28'),
(377, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:33', '2026-03-05 15:13:33'),
(378, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:35', '2026-03-05 15:13:35'),
(379, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:41', '2026-03-05 15:13:41'),
(380, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:43', '2026-03-05 15:13:43'),
(381, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:47', '2026-03-05 15:13:47'),
(382, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:47', '2026-03-05 15:13:47'),
(383, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:47', '2026-03-05 15:13:47'),
(384, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:47', '2026-03-05 15:13:47'),
(385, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:49', '2026-03-05 15:13:49'),
(386, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:49', '2026-03-05 15:13:49'),
(387, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:51', '2026-03-05 15:13:51'),
(388, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:52', '2026-03-05 15:13:52'),
(389, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:53', '2026-03-05 15:13:53'),
(390, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:55', '2026-03-05 15:13:55'),
(391, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:56', '2026-03-05 15:13:56'),
(392, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:58', '2026-03-05 15:13:58'),
(393, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:13:58', '2026-03-05 15:13:58'),
(394, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:00', '2026-03-05 15:14:00'),
(395, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:05', '2026-03-05 15:14:05'),
(396, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:05', '2026-03-05 15:14:05'),
(397, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:06', '2026-03-05 15:14:06'),
(398, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:08', '2026-03-05 15:14:08'),
(399, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:09', '2026-03-05 15:14:09'),
(400, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:09', '2026-03-05 15:14:09'),
(401, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:10', '2026-03-05 15:14:10'),
(402, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:10', '2026-03-05 15:14:10'),
(403, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:10', '2026-03-05 15:14:10'),
(404, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:11', '2026-03-05 15:14:11'),
(405, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:11', '2026-03-05 15:14:11'),
(406, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:11', '2026-03-05 15:14:11'),
(407, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:12', '2026-03-05 15:14:12'),
(408, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:12', '2026-03-05 15:14:12'),
(409, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:13', '2026-03-05 15:14:13'),
(410, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:13', '2026-03-05 15:14:13'),
(411, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:14', '2026-03-05 15:14:14'),
(412, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:15', '2026-03-05 15:14:15'),
(413, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:15', '2026-03-05 15:14:15'),
(414, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:16', '2026-03-05 15:14:16'),
(415, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:16', '2026-03-05 15:14:16'),
(416, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:17', '2026-03-05 15:14:17'),
(417, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:14:18', '2026-03-05 15:14:18'),
(418, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:26', '2026-03-05 15:15:26'),
(419, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:36', '2026-03-05 15:15:36'),
(420, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:40', '2026-03-05 15:15:40'),
(421, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:44', '2026-03-05 15:15:44'),
(422, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:53', '2026-03-05 15:15:53'),
(423, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:15:57', '2026-03-05 15:15:57'),
(424, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:17', '2026-03-05 15:16:17'),
(425, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:23', '2026-03-05 15:16:23'),
(426, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:23', '2026-03-05 15:16:23'),
(427, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:24', '2026-03-05 15:16:24'),
(428, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:25', '2026-03-05 15:16:25'),
(429, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:26', '2026-03-05 15:16:26'),
(430, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:26', '2026-03-05 15:16:26'),
(431, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:26', '2026-03-05 15:16:26'),
(432, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:27', '2026-03-05 15:16:27'),
(433, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:29', '2026-03-05 15:16:29'),
(434, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:30', '2026-03-05 15:16:30'),
(435, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:31', '2026-03-05 15:16:31'),
(436, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:32', '2026-03-05 15:16:32'),
(437, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:34', '2026-03-05 15:16:34'),
(438, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:35', '2026-03-05 15:16:35'),
(439, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:37', '2026-03-05 15:16:37'),
(440, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:37', '2026-03-05 15:16:37'),
(441, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:38', '2026-03-05 15:16:38'),
(442, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:39', '2026-03-05 15:16:39'),
(443, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:43', '2026-03-05 15:16:43'),
(444, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:45', '2026-03-05 15:16:45'),
(445, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:47', '2026-03-05 15:16:47'),
(446, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:48', '2026-03-05 15:16:48'),
(447, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:49', '2026-03-05 15:16:49'),
(448, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:49', '2026-03-05 15:16:49'),
(449, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:51', '2026-03-05 15:16:51'),
(450, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:52', '2026-03-05 15:16:52'),
(451, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:55', '2026-03-05 15:16:55'),
(452, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:16:58', '2026-03-05 15:16:58'),
(453, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:17:00', '2026-03-05 15:17:00'),
(454, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:17:05', '2026-03-05 15:17:05'),
(455, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:17:20', '2026-03-05 15:17:20'),
(456, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:17:29', '2026-03-05 15:17:29'),
(457, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:17:49', '2026-03-05 15:17:49'),
(458, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:29', '2026-03-05 15:24:29'),
(459, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:29', '2026-03-05 15:24:29'),
(460, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:30', '2026-03-05 15:24:30'),
(461, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:32', '2026-03-05 15:24:32'),
(462, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:32', '2026-03-05 15:24:32'),
(463, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:37', '2026-03-05 15:24:37'),
(464, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:38', '2026-03-05 15:24:38'),
(465, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:38', '2026-03-05 15:24:38'),
(466, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:39', '2026-03-05 15:24:39'),
(467, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:39', '2026-03-05 15:24:39'),
(468, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:40', '2026-03-05 15:24:40'),
(469, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:40', '2026-03-05 15:24:40'),
(470, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:40', '2026-03-05 15:24:40'),
(471, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:41', '2026-03-05 15:24:41'),
(472, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:41', '2026-03-05 15:24:41'),
(473, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:41', '2026-03-05 15:24:41'),
(474, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:41', '2026-03-05 15:24:41'),
(475, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:41', '2026-03-05 15:24:41'),
(476, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:43', '2026-03-05 15:24:43'),
(477, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:43', '2026-03-05 15:24:43'),
(478, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:44', '2026-03-05 15:24:44'),
(479, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:47', '2026-03-05 15:24:47'),
(480, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:48', '2026-03-05 15:24:48'),
(481, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:49', '2026-03-05 15:24:49'),
(482, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:50', '2026-03-05 15:24:50'),
(483, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:50', '2026-03-05 15:24:50'),
(484, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:52', '2026-03-05 15:24:52'),
(485, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:52', '2026-03-05 15:24:52'),
(486, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:24:53', '2026-03-05 15:24:53'),
(487, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:00', '2026-03-05 15:25:00'),
(488, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:00', '2026-03-05 15:25:00'),
(489, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:06', '2026-03-05 15:25:06'),
(490, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:06', '2026-03-05 15:25:06'),
(491, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:31', '2026-03-05 15:25:31'),
(492, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:32', '2026-03-05 15:25:32'),
(493, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:35', '2026-03-05 15:25:35'),
(494, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:37', '2026-03-05 15:25:37'),
(495, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:38', '2026-03-05 15:25:38'),
(496, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:39', '2026-03-05 15:25:39'),
(497, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:40', '2026-03-05 15:25:40'),
(498, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:42', '2026-03-05 15:25:42'),
(499, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:44', '2026-03-05 15:25:44'),
(500, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:44', '2026-03-05 15:25:44'),
(501, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:45', '2026-03-05 15:25:45'),
(502, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:45', '2026-03-05 15:25:45'),
(503, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:45', '2026-03-05 15:25:45'),
(504, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:46', '2026-03-05 15:25:46'),
(505, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:46', '2026-03-05 15:25:46'),
(506, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:48', '2026-03-05 15:25:48'),
(507, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:48', '2026-03-05 15:25:48'),
(508, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:52', '2026-03-05 15:25:52'),
(509, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:25:53', '2026-03-05 15:25:53'),
(510, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:26:42', '2026-03-05 15:26:42'),
(511, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:48', '2026-03-05 15:27:48'),
(512, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:49', '2026-03-05 15:27:49'),
(513, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:51', '2026-03-05 15:27:51'),
(514, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:52', '2026-03-05 15:27:52'),
(515, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:52', '2026-03-05 15:27:52'),
(516, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:53', '2026-03-05 15:27:53'),
(517, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:55', '2026-03-05 15:27:55'),
(518, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:55', '2026-03-05 15:27:55'),
(519, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:58', '2026-03-05 15:27:58'),
(520, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:59', '2026-03-05 15:27:59'),
(521, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:59', '2026-03-05 15:27:59'),
(522, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:27:59', '2026-03-05 15:27:59'),
(523, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:00', '2026-03-05 15:28:00'),
(524, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:02', '2026-03-05 15:28:02'),
(525, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:03', '2026-03-05 15:28:03'),
(526, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:04', '2026-03-05 15:28:04'),
(527, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:04', '2026-03-05 15:28:04'),
(528, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:05', '2026-03-05 15:28:05'),
(529, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:07', '2026-03-05 15:28:07'),
(530, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:08', '2026-03-05 15:28:08'),
(531, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:10', '2026-03-05 15:28:10'),
(532, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:11', '2026-03-05 15:28:11'),
(533, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:14', '2026-03-05 15:28:14'),
(534, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:18', '2026-03-05 15:28:18'),
(535, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:21', '2026-03-05 15:28:21'),
(536, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:22', '2026-03-05 15:28:22'),
(537, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:24', '2026-03-05 15:28:24'),
(538, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:24', '2026-03-05 15:28:24'),
(539, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:24', '2026-03-05 15:28:24'),
(540, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:25', '2026-03-05 15:28:25'),
(541, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:25', '2026-03-05 15:28:25'),
(542, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:28', '2026-03-05 15:28:28'),
(543, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:28', '2026-03-05 15:28:28'),
(544, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:28', '2026-03-05 15:28:28'),
(545, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:34', '2026-03-05 15:28:34'),
(546, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:35', '2026-03-05 15:28:35'),
(547, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:35', '2026-03-05 15:28:35'),
(548, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:35', '2026-03-05 15:28:35'),
(549, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:35', '2026-03-05 15:28:35'),
(550, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:36', '2026-03-05 15:28:36'),
(551, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:36', '2026-03-05 15:28:36'),
(552, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:37', '2026-03-05 15:28:37'),
(553, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:44', '2026-03-05 15:28:44'),
(554, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:47', '2026-03-05 15:28:47'),
(555, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:54', '2026-03-05 15:28:54'),
(556, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:58', '2026-03-05 15:28:58'),
(557, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:59', '2026-03-05 15:28:59'),
(558, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:59', '2026-03-05 15:28:59'),
(559, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:28:59', '2026-03-05 15:28:59'),
(560, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:00', '2026-03-05 15:29:00'),
(561, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:00', '2026-03-05 15:29:00'),
(562, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:00', '2026-03-05 15:29:00'),
(563, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:00', '2026-03-05 15:29:00'),
(564, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:07', '2026-03-05 15:29:07'),
(565, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:29:47', '2026-03-05 15:29:47'),
(566, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:40', '2026-03-05 15:30:40'),
(567, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:40', '2026-03-05 15:30:40'),
(568, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:41', '2026-03-05 15:30:41'),
(569, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:41', '2026-03-05 15:30:41'),
(570, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:43', '2026-03-05 15:30:43'),
(571, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:43', '2026-03-05 15:30:43'),
(572, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:44', '2026-03-05 15:30:44'),
(573, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:44', '2026-03-05 15:30:44'),
(574, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:44', '2026-03-05 15:30:44'),
(575, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:46', '2026-03-05 15:30:46'),
(576, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:46', '2026-03-05 15:30:46'),
(577, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:47', '2026-03-05 15:30:47'),
(578, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:47', '2026-03-05 15:30:47'),
(579, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:47', '2026-03-05 15:30:47'),
(580, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:47', '2026-03-05 15:30:47'),
(581, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:48', '2026-03-05 15:30:48'),
(582, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:48', '2026-03-05 15:30:48'),
(583, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:48', '2026-03-05 15:30:48'),
(584, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:52', '2026-03-05 15:30:52'),
(585, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:54', '2026-03-05 15:30:54'),
(586, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:54', '2026-03-05 15:30:54'),
(587, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:54', '2026-03-05 15:30:54'),
(588, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:55', '2026-03-05 15:30:55'),
(589, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:56', '2026-03-05 15:30:56'),
(590, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:56', '2026-03-05 15:30:56'),
(591, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:56', '2026-03-05 15:30:56'),
(592, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:56', '2026-03-05 15:30:56'),
(593, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:56', '2026-03-05 15:30:56'),
(594, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:58', '2026-03-05 15:30:58'),
(595, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:30:58', '2026-03-05 15:30:58'),
(596, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:00', '2026-03-05 15:31:00'),
(597, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:01', '2026-03-05 15:31:01'),
(598, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:01', '2026-03-05 15:31:01'),
(599, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:01', '2026-03-05 15:31:01'),
(600, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:02', '2026-03-05 15:31:02'),
(601, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:05', '2026-03-05 15:31:05'),
(602, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:06', '2026-03-05 15:31:06'),
(603, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:08', '2026-03-05 15:31:08'),
(604, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:08', '2026-03-05 15:31:08'),
(605, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:08', '2026-03-05 15:31:08'),
(606, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:09', '2026-03-05 15:31:09'),
(607, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:15', '2026-03-05 15:31:15'),
(608, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:18', '2026-03-05 15:31:18'),
(609, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:19', '2026-03-05 15:31:19'),
(610, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:21', '2026-03-05 15:31:21'),
(611, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:21', '2026-03-05 15:31:21'),
(612, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:21', '2026-03-05 15:31:21'),
(613, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:22', '2026-03-05 15:31:22'),
(614, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:22', '2026-03-05 15:31:22'),
(615, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:26', '2026-03-05 15:31:26'),
(616, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:28', '2026-03-05 15:31:28'),
(617, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:30', '2026-03-05 15:31:30'),
(618, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:31', '2026-03-05 15:31:31'),
(619, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:32', '2026-03-05 15:31:32'),
(620, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:32', '2026-03-05 15:31:32'),
(621, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:32', '2026-03-05 15:31:32'),
(622, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:52', '2026-03-05 15:31:52'),
(623, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:54', '2026-03-05 15:31:54'),
(624, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:55', '2026-03-05 15:31:55'),
(625, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:31:57', '2026-03-05 15:31:57'),
(626, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:32:00', '2026-03-05 15:32:00'),
(627, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:32:01', '2026-03-05 15:32:01'),
(628, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:32:02', '2026-03-05 15:32:02'),
(629, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:32:02', '2026-03-05 15:32:02'),
(630, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:32:03', '2026-03-05 15:32:03'),
(631, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:35:52', '2026-03-05 15:35:52'),
(632, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:35:53', '2026-03-05 15:35:53'),
(633, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:36:06', '2026-03-05 15:36:06'),
(634, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:36:06', '2026-03-05 15:36:06'),
(635, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:36:13', '2026-03-05 15:36:13'),
(636, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:36:20', '2026-03-05 15:36:20'),
(637, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:39:43', '2026-03-05 15:39:43'),
(638, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:39:43', '2026-03-05 15:39:43'),
(639, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:39:43', '2026-03-05 15:39:43'),
(640, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:39:44', '2026-03-05 15:39:44'),
(641, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:39:44', '2026-03-05 15:39:44'),
(642, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:18', '2026-03-05 15:40:18'),
(643, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:20', '2026-03-05 15:40:20'),
(644, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:22', '2026-03-05 15:40:22'),
(645, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:23', '2026-03-05 15:40:23'),
(646, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:24', '2026-03-05 15:40:24'),
(647, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:24', '2026-03-05 15:40:24'),
(648, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:24', '2026-03-05 15:40:24'),
(649, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:26', '2026-03-05 15:40:26'),
(650, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:28', '2026-03-05 15:40:28'),
(651, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:30', '2026-03-05 15:40:30'),
(652, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:32', '2026-03-05 15:40:32'),
(653, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:32', '2026-03-05 15:40:32'),
(654, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:33', '2026-03-05 15:40:33'),
(655, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:33', '2026-03-05 15:40:33'),
(656, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:33', '2026-03-05 15:40:33'),
(657, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:33', '2026-03-05 15:40:33'),
(658, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:33', '2026-03-05 15:40:33'),
(659, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:34', '2026-03-05 15:40:34'),
(660, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:37', '2026-03-05 15:40:37'),
(661, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:41', '2026-03-05 15:40:41'),
(662, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:45', '2026-03-05 15:40:45'),
(663, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:49', '2026-03-05 15:40:49'),
(664, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:40:55', '2026-03-05 15:40:55'),
(665, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:02', '2026-03-05 15:41:02'),
(666, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:03', '2026-03-05 15:41:03'),
(667, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:03', '2026-03-05 15:41:03'),
(668, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:06', '2026-03-05 15:41:06'),
(669, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:07', '2026-03-05 15:41:07'),
(670, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:10', '2026-03-05 15:41:10'),
(671, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:11', '2026-03-05 15:41:11'),
(672, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:14', '2026-03-05 15:41:14'),
(673, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:14', '2026-03-05 15:41:14'),
(674, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:21', '2026-03-05 15:41:21'),
(675, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:21', '2026-03-05 15:41:21'),
(676, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:25', '2026-03-05 15:41:25'),
(677, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:25', '2026-03-05 15:41:25'),
(678, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:41:26', '2026-03-05 15:41:26'),
(679, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:42:31', '2026-03-05 15:42:31'),
(680, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:42:32', '2026-03-05 15:42:32'),
(681, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:42:32', '2026-03-05 15:42:32'),
(682, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:43:53', '2026-03-05 15:43:53'),
(683, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:43:54', '2026-03-05 15:43:54'),
(684, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:28', '2026-03-05 15:48:28'),
(685, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:28', '2026-03-05 15:48:28'),
(686, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:28', '2026-03-05 15:48:28'),
(687, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:29', '2026-03-05 15:48:29'),
(688, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:29', '2026-03-05 15:48:29'),
(689, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:29', '2026-03-05 15:48:29'),
(690, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:48:29', '2026-03-05 15:48:29'),
(691, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:51:53', '2026-03-05 15:51:53'),
(692, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:51:53', '2026-03-05 15:51:53'),
(693, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:51:53', '2026-03-05 15:51:53'),
(694, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:51:53', '2026-03-05 15:51:53'),
(695, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:08', '2026-03-05 15:52:08'),
(696, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:08', '2026-03-05 15:52:08'),
(697, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:15', '2026-03-05 15:52:15'),
(698, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:20', '2026-03-05 15:52:20'),
(699, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:21', '2026-03-05 15:52:21'),
(700, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:24', '2026-03-05 15:52:24'),
(701, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:24', '2026-03-05 15:52:24'),
(702, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:27', '2026-03-05 15:52:27'),
(703, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:28', '2026-03-05 15:52:28'),
(704, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:29', '2026-03-05 15:52:29'),
(705, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:30', '2026-03-05 15:52:30'),
(706, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:32', '2026-03-05 15:52:32'),
(707, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:38', '2026-03-05 15:52:38'),
(708, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:39', '2026-03-05 15:52:39'),
(709, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:39', '2026-03-05 15:52:39'),
(710, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:40', '2026-03-05 15:52:40'),
(711, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:43', '2026-03-05 15:52:43'),
(712, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:43', '2026-03-05 15:52:43'),
(713, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:43', '2026-03-05 15:52:43'),
(714, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:44', '2026-03-05 15:52:44'),
(715, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:48', '2026-03-05 15:52:48'),
(716, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:49', '2026-03-05 15:52:49'),
(717, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:49', '2026-03-05 15:52:49'),
(718, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:49', '2026-03-05 15:52:49'),
(719, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:50', '2026-03-05 15:52:50'),
(720, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:50', '2026-03-05 15:52:50'),
(721, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:50', '2026-03-05 15:52:50'),
(722, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:51', '2026-03-05 15:52:51'),
(723, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:52', '2026-03-05 15:52:52'),
(724, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:52:53', '2026-03-05 15:52:53'),
(725, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:04', '2026-03-05 15:53:04'),
(726, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:04', '2026-03-05 15:53:04'),
(727, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:05', '2026-03-05 15:53:05'),
(728, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:05', '2026-03-05 15:53:05'),
(729, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:09', '2026-03-05 15:53:09'),
(730, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:11', '2026-03-05 15:53:11'),
(731, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:11', '2026-03-05 15:53:11'),
(732, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:12', '2026-03-05 15:53:12'),
(733, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:13', '2026-03-05 15:53:13'),
(734, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:13', '2026-03-05 15:53:13'),
(735, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:14', '2026-03-05 15:53:14'),
(736, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:15', '2026-03-05 15:53:15'),
(737, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:15', '2026-03-05 15:53:15'),
(738, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:33', '2026-03-05 15:53:33'),
(739, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:37', '2026-03-05 15:53:37'),
(740, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:38', '2026-03-05 15:53:38'),
(741, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:38', '2026-03-05 15:53:38'),
(742, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:39', '2026-03-05 15:53:39'),
(743, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:39', '2026-03-05 15:53:39'),
(744, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:40', '2026-03-05 15:53:40'),
(745, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:41', '2026-03-05 15:53:41'),
(746, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:41', '2026-03-05 15:53:41'),
(747, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:41', '2026-03-05 15:53:41'),
(748, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:41', '2026-03-05 15:53:41'),
(749, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:42', '2026-03-05 15:53:42'),
(750, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:43', '2026-03-05 15:53:43'),
(751, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:46', '2026-03-05 15:53:46'),
(752, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:46', '2026-03-05 15:53:46'),
(753, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:46', '2026-03-05 15:53:46'),
(754, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:47', '2026-03-05 15:53:47'),
(755, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:49', '2026-03-05 15:53:49'),
(756, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:50', '2026-03-05 15:53:50'),
(757, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:51', '2026-03-05 15:53:51'),
(758, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:53:51', '2026-03-05 15:53:51'),
(759, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:55:59', '2026-03-05 15:55:59'),
(760, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:55:59', '2026-03-05 15:55:59'),
(761, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:00', '2026-03-05 15:56:00'),
(762, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:00', '2026-03-05 15:56:00'),
(763, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:00', '2026-03-05 15:56:00'),
(764, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:00', '2026-03-05 15:56:00'),
(765, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:01', '2026-03-05 15:56:01'),
(766, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:01', '2026-03-05 15:56:01'),
(767, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:01', '2026-03-05 15:56:01'),
(768, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:01', '2026-03-05 15:56:01'),
(769, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:01', '2026-03-05 15:56:01'),
(770, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(771, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(772, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(773, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(774, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(775, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:02', '2026-03-05 15:56:02'),
(776, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:03', '2026-03-05 15:56:03'),
(777, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:03', '2026-03-05 15:56:03'),
(778, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:05', '2026-03-05 15:56:05'),
(779, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:09', '2026-03-05 15:56:09'),
(780, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:09', '2026-03-05 15:56:09'),
(781, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:10', '2026-03-05 15:56:10'),
(782, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:13', '2026-03-05 15:56:13'),
(783, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:13', '2026-03-05 15:56:13'),
(784, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:14', '2026-03-05 15:56:14'),
(785, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:16', '2026-03-05 15:56:16'),
(786, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:17', '2026-03-05 15:56:17'),
(787, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:18', '2026-03-05 15:56:18'),
(788, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:23', '2026-03-05 15:56:23'),
(789, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:23', '2026-03-05 15:56:23'),
(790, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:27', '2026-03-05 15:56:27'),
(791, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:27', '2026-03-05 15:56:27'),
(792, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:27', '2026-03-05 15:56:27'),
(793, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:28', '2026-03-05 15:56:28'),
(794, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:28', '2026-03-05 15:56:28'),
(795, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:28', '2026-03-05 15:56:28'),
(796, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:28', '2026-03-05 15:56:28'),
(797, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:28', '2026-03-05 15:56:28'),
(798, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:29', '2026-03-05 15:56:29'),
(799, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:30', '2026-03-05 15:56:30'),
(800, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:30', '2026-03-05 15:56:30'),
(801, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:30', '2026-03-05 15:56:30'),
(802, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:31', '2026-03-05 15:56:31'),
(803, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:33', '2026-03-05 15:56:33'),
(804, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:33', '2026-03-05 15:56:33'),
(805, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:34', '2026-03-05 15:56:34'),
(806, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:34', '2026-03-05 15:56:34'),
(807, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:34', '2026-03-05 15:56:34'),
(808, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:34', '2026-03-05 15:56:34'),
(809, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:35', '2026-03-05 15:56:35'),
(810, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:35', '2026-03-05 15:56:35'),
(811, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:35', '2026-03-05 15:56:35'),
(812, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:35', '2026-03-05 15:56:35'),
(813, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:35', '2026-03-05 15:56:35'),
(814, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:37', '2026-03-05 15:56:37'),
(815, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:38', '2026-03-05 15:56:38'),
(816, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:38', '2026-03-05 15:56:38'),
(817, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:40', '2026-03-05 15:56:40'),
(818, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:41', '2026-03-05 15:56:41'),
(819, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:48', '2026-03-05 15:56:48'),
(820, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:48', '2026-03-05 15:56:48'),
(821, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:49', '2026-03-05 15:56:49'),
(822, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:49', '2026-03-05 15:56:49'),
(823, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:49', '2026-03-05 15:56:49'),
(824, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:49', '2026-03-05 15:56:49'),
(825, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:50', '2026-03-05 15:56:50'),
(826, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:55', '2026-03-05 15:56:55'),
(827, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:55', '2026-03-05 15:56:55'),
(828, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:56', '2026-03-05 15:56:56'),
(829, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:56', '2026-03-05 15:56:56'),
(830, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:57', '2026-03-05 15:56:57'),
(831, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:57', '2026-03-05 15:56:57'),
(832, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:57', '2026-03-05 15:56:57'),
(833, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:58', '2026-03-05 15:56:58'),
(834, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:58', '2026-03-05 15:56:58'),
(835, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:59', '2026-03-05 15:56:59'),
(836, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:56:59', '2026-03-05 15:56:59'),
(837, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:00', '2026-03-05 15:57:00'),
(838, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:00', '2026-03-05 15:57:00'),
(839, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:00', '2026-03-05 15:57:00'),
(840, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:01', '2026-03-05 15:57:01'),
(841, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:01', '2026-03-05 15:57:01'),
(842, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:01', '2026-03-05 15:57:01'),
(843, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:02', '2026-03-05 15:57:02'),
(844, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:02', '2026-03-05 15:57:02'),
(845, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:02', '2026-03-05 15:57:02'),
(846, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:03', '2026-03-05 15:57:03'),
(847, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:04', '2026-03-05 15:57:04'),
(848, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:04', '2026-03-05 15:57:04'),
(849, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:05', '2026-03-05 15:57:05'),
(850, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:05', '2026-03-05 15:57:05'),
(851, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:05', '2026-03-05 15:57:05'),
(852, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:05', '2026-03-05 15:57:05'),
(853, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:05', '2026-03-05 15:57:05'),
(854, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:07', '2026-03-05 15:57:07'),
(855, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:07', '2026-03-05 15:57:07'),
(856, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:08', '2026-03-05 15:57:08'),
(857, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:09', '2026-03-05 15:57:09'),
(858, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:09', '2026-03-05 15:57:09'),
(859, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:10', '2026-03-05 15:57:10'),
(860, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:10', '2026-03-05 15:57:10'),
(861, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:11', '2026-03-05 15:57:11'),
(862, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:11', '2026-03-05 15:57:11'),
(863, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:11', '2026-03-05 15:57:11'),
(864, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:11', '2026-03-05 15:57:11'),
(865, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:11', '2026-03-05 15:57:11'),
(866, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:12', '2026-03-05 15:57:12'),
(867, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:12', '2026-03-05 15:57:12'),
(868, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:12', '2026-03-05 15:57:12'),
(869, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:15', '2026-03-05 15:57:15'),
(870, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:16', '2026-03-05 15:57:16'),
(871, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:17', '2026-03-05 15:57:17'),
(872, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:19', '2026-03-05 15:57:19'),
(873, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:20', '2026-03-05 15:57:20'),
(874, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:22', '2026-03-05 15:57:22'),
(875, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:23', '2026-03-05 15:57:23'),
(876, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:24', '2026-03-05 15:57:24'),
(877, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:24', '2026-03-05 15:57:24'),
(878, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:25', '2026-03-05 15:57:25'),
(879, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:25', '2026-03-05 15:57:25'),
(880, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:28', '2026-03-05 15:57:28'),
(881, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:28', '2026-03-05 15:57:28'),
(882, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:32', '2026-03-05 15:57:32'),
(883, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:57:34', '2026-03-05 15:57:34'),
(884, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:58:34', '2026-03-05 15:58:34');
INSERT INTO `site_visits` (`id`, `ip_address`, `user_agent`, `page_url`, `referrer`, `visit_date`, `visit_time`, `created_at`) VALUES
(885, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:58:45', '2026-03-05 15:58:45'),
(886, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:58:46', '2026-03-05 15:58:46'),
(887, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:58:48', '2026-03-05 15:58:48'),
(888, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:58:50', '2026-03-05 15:58:50'),
(889, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '18:59:12', '2026-03-05 15:59:12'),
(890, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '19:02:09', '2026-03-05 16:02:09'),
(891, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '19:02:16', '2026-03-05 16:02:16'),
(892, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '19:07:32', '2026-03-05 16:07:32'),
(893, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:12:51', '2026-03-05 19:12:51'),
(894, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:31:32', '2026-03-05 19:31:32'),
(895, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:31:37', '2026-03-05 19:31:37'),
(896, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:31:38', '2026-03-05 19:31:38'),
(897, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:31:39', '2026-03-05 19:31:39'),
(898, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:31:40', '2026-03-05 19:31:40'),
(899, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:56:33', '2026-03-05 19:56:33'),
(900, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:56:41', '2026-03-05 19:56:41'),
(901, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:56:45', '2026-03-05 19:56:45'),
(902, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:58:42', '2026-03-05 19:58:42'),
(903, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:58:53', '2026-03-05 19:58:53'),
(904, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '22:59:16', '2026-03-05 19:59:16'),
(905, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:12', '2026-03-05 20:01:12'),
(906, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:14', '2026-03-05 20:01:14'),
(907, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:18', '2026-03-05 20:01:18'),
(908, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:24', '2026-03-05 20:01:24'),
(909, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:30', '2026-03-05 20:01:30'),
(910, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:33', '2026-03-05 20:01:33'),
(911, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:36', '2026-03-05 20:01:36'),
(912, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:37', '2026-03-05 20:01:37'),
(913, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:49', '2026-03-05 20:01:49'),
(914, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:51', '2026-03-05 20:01:51'),
(915, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:51', '2026-03-05 20:01:51'),
(916, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:52', '2026-03-05 20:01:52'),
(917, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:52', '2026-03-05 20:01:52'),
(918, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:53', '2026-03-05 20:01:53'),
(919, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:53', '2026-03-05 20:01:53'),
(920, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:54', '2026-03-05 20:01:54'),
(921, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:54', '2026-03-05 20:01:54'),
(922, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:54', '2026-03-05 20:01:54'),
(923, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:55', '2026-03-05 20:01:55'),
(924, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:55', '2026-03-05 20:01:55'),
(925, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:55', '2026-03-05 20:01:55'),
(926, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:55', '2026-03-05 20:01:55'),
(927, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:55', '2026-03-05 20:01:55'),
(928, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:58', '2026-03-05 20:01:58'),
(929, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:01:58', '2026-03-05 20:01:58'),
(930, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:01', '2026-03-05 20:02:01'),
(931, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:02', '2026-03-05 20:02:02'),
(932, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:03', '2026-03-05 20:02:03'),
(933, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:04', '2026-03-05 20:02:04'),
(934, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:09', '2026-03-05 20:02:09'),
(935, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:21', '2026-03-05 20:02:21'),
(936, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:23', '2026-03-05 20:02:23'),
(937, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:25', '2026-03-05 20:02:25'),
(938, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:26', '2026-03-05 20:02:26'),
(939, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:27', '2026-03-05 20:02:27'),
(940, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:28', '2026-03-05 20:02:28'),
(941, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:29', '2026-03-05 20:02:29'),
(942, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:29', '2026-03-05 20:02:29'),
(943, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:30', '2026-03-05 20:02:30'),
(944, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:30', '2026-03-05 20:02:30'),
(945, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:33', '2026-03-05 20:02:33'),
(946, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:35', '2026-03-05 20:02:35'),
(947, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:36', '2026-03-05 20:02:36'),
(948, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:38', '2026-03-05 20:02:38'),
(949, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:02:39', '2026-03-05 20:02:39'),
(950, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:18', '2026-03-05 20:03:18'),
(951, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:20', '2026-03-05 20:03:20'),
(952, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:21', '2026-03-05 20:03:21'),
(953, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:23', '2026-03-05 20:03:23'),
(954, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:24', '2026-03-05 20:03:24'),
(955, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:25', '2026-03-05 20:03:25'),
(956, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:26', '2026-03-05 20:03:26'),
(957, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:27', '2026-03-05 20:03:27'),
(958, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:30', '2026-03-05 20:03:30'),
(959, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:32', '2026-03-05 20:03:32'),
(960, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:33', '2026-03-05 20:03:33'),
(961, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:34', '2026-03-05 20:03:34'),
(962, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:35', '2026-03-05 20:03:35'),
(963, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:35', '2026-03-05 20:03:35'),
(964, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:35', '2026-03-05 20:03:35'),
(965, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:39', '2026-03-05 20:03:39'),
(966, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:41', '2026-03-05 20:03:41'),
(967, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:42', '2026-03-05 20:03:42'),
(968, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:43', '2026-03-05 20:03:43'),
(969, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:45', '2026-03-05 20:03:45'),
(970, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:45', '2026-03-05 20:03:45'),
(971, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:03:48', '2026-03-05 20:03:48'),
(972, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:26', '2026-03-05 20:05:26'),
(973, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:36', '2026-03-05 20:05:36'),
(974, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:40', '2026-03-05 20:05:40'),
(975, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:45', '2026-03-05 20:05:45'),
(976, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:45', '2026-03-05 20:05:45'),
(977, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:47', '2026-03-05 20:05:47'),
(978, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:48', '2026-03-05 20:05:48'),
(979, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:50', '2026-03-05 20:05:50'),
(980, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:51', '2026-03-05 20:05:51'),
(981, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:56', '2026-03-05 20:05:56'),
(982, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:05:57', '2026-03-05 20:05:57'),
(983, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:07:35', '2026-03-05 20:07:35'),
(984, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:08', '2026-03-05 20:08:08'),
(985, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:08', '2026-03-05 20:08:08'),
(986, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:08', '2026-03-05 20:08:08'),
(987, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:09', '2026-03-05 20:08:09'),
(988, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:09', '2026-03-05 20:08:09'),
(989, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:29', '2026-03-05 20:08:29'),
(990, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:34', '2026-03-05 20:08:34'),
(991, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:39', '2026-03-05 20:08:39'),
(992, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:40', '2026-03-05 20:08:40'),
(993, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:44', '2026-03-05 20:08:44'),
(994, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:52', '2026-03-05 20:08:52'),
(995, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:52', '2026-03-05 20:08:52'),
(996, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:52', '2026-03-05 20:08:52'),
(997, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:53', '2026-03-05 20:08:53'),
(998, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:53', '2026-03-05 20:08:53'),
(999, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:55', '2026-03-05 20:08:55'),
(1000, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:57', '2026-03-05 20:08:57'),
(1001, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:08:58', '2026-03-05 20:08:58'),
(1002, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:00', '2026-03-05 20:09:00'),
(1003, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:01', '2026-03-05 20:09:01'),
(1004, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:02', '2026-03-05 20:09:02'),
(1005, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:14', '2026-03-05 20:09:14'),
(1006, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:16', '2026-03-05 20:09:16'),
(1007, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:24', '2026-03-05 20:09:24'),
(1008, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:30', '2026-03-05 20:09:30'),
(1009, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:33', '2026-03-05 20:09:33'),
(1010, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:35', '2026-03-05 20:09:35'),
(1011, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:36', '2026-03-05 20:09:36'),
(1012, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:38', '2026-03-05 20:09:38'),
(1013, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:09:39', '2026-03-05 20:09:39'),
(1014, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:34', '2026-03-05 20:13:34'),
(1015, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:35', '2026-03-05 20:13:35'),
(1016, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:35', '2026-03-05 20:13:35'),
(1017, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:35', '2026-03-05 20:13:35'),
(1018, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:35', '2026-03-05 20:13:35'),
(1019, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:35', '2026-03-05 20:13:35'),
(1020, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:36', '2026-03-05 20:13:36'),
(1021, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:36', '2026-03-05 20:13:36'),
(1022, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:36', '2026-03-05 20:13:36'),
(1023, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:37', '2026-03-05 20:13:37'),
(1024, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:37', '2026-03-05 20:13:37'),
(1025, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:37', '2026-03-05 20:13:37'),
(1026, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:13:37', '2026-03-05 20:13:37'),
(1027, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:13', '2026-03-05 20:14:13'),
(1028, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:16', '2026-03-05 20:14:16'),
(1029, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:17', '2026-03-05 20:14:17'),
(1030, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:21', '2026-03-05 20:14:21'),
(1031, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:27', '2026-03-05 20:14:27'),
(1032, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:29', '2026-03-05 20:14:29'),
(1033, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:32', '2026-03-05 20:14:32'),
(1034, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:35', '2026-03-05 20:14:35'),
(1035, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:14:37', '2026-03-05 20:14:37'),
(1036, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:20', '2026-03-05 20:15:20'),
(1037, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:21', '2026-03-05 20:15:21'),
(1038, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:21', '2026-03-05 20:15:21'),
(1039, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:26', '2026-03-05 20:15:26'),
(1040, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:36', '2026-03-05 20:15:36'),
(1041, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:37', '2026-03-05 20:15:37'),
(1042, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:38', '2026-03-05 20:15:38'),
(1043, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:39', '2026-03-05 20:15:39'),
(1044, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:40', '2026-03-05 20:15:40'),
(1045, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:40', '2026-03-05 20:15:40'),
(1046, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:40', '2026-03-05 20:15:40'),
(1047, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:41', '2026-03-05 20:15:41'),
(1048, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:41', '2026-03-05 20:15:41'),
(1049, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:41', '2026-03-05 20:15:41'),
(1050, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:41', '2026-03-05 20:15:41'),
(1051, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1052, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1053, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1054, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1055, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1056, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:15:42', '2026-03-05 20:15:42'),
(1057, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:16:02', '2026-03-05 20:16:02'),
(1058, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:16:04', '2026-03-05 20:16:04'),
(1059, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:16:04', '2026-03-05 20:16:04'),
(1060, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:16:05', '2026-03-05 20:16:05'),
(1061, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:16:55', '2026-03-05 20:16:55'),
(1062, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:17', '2026-03-05 20:21:17'),
(1063, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:18', '2026-03-05 20:21:18'),
(1064, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:18', '2026-03-05 20:21:18'),
(1065, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:18', '2026-03-05 20:21:18'),
(1066, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:18', '2026-03-05 20:21:18'),
(1067, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:18', '2026-03-05 20:21:18'),
(1068, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:19', '2026-03-05 20:21:19'),
(1069, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:27', '2026-03-05 20:21:27'),
(1070, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:27', '2026-03-05 20:21:27'),
(1071, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:27', '2026-03-05 20:21:27'),
(1072, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:27', '2026-03-05 20:21:27'),
(1073, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:21:27', '2026-03-05 20:21:27'),
(1074, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:22:03', '2026-03-05 20:22:03'),
(1075, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:22:07', '2026-03-05 20:22:07'),
(1076, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:22:28', '2026-03-05 20:22:28'),
(1077, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:22:35', '2026-03-05 20:22:35'),
(1078, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:22:37', '2026-03-05 20:22:37'),
(1079, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:52', '2026-03-05 20:25:52'),
(1080, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:53', '2026-03-05 20:25:53'),
(1081, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:53', '2026-03-05 20:25:53'),
(1082, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:53', '2026-03-05 20:25:53'),
(1083, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:53', '2026-03-05 20:25:53'),
(1084, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:53', '2026-03-05 20:25:53'),
(1085, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:55', '2026-03-05 20:25:55'),
(1086, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:55', '2026-03-05 20:25:55'),
(1087, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:55', '2026-03-05 20:25:55'),
(1088, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:55', '2026-03-05 20:25:55'),
(1089, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:56', '2026-03-05 20:25:56'),
(1090, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:25:56', '2026-03-05 20:25:56'),
(1091, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:04', '2026-03-05 20:28:04'),
(1092, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:04', '2026-03-05 20:28:04'),
(1093, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:05', '2026-03-05 20:28:05'),
(1094, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:05', '2026-03-05 20:28:05'),
(1095, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:05', '2026-03-05 20:28:05'),
(1096, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:05', '2026-03-05 20:28:05'),
(1097, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:05', '2026-03-05 20:28:05'),
(1098, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:06', '2026-03-05 20:28:06'),
(1099, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:09', '2026-03-05 20:28:09'),
(1100, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:10', '2026-03-05 20:28:10'),
(1101, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:14', '2026-03-05 20:28:14'),
(1102, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:15', '2026-03-05 20:28:15'),
(1103, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:18', '2026-03-05 20:28:18'),
(1104, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:25', '2026-03-05 20:28:25'),
(1105, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:28', '2026-03-05 20:28:28'),
(1106, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:28', '2026-03-05 20:28:28'),
(1107, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:28:30', '2026-03-05 20:28:30'),
(1108, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:39:26', '2026-03-05 20:39:26'),
(1109, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:39:27', '2026-03-05 20:39:27'),
(1110, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:39:33', '2026-03-05 20:39:33'),
(1111, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:39:33', '2026-03-05 20:39:33'),
(1112, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:22', '2026-03-05 20:42:22'),
(1113, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:24', '2026-03-05 20:42:24'),
(1114, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:34', '2026-03-05 20:42:34'),
(1115, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:35', '2026-03-05 20:42:35'),
(1116, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:35', '2026-03-05 20:42:35'),
(1117, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:36', '2026-03-05 20:42:36'),
(1118, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:42', '2026-03-05 20:42:42'),
(1119, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:42', '2026-03-05 20:42:42'),
(1120, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:51', '2026-03-05 20:42:51'),
(1121, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:42:55', '2026-03-05 20:42:55'),
(1122, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:43:06', '2026-03-05 20:43:06'),
(1123, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:43:06', '2026-03-05 20:43:06'),
(1124, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:44:47', '2026-03-05 20:44:47'),
(1125, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:46:51', '2026-03-05 20:46:51'),
(1126, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:55', '2026-03-05 20:51:55'),
(1127, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:55', '2026-03-05 20:51:55'),
(1128, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:56', '2026-03-05 20:51:56'),
(1129, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:56', '2026-03-05 20:51:56'),
(1130, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:56', '2026-03-05 20:51:56'),
(1131, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:57', '2026-03-05 20:51:57'),
(1132, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:51:57', '2026-03-05 20:51:57'),
(1133, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:52:06', '2026-03-05 20:52:06'),
(1134, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:52:07', '2026-03-05 20:52:07'),
(1135, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:52:36', '2026-03-05 20:52:36'),
(1136, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:52:50', '2026-03-05 20:52:50'),
(1137, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:52:54', '2026-03-05 20:52:54'),
(1138, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:53:13', '2026-03-05 20:53:13'),
(1139, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:53:29', '2026-03-05 20:53:29'),
(1140, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:56:08', '2026-03-05 20:56:08'),
(1141, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:57:45', '2026-03-05 20:57:45'),
(1142, '127.0.0.1', NULL, NULL, NULL, '2026-03-05', '23:57:46', '2026-03-05 20:57:46'),
(1143, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:14', '2026-03-05 21:00:14'),
(1144, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:16', '2026-03-05 21:00:16'),
(1145, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:16', '2026-03-05 21:00:16'),
(1146, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:24', '2026-03-05 21:00:24'),
(1147, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:24', '2026-03-05 21:00:24'),
(1148, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:24', '2026-03-05 21:00:24'),
(1149, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:25', '2026-03-05 21:00:25'),
(1150, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:25', '2026-03-05 21:00:25'),
(1151, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:25', '2026-03-05 21:00:25'),
(1152, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:25', '2026-03-05 21:00:25'),
(1153, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:25', '2026-03-05 21:00:25'),
(1154, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:41', '2026-03-05 21:00:41'),
(1155, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:45', '2026-03-05 21:00:45'),
(1156, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '00:00:52', '2026-03-05 21:00:52'),
(1157, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:41:02', '2026-03-06 19:41:02'),
(1158, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:41:09', '2026-03-06 19:41:09'),
(1159, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:41:53', '2026-03-06 19:41:53'),
(1160, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:42:07', '2026-03-06 19:42:07'),
(1161, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:42:20', '2026-03-06 19:42:20'),
(1162, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:44:55', '2026-03-06 19:44:55'),
(1163, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:44:57', '2026-03-06 19:44:57'),
(1164, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:00', '2026-03-06 19:45:00'),
(1165, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:01', '2026-03-06 19:45:01'),
(1166, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:05', '2026-03-06 19:45:05'),
(1167, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:09', '2026-03-06 19:45:09'),
(1168, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:11', '2026-03-06 19:45:11'),
(1169, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:15', '2026-03-06 19:45:15'),
(1170, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:16', '2026-03-06 19:45:16'),
(1171, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:23', '2026-03-06 19:45:23'),
(1172, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:26', '2026-03-06 19:45:26'),
(1173, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:45:27', '2026-03-06 19:45:27'),
(1174, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:46:41', '2026-03-06 19:46:41'),
(1175, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:46:42', '2026-03-06 19:46:42'),
(1176, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:46:42', '2026-03-06 19:46:42'),
(1177, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:46:42', '2026-03-06 19:46:42'),
(1178, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:46:42', '2026-03-06 19:46:42'),
(1179, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:47:01', '2026-03-06 19:47:01'),
(1180, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:47:02', '2026-03-06 19:47:02'),
(1181, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:47:04', '2026-03-06 19:47:04'),
(1182, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:47:12', '2026-03-06 19:47:12'),
(1183, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '22:48:04', '2026-03-06 19:48:04'),
(1184, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:47', '2026-03-06 20:17:47'),
(1185, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:47', '2026-03-06 20:17:47'),
(1186, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:47', '2026-03-06 20:17:47'),
(1187, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:47', '2026-03-06 20:17:47'),
(1188, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1189, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1190, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1191, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1192, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1193, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1194, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:48', '2026-03-06 20:17:48'),
(1195, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1196, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1197, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1198, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1199, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1200, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1201, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:49', '2026-03-06 20:17:49'),
(1202, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:50', '2026-03-06 20:17:50'),
(1203, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:55', '2026-03-06 20:17:55'),
(1204, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:55', '2026-03-06 20:17:55'),
(1205, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:55', '2026-03-06 20:17:55'),
(1206, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:55', '2026-03-06 20:17:55'),
(1207, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:55', '2026-03-06 20:17:55'),
(1208, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1209, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1210, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1211, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1212, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1213, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1214, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:56', '2026-03-06 20:17:56'),
(1215, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:57', '2026-03-06 20:17:57'),
(1216, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:57', '2026-03-06 20:17:57'),
(1217, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:57', '2026-03-06 20:17:57'),
(1218, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:57', '2026-03-06 20:17:57'),
(1219, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:57', '2026-03-06 20:17:57'),
(1220, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:58', '2026-03-06 20:17:58'),
(1221, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:58', '2026-03-06 20:17:58'),
(1222, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:58', '2026-03-06 20:17:58'),
(1223, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1224, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1225, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1226, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1227, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1228, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1229, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:17:59', '2026-03-06 20:17:59'),
(1230, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:06', '2026-03-06 20:18:06'),
(1231, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:15', '2026-03-06 20:18:15'),
(1232, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:18', '2026-03-06 20:18:18'),
(1233, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:21', '2026-03-06 20:18:21'),
(1234, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:24', '2026-03-06 20:18:24'),
(1235, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:29', '2026-03-06 20:18:29'),
(1236, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:32', '2026-03-06 20:18:32'),
(1237, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:33', '2026-03-06 20:18:33'),
(1238, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:37', '2026-03-06 20:18:37'),
(1239, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:37', '2026-03-06 20:18:37'),
(1240, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:40', '2026-03-06 20:18:40'),
(1241, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:41', '2026-03-06 20:18:41'),
(1242, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:18:43', '2026-03-06 20:18:43'),
(1243, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:19:15', '2026-03-06 20:19:15'),
(1244, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:19:29', '2026-03-06 20:19:29'),
(1245, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:19:50', '2026-03-06 20:19:50'),
(1246, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:23:51', '2026-03-06 20:23:51'),
(1247, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:23:53', '2026-03-06 20:23:53'),
(1248, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:23:53', '2026-03-06 20:23:53'),
(1249, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:23:53', '2026-03-06 20:23:53'),
(1250, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:23:53', '2026-03-06 20:23:53'),
(1251, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:25:58', '2026-03-06 20:25:58'),
(1252, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:25:58', '2026-03-06 20:25:58'),
(1253, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:25:58', '2026-03-06 20:25:58'),
(1254, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:25:58', '2026-03-06 20:25:58'),
(1255, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:04', '2026-03-06 20:28:04'),
(1256, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:04', '2026-03-06 20:28:04'),
(1257, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:04', '2026-03-06 20:28:04'),
(1258, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:05', '2026-03-06 20:28:05'),
(1259, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:05', '2026-03-06 20:28:05'),
(1260, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:12', '2026-03-06 20:28:12'),
(1261, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:14', '2026-03-06 20:28:14'),
(1262, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:17', '2026-03-06 20:28:17'),
(1263, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:20', '2026-03-06 20:28:20'),
(1264, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:28:35', '2026-03-06 20:28:35'),
(1265, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:17', '2026-03-06 20:29:17'),
(1266, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:33', '2026-03-06 20:29:33'),
(1267, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:47', '2026-03-06 20:29:47'),
(1268, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:56', '2026-03-06 20:29:56'),
(1269, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:56', '2026-03-06 20:29:56'),
(1270, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:29:56', '2026-03-06 20:29:56'),
(1271, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:58:32', '2026-03-06 20:58:32'),
(1272, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:58:33', '2026-03-06 20:58:33'),
(1273, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:59:08', '2026-03-06 20:59:08'),
(1274, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:59:14', '2026-03-06 20:59:14'),
(1275, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:59:15', '2026-03-06 20:59:15'),
(1276, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:59:18', '2026-03-06 20:59:18'),
(1277, '127.0.0.1', NULL, NULL, NULL, '2026-03-06', '23:59:19', '2026-03-06 20:59:19'),
(1278, '127.0.0.1', NULL, NULL, NULL, '2026-03-07', '00:07:35', '2026-03-06 21:07:35'),
(1279, '127.0.0.1', NULL, NULL, NULL, '2026-03-07', '00:07:39', '2026-03-06 21:07:39'),
(1280, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/profile.php', '2026-03-07', '00:17:21', '2026-03-06 21:17:21'),
(1281, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin_profile.php', '2026-03-07', '00:18:26', '2026-03-06 21:18:26'),
(1282, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2026-03-07', '00:18:30', '2026-03-06 21:18:30'),
(1283, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/profile.php', '2026-03-07', '00:18:53', '2026-03-06 21:18:53'),
(1284, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/products.php', '2026-03-07', '00:18:53', '2026-03-06 21:18:53'),
(1285, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/products.php', '2026-03-07', '00:19:21', '2026-03-06 21:19:21'),
(1286, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/profile.php', '2026-03-07', '00:19:29', '2026-03-06 21:19:29'),
(1287, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin/orders.php', '2026-03-07', '00:19:52', '2026-03-06 21:19:52'),
(1288, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2026-03-07', '00:19:58', '2026-03-06 21:19:58'),
(1289, '127.0.0.1', NULL, '/mini_shop/products.php?search=zen', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:20:03', '2026-03-06 21:20:03'),
(1290, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/products.php?search=zen', '2026-03-07', '00:20:04', '2026-03-06 21:20:04'),
(1291, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', '2026-03-07', '00:20:18', '2026-03-06 21:20:18'),
(1292, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', '2026-03-07', '00:20:18', '2026-03-06 21:20:18'),
(1293, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', '2026-03-07', '00:20:24', '2026-03-06 21:20:24'),
(1294, '127.0.0.1', NULL, '/mini_shop/products.php?search=zeno', 'http://localhost/mini_shop/admin/comments.php', '2026-03-07', '00:20:45', '2026-03-06 21:20:45'),
(1295, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/products.php?search=zeno', '2026-03-07', '00:20:46', '2026-03-06 21:20:46'),
(1296, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin_profile.php', '2026-03-07', '00:23:40', '2026-03-06 21:23:40'),
(1297, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2026-03-07', '00:23:44', '2026-03-06 21:23:44'),
(1298, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/profile.php', '2026-03-07', '00:24:52', '2026-03-06 21:24:52'),
(1299, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:24:54', '2026-03-06 21:24:54'),
(1300, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:24:55', '2026-03-06 21:24:55'),
(1301, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin_profile.php', '2026-03-07', '00:46:17', '2026-03-06 21:46:17'),
(1302, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:46:17', '2026-03-06 21:46:17'),
(1303, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:46:17', '2026-03-06 21:46:17'),
(1304, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '00:46:18', '2026-03-06 21:46:18'),
(1305, '127.0.0.1', NULL, '/mini_shop/about.php', 'http://localhost/mini_shop/admin/orders.php', '2026-03-07', '00:46:25', '2026-03-06 21:46:25'),
(1306, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/about.php', '2026-03-07', '00:46:29', '2026-03-06 21:46:29'),
(1307, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin/page_content.php', '2026-03-07', '00:50:23', '2026-03-06 21:50:23'),
(1308, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '00:50:25', '2026-03-06 21:50:25'),
(1309, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '00:50:28', '2026-03-06 21:50:28'),
(1310, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '00:50:29', '2026-03-06 21:50:29'),
(1311, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '00:50:29', '2026-03-06 21:50:29'),
(1312, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '00:50:30', '2026-03-06 21:50:30'),
(1313, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin/dashboard.php', '2026-03-07', '01:08:58', '2026-03-06 22:08:58'),
(1314, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/login.php?redirect=order_success.php%3Fid%3D1', '2026-03-07', '01:10:20', '2026-03-06 22:10:20'),
(1315, '127.0.0.1', NULL, '/mini_shop/product.php?slug=zenobia-bir-g-ocmen-hikayesi', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:10:22', '2026-03-06 22:10:22'),
(1316, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/order_success.php?id=1', '2026-03-07', '01:11:16', '2026-03-06 22:11:16'),
(1317, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:11:24', '2026-03-06 22:11:24'),
(1318, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:11:27', '2026-03-06 22:11:27'),
(1319, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:11:29', '2026-03-06 22:11:29'),
(1320, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/login.php', '2026-03-07', '01:12:04', '2026-03-06 22:12:04'),
(1321, '127.0.0.1', NULL, '/mini_shop/product.php?slug=bleach-cilt-49', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:12:09', '2026-03-06 22:12:09'),
(1322, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:12:59', '2026-03-06 22:12:59'),
(1323, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:13:03', '2026-03-06 22:13:03'),
(1324, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:01', '2026-03-06 22:14:01'),
(1325, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:14:02', '2026-03-06 22:14:02'),
(1326, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:14:03', '2026-03-06 22:14:03'),
(1327, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:04', '2026-03-06 22:14:04'),
(1328, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:05', '2026-03-06 22:14:05'),
(1329, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:05', '2026-03-06 22:14:05'),
(1330, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:06', '2026-03-06 22:14:06'),
(1331, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:06', '2026-03-06 22:14:06'),
(1332, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:06', '2026-03-06 22:14:06'),
(1333, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:07', '2026-03-06 22:14:07'),
(1334, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:14:07', '2026-03-06 22:14:07'),
(1335, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:14:10', '2026-03-06 22:14:10'),
(1336, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:14:11', '2026-03-06 22:14:11'),
(1337, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:14:12', '2026-03-06 22:14:12'),
(1338, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:14:13', '2026-03-06 22:14:13'),
(1339, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:14:14', '2026-03-06 22:14:14'),
(1340, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:14:15', '2026-03-06 22:14:15'),
(1341, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:14:15', '2026-03-06 22:14:15'),
(1342, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:14:16', '2026-03-06 22:14:16'),
(1343, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:14:19', '2026-03-06 22:14:19'),
(1344, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:19', '2026-03-06 22:14:19'),
(1345, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:14:22', '2026-03-06 22:14:22'),
(1346, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:23', '2026-03-06 22:14:23'),
(1347, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:14:24', '2026-03-06 22:14:24'),
(1348, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:25', '2026-03-06 22:14:25'),
(1349, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:26', '2026-03-06 22:14:26'),
(1350, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:26', '2026-03-06 22:14:26'),
(1351, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:27', '2026-03-06 22:14:27'),
(1352, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:27', '2026-03-06 22:14:27'),
(1353, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:27', '2026-03-06 22:14:27'),
(1354, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:27', '2026-03-06 22:14:27'),
(1355, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:27', '2026-03-06 22:14:27'),
(1356, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:28', '2026-03-06 22:14:28'),
(1357, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:28', '2026-03-06 22:14:28'),
(1358, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:28', '2026-03-06 22:14:28'),
(1359, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:14:29', '2026-03-06 22:14:29'),
(1360, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:31', '2026-03-06 22:14:31'),
(1361, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:32', '2026-03-06 22:14:32'),
(1362, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:33', '2026-03-06 22:14:33'),
(1363, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:33', '2026-03-06 22:14:33'),
(1364, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:35', '2026-03-06 22:14:35'),
(1365, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:36', '2026-03-06 22:14:36'),
(1366, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:36', '2026-03-06 22:14:36'),
(1367, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:36', '2026-03-06 22:14:36'),
(1368, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:41', '2026-03-06 22:14:41'),
(1369, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:44', '2026-03-06 22:14:44'),
(1370, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:45', '2026-03-06 22:14:45'),
(1371, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:45', '2026-03-06 22:14:45'),
(1372, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:46', '2026-03-06 22:14:46');
INSERT INTO `site_visits` (`id`, `ip_address`, `user_agent`, `page_url`, `referrer`, `visit_date`, `visit_time`, `created_at`) VALUES
(1373, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=oldest&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=oldest', '2026-03-07', '01:14:48', '2026-03-06 22:14:48'),
(1374, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=newest&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=newest', '2026-03-07', '01:14:50', '2026-03-06 22:14:50'),
(1375, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:14:55', '2026-03-06 22:14:55'),
(1376, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:59', '2026-03-06 22:14:59'),
(1377, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:59', '2026-03-06 22:14:59'),
(1378, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:59', '2026-03-06 22:14:59'),
(1379, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:14:59', '2026-03-06 22:14:59'),
(1380, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:00', '2026-03-06 22:15:00'),
(1381, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:01', '2026-03-06 22:15:01'),
(1382, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:01', '2026-03-06 22:15:01'),
(1383, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:02', '2026-03-06 22:15:02'),
(1384, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:02', '2026-03-06 22:15:02'),
(1385, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:02', '2026-03-06 22:15:02'),
(1386, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:06', '2026-03-06 22:15:06'),
(1387, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:10', '2026-03-06 22:15:10'),
(1388, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=price_desc', '2026-03-07', '01:15:12', '2026-03-06 22:15:12'),
(1389, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:13', '2026-03-06 22:15:13'),
(1390, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:16', '2026-03-06 22:15:16'),
(1391, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:18', '2026-03-06 22:15:18'),
(1392, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:19', '2026-03-06 22:15:19'),
(1393, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:20', '2026-03-06 22:15:20'),
(1394, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:20', '2026-03-06 22:15:20'),
(1395, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:20', '2026-03-06 22:15:20'),
(1396, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:21', '2026-03-06 22:15:21'),
(1397, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:21', '2026-03-06 22:15:21'),
(1398, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:21', '2026-03-06 22:15:21'),
(1399, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:22', '2026-03-06 22:15:22'),
(1400, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:23', '2026-03-06 22:15:23'),
(1401, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:24', '2026-03-06 22:15:24'),
(1402, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:24', '2026-03-06 22:15:24'),
(1403, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:25', '2026-03-06 22:15:25'),
(1404, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:25', '2026-03-06 22:15:25'),
(1405, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:25', '2026-03-06 22:15:25'),
(1406, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:25', '2026-03-06 22:15:25'),
(1407, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:25', '2026-03-06 22:15:25'),
(1408, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:28', '2026-03-06 22:15:28'),
(1409, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:28', '2026-03-06 22:15:28'),
(1410, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:29', '2026-03-06 22:15:29'),
(1411, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:29', '2026-03-06 22:15:29'),
(1412, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:29', '2026-03-06 22:15:29'),
(1413, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random&ajax=popular', 'http://localhost/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', '2026-03-07', '01:15:30', '2026-03-06 22:15:30'),
(1414, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:31', '2026-03-06 22:15:31'),
(1415, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:31', '2026-03-06 22:15:31'),
(1416, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:32', '2026-03-06 22:15:32'),
(1417, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:32', '2026-03-06 22:15:32'),
(1418, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:33', '2026-03-06 22:15:33'),
(1419, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:34', '2026-03-06 22:15:34'),
(1420, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:34', '2026-03-06 22:15:34'),
(1421, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:34', '2026-03-06 22:15:34'),
(1422, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:36', '2026-03-06 22:15:36'),
(1423, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:36', '2026-03-06 22:15:36'),
(1424, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:36', '2026-03-06 22:15:36'),
(1425, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:37', '2026-03-06 22:15:37'),
(1426, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:38', '2026-03-06 22:15:38'),
(1427, '127.0.0.1', NULL, '/mini_shop/index.php?popular_page=1&random_refresh=1&popular_sort=random', 'http://localhost/mini_shop/products.php', '2026-03-07', '01:15:38', '2026-03-06 22:15:38'),
(1428, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:15:40', '2026-03-06 22:15:40'),
(1429, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:15:40', '2026-03-06 22:15:40'),
(1430, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=2', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=2', '2026-03-07', '01:15:42', '2026-03-06 22:15:42'),
(1431, '127.0.0.1', NULL, '/mini_shop/index.php?popular_sort=random&popular_page=1', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:15:42', '2026-03-06 22:15:42'),
(1432, '127.0.0.1', NULL, '/mini_shop/products.php', 'http://localhost/mini_shop/index.php?popular_sort=random&popular_page=1', '2026-03-07', '01:15:43', '2026-03-06 22:15:43'),
(1433, '127.0.0.1', NULL, '/mini_shop/products.php?random_refresh=1&sort=random&ajax=1', 'http://localhost/mini_shop/products.php?random_refresh=1&sort=random', '2026-03-07', '01:15:46', '2026-03-06 22:15:46'),
(1434, '127.0.0.1', NULL, '/mini_shop/products.php?random_refresh=1&sort=random&ajax=1', 'http://localhost/mini_shop/products.php?random_refresh=1&sort=random', '2026-03-07', '01:15:46', '2026-03-06 22:15:46'),
(1435, '127.0.0.1', NULL, '/mini_shop/products.php?random_refresh=1&sort=random&ajax=1', 'http://localhost/mini_shop/products.php?random_refresh=1&sort=random', '2026-03-07', '01:15:48', '2026-03-06 22:15:48'),
(1436, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=2&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=2', '2026-03-07', '01:15:50', '2026-03-06 22:15:50'),
(1437, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=1&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=1', '2026-03-07', '01:15:50', '2026-03-06 22:15:50'),
(1438, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=2&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=2', '2026-03-07', '01:15:51', '2026-03-06 22:15:51'),
(1439, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=1&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=1', '2026-03-07', '01:15:51', '2026-03-06 22:15:51'),
(1440, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=2&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=2', '2026-03-07', '01:15:52', '2026-03-06 22:15:52'),
(1441, '127.0.0.1', NULL, '/mini_shop/products.php?sort=random&page=1&ajax=1', 'http://localhost/mini_shop/products.php?sort=random&page=1', '2026-03-07', '01:15:52', '2026-03-06 22:15:52'),
(1442, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/products.php?sort=random&page=1', '2026-03-07', '01:16:17', '2026-03-06 22:16:17'),
(1443, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:16:27', '2026-03-06 22:16:27'),
(1444, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:16:35', '2026-03-06 22:16:35'),
(1445, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:17:39', '2026-03-06 22:17:39'),
(1446, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/profile.php', '2026-03-07', '01:18:14', '2026-03-06 22:18:14'),
(1447, '127.0.0.1', NULL, '/mini_shop/product.php?slug=venom-2018-cilt-6-venom-alternatif-d-unyada', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:18:17', '2026-03-06 22:18:17'),
(1448, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:18:41', '2026-03-06 22:18:41'),
(1449, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:18:52', '2026-03-06 22:18:52'),
(1450, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:19:12', '2026-03-06 22:19:12'),
(1451, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:20:25', '2026-03-06 22:20:25'),
(1452, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:20:26', '2026-03-06 22:20:26'),
(1453, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '01:20:26', '2026-03-06 22:20:26'),
(1454, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:20:28', '2026-03-06 22:20:28'),
(1455, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:20:31', '2026-03-06 22:20:31'),
(1456, '127.0.0.1', NULL, '/mini_shop/index.php', NULL, '2026-03-07', '01:20:36', '2026-03-06 22:20:36'),
(1457, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/my_orders.php', '2026-03-07', '01:20:43', '2026-03-06 22:20:43'),
(1458, '127.0.0.1', NULL, '/mini_shop/about.php', 'http://localhost/mini_shop/admin/page_content.php', '2026-03-07', '01:21:18', '2026-03-06 22:21:18'),
(1459, '127.0.0.1', NULL, '/mini_shop/about.php', 'http://localhost/mini_shop/admin/page_content.php', '2026-03-07', '01:21:37', '2026-03-06 22:21:37'),
(1460, '127.0.0.1', NULL, '/mini_shop/about.php', 'http://localhost/mini_shop/admin/page_content.php', '2026-03-07', '01:21:43', '2026-03-06 22:21:43'),
(1461, '127.0.0.1', NULL, '/mini_shop/about.php', 'http://localhost/mini_shop/admin/page_content.php', '2026-03-07', '01:25:32', '2026-03-06 22:25:32'),
(1462, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin/dashboard.php', '2026-03-07', '01:26:17', '2026-03-06 22:26:17'),
(1463, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:38', '2026-03-06 23:02:38'),
(1464, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:38', '2026-03-06 23:02:38'),
(1465, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:38', '2026-03-06 23:02:38'),
(1466, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:38', '2026-03-06 23:02:38'),
(1467, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:39', '2026-03-06 23:02:39'),
(1468, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:39', '2026-03-06 23:02:39'),
(1469, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1470, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1471, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1472, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1473, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1474, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:40', '2026-03-06 23:02:40'),
(1475, '127.0.0.1', NULL, '/mini_shop/contact.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:41', '2026-03-06 23:02:41'),
(1476, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin/page_content.php?sayfa=iletisim', '2026-03-07', '02:02:52', '2026-03-06 23:02:52'),
(1477, '127.0.0.1', NULL, '/mini_shop/about.php', NULL, '2026-03-07', '02:18:33', '2026-03-06 23:18:33'),
(1478, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/admin_profile.php', '2026-03-07', '02:28:33', '2026-03-06 23:28:33'),
(1479, '127.0.0.1', NULL, '/mini_shop/index.php', 'http://localhost/mini_shop/index.php', '2026-03-07', '02:28:33', '2026-03-06 23:28:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `slider_slides`
--

CREATE TABLE `slider_slides` (
  `id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `full_name`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$12$s7wXmcFVx8EZ9yBq88jvyeT73JSa.nZa56/rJxFvG3U9UYgH9suJK', 'admin', '2025-11-29 17:53:39');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `address_title` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_title`, `address`, `is_default`, `created_at`) VALUES
(1, 1, 'ev', 'ev', 0, '2026-03-06 22:10:46');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin_addresses`
--
ALTER TABLE `admin_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`);

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Tablo için indeksler `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Tablo için indeksler `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `site_users`
--
ALTER TABLE `site_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `site_visits`
--
ALTER TABLE `site_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_visit_date` (`visit_date`);

--
-- Tablo için indeksler `slider_slides`
--
ALTER TABLE `slider_slides`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Tablo için indeksler `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admin_addresses`
--
ALTER TABLE `admin_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Tablo için AUTO_INCREMENT değeri `site_users`
--
ALTER TABLE `site_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `site_visits`
--
ALTER TABLE `site_visits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1480;

--
-- Tablo için AUTO_INCREMENT değeri `slider_slides`
--
ALTER TABLE `slider_slides`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `admin_addresses`
--
ALTER TABLE `admin_addresses`
  ADD CONSTRAINT `admin_addresses_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `site_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
