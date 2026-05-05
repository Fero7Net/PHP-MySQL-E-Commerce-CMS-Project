<?php

require __DIR__ . '/config.php';

if (!adminIsLoggedIn()) {
    setFlash('error', 'Bu sayfaya erişmek için admin girişi yapmanız gerekiyor.');
    redirect(BASE_URL . '/login.php');
}

$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    redirect(BASE_URL . '/login.php');
}

$pageTitle = 'Profilim';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $admin['id']]);
$currentUser = $stmt->fetch();
if (!$currentUser) {
    setFlash('error', 'Admin bilgileri bulunamadı.');
    redirect(BASE_URL . '/index.php');
}

$errors = [];
$success = false;

try {
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'full_name'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(150) NULL AFTER email");
    }
} catch (PDOException $e) {
}
try {
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(50) NULL AFTER email");
    }
} catch (PDOException $e) {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    if (!validate_csrf()) {
        $errors[] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
        try {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('DELETE FROM admin_addresses WHERE admin_id = :admin_id');
                $stmt->execute(['admin_id' => $admin['id']]);
            } catch (PDOException $e) {}
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $admin['id']]);
            $pdo->commit();
            session_destroy();
            setFlash('success', 'Hesabınız başarıyla silindi.');
            redirect(BASE_URL . '/index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Hesap silinirken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account']) && !isset($_POST['address_action'])) {
    if (!validate_csrf()) {
        $errors[] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($username === '') {
            $errors[] = 'Kullanıcı adı zorunludur.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        } else {
            $statement = $pdo->prepare('SELECT id FROM users WHERE (username = :username OR (email IS NOT NULL AND email != "" AND LOWER(TRIM(email)) = LOWER(TRIM(:email)))) AND id != :id LIMIT 1');
            $statement->execute(['username' => $username, 'email' => $email, 'id' => $admin['id']]);
            if ($statement->fetch()) {
                $errors[] = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor.';
            }
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }

        if ($password !== '') {
            $pwdErrors = validatePasswordStrength($password);
            if (!empty($pwdErrors)) {
                $errors = array_merge($errors, $pwdErrors);
            } elseif ($password !== $passwordConfirm) {
                $errors[] = 'Şifreler eşleşmiyor.';
            }
        }

        if (empty($errors)) {
            try {
                $hasFullName = (bool) $pdo->query("SHOW COLUMNS FROM users LIKE 'full_name'")->fetch();
                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    if ($hasFullName) {
                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, full_name = :full_name, phone = :phone, password_hash = :password_hash WHERE id = :id');
                        $statement->execute(['username' => $username, 'email' => $email ?: null, 'full_name' => $fullName ?: null, 'phone' => $phone ?: null, 'password_hash' => $passwordHash, 'id' => $admin['id']]);
                    } else {
                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, phone = :phone, password_hash = :password_hash WHERE id = :id');
                        $statement->execute(['username' => $username, 'email' => $email ?: null, 'phone' => $phone ?: null, 'password_hash' => $passwordHash, 'id' => $admin['id']]);
                    }
                } else {
                    if ($hasFullName) {
                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, full_name = :full_name, phone = :phone WHERE id = :id');
                        $statement->execute(['username' => $username, 'email' => $email ?: null, 'full_name' => $fullName ?: null, 'phone' => $phone ?: null, 'id' => $admin['id']]);
                    } else {
                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, phone = :phone WHERE id = :id');
                        $statement->execute(['username' => $username, 'email' => $email ?: null, 'phone' => $phone ?: null, 'id' => $admin['id']]);
                    }
                }
                $_SESSION['admin']['username'] = $username;
                $success = true;
                setFlash('success', 'Profil bilgileriniz güncellendi.');
                redirect(BASE_URL . '/admin_profile.php');
            } catch (PDOException $e) {
                $errors[] = 'Bir hata oluştu: ' . $e->getMessage();
            }
        }
    }
}

