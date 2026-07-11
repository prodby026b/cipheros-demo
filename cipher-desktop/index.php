<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher KALI Desktop', '#06b6d4');
cipher_navbar('Cipher KALI Desktop', '🐉', '../', 'DESKTOP');

// ۱. خواندن اطلاعات واقعی سیستم‌عامل کالی لینوکس
$kernel = php_uname('r');
$uptime_raw = @shell_exec('uptime -p') ?: 'Uptime unknown';
$uptime = trim(str_replace('up ', '', $uptime_raw));

// ۲. هندل کردن دستورات ارسالی از ترمینال (AJAX Endpoint)
if (isset($_POST['execute_command'])) {
    header('Content-Type: application/json');
    $cmd = $_POST['execute_command'];
    
    // لیست سیاه برای امنیت پایه (در صورت نیاز می‌توانید تغییر دهید)
    $disabled_cmds = ['rm -rf /', 'mkfs', 'shutdown', 'reboot'];
    foreach ($disabled_cmds as $blocked) {
        if (strpos($cmd, $blocked) !== false) {
            echo json_encode(['output' => "Access Denied: Dangerous command blocked by Cipher OS."]);
            exit;
        }
    }

    // اجرای دستور واقعی روی سرور کالی لینوکس و دریافت خروجی
    $output = [];
    $retval = 0;
    exec($cmd . ' 2>&1', $output, $retval);
    
    if (empty($output)) {
        $result = "Command executed with exit code $retval (No output).";
    } else {
        $result = implode("\n", $output);
    }
    
    echo json_encode(['output' => htmlspecialchars($result)]);
    exit;
}

// ۳. هندل کردن دریافت دیتای واقعی سخت‌افزار سرور (AJAX Endpoint)
if (isset($_GET['get_sys_stats'])) {
    header('Content-Type: application/json');
    
    // دریافت میزان مصرف واقعی CPU در لینوکس
    $cpu_load = sys_getloadavg();
    $cpu_pct = isset($cpu_load[0]) ? round($cpu_load[0] * 10, 1) : rand(5, 15);
    if ($cpu_pct > 100) $cpu_pct = 100;

    // دریافت میزان مصرف واقعی RAM از /proc/meminfo
    $free_mem = 0;
    $total_mem = 0;
    if (file_exists('/proc/meminfo')) {
        $meminfo = file('/proc/meminfo');
        foreach ($meminfo as $line) {
            if (preg_match('/^MemTotal:\s+(\d+)/', $line, $matches)) $total_mem = $matches[1];
            if (preg_match('/^MemAvailable:\s+(\d+)/', $line, $matches)) { $free_mem = $matches[1]; break; }
        }
    }
    
    if ($total_mem > 0) {
        $used_mem = $total_mem - $free_mem;
        $ram_total_gb = round($total_mem / 1024 / 1024, 1);
        $ram_used_gb = round($used_mem / 1024 / 1024, 1);
        $ram_pct = round(($used_mem / $total_mem) * 100, 1);
    } else {
        $ram_total_gb = 16; $ram_used_gb = 3.2; $ram_pct = 20;
    }

    echo json_encode([
        'cpu_pct' => $cpu_pct,
        'ram_string' => "$ram_used_gb GB / $ram_total_gb GB",
        'ram_pct' => $ram_pct
    ]);
    exit;
}

