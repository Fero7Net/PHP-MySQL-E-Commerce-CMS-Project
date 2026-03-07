<?php

requireAdminLogin();

if (!isset($pages)) {
    $pages = getPages($pdo);
}
if (!isset($categories)) {
    $categories = getCategories($pdo);
}
$navPageSlugs = array_column($pages, 'slug');
$hasAboutPage = in_array('hakkimizda', $navPageSlugs, true);
$hasContactPage = in_array('iletisim', $navPageSlugs, true);
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
    
    <!-- ============================================
         SAYFA BAŞLIĞI
         ============================================
         Admin paneli için sabit başlık
    -->
    <title>ManRoMan Yönetim</title>
    
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
    <script>
        // BASE_URL'i JavaScript'te kullanmak için global değişken olarak tanımla
        // Harici JS dosyalarında (assets/js/admin.js) AJAX çağrıları için kullanılır
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <!-- ============================================
         ADMIN PANELİ ÖZEL STİLLERİ
         ============================================
         Admin paneli için özel CSS kuralları
         (Sidebar, header, mobil menü vb.)
    -->
    <style>
        /* ============================================
           ADMIN PANEL BODY STİLİ
           ============================================
           Flexbox layout ile sidebar ve content yan yana
        */
        body.admin-panel {
            display: flex; /* Flexbox layout */
            padding-top: 0; /* Üstten padding yok (header fixed) */
            min-height: 100vh; /* Minimum yükseklik: viewport height */
        }
        
        /* ============================================
           MOBİL MENÜ AÇIKKEN SCROLL KİLİDİ
           ============================================
           Mobilde menü açıkken sayfa kaydırmayı engelle
        */
        body.admin-panel.menu-open {
            overflow: hidden; /* Scroll'u engelle */
            position: fixed; /* Sayfayı sabitle */
            width: 100%; /* Tam genişlik */
            height: 100%; /* Tam yükseklik */
        }
        
        /* ============================================
           ADMIN SIDEBAR (YAN MENÜ)
           ============================================
           Sol tarafta sabitlenmiş yan menü
        */
        .admin-sidebar {
            width: 260px; /* Sabit genişlik */
            background: var(--light); /* Arka plan rengi */
            border-right: 2px solid var(--border); /* Sağ kenarlık */
            position: fixed; /* Sabit pozisyon */
            left: 0; /* Sol tarafta */
            top: 80px; /* Header yüksekliği kadar aşağıda */
            bottom: 0; /* Alt kısma kadar */
            overflow-y: auto; /* Dikey scroll (içerik taşarsa) */
            z-index: 998; /* Diğer elementlerin üstünde */
            display: flex; /* Flexbox layout */
            flex-direction: column; /* Dikey düzen */
            padding-top: 0; /* Üstten padding yok */
        }
        
        .admin-sidebar-header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 2px solid var(--border);
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70px;
            flex-shrink: 0;
        }
        
        .admin-sidebar-header strong {
            font-size: 1.1rem;
            display: block;
            text-align: center;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .admin-sidebar-logo {
            max-height: 50px;
            max-width: 200px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            filter: brightness(0) invert(1);
            margin: 0 auto;
        }
        
        body.dark-theme .admin-sidebar-logo {
            filter: brightness(0) invert(1);
        }
        
        .admin-sidebar-nav {
            flex: 1;
            padding: 1rem 0;
        }
        
        .admin-sidebar-nav a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .admin-sidebar-nav a:hover {
            background: rgba(37, 99, 235, 0.1);
            border-left-color: var(--primary);
        }
        
        .admin-sidebar-nav a.active {
            background: rgba(37, 99, 235, 0.15);
            border-left-color: var(--primary);
            font-weight: 600;
        }
        
        .admin-sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 2px solid var(--border);
            margin-top: auto;
        }
        
        .admin-sidebar-footer a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            margin: 0 -1.5rem;
        }
        
        .admin-sidebar-footer a:hover {
            background: rgba(220, 38, 38, 0.1);
            border-left-color: #dc2626;
        }
        
        .admin-sidebar-footer a.active {
            background: rgba(220, 38, 38, 0.15);
            border-left-color: #dc2626;
            font-weight: 600;
        }
        
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem;
            padding-top: 100px; /* Header'dan boşluk */
            padding-bottom: 100px !important; /* Header'dan verdiğimiz boşluk (100px) ile aynı oranda alt boşluk */
            min-height: calc(100vh - 100px); /* Minimum yükseklik: viewport - header */
        }
        
        .admin-top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: #f8f9fa;
            border-bottom: 2px solid var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: visible !important;
        }
        .admin-top-header .container,
        .admin-top-header .header-row,
        .admin-top-header .main-nav,
        .admin-top-header .nav-right-buttons {
            overflow: visible !important;
        }
        /* Merhaba admin dropdown: sadece JS ile göster, fixed konumda kesilmesin */
        .admin-top-header .nav-right-buttons .admin-user-dropdown .dropdown-menu {
            display: none;
            z-index: 99999 !important;
            margin-top: 0;
        }
        
        body.dark-theme .admin-top-header {
            background: #1e293b;
            border-bottom-color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .admin-top-header {
                left: 0;
            }
            
            .admin-content {
                margin-left: 0;
                padding-top: 100px;
            }
        }
        
        .admin-theme-toggle {
            margin-bottom: 1rem;
        }
        
        .admin-theme-toggle .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: var(--border);
            border: none;
            width: 100%;
            transition: background 0.2s;
        }
        
        .admin-theme-toggle .theme-toggle:hover {
            background: rgba(37, 99, 235, 0.1);
        }
        
        .admin-theme-toggle .theme-label {
            font-size: 1.2rem;
            line-height: 1;
            flex-shrink: 0;
        }
        
        .admin-theme-toggle .theme-slider {
            width: 50px;
            height: 26px;
            background: #cbd5e1;
            border-radius: 13px;
            position: relative;
            transition: background 0.3s;
            flex-shrink: 0;
        }
        
        .admin-theme-toggle .theme-slider-handle {
            width: 22px;
            height: 22px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: left 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-theme .admin-theme-toggle .theme-slider {
            background: var(--primary);
        }
        
        body.dark-theme .admin-theme-toggle .theme-slider-handle {
            left: 26px;
        }
        
        .admin-theme-toggle input[type="checkbox"] {
            display: none;
        }
        
        body.dark-theme .admin-sidebar {
            background: #1e293b;
            border-right-color: #334155;
        }
        
        body.dark-theme .admin-sidebar-header {
            background: var(--primary-dark);
        }
        
        body.dark-theme .admin-sidebar-nav a {
            color: #e2e8f0;
        }
        
        body.dark-theme .admin-sidebar-nav a:hover {
            background: rgba(37, 99, 235, 0.2);
        }
        
        body.dark-theme .admin-sidebar-nav a.active {
            background: rgba(37, 99, 235, 0.3);
        }
        
        body.dark-theme .admin-sidebar-footer {
            border-top-color: #334155;
        }
        
        body.dark-theme .admin-sidebar-footer a {
            color: #e2e8f0;
        }
        
        body.dark-theme .admin-sidebar-footer a:hover {
            background: rgba(220, 38, 38, 0.2);
        }
        
        body.dark-theme .admin-sidebar-footer a.active {
            background: rgba(220, 38, 38, 0.3);
        }
        
        @media (max-width: 768px) {
            .admin-top-header {
                left: 0;
            }
            
            .admin-content {
                margin-left: 0;
                padding-top: 100px;
            }
        }
    </style>
</head>
<body class="admin-panel">
<!-- ============================================
     TEMA KONTROLÜ (DARK MODE)
     ============================================
     Sayfa yüklendiğinde localStorage'dan tema tercihini oku ve uygula
-->
<script>
    // DOMContentLoaded ile güvenli yükleme (DOM hazır olmadan çalışmaz)
    document.addEventListener('DOMContentLoaded', function() {
        // localStorage'dan tema tercihini al (yoksa 'light' varsayılan)
        const theme = localStorage.getItem('theme') || 'light';
        
        // Eğer tema 'dark' ise body'ye 'dark-theme' class'ını ekle
        if (theme === 'dark' && document.body) {
            document.body.classList.add('dark-theme');
        }
    });
    
    // Fallback (yedek) tema kontrolü
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

<!-- ============================================
     ADMIN SIDEBAR (YAN MENÜ)
     ============================================
     Sol tarafta sabitlenmiş admin navigasyon menüsü
-->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar başlığı -->
    <div class="admin-sidebar-header">
        <strong>Admin Paneli</strong>
    </div>
    
    <!-- Sidebar navigasyon menüsü -->
    <nav class="admin-sidebar-nav">
        <!-- Genel Bakış (Dashboard) -->
        <!-- basename($_SERVER['PHP_SELF']) = mevcut dosya adını al -->
        <!-- Eğer mevcut sayfa bu link ise 'active' class'ı ekle -->
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">📊 Genel Bakış</a>
        
        <!-- Anasayfa özelleştirme -->
        <a href="<?php echo BASE_URL; ?>/admin/homepage.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'homepage.php' ? 'active' : ''; ?>">🏠 Anasayfa Özelleştir</a>
        
        <!-- Sayfalar yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/pages.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'pages.php' ? 'active' : ''; ?>">📄 Sayfalar</a>
        
        <!-- Sayfa içerikleri (Hakkımızda, İletişim blokları) -->
        <a href="<?php echo BASE_URL; ?>/admin/page_content.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'page_content.php' ? 'active' : ''; ?>">📝 Sayfa İçerikleri</a>
        
        <!-- Slider yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/slider.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'slider.php' ? 'active' : ''; ?>">🖼️ Slider</a>
        
        <!-- Kategoriler yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">📁 Kategoriler</a>
        
        <!-- Ürünler yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/products.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">📚 Ürünler</a>
        
        <!-- Siparişler yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">📦 Siparişler</a>
        
        <!-- Yorumlar yönetimi -->
        <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'comments.php' ? 'active' : ''; ?>">💬 Yorumlar</a>
        
        <!-- ============================================
             SADECE ADMIN YETKİSİ OLAN MENÜLER
             ============================================
             isAdmin() fonksiyonu ile kontrol edilir
        -->
        <?php if (isAdmin()): ?>
            <!-- Kullanıcılar yönetimi (sadece admin) -->
            <a href="<?php echo BASE_URL; ?>/admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">👥 Kullanıcılar</a>
            
            <!-- İstatistikler (sadece admin) -->
            <a href="<?php echo BASE_URL; ?>/admin/statistics.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'statistics.php' ? 'active' : ''; ?>">📈 İstatistikler</a>
            
            <!-- Site ayarları (sadece admin) -->
            <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">⚙️ Ayarlar</a>
            
            <!-- Veritabanı yedekleme (sadece admin) -->
            <a href="<?php echo BASE_URL; ?>/admin/backup.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'backup.php' ? 'active' : ''; ?>">💾 Yedek</a>
            
            <!-- Toplu ürün silme (sadece admin, kırmızı renk ile vurgulanmış) -->
            <a href="<?php echo BASE_URL; ?>/admin/fix_products.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'fix_products.php' ? 'active' : ''; ?>" style="color: #dc2626;">🗑️ Tüm Ürünleri Sil</a>
        <?php endif; ?>
    </nav>
</aside>

<?php

$pages = getPages($pdo);
$categories = getCategories($pdo);
?>

<!-- ============================================
     ADMIN PANELİ ÜST HEADER
     ============================================
     Logo, navigasyon menüsü ve kullanıcı butonları
-->
<header class="site-header admin-top-header">
    <div class="container header-row">
        <!-- Logo (anasayfaya link) -->
        <a class="logo" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo BASE_URL; ?>/img/logo.png" alt="ManRoMan" class="logo-image">
        </a>
        
        <!-- ============================================
             MOBİL MENÜ TOGGLE BUTONU (ADMIN)
             ============================================
             Sadece mobilde görünür (max-width: 768px)
             JavaScript ile sidebar'ı açar/kapatır
        -->
        <button class="admin-mobile-menu-toggle" id="adminMobileMenuToggle" type="button" aria-label="Menü">
            <span></span>
        </button>
        
        <!-- ============================================
             ANA NAVİGASYON MENÜSÜ
             ============================================
             Frontend navigasyon menüsü (admin paneli üst kısmında da gösterilir)
        -->
        <nav class="main-nav">
            <a href="<?php echo BASE_URL; ?>/index.php">Anasayfa</a>
            <div class="dropdown header-nav-dropdown">
                <button type="button">Kategoriler</button>
                <div class="dropdown-menu">
                    <?php foreach ($categories as $navCategory): ?>
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
                 Ürün arama kutusu (products.php'ye yönlendirir)
            -->
            <form class="header-search" method="get" action="<?php echo BASE_URL; ?>/products.php" style="margin-left: auto; margin-right: 1rem;">
                <!-- Arama input'u - GET parametresi olarak 'search' gönderir -->
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Ürün ara..." 
                    value="<?php echo sanitize($_GET['search'] ?? ''); ?>"
                    class="search-input"
                >
                <!-- Arama butonu -->
                <button type="submit" class="search-button" title="Ara">🔍</button>
            </form>
            
            <!-- ============================================
                 SAĞ TARAF BUTONLARI
                 ============================================
                 Kullanıcı menüsü, sepet ve tema değiştirme
            -->
            <div class="nav-right-buttons">
                <!-- ============================================
                     ADMIN GİRİŞİ KONTROLÜ
                     ============================================
                     Eğer admin girişi yapılmışsa admin menüsü göster
                -->
                <?php if (adminIsLoggedIn()): ?>
                    <?php 
                    
                    $adminUser = $_SESSION['admin'] ?? null;
                    $adminName = $adminUser['username'] ?? 'Admin';
                    ?>
                    <div class="dropdown admin-user-dropdown" id="adminUserDropdown">
                        <!-- Admin kullanıcı adını göster -->
                        <button type="button" class="btn-user" id="adminUserDropdownBtn">Merhaba, <?php echo sanitize($adminName); ?></button>
                        <div class="dropdown-menu" id="adminUserDropdownMenu">
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">⚙️ Yönetim Paneli</a>
                            <a href="<?php echo BASE_URL; ?>/admin_profile.php">👤 Profilim</a>
                            <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                            <a href="<?php echo BASE_URL; ?>/admin/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <!-- ============================================
                     NORMAL KULLANICI GİRİŞİ KONTROLÜ
                     ============================================
                     Eğer site kullanıcısı giriş yapmışsa kullanıcı menüsü göster
                -->
                <?php elseif (userIsLoggedIn()): ?>
                    <?php 
                    
                    $user = getUser(); 
                    ?>
                    <div class="dropdown admin-user-dropdown" id="adminUserDropdown">
                        <!-- Kullanıcı adını veya tam adını göster -->
                        <button type="button" class="btn-user" id="adminUserDropdownBtn">Merhaba, <?php echo sanitize($user['full_name'] ?: $user['username']); ?></button>
                        <div class="dropdown-menu" id="adminUserDropdownMenu">
                            <a href="<?php echo BASE_URL; ?>/profile.php">👤 Profilim</a>
                            <a href="<?php echo BASE_URL; ?>/my_orders.php">📦 Siparişlerim</a>
                            <a href="<?php echo BASE_URL; ?>/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <!-- ============================================
                     GİRİŞ YAPILMAMIŞSA
                     ============================================
                     Giriş ve kayıt butonlarını göster
                -->
                <?php else: ?>
                    <!-- Giriş sayfasına git -->
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline">Giriş Yap</a>
                    <!-- Kayıt sayfasına git -->
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary">Üye Ol</a>
                <?php endif; ?>
                
                <!-- ============================================
                     SEPET BUTONU
                     ============================================
                     Sepet sayfasına giden buton ve ürün sayısı badge'i
                -->
                <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-button" title="Sepet">
                    <span class="cart-icon">🛒</span>
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
    </div>
</header>

<script>
(function() {
    function initAdminUserDropdown() {
        var wrap = document.getElementById('adminUserDropdown');
        var btn = document.getElementById('adminUserDropdownBtn');
        var menu = document.getElementById('adminUserDropdownMenu');
        if (!wrap || !btn || !menu) return;
        function positionMenu() {
            var r = btn.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.top = (r.bottom + 4) + 'px';
            menu.style.left = r.left + 'px';
            menu.style.minWidth = Math.max(r.width, 200) + 'px';
            menu.style.display = 'block';
        }
        function hideMenu(m) {
            if (m) m.style.display = 'none';
        }
        wrap.addEventListener('mouseenter', function() { positionMenu(); });
        wrap.addEventListener('mouseleave', function() { hideMenu(menu); });
        window.addEventListener('scroll', function() { hideMenu(menu); }, true);
        window.addEventListener('resize', function() { hideMenu(menu); });
    }
    function initAdminNavDropdowns() {
        document.querySelectorAll('.admin-top-header .header-nav-dropdown').forEach(function(wrap) {
            var btn = wrap.querySelector('button');
            var menu = wrap.querySelector('.dropdown-menu');
            if (!btn || !menu) return;
            function positionDropdownMenu() {
                var r = btn.getBoundingClientRect();
                menu.style.position = 'fixed';
                menu.style.top = (r.bottom + 4) + 'px';
                menu.style.left = r.left + 'px';
                menu.style.minWidth = Math.max(r.width, 220) + 'px';
                menu.style.display = 'block';
            }
            function hideMenu() {
                menu.style.display = 'none';
            }
            wrap.addEventListener('mouseenter', positionDropdownMenu);
            wrap.addEventListener('mouseleave', hideMenu);
            window.addEventListener('scroll', hideMenu, true);
            window.addEventListener('resize', hideMenu);
        });
    }
    function init() {
        initAdminUserDropdown();
        initAdminNavDropdowns();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<!-- ============================================
     ADMIN SIDEBAR OVERLAY (MOBİL)
     ============================================
     Mobilde sidebar açıkken arka planı karartan overlay
     JavaScript ile tıklandığında sidebar'ı kapatır
-->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<!-- ============================================
     ADMIN İÇERİK ALANI
     ============================================
     Admin paneli sayfalarının içeriği buraya gelecek
-->
<main class="admin-content">

