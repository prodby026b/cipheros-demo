<?php session_start(); include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Colors','#f472b6'); cipher_navbar('Cipher Colors','🎨','../','COLORS'); ?>
<div class="c-wrap" style="max-width:900px;">
<div class="c-panel" style="margin-bottom:22px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🎨 Cipher Colors</div>
  <div class="c-sub">انتخاب رنگ، تبدیل فرمت و ساخت پالت رنگی</div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">🎯 Color Picker</div>
  <div style="text-align:center;margin-bottom:20px;">
    <input type="color" id="mainPicker" value="#00eaff" oninput="updateColor(this.value)"
      style="width:100%;height:120px;border:none;border-radius:16px;cursor:pointer;background:none;padding:0;">
  </div>
  <div id="colorInfo" style="display:flex;flex-direction:column;gap:8px;"></div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">📝 وارد کردن رنگ</div>
  <div style="display:flex;gap:8px;margin-bottom:16px;">
    <input id="hexInput" class="c-input" placeholder="#00eaff" style="font-family:var(--mono);flex:1;">
    <button onclick="fromHex()" class="c-btn" style="font-size:12px;padding:11px 16px;">→</button>
  </div>
  <div class="c-sec-title" style="margin-bottom:14px;">🎨 پالت Cipher OS</div>
  <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;" id="palette"></div>
</div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">🔮 تولید پالت هارمونیک</div>
  <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
    <?php foreach(['Complementary','Triadic','Analogous','Shades'] as $m):?>
    <button onclick="genPalette('<?=$m?>')" class="c-btn-ghost" style="font-size:12px;padding:8px 14px;"><?=$m?></button>
    <?php endforeach;?>
  </div>
  <div id="genPalette" style="display:flex;gap:10px;flex-wrap:wrap;"></div>
</div>
</div>
<script>
const cipherPalette=['#00eaff','#7c3aed','#f43f5e','#f59e0b','#34d399','#f472b6','#38bdf8','#a78bfa','#4ade80','#facc15','#fb923c','#2dd4bf'];
document.getElementById('palette').innerHTML = cipherPalette.map(c=>`
  <div onclick="updateColor('${c}')" title="${c}"
    style="height:36px;border-radius:8px;background:${c};cursor:pointer;border:2px solid transparent;transition:.2s;"
    onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'"></div>`).join('');

function hexToRgb(hex){hex=hex.replace('#','');if(hex.length===3)hex=hex.split('').map(c=>c+c).join('');const n=parseInt(hex,16);return[n>>16&255,n>>8&255,n&255];}
function rgbToHsl(r,g,b){r/=255;g/=255;b/=255;const max=Math.max(r,g,b),min=Math.min(r,g,b);let h,s,l=(max+min)/2;if(max===min){h=s=0;}else{const d=max-min;s=l>.5?d/(2-max-min):d/(max+min);switch(max){case r:h=(g-b)/d+(g<b?6:0);break;case g:h=(b-r)/d+2;break;case b:h=(r-g)/d+4;}h/=6;}return[Math.round(h*360),Math.round(s*100),Math.round(l*100)];}
function hslToHex(h,s,l){s/=100;l/=100;const a=s*Math.min(l,1-l);const f=n=>{const k=(n+h/30)%12;return l-a*Math.max(Math.min(k-3,9-k,1),-1);};return '#'+[f(0),f(8),f(4)].map(x=>Math.round(x*255).toString(16).padStart(2,'0')).join('');}

function updateColor(hex){
  if(!/^#[0-9a-fA-F]{3,6}$/.test(hex)) return;
  hex=hex.length===4?'#'+hex.slice(1).split('').map(c=>c+c).join(''):hex;
  document.getElementById('mainPicker').value=hex;
  document.getElementById('hexInput').value=hex;
  const [r,g,b]=hexToRgb(hex);
  const [h,s,l]=rgbToHsl(r,g,b);
  const rows=[
    ['HEX',hex,'copyText("'+hex+'")'],
    ['RGB',`rgb(${r}, ${g}, ${b})`,'copyText("rgb('+r+', '+g+', '+b+')")'],
    ['HSL',`hsl(${h}, ${s}%, ${l}%)`,'copyText("hsl('+h+', '+s+'%, '+l+'%)")'],
    ['Luminance',`${l}%`,''],
  ];
  document.getElementById('colorInfo').innerHTML = rows.map(([k,v,fn])=>`
    <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 12px;background:var(--bg2);border:1px solid var(--stroke);border-radius:10px;">
      <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">${k}</span>
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-family:var(--mono);font-size:12px;color:var(--cyan);">${v}</span>
        ${fn?`<button onclick="${fn}" style="padding:3px 7px;border-radius:6px;background:var(--glass2);border:1px solid var(--stroke);cursor:pointer;font-size:10px;color:var(--muted2);">📋</button>`:''}
      </div>
    </div>`).join('');
  // Preview swatch
  document.querySelector('.c-panel .hud-c.hud-tl') && null;
}

function fromHex(){const v=document.getElementById('hexInput').value.trim();if(v)updateColor(v.startsWith('#')?v:'#'+v);}
document.getElementById('hexInput').addEventListener('keydown',e=>{if(e.key==='Enter')fromHex();});

function genPalette(mode){
  const hex=document.getElementById('mainPicker').value;
  const [r,g,b]=hexToRgb(hex);
  const [h,s,l]=rgbToHsl(r,g,b);
  let colors=[];
  if(mode==='Complementary') colors=[hex,hslToHex((h+180)%360,s,l),hslToHex(h,s,Math.max(20,l-20)),hslToHex((h+180)%360,s,Math.min(80,l+20))];
  else if(mode==='Triadic') colors=[hex,hslToHex((h+120)%360,s,l),hslToHex((h+240)%360,s,l)];
  else if(mode==='Analogous') colors=[hslToHex((h-30+360)%360,s,l),hex,hslToHex((h+30)%360,s,l),hslToHex((h+60)%360,s,l)];
  else colors=[10,20,30,40,50,60,70,80].map(ll=>hslToHex(h,s,ll));
  document.getElementById('genPalette').innerHTML = colors.map(c=>`
    <div onclick="copyText('${c}')" title="${c}" style="display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer;">
      <div style="width:60px;height:60px;border-radius:12px;background:${c};border:2px solid rgba(255,255,255,.1);transition:.2s;"
           onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"></div>
      <span style="font-family:var(--mono);font-size:9px;color:var(--muted);">${c}</span>
    </div>`).join('');
}
function copyText(t){navigator.clipboard.writeText(t);cToast('✅ '+t+' کپی شد');}
updateColor('#00eaff');
</script>
<?php cipher_foot(); ?>
