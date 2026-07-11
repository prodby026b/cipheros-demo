<?php
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Stream', '#a78bfa');
cipher_navbar('Cipher Stream', '📺', '../', 'STREAM');

$videoPath = '../cipher-media/videos/';
if (!is_dir($videoPath)) mkdir($videoPath, 0777, true);
$videos = glob($videoPath . '*.{mp4,webm,mkv}', GLOB_BRACE) ?: [];
?>
<div class="c-wrap">

  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📺 Cipher Stream</div>
    <div class="c-sub">پخش امن و داخلی ویدیوها — کتابخانه اختصاصی prodby026b</div>
    <div style="margin-top:12px;display:flex;gap:10px;align-items:center;">
      <span class="c-tag" style="background:rgba(167,139,250,.08);border-color:rgba(167,139,250,.25);color:#a78bfa;">● <?= count($videos) ?> ویدیو</span>
      <span style="font-family:var(--mono);font-size:11px;color:var(--muted);">مسیر: /cipher-media/videos/</span>
    </div>
  </div>

  <?php if (empty($videos)): ?>
  <div class="c-panel"><div class="c-empty">
    <div class="c-empty-icon">📺</div>
    <p>هنوز ویدیویی در کتابخانه وجود ندارد.<br>فایل‌های ویدیویی را در پوشه <code style="color:var(--cyan)">cipher-media/videos/</code> قرار دهید.</p>
  </div></div>
  <?php else: ?>

  <!-- Featured / First Video -->
  <div class="c-panel" style="margin-bottom:22px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div class="c-label" style="margin-bottom:10px;">// NOW PLAYING</div>
    <video id="mainPlayer" controls preload="metadata"
           style="width:100%;border-radius:14px;background:#000;max-height:480px;">
      <source src="<?= htmlspecialchars($videos[0]) ?>" type="video/mp4">
    </video>
    <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;">
      <span id="mainTitle" style="font-size:14px;font-weight:600;"><?= htmlspecialchars(basename($videos[0])) ?></span>
      <a id="mainDl" href="<?= htmlspecialchars($videos[0]) ?>" download
         style="padding:7px 14px;border-radius:10px;background:rgba(167,139,250,.1);
                border:1px solid rgba(167,139,250,.25);color:#a78bfa;font-family:var(--mono);font-size:11px;">↓ دانلود</a>
    </div>
  </div>

  <!-- Playlist -->
  <div class="c-panel">
    <div class="c-sec"><span class="c-sec-title">📋 پلی‌لیست</span><span style="font-family:var(--mono);font-size:10px;color:var(--muted);"><?= count($videos) ?> ویدیو</span></div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($videos as $i => $v):
        $name = basename($v);
        $size = file_exists($v) ? round(filesize($v)/1048576,1) . ' MB' : '—';
      ?>
      <div class="fade-in-item" data-src="<?= htmlspecialchars($v) ?>" data-name="<?= htmlspecialchars($name) ?>"
           onclick="playVideo(this)"
           style="display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer;
                  background:<?= $i===0 ? 'rgba(167,139,250,.1)' : 'var(--bg2)' ?>;
                  border:1px solid <?= $i===0 ? 'rgba(167,139,250,.35)' : 'var(--stroke)' ?>;
                  border-radius:12px;transition:.2s;" id="item-<?= $i ?>">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(167,139,250,.1);
                    border:1px solid rgba(167,139,250,.2);display:flex;align-items:center;justify-content:center;
                    font-size:16px;flex-shrink:0;">🎬</div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($name) ?></div>
          <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:2px;"><?= $size ?></div>
        </div>
        <span style="font-family:var(--mono);font-size:10px;color:rgba(167,139,250,.7);">▶</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  let currentIdx = 0;
  const items = document.querySelectorAll('[id^="item-"]');
  function playVideo(el) {
    const src = el.dataset.src, name = el.dataset.name;
    document.getElementById('mainPlayer').src = src;
    document.getElementById('mainPlayer').play();
    document.getElementById('mainTitle').textContent = name;
    document.getElementById('mainDl').href = src;
    items.forEach(i => {
      i.style.background = 'var(--bg2)';
      i.style.borderColor = 'var(--stroke)';
    });
    el.style.background = 'rgba(167,139,250,.1)';
    el.style.borderColor = 'rgba(167,139,250,.35)';
  }
  </script>

  <?php endif; ?>
</div>
<?php cipher_foot(); ?>
