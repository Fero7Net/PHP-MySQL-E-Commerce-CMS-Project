<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

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
$errors = [];
$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : null;
$editingProduct = null;
$categoryOptions = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['id'])) {

    if (!adminIsLoggedIn()) {
        setFlash('admin_error', 'Yetkiniz yok.');
        redirect('products.php');
        exit;
    }
    
    $id = (int) $_GET['id'];

    if ($id <= 0) {
        setFlash('admin_error', 'Geçersiz ürün ID: ' . htmlspecialchars($_GET['id'] ?? ''));
        redirect('products.php');
        exit;
    }
    
    try {
        
        $checkStmt = $pdo->prepare('SELECT id, image_url FROM products WHERE id = :id');
        $checkStmt->execute(['id' => $id]);
        $product = $checkStmt->fetch();
        
        if (!$product) {
            setFlash('admin_error', 'Ürün bulunamadı (ID: ' . $id . ').');
            redirect('products.php');
            exit;
        }

        $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);

        $deletedRows = $statement->rowCount();
        
        if ($deletedRows === 0) {
            
            setFlash('admin_error', 'Ürün silinemedi. Muhtemelen bu ürün başka bir tabloda kullanılıyor (Foreign Key hatası).');
            redirect('products.php');
            exit;
        }

        if (!empty($product['image_url'])) {
            $productsImgDir = __DIR__ . '/../products_img';
            deleteImageFile($product['image_url'], $productsImgDir);
        }
        
        setFlash('admin_success', 'Ürün başarıyla silindi. (Silinen satır sayısı: ' . $deletedRows . ')');
    } catch (PDOException $e) {
        
        $errorMessage = 'Silme işlemi sırasında bir hata oluştu.';

        if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), '1451') !== false) {
            $errorMessage = 'Bu ürün başka bir tabloda kullanılıyor (siparişler, yorumlar vb.) ve bu nedenle silinemiyor.';
        } else {
            
            $errorMessage .= ' Hata: ' . htmlspecialchars($e->getMessage());
        }
        
        setFlash('admin_error', $errorMessage);
    } catch (Exception $e) {
        setFlash('admin_error', 'Beklenmeyen bir hata oluştu: ' . htmlspecialchars($e->getMessage()));
    }
    
    $params = $_GET;
    unset($params['islem'], $params['id']);
    redirect('products.php?' . http_build_query(array_filter($params)));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['islem'])) {
        if ($_POST['islem'] === 'secilenleri_sil') {
            
            if (isset($_POST['silinecek_id']) && is_array($_POST['silinecek_id']) && !empty($_POST['silinecek_id'])) {
                $selectedIds = array_map('intval', $_POST['silinecek_id']); 
                $selectedIds = array_filter($selectedIds, function($id) { return $id > 0; }); 
                
                if (!empty($selectedIds)) {
                    try {
                        
                        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                        $imageStmt = $pdo->prepare("SELECT id, image_url FROM products WHERE id IN ($placeholders)");
                        $imageStmt->execute($selectedIds);
                        $productsToDelete = $imageStmt->fetchAll();

                        $productsImgDir = __DIR__ . '/../products_img';
                        
                        foreach ($productsToDelete as $product) {
                            if (!empty($product['image_url'])) {
                                deleteImageFile($product['image_url'], $productsImgDir);
                            }
                        }

                        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                        $deleteStmt->execute($selectedIds);
                        $deletedCount = $deleteStmt->rowCount();
                        
                        setFlash('admin_success', $deletedCount . ' ürün başarıyla silindi.');
                        $redirectParams = array_filter([
                            'sort' => $_POST['redirect_sort'] ?? 'newest',
                            'search' => trim($_POST['redirect_search'] ?? ''),
                        ], function ($v) { return $v !== ''; });
                        redirect('products.php?' . http_build_query($redirectParams));
                    } catch (PDOException $e) {
                        $errorMessage = 'Toplu silme işlemi sırasında bir hata oluştu.';
                        if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), '1451') !== false) {
                            $errorMessage = 'Bazı ürünler başka tablolarda kullanılıyor (siparişler, yorumlar vb.) ve bu nedenle silinemiyor.';
                        }
                        setFlash('admin_error', $errorMessage);
                    }
                } else {
                    setFlash('admin_error', 'Geçerli ürün seçilmedi.');
                }
            } else {
                setFlash('admin_error', 'Lütfen silmek istediğiniz ürünleri seçin.');
            }
            $rp = array_filter(['sort' => $_POST['redirect_sort'] ?? 'newest', 'search' => trim($_POST['redirect_search'] ?? '')], function ($v) { return $v !== ''; });
            redirect('products.php?' . http_build_query($rp));
            exit;
        } elseif ($_POST['islem'] === 'tumunu_sil') {
            
            try {
                
                $allProductsStmt = $pdo->query("SELECT id, image_url FROM products");
                $allProducts = $allProductsStmt->fetchAll();

                $productsImgDir = __DIR__ . '/../products_img';
                
                foreach ($allProducts as $product) {
                    if (!empty($product['image_url'])) {
                        deleteImageFile($product['image_url'], $productsImgDir);
                    }
                }

                $deleteAllStmt = $pdo->query("DELETE FROM products");
                $deletedCount = $deleteAllStmt->rowCount();
                
                setFlash('admin_success', 'Tüm ürünler başarıyla silindi. (Silinen satır sayısı: ' . $deletedCount . ')');
                $redirectParams = array_filter([
                    'sort' => $_POST['redirect_sort'] ?? 'newest',
                    'search' => trim($_POST['redirect_search'] ?? ''),
                ], function ($v) { return $v !== ''; });
                redirect('products.php?' . http_build_query($redirectParams));
            } catch (PDOException $e) {
                $errorMessage = 'Tümünü silme işlemi sırasında bir hata oluştu.';
                if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), '1451') !== false) {
                    $errorMessage = 'Bazı ürünler başka tablolarda kullanılıyor (siparişler, yorumlar vb.) ve bu nedenle silinemiyor.';
                }
                setFlash('admin_error', $errorMessage);
            }
            $rp = array_filter(['sort' => $_POST['redirect_sort'] ?? 'newest', 'search' => trim($_POST['redirect_search'] ?? '')], function ($v) { return $v !== ''; });
            redirect('products.php?' . http_build_query($rp));
            exit;
        }
    }
    
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        
        if (!empty($imageUrl)) {
            $productsImgDir = __DIR__ . '/../products_img';
            $imageUrl = commitTempProductImage($imageUrl, $productsImgDir);
        }
        
        if (empty($imageUrl)) {
            $imageUrl = BASE_URL . '/img/icon.png';
        }

        if ($name === '') {
            $errors[] = 'Ürün adı zorunludur.';
        }

        if ($categoryId === 0) {
            $errors[] = 'Kategori seçiniz.';
        }

        if ($price <= 0) {
            $errors[] = 'Fiyat sıfırdan büyük olmalıdır.';
        }

        if (!$errors) {
            if ($action === 'create') {
                $pdo->beginTransaction();
                try {
                    $nextId = getNextAvailableId($pdo, 'products');
                    if ($hasAuthorColumn) {
                        $statement = $pdo->prepare('INSERT INTO products (id, name, slug, category_id, description, author, price, stock, image_url) VALUES (:id, :name, :slug, :category_id, :description, :author, :price, :stock, :image_url)');
                        $statement->execute([
                            'id' => $nextId,
                            'name' => $name,
                            'slug' => slugify($name),
                            'category_id' => $categoryId,
                            'description' => $description,
                            'author' => $author ?: null,
                            'price' => $price,
                            'stock' => $stock,
                            'image_url' => $imageUrl,
                        ]);
                    } else {
                        $statement = $pdo->prepare('INSERT INTO products (id, name, slug, category_id, description, price, stock, image_url) VALUES (:id, :name, :slug, :category_id, :description, :price, :stock, :image_url)');
                        $statement->execute([
                            'id' => $nextId,
                            'name' => $name,
                            'slug' => slugify($name),
                            'category_id' => $categoryId,
                            'description' => $description,
                            'price' => $price,
                            'stock' => $stock,
                            'image_url' => $imageUrl,
                        ]);
                    }
                    $pdo->commit();
                    try {
                        updateTableAutoIncrement($pdo, 'products');
                    } catch (Exception $e) {
                        
                    }
                    setFlash('admin_success', 'Ürün eklendi.');
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $errors[] = 'Ürün eklenirken bir hata oluştu.';
                }
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                if ($hasAuthorColumn) {
                    $statement = $pdo->prepare('UPDATE products SET name = :name, slug = :slug, category_id = :category_id, description = :description, author = :author, price = :price, stock = :stock, image_url = :image_url WHERE id = :id');
                    $statement->execute([
                        'name' => $name,
                        'slug' => slugify($name),
                        'category_id' => $categoryId,
                        'description' => $description,
                        'author' => $author ?: null,
                        'price' => $price,
                        'stock' => $stock,
                        'image_url' => $imageUrl,
                        'id' => $id,
                    ]);
                } else {
                    $statement = $pdo->prepare('UPDATE products SET name = :name, slug = :slug, category_id = :category_id, description = :description, price = :price, stock = :stock, image_url = :image_url WHERE id = :id');
                    $statement->execute([
                        'name' => $name,
                        'slug' => slugify($name),
                        'category_id' => $categoryId,
                        'description' => $description,
                        'price' => $price,
                        'stock' => $stock,
                        'image_url' => $imageUrl,
                        'id' => $id,
                    ]);
                }
                setFlash('admin_success', 'Ürün güncellendi.');
            }
            $rp = array_filter(['sort' => $_POST['redirect_sort'] ?? 'newest', 'search' => trim($_POST['redirect_search'] ?? '')], function ($v) { return $v !== ''; });
            redirect('products.php?' . http_build_query($rp));
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        $productStmt = $pdo->prepare('SELECT image_url FROM products WHERE id = :id');
        $productStmt->execute(['id' => $id]);
        $product = $productStmt->fetch();

        $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);

        if ($product && !empty($product['image_url'])) {
            $productsImgDir = __DIR__ . '/../products_img';
            deleteImageFile($product['image_url'], $productsImgDir);
        }
        
        setFlash('admin_success', 'Ürün silindi.');
        $rp = array_filter(['sort' => $_POST['redirect_sort'] ?? 'newest', 'search' => trim($_POST['redirect_search'] ?? '')], function ($v) { return $v !== ''; });
        redirect('products.php?' . http_build_query($rp));
    }
}

