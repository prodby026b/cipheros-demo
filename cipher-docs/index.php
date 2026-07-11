<?php session_start(); include '../cipher-core/cipher-theme.php';
$dir=__DIR__.'/files/'; if(!is_dir($dir)) mkdir($dir,0777,true);
if(isset($_FILES['doc'])&&$_FILES['doc']['error']===0){
  $name=preg_replace('/[^a-zA-Z0-9._-]/','_',$_FILES['doc']['name']);
  move_uploaded_file($_FILES['doc']['tmp_name'],$dir.$name);
  header('Location:index.php');exit;
}
if(isset($_GET['del'])){ $f=$dir.basename($_GET['del']); if(file_exists($f))unlink($f); header('Location:index.php');exit; }
$files=glob($dir.'*')?:[]; $files=array_filter($files,'is_file');
$total=array_sum(array_map('filesize',$files));
function fmtSize($b){if($b<1024)return $b.'B';if($b<1048576)return round($b/1024,1).'KB';return round($b/1048576,1).'MB';}
$exIcons=['pdf'=>'📄','doc'=>'📝','docx'=>'📝','xls'=>'📊','xlsx'=>'📊','png'=>'🖼️','jpg'=>'🖼️','jpeg'=>'🖼️','gif'=>'🖼️','zip'=>'📦','rar'=>'📦','mp4'=>'🎬','mp3'=>'🎵','txt'=>'📃','php'=>'🐘','js'=>'⚡','css'=>'🎨','html'=>'🌐','json'=>'{}'];
cipher_head('Cipher Docs','#38bdf8'); cipher_navbar('Cipher Docs','📂','../','DOCS'); ?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📂 Cipher Docs</div>
  <div class="c-sub">مدیریت و ذخیره فایل‌ها و مستندات · <?=count($files)?> فایل · <?=fmtSize($total)?> مصرف شده</div>
</div>
<div style="display:grid;grid-template-columns:280px 1fr;gap:22px;align-items:start;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;">⬆️ آپلود فایل</div>
  <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;">
    <div style="border:2px dashed var(--stroke);border-radius:14px;padding:30px 20px;text-align:center;cursor:pointer;transition:.2s;"
         onclick="document.getElementById('fileIn').click()"
         onmouseover="this.style.borderColor='rgba(56,189,248,.4)'" onmouseout="this.style.borderColor='var(--stroke)'">
      <div style="font-size:36px;margin-bottom:8px;">⬆️</div>
      <div style="font-family:var(--fa);color:var(--muted2);font-size:13px;">کلیک کنید یا فایل را بکشید</div>
      <div id="fileName" style="font-family:var(--mono);font-size:11px;color:var(--cyan);margin-top:8px;"></div>
    </div>
    <input type="file" id="fileIn" name="doc" style="display:none" onchange="document.getElementById('fileName').textContent=this.files[0].name">
    <button type="submit" class="c-btn" style="justify-content:center;">⬆️ آپلود</button>
  </form>
</div>
<div>
<?php if(empty($files)):?>
<div class="c-panel"><div class="c-empty"><div class="c-empty-icon">📂</div><p>هنوز فایلی آپلود نشده.</p></div></div>
<?php else:?>
<div style="display:flex;flex-direction:column;gap:8px;">
<?php foreach($files as $f):
  $name=basename($f); $ext=strtolower(pathinfo($f,PATHINFO_EXTENSION));
  $ic=$exIcons[$ext]??'📄'; $sz=fmtSize(filesize($f)); $mod=date('Y-m-d H:i',filemtime($f));
?>
<div class="fade-in-item" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;transition:.2s;"
     onmouseover="this.style.borderColor='rgba(56,189,248,.3)'" onmouseout="this.style.borderColor='var(--stroke)'">
  <div style="font-size:26px;flex-shrink:0;"><?=$ic?></div>
  <div style="flex:1;min-width:0;">
    <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($name)?></div>
    <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:2px;"><?=$sz?> · <?=$mod?></div>
  </div>
  <a href="files/<?=urlencode($name)?>" download style="padding:7px 12px;border-radius:8px;background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);color:#38bdf8;font-family:var(--mono);font-size:11px;flex-shrink:0;">⬇️</a>
  <a href="?del=<?=urlencode($name)?>" onclick="return confirm('حذف؟')" style="padding:7px 12px;border-radius:8px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);color:#ef4444;font-family:var(--mono);font-size:11px;flex-shrink:0;">✕</a>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
</div>
</div>
</div>
<?php cipher_foot(); ?>
