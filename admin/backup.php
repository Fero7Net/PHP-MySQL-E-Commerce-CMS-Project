<?php

require __DIR__ . '/../config.php';

requireAdminLogin();

set_time_limit(0);

ini_set('memory_limit', '512M');

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return number_format($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return number_format($bytes / 1048576, 2) . ' MB';
    return number_format($bytes / 1073741824, 2) . ' GB';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        setFlash('admin_error', 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.');
        redirect('backup.php');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'create_backup') {
        
        try {
            
            $backupDir = __DIR__ . '/../backups/';

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $tempDir = $backupDir . 'temp_' . time() . '/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $sqlFile = $tempDir . 'database.sql';

            $tables = [
                'users', 'site_users', 'user_addresses', 'admin_addresses',
                'pages', 'categories', 'products',
                'orders', 'order_items', 'comments',
                'settings', 'site_visits', 'slider_slides'
            ];

            $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
            
            $output = "-- ManRoMan Database Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: " . $dbName . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tables as $table) {
                try {
                    $output .= "-- Table: $table\n";
                    $output .= "DROP TABLE IF EXISTS `$table`;\n";
                    
                    $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
                    $output .= $createTable['Create Table'] . ";\n\n";
                    
                    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($rows)) {
                        $output .= "INSERT INTO `$table` VALUES\n";
                        $values = [];
                        
                        foreach ($rows as $row) {
                            $rowValues = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    $rowValues[] = $pdo->quote($value);
                                }
                            }
                            $values[] = '(' . implode(',', $rowValues) . ')';
                        }
                        
                        $output .= implode(",\n", $values) . ";\n\n";
                    }
                } catch (PDOException $e) {
                    $output .= "-- Error: Table $table not found\n\n";
                }
            }
            
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($sqlFile, $output);

            $backupSqlFile = $backupDir . 'database.sql';
            if (!copy($sqlFile, $backupSqlFile)) {
                
                file_put_contents($backupSqlFile, $output);
            }

            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive sınıfı bulunamadı. PHP zip extension yüklü olmalıdır.');
            }
            
            $zipFileName = 'backup_' . date('Y-m-d_His') . '.zip';
            $zipFilePath = $backupDir . $zipFileName;
            
            $zip = new ZipArchive();
            
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception('ZIP dosyası oluşturulamadı.');
            }

            $zip->addFile($sqlFile, 'database.sql');

            $rootPath = realpath(__DIR__ . '/../');
            
            if (!$rootPath) {
                throw new Exception('Proje root dizini bulunamadı.');
            }

            $rootPath = rtrim(str_replace('\\', '/', $rootPath), '/');

            $folders = ['products_img', 'uploads'];

            $folderStatus = [];
            $totalFilesAdded = 0;
            
            foreach ($folders as $folder) {
                $fullPath = $rootPath . DIRECTORY_SEPARATOR . $folder;

                if (!is_dir($fullPath)) {
                    $folderStatus[$folder] = [
                        'exists' => false,
                        'readable' => false,
                        'file_count' => 0,
                        'message' => "Uyarı: {$folder} klasörü bulunamadı."
                    ];
                    continue;
                }
                
                if (!is_readable($fullPath)) {
                    $folderStatus[$folder] = [
                        'exists' => true,
                        'readable' => false,
                        'file_count' => 0,
                        'message' => "Uyarı: {$folder} klasörü okunamıyor (izin hatası)."
                    ];
                    continue;
                }

                try {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    $fileCount = 0;
                    
                    foreach ($files as $file) {
                        
                        if ($file->isFile()) {
                            
                            $filePath = $file->getRealPath();

                            $relativePath = substr($filePath, strlen($rootPath) + 1);

                            $relativePath = str_replace('\\', '/', $relativePath);

                            $relativePath = ltrim($relativePath, '/\\');

                            if ($zip->addFile($filePath, $relativePath)) {
                                $fileCount++;
                                $totalFilesAdded++;
                            }
                        }
                    }
                    
                    $folderStatus[$folder] = [
                        'exists' => true,
                        'readable' => true,
                        'file_count' => $fileCount,
                        'message' => "✅ {$folder} klasöründen {$fileCount} adet dosya eklendi."
                    ];
                    
                } catch (Exception $e) {
                    $folderStatus[$folder] = [
                        'exists' => true,
                        'readable' => true,
                        'file_count' => 0,
                        'message' => "❌ {$folder} klasörü taranırken hata: " . $e->getMessage()
                    ];
                }
            }

            $manifest = [
                'version' => '1.0',
                'created_at' => date('Y-m-d H:i:s'),
                'database' => $dbName,
                'tables' => $tables,
                'folders' => $folderStatus,
                'total_files' => $totalFilesAdded
            ];
            
            $manifestContent = json_encode($manifest, JSON_PRETTY_PRINT);
            file_put_contents($tempDir . 'manifest.json', $manifestContent);

            $zip->addFile($tempDir . 'manifest.json', 'manifest.json');

            $zip->close();

            $zipCheck = new ZipArchive();
            if ($zipCheck->open($zipFilePath) === TRUE) {
                $zipFileCount = $zipCheck->numFiles;
                $zipCheck->close();

                if ($zipFileCount <= 2) {
                    $errorMessage = '⚠️ HATA: Zip içine resimler eklenemedi! (Sadece ' . $zipFileCount . ' dosya var)<br>';
                    $errorMessage .= '📁 Lütfen klasör izinlerini kontrol edin.<br>';
                    foreach ($folderStatus as $folder => $status) {
                        $errorMessage .= '• ' . $status['message'] . '<br>';
                    }
                    throw new Exception($errorMessage);
                }

                $successMessage = '✅ Yedek başarıyla oluşturuldu: <strong>' . $zipFileName . '</strong><br>';
                $successMessage .= '📊 Zip içine <strong>' . $zipFileCount . ' adet dosya</strong> eklendi.<br>';
                $successMessage .= '📁 Resim dosyaları: <strong>' . $totalFilesAdded . ' adet</strong><br>';
                
                setFlash('admin_success', $successMessage);
            } else {
                throw new Exception('ZIP dosyası kontrol edilemedi.');
            }

            deleteDirectory($tempDir);
            
        } catch (Exception $e) {
            
            if (isset($tempDir) && is_dir($tempDir)) {
                deleteDirectory($tempDir);
            }
            setFlash('admin_error', 'Yedek oluşturulurken hata oluştu: ' . $e->getMessage());
        }
        
        redirect('backup.php');

    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore_backup' && isset($_FILES['backup_file'])) {
        
        try {
            $uploadedFile = $_FILES['backup_file'];

            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Dosya yüklenirken hata oluştu.');
            }

            $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            if ($fileExtension !== 'zip') {
                throw new Exception('Sadece ZIP dosyaları yüklenebilir.');
            }

            $tempDir = __DIR__ . '/../backups/restore_' . time() . '/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zipPath = $tempDir . basename($uploadedFile['name']);
            if (!move_uploaded_file($uploadedFile['tmp_name'], $zipPath)) {
                throw new Exception('Dosya taşınamadı.');
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== TRUE) {
                throw new Exception('ZIP dosyası açılamadı.');
            }

            $hasDatabase = false;
            $hasProductsImg = false;
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if ($filename === 'database.sql' || $filename === './database.sql') {
                    $hasDatabase = true;
                }
                if (strpos($filename, 'products_img/') === 0 || strpos($filename, './products_img/') === 0) {
                    $hasProductsImg = true;
                }
            }
            
            if (!$hasDatabase) {
                $zip->close();
                deleteDirectory($tempDir);
                throw new Exception('Geçersiz yedek dosyası! database.sql bulunamadı. Bu dosya sisteme ait değil.');
            }

            $targetPath = dirname(__DIR__); 
            $targetPath = rtrim(str_replace('\\', '/', $targetPath), '/');

            $zip->extractTo($targetPath);
            $zip->close();

            $sqlFile = $targetPath . '/database.sql';
            if (!file_exists($sqlFile)) {
                deleteDirectory($tempDir);
                throw new Exception('Geçersiz yedek dosyası! database.sql bulunamadı. Bu dosya sisteme ait değil.');
            }

            $manifestFile = $targetPath . '/manifest.json';
            $isValidBackup = false;
            
            if (file_exists($manifestFile)) {
                $manifest = json_decode(file_get_contents($manifestFile), true);
                if ($manifest && isset($manifest['version'])) {
                    $isValidBackup = true;
                }
            } else {
                
                if (file_exists($sqlFile)) {
                    $isValidBackup = true;
                }
            }
            
            if (!$isValidBackup) {
                deleteDirectory($tempDir);
                throw new Exception('Geçersiz yedek dosyası! Bu dosya sisteme ait değil.');
            }

            $sqlContent = file_get_contents($sqlFile);

            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            $lines = explode("\n", $sqlContent);
            $cleanedLines = [];
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line) || preg_match('/^--/', $line) || preg_match('/^\/\*/', $line)) {
                    continue;
                }
                
                if (preg_match('/\*\//', $line)) {
                    continue;
                }
                $cleanedLines[] = $line;
            }
            $cleanedContent = implode("\n", $cleanedLines);

            $statements = [];
            $currentStatement = '';
            $inString = false;
            $stringChar = '';
            
            for ($i = 0; $i < strlen($cleanedContent); $i++) {
                $char = $cleanedContent[$i];
                $currentStatement .= $char;

                if (($char === '"' || $char === "'") && ($i === 0 || $cleanedContent[$i-1] !== '\\')) {
                    if (!$inString) {
                        $inString = true;
                        $stringChar = $char;
                    } elseif ($char === $stringChar) {
                        $inString = false;
                        $stringChar = '';
                    }
                }

                if ($char === ';' && !$inString) {
                    $statement = trim($currentStatement);
                    if (!empty($statement)) {
                        $statements[] = $statement;
                    }
                    $currentStatement = '';
                }
            }

            if (!empty(trim($currentStatement))) {
                $statements[] = trim($currentStatement);
            }

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        
                        error_log('SQL Error: ' . $e->getMessage() . ' | Statement: ' . substr($statement, 0, 100));
                    }
                }
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            $adminDir = __DIR__; 
            $wrongProductsImgDir = $adminDir . '/products_img';
            $wrongUploadsDir = $adminDir . '/uploads';
            
            $movedFiles = 0;
            $deletedDirs = 0;

            if (is_dir($wrongProductsImgDir)) {
                $targetProductsImgDir = $targetPath . '/products_img';

                if (!is_dir($targetProductsImgDir)) {
                    mkdir($targetProductsImgDir, 0755, true);
                }

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($wrongProductsImgDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $sourcePath = $file->getRealPath();
                        $relativePath = substr($sourcePath, strlen($wrongProductsImgDir) + 1);
                        $destPath = $targetProductsImgDir . '/' . str_replace('\\', '/', $relativePath);

                        $destDir = dirname($destPath);
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0755, true);
                        }

                        if (rename($sourcePath, $destPath)) {
                            $movedFiles++;
                        }
                    }
                }

                if (is_dir($wrongProductsImgDir)) {
                    deleteDirectory($wrongProductsImgDir);
                    $deletedDirs++;
                }
            }

            if (is_dir($wrongUploadsDir)) {
                $targetUploadsDir = $targetPath . '/uploads';
                
                if (!is_dir($targetUploadsDir)) {
                    mkdir($targetUploadsDir, 0755, true);
                }
                
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($wrongUploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $sourcePath = $file->getRealPath();
                        $relativePath = substr($sourcePath, strlen($wrongUploadsDir) + 1);
                        $destPath = $targetUploadsDir . '/' . str_replace('\\', '/', $relativePath);
                        
                        $destDir = dirname($destPath);
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0755, true);
                        }
                        
                        if (rename($sourcePath, $destPath)) {
                            $movedFiles++;
                        }
                    }
                }
                
                if (is_dir($wrongUploadsDir)) {
                    deleteDirectory($wrongUploadsDir);
                    $deletedDirs++;
                }
            }

            $successMessage = '✅ Sistem başarıyla geri yüklendi!<br>';
            $successMessage .= '📊 Veritabanı Yüklendi.<br>';
            
            if ($movedFiles > 0 || $deletedDirs > 0) {
                $successMessage .= "📂 Yanlış konumdan taşıma: {$movedFiles} dosya taşındı, {$deletedDirs} klasör silindi.<br>";
            }

            if (isset($tempDir) && is_dir($tempDir)) {
                deleteDirectory($tempDir);
            }
            
            setFlash('admin_success', $successMessage);
            
        } catch (Exception $e) {
            
            if (isset($tempDir) && is_dir($tempDir)) {
                deleteDirectory($tempDir);
            }
            setFlash('admin_error', $e->getMessage());
        }
        
        redirect('backup.php');

    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_backup' && isset($_POST['filename'])) {
        
        $filename = basename($_POST['filename']);
        $backupDir = __DIR__ . '/../backups/';
        $filePath = $backupDir . $filename;

        if (strpos($filename, 'backup_') === 0 && strpos($filename, '.zip') !== false && file_exists($filePath)) {
            if (unlink($filePath)) {
                setFlash('admin_success', 'Yedek başarıyla silindi: ' . $filename);
            } else {
                setFlash('admin_error', 'Yedek silinirken hata oluştu.');
            }
        } else {
            setFlash('admin_error', 'Geçersiz dosya adı.');
        }

        if (is_dir($backupDir)) {
            $tempFolders = glob($backupDir . 'temp_*', GLOB_ONLYDIR);
            foreach ($tempFolders as $tempFolder) {
                if (is_dir($tempFolder)) {
                    deleteDirectory($tempFolder);
                }
            }

            $sqlFiles = glob($backupDir . '*.sql');
            foreach ($sqlFiles as $sqlFile) {
                if (is_file($sqlFile)) {
                    @unlink($sqlFile);
                }
            }

            $manifestFiles = glob($backupDir . 'manifest.json');
            foreach ($manifestFiles as $manifestFile) {
                if (is_file($manifestFile)) {
                    @unlink($manifestFile);
                }
            }
        }
        
        redirect('backup.php');
    }
}

