<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Screenshot','#2dd4bf');
cipher_navbar('Cipher Screenshot','📸','../','SCREENSHOT');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📸 Cipher Screenshot</div>
  <div class="c-sub">ثبت صفحه، ویرایش و مدیریت تصاویر</div>
</div>

<!-- Capture Controls -->
<div class="c-panel" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// ابزارهای ثبت</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-bottom:14px;">
    <button onclick="captureScreen('full')" class="c-btn" style="flex:1;">📺 تمام صفحه</button>
    <button onclick="captureScreen('window')" class="c-btn" style="flex:1;">🪟 پنجره فعال</button>
    <button onclick="captureScreen('region')" class="c-btn" style="flex:1;">🎯 منطقه‌ای</button>
    <button onclick="captureScreen('delayed')" class="c-btn" style="flex:1;">⏰ تاخیر 3 ثانیه</button>
  </div>
  
  <div style="display:flex;gap:8px;align-items:center;">
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
      <input type="checkbox" checked>
      <span style="font-size:12px;">صدای انجام</span>
    </label>
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
      <input type="checkbox" checked>
      <span style="font-size:12px;">کپی خودکار</span>
    </label>
    <select class="c-input" style="flex:1;font-size:12px;padding:6px;">
      <option>PNG (بهترین کیفیت)</option>
      <option>JPG (کمتر جا)</option>
      <option>WebP (سریع)</option>
    </select>
  </div>
</div>

<!-- Screenshots Gallery -->
<div class="c-panel" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <div class="c-label">// نمایه‌ی اسکرین‌شات‌ها</div>
    <div style="font-size:11px;color:var(--muted);">12 فایل • 45.2 MB</div>
  </div>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:14px;max-height:400px;overflow-y:auto;">
    <div class="screenshot-card" onclick="viewScreenshot()" style="cursor:pointer;position:relative;border-radius:8px;overflow:hidden;background:var(--bg2);border:1px solid var(--stroke);aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
      <div style="width:100%;height:100%;background:linear-gradient(135deg,#2dd4bf,#0af);opacity:.2;"></div>
      <div style="position:absolute;text-align:center;">
        <div style="font-size:32px;margin-bottom:4px;">📸</div>
        <div style="font-size:10px;color:var(--muted);">1402/12/15</div>
        <div style="font-size:9px;color:var(--muted);">۲۱:۴۵:۲۳</div>
      </div>
    </div>
    
    <div class="screenshot-card" onclick="viewScreenshot()" style="cursor:pointer;position:relative;border-radius:8px;overflow:hidden;background:var(--bg2);border:1px solid var(--stroke);aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
      <div style="width:100%;height:100%;background:linear-gradient(135deg,#38bdf8,#0af);opacity:.2;"></div>
      <div style="position:absolute;text-align:center;">
        <div style="font-size:32px;margin-bottom:4px;">📸</div>
        <div style="font-size:10px;color:var(--muted);">1402/12/14</div>
        <div style="font-size:9px;color:var(--muted);">۱۹:۳۰:۱۲</div>
      </div>
    </div>
    
    <div class="screenshot-card" onclick="viewScreenshot()" style="cursor:pointer;position:relative;border-radius:8px;overflow:hidden;background:var(--bg2);border:1px solid var(--stroke);aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
      <div style="width:100%;height:100%;background:linear-gradient(135deg,#a78bfa,#0af);opacity:.2;"></div>
      <div style="position:absolute;text-align:center;">
        <div style="font-size:32px;margin-bottom:4px;">📸</div>
        <div style="font-size:10px;color:var(--muted);">1402/12/13</div>
        <div style="font-size:9px;color:var(--muted);">۱۶:۲۲:۴۵</div>
      </div>
    </div>
    
    <div class="screenshot-card" onclick="viewScreenshot()" style="cursor:pointer;position:relative;border-radius:8px;overflow:hidden;background:var(--bg2);border:1px solid var(--stroke);aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
      <div style="width:100%;height:100%;background:linear-gradient(135deg,#f472b6,#0af);opacity:.2;"></div>
      <div style="position:absolute;text-align:center;">
        <div style="font-size:32px;margin-bottom:4px;">📸</div>
        <div style="font-size:10px;color:var(--muted);">1402/12/12</div>
        <div style="font-size:9px;color:var(--muted);">۱۴:۰۸:۳۶</div>
      </div>
    </div>
  </div>
  
  <div style="text-align:right;">
    <button onclick="deleteScreenshots()" class="c-btn-ghost" style="font-size:11px;">🗑️ حذف انتخابی</button>
  </div>
