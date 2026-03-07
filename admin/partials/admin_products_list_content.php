<?php

?>
<?php if (!$editId): ?>
<form method="get" id="admin-products-filter-form" class="admin-filter-form" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label for="admin-sort" style="font-weight: 600;">Sırala:</label>
        <select id="admin-sort" name="sort" style="padding: 0.4rem 0.75rem;">
            <?php foreach ($sortOptions as $val => $label): ?>
                <option value="<?php echo sanitize($val); ?>" <?php echo $sort === $val ? 'selected' : ''; ?>><?php echo sanitize($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label for="admin-search" style="font-weight: 600;">Ara:</label>
        <input type="text" id="admin-search" name="search" value="<?php echo sanitize($search); ?>" placeholder="Ürün adı, açıklama, yazar..." style="padding: 0.4rem 0.75rem; min-width: 200px;">
        <button type="submit" class="btn">Filtrele</button>
    </div>
</form>
<?php endif; ?>

<?php if ($products): ?>
<?php if (!$editId): ?>
<form method="POST" action="" id="bulkDeleteForm">
    <input type="hidden" name="redirect_sort" value="<?php echo sanitize($sort); ?>">
    <input type="hidden" name="redirect_search" value="<?php echo sanitize($search); ?>">
    <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button type="submit" name="islem" value="secilenleri_sil" class="btn btn-delete-user" onclick="return confirm('Seçili ürünleri silmek istediğinize emin misiniz?');">Seçilenleri Sil</button>
        <button type="submit" name="islem" value="tumunu_sil" class="btn btn-delete-user" onclick="return confirm('TÜM ÜRÜNLERİ silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">Tüm Ürünleri Sil</button>
    </div>
<?php endif; ?>
<table class="admin-products-table">
    <thead>
    <tr>
        <?php if (!$editId): ?><th><input type="checkbox" id="selectAll" title="Tümünü Seç/Kaldır"></th><?php endif; ?>
        <th>ID</th>
        <th>Ürün</th>
        <?php if ($hasAuthorColumn): ?><th>Yazar</th><?php endif; ?>
        <th>Kategori</th>
        <th style="white-space: nowrap; min-width: 100px;">Fiyat</th>
        <th>Stok</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $product): ?>
        <tr>
            <?php if (!$editId): ?>
            <td><input type="checkbox" name="silinecek_id[]" value="<?php echo (int) $product['id']; ?>" class="product-checkbox"></td>
            <?php endif; ?>
            <td><?php echo $product['id']; ?></td>
            <td class="product-cell">
                <?php
                $imageUrl = normalizeImageUrl($product['image_url'] ?? null);
                if (!empty($imageUrl)): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/products.php?edit_id=<?php echo $product['id']; ?>" class="product-image-link">
                        <img src="<?php echo sanitize($imageUrl); ?>" alt="<?php echo sanitize($product['name']); ?>" class="product-thumbnail"
                             onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/img/icon.png'; this.style.opacity='0.3';">
                    </a>
                <?php endif; ?>
                <div class="product-name-text"><?php echo sanitize($product['name']); ?></div>
            </td>
            <?php if ($hasAuthorColumn): ?><td><?php echo sanitize($product['author'] ?? '-'); ?></td><?php endif; ?>
            <td><?php echo sanitize($product['category_name'] ?? '-'); ?></td>
            <td style="white-space: nowrap;"><?php echo number_format((float) $product['price'], 2); ?> ₺</td>
            <td><?php echo (int) $product['stock']; ?></td>
            <td>
                <a class="btn" href="<?php echo BASE_URL; ?>/admin/products.php?edit_id=<?php echo $product['id']; ?>">Düzenle</a>
                <a class="btn btn-delete-user" href="<?php echo BASE_URL; ?>/admin/products.php?<?php echo http_build_query(array_merge(['islem' => 'sil', 'id' => $product['id']], array_filter(['sort' => $sort !== 'newest' ? $sort : null, 'search' => $search ?: null, 'page' => $currentPage > 1 ? $currentPage : null]))); ?>" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (!$editId): ?></form><?php endif; ?>

<?php if ($pagination['total_pages'] > 1): ?>
<?php
$baseParams = array_filter(['sort' => $sort, 'search' => $search ?: null], function ($v) { return $v !== null && $v !== ''; });
$totalP = $pagination['total_pages'];
$curr = $pagination['current_page'];
$showPages = 7;
$half = (int) floor($showPages / 2);
$start = max(1, $curr - $half);
$end = min($totalP, $start + $showPages - 1);
if ($end - $start + 1 < $showPages) { $start = max(1, $end - $showPages + 1); }
?>
<div class="admin-pagination" style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.35rem;">
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?php echo BASE_URL; ?>/admin/products.php?<?php echo http_build_query(array_merge($baseParams, ['page' => $i])); ?>"
           class="btn admin-pagination-link"
           style="min-width: 2.25rem; padding: 0.4rem 0.6rem; <?php echo $i === $curr ? 'background: var(--primary); color: white; font-weight: 600;' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    <span style="margin-left: 0.5rem; color: var(--muted); font-size: 0.9rem;">(<?php echo $pagination['total_items']; ?> ürün, sayfa <?php echo $curr; ?>/<?php echo $totalP; ?>)</span>
</div>
<?php endif; ?>
<?php else: ?>
<p>Henüz ürün yok.</p>
<?php endif; ?>
