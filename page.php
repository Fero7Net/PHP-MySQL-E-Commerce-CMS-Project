<?php

require __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';

$page = $slug ? findPageBySlug($pdo, $slug) : null;

if (!$page) {
    redirect(BASE_URL . '/');
    exit;
}

$pageTitle = $page['title'] ?? 'ManRoMan';
$pageDescription = mb_substr(strip_tags($page['content'] ?? $page['title'] ?? ''), 0, 160) . (mb_strlen($page['content'] ?? '') > 160 ? '…' : '');
$canonicalUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/sayfa/' . rawurlencode($slug) : '';

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     SAYFA İÇERİĞİ
     ============================================
     Statik sayfa içeriğini gösterir
     Hakkımızda ve İletişim sayfalarıyla aynı stil yapısını kullanır
-->
<!-- Hero Section - Header'dan boşluk için -->
<section class="hero">
    <h1><?php echo sanitize($page['title'] ?? 'Başlıksız Sayfa'); ?></h1>
</section>

<!-- Ana İçerik Kartı - Hakkımızda ve İletişim sayfalarıyla aynı stil -->
<section class="card" style="max-width: 900px; margin: 2rem auto;">
    <?php
    $paragraphsJson = getSetting($pdo, 'content_page_' . $slug . '_paragraphs', '');
    $paragraphs = $paragraphsJson !== '' ? json_decode($paragraphsJson, true) : null;
    $hasParagraphs = is_array($paragraphs) && !empty($paragraphs);
    ?>
    <?php if ($hasParagraphs): ?>
        <!-- Sayfa İçerikleri'nden düzenlenen paragraflar -->
        <div class="page-content" style="max-width: 800px; margin: 0 auto;">
            <?php foreach ($paragraphs as $p): ?>
                <p style="margin-bottom: 1.25rem; line-height: 1.7; color: var(--text);"><?php echo nl2br(sanitize($p)); ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif (!empty($page['content'])): ?>
        <!-- Sayfalar bölümündeki tek blok içerik -->
        <div class="page-content" style="text-align: center; max-width: 800px; margin: 0 auto;">
            <?php echo nl2br(sanitize($page['content'])); ?>
        </div>
    <?php else: ?>
        <div class="page-content" style="text-align: center; max-width: 800px; margin: 0 auto;">
            <p class="text-muted">Bu sayfa için henüz içerik eklenmemiş.</p>
        </div>
    <?php endif; ?>
</section>

<?php 

include __DIR__ . '/partials/footer.php'; 
?>

