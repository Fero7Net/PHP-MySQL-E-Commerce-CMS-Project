<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

$errors = [];

$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$editingCategory = null;

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['id'])) {
    
    if (!adminIsLoggedIn()) {
        setFlash('admin_error', 'Yetkiniz yok.');
        redirect('categories.php');
        exit;
    }
    
    $id = (int) $_GET['id'];
    
    if ($id > 0) {
        try {
            
            $statement = $pdo->prepare('DELETE FROM categories WHERE id = :id');
            $statement->execute(['id' => $id]);
            
            setFlash('admin_success', 'Kategori silindi.');
        } catch (PDOException $e) {
            setFlash('admin_error', 'Silme işlemi sırasında bir hata oluştu.');
        }
    } else {
        setFlash('admin_error', 'Geçersiz kategori ID.');
    }
    
    redirect('categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $errors[] = 'Kategori adı zorunludur.';
        }

        if (!$errors) {
            $pdo->beginTransaction();
            try {
                $nextId = getNextAvailableId($pdo, 'categories');
                $statement = $pdo->prepare('INSERT INTO categories (id, name, slug, description) VALUES (:id, :name, :slug, :description)');
                $statement->execute([
                    'id' => $nextId,
                    'name' => $name,
                    'slug' => slugify($name),
                    'description' => $description,
                ]);
                $pdo->commit();
                try {
                    updateTableAutoIncrement($pdo, 'categories');
                } catch (Exception $e) {
                    
                }
                setFlash('admin_success', 'Kategori eklendi.');
                redirect('categories.php');
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Kategori eklenirken bir hata oluştu.';
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $errors[] = 'Kategori adı zorunludur.';
        }

        if (!$errors) {
            $statement = $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id');
            $statement->execute([
                'name' => $name,
                'slug' => slugify($name),
                'description' => $description,
                'id' => $id,
            ]);
            setFlash('admin_success', 'Kategori güncellendi.');
            redirect('categories.php');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $statement = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        setFlash('admin_success', 'Kategori silindi.');
        redirect('categories.php');
    }
}

if ($editId) {
    $statement = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $statement->execute(['id' => $editId]);
    $editingCategory = $statement->fetch();
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem;">
    <h1>Kategori Yönetimi</h1>
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
        <input type="hidden" name="action" value="<?php echo $editingCategory ? 'update' : 'create'; ?>">
        <?php if ($editingCategory): ?>
            <input type="hidden" name="id" value="<?php echo $editingCategory['id']; ?>">
        <?php endif; ?>
        <label for="name">Kategori Adı</label>
        <input id="name" name="name" value="<?php echo sanitize($editingCategory['name'] ?? ($_POST['name'] ?? '')); ?>" required>

        <label for="description">Açıklama</label>
        <textarea id="description" name="description" rows="4"><?php echo sanitize($editingCategory['description'] ?? ($_POST['description'] ?? '')); ?></textarea>

        <button class="btn btn-primary" type="submit">
            <?php echo $editingCategory ? 'Kategoriyi Güncelle' : 'Kategori Ekle'; ?>
        </button>
        <?php if ($editingCategory): ?>
            <a class="btn" href="categories.php">Formu Temizle</a>
        <?php endif; ?>
    </form>
</section>

<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h2>Mevcut Kategoriler</h2>
    <?php if ($categories): ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Slug</th>
                <th>İşlemler</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo sanitize($category['name']); ?></td>
                    <td><?php echo sanitize($category['slug']); ?></td>
                    <td>
                        <a class="btn" href="categories.php?edit_id=<?php echo $category['id']; ?>">Düzenle</a>
                        <a class="btn" href="categories.php?islem=sil&id=<?php echo $category['id']; ?>" 
                           onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Henüz kategori yok.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

