<?php

?>
<?php if ($products): ?>
<div class="grid grid-4" id="category-products-grid">
    <?php foreach ($products as $product):
        $productRating = $productRatings[$product['id']] ?? null;
        $productRating = $productRating !== null ? (float) $productRating : 0;
        $descriptionLength = 100;
        $showAddToCart = false;
        include __DIR__ . '/product_card.php';
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
if ($end - $start + 1 < $showPages) { $start = max(1, $end - $showPages + 1); }
?>
<div class="category-pagination pagination" style="margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a class="btn pagination-link" href="?<?php echo http_build_query(array_merge(array_diff_key($_GET, ['ajax' => 1, 'random_refresh' => 1]), ['page' => $i])); ?>" data-page="<?php echo $i; ?>"
           style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $curr ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
    <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">(<?php echo $pagination['total_items']; ?> ürün, sayfa <?php echo $curr; ?>/<?php echo $totalP; ?>)</span>
</div>
<?php endif; ?>
