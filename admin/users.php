<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

$hasRoleColumnGlobal = true; 
try {
    
    $checkRoleStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $roleColumnCheck = $checkRoleStmt->fetch();
    if ($roleColumnCheck) {
        $hasRoleColumnGlobal = true; 
    } else {
        
        $checkRoleStmt2 = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'");
        $count = (int)$checkRoleStmt2->fetchColumn();
        $hasRoleColumnGlobal = $count > 0;

        if (!$hasRoleColumnGlobal) {
            $hasRoleColumnGlobal = true; 
        }
    }

    if (!$hasRoleColumnGlobal) {
        try {
            
            $doubleCheckStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
            $doubleCheckResult = $doubleCheckStmt->fetch();
            
            if (!$doubleCheckResult) {
                
                try {
                    
                    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'admin' AFTER password_hash");
                    
                    $checkRoleStmt2 = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'");
                    $hasRoleColumnGlobal = (int)$checkRoleStmt2->fetchColumn() > 0;
                } catch (PDOException $e) {
                    
                    if (strpos($e->getMessage(), 'AFTER') !== false || strpos($e->getMessage(), '1064') !== false) {
                        try {
                            $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'admin'");
                            
                            $checkRoleStmt3 = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'");
                            $hasRoleColumnGlobal = (int)$checkRoleStmt3->fetchColumn() > 0;
                        } catch (PDOException $e2) {
                            
                            if (strpos($e2->getMessage(), 'Duplicate column name') !== false || strpos($e2->getMessage(), '1060') !== false) {
                                $hasRoleColumnGlobal = true;
                            } else {
                                
                                $hasRoleColumnGlobal = false;
                                error_log("Role kolonu eklenemedi (AFTER olmadan): " . $e2->getMessage());
                            }
                        }
                    } elseif (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                        
                        $hasRoleColumnGlobal = true;
                    } else {
                        
                        $hasRoleColumnGlobal = false;
                        error_log("Role kolonu eklenemedi: " . $e->getMessage());
                    }
                }
            } else {
                
                $hasRoleColumnGlobal = true;
            }
        } catch (PDOException $e) {
            
            if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                $hasRoleColumnGlobal = true;
            } else {
                
                $hasRoleColumnGlobal = false;
                error_log("Role kolonu kontrolü başarısız: " . $e->getMessage());
            }
        }
    }
} catch (PDOException $e) {
    $hasRoleColumnGlobal = false;
    error_log("Role kolonu kontrolü başarısız: " . $e->getMessage());
}

$errors = [];

$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$userType = $_GET['type'] ?? null;

$editingUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userType = $_POST['user_type'] ?? 'admin';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'standard';
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');

        if ($username === '') {
            $errors[] = 'Kullanıcı adı zorunludur.';
        }

        $pwdErrors = $password === '' ? ['Şifre zorunludur.'] : validatePasswordStrength($password);
        if (!empty($pwdErrors)) {
            $errors = array_merge($errors, $pwdErrors);
        }

        if ($userType === 'site' && ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }
        
        if ($userType === 'admin' && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }

        if (!in_array($role, ['admin', 'standard'], true)) {
            $errors[] = 'Geçersiz rol.';
        }

        if (!$errors) {
            try {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
            if ($userType === 'admin') {
                
                $hasEmailColumn = hasEmailColumn($pdo, 'users');

                $hasRoleColumn = $hasRoleColumnGlobal;
                
                if ($hasEmailColumn && $hasRoleColumn) {
                    $statement = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
                    $statement->execute([
                        'username' => $username,
                        'email' => $email ?: null,
                        'password_hash' => $passwordHash,
                        'role' => $role === 'admin' ? 'admin' : 'standard',
                    ]);
                } elseif ($hasEmailColumn) {
                    
                    $statement = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
                    $statement->execute([
                        'username' => $username,
                        'email' => $email ?: null,
                        'password_hash' => $passwordHash,
                    ]);
                } elseif ($hasRoleColumn) {
                    
                    $statement = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)');
                    $statement->execute([
                        'username' => $username,
                        'password_hash' => $passwordHash,
                        'role' => $role === 'admin' ? 'admin' : 'standard',
                    ]);
                } else {
                    
                    $statement = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
                    $statement->execute([
                        'username' => $username,
                        'password_hash' => $passwordHash,
                    ]);
                }
                } else {

                    $hasRoleColumnSiteUsers = false;
                    try {
                        $checkRoleStmt = $pdo->query("SHOW COLUMNS FROM site_users LIKE 'role'");
                        $roleExists = $checkRoleStmt->fetch();
                        if ($roleExists) {
                            $hasRoleColumnSiteUsers = true;
                        } else {
                            
                            try {
                                $pdo->exec("ALTER TABLE site_users ADD COLUMN role VARCHAR(50) DEFAULT 'standard'");
                                $hasRoleColumnSiteUsers = true;
                            } catch (PDOException $e) {
                                if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                                    $hasRoleColumnSiteUsers = true;
                                } else {
                                    $hasRoleColumnSiteUsers = false;
                                    error_log("site_users tablosuna role kolonu eklenemedi: " . $e->getMessage());
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        $hasRoleColumnSiteUsers = false;
                        error_log("site_users tablosunda role kolonu kontrolü başarısız: " . $e->getMessage());
                    }

                    $nextId = getNextAvailableId($pdo, 'site_users');
                    if ($hasRoleColumnSiteUsers) {
                        $statement = $pdo->prepare('INSERT INTO site_users (id, username, email, password_hash, full_name, role) VALUES (:id, :username, :email, :password_hash, :full_name, :role)');
                        $statement->execute([
                            'id' => $nextId,
                            'username' => $username,
                            'email' => $email,
                            'password_hash' => $passwordHash,
                            'full_name' => $fullName,
                            'role' => $role,
                        ]);
                    } else {
                        
                        $statement = $pdo->prepare('INSERT INTO site_users (id, username, email, password_hash, full_name) VALUES (:id, :username, :email, :password_hash, :full_name)');
                        $statement->execute([
                            'id' => $nextId,
                            'username' => $username,
                            'email' => $email,
                            'password_hash' => $passwordHash,
                            'full_name' => $fullName,
                        ]);
                    }
                    try {
                        updateTableAutoIncrement($pdo, 'site_users');
                    } catch (Exception $e) {
                        
                    }
                }
                
                setFlash('admin_success', 'Kullanıcı eklendi.');
                redirect('users.php');
            } catch (PDOException $e) {
                
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();

                if ($errorCode == 23000 || strpos($errorMessage, 'Duplicate entry') !== false) {
                    if (strpos($errorMessage, 'username') !== false) {
                        $errors[] = 'Bu kullanıcı adı zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.';
                    } elseif (strpos($errorMessage, 'email') !== false) {
                        $errors[] = 'Bu e-posta adresi zaten kullanılıyor. Lütfen farklı bir e-posta adresi seçin.';
                    } else {
                        $errors[] = 'Bu bilgiler zaten kullanılıyor. Lütfen farklı bilgiler girin.';
                    }
                } else {
                    
                    $errors[] = 'Kullanıcı eklenirken bir hata oluştu: ' . htmlspecialchars($errorMessage);
                }
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $userType = $_POST['user_type'] ?? 'admin';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'standard';
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if ($username === '') {
            $errors[] = 'Kullanıcı adı zorunludur.';
        }

        if ($userType === 'site' && ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }
        
        if ($userType === 'admin' && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }

        if (!in_array($role, ['admin', 'standard'], true)) {
            $errors[] = 'Geçersiz rol.';
        }

        if (!$errors) {
            try {
                if ($userType === 'admin') {
                    
                    $hasEmailColumn = hasEmailColumn($pdo, 'users');

                    $hasRoleColumn = false; 
                    try {
                        
                        $finalCheckStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
                        $roleExists = $finalCheckStmt->fetch();
                        if ($roleExists) {
                            $hasRoleColumn = true; 
                        } else {
                            
                            $checkRoleStmt2 = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'");
                            $count = (int)$checkRoleStmt2->fetchColumn();
                            $hasRoleColumn = $count > 0;

                            if (!$hasRoleColumn) {
                                try {
                                    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'admin'");
                                    $hasRoleColumn = true; 
                                } catch (PDOException $e) {
                                    
                                    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                                        $hasRoleColumn = true;
                                    } else {
                                        $hasRoleColumn = false; 
                                        error_log("Role kolonu eklenemedi: " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        
                        $hasRoleColumn = false;
                        error_log("Role kolonu kontrolü başarısız: " . $e->getMessage());
                    }

                    $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = :username AND id != :id');
                    $checkStmt->execute(['username' => $username, 'id' => $id]);
                    if ($checkStmt->fetch()) {
                        $errors[] = 'Bu kullanıcı adı zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.';
                    }

                    if ($hasEmailColumn && !empty($email)) {
                        $checkEmailStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
                        $checkEmailStmt->execute(['email' => $email, 'id' => $id]);
                        if ($checkEmailStmt->fetch()) {
                            $errors[] = 'Bu e-posta adresi zaten kullanılıyor. Lütfen farklı bir e-posta adresi seçin.';
                        }
                    }
                    
                    if (!$errors) {

                        $hasRoleColumn = true; 

                        try {
                            $finalCheckRoleStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
                            $roleColumnExists = $finalCheckRoleStmt->fetch();
                            if ($roleColumnExists) {
                                $hasRoleColumn = true; 
                            } else {
                                
                                try {
                                    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'admin'");
                                    $hasRoleColumn = true;
                                } catch (PDOException $e) {
                                    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                                        $hasRoleColumn = true; 
                                    } else {
                                        $hasRoleColumn = false; 
                                        error_log("Role kolonu eklenemedi: " . $e->getMessage());
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            
                            $hasRoleColumn = false; 
                            error_log("Role kolonu kontrolü başarısız, varsayılan false kullanılıyor: " . $e->getMessage());
                        }
                        
                        if ($password !== '') {
                            $pwdErrors = validatePasswordStrength($password);
                            if (!empty($pwdErrors)) {
                                $errors = array_merge($errors, $pwdErrors);
                            } else {
                                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                                try {
                                    if ($hasEmailColumn && $hasRoleColumn) {
                                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, password_hash = :password_hash, role = :role WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'email' => $email ?: null,
                                            'password_hash' => $passwordHash,
                                            'role' => $role === 'admin' ? 'admin' : 'standard',
                                            'id' => $id,
                                        ]);
                                    } elseif ($hasEmailColumn) {
                                        
                                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, password_hash = :password_hash WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'email' => $email ?: null,
                                            'password_hash' => $passwordHash,
                                            'id' => $id,
                                        ]);
                                    } elseif ($hasRoleColumn) {
                                        
                                        $statement = $pdo->prepare('UPDATE users SET username = :username, password_hash = :password_hash, role = :role WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'password_hash' => $passwordHash,
                                            'role' => $role === 'admin' ? 'admin' : 'standard',
                                            'id' => $id,
                                        ]);
                                    } else {
                                        
                                        $statement = $pdo->prepare('UPDATE users SET username = :username, password_hash = :password_hash WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'password_hash' => $passwordHash,
                                            'id' => $id,
                                        ]);
                                    }
                                } catch (PDOException $e) {
                                    
                                    if (strpos($e->getMessage(), 'role') !== false || strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getMessage(), '1054') !== false) {
                                        $hasRoleColumn = false; 
                                        error_log("Role kolonu hatası yakalandı, role olmadan tekrar deneniyor: " . $e->getMessage());

                                        if ($hasEmailColumn) {
                                            $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, password_hash = :password_hash WHERE id = :id');
                                            $statement->execute([
                                                'username' => $username,
                                                'email' => $email ?: null,
                                                'password_hash' => $passwordHash,
                                                'id' => $id,
                                            ]);
                                        } else {
                                            $statement = $pdo->prepare('UPDATE users SET username = :username, password_hash = :password_hash WHERE id = :id');
                                            $statement->execute([
                                                'username' => $username,
                                                'password_hash' => $passwordHash,
                                                'id' => $id,
                                            ]);
                                        }
                                    } else {
                                        
                                        throw $e;
                                    }
                                }
                            }
                        } else {
                            
                            try {
                                if ($hasEmailColumn && $hasRoleColumn) {
                                    $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'email' => $email ?: null,
                                        'role' => $role === 'admin' ? 'admin' : 'standard',
                                        'id' => $id,
                                    ]);
                                } elseif ($hasEmailColumn) {
                                    
                                    $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'email' => $email ?: null,
                                        'id' => $id,
                                    ]);
                                } elseif ($hasRoleColumn) {
                                    
                                    $statement = $pdo->prepare('UPDATE users SET username = :username, role = :role WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'role' => $role === 'admin' ? 'admin' : 'standard',
                                        'id' => $id,
                                    ]);
                                } else {
                                    
                                    $statement = $pdo->prepare('UPDATE users SET username = :username WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'id' => $id,
                                    ]);
                                }
                            } catch (PDOException $e) {
                                
                                if (strpos($e->getMessage(), 'role') !== false || strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getMessage(), '1054') !== false) {
                                    $hasRoleColumn = false; 
                                    error_log("Role kolonu hatası yakalandı (şifre olmadan), role olmadan tekrar deneniyor: " . $e->getMessage());

                                    if ($hasEmailColumn) {
                                        $statement = $pdo->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'email' => $email ?: null,
                                            'id' => $id,
                                        ]);
                                    } else {
                                        $statement = $pdo->prepare('UPDATE users SET username = :username WHERE id = :id');
                                        $statement->execute([
                                            'username' => $username,
                                            'id' => $id,
                                        ]);
                                    }
                                } else {
                                    
                                    throw $e;
                                }
                            }
                        }
                    }
                } else {

                    $checkStmt = $pdo->prepare('SELECT id FROM site_users WHERE username = :username AND id != :id');
                    $checkStmt->execute(['username' => $username, 'id' => $id]);
                    if ($checkStmt->fetch()) {
                        $errors[] = 'Bu kullanıcı adı zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.';
                    }

                    $checkEmailStmt = $pdo->prepare('SELECT id FROM site_users WHERE email = :email AND id != :id');
                    $checkEmailStmt->execute(['email' => $email, 'id' => $id]);
                    if ($checkEmailStmt->fetch()) {
                        $errors[] = 'Bu e-posta adresi zaten kullanılıyor. Lütfen farklı bir e-posta adresi seçin.';
                    }
                    
                    if (!$errors) {
                        
                        $hasRoleColumnSiteUsers = false;
                        try {
                            $checkRoleStmt = $pdo->query("SHOW COLUMNS FROM site_users LIKE 'role'");
                            $roleExists = $checkRoleStmt->fetch();
                            if ($roleExists) {
                                $hasRoleColumnSiteUsers = true;
                            } else {
                                
                                try {
                                    $pdo->exec("ALTER TABLE site_users ADD COLUMN role VARCHAR(50) DEFAULT 'standard'");
                                    $hasRoleColumnSiteUsers = true;
                                } catch (PDOException $e) {
                                    if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), '1060') !== false) {
                                        $hasRoleColumnSiteUsers = true;
                                    } else {
                                        $hasRoleColumnSiteUsers = false;
                                        error_log("site_users tablosuna role kolonu eklenemedi: " . $e->getMessage());
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            $hasRoleColumnSiteUsers = false;
                            error_log("site_users tablosunda role kolonu kontrolü başarısız: " . $e->getMessage());
                        }
                        
                        if ($password !== '') {
                            $pwdErrors = validatePasswordStrength($password);
                            if (!empty($pwdErrors)) {
                                $errors = array_merge($errors, $pwdErrors);
                            } else {
                                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                                if ($hasRoleColumnSiteUsers) {
                                    $statement = $pdo->prepare('UPDATE site_users SET username = :username, email = :email, full_name = :full_name, status = :status, password_hash = :password_hash, role = :role WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'email' => $email,
                                        'full_name' => $fullName,
                                        'status' => $status,
                                        'password_hash' => $passwordHash,
                                        'role' => $role,
                                        'id' => $id,
                                    ]);
                                } else {
                                    
                                    $statement = $pdo->prepare('UPDATE site_users SET username = :username, email = :email, full_name = :full_name, status = :status, password_hash = :password_hash WHERE id = :id');
                                    $statement->execute([
                                        'username' => $username,
                                        'email' => $email,
                                        'full_name' => $fullName,
                                        'status' => $status,
                                        'password_hash' => $passwordHash,
                                        'id' => $id,
                                    ]);
                                }
                            }
                        } else {
                            
                            if ($hasRoleColumnSiteUsers) {
                                $statement = $pdo->prepare('UPDATE site_users SET username = :username, email = :email, full_name = :full_name, status = :status, role = :role WHERE id = :id');
                                $statement->execute([
                                    'username' => $username,
                                    'email' => $email,
                                    'full_name' => $fullName,
                                    'status' => $status,
                                    'role' => $role,
                                    'id' => $id,
                                ]);
                            } else {
                                
                                $statement = $pdo->prepare('UPDATE site_users SET username = :username, email = :email, full_name = :full_name, status = :status WHERE id = :id');
                                $statement->execute([
                                    'username' => $username,
                                    'email' => $email,
                                    'full_name' => $fullName,
                                    'status' => $status,
                                    'id' => $id,
                                ]);
                            }
                        }
                    }
                }
                
                if (!$errors) {
                    setFlash('admin_success', 'Kullanıcı güncellendi.');
                    redirect('users.php');
                }
            } catch (PDOException $e) {
                
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();

                if ($errorCode == 23000 || strpos($errorMessage, 'Duplicate entry') !== false) {
                    if (strpos($errorMessage, 'username') !== false) {
                        $errors[] = 'Bu kullanıcı adı zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.';
                    } elseif (strpos($errorMessage, 'email') !== false) {
                        $errors[] = 'Bu e-posta adresi zaten kullanılıyor. Lütfen farklı bir e-posta adresi seçin.';
                    } else {
                        $errors[] = 'Bu bilgiler zaten kullanılıyor. Lütfen farklı bilgiler girin.';
                    }
                } else {
                    
                    $errors[] = 'Kullanıcı güncellenirken bir hata oluştu: ' . htmlspecialchars($errorMessage);
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $userType = $_POST['user_type'] ?? 'admin';
        
        if ($userType === 'admin') {
            if ($id !== (int) $_SESSION['admin']['id']) {
                $statement = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $statement->execute(['id' => $id]);
                setFlash('admin_success', 'Kullanıcı silindi.');
            } else {
                setFlash('admin_error', 'Kendi hesabınızı silemezsiniz.');
            }
        } else {
            $statement = $pdo->prepare('DELETE FROM site_users WHERE id = :id');
            $statement->execute(['id' => $id]);
            setFlash('admin_success', 'Kullanıcı silindi.');
        }
        
        redirect('users.php');
    }
}

