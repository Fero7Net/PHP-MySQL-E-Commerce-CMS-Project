<?php

declare(strict_types=1);

function getProductSortOptions(): array
{
    return [
        'newest' => 'Yeni Eklenenler',
        'oldest' => 'En Eski',
        'name_asc' => 'Alfabe (A-Z)',
        'name_desc' => 'Alfabe (Z-A)',
        'price_asc' => 'Fiyat (Düşük)',
        'price_desc' => 'Fiyat (Yüksek)',
        'rating_desc' => 'En yüksek puanlı',
        'rating_asc' => 'En düşük puanlı',
        'random' => 'Rastgele',
    ];
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Şifre: min 8 karakter, 1 büyük, 1 küçük, 1 özel karakter */
function validatePasswordStrength(string $password): array
{
    $err = [];
    if (strlen($password) < 8) {
        $err[] = 'Şifre en az 8 karakter olmalıdır.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $err[] = 'Şifre en az 1 büyük harf içermelidir.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $err[] = 'Şifre en az 1 küçük harf içermelidir.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $err[] = 'Şifre en az 1 özel karakter içermelidir.';
    }
    return $err;
}

function renderProductRating(?float $avg, bool $showLabel = true): string
{
    $avg = $avg !== null ? (float) $avg : 0;
    $r = (int) round($avg);
    $ratingLabel = $avg > 0 ? number_format($avg, 1) : '0 (0 değerlendirme)';
    $html = '<span class="product-rating-stars" aria-label="Ortalama puan: ' . $avg . '">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $r ? 'star star-filled' : 'star star-empty';
        $char = $i <= $r ? '★' : '☆';
        $html .= '<span class="' . $cls . '" aria-hidden="true">' . $char . '</span>';
    }
    $html .= '</span>';
    if ($showLabel) {
        $html .= ' <span class="muted" style="margin-left: 0.35rem;">(' . $ratingLabel . ')</span>';
    }
    return $html;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/** Login/redirect için güvenli path - sadece internal path'lere izin */
function is_valid_redirect(string $path): bool
{
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return true;
    }
    if (stripos($path, 'javascript:') === 0 || stripos($path, 'data:') === 0 || stripos($path, 'vbscript:') === 0) {
        return false;
    }
    if (preg_match('#^https?://#i', $path) && strpos($path, $_SERVER['HTTP_HOST'] ?? '') === false) {
        return false;
    }
    return true;
}

function get_safe_redirect(string $input): string
{
    $path = trim($input);
    if (!is_valid_redirect($path)) {
        return 'index.php';
    }
    if ($path === '' || $path === '/') {
        return 'index.php';
    }
    if (preg_match('#^https?://#i', $path)) {
        return 'index.php';
    }
    $base = defined('BASE_URL') ? trim(BASE_URL, '/') : '';
    if ($base !== '' && strpos($path, '/' . $base . '/') === 0) {
        $path = substr($path, strlen($base) + 2);
    } elseif ($base !== '' && strpos($path, '/' . $base) === 0 && strlen($path) > strlen($base) + 1) {
        $path = substr($path, strlen($base) + 1);
    }
    $path = ltrim($path, '/');
    return $path !== '' ? $path : 'index.php';
}

function slugify(string $text): string
{
    
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);

    $text = trim($text, '-');

    $text = strtolower($text);

    return $text ?: uniqid();
}

const ALLOWED_AUTO_INCREMENT_TABLES = ['pages', 'categories', 'comments', 'products', 'orders', 'order_items', 'site_users', 'user_addresses', 'admin_addresses'];

function getNextAvailableId(PDO $pdo, string $table): int
{
    if (!in_array($table, ALLOWED_AUTO_INCREMENT_TABLES, true)) {
        throw new InvalidArgumentException('Tablo izin listesinde yok: ' . $table);
    }
    $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM {$table}");
    return $stmt ? (int) $stmt->fetchColumn() : 1;
}

function updateTableAutoIncrement(PDO $pdo, string $table): void
{
    if (!in_array($table, ALLOWED_AUTO_INCREMENT_TABLES, true)) {
        return;
    }
    $maxStmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM {$table}");
    if ($maxStmt) {
        $nextVal = (int) $maxStmt->fetchColumn();
        $pdo->exec("ALTER TABLE {$table} AUTO_INCREMENT = " . $nextVal);
    }
}

function getPages(PDO $pdo): array
{
    
    $statement = $pdo->query('SELECT id, title, slug FROM pages ORDER BY id DESC');
    return $statement->fetchAll();
}

function getCategories(PDO $pdo): array
{
    
    $statement = $pdo->query('SELECT id, name, slug FROM categories ORDER BY id DESC');
    return $statement->fetchAll();
}

function getLatestProducts(PDO $pdo, int $limit = 6): array
{
    
    $limit = max(1, $limit);

    $statement = $pdo->prepare('SELECT * FROM products ORDER BY id DESC LIMIT :limit');
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();
    
    return $statement->fetchAll();
}

