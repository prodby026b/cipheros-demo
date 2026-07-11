<?php session_start(); include '../cipher-core/cipher-theme.php';
cipher_head('Cipher MD','#c084fc'); cipher_navbar('Cipher MD','✍️','../','MARKDOWN'); ?>
<div class="c-wrap" style="padding-bottom:40px;">
<div class="c-panel" style="margin-bottom:20px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">✍️ Cipher MD</div>
  <div class="c-sub">ویرایشگر Markdown پیشرفته با پیش‌نمایش زنده — Cipher OS</div>
</div>
<div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
  <?php foreach(['**Bold**'=>'B','*Italic*'=>'I','`Code`'=>'</>','# Heading'=>'H','[Link](url)'=>'🔗','> Quote'=>'❝','- List'=>'☰','---'=>'─'] as $md=>$label):?>
  <button onclick="insertMD('<?=addslashes($md)?>')" class="c-btn-ghost" style="font-family:var(--mono);font-size:12px;padding:6px 12px;font-weight:600;"><?=$label?></button>
  <?php endforeach;?>
  <div style="margin-right:auto;display:flex;gap:8px;">
    <button onclick="clearAll()" class="c-btn-ghost" style="font-size:12px;padding:6px 12px;">🗑️ پاک</button>
    <button onclick="copyHTML()" class="c-btn-ghost" style="font-size:12px;padding:6px 12px;">📋 HTML</button>
    <button onclick="downloadMD()" class="c-btn" style="font-size:12px;padding:6px 14px;">⬇️ .md</button>
  </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;height:calc(100vh - 320px);min-height:400px;">
  <div style="display:flex;flex-direction:column;">
    <div class="c-label" style="margin-bottom:6px;">MARKDOWN INPUT</div>
    <textarea id="mdInput" style="flex:1;background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;color:var(--text);font-family:var(--mono);font-size:13px;padding:16px;outline:none;resize:none;line-height:1.7;transition:.2s;" placeholder="# سلام Cipher OS&#10;&#10;متن Markdown خود را بنویسید..." oninput="renderMD()"></textarea>
  </div>
  <div style="display:flex;flex-direction:column;">
    <div class="c-label" style="margin-bottom:6px;">LIVE PREVIEW</div>
    <div id="mdPreview" style="flex:1;background:var(--bg2);border:1px solid var(--stroke);border-radius:14px;padding:16px;overflow-y:auto;line-height:1.8;font-family:var(--fa);"></div>
  </div>
</div>
</div>
<style>
#mdPreview h1{font-size:24px;font-weight:700;color:var(--cyan);margin:0 0 12px;border-bottom:1px solid var(--stroke);padding-bottom:8px;}
#mdPreview h2{font-size:20px;font-weight:700;color:var(--text);margin:16px 0 8px;}
#mdPreview h3{font-size:16px;font-weight:600;color:var(--muted2);margin:12px 0 6px;}
#mdPreview p{margin-bottom:10px;font-size:14px;}
#mdPreview code{background:var(--bg3);border:1px solid var(--stroke);border-radius:6px;padding:2px 6px;font-family:var(--mono);font-size:12px;color:#f472b6;}
#mdPreview pre{background:var(--bg3);border:1px solid var(--stroke);border-radius:10px;padding:14px;overflow-x:auto;margin:12px 0;}
#mdPreview pre code{background:none;border:none;padding:0;font-size:13px;color:var(--text);}
#mdPreview blockquote{border-right:3px solid var(--cyan);padding:10px 16px;background:rgba(0,234,255,.04);border-radius:0 10px 10px 0;margin:12px 0;color:var(--muted2);}
#mdPreview ul,#mdPreview ol{padding-right:20px;margin:8px 0;}
#mdPreview li{margin-bottom:4px;font-size:14px;}
#mdPreview hr{border:none;border-top:1px solid var(--stroke);margin:16px 0;}
#mdPreview a{color:var(--cyan);text-decoration:underline;}
#mdPreview strong{color:var(--text);font-weight:700;}
#mdPreview em{color:var(--muted2);}
#mdInput:focus{border-color:rgba(192,132,252,.4);box-shadow:0 0 0 3px rgba(192,132,252,.08);}
</style>
<script>
function renderMD(){
  let md=document.getElementById('mdInput').value;
  // Simple MD parser
  md=md.replace(/^# (.+)$/gm,'<h1>$1</h1>').replace(/^## (.+)$/gm,'<h2>$1</h2>').replace(/^### (.+)$/gm,'<h3>$1</h3>');
  md=md.replace(/```([\s\S]*?)```/g,'<pre><code>$1</code></pre>');
  md=md.replace(/`([^`]+)`/g,'<code>$1</code>');
  md=md.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\*(.+?)\*/g,'<em>$1</em>');
  md=md.replace(/\[(.+?)\]\((.+?)\)/g,'<a href="$2" target="_blank">$1</a>');
  md=md.replace(/^> (.+)$/gm,'<blockquote>$1</blockquote>');
  md=md.replace(/^- (.+)$/gm,'<li>$1</li>').replace(/(<li>[\s\S]*?<\/li>)/g,'<ul>$1</ul>');
  md=md.replace(/^---$/gm,'<hr>');
  md=md.replace(/\n\n/g,'</p><p>').replace(/^(?!<[hupb])(.+)$/gm,'<p>$1</p>');
  document.getElementById('mdPreview').innerHTML=md||'<span style="color:var(--muted);font-size:13px;">پیش‌نمایش اینجا نمایش داده می‌شود...</span>';
}
function insertMD(t){const el=document.getElementById('mdInput');const s=el.selectionStart,e=el.selectionEnd;el.value=el.value.slice(0,s)+'\n'+t+'\n'+el.value.slice(e);el.focus();renderMD();}
function clearAll(){if(confirm('پاک شود؟')){document.getElementById('mdInput').value='';renderMD();}}
function copyHTML(){navigator.clipboard.writeText(document.getElementById('mdPreview').innerHTML);cToast('✅ HTML کپی شد');}
function downloadMD(){const b=new Blob([document.getElementById('mdInput').value],{type:'text/markdown'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='cipher-note.md';a.click();}
// Default content
document.getElementById('mdInput').value=`# خوش آمدید به Cipher MD\n\n**Cipher OS** ویرایشگر Markdown پیشرفته شما.\n\n## ویژگی‌ها\n\n- پیش‌نمایش زنده\n- ابزارهای سریع\n- دانلود فایل \`.md\`\n\n## مثال کد\n\n\`\`\`php\n<?php\necho "Cipher OS — PRODBY026B";\n\`\`\`\n\n> *قدرت در سادگی است.*`;
renderMD();
</script>
<?php cipher_foot(); ?>