$backupDir = __DIR__ . '/../backups/';
if (is_dir($backupDir)) {
    
    $tempFolders = glob($backupDir . 'temp_*', GLOB_ONLYDIR);
    foreach ($tempFolders as $tempFolder) {
        if (is_dir($tempFolder)) {
            deleteDirectory($tempFolder);
        }
    }

    $sqlFiles = glob($backupDir . '*.sql');
    foreach ($sqlFiles as $sqlFile) {
        if (is_file($sqlFile)) {
            @unlink($sqlFile);
        }
    }

    $manifestFiles = glob($backupDir . 'manifest.json');
    foreach ($manifestFiles as $manifestFile) {
        if (is_file($manifestFile)) {
            @unlink($manifestFile);
        }
    }
}

$backups = [];

if (is_dir($backupDir)) {
    
    $files = glob($backupDir . 'backup_*.zip');

    rsort($files);
    
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('d.m.Y H:i:s', filemtime($file)),
            'timestamp' => filemtime($file)
        ];
    }
}

include __DIR__ . '/partials/header.php';
?>

<!-- ============================================
     YEDEKLEME VE GERİ YÜKLEME MERKEZİ ARAYÜZÜ
     ============================================ -->
<section class="card" style="margin-top: 2rem; padding-top: 1.5rem; margin-bottom: 3rem; padding-bottom: 3rem;">
    <h1>Yedekleme ve Geri Yükleme Merkezi</h1>
    
    <!-- Flash mesajları -->
    <?php if ($message = getFlash('admin_success')): ?>
        <!-- HTML içeriğine izin ver (güvenli: admin paneli, sadece admin erişebilir) -->
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error = getFlash('admin_error')): ?>
        <!-- Hata mesajları için de HTML'e izin ver (güvenli: admin paneli) -->
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Üst Kısım: Yedekleme ve Geri Yükleme Butonları -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Sol: Yeni Yedek Oluştur -->
        <div class="card" style="padding: 1.25rem; border: 1px solid var(--border); background: var(--light);">
            <h5 style="color: var(--text); margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;">
                💾 Yeni Yedek Oluştur
            </h5>
            <p style="color: var(--muted); margin: 0 0 1rem 0; font-size: 0.875rem; line-height: 1.5;">
                Veritabanı ve tüm resim dosyaları ziplenip sunucuda saklanacaktır.
            </p>
            <form method="post" style="margin: 0;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="create_backup">
                <button class="btn btn-primary" type="submit" style="width: 100%; padding: 0.625rem 1rem; font-size: 0.9375rem;">
                    Yedek Oluştur
                </button>
            </form>
        </div>
        
        <!-- Orta: Yedek Geri Yükle -->
        <div class="card" style="padding: 1.25rem; border: 1px solid var(--border); background: var(--light);">
            <h5 style="color: var(--text); margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;">
                ⚠️ Yedek Geri Yükle
            </h5>
            <p style="color: var(--muted); margin: 0 0 1rem 0; font-size: 0.875rem; line-height: 1.5;">
                <strong style="color: var(--danger, #dc2626);">DİKKAT:</strong> Bu işlem mevcut verileri silip yüklediğiniz yedeği kurar.
            </p>
            <form method="post" enctype="multipart/form-data" onsubmit="return confirm('Mevcut veriler silinecek ve yedek geri yüklenecek. Bu işlem geri alınamaz! Emin misiniz?');" style="margin: 0;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="restore_backup">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <input type="file" id="backup_file" name="backup_file" accept=".zip" required class="backup-file-input" style="flex: 1; padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px; font-size: 0.875rem;">
                    <button class="btn" type="submit" style="background: var(--danger, #dc2626); color: white; padding: 0.5rem 1rem; font-size: 0.875rem; white-space: nowrap;">
                        Yükle
                    </button>
                </div>
            </form>
        </div>
        
    </div>

    <!-- Alt Kısım: Mevcut Yedekler Listesi -->
    <h2 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.25rem; color: var(--text);">Mevcut Yedekler</h2>
    
    <?php if ($backups): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: var(--light);">
                <thead>
                <tr style="background: var(--light); border-bottom: 2px solid var(--border);">
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: var(--text);">Dosya Adı</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: var(--text);">Oluşturulma Tarihi</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: var(--text);">Boyut</th>
                    <th style="padding: 0.75rem; text-align: center; font-weight: 600; font-size: 0.875rem; color: var(--text);">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($backups as $backup): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 0.75rem; color: var(--text); font-size: 0.875rem;">
                            <strong><?php echo sanitize($backup['name']); ?></strong>
                        </td>
                        <td style="padding: 0.75rem; color: var(--muted); font-size: 0.875rem;">
                            <?php echo sanitize($backup['date']); ?>
                        </td>
                        <td style="padding: 0.75rem; color: var(--muted); font-size: 0.875rem;">
                            <?php echo formatFileSize($backup['size']); ?>
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <!-- İndir butonu -->
                            <a class="btn" href="<?php echo BASE_URL; ?>/backups/<?php echo sanitize($backup['name']); ?>" download style="background: var(--success, #10b981); color: white; margin-right: 0.5rem; padding: 0.375rem 0.75rem; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 0.875rem;">
                                İndir
                            </a>
                            
                            <!-- Sil butonu -->
                            <form method="post" style="display: inline-block;" onsubmit="return confirm('Bu yedeği silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_backup">
                                <input type="hidden" name="filename" value="<?php echo sanitize($backup['name']); ?>">
                                <button type="submit" class="btn" style="background: var(--danger, #dc2626); color: white; padding: 0.375rem 0.75rem; border-radius: 4px; font-size: 0.875rem;">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="color: var(--muted); padding: 1.5rem; text-align: center; background: var(--light); border-radius: 0.5rem; border: 1px solid var(--border); font-size: 0.875rem;">
            Henüz yedek bulunmamaktadır.
        </p>
    <?php endif; ?>
</section>

<style>
/* File input genel stili (light mode için) */
.backup-file-input {
    background: var(--light);
    color: var(--text);
    border-color: var(--border);
}

/* Dark mode için file input özel stili */
body.dark-theme .backup-file-input {
    background: #1e293b !important;
    color: var(--text) !important;
    border-color: var(--border) !important;
}

body.dark-theme .backup-file-input::file-selector-button {
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 0.75rem;
    transition: background 0.2s ease;
}

body.dark-theme .backup-file-input::file-selector-button:hover {
    background: var(--primary-dark);
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
