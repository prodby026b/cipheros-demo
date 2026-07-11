<?php
/**
 * Cipher Chat — نسخه تقویت‌شده
 * ------------------------------------------------------------------
 * احراز هویت از سشن اصلی Cipher OS انجام می‌شود.
 * نام کاربری از سشن خوانده می‌شود (بدون جعل).
 * تمام عملیات از طریق api/*.php امن انجام می‌شود.
 */
require_once __DIR__ . '/../cipher-core/security.php';
require_auth(false); // اگر احراز هویت نشده → redirect به login.php

$me        = current_chat_user();
$csrf      = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Cipher Chat — Cipher OS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Space+Mono:wght@400;700&family=Vazirmatn:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg0:#03040d;--bg1:#070914;--bg2:#0c0f1e;--bg3:#111628;
  --glass:rgba(255,255,255,.045);--glass2:rgba(255,255,255,.075);
  --stroke:rgba(255,255,255,.08);--stroke2:rgba(0,234,255,.22);
  --text:#e8edf8;--muted:#5b6b8a;--muted2:#8899b8;
  --cyan:#00eaff;--purple:#7c3aed;--success:#00ff99;--danger:#ef4444;--warn:#f59e0b;
  --font:'Space Grotesk','Vazirmatn',system-ui,sans-serif;
  --mono:'Space Mono',monospace;--fa:'Vazirmatn',system-ui,sans-serif;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;overflow:hidden;}
body{background:var(--bg0);color:var(--text);font-family:var(--font);}

body::before{
  content:'';position:fixed;inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.07) 2px,rgba(0,0,0,.07) 4px);
  pointer-events:none;z-index:999;
}
.orb{position:fixed;border-radius:50%;filter:blur(130px);pointer-events:none;z-index:0;}
.orb1{width:600px;height:600px;top:-200px;right:-150px;background:rgba(124,58,237,.14);}
.orb2{width:400px;height:400px;bottom:-150px;left:-100px;background:rgba(0,234,255,.07);}

/* NAVBAR */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  height:56px;background:rgba(3,4,13,.85);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--stroke);
  display:flex;align-items:center;justify-content:space-between;padding:0 20px;
}
.nav-brand{display:flex;align-items:center;gap:10px;}
.nav-icon{width:34px;height:34px;border-radius:10px;background:rgba(0,234,255,.1);border:1px solid rgba(0,234,255,.25);display:flex;align-items:center;justify-content:center;font-size:18px;}
.nav-name{font-weight:700;font-size:14px;}
.nav-sub{font-size:9px;color:var(--muted);font-family:var(--mono);letter-spacing:.1em;}
.nav-right{display:flex;align-items:center;gap:12px;}
.nav-search{
  display:flex;align-items:center;gap:8px;padding:7px 14px;border-radius:10px;
  background:var(--glass);border:1px solid var(--stroke);font-size:12px;
  color:var(--muted2);cursor:pointer;transition:.2s;font-family:var(--mono);
}
.nav-search:hover{border-color:var(--stroke2);color:var(--cyan);}
.nav-search kbd{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:10px;border:1px solid var(--stroke);}
.nav-home{
  display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;
  background:var(--glass);border:1px solid var(--stroke);font-size:12px;
  color:var(--muted2);text-decoration:none;transition:.2s;
}
.nav-home:hover{border-color:var(--stroke2);color:var(--cyan);}

/* LAYOUT */
.app{position:fixed;top:56px;bottom:0;left:0;right:0;display:flex;z-index:1;}

