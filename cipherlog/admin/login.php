<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

authStart();
if (isLoggedIn()) redirect(url('admin/'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user && $pass) {
        if (login($user, $pass)) {
            redirect($_GET['redirect'] ?? url('admin/'));
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — <?= e(getSetting('blog_name','CipherLog')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0a0e17;--bg2:#0f1524;--bg3:#141929;--card:#111827;--green:#00ff9d;--blue:#0af;--text:#e2e8f0;--muted:#64748b;--border:#1e2d4a;--red:#ff3e3e}
body{background:var(--bg);color:var(--text);font-family:'JetBrains Mono',monospace;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;position:relative;overflow:hidden}
body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,255,157,.012) 2px,rgba(0,255,157,.012) 4px);pointer-events:none}
.bg-glow{position:fixed;width:500px;height:500px;border-radius:50%;background:radial-gradient(ellipse,rgba(0,255,157,.04),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none}
.card{background:var(--card);border:1px solid var(--border);border-radius:12px;width:420px;max-width:100%;overflow:hidden;position:relative;z-index:1}
.card-head{padding:28px;border-bottom:1px solid var(--border);text-align:center}
.logo{font-size:22px;font-weight:700;letter-spacing:3px;color:var(--green);margin-bottom:6px}
.logo-sub{font-size:9px;color:var(--muted);letter-spacing:2px}
.card-body{padding:28px}
.ascii{font-size:8px;line-height:1.4;color:rgba(0,255,157,.2);text-align:center;margin-bottom:20px;white-space:pre}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:9px;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px;text-transform:uppercase}
.form-input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:6px;padding:11px 14px;font-size:12px;font-family:inherit;color:var(--text);outline:none;transition:border-color .15s}
.form-input:focus{border-color:var(--green)}
.form-input::placeholder{color:rgba(100,116,139,.4)}
.input-wrap{position:relative}
.input-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;pointer-events:none}
.form-input.has-icon{padding-left:36px}
.toggle-pass{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:14px;background:none;border:none}
.toggle-pass:hover{color:var(--green)}
.btn{width:100%;background:var(--green);color:#000;border:none;border-radius:6px;padding:13px;font-family:inherit;font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .2s;margin-top:4px}
.btn:hover{background:#00e68a;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,255,157,.25)}
.error{background:rgba(255,62,62,.1);border:1px solid rgba(255,62,62,.25);border-radius:6px;padding:10px 14px;font-size:11px;color:var(--red);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.card-foot{padding:16px 28px;border-top:1px solid var(--border);text-align:center;font-size:10px;color:var(--muted)}
.card-foot a{color:var(--blue);text-decoration:none}
.card-foot a:hover{color:var(--green)}
.term-row{display:flex;gap:6px;align-items:center;font-size:9px;color:var(--muted);margin-top:4px;padding-top:4px}
.dot-live{width:5px;height:5px;border-radius:50%;background:var(--green);animation:pulse 2s infinite;flex-shrink:0}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.5)}}
</style>
</head>
<body>
<div class="bg-glow"></div>
<div class="card">
  <div class="card-head">
    <div class="ascii">  ____ _       _               
 / ___(_)_ __ | |__   ___ _ __ 
| |   | | '_ \| '_ \ / _ \ '__|
| |___| | |_) | | | |  __/ |   
 \____|_| .__/|_| |_|\___|_|   
        |_|</div>
    <div class="logo">CIPHER_LOG</div>
    <div class="logo-sub">ADMIN PANEL · ROOT ACCESS</div>
  </div>
  <div class="card-body">
    <?php if ($error): ?>
    <div class="error"><i class="ti ti-alert-circle"></i><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <div class="input-wrap">
          <i class="ti ti-user input-icon"></i>
          <input class="form-input has-icon" type="text" name="username" placeholder="prodby026b" required autofocus
                 value="<?= e($_POST['username'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="ti ti-lock input-icon"></i>
          <input class="form-input has-icon" type="password" name="password" id="pass-field" placeholder="••••••••" required>
          <button type="button" class="toggle-pass" onclick="togglePass()" id="pass-eye"><i class="ti ti-eye"></i></button>
        </div>
      </div>
      <button class="btn" type="submit"><i class="ti ti-login"></i> LOGIN TO ADMIN</button>
    </form>
    <div class="term-row">
      <span class="dot-live"></span>
      <span>Secure connection · <?= date('Y-m-d H:i') ?></span>
    </div>
  </div>
  <div class="card-foot">
    <a href="<?= url() ?>">← Back to Blog</a>
  </div>
</div>
<script>
function togglePass(){
  const f=document.getElementById('pass-field');
  const e=document.getElementById('pass-eye');
  if(f.type==='password'){f.type='text';e.innerHTML='<i class="ti ti-eye-off"></i>';}
  else{f.type='password';e.innerHTML='<i class="ti ti-eye"></i>';}
}
document.addEventListener('keydown',e=>{
  if(e.key==='Enter')document.querySelector('form').submit();
});
</script>
</body>
</html>
