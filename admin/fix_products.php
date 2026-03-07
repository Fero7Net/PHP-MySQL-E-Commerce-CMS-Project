<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['id'])) {

    if (!adminIsLoggedIn()) {
        setFlash('admin_error', 'Yetkiniz yok.');
        redirect('fix_products.php');
        exit;
    }

    $id = (int) $_GET['id'];

    if ($id > 0) {
        try {

            $productStmt = $pdo->prepare('SELECT image_url FROM products WHERE id = :id');
            $productStmt->execute(['id' => $id]);
            $product = $productStmt->fetch();

            $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $statement->execute(['id' => $id]);

            if ($product && !empty($product['image_url'])) {
            
            $productsImgDir = __DIR__ . '/../products_img';

            deleteImageFile($product['image_url'], $productsImgDir);
            }

            setFlash('admin_success', 'Ürün başarıyla silindi.');
        } catch (PDOException $e) {
            
            setFlash('admin_error', 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        
        setFlash('admin_error', 'Geçersiz ürün ID.');
    }

    redirect('fix_products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem'])) {
    if (!validate_csrf()) {
        setFlash('admin_error', 'Güvenlik doğrulaması başarısız.');
        redirect(BASE_URL . '/admin/fix_products.php');
    }
    try {

        $pdo->beginTransaction();

        $deletedCount = 0; 
        $deletedFiles = 0; 

        $productsImgDir = __DIR__ . '/../products_img';

        if ($_POST['islem'] === 'toplu_sil') {

            if (!empty($_POST['silinecek_id']) && is_array($_POST['silinecek_id'])) {

                $ids = array_map('intval', $_POST['silinecek_id']);

                $ids = array_filter($ids, function($id) { return $id > 0; });

                if (!empty($ids)) {

                    $placeholders = implode(',', array_fill(0, count($ids), '?'));

                    $stmt = $pdo->prepare("SELECT id, image_url FROM products WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($products as $product) {
                        if (!empty($product['image_url'])) {
                            
                            if (deleteImageFile($product['image_url'], $productsImgDir)) {
                                $deletedFiles++; 
                            }
                        }
                    }

                    $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $deletedCount = $stmt->rowCount(); 

                    $message = "✅ Seçilen {$deletedCount} ürün başarıyla silindi!";
                    if ($deletedFiles > 0) {
                        $message .= " {$deletedFiles} resim dosyası da silindi.";
                    }
                } else {
                    
                    setFlash('admin_error', 'Geçerli ürün seçilmedi.');
                    $pdo->rollBack(); 
                    redirect('fix_products.php');
                    exit;
                }
            } else {
                
                setFlash('admin_error', 'Lütfen silmek istediğiniz ürünleri seçin.');
                $pdo->rollBack(); 
                redirect('fix_products.php');
                exit;
            }
        }

        elseif ($_POST['islem'] === 'hepsini_sil') {

            $stmt = $pdo->query('SELECT id, image_url FROM products WHERE image_url IS NOT NULL AND image_url != ""');
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                if (!empty($product['image_url'])) {
                    if (deleteImageFile($product['image_url'], $productsImgDir)) {
                        $deletedFiles++; 
                    }
                }
            }

            $stmt = $pdo->prepare('DELETE FROM products');
            $stmt->execute();
            $deletedCount = $stmt->rowCount(); 

            $message = "✅ Tüm ürünler başarıyla silindi! Toplam {$deletedCount} ürün silindi.";
            if ($deletedFiles > 0) {
                $message .= " {$deletedFiles} resim dosyası da silindi.";
            }
        }

        $pdo->commit();

        setFlash('admin_success', $message);
        redirect('fix_products.php');
        exit;
        
    } catch (Exception $e) {

        $pdo->rollBack();

        setFlash('admin_error', '❌ Hata: ' . htmlspecialchars($e->getMessage()));
        redirect('fix_products.php');
        exit;
    }
}

function hasAuthorColumn(PDO $pdo): bool
{
    try {
        $statement = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'products' 
            AND COLUMN_NAME = 'author'
        ");
        $statement->execute();
        $count = (int) $statement->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        return false;
    }
}

$hasAuthorColumn = hasAuthorColumn($pdo);

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

$categories = $pdo->query('SELECT c.id, c.name, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id, c.name ORDER BY c.name')->fetchAll();

