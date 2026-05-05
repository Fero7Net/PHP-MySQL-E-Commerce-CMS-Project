<?php

require __DIR__ . '/config.php';

if (!canUseCart()) {
    setFlash('error', 'Sipariş vermek için giriş yapmanız gerekiyor.');
    redirect('login.php?redirect=' . urlencode('checkout.php'));
}

$pageTitle = 'Ödeme';
$cartItems = getCartItems($pdo);
if (!$cartItems) {
    redirect('cart.php');
}

$user = getCheckoutUser($pdo);
if (!$user) {
    setFlash('error', 'Kullanıcı bilgileri bulunamadı.');
    redirect('login.php');
}

$userAddresses = [];
if (userIsLoggedIn() && isset($user['id'])) {
    try {
        $addressesStatement = $pdo->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY id DESC');
        $addressesStatement->execute(['user_id' => $user['id']]);
        $userAddresses = $addressesStatement->fetchAll();
    } catch (PDOException $e) {
        if (!empty($user['address'])) {
            $userAddresses = [[
                'id' => 'old',
                'address_title' => 'Ev Adresi',
                'address' => $user['address'],
                'is_default' => 1
            ]];
        }
    }
} elseif (adminIsLoggedIn() && isset($user['id'])) {
    try {
        $addressesStatement = $pdo->prepare('SELECT * FROM admin_addresses WHERE admin_id = :admin_id ORDER BY id DESC');
        $addressesStatement->execute(['admin_id' => $user['id']]);
        $userAddresses = $addressesStatement->fetchAll();
    } catch (PDOException $e) {
        
    }
}

