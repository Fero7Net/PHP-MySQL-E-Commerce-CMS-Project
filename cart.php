<?php

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        setFlash('error', 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
        redirect(BASE_URL . '/cart.php');
    }
    foreach ($_POST['quantities'] ?? [] as $productId => $quantity) {
        updateCartQuantity((int) $productId, (int) $quantity);
    }

    if (isset($_POST['clear_cart'])) {
        clearCart();
    }

    setFlash('success', 'Sepet güncellendi.');
    redirect('cart.php');
}

$pageTitle = 'Sepetim';
$cartItems = getCartItems($pdo);
$cartTotal = calculateCartTotal($cartItems);

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h1>Sepet</h1>
    <?php if ($message = getFlash('success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <?php if (!$cartItems): ?>
        <div style="text-align: center; padding: 3rem 0;">
            <p style="font-size: 1.2rem; margin-bottom: 1rem;">Sepetiniz boş</p>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Alışverişe Devam Et</a>
        </div>
    <?php else: ?>
        <form method="post">
            <?php echo csrf_field(); ?>
            <table>
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Adet</th>
                        <th>Birim Fiyat</th>
                        <th>Toplam</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cart-items-tbody">
                    <?php foreach ($cartItems as $item): ?>
                        <tr data-product-id="<?php echo (int) $item['id']; ?>">
                            <td><?php echo sanitize($item['name']); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0">
                            </td>
                            <td><?php echo number_format((float) $item['price'], 2); ?> ₺</td>
                            <td class="line-total"><?php echo number_format((float) $item['line_total'], 2); ?> ₺</td>
                            <td>
                                <button type="button" class="btn btn-remove-cart" data-product-id="<?php echo (int) $item['id']; ?>" title="Sepetten Kaldır">🗑️ Kaldır</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="price" id="cart-total-display">Genel Toplam: <?php echo number_format($cartTotal, 2); ?> ₺</p>
            <div class="actions">
                <button class="btn" type="submit">Sepeti Güncelle</button>
                <button class="btn" type="submit" name="clear_cart" value="1">Sepeti Boşalt</button>
                <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/checkout.php">Satın Al</a>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php if ($cartItems): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-remove-cart').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var productId = this.getAttribute('data-product-id');
            if (!productId) return;
            var row = this.closest('tr');
            var originalText = this.textContent;
            this.disabled = true;
            this.textContent = '...';

            var formData = new FormData();
            formData.append('product_id', productId);
            if (window.CSRF_TOKEN) formData.append('_csrf_token', window.CSRF_TOKEN);

            fetch(window.BASE_URL + '/cart_remove_item.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    row.remove();
                    var totalEl = document.getElementById('cart-total-display');
                    if (totalEl) totalEl.textContent = 'Genel Toplam: ' + parseFloat(data.cart_total).toFixed(2) + ' ₺';
                    var badge = document.querySelector('.cart-badge');
                    if (badge) badge.textContent = data.cart_count;
                    if (data.cart_empty) window.location.reload();
                } else {
                    btn.disabled = false;
                    btn.textContent = originalText;
                    if (data.redirect) window.location.href = data.redirect;
                    else alert(data.message || 'Bir hata oluştu.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = originalText;
                alert('Bir hata oluştu.');
            });
        });
    });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>