function getRandomProducts(PDO $pdo, int $limit = 4): array
{
    $limit = max(1, $limit);

    $countStmt = $pdo->query('SELECT COUNT(*) FROM products');
    $total = $countStmt ? (int) $countStmt->fetchColumn() : 0;
    if ($total <= $limit) {
        $statement = $pdo->prepare('SELECT * FROM products ORDER BY id ASC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    $offset = mt_rand(0, $total - $limit);
    $statement = $pdo->prepare('SELECT * FROM products ORDER BY id ASC LIMIT :limit OFFSET :offset');
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

function getProductsByIds(PDO $pdo, array $ids): array
{
    if (empty($ids)) {
        return [];
    }
    $ids = array_map('intval', array_unique($ids));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $byId = [];
    foreach ($products as $p) {
        $byId[(int) $p['id']] = $p;
    }
    $result = [];
    foreach ($ids as $id) {
        if (isset($byId[$id])) {
            $result[] = $byId[$id];
        }
    }
    return $result;
}

function getCategoriesByIds(PDO $pdo, array $ids): array
{
    if (empty($ids)) {
        return [];
    }
    $ids = array_map('intval', array_unique($ids));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $categories = $stmt->fetchAll();
    $byId = [];
    foreach ($categories as $c) {
        $byId[(int) $c['id']] = $c;
    }
    $result = [];
    foreach ($ids as $id) {
        if (isset($byId[$id])) {
            $result[] = $byId[$id];
        }
    }
    return $result;
}

function getHomepagePopularProducts(PDO $pdo, array $productIds, string $sort, int $limit = 10, int $offset = 0, string $search = '', ?int $randomSeed = null): array
{
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    $search = trim($search);

    $ratingJoin = " LEFT JOIN (SELECT product_id, AVG(rating) AS ar FROM comments WHERE status='approved' AND rating IS NOT NULL GROUP BY product_id) r ON p.id = r.product_id";
    switch ($sort) {
        case 'price_asc':
            $orderBy = 'p.price ASC';
            $ratingJoin = '';
            break;
        case 'price_desc':
            $orderBy = 'p.price DESC';
            $ratingJoin = '';
            break;
        case 'name_asc':
            $orderBy = 'p.name ASC';
            $ratingJoin = '';
            break;
        case 'name_desc':
            $orderBy = 'p.name DESC';
            $ratingJoin = '';
            break;
        case 'oldest':
            $orderBy = 'p.id ASC';
            $ratingJoin = '';
            break;
        case 'rating_desc':
            $orderBy = '(r.ar IS NULL), r.ar DESC, p.id DESC';
            break;
        case 'rating_asc':
            $orderBy = '(r.ar IS NULL), r.ar ASC, p.id DESC';
            break;
        case 'random':
            $orderBy = $randomSeed !== null ? 'RAND(' . (int) $randomSeed . ')' : 'RAND()';
            $ratingJoin = '';
            break;
        case 'newest':
        default:
            $orderBy = 'p.id DESC';
            $ratingJoin = '';
            break;
    }

    $limit = (int) $limit;
    $offset = (int) $offset;
    $searchCond = $search !== '' ? ' AND (p.name LIKE ? OR p.description LIKE ? OR p.author LIKE ?)' : '';
    $searchVal = $search !== '' ? '%' . $search . '%' : '';

    if (!empty($productIds)) {
        $ids = array_map('intval', array_unique($productIds));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $countSql = "SELECT COUNT(*) FROM products WHERE id IN ($placeholders)" . ($search !== '' ? ' AND (name LIKE ? OR description LIKE ? OR author LIKE ?)' : '');
        $countStmt = $pdo->prepare($countSql);
        $countParams = $ids;
        if ($search !== '') {
            $countParams = array_merge($countParams, [$searchVal, $searchVal, $searchVal]);
        }
        $countStmt->execute($countParams);
        $total = (int) $countStmt->fetchColumn();
        $fromPart = "products p" . $ratingJoin;
        $wherePart = "p.id IN ($placeholders)" . $searchCond;
        $sql = "SELECT p.* FROM $fromPart WHERE $wherePart ORDER BY $orderBy LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmtParams = $ids;
        if ($search !== '') {
            $stmtParams = array_merge($stmtParams, [$searchVal, $searchVal, $searchVal]);
        }
        $stmt->execute($stmtParams);
    } else {
        $whereSql = $search !== '' ? ' WHERE (p.name LIKE ? OR p.description LIKE ? OR p.author LIKE ?)' : '';
        $countSql = 'SELECT COUNT(*) FROM products p' . $whereSql;
        $countStmt = $pdo->prepare($countSql);
        $countParams = $search !== '' ? [$searchVal, $searchVal, $searchVal] : [];
        $countStmt->execute($countParams);
        $total = (int) $countStmt->fetchColumn();
        $sql = 'SELECT p.* FROM products p' . $ratingJoin . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        $stmt = $pdo->prepare($sql);
        $stmtParams = $search !== '' ? [$searchVal, $searchVal, $searchVal] : [];
        $stmt->execute($stmtParams);
    }

    $products = $stmt->fetchAll();
    return ['products' => $products, 'total' => $total];
}

function getProductsByCategory(PDO $pdo, int $categoryId, string $sort = 'random'): array
{
    $result = getProductsByCategoryFiltered($pdo, $categoryId, $sort, 1000, 0, '', null);
    return $result['products'];
}

function getProductsByCategoryFiltered(PDO $pdo, int $categoryId, string $sort, int $limit = 12, int $offset = 0, string $search = '', ?int $randomSeed = null): array
{
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    $search = trim($search);

    $ratingJoin = " LEFT JOIN (SELECT product_id, AVG(rating) AS ar FROM comments WHERE status='approved' AND rating IS NOT NULL GROUP BY product_id) r ON p.id = r.product_id";
    switch ($sort) {
        case 'oldest':
            $orderBy = 'p.created_at ASC, p.id ASC';
            $ratingJoin = '';
            break;
        case 'name_asc':
            $orderBy = 'p.name ASC';
            $ratingJoin = '';
            break;
        case 'name_desc':
            $orderBy = 'p.name DESC';
            $ratingJoin = '';
            break;
        case 'price_asc':
            $orderBy = 'p.price ASC';
            $ratingJoin = '';
            break;
        case 'price_desc':
            $orderBy = 'p.price DESC';
            $ratingJoin = '';
            break;
        case 'rating_desc':
            $orderBy = '(r.ar IS NULL), r.ar DESC, p.id DESC';
            break;
        case 'rating_asc':
            $orderBy = '(r.ar IS NULL), r.ar ASC, p.id DESC';
            break;
        case 'random':
            $orderBy = $randomSeed !== null ? 'RAND(' . (int) $randomSeed . ')' : 'RAND()';
            $ratingJoin = '';
            break;
        case 'newest':
        default:
            $orderBy = 'p.created_at DESC, p.id DESC';
            $ratingJoin = '';
            break;
    }

    $searchCond = $search !== '' ? ' AND (p.name LIKE :search1 OR p.description LIKE :search2 OR p.author LIKE :author)' : '';
    $params = ['categoryId' => $categoryId];
    if ($search !== '') {
        $params['search1'] = $params['search2'] = $params['author'] = '%' . $search . '%';
    }

    $countSql = 'SELECT COUNT(*) FROM products WHERE category_id = :categoryId' . ($search !== '' ? ' AND (name LIKE :search1 OR description LIKE :search2 OR author LIKE :author)' : '');
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql = 'SELECT p.* FROM products p' . $ratingJoin . ' WHERE p.category_id = :categoryId' . $searchCond . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();

    return ['products' => $products, 'total' => $total];
}

function findPageBySlug(PDO $pdo, string $slug): ?array
{
    $statement = $pdo->prepare('SELECT * FROM pages WHERE slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $result = $statement->fetch();
    return $result ?: null;
}

function findCategoryBySlug(PDO $pdo, string $slug): ?array
{
    $statement = $pdo->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $result = $statement->fetch();
    return $result ?: null;
}

function findProductBySlug(PDO $pdo, string $slug): ?array
{
    $statement = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $result = $statement->fetch();
    return $result ?: null;
}

function getAllProductSlugs(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT slug, created_at FROM products ORDER BY id ASC');
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function userIsLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function canUseCart(): bool
{
    return userIsLoggedIn() || adminIsLoggedIn();
}

function getCheckoutUser(PDO $pdo): ?array
{
    if (userIsLoggedIn()) {
        $sessionUser = getUser();
        if (!$sessionUser) return null;
        $stmt = $pdo->prepare('SELECT * FROM site_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $sessionUser['id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    if (adminIsLoggedIn()) {
        $admin = $_SESSION['admin'] ?? null;
        if (!$admin) return null;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $admin['id']]);
        $adminRow = $stmt->fetch();
        if (!$adminRow) return null;
        return [
            'id' => $adminRow['id'],
            'username' => $adminRow['username'],
            'email' => $adminRow['email'] ?? '',
            'full_name' => $adminRow['username'],
            'phone' => $adminRow['phone'] ?? '',
        ];
    }
    return null;
}

function getUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireUserLogin(): void
{
    if (!userIsLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

function adminIsLoggedIn(): bool
{
    return isset($_SESSION['admin']);
}

function getAdminRole(): ?string
{
    return $_SESSION['admin']['role'] ?? null;
}

function isAdmin(): bool
{
    return getAdminRole() === 'admin';
}

function requireAdminLogin(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdminRole(): void
{
    requireAdminLogin();
    if (!isAdmin()) {
        setFlash('admin_error', 'Bu işlem için yönetici yetkisi gereklidir.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}

function addToCart(int $productId, int $quantity = 1): void
{
    if ($quantity < 1) {
        return;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function updateCartQuantity(int $productId, int $quantity): void
{
    if (!isset($_SESSION['cart'][$productId])) {
        return;
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }

    $_SESSION['cart'][$productId] = $quantity;
}

function clearCart(): void
{
    unset($_SESSION['cart']);
}

function getCartItems(PDO $pdo): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $ids = array_unique($ids);

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id IN ($placeholders)");
    $checkStmt->execute($ids);
    $validIds = array_map('intval', $checkStmt->fetchAll(PDO::FETCH_COLUMN));

    foreach ($ids as $id) {
        if (!in_array($id, $validIds, true)) {
            unset($_SESSION['cart'][$id]);
        }
    }

    if (empty($validIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($validIds), '?'));
    $statement = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $statement->execute($validIds);
    $products = $statement->fetchAll();

    return array_map(
        static function (array $product) use ($cart): array {
            $productId = (int) $product['id'];
            $quantity = $cart[$productId] ?? 0;
            $product['quantity'] = $quantity;
            $product['line_total'] = $quantity * (float) $product['price'];
            return $product;
        },
        $products,
    );
}

function getCartCount(PDO $pdo): int
{
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return 0;
    }

    $ids = array_map('intval', array_keys($cart));
    $ids = array_unique($ids);

    if (empty($ids)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id IN ($placeholders)");
    $checkStmt->execute($ids);
    $validIds = array_flip(array_map('intval', $checkStmt->fetchAll(PDO::FETCH_COLUMN)));

    $totalCount = 0;
    foreach ($ids as $id) {
        if (isset($validIds[$id])) {
            $totalCount += (int) ($cart[$id] ?? 0);
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }

    return $totalCount;
}

function calculateCartTotal(array $cartItems): float
{
    return array_reduce(
        $cartItems,
        static fn (float $carry, array $item): float => $carry + (float) $item['line_total'],
        0.0,
    );
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function validate_csrf(): bool
{
    $token = $_POST['_csrf_token'] ?? '';
    return $token !== '' && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

function getFlash(string $type): ?string
{
    if (!isset($_SESSION['flash'][$type])) {
        return null;
    }

    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $message;
}

function normalizeImageUrl(?string $imageUrl, bool $forSlider = false): string
{
    if (empty($imageUrl)) {
        return BASE_URL . '/img/icon.png';
    }
    if (strpos($imageUrl, 'http://') === 0 || strpos($imageUrl, 'https://') === 0) {
        return $imageUrl;
    }
    if (strpos($imageUrl, BASE_URL) === 0) {
        return $imageUrl;
    }
    if ($forSlider) {
        return BASE_URL . '/' . ltrim($imageUrl, '/');
    }
    if (strpos($imageUrl, '/') === false && strpos($imageUrl, '\\') === false) {
        $imageUrl = BASE_URL . '/products_img/' . $imageUrl;
    } elseif (strpos($imageUrl, '/') === 0) {
        $imageUrl = BASE_URL . $imageUrl;
    } elseif (strpos($imageUrl, 'products_img/') !== false || strpos($imageUrl, '/products_img/') !== false) {
        if (strpos($imageUrl, BASE_URL) === false) {
            $imageUrl = (strpos($imageUrl, '/') === 0) ? BASE_URL . $imageUrl : BASE_URL . '/' . $imageUrl;
        }
    }
    return $imageUrl;
}

function normalizeSliderImageUrl(?string $imageUrl): string
{
    return normalizeImageUrl($imageUrl, true);
}

function getSliderSlides(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM slider_slides ORDER BY sort_order ASC, id ASC');
    return $stmt ? $stmt->fetchAll() : [];
}

function addSliderSlide(PDO $pdo, string $imageUrl, ?string $altText, int $sortOrder): bool
{
    $stmt = $pdo->prepare('INSERT INTO slider_slides (sort_order, image_url, alt_text) VALUES (:sort_order, :image_url, :alt_text)');
    return $stmt->execute([
        'sort_order' => $sortOrder,
        'image_url' => $imageUrl,
        'alt_text' => $altText,
    ]);
}

function updateSliderSlide(PDO $pdo, int $id, string $newImageUrl, ?string $altText): bool
{
    $stmt = $pdo->prepare('UPDATE slider_slides SET image_url = :image_url, alt_text = :alt_text WHERE id = :id');
    return $stmt->execute([
        'image_url' => $newImageUrl,
        'alt_text' => $altText,
        'id' => $id,
    ]);
}

function deleteSliderSlide(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM slider_slides WHERE id = :id');
    return $stmt->execute(['id' => $id]);
}

function reorderSliderSlides(PDO $pdo): void
{
    $slides = getSliderSlides($pdo);
    $stmt = $pdo->prepare('UPDATE slider_slides SET sort_order = :sort_order WHERE id = :id');
    foreach ($slides as $i => $slide) {
        $stmt->execute(['sort_order' => $i + 1, 'id' => (int) $slide['id']]);
    }
}

function deleteSliderImageFile(?string $imageUrl, string $projectRoot): bool
{
    if (empty($imageUrl)) {
        return false;
    }
    $pathPart = parse_url($imageUrl, PHP_URL_PATH);
    $path = ($pathPart !== false && $pathPart !== null && $pathPart !== '') ? $pathPart : $imageUrl;
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path, '/\\'));
    $fileName = basename($path);
    if ($fileName === '' || $fileName === '.' || $fileName === '..') {
        return false;
    }
    $projectRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $projectRoot), DIRECTORY_SEPARATOR);

    $deleted = false;
    if (strpos($imageUrl, 'uploads/slider') !== false || strpos($path, 'uploads' . DIRECTORY_SEPARATOR . 'slider') !== false) {
        $dir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'slider';
        if (is_dir($dir)) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($filePath) && is_file($filePath)) {
                $realPath = realpath($filePath);
                $allowedDir = realpath($dir);
                if ($realPath !== false && $allowedDir !== false && strpos($realPath, $allowedDir) === 0) {
                    $deleted = @unlink($filePath);
                }
            }
        }
    }
    if (!$deleted && (strpos($imageUrl, '/img/') !== false || strpos($imageUrl, 'img/') === 0 || strpos($path, 'img') === 0)) {
        $dir = $projectRoot . DIRECTORY_SEPARATOR . 'img';
        if (is_dir($dir)) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($filePath) && is_file($filePath)) {
                $realPath = realpath($filePath);
                $allowedDir = realpath($dir);
                if ($realPath !== false && $allowedDir !== false && strpos($realPath, $allowedDir) === 0) {
                    $deleted = @unlink($filePath);
                }
            }
        }
    }
    return $deleted;
}

function deleteImageFile(?string $imageUrl, string $productsImgDir): bool
{
    if (empty($imageUrl)) {
        return false;
    }

    $productsImgDir = rtrim($productsImgDir, '/\\') . DIRECTORY_SEPARATOR;

    $fileName = basename($imageUrl);

    if (empty($fileName)) {
        return false;
    }
    
    $deleted = false;

    if (strpos($imageUrl, '/products_img/') !== false || strpos($imageUrl, 'products_img/') !== false || strpos($imageUrl, 'products_img') !== false) {
        
        $filePath = $productsImgDir . $fileName;

        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

        if (file_exists($filePath) && is_file($filePath)) {
            if (@unlink($filePath)) {
                $deleted = true;
            }
        }

        $resizedPath = $productsImgDir . 'resized_' . $fileName;
        $resizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $resizedPath);
        if (file_exists($resizedPath) && is_file($resizedPath)) {
            @unlink($resizedPath);
        }
    }
    
    return $deleted;
}

function commitTempProductImage(string $imageUrl, string $productsImgDir): string
{
    if (strpos($imageUrl, 'products_img/temp/') === false && strpos($imageUrl, 'products_img\\temp\\') === false) {
        return $imageUrl;
    }
    $fileName = basename($imageUrl);
    if ($fileName === '' || $fileName === '.' || $fileName === '..') {
        return $imageUrl;
    }
    $dir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $productsImgDir), DIRECTORY_SEPARATOR);
    $tempPath = $dir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileName;
    $finalPath = $dir . DIRECTORY_SEPARATOR . $fileName;
    if (!file_exists($tempPath) || !is_file($tempPath)) {
        return $imageUrl;
    }
    if (@rename($tempPath, $finalPath)) {
        return (defined('BASE_URL') ? BASE_URL : '') . '/products_img/' . $fileName;
    }
    return $imageUrl;
}

function getSetting(PDO $pdo, string $key, string $default = ''): string
{
    $statement = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $statement->execute(['key' => $key]);
    $result = $statement->fetch();
    return $result ? $result['setting_value'] : $default;
}

function setSetting(PDO $pdo, string $key, string $value): void
{
    $statement = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2');
    $statement->execute(['key' => $key, 'value' => $value, 'value2' => $value]);
}

function resizeImage(string $sourcePath, string $destinationPath, int $maxWidth = 800, int $maxHeight = 600, int $quality = 82): bool
{
    
    if (!function_exists('imagecreatefromjpeg') || 
        !function_exists('imagecreatetruecolor') || 
        !function_exists('imagecopyresampled')) {
        return false; 
    }

    if (!file_exists($sourcePath)) {
        return false;
    }

    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }

    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];

    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $newWidth = (int) ($sourceWidth * $ratio);
    $newHeight = (int) ($sourceHeight * $ratio);

    $sourceImage = null;
    if ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $sourceImage = imagecreatefromjpeg($sourcePath);
    } elseif ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
        $sourceImage = imagecreatefrompng($sourcePath);
    } elseif ($mimeType === 'image/gif' && function_exists('imagecreatefromgif')) {
        $sourceImage = imagecreatefromgif($sourcePath);
    } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $sourceImage = imagecreatefromwebp($sourcePath);
    }

    if (!$sourceImage) {
        return false;
    }

    $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
    if (!$destinationImage) {
        imagedestroy($sourceImage);
        return false;
    }

    if ($mimeType === 'image/png') {
        imagealphablending($destinationImage, false);
        imagesavealpha($destinationImage, true);
    }

    imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

    $result = false;
    if ($mimeType === 'image/jpeg' && function_exists('imagejpeg')) {
        $result = imagejpeg($destinationImage, $destinationPath, $quality);
    } elseif ($mimeType === 'image/png' && function_exists('imagepng')) {
        $result = imagepng($destinationImage, $destinationPath, 6);
    } elseif ($mimeType === 'image/gif' && function_exists('imagegif')) {
        $result = imagegif($destinationImage, $destinationPath);
    } elseif (($mimeType === 'image/webp' || $mimeType === 'image/jpeg') && function_exists('imagejpeg')) {
        $result = imagejpeg($destinationImage, $destinationPath, $quality);
    }

    imagedestroy($sourceImage);
    imagedestroy($destinationImage);

    return $result !== false;
}