/* SIDEBAR */
.sidebar{
  width:280px;flex-shrink:0;background:rgba(7,9,20,.6);backdrop-filter:blur(14px);
  border-left:1px solid var(--stroke);display:flex;flex-direction:column;
}
.side-sec{padding:16px 18px;border-bottom:1px solid var(--stroke);}
.side-title{font-family:var(--mono);font-size:10px;letter-spacing:.18em;color:var(--muted);margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;}
.side-title button{background:none;border:none;color:var(--cyan);cursor:pointer;font-size:14px;font-family:var(--fa);}
.rooms-list,.users-list{display:flex;flex-direction:column;gap:4px;overflow-y:auto;}
.rooms-list{flex:1;}
.room-item{
  display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;
  cursor:pointer;transition:.15s;border:1px solid transparent;
}
.room-item:hover{background:var(--glass);}
.room-item.active{background:rgba(0,234,255,.08);border-color:var(--stroke2);}
.room-item .ri-icon{font-size:16px;}
.room-item .ri-name{font-size:13px;font-family:var(--fa);flex:1;}
.room-item .ri-lock{font-size:10px;color:var(--warn);}
.user-item{display:flex;align-items:center;gap:9px;padding:6px 8px;}
.user-item .dot{width:8px;height:8px;border-radius:50%;background:var(--success);box-shadow:0 0 8px var(--success);flex-shrink:0;}
.user-item .uname{font-size:12px;font-family:var(--fa);color:var(--muted2);}
.user-item.me .uname{color:var(--cyan);font-weight:600;}
.user-item .avatar-mini{width:24px;height:24px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:10px;font-weight:700;color:#03040d;}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;}
.chat-head{
  padding:14px 20px;border-bottom:1px solid var(--stroke);
  display:flex;align-items:center;gap:12px;background:rgba(7,9,20,.4);
}
.chat-head .ch-icon{width:38px;height:38px;border-radius:11px;background:rgba(0,234,255,.1);border:1px solid rgba(0,234,255,.2);display:flex;align-items:center;justify-content:center;font-size:18px;}
.chat-head .ch-name{font-size:15px;font-weight:700;font-family:var(--fa);}
.chat-head .ch-desc{font-size:11px;color:var(--muted);font-family:var(--mono);}
.online-count{margin-right:auto;display:flex;align-items:center;gap:6px;font-size:11px;color:var(--success);font-family:var(--mono);}

#chatBox{flex:1;overflow-y:auto;padding:16px 20px;scroll-behavior:smooth;}
#chatBox::-webkit-scrollbar{width:5px;}
#chatBox::-webkit-scrollbar-track{background:transparent;}
#chatBox::-webkit-scrollbar-thumb{background:var(--stroke2);border-radius:10px;}

/* MESSAGES */
.msg{display:flex;gap:10px;margin-bottom:16px;animation:msgIn .25s ease;}
.msg.mine{flex-direction:row-reverse;}
@keyframes msgIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.avatar{width:36px;height:36px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:13px;font-weight:700;color:#03040d;}
.msg-body{max-width:62%;display:flex;flex-direction:column;}
.msg.mine .msg-body{align-items:flex-end;}
.msg-meta{display:flex;align-items:center;gap:8px;margin-bottom:5px;}
.msg.mine .msg-meta{flex-direction:row-reverse;}
.msg-name{font-size:11px;font-family:var(--mono);color:var(--cyan);}
.msg-time{font-size:10px;color:var(--muted);font-family:var(--mono);}
.msg-edited{font-size:9px;color:var(--warn);font-family:var(--mono);}
.bubble{
  padding:10px 14px;background:var(--bg2);border:1px solid var(--stroke);
  font-size:14px;font-family:var(--fa);line-height:1.75;color:var(--text);
  word-break:break-word;position:relative;max-width:100%;
}
.msg:not(.mine) .bubble{border-radius:4px 14px 14px 14px;}
.msg.mine .bubble{border-radius:14px 4px 14px 14px;}
.msg.mine .bubble{background:rgba(0,234,255,.08);border-color:var(--stroke2);}
.msg.deleted .bubble{opacity:.5;font-style:italic;color:var(--muted);}
.msg-img{max-width:260px;border-radius:10px;display:block;border:1px solid var(--stroke);cursor:pointer;}

/* reply preview */
.reply-prev{display:flex;align-items:center;gap:6px;padding:6px 10px;background:var(--bg3);border-right:3px solid var(--cyan);border-radius:8px;margin-bottom:5px;font-size:11px;color:var(--muted2);font-family:var(--fa);}
.reply-prev b{color:var(--cyan);font-family:var(--mono);font-size:10px;}

/* reactions */
.reactions{display:flex;flex-wrap:wrap;gap:4px;margin-top:5px;}
.msg.mine .reactions{justify-content:flex-end;}
.reaction{display:flex;align-items:center;gap:4px;padding:2px 8px;border-radius:10px;background:var(--bg3);border:1px solid var(--stroke);font-size:12px;cursor:pointer;transition:.15s;font-family:var(--fa);}
.reaction:hover{border-color:var(--stroke2);}
.reaction.mine{background:rgba(0,234,255,.12);border-color:var(--stroke2);}
.reaction .count{font-size:10px;color:var(--muted2);font-family:var(--mono);}

/* actions on hover */
.msg-actions{position:absolute;top:-12px;display:flex;gap:2px;background:var(--bg3);border:1px solid var(--stroke);border-radius:8px;padding:2px;opacity:0;transition:.15s;z-index:5;}
.msg:not(.mine) .msg-actions{right:8px;}
.msg.mine .msg-actions{left:8px;}
.msg-wrap:hover .msg-actions{opacity:1;}
.msg-actions button{background:none;border:none;cursor:pointer;padding:4px 6px;border-radius:5px;font-size:13px;transition:.15s;}
.msg-actions button:hover{background:var(--glass2);}

.msg-wrap{position:relative;display:inline-block;max-width:100%;}

/* read status */
.read-status{font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:3px;display:flex;align-items:center;gap:4px;}
.read-status.seen{color:var(--cyan);}

/* TYPING */
.typing-bar{height:22px;padding:0 20px;font-size:11px;color:var(--muted2);font-family:var(--fa);font-style:italic;display:flex;align-items:center;}
.typing-dots span{display:inline-block;width:4px;height:4px;border-radius:50%;background:var(--cyan);margin:0 1px;animation:bounce 1.2s infinite;}
.typing-dots span:nth-child(2){animation-delay:.2s;}
.typing-dots span:nth-child(3){animation-delay:.4s;}
@keyframes bounce{0%,60%,100%{transform:translateY(0);opacity:.4}30%{transform:translateY(-4px);opacity:1}}

/* INPUT */
.input-bar{padding:12px 16px;background:rgba(3,4,13,.8);border-top:1px solid var(--stroke);display:flex;gap:10px;align-items:flex-end;}
.reply-quote{flex:1 100%;display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--bg2);border:1px solid var(--stroke2);border-radius:10px;margin-bottom:8px;font-size:12px;color:var(--muted2);font-family:var(--fa);}
.reply-quote b{color:var(--cyan);font-family:var(--mono);}
.reply-quote button{margin-right:auto;background:none;border:none;color:var(--danger);cursor:pointer;font-size:14px;}
.file-btn{width:44px;height:44px;border-radius:12px;background:var(--glass2);border:1px solid var(--stroke);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:19px;flex-shrink:0;transition:.2s;}
.file-btn:hover{border-color:var(--stroke2);}
.file-btn input{display:none;}
#msgInput{flex:1;min-height:44px;max-height:120px;padding:12px 16px;border-radius:12px;background:var(--bg2);border:1px solid var(--stroke);color:var(--text);font-family:var(--fa);font-size:15px;outline:none;transition:.2s;resize:none;line-height:1.5;}
#msgInput:focus{border-color:rgba(0,234,255,.4);box-shadow:0 0 0 3px rgba(0,234,255,.06);}
.send-btn{height:44px;padding:0 20px;border:none;border-radius:12px;cursor:pointer;background:linear-gradient(135deg,var(--cyan),#0099bb);color:var(--bg0);font-family:var(--mono);font-size:12px;font-weight:700;letter-spacing:.06em;white-space:nowrap;transition:.2s;flex-shrink:0;}
.send-btn:hover{opacity:.88;}
.send-btn:disabled{opacity:.4;cursor:not-allowed;}

/* SEARCH MODAL */
.modal-bg{position:fixed;inset:0;background:rgba(3,4,13,.8);backdrop-filter:blur(6px);z-index:200;display:none;align-items:flex-start;justify-content:center;padding-top:80px;}
.modal-bg.show{display:flex;}
.modal-card{width:min(100%,560px);background:var(--bg1);border:1px solid var(--stroke);border-radius:16px;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.6);}
.modal-card input{width:100%;padding:16px 20px;background:transparent;border:none;border-bottom:1px solid var(--stroke);color:var(--text);font-family:var(--fa);font-size:15px;outline:none;}
.modal-card input:focus{border-bottom-color:var(--cyan);}
.search-results{max-height:50vh;overflow-y:auto;}
.search-result{padding:12px 20px;border-bottom:1px solid var(--stroke);cursor:pointer;transition:.15s;}
.search-result:hover{background:var(--glass);}
.search-result .sr-name{font-size:11px;color:var(--cyan);font-family:var(--mono);margin-bottom:3px;}
.search-result .sr-msg{font-size:13px;font-family:var(--fa);color:var(--text);}
.search-result mark{background:rgba(0,234,255,.25);color:var(--text);border-radius:3px;padding:0 2px;}
.no-results{padding:30px;text-align:center;color:var(--muted);font-family:var(--fa);}

/* NEW ROOM MODAL */
.nr-input{width:100%;padding:12px 14px;border-radius:10px;background:var(--bg2);border:1px solid var(--stroke);color:var(--text);font-family:var(--fa);font-size:14px;outline:none;margin-bottom:10px;}
.nr-input:focus{border-color:var(--cyan);}
.nr-label{font-size:11px;color:var(--muted);font-family:var(--mono);margin:8px 0 6px;display:block;}
.nr-check{display:flex;align-items:center;gap:8px;margin:10px 0;font-size:13px;color:var(--muted2);font-family:var(--fa);cursor:pointer;}

.toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--bg3);border:1px solid var(--stroke2);padding:12px 20px;border-radius:12px;font-family:var(--fa);font-size:13px;z-index:300;opacity:0;transition:.3s;pointer-events:none;}
.toast.show{opacity:1;}

