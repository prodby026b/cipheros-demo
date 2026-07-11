<?php
session_start();
include '../cipher-core/cipher-theme.php';
$dir=__DIR__.'/data/'; if(!is_dir($dir)) mkdir($dir,0777,true);
if(isset($_POST['create'])){
  $id=substr(md5(uniqid()),0,8);
  $d=['code'=>$_POST['code'],'lang'=>$_POST['lang']??'text','title'=>trim($_POST['title']),'ts'=>time(),'expires'=>(int)($_POST['expires']??0)];
  file_put_contents($dir.$id.'.json',json_encode($d,JSON_UNESCAPED_UNICODE));
  header('Location:index.php?view='.$id);exit;
}
if(isset($_GET['del'])){ @unlink($dir.basename($_GET['del']).'.json'); header('Location:index.php');exit; }
$view=null;
if(isset($_GET['view'])){
  $f=$dir.basename($_GET['view']).'.json';
  if(file_exists($f)){$view=json_decode(file_get_contents($f),true);$view['id']=$_GET['view'];}
}
$files=glob($dir.'*.json')?:[];
$pastes=[];
foreach($files as $f){
  $p=json_decode(file_get_contents($f),true);
  if($p && (!$p['expires']||$p['expires']>time())) $pastes[basename($f,'.json')]=$p;
}
uasort($pastes,fn($a,$b)=>$b['ts']-$a['ts']);
$langs=['text'=>'Plain Text','php'=>'PHP','js'=>'JavaScript','python'=>'Python','html'=>'HTML','css'=>'CSS','bash'=>'Bash','sql'=>'SQL','json'=>'JSON'];
cipher_head('Cipher Paste','#a78bfa');
cipher_navbar('Cipher Paste','📋','../','PASTE');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:24px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📋 Cipher Paste</div>
  <div class="c-sub">اشتراک‌گذاری سریع کد و متن — <?=count($pastes)?> paste ذخیره شده</div>
</div>
<?php if($view):?>
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <div>
      <div style="font-weight:700;font-size:16px;"><?=htmlspecialchars($view['title'])?></div>
      <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:3px;"><?=$langs[$view['lang']]??$view['lang']?> · <?=date('Y-m-d H:i',$view['ts'])?></div>
    </div>
    <div style="display:flex;gap:8px;">
      <button onclick="copyPaste()" class="c-btn" style="font-size:11px;padding:8px 14px;">📋 کپی</button>
      <a href="?del=<?=$view['id']?>" onclick="return confirm('حذف؟')" style="padding:8px 14px;border-radius:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-family:var(--mono);font-size:11px;">✕</a>
    </div>
  </div>
  <pre id="pasteCode" style="background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;padding:20px;overflow-x:auto;font-family:var(--mono);font-size:13px;line-height:1.8;color:var(--text);white-space:pre-wrap;word-break:break-word;"><?=htmlspecialchars($view['code']??'')?></pre>
</div>
<script>function copyPaste(){navigator.clipboard.writeText(document.getElementById('pasteCode').textContent);cToast('✅ کپی شد');}</script>
<?php endif;?>
<div style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">+ Paste جدید</div>
  <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
    <input name="title" class="c-input" placeholder="عنوان (اختیاری)" value="">
    <select name="lang" class="c-input"><?php foreach($langs as $k=>$v) echo "<option value='$k'>$v</option>";?></select>
    <textarea name="code" class="c-textarea" placeholder="کد یا متن را اینجا paste کنید..." style="min-height:160px;font-family:var(--mono);font-size:13px;" required></textarea>
    <button type="submit" name="create" class="c-btn" style="justify-content:center;">+ ذخیره و اشتراک‌گذاری</button>
  </form>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">آخرین Paste‌ها</div>
  <?php if(empty($pastes)):?>
  <div style="text-align:center;padding:30px 0;font-family:var(--fa);color:var(--muted);font-size:13px;">هنوز paste‌ای ندارید.</div>
  <?php else: foreach($pastes as $pid=>$p):?>
  <a href="?view=<?=$pid?>" class="fade-in-item" style="display:block;padding:11px 14px;background:var(--bg2);border:1px solid var(--stroke);border-radius:11px;margin-bottom:8px;transition:.2s;"
     onmouseover="this.style.borderColor='rgba(167,139,250,.4)'" onmouseout="this.style.borderColor='var(--stroke)'">
    <div style="font-size:13px;font-weight:600;margin-bottom:3px;"><?=htmlspecialchars($p['title']?:'Untitled')?></div>
    <div style="font-size:10px;color:var(--muted);font-family:var(--mono);"><?=$langs[$p['lang']]??$p['lang']?> · <?=date('m/d H:i',$p['ts'])?></div>
  </a>
  <?php endforeach;endif;?>
</div>
</div>
</div>
<?php cipher_foot();?>