$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     ANA İÇERİK BÖLÜMÜ
     ============================================
     Ürün silme sayfasının ana kartı
     Kırmızı border ile vurgulanmış (tehlikeli işlem)
-->
<section class="card" style="border: 2px solid #dc2626; margin-top: 2rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <!-- Sayfa başlığı (kırmızı renk ile vurgulanmış) -->
    <h1 style="color: #dc2626; margin-top: 0;">Ürün Yönetimi ve Silme</h1>
    
    <!-- ============================================
         FLASH MESAJLARI (BAŞARI/HATA)
         ============================================
         Önceki işlemlerden gelen mesajları göster
    -->
    <?php if ($message = getFlash('admin_success')): ?>
        <!-- Başarı mesajı (yeşil) -->
        <div class="alert alert-success" style="margin-bottom: 1rem;"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error = getFlash('admin_error')): ?>
        <!-- Hata mesajı (kırmızı) -->
        <div class="alert alert-error" style="margin-bottom: 1rem;"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    
    <!-- ============================================
         UYARI KUTUSU
         ============================================
         Kullanıcıyı işlemin geri alınamaz olduğu konusunda uyarır
    -->
    <div style="margin-bottom: 2rem; padding: 1rem; background: #fee2e2; border-radius: 0.5rem; border: 1px solid #dc2626;">
        <h3 style="margin-top: 0; color: #dc2626;">⚠️ Uyarı</h3>
        <p style="margin: 0.5rem 0; color: #991b1b;">
            Bu sayfadan <strong>ürünleri kalıcı olarak silebilirsiniz</strong>. Bu işlem geri alınamaz!
        </p>
        <p style="margin: 0.5rem 0; color: #991b1b;">
            <strong>Toplam <?php echo $totalProducts; ?> ürün</strong> mevcut.
        </p>
    </div>
    
    <!-- ============================================
         ÜRÜN LİSTESİ (SADECE ÜRÜN VARSA)
         ============================================
         Eğer hiç ürün yoksa liste gösterilmez
    -->
    <?php if ($totalProducts > 0): ?>
        <!-- ============================================
             ÖZET BİLGİ KUTUSU
             ============================================
             Toplam ürün ve kategori sayısını gösterir
        -->
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f0f9ff; border-radius: 0.5rem; border: 1px solid #0ea5e9;">
            <h3 style="margin-top: 0; color: #0369a1;">📊 Özet</h3>
            <p style="margin: 0.5rem 0; color: #0c4a6e;">
                <strong>Toplam Ürün:</strong> <?php echo $totalProducts; ?>
            </p>
            <p style="margin: 0.5rem 0; color: #0c4a6e;">
                <strong>Kategori Sayısı:</strong> <?php echo count($categories); ?>
            </p>
        </div>

        <!-- Ürün listesi başlığı -->
        <h3>Ürün Listesi</h3>
        
        <!-- ============================================
             TÜMÜNÜ SEÇ CHECKBOX'U
             ============================================
             Tüm ürünleri tek seferde seçmek için
        -->
        <div class="select-all-container" style="margin-bottom: 1rem; padding: 0.75rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
            <label class="select-all-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text);">
                <input type="checkbox" id="selectAll" class="select-all-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                <strong style="color: var(--text);">Tümünü Seç</strong>
            </label>
        </div>

        <!-- ============================================
             SİLME FORMU
             ============================================
             POST metodu ile toplu silme işlemi yapılır
        -->
        <form method="post" action="fix_products.php" id="deleteForm">
            <?php echo csrf_field(); ?>
            <div style="max-height: 500px; overflow-y: auto; border: 1px solid var(--border); border-radius: 0.5rem; margin-bottom: 1rem;">
                <table class="admin-products-table" style="width: 100%; margin: 0;">
                    <!-- Tablo başlıkları (sticky - scroll'da üstte kalır) -->
                    <thead style="position: sticky; top: 0; background: var(--light); z-index: 10;">
                        <tr>
                            <!-- Checkbox sütunu (header'da da checkbox var) -->
                            <th style="width: 40px; padding: 0.75rem; text-align: center;">
                                <input type="checkbox" id="selectAllHeader" class="select-all-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                            </th>
                            <th style="padding: 0.75rem; text-align: left;">ID</th>
                            <th style="padding: 0.75rem; text-align: left;">Ürün</th>
                            <?php if ($hasAuthorColumn): ?>
                                <th style="padding: 0.75rem; text-align: left;">Yazar</th>
                            <?php endif; ?>
                            <th style="padding: 0.75rem; text-align: left;">Kategori</th>
                            <th style="padding: 0.75rem; text-align: right; white-space: nowrap; min-width: 100px;">Fiyat</th>
                            <th style="padding: 0.75rem; text-align: center;">Stok</th>
                            <th style="padding: 0.75rem; text-align: center;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ============================================
                             ÜRÜN SATIRLARI
                             ============================================
                             Her ürün için bir satır oluştur
                        -->
                        <?php foreach ($products as $product): ?>
                            <tr style="border-top: 1px solid var(--border);">
                                <!-- Checkbox sütunu (ürün seçimi için) -->
                                <td style="padding: 0.75rem; text-align: center;">
                                    <!-- name="silinecek_id[]" ile dizi olarak gönderilir -->
                                    <input type="checkbox" name="silinecek_id[]" value="<?php echo (int) $product['id']; ?>" class="product-checkbox select-all-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                </td>
                                
                                <!-- Ürün ID'si -->
                                <td style="padding: 0.75rem;"><?php echo $product['id']; ?></td>
                                
                                <!-- Ürün adı ve resmi (admin/products.php ile aynı yapı) -->
                                <td class="product-cell" style="padding: 0.75rem; vertical-align: middle;">
                                    <?php 
                                    
                                    $imageUrl = normalizeImageUrl($product['image_url'] ?? null);
                                    ?>
                                    <a href="<?php echo BASE_URL; ?>/product.php?slug=<?php echo sanitize($product['slug']); ?>" style="display: inline-block; margin-right: 0.5rem; vertical-align: middle;">
                                        <img src="<?php echo sanitize($imageUrl); ?>" 
                                             alt="<?php echo sanitize($product['name']); ?>"
                                             class="product-thumbnail"
                                             style="width: 60px; height: 90px; object-fit: contain; border-radius: 0.25rem;"
                                             onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/img/icon.png'; this.style.opacity='0.3';">
                                    </a>
                                    <span class="product-name-text" style="vertical-align: middle;"><?php echo sanitize($product['name']); ?></span>
                                </td>
                                
                                <?php if ($hasAuthorColumn): ?>
                                    <!-- Yazar adı (yoksa '-' göster) -->
                                    <td style="padding: 0.75rem; vertical-align: middle;"><?php echo sanitize($product['author'] ?? '-'); ?></td>
                                <?php endif; ?>
                                
                                <!-- Kategori adı (yoksa '-' göster) -->
                                <td style="padding: 0.75rem; vertical-align: middle;"><?php echo sanitize($product['category_name'] ?? '-'); ?></td>
                                
                                <!-- Ürün fiyatı (2 ondalık basamak, Türk Lirası formatında) -->
                                <td style="padding: 0.75rem; text-align: right; vertical-align: middle; white-space: nowrap;"><?php echo number_format((float) $product['price'], 2); ?> ₺</td>
                                
                                <!-- Stok miktarı (tam sayı) -->
                                <td style="padding: 0.75rem; text-align: center; vertical-align: middle;"><?php echo (int) $product['stock']; ?></td>
                                
                                <!-- İşlemler sütunu (tek tek silme butonu) -->
                                <td style="padding: 0.75rem; text-align: center; vertical-align: middle;">
                                    <!-- GET parametresi ile tek tek silme linki -->
                                    <!-- onclick ile JavaScript confirm() dialog'u göster -->
                                    <a class="btn" href="fix_products.php?islem=sil&id=<?php echo $product['id']; ?>" 
                                       onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?\n\nÜrün: <?php echo addslashes($product['name']); ?>\n\nBu işlem geri alınamaz!');"
                                       style="background: #dc2626; color: white; padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                        Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================
                 TOPLU SİLME BUTONLARI
                 ============================================
                 Seçili ürünleri veya tümünü silmek için
            -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <!-- Seçilen ürünleri sil butonu -->
                <!-- disabled: Hiç ürün seçilmediyse buton pasif -->
                <!-- onclick: JavaScript confirm() ile onay al -->
                <button type="submit" name="islem" value="toplu_sil" class="btn" style="background: #dc2626; color: white; flex: 1;" id="deleteSelectedBtn" disabled
                        onclick="return confirm('Seçili ürünleri silmek istediğinize emin misiniz?');">
                    Seçilen Ürünleri Sil (<span id="selectedCount">0</span>)
                </button>
                
                <!-- Tümünü sil butonu (daha koyu kırmızı) -->
                <!-- onclick: Çift onay (daha tehlikeli işlem) -->
                <button type="submit" name="islem" value="hepsini_sil" class="btn" style="background: #991b1b; color: white; flex: 1;" id="deleteAllBtn"
                        onclick="return confirm('TÜM ÜRÜNLERİ silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                    Tümünü Sil
                </button>
            </div>
            
            <!-- İptal butonu (ürün yönetimine dön) -->
            <a href="<?php echo BASE_URL; ?>/admin/products.php" class="btn btn-outline" style="width: 100%; display: block; text-align: center;">İptal</a>
        </form>
    <?php else: ?>
        <!-- ============================================
             ÜRÜN YOKSA GÖSTERİLEN MESAJ
             ============================================
             Veritabanında hiç ürün yoksa bu mesaj gösterilir
        -->
        <p style="color: var(--muted); margin-bottom: 2rem;">Veritabanında ürün bulunmuyor.</p>
        <a href="<?php echo BASE_URL; ?>/admin/products.php" class="btn btn-outline" style="width: 100%; display: block; text-align: center;">Ürün Yönetimine Dön</a>
    <?php endif; ?>
</section>

<!-- ============================================
     JAVASCRIPT - CHECKBOX YÖNETİMİ
     ============================================
     Tümünü seç, seçili sayısı güncelleme ve buton durumu
-->
<script>
// ============================================
// DOM YÜKLENDİĞİNDE ÇALIŞ
// ============================================
// DOMContentLoaded ile güvenli yükleme
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // DOM ELEMANLARINI AL
    // ============================================
    // Tüm checkbox ve buton elementlerini güvenli şekilde al
    const selectAllCheckbox = document.getElementById('selectAll'); // Ana "Tümünü Seç" checkbox'ı
    const selectAllHeader = document.getElementById('selectAllHeader'); // Tablo header'daki checkbox
    const productCheckboxes = document.querySelectorAll('.product-checkbox'); // Tüm ürün checkbox'ları
    const deleteForm = document.getElementById('deleteForm'); // Silme formu
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn'); // "Seçilenleri Sil" butonu
    const deleteAllBtn = document.getElementById('deleteAllBtn'); // "Tümünü Sil" butonu
    const selectedCountSpan = document.getElementById('selectedCount'); // Seçili sayısı gösteren span

    // ============================================
    // ERKEN ÇIKIŞ KONTROLÜ
    // ============================================
    // Eğer ürün listesi boşsa veya elemanlar yoksa, script çalışmasın
    if (!selectAllCheckbox || !selectAllHeader || productCheckboxes.length === 0) {
        // Ürün listesi boş veya checkbox'lar render edilmemiş
        return; // Script'i durdur
    }

    // ============================================
    // TÜMÜNÜ SEÇ/KALDIR FONKSİYONU
    // ============================================
    // Checkbox durumlarını senkronize et
    function updateSelectAll() {
        // Null check (güvenlik)
        if (!selectAllCheckbox || !selectAllHeader || productCheckboxes.length === 0) {
            return;
        }
        
        // Tüm checkbox'lar seçili mi kontrol et
        const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
        
        // En az bir checkbox seçili mi kontrol et
        const someChecked = Array.from(productCheckboxes).some(cb => cb.checked);
        
        // "Tümünü Seç" checkbox'larını güncelle
        selectAllCheckbox.checked = allChecked;
        selectAllHeader.checked = allChecked;
        
        // Indeterminate durumu (yarı seçili görünüm)
        // Bazıları seçili ama hepsi değilse indeterminate göster
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
        selectAllHeader.indeterminate = someChecked && !allChecked;
    }

    // ============================================
    // SEÇİLİ SAYISINI GÜNCELLE FONKSİYONU
    // ============================================
    // Seçili ürün sayısını göster ve buton durumunu güncelle
    function updateSelectedCount() {
        // Null check (güvenlik)
        if (!selectedCountSpan || !deleteSelectedBtn || productCheckboxes.length === 0) {
            return;
        }
        
        // Seçili checkbox'ları say
        const selected = Array.from(productCheckboxes).filter(cb => cb.checked).length;
        
        // Seçili sayısını göster
        selectedCountSpan.textContent = selected;
        
        // Eğer hiç seçili yoksa butonu pasif yap
        deleteSelectedBtn.disabled = selected === 0;
    }

    // ============================================
    // TÜMÜNÜ SEÇ CHECKBOX EVENT LISTENER
    // ============================================
    // Ana "Tümünü Seç" checkbox'ına tıklandığında
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            // Tüm ürün checkbox'larını bu checkbox'ın durumuna göre ayarla
            productCheckboxes.forEach(cb => cb.checked = this.checked);
            
            // Seçili sayısını güncelle
            updateSelectedCount();
        });
    }

    // ============================================
    // HEADER CHECKBOX EVENT LISTENER
    // ============================================
    // Tablo header'daki "Tümünü Seç" checkbox'ına tıklandığında
    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            // Tüm ürün checkbox'larını bu checkbox'ın durumuna göre ayarla
            productCheckboxes.forEach(cb => cb.checked = this.checked);
            
            // Ana checkbox'ı da senkronize et
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = this.checked;
            }
            
            // Seçili sayısını güncelle
            updateSelectedCount();
        });
    }

    // ============================================
    // ÜRÜN CHECKBOX EVENT LISTENER
    // ============================================
    // Her bir ürün checkbox'ına tıklandığında
    productCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            // "Tümünü Seç" durumunu güncelle
            updateSelectAll();
            
            // Seçili sayısını güncelle
            updateSelectedCount();
        });
    });

    // ============================================
    // FORM SUBMIT EVENT LISTENER
    // ============================================
    // Form gönderilmeden önce ekstra kontroller yap
    // Butonlarda onclick zaten var, burada ikinci bir güvenlik katmanı ekliyoruz
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            // Hangi butonun tıklandığını al (submitter API)
            const submitter = e.submitter;
            if (!submitter) return; // Submitter yoksa çık
            
            // Butonun value değerini al (islem parametresi)
            const islem = submitter.value;
            
            // ============================================
            // TÜMÜNÜ SİL KONTROLÜ
            // ============================================
            if (islem === 'hepsini_sil') {
                // Buton onclick zaten kontrol ediyor, burada çift onay (double confirm)
                // Daha tehlikeli işlem olduğu için ekstra uyarı
                const confirmed = confirm('SON UYARI!\n\nTüm ürünler silinecek. Bu işlemi gerçekten yapmak istiyor musunuz?');
                if (!confirmed) {
                    e.preventDefault(); // Onaylanmadıysa form gönderimini engelle
                }
            } 
            // ============================================
            // SEÇİLENLERİ SİL KONTROLÜ
            // ============================================
            else if (islem === 'toplu_sil') {
                // Seçili checkbox'ları kontrol et
                const selected = Array.from(productCheckboxes).filter(cb => cb.checked);
                
                // Eğer hiç seçili yoksa uyar ve form gönderimini engelle
                if (selected.length === 0) {
                    e.preventDefault(); // Form gönderimini engelle
                    alert('Lütfen silmek istediğiniz ürünleri seçin.');
                    return;
                }
                // Buton onclick zaten kontrol ediyor, burada ekstra bir kontrol yapmaya gerek yok
            }
        });
    }

    // ============================================
    // İLK YÜKLEMEDE GÜNCELLEME
    // ============================================
    // Sayfa yüklendiğinde seçili sayısını güncelle (varsayılan: 0)
    updateSelectedCount();
});
</script>

<!-- ============================================
     FOOTER ŞABLONUNU YÜKLE
     ============================================
     Admin paneli footer'ını include et
-->
<style>
/* Dark mode için checkbox ve label stilleri */
body.dark-theme .select-all-container {
    background: #1e293b !important;
    border-color: var(--border) !important;
}

body.dark-theme .select-all-label,
body.dark-theme .select-all-label strong {
    color: var(--text) !important;
}

/* Dark mode için checkbox stilleri */
body.dark-theme .select-all-checkbox,
body.dark-theme .product-checkbox {
    background: #1e293b !important;
    border: 2px solid var(--border) !important;
    accent-color: var(--primary) !important;
}

body.dark-theme .select-all-checkbox:checked,
body.dark-theme .product-checkbox:checked {
    background: var(--primary) !important;
    border-color: var(--primary) !important;
}

body.dark-theme .select-all-checkbox:focus,
body.dark-theme .product-checkbox:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>

