<?php

require __DIR__ . '/config.php';

logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');

$pageTitle = 'Hakkımızda';
$page = findPageBySlug($pdo, 'hakkimizda');
if ($page === null) {
    redirect(BASE_URL . '/');
    exit;
}
$aboutIntro = getSetting($pdo, 'content_about_intro', $page['content'] ?? 'ManRoMan; çizgi roman ve manga tutkunları için özel hazırlanmış bir çevrim içi vitrinidir.');

$heroTitle = getSetting($pdo, 'content_about_hero_title', 'Hakkımızda');
$heroSubtitle = getSetting($pdo, 'content_about_hero_subtitle', 'ManRoMan olarak çizgi roman ve manga tutkunlarına kaliteli hizmet sunuyoruz.');
$bizKimizTitle = getSetting($pdo, 'content_about_biz_kimiz_title', 'Biz Kimiz?');
$bizKimizText = getSetting($pdo, 'content_about_biz_kimiz_text', "ManRoMan, çizgi roman ve manga tutkunları için özel olarak tasarlanmış bir çevrim içi kitapçıdır.\n2018 yılında kurulan şirketimiz, Türkiye'nin dört bir yanındaki okurlara en sevdikleri serileri ulaştırmak için çalışmaktadır. Yerli ve yabancı yayınevlerinden özenle seçtiğimiz koleksiyonlarımızla, her yaştan okurun hayal dünyasına katkıda bulunmayı hedefliyoruz.");
$misyonTitle = getSetting($pdo, 'content_about_misyon_title', 'Misyonumuz');
$misyonText = getSetting($pdo, 'content_about_misyon_text', "En kaliteli çizgi roman ve manga koleksiyonlarını, uygun fiyatlarla ve hızlı teslimatla sizlere sunmak.\nHer yaştan okurun hayal dünyasına katkıda bulunmak ve çizgi roman kültürünü Türkiye'de yaygınlaştırmak.\nMüşterilerimize en iyi alışveriş deneyimini sunarak, onların memnuniyetini ön planda tutmak.");
$vizyonTitle = getSetting($pdo, 'content_about_vizyon_title', 'Vizyonumuz');
$vizyonText = getSetting($pdo, 'content_about_vizyon_text', "Türkiye'nin en güvenilir ve kapsamlı çizgi roman platformu olmak. Okurlarımızın her zaman yanında olmak ve onlara en iyi alışveriş deneyimini sunmak. Çizgi roman ve manga kültürünü Türkiye'de daha da yaygınlaştırarak, genç nesillere bu sanat dalını sevdirmek ve tanıtmak.");
$nedenTitle = getSetting($pdo, 'content_about_neden_title', 'Neden Bizi Seçmelisiniz?');
$neden1Title = getSetting($pdo, 'content_about_neden_1_title', '📚 Geniş Ürün Yelpazesi');
$neden1Text = getSetting($pdo, 'content_about_neden_1_text', '80+ farklı seri ve binlerce çizgi roman çeşidi ile ihtiyacınız olan her şeyi bulabilirsiniz.');
$neden2Title = getSetting($pdo, 'content_about_neden_2_title', '🚚 Hızlı Teslimat');
$neden2Text = getSetting($pdo, 'content_about_neden_2_text', 'Siparişleriniz 1-3 iş günü içinde kapınızda. Türkiye genelinde ücretsiz kargo fırsatları.');
$neden3Title = getSetting($pdo, 'content_about_neden_3_title', '💎 Kaliteli Ürünler');
$neden3Text = getSetting($pdo, 'content_about_neden_3_text', 'Sadece orijinal ve kaliteli ürünler sunuyoruz. Tüm kitaplarımız özenle seçilmiş ve kontrol edilmiştir.');
$neden4Title = getSetting($pdo, 'content_about_neden_4_title', '💰 Uygun Fiyatlar');
$neden4Text = getSetting($pdo, 'content_about_neden_4_text', 'En uygun fiyat garantisi ile bütçenize uygun alışveriş yapabilirsiniz. Düzenli kampanyalar ve indirimler.');
$ctaTitle = getSetting($pdo, 'content_about_cta_title', 'Bize Katılın!');
$ctaText = getSetting($pdo, 'content_about_cta_text', "Çizgi roman ve manga tutkunu bir topluluk oluşturmak için buradayız.\nSiz de aramıza katılın ve bu büyülü dünyanın bir parçası olun!");
$ctaBtn = getSetting($pdo, 'content_about_cta_btn', 'Hemen Üye Ol');

