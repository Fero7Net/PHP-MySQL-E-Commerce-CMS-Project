<?php

?>
<div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 1.5rem;">
    <span style="font-weight: 600; margin-right: 0.25rem;">Duruma göre:</span>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn admin-orders-filter-link <?php echo $filterStatus === 'all' ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.75rem; font-size: 0.9rem;">Tümü</a>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php?status=<?php echo urlencode('Hazırlanıyor'); ?>" class="btn admin-orders-filter-link <?php echo $filterStatus === 'Hazırlanıyor' ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.75rem; font-size: 0.9rem;">Hazırlanıyor</a>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php?status=<?php echo urlencode('Kargolandı'); ?>" class="btn admin-orders-filter-link <?php echo $filterStatus === 'Kargolandı' ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.75rem; font-size: 0.9rem;">Kargolandı</a>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php?status=<?php echo urlencode('Tamamlandı'); ?>" class="btn admin-orders-filter-link <?php echo $filterStatus === 'Tamamlandı' ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.75rem; font-size: 0.9rem;">Tamamlandı</a>
    <a href="<?php echo BASE_URL; ?>/admin/orders.php?status=<?php echo urlencode('İptal'); ?>" class="btn admin-orders-filter-link <?php echo $filterStatus === 'İptal' ? 'btn-primary' : ''; ?>" style="padding: 0.4rem 0.75rem; font-size: 0.9rem;">İptal</a>
</div>

<?php if ($orders): ?>
    <?php foreach ($orders as $order): ?>
        <?php
        $itemsStatement = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE order_id = :order_id');
        $itemsStatement->execute(['order_id' => $order['id']]);
        $items = $itemsStatement->fetchAll();
        ?>
        <article class="card" style="margin-bottom:1rem;">
            <h3>#<?php echo $order['id']; ?> - <?php echo sanitize($order['customer_name']); ?></h3>
            <p>Tutar: <?php echo number_format((float) $order['total_amount'], 2); ?> ₺</p>
            <p>Durum: <strong><?php echo sanitize($order['status']); ?></strong></p>
            <p>Tarih: <?php echo $order['created_at']; ?></p>
            <p>E-posta: <?php echo sanitize($order['customer_email']); ?> | Telefon: <?php echo sanitize($order['customer_phone']); ?></p>
            <p>Adres: <?php echo nl2br(sanitize($order['address'])); ?></p>
            <?php if ($items): ?>
                <table>
                    <thead><tr><th>Ürün</th><th>Adet</th><th>Birim Fiyat</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo sanitize($item['name']); ?></td>
                                <td><?php echo (int) $item['quantity']; ?></td>
                                <td><?php echo number_format((float) $item['unit_price'], 2); ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap; align-items: flex-end;">
                <form method="post" style="flex: 1; min-width: 200px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="redirect_filter" value="<?php echo sanitize($filterStatus); ?>">
                    <label for="status-<?php echo $order['id']; ?>">Durum Güncelle</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <select id="status-<?php echo $order['id']; ?>" name="status" style="flex: 1;">
                            <?php foreach (['Hazırlanıyor', 'Kargolandı', 'Tamamlandı', 'İptal'] as $s): ?>
                                <option value="<?php echo sanitize($s); ?>" <?php echo $s === $order['status'] ? 'selected' : ''; ?>><?php echo sanitize($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Kaydet</button>
                    </div>
                </form>
                <form method="post" style="display: inline-block;" onsubmit="return confirm('Bu siparişi silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="redirect_filter" value="<?php echo sanitize($filterStatus); ?>">
                    <button class="btn" type="submit" style="background: #dc2626; color: white; white-space: nowrap;">Sil</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <p>Henüz sipariş yok.</p>
<?php endif; ?>