$apps = [
  ['name'=>'Cipher Media',    'url'=>'../cipher-media/',    'icon'=>'🎬','color'=>'#f43f5e','desc'=>'پخش رسانه'],
  ['name'=>'Cipher Stream',   'url'=>'../cipher-stream/',   'icon'=>'📺','color'=>'#a78bfa','desc'=>'استریم ویدیو'],
  ['name'=>'Cipher Music',    'url'=>'../cipher-music/',    'icon'=>'🎵','color'=>'#f472b6','desc'=>'پلیر موزیک'],
  ['name'=>'Cipher Cloud',    'url'=>'../cipher-cloud/',    'icon'=>'☁️','color'=>'#38bdf8','desc'=>'فضای ابری'],
  ['name'=>'Cipher Network',  'url'=>'../cipher-network/',  'icon'=>'🌐','color'=>'#34d399','desc'=>'شبکه'],
  ['name'=>'Cipher Chat',     'url'=>'../cipher-chat/',     'icon'=>'💬','color'=>'#00eaff','desc'=>'چت تیمی'],
  ['name'=>'Cipher Tasks',    'url'=>'../cipher-tasks/',    'icon'=>'✅','color'=>'#4ade80','desc'=>'مدیریت تسک'],
  ['name'=>'Cipher Calendar', 'url'=>'../cipher-calendar/','icon'=>'📅','color'=>'#facc15','desc'=>'تقویم'],
  ['name'=>'Cipher Calls',    'url'=>'../cipher-calls/',    'icon'=>'📞','color'=>'#2dd4bf','desc'=>'تماس ویدیویی'],
  ['name'=>'Cipher Wiki',     'url'=>'../cipher-wiki/',     'icon'=>'📖','color'=>'#c084fc','desc'=>'مستندات'],
  ['name'=>'Cipher Appstore', 'url'=>'../cipher-appstore/','icon'=>'📦','color'=>'#fb923c','desc'=>'اپ استور'],
  ['name'=>'Cipher Dashboard','url'=>'../cipher-dashboard/','icon'=>'📊','color'=>'#f59e0b','desc'=>'پنل آمار'],
];
?>
<div class="c-wrap">
  
  <div class="c-panel" style="margin-bottom:24px; position:relative; overflow:hidden;">
    <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
    <div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
    <div class="c-label">// KALI LINUX ROOT @ CIPHER OS · PRODBY026B</div>
    <div class="c-title" style="color:#06b6d4; text-shadow: 0 0 15px rgba(6,182,212,0.4);">🐉 Cipher True Kali Desktop</div>
    <div class="c-sub">اتصال مستقیم به ترمینال سرور کالی لینوکس — اجرای زنده ابزارهای تست نفوذ و پنتست</div>
    
    <div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
      <span class="c-tag" style="background:rgba(6,182,212,0.15); border-color:rgba(6,182,212,0.4); color:#06b6d4;">● STATUS: LIVE ROOT INTERACTION</span>
      <span class="c-tag">● <?= count($apps) ?> CORE MODULES</span>
      <span class="c-tag" style="font-family:var(--mono); color:#f43f5e;">● KERNEL: <?= htmlspecialchars($kernel) ?></span>
      <span class="c-tag" style="font-family:var(--mono);">● UP: <?= htmlspecialchars($uptime) ?></span>
    </div>
  </div>

  <div style="display:grid; grid-template-columns: 1fr 340px; gap:20px; margin-bottom:24px; align-items: stretch;" id="desktop-grid-top">
    
    <div class="c-panel" style="display:flex; flex-direction:column; justify-content:center; padding:20px;">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
      <div style="font-size:11px; color:var(--muted); margin-bottom:8px; font-family:var(--mono);"># kali-menu --search-apps</div>
      <input type="text" id="desktopSearch" placeholder="🔍 جستجوی سریع در برنامه‌ها و ابزارها..." 
             style="width:100%; background:var(--bg2); border:1px solid var(--stroke); border-radius:12px; 
                    color:var(--text); padding:12px 16px; font-size:13px; font-family:var(--fa); transition:.2s; direction:rtl;"
             onfocus="this.style.borderColor='#06b6d4'; this.style.boxShadow='0 0 10px rgba(6,182,212,0.2)';" 
             onblur="this.style.borderColor='var(--stroke)'; this.style.boxShadow='none';" />
    </div>

    <div class="c-panel" style="font-family:var(--mono); font-size:12px; display:flex; flex-direction:column; gap:8px;">
      <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div>
      <div style="color:#06b6d4; font-weight:bold; margin-bottom:4px;">[ REAL-TIME SERVER METRICS ]</div>
      <div style="display:flex; justify-content:space-between;">
        <span>SERVER CPU:</span><span id="cpu-val" style="color:#4ade80;">...</span>
      </div>
      <div style="width:100%; height:4px; background:var(--bg2); border-radius:2px; overflow:hidden;">
        <div id="cpu-bar" style="width:0%; height:100%; background:#4ade80; transition:0.4s;"></div>
      </div>
      <div style="display:flex; justify-content:space-between; margin-top:4px;">
        <span>SERVER RAM:</span><span id="ram-val" style="color:#facc15;">...</span>
      </div>
      <div style="width:100%; height:4px; background:var(--bg2); border-radius:2px; overflow:hidden;">
        <div id="ram-bar" style="width:0%; height:100%; background:#facc15; transition:0.4s;"></div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:18px; margin-bottom:24px;" id="appsContainer">
    <?php foreach ($apps as $a): ?>
    <a href="<?= $a['url'] ?>" class="fade-in-item app-card" data-name="<?= htmlspecialchars(strtolower($a['name'])) ?>" data-desc="<?= htmlspecialchars(strtolower($a['desc'])) ?>"
       style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:24px 14px;
              background:var(--bg2);border:1px solid var(--stroke);border-radius:20px;
              transition:.25s;text-align:center;cursor:pointer; text-decoration:none; color:var(--text);"
       onmouseover="this.style.borderColor='<?= $a['color'] ?>cc';this.style.transform='translateY(-6px)';this.style.background='var(--bg3)';this.style.boxShadow='0 10px 25px <?= $a['color'] ?>18';"
       onmouseout="this.style.borderColor='var(--stroke)';this.style.transform='translateY(0)';this.style.background='var(--bg2)';this.style.boxShadow='none';">
      <div style="width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;
                  font-size:30px;background:<?= $a['color'] ?>14;border:1px solid <?= $a['color'] ?>25;
                  box-shadow:0 0 20px <?= $a['color'] ?>12; transition: 0.2s;">
        <?= $a['icon'] ?>
      </div>
      <div style="font-size:13px;font-weight:700;line-height:1.3; color:var(--text);"><?= $a['name'] ?></div>
      <div style="font-size:10px;color:var(--muted);font-family:var(--fa);"><?= $a['desc'] ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="c-panel" style="padding:0; overflow:hidden; border:1px solid rgba(6,182,212,0.3); border-radius:16px;">
    <div style="background:rgba(255,255,255,0.03); border-bottom:1px solid var(--stroke); padding:10px 16px; display:flex; align-items:center; justify-content:between;">
      <div style="display:flex; gap:6px;">
        <span style="width:10px; height:10px; background:#ef4444; border-radius:50%;"></span>
        <span style="width:10px; height:10px; background:#facc15; border-radius:50%;"></span>
        <span style="width:10px; height:10px; background:#4ade80; border-radius:50%;"></span>
      </div>
      <div style="margin-right:auto; font-family:var(--mono); font-size:11px; color:#06b6d4; font-weight:bold; letter-spacing:1px;">kali-root-terminal</div>
    </div>
    <div id="terminal-body" style="background:#070b14; padding:16px; height:280px; overflow-y:auto; font-family:var(--mono); font-size:12px; color:#e2e8f0; line-height:1.6; direction:ltr; text-align:left;">
      <div style="color:#06b6d4; font-weight:bold;">🐉 Cipher True Kali Server Terminal Active</div>
      <div style="color:var(--muted); margin-bottom:10px;">شما دسترسی مستقیم به ابزارهای کالی سرور دارید. مثال: <span style="color:#00eaff;">nmap -F localhost</span> یا <span style="color:#00eaff;">whoami</span> یا <span style="color:#00eaff;">pwd</span></div>
      <div id="terminal-output"></div>
      <div style="display:flex; align-items:center; margin-top:4px;">
        <span style="color:#ef4444; font-weight:bold; margin-right:8px; white-space:nowrap;">root@kali-server:~#</span>
        <input type="text" id="terminal-input" style="background:transparent; border:none; color:#4ade80; outline:none; width:100%; font-family:var(--mono); font-size:12px;" autofocus autocomplete="off" />
      </div>
    </div>
  </div>

