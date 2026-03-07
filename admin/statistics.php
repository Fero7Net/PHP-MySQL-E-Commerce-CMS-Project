<?php

require __DIR__ . '/../config.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_period'])) {
    $period = $_POST['reset_period'];
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $last7 = date('Y-m-d', strtotime('-7 days'));
    $last30 = date('Y-m-d', strtotime('-30 days'));

    $labels = [
        'today' => 'Bugün',
        'yesterday' => 'Dün',
        'last7' => 'Son 7 gün',
        'last30' => 'Son 30 gün',
        'total' => 'Tüm istatistikler'
    ];
    $label = $labels[$period] ?? 'İstatistik';

    try {
        if ($period === 'today') {
            $pdo->prepare('DELETE FROM site_visits WHERE visit_date = ?')->execute([$today]);
        } elseif ($period === 'yesterday') {
            $pdo->prepare('DELETE FROM site_visits WHERE visit_date = ?')->execute([$yesterday]);
        } elseif ($period === 'last7') {
            $pdo->prepare('DELETE FROM site_visits WHERE visit_date >= ?')->execute([$last7]);
        } elseif ($period === 'last30') {
            $pdo->prepare('DELETE FROM site_visits WHERE visit_date >= ?')->execute([$last30]);
        } elseif ($period === 'total') {
            $pdo->query('TRUNCATE TABLE site_visits');
        } else {
            setFlash('admin_error', 'Geçersiz işlem.');
            header('Location: statistics.php');
            exit;
        }
        setFlash('admin_success', '"' . $label . '" sıfırlandı.');
    } catch (PDOException $e) {
        setFlash('admin_error', 'Sıfırlama sırasında hata oluştu.');
    }
    header('Location: statistics.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'loglari_temizle') {
    $pdo->query('TRUNCATE TABLE site_visits');
    setFlash('admin_success', 'Tüm ziyaretçi istatistikleri sıfırlandı.');
    header('Location: statistics.php');
    exit;
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last7Days = date('Y-m-d', strtotime('-7 days'));
$last30Days = date('Y-m-d', strtotime('-30 days'));

$todayStmt = $pdo->prepare('SELECT COUNT(DISTINCT ip_address) as count FROM site_visits WHERE visit_date = :date');
$todayStmt->execute(['date' => $today]);
$stats['today'] = (int) $todayStmt->fetch()['count'];

$yesterdayStmt = $pdo->prepare('SELECT COUNT(DISTINCT ip_address) as count FROM site_visits WHERE visit_date = :date');
$yesterdayStmt->execute(['date' => $yesterday]);
$stats['yesterday'] = (int) $yesterdayStmt->fetch()['count'];

$last7DaysStmt = $pdo->prepare('SELECT COUNT(DISTINCT ip_address) as count FROM site_visits WHERE visit_date >= :date');
$last7DaysStmt->execute(['date' => $last7Days]);
$stats['last7days'] = (int) $last7DaysStmt->fetch()['count'];

$last30DaysStmt = $pdo->prepare('SELECT COUNT(DISTINCT ip_address) as count FROM site_visits WHERE visit_date >= :date');
$last30DaysStmt->execute(['date' => $last30Days]);
$stats['last30days'] = (int) $last30DaysStmt->fetch()['count'];

$totalStmt = $pdo->query('SELECT COUNT(DISTINCT ip_address) as count FROM site_visits');
$stats['total'] = (int) $totalStmt->fetch()['count'];

$totalProducts = $pdo->query('SELECT COUNT(*) as count FROM products')->fetch()['count'];
$totalOrders = $pdo->query('SELECT COUNT(*) as count FROM orders')->fetch()['count'];
$totalComments = $pdo->query('SELECT COUNT(*) as count FROM comments')->fetch()['count'];

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Site İstatistikleri</h1>
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($message = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <p style="color: var(--muted); margin-bottom: 1.5rem;">Siteye giren <strong>farklı kişi</strong> (benzersiz ziyaretçi) sayıları. Aynı IP birden fazla sayılmaz.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="position: relative;">
            <h3>Bugün</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $stats['today']; ?></p>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 0.5rem 0;">farklı ziyaretçi</p>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="reset_period" value="today">
                <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Bugüne ait veriler silinecek. Emin misiniz?');">Sıfırla</button>
            </form>
        </div>
        <div class="card" style="position: relative;">
            <h3>Dün</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $stats['yesterday']; ?></p>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 0.5rem 0;">farklı ziyaretçi</p>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="reset_period" value="yesterday">
                <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Düne ait veriler silinecek. Emin misiniz?');">Sıfırla</button>
            </form>
        </div>
        <div class="card" style="position: relative;">
            <h3>Son 7 Gün</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $stats['last7days']; ?></p>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 0.5rem 0;">farklı ziyaretçi</p>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="reset_period" value="last7">
                <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Son 7 güne ait veriler silinecek. Emin misiniz?');">Sıfırla</button>
            </form>
        </div>
        <div class="card" style="position: relative;">
            <h3>Son 30 Gün</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $stats['last30days']; ?></p>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 0.5rem 0;">farklı ziyaretçi</p>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="reset_period" value="last30">
                <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Son 30 güne ait veriler silinecek. Emin misiniz?');">Sıfırla</button>
            </form>
        </div>
        <div class="card" style="position: relative;">
            <h3>Toplam</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $stats['total']; ?></p>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 0.5rem 0;">farklı ziyaretçi</p>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="reset_period" value="total">
                <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Tüm ziyaret verileri silinecek. Emin misiniz?');">Sıfırla</button>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card">
            <h3>Toplam Ürün</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $totalProducts; ?></p>
        </div>
        <div class="card">
            <h3>Toplam Sipariş</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $totalOrders; ?></p>
        </div>
        <div class="card">
            <h3>Toplam Yorum</h3>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $totalComments; ?></p>
        </div>
    </div>

</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

