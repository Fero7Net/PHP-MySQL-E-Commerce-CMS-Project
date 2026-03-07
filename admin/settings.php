<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        setFlash('admin_error', 'Güvenlik doğrulaması başarısız.');
        redirect('settings.php');
    }
    
    $commentsEnabled = isset($_POST['comments_enabled']) ? '1' : '0';

    setSetting($pdo, 'comments_enabled', $commentsEnabled);

    setFlash('admin_success', 'Ayarlar güncellendi.');

    redirect('settings.php');
}

$commentsEnabled = getSetting($pdo, 'comments_enabled', '1') === '1';

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Site Ayarları</h1>
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success" style="white-space: pre-line;"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php echo csrf_field(); ?>
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
            <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1rem; background: white; border-radius: 0.5rem; border: 1px solid var(--border); transition: all 0.2s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 2px 8px rgba(37, 99, 235, 0.1)'" onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                <div class="toggle-switch">
                    <input type="checkbox" id="comments_enabled" name="comments_enabled" value="1" <?php echo $commentsEnabled ? 'checked' : ''; ?> style="display: none;">
                    <span class="toggle-slider"></span>
                </div>
                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 0.25rem; font-size: 1.1rem; color: #1e293b;">Yorumlar</strong>
                    <p style="margin: 0; font-size: 0.95rem; line-height: 1.5;">
                        <?php if ($commentsEnabled): ?>
                            <span style="color: #059669; font-weight: 700; background: #d1fae5; padding: 0.25rem 0.75rem; border-radius: 0.375rem; display: inline-block; margin-right: 0.5rem;">✓ Aktif</span>
                            <span style="color: #334155;">Kullanıcılar ürünlere yorum yapabilir</span>
                        <?php else: ?>
                            <span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.25rem 0.75rem; border-radius: 0.375rem; display: inline-block; margin-right: 0.5rem;">✗ Kapalı</span>
                            <span style="color: #334155;">Yorumlar devre dışı</span>
                        <?php endif; ?>
                    </p>
                </div>
            </label>
        </div>

        <button class="btn btn-primary" type="submit">Ayarları Kaydet</button>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

