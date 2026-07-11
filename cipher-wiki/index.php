<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Wiki', '#c084fc');
cipher_navbar('Cipher Wiki', '📖', '../', 'WIKI');

$dir = __DIR__ . '/notes/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

if (isset($_POST['title']) && trim($_POST['title']) !== '') {
    $id = time();
    $data = [
        'id'      => $id,
        'title'   => trim($_POST['title']),
        'content' => trim($_POST['content'] ?? ''),
        'tag'     => trim($_POST['tag'] ?? 'General'),
        'created' => date('Y-m-d H:i'),
    ];
    file_put_contents($dir . $id . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    header('Location: index.php'); exit;
}
if (isset($_GET['del'])) {
    $f = $dir . (int)$_GET['del'] . '.json';
    if (file_exists($f)) unlink($f);
    header('Location: index.php'); exit;
}

$files = glob($dir . '*.json') ?: [];
rsort($files);
$notes = array_map(fn($f) => json_decode(file_get_contents($f), true), $files);
$notes = array_filter($notes);
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📖 Cipher Wiki</div>
    <div class="c-sub">پایگاه دانش و مستندات داخلی سازمان — <?= count($notes) ?> یادداشت ذخیره شده</div>
    <div style="margin-top:12px;"><span class="c-tag">● <?= count($notes) ?> Notes</span></div>
  </div>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start;">
    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div class="c-sec-title" style="margin-bottom:18px;">یادداشت جدید</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
        <div>
          <div class="c-label" style="margin-bottom:5px;">عنوان</div>
          <input type="text" name="title" class="c-input" placeholder="عنوان مستند..." required>
        </div>
        <div>
          <div class="c-label" style="margin-bottom:5px;">برچسب</div>
          <input type="text" name="tag" class="c-input" placeholder="General / Dev / Ops ...">
        </div>
        <div>
          <div class="c-label" style="margin-bottom:5px;">محتوا</div>
          <textarea name="content" class="c-textarea" placeholder="محتوای مستند..."></textarea>
        </div>
        <button type="submit" class="c-btn" style="width:100%;justify-content:center;">+ ذخیره یادداشت</button>
      </form>
    </div>

    <div>
      <?php if (empty($notes)): ?>
      <div class="c-panel"><div class="c-empty">
        <div class="c-empty-icon">📖</div>
        <p>هنوز یادداشتی ثبت نشده است.<br>اولین مستند Cipher Wiki را بنویسید.</p>
      </div></div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($notes as $n): ?>
        <div class="c-panel fade-in-item" style="position:relative;">
          <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <div>
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <span style="font-size:16px;font-weight:700;"><?= htmlspecialchars($n['title']) ?></span>
                <span style="font-size:9px;font-family:var(--mono);padding:3px 8px;border-radius:99px;background:rgba(192,132,252,.1);border:1px solid rgba(192,132,252,.25);color:#c084fc;"><?= htmlspecialchars($n['tag'] ?? 'General') ?></span>
              </div>
              <div style="font-size:10px;color:var(--muted);font-family:var(--mono);"><?= $n['created'] ?? '' ?> · CIPHER WIKI</div>
            </div>
            <a href="?del=<?= $n['id'] ?>" onclick="return confirm('حذف شود؟')"
               style="padding:6px 12px;border-radius:8px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-family:var(--mono);font-size:11px;flex-shrink:0;">✕</a>
          </div>
          <?php if (!empty($n['content'])): ?>
          <div style="font-family:var(--fa);font-size:13.5px;color:var(--muted2);line-height:1.9;white-space:pre-wrap;background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;padding:14px 16px;"><?= htmlspecialchars($n['content']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php cipher_foot(); ?>