$addressAction = $_GET['address_action'] ?? '';
$addressId = isset($_GET['address_id']) ? (int) $_GET['address_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_action'])) {
    if (!validate_csrf()) {
        setFlash('address_error', 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
        redirect(BASE_URL . '/admin_profile.php');
    }
    $action = $_POST['address_action'];
    $addressId = (int) ($_POST['address_id'] ?? 0);
    $addressTitle = trim($_POST['address_title'] ?? '');
    $addressText = trim($_POST['address'] ?? '');

        if ($action === 'add') {
            if ($addressTitle === '' || $addressText === '') {
                setFlash('address_error', 'Adres başlığı ve adres bilgisi zorunludur.');
            } else {
                try {
                    $nextId = getNextAvailableId($pdo, 'admin_addresses');
                    $stmt = $pdo->prepare('INSERT INTO admin_addresses (id, admin_id, address_title, address, is_default) VALUES (:id, :admin_id, :title, :address, 0)');
                    $stmt->execute(['id' => $nextId, 'admin_id' => $admin['id'], 'title' => $addressTitle, 'address' => $addressText]);
                    try { updateTableAutoIncrement($pdo, 'admin_addresses'); } catch (Exception $e) {}
                    setFlash('address_success', 'Adres eklendi.');
                    redirect(BASE_URL . '/admin_profile.php');
                } catch (PDOException $e) {
                    setFlash('address_error', 'Adres eklenirken bir hata oluştu.');
                }
            }
        } elseif ($action === 'delete' && $addressId > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM admin_addresses WHERE id = :id AND admin_id = :admin_id');
                $stmt->execute(['id' => $addressId, 'admin_id' => $admin['id']]);
                setFlash('address_success', 'Adres silindi.');
                redirect(BASE_URL . '/admin_profile.php');
            } catch (PDOException $e) {
                setFlash('address_error', 'Adres silinirken bir hata oluştu.');
            }
        } elseif ($action === 'set_default' && $addressId > 0) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('UPDATE admin_addresses SET is_default = 0 WHERE admin_id = :admin_id');
                $stmt->execute(['admin_id' => $admin['id']]);
                $stmt = $pdo->prepare('UPDATE admin_addresses SET is_default = 1 WHERE id = :id AND admin_id = :admin_id');
                $stmt->execute(['id' => $addressId, 'admin_id' => $admin['id']]);
                $pdo->commit();
                setFlash('address_success', 'Varsayılan adres güncellendi.');
                redirect(BASE_URL . '/admin_profile.php');
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                setFlash('address_error', 'Varsayılan adres güncellenirken bir hata oluştu.');
            }
        }
}

$adminAddresses = [];
try {
    $addressesStmt = $pdo->prepare('SELECT * FROM admin_addresses WHERE admin_id = :admin_id ORDER BY id DESC');
    $addressesStmt->execute(['admin_id' => $admin['id']]);
    $adminAddresses = $addressesStmt->fetchAll();
} catch (PDOException $e) {}

$hasFullName = (bool) $pdo->query("SHOW COLUMNS FROM users LIKE 'full_name'")->fetch();

require __DIR__ . '/partials/header.php';
?>

<section class="card" style="max-width: 600px; margin: 2rem auto;">
    <h1>Profilim</h1>

    <?php if ($success || ($message = getFlash('success'))): ?>
        <div class="alert alert-success"><?php echo sanitize($message ?? 'Profil bilgileriniz güncellendi.'); ?></div>
    <?php endif; ?>
    <?php getFlash('error'); ?>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php echo csrf_field(); ?>
        <label for="username">Kullanıcı Adı *</label>
        <input id="username" name="username" value="<?php echo sanitize($_POST['username'] ?? $currentUser['username'] ?? ''); ?>" required minlength="3">

        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" value="<?php echo sanitize($_POST['email'] ?? $currentUser['email'] ?? ''); ?>">

        <?php if ($hasFullName): ?>
        <label for="full_name">Ad Soyad</label>
        <input id="full_name" name="full_name" value="<?php echo sanitize($_POST['full_name'] ?? $currentUser['full_name'] ?? ''); ?>">
        <?php endif; ?>

        <label for="phone">Telefon</label>
        <input id="phone" name="phone" type="tel" value="<?php echo sanitize($_POST['phone'] ?? $currentUser['phone'] ?? ''); ?>" placeholder="05XX XXX XX XX">

        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border);">

        <h3 style="margin-top: 0;">Şifre Değiştir</h3>
        <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 1rem;">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>

        <label for="password">Yeni Şifre</label>
        <input id="password" name="password" type="password" minlength="8" placeholder="Min. 8 karakter, 1 büyük, 1 küçük, 1 özel karakter">

        <label for="password_confirm">Yeni Şifre Tekrar</label>
        <input id="password_confirm" name="password_confirm" type="password" minlength="8" placeholder="Şifreyi tekrar girin">

        <div style="margin-top: 1.5rem;">
            <button class="btn btn-primary" type="submit">Güncelle</button>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-outline" style="margin-left: 0.5rem;">İptal</a>
        </div>
    </form>

    <div style="margin-top: 2rem; padding: 1rem; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border);">
        <h3 style="margin-top: 0;">Hesap Bilgileri</h3>
        <p style="margin: 0.5rem 0; color: var(--muted);"><strong>Üyelik Tarihi:</strong> <?php echo date('d.m.Y', strtotime($currentUser['created_at'] ?? 'now')); ?></p>
        <p style="margin: 0.5rem 0; color: var(--muted);"><strong>Rol:</strong> <span style="color: var(--primary);"><?php echo sanitize($currentUser['role'] ?? 'admin'); ?></span></p>
    </div>
