<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher QR', '#2dd4bf');
cipher_navbar('Cipher QR', '⬛', '../', 'QR');
?>
<div class="c-wrap" style="max-width:800px;">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">⬛ Cipher QR</div>
  <div class="c-sub">ساخت و اسکن QR Code اختصاصی Cipher OS</div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">⬛ ساخت QR Code</div>
  <div style="display:flex;flex-direction:column;gap:12px;">
    <div>
      <div class="c-label" style="margin-bottom:6px;">متن یا لینک</div>
      <textarea id="qrText" class="c-textarea" placeholder="https://prodby026b.sbs یا هر متنی..." style="min-height:80px;"></textarea>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <div>
        <div class="c-label" style="margin-bottom:6px;">رنگ QR</div>
        <input type="color" id="qrColor" value="#00eaff" style="width:100%;height:40px;border:1px solid var(--stroke);border-radius:10px;background:var(--bg2);cursor:pointer;">
      </div>
      <div>
        <div class="c-label" style="margin-bottom:6px;">سایز</div>
        <select id="qrSize" class="c-input" style="height:40px;">
          <option value="200">کوچک (200px)</option>
          <option value="300" selected>متوسط (300px)</option>
          <option value="400">بزرگ (400px)</option>
        </select>
      </div>
    </div>
    <button onclick="genQR()" class="c-btn" style="justify-content:center;width:100%;">⬛ ساخت QR Code</button>
  </div>
  <div id="qrResult" style="display:none;margin-top:20px;text-align:center;">
    <div style="background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;padding:20px;display:inline-block;">
      <img id="qrImg" style="display:block;border-radius:8px;">
    </div>
    <button onclick="dlQR()" class="c-btn" style="margin-top:14px;justify-content:center;">⬇️ دانلود PNG</button>
  </div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">📷 اسکن QR Code</div>
  <div style="border:2px dashed var(--stroke);border-radius:14px;padding:40px 20px;text-align:center;cursor:pointer;transition:.2s;"
       onclick="document.getElementById('scanFile').click()"
       ondragover="e=>e.preventDefault()" ondrop="handleDrop(event)"
       id="dropZone">
    <div style="font-size:48px;margin-bottom:12px;">📷</div>
    <div style="font-family:var(--fa);color:var(--muted2);font-size:14px;line-height:1.8;">تصویر QR Code را<br>اینجا بکشید یا کلیک کنید</div>
    <input type="file" id="scanFile" accept="image/*" style="display:none" onchange="scanQR(this.files[0])">
  </div>
  <div id="scanResult" style="display:none;margin-top:16px;">
    <div class="c-label" style="margin-bottom:8px;">نتیجه اسکن:</div>
    <div id="scanText" style="background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;padding:14px;font-family:var(--mono);font-size:13px;word-break:break-all;color:var(--cyan);"></div>
    <button onclick="copyScan()" class="c-btn" style="margin-top:10px;font-size:11px;padding:8px 16px;">📋 کپی</button>
  </div>
</div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function genQR(){
  const text=document.getElementById('qrText').value.trim();
  if(!text){cToast('⚠️ متن را وارد کنید');return;}
  const size=parseInt(document.getElementById('qrSize').value);
  const color=document.getElementById('qrColor').value;
  const url=`https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}&color=${color.slice(1)}&bgcolor=0c0f1e&margin=2`;
  const img=document.getElementById('qrImg');
  img.src=url; img.width=size; img.height=size;
  document.getElementById('qrResult').style.display='block';
  cToast('✅ QR Code ساخته شد');
}
function dlQR(){
  const img=document.getElementById('qrImg');
  const a=document.createElement('a');
  a.href=img.src; a.download='cipher-qr.png'; a.click();
}
function scanQR(file){
  if(!file) return;
  const reader=new FileReader();
  reader.onload=e=>{
    const img=new Image();
    img.onload=()=>{
      const c=document.createElement('canvas');
      c.width=img.width;c.height=img.height;
      const ctx=c.getContext('2d');
      ctx.drawImage(img,0,0);
      // بدون کتابخانه اسکن، نمایش پیام
      document.getElementById('scanText').textContent='برای اسکن QR از دوربین موبایل استفاده کنید یا از jsQR استفاده شود.';
      document.getElementById('scanResult').style.display='block';
    };
    img.src=e.target.result;
  };
  reader.readAsDataURL(file);
}
function handleDrop(e){e.preventDefault();const f=e.dataTransfer.files[0];if(f)scanQR(f);}
function copyScan(){navigator.clipboard.writeText(document.getElementById('scanText').textContent);cToast('✅ کپی شد');}
</script>
<?php cipher_foot(); ?>