function processCommentImages(array $files, string $projectRoot): array
{
    $uploadDir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $projectRoot), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; 
    $maxFiles = 5;
    $maxWidth = 600;
    $maxHeight = 600;
    $result = [];

    if (!isset($files['tmp_name']) || !is_array($files['tmp_name'])) {
        $tmpName = $files['tmp_name'] ?? null;
        if ($tmpName && is_uploaded_file($tmpName)) {
            $files = ['name' => [$files['name']], 'type' => [$files['type']], 'tmp_name' => [$tmpName], 'error' => [$files['error']], 'size' => [$files['size']]];
        } else {
            return [];
        }
    }

    $count = 0;
    foreach ($files['tmp_name'] as $i => $tmpName) {
        if ($count >= $maxFiles) {
            break;
        }
        if (empty($tmpName) || !is_uploaded_file($tmpName)) {
            continue;
        }
        if (isset($files['error'][$i]) && $files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $size = (int) ($files['size'][$i] ?? 0);
        if ($size > $maxSize) {
            continue;
        }
        $mime = $files['type'][$i] ?? '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_file($finfo, $tmpName);
                if ($detected) {
                    $mime = $detected;
                }
                finfo_close($finfo);
            }
        }
        if (!in_array($mime, $allowedTypes, true)) {
            continue;
        }

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $ext = 'jpg';
        if (strpos($mime, 'png') !== false) {
            $ext = 'png';
        } elseif (strpos($mime, 'gif') !== false) {
            $ext = 'gif';
        }
        $safeName = 'c_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $destPath = $uploadDir . $safeName;
        $saved = false;
        if (function_exists('resizeImage') && resizeImage($tmpName, $destPath, $maxWidth, $maxHeight)) {
            $saved = true;
        } else {
            if (@move_uploaded_file($tmpName, $destPath)) {
                $saved = true;
            }
        }
        if ($saved) {
            $result[] = 'uploads/comments/' . $safeName;
            $count++;
        }
    }

    return $result;
}

