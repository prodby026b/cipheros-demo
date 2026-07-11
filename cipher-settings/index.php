<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Settings','#c084fc');
cipher_navbar('Cipher Settings','⚙️','../','SETTINGS');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">⚙️ Cipher Settings</div>
  <div class="c-sub">تنظیمات سیستم، صفحه نمایش و امنیت</div>
</div>

<div style="display:grid;grid-template-columns:200px 1fr;gap:16px;">
<!-- Settings Menu -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// بخش‌ها</div>
  <div style="display:flex;flex-direction:column;gap:6px;">
    <button onclick="showSection('display')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;background:color-mix(in srgb,#c084fc 15%,transparent);border-left:3px solid #c084fc;">🖥️ نمایش</button>
    <button onclick="showSection('system')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">⚙️ سیستم</button>
    <button onclick="showSection('security')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">🔐 امنیت</button>
    <button onclick="showSection('network')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">🌐 شبکه</button>
    <button onclick="showSection('account')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">👤 حساب</button>
    <button onclick="showSection('notifications')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">🔔 اطلاعات</button>
    <button onclick="showSection('backup')" class="setting-btn" style="justify-content:flex-start;padding:10px;border-radius:8px;">💾 بک‌آپ</button>
  </div>
</div>

<!-- Settings Content -->
<div>
<div id="display-section" class="section">
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div class="c-title" style="margin-bottom:16px;">🖥️ تنظیمات نمایش</div>
    
    <div style="display:grid;gap:14px;">
      <div>
        <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;">موضوع رنگی</label>
        <div style="display:flex;gap:8px;">
          <button class="c-btn-ghost" style="flex:1;">🌙 تاریک (فعال)</button>
          <button class="c-btn-ghost" style="flex:1;">☀️ روشن</button>
          <button class="c-btn-ghost" style="flex:1;">🎨 خودکار</button>
        </div>
      </div>
      
      <div>
        <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;">اندازه فونت</label>
        <input type="range" min="10" max="18" value="13" style="width:100%;cursor:pointer;">
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">13px (عادی)</div>
      </div>
      
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" checked>
          <span>اسکن‌لاین Overlay</span>
        </label>
      </div>
      
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" checked>
          <span>انیمیشن‌های Glassmorphism</span>
        </label>
      </div>
    </div>
  </div>
</div>

<div id="system-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div class="c-title" style="margin-bottom:16px;">⚙️ اطلاعات سیستم</div>
    
    <div style="display:grid;gap:12px;">
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">نام سیستم</div>
        <div style="font-weight:600;margin-top:4px;">Cipher OS</div>
      </div>
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">نسخه</div>
        <div style="font-weight:600;margin-top:4px;">1.2.0 Build 2024.12.15</div>
      </div>
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">بالا‌آمدن سیستم</div>
        <div style="font-weight:600;margin-top:4px;">45 روز، 12:34:56</div>
      </div>
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">مصرف RAM</div>
        <div style="height:8px;background:var(--bg3);border-radius:4px;margin:8px 0;overflow:hidden;">
          <div style="height:100%;width:68%;background:#00ff99;"></div>
        </div>
        <div style="font-weight:600;">8.2 / 12 GB (68%)</div>
      </div>
    </div>
    
    <button onclick="restartSystem()" class="c-btn" style="width:100%;margin-top:14px;">🔄 راه‌اندازی مجدد</button>
  </div>
</div>

<div id="security-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div class="c-title" style="margin-bottom:16px;">🔐 تنظیمات امنیتی</div>
    
    <div style="display:grid;gap:12px;">
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:10px;">
          <input type="checkbox" checked>
          <span style="font-weight:600;">احراز هویت دو عاملی</span>
        </label>
      </div>
      
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:10px;">
          <input type="checkbox" checked>
          <span style="font-weight:600;">رمزگذاری داده‌ها</span>
        </label>
      </div>
      
      <div>
        <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;">زمان انتظار خروج خودکار</label>
        <select class="c-input" style="font-size:13px;">
          <option>۵ دقیقه</option>
          <option>۱۰ دقیقه</option>
          <option>۳۰ دقیقه</option>
          <option>۱ ساعت</option>
          <option>هرگز</option>
        </select>
      </div>
      
      <div>
        <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;">پسورد</label>
        <button onclick="changePassword()" class="c-btn-ghost" style="width:100%;">تغییر رمز عبور</button>
      </div>
    </div>
  </div>
