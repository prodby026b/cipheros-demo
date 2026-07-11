<?php
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Media', '#f43f5e');
cipher_navbar('Cipher Media', '🎬', '../', 'MEDIA');

$dir = __DIR__ . '/cloud/';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$videos = glob($dir . '*.{mp4,webm,mkv,avi,mov}', GLOB_BRACE) ?: [];
$images = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
$all = array_merge($videos, $images);
?>
<div class="c-wrap">

  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">🎬 Cipher Media</div>
    <div class="c-sub">مدیریت و پخش فایل‌های رسانه‌ای — <?= count($all) ?> فایل در کتابخانه</div>
    <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
      <span class="c-tag">● <?= count($videos) ?> ویدیو</span>
      <span class="c-tag" style="background:rgba(244,63,94,.08);border-color:rgba(244,63,94,.25);color:#f43f5e;">● <?= count($images) ?> تصویر</span>
    </div>
  </div>

  <?php if (empty($all)): ?>
  <div class="c-panel"><div class="c-empty">
    <div class="c-empty-icon">🎬</div>
    <p>هنوز فایل رسانه‌ای آپلود نشده است.<br>از Cipher Cloud فایل‌های خود را آپلود کنید.</p>
  </div></div>
  <?php else: ?>
  <div class="c-grid-2">
    <?php foreach ($all as $file):
      $name = basename($file);
      $rel  = 'cloud/' . $name;
      $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $isVid = in_array($ext, ['mp4','webm','mkv','avi','mov']);
      $size = file_exists($file) ? round(filesize($file)/1048576, 1) . ' MB' : '?';
    ?>
    <div class="c-card fade-in-item">
      <?php if ($isVid): ?>
      <video controls preload="metadata" style="width:100%;border-radius:10px;background:#000;aspect-ratio:16/9;margin-bottom:12px;">
        <source src="<?= htmlspecialchars($rel) ?>">
      </video>
      <?php else: ?>
      <img src="<?= htmlspecialchars($rel) ?>" alt="<?= htmlspecialchars($name) ?>"
           style="width:100%;border-radius:10px;object-fit:cover;max-height:180px;margin-bottom:12px;">
      <?php endif; ?>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;"
             title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></div>
        <span style="font-family:var(--mono);font-size:10px;color:var(--muted);"><?= $size ?></span>
      </div>
      <a href="<?= htmlspecialchars($rel) ?>" download
         style="display:block;margin-top:10px;text-align:center;padding:8px;border-radius:10px;
                background:rgba(244,63,94,.1);border:1px solid rgba(244,63,94,.25);
                color:#f43f5e;font-family:var(--mono);font-size:11px;">
        ↓ دانلود
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php cipher_foot(); ?>