function commentImageUrl(string $storedPath): string
{
    $path = trim(str_replace(['\\'], '/', $storedPath), '/');
    if ($path === '') {
        return defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    }
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    return $base . '/' . $path;
}

function deleteCommentImageFiles(array $comments, string $baseDir): void
{
    $baseDir = rtrim(str_replace(['\\'], '/', $baseDir), '/');
    foreach ($comments as $row) {
        if (empty($row['images'])) {
            continue;
        }
        $decoded = json_decode($row['images'], true);
        if (!is_array($decoded)) {
            continue;
        }
        foreach ($decoded as $storedPath) {
            $path = trim(str_replace(['\\'], '/', (string) $storedPath), '/');
            if ($path === '' || strpos($path, '..') !== false) {
                continue;
            }
            $fullPath = $baseDir . '/' . $path;
            if (is_file($fullPath) && @unlink($fullPath)) {
            }
        }
    }
}

function getCommentsByProduct(PDO $pdo, int $productId, int $limit = 50): array
{
    try {
        $statement = $pdo->prepare('SELECT * FROM comments WHERE product_id = :productId AND (parent_id IS NULL OR parent_id = 0) AND status = "approved" ORDER BY id DESC LIMIT :limit');
        $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    } catch (PDOException $e) {
        $statement = $pdo->prepare('SELECT * FROM comments WHERE product_id = :productId AND status = "approved" ORDER BY id DESC LIMIT :limit');
        $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}

function getCommentReplies(PDO $pdo, int $parentCommentId): array
{
    try {
        $statement = $pdo->prepare('SELECT * FROM comments WHERE parent_id = :parentId AND status = "approved" ORDER BY id ASC');
        $statement->execute(['parentId' => $parentCommentId]);
        return $statement->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/** @return array<int, array> parent_id => replies */
function getCommentRepliesBatch(PDO $pdo, array $parentIds): array
{
    if (empty($parentIds)) {
        return [];
    }
    $parentIds = array_map('intval', array_unique($parentIds));
    $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
    try {
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE parent_id IN ($placeholders) AND status = 'approved' ORDER BY parent_id ASC, id ASC");
        $stmt->execute($parentIds);
        $rows = $stmt->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $pid = (int) $row['parent_id'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [];
            }
            $grouped[$pid][] = $row;
        }
        return $grouped;
    } catch (PDOException $e) {
        return [];
    }
}

function ensureCommentsTableColumns(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comments'");
    $columns = $stmt ? array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME') : [];
    if (!in_array('images', $columns, true)) {
        $pdo->exec("ALTER TABLE comments ADD COLUMN images TEXT NULL COMMENT 'JSON array of image URLs' AFTER content");
    }
    if (!in_array('parent_id', $columns, true)) {
        $pdo->exec('ALTER TABLE comments ADD COLUMN parent_id INT UNSIGNED NULL DEFAULT NULL AFTER product_id');
        try {
            $pdo->exec('ALTER TABLE comments ADD INDEX idx_parent_id (parent_id)');
        } catch (PDOException $e) {
        }
    }
    if (!in_array('rating', $columns, true)) {
        $pdo->exec('ALTER TABLE comments ADD COLUMN rating TINYINT UNSIGNED NULL COMMENT \'1-5 puan\' AFTER images');
    }
}

function getProductAverageRating(PDO $pdo, int $productId): ?float
{
    try {
        $stmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating FROM comments WHERE product_id = :productId AND status = \'approved\' AND rating IS NOT NULL');
        $stmt->execute(['productId' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['avg_rating'] !== null) {
            return round((float) $row['avg_rating'], 1);
        }
    } catch (PDOException $e) {
    }
    return null;
}

function getProductAverageRatingsBatch(PDO $pdo, array $productIds): array
{
    $productIds = array_values(array_unique(array_map('intval', $productIds)));
    if (empty($productIds)) {
        return [];
    }
    try {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("SELECT product_id, AVG(rating) AS avg_rating FROM comments WHERE product_id IN ($placeholders) AND status = 'approved' AND rating IS NOT NULL GROUP BY product_id");
        $stmt->execute($productIds);
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(int) $row['product_id']] = round((float) $row['avg_rating'], 1);
        }
        return $out;
    } catch (PDOException $e) {
        return [];
    }
}

