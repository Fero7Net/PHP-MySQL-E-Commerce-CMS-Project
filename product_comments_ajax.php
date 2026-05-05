<?php

require __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
header('X-Requested-With: XMLHttpRequest');

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$sort = isset($_GET['comment_sort']) ? (string) $_GET['comment_sort'] : 'newest';

$product = $slug ? findProductBySlug($pdo, $slug) : null;
if (!$product) {
    echo '<p class="muted">Ürün bulunamadı.</p>';
    exit;
}

$allowedSort = ['newest', 'oldest', 'rating_asc', 'rating_desc', 'random'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'newest';
}

$comments = getCommentsByProductSorted($pdo, (int) $product['id'], $sort, 50);
$commentRepliesByParent = !empty($comments) ? getCommentRepliesBatch($pdo, array_column($comments, 'id')) : [];

echo '<div data-comment-count="' . (int) count($comments) . '">';
if (!empty($comments)) {
    include __DIR__ . '/partials/product_comments_list.php';
} else {
    echo '<p class="muted">Henüz yorum yok.</p>';
}
echo '</div>';
