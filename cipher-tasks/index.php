<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Tasks', '#4ade80');
cipher_navbar('Cipher Tasks', '✅', '../', 'TASKS');
include '../db.php';

// Ensure priority column exists
@mysqli_query($conn, "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS priority VARCHAR(10) DEFAULT 'normal'");

if (isset($_POST['task']) && trim($_POST['task']) !== '') {
    $title = mysqli_real_escape_string($conn, trim($_POST['task']));
    $prio  = in_array($_POST['priority']??'normal', ['high','normal','low']) ? $_POST['priority'] : 'normal';
    mysqli_query($conn, "INSERT INTO tasks (title, priority) VALUES ('$title', '$prio')");
    header('Location: index.php'); exit;
}
if (isset($_GET['done'])) {
    $id = (int)$_GET['done'];
    mysqli_query($conn, "UPDATE tasks SET status='done' WHERE id=$id");
    header('Location: index.php'); exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header('Location: index.php'); exit;
}
if (isset($_GET['undo'])) {
    $id = (int)$_GET['undo'];
    mysqli_query($conn, "UPDATE tasks SET status='pending' WHERE id=$id");
    header('Location: index.php'); exit;
}

$all = mysqli_query($conn, "SELECT * FROM tasks ORDER BY id DESC");
$tasks = []; while ($r = mysqli_fetch_assoc($all)) $tasks[] = $r;
$done   = array_filter($tasks, fn($t) => $t['status'] === 'done');
$active = array_filter($tasks, fn($t) => $t['status'] !== 'done');
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">✅ Cipher Tasks</div>
    <div class="c-sub">مدیریت وظایف تیمی — <?= count($active) ?> فعال / <?= count($done) ?> انجام شده</div>
    <div style="margin-top:12px;display:flex;gap:10px;">
      <span class="c-tag">● <?= count($active) ?> Active</span>
      <span class="c-tag" style="background:rgba(100,116,139,.1);border-color:rgba(100,116,139,.25);color:var(--muted2);">✓ <?= count($done) ?> Done</span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start;">
    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div class="c-sec-title" style="margin-bottom:18px;">افزودن وظیفه</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
        <div>
          <div class="c-label" style="margin-bottom:5px;">عنوان تسک</div>
          <input type="text" name="task" class="c-input" placeholder="وظیفه جدید..." required>
        </div>
        <div>
          <div class="c-label" style="margin-bottom:5px;">اولویت</div>
          <select name="priority" class="c-input">
            <option value="high">🔴 بالا</option>
            <option value="normal" selected>🟡 معمولی</option>
            <option value="low">🟢 پایین</option>
          </select>
        </div>
        <button type="submit" class="c-btn" style="width:100%;justify-content:center;">+ افزودن</button>
      </form>
    </div>

    <div>
      <div class="c-panel" style="margin-bottom:18px;">
        <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
        <div class="c-sec"><span class="c-sec-title">وظایف فعال</span><span class="c-tag">● <?= count($active) ?></span></div>
        <?php if (empty($active)): ?>
        <div class="c-empty"><div class="c-empty-icon" style="font-size:32px;">🎉</div><p>همه وظایف انجام شده!</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach ($active as $t):
            $p = $t['priority'] ?? 'normal';
            $pcol = ['high'=>'#ef4444','normal'=>'#f59e0b','low'=>'#4ade80'][$p] ?? '#f59e0b';
            $pico = ['high'=>'🔴','normal'=>'🟡','low'=>'🟢'][$p] ?? '🟡';
          ?>
          <div class="fade-in-item" style="display:flex;align-items:center;gap:12px;padding:14px 16px;
               background:var(--bg2);border:1px solid var(--stroke);border-right:3px solid <?= $pcol ?>;border-radius:12px;transition:.2s;"
               onmouseover="this.style.borderColor='rgba(74,222,128,.25)';this.style.borderRightColor='<?= $pcol ?>'"
               onmouseout="this.style.borderColor='var(--stroke)';this.style.borderRightColor='<?= $pcol ?>'">
            <div style="flex:1;font-size:13px;font-weight:500;"><?= htmlspecialchars($t['title']) ?></div>
            <span style="font-size:12px;"><?= $pico ?></span>
            <a href="?done=<?= $t['id'] ?>" style="padding:6px 12px;border-radius:8px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);color:#4ade80;font-family:var(--mono);font-size:11px;">✓</a>
            <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('حذف شود؟')" style="padding:6px 12px;border-radius:8px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-family:var(--mono);font-size:11px;">✕</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($done)): ?>
      <div class="c-panel">
        <div class="c-sec"><span class="c-sec-title" style="color:var(--muted2);">انجام شده</span></div>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <?php foreach ($done as $t): ?>
          <div class="fade-in-item" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid var(--stroke);border-radius:10px;opacity:.6;">
            <div style="flex:1;font-size:12px;text-decoration:line-through;color:var(--muted);"><?= htmlspecialchars($t['title']) ?></div>
            <a href="?undo=<?= $t['id'] ?>" style="font-size:11px;color:var(--muted);font-family:var(--mono);">↩</a>
            <a href="?delete=<?= $t['id'] ?>" style="font-size:11px;color:var(--danger);font-family:var(--mono);">✕</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php cipher_foot(); ?>
