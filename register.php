<?php

require __DIR__ . '/config.php';

if (userIsLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Üye Ol';

$errors = [];

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');

    if ($username === '') {
        $errors[] = 'Kullanıcı adı zorunludur.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi girin.';
    }

    $pwdErrors = validatePasswordStrength($password);
    if ($password === '') {
        $errors[] = 'Şifre zorunludur.';
    } elseif (!empty($pwdErrors)) {
        $errors = array_merge($errors, $pwdErrors);
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }

    if (!$errors) {
        
        $statement = $pdo->prepare('SELECT id FROM site_users WHERE username = :username OR email = :email LIMIT 1');
        $statement->execute(['username' => $username, 'email' => $email]);
        if ($statement->fetch()) {
            $errors[] = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $nextId = getNextAvailableId($pdo, 'site_users');
            $statement = $pdo->prepare('INSERT INTO site_users (id, username, email, password_hash, full_name) VALUES (:id, :username, :email, :password_hash, :full_name)');
            $statement->execute([
                'id' => $nextId,
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'full_name' => $fullName,
            ]);
            try {
                updateTableAutoIncrement($pdo, 'site_users');
            } catch (Exception $e) {
                
            }
            $success = true;
            setFlash('success', 'Kayıt başarılı! Giriş yapabilirsiniz.');
            redirect('login.php');
        }
    }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>Üye Ol</h1>
    
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
        <input id="username" name="username" value="<?php echo sanitize($_POST['username'] ?? ''); ?>" required minlength="3">

        <label for="email">E-posta *</label>
        <input id="email" name="email" type="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>

        <label for="full_name">Ad Soyad</label>
        <input id="full_name" name="full_name" value="<?php echo sanitize($_POST['full_name'] ?? ''); ?>">

        <label for="password">Şifre *</label>
        <input id="password" name="password" type="password" required minlength="8" placeholder="Min. 8 karakter, 1 büyük, 1 küçük, 1 özel karakter">

        <label for="password_confirm">Şifre Tekrar *</label>
        <input id="password_confirm" name="password_confirm" type="password" required minlength="8" placeholder="Şifreyi tekrar girin">

        <button class="btn btn-primary" type="submit">Kayıt Ol</button>
    </form>

    <p style="margin-top: 1rem; text-align: center;">
        Zaten üye misiniz? <a href="<?php echo BASE_URL; ?>/login.php">Giriş yapın</a>
    </p>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

