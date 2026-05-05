<?php

if (!ob_get_level()) {
    
    ob_start();
} else {
    
    ob_clean();
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    
    if (ob_get_level()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false, 
        'error' => 'PHP Hatası: ' . $errstr . ' (Satır: ' . $errline . ')'
    ]);

    exit;
});

require __DIR__ . '/../config.php';

if (!adminIsLoggedIn()) {
    
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['success' => false, 'error' => 'Yetkisiz erişim.']);

    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz istek.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../products_img/temp/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Geçici klasör oluşturulamadı.']);
            exit;
        }
    }

    $tempMaxAge = 3600; 
    foreach (glob($uploadDir . '*') ?: [] as $oldFile) {
        if (is_file($oldFile) && (time() - filemtime($oldFile)) > $tempMaxAge) {
            @unlink($oldFile);
        }
    }

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Dosya boyutu çok büyük (php.ini limiti).',      
            UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu çok büyük (form limiti).',        
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi.',                         
            UPLOAD_ERR_NO_FILE => 'Dosya seçilmedi.',                              
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı.',                  
            UPLOAD_ERR_CANT_WRITE => 'Dosya yazılamadı.',                          
            UPLOAD_ERR_EXTENSION => 'Bir PHP uzantısı dosya yüklemeyi durdurdu.',  
        ];

        $errorMsg = $errorMessages[$file['error']] ?? 'Bilinmeyen yükleme hatası.';

        echo json_encode(['success' => false, 'error' => $errorMsg]);
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];

    $maxSize = 50 * 1024 * 1024;

    $mimeType = $file['type'];

    if (function_exists('finfo_open') && function_exists('finfo_file')) {
        try {
            
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            
            if ($finfo) {
                
                $detectedMimeType = @finfo_file($finfo, $file['tmp_name']);

                if ($detectedMimeType && $detectedMimeType !== false) {
                    $mimeType = $detectedMimeType;
                }

                @finfo_close($finfo);
            }
        } catch (Exception $e) {
            
        }
    }

    if (!in_array($mimeType, $allowedTypes, true)) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz dosya tipi. Sadece JPEG, PNG, GIF ve WebP kabul edilir. (Algılanan tip: ' . htmlspecialchars($mimeType) . ')']);
        exit;
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'error' => 'Dosya boyutu çok büyük. Maksimum 50MB.']);
        exit;
    }

    if (strpos($mimeType, 'jpeg') !== false || strpos($mimeType, 'jpg') !== false) {
        $extension = 'jpg';
    } elseif (strpos($mimeType, 'png') !== false) {
        $extension = 'png';
    } elseif (strpos($mimeType, 'gif') !== false) {
        $extension = 'gif';
    } elseif (strpos($mimeType, 'webp') !== false) {
        $extension = 'webp';
    } else {
        
        $extension = 'jpg';
    }

    $originalName = $file['name'];

    $pathInfo = pathinfo($originalName);
    $baseName = $pathInfo['filename']; 
    $originalExtension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : $extension;

    $turkishChars = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'];
    $englishChars = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U'];
    $baseName = str_replace($turkishChars, $englishChars, $baseName);

    $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);

    $baseName = preg_replace('/_+/', '_', $baseName);

    $baseName = trim($baseName, '_');

    if (empty($baseName)) {
        $baseName = 'image';
    }

    $finalExtension = !empty($originalExtension) ? $originalExtension : $extension;

    $fileName = $baseName . '.' . $finalExtension;

    $fileName = 'temp_' . uniqid('', true) . '.' . $finalExtension;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'error' => 'Dosya yüklenemedi. Klasör yazma izni kontrol edin.']);
        exit;
    }

    $pathInfo = pathinfo($fileName);
    $resizedFileName = $pathInfo['filename'] . '_resized.' . $pathInfo['extension'];
    $resizedPath = $uploadDir . $resizedFileName;
    
    if (function_exists('resizeImage') && resizeImage($filePath, $resizedPath, 800, 600)) {
        @unlink($filePath);
        $fileName = $resizedFileName;
    }
    
    $imageUrl = BASE_URL . '/products_img/temp/' . $fileName;

    echo json_encode(['success' => true, 'url' => $imageUrl]);

} catch (Exception $e) {

    if (ob_get_level()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false, 
        'error' => 'Hata: ' . $e->getMessage() . ' (Dosya: ' . basename($e->getFile()) . ', Satır: ' . $e->getLine() . ')'
    ]);
} catch (Error $e) {

    if (ob_get_level()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false, 
        'error' => 'Fatal Hata: ' . $e->getMessage() . ' (Dosya: ' . basename($e->getFile()) . ', Satır: ' . $e->getLine() . ')'
    ]);
}