</section>

<section class="card" style="max-width: 600px; margin: 2rem auto;">
    <h2>Kayıtlı Adreslerim</h2>

    <?php if ($message = getFlash('address_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error = getFlash('address_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <?php if (count($adminAddresses) > 0): ?>
        <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
            <?php foreach ($adminAddresses as $addr): ?>
                <div style="padding: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--light);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <div>
                            <strong><?php echo sanitize($addr['address_title']); ?></strong>
                            <?php if ($addr['is_default']): ?>
                                <span style="font-size: 0.85rem; color: var(--primary); margin-left: 0.5rem;">(Varsayılan)</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <?php if (!$addr['is_default']): ?>
                                <form method="post" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="address_action" value="set_default">
                                    <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                    <button type="submit" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">Varsayılan Yap</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" style="display: inline;" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="address_action" value="delete">
                                <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                                <button type="submit" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background: #dc2626; color: white;">Sil</button>
                            </form>
                        </div>
                    </div>
                    <p style="margin: 0; color: var(--muted);"><?php echo nl2br(sanitize($addr['address'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--muted); margin-bottom: 2rem;">Henüz kayıtlı adresiniz yok.</p>
    <?php endif; ?>

    <h3>Yeni Adres Ekle</h3>
    <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="address_action" value="add">
        <label for="address_title">Adres Başlığı (örn: Ev, İş, Ofis) *</label>
        <input id="address_title" name="address_title" type="text" required placeholder="Ev">

        <label for="address_new">Adres *</label>
        <textarea id="address_new" name="address" rows="4" required placeholder="Adres bilgilerinizi giriniz..."></textarea>

        <button class="btn btn-primary" type="submit" style="margin-top: 1rem;">Adres Ekle</button>
    </form>
</section>

<section class="card" style="max-width: 600px; margin: 2rem auto; border: 2px solid #dc2626;">
    <h2 style="color: #dc2626; margin-top: 0;">Tehlikeli Bölge</h2>
    <p style="color: var(--muted); margin-bottom: 1.5rem;">Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve tüm verileriniz kalıcı olarak silinecektir.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" id="deleteAccountForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="delete_account" value="1">
        <button class="btn" type="submit" id="deleteAccountBtn" style="background: #dc2626; color: white; width: 100%;">
            Hesabımı Kalıcı Olarak Sil
        </button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteForm = document.getElementById('deleteAccountForm');
    const deleteBtn = document.getElementById('deleteAccountBtn');
    if (deleteForm && deleteBtn) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const confirmed = confirm('Hesabınızı silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz ve tüm verileriniz kalıcı olarak silinecektir.\n\nDevam etmek istiyor musunuz?');
            if (confirmed) {
                const doubleConfirm = confirm('Son uyarı: Hesabınız kalıcı olarak silinecek. Bu işlemi gerçekten yapmak istiyor musunuz?');
                if (doubleConfirm) deleteForm.submit();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
