<?php
require __DIR__ . '/config.php';

if (!canUseCart()) {
    setFlash('error', 'Siparişlerinizi görüntülemek için giriş yapmanız gerekiyor.');
    redirect('login.php?redirect=' . urlencode('my_orders.php'));
}

$pageTitle = 'Siparişlerim';
$user = getCheckoutUser($pdo);
if (!$user) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    if ($orderId) {
        
        $orderCheck = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND customer_email = :email AND status = :status');
        $orderCheck->execute([
            'id' => $orderId,
            'email' => $user['email'],
            'status' => 'Hazırlanıyor'
        ]);
        $orderToCancel = $orderCheck->fetch();
        
        if ($orderToCancel) {
            $updateStmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $updateStmt->execute([
                'status' => 'İptal',
                'id' => $orderId
            ]);
            setFlash('success', 'Siparişiniz başarıyla iptal edildi.');
            redirect('my_orders.php');
        } else {
            setFlash('error', 'Bu sipariş iptal edilemez. Sadece "Hazırlanıyor" durumundaki siparişler iptal edilebilir.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $selectedAddressId = $_POST['selected_address'] ?? '';
    $newAddressTitle = trim($_POST['new_address_title'] ?? '');
    $newAddress = trim($_POST['new_address'] ?? '');
    
    if ($orderId) {
        
        $orderCheck = $pdo->prepare('SELECT * FROM orders WHERE id = :id AND customer_email = :email AND status = :status');
        $orderCheck->execute([
            'id' => $orderId,
            'email' => $user['email'],
            'status' => 'Hazırlanıyor'
        ]);
        $orderToUpdate = $orderCheck->fetch();
        
        if ($orderToUpdate) {
            $address = '';
            $errors = [];
            
            if ($selectedAddressId === 'new') {
                if ($newAddressTitle === '') {
                    $errors[] = 'Adres başlığı zorunludur (örn: Ev, İş).';
                }
                if ($newAddress === '') {
                    $errors[] = 'Adres bilgisi zorunludur.';
                }
                if (!$errors) {
                    $address = $newAddress;
                    if (userIsLoggedIn()) {
                        try {
                            $nextId = getNextAvailableId($pdo, 'user_addresses');
                            $insertStatement = $pdo->prepare('INSERT INTO user_addresses (id, user_id, address_title, address, is_default) VALUES (:id, :user_id, :title, :address, 0)');
                            $insertStatement->execute([
                                'id' => $nextId,
                                'user_id' => $user['id'],
                                'title' => $newAddressTitle,
                                'address' => $newAddress
                            ]);
                            try { updateTableAutoIncrement($pdo, 'user_addresses'); } catch (Exception $e) {}
                        } catch (PDOException $e) {
                            $address = $newAddress;
                        }
                    } elseif (adminIsLoggedIn()) {
                        try {
                            $nextId = getNextAvailableId($pdo, 'admin_addresses');
                            $insertStatement = $pdo->prepare('INSERT INTO admin_addresses (id, admin_id, address_title, address, is_default) VALUES (:id, :admin_id, :title, :address, 0)');
                            $insertStatement->execute([
                                'id' => $nextId,
                                'admin_id' => $user['id'],
                                'title' => $newAddressTitle,
                                'address' => $newAddress
                            ]);
                            try { updateTableAutoIncrement($pdo, 'admin_addresses'); } catch (Exception $e) {}
                        } catch (PDOException $e) {}
                    }
                }
            } elseif ($selectedAddressId !== '' && is_numeric($selectedAddressId)) {
                $addressFound = false;
                if (userIsLoggedIn()) {
                    try {
                        $selectedAddressStatement = $pdo->prepare('SELECT address FROM user_addresses WHERE id = :id AND user_id = :user_id LIMIT 1');
                        $selectedAddressStatement->execute(['id' => $selectedAddressId, 'user_id' => $user['id']]);
                        $selectedAddressData = $selectedAddressStatement->fetch();
                        if ($selectedAddressData) {
                            $address = $selectedAddressData['address'];
                            $addressFound = true;
                        }
                    } catch (PDOException $e) {}
                } elseif (adminIsLoggedIn()) {
                    try {
                        $selectedAddressStatement = $pdo->prepare('SELECT address FROM admin_addresses WHERE id = :id AND admin_id = :admin_id LIMIT 1');
                        $selectedAddressStatement->execute(['id' => $selectedAddressId, 'admin_id' => $user['id']]);
                        $selectedAddressData = $selectedAddressStatement->fetch();
                        if ($selectedAddressData) {
                            $address = $selectedAddressData['address'];
                            $addressFound = true;
                        }
                    } catch (PDOException $e) {}
                }
                if (!$addressFound) {
                    $errors[] = 'Seçilen adres bulunamadı.';
                }
            } else {
                $profileUrl = adminIsLoggedIn() ? (BASE_URL . '/admin_profile.php') : (BASE_URL . '/profile.php');
                $errors[] = 'Lütfen bir adres seçin veya <a href="' . $profileUrl . '" style="color: var(--primary); text-decoration: underline;">yeni adres ekleyin</a>.';
            }
            
            if (!$errors && $address) {
                $updateStmt = $pdo->prepare('UPDATE orders SET address = :address WHERE id = :id');
                $updateStmt->execute([
                    'address' => $address,
                    'id' => $orderId
                ]);
                setFlash('success', 'Sipariş adresi başarıyla güncellendi.');
                redirect('my_orders.php');
            } else {
                setFlash('error', implode('<br>', $errors));
            }
        } else {
            setFlash('error', 'Bu siparişin adresi değiştirilemez. Sadece "Hazırlanıyor" durumundaki siparişlerin adresi değiştirilebilir.');
        }
    }
}

$orders = $pdo->prepare('SELECT * FROM orders WHERE customer_email = :email ORDER BY id DESC');
$orders->execute(['email' => $user['email']]);
$userOrders = $orders->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h1>Siparişlerim</h1>
    
    <?php if ($message = getFlash('success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($message = getFlash('error')): ?>
        <div class="alert alert-error"><?php 
            
            if (strpos($message, '<a') !== false) {
                echo $message;
            } else {
                echo sanitize($message);
            }
        ?></div>
    <?php endif; ?>
    
    <?php if ($userOrders): ?>
        <?php foreach ($userOrders as $order): ?>
            <article class="card" style="margin-bottom: 1.5rem; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0;">Sipariş #<?php echo $order['id']; ?></h3>
                        <p style="margin: 0.25rem 0; color: var(--muted);">
                            <strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                        </p>
                        <p style="margin: 0.25rem 0;">
                            <strong>Toplam:</strong> <span style="color: var(--primary); font-weight: 600; font-size: 1.1rem;"><?php echo number_format((float) $order['total_amount'], 2); ?> ₺</span>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: inline-block; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; 
                            background: <?php 
                                echo $order['status'] === 'Tamamlandı' ? '#d1fae5' : 
                                    ($order['status'] === 'Kargolandı' ? '#dbeafe' : 
                                    ($order['status'] === 'İptal' ? '#fee2e2' : '#fef3c7')); 
                            ?>; 
                            color: <?php 
                                echo $order['status'] === 'Tamamlandı' ? '#059669' : 
                                    ($order['status'] === 'Kargolandı' ? '#2563eb' : 
                                    ($order['status'] === 'İptal' ? '#dc2626' : '#d97706')); 
                            ?>;">
                            <?php echo sanitize($order['status']); ?>
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text);">Teslimat Bilgileri</h4>
                    <p style="margin: 0.25rem 0;"><strong>Ad Soyad:</strong> <?php echo sanitize($order['customer_name']); ?></p>
                    <p style="margin: 0.25rem 0;"><strong>E-posta:</strong> <?php echo sanitize($order['customer_email']); ?></p>
                    <?php if (!empty($order['customer_phone'])): ?>
                        <p style="margin: 0.25rem 0;"><strong>Telefon:</strong> <?php echo sanitize($order['customer_phone']); ?></p>
                    <?php endif; ?>
                    <p style="margin: 0.25rem 0;"><strong>Adres:</strong></p>
                    <p style="margin: 0.25rem 0; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; white-space: pre-wrap;"><?php echo sanitize($order['address']); ?></p>
                    
                    <?php if ($order['status'] === 'Hazırlanıyor'): ?>
                        <?php
                        $userAddresses = [];
                        if (userIsLoggedIn()) {
                            try {
                                $addressesStatement = $pdo->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY id DESC');
                                $addressesStatement->execute(['user_id' => $user['id']]);
                                $userAddresses = $addressesStatement->fetchAll();
                            } catch (PDOException $e) {}
                        } elseif (adminIsLoggedIn()) {
                            try {
                                $addressesStatement = $pdo->prepare('SELECT * FROM admin_addresses WHERE admin_id = :admin_id ORDER BY id DESC');
                                $addressesStatement->execute(['admin_id' => $user['id']]);
                                $userAddresses = $addressesStatement->fetchAll();
                            } catch (PDOException $e) {}
                        }
                        ?>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                            <h4 style="margin: 0 0 0.75rem 0; color: var(--text); font-size: 1rem;">Adresi Değiştir</h4>
                            <form method="post" id="updateAddressForm-<?php echo $order['id']; ?>">
                                <input type="hidden" name="update_address" value="1">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                
                                <?php if (!empty($userAddresses)): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kayıtlı Adreslerden Seç:</label>
                                        <?php foreach ($userAddresses as $addr): ?>
                                            <label class="address-option" style="display: block; padding: 0.75rem; margin-bottom: 0.5rem; border: 2px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                                                <input type="radio" name="selected_address" value="<?php echo $addr['id']; ?>" style="margin-right: 0.5rem;" onchange="toggleNewAddress<?php echo $order['id']; ?>(false)">
                                                <strong><?php echo sanitize($addr['address_title']); ?></strong>
                                                <?php if ($addr['is_default']): ?>
                                                    <span style="color: var(--primary); font-size: 0.85rem;">(Varsayılan)</span>
                                                <?php endif; ?>
                                                <div style="margin-top: 0.25rem; color: var(--muted); font-size: 0.9rem; white-space: pre-wrap;"><?php echo sanitize($addr['address']); ?></div>
                                            </label>
                                        <?php endforeach; ?>
                                        
                                        <label class="address-option" style="display: block; padding: 0.75rem; margin-bottom: 0.5rem; border: 2px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                                            <input type="radio" name="selected_address" value="new" style="margin-right: 0.5rem;" onchange="toggleNewAddress<?php echo $order['id']; ?>(true)">
                                            <strong>Yeni Adres Ekle</strong>
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="selected_address" value="new">
                                <?php endif; ?>
                                
                                <div id="newAddressFields-<?php echo $order['id']; ?>" style="<?php echo empty($userAddresses) ? '' : 'display: none;'; ?> margin-bottom: 1rem; padding: 1rem; background: var(--light); border-radius: 0.5rem;">
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                        Adres Başlığı <span style="color: #dc2626;">*</span>
                                        <input type="text" name="new_address_title" value="<?php echo sanitize($_POST['new_address_title'] ?? ''); ?>" placeholder="Örn: Ev, İş" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid var(--border); border-radius: 0.25rem;" required>
                                    </label>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                        Adres <span style="color: #dc2626;">*</span>
                                        <textarea name="new_address" rows="4" placeholder="Adres bilgilerinizi giriniz..." style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid var(--border); border-radius: 0.25rem; resize: vertical;" required><?php echo sanitize($_POST['new_address'] ?? ''); ?></textarea>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn" style="background: var(--primary); color: white;">Adresi Güncelle</button>
                            </form>
                            
                            <script>
                            function toggleNewAddress<?php echo $order['id']; ?>(show) {
                                const fields = document.getElementById('newAddressFields-<?php echo $order['id']; ?>');
                                if (fields) {
                                    fields.style.display = show ? 'block' : 'none';
                                    if (show) {
                                        fields.querySelector('input[name="new_address_title"]').required = true;
                                        fields.querySelector('textarea[name="new_address"]').required = true;
                                    } else {
                                        fields.querySelector('input[name="new_address_title"]').required = false;
                                        fields.querySelector('textarea[name="new_address"]').required = false;
                                    }
                                }
                            }
                            
                            // Radio button seçimine göre stil güncelle
                            document.querySelectorAll('#updateAddressForm-<?php echo $order['id']; ?> input[type="radio"]').forEach(radio => {
                                radio.addEventListener('change', function() {
                                    document.querySelectorAll('#updateAddressForm-<?php echo $order['id']; ?> .address-option').forEach(option => {
                                        option.style.borderColor = 'var(--border)';
                                        option.style.background = '';
                                    });
                                    if (this.checked) {
                                        this.closest('.address-option').style.borderColor = 'var(--primary)';
                                        this.closest('.address-option').style.background = 'rgba(var(--primary-rgb), 0.1)';
                                    }
                                });
                            });
                            </script>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                $itemsStatement = $pdo->prepare('SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE order_id = :order_id');
                $itemsStatement->execute(['order_id' => $order['id']]);
                $items = $itemsStatement->fetchAll();
                ?>
                <?php if ($items): ?>
                    <div>
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--text);">Sipariş Detayları</h4>
                        <div class="table-responsive">
                        <table style="width: 100%;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <th style="text-align: left; padding: 0.75rem 0;">Ürün</th>
                                    <th style="text-align: center; padding: 0.75rem 0;">Adet</th>
                                    <th style="text-align: right; padding: 0.75rem 0;">Birim Fiyat</th>
                                    <th style="text-align: right; padding: 0.75rem 0;">Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 0.75rem 0;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if (!empty($item['image_url'])): ?>
                                                    <img src="<?php echo sanitize(normalizeImageUrl($item['image_url'])); ?>" alt="<?php echo sanitize($item['name']); ?>" loading="lazy" decoding="async" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.25rem;">
                                                <?php endif; ?>
                                                <span><?php echo sanitize($item['name']); ?></span>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding: 0.75rem 0;"><?php echo (int) $item['quantity']; ?></td>
                                        <td style="text-align: right; padding: 0.75rem 0;"><?php echo number_format((float) $item['unit_price'], 2); ?> ₺</td>
                                        <td style="text-align: right; padding: 0.75rem 0; font-weight: 600;">
                                            <?php echo number_format((float) ($item['unit_price'] * $item['quantity']), 2); ?> ₺
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right; padding: 0.75rem 0; font-weight: 600;">Genel Toplam:</td>
                                    <td style="text-align: right; padding: 0.75rem 0; font-weight: 700; font-size: 1.1rem; color: var(--primary);">
                                        <?php echo number_format((float) $order['total_amount'], 2); ?> ₺
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($order['status'] === 'Hazırlanıyor'): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                        <form method="post" onsubmit="return confirm('Bu siparişi iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                            <input type="hidden" name="cancel_order" value="1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button class="btn" type="submit" style="background: #dc2626; color: white;">Siparişi İptal Et</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <p style="font-size: 1.2rem; color: var(--muted); margin-bottom: 1rem;">Henüz siparişiniz bulunmuyor.</p>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary">Ürünleri Keşfet</a>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

