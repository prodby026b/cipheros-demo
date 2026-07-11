<?php
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Music', '#f472b6');
cipher_navbar('Cipher Music', '🎵', '../', 'MUSIC');

$music = glob('music/*.{mp3,wav,ogg,flac}', GLOB_BRACE) ?: [];
?>
<div class="c-wrap">

  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">🎵 Cipher Music</div>
    <div class="c-sub">پلیر موزیک اختصاصی — <?= count($music) ?> آهنگ در کتابخانه</div>
  </div>

  <?php if (empty($music)): ?>
  <div class="c-panel"><div class="c-empty">
    <div class="c-empty-icon">🎵</div>
    <p>هنوز موزیکی اضافه نشده است.<br>فایل‌های <code style="color:#f472b6">mp3 / wav / ogg</code> را در پوشه <code style="color:#f472b6">cipher-music/music/</code> قرار دهید.</p>
  </div></div>
  <?php else: ?>

  <div class="c-panel" style="margin-bottom:22px;text-align:center;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div style="width:100px;height:100px;border-radius:50%;margin:0 auto 18px;
                background:linear-gradient(135deg,rgba(244,114,182,.2),rgba(167,139,250,.2));
                border:2px solid rgba(244,114,182,.3);
                display:flex;align-items:center;justify-content:center;font-size:44px;
                animation:spin 8s linear infinite paused;" id="albumArt">🎧</div>
    <div class="c-label" style="margin-bottom:6px;">NOW PLAYING</div>
    <div id="nowTitle" style="font-size:16px;font-weight:700;margin-bottom:4px;">انتخاب کنید</div>
    <div id="nowSub" style="font-size:12px;color:var(--muted);font-family:var(--mono);">Cipher Music · PRODBY026B</div>
    <audio id="mainAudio" style="width:100%;margin-top:18px;accent-color:#f472b6;"></audio>
    <div style="margin-top:10px;display:flex;gap:10px;justify-content:center;">
      <button onclick="prevTrack()" class="c-btn-ghost">⏮</button>
      <button onclick="togglePlay()" class="c-btn" id="playBtn">▶ پخش</button>
      <button onclick="nextTrack()" class="c-btn-ghost">⏭</button>
    </div>
  </div>

  <div class="c-panel">
    <div class="c-sec" style="margin-bottom:14px;">
      <span class="c-sec-title">📋 لیست آهنگ‌ها</span>
      <span style="font-family:var(--mono);font-size:10px;color:var(--muted);"><?= count($music) ?> track</span>
    </div>

    <div style="margin-bottom:16px; position:relative;">
      <input type="text" id="musicSearch" placeholder="🔍 جستجوی آهنگ بر اساس نام یا فرمت..." 
             style="width:100%; background:var(--bg2); border:1px solid var(--stroke); border-radius:10px; 
                    color:var(--text); padding:10px 14px; font-size:12px; font-family:var(--fa); transition:.2s; direction:rtl;"
             onfocus="this.style.borderColor='rgba(244,114,182,.5)';" onblur="this.style.borderColor='var(--stroke)';" />
    </div>

    <div style="display:flex;flex-direction:column;gap:8px;" id="playlist">
      <?php foreach ($music as $i => $s):
        $name = basename($s, '.'.pathinfo($s,PATHINFO_EXTENSION));
      ?>
      <div class="fade-in-item track-item" id="track-<?= $i ?>" data-name="<?= htmlspecialchars(strtolower($name)) ?>" data-ext="<?= htmlspecialchars(strtolower(pathinfo($s,PATHINFO_EXTENSION))) ?>"
           onclick="playTrack(<?= $i ?>, '<?= htmlspecialchars(addslashes($s)) ?>', '<?= htmlspecialchars(addslashes($name)) ?>')"
           style="display:flex;align-items:center;gap:12px;padding:12px 14px;cursor:pointer;
                  background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;transition:.2s;">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(244,114,182,.1);
                    border:1px solid rgba(244,114,182,.2);display:flex;align-items:center;justify-content:center;font-size:14px;">
          <span id="ti-<?= $i ?>">🎵</span>
        </div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($name) ?></div>
          <div style="font-size:10px;color:var(--muted);font-family:var(--mono);">
            <?= strtoupper(pathinfo($s,PATHINFO_EXTENSION)) ?> · Cipher Music
          </div>
        </div>
        <span style="font-family:var(--mono);font-size:10px;color:rgba(244,114,182,.6);">▶</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  const audio = document.getElementById('mainAudio');
  const tracks = <?= json_encode(array_values($music)) ?>;
  const names  = <?= json_encode(array_map(fn($s)=>basename($s, '.'.pathinfo($s,PATHINFO_EXTENSION)), $music)) ?>;
  let cur = -1;

  function playTrack(i, src, name) {
    if (cur >= 0) { document.getElementById('track-'+cur).style.background='var(--bg2)'; document.getElementById('track-'+cur).style.borderColor='var(--stroke)'; document.getElementById('ti-'+cur).textContent='🎵'; }
    cur = i;
    audio.src = src;
    audio.play();
    document.getElementById('nowTitle').textContent = name;
    document.getElementById('playBtn').textContent = '⏸ توقف';
    document.getElementById('track-'+i).style.background='rgba(244,114,182,.1)';
    document.getElementById('track-'+i).style.borderColor='rgba(244,114,182,.35)';
    document.getElementById('ti-'+i).textContent = '▶';
    document.getElementById('albumArt').style.animationPlayState = 'running';
  }
  function togglePlay() {
    if (cur < 0) { playTrack(0, tracks[0], names[0]); return; }
    if (audio.paused) { audio.play(); document.getElementById('playBtn').textContent='⏸ توقف'; document.getElementById('albumArt').style.animationPlayState='running'; }
    else { audio.pause(); document.getElementById('playBtn').textContent='▶ پخش'; document.getElementById('albumArt').style.animationPlayState='paused'; }
  }
  function nextTrack() { const n=(cur+1)%tracks.length; playTrack(n,tracks[n],names[n]); }
  function prevTrack() { const n=(cur-1+tracks.length)%tracks.length; playTrack(n,tracks[n],names[n]); }
  audio.addEventListener('ended', nextTrack);

  // منطق سرچ زنده بدون اختلال در آرایه‌ها
  document.getElementById('musicSearch').addEventListener('input', function(e) {
      const query = e.target.value.toLowerCase().trim();
      const items = document.querySelectorAll('.track-item');
      
      items.forEach(item => {
          const name = item.getAttribute('data-name');
          const ext = item.getAttribute('data-ext');
          
          if (name.includes(query) || ext.includes(query)) {
              item.style.display = 'flex';
          } else {
              item.style.display = 'none';
          }
      });
  });
  </script>
  <?php endif; ?>
</div>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
<?php cipher_foot(); ?>