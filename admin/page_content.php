<?php

require __DIR__ . '/../config.php';
requireAdminLogin();

$errors = [];

$defaultsAbout = [
    'content_about_intro' => 'ManRoMan; çizgi roman ve manga tutkunları için özel hazırlanmış bir çevrim içi vitrinidir.',
    'content_about_hero_title' => 'Hakkımızda',
    'content_about_hero_subtitle' => 'ManRoMan olarak çizgi roman ve manga tutkunlarına kaliteli hizmet sunuyoruz.',
    'content_about_biz_kimiz_title' => 'Biz Kimiz?',
    'content_about_biz_kimiz_text' => "ManRoMan, çizgi roman ve manga tutkunları için özel olarak tasarlanmış bir çevrim içi kitapçıdır.\n2018 yılında kurulan şirketimiz, Türkiye'nin dört bir yanındaki okurlara en sevdikleri serileri ulaştırmak için çalışmaktadır. Yerli ve yabancı yayınevlerinden özenle seçtiğimiz koleksiyonlarımızla, her yaştan okurun hayal dünyasına katkıda bulunmayı hedefliyoruz.",
    'content_about_misyon_title' => 'Misyonumuz',
    'content_about_misyon_text' => "En kaliteli çizgi roman ve manga koleksiyonlarını, uygun fiyatlarla ve hızlı teslimatla sizlere sunmak.\nHer yaştan okurun hayal dünyasına katkıda bulunmak ve çizgi roman kültürünü Türkiye'de yaygınlaştırmak.\nMüşterilerimize en iyi alışveriş deneyimini sunarak, onların memnuniyetini ön planda tutmak.",
    'content_about_vizyon_title' => 'Vizyonumuz',
    'content_about_vizyon_text' => "Türkiye'nin en güvenilir ve kapsamlı çizgi roman platformu olmak. Okurlarımızın her zaman yanında olmak ve onlara en iyi alışveriş deneyimini sunmak. Çizgi roman ve manga kültürünü Türkiye'de daha da yaygınlaştırarak, genç nesillere bu sanat dalını sevdirmek ve tanıtmak.",
    'content_about_neden_title' => 'Neden Bizi Seçmelisiniz?',
    'content_about_neden_1_title' => '📚 Geniş Ürün Yelpazesi',
    'content_about_neden_1_text' => '80+ farklı seri ve binlerce çizgi roman çeşidi ile ihtiyacınız olan her şeyi bulabilirsiniz.',
    'content_about_neden_2_title' => '🚚 Hızlı Teslimat',
    'content_about_neden_2_text' => 'Siparişleriniz 1-3 iş günü içinde kapınızda. Türkiye genelinde ücretsiz kargo fırsatları.',
    'content_about_neden_3_title' => '💎 Kaliteli Ürünler',
    'content_about_neden_3_text' => 'Sadece orijinal ve kaliteli ürünler sunuyoruz. Tüm kitaplarımız özenle seçilmiş ve kontrol edilmiştir.',
    'content_about_neden_4_title' => '💰 Uygun Fiyatlar',
    'content_about_neden_4_text' => 'En uygun fiyat garantisi ile bütçenize uygun alışveriş yapabilirsiniz. Düzenli kampanyalar ve indirimler.',
    'content_about_cta_title' => 'Bize Katılın!',
    'content_about_cta_text' => "Çizgi roman ve manga tutkunu bir topluluk oluşturmak için buradayız.\nSiz de aramıza katılın ve bu büyülü dünyanın bir parçası olun!",
    'content_about_cta_btn' => 'Hemen Üye Ol',
];

$defaultsContact = [
    'content_contact_hero_title' => 'İletişim',
    'content_contact_hero_subtitle' => 'Bizimle iletişime geçin, sorularınızı yanıtlayalım.',
    'content_contact_section_title' => 'İletişim Bilgileri',
    'content_contact_email_label' => 'E-posta',
    'content_contact_email_desc' => 'Sorularınız için bize yazın',
    'content_contact_email_value' => 'info@manroman.local',
    'content_contact_phone_label' => 'Telefon',
    'content_contact_phone_desc' => 'Bizi arayın',
    'content_contact_phone_value' => '0 212 000 00 00',
    'content_contact_phone_mobile' => '0 555 555 55 55',
    'content_contact_address_title' => 'Adres',
    'content_contact_address_company' => 'ManRoMan Kitapçılık',
    'content_contact_address_line1' => 'Bağdat Caddesi No: 123/A',
    'content_contact_address_line2' => 'Balıkesir Gönen',
    'content_contact_address_country' => 'Türkiye',
    'content_contact_hours_title' => 'Çalışma Saatleri',
    'content_contact_hours_weekdays' => '09:00 - 18:00',
    'content_contact_hours_weekdays_label' => 'Pazartesi - Cuma',
    'content_contact_hours_saturday' => '10:00 - 16:00',
    'content_contact_hours_saturday_label' => 'Cumartesi',
    'content_contact_hours_sunday' => 'Kapalı',
    'content_contact_hours_sunday_label' => 'Pazar',
    'content_contact_social_title' => 'Sosyal Medya',
    'content_contact_social_desc' => 'Bizi sosyal medyada takip edin!',
    'content_contact_social_facebook' => '#',
    'content_contact_social_instagram' => '#',
    'content_contact_social_twitter' => '#',
];

