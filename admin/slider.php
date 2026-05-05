<?php

require __DIR__ . '/../config.php';
requireAdminLogin();

$errors = [];
$uploadDir = __DIR__ . '/../uploads/slider/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
$maxSize = 50 * 1024 * 1024; 
$maxWidth = 1200;
$maxHeight = 600;

function uploadSliderImage(array $file, string $uploadDir, array $allowedTypes, int $maxSize, int $maxWidth, int $maxHeight, int $quality = 82): ?string
{
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $maxSize) {
        return null;
    }
    $mime = $file['type'];
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_file($finfo, $file['tmp_name']);
            if ($detected) {
                $mime = $detected;
            }
            finfo_close($finfo);
        }
    }
    if (!in_array($mime, $allowedTypes, true)) {
        return null;
    }
    $ext = 'jpg';
    if (strpos($mime, 'png') !== false) {
        $ext = 'png';
    } elseif (strpos($mime, 'gif') !== false) {
        $ext = 'gif';
    } elseif (strpos($mime, 'webp') !== false) {
        $ext = 'webp';
    }
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    $fileName = 'slider_' . uniqid() . '.' . $ext;
    $destPath = $uploadDir . $fileName;
    if (function_exists('resizeImage') && resizeImage($file['tmp_name'], $destPath, $maxWidth, $maxHeight, $quality)) {
        return 'uploads/slider/' . $fileName;
    }
    if (@move_uploaded_file($file['tmp_name'], $destPath)) {
        return 'uploads/slider/' . $fileName;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_slide') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT image_url FROM slider_slides WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            deleteSliderImageFile($row['image_url'], __DIR__ . '/..');
            deleteSliderSlide($pdo, $id);
            reorderSliderSlides($pdo);
            setFlash('admin_success', 'Slide silindi.');
        }
    }
    redirect('slider.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_slide') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && !empty($_FILES['image']['tmp_name'])) {
        $stmt = $pdo->prepare('SELECT image_url FROM slider_slides WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $newUrl = uploadSliderImage($_FILES['image'], $uploadDir, $allowedTypes, $maxSize, $maxWidth, $maxHeight);
            if ($newUrl) {
                updateSliderSlide($pdo, $id, $newUrl, null);
                deleteSliderImageFile($row['image_url'], __DIR__ . '/..');
                setFlash('admin_success', 'Slide güncellendi.');
            } else {
                $errors[] = 'Geçersiz veya çok büyük dosya. Sadece JPEG, PNG, GIF, WebP (max 50MB).';
            }
        }
    }
    redirect('slider.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_slide_meta') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $hoverText = isset($_POST['hover_text']) ? trim((string) $_POST['hover_text']) : null;
        $linkUrl = isset($_POST['link_url']) ? trim((string) $_POST['link_url']) : null;
        if ($hoverText === '') {
            $hoverText = null;
        }
        if ($linkUrl === '') {
            $linkUrl = null;
        }
        updateSliderSlideMeta($pdo, $id, $hoverText, $linkUrl);
        setFlash('admin_success', 'Hover metni ve link güncellendi.');
    }
    redirect('slider.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slide') {
    if (!empty($_FILES['image']['tmp_name'])) {
        $newUrl = uploadSliderImage($_FILES['image'], $uploadDir, $allowedTypes, $maxSize, $maxWidth, $maxHeight);
        if ($newUrl) {
            $slides = getSliderSlides($pdo);
            $nextOrder = empty($slides) ? 1 : (int) max(array_column($slides, 'sort_order')) + 1;
            $hoverText = isset($_POST['hover_text']) ? trim((string) $_POST['hover_text']) : null;
            $linkUrl = isset($_POST['link_url']) ? trim((string) $_POST['link_url']) : null;
            if ($hoverText === '') {
                $hoverText = null;
            }
            if ($linkUrl === '') {
                $linkUrl = null;
            }
            addSliderSlide($pdo, $newUrl, null, $nextOrder, $hoverText, $linkUrl);
            setFlash('admin_success', 'Slide eklendi.');
        } else {
            $errors[] = 'Geçersiz veya çok büyük dosya. Sadece JPEG, PNG, GIF, WebP (max 50MB).';
        }
    } else {
        $errors[] = 'Lütfen bir görsel seçin.';
    }
    if (empty($errors)) {
        redirect('slider.php');
        exit;
    }
}

