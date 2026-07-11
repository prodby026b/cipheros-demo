<?php
session_start();
include '../cipher-core/cipher-theme.php';
$dir=__DIR__.'/data/'; if(!is_dir($dir)) mkdir($dir,0777,true);
$dbf=$dir.'links.json';
$links=file_exists($dbf)?json_decode(file_get_contents($dbf),true):[];
if(isset($_POST['add'])&&trim($_POST['url'])!==''){
  $url=trim($_POST['url']); if(!preg_match('/^https?:\/\//',$url)) $url='https://'.$url;
  $links[]=[ 'id'=>uniqid(),'url'=>$url,'title'=>trim($_POST['title'])?:$url,'category'=>trim($_POST['cat'])?:'General','ts'=>time()];
  file_put_contents($dbf,json_encode($links,JSON_UNESCAPED_UNICODE));
  header('Location:index.php');exit;
}
if(isset($_GET['del'])){ $links=array_filter($links,fn($l)=>$l['id']!==$_GET['del']); file_put_contents($dbf,json_encode(array_values($links),JSON_UNESCAPED_UNICODE)); header('Location:index.php');exit; }
$cats=array_unique(array_column($links,'category'));
cipher_head('Cipher Links','#38bdf8');
cipher_navbar('Cipher Links','🔗','../','LINKS');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:24px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🔗 Cipher Links</div>
  <div class="c-sub">مدیریت لینک‌های مهم — <?=count($links)?> لینک · <?=count($cats)?> دسته‌بندی</div>
</div>
<div style="display:grid;grid-template-columns:280px 1fr;gap:22px;align-items:start;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">+ لینک جدید</div>
  <form method="POST" style="display:flex;flex-direction:column;gap:11px;">
    <input name="url" class="c-input" placeholder="https://..." required>
    <input name="title" class="c-input" placeholder="عنوان لینک">
    <input name="cat" class="c-input" placeholder="دسته‌بندی (مثل: Dev, Design)">
    <button type="submit" name="add" class="c-btn" style="justify-content:center;">+ افزودن</button>
  </form>
</div>
<div>
<?php
$byCat=[];
foreach($links as $l) $byCat[$l['category']][]=$l;
foreach($byCat as $cat=>$ls):
  rsort($ls);
?>
<div class="c-panel fade-in-item" style="margin-bottom:16px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">📁 <?=htmlspecialchars($cat)?> <span style="font-size:11px;color:var(--muted);font-family:var(--mono);">(<?=count($ls)?>)</span></div>
  <div style="display:flex;flex-direction:column;gap:8px;">
  <?php foreach($ls as $l):?>
  <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:11px;">
    <img src="https://www.google.com/s2/favicons?sz=16&domain=<?=urlencode($l['url'])?>" width="16" height="16" style="opacity:.7;flex-shrink:0;" onerror="this.style.display='none'">
    <div style="flex:1;min-width:0;">
      <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($l['title'])?></div>
      <div style="font-size:10px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:var(--mono);"><?=htmlspecialchars($l['url'])?></div>
    </div>
    <a href="<?=htmlspecialchars($l['url'])?>" target="_blank" style="padding:6px 11px;border-radius:8px;background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);color:#38bdf8;font-family:var(--mono);font-size:11px;flex-shrink:0;">→</a>
    <a href="?del=<?=$l['id']?>" onclick="return confirm('حذف؟')" style="padding:6px 11px;border-radius:8px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);color:#ef4444;font-family:var(--mono);font-size:11px;flex-shrink:0;">✕</a>
  </div>
  <?php endforeach;?>
  </div>
</div>
<?php endforeach;?>
<?php if(empty($links)):?>
<div class="c-panel"><div class="c-empty"><div class="c-empty-icon">🔗</div><p>هنوز لینکی ندارید.<br>اولین لینک خود را اضافه کنید.</p></div></div>
<?php endif;?>
</div>
</div>
</div>
<?php cipher_foot();?>
