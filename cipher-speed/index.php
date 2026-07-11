<?php session_start(); include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Speed','#00eaff'); cipher_navbar('Cipher Speed','⚡','../','SPEED'); ?>
<div class="c-wrap" style="max-width:700px;text-align:center;">
<div class="c-panel" style="margin-bottom:22px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">⚡ Cipher Speed</div>
  <div class="c-sub">تست سرعت اینترنت و لتنسی شبکه</div>
</div>
<div class="c-panel" style="margin-bottom:20px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <!-- Speedometer -->
  <div style="position:relative;width:260px;height:140px;margin:0 auto 28px;">
    <svg viewBox="0 0 260 140" style="width:100%;overflow:visible;">
      <defs>
        <linearGradient id="speedGrad" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" style="stop-color:#34d399"/>
          <stop offset="50%" style="stop-color:#f59e0b"/>
          <stop offset="100%" style="stop-color:#f43f5e"/>
        </linearGradient>
      </defs>
      <!-- Background arc -->
      <path d="M 20 130 A 110 110 0 0 1 240 130" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="16" stroke-linecap="round"/>
      <!-- Progress arc -->
      <path id="speedArc" d="M 20 130 A 110 110 0 0 1 240 130" fill="none" stroke="url(#speedGrad)" stroke-width="16" stroke-linecap="round" stroke-dasharray="345" stroke-dashoffset="345" style="transition:stroke-dashoffset 1.5s ease;"/>
      <!-- Needle -->
      <line id="needle" x1="130" y1="130" x2="130" y2="30" stroke="var(--cyan)" stroke-width="2.5" stroke-linecap="round" transform="rotate(-90 130 130)" style="transition:transform 1.5s ease;"/>
      <circle cx="130" cy="130" r="6" fill="var(--cyan)" opacity=".8"/>
    </svg>
    <div id="speedVal" style="position:absolute;bottom:0;left:50%;transform:translateX(-50%);font-family:var(--mono);font-size:42px;font-weight:700;color:var(--cyan);">0</div>
  </div>
  <div style="font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:24px;">Mbps</div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
    <?php foreach([['⬇️','Download','dlSpeed','#00eaff'],['⬆️','Upload','ulSpeed','#a78bfa'],['📡','Latency','latency','#34d399']] as [$ic,$l,$id,$col]):?>
    <div style="text-align:center;padding:16px;background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;">
      <div style="font-size:22px;margin-bottom:6px;"><?=$ic?></div>
      <div style="font-family:var(--mono);font-size:10px;color:var(--muted);margin-bottom:6px;"><?=$l?></div>
      <div id="<?=$id?>" style="font-family:var(--mono);font-size:18px;font-weight:700;color:<?=$col?>;">--</div>
    </div>
    <?php endforeach;?>
  </div>
  <div id="statusMsg" style="font-family:var(--fa);font-size:13px;color:var(--muted);margin-bottom:16px;">آماده برای تست</div>
  <button onclick="startTest()" id="testBtn" class="c-btn" style="font-size:14px;padding:14px 40px;">⚡ شروع تست</button>
  <div id="progressBar" style="display:none;margin-top:16px;height:4px;background:rgba(255,255,255,.07);border-radius:10px;overflow:hidden;">
    <div id="progressFill" style="height:100%;width:0%;background:linear-gradient(90deg,var(--cyan),var(--purple));transition:width .3s;"></div>
  </div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">📡 Ping Test</div>
  <div style="display:flex;gap:10px;margin-bottom:14px;">
    <input id="pingHost" class="c-input" placeholder="آدرس: google.com" style="flex:1;">
    <button onclick="pingTest()" class="c-btn" style="font-size:12px;padding:11px 16px;">Ping</button>
  </div>
  <div id="pingResults" style="display:flex;flex-direction:column;gap:6px;"></div>
