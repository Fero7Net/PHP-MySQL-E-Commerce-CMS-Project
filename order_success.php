<?php

require __DIR__ . '/config.php';

$pageTitle = 'Sipariş Başarılı';

if (!canUseCart()) {
    setFlash('error', 'Sipariş detaylarını görüntülemek için giriş yapmanız gerekiyor.');
    redirect(BASE_URL . '/login.php?redirect=' . urlencode('order_success.php' . ($_GET['id'] ? '?id=' . (int) $_GET['id'] : '')));
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$statement = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
$statement->execute(['id' => $orderId]);
$order = $statement->fetch();

if (!$order) {
    redirect(BASE_URL . '/index.php');
}

$currentUser = getCheckoutUser($pdo);
$orderEmail = strtolower(trim((string) ($order['customer_email'] ?? '')));
$userEmail = $currentUser ? strtolower(trim((string) ($currentUser['email'] ?? ''))) : '';
if ($orderEmail === '' || $userEmail === '' || $orderEmail !== $userEmail) {
    redirect(BASE_URL . '/index.php');
}

$itemsStatement = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE order_id = :orderId');

$itemsStatement->execute(['orderId' => $orderId]);

$items = $itemsStatement->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     SİPARİŞ BAŞARI MESAJI
     ============================================
     Kullanıcıya sipariş bilgilerini gösterir
-->
<section class="card">
    <h1>Teşekkürler!</h1>
    
    <!-- Müşteri adı ile kişiselleştirilmiş mesaj (XSS koruması için sanitize) -->
    <p><?php echo sanitize($order['customer_name']); ?>, siparişiniz başarıyla oluşturuldu.</p>
    
    <!-- Sipariş numarası (güçlü vurgu ile) -->
    <p>Sipariş Numaranız: <strong>#<?php echo $orderId; ?></strong></p>
    
    <!-- Ürünler başlığı -->
    <h3>Ürünler</h3>
    <ul>
        <!-- Her sipariş kalemi için bir liste elemanı oluştur -->
        <?php foreach ($items as $item): ?>
            <li>
                <!-- Ürün adı (XSS koruması için sanitize) -->
                <?php echo sanitize($item['name']); ?> 
                <!-- Ürün miktarı -->
                x <?php echo $item['quantity']; ?> 
                <!-- Birim fiyat (2 ondalık basamak, Türk Lirası formatı) -->
                - <?php echo number_format((float) $item['unit_price'], 2); ?> ₺
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Toplam tutar (fiyat stili ile vurgulanmış) -->
    <p class="price">Toplam: <?php echo number_format((float) $order['total_amount'], 2); ?> ₺</p>
    
    <!-- Ana sayfaya dön butonu -->
    <a class="btn" href="<?php echo BASE_URL; ?>/index.php">Alışverişe Devam Et</a>
</section>

<?php 

include __DIR__ . '/partials/footer.php'; 
?>

