<?php

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!canUseCart()) {

    echo json_encode([
        'success' => false, 
        'message' => 'Sepete ürün eklemek için giriş yapmanız gerekiyor.', 
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

    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    if ($productId > 0) {
        
        addToCart($productId, $quantity);

        $cartCount = getCartCount($pdo);

        echo json_encode([
            'success' => true,  
            'message' => 'Ürün sepete eklendi!', 
            'cart_count' => $cartCount 
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

