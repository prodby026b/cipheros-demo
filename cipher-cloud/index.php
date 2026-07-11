<?php
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Cloud', '#38bdf8');
cipher_navbar('Cipher Cloud', '☁️', '../', 'CLOUD');

$cloudPath = '../cipher-media/cloud/';
if (!is_dir($cloudPath)) mkdir($cloudPath, 0777, true);

$msg = '';
if (isset($_FILES['cipher_file']) && $_FILES['cipher_file']['error'] === 0) {
    $dest = $cloudPath . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['cipher_file']['name']);
    if (move_uploaded_file($_FILES['cipher_file']['tmp_name'], $dest))
        $msg = 'success';
    else
        $msg = 'error';
}

$files = array_values(array_diff(scandir($cloudPath), ['.', '..']));
rsort($files);

function ficon($name) {
    $e = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $map = ['mp4'=>'🎬','webm'=>'🎬','mkv'=>'🎬','mp3'=>'🎵','wav'=>'🎵',
            'jpg'=>'🖼️','jpeg'=>'🖼️','png'=>'🖼️','gif'=>'🖼️','webp'=>'🖼️',
            'pdf'=>'📄','zip'=>'📦','rar'=>'📦','txt'=>'📝','doc'=>'📝','docx'=>'📝'];
    return $map[$e] ?? '📁';
}
?>
<div class="c-wrap">

  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">☁️ Cipher Cloud</div>
    <div class="c-sub">فضای ابری اختصاصی برای ذخیره، آپلود و دانلود فایل‌های شما</div>
    <div style="margin-top:12px;">
      <span class="c-tag">● <?= count($files) ?> فایل ذخیره شده</span>
    </div>
  </div>

  <?php if ($msg === 'success'): ?>
  <div style="margin-bottom:16px;padding:14px 18px;background:rgba(0,255,153,.07);border:1px solid rgba(0,255,153,.2);border-radius:14px;color:var(--success);font-family:var(--mono);font-size:13px;">
    ✓ فایل با موفقیت آپلود شد
  </div>
  <?php elseif ($msg === 'error'): ?>
  <div style="margin-bottom:16px;padding:14px 18px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:14px;color:var(--danger);font-family:var(--mono);font-size:13px;">
    ✗ خطا در آپلود فایل
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;">

    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div class="c-sec-title" style="margin-bottom:18px;">آپلود فایل جدید</div>
      <form id="cipher-upload-form" method="POST" enctype="multipart/form-data">
        <div style="border:2px dashed rgba(56,189,248,.25);border-radius:14px;padding:28px;text-align:center;margin-bottom:16px;cursor:pointer;transition:.2s;"
             onmouseover="this.style.borderColor='rgba(56,189,248,.5)'" onmouseout="this.style.borderColor='rgba(56,189,248,.25)'">
          <div style="font-size:32px;margin-bottom:8px;">☁️</div>
          <div style="font-family:var(--fa);font-size:13px;color:var(--muted2);margin-bottom:12px;">فایل را اینجا رها کنید</div>
          <input type="file" name="cipher_file" required
                 style="display:block;width:100%;background:var(--bg2);border:1px solid var(--stroke);
                        border-radius:10px;color:var(--text);padding:8px;font-size:12px;">
        </div>
        
        <div id="upload-progress-container" style="display:none; margin-bottom:16px; font-family:var(--mono); font-size:12px;">
          <div style="display:flex; justify-content:between; margin-bottom:6px; color:var(--text);">
            <span id="upload-pct">0%</span>
            <span id="upload-speed" style="margin-right:auto; color:var(--muted);">0 KB/s</span>
          </div>
          <div style="width:100%; height:6px; background:var(--bg2); border:1px solid var(--stroke); border-radius:10px; overflow:hidden;">
            <div id="upload-bar" style="width:0%; height:100%; background:#38bdf8; transition: width 0.1s linear;"></div>
          </div>
        </div>

        <button type="submit" id="upload-submit-btn" class="c-btn" style="width:100%;justify-content:center;">↑ آپلود در Cipher Cloud</button>
      </form>
    </div>

    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div class="c-sec">
        <span class="c-sec-title">فایل‌های ذخیره شده</span>
        <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">↓ دانلود مستقیم</span>
      </div>
      <?php if (empty($files)): ?>
      <div class="c-empty"><div class="c-empty-icon">☁️</div><p>هنوز فایلی آپلود نشده است.</p></div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($files as $f):
          $fp = $cloudPath . $f;
          $size = file_exists($fp) ? round(filesize($fp)/1048576, 2) . ' MB' : '—';
        ?>
        <div class="fade-in-item" style="display:flex;align-items:center;gap:12px;padding:12px 14px;
             background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;transition:.2s;"
             onmouseover="this.style.borderColor='rgba(56,189,248,.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">
          <div style="width:38px;height:38px;border-radius:10px;background:rgba(56,189,248,.08);
                      border:1px solid rgba(56,189,248,.18);display:flex;align-items:center;justify-content:center;
                      font-size:18px;flex-shrink:0;"><?= ficon($f) ?></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                 title="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></div>
            <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:2px;"><?= $size ?></div>
          </div>
          <a href="<?= htmlspecialchars($cloudPath . $f) ?>" download
             style="padding:7px 14px;border-radius:10px;background:rgba(56,189,248,.08);
                    border:1px solid rgba(56,189,248,.2);color:#38bdf8;font-family:var(--mono);
                    font-size:11px;white-space:nowrap;">↓ دانلود</a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.getElementById('cipher-upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    
    const progressContainer = document.getElementById('upload-progress-container');
    const progressBar = document.getElementById('upload-bar');
    const progressPct = document.getElementById('upload-pct');
    const uploadSpeed = document.getElementById('upload-speed');
    const submitBtn = document.getElementById('upload-submit-btn');
    
    // نمایش المان‌های پیشرفت و غیرفعال کردن دکمه سابمیت
    progressContainer.style.display = 'block';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.5';
    submitBtn.innerText = '⏳ در حال آپلود...';
    
    let startTime = Date.now();
    
    // محاسبه درصد و سرعت آپلود
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            // درصد پیشرفت
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressPct.innerText = percent + '%';
            
            // سرعت آپلود
            const duration = (Date.now() - startTime) / 1000; // ثانیه
            if (duration > 0) {
                const bps = e.loaded / duration; // بایت بر ثانیه
                const kbps = bps / 1024;
                if (kbps > 1024) {
                    uploadSpeed.innerText = (kbps / 1024).toFixed(2) + ' MB/s';
                } else {
                    uploadSpeed.innerText = kbps.toFixed(1) + ' KB/s';
                }
            }
        }
    });
    
    // اتمام پردازش و بارگذاری مجدد جهت اعمال تغییرات PHP
    xhr.addEventListener('load', function() {
        // بازخوانی صفحه برای نشان دادن پیام موفقیت/خطا و لیست فایل‌های جدید
        document.open();
        document.write(xhr.responseText);
        document.close();
    });
    
    xhr.open('POST', form.action || window.location.href, true);
    xhr.send(formData);
});
</script>

<?php cipher_foot(); ?>