<?php
/**
 * Cipher Chat — اتصال دیتابیس
 * ------------------------------------------------------------------
 * از دیتابیس اصلی cipher_os استفاده می‌کند (یکی‌سازی با کل پروژه).
 * اعتبارنامه‌ها از فایل کانفیگ خوانده می‌شود؛ در صورت نبودن، مقادیر
 * پیش‌فرض XAMPP محلی استفاده می‌شود.
 * ------------------------------------------------------------------
 */

// --- هسته امنیتی مشترک (CSRF / Sanitizer / Rate-limit / Auth) ---
require_once __DIR__ . '/../cipher-core/security.php';

// --- تنظیمات اتصال ---
// در محیط تولید، این مقادیر را در فایلی خارج webroot قرار دهید.
if (file_exists(__DIR__ . '/.dbconfig.php')) {
    $cfg = require __DIR__ . '/.dbconfig.php';
    define('DB_HOST', $cfg['host'] ?? 'localhost');
    define('DB_USER', $cfg['user'] ?? 'root');
    define('DB_PASS', $cfg['pass'] ?? 'varxk72mq6bb8j6');
    define('DB_NAME', $cfg['name'] ?? 'cipher_os');
} else {
    // مقادیر پیش‌فرض محلی (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cipher_os');
}

// غیرفعال کردن نمایش خطا به کاربر (جلوگیری از نشت اطلاعات)
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (\Throwable $e) {
    error_log('[cipher-chat] DB connect failed: ' . $e->getMessage());
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        json_response(['ok' => false, 'error' => 'db_error'], 500);
    } else {
        die('در حال حاضر اتصال به دیتابیس ممکن نیست. لطفاً بعداً تلاش کنید.');
    }
}
if ($conn->connect_error) {
    error_log('[cipher-chat] DB connect failed: ' . $conn->connect_error);
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        json_response(['ok' => false, 'error' => 'db_error'], 500);
    } else {
        die('در حال حاضر اتصال به دیتابیس ممکن نیست. لطفاً بعداً تلاش کنید.');
    }
}

// پشتیبانی کامل از فارسی و ایموجی
if (!$conn->set_charset('utf8mb4')) {
    error_log('[cipher-chat] utf8mb4 charset error: ' . $conn->error);
}

/**
 * helper برای اجرای کوئری‌های آماده (prepared statement) با خروجی ساده
 */
function db_query(mysqli $conn, string $sql, string $types = '', array $params = []) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('[cipher-chat] prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return false;
    }
    if ($types !== '' && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}
