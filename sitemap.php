<?php

require __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(defined('SITE_URL') ? SITE_URL : ('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL), '/');

$urls = [];
$urls[] = ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'];
$urls[] = ['loc' => $base . '/products.php', 'priority' => '0.9', 'changefreq' => 'daily'];
if (findPageBySlug($pdo, 'hakkimizda') !== null) {
    $urls[] = ['loc' => $base . '/about.php', 'priority' => '0.7', 'changefreq' => 'monthly'];
}
if (findPageBySlug($pdo, 'iletisim') !== null) {
    $urls[] = ['loc' => $base . '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly'];
}

$categories = getCategories($pdo);
foreach ($categories as $cat) {
    $urls[] = [
        'loc' => $base . '/kategori/' . rawurlencode($cat['slug']),
        'priority' => '0.8',
        'changefreq' => 'weekly'
    ];
}

$productSlugs = getAllProductSlugs($pdo);
foreach ($productSlugs as $row) {
    $lastmod = !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : date('Y-m-d');
    $urls[] = [
        'loc' => $base . '/urun/' . rawurlencode($row['slug']),
        'priority' => '0.8',
        'changefreq' => 'weekly',
        'lastmod' => $lastmod
    ];
}

$pages = getPages($pdo);
foreach ($pages as $p) {
    $urls[] = [
        'loc' => $base . '/sayfa/' . rawurlencode($p['slug']),
        'priority' => '0.7',
        'changefreq' => 'monthly'
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>';
    if (!empty($u['lastmod'])) {
        echo '<lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>';
    }
    echo '<changefreq>' . htmlspecialchars($u['changefreq'] ?? 'weekly', ENT_XML1, 'UTF-8') . '</changefreq>';
    echo '<priority>' . htmlspecialchars($u['priority'] ?? '0.5', ENT_XML1, 'UTF-8') . '</priority>';
    echo '</url>' . "\n";
}
echo '</urlset>';