include __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <h1><?php echo sanitize($heroTitle); ?></h1>
    <p><?php echo sanitize($heroSubtitle); ?></p>
</section>

<section class="card" style="max-width: 900px; margin: 2rem auto;">
    <div class="page-content" style="text-align: center; max-width: 800px; margin: 0 auto;">
        <?php if (!empty($aboutIntro)): ?>
            <div style="margin-bottom: 3rem; padding: 2rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
                <?php echo nl2br(sanitize($aboutIntro)); ?>
            </div>
        <?php endif; ?>

        <?php if ($bizKimizTitle !== '' || $bizKimizText !== ''): ?>
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?php echo sanitize($bizKimizTitle); ?></h2>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--muted);">
                <?php echo nl2br(sanitize($bizKimizText)); ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($misyonTitle !== '' || $misyonText !== ''): ?>
        <div style="margin-bottom: 3rem; padding: 2rem; background: var(--light); border-radius: 0.5rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?php echo sanitize($misyonTitle); ?></h2>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--muted);">
                <?php echo nl2br(sanitize($misyonText)); ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($vizyonTitle !== '' || $vizyonText !== ''): ?>
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?php echo sanitize($vizyonTitle); ?></h2>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--muted);">
                <?php echo nl2br(sanitize($vizyonText)); ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($nedenTitle !== '' || $neden1Title !== '' || $neden1Text !== '' || $neden2Title !== '' || $neden2Text !== '' || $neden3Title !== '' || $neden3Text !== '' || $neden4Title !== '' || $neden4Text !== ''): ?>
        <div class="about-neden-section" style="margin-bottom: 3rem; padding: 2rem; background: var(--light); border-radius: 0.5rem;">
            <h2 style="color: var(--primary); margin-bottom: 1.5rem;"><?php echo sanitize($nedenTitle); ?></h2>
            <div class="about-neden-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; text-align: left; margin-top: 2rem;">
                <?php if ($neden1Title !== '' || $neden1Text !== ''): ?>
                <div style="padding: 1.5rem; background: white; border-radius: 0.5rem; border: 1px solid var(--border);">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.2rem;"><?php echo sanitize($neden1Title); ?></h3>
                    <p style="color: var(--muted); line-height: 1.6;"><?php echo sanitize($neden1Text); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($neden2Title !== '' || $neden2Text !== ''): ?>
                <div style="padding: 1.5rem; background: white; border-radius: 0.5rem; border: 1px solid var(--border);">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.2rem;"><?php echo sanitize($neden2Title); ?></h3>
                    <p style="color: var(--muted); line-height: 1.6;"><?php echo sanitize($neden2Text); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($neden3Title !== '' || $neden3Text !== ''): ?>
                <div style="padding: 1.5rem; background: white; border-radius: 0.5rem; border: 1px solid var(--border);">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.2rem;"><?php echo sanitize($neden3Title); ?></h3>
                    <p style="color: var(--muted); line-height: 1.6;"><?php echo sanitize($neden3Text); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($neden4Title !== '' || $neden4Text !== ''): ?>
                <div style="padding: 1.5rem; background: white; border-radius: 0.5rem; border: 1px solid var(--border);">
                    <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.2rem;"><?php echo sanitize($neden4Title); ?></h3>
                    <p style="color: var(--muted); line-height: 1.6;"><?php echo sanitize($neden4Text); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ctaTitle !== '' || $ctaText !== '' || $ctaBtn !== ''): ?>
        <div style="padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 0.5rem; color: white;">
            <h2 style="margin-bottom: 1rem; color: white;"><?php echo sanitize($ctaTitle); ?></h2>
            <?php if ($ctaText !== ''): ?>
            <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1.5rem;">
                <?php echo nl2br(sanitize($ctaText)); ?>
            </p>
            <?php endif; ?>
            <?php if ($ctaBtn !== ''): ?>
            <a href="<?php echo BASE_URL; ?>/register.php" class="btn" style="background: white; color: var(--primary); padding: 0.75rem 2rem; text-decoration: none; border-radius: 0.5rem; display: inline-block; font-weight: 600;"><?php echo sanitize($ctaBtn); ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