function getCommentsByProductSorted(PDO $pdo, int $productId, string $sort = 'newest', int $limit = 50): array
{
    $allowedSort = ['newest', 'oldest', 'rating_asc', 'rating_desc', 'random'];
    if (!in_array($sort, $allowedSort, true)) {
        $sort = 'newest';
    }
    $orderBy = 'created_at DESC, id DESC';
    switch ($sort) {
        case 'oldest':
            $orderBy = 'created_at ASC, id ASC';
            break;
        case 'rating_asc':
            $orderBy = 'rating IS NULL, rating ASC, created_at DESC, id DESC';
            break;
        case 'rating_desc':
            $orderBy = 'rating IS NULL, rating DESC, created_at DESC, id DESC';
            break;
        case 'random':
            $orderBy = 'RAND()';
            break;
        default:
            $orderBy = 'created_at DESC, id DESC';
            break;
    }
    try {
        $sql = 'SELECT * FROM comments WHERE product_id = :productId AND (parent_id IS NULL OR parent_id = 0) AND status = \'approved\' ORDER BY ' . $orderBy . ' LIMIT :limit';
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    } catch (PDOException $e) {
        $fallbackOrder = ($sort === 'oldest') ? 'created_at ASC, id ASC' : 'created_at DESC, id DESC';
        try {
            $statement = $pdo->prepare('SELECT * FROM comments WHERE product_id = :productId AND (parent_id IS NULL OR parent_id = 0) AND status = \'approved\' ORDER BY ' . $fallbackOrder . ' LIMIT :limit');
            $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll();
        } catch (PDOException $e2) {
            $statement = $pdo->prepare('SELECT * FROM comments WHERE product_id = :productId AND status = \'approved\' ORDER BY ' . $fallbackOrder . ' LIMIT :limit');
            $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll();
        }
    }
}

