<?php

$pages = getPages($pdo);
$pageSlugs = array_column($pages, 'slug');
$hasAboutPage = in_array('hakkimizda', $pageSlugs, true);
$hasContactPage = in_array('iletisim', $pageSlugs, true);

$navCategories = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- ============================================
         META ETİKETLERİ
         ============================================
         Sayfa karakter kodlaması ve responsive ayarları
    -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? sanitize($pageDescription) : 'ManRoMan - E-ticaret ve kitap'; ?>">
    <meta name="robots" content="index, follow">
    <?php
    $seoTitle = isset($pageTitle) ? 'ManRoMan - ' . sanitize($pageTitle) : 'ManRoMan';
    $seoDesc = isset($pageDescription) ? sanitize($pageDescription) : 'ManRoMan - E-ticaret ve kitap';
    $seoCanonical = isset($canonicalUrl) ? $canonicalUrl : '';
    if ($seoCanonical === '' && defined('SITE_URL')) {
        $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $seoCanonical = SITE_URL . (strpos($reqPath, BASE_URL) === 0 ? substr($reqPath, strlen(BASE_URL)) : $reqPath);
        if ($seoCanonical === SITE_URL) {
            $seoCanonical = SITE_URL . '/';
        }
    }
    $seoImage = '';
    if (!empty($ogImage)) {
        $seoImage = (strpos($ogImage, 'http') === 0) ? $ogImage : (defined('SITE_URL') ? rtrim(SITE_URL, '/') . (strpos($ogImage, '/') === 0 ? '' : '/') . ltrim($ogImage, '/') : $ogImage);
    } else {
        $seoImage = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/img/logo.png' : '';
    }
    ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="<?php echo isset($ogType) ? sanitize($ogType) : 'website'; ?>">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="ManRoMan">
    <meta property="og:title" content="<?php echo $seoTitle; ?>">
    <meta property="og:description" content="<?php echo $seoDesc; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $seoTitle; ?>">
    <meta name="twitter:description" content="<?php echo $seoDesc; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- ============================================
         SAYFA BAŞLIĞI
         ============================================
         Her sayfa için dinamik başlık (eğer $pageTitle tanımlıysa)
    -->
    <title><?php echo $seoTitle; ?></title>
    
    <!-- ============================================
         FAVİCON (SİTE İKONU)
         ============================================
         Tarayıcı sekmesinde görünecek ikon
    -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/img/icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>/img/icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL; ?>/img/icon.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_URL; ?>/img/icon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL; ?>/img/icon.png">
    
    <!-- ============================================
         CSS DOSYALARI
         ============================================
         Ana stil dosyası (hem frontend hem admin için)
    -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
    
    <!-- ============================================
         FINAL OVERRIDE CSS (YÜKSEK ÖNCELİK)
         ============================================
         Tüm tema kurallarını override eden kritik kurallar
         EN SON yüklenir, bu yüzden en yüksek önceliğe sahiptir
    -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/final_override.css">
    
    <!-- ============================================
         JAVASCRIPT GLOBAL DEĞİŞKENLER
         ============================================
         PHP değişkenlerini JavaScript'e aktar (harici JS dosyaları için)
    -->
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <script>
        // BASE_URL'i JavaScript'te kullanmak için global değişken olarak tanımla
        // Harici JS dosyalarında (assets/js/main.js) AJAX çağrıları için kullanılır
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        window.CSRF_TOKEN = '<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>';
        
        // ============================================
        // TEMA KONTROLÜ (DARK MODE)
        // ============================================
        // Sayfa yüklendiğinde localStorage'dan tema tercihini oku ve uygula
        // DOMContentLoaded ile güvenli yükleme (DOM hazır olmadan çalışmaz)
        document.addEventListener('DOMContentLoaded', function() {
            // localStorage'dan tema tercihini al (yoksa 'light' varsayılan)
            const theme = localStorage.getItem('theme') || 'light';
            
            // Eğer tema 'dark' ise body'ye 'dark-theme' class'ını ekle
            if (theme === 'dark' && document.body) {
                document.body.classList.add('dark-theme');
            }
        });
        
        // ============================================
        // FALLBACK (YEDEK) TEMA KONTROLÜ
        // ============================================
        // Eğer DOM zaten yüklendiyse (script geç yüklendi) hemen çalıştır
        if (document.readyState === 'loading') {
            // DOMContentLoaded bekleniyor, yukarıdaki event listener çalışacak
        } else {
            // DOM zaten yüklü, hemen çalıştır (geç yüklenen scriptler için)
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark' && document.body) {
                document.body.classList.add('dark-theme');
            }
        }
    </script>
    <?php if (defined('SITE_URL')): ?>
    <script type="application/ld+json"><?php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'ManRoMan',
        'url' => SITE_URL . '/',
        'description' => 'ManRoMan - E-ticaret ve kitap',
        'inLanguage' => 'tr',
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'ManRoMan',
            'url' => SITE_URL . '/',
            'logo' => ['@type' => 'ImageObject', 'url' => rtrim(SITE_URL, '/') . '/img/logo.png']
        ]
    ], JSON_UNESCAPED_UNICODE); ?></script>
    <?php if (!empty($jsonLdProduct)): ?>
    <script type="application/ld+json"><?php echo json_encode($jsonLdProduct, JSON_UNESCAPED_UNICODE); ?></script>
    <?php endif; ?>
    <?php endif; ?>