if ($editId) {
    $statement = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $statement->execute(['id' => $editId]);
    $editingProduct = $statement->fetch();
}

$itemsPerPage = 15;
$sort = isset($_GET['sort']) ? (string) $_GET['sort'] : 'newest';
$search = trim($_GET['search'] ?? '');
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

$sortOptions = [
    'newest' => 'Son Eklenen',
    'oldest' => 'En Eski',
    'name_asc' => 'Alfabe (A-Z)',
    'name_desc' => 'Alfabe (Z-A)',
    'price_asc' => 'Fiyat (Düşük → Yüksek)',
    'price_desc' => 'Fiyat (Yüksek → Düşük)',
];
if (!isset($sortOptions[$sort])) {
    $sort = 'newest';
}

switch ($sort) {
    case 'price_asc':
        $orderBy = 'p.price ASC';
        break;
    case 'price_desc':
        $orderBy = 'p.price DESC';
        break;
    case 'name_asc':
        $orderBy = 'p.name ASC';
        break;
    case 'name_desc':
        $orderBy = 'p.name DESC';
        break;
    case 'oldest':
        $orderBy = 'p.id ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'p.id DESC';
        break;
}

$whereClause = '';
$params = [];
if ($search !== '') {
    $whereClause = 'WHERE (p.name LIKE :search OR p.description LIKE :search OR p.author LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM products p ' . $whereClause);
