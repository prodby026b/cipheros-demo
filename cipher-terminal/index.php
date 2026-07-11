<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Terminal','#00eaff');
cipher_navbar('Cipher Terminal','⌨️','../','TERMINAL');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">⌨️ Cipher Terminal</div>
  <div class="c-sub">ترمینال وب emulator برای دستورات سیستم</div>
</div>

<div class="c-panel" style="padding:0;overflow:hidden;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div style="background:var(--bg1);border-bottom:1px solid var(--stroke);padding:12px 16px;display:flex;justify-content:space-between;align-items:center;">
    <div style="font-family:var(--mono);font-size:13px;">
      <span style="color:var(--muted);">prodby026b@cipher</span><span style="color:var(--cyan);">:~$</span>
    </div>
    <button onclick="clearTerminal()" class="c-btn-ghost" style="padding:6px 10px;font-size:11px;">🗑️ پاک‌کنی</button>
  </div>
  
  <div id="terminal" style="background:var(--bg0);padding:16px;font-family:var(--mono);font-size:13px;color:var(--success);min-height:400px;max-height:500px;overflow-y:auto;line-height:1.6;white-space:pre-wrap;word-break:break-all;"></div>
  
  <div style="background:var(--bg1);border-top:1px solid var(--stroke);padding:12px 16px;display:flex;gap:8px;">
    <span style="color:var(--muted);">$</span>
    <input type="text" id="cmdInput" class="c-input" placeholder="دستور را وارد کنید..." style="flex:1;background:var(--bg0);border:1px solid var(--stroke);padding:8px 12px;font-family:var(--mono);font-size:13px;">
  </div>
</div>

<!-- Command Help -->
<div class="c-panel" style="margin-top:22px;">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// دستورات موجود</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #00ff99;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">help</div>
      <div style="font-size:11px;color:var(--muted);">لیست تمام دستورات</div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #38bdf8;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">clear</div>
      <div style="font-size:11px;color:var(--muted);">پاک‌کردن ترمینال</div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #f59e0b;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">time</div>
      <div style="font-size:11px;color:var(--muted);">نمایش ساعت و تاریخ</div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #a78bfa;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">whoami</div>
      <div style="font-size:11px;color:var(--muted);">نام کاربر فعلی</div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #00eaff;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">os</div>
      <div style="font-size:11px;color:var(--muted);">اطلاعات سیستم عامل</div>
    </div>
    <div style="padding:10px;background:var(--bg2);border-radius:8px;border-left:3px solid #f472b6;">
      <div style="font-weight:600;font-size:12px;margin-bottom:4px;">echo [text]</div>
      <div style="font-size:11px;color:var(--muted);">تکرار متن</div>
    </div>
  </div>
</div>
</div>

<script>
const terminal = document.getElementById('terminal');
const cmdInput = document.getElementById('cmdInput');
let history = [];

const commands = {
  help: () => 'دستورات موجود: help, clear, time, whoami, os, echo, uptime, date, pwd, services, uname',
  clear: () => { terminal.textContent = ''; return null; },
  time: () => new Date().toLocaleString('fa-IR'),
  whoami: () => 'prodby026b',
  os: () => 'Cipher OS v1.2.0 (Linux-based)',
  uptime: () => '45 days, 12:34:56',
  date: () => new Date().toLocaleDateString('fa-IR'),
  pwd: () => '/home/prodby026b/cipher-os',
  services: () => 'Cipher Chat (✓), Cipher Cloud (✓), Cipher Code (✓), Cipher API (✓)...',
  uname: () => 'Cipher OS 1.2.0 x86_64 GNU/Linux'
};

function addToTerminal(text, type = 'output') {
  const line = document.createElement('div');
  line.style.color = type === 'error' ? '#ef4444' : type === 'input' ? '#00eaff' : '#00ff99';
  line.textContent = text;
  terminal.appendChild(line);
  terminal.scrollTop = terminal.scrollHeight;
}

function executeCommand(cmd) {
  const parts = cmd.trim().split(' ');
  const command = parts[0];
  const args = parts.slice(1).join(' ');
  
  addToTerminal(`$ ${cmd}`, 'input');
  
  if(commands[command]) {
    if(command === 'echo') {
      addToTerminal(args || '');
    } else {
      const result = commands[command]();
      if(result !== null) addToTerminal(result);
    }
  } else if(cmd.trim() === '') {
    // خط خالی
  } else {
    addToTerminal(`❌ دستور یافت نشد: ${command}`, 'error');
  }
  
  addToTerminal('');
}

function clearTerminal() {
  terminal.textContent = '';
}

cmdInput.addEventListener('keydown', e => {
  if(e.key === 'Enter') {
    const cmd = cmdInput.value;
    executeCommand(cmd);
    history.push(cmd);
    cmdInput.value = '';
  }
});

addToTerminal('Cipher Terminal v1.0.0');
addToTerminal('برای دیدن دستورات، "help" را وارد کنید');
addToTerminal('');
</script>
<?php cipher_foot();?>
