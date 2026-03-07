<?php

require __DIR__ . '/config.php';

logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');

$pageTitle = 'Anasayfa';
$pageDescription = 'ManRoMan - E-ticaret ve kitap. Yeni çıkan kitaplar, popüler kategoriler ve kampanyalı ürünler.';
$canonicalUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/' : '';

$latestProducts = getLatestProducts($pdo, 8);
$latestProductRatings = getProductAverageRatingsBatch($pdo, array_column($latestProducts, 'id')); 

$popularCategoryIdsRaw = getSetting($pdo, 'home_popular_categories', '[]');
$popularCategoryIds = json_decode(trim($popularCategoryIdsRaw), true);
$popularCategoryIds = is_array($popularCategoryIds) ? array_values(array_map('intval', $popularCategoryIds)) : [];
$popularProductIds = json_decode(trim(getSetting($pdo, 'home_popular_products', '[]')), true);
$popularProductIds = is_array($popularProductIds) ? $popularProductIds : [];

$categories = !empty($popularCategoryIds)
    ? getCategoriesByIds($pdo, $popularCategoryIds)
    : getCategories($pdo);

if (!empty($popularCategoryIds) && empty($categories)) {
    $categories = getCategories($pdo);
}

$popularSearch = trim($_GET['search'] ?? '');
$popularSort = isset($_GET['popular_sort']) ? (string) $_GET['popular_sort'] : 'random';
$popularSortOptions = getProductSortOptions();
if (!isset($popularSortOptions[$popularSort])) {
    $popularSort = 'random';
}
if (isset($_GET['random_refresh']) && ($_GET['popular_sort'] ?? '') === 'random') {
    unset($_SESSION['index_popular_random_seed']);
}
if ($popularSort === 'random') {
    if (!isset($_SESSION['index_popular_random_seed'])) {
        $_SESSION['index_popular_random_seed'] = mt_rand(1, 999999);
    }
    $popularRandomSeed = (int) $_SESSION['index_popular_random_seed'];
} else {
    unset($_SESSION['index_popular_random_seed']);
    $popularRandomSeed = null;
}
$popularPage = max(1, (int) ($_GET['popular_page'] ?? 1));
$popularPerPage = 12;
$popularData = getHomepagePopularProducts($pdo, $popularProductIds, $popularSort, $popularPerPage, ($popularPage - 1) * $popularPerPage, $popularSearch, $popularRandomSeed);
$popularProducts = $popularData['products'];
$popularTotal = $popularData['total'];
$popularTotalPages = max(1, (int) ceil($popularTotal / $popularPerPage));
$productRatings = getProductAverageRatingsBatch($pdo, array_column($popularProducts, 'id'));
if ($popularPage > $popularTotalPages) {
    $popularPage = $popularTotalPages;
    $popularData = getHomepagePopularProducts($pdo, $popularProductIds, $popularSort, $popularPerPage, ($popularPage - 1) * $popularPerPage, $popularSearch, $popularRandomSeed ?? null);
    $popularProducts = $popularData['products'];
    $productRatings = getProductAverageRatingsBatch($pdo, array_column($popularProducts, 'id'));
}
$slides = [];
try {
    $slides = getSliderSlides($pdo);
} catch (PDOException $e) {
    $slides = [];
}

$baseParams = ['popular_sort' => $popularSort];
if ($popularSearch !== '') {
    $baseParams['search'] = $popularSearch;
}

if (!empty($_GET['ajax']) && $_GET['ajax'] === 'popular') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="index-popular-ajax-container">';
    include __DIR__ . '/partials/index_popular_ajax_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     ANA SAYFA SLIDER (BANNER CAROUSEL)
     ============================================
     Otomatik geçiş yapan banner slider'ı
     JavaScript ile kontrol edilir (assets/js/main.js)
-->
<?php if (!empty($slides)): ?>
<div class="main-slider">
    <div class="slider-container">
        <div class="slider-wrapper">
            <?php foreach ($slides as $i => $slide): ?>
            <div class="slider-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="<?php echo normalizeSliderImageUrl($slide['image_url']); ?>" alt="<?php echo sanitize($slide['alt_text'] ?? 'Banner'); ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="slider-controls">
            <button class="slider-prev">‹</button>
            <button class="slider-next">›</button>
        </div>
        <div class="slider-dots">
            <?php foreach ($slides as $i => $slide): ?>
            <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<section class="hero">
    <h1><?php echo sanitize(getSetting($pdo, 'home_hero_title', "ManRoMan'a Hoş Geldiniz")); ?></h1>
    <p><?php echo sanitize(getSetting($pdo, 'home_hero_subtitle', 'Hayal gücünüzün sınırlarını aşan koleksiyonlar, her zevke uygun seçeneklerle sizleri bekliyor.')); ?></p>
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/products.php">
        Hemen Keşfet
    </a>
