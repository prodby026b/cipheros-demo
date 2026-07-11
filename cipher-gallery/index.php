<?php
session_start();
include '../cipher-core/cipher-theme.php';

$dir = __DIR__ . '/uploads/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$allowedExt = ['jpg','jpeg','png','gif','webp','svg'];
$maxSize    = 10 * 1024 * 1024; // 10MB

// Upload
if (isset($_FILES['imgs']) && !empty($_FILES['imgs']['name'][0])) {
    $uploaded = 0;
    foreach ($_FILES['imgs']['tmp_name'] as $i => $tmp) {
        if ($_FILES['imgs']['error'][$i] !== 0) continue;
        if ($_FILES['imgs']['size'][$i] > $maxSize) continue;
        $ext = strtolower(pathinfo($_FILES['imgs']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) continue;
        $name = time() . '_' . $i . '.' . $ext;
        move_uploaded_file($tmp, $dir . $name);
        $uploaded++;
    }
    header('Location:index.php?uploaded=' . $uploaded); exit;
}

// Delete
if (isset($_GET['del'])) {
    $f = $dir . basename($_GET['del']);
    if (file_exists($f) && strpos(realpath($f), realpath($dir)) === 0) unlink($f);
    header('Location:index.php'); exit;
}

// Load images
$imgFiles = [];
foreach (glob($dir . '*') ?: [] as $f) {
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, $allowedExt) && is_file($f)) {
        $imgFiles[] = ['path' => $f, 'name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
    }
}
usort($imgFiles, fn($a,$b) => $b['time'] - $a['time']);

function fmtSize($b) { return $b < 1048576 ? round($b/1024,1).'KB' : round($b/1048576,1).'MB'; }
$totalSize = array_sum(array_column($imgFiles, 'size'));

cipher_head('Cipher Gallery', '#f472b6');
cipher_navbar('Cipher Gallery', '🖼️', '../', 'GALLERY');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
  <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🖼️ Cipher Gallery</div>
  <div class="c-sub">گالری تصاویر اختصاصی Cipher OS · <?= count($imgFiles) ?> تصویر · <?= fmtSize($totalSize) ?></div>
  <?php if (isset($_GET['uploaded'])): ?>
  <div style="margin-top:10px;padding:10px 16px;border-radius:10px;background:rgba(0,255,153,.07);
       border:1px solid rgba(0,255,153,.2);color:var(--success);font-family:var(--mono);font-size:12px;display:inline-block;">
    ✅ <?= (int)$_GET['uploaded'] ?> تصویر با موفقیت آپلود شد
  </div>
  <?php endif; ?>
</div>

<!-- Upload Zone -->
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <form method="POST" enctype="multipart/form-data" id="uploadForm">
    <div id="dropZone" onclick="document.getElementById('imgIn').click()"
         style="border:2px dashed var(--stroke);border-radius:16px;padding:32px 20px;text-align:center;
                cursor:pointer;transition:.3s;position:relative;"
         ondragover="this.style.borderColor='rgba(244,114,182,.5)';this.style.background='rgba(244,114,182,.04)';event.preventDefault()"
         ondragleave="this.style.borderColor='var(--stroke)';this.style.background='none'"
         ondrop="handleDrop(event)">
      <div style="font-size:48px;margin-bottom:12px;">🖼️</div>
      <div style="font-family:var(--fa);font-size:15px;font-weight:600;margin-bottom:6px;">تصاویر را اینجا بکشید</div>
      <div style="font-family:var(--fa);font-size:12px;color:var(--muted);">یا کلیک کنید — JPG, PNG, GIF, WebP, SVG · حداکثر 10MB</div>
      <div id="dropInfo" style="margin-top:10px;font-family:var(--mono);font-size:11px;color:var(--cyan);"></div>
      <input type="file" id="imgIn" name="imgs[]" multiple accept="image/*"
             style="display:none" onchange="previewFiles(this.files)">
    </div>
    <div id="previewRow" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"></div>
    <button type="submit" id="uploadBtn" style="display:none;" class="c-btn" style="margin-top:14px;">⬆️ آپلود تصاویر</button>
  </form>
</div>

<!-- Filter + Sort bar -->
<div style="display:flex;gap:10px;margin-bottom:18px;align-items:center;flex-wrap:wrap;">
  <div class="c-label" style="margin-bottom:0;"><?= count($imgFiles) ?> تصویر</div>
  <div style="flex:1;"></div>
  <input id="galSearch" class="c-input" placeholder="🔍 جستجو..." style="max-width:200px;"
         oninput="filterGallery(this.value)">
  <button onclick="setView('grid')" id="vGrid" class="c-btn" style="font-size:12px;padding:8px 14px;">⊞ Grid</button>
  <button onclick="setView('list')" id="vList" class="c-btn-ghost" style="font-size:12px;padding:8px 14px;">☰ List</button>
</div>

<!-- Gallery Grid -->
<?php if (empty($imgFiles)): ?>
<div class="c-panel">
  <div class="c-empty">
    <div class="c-empty-icon">🖼️</div>
    <p>هنوز تصویری آپلود نشده.<br>اولین تصویر خود را آپلود کنید.</p>
  </div>
