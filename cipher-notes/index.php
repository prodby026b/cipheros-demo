<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Notes', '#facc15');
cipher_navbar('Cipher Notes', '🗒️', '../', 'NOTES');
$dir = __DIR__.'/data/'; if(!is_dir($dir)) mkdir($dir,0777,true);
if(isset($_POST['save'])){
  $id = $_POST['id'] ?: uniqid();
  $note=['id'=>$id,'title'=>trim($_POST['title']),'body'=>trim($_POST['body']),'color'=>$_POST['color']??'#facc15','ts'=>time()];
  file_put_contents($dir.$id.'.json',json_encode($note,JSON_UNESCAPED_UNICODE));
  header('Location:index.php');exit;
}
if(isset($_GET['del'])){ @unlink($dir.basename($_GET['del']).'.json'); header('Location:index.php');exit; }
$files=glob($dir.'*.json')?:[];
$notes=array_map(fn($f)=>json_decode(file_get_contents($f),true),$files);
usort($notes,fn($a,$b)=>($b['ts']??0)-($a['ts']??0));
$edit=null;
if(isset($_GET['edit'])){ foreach($notes as $n){ if($n['id']===$_GET['edit']){$edit=$n;break;} } }
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:24px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🗒️ Cipher Notes</div>
  <div class="c-sub">یادداشت‌های رنگی شخصی — <?=count($notes)?> یادداشت ذخیره شده</div>
</div>
<div style="display:grid;grid-template-columns:300px 1fr;gap:22px;align-items:start;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:16px;"><?=$edit?'✏️ ویرایش':'+ یادداشت جدید'?></div>
  <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
    <input type="hidden" name="id" value="<?=htmlspecialchars($edit['id']??'')?>">
    <input name="title" class="c-input" placeholder="عنوان..." value="<?=htmlspecialchars($edit['title']??'')?>" required>
    <textarea name="body" class="c-textarea" placeholder="متن یادداشت..."><?=htmlspecialchars($edit['body']??'')?></textarea>
    <div style="display:flex;align-items:center;gap:10px;font-family:var(--mono);font-size:11px;color:var(--muted);">
      رنگ: <input type="color" name="color" value="<?=htmlspecialchars($edit['color']??'#facc15')?>" style="width:36px;height:28px;border:none;background:none;cursor:pointer;border-radius:6px;">
    </div>
    <button type="submit" name="save" class="c-btn" style="justify-content:center;"><?=$edit?'💾 ذخیره':'+ افزودن'?></button>
    <?php if($edit):?><a href="index.php" class="c-btn-ghost" style="text-align:center;display:block;margin-top:4px;">انصراف</a><?php endif;?>
  </form>
</div>
<div>
<?php if(empty($notes)):?>
<div class="c-panel"><div class="c-empty"><div class="c-empty-icon">🗒️</div><p>هنوز یادداشتی ندارید.</p></div></div>
<?php else:?>
<div style="columns:2;gap:14px;">
<?php foreach($notes as $n):?>
<div class="fade-in-item" style="break-inside:avoid;margin-bottom:14px;background:var(--bg2);border:1px solid var(--stroke);border-top:3px solid <?=htmlspecialchars($n['color']??'#facc15')?>;border-radius:14px;padding:16px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
    <div style="font-weight:600;font-size:14px;"><?=htmlspecialchars($n['title']??'')?></div>
    <div style="display:flex;gap:6px;">
      <a href="?edit=<?=$n['id']?>" style="font-size:11px;padding:4px 8px;border-radius:7px;background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.2);color:#facc15;font-family:var(--mono);">✏️</a>
      <a href="?del=<?=$n['id']?>" onclick="return confirm('حذف؟')" style="font-size:11px;padding:4px 8px;border-radius:7px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-family:var(--mono);">✕</a>
    </div>
  </div>
  <div style="font-family:var(--fa);font-size:13px;color:var(--muted2);line-height:1.75;white-space:pre-wrap;"><?=htmlspecialchars($n['body']??'')?></div>
  <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:10px;"><?=date('Y-m-d H:i',$n['ts']??0)?></div>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
</div>
</div>
</div>
<?php cipher_foot();?>