$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();

$pagination = getPaginationData($currentPage, $totalProducts, $itemsPerPage);
$offset = $pagination['offset'];

$productsQuery = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ' . $whereClause . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
$productsStmt = $pdo->prepare($productsQuery);
foreach ($params as $k => $v) {
    $productsStmt->bindValue(':' . $k, $v);
}
$productsStmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$productsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$productsStmt->execute();
$products = $productsStmt->fetchAll();

if (!empty($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div id="admin-products-ajax-container">';
    include __DIR__ . '/partials/admin_products_list_content.php';
    echo '</div>';
    exit;
}

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem;">
    <h1>Ürün Yönetimi</h1>
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
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
        <input type="hidden" name="action" value="<?php echo $editingProduct ? 'update' : 'create'; ?>">
        <input type="hidden" name="redirect_sort" value="<?php echo sanitize($sort); ?>">
        <input type="hidden" name="redirect_search" value="<?php echo sanitize($search); ?>">
        <?php if ($editingProduct): ?>
            <input type="hidden" name="id" value="<?php echo $editingProduct['id']; ?>">
        <?php endif; ?>

        <label for="name">Ürün Adı</label>
        <input id="name" name="name" value="<?php echo sanitize($editingProduct['name'] ?? ($_POST['name'] ?? '')); ?>" required>

        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id" required>
            <option value="">Seçiniz</option>
            <?php foreach ($categoryOptions as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo (int) ($editingProduct['category_id'] ?? ($_POST['category_id'] ?? 0)) === (int) $category['id'] ? 'selected' : ''; ?>>
                    <?php echo sanitize($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($hasAuthorColumn): ?>
            <label for="author">Yazar</label>
            <input id="author" name="author" value="<?php echo sanitize($editingProduct['author'] ?? ($_POST['author'] ?? '')); ?>" placeholder="Yazar adı (opsiyonel)">
        <?php else: ?>
            <div class="alert alert-error" style="margin-bottom: 1rem;">
                <strong>⚠️ Uyarı:</strong> Author kolonu henüz eklenmemiş. 
                <a href="<?php echo BASE_URL; ?>/_backup_add_author_column.php" style="color: #dc2626; text-decoration: underline;">Buraya tıklayarak</a> kolonu ekleyebilirsiniz. (Not: Dosya yedeklenmiş durumda, gerekirse yorum satırlarını kaldırarak kullanabilirsiniz)
            </div>
        <?php endif; ?>

        <label for="price">Fiyat</label>
        <input id="price" name="price" type="number" step="0.01" value="<?php echo sanitize((string) ($editingProduct['price'] ?? ($_POST['price'] ?? '0'))); ?>" required>

        <label for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0" value="<?php echo sanitize((string) ($editingProduct['stock'] ?? ($_POST['stock'] ?? '0'))); ?>" required>

        <label for="image_upload">Ürün Görseli</label>
        <input type="file" id="image_upload" name="image_upload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top: 0.5rem;">
        <input type="hidden" id="image_url" name="image_url" value="<?php echo sanitize($editingProduct['image_url'] ?? ($_POST['image_url'] ?? '')); ?>">
        <button type="button" id="upload_btn" class="btn" style="margin-top: 0.5rem;">Resim Yükle</button>
        <div id="upload_status" style="margin-top: 0.5rem;"></div>
        <div id="image_preview" style="margin-top: 0.5rem;">
            <?php if (!empty($editingProduct['image_url'] ?? '')): ?>
                <p style="margin: 0.5rem 0; color: var(--muted); font-size: 0.9rem;">Mevcut görsel:</p>
                <img id="preview_img" src="<?php echo sanitize(normalizeImageUrl($editingProduct['image_url'])); ?>" 
                     alt="Mevcut görsel" 
                     style="max-width: 200px; max-height: 200px; border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.5rem; background: var(--light);"
                     onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/img/icon.png'; this.style.opacity='0.3';">
            <?php endif; ?>
        </div>
        <script>
        (function() {
            var deleteTempUrl = '<?php echo BASE_URL; ?>/admin/delete_temp_image.php';
            var currentTempImageUrl = '';
            var urlInputEl = document.getElementById('image_url');
            if (urlInputEl && urlInputEl.value && urlInputEl.value.indexOf('products_img/temp/') !== -1) {
                currentTempImageUrl = urlInputEl.value;
            }

            document.getElementById('upload_btn').addEventListener('click', function() {
                var fileInput = document.getElementById('image_upload');
                var urlInput = urlInputEl;
                var statusDiv = document.getElementById('upload_status');
                var previewDiv = document.getElementById('image_preview');

                if (!fileInput.files.length) {
                    statusDiv.innerHTML = '<span style="color: red;">Lütfen bir dosya seçin.</span>';
                    return;
                }

                var formData = new FormData();
                formData.append('image', fileInput.files[0]);

                statusDiv.innerHTML = 'Yükleniyor...';

                fetch('<?php echo BASE_URL; ?>/admin/upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text().then(function(text) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 200));
                        }
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse hatası:', text);
                            var errorMatch = text.match(/<title>(.*?)<\/title>/i) || text.match(/Fatal error:.*?in.*?on line/i) || text.match(/Warning:.*?in.*?on line/i);
                            var errorMsg = errorMatch ? errorMatch[0].substring(0, 150) : text.substring(0, 200);
                            throw new Error('Sunucu hatası: ' + errorMsg);
                        }
                    });
                })
                .then(function(data) {
                    if (data.success) {
                        currentTempImageUrl = data.url;
                        urlInput.value = data.url;
                        statusDiv.innerHTML = '<span style="color: green;">✓ Resim başarıyla yüklendi! Formu gönderdiğinizde kaydedilecektir.</span>';

                        var previewImg = document.getElementById('preview_img');
                        if (!previewImg) {
                            previewDiv.innerHTML = '<p style="margin: 0.5rem 0; color: var(--muted); font-size: 0.9rem;">Yüklenen görsel:</p>';
                            previewImg = document.createElement('img');
                            previewImg.id = 'preview_img';
                            previewImg.alt = 'Yüklenen görsel';
                            previewImg.style.cssText = 'max-width: 200px; max-height: 200px; border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.5rem; background: var(--light);';
                            previewDiv.appendChild(previewImg);
                        }
                        previewImg.src = data.url;
                        fileInput.value = '';
                    } else {
                        statusDiv.innerHTML = '<span style="color: red;">' + (data.error || 'Bilinmeyen hata') + '</span>';
                    }
                })
                .catch(function(error) {
                    console.error('Hata:', error);
                    statusDiv.innerHTML = '<span style="color: red;">Bir hata oluştu: ' + error.message + '</span>';
                });
            });

            // Form gönderildiğinde temp silinmesin (resim asıl klasöre taşınacak)
            var productForm = document.getElementById('image_url').closest('form');
            if (productForm) productForm.addEventListener('submit', function() {
                window._productFormSubmitting = true;
            }, false);

            // Sayfa terk edilirse ve form gönderilmediyse temp resmi sil (veritabanına kaydedilmeyen)
            function cleanupTempIfNeeded() {
                if (window._productFormSubmitting || !currentTempImageUrl) return;
                if (currentTempImageUrl.indexOf('products_img/temp/') === -1) return;
                var body = 'image_url=' + encodeURIComponent(currentTempImageUrl);
                navigator.sendBeacon(deleteTempUrl, new Blob([body], { type: 'application/x-www-form-urlencoded' }));
            }
            window.addEventListener('beforeunload', cleanupTempIfNeeded);
            window.addEventListener('pagehide', cleanupTempIfNeeded);
        })();
        </script>

        <label for="description">Açıklama</label>
        <textarea id="description" name="description" rows="5"><?php echo sanitize($editingProduct['description'] ?? ($_POST['description'] ?? '')); ?></textarea>

        <button class="btn btn-primary" type="submit">
            <?php echo $editingProduct ? 'Ürünü Güncelle' : 'Ürün Ekle'; ?>
        </button>
        <?php if ($editingProduct): ?>
            <a class="btn" href="products.php">Formu Temizle</a>
        <?php endif; ?>
    </form>