function hasUserPurchasedProduct(PDO $pdo, string $email, int $productId): bool
{
    if ($email === '') {
        return false;
    }
    $stmt = $pdo->prepare(
        'SELECT 1 FROM orders o JOIN order_items oi ON o.id = oi.order_id ' .
        'WHERE o.customer_email = :email AND oi.product_id = :productId AND o.status = :completed LIMIT 1'
    );
    $stmt->execute(['email' => $email, 'productId' => $productId, 'completed' => 'Tamamlandı']);
    return (bool) $stmt->fetch();
}

function addComment(PDO $pdo, int $productId, string $authorName, string $authorEmail, string $content, array $imageUrls = [], ?int $parentId = null, ?int $rating = null): bool
{
    $commentsEnabled = getSetting($pdo, 'comments_enabled', '1') === '1';
    if (!$commentsEnabled) {
        return false;
    }

    $status = 'pending';
    $nextId = getNextAvailableId($pdo, 'comments');
    $imagesJson = !empty($imageUrls) ? json_encode(array_values($imageUrls)) : null;
    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        $rating = null;
    }

    $ok = false;
    $isColumnError = static function (PDOException $e) {
        $code = (int) $e->getCode();
        $msg = $e->getMessage();
        return $code === 42 || strpos($msg, 'Unknown column') !== false || strpos($msg, '42S22') !== false;
    };

    try {
        $statement = $pdo->prepare('INSERT INTO comments (id, product_id, parent_id, author_name, author_email, content, images, rating, status) VALUES (:id, :productId, :parentId, :authorName, :authorEmail, :content, :images, :rating, :status)');
        $ok = $statement->execute([
            'id' => $nextId,
            'productId' => $productId,
            'parentId' => $parentId,
            'authorName' => $authorName,
            'authorEmail' => $authorEmail,
            'content' => $content,
            'images' => $imagesJson,
            'rating' => $rating,
            'status' => $status,
        ]);
    } catch (PDOException $e) {
        if ($isColumnError($e)) {
            ensureCommentsTableColumns($pdo);
            try {
                $statement = $pdo->prepare('INSERT INTO comments (id, product_id, parent_id, author_name, author_email, content, images, rating, status) VALUES (:id, :productId, :parentId, :authorName, :authorEmail, :content, :images, :rating, :status)');
                $ok = $statement->execute([
                    'id' => $nextId,
                    'productId' => $productId,
                    'parentId' => $parentId,
                    'authorName' => $authorName,
                    'authorEmail' => $authorEmail,
                    'content' => $content,
                    'images' => $imagesJson,
                    'rating' => $rating,
                    'status' => $status,
                ]);
            } catch (PDOException $e2) {
                if ($isColumnError($e2)) {
                    try {
                        $statement = $pdo->prepare('INSERT INTO comments (id, product_id, author_name, author_email, content, status) VALUES (:id, :productId, :authorName, :authorEmail, :content, :status)');
                        $ok = $statement->execute([
                            'id' => $nextId,
                            'productId' => $productId,
                            'authorName' => $authorName,
                            'authorEmail' => $authorEmail,
                            'content' => $content,
                            'status' => $status,
                        ]);
                    } catch (PDOException $e3) {
                        throw new RuntimeException('comments tablosu güncellenemedi. migrations/migrate_comments_images.sql ve migrate_comments_parent_id.sql çalıştırın.', 0, $e3);
                    }
                } else {
                    throw $e2;
                }
            }
        } else {
            throw $e;
        }
    }
    if (!isset($ok)) {
        $ok = false;
    }
    if ($ok) {
        try {
            updateTableAutoIncrement($pdo, 'comments');
        } catch (Exception $e) {
            
        }
    }
    return $ok;
}