$slides = [];
try {
    $slides = getSliderSlides($pdo);
} catch (PDOException $e) {
    $errors[] = 'Slider tablosu bulunamadı. Veritabanı yedeğini kontrol edin veya tabloyu oluşturun.';
}

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem;">
    <h1>Slider Yönetimi</h1>
    <?php if ($msg = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($msg); ?></div>
    <?php endif; ?>
    <?php if ($err = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($err); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo sanitize($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2>Yeni slide ekle</h2>
    <p style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--muted, #64748b);">
        <strong>Önerilen resim boyutları:</strong> <strong>1200 × 600 px</strong> (oran 2:1). Bu boyutlarda yükleme yaparsanız görsel kesilmeden ve net görünür. Daha büyük görseller otomatik küçültülür (max 50 MB, JPEG/PNG/GIF/WebP).
    </p>
    <p style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--muted, #64748b);">
        <strong>Hover metni:</strong> Slider üzerine gelindiğinde görünecek metin. <strong>Link:</strong> Resme tıklanınca yeni sekmede açılacak adres (boş bırakılırsa tıklama olmaz).
    </p>
    <form method="post" enctype="multipart/form-data" style="margin-bottom: 2rem;">
        <input type="hidden" name="action" value="add_slide">
        <label for="add_image">Görsel</label>
        <input type="file" id="add_image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
        <label for="add_hover_text" style="display: block; margin-top: 1rem;">Hover metni (isteğe bağlı)</label>
        <input type="text" id="add_hover_text" name="hover_text" placeholder="Örn: Kampanyayı inceleyin" maxlength="255" style="width: 100%; max-width: 400px;">
        <label for="add_link_url" style="display: block; margin-top: 0.5rem;">Link URL (isteğe bağlı, yeni sekmede açılır)</label>
        <input type="url" id="add_link_url" name="link_url" placeholder="https://..." maxlength="500" style="width: 100%; max-width: 400px;">
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Ekle</button>
    </form>

    <h2>Mevcut slide'lar</h2>
    <?php if (empty($slides)): ?>
        <p>Henüz slide yok. Yukarıdan yeni görsel ekleyebilirsiniz.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Önizleme</th>
                    <th>Hover metni / Link</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slides as $slide): ?>
                    <?php
                    $hoverText = isset($slide['hover_text']) ? $slide['hover_text'] : '';
                    $linkUrl = isset($slide['link_url']) ? $slide['link_url'] : '';
                    ?>
                    <tr>
                        <td><?php echo (int) $slide['sort_order']; ?></td>
                        <td>
                            <img src="<?php echo normalizeSliderImageUrl($slide['image_url']); ?>" alt="" style="max-width: 120px; max-height: 60px; object-fit: contain; background: var(--light, #f1f5f9); border-radius: 0.25rem;">
                        </td>
                        <td>
                            <form method="post" style="max-width: 320px;">
                                <input type="hidden" name="action" value="update_slide_meta">
                                <input type="hidden" name="id" value="<?php echo (int) $slide['id']; ?>">
                                <label for="hover_<?php echo (int) $slide['id']; ?>" style="font-size: 0.85rem;">Hover metni</label>
                                <input type="text" id="hover_<?php echo (int) $slide['id']; ?>" name="hover_text" value="<?php echo sanitize($hoverText); ?>" placeholder="Üzerine gelince görünecek metin" maxlength="255" style="width: 100%; margin-bottom: 0.5rem;">
                                <label for="link_<?php echo (int) $slide['id']; ?>" style="font-size: 0.85rem;">Link (yeni sekme)</label>
                                <input type="url" id="link_<?php echo (int) $slide['id']; ?>" name="link_url" value="<?php echo sanitize($linkUrl); ?>" placeholder="https://..." maxlength="500" style="width: 100%; margin-bottom: 0.5rem;">
                                <button type="submit" class="btn">Metin/Link güncelle</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" enctype="multipart/form-data" style="display: inline-block; margin-right: 0.5rem;">
                                <input type="hidden" name="action" value="update_slide">
                                <input type="hidden" name="id" value="<?php echo (int) $slide['id']; ?>">
                                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" style="max-width: 180px;">
                                <button type="submit" class="btn" style="margin-top: 0.25rem;">Fotoğrafı değiştir</button>
                            </form>
                            <form method="post" style="display: inline-block;" onsubmit="return confirm('Bu slide silinsin mi?');">
                                <input type="hidden" name="action" value="delete_slide">
                                <input type="hidden" name="id" value="<?php echo (int) $slide['id']; ?>">
                                <button type="submit" class="btn">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