if ($editId) {
    $userType = $_GET['type'] ?? 'admin';
    if ($userType === 'admin') {
        
        $hasRoleColumn = $hasRoleColumnGlobal;

        try {
            if ($hasRoleColumn) {
                
                $statement = $pdo->prepare('SELECT *, "admin" as user_type, COALESCE(role, "admin") as role FROM users WHERE id = :id');
            } else {
                
                $statement = $pdo->prepare('SELECT *, "admin" as user_type, "admin" as role FROM users WHERE id = :id');
            }
            $statement->execute(['id' => $editId]);
            $editingUser = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            
            if (strpos($e->getMessage(), 'role') !== false) {
                
                $hasRoleColumn = false;
                $statement = $pdo->prepare('SELECT *, "admin" as user_type, "admin" as role FROM users WHERE id = :id');
                $statement->execute(['id' => $editId]);
                $editingUser = $statement->fetch(PDO::FETCH_ASSOC);
            } else {
                
                throw $e;
            }
        }
        if ($editingUser) {
            
            if (($editingUser['role'] ?? '') === 'author') {
                $editingUser['role'] = 'standard';
            }
            
            if (empty($editingUser['role'] ?? '')) {
                $editingUser['role'] = 'admin';
            }
        } else {
            
            setFlash('admin_error', 'Kullanıcı bulunamadı.');
            redirect('users.php');
        }
    } else {
        $statement = $pdo->prepare('SELECT *, "site" as user_type FROM site_users WHERE id = :id');
        $statement->execute(['id' => $editId]);
        $editingUser = $statement->fetch(PDO::FETCH_ASSOC);
        if ($editingUser) {
            if (empty($editingUser['role'] ?? '')) {
                $editingUser['role'] = 'standard';
            }
        } else {
            
            setFlash('admin_error', 'Kullanıcı bulunamadı.');
            redirect('users.php');
        }
    }
}