function hasEmailColumn(PDO $pdo, string $table = 'users'): bool
{
    try {
        
        $statement = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = :table 
            AND COLUMN_NAME = 'email'
        ");
        $statement->execute(['table' => $table]);
        $count = (int) $statement->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        
        $allowedTables = ['users', 'site_users'];
        if (!in_array($table, $allowedTables, true)) {
            return false;
        }
        try {
            
            $pdo->query("SELECT email FROM `{$table}` LIMIT 1");
            return true;
        } catch (PDOException $e2) {
            return false;
        }
    }
}

function logVisit(PDO $pdo, string $pageUrl = ''): void
{
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ipAddress === '') {
            return;
        }
        $pageUrl = mb_strlen($pageUrl) > 500 ? mb_substr($pageUrl, 0, 500) : $pageUrl;
        $referrer = isset($_SERVER['HTTP_REFERER']) ? mb_substr($_SERVER['HTTP_REFERER'], 0, 500) : null;
        $statement = $pdo->prepare('INSERT INTO site_visits (ip_address, user_agent, page_url, referrer, visit_date, visit_time) VALUES (:ip, NULL, :page_url, :referrer, CURDATE(), CURTIME())');
        $statement->execute(['ip' => $ipAddress, 'page_url' => $pageUrl ?: null, 'referrer' => $referrer]);
    } catch (PDOException $e) {
    }
}

function getPaginationData(int $currentPage, int $totalItems, int $itemsPerPage = 10): array
{
    $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