@keyframes pulse{0%{opacity:.3}50%{opacity:1}100%{opacity:.3}}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}

@media (max-width:768px){
  .sidebar{position:absolute;right:-280px;top:0;bottom:0;z-index:50;transition:.25s;}
  .sidebar.open{right:0;}
  .msg-body{max-width:85%;}
  .nav-search span{display:none;}
}
</style>
</head>
<body>

<div class="orb orb1"></div>
<div class="orb orb2"></div>

<nav class="nav">
  <div class="nav-brand">
    <div class="nav-icon">💬</div>
    <div>
      <div class="nav-name">Cipher Chat</div>
      <div class="nav-sub">CIPHER OS · PRODBY026B</div>
    </div>
  </div>
  <div class="nav-right">
    <div class="nav-search" onclick="openSearch()">
      🔍 <span>جستجو</span> <kbd>Ctrl+K</kbd>
    </div>
    <a href="../" class="nav-home">⌂ Dashboard</a>
  </div>
</nav>

<div class="app">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="side-sec" style="flex:0 0 auto;">
      <div class="side-title">اتاق‌ها <button onclick="openNewRoom()" title="اتاق جدید">＋</button></div>
      <div class="rooms-list" id="roomsList"></div>
    </div>
    <div class="side-sec" style="flex:0 0 auto;">
      <div class="side-title">آنلاین (<span id="onlineCount">0</span>)</div>
      <div class="users-list" id="usersList" style="max-height:200px;"></div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="chat-head">
      <div class="ch-icon" id="roomIcon">#</div>
      <div>
        <div class="ch-name" id="roomName">—</div>
        <div class="ch-desc" id="roomDesc"></div>
      </div>
      <div class="online-count">
        <span style="width:7px;height:7px;border-radius:50%;background:var(--success);box-shadow:0 0 8px var(--success);display:inline-block;animation:pulse 2s infinite;"></span>
        <span id="headOnline">0 آنلاین</span>
      </div>
    </div>

    <div id="chatBox"></div>

    <div class="typing-bar" id="typingBar"></div>

    <div class="input-bar" id="inputBar">
      <div id="replyQuote" style="display:none;width:100%;"></div>
      <label class="file-btn" title="ارسال تصویر">
        📎<input type="file" id="fileIn" accept="image/jpeg,image/png,image/gif,image/webp">
      </label>
      <textarea id="msgInput" placeholder="پیام بنویسید..." rows="1" autocomplete="off"></textarea>
      <button class="send-btn" id="sendBtn" onclick="sendMsg()">ارسال →</button>
    </div>
  </main>