$hasEmailColumn = hasEmailColumn($pdo, 'users');

$hasRoleColumn = $hasRoleColumnGlobal;

try {
    if ($hasEmailColumn && $hasRoleColumn) {
        
        $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, COALESCE(email, "") as email, COALESCE(role, "admin") as role FROM users ORDER BY id DESC')->fetchAll();
    } elseif ($hasEmailColumn) {
        
        $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, COALESCE(email, "") as email, "admin" as role FROM users ORDER BY id DESC')->fetchAll();
    } elseif ($hasRoleColumn) {
        
        $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, "" as email, COALESCE(role, "admin") as role FROM users ORDER BY id DESC')->fetchAll();
    } else {
        
        $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, "" as email, "admin" as role FROM users ORDER BY id DESC')->fetchAll();
    }
} catch (PDOException $e) {
    
    if (strpos($e->getMessage(), 'role') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        
        $hasRoleColumn = false;
        if ($hasEmailColumn) {
            $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, COALESCE(email, "") as email, "admin" as role FROM users ORDER BY id DESC')->fetchAll();
        } else {
            $adminUsers = $pdo->query('SELECT *, "admin" as user_type, created_at as created, "" as email, "admin" as role FROM users ORDER BY id DESC')->fetchAll();
        }
    } else {
        
        throw $e;
    }
}
$siteUsers = $pdo->query('SELECT *, "site" as user_type, created_at as created FROM site_users ORDER BY id DESC')->fetchAll();

