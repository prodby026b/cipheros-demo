<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Timer', '#f43f5e');
cipher_navbar('Cipher Timer', '⏱️', '../', 'TIMER');
?>
<div class="c-wrap" style="max-width:700px;">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">⏱️ Cipher Timer</div>
  <div class="c-sub">تایمر، کرونومتر و تایمر Pomodoro اختصاصی Cipher OS</div>
</div>

<!-- TABS -->
<div style="display:flex;gap:8px;margin-bottom:20px;">
  <?php foreach(['⏱️ کرونومتر','⏳ تایمر','🍅 Pomodoro'] as $i=>$t):?>
  <button onclick="switchTab(<?=$i?>)" id="tab<?=$i?>" class="<?=$i===0?'c-btn':'c-btn-ghost'?>"
    style="<?=$i===0?'':'border:1px solid var(--stroke);'?> font-family:var(--fa);font-size:13px;padding:9px 18px;"><?=$t?></button>
  <?php endforeach;?>
</div>

<!-- STOPWATCH -->
<div id="tab-0" class="c-panel" style="text-align:center;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div id="swDisplay" style="font-family:var(--mono);font-size:72px;font-weight:700;color:var(--cyan);letter-spacing:.05em;margin:24px 0;">00:00.00</div>
  <div style="display:flex;justify-content:center;gap:12px;margin-bottom:20px;">
    <button onclick="swToggle()" id="swBtn" class="c-btn" style="font-size:14px;padding:14px 32px;">▶ شروع</button>
    <button onclick="swReset()" class="c-btn-ghost" style="font-size:14px;padding:14px 24px;">↺ ریست</button>
    <button onclick="swLap()" class="c-btn-ghost" style="font-size:14px;padding:14px 24px;">📌 Lap</button>
  </div>
  <div id="laps" style="max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;"></div>
</div>

<!-- COUNTDOWN -->
<div id="tab-1" class="c-panel" style="display:none;text-align:center;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div style="display:flex;justify-content:center;gap:12px;margin-bottom:24px;">
    <div style="text-align:center;">
      <input id="cdH" type="number" min="0" max="99" value="0" class="c-input" style="width:80px;text-align:center;font-family:var(--mono);font-size:22px;font-weight:700;">
      <div class="c-label" style="margin-top:4px;">ساعت</div>
    </div>
    <div style="font-size:32px;padding-top:8px;color:var(--muted);">:</div>
    <div style="text-align:center;">
      <input id="cdM" type="number" min="0" max="59" value="25" class="c-input" style="width:80px;text-align:center;font-family:var(--mono);font-size:22px;font-weight:700;">
      <div class="c-label" style="margin-top:4px;">دقیقه</div>
    </div>
    <div style="font-size:32px;padding-top:8px;color:var(--muted);">:</div>
    <div style="text-align:center;">
      <input id="cdS" type="number" min="0" max="59" value="0" class="c-input" style="width:80px;text-align:center;font-family:var(--mono);font-size:22px;font-weight:700;">
      <div class="c-label" style="margin-top:4px;">ثانیه</div>
    </div>
  </div>
  <div id="cdDisplay" style="font-family:var(--mono);font-size:72px;font-weight:700;color:var(--cyan);letter-spacing:.05em;margin-bottom:24px;">25:00</div>
  <div style="display:flex;justify-content:center;gap:12px;">
    <button onclick="cdStart()" id="cdBtn" class="c-btn" style="font-size:14px;padding:14px 32px;">▶ شروع</button>
    <button onclick="cdReset()" class="c-btn-ghost" style="font-size:14px;padding:14px 24px;">↺ ریست</button>
  </div>
</div>

<!-- POMODORO -->
<div id="tab-2" class="c-panel" style="display:none;text-align:center;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div id="pomMode" style="font-family:var(--mono);font-size:11px;letter-spacing:.2em;color:#f43f5e;margin-bottom:8px;">🍅 FOCUS TIME</div>
  <div id="pomDisplay" style="font-family:var(--mono);font-size:72px;font-weight:700;color:#f43f5e;letter-spacing:.05em;margin:16px 0 24px;">25:00</div>
  <div style="display:flex;justify-content:center;gap:8px;margin-bottom:20px;">
    <?php foreach([['🍅 Work','25:00','work'],['☕ Break','05:00','short'],['🛌 Long','15:00','long']] as [$l,$t,$m]):?>
    <button onclick="pomSet('<?=$m?>','<?=$t?>')" class="c-btn-ghost" style="font-size:12px;padding:8px 14px;font-family:var(--fa);"><?=$l?></button>
    <?php endforeach;?>
  </div>
  <div style="display:flex;justify-content:center;gap:12px;margin-bottom:20px;">
    <button onclick="pomToggle()" id="pomBtn" class="c-btn" style="font-size:14px;padding:14px 32px;">▶ شروع</button>
    <button onclick="pomReset()" class="c-btn-ghost" style="font-size:14px;padding:14px 24px;">↺ ریست</button>
  </div>
  <div id="pomCycles" style="font-family:var(--mono);font-size:12px;color:var(--muted);">🍅 چرخه: 0</div>