</div>

<!-- SEARCH MODAL -->
<div class="modal-bg" id="searchModal" onclick="if(event.target===this)closeSearch()">
  <div class="modal-card">
    <input type="text" id="searchInput" placeholder="جستجو در پیام‌ها..." autocomplete="off">
    <div class="search-results" id="searchResults">
      <div class="no-results">حداقل ۲ حرف تایپ کنید...</div>
    </div>
  </div>
</div>

<!-- NEW ROOM MODAL -->
<div class="modal-bg" id="roomModal" onclick="if(event.target===this)closeNewRoom()">
  <div class="modal-card" style="width:min(100%,420px);">
    <div style="padding:20px;">
      <div style="font-size:15px;font-weight:700;margin-bottom:16px;font-family:var(--fa);">🏗️ ساخت اتاق جدید</div>
      <label class="nr-label">نام اتاق</label>
      <input type="text" id="nrName" class="nr-input" placeholder="مثلاً: تیم طراحی" autocomplete="off">
      <label class="nr-label">توضیحات (اختیاری)</label>
      <input type="text" id="nrDesc" class="nr-input" placeholder="توضیحات کوتاه...">
      <label class="nr-check"><input type="checkbox" id="nrPrivate"> اتاق خصوصی</label>
      <button class="send-btn" style="width:100%;margin-top:10px;" onclick="createRoom()">ایجاد اتاق</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ==================================================================
 * Cipher Chat — Frontend
 * ================================================================== */
