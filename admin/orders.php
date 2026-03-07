<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        setFlash('admin_error', 'Güvenlik doğrulaması başarısız.');
        redirect('orders.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {

        $orderId = (int) ($_POST['order_id'] ?? 0); 
        $status = trim($_POST['status'] ?? ''); 

        if ($orderId && $status !== '') {
            
            $statement = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');

            $statement->execute([
                'status' => $status, 
                'id' => $orderId, 
            ]);

            setFlash('admin_success', 'Sipariş durumu güncellendi.');
        }

    } elseif ($action === 'delete') {
        
        $orderId = (int) ($_POST['order_id'] ?? 0);
        
        if ($orderId) {
            $statement = $pdo->prepare('DELETE FROM orders WHERE id = :id');
            $statement->execute(['id' => $orderId]);
            setFlash('admin_success', 'Sipariş başarıyla silindi.');
        }
    }

    $redirectUrl = 'orders.php';
    $redirectFilter = $_POST['redirect_filter'] ?? '';
    if ($redirectFilter !== '' && in_array($redirectFilter, ['Hazırlanıyor', 'Kargolandı', 'Tamamlandı', 'İptal'], true)) {
        $redirectUrl .= '?status=' . urlencode($redirectFilter);
    }
    redirect($redirectUrl);
}

$allowedStatuses = ['Hazırlanıyor', 'Kargolandı', 'Tamamlandı', 'İptal'];
$filterStatus = $_GET['status'] ?? 'all';
if ($filterStatus !== 'all' && !in_array($filterStatus, $allowedStatuses, true)) {
    $filterStatus = 'all';
}

if ($filterStatus === 'all') {
    $ordersStmt = $pdo->query('SELECT * FROM orders ORDER BY id DESC');
    $orders = $ordersStmt->fetchAll();
} else {
    $ordersStmt = $pdo->prepare('SELECT * FROM orders WHERE status = :status ORDER BY id DESC');
    $ordersStmt->execute(['status' => $filterStatus]);
    $orders = $ordersStmt->fetchAll();
}

if (!empty($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="admin-orders-ajax-container">';
    include __DIR__ . '/partials/admin_orders_list_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     SİPARİŞ LİSTESİ ARAYÜZÜ
     ============================================
     Tüm siparişleri detaylı olarak gösterir
-->
<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Siparişler</h1>
    
    <!-- Başarı mesajı gösterimi (flash message) -->
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    
    <!-- Hata mesajı gösterimi (flash message) -->
    <?php if ($error = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div id="admin-orders-ajax-container">
        <?php include __DIR__ . '/partials/admin_orders_list_content.php'; ?>
    </div>
</section>

<script>
(function() {
    var container = document.getElementById('admin-orders-ajax-container');
    if (!container) return;
    container.addEventListener('click', function(e) {
        var link = e.target.closest('a.admin-orders-filter-link');
        if (!link) return;
        e.preventDefault();
        var href = link.getAttribute('href') || '';
        if (!href) return;
        var url = href + (href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1';
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var wrap = doc.getElementById('admin-orders-ajax-container');
                if (wrap) container.innerHTML = wrap.innerHTML;
                container.style.opacity = '';
                container.style.pointerEvents = '';
            })
            .catch(function() {
                container.style.opacity = '';
                container.style.pointerEvents = '';
                window.location.href = href;
            });
        history.replaceState(null, '', href);
    });
})();
</script>

<?php 
include __DIR__ . '/partials/footer.php'; 
?>

