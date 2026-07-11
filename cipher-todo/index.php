<?php session_start(); include '../cipher-core/cipher-theme.php';
$dir=__DIR__.'/data/'; if(!is_dir($dir)) mkdir($dir,0777,true);
$dbf=$dir.'todos.json';
$todos=file_exists($dbf)?json_decode(file_get_contents($dbf),true):[];
if(isset($_POST['add'])&&trim($_POST['text'])!==''){
  $todos[]=[ 'id'=>uniqid(),'text'=>trim($_POST['text']),'done'=>false,'priority'=>$_POST['priority']??'normal','ts'=>time()];
  file_put_contents($dbf,json_encode($todos,JSON_UNESCAPED_UNICODE)); header('Location:index.php');exit;
}
if(isset($_GET['toggle'])){ foreach($todos as &$t){if($t['id']===$_GET['toggle'])$t['done']=!$t['done'];} unset($t); file_put_contents($dbf,json_encode($todos,JSON_UNESCAPED_UNICODE)); header('Location:index.php');exit; }
if(isset($_GET['del'])){ $todos=array_values(array_filter($todos,fn($t)=>$t['id']!==$_GET['del'])); file_put_contents($dbf,json_encode($todos,JSON_UNESCAPED_UNICODE)); header('Location:index.php');exit; }
$active=array_filter($todos,fn($t)=>!$t['done']); $done=array_filter($todos,fn($t)=>$t['done']);
cipher_head('Cipher Todo','#4ade80'); cipher_navbar('Cipher Todo','📝','../','TODO'); ?>
<div class="c-wrap" style="max-width:700px;">
<div class="c-panel" style="margin-bottom:22px;"><div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">📝 Cipher Todo</div>
  <div class="c-sub">لیست کارهای روزانه · <?=count($active)?> باقی‌مانده / <?=count($done)?> انجام شده</div>
  <div style="margin-top:10px;display:flex;gap:8px;">
    <span class="c-tag">● <?=count($active)?> Active</span>
    <div style="flex:1;max-width:200px;height:6px;background:rgba(255,255,255,.07);border-radius:10px;align-self:center;overflow:hidden;">
      <div style="height:100%;width:<?=count($todos)>0?round(count($done)/count($todos)*100):0?>%;background:linear-gradient(90deg,#4ade80,#00eaff);border-radius:10px;transition:width 1s;"></div>
    </div>
  </div>
</div>
<form method="POST" style="display:flex;gap:10px;margin-bottom:20px;">
  <select name="priority" class="c-input" style="max-width:120px;">
    <option value="high">🔴 بالا</option><option value="normal" selected>🟡 معمولی</option><option value="low">🟢 پایین</option>
  </select>
  <input name="text" class="c-input" placeholder="کار جدید اضافه کنید..." required autofocus style="flex:1;">
  <button type="submit" name="add" class="c-btn">+ افزودن</button>
</form>
<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
<?php $pOrder=['high'=>0,'normal'=>1,'low'=>2]; usort($active,fn($a,$b)=>($pOrder[$a['priority']??'normal']??1)-($pOrder[$b['priority']??'normal']??1));
foreach($active as $t): $pc=['high'=>'#ef4444','normal'=>'#f59e0b','low'=>'#4ade80'][$t['priority']??'normal']??'#f59e0b';?>
<div class="fade-in-item" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--bg2);border:1px solid var(--stroke);border-right:3px solid <?=$pc?>;border-radius:12px;transition:.2s;"
     onmouseover="this.style.borderColor='rgba(74,222,128,.3)';this.style.borderRightColor='<?=$pc?>'" onmouseout="this.style.borderColor='var(--stroke)';this.style.borderRightColor='<?=$pc?>'">
  <a href="?toggle=<?=$t['id']?>" style="width:22px;height:22px;border-radius:6px;border:2px solid var(--stroke);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.2s;"
     onmouseover="this.style.borderColor='#4ade80'" onmouseout="this.style.borderColor='var(--stroke)'">
  </a>
  <div style="flex:1;font-size:14px;font-weight:500;"><?=htmlspecialchars($t['text'])?></div>
  <span style="font-size:13px;"><?=['high'=>'🔴','normal'=>'🟡','low'=>'🟢'][$t['priority']??'normal']?></span>
  <a href="?del=<?=$t['id']?>" style="font-size:11px;padding:5px 9px;border-radius:7px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);color:#ef4444;font-family:var(--mono);">✕</a>
</div>
<?php endforeach; if(empty($active)):?>
<div class="c-panel"><div class="c-empty"><div class="c-empty-icon" style="font-size:36px;">🎉</div><p>همه کارها انجام شده!</p></div></div>
<?php endif; if(!empty($done)):?>
<div style="border-top:1px solid var(--stroke);padding-top:14px;margin-top:6px;">
  <div class="c-label" style="margin-bottom:10px;">✅ انجام شده (<?=count($done)?>)</div>
  <?php foreach($done as $t):?>
  <div class="fade-in-item" style="display:flex;align-items:center;gap:12px;padding:10px 18px;background:rgba(255,255,255,.02);border:1px solid var(--stroke);border-radius:10px;margin-bottom:6px;opacity:.55;">
    <a href="?toggle=<?=$t['id']?>" style="width:22px;height:22px;border-radius:6px;background:#4ade8022;border:2px solid #4ade8044;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;color:#4ade80;">✓</a>
    <div style="flex:1;font-size:13px;text-decoration:line-through;color:var(--muted);"><?=htmlspecialchars($t['text'])?></div>
    <a href="?del=<?=$t['id']?>" style="font-size:10px;padding:4px 8px;border-radius:6px;color:#ef4444;font-family:var(--mono);">✕</a>
  </div>
  <?php endforeach;endif;?>
</div>
</div>
<?php cipher_foot(); ?>