const ME = <?= json_encode($me) ?>;
const CSRF = <?= json_encode($csrf) ?>;
const POLL_INTERVAL = 3000;       // polling هر ۳ ثانیه
const ONLINE_INTERVAL = 30000;    // بروزرسانی آنلاین هر ۳۰ ثانیه

let currentRoom = null;
let lastMsgId = 0;
let roomsCache = [];
let messagesCache = new Map();    // id → element
let replyTo = null;
let typingTimer = null;
let lastTypingSent = 0;
let searchTimer = null;

const chatBox = document.getElementById('chatBox');
const msgInput = document.getElementById('msgInput');

/* ─── helpers ─── */
function esc(s){const d=document.createElement('div');d.textContent=s??'';return d.innerHTML;}
function initials(name){return (name||'?').substring(0,2).toUpperCase();}
function avatarColor(name){
  let h=0;for(let i=0;i<(name||'').length;i++)h=name.charCodeAt(i)+((h<<5)-h);
  return '#'+('000000'+(h&0xFFFFFF).toString(16)).slice(-6);
}
function faTime(ts){
  if(!ts) return '';
  try{return new Date(ts.replace(' ','T')+'Z').toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'});}
  catch(e){return '';}
}
function toast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg;t.classList.add('show');
  clearTimeout(t._tm);t._tm=setTimeout(()=>t.classList.remove('show'),2500);
}
async function api(path, opts={}){
  const headers = opts.body instanceof FormData ? {} : {'Content-Type':'application/json'};
  const body = opts.body instanceof FormData ? opts.body : (opts.body ? JSON.stringify({...opts.body, csrf_token:CSRF}) : undefined);
  const res = await fetch('api/'+path, {method:opts.method||'POST', headers, body});
  let data; try{data=await res.json();}catch(e){data={ok:false,error:'parse_error'};}
  if(!res.ok && !data.error) data.error='http_'+res.status;
  return data;
}

/* ─── rooms ─── */
async function loadRooms(){
  const data = await api('rooms.php', {method:'GET'});
  if(!data.ok) return;
  roomsCache = data.rooms || [];
  renderRooms();
  // اگر اتاقی انتخاب نشده، اولین اتاق عمومی
  if(!currentRoom && roomsCache.length){
    const def = roomsCache.find(r=>!parseInt(r.is_private)) || roomsCache[0];
    selectRoom(parseInt(def.id));
  }
}
function renderRooms(){
  const list = document.getElementById('roomsList');
  list.innerHTML = roomsCache.map(r=>`
    <div class="room-item ${currentRoom===parseInt(r.id)?'active':''}" onclick="selectRoom(${parseInt(r.id)})">
      <span class="ri-icon">${parseInt(r.is_private)?'🔒':'#'}</span>
      <span class="ri-name">${esc(r.name)}</span>
    </div>`).join('');
}

