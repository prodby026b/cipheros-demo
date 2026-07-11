<?php
/**
 * Cipher OS — اتصال دیتابیس اصلی
 * ------------------------------------------------------------------
 * اعتبارنامه‌ها از فایل کانفیگ (در صورت وجود) یا مقادیر پیش‌فرض محلی.
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/cipher-core/security.php';

if (file_exists(__DIR__ . '/cipher-core/.dbconfig.php')) {
    $cfg = require __DIR__ . '/cipher-core/.dbconfig.php';
    define('DB_HOST', $cfg['host'] ?? 'localhost');
    define('DB_USER', $cfg['user'] ?? 'root');
    define('DB_PASS', $cfg['pass'] ?? 'varxk72mq6bb8j6');
    define('DB_NAME', $cfg['name'] ?? 'cipher_os');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cipher_os');
}

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (\Throwable $e) {
    error_log('[cipher-os] DB connect failed: ' . $e->getMessage());
    http_response_code(500);
    die('در حال حاضر اتصال به دیتابیس ممکن نیست. لطفاً بعداً تلاش کنید.');
}

if (!$conn) {
    error_log('[cipher-os] DB connect failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('در حال حاضر اتصال به دیتابیس ممکن نیست. لطفاً بعداً تلاش کنید.');
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('[cipher-os] utf8mb4 error: ' . mysqli_error($conn));
}