$errors = [];
$selectedAddressId = $_POST['selected_address'] ?? '';
$newAddressTitle = trim($_POST['new_address_title'] ?? '');
$newAddress = trim($_POST['new_address'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
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
                    try {
                        updateTableAutoIncrement($pdo, 'user_addresses');
                    } catch (Exception $e) {}
                } catch (PDOException $e) {
                    $updateStatement = $pdo->prepare('UPDATE site_users SET address = :address WHERE id = :id');
                    $updateStatement->execute(['address' => $newAddress, 'id' => $user['id']]);
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
                    try {
                        updateTableAutoIncrement($pdo, 'admin_addresses');
                    } catch (Exception $e) {}
                } catch (PDOException $e) {}
            }
        }
    } elseif ($selectedAddressId === 'old') {
        
        $address = $user['address'] ?? '';
        if (empty($address)) {
            $errors[] = 'Adres bulunamadı.';
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

    if (!$errors) {
        $cartTotal = calculateCartTotal($cartItems);
        try {
            $pdo->beginTransaction();
            $orderId = getNextAvailableId($pdo, 'orders');
            $orderStatement = $pdo->prepare('INSERT INTO orders (id, customer_name, customer_email, customer_phone, address, total_amount, status) VALUES (:id, :name, :email, :phone, :address, :total, :status)');
            $orderStatement->execute([
                'id' => $orderId,
                'name' => $user['full_name'] ?: $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
                'address' => $address,
                'total' => $cartTotal,
                'status' => 'Hazırlanıyor',
            ]);

            $itemStatement = $pdo->prepare('INSERT INTO order_items (id, order_id, product_id, quantity, unit_price) VALUES (:id, :order_id, :product_id, :quantity, :price)');
            $stockUpdateStatement = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :product_id AND stock >= :quantity');
            $stockCheckStatement = $pdo->prepare('SELECT stock FROM products WHERE id = :product_id');
            
            foreach ($cartItems as $item) {
                
                $stockCheckStatement->execute(['product_id' => $item['id']]);
                $productStock = $stockCheckStatement->fetch();
                
                if (!$productStock || (int) $productStock['stock'] < $item['quantity']) {
                    throw new Exception('Ürün "' . $item['name'] . '" için yeterli stok bulunmuyor. Mevcut stok: ' . ($productStock ? $productStock['stock'] : 0));
                }

                $itemId = getNextAvailableId($pdo, 'order_items');
                $itemStatement->execute([
                    'id' => $itemId,
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $stockUpdateStatement->execute([
                    'quantity' => $item['quantity'],
                    'product_id' => $item['id'],
                ]);

                if ($stockUpdateStatement->rowCount() === 0) {
                    throw new Exception('Ürün "' . $item['name'] . '" için stok güncellenemedi.');
                }
            }

            $pdo->commit();
            try {
                updateTableAutoIncrement($pdo, 'orders');
                updateTableAutoIncrement($pdo, 'order_items');
            } catch (Exception $e) {
                
            }
            clearCart();
            redirect('order_success.php?id=' . $orderId);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Sipariş kaydedilirken bir hata oluştu: ' . $exception->getMessage();
        }
    }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h1>Ödeme</h1>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php 
                        
                        if (strpos($error, '<a') !== false) {
                            echo $error;
                        } else {
                            echo sanitize($error);
                        }
                    ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div style="margin-bottom: 2rem; padding: 1rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
        <h3 style="margin-top: 0;">Müşteri Bilgileri</h3>
        <p><strong>Ad Soyad:</strong> <?php echo sanitize($user['full_name'] ?: $user['username']); ?></p>
        <p><strong>E-posta:</strong> <?php echo sanitize($user['email']); ?></p>
        <?php if (!empty($user['phone'])): ?>
            <p><strong>Telefon:</strong> <?php echo sanitize($user['phone']); ?></p>
        <?php endif; ?>
    </div>

    <form method="post" id="checkout-form">
        <?php echo csrf_field(); ?>
        <label for="address_selection">Teslimat Adresi</label>
        
        <?php if (count($userAddresses) > 0): ?>
            <?php foreach ($userAddresses as $addr): ?>
                <div style="margin-bottom: 1rem;">
                    <label class="address-option" style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: 0.5rem; cursor: pointer;">
                        <input type="radio" name="selected_address" value="<?php echo htmlspecialchars($addr['id']); ?>" id="address_<?php echo htmlspecialchars($addr['id']); ?>" style="margin-top: 0.25rem;" <?php echo ($selectedAddressId === '' && $addr['is_default']) || $selectedAddressId == $addr['id'] ? 'checked' : ''; ?>>
                        <div style="flex: 1;">
                            <strong><?php echo sanitize($addr['address_title']); ?></strong>
                            <?php if ($addr['is_default']): ?>
                                <span style="font-size: 0.85rem; color: var(--primary); margin-left: 0.5rem;">(Varsayılan)</span>
                            <?php endif; ?>
                            <p style="margin: 0.5rem 0 0 0; color: var(--muted);"><?php echo nl2br(sanitize($addr['address'])); ?></p>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-bottom: 1rem;">
                <label class="address-option" style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: 0.5rem; cursor: pointer;">
                    <input type="radio" name="selected_address" value="new" id="new_address" style="margin-top: 0.25rem;" <?php echo $selectedAddressId === 'new' ? 'checked' : ''; ?>>
                    <div style="flex: 1;">
                        <strong>Yeni Adres Ekle</strong>
                    </div>
                </label>
            </div>
        <?php endif; ?>
        
        <div id="new_address_section" style="<?php echo (count($userAddresses) === 0 || $selectedAddressId === 'new') ? '' : 'display: none;'; ?>">
            <label for="new_address_title">Adres Başlığı (örn: Ev, İş, Ofis)</label>
            <input id="new_address_title" name="new_address_title" type="text" value="<?php echo sanitize($newAddressTitle); ?>" placeholder="Ev" <?php echo count($userAddresses) === 0 ? 'required' : ''; ?>>
            
            <label for="new_address" style="margin-top: 1rem;"><?php echo count($userAddresses) === 0 ? 'Teslimat Adresi' : 'Yeni Adres'; ?></label>
            <textarea id="new_address" name="new_address" rows="4" placeholder="Adres bilgilerinizi giriniz..." <?php echo count($userAddresses) === 0 ? 'required' : ''; ?>><?php echo sanitize($newAddress); ?></textarea>
        </div>

        <p class="price" style="margin-top: 2rem; font-size: 1.5rem;">Ödenecek Tutar: <?php echo number_format(calculateCartTotal($cartItems), 2); ?> ₺</p>

        <button class="btn btn-primary" type="submit" style="margin-top: 1rem;">Siparişi Tamamla</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!confirm('Siparişi tamamlamak istediğinize emin misiniz?')) {
                e.preventDefault();
            }
        });
    }

    const newAddressRadio = document.getElementById('new_address');
    const newAddressSection = document.getElementById('new_address_section');
    const newAddressTextarea = document.getElementById('new_address');
    const newAddressTitle = document.getElementById('new_address_title');
    const addressOptions = document.querySelectorAll('.address-option');
    const allAddressRadios = document.querySelectorAll('input[name="selected_address"]');
    
    // Radio button değiştiğinde stil güncelle
    function updateAddressStyles() {
        addressOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                option.style.borderColor = 'var(--primary)';
                option.style.background = 'rgba(37, 99, 235, 0.05)';
            } else {
                option.style.borderColor = 'var(--border)';
                option.style.background = '';
            }
        });
    }
    
    function toggleNewAddressSection() {
        if (newAddressRadio && newAddressRadio.checked) {
            if (newAddressSection) {
                newAddressSection.style.display = 'block';
            }
            if (newAddressTextarea) {
                newAddressTextarea.required = true;
            }
            if (newAddressTitle) {
                newAddressTitle.required = true;
            }
        } else {
            if (newAddressSection) {
                newAddressSection.style.display = 'none';
            }
            if (newAddressTextarea) {
                newAddressTextarea.required = false;
            }
            if (newAddressTitle) {
                newAddressTitle.required = false;
            }
        }
        updateAddressStyles();
    }
    
    // Tüm radio button'lara event listener ekle
    allAddressRadios.forEach(radio => {
        radio.addEventListener('change', toggleNewAddressSection);
    });
    
    // İlk yüklemede kontrol et
    toggleNewAddressSection();
    updateAddressStyles();
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