</div>

<!-- Image Editor -->
<div class="c-panel" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:12px;">// ابزارهای ویرایش</div>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;">
    <button onclick="editTool('crop')" class="c-btn-ghost">✂️ برش</button>
    <button onclick="editTool('rotate')" class="c-btn-ghost">🔄 چرخش</button>
    <button onclick="editTool('blur')" class="c-btn-ghost">🌫️ تار کردن</button>
    <button onclick="editTool('arrow')" class="c-btn-ghost">➜ فلش</button>
    <button onclick="editTool('text')" class="c-btn-ghost">📝 متن</button>
    <button onclick="editTool('draw')" class="c-btn-ghost">✏️ نقاشی</button>
    <button onclick="editTool('pixelate')" class="c-btn-ghost">⬜ مسایک</button>
    <button onclick="editTool('highlight')" class="c-btn-ghost">🟨 هایلایت</button>
  </div>
</div>

<!-- Quick Actions -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div class="c-label" style="margin-bottom:10px;">// اعدادیات سریع</div>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <button onclick="quickAction('clipboard')" class="c-btn-ghost" style="justify-content:flex-start;">📋 کپی به کلیپ‌بورد</button>
      <button onclick="quickAction('download')" class="c-btn-ghost" style="justify-content:flex-start;">⬇️ دانلود</button>
      <button onclick="quickAction('share')" class="c-btn-ghost" style="justify-content:flex-start;">🔗 اشتراک لینک</button>
      <button onclick="quickAction('cloud')" class="c-btn-ghost" style="justify-content:flex-start;">☁️ ذخیره در ابر</button>
    </div>
  </div>
  
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div class="c-label" style="margin-bottom:10px;">// آمار</div>
    <div style="display:grid;gap:8px;">
      <div style="padding:8px;background:var(--bg2);border-radius:6px;border-left:3px solid #2dd4bf;">
        <div style="font-size:10px;color:var(--muted);">ثبت امروز</div>
        <div style="font-weight:600;">12</div>
      </div>
      <div style="padding:8px;background:var(--bg2);border-radius:6px;border-left:3px solid #2dd4bf;">
        <div style="font-size:10px;color:var(--muted);">کل ذخیره شده</div>
        <div style="font-weight:600;">245</div>
      </div>
    </div>
  </div>
</div>
</div>

<script>
function captureScreen(type) {
  const messages = {
    full: '📺 تمام صفحه ثبت شد',
    window: '🪟 پنجره ثبت شد',
    region: '🎯 منطقه را انتخاب کنید',
    delayed: '⏰ ۳ ثانیه تا ثبت...'
  };
  cToast(messages[type] || '✓ اسکرین‌شات ثبت شد');
}

function viewScreenshot() {
  alert('📸 نمایش تصویر کامل\n\nنام: screenshot_2024_12_15.png\nاندازه: 2560x1440px\n\nتاریخ: 1402/12/15\nوقت: 21:45:23');
}

function editTool(tool) {
  cToast(`🎨 ابزار: ${tool}`);
}

function quickAction(action) {
  const messages = {
    clipboard: '✓ کپی شد',
    download: '⬇️ دانلود آغاز شد',
    share: '🔗 لینک کپی شد',
    cloud: '☁️ ذخیره شد'
  };
  cToast(messages[action] || '✓ تکمیل شد');
}

function deleteScreenshots() {
  if(confirm('اسکرین‌شات‌های انتخابی حذف شود؟')) {
    cToast('✓ حذف شد');
  }
}
</script>
<?php cipher_foot();?>