</div>

<script>
// ۱. فیلتر کردن برنامه‌ها
document.getElementById('desktopSearch').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const apps = document.querySelectorAll('.app-card');
    apps.forEach(app => {
        if (app.getAttribute('data-name').includes(query) || app.getAttribute('data-desc').includes(query)) {
            app.style.display = 'flex';
        } else {
            app.style.display = 'none';
        }
    });
});

// ۲. واکشی اطلاعات واقعی سخت‌افزار از بک‌اند با AJAX
function updateSystemStats() {
    fetch('?get_sys_stats=1')
        .then(res => res.json())
        .then(data => {
            document.getElementById('cpu-val').innerText = data.cpu_pct + '%';
            document.getElementById('cpu-bar').style.width = data.cpu_pct + '%';
            document.getElementById('ram-val').innerText = data.ram_string;
            document.getElementById('ram-bar').style.width = data.ram_pct + '%';
            
            if(data.cpu_pct > 65) document.getElementById('cpu-val').style.color = '#ef4444';
            else document.getElementById('cpu-val').style.color = '#4ade80';
        }).catch(() => {});
}
setInterval(updateSystemStats, 3000);
updateSystemStats();

// ۳. اتصال ترمینال به شل لینوکس واقعی سرور از طریق درخواست‌های POST
const termInput = document.getElementById('terminal-input');
const termOutput = document.getElementById('terminal-output');
const termBody = document.getElementById('terminal-body');

termInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const cmd = this.value.trim();
        if(!cmd) return;
        
        printLine('root@kali-server:~# ' + cmd, '#e2e8f0');
        this.value = '';
        
        if (cmd.toLowerCase() === 'clear') {
            termOutput.innerHTML = '';
            return;
        }
        
        // ارسال دستور به سرور و چاپ نتیجه واقعی لینوکس کالی
        const formData = new FormData();
        formData.append('execute_command', cmd);
        
        printLine('⏳ Executing on server...', 'var(--muted)');
        termBody.scrollTop = termBody.scrollHeight;

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            // پاک کردن خط در حال اجرا
            if(termOutput.lastChild) termOutput.removeChild(termOutput.lastChild);
            
            // چاپ خروجی اصلی دستور لینوکس کالی
            const formattedOutput = data.output.replace(/\n/g, '<br>');
            printLine(formattedOutput, '#4ade80');
            termBody.scrollTop = termBody.scrollHeight;
        })
        .catch(() => {
            if(termOutput.lastChild) termOutput.removeChild(termOutput.lastChild);
            printLine('❌ Error connecting to system terminal execution context.', '#ef4444');
        });
    }
});

function printLine(text, color) {
    const div = document.createElement('div');
    div.style.color = color;
    div.style.whiteSpace = 'pre-wrap';
    div.innerHTML = text;
    termOutput.appendChild(div);
}
</script>

<style>
@media (max-width: 768px) {
    #desktop-grid-top { grid-template-columns: 1fr !important; }
}
#terminal-body::-webkit-scrollbar { width: 6px; }
#terminal-body::-webkit-scrollbar-thumb { background: rgba(6,182,212,0.3); border-radius: 4px; }
</style>
<?php cipher_foot(); ?>