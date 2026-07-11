<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
authStart();
http_response_code(404);
$pageTitle = '404 Not Found — ' . getSetting('blog_name','CipherLog');
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="text-align:center;padding:100px 24px">
    <div style="font-size:80px;color:var(--red);margin-bottom:16px;line-height:1">404</div>
    <h1 style="font-size:24px;color:var(--text);margin-bottom:10px">Page Not Found</h1>
    <p style="color:var(--muted);font-size:13px;margin-bottom:28px">The page you're looking for doesn't exist or has been moved.</p>
    <div style="background:#040810;border:1px solid var(--border);border-radius:8px;padding:14px;display:inline-block;text-align:left;margin-bottom:28px">
        <span style="color:var(--green);font-size:12px">root@cipherlog:~# </span>
        <span style="color:var(--muted);font-size:12px">find / -name "<?= e(basename($_SERVER['REQUEST_URI'])) ?>" 2>/dev/null</span><br>
        <span style="color:var(--red);font-size:11px;display:block;padding-left:14px;margin-top:4px">find: no results found</span>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= url() ?>" class="btn-primary"><i class="ti ti-home"></i>GO HOME</a>
        <a href="<?= url('search.php') ?>" class="btn-outline"><i class="ti ti-search"></i>SEARCH</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