</head>
<body>
<!-- ============================================
     SİTE HEADER (ÜST KISIM)
     ============================================
     Logo, navigasyon menüsü, arama ve kullanıcı butonları
-->
<header class="site-header">
    <div class="container header-row">
        <!-- Logo (anasayfaya link) -->
        <a class="logo" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo BASE_URL; ?>/img/logo.png" alt="ManRoMan" class="logo-image">
        </a>
        
        <!-- Ana Navigasyon Menüsü -->
        <nav class="main-nav">
            <a href="<?php echo BASE_URL; ?>/index.php">Anasayfa</a>
            <div class="dropdown header-nav-dropdown">
                <button type="button">Kategoriler</button>
                <div class="dropdown-menu">
                    <?php foreach ($navCategories as $navCategory): ?>
                        <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo sanitize($navCategory['slug']); ?>">
                            <?php echo sanitize($navCategory['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/products.php">Ürünler</a>
            <?php if ($hasAboutPage): ?><a href="<?php echo BASE_URL; ?>/about.php">Hakkımızda</a><?php endif; ?>
            <?php if ($hasContactPage): ?><a href="<?php echo BASE_URL; ?>/contact.php">İletişim</a><?php endif; ?>
            <?php
            $dynamicPages = array_values(array_filter($pages, function ($p) { return !in_array($p['slug'], ['hakkimizda', 'iletisim'], true); }));
            usort($dynamicPages, function ($a, $b) { return (int) $a['id'] - (int) $b['id']; });
            $maxDirectLinks = 5;
            $directLinkPages = array_slice($dynamicPages, 0, $maxDirectLinks);
            $otherPages = array_slice($dynamicPages, $maxDirectLinks);
            ?>
            <?php foreach ($directLinkPages as $navPage): ?>
            <a href="<?php echo BASE_URL; ?>/page.php?slug=<?php echo sanitize($navPage['slug']); ?>"><?php echo sanitize($navPage['title']); ?></a>
            <?php endforeach; ?>
            <?php if (!empty($otherPages)): ?>
            <div class="dropdown header-nav-dropdown">
                <button type="button">Diğer</button>
                <div class="dropdown-menu">
                    <?php foreach ($otherPages as $navPage): ?>
                        <a href="<?php echo BASE_URL; ?>/page.php?slug=<?php echo sanitize($navPage['slug']); ?>">
                            <?php echo sanitize($navPage['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <!-- ============================================
                 ARAMA FORMU
                 ============================================
                 Ürün arama işlemi (products.php'ye yönlendirir)
            -->
            <form class="header-search" method="get" action="<?php echo BASE_URL; ?>/products.php" style="margin-left: auto; margin-right: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Ürün ara..." 
                    value="<?php echo sanitize($_GET['search'] ?? ''); ?>"
                    class="search-input"
                >
                <button type="submit" class="search-button" title="Ara">🔍</button>
            </form>
            
            <!-- ============================================
                 KULLANICI BUTONLARI
                 ============================================
                 Giriş yapmış kullanıcı veya admin için menü
            -->
            <div class="nav-right-buttons">
                <!-- Eğer admin giriş yapmışsa -->
                <?php if (adminIsLoggedIn()): ?>
                    <?php 
                    
                    $adminUser = $_SESSION['admin'] ?? null;
                    $adminName = $adminUser['username'] ?? 'Admin';
                    ?>
                    <!-- Admin dropdown menüsü -->
                    <div class="dropdown header-user-dropdown">
                        <button type="button" class="btn-user">Merhaba, <?php echo sanitize($adminName); ?></button>
                        <div class="dropdown-menu">
                            <!-- Yönetim paneli linki -->
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">⚙️ Yönetim Paneli</a>
                            <!-- Admin profil (adresler) linki -->
                            <a href="<?php echo BASE_URL; ?>/admin_profile.php">👤 Profilim</a>
                            <!-- Siparişlerim linki -->
                            <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                            <!-- Çıkış yap linki -->
                            <a href="<?php echo BASE_URL; ?>/admin/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <!-- Eğer normal kullanıcı giriş yapmışsa -->
            <?php elseif (userIsLoggedIn()): ?>
                <?php 
                
                $user = getUser(); 
                ?>
                <!-- Kullanıcı dropdown menüsü -->
                <div class="dropdown header-user-dropdown">
                    <button type="button" class="btn-user">Merhaba, <?php echo sanitize($user['full_name'] ?: $user['username']); ?></button>
                    <div class="dropdown-menu">
                        <!-- Profil sayfası linki -->
                        <a href="<?php echo BASE_URL; ?>/profile.php">👤 Profilim</a>
                        <!-- Siparişlerim sayfası linki -->
                        <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                        <!-- Çıkış yap linki -->
                        <a href="<?php echo BASE_URL; ?>/logout.php">Çıkış Yap</a>
                    </div>
                </div>
                <!-- Eğer kullanıcı giriş yapmamışsa -->
                <?php else: ?>
                    <!-- Giriş yap butonu -->
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline">Giriş Yap</a>
                    <!-- Üye ol butonu -->
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary">Üye Ol</a>
                <?php endif; ?>
                
                <!-- ============================================
                     SEPET BUTONU
                     ============================================
                     Sepet sayfasına giden buton (ürün sayısı badge ile)
                -->
                <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-button" title="Sepet">
                    <span class="cart-icon">🛒</span>
                    <!-- Sepetteki toplam ürün sayısını hesapla (geçersiz ürünler otomatik temizlenir) -->
                    <?php 

                    $cartCount = getCartCount($pdo); 
                    ?>
                    <!-- Eğer sepette ürün varsa badge göster -->
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- ============================================
                     TEMA DEĞİŞTİRME BUTONU
                     ============================================
                     Light/Dark mode toggle butonu
                -->
                <button id="theme-toggle" class="theme-toggle" title="Tema Değiştir" type="button">
                    <span class="theme-slider">
                        <span class="theme-slider-handle"></span>
                    </span>
                    <span class="theme-label">☀️</span>
                </button>
            </div>
        </nav>
        
        <!-- ============================================
             MOBİL MENÜ TOGGLE BUTONU (HAMBURGER)
             ============================================
             Sadece mobilde görünür (max-width: 768px)
             JavaScript ile menüyü açar/kapatır
        -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Menü">
            <span></span>
        </button>
    </div>
    
    <!-- ============================================
         MOBİL MENÜ (OFF-CANVAS)
         ============================================
         Mobil cihazlarda açılan dropdown menü
         Accordion yapısında alt menüler
    -->
    <div class="mobile-menu" id="mobileMenu">
        <!-- Mobil menü içeriği (navigasyon linkleri) -->
        <div class="mobile-menu-content">
            <!-- Anasayfa linki -->
            <a href="<?php echo BASE_URL; ?>/index.php">Anasayfa</a>
            
            <!-- Kategoriler dropdown (accordion yapısında) -->
            <div class="dropdown" id="mobileCategoriesDropdown">
                <button type="button">Kategoriler</button>
                <div class="dropdown-menu">
                    <!-- Her kategori için bir link oluştur -->
                    <?php foreach ($navCategories as $navCategory): ?>
                        <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo sanitize($navCategory['slug']); ?>">
                            <?php echo sanitize($navCategory['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Ürünler linki -->
            <a href="<?php echo BASE_URL; ?>/products.php">Ürünler</a>
            
            <!-- Hakkımızda linki -->
            <?php if ($hasAboutPage): ?>
            <a href="<?php echo BASE_URL; ?>/about.php">Hakkımızda</a>
            <?php endif; ?>
            
            <!-- İletişim linki -->
            <?php if ($hasContactPage): ?>
            <a href="<?php echo BASE_URL; ?>/contact.php">İletişim</a>
            <?php endif; ?>
            
            <?php foreach ($directLinkPages as $navPage): ?>
            <a href="<?php echo BASE_URL; ?>/page.php?slug=<?php echo sanitize($navPage['slug']); ?>"><?php echo sanitize($navPage['title']); ?></a>
            <?php endforeach; ?>
            <?php if (!empty($otherPages)): ?>
            <div class="dropdown" id="mobileOtherDropdown">
                <button type="button">Diğer</button>
                <div class="dropdown-menu">
                    <?php foreach ($otherPages as $navPage): ?>
                        <a href="<?php echo BASE_URL; ?>/page.php?slug=<?php echo sanitize($navPage['slug']); ?>">
                            <?php echo sanitize($navPage['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Mobil menü arama formu -->
        <div class="mobile-menu-search">
            <form method="get" action="<?php echo BASE_URL; ?>/products.php">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Ürün ara..." 
                    value="<?php echo sanitize($_GET['search'] ?? ''); ?>"
                    class="search-input"
                >
                <button type="submit" class="search-button" title="Ara">🔍</button>
            </form>
        </div>
        
        <!-- Mobil menü butonları (kullanıcı, sepet, tema) -->
        <div class="mobile-menu-buttons">
            <div class="nav-right-buttons">
                <!-- Eğer admin giriş yapmışsa -->
                <?php if (adminIsLoggedIn()): ?>
                    <?php 
                    
                    $adminUser = $_SESSION['admin'] ?? null;
                    $adminName = $adminUser['username'] ?? 'Admin';
                    ?>
                    <!-- Admin dropdown menüsü -->
                    <div class="dropdown">
                        <button type="button" class="btn-user">Merhaba, <?php echo sanitize($adminName); ?></button>
                        <div class="dropdown-menu">
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">⚙️ Yönetim Paneli</a>
                            <a href="<?php echo BASE_URL; ?>/admin_profile.php">👤 Profilim</a>
                            <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                            <a href="<?php echo BASE_URL; ?>/admin/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <!-- Eğer normal kullanıcı giriş yapmışsa -->
                <?php elseif (userIsLoggedIn()): ?>
                    <?php 
                    
                    $user = getUser(); 
                    ?>
                    <!-- Kullanıcı dropdown menüsü -->
                    <div class="dropdown">
                        <button type="button" class="btn-user">Merhaba, <?php echo sanitize($user['full_name'] ?: $user['username']); ?></button>
                        <div class="dropdown-menu">
                            <a href="<?php echo BASE_URL; ?>/profile.php">👤 Profilim</a>
                            <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                            <a href="<?php echo BASE_URL; ?>/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <!-- Eğer kullanıcı giriş yapmamışsa -->
                <?php else: ?>
                    <!-- Giriş yap butonu -->
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline">Giriş Yap</a>
                    <!-- Üye ol butonu -->
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary">Üye Ol</a>
                <?php endif; ?>
                
                <!-- Sepet butonu (mobil menü içinde) -->
                <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-button" title="Sepet">
                    <span class="cart-icon">🛒</span>
                    <!-- Sepetteki toplam ürün sayısını hesapla (geçersiz ürünler otomatik temizlenir) -->
                    <?php 

                    $cartCount = getCartCount($pdo); 
                    ?>
                    <!-- Eğer sepette ürün varsa badge göster -->
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Tema değiştir butonu (mobil menü içinde) -->
                <button id="theme-toggle-mobile" class="theme-toggle" title="Tema Değiştir" type="button">
                    <span class="theme-slider">
                        <span class="theme-slider-handle"></span>
                    </span>
                    <span class="theme-label">☀️</span>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
(function() {
    function positionDropdownMenu(btn, menu, minWidth) {
        if (!btn || !menu) return;
        var r = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top = (r.bottom + 4) + 'px';
        menu.style.left = r.left + 'px';
        menu.style.minWidth = (minWidth || Math.max(r.width, 200)) + 'px';
        menu.style.display = 'block';
    }
    function hideMenu(menu) {
        menu.style.display = 'none';
    }
    function initFrontendDropdowns() {
        /* Kullanıcı/Admin dropdown */
        document.querySelectorAll('.site-header .header-user-dropdown').forEach(function(wrap) {
            var btn = wrap.querySelector('.btn-user');
            var menu = wrap.querySelector('.dropdown-menu');
            if (!btn || !menu) return;
            wrap.addEventListener('mouseenter', function() { positionDropdownMenu(btn, menu); });
            wrap.addEventListener('mouseleave', function() { hideMenu(menu); });
        });
        /* Kategoriler ve Diğer dropdown - fixed konumda, slider üstünde görünsün */
        document.querySelectorAll('.site-header .header-nav-dropdown').forEach(function(wrap) {
            var btn = wrap.querySelector('button');
            var menu = wrap.querySelector('.dropdown-menu');
            if (!btn || !menu) return;
            wrap.addEventListener('mouseenter', function() { positionDropdownMenu(btn, menu, 220); });
            wrap.addEventListener('mouseleave', function() { hideMenu(menu); });
        });
        function hideAllDropdowns() {
            document.querySelectorAll('.site-header .header-user-dropdown .dropdown-menu, .site-header .header-nav-dropdown .dropdown-menu').forEach(function(m) { hideMenu(m); });
        }
        window.addEventListener('scroll', hideAllDropdowns, true);
        window.addEventListener('resize', hideAllDropdowns);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFrontendDropdowns);
    } else {
        initFrontendDropdowns();
    }

})();
</script>

<!-- ============================================
     ANA İÇERİK ALANI
     ============================================
     Sayfa içeriği buraya gelecek
-->
<main class="container" style="flex: 1; padding-top: 2.5rem;">

