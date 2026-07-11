<?php session_start(); include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Converter','#fb923c'); cipher_navbar('Cipher Converter','🔄','../','CONVERTER'); ?>
<div class="c-wrap" style="max-width:800px;">
<div class="c-panel" style="margin-bottom:22px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🔄 Cipher Converter</div>
  <div class="c-sub">تبدیل واحدها، ارزها، دماها و انکودینگ فایل‌ها</div>
</div>

<!-- Category tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
  <?php foreach([['length','📏 طول'],['weight','⚖️ وزن'],['temp','🌡️ دما'],['data','💾 داده'],['currency','💵 ارز'],['encode','🔤 Encode']] as $i=>[$k,$l]):?>
  <button onclick="showCat('<?=$k?>')" id="cat_<?=$k?>" class="<?=$i===0?'c-btn':'c-btn-ghost'?>" style="font-size:12px;padding:9px 16px;font-family:var(--fa);"><?=$l?></button>
  <?php endforeach;?>
</div>

<div id="converter_wrap" class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>

  <!-- LENGTH -->
  <div id="cat-length">
    <div class="c-sec-title" style="margin-bottom:16px;">📏 تبدیل طول</div>
    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:12px;align-items:center;">
      <div><input id="l_val" class="c-input" type="number" placeholder="مقدار" oninput="convertLength()">
        <select id="l_from" class="c-input" style="margin-top:8px;" onchange="convertLength()">
          <?php foreach(['m:متر','km:کیلومتر','cm:سانتیمتر','mm:میلیمتر','mi:مایل','ft:فوت','in:اینچ','yd:یارد'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
      <div style="font-size:24px;color:var(--muted);">⇄</div>
      <div><input id="l_res" class="c-input" readonly style="color:var(--cyan);font-family:var(--mono);" placeholder="نتیجه">
        <select id="l_to" class="c-input" style="margin-top:8px;" onchange="convertLength()">
          <?php foreach(['km:کیلومتر','m:متر','cm:سانتیمتر','mm:میلیمتر','mi:مایل','ft:فوت','in:اینچ','yd:یارد'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
    </div>
  </div>

  <!-- WEIGHT -->
  <div id="cat-weight" style="display:none;">
    <div class="c-sec-title" style="margin-bottom:16px;">⚖️ تبدیل وزن</div>
    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:12px;align-items:center;">
      <div><input id="w_val" class="c-input" type="number" placeholder="مقدار" oninput="convertWeight()">
        <select id="w_from" class="c-input" style="margin-top:8px;" onchange="convertWeight()">
          <?php foreach(['kg:کیلوگرم','g:گرم','mg:میلیگرم','lb:پوند','oz:اونس','t:تن'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
      <div style="font-size:24px;color:var(--muted);">⇄</div>
      <div><input id="w_res" class="c-input" readonly style="color:var(--cyan);font-family:var(--mono);" placeholder="نتیجه">
        <select id="w_to" class="c-input" style="margin-top:8px;" onchange="convertWeight()">
          <?php foreach(['lb:پوند','kg:کیلوگرم','g:گرم','mg:میلیگرم','oz:اونس','t:تن'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
    </div>
  </div>

  <!-- TEMP -->
  <div id="cat-temp" style="display:none;">
    <div class="c-sec-title" style="margin-bottom:16px;">🌡️ تبدیل دما</div>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
      <input id="t_val" class="c-input" type="number" placeholder="دما" style="max-width:160px;" oninput="convertTemp()">
      <select id="t_from" class="c-input" style="max-width:140px;" onchange="convertTemp()">
        <option value="C">سلسیوس °C</option><option value="F">فارنهایت °F</option><option value="K">کلوین K</option>
      </select>
      <div style="font-size:20px;color:var(--muted);">⇄</div>
    </div>
    <div id="temp_results" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;"></div>
  </div>

  <!-- DATA -->
  <div id="cat-data" style="display:none;">
    <div class="c-sec-title" style="margin-bottom:16px;">💾 تبدیل داده</div>
    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:12px;align-items:center;">
      <div><input id="d_val" class="c-input" type="number" placeholder="مقدار" oninput="convertData()">
        <select id="d_from" class="c-input" style="margin-top:8px;" onchange="convertData()">
          <?php foreach(['B:بایت','KB:کیلوبایت','MB:مگابایت','GB:گیگابایت','TB:ترابایت'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
      <div style="font-size:24px;color:var(--muted);">⇄</div>
      <div><input id="d_res" class="c-input" readonly style="color:var(--cyan);font-family:var(--mono);" placeholder="نتیجه">
        <select id="d_to" class="c-input" style="margin-top:8px;" onchange="convertData()">
          <?php foreach(['GB:گیگابایت','MB:مگابایت','KB:کیلوبایت','TB:ترابایت','B:بایت'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l</option>";}?>
        </select></div>
    </div>
  </div>

  <!-- CURRENCY -->
  <div id="cat-currency" style="display:none;">
    <div class="c-sec-title" style="margin-bottom:16px;">💵 تبدیل ارز (تقریبی)</div>
    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:12px;align-items:center;">
      <div><input id="c_val" class="c-input" type="number" placeholder="مقدار" oninput="convertCur()">
        <select id="c_from" class="c-input" style="margin-top:8px;" onchange="convertCur()">
          <?php foreach(['USD:دلار آمریکا','EUR:یورو','GBP:پوند','AED:درهم','TRY:لیر','IRR:ریال ایران','TOMAN:تومان'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l ($k)</option>";}?>
        </select></div>
      <div style="font-size:24px;color:var(--muted);">⇄</div>
      <div><input id="c_res" class="c-input" readonly style="color:var(--cyan);font-family:var(--mono);" placeholder="نتیجه">
        <select id="c_to" class="c-input" style="margin-top:8px;" onchange="convertCur()">
          <?php foreach(['TOMAN:تومان','IRR:ریال ایران','USD:دلار','EUR:یورو','GBP:پوند','AED:درهم','TRY:لیر'] as $u){[$k,$l]=explode(':',$u);echo "<option value='$k'>$l ($k)</option>";}?>
        </select></div>
    </div>
    <div style="margin-top:10px;font-family:var(--fa);font-size:11px;color:var(--muted);">* نرخ‌ها تقریبی هستند — برای نرخ دقیق از سایت‌های تخصصی استفاده کنید</div>
  </div>

  <!-- ENCODE -->
  <div id="cat-encode" style="display:none;">
    <div class="c-sec-title" style="margin-bottom:16px;">🔤 Base64 / URL Encode</div>
    <textarea id="enc_input" class="c-textarea" placeholder="متن ورودی..." style="min-height:80px;margin-bottom:12px;"></textarea>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
      <button onclick="doEncode('b64e')" class="c-btn" style="font-size:11px;padding:8px 14px;">Base64 Encode</button>
      <button onclick="doEncode('b64d')" class="c-btn-ghost" style="font-size:11px;padding:8px 14px;">Base64 Decode</button>
      <button onclick="doEncode('urle')" class="c-btn" style="font-size:11px;padding:8px 14px;">URL Encode</button>
      <button onclick="doEncode('urld')" class="c-btn-ghost" style="font-size:11px;padding:8px 14px;">URL Decode</button>
    </div>
    <textarea id="enc_output" class="c-textarea" readonly placeholder="خروجی..." style="min-height:80px;color:var(--cyan);font-family:var(--mono);"></textarea>
    <button onclick="copyText(document.getElementById('enc_output').value)" class="c-btn" style="margin-top:10px;font-size:11px;padding:8px 14px;">📋 کپی</button>
  </div>
</div>
</div>
<script>
function showCat(c){
  ['length','weight','temp','data','currency','encode'].forEach(k=>{
    document.getElementById('cat-'+k).style.display=k===c?'block':'none';
    const b=document.getElementById('cat_'+k);
    b.className=k===c?'c-btn':'c-btn-ghost';
    if(k!==c) b.style.border='1px solid var(--stroke)';
  });
}
const LEN_M={m:1,km:1000,cm:.01,mm:.001,mi:1609.34,ft:.3048,in:.0254,yd:.9144};
function convertLength(){const v=parseFloat(document.getElementById('l_val').value)||0,f=document.getElementById('l_from').value,t=document.getElementById('l_to').value;document.getElementById('l_res').value=fmt(v*LEN_M[f]/LEN_M[t]);}
const W_KG={kg:1,g:.001,mg:.000001,lb:.453592,oz:.0283495,t:1000};
function convertWeight(){const v=parseFloat(document.getElementById('w_val').value)||0,f=document.getElementById('w_from').value,t=document.getElementById('w_to').value;document.getElementById('w_res').value=fmt(v*W_KG[f]/W_KG[t]);}
function convertTemp(){
  const v=parseFloat(document.getElementById('t_val').value)||0,f=document.getElementById('t_from').value;
  let c=f==='C'?v:f==='F'?(v-32)*5/9:v-273.15;
  const results=[['°C',fmt(c),'#00eaff'],['°F',fmt(c*9/5+32),'#f59e0b'],['K',fmt(c+273.15),'#a78bfa']];
  document.getElementById('temp_results').innerHTML=results.map(([u,val,col])=>`<div style="flex:1;min-width:120px;padding:14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;text-align:center;"><div style="font-family:var(--mono);font-size:20px;font-weight:700;color:${col};">${val}</div><div style="font-family:var(--mono);font-size:11px;color:var(--muted);margin-top:4px;">${u}</div></div>`).join('');
}
const DATA_B={B:1,KB:1024,MB:1048576,GB:1073741824,TB:1099511627776};
function convertData(){const v=parseFloat(document.getElementById('d_val').value)||0,f=document.getElementById('d_from').value,t=document.getElementById('d_to').value;document.getElementById('d_res').value=fmt(v*DATA_B[f]/DATA_B[t]);}
const CUR_USD={USD:1,EUR:0.92,GBP:0.79,AED:3.67,TRY:32.5,IRR:600000,TOMAN:60000};
function convertCur(){const v=parseFloat(document.getElementById('c_val').value)||0,f=document.getElementById('c_from').value,t=document.getElementById('c_to').value;document.getElementById('c_res').value=fmt(v/CUR_USD[f]*CUR_USD[t]);}
function fmt(n){return parseFloat(n.toPrecision(8)).toLocaleString('en-US',{maximumFractionDigits:6});}
function doEncode(mode){
  const inp=document.getElementById('enc_input').value;
  let out='';
  try{
    if(mode==='b64e') out=btoa(unescape(encodeURIComponent(inp)));
    else if(mode==='b64d') out=decodeURIComponent(escape(atob(inp)));
    else if(mode==='urle') out=encodeURIComponent(inp);
    else if(mode==='urld') out=decodeURIComponent(inp);
  }catch(e){out='❌ خطا: '+e.message;}
  document.getElementById('enc_output').value=out;
}
function copyText(t){navigator.clipboard.writeText(t);cToast('✅ کپی شد');}
</script>
<?php cipher_foot(); ?>
