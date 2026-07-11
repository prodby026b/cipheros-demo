<?php
/**
 * Cipher OS — Shared Security Core
 * ------------------------------------------------------------------
 * یک فایل واحد برای تمام ماژول‌های Cipher OS که شامل:
 *   - CSRF Protection
 *   - Input Sanitizer (ضد XSS / SQL Injection)
 *   - Rate Limiter (ضد Spam / Brute-force)
 *   - Auth Guard (بررسی session احراز هویت)
 *   - Secure HTTP Headers
 *   - Secure File Upload Guard
 *
 * نحوه استفاده در هر ماژول:
 *   require __DIR__ . '/../cipher-core/security.php';
 *   require_auth();   // در صورت عدم احراز هویت → redirect به login
 * ------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    // تنظیمات امن کوکی سشن
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * ارسال هدرهای امنیتی استاندارد
 */
function send_secure_headers(): void {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; ".
           "img-src 'self' data: blob: https:; ".
           "media-src 'self' blob:; ".
           "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
           "font-src 'self' https://fonts.gstatic.com data:; ".
           "script-src 'self' 'unsafe-inline'; ".
           "connect-src 'self';");
}
send_secure_headers();

/* ==================================================================
 * 1) CSRF PROTECTION
 * ================================================================== */

/**
 * تولید یا بازگردانی توکن CSRF مربوط به سشن فعلی
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * فیلد مخفی HTML برای استفاده در فرم‌ها
 *   <form><?= csrf_field() ?> ... </form>
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * اعتبارسنجی توکن CSRF
 * اگر از طریق POST/PUT/DELETE باشد و توکن نادرست باشد → 403
 */
function verify_csrf(): bool {
    $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || $sent === '') return false;
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
}

/**
 * اگر درخواست از نوع نوشتاری است، توکن CSRF را اجباری بررسی می‌کند
 * برای APIهایی که JSON دریافت می‌کنند، توکن از هدر یا بدنه خوانده می‌شود
 */
function require_csrf(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
        // خواندن توکن از POST، هدر، یا JSON body
        $sent = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? null;
        if ($sent === null) {
            $json = json_decode(file_get_contents('php://input'), true);
            $sent = $json['csrf_token'] ?? null;
        }
        if (!is_string($sent) || empty($_SESSION['csrf_token'])
            || !hash_equals($_SESSION['csrf_token'], $sent)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
            exit;
        }
    }
}

/* ==================================================================
 * 2) INPUT SANITIZER
 * ================================================================== */

/**
 * پاکسازی ورودی کاربر
 *   type: 'string' (پیش‌فرض)، 'int'، 'email'، 'username'، 'html'
 *
 * توجه: این تابع برای نمایش (XSS) پاکسازی می‌کند.
 * برای دیتابیس حتماً از prepared statements استفاده کنید.
 */
function sanitize($input, string $type = 'string'): mixed {
    if ($input === null) return '';
    if (is_array($input)) {
        return array_map(fn($v) => sanitize($v, $type), $input);
    }
    $input = (string)$input;

    switch ($type) {
        case 'int':
            return (int)$input;

        case 'email':
            $input = trim($input);
            return filter_var($input, FILTER_SANITIZE_EMAIL) ?: '';

        case 'username':
            // فقط حروف، اعداد، نقطه، خط زیر، خط تیره (۱ تا ۳۲ کاراکتر)
            $input = trim($input);
            $input = preg_replace('/[^\p{L}\p{N}._-]/u', '', $input);
            return mb_substr($input, 0, 32);

        case 'html':
            // فقط متن ساده (حذف کامل تگ‌ها)
            return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        case 'string':
        default:
            return trim(htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

/**
 * پاکسازی نام فایل (جلوگیری از directory traversal و کاراکترهای خطرناک)
 */
function sanitize_filename(string $name): string {
    // حذف path traversal
    $name = basename($name);
    // فقط کاراکترهای امن
    $name = preg_replace('/[^\p{L}\p{N}._-]/u', '_', $name);
    $name = preg_replace('/_{2,}/', '_', $name);
    return trim($name, '._-');
}

/* ==================================================================
 * 3) RATE LIMITER (file-based)
 * ================================================================== */

/**
 * بررسی محدودیت نرخ درخواست
 *   $key      : شناسه منحصربه‌فرد (مثلاً 'chat_send_'.ip یا user_id)
 *   $max      : حداکثر تعداد درخواست
 *   $seconds  : در بازه چند ثانیه
 *
 * بازگرداندن true = اجازه، false = مسدود
 */
function rate_limit(string $key, int $max = 30, int $seconds = 60): bool {
    $dir = sys_get_temp_dir() . '/cipher_ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . md5($key);
    $now = time();
    $data = [];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $data = json_decode($raw, true) ?: [];
            // حذف درخواست‌های قدیمی
            $data = array_values(array_filter($data, fn($t) => $t > ($now - $seconds)));
        }
    }

    if (count($data) >= $max) return false;

    $data[] = $now;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

/**
 * اگر محدودیت نقض شد → 429 و خروج
 */
function require_rate_limit(string $key, int $max = 30, int $seconds = 60): void {
    if (!rate_limit($key, $max, $seconds)) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . $seconds);
        echo json_encode(['ok' => false, 'error' => 'rate_limited']);
        exit;
    }
}

