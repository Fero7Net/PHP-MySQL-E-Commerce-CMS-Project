<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['low_stock_threshold'])) {
    if (!validate_csrf()) {
        setFlash('admin_error', 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
    } else {
    $newThreshold = (int) $_POST['low_stock_threshold'];
    if ($newThreshold >= 0 && $newThreshold <= 9999) {
        setSetting($pdo, 'low_stock_threshold', (string) $newThreshold);
        setFlash('admin_success', 'Düşük stok eşiği güncellendi.');
    }
    }
    redirect('dashboard.php');
}

$pageCount = (int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$commentCount = (int) $pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$siteUserCount = (int) $pdo->query('SELECT COUNT(*) FROM site_users')->fetchColumn();

$recentOrders = $pdo->query('SELECT id, customer_name, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();

$lowStockThreshold = (int) getSetting($pdo, 'low_stock_threshold', '5');
$lowStockThreshold = max(0, min(9999, $lowStockThreshold)); 

$lowStockStmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE stock < :threshold ORDER BY stock ASC');
$lowStockStmt->execute(['threshold' => $lowStockThreshold]);
$lowStockProducts = $lowStockStmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<section>
    <h1>Genel Bakış</h1>
    <div class="grid grid-3">
        <div class="card">
            <h3>Sayfalar</h3>
            <p class="price"><?php echo $pageCount; ?></p>
        </div>
        <div class="card">
            <h3>Kategoriler</h3>
            <p class="price"><?php echo $categoryCount; ?></p>
        </div>
        <div class="card">
            <h3>Ürünler</h3>
            <p class="price"><?php echo $productCount; ?></p>
        </div>
        <div class="card">
            <h3>Siparişler</h3>
            <p class="price"><?php echo $orderCount; ?></p>
        </div>
        <div class="card">
            <h3>Yorumlar</h3>
            <p class="price"><?php echo $commentCount; ?></p>
        </div>
        <div class="card">
            <h3>Site Kullanıcıları</h3>
            <p class="price"><?php echo $siteUserCount; ?></p>
        </div>
    </div>
</section>

<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem;">
        <h2 style="margin: 0;">Düşük Stoklu Ürünler (Stok &lt; <?php echo $lowStockThreshold; ?>)</h2>
        <form method="post" style="display: flex; align-items: center; gap: 0.5rem;">
            <?php echo csrf_field(); ?>
            <label for="low_stock_threshold" style="margin: 0; font-weight: 600;">Eşik:</label>
            <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo $lowStockThreshold; ?>" min="0" max="9999" style="width: 70px; padding: 0.4rem;">
            <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
        </form>
    </div>
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    <?php if ($lowStockProducts): ?>
        <table>
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Stok</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $p): ?>
                    <tr>
                        <td><?php echo sanitize($p['name']); ?></td>
                        <td><strong><?php echo (int) $p['stock']; ?></strong></td>
                        <td><a href="<?php echo BASE_URL; ?>/admin/products.php?edit_id=<?php echo (int) $p['id']; ?>" class="btn btn-sm">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Stoğu <?php echo $lowStockThreshold; ?>'ten az olan ürün yok.</p>
    <?php endif; ?>
</section>

<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h2>Son Siparişler</h2>
    <?php if ($recentOrders): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Müşteri</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo sanitize($order['customer_name']); ?></td>
                        <td><?php echo number_format((float) $order['total_amount'], 2); ?> ₺</td>
                        <td><?php echo sanitize($order['status']); ?></td>
                        <td><?php echo $order['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Henüz sipariş yok.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

