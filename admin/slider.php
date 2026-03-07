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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slide') {
    if (!empty($_FILES['image']['tmp_name'])) {
        $newUrl = uploadSliderImage($_FILES['image'], $uploadDir, $allowedTypes, $maxSize, $maxWidth, $maxHeight);
        if ($newUrl) {
            $slides = getSliderSlides($pdo);
            $nextOrder = empty($slides) ? 1 : (int) max(array_column($slides, 'sort_order')) + 1;
            addSliderSlide($pdo, $newUrl, null, $nextOrder);
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
    <form method="post" enctype="multipart/form-data" style="margin-bottom: 2rem;">
        <input type="hidden" name="action" value="add_slide">
        <label for="add_image">Görsel</label>
        <input type="file" id="add_image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
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
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slides as $slide): ?>
                    <tr>
                        <td><?php echo (int) $slide['sort_order']; ?></td>
                        <td>
                            <img src="<?php echo normalizeSliderImageUrl($slide['image_url']); ?>" alt="" style="max-width: 120px; max-height: 60px; object-fit: contain; background: var(--light, #f1f5f9); border-radius: 0.25rem;">
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
