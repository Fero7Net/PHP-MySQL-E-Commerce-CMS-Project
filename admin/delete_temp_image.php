<?php

require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!adminIsLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Yetkisiz']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
    exit;
}

$imageUrl = trim($_POST['image_url'] ?? '');
if ($imageUrl === '') {
    echo json_encode(['success' => false, 'error' => 'URL yok']);
    exit;
}

if (strpos($imageUrl, 'products_img/temp/') === false && strpos($imageUrl, 'products_img\\temp\\') === false) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz temp URL']);
    exit;
}

$fileName = basename($imageUrl);
if ($fileName === '' || $fileName === '.' || $fileName === '..') {
    echo json_encode(['success' => false, 'error' => 'Geçersiz dosya adı']);
    exit;
}

$tempDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'products_img' . DIRECTORY_SEPARATOR . 'temp';
$filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
$filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

$realTemp = realpath($tempDir);
$realFile = realpath($filePath);
if ($realTemp === false || $realFile === false || strpos($realFile, $realTemp) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Dosya bulunamadı']);
    exit;
}

if (is_file($realFile) && @unlink($realFile)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Silinemedi']);
}
