<?php
// admin/includes/admin_header.php
$blogName = getSetting('blog_name','CipherLog');
$adminUser = getCurrentUser();
$pageTitle = ($pageTitle ?? 'Dashboard') . ' — Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0a0e17;--bg2:#0f1524;--bg3:#141929;--card:#111827;--card2:#1a2035;--green:#00ff9d;--blue:#0af;--red:#ff3e3e;--yellow:#ffd600;--purple:#a78bfa;--text:#e2e8f0;--muted:#64748b;--border:#1e2d4a;--border2:#2a3a5c}
body{background:var(--bg);color:var(--text);font-family:'JetBrains Mono',monospace;min-height:100vh;overflow-x:hidden}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--border2);border-radius:2px}
a{text-decoration:none;color:inherit}
/* Layout */
.admin-wrap{display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--bg2);border-right:1px solid var(--border);flex-shrink:0;display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100;transition:transform .3s}
.sidebar.collapsed{transform:translateX(-240px)}
.main{flex:1;margin-left:240px;display:flex;flex-direction:column;min-height:100vh;transition:margin .3s}
.main.expanded{margin-left:0}
/* Sidebar */
.s-logo{padding:20px 16px;border-bottom:1px solid var(--border);flex-shrink:0}
.s-logo-ascii{font-size:7px;line-height:1.3;color:rgba(0,255,157,.2);margin-bottom:6px;white-space:pre;overflow:hidden;height:40px}
.s-logo-text{font-size:13px;font-weight:700;letter-spacing:2px;color:var(--green)}
.s-logo-sub{font-size:8px;color:var(--muted);letter-spacing:1px;margin-top:2px}
.cursor{display:inline-block;width:7px;height:13px;background:var(--green);animation:blink 1s step-end infinite;vertical-align:-2px;margin-left:1px}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
.s-nav{flex:1;padding:10px 0;overflow-y:auto}
.s-section{padding:10px 16px 4px;font-size:8px;letter-spacing:2px;color:rgba(100,116,139,.5);text-transform:uppercase;display:flex;align-items:center;gap:6px}
.s-section::after{content:'';flex:1;height:1px;background:var(--border)}
.s-link{display:flex;align-items:center;gap:10px;padding:9px 16px;font-size:11px;color:var(--muted);cursor:pointer;border-left:2px solid transparent;transition:all .15s}
.s-link:hover{background:rgba(0,255,157,.04);color:var(--text);border-left-color:var(--border2)}
.s-link.active{background:rgba(0,255,157,.08);color:var(--green);border-left-color:var(--green)}
.s-link i{font-size:14px;width:18px;flex-shrink:0}
.s-badge{margin-left:auto;font-size:8px;padding:2px 6px;border-radius:10px;font-weight:600}
.sb-red{background:rgba(255,62,62,.2);color:var(--red)}.sb-green{background:rgba(0,255,157,.15);color:var(--green)}.sb-blue{background:rgba(0,170,255,.15);color:var(--blue)}
.s-footer{padding:14px 16px;border-top:1px solid var(--border);flex-shrink:0}
.s-user{display:flex;align-items:center;gap:10px}
.s-ava{width:32px;height:32px;border-radius:50%;background:conic-gradient(var(--green),var(--blue),var(--green));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#000;flex-shrink:0}
.s-uname{font-size:11px;font-weight:600;color:var(--text)}
.s-role{font-size:8px;color:var(--green);letter-spacing:1px;margin-top:1px}
.s-ver{margin-left:auto;font-size:8px;color:var(--muted)}
/* Topbar */
.topbar{background:var(--bg2);border-bottom:1px solid var(--border);padding:0 24px;display:flex;align-items:center;gap:14px;height:52px;position:sticky;top:0;z-index:50}
.menu-toggle{width:32px;height:32px;border-radius:5px;border:1px solid var(--border);background:transparent;color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all .15s}
.menu-toggle:hover{border-color:var(--green);color:var(--green)}
.breadcrumb{font-size:10px;display:flex;align-items:center;gap:5px;color:var(--muted)}
.breadcrumb .sep{color:var(--border2)}
.breadcrumb .cur{color:var(--green)}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.live-ind{display:flex;align-items:center;gap:5px;font-size:9px;letter-spacing:1px;color:var(--muted)}
.dot-live{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);animation:pulse 2s infinite;flex-shrink:0}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.5)}}
/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:5px;font-size:11px;font-family:'JetBrains Mono',monospace;font-weight:600;cursor:pointer;border:1px solid;letter-spacing:.5px;transition:all .15s;white-space:nowrap}
.btn-primary{background:var(--green);color:#000;border-color:var(--green)}.btn-primary:hover{background:#00e68a}
.btn-ghost{background:transparent;color:var(--muted);border-color:var(--border)}.btn-ghost:hover{border-color:var(--border2);color:var(--text)}
.btn-danger{background:rgba(255,62,62,.1);color:var(--red);border-color:rgba(255,62,62,.25)}.btn-danger:hover{background:rgba(255,62,62,.2)}
.btn-blue{background:rgba(0,170,255,.1);color:var(--blue);border-color:rgba(0,170,255,.25)}.btn-blue:hover{background:rgba(0,170,255,.2)}
.btn-sm{padding:5px 10px;font-size:10px}
/* Content */
.admin-content{flex:1;padding:24px}
/* Panel */
.panel{background:var(--card);border:1px solid var(--border);border-radius:8px;overflow:hidden}
.panel-head{padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.panel-title{font-size:11px;font-weight:600;letter-spacing:.5px;color:var(--text);display:flex;align-items:center;gap:7px}
.panel-title i{color:var(--green);font-size:14px}
.panel-action{margin-left:auto;font-size:10px;color:var(--blue);cursor:pointer;letter-spacing:.5px}
.panel-action:hover{color:var(--green)}
.panel-body{padding:18px}
/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:18px 16px;position:relative;overflow:hidden;transition:border-color .2s}
.stat-card:hover{border-color:var(--border2)}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.sc-g::before{background:var(--green)}.sc-b::before{background:var(--blue)}.sc-y::before{background:var(--yellow)}.sc-r::before{background:var(--red)}
.stat-icon{position:absolute;top:16px;right:16px;font-size:24px;opacity:.1}
.stat-label{font-size:9px;letter-spacing:2px;color:var(--muted);text-transform:uppercase;margin-bottom:8px}
.stat-value{font-size:26px;font-weight:700;color:var(--text);margin-bottom:4px;line-height:1}
.stat-sub{font-size:10px;color:var(--muted)}
.stat-up{color:var(--green)}
/* Grid */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.grid3{display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px}
.gap-col{display:flex;flex-direction:column;gap:14px}
/* Metric rows */
.metric-row{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border)}
.metric-row:last-child{border-bottom:none}
.metric-name{font-size:11px;color:var(--text)}
.metric-val{font-size:11px;font-weight:600;color:var(--green)}
.mv-b{color:var(--blue)}.mv-r{color:var(--red)}.mv-y{color:var(--yellow)}
.progress-bar{height:3px;background:var(--border);border-radius:2px;overflow:hidden;margin-bottom:10px}
.progress-fill{height:100%;border-radius:2px}
/* Tables */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:11px}
th{padding:10px 14px;text-align:left;font-size:9px;letter-spacing:2px;color:var(--muted);border-bottom:1px solid var(--border);font-weight:600;white-space:nowrap}
td{padding:11px 14px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr:hover td{background:rgba(255,255,255,.02)}
.sbadge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;font-size:9px;font-weight:600;white-space:nowrap}
.s-pub{background:rgba(0,255,157,.1);color:var(--green)}.s-draft{background:rgba(100,116,139,.1);color:var(--muted)}.s-feat{background:rgba(255,214,0,.1);color:var(--yellow)}.s-rev{background:rgba(0,170,255,.1);color:var(--blue)}.s-pend{background:rgba(255,214,0,.1);color:var(--yellow)}.s-spam{background:rgba(255,62,62,.1);color:var(--red)}.s-appr{background:rgba(0,255,157,.1);color:var(--green)}
.row-actions{display:flex;gap:5px}
.icon-btn{width:28px;height:28px;border-radius:5px;border:1px solid var(--border);background:transparent;color:var(--muted);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;transition:all .15s;flex-shrink:0}
.icon-btn:hover{border-color:var(--green);color:var(--green)}
.icon-btn.del:hover{border-color:var(--red);color:var(--red)}
.icon-btn.ib:hover{border-color:var(--blue);color:var(--blue)}
/* Tags */
.ptag{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:20px;font-size:9px;font-weight:600;letter-spacing:.5px;border:1px solid}
.ptag-linux{background:rgba(0,255,157,.08);color:var(--green);border-color:rgba(0,255,157,.2)}
.ptag-network{background:rgba(0,170,255,.08);color:var(--blue);border-color:rgba(0,170,255,.2)}
.ptag-security{background:rgba(255,62,62,.08);color:var(--red);border-color:rgba(255,62,62,.2)}
.ptag-scripting{background:rgba(167,139,250,.08);color:var(--purple);border-color:rgba(167,139,250,.2)}
/* Forms */
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:9px;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px;text-transform:uppercase}
.form-input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:5px;padding:9px 12px;font-size:12px;font-family:'JetBrains Mono',monospace;color:var(--text);outline:none;transition:border-color .15s}
.form-input:focus{border-color:var(--green)}
.form-input::placeholder{color:rgba(100,116,139,.5)}
textarea.form-input{resize:vertical;min-height:100px}
select.form-input{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;cursor:pointer}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
/* Filter bar */
.filter-bar{display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.search-box{display:flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid var(--border);border-radius:5px;padding:0 12px;transition:border-color .15s}
.search-box:focus-within{border-color:var(--green)}
.search-box i{color:var(--muted);font-size:14px;flex-shrink:0}
.search-box input{background:none;border:none;outline:none;font-family:inherit;font-size:11px;color:var(--text);padding:8px 0;min-width:140px}
.search-box input::placeholder{color:rgba(100,116,139,.5)}
.filter-btn{padding:7px 12px;border-radius:5px;border:1px solid var(--border);background:transparent;color:var(--muted);font-size:10px;font-family:inherit;cursor:pointer;letter-spacing:.5px;transition:all .15s}
.filter-btn:hover,.filter-btn.active{border-color:var(--green);color:var(--green);background:rgba(0,255,157,.05)}
/* Toggle */
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)}
.toggle-row:last-child{border-bottom:none}
.toggle-name{font-size:12px;color:var(--text)}
.toggle-desc{font-size:9px;color:var(--muted);margin-top:2px}
.toggle{width:42px;height:23px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);cursor:pointer;position:relative;flex-shrink:0;transition:all .2s}
.toggle.on{background:rgba(0,255,157,.2);border-color:rgba(0,255,157,.4)}
.toggle::after{content:'';position:absolute;width:17px;height:17px;border-radius:50%;background:var(--muted);top:2px;left:2px;transition:all .2s}
.toggle.on::after{background:var(--green);left:21px}
/* Editor toolbar */
.editor-toolbar{display:flex;gap:4px;padding:9px 12px;background:var(--bg3);border:1px solid var(--border);border-bottom:none;border-radius:5px 5px 0 0;flex-wrap:wrap}
.editor-area{border-radius:0 0 5px 5px!important;min-height:300px;font-size:12px}
.editor-sep{width:1px;height:20px;background:var(--border);margin:0 2px;align-self:center}
/* Activity */
.act-item{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.act-item:last-child{border-bottom:none}
.act-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:5px}
.ad-g{background:var(--green)}.ad-b{background:var(--blue)}.ad-r{background:var(--red)}.ad-y{background:var(--yellow)}
.act-text{font-size:11px;color:var(--text);line-height:1.5}
.act-meta{font-size:9px;color:var(--muted);margin-top:2px}
/* Upload zone */
.upload-zone{border:1px dashed var(--border2);border-radius:6px;padding:28px;text-align:center;cursor:pointer;color:var(--muted);font-size:11px;transition:all .2s}
.upload-zone:hover{border-color:var(--green);color:var(--green);background:rgba(0,255,157,.03)}
.upload-zone i{font-size:28px;display:block;margin-bottom:8px;opacity:.6}
/* Alert */
.alert{padding:12px 16px;border-radius:6px;font-size:11px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-success{background:rgba(0,255,157,.1);border:1px solid rgba(0,255,157,.2);color:var(--green)}
.alert-error{background:rgba(255,62,62,.1);border:1px solid rgba(255,62,62,.2);color:var(--red)}
/* Toast */
#toasts{position:fixed;bottom:20px;right:20px;z-index:1000;display:flex;flex-direction:column;gap:8px}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:6px;background:var(--card);border:1px solid var(--border);font-size:11px;color:var(--text);animation:fadeIn .2s ease;min-width:240px}
@keyframes fadeIn{from{opacity:0;transform:translateX(10px)}to{opacity:1;transform:translateX(0)}}
/* Responsive */
@media(max-width:900px){.stats-grid{grid-template-columns:1fr 1fr}.grid3{grid-template-columns:1fr}.grid2{grid-template-columns:1fr}}
</style>
</head>
<body>
<div id="toasts"></div>
<div class="admin-wrap">
<aside class="sidebar" id="sidebar">
  <div class="s-logo">
    <div class="s-logo-ascii"> ___ _      _               
/ __(_)_ __| |_  ___ _ _  
| (__ | | '_ \ ' \/ -_) '_|
\___||_| .__/_||_\___|_|  
       |_|</div>
    <div class="s-logo-text">CIPHER<span style="color:var(--blue)">_</span>LOG<span class="cursor"></span></div>
    <div class="s-logo-sub">root@admin:~#</div>
  </div>
  <nav class="s-nav">
    <div class="s-section">MAIN</div>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='index.php'&&dirname($_SERVER['PHP_SELF'])!=='/'?'active':'' ?>" href="<?= url('admin/') ?>"><i class="ti ti-layout-dashboard"></i>Dashboard</a>
    <div class="s-section">CONTENT</div>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='posts.php'?'active':'' ?>" href="<?= url('admin/posts.php') ?>"><i class="ti ti-file-text"></i>All Posts<span class="s-badge sb-green"><?= (int)queryOne("SELECT COUNT(*) AS c FROM posts",[])['c'] ?></span></a>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='new-post.php'?'active':'' ?>" href="<?= url('admin/new-post.php') ?>"><i class="ti ti-edit"></i>New Post</a>
    <a class="s-link" href="<?= url('admin/posts.php?cat=1') ?>"><i class="ti ti-folder"></i>Categories</a>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='media.php'?'active':'' ?>" href="<?= url('admin/media.php') ?>"><i class="ti ti-photo"></i>Media Library</a>
    <div class="s-section">COMMUNITY</div>
    <?php $pendingComments=(int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='pending'",[])['c']; ?>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='comments.php'?'active':'' ?>" href="<?= url('admin/comments.php') ?>"><i class="ti ti-message-circle"></i>Comments<?php if($pendingComments): ?><span class="s-badge sb-red"><?= $pendingComments ?></span><?php endif; ?></a>
    <?php $subCount=(int)queryOne("SELECT COUNT(*) AS c FROM subscribers WHERE status='active'",[])['c']; ?>
    <a class="s-link" href="<?= url('admin/subscribers.php') ?>"><i class="ti ti-mail"></i>Subscribers<span class="s-badge sb-blue"><?= $subCount ?></span></a>
    <div class="s-section">SYSTEM</div>
    <a class="s-link <?= basename($_SERVER['PHP_SELF'])==='settings.php'?'active':'' ?>" href="<?= url('admin/settings.php') ?>"><i class="ti ti-settings"></i>Settings</a>
    <a class="s-link" href="<?= url('admin/security.php') ?>"><i class="ti ti-shield-lock"></i>Security</a>
    <a class="s-link" href="<?= url('api/rss.php') ?>" target="_blank"><i class="ti ti-rss"></i>RSS Feed</a>
    <a class="s-link" href="<?= url() ?>" target="_blank"><i class="ti ti-world"></i>View Blog</a>
    <a class="s-link" href="<?= url('admin/logout.php') ?>"><i class="ti ti-logout"></i>Logout</a>
  </nav>
  <div class="s-footer">
    <div class="s-user">
      <div class="s-ava"><?= strtoupper(substr($adminUser['username']??'A',0,2)) ?></div>
      <div><div class="s-uname"><?= e($adminUser['username']??'') ?></div><div class="s-role">ROOT ACCESS</div></div>
      <div class="s-ver">v1.0</div>
    </div>
  </div>
</aside>
<div class="main" id="main">
<header class="topbar">
  <button class="menu-toggle" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
  <div class="breadcrumb">
    <i class="ti ti-chevron-right" style="font-size:12px;color:var(--green)"></i>
    <span class="sep">/</span><span>cipher_log</span><span class="sep">/</span>
    <span class="cur"><?= e($activePage ?? basename($_SERVER['PHP_SELF'],'.php')) ?></span>
  </div>
  <div class="topbar-right">
    <div class="live-ind"><span class="dot-live"></span>ONLINE</div>
    <a class="btn btn-ghost btn-sm" href="<?= url('admin/new-post.php') ?>"><i class="ti ti-plus"></i>POST</a>
    <a class="btn btn-primary btn-sm" href="<?= url() ?>" target="_blank"><i class="ti ti-world"></i>VIEW BLOG</a>
  </div>
</header>
<div class="admin-content">
