<?php session_start(); include '../cipher-core/cipher-theme.php';
cipher_head('Cipher JSON','#facc15'); cipher_navbar('Cipher JSON','{}','../','JSON'); ?>
<div class="c-wrap" style="max-width:1000px;">
<div class="c-panel" style="margin-bottom:20px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">{} Cipher JSON</div>
  <div class="c-sub">فرمت، اعتبارسنجی و مقایسه JSON — JSON Toolkit</div>
</div>
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <button onclick="formatJSON()" class="c-btn" style="font-size:12px;padding:9px 16px;">✨ Format</button>
  <button onclick="minifyJSON()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">⚡ Minify</button>
  <button onclick="validateJSON()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">✅ Validate</button>
  <button onclick="json2csv()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">📊 JSON→CSV</button>
  <button onclick="sortKeys()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">🔤 Sort Keys</button>
  <button onclick="copyOut()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">📋 کپی</button>
  <button onclick="clearAll()" class="c-btn-ghost" style="font-size:12px;padding:9px 16px;">🗑️ پاک</button>
</div>
<div id="statusBar" style="padding:10px 16px;border-radius:10px;background:rgba(0,255,153,.07);border:1px solid rgba(0,255,153,.2);color:var(--success);font-family:var(--mono);font-size:12px;margin-bottom:14px;display:none;"></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;height:calc(100vh - 340px);min-height:400px;">
  <div style="display:flex;flex-direction:column;">
    <div class="c-label" style="margin-bottom:6px;">JSON INPUT</div>
    <textarea id="jsonIn" style="flex:1;background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;color:var(--text);font-family:var(--mono);font-size:13px;padding:16px;outline:none;resize:none;line-height:1.7;transition:.2s;" placeholder='{"key": "value"}'></textarea>
  </div>
  <div style="display:flex;flex-direction:column;">
    <div class="c-label" style="margin-bottom:6px;">OUTPUT</div>
    <div id="jsonOut" style="flex:1;background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;color:var(--cyan);font-family:var(--mono);font-size:13px;padding:16px;overflow:auto;white-space:pre;line-height:1.7;"></div>
  </div>
</div>
</div>
<script>
function getIn(){return document.getElementById('jsonIn').value.trim();}
function setOut(t){document.getElementById('jsonOut').textContent=t;}
function showStatus(msg,ok=true){const s=document.getElementById('statusBar');s.style.display='block';s.style.background=ok?'rgba(0,255,153,.07)':'rgba(239,68,68,.07)';s.style.borderColor=ok?'rgba(0,255,153,.2)':'rgba(239,68,68,.2)';s.style.color=ok?'var(--success)':'var(--danger)';s.textContent=msg;}
function formatJSON(){try{const j=JSON.parse(getIn());setOut(JSON.stringify(j,null,2));showStatus('✅ JSON معتبر — فرمت شد');}catch(e){showStatus('❌ '+e.message,false);}}
function minifyJSON(){try{const j=JSON.parse(getIn());setOut(JSON.stringify(j));showStatus('✅ Minify شد — '+JSON.stringify(j).length+' char');}catch(e){showStatus('❌ '+e.message,false);}}
function validateJSON(){try{const j=JSON.parse(getIn());const keys=Object.keys(j).length;showStatus('✅ JSON معتبر — '+keys+' key در root');}catch(e){showStatus('❌ خطا در line '+getLineOfError(e.message)+': '+e.message,false);}}
function getLineOfError(msg){const m=msg.match(/position (\d+)/);if(!m)return '?';const pos=parseInt(m[1]);return getIn().substring(0,pos).split('\n').length;}
function json2csv(){
  try{
    const j=JSON.parse(getIn());
    if(!Array.isArray(j)){showStatus('❌ برای CSV نیاز به آرایه JSON است',false);return;}
    const keys=Object.keys(j[0]||{});
    const rows=[keys.join(','),...j.map(r=>keys.map(k=>JSON.stringify(r[k]??'')).join(','))];
    setOut(rows.join('\n'));showStatus('✅ CSV ساخته شد — '+j.length+' ردیف');
  }catch(e){showStatus('❌ '+e.message,false);}
}
function sortKeys(){try{const j=JSON.parse(getIn());setOut(JSON.stringify(sortObj(j),null,2));showStatus('✅ کلیدها sort شدند');}catch(e){showStatus('❌ '+e.message,false);}}
function sortObj(o){if(typeof o!=='object'||!o)return o;if(Array.isArray(o))return o.map(sortObj);return Object.fromEntries(Object.keys(o).sort().map(k=>[k,sortObj(o[k])]));}
function copyOut(){navigator.clipboard.writeText(document.getElementById('jsonOut').textContent);cToast('✅ کپی شد');}
function clearAll(){document.getElementById('jsonIn').value='';document.getElementById('jsonOut').textContent='';document.getElementById('statusBar').style.display='none';}
// Sample
document.getElementById('jsonIn').value='{\n  "project": "Cipher OS",\n  "author": "prodby026b",\n  "version": "3.0",\n  "services": ["AI","Chat","Cloud","Media"],\n  "status": "operational"\n}';
</script>
<?php cipher_foot(); ?>
