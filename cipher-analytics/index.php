<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Analytics','#00ff99');
cipher_navbar('Cipher Analytics','📊','../','ANALYTICS');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📊 Cipher Analytics</div>
  <div class="c-sub">آمار و تحلیل سیستم، کاربران و کارکرد</div>
</div>

<!-- KPI Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:22px;">
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div style="display:flex;justify-content:space-between;align-items:start;">
      <div>
        <div class="c-label">// کل بازدیدها</div>
        <div style="font-size:32px;font-weight:700;color:#00ff99;margin:8px 0;">12,847</div>
        <div style="color:var(--success);font-size:12px;">↑ 23% از هفته گذشته</div>
      </div>
      <div style="font-size:40px;opacity:.3;">👁️</div>
    </div>
  </div>
  
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div style="display:flex;justify-content:space-between;align-items:start;">
      <div>
        <div class="c-label">// کاربران فعال</div>
        <div style="font-size:32px;font-weight:700;color:#38bdf8;margin:8px 0;">1,324</div>
        <div style="color:var(--success);font-size:12px;">↑ 12% افزایش</div>
      </div>
      <div style="font-size:40px;opacity:.3;">👥</div>
    </div>
  </div>
  
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div style="display:flex;justify-content:space-between;align-items:start;">
      <div>
        <div class="c-label">// درخواست‌های API</div>
        <div style="font-size:32px;font-weight:700;color:#f59e0b;margin:8px 0;">58,342</div>
        <div style="color:var(--success);font-size:12px;">↑ 45% رشد</div>
      </div>
      <div style="font-size:40px;opacity:.3;">⚡</div>
    </div>
  </div>
  
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div style="display:flex;justify-content:space-between;align-items:start;">
      <div>
        <div class="c-label">// بالای میانگین سیستم</div>
        <div style="font-size:32px;font-weight:700;color:#00eaff;margin:8px 0;">99.8%</div>
        <div style="color:var(--success);font-size:12px;">⭐ فوق‌العاده</div>
      </div>
      <div style="font-size:40px;opacity:.3;">✓</div>
    </div>
  </div>
</div>

<!-- Charts and Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:22px;">
  <div class="c-panel">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div class="c-label" style="margin-bottom:16px;">// ترافیک روزانه</div>
    <div id="chart1" style="height:200px;display:flex;align-items:flex-end;gap:6px;justify-content:space-around;padding:10px 0;">
      <div style="width:20px;height:80%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:60%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:90%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:75%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:85%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:100%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
      <div style="width:20px;height:70%;background:linear-gradient(180deg,#00ff99,#00ff99AA);border-radius:4px;"></div>
    </div>
    <div style="font-size:11px;color:var(--muted);text-align:center;">دوش. سه. چهار. پنج. جمع. شنبه یکش.</div>
  </div>
  
  <div class="c-panel">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div class="c-label" style="margin-bottom:16px;">// استفاده سرویس‌ها</div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px;">
          <span>Cipher Chat</span><span>34%</span>
        </div>
        <div style="height:8px;background:var(--bg2);border-radius:4px;overflow:hidden;">
          <div style="height:100%;width:34%;background:#00eaff;"></div>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px;">
          <span>Cipher Cloud</span><span>28%</span>
        </div>
        <div style="height:8px;background:var(--bg2);border-radius:4px;overflow:hidden;">
          <div style="height:100%;width:28%;background:#38bdf8;"></div>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px;">
          <span>Cipher Code</span><span:22%</span>
        </div>
        <div style="height:8px;background:var(--bg2);border-radius:4px;overflow:hidden;">
          <div style="height:100%;width:22%;background:#a78bfa;"></div>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px;">
          <span>Cipher Media</span><span>16%</span>
        </div>
        <div style="height:8px;background:var(--bg2);border-radius:4px;overflow:hidden;">
          <div style="height:100%;width:16%;background:#f472b6;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Activity Log -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:16px;">// فعالیت اخیر</div>
  <div style="display:flex;flex-direction:column;gap:10px;">
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-right:3px solid #00ff99;">
      <div style="display:flex;justify-content:space-between;font-size:12px;">
        <span>✓ سرور شروع شد</span>
        <span style="color:var(--muted);">۲۱:۳۰</span>
      </div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-right:3px solid #38bdf8;">
      <div style="display:flex;justify-content:space-between;font-size:12px;">
        <span>📤 ۱۵ فایل بارگذاری شد</span>
        <span style="color:var(--muted);">۲۱:۲۵</span>
      </div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-right:3px solid #a78bfa;">
      <div style="display:flex;justify-content:space-between;font-size:12px;">
        <span>🔧 بک‌آپ تکمیل شد</span>
        <span style="color:var(--muted);">۲۰:۴۵</span>
      </div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-right:3px solid #f59e0b;">
      <div style="display:flex;justify-content:space-between;font-size:12px;">
        <span>⚠️ تحذیر: CPU بالا</span>
        <span style="color:var(--muted);">۲۰:۳۰</span>
      </div>
    </div>
  </div>
</div>
</div>

<script>
document.querySelectorAll('.c-panel').forEach(el => {
  el.style.animation = 'fadeIn .5s ease forwards';
  el.style.opacity = '0';
  el.style.animationDelay = Math.random() * 0.3 + 's';
});
</script>
<?php cipher_foot();?>
