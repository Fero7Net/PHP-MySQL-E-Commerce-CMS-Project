<?php
declare(strict_types=1);

if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', getenv('APP_ENV') === 'production');
}
$isProduction = IS_PRODUCTION;

date_default_timezone_set('Europe/Istanbul');

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', $isProduction ? '0' : '1');

if (session_status() === PHP_SESSION_NONE) {
    $secure = $isProduction && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=Strict', '', $secure, true);
    }
    session_start();
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_shop';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$baseUrl = getenv('BASE_URL') ?: '/mini_shop';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName),
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );
} catch (PDOException $exception) {
    if ($isProduction) {
        exit('Veritabanı bağlantı hatası.');
    }
    exit('Veritabanı bağlantı hatası: ' . $exception->getMessage());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

$siteUrl = '';
if ($siteUrl === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $siteUrl = $scheme . '://' . $host . $baseUrl;
}
if (!defined('SITE_URL')) {
    define('SITE_URL', rtrim($siteUrl, '/'));
}

require_once __DIR__ . '/functions.php';
