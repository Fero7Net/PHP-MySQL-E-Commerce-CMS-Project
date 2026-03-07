<?php
/**
 * Ürün kartı partial - tüm sayfalarda tutarlı görünüm
 * Gerekli: $product, $productRating (float|null), $descriptionLength (int, default 100), $showAddToCart (bool)
 */
if (!isset($product) || !is_array($product)) {
    return;
}
$productRating = $productRating ?? null;
$descriptionLength = $descriptionLength ?? 100;
$showAddToCart = $showAddToCart ?? false;
$imageUrl = normalizeImageUrl($product['image_url'] ?? null);
?>
<div class="card product-card-fixed">
    <a href="<?php echo BASE_URL; ?>/product.php?slug=<?php echo sanitize($product['slug']); ?>" style="text-decoration: none; display: block;">
        <div class="card-cover">
            <img src="<?php echo sanitize($imageUrl); ?>" alt="<?php echo sanitize($product['name']); ?>" class="product-image" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/img/icon.png'; this.style.opacity='0.3';">
        </div>
    </a>
    <h3><a href="<?php echo BASE_URL; ?>/product.php?slug=<?php echo sanitize($product['slug']); ?>" style="text-decoration: none; color: inherit;"><?php echo sanitize($product['name']); ?></a></h3>
    <?php if (!empty($product['author'])): ?>
        <p style="color: var(--muted); font-size: 0.9rem; margin: 0.25rem 0; font-style: italic;">Yazar: <?php echo sanitize($product['author']); ?></p>
    <?php endif; ?>
    <p class="product-rating" style="margin: 0.25rem 0; font-size: 0.95rem;"><?php echo renderProductRating($productRating); ?></p>
    <p class="price"><?php echo number_format((float) $product['price'], 2); ?> ₺</p>
    <p><?php echo mb_strimwidth(sanitize($product['description'] ?? ''), 0, $descriptionLength, '...'); ?></p>
    <div style="margin-top: 1rem;">
        <?php if ($showAddToCart && canUseCart()): ?>
            <form class="add-to-cart-form" method="post" style="display: grid; grid-template-columns: 70px 1fr; gap: 0.5rem; align-items: stretch;">
                <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                <?php echo csrf_field(); ?>
                <input type="number" name="quantity" value="1" min="1" max="99" style="padding: 0.75rem; text-align: center; border: 1px solid var(--border); border-radius: 0.5rem; font-size: 0.95rem;">
                <button type="submit" name="add_to_cart" value="1" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">🛒 Sepete Ekle</button>
            </form>
        <?php elseif ($showAddToCart): ?>
            <div style="padding: 0.75rem; background: var(--light); border: 1px solid var(--border); border-radius: 0.5rem; text-align: center;">
                <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode(get_safe_redirect($_SERVER['REQUEST_URI'] ?? 'index.php')); ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">Giriş yapın</a> sepete eklemek için
            </div>
        <?php else: ?>
            <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/product.php?slug=<?php echo sanitize($product['slug']); ?>">İncele</a>
        <?php endif; ?>
    </div>
</div>