/* ─── select room ─── */
function selectRoom(id){
  if(currentRoom===id) return;
  currentRoom = id;
  lastMsgId = 0;
  messagesCache.clear();
  chatBox.innerHTML = '';
  const room = roomsCache.find(r=>parseInt(r.id)===id);
  if(room){
    document.getElementById('roomName').textContent = room.name;
    document.getElementById('roomDesc').textContent = room.description || (parseInt(room.is_private)?'اتاق خصوصی':'اتاق عمومی');
    document.getElementById('roomIcon').textContent = parseInt(room.is_private)?'🔒':'#';
  }
  renderRooms();
  cancelReply();
  fetchMessages();
}

/* ─── fetch messages (optimized polling) ─── */
async function fetchMessages(){
  if(!currentRoom) return;
  const data = await api('fetch.php', {method:'POST', body:{roomId:currentRoom, lastId:lastMsgId}});
  if(!data.ok) return;
  // update identity
  // rooms may have changed
  if(data.rooms && data.rooms.length){
    roomsCache = data.rooms;
    renderRooms();
  }
  // online users
  renderOnline(data.online || []);
  document.getElementById('onlineCount').textContent = (data.online||[]).length;
  document.getElementById('headOnline').textContent = (data.online||[]).length + ' آنلاین';
  // typing
  renderTyping(data.typing || []);
  // new messages
  if(data.messages && data.messages.length){
    for(const m of data.messages){
      if(!messagesCache.has(m.id)){
        appendMessage(m);
        if(m.id > lastMsgId) lastMsgId = m.id;
      }
    }
    // اسکرول به پایین اگر کاربر نزدیک پایین بود
    const nearBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 150;
    if(nearBottom) chatBox.scrollTop = chatBox.scrollHeight;
  }
}
setInterval(fetchMessages, POLL_INTERVAL);

/* ─── render online ─── */
function renderOnline(users){
  const list = document.getElementById('usersList');
  list.innerHTML = users.map(u=>{
    const isMe = u.username === ME;
    return `<div class="user-item ${isMe?'me':''}">
      <div class="avatar-mini" style="background:${avatarColor(u.username)};">${esc(initials(u.username))}</div>
      <span class="dot"></span>
      <span class="uname">${esc(u.username)}${isMe?' (شما)':''}</span>
    </div>`;
  }).join('');
}

/* ─── render typing ─── */
function renderTyping(typingUsers){
  const bar = document.getElementById('typingBar');
  const others = typingUsers.filter(u=>u!==ME);
  if(!others.length){bar.innerHTML='';return;}
  const names = others.slice(0,3).join('، ');
  const extra = others.length>3 ? ` و ${others.length-3} نفر دیگر` : '';
  bar.innerHTML = `<span class="typing-dots"><span></span><span></span><span></span></span> ${esc(names)}${extra} در حال تایپ...`;
}

/* ─── append a message ─── */
function appendMessage(m){
  const wrap = document.createElement('div');
  wrap.className = 'msg' + (m.mine?' mine':'') + (m.deletedAt?' deleted':'');
  wrap.dataset.id = m.id;

  const avatar = `<div class="avatar" style="background:${avatarColor(m.username)};">${esc(initials(m.username))}</div>`;
  const time = faTime(m.createdAt);

  // reply preview
  let replyHtml = '';
  if(m.reply){
    replyHtml = `<div class="reply-prev"><b>${esc(m.reply.username)}</b> ${esc(m.reply.preview||'')}</div>`;
  }

  // body
  let bodyHtml = '';
  if(m.deletedAt){
    bodyHtml = `<span style="color:var(--muted);font-style:italic;">🗑️ این پیام حذف شده است</span>`;
  } else if(m.type==='image' && m.filePath){
    bodyHtml = `<img src="../cipher-chat/${esc(m.filePath)}" class="msg-img" onclick="window.open(this.src,'_blank')" onerror="this.style.display='none'">`;
  } else {
    bodyHtml = linkify(esc(m.message));
  }

  // actions (only for non-deleted, and edit/delete only for own)
  let actionsHtml = '<div class="msg-actions">';
  actionsHtml += `<button onclick="setReply(${m.id})" title="ریپلای">↩</button>`;
  actionsHtml += `<button onclick="quickReact(${m.id},'👍')" title="پسندیدم">👍</button>`;
  if(m.mine && !m.deletedAt){
    actionsHtml += `<button onclick="editMsg(${m.id})" title="ویرایش">✏️</button>`;
    actionsHtml += `<button onclick="deleteMsg(${m.id})" title="حذف">🗑️</button>`;
  }
  actionsHtml += '</div>';

  // reactions
  let reactHtml = '';
  if(m.reactions && Object.keys(m.reactions).length){
    reactHtml = '<div class="reactions">' + Object.entries(m.reactions).map(([emoji,users])=>{
      const mine = users.includes(ME);
      return `<span class="reaction ${mine?'mine':''}" onclick="toggleReact(${m.id},'${emoji}')">${emoji}<span class="count">${users.length}</span></span>`;
    }).join('') + '</div>';
  }

  // read status (only for own non-deleted)
  let readHtml = '';
  if(m.mine && !m.deletedAt){
    if(m.readCount>0){
      readHtml = `<div class="read-status seen">✓✓ خوانده شد (${m.readCount})</div>`;
    } else {
      readHtml = `<div class="read-status">✓ تحویل داده شد</div>`;
    }
  }

  const edited = m.editedAt ? '<span class="msg-edited">ویرایش‌شده</span>' : '';

  wrap.innerHTML = `
    ${avatar}
    <div class="msg-body">
      <div class="msg-meta">
        <span class="msg-name">${esc(m.username)}${m.username===ME?' (شما)':''}</span>
        <span class="msg-time">${time}</span>
        ${edited}
      </div>
      <div class="msg-wrap">
        ${actionsHtml}
        ${replyHtml}
        <div class="bubble">${bodyHtml}</div>
      </div>
      ${reactHtml}
      ${readHtml}
    </div>`;

  chatBox.appendChild(wrap);
  messagesCache.set(m.id, {element:wrap, data:m});
}

