<?php

?>
<div class="grid grid-4" id="popular-products-grid">
    <?php foreach ($popularProducts as $product):
        $productRating = $productRatings[$product['id']] ?? null;
        $productRating = $productRating !== null ? (float) $productRating : 0;
        $descriptionLength = 80;
        $showAddToCart = false;
        include __DIR__ . '/product_card.php';
    endforeach; ?>
</div>
<?php if ($popularTotalPages > 1): ?>
<?php
$baseParams = ['popular_sort' => $popularSort];
$showPages = 7;
$half = (int) floor($showPages / 2);
$start = max(1, $popularPage - $half);
$end = min($popularTotalPages, $start + $showPages - 1);
if ($end - $start + 1 < $showPages) { $start = max(1, $end - $showPages + 1); }
?>
<div class="popular-pagination pagination" style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a class="btn pagination-link" href="<?php echo BASE_URL; ?>/index.php?<?php echo http_build_query(array_merge($baseParams, ['popular_page' => $i])); ?>#populer-urunler" data-page="<?php echo $i; ?>"
           style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $popularPage ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
    <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">(<?php echo $popularTotal; ?> ürün, sayfa <?php echo $popularPage; ?>/<?php echo $popularTotalPages; ?>)</span>
</div>
<?php endif; ?>
