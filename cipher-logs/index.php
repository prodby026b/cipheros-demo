<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Logs','#f59e0b');
cipher_navbar('Cipher Logs','📋','../','LOGS');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📋 Cipher Logs</div>
  <div class="c-sub">نمایش، جستجو و تحلیل لاگ‌های سیستم</div>
</div>

<!-- Log Filters -->
<div class="c-panel" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// فیلترها</div>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:12px;">
    <div>
      <label style="display:block;font-size:11px;color:var(--muted);margin-bottom:6px;">نوع لاگ</label>
      <select class="c-input" id="logType" onchange="filterLogs()" style="font-size:13px;">
        <option value="">همه</option>
        <option value="info">📘 اطلاعات</option>
        <option value="warning">⚠️ هشدار</option>
        <option value="error">❌ خطا</option>
        <option value="debug">🐛 Debug</option>
        <option value="success">✓ موفق</option>
      </select>
    </div>
    
    <div>
      <label style="display:block;font-size:11px;color:var(--muted);margin-bottom:6px;">بازه زمانی</label>
      <select class="c-input" onchange="filterLogs()" style="font-size:13px;">
        <option>آخرین ساعت</option>
        <option>آخرین 24 ساعت</option>
        <option>آخرین 7 روز</option>
        <option>این ماه</option>
        <option>همه</option>
      </select>
    </div>
    
    <div>
      <label style="display:block;font-size:11px;color:var(--muted);margin-bottom:6px;">سرویس</label>
      <select class="c-input" onchange="filterLogs()" style="font-size:13px;">
        <option>همه سرویس‌ها</option>
        <option>Cipher Chat</option>
        <option>Cipher Cloud</option>
        <option>Cipher API</option>
        <option>Cipher Code</option>
        <option>سیستم</option>
      </select>
    </div>
  </div>
  
  <div style="display:flex;gap:8px;">
    <input type="text" id="searchLogs" class="c-input" placeholder="جستجو در لاگ‌ها..." onkeyup="filterLogs()" style="flex:1;">
    <button onclick="clearLogs()" class="c-btn-ghost">🗑️</button>
    <button onclick="exportLogs()" class="c-btn-ghost">⬇️</button>
  </div>
</div>

<!-- Log Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:16px;">
  <div class="c-panel">
    <div style="text-align:center;">
      <div style="font-size:24px;color:#f59e0b;font-weight:700;">1,234</div>
      <div style="font-size:11px;color:var(--muted);">کل لاگ‌ها</div>
    </div>
  </div>
  <div class="c-panel">
    <div style="text-align:center;">
      <div style="font-size:24px;color:#00ff99;font-weight:700;">856</div>
      <div style="font-size:11px;color:var(--muted);">✓ موفق</div>
    </div>
  </div>
  <div class="c-panel">
    <div style="text-align:center;">
      <div style="font-size:24px;color:#f59e0b;font-weight:700;">45</div>
      <div style="font-size:11px;color:var(--muted);">⚠️ هشدار</div>
    </div>
  </div>
  <div class="c-panel">
    <div style="text-align:center;">
      <div style="font-size:24px;color:#ef4444;font-weight:700;">18</div>
      <div style="font-size:11px;color:var(--muted);">❌ خطا</div>
    </div>
  </div>
</div>

<!-- Logs List -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:12px;">// لاگ‌های سیستم</div>
  <div id="logsList" style="display:flex;flex-direction:column;gap:8px;max-height:600px;overflow-y:auto;">
    <!-- Logs will be populated here -->
  </div>
</div>
</div>

<script>
const logs = [
  {time:'21:45:23', type:'success', service:'Cipher Chat', msg:'کاربر جدید متصل شد: user@cipher.local'},
  {time:'21:44:56', type:'info', service:'Cipher Cloud', msg:'۵ فایل بارگذاری شد (2.4MB)'},
  {time:'21:43:12', type:'success', service:'Cipher Code', msg:'کوئری SQL اجرا شد (45ms)'},
  {time:'21:42:01', type:'warning', service:'سیستم', msg:'مصرف CPU: 78% (بالا)'},
  {time:'21:40:34', type:'error', service:'Cipher API', msg:'درخواست API ناموفق: timeout after 30s'},
  {time:'21:39:45', type:'info', service:'Cipher Chat', msg:'دیتابیس بک‌آپ شد'},
  {time:'21:38:12', type:'success', service:'Cipher Cloud', msg:'حق‌دسترسی فایل تغییر یافت'},
  {time:'21:37:00', type:'debug', service:'سیستم', msg:'Cleanup cache: 512MB آزاد شد'},
  {time:'21:35:45', type:'warning', service:'Cipher Code', msg:'درخواست طولانی مدت (5 ثانیه)'},
  {time:'21:34:23', type:'success', service:'Cipher Chat', msg:'پیغام به 12 دستگاه ارسال شد'},
  {time:'21:33:10', type:'info', service:'Cipher API', msg:'API endpoint: /v1/users - ۳۴۵ درخواست'},
  {time:'21:32:01', type:'error', service:'Cipher Cloud', msg:'خطای اتصال فضای ابری'},
];

function renderLogs() {
  const list = document.getElementById('logsList');
  list.innerHTML = logs.map(log => {
    const icons = {success:'✓', error:'❌', warning:'⚠️', info:'📘', debug:'🐛'};
    const colors = {success:'#00ff99', error:'#ef4444', warning:'#f59e0b', info:'#38bdf8', debug:'#a78bfa'};
    return `
      <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid ${colors[log.type]};display:grid;grid-template-columns:auto 1fr auto;gap:10px;align-items:center;font-size:12px;">
        <div style="text-align:center;color:${colors[log.type]};font-size:14px;">${icons[log.type]}</div>
        <div>
          <div style="color:var(--text);margin-bottom:3px;">${log.msg}</div>
          <div style="color:var(--muted);font-size:11px;">${log.service} • ${log.time}</div>
        </div>
        <button onclick="viewLogDetails()" class="c-btn-ghost" style="padding:4px 8px;font-size:10px;">بیشتر</button>
      </div>
    `;
  }).join('');
}

function filterLogs() {
  const type = document.getElementById('logType').value;
  const search = document.getElementById('searchLogs').value.toLowerCase();
  
  const list = document.getElementById('logsList');
  list.querySelectorAll('div').forEach((div, idx) => {
    const log = logs[idx];
    if(!log) return;
    
    const matchType = !type || log.type === type;
    const matchSearch = !search || log.msg.includes(search) || log.service.includes(search);
    
    div.style.display = (matchType && matchSearch) ? 'grid' : 'none';
  });
}

function viewLogDetails() {
  alert('جزئیات لاگ:\n\nزمان: 21:45:23\nنوع: Success\nسرویس: Cipher Chat\nپیام: کاربر جدید متصل شد\nآدرس IP: 192.168.1.105\nمدت پردازش: 234ms');
}

function clearLogs() {
  if(confirm('تمام لاگ‌ها حذف شود؟')) {
    logs.length = 0;
    renderLogs();
    cToast('✓ لاگ‌ها حذف شدند');
  }
}

function exportLogs() {
  const csv = logs.map(l => `${l.time},${l.type},${l.service},"${l.msg}"`).join('\n');
  const blob = new Blob([csv], {type:'text/csv'});
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'cipher-logs.csv';
  a.click();
  cToast('✓ لاگ‌ها دانلود شدند');
}

renderLogs();
</script>
<?php cipher_foot();?>
