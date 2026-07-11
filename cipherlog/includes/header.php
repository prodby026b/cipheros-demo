<?php
// includes/header.php
$blogName    = getSetting('blog_name', 'CipherLog');
$blogTagline = getSetting('blog_tagline', 'Linux & Network');
$blogUrl     = BLOG_URL;
$pageTitle   = $pageTitle ?? $blogName;
$pageDesc    = $pageDesc  ?? getSetting('blog_tagline');
$gaId        = getSetting('ga_id');

$categories  = query("SELECT * FROM categories ORDER BY post_count DESC");
$topPosts    = getPosts(['limit' => 5]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:type" content="<?= isset($post) ? 'article' : 'website' ?>">
<meta property="og:url" content="<?= e(url($_SERVER['REQUEST_URI'])) ?>">
<?php if (isset($post['og_image']) && $post['og_image']): ?>
<meta property="og:image" content="<?= e(url('uploads/' . $post['og_image'])) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= e(isset($post['canonical_url']) && $post['canonical_url'] ? $post['canonical_url'] : url($_SERVER['REQUEST_URI'])) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= e($blogName) ?> RSS" href="<?= url('api/rss.php') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<?php if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
<?php endif; ?>
<style>
<?php include __DIR__ . '/../assets/css/blog.css'; ?>
</style>
</head>
<body>
<?php if (getSetting('maintenance_mode') === '1' && !isLoggedIn()): ?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px">
  <div>
    <div style="font-size:48px;color:var(--yellow);margin-bottom:16px">⚠</div>
    <h1 style="font-size:24px;color:var(--text);margin-bottom:8px">Maintenance Mode</h1>
    <p style="color:var(--muted);font-size:13px"><?= e($blogName) ?> is under maintenance. Check back soon.</p>
  </div>
</div>
<?php exit; endif; ?>

<!-- Scanline overlay -->
<div class="scanline"></div>

<!-- Reading progress -->
<div class="reading-progress" id="reading-progress"><div class="rp-fill" id="rp-fill"></div></div>

<!-- NAVBAR -->
<nav class="navbar">
  <a class="nav-logo" href="<?= url() ?>">
    <div class="nav-logo-icon"><i class="ti ti-terminal-2"></i></div>
    <div>
      <div class="nav-logo-text"><?= e(strtoupper($blogName)) ?></div>
      <div class="nav-logo-sub"><?= e(strtolower($blogTagline)) ?></div>
    </div>
  </a>
  <div class="nav-links">
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= url() ?>">Home</a>
    <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
    <a class="nav-link" href="<?= url('category.php?slug=' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
    <?php endforeach; ?>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : '' ?>" href="<?= url('about.php') ?>">About</a>
  </div>
  <div class="nav-right">
    <button class="search-trigger" onclick="openSearch()">
      <i class="ti ti-search"></i>
      <span>Search...</span>
      <kbd>⌘K</kbd>
    </button>
    <?php if (isLoggedIn()): ?>
    <a class="btn-admin" href="<?= url('admin/') ?>">
      <span class="dot-live"></span>ADMIN
    </a>
    <?php endif; ?>
  </div>
</nav>

<!-- SEARCH OVERLAY -->
<div class="search-overlay" id="search-overlay">
  <div class="search-modal">
    <div class="sm-top">
      <i class="ti ti-search"></i>
      <input type="text" id="search-input" placeholder="Search posts..." autocomplete="off">
      <kbd onclick="closeSearch()">ESC</kbd>
    </div>
    <div id="search-results-overlay" class="sm-results">
      <div class="sm-empty">Start typing to search...</div>
    </div>
    <div class="sm-footer">
      <span><kbd>↵</kbd> open</span>
      <span><kbd>ESC</kbd> close</span>
    </div>
  </div>
</div>