</div>
</div>

<script>
// TAB
function switchTab(i){
  [0,1,2].forEach(j=>{
    document.getElementById('tab-'+j).style.display=j===i?'block':'none';
    const b=document.getElementById('tab'+j);
    b.className=j===i?'c-btn':'c-btn-ghost';
    if(j!==i) b.style.border='1px solid var(--stroke)';
  });
}

// STOPWATCH
let swInt,swMs=0,swRun=false,lapN=1;
function swTick(){swMs+=10;document.getElementById('swDisplay').textContent=fmtSW(swMs);}
function fmtSW(ms){const s=Math.floor(ms/1000);return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0')+'.'+String(Math.floor((ms%1000)/10)).padStart(2,'0');}
function swToggle(){swRun=!swRun;if(swRun){swInt=setInterval(swTick,10);document.getElementById('swBtn').textContent='⏸ توقف';}else{clearInterval(swInt);document.getElementById('swBtn').textContent='▶ ادامه';}}
function swReset(){clearInterval(swInt);swRun=false;swMs=0;lapN=1;document.getElementById('swDisplay').textContent='00:00.00';document.getElementById('swBtn').textContent='▶ شروع';document.getElementById('laps').innerHTML='';}
function swLap(){const laps=document.getElementById('laps');laps.innerHTML=`<div style="padding:8px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;font-family:var(--mono);font-size:13px;display:flex;justify-content:space-between;"><span style="color:var(--muted);">Lap ${lapN++}</span><span style="color:var(--cyan);">${fmtSW(swMs)}</span></div>`+laps.innerHTML;}

// COUNTDOWN
let cdInt,cdSec=0,cdRun=false,cdTotal=0;
function cdStart(){
  if(!cdRun&&cdSec===0){
    cdTotal=(parseInt(document.getElementById('cdH').value)||0)*3600+(parseInt(document.getElementById('cdM').value)||0)*60+(parseInt(document.getElementById('cdS').value)||0);
    cdSec=cdTotal; if(!cdSec)return;
  }
  cdRun=!cdRun;
  if(cdRun){cdInt=setInterval(()=>{if(cdSec<=0){clearInterval(cdInt);cdRun=false;document.getElementById('cdBtn').textContent='▶ شروع';notify('⏰ تایمر تموم شد!');return;}cdSec--;document.getElementById('cdDisplay').textContent=fmtCD(cdSec);},1000);document.getElementById('cdBtn').textContent='⏸ توقف';}
  else{clearInterval(cdInt);document.getElementById('cdBtn').textContent='▶ ادامه';}
}
function cdReset(){clearInterval(cdInt);cdRun=false;cdSec=0;document.getElementById('cdBtn').textContent='▶ شروع';document.getElementById('cdDisplay').textContent='00:00';}
function fmtCD(s){return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');}

// POMODORO
let pomInt,pomSec=25*60,pomRun=false,pomCyc=0,pomCurMode='work';
const pomColors={work:'#f43f5e',short:'#34d399',long:'#38bdf8'};
const pomTimes={work:25*60,short:5*60,long:15*60};
const pomLabels={work:'🍅 FOCUS TIME',short:'☕ SHORT BREAK',long:'🛌 LONG BREAK'};
function pomSet(mode,timeStr){pomCurMode=mode;pomSec=pomTimes[mode];pomRun=false;clearInterval(pomInt);document.getElementById('pomBtn').textContent='▶ شروع';document.getElementById('pomDisplay').style.color=pomColors[mode];document.getElementById('pomMode').style.color=pomColors[mode];document.getElementById('pomDisplay').textContent=fmtCD(pomSec);document.getElementById('pomMode').textContent=pomLabels[mode];}
function pomToggle(){pomRun=!pomRun;if(pomRun){pomInt=setInterval(()=>{if(pomSec<=0){clearInterval(pomInt);pomRun=false;if(pomCurMode==='work'){pomCyc++;document.getElementById('pomCycles').textContent='🍅 چرخه: '+pomCyc;pomSet('short','05:00');}else pomSet('work','25:00');notify('🍅 Pomodoro: وقت تغییر!');return;}pomSec--;document.getElementById('pomDisplay').textContent=fmtCD(pomSec);},1000);document.getElementById('pomBtn').textContent='⏸ توقف';}else{clearInterval(pomInt);document.getElementById('pomBtn').textContent='▶ ادامه';}}
function pomReset(){clearInterval(pomInt);pomRun=false;pomSet(pomCurMode,'');}

function notify(msg){if(Notification.permission==='granted')new Notification(msg);else cToast(msg);}
if(Notification.permission==='default')Notification.requestPermission();
</script>
<?php cipher_foot(); ?>
