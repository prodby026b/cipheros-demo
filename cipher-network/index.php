<?php
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Network', '#34d399');
cipher_navbar('Cipher Network', '🌐', '../', 'NETWORK');

$cards = [
  ['label'=>'Server IP',       'val'=> $_SERVER['SERVER_ADDR'] ?? 'N/A',        'icon'=>'🖥️'],
  ['label'=>'Client IP',       'val'=> $_SERVER['REMOTE_ADDR'] ?? 'N/A',        'icon'=>'📡'],
  ['label'=>'Hostname',        'val'=> gethostname(),                            'icon'=>'🌐'],
  ['label'=>'Server Software', 'val'=> $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',    'icon'=>'⚙️'],
  ['label'=>'Server Port',     'val'=> $_SERVER['SERVER_PORT'] ?? '80',          'icon'=>'🔌'],
  ['label'=>'Protocol',        'val'=> $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',     'icon'=>'🔒'],
  ['label'=>'PHP Version',     'val'=> phpversion(),                             'icon'=>'🐘'],
  ['label'=>'SAPI',            'val'=> php_sapi_name(),                          'icon'=>'🔧'],
  ['label'=>'Server Time',     'val'=> date('Y-m-d H:i:s'),                     'icon'=>'🕐'],
];
?>
<div class="c-wrap">

  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">🌐 Cipher Network</div>
    <div class="c-sub">مانیتورینگ لحظه‌ای شبکه و زیرساخت سرور — Real-time monitoring</div>
    <div style="margin-top:12px;">
      <span class="c-tag">● All Systems Online</span>
    </div>
  </div>

  <div class="c-grid-3" style="margin-bottom:22px;">
    <?php foreach ($cards as $c): ?>
    <div class="c-card fade-in-item" style="text-align:center;">
      <div style="font-size:26px;margin-bottom:10px;"><?= $c['icon'] ?></div>
      <div style="font-size:10px;letter-spacing:.15em;color:var(--muted);font-family:var(--mono);margin-bottom:8px;"><?= $c['label'] ?></div>
      <div style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent);word-break:break-all;"><?= htmlspecialchars($c['val']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Live Ping Panel -->
  <div class="c-panel">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div class="c-sec"><span class="c-sec-title">📶 وضعیت اتصال‌ها</span>
      <span class="c-tag">● Live</span></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
      <?php
      $hosts = ['prodby026b.sbs'=>'Website','8.8.8.8'=>'Google DNS','1.1.1.1'=>'Cloudflare'];
      foreach ($hosts as $host => $label):
        $start = microtime(true);
        $sock = @fsockopen($host, 80, $errno, $errstr, 1);
        $ms = round((microtime(true) - $start) * 1000);
        $ok = $sock !== false;
        if ($sock) fclose($sock);
        $color = $ok ? 'var(--success)' : 'var(--danger)';
      ?>
      <div class="c-card" style="display:flex;align-items:center;gap:12px;">
        <div style="width:10px;height:10px;border-radius:50%;background:<?= $color ?>;box-shadow:0 0 8px <?= $color ?>;flex-shrink:0;animation:pulse 2s infinite;"></div>
        <div>
          <div style="font-size:12px;font-weight:600;"><?= $label ?></div>
          <div style="font-size:11px;color:var(--muted);font-family:var(--mono);"><?= $ok ? $ms.'ms' : 'Offline' ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>
<?php cipher_foot(); ?>
