<?php

require __DIR__ . '/config.php';

logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');

$pageTitle = 'Ürünler';

$search = trim($_GET['search'] ?? '');

$sort = $_GET['sort'] ?? 'newest';

if (isset($_GET['random_refresh']) && ($_GET['sort'] ?? '') === 'random') {
    unset($_SESSION['products_random_seed']);
}
if ($sort === 'random') {
    if (!isset($_SESSION['products_random_seed'])) {
        $_SESSION['products_random_seed'] = mt_rand(1, 999999);
    }
    $randomSeed = (int) $_SESSION['products_random_seed'];
} else {
    unset($_SESSION['products_random_seed']);
    $randomSeed = null;
}

$itemsPerPage = 12;

$currentPage = max(1, (int) ($_GET['page'] ?? 1));

$whereConditions = []; 
$params = []; 

if ($search !== '') {

    $whereConditions[] = '(name LIKE :search OR description LIKE :search OR author LIKE :search)';

    $params['search'] = '%' . $search . '%';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
$sortOptions = getProductSortOptions();
if (!isset($sortOptions[$sort])) {
    $sort = 'newest';
}

$ratingJoin = '';
switch ($sort) {
    case 'newest':
        $orderBy = 'p.id DESC';
        break;
    case 'oldest':
        $orderBy = 'p.id ASC';
        break;
    case 'name_asc':
        $orderBy = 'p.name ASC';
        break;
    case 'name_desc':
        $orderBy = 'p.name DESC';
        break;
    case 'price_asc':
        $orderBy = 'p.price ASC';
        break;
    case 'price_desc':
        $orderBy = 'p.price DESC';
        break;
    case 'rating_desc':
        $ratingJoin = " LEFT JOIN (SELECT product_id, AVG(rating) AS ar FROM comments WHERE status='approved' AND rating IS NOT NULL GROUP BY product_id) r ON p.id = r.product_id";
        $orderBy = '(r.ar IS NULL), r.ar DESC, p.id DESC';
        break;
    case 'rating_asc':
        $ratingJoin = " LEFT JOIN (SELECT product_id, AVG(rating) AS ar FROM comments WHERE status='approved' AND rating IS NOT NULL GROUP BY product_id) r ON p.id = r.product_id";
        $orderBy = '(r.ar IS NULL), r.ar ASC, p.id DESC';
        break;
    case 'random':
        $orderBy = $randomSeed !== null ? 'RAND(' . $randomSeed . ')' : 'RAND()';
        break;
    default:
        $orderBy = 'p.id DESC';
        break;
}

$countQuery = 'SELECT COUNT(*) FROM products ' . $whereClause;
$countStatement = $pdo->prepare($countQuery);
$countStatement->execute($params);
$totalProducts = (int) $countStatement->fetchColumn();

$pagination = getPaginationData($currentPage, $totalProducts, $itemsPerPage);

$offset = $pagination['offset'];

$fromClause = 'products p' . $ratingJoin;
$whereForFrom = $whereClause === '' ? '' : ' WHERE (p.name LIKE :search OR p.description LIKE :search OR p.author LIKE :search)';
$query = 'SELECT p.* FROM ' . $fromClause . $whereForFrom . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';

$statement = $pdo->prepare($query);

foreach ($params as $key => $value) {
    $statement->bindValue(':' . $key, $value);
}

$statement->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);

$statement->execute();

$products = $statement->fetchAll();
$productRatings = getProductAverageRatingsBatch($pdo, array_column($products, 'id'));

if (!empty($_GET['ajax'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="products-ajax-container">';
    include __DIR__ . '/partials/products_ajax_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     ÜRÜN LİSTELEME SAYFASI
     ============================================
     Arama, filtreleme, sıralama ve sayfalama özellikleri
-->
<section style="margin-top: 3rem; margin-bottom: 4rem;">
    <h1 style="margin-top: 0;">Tüm Ürünler</h1>
    
    <!-- ============================================
         ARAMA VE FİLTRELEME FORMU
         ============================================
         Ürün arama ve sıralama işlemleri
    -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="get" class="filter-form" action="" style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <div>
                <input type="hidden" name="random_refresh" value="1">
                <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ara</label>
                <input id="search" name="search" type="text" value="<?php echo sanitize($search); ?>" placeholder="Ürün ara..." style="width: 100%;">
            </div>
            <div>
                <label for="sort" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sırala</label>
                <select id="sort" name="sort" style="min-width: 180px;">
                    <?php foreach ($sortOptions as $val => $label): ?>
                    <option value="<?php echo sanitize($val); ?>" <?php echo $sort === $val ? 'selected' : ''; ?>><?php echo sanitize($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button class="btn btn-primary" type="submit" name="filter_submit">Filtrele</button>
            </div>
        </form>
    </div>

    <!-- ============================================
         ÜRÜN LİSTESİ (AJAX ile güncellenebilir)
         ============================================
    -->
    <div id="products-ajax-container">
        <?php if ($search !== ''): ?>
            <div id="search-results-msg" style="margin-top: 1rem; margin-bottom: 1rem; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; border-left: 3px solid var(--primary);">
                <strong>Arama sonuçları:</strong> "<em><?php echo sanitize($search); ?></em>" için <strong><?php echo $totalProducts; ?></strong> ürün bulundu.
                <a href="<?php echo BASE_URL; ?>/products.php" style="margin-left: 0.5rem; color: var(--primary);">Temizle</a>
            </div>
        <?php endif; ?>
    <?php if ($products): ?>
        <div class="grid grid-4">
            <?php foreach ($products as $product):
                $productRating = $productRatings[$product['id']] ?? null;
                $productRating = $productRating !== null ? (float) $productRating : 0;
                $descriptionLength = 100;
                $showAddToCart = true;
                include __DIR__ . '/partials/product_card.php';
            endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="font-size: 1.2rem; margin-bottom: 1rem;">Ürün bulunamadı</p>
            <?php if ($search !== ''): ?>
                <p>Arama kriterlerinize uygun ürün bulunamadı.</p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary" style="margin-top: 1rem;">Tüm Ürünleri Gör</a>
            <?php else: ?>
                <p>Henüz ürün eklenmemiş.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination" style="margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
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
            ?>
            <?php $pagParams = array_diff_key($_GET, ['random_refresh' => 1]); ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a class="btn pagination-link" href="<?php echo BASE_URL; ?>/products.php?<?php echo http_build_query(array_merge($pagParams, ['page' => $i])); ?>"
                   style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $curr ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">
                (<?php echo $pagination['total_items']; ?> ürün, sayfa <?php echo $curr; ?>/<?php echo $totalP; ?>)
            </span>
        </div>
    <?php endif; ?>
    </div><!-- #products-ajax-container -->
</section>

<?php 

include __DIR__ . '/partials/footer.php'; 
?>

