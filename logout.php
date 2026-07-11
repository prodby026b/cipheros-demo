<?php
/**
 * Cipher OS — خروج امن
 * پاکسازی کامل سشن + کوکی
 */
require_once __DIR__ . '/cipher-core/security.php';

// پاک کردن متغیرهای سشن
$_SESSION = [];

// حذف کوکی سشن
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header('Location: login.php');
exit;
