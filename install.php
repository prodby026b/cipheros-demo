<?php
/**
 * Cipher OS — نصب‌کننده دیتابیس
 * ------------------------------------------------------------------
 * این فایل را یک‌بار در مرورگر اجرا کنید تا همه جداول ساخته شوند:
 *   http://localhost/cipheros_updated/install.php
 *
 * پس از نصب موفق، این فایل را حذف کنید!
 */
require_once __DIR__ . '/cipher-core/security.php';

$logs = [];
function log_line($m){ global $logs; $logs[] = $m; }

// --- خواندن تنظیمات اتصال (مشابه db.php ولی مستقل، چون دیتابیس هنوز ساخته نشده) ---
if (file_exists(__DIR__ . '/cipher-core/.dbconfig.php')) {
    $cfg = require __DIR__ . '/cipher-core/.dbconfig.php';
    $DB_HOST = $cfg['host'] ?? 'localhost';
    $DB_USER = $cfg['user'] ?? 'root';
    $DB_PASS = $cfg['pass'] ?? 'varxk72mq6bb8j6';
    $DB_NAME = $cfg['name'] ?? 'cipher_os';
} else {
    $DB_HOST = 'localhost';
    $DB_USER = 'root';
    $DB_PASS = '';
    $DB_NAME = 'cipher_os';
}

// اتصال (بدون انتخاب دیتابیس تا بتوانیم آن را بسازیم)
// PHP 8.2: mysqli خطا را به‌صورت Exception پرتاب می‌کند، نه false
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);
} catch (\Throwable $e) {
    die('<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><style>'
        . 'body{background:#03040d;color:#e8edf8;font-family:Vazirmatn,monospace;display:flex;align-items:center;justify-content:center;min-height:100vh;}'
        . 'div{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:40px;max-width:500px;text-align:center;}'
        . 'h2{color:#ef4444;margin-bottom:15px;}p{color:#8899b8;line-height:2;}'
        . '</style></head><body><div>'
        . '<h2>❌ MySQL خاموش است</h2>'
        . '<p>لطفاً ابتدا <strong>MySQL</strong> را در <strong>XAMPP Control Panel</strong> فعال کنید (روی <strong>Start</strong> کلیک کنید)، سپس این صفحه را دوباره باز کنید.</p>'
        . '<br><a href="" style="color:#00eaff;">🔄 تلاش دوباره</a>'
        . '</div></body></html>');
}
if (!$conn) {
    die('❌ اتصال به MySQL ناموفق: ' . mysqli_connect_error());
}

// ساخت دیتابیس
if (mysqli_query($conn, 'CREATE DATABASE IF NOT EXISTS `' . $DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
    log_line('✅ دیتابیس `' . $DB_NAME . '` ساخته شد/موجود بود');
} else {
    die('❌ ساخت دیتابیس ناموفق: ' . mysqli_error($conn));
}
mysqli_select_db($conn, $DB_NAME);
mysqli_set_charset($conn, 'utf8mb4');

// اجرای schema چت
$chatSql = file_get_contents(__DIR__ . '/cipher-chat/schema.sql');
runSqlFile($conn, $chatSql, 'cipher-chat/schema.sql');

// اجرای schema کاربران
$userSql = file_get_contents(__DIR__ . '/cipher-core/users_schema.sql');
runSqlFile($conn, $userSql, 'cipher-core/users_schema.sql');

// نمایش جداول
$res = mysqli_query($conn, 'SHOW TABLES');
$tables = [];
while ($row = mysqli_fetch_array($res)) $tables[] = $row[0];
log_line('📋 جداول موجود: ' . implode('، ', $tables));

function runSqlFile($conn, $sql, $name) {
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0; $fail = 0;
    foreach ($statements as $st) {
        if ($st === '') continue;
        $clean = preg_replace('/^--.*$/m', '', $st);
        $clean = trim($clean);
        if ($clean === '' || strpos(ltrim($clean), '--') === 0) continue;
        if (mysqli_query($conn, $clean)) $ok++; else { $fail++; log_line("  ⚠️ خطا در `$name`: " . mysqli_error($conn)); }
    }
    log_line("✅ `$name`: $ok دستور موفق" . ($fail ? "، $fail خطا" : ''));
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl"><head><meta charset="UTF-8">
<style>
body{background:#03040d;color:#e8edf8;font-family:'Vazirmatn',monospace;padding:40px;line-height:1.8;}
.box{max-width:700px;margin:auto;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:30px;}
h1{color:#00eaff;font-family:'Space Mono',monospace;letter-spacing:.1em;}
.log{background:#0c0f1e;padding:15px;border-radius:10px;font-family:monospace;font-size:13px;border:1px solid rgba(255,255,255,.08);}
.ok{color:#00ff99;}
.warn{color:#f59e0b;}
a.btn{display:inline-block;margin-top:20px;padding:12px 24px;background:linear-gradient(135deg,#00eaff,#7c3aed);color:#03040d;text-decoration:none;border-radius:12px;font-weight:700;font-family:'Space Mono',monospace;}
.danger{color:#ef4444;}
</style></head>
<body><div class="box">
<h1>⚡ CIPHER OS — نصب</h1>
<div class="log"><?php foreach ($logs as $l): ?>
<div><?= htmlspecialchars($l) ?></div>
<?php endforeach; ?></div>
<br>
<p><strong>نکته:</strong> جدول کاربران با ادمین پیش‌فرض ساخته شد.</p>
<p>🔑 ورود پیش‌فرض: <code>username: admin</code> · <code>password: CHANGE_ME_ON_FIRST_LOGIN</code></p>
<p class="danger">⚠️ حتماً پس از اولین ورود، رمز ادمین را تغییر دهید و فایل install.php را حذف کنید!</p>
<a href="login.php" class="btn">ورود به سیستم →</a>
</div></body></html>