<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Calc','#34d399');
cipher_navbar('Cipher Calc','🧮','../','CALC');
?>
<div class="c-wrap" style="max-width:500px;">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🧮 Cipher Calc</div>
  <div class="c-sub">ماشین حساب پیشرفته Cipher OS</div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div id="expr" style="font-family:var(--mono);font-size:12px;color:var(--muted);min-height:20px;margin-bottom:4px;text-align:right;padding:0 4px;"></div>
  <div id="display" style="font-family:var(--mono);font-size:38px;font-weight:700;text-align:right;color:var(--cyan);margin-bottom:20px;padding:0 4px;min-height:52px;word-break:break-all;">0</div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
<?php
$btns=[
  ['AC','all',null,'#ef4444'],['±','sign',null,'#8899b8'],['%','pct',null,'#8899b8'],['÷','op','/',null],
  ['7','num','7',null],['8','num','8',null],['9','num','9',null],['×','op','*',null],
  ['4','num','4',null],['5','num','5',null],['6','num','6',null],['−','op','-',null],
  ['1','num','1',null],['2','num','2',null],['3','num','3',null],['+','op','+',null],
  ['0','num','0',null],['·','dot','.',null],['⌫','del',null,'#f59e0b'],['=','eq',null,'#00eaff'],
];
foreach($btns as $b){
  $col=$b[3]??"rgba(255,255,255,.06)";
  $tc=($col==='#00eaff')?'#03040d':($col==='#ef4444'?'#fff':($col==='#f59e0b'?'#fff':'var(--text)'));
  $bg=in_array($col,['#00eaff','#ef4444','#f59e0b'])?$col:"rgba(255,255,255,.06)";
  echo "<button onclick=\"calc('{$b[1]}','{$b[2]}')\" style=\"padding:16px 0;border:1px solid var(--stroke);border-radius:12px;background:{$bg};color:{$tc};font-family:var(--mono);font-size:18px;font-weight:600;cursor:pointer;transition:.15s;\" onmouseover=\"this.style.opacity='.8'\" onmouseout=\"this.style.opacity='1'\">{$b[0]}</button>\n";
}
?>
  </div>
  <div style="margin-top:16px;border-top:1px solid var(--stroke);padding-top:14px;">
    <div class="c-label" style="margin-bottom:10px;">تبدیل سریع</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <button onclick="convertMode('usd')" class="c-btn-ghost" style="font-size:11px;padding:7px 12px;">💵 USD → IRR</button>
      <button onclick="convertMode('km')" class="c-btn-ghost" style="font-size:11px;padding:7px 12px;">📏 km → mi</button>
      <button onclick="convertMode('kg')" class="c-btn-ghost" style="font-size:11px;padding:7px 12px;">⚖️ kg → lb</button>
    </div>
    <div id="convertResult" style="margin-top:10px;font-family:var(--mono);font-size:13px;color:var(--success);display:none;"></div>
  </div>
</div>
</div>
<script>
let cur='0',prev='',op='',justEq=false;
const disp=document.getElementById('display');
const expr=document.getElementById('expr');
function update(){disp.textContent=cur;}
function calc(type,val){
  if(type==='num'){
    if(justEq){cur='0';prev='';op='';justEq=false;}
    cur=(cur==='0'&&val!=='.')?val:(cur.length<16?cur+val:cur);
  }else if(type==='dot'){
    if(!cur.includes('.'))cur+='.';
  }else if(type==='op'){
    if(op&&!justEq){
      prev=String(eval(prev+op+cur));cur=prev;
    } else { prev=cur; }
    op=val; justEq=false;
    expr.textContent=prev+' '+(val==='*'?'×':val==='/'?'÷':val);
  }else if(type==='eq'){
    if(!op) return;
    try{
      const r=eval(prev+op+cur);
      expr.textContent=prev+' '+(op==='*'?'×':op==='/'?'÷':op)+' '+cur+' =';
      cur=String(Math.round(r*1e10)/1e10);
      prev='';op='';justEq=true;
    }catch(e){cur='Error';}
  }else if(type==='all'){cur='0';prev='';op='';justEq=false;expr.textContent='';}
  else if(type==='del'){cur=cur.length>1?cur.slice(0,-1):'0';}
  else if(type==='sign'){cur=String(-parseFloat(cur));}
  else if(type==='pct'){cur=String(parseFloat(cur)/100);}
  update();
}
const rates={usd:60000,km:0.621371,kg:2.20462};
function convertMode(m){
  const n=parseFloat(cur)||0;
  const res=document.getElementById('convertResult');
  res.style.display='block';
  if(m==='usd') res.textContent=`${n} USD = ${(n*rates.usd).toLocaleString()} تومان`;
  else if(m==='km') res.textContent=`${n} km = ${(n*rates.km).toFixed(3)} mile`;
  else if(m==='kg') res.textContent=`${n} kg = ${(n*rates.kg).toFixed(3)} lb`;
}
document.addEventListener('keydown',e=>{
  const k=e.key;
  if(k>='0'&&k<='9') calc('num',k);
  else if(k==='.') calc('dot','.');
  else if(k==='+') calc('op','+');
  else if(k==='-') calc('op','-');
  else if(k==='*') calc('op','*');
  else if(k==='/') {e.preventDefault();calc('op','/');}
  else if(k==='Enter'||k==='=') calc('eq','');
  else if(k==='Backspace') calc('del','');
  else if(k==='Escape') calc('all','');
});
</script>
<?php cipher_foot();?>