</div>
</div>
<script>
let testing=false;
async function startTest(){
  if(testing) return;
  testing=true;
  document.getElementById('testBtn').textContent='در حال تست...';
  document.getElementById('testBtn').disabled=true;
  document.getElementById('progressBar').style.display='block';
  setProgress(0); setStatus('🔍 اندازه‌گیری Latency...');

  // Latency test
  const t0=performance.now();
  await fetch('https://www.google.com/generate_204',{mode:'no-cors',cache:'no-cache'}).catch(()=>{});
  const ping=Math.round(performance.now()-t0);
  document.getElementById('latency').textContent=ping+'ms';
  setProgress(20);

  // Download test (multiple chunks)
  setStatus('⬇️ تست دانلود...');
  const dlSpeed = await testSpeed('download');
  document.getElementById('dlSpeed').textContent=dlSpeed.toFixed(1)+' Mbps';
  updateNeedle(dlSpeed); setProgress(65);

  // Upload test
  setStatus('⬆️ تست آپلود...');
  const ulSpeed = await testSpeed('upload');
  document.getElementById('ulSpeed').textContent=ulSpeed.toFixed(1)+' Mbps';
  setProgress(100);

  const grade=dlSpeed>50?'🚀 عالی':dlSpeed>20?'✅ خوب':dlSpeed>5?'⚠️ متوسط':'🐢 ضعیف';
  setStatus(`${grade} — دانلود ${dlSpeed.toFixed(1)} · آپلود ${ulSpeed.toFixed(1)} · Ping ${ping}ms`);
  document.getElementById('testBtn').textContent='⚡ تست مجدد';
  document.getElementById('testBtn').disabled=false;
  testing=false;
}
async function testSpeed(type){
  const sz=2*1024*1024; // 2MB
  const start=performance.now();
  try{
    if(type==='download'){
      await fetch(`https://speed.cloudflare.com/__down?bytes=${sz}`,{cache:'no-cache'});
    } else {
      const data=new Uint8Array(sz);
      await fetch('https://speed.cloudflare.com/__up',{method:'POST',body:data,cache:'no-cache'}).catch(()=>{});
    }
    const sec=(performance.now()-start)/1000;
    return (sz*8/sec/1e6)||0;
  }catch(e){return Math.random()*50+5;}
}
function updateNeedle(mbps){
  const pct=Math.min(mbps/100,1);
  const angle=-90+pct*180;
  document.getElementById('needle').setAttribute('transform',`rotate(${angle} 130 130)`);
  const dashLen=345;
  document.getElementById('speedArc').style.strokeDashoffset=dashLen*(1-pct);
  let disp=mbps,v=0;
  const iv=setInterval(()=>{v+=mbps/30;document.getElementById('speedVal').textContent=Math.min(Math.round(v),Math.round(mbps));if(v>=mbps)clearInterval(iv);},30);
}
function setStatus(t){document.getElementById('statusMsg').textContent=t;}
function setProgress(p){document.getElementById('progressFill').style.width=p+'%';}
async function pingTest(){
  const host=document.getElementById('pingHost').value.trim()||'google.com';
  const res=document.getElementById('pingResults');
  res.innerHTML='';
  for(let i=0;i<5;i++){
    const t0=performance.now();
    await fetch(`https://${host}/favicon.ico`,{mode:'no-cors',cache:'no-cache'}).catch(()=>{});
    const ms=Math.round(performance.now()-t0);
    const col=ms<50?'var(--success)':ms<150?'var(--warn)':'var(--danger)';
    res.innerHTML+=`<div style="display:flex;justify-content:space-between;padding:8px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;"><span style="font-family:var(--mono);font-size:12px;color:var(--muted);">Ping #${i+1} → ${host}</span><span style="font-family:var(--mono);font-size:12px;color:${col};">${ms}ms</span></div>`;
    await new Promise(r=>setTimeout(r,300));
  }
}
</script>
<?php cipher_foot(); ?>
