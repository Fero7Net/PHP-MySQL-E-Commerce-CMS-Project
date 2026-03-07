<?php

?>
<?php if ($search !== ''): ?>
<div id="search-results-msg" style="margin-top: 1rem; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; border-left: 3px solid var(--primary);">
    <strong>Arama sonuçları:</strong> "<em><?php echo sanitize($search); ?></em>" için <strong><?php echo $totalProducts; ?></strong> ürün bulundu.
    <a href="<?php echo BASE_URL; ?>/products.php" style="margin-left: 0.5rem; color: var(--primary);">Temizle</a>
</div>
<?php endif; ?>

<?php if ($products): ?>
<div class="grid grid-4" id="products-grid">
    <?php foreach ($products as $product):
        $productRating = $productRatings[$product['id']] ?? null;
        $productRating = $productRating !== null ? (float) $productRating : 0;
        $descriptionLength = 100;
        $showAddToCart = true;
        include __DIR__ . '/product_card.php';
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
<?php
$totalP = $pagination['total_pages'];
$curr = $pagination['current_page'];
$showPages = 7;
$half = (int) floor($showPages / 2);
$start = max(1, $curr - $half);
$end = min($totalP, $start + $showPages - 1);
if ($end - $start + 1 < $showPages) { $start = max(1, $end - $showPages + 1); }
?>
<div class="products-pagination pagination" style="margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;" data-base-url="<?php echo BASE_URL; ?>/products.php">
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a class="btn pagination-link" href="<?php echo BASE_URL; ?>/products.php?<?php echo http_build_query(array_merge(array_diff_key($_GET, ['ajax' => 1, 'random_refresh' => 1]), ['page' => $i])); ?>" data-page="<?php echo $i; ?>"
           style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $curr ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
    <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">(<?php echo $pagination['total_items']; ?> ürün, sayfa <?php echo $curr; ?>/<?php echo $totalP; ?>)</span>
</div>
<?php endif; ?>
