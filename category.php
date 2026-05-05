<?php

require __DIR__ . '/config.php';

logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');

$slug = $_GET['slug'] ?? '';

$category = $slug ? findCategoryBySlug($pdo, $slug) : null;

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Kategori Bulunamadı';
    include __DIR__ . '/partials/header.php';
    echo '<section class="card"><h2>Kategori bulunamadı</h2><p>Aradığınız kategori mevcut değil.</p></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = $category['name'] ?? 'Kategori';
$pageDescription = $category['name'] . ' kategorisindeki ürünler - ManRoMan.';
$canonicalUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/kategori/' . rawurlencode($slug) : '';

$categorySearch = trim($_GET['search'] ?? '');
$sort = isset($_GET['sort']) ? (string) $_GET['sort'] : 'newest';
$categorySortOptions = getProductSortOptions();
if (!isset($categorySortOptions[$sort])) {
    $sort = 'newest';
}
if (isset($_GET['random_refresh']) && ($_GET['sort'] ?? '') === 'random') {
    unset($_SESSION['category_random_seed']);
}
if ($sort === 'random') {
    if (!isset($_SESSION['category_random_seed'])) {
        $_SESSION['category_random_seed'] = mt_rand(1, 999999);
    }
    $categoryRandomSeed = (int) $_SESSION['category_random_seed'];
} else {
    unset($_SESSION['category_random_seed']);
    $categoryRandomSeed = null;
}

$itemsPerPage = 12;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$categoryData = getProductsByCategoryFiltered($pdo, (int) $category['id'], $sort, $itemsPerPage, ($currentPage - 1) * $itemsPerPage, $categorySearch, $categoryRandomSeed);
$products = $categoryData['products'];
$categoryTotal = $categoryData['total'];
$pagination = getPaginationData($currentPage, $categoryTotal, $itemsPerPage);
$productRatings = getProductAverageRatingsBatch($pdo, array_column($products, 'id'));
if ($currentPage > $pagination['total_pages']) {
    $currentPage = max(1, $pagination['total_pages']);
    $categoryData = getProductsByCategoryFiltered($pdo, (int) $category['id'], $sort, $itemsPerPage, ($currentPage - 1) * $itemsPerPage, $categorySearch, $categoryRandomSeed ?? null);
    $products = $categoryData['products'];
    $pagination = getPaginationData($currentPage, $categoryData['total'], $itemsPerPage);
    $productRatings = getProductAverageRatingsBatch($pdo, array_column($products, 'id'));
}

if (!empty($_GET['ajax'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="category-ajax-container">';
    include __DIR__ . '/partials/category_ajax_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<section style="margin-top: 3rem; margin-bottom: 4rem;">
    <!-- ============================================
         BAŞLIK VE FİLTRE/SIRALAMA KONTEYNERİ
         ============================================
         Masaüstünde yan yana, mobilde alt alta
    -->
    <div class="category-header-controls" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <h1 style="margin: 0; flex: 1; min-width: 200px;"><?php echo sanitize($category['name']); ?></h1>
        <div class="category-controls-wrapper" style="flex: 1; min-width: 280px;">
            <div class="card" style="margin-bottom: 1rem;">
                <form method="get" class="filter-form" action="" style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
                    <div>
                        <input type="hidden" name="slug" value="<?php echo sanitize($slug); ?>">
                        <input type="hidden" name="random_refresh" value="1">
                        <label for="category-search" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ara</label>
                        <input id="category-search" name="search" type="text" value="<?php echo sanitize($categorySearch); ?>" placeholder="Ürün ara..." style="width: 100%;">
                    </div>
                    <div>
                        <label for="sort" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sırala</label>
                        <select id="sort" name="sort" style="min-width: 180px;">
                            <?php foreach ($categorySortOptions as $val => $label): ?>
                            <option value="<?php echo sanitize($val); ?>" <?php echo $sort === $val ? 'selected' : ''; ?>><?php echo sanitize($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-primary" type="submit" name="filter_submit">Filtrele</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if ($categorySearch !== ''): ?>
    <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; border-left: 3px solid var(--primary);">
        <strong>Arama sonuçları:</strong> &quot;<em><?php echo sanitize($categorySearch); ?></em>&quot; için <strong><?php echo $categoryTotal; ?></strong> ürün bulundu.
        <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo rawurlencode($slug); ?>" style="margin-left: 0.5rem; color: var(--primary);">Temizle</a>
    </div>
    <?php endif; ?>
    <div id="category-ajax-container">
    <?php if ($products): ?>
        <div class="grid grid-4">
            <?php foreach ($products as $product):
                $productRating = $productRatings[$product['id']] ?? null;
                $productRating = $productRating !== null ? (float) $productRating : 0;
                $descriptionLength = 100;
                $showAddToCart = false;
                include __DIR__ . '/partials/product_card.php';
            endforeach; ?>
        </div>
    <?php else: ?>
        <p>Bu kategoride henüz ürün yok.</p>
    <?php endif; ?>

    <?php if ($pagination['total_pages'] > 1): ?>
        <?php
        $totalP = $pagination['total_pages'];
        $curr = $pagination['current_page'];
        $showPages = 7;
        $half = (int) floor($showPages / 2);
        $start = max(1, $curr - $half);
        $end = min($totalP, $start + $showPages - 1);
        if ($end - $start + 1 < $showPages) {
            $start = max(1, $end - $showPages + 1);
        }
        $categoryPagParams = array_diff_key($_GET, ['random_refresh' => 1]);
        ?>
        <div class="pagination" style="margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a class="btn pagination-link" href="?<?php echo http_build_query(array_merge($categoryPagParams, ['page' => $i])); ?>"
                   style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $curr ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">
                (<?php echo $pagination['total_items']; ?> ürün, sayfa <?php echo $curr; ?>/<?php echo $totalP; ?>)
            </span>
        </div>
    <?php endif; ?>
    </div><!-- #category-ajax-container -->
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