$allUsers = [];
foreach ($adminUsers as $user) {
    
    if ($user['role'] === 'author') {
        $user['role'] = 'standard';
    }
    $allUsers[] = $user;
}
foreach ($siteUsers as $user) {
    if (empty($user['role'])) {
        $user['role'] = 'standard';
    }
    $allUsers[] = $user;
}

usort($allUsers, function($a, $b) {
    return strtotime($b['created']) - strtotime($a['created']);
});

include __DIR__ . '/partials/header.php';
?>

<section class="card" style="margin-top: 2rem; padding-top: 1.5rem;">
    <h1>Kullanıcı Yönetimi</h1>
    <?php if ($message = getFlash('admin_success')): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($message = getFlash('admin_error')): ?>
        <div class="alert alert-error"><?php echo sanitize($message); ?></div>
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
        <input type="hidden" name="action" value="<?php echo $editingUser ? 'update' : 'create'; ?>">
        <?php if ($editingUser): ?>
            <input type="hidden" name="id" value="<?php echo $editingUser['id']; ?>">
            <input type="hidden" name="user_type" value="<?php echo $editingUser['user_type']; ?>">
        <?php else: ?>
            <label for="user_type">Kullanıcı Tipi</label>
            <select id="user_type" name="user_type" required onchange="toggleUserFields()">
                <option value="admin">Admin Kullanıcı</option>
                <option value="site">Standart Kullanıcı</option>
            </select>
        <?php endif; ?>

        <label for="username">Kullanıcı Adı</label>
        <input id="username" name="username" value="<?php echo sanitize($editingUser['username'] ?? ($_POST['username'] ?? '')); ?>" required>

        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" value="<?php echo sanitize($editingUser['email'] ?? ($_POST['email'] ?? '')); ?>" <?php echo (!$editingUser || ($editingUser['user_type'] ?? '') === 'site') ? 'required' : ''; ?>>

        <?php if (!$editingUser || ($editingUser['user_type'] ?? '') === 'site'): ?>
            <label for="full_name">Ad Soyad</label>
            <input id="full_name" name="full_name" value="<?php echo sanitize($editingUser['full_name'] ?? ($_POST['full_name'] ?? '')); ?>">
        <?php endif; ?>

        <label for="password">Şifre <?php if ($editingUser): ?>(Değiştirmek istemiyorsanız boş bırakın)<?php endif; ?></label>
        <input id="password" name="password" type="password" minlength="8" placeholder="Min. 8 karakter, 1 büyük, 1 küçük, 1 özel karakter" <?php echo $editingUser ? '' : 'required'; ?>>

        <label for="role">Rol</label>
        <select id="role" name="role" required>
            <?php 
            $selectedRole = ($editingUser && isset($editingUser['role'])) ? $editingUser['role'] : ($_POST['role'] ?? 'standard');
            ?>
            <option value="standard" <?php echo $selectedRole === 'standard' ? 'selected' : ''; ?>>Standart Kullanıcı</option>
            <option value="admin" <?php echo $selectedRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>

        <?php if ($editingUser && ($editingUser['user_type'] ?? '') === 'site'): ?>
            <label for="status">Durum</label>
            <select id="status" name="status" required>
                <option value="active" <?php echo ($editingUser['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                <option value="inactive" <?php echo ($editingUser['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
            </select>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">
            <?php echo $editingUser ? 'Kullanıcıyı Güncelle' : 'Kullanıcı Ekle'; ?>
        </button>
        <?php if ($editingUser): ?>
            <a class="btn" href="users.php">Formu Temizle</a>
        <?php endif; ?>
    </form>
</section>

<section class="card" style="margin-top: 3rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h2>Kullanıcı Listesi</h2>
    <?php if ($allUsers): ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı Adı</th>
                <th>E-posta</th>
                <th>Ad Soyad</th>
                <th>Rol</th>
                <th>Tip</th>
                <th>Durum</th>
                <th>Oluşturulma</th>
                <th>İşlemler</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($allUsers as $user): ?>
                <tr>
                    <td>
                        <?php 
                        $displayId = $user['user_type'] === 'admin' ? 'A' . $user['id'] : 'S' . $user['id'];
                        echo $displayId;
                        ?>
                    </td>
                    <td><?php echo sanitize($user['username']); ?></td>
                    <td><?php echo sanitize($user['email'] ?? '-'); ?></td>
                    <td><?php echo sanitize($user['full_name'] ?? '-'); ?></td>
                    <td>
                        <span style="color: <?php echo $user['role'] === 'admin' ? '#dc2626' : '#2563eb'; ?>; font-weight: 600;">
                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'Standart Kullanıcı'; ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: var(--muted);">
                            <?php echo $user['user_type'] === 'admin' ? 'Admin' : 'Site'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['user_type'] === 'site'): ?>
                            <span style="color: <?php echo ($user['status'] ?? 'active') === 'active' ? 'green' : 'red'; ?>;">
                                <?php echo ($user['status'] ?? 'active') === 'active' ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: green;">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user['created'])); ?></td>
                    <td style="white-space: nowrap;">
                        <div class="admin-users-actions">
                            <a class="btn" href="users.php?edit_id=<?php echo $user['id']; ?>&type=<?php echo $user['user_type']; ?>">Düzenle</a>
                            <form method="post" style="display:inline; margin: 0;" onsubmit="<?php if ($user['user_type'] === 'admin' && $user['id'] === (int) $_SESSION['admin']['id']): ?>alert('Kendi hesabınızı silemezsiniz!'); return false;<?php else: ?>return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');<?php endif; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="user_type" value="<?php echo $user['user_type']; ?>">
                                <button class="btn btn-delete-user" type="submit" <?php if ($user['user_type'] === 'admin' && $user['id'] === (int) $_SESSION['admin']['id']): ?>disabled title="Kendi hesabınızı silemezsiniz"<?php endif; ?>>🗑️ Sil</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Henüz kullanıcı yok.</p>
    <?php endif; ?>
</section>

<script>
function toggleUserFields() {
    const userType = document.getElementById('user_type').value;
    const emailField = document.getElementById('email').closest('label');
    const fullNameField = document.getElementById('full_name').closest('label');
    
    if (userType === 'site') {
        emailField.style.display = 'block';
        fullNameField.style.display = 'block';
        document.getElementById('email').required = true;
    } else {
        emailField.style.display = 'none';
        fullNameField.style.display = 'none';
        document.getElementById('email').required = false;
    }
}

// Sayfa yüklendiğinde kontrol et
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('user_type') && !document.querySelector('input[name="id"]')) {
        toggleUserFields();
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