/* ─── linkify URLs in message text ─── */
function linkify(text){
  return text.replace(/(https?:\/\/[^\s<]+)/g, url=>`<a href="${esc(url)}" target="_blank" rel="noopener" style="color:var(--cyan);text-decoration:underline;">${esc(url)}</a>`);
}

/* ─── send message ─── */
async function sendMsg(){
  const msg = msgInput.value.trim();
  if(!msg || !currentRoom) return;
  msgInput.value=''; autoGrow();
  const body = {roomId:currentRoom, message:msg};
  if(replyTo) body.replyTo = replyTo;
  const data = await api('send.php', {body});
  if(!data.ok){toast('خطا در ارسال: '+(data.error||'')); msgInput.value=msg; return;}
  cancelReply();
  fetchMessages();
}

/* ─── upload ─── */
document.getElementById('fileIn').addEventListener('change', async function(){
  if(!this.files[0] || !currentRoom) return;
  const fd = new FormData();
  fd.append('file', this.files[0]);
  fd.append('roomId', currentRoom);
  fd.append('csrf_token', CSRF);
  toast('در حال آپلود...');
  const data = await api('upload.php', {body:fd});
  this.value='';
  if(!data.ok){toast('خطا در آپلود: '+(data.error||'')); return;}
  toast('تصویر ارسال شد ✓');
  fetchMessages();
});

/* ─── reply / edit / delete / react ─── */
function setReply(id){
  const cached = messagesCache.get(id);
  if(!cached) return;
  replyTo = id;
  const q = document.getElementById('replyQuote');
  q.style.display='flex';
  q.className='reply-quote';
  q.innerHTML = `<b>${esc(cached.data.username)}</b> ${esc((cached.data.message||'').substring(0,60))}<button onclick="cancelReply()">✕</button>`;
  msgInput.focus();
}
function cancelReply(){
  replyTo=null;
  const q=document.getElementById('replyQuote');
  q.style.display='none';q.innerHTML='';
}

async function editMsg(id){
  const cached = messagesCache.get(id);
  if(!cached) return;
  const newText = prompt('ویرایش پیام:', cached.data.message || '');
  if(newText===null) return;
  if(!newText.trim()) return;
  const data = await api('edit.php', {body:{messageId:id, message:newText.trim()}});
  if(!data.ok){toast('خطا در ویرایش'); return;}
  toast('ویرایش شد ✓');
  fetchMessages();
}

async function deleteMsg(id){
  if(!confirm('این پیام حذف شود؟')) return;
  const data = await api('delete.php', {body:{messageId:id}});
  if(!data.ok){toast('خطا در حذف'); return;}
  toast('حذف شد ✓');
  fetchMessages();
}

