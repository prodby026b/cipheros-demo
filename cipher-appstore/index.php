<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Appstore', '#fb923c');
cipher_navbar('Cipher Appstore', '📦', '../', 'APP STORE');

$services = [
  ['name'=>'Cipher Media',    'url'=>'../cipher-media/',    'icon'=>'🎬','color'=>'#f43f5e','desc'=>'مدیریت و پخش فایل‌های رسانه‌ای',          'ver'=>'v2.1','tag'=>'Media'],
  ['name'=>'Cipher Stream',   'url'=>'../cipher-stream/',   'icon'=>'📺','color'=>'#a78bfa','desc'=>'پخش امن و داخلی ویدیوها',                 'ver'=>'v1.8','tag'=>'Stream'],
  ['name'=>'Cipher Music',    'url'=>'../cipher-music/',    'icon'=>'🎵','color'=>'#f472b6','desc'=>'پلیر موزیک اختصاصی',                     'ver'=>'v1.5','tag'=>'Music'],
  ['name'=>'Cipher Cloud',    'url'=>'../cipher-cloud/',    'icon'=>'☁️','color'=>'#38bdf8','desc'=>'فضای ابری ذخیره و اشتراک فایل',          'ver'=>'v3.0','tag'=>'Cloud'],
  ['name'=>'Cipher Network',  'url'=>'../cipher-network/',  'icon'=>'🌐','color'=>'#34d399','desc'=>'مانیتورینگ شبکه و زیرساخت',              'ver'=>'v2.3','tag'=>'Network'],
  ['name'=>'Cipher Chat',     'url'=>'../cipher-chat/',     'icon'=>'💬','color'=>'#00eaff','desc'=>'پیام‌رسان داخلی امن',                    'ver'=>'v4.1','tag'=>'Chat'],
  ['name'=>'Cipher Tasks',    'url'=>'../cipher-tasks/',    'icon'=>'✅','color'=>'#4ade80','desc'=>'مدیریت وظایف تیمی',                      'ver'=>'v2.0','tag'=>'Tasks'],
  ['name'=>'Cipher Calendar', 'url'=>'../cipher-calendar/','icon'=>'📅','color'=>'#facc15','desc'=>'تقویم رویدادهای سازمانی',                 'ver'=>'v1.2','tag'=>'Calendar'],
  ['name'=>'Cipher Calls',    'url'=>'../cipher-calls/',    'icon'=>'📞','color'=>'#2dd4bf','desc'=>'تماس ویدیویی و صوتی',                    'ver'=>'v1.0','tag'=>'Calls'],
  ['name'=>'Cipher Wiki',     'url'=>'../cipher-wiki/',     'icon'=>'📖','color'=>'#c084fc','desc'=>'پایگاه دانش داخلی',                      'ver'=>'v1.3','tag'=>'Wiki'],
  ['name'=>'Cipher Desktop',  'url'=>'../cipher-desktop/',  'icon'=>'🖥️','color'=>'#94a3b8','desc'=>'محیط دسکتاپ مجازی',                    'ver'=>'v1.0','tag'=>'Desktop'],
  ['name'=>'Cipher Dashboard','url'=>'../cipher-dashboard/','icon'=>'📊','color'=>'#f59e0b','desc'=>'پنل آمار و اطلاعات سرور',                'ver'=>'v2.2','tag'=>'Stats'],
];
?>
<div class="c-wrap">
  <div class="c-panel" style="margin-bottom:28px;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// CIPHER OS · PRODBY026B</div>
    <div class="c-title">📦 Cipher Appstore</div>
    <div class="c-sub">مرکز رسمی سرویس‌های Cipher OS — <?= count($services) ?> اپلیکیشن فعال</div>
    <div style="margin-top:12px;"><span class="c-tag">● <?= count($services) ?> Apps Installed</span></div>
  </div>

  <div class="c-grid-2" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
    <?php foreach ($services as $s): ?>
    <a href="<?= $s['url'] ?>" class="fade-in-item c-card" style="display:block;position:relative;overflow:hidden;">
      <div style="position:absolute;bottom:0;left:0;right:0;height:2px;background:<?= $s['color'] ?>;opacity:.4;"></div>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
        <div style="width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;background:<?= $s['color'] ?>18;border:1px solid <?= $s['color'] ?>33;"><?= $s['icon'] ?></div>
        <div style="text-align:left;">
          <span style="font-size:9px;font-family:var(--mono);color:<?= $s['color'] ?>;letter-spacing:.1em;"><?= $s['tag'] ?></span><br>
          <span style="font-size:9px;font-family:var(--mono);color:var(--muted);"><?= $s['ver'] ?></span>
        </div>
      </div>
      <div style="font-size:14px;font-weight:600;margin-bottom:5px;"><?= $s['name'] ?></div>
      <div style="font-size:12px;color:var(--muted2);font-family:var(--fa);line-height:1.6;"><?= $s['desc'] ?></div>
      <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:10px;color:var(--success);font-family:var(--mono);">● Installed</span>
        <span style="font-size:11px;color:<?= $s['color'] ?>;font-family:var(--mono);">باز کردن →</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php cipher_foot(); ?>
