<?php

require __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';

$product = $slug ? findProductBySlug($pdo, $slug) : null;

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Ürün Bulunamadı';
    include __DIR__ . '/partials/header.php';
    echo '<section class="card"><h2>Ürün bulunamadı</h2><p>Aradığınız ürün mevcut değil.</p></section>';
    include __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = sanitize($product['name']);
logVisit($pdo, $_SERVER['REQUEST_URI'] ?? '');
ensureCommentsTableColumns($pdo);

$commentError = null;
$commentSuccess = false;
$commentSuccessMessage = getFlash('success');

if ($commentSuccessMessage !== null) {
    $commentSuccess = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment'])) {
        if (!validate_csrf()) {
            $commentError = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
        } elseif (!canUseCart()) {
            $commentError = 'Yorum yapmak için giriş yapmanız gerekiyor.';
        } else {
            $user = getCheckoutUser($pdo);
            $userEmail = $user ? ((string) ($user['email'] ?? '')) : '';
            if ($userEmail !== '' && !hasUserPurchasedProduct($pdo, $userEmail, (int) $product['id'])) {
                $commentError = 'Bu ürüne yorum yapmak için önce ürünü satın almanız gerekiyor.';
            } else {
                $content = trim((string) ($_POST['content'] ?? ''));

                if ($content === '') {
                    $commentError = 'Yorum içeriği zorunludur.';
                } elseif (!$user) {
                    $commentError = 'Kullanıcı bilgisi alınamadı. Tekrar giriş yapmayı deneyin.';
                } else {
                    $authorName = (string) ($user['full_name'] ?? $user['username'] ?? 'Misafir');
                    $authorEmail = (string) ($user['email'] ?? '');
                    $imageUrls = [];
                    if (!empty($_FILES['comment_images']['tmp_name']) && is_array($_FILES['comment_images']['tmp_name'])) {
                        $imageUrls = processCommentImages($_FILES['comment_images'], __DIR__);
                    } elseif (!empty($_FILES['comment_images']['tmp_name'])) {
                        $imageUrls = processCommentImages($_FILES['comment_images'], __DIR__);
                    }

                    $parentId = isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
                    $rating = isset($_POST['comment_rating']) ? (int) $_POST['comment_rating'] : null;
                    if (addComment($pdo, (int) $product['id'], $authorName, $authorEmail, $content, $imageUrls, $parentId, $rating)) {
                        $commentSuccess = true;
                        setFlash('success', 'Yorumunuz gönderildi. Onaylandıktan sonra yayınlanacaktır.');
                        redirect('product.php?slug=' . $slug);
                    } else {
                        $commentError = (getSetting($pdo, 'comments_enabled', '1') !== '1')
                            ? 'Yorumlar şu an kapalı; yeni yorum eklenemiyor.'
                            : 'Yorum eklenemedi.';
                    }
                }
            }
        }
    } else {
        
    }
}

$commentSort = isset($_GET['comment_sort']) ? (string) $_GET['comment_sort'] : 'newest';
$commentSortOptions = ['newest' => 'Tarihe göre (en yeni)', 'oldest' => 'Tarihe göre (en eski)', 'rating_desc' => 'En yüksek puanlı', 'rating_asc' => 'En düşük puanlı', 'random' => 'Rastgele'];
if (!array_key_exists($commentSort, $commentSortOptions)) {
    $commentSort = 'newest';
}
$comments = getCommentsByProductSorted($pdo, (int) $product['id'], $commentSort, 50);
$commentRepliesByParent = !empty($comments) ? getCommentRepliesBatch($pdo, array_column($comments, 'id')) : [];
$commentsEnabled = getSetting($pdo, 'comments_enabled', '1') === '1';
$productAverageRating = getProductAverageRating($pdo, (int) $product['id']);
$commentUser = canUseCart() ? getCheckoutUser($pdo) : null;
$commentUserEmail = $commentUser ? ((string) ($commentUser['email'] ?? '')) : '';
$canComment = $commentsEnabled && canUseCart() && hasUserPurchasedProduct($pdo, $commentUserEmail, (int) $product['id']);

$pageDescription = mb_substr(strip_tags($product['description'] ?? $product['name']), 0, 160) . (mb_strlen($product['description'] ?? '') > 160 ? '…' : '');
$canonicalUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/urun/' . rawurlencode($slug) : '';
$productImageUrl = normalizeImageUrl($product['image_url'] ?? null);
$ogImage = $productImageUrl && strpos($productImageUrl, 'http') === 0 ? $productImageUrl : (defined('SITE_URL') ? rtrim(SITE_URL, '/') . (strpos((string)$productImageUrl, BASE_URL) === 0 ? substr($productImageUrl, strlen(BASE_URL)) : (strpos((string)$productImageUrl, '/') === 0 ? $productImageUrl : '/' . $productImageUrl)) : $productImageUrl);
$ogType = 'product';

