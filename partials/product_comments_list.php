<?php
if (!isset($comments) || !isset($pdo)) {
    return;
}
foreach ($comments as $comment):
    $commentImages = [];
    if (!empty($comment['images'])) {
        $decoded = json_decode($comment['images'], true);
        $commentImages = is_array($decoded) ? $decoded : [];
    }
    $commentRating = isset($comment['rating']) && $comment['rating'] !== null && $comment['rating'] !== '' ? (int) $comment['rating'] : null;
?>
<div class="product-comment-item" style="border-bottom: 1px solid #eee; padding: 1rem 0;">
    <?php if (!empty($commentImages)): ?>
    <div class="comment-images" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
        <?php foreach ($commentImages as $imgPath): ?>
        <?php $imgUrl = commentImageUrl((string) $imgPath); $imgUrlSafe = htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>
        <a href="<?php echo $imgUrlSafe; ?>" class="product-comment-image-link" data-fullimg="<?php echo $imgUrlSafe; ?>" style="display: block; cursor: zoom-in;">
            <img src="<?php echo $imgUrlSafe; ?>" alt="Yorum fotoğrafı" loading="lazy" decoding="async" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--border, #eee);" onerror="this.style.display='none';">
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <strong><?php echo sanitize($comment['author_name']); ?></strong>
        <?php if ($commentRating !== null): ?>
        <span class="product-comment-stars product-rating-stars" aria-label="<?php echo $commentRating; ?> puan"><?php for ($s = 1; $s <= 5; $s++) { $cls = $s <= $commentRating ? 'star star-filled' : 'star star-empty'; echo '<span class="' . $cls . '" aria-hidden="true">' . ($s <= $commentRating ? '★' : '☆') . '</span>'; } ?></span>
        <?php endif; ?>
        <span class="muted" style="margin-left: 0.25rem;"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></span>
    </div>
    <p style="margin-top: 0.5rem;"><?php echo nl2br(sanitize($comment['content'])); ?></p>
    <?php
    $replies = isset($commentRepliesByParent) ? ($commentRepliesByParent[(int) $comment['id']] ?? []) : getCommentReplies($pdo, (int) $comment['id']);
    if (!empty($replies)):
        foreach ($replies as $reply):
            $replyImages = [];
            if (!empty($reply['images'])) {
                $dec = json_decode($reply['images'], true);
                $replyImages = is_array($dec) ? $dec : [];
            }
    ?>
    <div style="margin-left: 1.5rem; margin-top: 0.75rem; padding: 0.75rem; background: var(--light, #f8fafc); border-radius: 0.5rem; border-left: 3px solid var(--primary, #3b82f6);">
        <?php if (!empty($replyImages)): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
            <?php foreach ($replyImages as $rp): $ru = commentImageUrl((string) $rp); $ruSafe = htmlspecialchars($ru, ENT_QUOTES, 'UTF-8'); ?>
            <a href="<?php echo $ruSafe; ?>" class="product-comment-image-link" data-fullimg="<?php echo $ruSafe; ?>" style="display: block; cursor: zoom-in;">
                <img src="<?php echo $ruSafe; ?>" alt="" style="max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 0.25rem;" onerror="this.style.display='none';">
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <strong><?php echo sanitize($reply['author_name']); ?></strong>
        <span class="muted" style="margin-left: 0.5rem; font-size: 0.9rem;"><?php echo date('d.m.Y H:i', strtotime($reply['created_at'])); ?></span>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.95rem;"><?php echo nl2br(sanitize($reply['content'])); ?></p>
    </div>
    <?php endforeach; endif; ?>
    <?php if (canUseCart() && adminIsLoggedIn()): ?>
    <form method="post" enctype="multipart/form-data" style="margin-top: 0.75rem; margin-left: 1rem;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="add_comment" value="1">
        <input type="hidden" name="parent_id" value="<?php echo (int) $comment['id']; ?>">
        <textarea name="content" rows="2" placeholder="Cevabınızı yazın..." required style="width: 100%; max-width: 500px; padding: 0.5rem;"></textarea>
        <button type="submit" class="btn" style="margin-top: 0.25rem;">Yanıtla</button>
    </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>
