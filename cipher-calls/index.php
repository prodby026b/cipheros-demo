<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Calls', '#2dd4bf');
cipher_navbar('Cipher Calls', '📞', '../', 'CALLS');
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📞 Cipher Calls</div>
    <div class="c-sub">تماس ویدیویی و صوتی داخلی — Secure Real-time Communication</div>
    <div style="margin-top:12px;"><span class="c-tag" style="background:rgba(45,212,191,.07);border-color:rgba(45,212,191,.25);color:#2dd4bf;">● System Ready</span></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start;">

    <!-- VIDEO PANEL -->
    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
      <div class="c-sec" style="margin-bottom:16px;">
        <span class="c-sec-title">📷 دوربین محلی</span>
        <span id="camStatus" class="c-tag" style="background:rgba(239,68,68,.07);border-color:rgba(239,68,68,.2);color:#ef4444;">● Disconnected</span>
      </div>
      <div style="position:relative;border-radius:14px;overflow:hidden;background:#000;aspect-ratio:16/9;margin-bottom:16px;">
        <video id="localVid" autoplay muted playsinline
               style="width:100%;height:100%;object-fit:cover;border-radius:14px;display:none;"></video>
        <div id="noCamera" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:var(--muted);">
          <span style="font-size:48px;opacity:.3;">📷</span>
          <span style="font-family:var(--mono);font-size:11px;letter-spacing:.1em;">CAMERA OFFLINE</span>
        </div>
        <!-- HUD overlay -->
        <div id="hudOverlay" style="display:none;position:absolute;inset:0;pointer-events:none;">
          <div style="position:absolute;top:12px;right:12px;display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.6);padding:6px 10px;border-radius:8px;">
            <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;animation:pulse 1s infinite;display:inline-block;"></span>
            <span style="font-family:var(--mono);font-size:10px;color:#fff;" id="recTimer">00:00</span>
          </div>
          <div style="position:absolute;bottom:12px;right:12px;font-family:var(--mono);font-size:9px;color:rgba(255,255,255,.5);letter-spacing:.1em;">CIPHER OS · PRODBY026B</div>
          <div style="position:absolute;top:10px;left:10px;width:16px;height:16px;border-top:2px solid rgba(45,212,191,.5);border-right:2px solid rgba(45,212,191,.5);"></div>
          <div style="position:absolute;bottom:10px;right:10px;width:16px;height:16px;border-bottom:2px solid rgba(45,212,191,.5);border-left:2px solid rgba(45,212,191,.5);"></div>
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <button onclick="startCamera()" class="c-btn" id="startBtn">▶ فعال‌سازی دوربین</button>
        <button onclick="toggleMute()" class="c-btn-ghost" id="muteBtn">🎤 صدا فعال</button>
        <button onclick="stopCamera()" class="c-btn-ghost" id="stopBtn" style="display:none;">⏹ خاموش</button>
        <button onclick="toggleFullscreen()" class="c-btn-ghost">⛶ تمام‌صفحه</button>
      </div>
    </div>

    <!-- SIDE -->
    <div style="display:flex;flex-direction:column;gap:16px;">
      <div class="c-panel">
        <div class="c-label" style="margin-bottom:14px;">SYSTEM STATUS</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;">
            <span style="font-size:12px;font-family:var(--fa);">دوربین</span>
            <span id="camBadge" style="font-size:10px;font-family:var(--mono);color:var(--danger);">OFFLINE</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;">
            <span style="font-size:12px;font-family:var(--fa);">میکروفون</span>
            <span id="micBadge" style="font-size:10px;font-family:var(--mono);color:var(--danger);">OFFLINE</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;">
            <span style="font-size:12px;font-family:var(--fa);">WebRTC</span>
            <span style="font-size:10px;font-family:var(--mono);color:var(--success);">SUPPORTED</span>
          </div>
        </div>
      </div>
      <div class="c-panel">
        <div class="c-label" style="margin-bottom:10px;">INFO</div>
        <div style="font-family:var(--fa);font-size:12px;color:var(--muted2);line-height:1.9;">
          این ماژول از WebRTC استفاده می‌کند و نیازی به نصب نرم‌افزار ندارد. تمام ارتباطات در شبکه داخلی رمزنگاری شده‌اند.
        </div>
        <div style="margin-top:12px;font-family:var(--mono);font-size:10px;color:var(--muted);letter-spacing:.1em;">CIPHER OS · PRODBY026B</div>
      </div>
    </div>
  </div>
</div>

<script>
let stream = null, muted = false, sec = 0, timerInt = null;

async function startCamera() {
  try {
    stream = await navigator.mediaDevices.getUserMedia({video:true, audio:true});
    document.getElementById('localVid').srcObject = stream;
    document.getElementById('localVid').style.display = 'block';
    document.getElementById('noCamera').style.display = 'none';
    document.getElementById('hudOverlay').style.display = 'block';
    document.getElementById('camStatus').textContent = '● Live';
    document.getElementById('camStatus').style.color = 'var(--success)';
    document.getElementById('camStatus').style.background = 'rgba(0,255,153,.07)';
    document.getElementById('camStatus').style.borderColor = 'rgba(0,255,153,.25)';
    document.getElementById('camBadge').textContent = 'ONLINE';
    document.getElementById('camBadge').style.color = 'var(--success)';
    document.getElementById('micBadge').textContent = 'ONLINE';
    document.getElementById('micBadge').style.color = 'var(--success)';
    document.getElementById('startBtn').style.display = 'none';
    document.getElementById('stopBtn').style.display = 'inline-flex';
    sec = 0; timerInt = setInterval(() => {
      sec++;
      const m = String(Math.floor(sec/60)).padStart(2,'0');
      const s = String(sec%60).padStart(2,'0');
      document.getElementById('recTimer').textContent = m+':'+s;
    }, 1000);
  } catch(e) {
    cToast('⚠ دسترسی به دوربین رد شد');
  }
}

function stopCamera() {
  if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
  document.getElementById('localVid').style.display = 'none';
  document.getElementById('noCamera').style.display = 'flex';
  document.getElementById('hudOverlay').style.display = 'none';
  document.getElementById('camStatus').textContent = '● Disconnected';
  document.getElementById('camStatus').style.color = '#ef4444';
  document.getElementById('camBadge').textContent = 'OFFLINE';
  document.getElementById('camBadge').style.color = 'var(--danger)';
  document.getElementById('micBadge').textContent = 'OFFLINE';
  document.getElementById('micBadge').style.color = 'var(--danger)';
  document.getElementById('startBtn').style.display = 'inline-flex';
  document.getElementById('stopBtn').style.display = 'none';
  clearInterval(timerInt);
}

function toggleMute() {
  if (!stream) return;
  muted = !muted;
  stream.getAudioTracks().forEach(t => t.enabled = !muted);
  document.getElementById('muteBtn').textContent = muted ? '🔇 بی‌صدا' : '🎤 صدا فعال';
}

function toggleFullscreen() {
  const v = document.getElementById('localVid');
  if (v.requestFullscreen) v.requestFullscreen();
}
</script>
<?php cipher_foot(); ?>