$jsonLdProduct = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['description'] ?? $product['name'],
    'image' => $ogImage,
    'url' => $canonicalUrl,
    'sku' => 'product-' . (int) $product['id'],
    'offers' => [
        '@type' => 'Offer',
        'url' => $canonicalUrl,
        'priceCurrency' => 'TRY',
        'price' => (float) $product['price'],
        'availability' => 'https://schema.org/' . ((int) ($product['stock'] ?? 0) > 0 ? 'InStock' : 'OutOfStock'),
    ],
];
if ($productAverageRating !== null) {
    $ratingCount = 0;
    foreach ($comments as $c) {
        if (isset($c['rating']) && $c['rating'] !== null && $c['rating'] !== '') {
            $ratingCount++;
        }
    }
    $jsonLdProduct['aggregateRating'] = [
        '@type' => 'AggregateRating',
        'ratingValue' => $productAverageRating,
        'bestRating' => 5,
        'ratingCount' => max(1, $ratingCount),
    ];
}

include __DIR__ . '/partials/header.php';
?>

<section class="card product-detail" style="margin-top: 2rem; padding-top: 1.5rem;">
    <div class="product-cover">
        <?php 

        $imageUrl = normalizeImageUrl($product['image_url'] ?? null);
        ?>
        <img src="<?php echo sanitize($imageUrl); ?>" 
             alt="<?php echo sanitize($product['name']); ?>"
             loading="lazy" decoding="async"
             onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/img/icon.png'; this.style.opacity='0.3';">
    </div>
    <div class="product-info">
        <span class="muted"><?php echo sanitize($product['category_name']); ?></span>
        <h1><?php echo sanitize($product['name']); ?></h1>
        <?php if ($productAverageRating !== null): ?>
        <p class="product-rating" style="margin: 0.25rem 0; font-size: 1.1rem;" aria-label="Ortalama puan: <?php echo $productAverageRating; ?>">
            <?php echo renderProductRating((float) $productAverageRating); ?>
        </p>
        <?php endif; ?>
        <?php if (!empty($product['author'])): ?>
            <p style="color: var(--muted); margin: 0.5rem 0; font-style: italic;">Yazar: <strong><?php echo sanitize($product['author']); ?></strong></p>
        <?php endif; ?>
        <p class="price"><?php echo number_format((float) $product['price'], 2); ?> ₺</p>
        <p><?php echo nl2br(sanitize($product['description'])); ?></p>
        <?php if (canUseCart()): ?>
            <form class="add-to-cart-form" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label for="quantity">Adet</label>
                <input id="quantity" name="quantity" type="number" min="1" value="1" required>
                <button class="btn btn-primary" type="submit" name="add_to_cart" value="1">Sepete Ekle</button>
            </form>
        <?php else: ?>
            <div class="alert alert-error">
                Sepete ürün eklemek için <a href="<?php echo BASE_URL; ?>/login.php" style="color: var(--primary); font-weight: 600;">giriş yapın</a>.
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="card" style="margin-top: 3rem; padding-top: 1.5rem;">
    <h2 id="product-comments-title">Yorumlar (<?php echo count($comments); ?>)</h2>
    
    <?php if ($commentSuccess): ?>
        <div class="alert alert-success"><?php echo $commentSuccessMessage !== null && $commentSuccessMessage !== '' ? sanitize($commentSuccessMessage) : 'Yorumunuz gönderildi. Onaylandıktan sonra yayınlanacaktır.'; ?></div>
    <?php endif; ?>
    
    <?php if ($commentError): ?>
        <div class="alert alert-error"><?php echo sanitize($commentError); ?></div>
    <?php endif; ?>

    <form method="get" id="product-comment-filter-form" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;" role="search">
        <input type="hidden" name="slug" value="<?php echo sanitize($slug); ?>">
        <label for="product-comment-sort" style="font-weight: 600;">Sırala:</label>
        <select id="product-comment-sort" name="comment_sort" style="padding: 0.4rem 0.75rem;" aria-label="Yorum sıralama">
            <?php foreach ($commentSortOptions as $val => $label): ?>
            <option value="<?php echo sanitize($val); ?>" <?php echo $commentSort === $val ? 'selected' : ''; ?>><?php echo sanitize($label); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div id="product-comments-list" data-product-slug="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if ($comments): ?>
        <div style="margin-bottom: 2rem;">
            <?php include __DIR__ . '/partials/product_comments_list.php'; ?>
        </div>
        <?php else: ?>
        <p class="muted">Henüz yorum yok.</p>
        <?php endif; ?>
    </div>

    <?php if ($commentsEnabled): ?>
        <?php if ($canComment): ?>
            <?php $displayName = $commentUser ? ($commentUser['full_name'] ?? $commentUser['username'] ?? '') : ''; ?>
            <h3>Yorum Yap</h3>
            <?php if ($displayName !== ''): ?>
            <p style="color: var(--muted); margin-bottom: 1rem;">
                Yorumunuz <strong><?php echo sanitize($displayName); ?></strong> olarak yayınlanacak.
            </p>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_comment" value="1">
                <label style="display: block; margin-bottom: 0.35rem;">Puan (1-5)</label>
                <div class="product-comment-rating-input">
                    <?php for ($i = 1; $i <= 5; $i++): $checked = ((int) ($_POST['comment_rating'] ?? 0)) === $i ? ' checked' : ''; ?>
                    <label class="comment-rating-option">
                        <span class="comment-rating-label"><span class="star star-filled">★</span> <?php echo $i; ?></span>
                        <input type="radio" name="comment_rating" value="<?php echo $i; ?>"<?php echo $checked; ?>>
                    </label>
                    <?php endfor; ?>
                </div>
                <label for="content">Yorumunuz</label>
                <textarea id="content" name="content" rows="4" required placeholder="Yorumunuzu buraya yazın..."><?php echo sanitize((string) ($_POST['content'] ?? '')); ?></textarea>
                <label for="comment_images" style="margin-top: 0.75rem; display: block;">Fotoğraf ekle (en fazla 5, her biri max 5MB)</label>
                <input type="file" id="comment_images" name="comment_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple style="margin-bottom: 1rem;">
                <button class="btn btn-primary" type="submit">Yorum Gönder</button>
            </form>
        <?php elseif (canUseCart()): ?>
            <div class="alert alert-error">
                Bu ürüne yorum yapmak için önce ürünü satın almanız gerekiyor.
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                Yorum yapmak için <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode('product.php?slug=' . $slug); ?>" style="color: var(--primary); font-weight: 600;">giriş yapın</a>.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: var(--muted);">Yeni yorum eklenemiyor; yorumlar şu an kapalı.</p>
    <?php endif; ?>
