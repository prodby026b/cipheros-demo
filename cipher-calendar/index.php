<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Calendar', '#facc15');
cipher_navbar('Cipher Calendar', '📅', '../', 'CALENDAR');

$eventsFile = __DIR__ . '/events.json';
$events = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) ?: [] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title']) && !empty($_POST['date'])) {
    $events[] = [
        'id'    => time(),
        'title' => htmlspecialchars(trim($_POST['title'])),
        'date'  => $_POST['date'],
        'time'  => $_POST['time'] ?? '',
        'color' => $_POST['color'] ?? '#facc15',
        'note'  => htmlspecialchars(trim($_POST['note'] ?? '')),
    ];
    usort($events, fn($a,$b) => strcmp($a['date'],$b['date']));
    file_put_contents($eventsFile, json_encode($events, JSON_UNESCAPED_UNICODE));
    header('Location: index.php'); exit;
}
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $events = array_values(array_filter($events, fn($e) => $e['id'] !== $id));
    file_put_contents($eventsFile, json_encode($events, JSON_UNESCAPED_UNICODE));
    header('Location: index.php'); exit;
}

$today = date('Y-m-d');
$upcoming = array_filter($events, fn($e) => $e['date'] >= $today);
$past = array_filter($events, fn($e) => $e['date'] < $today);
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📅 Cipher Calendar</div>
    <div class="c-sub">تقویم و زمان‌بندی رویدادهای سازمانی — <?= count($upcoming) ?> رویداد پیش رو</div>
    <div style="margin-top:12px;display:flex;gap:10px;">
      <span class="c-tag">● <?= count($upcoming) ?> Upcoming</span>
      <span class="c-tag" style="background:rgba(100,116,139,.1);border-color:rgba(100,116,139,.2);color:var(--muted2);">✓ <?= count($past) ?> Past</span>
      <span style="font-family:var(--mono);font-size:11px;color:var(--muted);padding:4px 12px;border-radius:99px;background:var(--glass);border:1px solid var(--stroke);">امروز: <?= $today ?></span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;">

    <!-- ADD FORM -->
    <div class="c-panel">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div class="c-sec-title" style="margin-bottom:18px;">افزودن رویداد</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
        <div>
          <label class="c-label" style="display:block;margin-bottom:5px;">عنوان رویداد</label>
          <input type="text" name="title" class="c-input" placeholder="نام رویداد..." required>
        </div>
        <div>
          <label class="c-label" style="display:block;margin-bottom:5px;">تاریخ</label>
          <input type="date" name="date" class="c-input" value="<?= $today ?>" required>
        </div>
        <div>
          <label class="c-label" style="display:block;margin-bottom:5px;">ساعت (اختیاری)</label>
          <input type="time" name="time" class="c-input">
        </div>
        <div>
          <label class="c-label" style="display:block;margin-bottom:5px;">رنگ</label>
          <select name="color" class="c-input">
            <option value="#facc15">🟡 زرد</option>
            <option value="#f43f5e">🔴 قرمز</option>
            <option value="#00eaff">🔵 سیان</option>
            <option value="#4ade80">🟢 سبز</option>
            <option value="#c084fc">🟣 بنفش</option>
          </select>
        </div>
        <div>
          <label class="c-label" style="display:block;margin-bottom:5px;">یادداشت</label>
          <textarea name="note" class="c-textarea" placeholder="توضیحات..."></textarea>
        </div>
        <button type="submit" class="c-btn" style="width:100%;justify-content:center;">+ افزودن رویداد</button>
      </form>
    </div>

    <!-- EVENTS LIST -->
    <div>
      <div class="c-panel" style="margin-bottom:18px;">
        <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
        <div class="c-sec"><span class="c-sec-title">رویدادهای پیش رو</span><span class="c-tag">● <?= count($upcoming) ?></span></div>
        <?php if (empty($upcoming)): ?>
        <div class="c-empty"><div class="c-empty-icon">📅</div><p>رویدادی ثبت نشده است.</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php foreach ($upcoming as $e):
            $isToday = $e['date'] === $today;
            $daysLeft = (strtotime($e['date']) - strtotime($today)) / 86400;
          ?>
          <div class="fade-in-item" style="display:flex;align-items:center;gap:14px;padding:14px 16px;
               background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;
               border-right:3px solid <?= $e['color'] ?>;">
            <div style="text-align:center;min-width:48px;">
              <div style="font-family:var(--mono);font-size:18px;font-weight:700;color:<?= $e['color'] ?>;"><?= date('d', strtotime($e['date'])) ?></div>
              <div style="font-family:var(--mono);font-size:9px;color:var(--muted);"><?= date('M', strtotime($e['date'])) ?></div>
            </div>
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:600;margin-bottom:3px;"><?= $e['title'] ?>
                <?php if ($isToday): ?><span style="font-size:10px;background:rgba(0,234,255,.1);border:1px solid rgba(0,234,255,.25);color:var(--cyan);padding:2px 8px;border-radius:99px;font-family:var(--mono);margin-right:8px;">امروز</span><?php endif; ?>
              </div>
              <div style="font-size:11px;color:var(--muted);font-family:var(--mono);">
                <?= $e['date'] ?><?= $e['time'] ? ' · '.$e['time'] : '' ?><?= !$isToday ? ' · '.round($daysLeft).' روز دیگر' : '' ?>
              </div>
              <?php if ($e['note']): ?><div style="font-size:12px;color:var(--muted2);margin-top:4px;font-family:var(--fa);"><?= $e['note'] ?></div><?php endif; ?>
            </div>
            <a href="?del=<?= $e['id'] ?>" onclick="return confirm('حذف شود؟')"
               style="padding:6px 12px;border-radius:8px;background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-family:var(--mono);font-size:11px;">✕</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php cipher_foot(); ?>
