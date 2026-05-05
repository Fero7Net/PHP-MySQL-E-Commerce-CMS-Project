<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

$errors = [];

$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$editingPage = null;

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['id'])) {

    if (!adminIsLoggedIn()) {
        setFlash('admin_error', 'Yetkiniz yok.');
        redirect('pages.php');
        exit; 
    }

    $id = (int) $_GET['id'];

    if ($id > 0) {
        try {

            $statement = $pdo->prepare('DELETE FROM pages WHERE id = :id');

            $statement->execute(['id' => $id]);

            setFlash('admin_success', 'Sayfa silindi.');
        } catch (PDOException $e) {
            
            setFlash('admin_error', 'Silme işlemi sırasında bir hata oluştu.');
        }
    } else {
        
        setFlash('admin_error', 'Geçersiz sayfa ID.');
    }

    redirect('pages.php');
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {

        $title = trim($_POST['title'] ?? ''); 
        $content = trim($_POST['content'] ?? ''); 

        if ($title === '') {
            $errors[] = 'Başlık zorunludur.'; 
        }

        if (!$errors) {
            
            $baseSlug = slugify($title);
            $slug = $baseSlug;
            $suffix = 2;
            $checkStmt = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug');
            while (true) {
                $checkStmt->execute(['slug' => $slug]);
                if (!$checkStmt->fetch()) {
                    break;
                }
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            $insertOk = false;
            $pdo->beginTransaction();
            try {
                $nextId = getNextAvailableId($pdo, 'pages');
                $statement = $pdo->prepare('INSERT INTO pages (id, title, slug, content) VALUES (:id, :title, :slug, :content)');
                $statement->execute([
                    'id' => $nextId,
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                ]);
                $pdo->commit();
                $insertOk = true;
                
                try {
                    updateTableAutoIncrement($pdo, 'pages');
                } catch (Exception $e2) {
                    
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Sayfa eklenirken bir hata oluştu.';
            }

            if ($insertOk) {
                $showInFooter = isset($_POST['show_in_footer']) ? '1' : '0';
                setSetting($pdo, 'page_footer_' . $slug, $showInFooter);
                setFlash('admin_success', 'Sayfa eklendi.');
                redirect('pages.php');
            }
        }

    } elseif ($action === 'update') {

        $id = (int) ($_POST['id'] ?? 0); 
        $title = trim($_POST['title'] ?? ''); 
        $content = trim($_POST['content'] ?? ''); 

        if ($title === '') {
            $errors[] = 'Başlık zorunludur.';
        }

        if (!$errors) {
            
            $baseSlug = slugify($title);
            $slug = $baseSlug;
            $suffix = 2;
            $checkStmt = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug AND id != :id');
            while (true) {
                $checkStmt->execute(['slug' => $slug, 'id' => $id]);
                if (!$checkStmt->fetch()) {
                    break;
                }
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            $statement = $pdo->prepare('UPDATE pages SET title = :title, slug = :slug, content = :content WHERE id = :id');
            $statement->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'id' => $id,
            ]);
            $showInFooter = isset($_POST['show_in_footer']) ? '1' : '0';
            setSetting($pdo, 'page_footer_' . $slug, $showInFooter);

            setFlash('admin_success', 'Sayfa güncellendi.');
            redirect('pages.php');
        }

    } elseif ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        $statement = $pdo->prepare('DELETE FROM pages WHERE id = :id');

        $statement->execute(['id' => $id]);

        setFlash('admin_success', 'Sayfa silindi.');

        redirect('pages.php');
    }

    elseif ($action === 'bulk_delete') {
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
        $ids = array_filter($ids, function ($id) { return $id > 0; });
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM pages WHERE id IN ($placeholders)");
            $stmt->execute(array_values($ids));
            $count = count($ids);
            setFlash('admin_success', $count . ' sayfa silindi.');
        } else {
            setFlash('admin_error', 'Silinecek sayfa seçilmedi.');
        }
        redirect('pages.php');
    }
}

if ($editId) {
    
    $statement = $pdo->prepare('SELECT * FROM pages WHERE id = :id');

    $statement->execute(['id' => $editId]);

    $editingPage = $statement->fetch();
}

$pages = $pdo->query('SELECT * FROM pages ORDER BY id DESC')->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     SAYFA EKLEME/DÜZENLEME FORMU
     ============================================
     Yeni sayfa ekleme veya mevcut sayfayı düzenleme formu