</section>

<!-- Yorum fotoğrafı tam ekran lightbox (geri dönüş için) -->
<div id="product-comment-lightbox" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.9); align-items: center; justify-content: center; padding: 2rem; box-sizing: border-box;" role="dialog" aria-modal="true" aria-label="Yorum fotoğrafı">
    <button type="button" id="product-comment-lightbox-close" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem 1rem; font-size: 1rem; background: #fff; border: 1px solid #ccc; border-radius: 0.5rem; cursor: pointer; z-index: 1;">Geri</button>
    <img id="product-comment-lightbox-img" src="" alt="Yorum fotoğrafı" style="max-width: 100%; max-height: 100%; object-fit: contain;">
</div>

<script>
(function() {
    var commentSortSelect = document.getElementById('product-comment-sort');
    var commentsListEl = document.getElementById('product-comments-list');
    var commentsTitleEl = document.getElementById('product-comments-title');
    if (commentSortSelect && commentsListEl && commentsTitleEl) {
        commentSortSelect.addEventListener('change', function() {
            var slug = commentsListEl.getAttribute('data-product-slug');
            var sort = commentSortSelect.value;
            if (!slug) return;
            var url = (window.BASE_URL || '') + '/product_comments_ajax.php?slug=' + encodeURIComponent(slug) + '&comment_sort=' + encodeURIComponent(sort);
            commentsListEl.style.opacity = '0.6';
            commentsListEl.style.pointerEvents = 'none';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    commentsListEl.innerHTML = html;
                    var wrap = commentsListEl.querySelector('[data-comment-count]');
                    if (wrap) {
                        var count = wrap.getAttribute('data-comment-count');
                        commentsTitleEl.textContent = 'Yorumlar (' + (count || '0') + ')';
                    }
                    history.replaceState(null, '', (window.BASE_URL || '') + '/product.php?slug=' + encodeURIComponent(slug) + '&comment_sort=' + encodeURIComponent(sort));
                })
                .catch(function() {
                    commentsListEl.style.opacity = '';
                    commentsListEl.style.pointerEvents = '';
                })
                .then(function() {
                    commentsListEl.style.opacity = '';
                    commentsListEl.style.pointerEvents = '';
                });
        });
        var filterForm = document.getElementById('product-comment-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                commentSortSelect.dispatchEvent(new Event('change'));
            });
        }
    }
})();
</script>
<script>
(function() {
    function openLightbox(src) {
        var lb = document.getElementById('product-comment-lightbox');
        var lbImg = document.getElementById('product-comment-lightbox-img');
        if (lb && lbImg && src) {
            lbImg.src = src;
            lb.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    function closeLightbox() {
        var lb = document.getElementById('product-comment-lightbox');
        if (lb) {
            lb.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a.product-comment-image-link');
        if (link) {
            e.preventDefault();
            var src = link.getAttribute('data-fullimg') || link.getAttribute('href') || '';
            if (src) openLightbox(src);
        }
    });
    var lbClose = document.getElementById('product-comment-lightbox-close');
    var lb = document.getElementById('product-comment-lightbox');
    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lb) lb.addEventListener('click', function(e) { if (e.target === lb) closeLightbox(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var lb = document.getElementById('product-comment-lightbox');
            if (lb && lb.style.display === 'flex') closeLightbox();
        }
    });
})();
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

