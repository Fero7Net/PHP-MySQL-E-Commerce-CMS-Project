<?php

require __DIR__ . '/../config.php';
requireAdminLogin();

$errors = [];

$defaults = [
    'hero_title' => "ManRoMan'a Hoş Geldiniz",
    'hero_subtitle' => 'Hayal gücünüzün sınırlarını aşan koleksiyonlar, her zevke uygun seçeneklerle sizleri bekliyor.',
    'section_categories_title' => 'Popüler Kategoriler',
];

$heroTitle = getSetting($pdo, 'home_hero_title', $defaults['hero_title']);
$heroSubtitle = getSetting($pdo, 'home_hero_subtitle', $defaults['hero_subtitle']);
$sectionCategoriesTitle = getSetting($pdo, 'home_section_categories_title', $defaults['section_categories_title']);
$popularCategoryIds = json_decode(getSetting($pdo, 'home_popular_categories', '[]'), true);

if (!is_array($popularCategoryIds)) {
    $popularCategoryIds = [];
}

$allCategories = getCategories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homepage_save'])) {
    $heroTitle = trim($_POST['hero_title'] ?? '');
    $heroSubtitle = trim($_POST['hero_subtitle'] ?? '');
    $sectionCategoriesTitle = trim($_POST['section_categories_title'] ?? '');
    $rawCats = $_POST['popular_categories'] ?? [];
    if (!is_array($rawCats)) {
        $rawCats = $rawCats !== '' ? [(int) $rawCats] : [];
    }

    $popularCategoryIds = array_values(array_filter(array_map('intval', $rawCats), function ($x) { return $x > 0; }));

    if ($heroTitle === '') {
        $errors[] = 'Hero başlığı zorunludur.';
    }
    if ($sectionCategoriesTitle === '') {
        $errors[] = 'Popüler kategoriler bölüm başlığı zorunludur.';
    }

    if (empty($errors)) {
        setSetting($pdo, 'home_hero_title', $heroTitle);
        setSetting($pdo, 'home_hero_subtitle', $heroSubtitle);
        setSetting($pdo, 'home_section_categories_title', $sectionCategoriesTitle);
        setSetting($pdo, 'home_popular_categories', json_encode($popularCategoryIds, JSON_UNESCAPED_UNICODE));
        setFlash('admin_success', 'Anasayfa ayarları kaydedildi.');
        redirect('homepage.php');
        exit;
    }
}

$pageTitle = 'Anasayfa Özelleştir';
include __DIR__ . '/partials/header.php';
?>

<div class="admin-content">
    <div class="homepage-admin-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Anasayfa Özelleştir</h1>
        <p style="color: var(--muted); margin: 0;">Anasayfadaki başlık, alt başlık ve popüler kategoriler bölümünü buradan düzenleyebilirsiniz.</p>
    </div>

    <?php if ($flash = getFlash('admin_success')): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?php echo sanitize($flash); ?></div>
    <?php endif; ?>
    <?php if ($flash = getFlash('admin_error')): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?php echo sanitize($flash); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo sanitize($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="homepage_save" value="1">
        <!-- ============================================
             HERO BÖLÜMÜ (Başlık & Alt Başlık)
             ============================================ -->
        <section class="card homepage-admin-section" style="margin-bottom: 1.5rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span aria-hidden="true">📌</span> Hero Bölümü (Başlık & Alt Başlık)
            </h2>
            <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.9rem;">Anasayfada slider'ın altında görünen ana metin alanı.</p>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="hero_title" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Başlık</label>
                <input type="text" id="hero_title" name="hero_title" value="<?php echo sanitize($heroTitle); ?>"
                       placeholder="<?php echo sanitize($defaults['hero_title']); ?>"
                       style="width: 100%; max-width: 560px; padding: 0.5rem 0.75rem; font-size: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--light); color: var(--text);">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="hero_subtitle" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Alt Başlık</label>
                <textarea id="hero_subtitle" name="hero_subtitle" rows="3"
                          placeholder="<?php echo sanitize($defaults['hero_subtitle']); ?>"
                          style="width: 100%; max-width: 560px; padding: 0.5rem 0.75rem; font-size: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--light); color: var(--text); resize: vertical;"><?php echo sanitize($heroSubtitle); ?></textarea>
            </div>
        </section>

        <!-- ============================================
             POPÜLER KATEGORİLER BÖLÜMÜ
             ============================================ -->
        <section class="card homepage-admin-section" style="margin-bottom: 1.5rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span aria-hidden="true">📂</span> Popüler Kategoriler
            </h2>
            <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.9rem;">Bu bölümün başlığını ve hangi kategorilerin gösterileceğini belirleyin.</p>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="section_categories_title" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Bölüm Başlığı</label>
                <input type="text" id="section_categories_title" name="section_categories_title" value="<?php echo sanitize($sectionCategoriesTitle); ?>"
                       placeholder="Popüler Kategoriler"
                       style="width: 100%; max-width: 400px; padding: 0.5rem 0.75rem; font-size: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--light); color: var(--text);">
            </div>

            <div class="form-group">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text);">Gösterilecek Kategoriler</label>
                <p style="color: var(--muted); font-size: 0.85rem; margin-bottom: 0.75rem;">Seçili kategoriler üstte sıralı gösterilir. Hiç seçilmezse tüm kategoriler gösterilir.</p>
                <?php if (!empty($allCategories)): ?>
                <div class="category-checkboxes" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php
                    $catById = [];
                    foreach ($allCategories as $cat) {
                        $catById[(int) $cat['id']] = $cat;
                    }
                    $orderedCats = [];
                    foreach ($popularCategoryIds as $id) {
                        if (isset($catById[$id])) {
                            $orderedCats[] = $catById[$id];
                            unset($catById[$id]);
                        }
                    }
                    foreach ($catById as $cat) {
                        $orderedCats[] = $cat;
                    }
                    foreach ($orderedCats as $cat):
                        $checked = in_array((int) $cat['id'], $popularCategoryIds, true);
                    ?>
                    <label class="category-checkbox-label" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: <?php echo $checked ? 'rgba(37, 99, 235, 0.12)' : 'var(--light)'; ?>; border: 1px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: background 0.2s;">
                        <input type="checkbox" name="popular_categories[]" value="<?php echo (int) $cat['id']; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                        <span><?php echo sanitize($cat['name']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--muted);">Henüz kategori eklenmemiş.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="form-actions" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn" style="border: 1px solid var(--border); color: var(--text); text-decoration: none;">Anasayfayı Görüntüle</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