</div>

<div id="network-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div class="c-title" style="margin-bottom:16px;">🌐 تنظیمات شبکه</div>
    
    <div style="display:grid;gap:12px;">
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">IP آدرس</div>
        <div style="font-weight:600;margin-top:4px;font-family:var(--mono);">192.168.1.100</div>
      </div>
      <div style="padding:10px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">Gateway</div>
        <div style="font-weight:600;margin-top:4px;font-family:var(--mono);">192.168.1.1</div>
      </div>
      
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" checked>
          <span>DNS Custom</span>
        </label>
      </div>
      
      <div>
        <input type="text" class="c-input" placeholder="DNS Server 1" value="8.8.8.8" style="margin-bottom:8px;">
        <input type="text" class="c-input" placeholder="DNS Server 2" value="8.8.4.4">
      </div>
    </div>
  </div>
</div>

<div id="account-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div class="c-title" style="margin-bottom:16px;">👤 حساب کاربری</div>
    
    <div style="display:grid;gap:12px;margin-bottom:16px;">
      <div style="padding:12px;background:var(--bg2);border-radius:8px;border-left:3px solid #c084fc;">
        <div style="font-size:11px;color:var(--muted);">نام کاربر</div>
        <div style="font-weight:600;margin-top:4px;">prodby026b</div>
      </div>
      <div style="padding:12px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">ایمیل</div>
        <div style="font-weight:600;margin-top:4px;">prod@cipher.local</div>
      </div>
      <div style="padding:12px;background:var(--bg2);border-radius:8px;">
        <div style="font-size:11px;color:var(--muted);">سطح دسترسی</div>
        <div style="font-weight:600;margin-top:4px;">👑 مدیر سیستم</div>
      </div>
    </div>
    
    <button onclick="logout()" class="c-btn" style="width:100%;background:linear-gradient(135deg,#ef4444,#ef4444AA);">🚪 خروج</button>
  </div>
</div>

<div id="notifications-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-br"></div>
    <div class="c-title" style="margin-bottom:16px;">🔔 اطلاعات و هشدارها</div>
    
    <div style="display:grid;gap:10px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;background:var(--bg2);border-radius:8px;">
        <input type="checkbox" checked>
        <span>اطلاع‌رسانی سیستم</span>
      </label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;background:var(--bg2);border-radius:8px;">
        <input type="checkbox" checked>
        <span>هشدار‌های امنیتی</span>
      </label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;background:var(--bg2);border-radius:8px;">
        <input type="checkbox" checked>
        <span>اطلاع‌رسانی به‌روزرسانی</span>
      </label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;background:var(--bg2);border-radius:8px;">
        <input type="checkbox">
        <span>اطلاع‌رسانی تبلیغاتی</span>
      </label>
    </div>
  </div>
</div>

<div id="backup-section" class="section" style="display:none;">
  <div class="c-panel">
    <div class="hud-c hud-tl"></div>
    <div class="c-title" style="margin-bottom:16px;">💾 تنظیمات بک‌آپ</div>
    <p style="color:var(--muted);margin-bottom:16px;">برای تنظیمات دقیق بک‌آپ، به Cipher Backup بروید</p>
    <button onclick="alert('این قابلیت فقط در نسخه Pro فعال است 🔒')" class="c-btn" style="width:100%;opacity:.6;">🔒 Cipher Backup — Pro Only</button>
  </div>
</div>
</div>
</div>

<script>
function showSection(section) {
  document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
  document.getElementById(section + '-section').style.display = 'block';
  
  document.querySelectorAll('.setting-btn').forEach(b => {
    b.style.background = '';
    b.style.borderLeft = 'none';
  });
  event.target.style.background = 'color-mix(in srgb,#c084fc 15%,transparent)';
  event.target.style.borderLeft = '3px solid #c084fc';
}

function changePassword() {
  const pass = prompt('رمز عبور جدید:');
  if(pass) cToast('✓ رمز عبور تغییر یافت');
}

function restartSystem() {
  if(confirm('سیستم راه‌اندازی مجدد شود؟')) {
    cToast('⏳ راه‌اندازی مجدد...');
  }
}

function logout() {
  if(confirm('خروج از سیستم؟')) {
    window.location.href = '../logout.php';
  }
}

function goToBackup() {
  window.location.href = '../cipher-backup/';
}
</script>
<?php cipher_foot();?>