$allPages = getPages($pdo);
$selectedSlug = isset($_GET['sayfa']) ? (string) $_GET['sayfa'] : '';
$slugs = array_column($allPages, 'slug');
if (!in_array($selectedSlug, $slugs, true)) {
    $selectedSlug = !empty($slugs) ? $slugs[0] : '';
}
$selectedPageId = null;
$selectedPageTitle = '';
if ($selectedSlug !== '') {
    foreach ($allPages as $p) {
        if ($p['slug'] === $selectedSlug) {
            $selectedPageId = (int) $p['id'];
            $selectedPageTitle = $p['title'];
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['add_paragraph']) && $selectedSlug !== 'hakkimizda' && $selectedSlug !== 'iletisim') {
    $key = 'content_page_' . $selectedSlug . '_paragraphs';
    $json = getSetting($pdo, $key, '[]');
    $arr = json_decode($json, true);
    if (!is_array($arr)) {
        $arr = [];
    }
    $arr[] = '';
    setSetting($pdo, $key, json_encode($arr, JSON_UNESCAPED_UNICODE));
    setFlash('admin_success', 'Yeni paragraf alanı eklendi.');
    redirect('page_content.php?sayfa=' . rawurlencode($selectedSlug));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_paragraph']) && $selectedSlug !== 'hakkimizda' && $selectedSlug !== 'iletisim') {
    $key = 'content_page_' . $selectedSlug . '_paragraphs';
    $json = getSetting($pdo, $key, '[]');
    $arr = json_decode($json, true);
    if (!is_array($arr)) {
        $arr = [];
    }
    $idx = (int) $_GET['delete_paragraph'];
    if ($idx >= 0 && $idx < count($arr)) {
        array_splice($arr, $idx, 1);
        setSetting($pdo, $key, json_encode($arr, JSON_UNESCAPED_UNICODE));
        setFlash('admin_success', 'Paragraf silindi.');
    } else {
        setFlash('admin_error', 'Geçersiz paragraf.');
    }
    redirect('page_content.php?sayfa=' . rawurlencode($selectedSlug));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sayfa = isset($_POST['sayfa']) ? (string) $_POST['sayfa'] : '';
    if ($sayfa === 'hakkimizda') {
        foreach (array_keys($defaultsAbout) as $key) {
            $value = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
            setSetting($pdo, $key, $value);
        }
        setFlash('admin_success', 'Hakkımızda sayfa içerikleri güncellendi.');
        redirect('page_content.php?sayfa=hakkimizda');
        exit;
    }
    if ($sayfa === 'iletisim') {
        foreach (array_keys($defaultsContact) as $key) {
            $value = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
            setSetting($pdo, $key, $value);
        }
        setFlash('admin_success', 'İletişim sayfa içerikleri güncellendi.');
        redirect('page_content.php?sayfa=iletisim');
        exit;
    }
    
    if (in_array($sayfa, $slugs, true)) {
        $paragraphs = isset($_POST['paragraphs']) && is_array($_POST['paragraphs']) ? $_POST['paragraphs'] : [];
        $paragraphs = array_map('trim', $paragraphs);
        $paragraphs = array_values(array_filter($paragraphs, function ($v) { return $v !== ''; }));
        setSetting($pdo, 'content_page_' . $sayfa . '_paragraphs', json_encode($paragraphs, JSON_UNESCAPED_UNICODE));
        setFlash('admin_success', 'Paragraflar güncellendi.');
        redirect('page_content.php?sayfa=' . rawurlencode($sayfa));
        exit;
    }
    setFlash('admin_error', 'Geçersiz sayfa.');
    redirect('page_content.php');
    exit;
}

$about = [];
$hakkimizdaPage = findPageBySlug($pdo, 'hakkimizda');
$aboutIntroDefault = ($hakkimizdaPage !== null && isset($hakkimizdaPage['content']) && $hakkimizdaPage['content'] !== '')
    ? $hakkimizdaPage['content']
    : $defaultsAbout['content_about_intro'];
foreach ($defaultsAbout as $key => $default) {
    $about[$key] = getSetting($pdo, $key, $key === 'content_about_intro' ? $aboutIntroDefault : $default);
}
$contact = [];
foreach ($defaultsContact as $key => $default) {
    $contact[$key] = getSetting($pdo, $key, $default);
}

$pageParagraphs = [];
if ($selectedSlug !== 'hakkimizda' && $selectedSlug !== 'iletisim') {
    $json = getSetting($pdo, 'content_page_' . $selectedSlug . '_paragraphs', '[]');
    $pageParagraphs = json_decode($json, true);
    if (!is_array($pageParagraphs)) {
        $pageParagraphs = [];
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Sayfa İçerikleri</h1>
    <p style="color: var(--muted); margin-bottom: 1rem;">
        Düzenlemek istediğiniz sayfayı seçin. Hakkımızda ve İletişim sayfalarında blok blok içerik düzenlenir; diğer sayfaların ana içeriği <strong>Sayfalar</strong> bölümünden düzenlenir.
    </p>

    <?php if (empty($slugs)): ?>
    <div class="alert" style="padding: 1.5rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
        <p style="margin: 0;">Henüz sayfa yok. Sayfa içeriklerini düzenlemek için önce <a href="<?php echo BASE_URL; ?>/admin/pages.php">Sayfalar</a> bölümünden sayfa ekleyin.</p>
    </div>
    <?php else: ?>
    <div style="margin-bottom: 1.5rem;">
        <label for="sayfa-select" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Düzenlenecek sayfa</label>
        <select id="sayfa-select" style="padding: 0.5rem 1rem; min-width: 280px; font-size: 1rem;" onchange="var s=this.value; if(s) window.location.href='<?php echo BASE_URL; ?>/admin/page_content.php?sayfa='+encodeURIComponent(s);">
            <?php foreach ($allPages as $p): ?>
                <option value="<?php echo sanitize($p['slug']); ?>" <?php echo $p['slug'] === $selectedSlug ? 'selected' : ''; ?>><?php echo sanitize($p['title']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($selectedSlug !== ''): ?>
    <?php if ($selectedSlug !== 'hakkimizda' && $selectedSlug !== 'iletisim'): ?>
        <form method="post" style="padding: 2rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border); color: var(--text);">
            <input type="hidden" name="sayfa" value="<?php echo sanitize($selectedSlug); ?>">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?php echo sanitize($selectedPageTitle); ?> — Paragraflar</h2>
            <p style="margin: 0 0 1.5rem 0; color: var(--muted);">Her kutu bir paragraf. Sırayla doldurun; yeni paragraf eklemek için alttaki butonu kullanın.</p>

            <?php
            $parList = $pageParagraphs;
            if (empty($parList)) {
                $parList = [''];
            }
            foreach ($parList as $idx => $text):
            ?>
            <div class="page-content-inner-card" style="margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label class="page-content-label" style="font-weight: 600; margin: 0;">Paragraf <?php echo (int)($idx + 1); ?></label>
                    <a href="<?php echo BASE_URL; ?>/admin/page_content.php?sayfa=<?php echo rawurlencode($selectedSlug); ?>&delete_paragraph=<?php echo $idx; ?>" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.875rem; background: #dc2626; color: white; text-decoration: none; border-radius: 0.375rem;" onclick="return confirm('Bu paragrafı silmek istediğinize emin misiniz?');">Paragrafı sil</a>
                </div>
                <textarea name="paragraphs[]" rows="4" style="width: 100%; padding: 0.5rem;"><?php echo sanitize($text); ?></textarea>
            </div>
            <?php endforeach; ?>

            <div style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                <a href="<?php echo BASE_URL; ?>/admin/page_content.php?sayfa=<?php echo rawurlencode($selectedSlug); ?>&add_paragraph=1" class="btn" style="background: var(--primary); color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 0.5rem;">+ Yeni paragraf ekle</a>
                <button type="submit" class="btn btn-primary">Paragrafları kaydet</button>
                <?php if ($selectedPageId): ?>
                <a href="<?php echo BASE_URL; ?>/admin/pages.php?edit_id=<?php echo $selectedPageId; ?>" class="btn" style="border: 1px solid var(--border); color: var(--text); text-decoration: none; padding: 0.5rem 1rem; border-radius: 0.5rem;">Sayfalar bölümünde başlık / slug düzenle</a>
                <?php endif; ?>
            </div>
        </form>
    <?php else: ?>
    <form method="post">
        <input type="hidden" name="sayfa" value="<?php echo sanitize($selectedSlug); ?>">
        <?php if ($selectedSlug === 'hakkimizda'): ?>
        <!-- ========== HAKKIMIZDA ========== -->
        <div style="margin-bottom: 2.5rem; padding: 1.5rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
            <h2 style="color: var(--primary); margin-bottom: 1.5rem;">Hakkımızda Sayfası</h2>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Sayfa giriş metni (üstteki açıklama)</label>
                <textarea name="content_about_intro" rows="3" style="width: 100%; max-width: 700px; padding: 0.5rem;"><?php echo sanitize($about['content_about_intro']); ?></textarea>
                <p style="margin: 0.35rem 0 0 0; font-size: 0.875rem; color: var(--muted);">Örn: ManRoMan; çizgi roman ve manga tutkunları için özel hazırlanmış bir çevrim içi vitrinidir.</p>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hero başlık</label>
                <input type="text" name="content_about_hero_title" value="<?php echo sanitize($about['content_about_hero_title']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hero alt başlık</label>
                <input type="text" name="content_about_hero_subtitle" value="<?php echo sanitize($about['content_about_hero_subtitle']); ?>" style="width: 100%; max-width: 600px; padding: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Biz Kimiz? (başlık)</label>
                <input type="text" name="content_about_biz_kimiz_title" value="<?php echo sanitize($about['content_about_biz_kimiz_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Biz Kimiz? (metin)</label>
                <textarea name="content_about_biz_kimiz_text" rows="4" style="width: 100%; max-width: 700px; padding: 0.5rem;"><?php echo sanitize($about['content_about_biz_kimiz_text']); ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Misyonumuz (başlık)</label>
                <input type="text" name="content_about_misyon_title" value="<?php echo sanitize($about['content_about_misyon_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Misyonumuz (metin)</label>
                <textarea name="content_about_misyon_text" rows="4" style="width: 100%; max-width: 700px; padding: 0.5rem;"><?php echo sanitize($about['content_about_misyon_text']); ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Vizyonumuz (başlık)</label>
                <input type="text" name="content_about_vizyon_title" value="<?php echo sanitize($about['content_about_vizyon_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Vizyonumuz (metin)</label>
                <textarea name="content_about_vizyon_text" rows="4" style="width: 100%; max-width: 700px; padding: 0.5rem;"><?php echo sanitize($about['content_about_vizyon_text']); ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Neden Bizi Seçmelisiniz? (başlık)</label>
                <input type="text" name="content_about_neden_title" value="<?php echo sanitize($about['content_about_neden_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <div class="page-content-inner-card" style="margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    <label class="page-content-label" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Kart <?php echo $i; ?> başlık</label>
                    <input type="text" name="content_about_neden_<?php echo $i; ?>_title" value="<?php echo sanitize($about['content_about_neden_' . $i . '_title']); ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem;">
                    <label class="page-content-label" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Kart <?php echo $i; ?> metin</label>
                    <textarea name="content_about_neden_<?php echo $i; ?>_text" rows="2" style="width: 100%; padding: 0.5rem;"><?php echo sanitize($about['content_about_neden_' . $i . '_text']); ?></textarea>
                </div>
            <?php endfor; ?>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Bize Katılın (başlık)</label>
                <input type="text" name="content_about_cta_title" value="<?php echo sanitize($about['content_about_cta_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Bize Katılın (metin)</label>
                <textarea name="content_about_cta_text" rows="3" style="width: 100%; max-width: 700px; padding: 0.5rem;"><?php echo sanitize($about['content_about_cta_text']); ?></textarea>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Bize Katılın (buton metni)</label>
                <input type="text" name="content_about_cta_btn" value="<?php echo sanitize($about['content_about_cta_btn']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
        </div>
        <?php endif; ?>

        <?php if ($selectedSlug === 'iletisim'): ?>
        <!-- ========== İLETİŞİM ========== -->
        <div style="margin-bottom: 2.5rem; padding: 1.5rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
            <h2 style="color: var(--primary); margin-bottom: 1.5rem;">İletişim Sayfası</h2>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hero başlık</label>
                <input type="text" name="content_contact_hero_title" value="<?php echo sanitize($contact['content_contact_hero_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hero alt başlık</label>
                <input type="text" name="content_contact_hero_subtitle" value="<?php echo sanitize($contact['content_contact_hero_subtitle']); ?>" style="width: 100%; max-width: 600px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">İletişim Bilgileri (bölüm başlığı)</label>
                <input type="text" name="content_contact_section_title" value="<?php echo sanitize($contact['content_contact_section_title']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">E-posta (etiket)</label>
                <input type="text" name="content_contact_email_label" value="<?php echo sanitize($contact['content_contact_email_label']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">E-posta (açıklama)</label>
                <input type="text" name="content_contact_email_desc" value="<?php echo sanitize($contact['content_contact_email_desc']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">E-posta adresi</label>
                <input type="text" name="content_contact_email_value" value="<?php echo sanitize($contact['content_contact_email_value']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Telefon (etiket)</label>
                <input type="text" name="content_contact_phone_label" value="<?php echo sanitize($contact['content_contact_phone_label']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Telefon (açıklama)</label>
                <input type="text" name="content_contact_phone_desc" value="<?php echo sanitize($contact['content_contact_phone_desc']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Telefon numarası</label>
                <input type="text" name="content_contact_phone_value" value="<?php echo sanitize($contact['content_contact_phone_value']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Mobil telefon</label>
                <input type="text" name="content_contact_phone_mobile" value="<?php echo sanitize($contact['content_contact_phone_mobile']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Adres (başlık)</label>
                <input type="text" name="content_contact_address_title" value="<?php echo sanitize($contact['content_contact_address_title']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Şirket / yer adı</label>
                <input type="text" name="content_contact_address_company" value="<?php echo sanitize($contact['content_contact_address_company']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Adres satır 1</label>
                <input type="text" name="content_contact_address_line1" value="<?php echo sanitize($contact['content_contact_address_line1']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Adres satır 2 (ilçe / şehir)</label>
                <input type="text" name="content_contact_address_line2" value="<?php echo sanitize($contact['content_contact_address_line2']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Ülke</label>
                <input type="text" name="content_contact_address_country" value="<?php echo sanitize($contact['content_contact_address_country']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Çalışma Saatleri (başlık)</label>
                <input type="text" name="content_contact_hours_title" value="<?php echo sanitize($contact['content_contact_hours_title']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hafta içi etiket</label>
                    <input type="text" name="content_contact_hours_weekdays_label" value="<?php echo sanitize($contact['content_contact_hours_weekdays_label']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Hafta içi saat</label>
                    <input type="text" name="content_contact_hours_weekdays" value="<?php echo sanitize($contact['content_contact_hours_weekdays']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Cumartesi etiket</label>
                    <input type="text" name="content_contact_hours_saturday_label" value="<?php echo sanitize($contact['content_contact_hours_saturday_label']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Cumartesi saat</label>
                    <input type="text" name="content_contact_hours_saturday" value="<?php echo sanitize($contact['content_contact_hours_saturday']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Pazar etiket</label>
                    <input type="text" name="content_contact_hours_sunday_label" value="<?php echo sanitize($contact['content_contact_hours_sunday_label']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Pazar saat</label>
                    <input type="text" name="content_contact_hours_sunday" value="<?php echo sanitize($contact['content_contact_hours_sunday']); ?>" style="width: 100%; padding: 0.5rem;">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Sosyal Medya (başlık)</label>
                <input type="text" name="content_contact_social_title" value="<?php echo sanitize($contact['content_contact_social_title']); ?>" style="width: 100%; max-width: 300px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Sosyal Medya (açıklama)</label>
                <input type="text" name="content_contact_social_desc" value="<?php echo sanitize($contact['content_contact_social_desc']); ?>" style="width: 100%; max-width: 400px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Facebook URL</label>
                <input type="text" name="content_contact_social_facebook" value="<?php echo sanitize($contact['content_contact_social_facebook']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Instagram URL</label>
                <input type="text" name="content_contact_social_instagram" value="<?php echo sanitize($contact['content_contact_social_instagram']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Twitter URL</label>
                <input type="text" name="content_contact_social_twitter" value="<?php echo sanitize($contact['content_contact_social_twitter']); ?>" style="width: 100%; max-width: 500px; padding: 0.5rem;">
            </div>
        </div>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">İçerikleri Kaydet</button>
    </form>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
