<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher API','#38bdf8');
cipher_navbar('Cipher API','🔌','../','API');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🔌 Cipher API</div>
  <div class="c-sub">ابزار تست API و مدیریت Requests/Responses</div>
</div>

<div style="display:grid;grid-template-columns:220px 1fr;gap:16px;">
<!-- Sidebar: Request Templates -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:12px;">// درخواست‌های نمونه</div>
  <div style="display:flex;flex-direction:column;gap:8px;">
    <button onclick="loadRequest('get')" class="c-btn-ghost" style="justify-content:flex-start;">GET JSONPlaceholder</button>
    <button onclick="loadRequest('post')" class="c-btn-ghost" style="justify-content:flex-start;">POST User Data</button>
    <button onclick="loadRequest('github')" class="c-btn-ghost" style="justify-content:flex-start;">GitHub API</button>
    <button onclick="loadRequest('weather')" class="c-btn-ghost" style="justify-content:flex-start;">Weather API</button>
    <button onclick="loadRequest('custom')" class="c-btn-ghost" style="justify-content:flex-start;">درخواست سفارشی</button>
  </div>
</div>

<!-- Main Editor -->
<div>
<div class="c-panel" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:10px;">// تنظیمات درخواست</div>
  
  <div style="display:grid;grid-template-columns:100px 1fr;gap:8px;margin-bottom:12px;align-items:center;">
    <select id="method" class="c-input" style="width:100px;padding:8px;">
      <option value="GET">GET</option>
      <option value="POST">POST</option>
      <option value="PUT">PUT</option>
      <option value="DELETE">DELETE</option>
      <option value="PATCH">PATCH</option>
    </select>
    <input type="text" id="url" class="c-input" placeholder="https://api.example.com/endpoint">
  </div>
  
  <div class="c-label" style="margin-bottom:8px;">// Headers</div>
  <textarea id="headers" class="c-input" style="height:80px;font-family:var(--mono);font-size:12px;margin-bottom:12px;">Content-Type: application/json</textarea>
  
  <div class="c-label" style="margin-bottom:8px;">// Body (JSON)</div>
  <textarea id="body" class="c-input" style="height:100px;font-family:var(--mono);font-size:12px;margin-bottom:12px;">{"key": "value"}</textarea>
  
  <button onclick="sendRequest()" class="c-btn" style="width:100%;">📤 ارسال درخواست</button>
</div>

<!-- Response -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <div class="c-label">// پاسخ</div>
    <button onclick="copyResponse()" class="c-btn-ghost" style="padding:6px 10px;font-size:11px;">📋 کپی</button>
  </div>
  <div id="response" style="background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;padding:14px;min-height:200px;max-height:400px;overflow-y:auto;font-family:var(--mono);font-size:12px;color:var(--success);white-space:pre-wrap;word-break:break-word;"></div>
</div>
</div>
</div>

<script>
const requests = {
  get: {
    method: 'GET',
    url: 'https://jsonplaceholder.typicode.com/posts/1',
    headers: 'Content-Type: application/json',
    body: ''
  },
  post: {
    method: 'POST',
    url: 'https://jsonplaceholder.typicode.com/posts',
    headers: 'Content-Type: application/json',
    body: JSON.stringify({title:'Test Post',body:'سلام',userId:1},null,2)
  },
  github: {
    method: 'GET',
    url: 'https://api.github.com/users/prodby026b',
    headers: 'Content-Type: application/json\nUser-Agent: Cipher-OS',
    body: ''
  },
  weather: {
    method: 'GET',
    url: 'https://api.github.com/repos/torvalds/linux',
    headers: 'Content-Type: application/json',
    body: ''
  },
  custom: {
    method: 'GET',
    url: '',
    headers: 'Content-Type: application/json',
    body: ''
  }
};

function loadRequest(type) {
  const req = requests[type];
  document.getElementById('method').value = req.method;
  document.getElementById('url').value = req.url;
  document.getElementById('headers').value = req.headers;
  document.getElementById('body').value = req.body;
}

async function sendRequest() {
  const method = document.getElementById('method').value;
  const url = document.getElementById('url').value;
  const respDiv = document.getElementById('response');
  
  if(!url) {
    respDiv.textContent = '❌ لطفاً URL را وارد کنید';
    return;
  }
  
  respDiv.textContent = '⏳ درحال ارسال...';
  
  try {
    const options = {
      method: method,
      headers: {'Content-Type': 'application/json'}
    };
    
    if(method !== 'GET' && document.getElementById('body').value) {
      options.body = document.getElementById('body').value;
    }
    
    const response = await fetch(url, options);
    const data = await response.json();
    
    respDiv.textContent = `✓ وضعیت: ${response.status} ${response.statusText}\n\n${JSON.stringify(data, null, 2)}`;
  } catch(e) {
    respDiv.textContent = `❌ خطا:\n${e.message}\n\n💡 نکته: برخی APIها نیاز به CORS setup دارند`;
  }
}

function copyResponse() {
  const text = document.getElementById('response').textContent;
  navigator.clipboard.writeText(text);
  cToast('✓ کپی شد');
}

loadRequest('custom');
</script>
<?php cipher_foot();?>
