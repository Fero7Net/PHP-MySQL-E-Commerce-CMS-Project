<?php

?>
<div style="margin-bottom: 1rem;">
    <a class="btn admin-comments-filter-link <?php echo $status === 'all' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/comments.php?status=all">Tümü</a>
    <a class="btn admin-comments-filter-link <?php echo $status === 'pending' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/comments.php?status=pending">Bekleyenler</a>
    <a class="btn admin-comments-filter-link <?php echo $status === 'approved' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/comments.php?status=approved">Onaylananlar</a>
    <a class="btn admin-comments-filter-link <?php echo $status === 'rejected' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/comments.php?status=rejected">Reddedilenler</a>
</div>

<?php if ($comments): ?>
<form method="post" id="admin-comments-bulk-form">
    <input type="hidden" name="action" value="bulk_delete">
    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
    <p style="margin-bottom: 1rem;">
        <button type="submit" name="submit_bulk" value="1" class="btn btn-danger" id="admin-comments-bulk-btn" disabled onclick="return confirm('Seçili yorumları silmek istediğinize emin misiniz?');">Seçilenleri sil</button>
        <button type="button" class="btn btn-danger" id="admin-comments-delete-all-btn" style="margin-left: 0.5rem;">Tümünü sil</button>
    </p>
<table>
    <thead>
    <tr>
        <th style="width: 2.5rem;">
            <input type="checkbox" id="admin-comments-select-all" title="Tümünü seç" aria-label="Tümünü seç">
        </th>
        <th>ID</th>
        <th>Ürün</th>
        <th>Yazar</th>
        <th>E-posta</th>
        <th>İçerik</th>
        <th>Fotoğraflar</th>
        <th>Durum</th>
        <th>Tarih</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($comments as $comment): ?>
        <tr>
            <td>
                <input type="checkbox" name="ids[]" value="<?php echo (int) $comment['id']; ?>" class="admin-comment-cb">
            </td>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo sanitize($comment['product_name'] ?? '-'); ?></td>
            <td><?php echo sanitize($comment['author_name']); ?></td>
            <td><?php echo sanitize($comment['author_email']); ?></td>
            <td><?php echo mb_strimwidth(sanitize($comment['content']), 0, 50, '...'); ?></td>
            <td>
                <?php
                $adminCommentImages = [];
                if (!empty($comment['images'])) {
                    $decoded = json_decode($comment['images'], true);
                    $adminCommentImages = is_array($decoded) ? $decoded : [];
                }
                if (!empty($adminCommentImages)): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                        <?php foreach ($adminCommentImages as $path): ?>
                            <?php $imgUrl = commentImageUrl((string) $path); $imgUrlSafe = htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>
                            <a href="<?php echo $imgUrlSafe; ?>" class="admin-comment-image-link" data-fullimg="<?php echo $imgUrlSafe; ?>" style="display: block; cursor: zoom-in;">
                                <img src="<?php echo $imgUrlSafe; ?>" alt="Yorum fotoğrafı" style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 0.25rem; border: 1px solid var(--border, #e5e7eb);" onerror="this.style.display='none';">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php
                $statusLabels = ['pending' => 'Bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi'];
                echo $statusLabels[$comment['status']] ?? $comment['status'];
                ?>
            </td>
            <td><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></td>
            <td>
                <?php if ($comment['status'] === 'pending'): ?>
                    <button type="button" class="btn admin-comment-action-btn" data-action="approve" data-id="<?php echo (int) $comment['id']; ?>">Onayla</button>
                    <button type="button" class="btn admin-comment-action-btn" data-action="reject" data-id="<?php echo (int) $comment['id']; ?>">Reddet</button>
                <?php endif; ?>
                <button type="button" class="btn admin-comment-action-btn" data-action="delete" data-id="<?php echo (int) $comment['id']; ?>">Sil</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</form>
<form method="post" id="admin-comments-delete-all-form" style="display: none;">
    <input type="hidden" name="action" value="delete_all">
</form>
<?php else: ?>
    <p>Henüz yorum yok.</p>
<?php endif; ?>