</div>
<?php else: ?>
<div id="gallery" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
  <?php foreach ($imgFiles as $img): ?>
  <div class="gal-item fade-in-item"
       data-name="<?= strtolower(htmlspecialchars($img['name'])) ?>"
       style="background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;
              overflow:hidden;transition:.25s;cursor:pointer;"
       onmouseover="this.style.transform='translateY(-4px)';this.style.borderColor='rgba(244,114,182,.4)'"
       onmouseout="this.style.transform='translateY(0)';this.style.borderColor='var(--stroke)'">
    <div style="aspect-ratio:4/3;overflow:hidden;background:var(--bg3);position:relative;"
         onclick="openLightbox('uploads/<?= urlencode($img['name']) ?>','<?= htmlspecialchars($img['name']) ?>')">
      <img src="uploads/<?= urlencode($img['name']) ?>" alt="<?= htmlspecialchars($img['name']) ?>"
           style="width:100%;height:100%;object-fit:cover;transition:.3s;"
           onmouseover="this.style.transform='scale(1.07)'" onmouseout="this.style.transform='scale(1)'"
           loading="lazy">
    </div>
    <div style="padding:10px 12px;display:flex;align-items:center;justify-content:space-between;gap:6px;">
      <div style="min-width:0;">
        <div style="font-size:11px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <?= htmlspecialchars($img['name']) ?>
        </div>
        <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:2px;">
          <?= fmtSize($img['size']) ?>
        </div>
      </div>
      <div style="display:flex;gap:4px;flex-shrink:0;">
        <a href="uploads/<?= urlencode($img['name']) ?>" download
           style="padding:5px 7px;border-radius:7px;background:rgba(244,114,182,.08);
                  border:1px solid rgba(244,114,182,.2);color:#f472b6;font-size:11px;">⬇️</a>
        <a href="?del=<?= urlencode($img['name']) ?>" onclick="return confirm('حذف؟')"
           style="padding:5px 7px;border-radius:7px;background:rgba(239,68,68,.07);
                  border:1px solid rgba(239,68,68,.18);color:#ef4444;font-size:11px;">✕</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Lightbox -->
<div id="lightbox" onclick="closeLB()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;
            align-items:center;justify-content:center;flex-direction:column;gap:14px;">
  <img id="lbImg" style="max-width:90vw;max-height:82vh;border-radius:12px;border:1px solid rgba(255,255,255,.1);">
  <div id="lbName" style="font-family:var(--mono);font-size:12px;color:var(--muted2);"></div>
  <div style="display:flex;gap:10px;">
    <button onclick="event.stopPropagation();downloadLB()" class="c-btn" style="font-size:12px;padding:8px 16px;">⬇️ دانلود</button>
    <button onclick="closeLB()" class="c-btn-ghost" style="font-size:12px;padding:8px 16px;">✕ بستن</button>
  </div>
</div>
</div>

<script>
// ─── UPLOAD PREVIEW ──────────────────────────────
function previewFiles(files){
  const row=document.getElementById('previewRow');
  const btn=document.getElementById('uploadBtn');
  row.innerHTML='';
  if(!files.length) return;
  document.getElementById('dropInfo').textContent=files.length+' تصویر انتخاب شده';
  btn.style.display='inline-flex';
  btn.textContent='⬆️ آپلود '+files.length+' تصویر';
  Array.from(files).slice(0,8).forEach(f=>{
    const url=URL.createObjectURL(f);
    row.innerHTML+=`<div style="width:80px;height:80px;border-radius:10px;overflow:hidden;border:1px solid var(--stroke);">
      <img src="${url}" style="width:100%;height:100%;object-fit:cover;"></div>`;
  });
  if(files.length>8) row.innerHTML+=`<div style="width:80px;height:80px;border-radius:10px;background:var(--bg2);
    border:1px solid var(--stroke);display:flex;align-items:center;justify-content:center;
    font-family:var(--mono);font-size:13px;color:var(--muted);">+${files.length-8}</div>`;
}
function handleDrop(e){
  e.preventDefault();
  document.getElementById('dropZone').style.borderColor='var(--stroke)';
  document.getElementById('dropZone').style.background='none';
  const dt=new DataTransfer();
  Array.from(e.dataTransfer.files).forEach(f=>dt.items.add(f));
  document.getElementById('imgIn').files=dt.files;
  previewFiles(dt.files);
}

// ─── LIGHTBOX ────────────────────────────────────
let lbSrc='';
function openLightbox(src,name){
  lbSrc=src;
  document.getElementById('lbImg').src=src;
  document.getElementById('lbName').textContent=name;
  document.getElementById('lightbox').style.display='flex';
  document.body.style.overflow='hidden';
}
function closeLB(){
  document.getElementById('lightbox').style.display='none';
  document.body.style.overflow='';
}
function downloadLB(){
  const a=document.createElement('a');a.href=lbSrc;a.download=lbSrc.split('/').pop();a.click();
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeLB();});

// ─── VIEW TOGGLE ──────────────────────────────────
function setView(v){
  const g=document.getElementById('gallery');
  if(!g) return;
  if(v==='list'){
    g.style.gridTemplateColumns='1fr';
    document.getElementById('vList').className='c-btn';
    document.getElementById('vGrid').className='c-btn-ghost';
  } else {
    g.style.gridTemplateColumns='repeat(auto-fill,minmax(180px,1fr))';
    document.getElementById('vGrid').className='c-btn';
    document.getElementById('vList').className='c-btn-ghost';
  }
}

// ─── SEARCH ──────────────────────────────────────
function filterGallery(q){
  document.querySelectorAll('.gal-item').forEach(el=>{
    el.style.display=!q||el.dataset.name.includes(q.toLowerCase())?'block':'none';
  });
}
</script>
<?php cipher_foot(); ?>
