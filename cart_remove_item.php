<?php

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!canUseCart()) {
    echo json_encode([
        'success' => false,
        'message' => 'Sepete erişmek için giriş yapmanız gerekiyor.',
        'redirect' => BASE_URL . '/login.php'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.']);
        exit;
    }
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($productId > 0) {
        updateCartQuantity($productId, 0);
        $cartCount = getCartCount($pdo);
        $cartItems = getCartItems($pdo);
        $cartTotal = calculateCartTotal($cartItems);

        echo json_encode([
            'success' => true,
            'message' => 'Ürün sepetten kaldırıldı.',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'cart_empty' => empty($cartItems)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz ürün.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek.'
    ]);
}
