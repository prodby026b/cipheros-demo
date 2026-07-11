<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Email','#f472b6');
cipher_navbar('Cipher Email','📧','../','EMAIL');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📧 Cipher Email</div>
  <div class="c-sub">کلاینت ایمیل محلی و مدیریت ایمیل‌های فعال</div>
</div>

<div style="display:grid;grid-template-columns:240px 1fr;gap:16px;margin-bottom:16px;height:500px;">
<!-- Sidebar -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// صندوق‌های ایمیل</div>
  <div style="display:flex;flex-direction:column;gap:8px;">
    <div onclick="setMailbox('inbox')" class="c-card" style="cursor:pointer;padding:12px;background:color-mix(in srgb,#f472b6 15%,transparent);">
      📥 صندوق دریافتی <span style="font-size:10px;color:var(--muted);">(12)</span>
    </div>
    <div onclick="setMailbox('sent')" class="c-card" style="cursor:pointer;padding:12px;">
      📤 ارسال شده <span style="font-size:10px;color:var(--muted);">(8)</span>
    </div>
    <div onclick="setMailbox('draft')" class="c-card" style="cursor:pointer;padding:12px;">
      ✍️ پیش‌نویس <span style="font-size:10px;color:var(--muted);">(3)</span>
    </div>
    <div onclick="setMailbox('spam')" class="c-card" style="cursor:pointer;padding:12px;">
      ⚠️ هرزنامه <span style="font-size:10px;color:var(--muted);">(24)</span>
    </div>
    <div onclick="setMailbox('archive')" class="c-card" style="cursor:pointer;padding:12px;">
      📦 بایگانی <span style="font-size:10px;color:var(--muted);">(156)</span>
    </div>
  </div>
  <button onclick="openCompose()" class="c-btn" style="width:100%;margin-top:14px;">✏️ نامه جدید</button>
</div>

<!-- Mail List -->
<div style="display:flex;flex-direction:column;gap:8px;overflow-y:auto;">
  <div id="emailList" style="display:flex;flex-direction:column;gap:8px;"></div>
</div>
</div>

<!-- Compose Modal -->
<div id="composeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(5px);z-index:9999;padding:20px;align-items:center;justify-content:center;">
  <div class="c-panel" style="width:100%;max-width:600px;max-height:80vh;overflow-y:auto;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <div class="c-title">✏️ نامه جدید</div>
      <button onclick="closeCompose()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">✕</button>
    </div>
    <input type="email" id="toEmail" class="c-input" placeholder="دریافت کننده..." style="margin-bottom:10px;">
    <input type="text" id="subject" class="c-input" placeholder="موضوع..." style="margin-bottom:10px;">
    <textarea id="body" class="c-textarea" placeholder="متن نامه..." style="margin-bottom:16px;"></textarea>
    <div style="display:flex;gap:8px;">
      <button onclick="sendEmail()" class="c-btn" style="flex:1;">📤 ارسال</button>
      <button onclick="closeCompose()" class="c-btn-ghost" style="flex:1;">انصراف</button>
    </div>
  </div>
</div>

<script>
const emails = [
  {from:'admin@cipher.local',subject:'سیستم سرور آنلاین',preview:'سرور Cipher OS فعال است...',time:'۱۰ دقیقه پیش',status:'read'},
  {from:'dev@cipher.local',subject:'به‌روزرسانی جدید v1.2.0',preview:'نسخه جدید با ویژگی‌های نویی...',time:'۱ ساعت پیش',status:'new'},
  {from:'security@cipher.local',subject:'هشدار امنیتی',preview:'سعی ورود ناموفق شناسایی شد...',time:'۲ ساعت پیش',status:'new'},
];

let currentMailbox = 'inbox';

function setMailbox(box) {
  currentMailbox = box;
  renderEmails();
}

function renderEmails() {
  const list = document.getElementById('emailList');
  list.innerHTML = emails.map((email, i) => `
    <div class="c-panel" style="padding:12px;cursor:pointer;border:1px solid var(--stroke);${email.status==='new' ? 'border-color:#f472b6;' : ''}" onclick="viewEmail(${i})">
      <div style="display:flex;justify-content:space-between;align-items:start;">
        <div>
          <div style="font-weight:600;">${email.from}</div>
          <div style="color:var(--muted);font-size:13px;">${email.subject}</div>
          <div style="color:var(--muted2);font-size:12px;margin-top:6px;">${email.preview.substring(0,50)}...</div>
        </div>
        <div style="text-align:right;font-size:11px;color:var(--muted);">${email.time}</div>
      </div>
    </div>
  `).join('');
}

function viewEmail(idx) {
  const email = emails[idx];
  alert(`از: ${email.from}\nموضوع: ${email.subject}\n\n${email.preview}`);
}

function openCompose() {
  document.getElementById('composeModal').style.display = 'flex';
}

function closeCompose() {
  document.getElementById('composeModal').style.display = 'none';
}

function sendEmail() {
  const to = document.getElementById('toEmail').value;
  const subject = document.getElementById('subject').value;
  const body = document.getElementById('body').value;
  
  if(!to || !subject || !body) {
    alert('لطفاً تمام فیلدها را پر کنید');
    return;
  }
  
  emails.unshift({
    from:'you@cipher.local',
    subject: subject,
    preview: body.substring(0, 50),
    time:'حالا',
    status:'read'
  });
  
  cToast('✓ نامه ارسال شد');
  closeCompose();
  document.getElementById('toEmail').value = '';
  document.getElementById('subject').value = '';
  document.getElementById('body').value = '';
  renderEmails();
}

renderEmails();
</script>
<?php cipher_foot();?>
