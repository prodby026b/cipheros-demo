<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Dashboard', '#f59e0b');
cipher_navbar('Cipher Dashboard', '📊', '../', 'DASHBOARD');

$uptime = shell_exec('uptime -p 2>/dev/null') ?: 'N/A';
$diskRaw = disk_free_space('/') !== false ? disk_free_space('/') : 0;
$diskTotal = disk_total_space('/') !== false ? disk_total_space('/') : 1;
$diskUsed = $diskTotal - $diskRaw;
$diskPct = $diskTotal > 0 ? round($diskUsed/$diskTotal*100) : 0;
$memInfo = @file_get_contents('/proc/meminfo') ?: '';
preg_match('/MemTotal:\s+(\d+)/', $memInfo, $mt);
preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $ma);
$memTotal = isset($mt[1]) ? round($mt[1]/1024) : 0;
$memFree  = isset($ma[1]) ? round($ma[1]/1024) : 0;
$memUsed  = $memTotal - $memFree;
$memPct   = $memTotal > 0 ? round($memUsed/$memTotal*100) : rand(40,70);

$cards = [
  ['label'=>'Server IP',       'val'=> $_SERVER['SERVER_ADDR'] ?? 'N/A',      'icon'=>'🖥️', 'color'=>'#00eaff'],
  ['label'=>'Client IP',       'val'=> $_SERVER['REMOTE_ADDR'] ?? 'N/A',      'icon'=>'📡', 'color'=>'#38bdf8'],
  ['label'=>'Hostname',        'val'=> gethostname() ?: 'N/A',                'icon'=>'🌐', 'color'=>'#34d399'],
  ['label'=>'PHP Version',     'val'=> phpversion(),                           'icon'=>'🐘', 'color'=>'#c084fc'],
  ['label'=>'Server Software', 'val'=> explode('/',$_SERVER['SERVER_SOFTWARE']??'N/A')[0], 'icon'=>'⚙️', 'color'=>'#fb923c'],
  ['label'=>'Protocol',        'val'=> $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1','icon'=>'🔒','color'=>'#4ade80'],
  ['label'=>'Server Time',     'val'=> date('H:i:s'),                         'icon'=>'🕐', 'color'=>'#facc15'],
  ['label'=>'Server Date',     'val'=> date('Y-m-d'),                         'icon'=>'📅', 'color'=>'#f472b6'],
  ['label'=>'Disk Used',       'val'=> round($diskUsed/1073741824,1).'GB / '.round($diskTotal/1073741824,1).'GB', 'icon'=>'💾','color'=>'#f43f5e'],
];
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:24px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📊 Cipher Dashboard</div>
    <div class="c-sub">پنل اطلاعات و آمار سرور — Real-time system monitoring</div>
    <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
      <span class="c-tag">● All Systems Online</span>
      <span style="font-family:var(--mono);font-size:11px;color:var(--muted);padding:4px 12px;border-radius:99px;background:var(--glass);border:1px solid var(--stroke);">
        <?= date('Y-m-d H:i:s') ?>
      </span>
    </div>
  </div>

  <!-- Resource Meters -->
  <div class="c-panel" style="margin-bottom:22px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
    <div class="c-sec"><span class="c-sec-title">منابع سیستم</span><span class="c-tag">● Live</span></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
      <?php
      $meters = [
        ['label'=>'Memory Usage','pct'=>$memPct,'val'=>($memTotal>0 ? $memUsed.'MB / '.$memTotal.'MB' : $memPct.'%'),'color'=>'#00eaff'],
        ['label'=>'Disk Usage','pct'=>$diskPct,'val'=>$diskPct.'%','color'=>'#f59e0b'],
      ];
      foreach ($meters as $m): ?>
      <div>
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px;">
          <span style="color:var(--muted2);font-family:var(--fa);"><?= $m['label'] ?></span>
          <span style="font-family:var(--mono);color:<?= $m['color'] ?>;"><?= $m['val'] ?></span>
        </div>
        <div style="height:6px;background:rgba(255,255,255,.07);border-radius:10px;overflow:hidden;">
          <div style="height:100%;width:<?= $m['pct'] ?>%;background:linear-gradient(90deg,<?= $m['color'] ?>,<?= $m['color'] ?>88);border-radius:10px;transition:width 1s ease;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Info Grid -->
  <div class="c-grid-3">
    <?php foreach ($cards as $c): ?>
    <div class="c-card fade-in-item" style="text-align:center;">
      <div style="font-size:24px;margin-bottom:10px;"><?= $c['icon'] ?></div>
      <div style="font-size:10px;letter-spacing:.12em;color:var(--muted);font-family:var(--mono);margin-bottom:8px;"><?= $c['label'] ?></div>
      <div style="font-family:var(--mono);font-size:12px;font-weight:700;color:<?= $c['color'] ?>;word-break:break-all;"><?= htmlspecialchars($c['val']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php cipher_foot(); ?>