</section>

<!-- ============================================
     POPÜLER KATEGORİLER BÖLÜMÜ
     ============================================
     Tüm kategorileri grid layout ile gösterir
-->
<section id="urunler">
    <h2><?php echo sanitize(getSetting($pdo, 'home_section_categories_title', 'Popüler Kategoriler')); ?></h2>
    <div class="grid grid-3">
        <?php foreach ($categories as $category): ?>
            <div class="card">
                <h3><?php echo sanitize($category['name']); ?></h3>
                <!-- Kategori detay sayfasına yönlendirme linki -->
                <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo sanitize($category['slug']); ?>">Ürünleri Gör</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============================================
     POPÜLER ÜRÜNLER BÖLÜMÜ
     ============================================
     Admin panelinden seçilen veya tüm ürünler; sıralama ve sayfalama
-->
<section id="populer-urunler">
    <h2>Ürünler</h2>
    <div class="card" style="margin-bottom: 2rem;">
        <form method="get" class="filter-form" action="<?php echo BASE_URL; ?>/index.php#populer-urunler" style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <div>
                <input type="hidden" name="popular_page" value="1">
                <input type="hidden" name="random_refresh" value="1">
                <label for="popular-search" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ara</label>
                <input id="popular-search" name="search" type="text" value="<?php echo sanitize($popularSearch); ?>" placeholder="Ürün ara..." style="width: 100%;">
            </div>
            <div>
                <label for="popular-sort" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sırala</label>
                <select id="popular-sort" name="popular_sort" style="min-width: 180px;">
                    <?php foreach ($popularSortOptions as $val => $label): ?>
                    <option value="<?php echo sanitize($val); ?>" <?php echo $popularSort === $val ? 'selected' : ''; ?>><?php echo sanitize($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button class="btn btn-primary" type="submit" name="filter_submit">Filtrele</button>
            </div>
        </form>
    </div>
    <?php if ($popularSearch !== ''): ?>
    <div id="index-search-results-msg" style="margin-bottom: 1rem; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; border-left: 3px solid var(--primary);">
        <strong>Arama sonuçları:</strong> &quot;<em><?php echo sanitize($popularSearch); ?></em>&quot; için <strong><?php echo $popularTotal; ?></strong> ürün bulundu.
        <a href="<?php echo BASE_URL; ?>/index.php#populer-urunler" style="margin-left: 0.5rem; color: var(--primary);">Temizle</a>
    </div>
    <?php endif; ?>
    <div id="index-popular-ajax-container">
    <div class="grid grid-4">
        <?php foreach ($popularProducts as $product):
            $productRating = $productRatings[$product['id']] ?? null;
            $productRating = $productRating !== null ? (float) $productRating : 0;
            $descriptionLength = 80;
            $showAddToCart = false;
            include __DIR__ . '/partials/product_card.php';
        endforeach; ?>
    </div>
    <?php if ($popularTotalPages > 1): ?>
    <div class="popular-pagination pagination" style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
        <?php
        $showPages = 7;
        $half = (int) floor($showPages / 2);
        $start = max(1, $popularPage - $half);
        $end = min($popularTotalPages, $start + $showPages - 1);
        if ($end - $start + 1 < $showPages) {
            $start = max(1, $end - $showPages + 1);
        }
        ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?<?php echo http_build_query(array_merge($baseParams, ['popular_page' => $i])); ?>#populer-urunler"
               class="btn pagination-link"
               style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $popularPage ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">
            (<?php echo $popularTotal; ?> ürün, sayfa <?php echo $popularPage; ?>/<?php echo $popularTotalPages; ?>)
        </span>
    </div>
    <?php endif; ?>
    </div><!-- #index-popular-ajax-container -->
</section>

<!-- ============================================
     YENİ EKLENEN ÜRÜNLER BÖLÜMÜ
     ============================================
     Son eklenen 8 ürünü gösterir (getLatestProducts fonksiyonu)
-->
<section style="margin-bottom: 3rem;">
    <h2>Yeni Eklenen Ürünler</h2>
    <div class="grid grid-4">
        <?php foreach ($latestProducts as $product):
            $productRating = $latestProductRatings[$product['id']] ?? null;
            $productRating = $productRating !== null ? (float) $productRating : 0;
            $descriptionLength = 100;
            $showAddToCart = false;
            include __DIR__ . '/partials/product_card.php';
        endforeach; ?>
    </div>
</section>

<?php 

include __DIR__ . '/partials/footer.php'; 
?>