</section>

<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h2>Ürün Listesi</h2>
    
    <div id="admin-products-ajax-container">
        <?php include __DIR__ . '/partials/admin_products_list_content.php'; ?>
    </div>
</section>

<?php if (!$editId): ?>
<script>
(function() {
    var container = document.getElementById('admin-products-ajax-container');
    if (!container) return;
    var baseUrl = (window.BASE_URL || '') + '/admin/products.php';

    function getFormParams(form) {
        var data = new FormData(form);
        var params = {};
        data.forEach(function(v, k) { if (v) params[k] = v; });
        return new URLSearchParams(params).toString();
    }
    function loadList(url) {
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var wrap = doc.getElementById('admin-products-ajax-container');
                if (wrap) container.innerHTML = wrap.innerHTML;
                container.style.opacity = '';
                container.style.pointerEvents = '';
                initPagination();
            })
            .catch(function() {
                container.style.opacity = '';
                container.style.pointerEvents = '';
                window.location.href = url.replace(/[?&]ajax=[^&]*/g, '').replace(/&$/, '');
            });
    }
    function initPagination() {
        container.querySelectorAll('a.admin-pagination-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var href = link.getAttribute('href') || '';
                if (!href) return;
                var url = href + (href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1';
                history.replaceState(null, '', href);
                loadList(url);
            });
        });
    }
    container.addEventListener('change', function(e) {
        if (e.target.id === 'selectAll') {
            container.querySelectorAll('.product-checkbox').forEach(function(cb) { cb.checked = e.target.checked; });
        } else if (e.target.classList.contains('product-checkbox')) {
            var all = container.querySelectorAll('.product-checkbox');
            var selectAll = container.querySelector('#selectAll');
            if (selectAll && all.length) selectAll.checked = Array.from(all).every(function(cb) { return cb.checked; });
        } else {
            var filterForm = container.querySelector('#admin-products-filter-form');
            if (filterForm && filterForm.contains(e.target)) {
                var params = getFormParams(filterForm);
                var url = baseUrl + (params ? '?' + params : '') + (params ? '&' : '?') + 'ajax=1';
                history.replaceState(null, '', baseUrl + (params ? '?' + params : ''));
                loadList(url);
            }
        }
    });
    container.addEventListener('submit', function(e) {
        var form = e.target;
        if (form.id !== 'admin-products-filter-form' && form.id !== 'bulkDeleteForm') return;
        if (form.method && form.method.toLowerCase() === 'get' && form.id === 'admin-products-filter-form') {
            e.preventDefault();
            var params = getFormParams(form);
            var url = baseUrl + (params ? '?' + params : '') + (params ? '&' : '?') + 'ajax=1';
            history.replaceState(null, '', baseUrl + (params ? '?' + params : ''));
            loadList(url);
            return false;
        }
    });
    initPagination();
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>