async function toggleReact(id, emoji){
  const data = await api('react.php', {body:{messageId:id, emoji}});
  if(!data.ok) return;
  fetchMessages();
}
function quickReact(id, emoji){ toggleReact(id, emoji); }

/* ─── typing ─── */
function onTyping(){
  if(!currentRoom) return;
  const now = Date.now();
  if(now - lastTypingSent < 2000) return; // حداقل هر ۲ ثانیه
  lastTypingSent = now;
  api('typing.php', {body:{roomId:currentRoom}});
}
msgInput.addEventListener('input', ()=>{ autoGrow(); onTyping(); });
msgInput.addEventListener('keydown', e=>{
  if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMsg(); }
});
function autoGrow(){
  msgInput.style.height='auto';
  msgInput.style.height=Math.min(msgInput.scrollHeight,120)+'px';
}

/* ─── online keepalive ─── */
setInterval(()=>{ api('online.php', {body:{}}); }, ONLINE_INTERVAL);

/* ─── search ─── */
function openSearch(){
  document.getElementById('searchModal').classList.add('show');
  setTimeout(()=>document.getElementById('searchInput').focus(),50);
}
function closeSearch(){
  document.getElementById('searchModal').classList.remove('show');
  document.getElementById('searchInput').value='';
  document.getElementById('searchResults').innerHTML='<div class="no-results">حداقل ۲ حرف تایپ کنید...</div>';
}
document.getElementById('searchInput').addEventListener('input', function(){
  clearTimeout(searchTimer);
  const q = this.value.trim();
  if(q.length<2){document.getElementById('searchResults').innerHTML='<div class="no-results">حداقل ۲ حرف تایپ کنید...</div>';return;}
  searchTimer = setTimeout(()=>doSearch(q), 350);
});
async function doSearch(q){
  if(!currentRoom) return;
  const data = await api('search.php', {body:{roomId:currentRoom, query:q}});
  const box = document.getElementById('searchResults');
  if(!data.ok || !data.results.length){
    box.innerHTML='<div class="no-results">نتیجه‌ای یافت نشد</div>';return;
  }
  const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi');
  box.innerHTML = data.results.map(m=>{
    const text = esc(m.message||'').replace(re, x=>`<mark>${x}</mark>`);
    return `<div class="search-result" onclick="jumpToMsg(${m.id})">
      <div class="sr-name">${esc(m.username)} · ${faTime(m.createdAt)}</div>
      <div class="sr-msg">${text}</div>
    </div>`;
  }).join('');
}
function jumpToMsg(id){
  closeSearch();
  const el = messagesCache.get(id);
  if(el){
    el.element.scrollIntoView({behavior:'smooth',block:'center'});
    el.element.style.transition='background .5s';
    el.element.style.background='rgba(0,234,255,.1)';
    setTimeout(()=>el.element.style.background='',1200);
  }
}

/* ─── new room ─── */
function openNewRoom(){ document.getElementById('roomModal').classList.add('show'); setTimeout(()=>document.getElementById('nrName').focus(),50); }
function closeNewRoom(){ document.getElementById('roomModal').classList.remove('show'); }
async function createRoom(){
  const name = document.getElementById('nrName').value.trim();
  const desc = document.getElementById('nrDesc').value.trim();
  const isPrivate = document.getElementById('nrPrivate').checked;
  if(!name){toast('نام اتاق الزامی است');return;}
  const data = await api('rooms.php', {body:{name, description:desc, isPrivate}});
  if(!data.ok){toast('خطا در ساخت اتاق');return;}
  document.getElementById('nrName').value='';document.getElementById('nrDesc').value='';document.getElementById('nrPrivate').checked=false;
  closeNewRoom();
  toast('اتاق ساخته شد ✓');
  await loadRooms();
  selectRoom(data.id);
}

/* ─── keyboard shortcuts ─── */
document.addEventListener('keydown', e=>{
  if((e.ctrlKey||e.metaKey) && e.key.toLowerCase()==='k'){ e.preventDefault(); openSearch(); }
  if(e.key==='Escape'){ closeSearch(); closeNewRoom(); }
});

/* ─── init ─── */
loadRooms();
api('online.php', {body:{}});
</script>

</body>
</html>
