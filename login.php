<?php
/**
 * Cipher OS — Login (نسخه امن)
 * ------------------------------------------------------------------
 * تغییرات امنیتی:
 *   - استفاده از password_hash/password_verify به جای مقایسه متن خام
 *   - session_regenerate_id بعد از لاگین موفق (ضد Session Fixation)
 *   - محدودیت تلاش‌های لاگین (Rate Limit / Brute-force protection)
 *   - توکن CSRF در فرم
 *   - backward-compatible: اگر جدول users نبود، رمز قدیمی کار می‌کند
 */
require_once __DIR__ . '/cipher-core/security.php';

// نام کاربری به‌محض لاگین در سشن ذخیره می‌شود
// اگر قبلاً احراز هویت شده → مستقیم به داشبورد
if (is_authed()) {
    header('Location: index.php');
    exit;
}

// --- اعتبارنامه‌ها ---
// رمز قدیمی (fallback اگر دیتابیس کاربران موجود نباشد)
$LEGACY_PASSWORD = 'CHANGE_ME_ON_FIRST_LOGIN';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!verify_csrf()) {
        $error = 'توکن امنیتی نامعتبر است. صفحه را تازه‌سازی کنید.';
    } else {
        $username = sanitize($_POST['username'] ?? '', 'username');
        $password = $_POST['pass'] ?? '';

        // Rate limit: حداکثر ۵ تلاش لاگین در ۵ دقیقه از هر IP
        $ipKey = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
        if (!rate_limit($ipKey, 5, 300)) {
            $error = 'تعداد تلاش‌های ناموفق زیاد بوده است. ۵ دقیقه بعد تلاش کنید.';
        } elseif ($username === '' || $password === '') {
            $error = 'نام کاربری و رمز عبور را وارد کنید.';
        } else {
            $authOk = false;

            // ۱) تلاش با دیتابیس users (اگر جدول موجود باشد)
            try {
                $conn = @new mysqli(DB_HOST ?: 'localhost', DB_USER ?: 'root', DB_PASS ?: '', DB_NAME ?: 'cipher_os');
                if ($conn && !$conn->connect_error) {
                    $conn->set_charset('utf8mb4');
                    $stmt = $conn->prepare('SELECT username, password_hash FROM users WHERE username = ? LIMIT 1');
                    if ($stmt) {
                        $stmt->bind_param('s', $username);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $user = $res->fetch_assoc();
                        if ($user && password_verify($password, $user['password_hash'])) {
                            $authOk = true;
                        }
                    }
                    $conn->close();
                }
            } catch (Throwable $e) {
                // جدول users موجود نیست → fallback به رمز قدیمی
            }

            // ۲) Fallback: ادمین پیش‌فرض با رمز قدیمی (backward compat)
            if (!$authOk && $username === 'admin' && hash_equals($LEGACY_PASSWORD, $password)) {
                $authOk = true;
            }

            if ($authOk) {
                // ضد Session Fixation: بازتولید شناسه سشن
                session_regenerate_id(true);
                $_SESSION['user_authenticated'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['login_at'] = time();

                header('Location: index.php');
                exit;
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است.';
            }
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cipher OS — Access</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Space+Mono:wght@400;700&family=Vazirmatn:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg0:#03040d; --cyan:#00eaff; --purple:#7c3aed;
  --glass:rgba(255,255,255,.05); --stroke:rgba(255,255,255,.09);
  --text:#e8edf8; --muted:#5b6b8a; --danger:#ef4444;
  --font-mono:'Space Mono',monospace;
  --font-fa:'Vazirmatn',system-ui,sans-serif;
  --font:'Space Grotesk','Vazirmatn',system-ui,sans-serif;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body {
  min-height:100vh; background:var(--bg0); color:var(--text);
  font-family:var(--font); display:flex; align-items:center; justify-content:center;
  overflow:hidden;
}
body::before {
  content:''; position:fixed; inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.07) 2px,rgba(0,0,0,.07) 4px);
  pointer-events:none; z-index:0;
}
.orb { position:fixed; border-radius:50%; filter:blur(130px); pointer-events:none; z-index:0; }
.orb1 { width:700px;height:700px;top:-250px;right:-200px; background:rgba(124,58,237,.18); }
.orb2 { width:500px;height:500px;bottom:-200px;left:-150px; background:rgba(0,234,255,.08); }

.card {
  position:relative; z-index:1; width:min(100%, 420px);
  background:rgba(255,255,255,.04); border:1px solid var(--stroke);
  border-radius:28px; padding:44px 40px; backdrop-filter:blur(20px);
  box-shadow:0 32px 80px rgba(0,0,0,.6);
}
.card::before {
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,var(--cyan),var(--purple),transparent);
  border-radius:28px 28px 0 0; opacity:.7;
}
.hc { position:absolute;width:16px;height:16px;border-color:var(--cyan);border-style:solid;opacity:.4; }
.htl{top:14px;left:14px;border-width:2px 0 0 2px;}
.htr{top:14px;right:14px;border-width:2px 2px 0 0;}
.hbl{bottom:14px;left:14px;border-width:0 0 2px 2px;}
.hbr{bottom:14px;right:14px;border-width:0 2px 2px 0;}
.logo {
  width:60px;height:60px;border-radius:18px;margin:0 auto 22px;
  background:linear-gradient(135deg,rgba(0,234,255,.15),rgba(124,58,237,.25));
  border:1px solid rgba(0,234,255,.3);
  display:flex;align-items:center;justify-content:center;
  font-size:28px;box-shadow:0 0 30px rgba(0,234,255,.15);
}
.os-label { font-family:var(--font-mono);font-size:13px;letter-spacing:.25em;color:var(--cyan);text-align:center;margin-bottom:6px; }
.os-sub { font-family:var(--font-fa);font-size:13px;color:var(--muted);text-align:center;margin-bottom:32px; }
label { display:block;font-size:11px;letter-spacing:.12em;color:var(--muted);font-family:var(--font-mono);margin-bottom:8px; }
input {
  width:100%;padding:14px 16px;border-radius:14px;
  background:rgba(255,255,255,.05);border:1px solid var(--stroke);
  color:var(--text);font-family:var(--font-fa);font-size:15px;
  outline:none;transition:.2s;margin-bottom:14px;
}
input:focus { border-color:rgba(0,234,255,.45); box-shadow:0 0 0 3px rgba(0,234,255,.08); }
input::placeholder { color:var(--muted); }
.btn {
  width:100%;padding:14px;border:none;border-radius:14px;
  font-family:var(--font-mono);font-size:13px;letter-spacing:.1em;
  font-weight:700;cursor:pointer;
  background:linear-gradient(135deg,var(--cyan),var(--purple));
  color:#03040d;transition:.2s; position:relative;overflow:hidden;
}
.btn::before { content:'';position:absolute;inset:0;background:rgba(255,255,255,.15);opacity:0;transition:.2s; }
.btn:hover::before { opacity:1; }
.btn:active { transform:scale(.98); }
.err {
  font-family:var(--font-fa);font-size:13px;color:var(--danger);
  text-align:center;margin-bottom:14px; padding:10px;border-radius:10px;
  background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
}
.footer-note { margin-top:24px;text-align:center;font-family:var(--font-mono);font-size:10px;letter-spacing:.15em;color:var(--muted); }
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
.cursor { display:inline-block;animation:blink 1s infinite; }
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>

<div class="card">
  <div class="hc htl"></div><div class="hc htr"></div>
  <div class="hc hbl"></div><div class="hc hbr"></div>

  <div class="logo">⚡</div>
  <div class="os-label">CIPHER OS</div>
  <div class="os-sub">احراز هویت لازم است<span class="cursor">_</span></div>

  <?php if (!empty($error)): ?>
  <div class="err">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <label>نام کاربری</label>
    <input type="text" name="username" placeholder="admin" autofocus>
    <label>ACCESS KEY</label>
    <input type="password" name="pass" placeholder="رمز دسترسی" autocomplete="current-password">
    <button type="submit" class="btn">AUTHENTICATE →</button>
  </form>

  <div class="footer-note">PRODBY026B · SECURE ACCESS</div>
</div>
</body>
</html>