/* ==================================================================
 * 4) AUTH GUARD
 * ================================================================== */

/**
 * آیا کاربر احراز هویت شده است؟
 */
function is_authed(): bool {
    return !empty($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
}

/**
 * نام کاربری سشن (اگر موجود نباشد، مهمان)
 */
function current_username(): string {
    return $_SESSION['username'] ?? ($_SESSION['cipher_chat_user'] ?? 'guest');
}

/**
 * نام نمایشی کاربر برای چت
 */
function current_chat_user(): string {
    // اولویت با سشن اصلی Cipher OS
    if (!empty($_SESSION['username'])) return $_SESSION['username'];
    if (!empty($_SESSION['cipher_chat_user'])) return $_SESSION['cipher_chat_user'];
    return 'guest_' . substr(md5(uniqid('', true)), 0, 5);
}

/**
 * اگر کاربر احراز هویت نشده → redirect به login
 * برای APIها: بازگرداندن JSON 401
 */
function require_auth(bool $isApi = false): void {
    if (is_authed()) {
        // اطمینان از تنظیم بودن نام کاربری
        if (empty($_SESSION['username'])) {
            $_SESSION['username'] = current_chat_user();
        }
        return;
    }
    if ($isApi) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    } else {
        $login = '/login.php';
        // اگر ماژول در پوشه فرعی است، مسیر صحیح را بساز
        $depth = substr_count($_SERVER['SCRIPT_NAME'] ?? '', '/') - 2;
        if ($depth > 0) $login = str_repeat('../', $depth) . 'login.php';
        header('Location: ' . $login);
    }
    exit;
}

/* ==================================================================
 * 5) SECURE FILE UPLOAD GUARD
 * ================================================================== */

/**
 * آپلود امن فایل
 *   $file         : آرایه $_FILES['name']
 *   $dest_dir     : مسیر پوشه مقصد (بدون اسلش انتهایی)
 *   $allowed_ext  : لیست پسوندهای مجاز (مثلاً ['jpg','jpeg','png','gif','webp'])
 *   $max_size     : حداکثر حجم به بایت (پیش‌فرض ۵ مگابایت)
 *
 * بازگرداندن: ['ok'=>true,'path'=>...] یا ['ok'=>false,'error'=>...]
 */
function secure_upload(array $file, string $dest_dir, array $allowed_ext = ['jpg','jpeg','png','gif','webp'], int $max_size = 5242880): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'invalid_file'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'upload_failed_' . $file['error']];
    }
    if ($file['size'] > $max_size) {
        return ['ok' => false, 'error' => 'file_too_large'];
    }

    // ۱) بررسی پسوند
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        return ['ok' => false, 'error' => 'extension_not_allowed'];
    }

    // ۲) بررسی MIME نوع واقعی (نه چیزی که مرورگر می‌گوید)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed_mime = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    $expected = $allowed_mime[$ext] ?? null;
    if ($expected === null || $mime !== $expected) {
        return ['ok' => false, 'error' => 'mime_mismatch'];
    }

    // ۳) تأیید واقعی بودن تصویر
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
        $check = @getimagesize($file['tmp_name']);
        if ($check === false) {
            return ['ok' => false, 'error' => 'not_an_image'];
        }
    }

    // ۴) ساخت پوشه مقصد
    if (!is_dir($dest_dir)) {
        @mkdir($dest_dir, 0755, true);
    }
    // جلوگیری از index listing و اجرای PHP در پوشه آپلود
    $htaccess = $dest_dir . '/.htaccess';
    if (!is_file($htaccess)) {
        @file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .phar\n");
    }

    // ۵) نام فایل تصادفی + امن
    $safe_name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest_path = rtrim($dest_dir, '/\\') . '/' . $safe_name;

    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        return ['ok' => false, 'error' => 'move_failed'];
    }

    return ['ok' => true, 'path' => $dest_path, 'name' => $safe_name];
}

/* ==================================================================
 * 6) JSON RESPONSE HELPER
 * ================================================================== */

/**
 * ارسال پاسخ JSON استاندارد و خروج
 */
function json_response($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * دریافت ورودی JSON از بدنه درخواست
 */
function json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