-->
<section class="card" style="margin-top: 2rem; padding-top: 1.5rem;">
    <h1>Sayfa Yönetimi</h1>
    
    <!-- Başarı mesajı gösterimi (flash message) -->
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    
    <!-- Hata mesajı gösterimi (flash message) -->
    <?php if ($error = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    
    <!-- Form validasyon hataları gösterimi -->
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <!-- Her hata mesajını liste olarak göster -->
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Sayfa ekleme/güncelleme formu -->
    <form method="post">
        <!-- Action parametresi: Düzenleme modundaysa "update", yoksa "create" -->
        <input type="hidden" name="action" value="<?php echo $editingPage ? 'update' : 'create'; ?>">
        
        <!-- Düzenleme modundaysa sayfa ID'sini gizli input olarak gönder -->
        <?php if ($editingPage): ?>
            <input type="hidden" name="id" value="<?php echo $editingPage['id']; ?>">
        <?php endif; ?>
        
        <!-- Başlık input alanı -->
        <label for="title">Başlık</label>
        <!-- Value: Düzenleme modundaysa mevcut başlık, yoksa POST'tan gelen (hata durumunda), yoksa boş -->
        <input id="title" name="title" value="<?php echo sanitize($editingPage['title'] ?? ($_POST['title'] ?? '')); ?>" required>

        <!-- İçerik textarea alanı -->
        <label for="content">İçerik</label>
        <!-- Value: Düzenleme modundaysa mevcut içerik, yoksa POST'tan gelen, yoksa boş -->
        <textarea id="content" name="content" rows="6"><?php echo sanitize($editingPage['content'] ?? ($_POST['content'] ?? '')); ?></textarea>

        <!-- Footer'da göster: düzenlemede mevcut ayar, yeni sayfada varsayılan açık -->
        <?php
        $showInFooter = true;
        if ($editingPage) {
            $showInFooter = getSetting($pdo, 'page_footer_' . ($editingPage['slug'] ?? ''), '1') === '1';
        }
        ?>
        <div style="margin-top: 1rem; display: flex; justify-content: center;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="show_in_footer" value="1" <?php echo $showInFooter ? 'checked' : ''; ?>>
                <span>Footer'da (alt kısımda) göster</span>
            </label>
        </div>

        <!-- Submit butonu: Düzenleme modundaysa "Güncelle", yoksa "Ekle" -->
        <button class="btn btn-primary" type="submit">
            <?php echo $editingPage ? 'Sayfayı Güncelle' : 'Sayfa Ekle'; ?>
        </button>
        
        <!-- Düzenleme modundaysa "Formu Temizle" butonu göster (yeni ekleme moduna döner) -->
        <?php if ($editingPage): ?>
            <a class="btn" href="pages.php">Formu Temizle</a>
        <?php endif; ?>
    </form>
</section>

<!-- ============================================
     MEVCUT SAYFALAR LİSTESİ
     ============================================
     Tüm sayfaları tablo formatında gösterir
-->
<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h2>Mevcut Sayfalar</h2>
    
    <!-- Eğer sayfalar varsa tablo göster -->
    <?php if ($pages): ?>
        <form method="post" id="bulk-pages-form" onsubmit="return confirm('Seçili sayfaları silmek istediğinize emin misiniz?');">
            <input type="hidden" name="action" value="bulk_delete">
            <p style="margin-bottom: 1rem;">
                <button type="submit" class="btn" id="bulk-delete-btn" disabled>Seçilenleri sil</button>
            </p>
        <table>
            <thead>
            <tr>
                <th style="width: 2.5rem;">
                    <input type="checkbox" id="select-all" title="Tümünü seç / seçimi kaldır" aria-label="Tümünü seç">
                </th>
                <th>ID</th>
                <th>Başlık</th>
                <th>Slug</th>
                <th>İşlemler</th>
            </tr>
            </thead>
            <tbody>
            <!-- Her sayfa için bir satır oluştur -->
            <?php foreach ($pages as $page): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="ids[]" value="<?php echo (int) $page['id']; ?>" class="page-checkbox">
                    </td>
                    <!-- Sayfa ID'si -->
                    <td><?php echo $page['id']; ?></td>
                    
                    <!-- Sayfa başlığı (XSS koruması için sanitize) -->
                    <td><?php echo sanitize($page['title']); ?></td>
                    
                    <!-- Sayfa slug'ı (SEO URL) (XSS koruması için sanitize) -->
                    <td><?php echo sanitize($page['slug']); ?></td>
                    
                    <td>
                        <!-- Düzenle butonu: edit_id parametresi ile düzenleme modunu açar -->
                        <a class="btn" href="pages.php?edit_id=<?php echo $page['id']; ?>">Düzenle</a>
                        
                        <!-- Sil butonu: islem=sil&id=X formatı ile silme işlemi yapar -->
                        <!-- JavaScript confirm ile kullanıcıdan onay alır -->
                        <a class="btn" href="pages.php?islem=sil&id=<?php echo $page['id']; ?>" 
                           onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </form>
        <script>
        (function() {
            var form = document.getElementById('bulk-pages-form');
            var selectAll = document.getElementById('select-all');
            var checkboxes = form.querySelectorAll('.page-checkbox');
            var bulkBtn = document.getElementById('bulk-delete-btn');
            function updateBulkBtn() {
                var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
                bulkBtn.disabled = !any;
            }
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
                updateBulkBtn();
            });
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', updateBulkBtn);
            });
        })();
        </script>
    <?php else: ?>
        <!-- Eğer sayfa yoksa bilgi mesajı göster -->
        <p>Henüz sayfa yok.</p>
    <?php endif; ?>
</section>

<?php 

include __DIR__ . '/partials/footer.php'; 
?>

