<?php

require __DIR__ . '/config.php';

if (userIsLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Giriş Yap';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
    if ($isEmail) {
        $username = strtolower(trim($username));
    }

    if ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre zorunludur.';
    } else {

        $hasEmailColumn = hasEmailColumn($pdo, 'users');
        if ($hasEmailColumn) {

            $adminStatement = $pdo->prepare('SELECT * FROM users WHERE username = :username OR (email IS NOT NULL AND email != "" AND LOWER(TRIM(email)) = LOWER(TRIM(:username))) LIMIT 1');
        } else {
            $adminStatement = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        }
        $adminStatement->execute(['username' => $username]);
        $adminUser = $adminStatement->fetch();

        if ($adminUser && password_verify($password, $adminUser['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = [
                'id' => $adminUser['id'],
                'username' => $adminUser['username'],
                'role' => $adminUser['role'] ?? 'admin',
            ];
            $redirect = $_GET['redirect'] ?? '';
            if (in_array($redirect, ['checkout.php', 'cart.php'], true) || strpos($redirect, 'checkout') !== false || strpos($redirect, 'cart') !== false) {
                header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
            } else {
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
            }
            exit;
        } else {

            $statement = $pdo->prepare('SELECT * FROM site_users WHERE (username = :username OR email = :username) LIMIT 1');
            $statement->execute(['username' => $username]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                
                if ($user['status'] !== 'active') {
                    
                    $error = 'Hesabınız askıya alınmış. Lütfen yönetici ile iletişime geçin.';
                } else {

                    $updateStatement = $pdo->prepare('UPDATE site_users SET last_login = NOW() WHERE id = :id');
                    $updateStatement->execute(['id' => $user['id']]);

                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'],
                    ];

                    $redirect = get_safe_redirect($_GET['redirect'] ?? 'index.php');
                    redirect(BASE_URL . '/' . ltrim($redirect, '/'));
                }
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı.';
            }
        }
    }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1>Giriş Yap</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <?php if ($message = getFlash('success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <form method="post">
        <?php echo csrf_field(); ?>
        <label for="username">Kullanıcı Adı veya E-posta</label>
        <input id="username" name="username" value="<?php echo sanitize($_POST['username'] ?? ''); ?>" required autofocus>

        <label for="password">Şifre</label>
        <input id="password" name="password" type="password" required>

        <button class="btn btn-primary" type="submit">Giriş Yap</button>
    </form>

    <p style="margin-top: 1rem; text-align: center;">
        Üye değil misiniz? <a href="<?php echo BASE_URL; ?>/register.php">Kayıt olun</a>
    </p>
    
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

