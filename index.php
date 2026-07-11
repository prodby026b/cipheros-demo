<?php
/**
 * Cipher OS — Dashboard v4 (Cinematic Edition)
 * ------------------------------------------------------------------
 * طراحی فوق‌العاده خفن با: پارتیکل کانواس، کارت‌های 3D، گروه‌بندی
 * دسته‌بندی، Command Palette، انیمیشن‌های سینمایی.
 */
require_once __DIR__ . '/cipher-core/security.php';
require_auth(false);

$dataFile = __DIR__ . '/dashboard_data.json';
$defaultData = [
    "management_notice" => "سلام این prodby026b است",
    "activities" => [
        ["icon" => "☁️", "title" => "آپلود فایل جدید در Cipher Cloud", "desc" => "گزارش ماهانه پروژه در فضای ابری ثبت شد.", "time" => "۱۰ دقیقه پیش"],
        ["icon" => "💬", "title" => "پیام جدید در Cipher Chat", "desc" => "یک پیام جدید در کانال داخلی تیم ارسال شده است.", "time" => "۳۵ دقیقه پیش"],
        ["icon" => "🎬", "title" => "رسانه جدید اضافه شد", "desc" => "یک فایل آموزشی جدید در Cipher Media بارگذاری شد.", "time" => "۱ ساعت پیش"]
    ]
];

if (file_exists($dataFile)) {
    $dashboardData = json_decode(file_get_contents($dataFile), true) ?: $defaultData;
} else {
    $dashboardData = $defaultData;
    file_put_contents($dataFile, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// ─── همه سرویس‌ها با دسته‌بندی ───
$apps = [
    ["name"=>"Cipher Net",       "url"=>"https://prodby026b.sbs",  "icon"=>"🏢","desc"=>"وب‌سایت رسمی prodby026b",                    "badge"=>"Official",   "cat"=>"core","color"=>"#00eaff"],
    ["name"=>"Cipher Media",     "url"=>"cipher-media/",           "icon"=>"🎬","desc"=>"مدیریت و استریم فایل‌های رسانه‌ای",         "badge"=>"Media",      "cat"=>"core","color"=>"#f43f5e"],
    ["name"=>"Cipher Cloud",     "url"=>"cipher-cloud/",           "icon"=>"☁️","desc"=>"فضای ابری اختصاصی برای ذخیره",              "badge"=>"Cloud",      "cat"=>"core","color"=>"#38bdf8"],
    ["name"=>"Cipher Stream",    "url"=>"cipher-stream/",          "icon"=>"📺","desc"=>"پخش امن ویدیوها و محتوای آموزشی",           "badge"=>"Stream",     "cat"=>"core","color"=>"#a78bfa"],
    ["name"=>"Cipher Chat",      "url"=>"cipher-chat/",            "icon"=>"💬","desc"=>"پیام‌رسان داخلی سریع و امن برای تیم",        "badge"=>"Chat",       "cat"=>"core","color"=>"#00eaff"],
    ["name"=>"Cipher AI",        "url"=>"#",              "icon"=>"🤖","desc"=>"دستیار هوش مصنوعی اختصاصی",                 "badge"=>"AI",         "cat"=>"core","color"=>"#a78bfa","pro"=>true],
    ["name"=>"Cipher Desktop",   "url"=>"cipher-desktop/",         "icon"=>"🖥️","desc"=>"محیط دسکتاپ مجازی Cipher OS",                "badge"=>"Desktop",    "cat"=>"core","color"=>"#94a3b8"],
    ["name"=>"Cipher Dashboard", "url"=>"cipher-dashboard/",       "icon"=>"📊","desc"=>"پنل اطلاعات و آمار سرور",                    "badge"=>"Dashboard",  "cat"=>"core","color"=>"#f59e0b"],

    ["name"=>"Cipher Network",   "url"=>"cipher-network/",         "icon"=>"🌐","desc"=>"ابزار مانیتورینگ شبکه و زیرساخت",           "badge"=>"Network",    "cat"=>"devops","color"=>"#34d399"],
    ["name"=>"Cipher Monitor",   "url"=>"#",         "icon"=>"📡","desc"=>"مانیتورینگ Real-time سرور",                  "badge"=>"Monitor",    "cat"=>"devops","color"=>"#f59e0b","pro"=>true],
    ["name"=>"Cipher IP",        "url"=>"cipher-ip/",              "icon"=>"🌐","desc"=>"IP Lookup، Whois و DNS اختصاصی",            "badge"=>"IP Tools",   "cat"=>"devops","color"=>"#34d399"],
    ["name"=>"Cipher Speed",     "url"=>"cipher-speed/",           "icon"=>"⚡","desc"=>"تست سرعت اینترنت و لتنسی شبکه",             "badge"=>"Speed",      "cat"=>"devops","color"=>"#00eaff"],
    ["name"=>"Cipher Terminal",  "url"=>"cipher-terminal/",        "icon"=>"⌨️","desc"=>"ترمینال وب emulator برای دستورات",          "badge"=>"Terminal",   "cat"=>"devops","color"=>"#00eaff"],
    ["name"=>"Cipher Logs",      "url"=>"cipher-logs/",            "icon"=>"📋","desc"=>"نمایش، جستجو و تحلیل لاگ‌های سیستم",         "badge"=>"Logs",       "cat"=>"devops","color"=>"#f59e0b"],
    ["name"=>"Cipher Database",  "url"=>"#",        "icon"=>"🗄️","desc"=>"مدیریت بانک اطلاعات و کوئری‌های SQL",       "badge"=>"Database",   "cat"=>"devops","color"=>"#34d399","pro"=>true],
    ["name"=>"Cipher API",       "url"=>"cipher-api/",             "icon"=>"🔌","desc"=>"ابزار تست API و مدیریت Requests",            "badge"=>"API",        "cat"=>"devops","color"=>"#38bdf8"],
    ["name"=>"Cipher Backup",    "url"=>"#",          "icon"=>"💾","desc"=>"بک‌آپ خودکار و مدیریت نسخه‌های ذخیره",       "badge"=>"Backup",     "cat"=>"devops","color"=>"#fb923c","pro"=>true],

    ["name"=>"Cipher Code",      "url"=>"cipher-code/",            "icon"=>"💻","desc"=>"ویرایشگر کد و رانر JavaScript/Python",        "badge"=>"Code",       "cat"=>"dev","color"=>"#a78bfa"],
    ["name"=>"Cipher MD",        "url"=>"cipher-md/",              "icon"=>"✍️","desc"=>"ویرایشگر Markdown با پیش‌نمایش زنده",         "badge"=>"Markdown",   "cat"=>"dev","color"=>"#c084fc"],
    ["name"=>"Cipher JSON",      "url"=>"cipher-json/",            "icon"=>"📂","desc"=>"فرمت، اعتبارسنجی و مقایسه JSON",             "badge"=>"JSON",       "cat"=>"dev","color"=>"#facc15"],
    ["name"=>"Cipher Paste",     "url"=>"cipher-paste/",           "icon"=>"📋","desc"=>"اشتراک‌گذاری سریع کد و متن",                 "badge"=>"Paste",      "cat"=>"dev","color"=>"#a78bfa"],

    ["name"=>"Cipher Vault",     "url"=>"#",           "icon"=>"🔐","desc"=>"ذخیره امن اطلاعات حساس",                     "badge"=>"Vault",      "cat"=>"security","color"=>"#ef4444","pro"=>true],
    ["name"=>"Cipher Passwords", "url"=>"#",       "icon"=>"🔑","desc"=>"مدیریت رمزهای عبور داخلی",                   "badge"=>"Passwords",  "cat"=>"security","color"=>"#f43f5e","pro"=>true],
    ["name"=>"Cipher Settings",  "url"=>"cipher-settings/",        "icon"=>"⚙️","desc"=>"تنظیمات سیستم، صفحه نمایش و امنیت",          "badge"=>"Settings",   "cat"=>"security","color"=>"#c084fc"],

    ["name"=>"Cipher Tasks",     "url"=>"cipher-tasks/",           "icon"=>"✅","desc"=>"مدیریت وظایف و تسک‌های تیمی",                "badge"=>"Tasks",      "cat"=>"productivity","color"=>"#4ade80"],
    ["name"=>"Cipher Calendar",  "url"=>"cipher-calendar/",        "icon"=>"📅","desc"=>"تقویم و زمان‌بندی رویدادهای سازمانی",        "badge"=>"Calendar",   "cat"=>"productivity","color"=>"#facc15"],
    ["name"=>"Cipher Notes",     "url"=>"cipher-notes/",           "icon"=>"🗒️","desc"=>"یادداشت‌های رنگی شخصی",                     "badge"=>"Notes",      "cat"=>"productivity","color"=>"#facc15"],
    ["name"=>"Cipher Todo",      "url"=>"cipher-todo/",            "icon"=>"📝","desc"=>"لیست کارهای روزانه با اولویت",               "badge"=>"Todo",       "cat"=>"productivity","color"=>"#4ade80"],
    ["name"=>"Cipher Docs",      "url"=>"cipher-docs/",            "icon"=>"📂","desc"=>"مدیریت و ذخیره فایل‌ها و مستندات",           "badge"=>"Docs",       "cat"=>"productivity","color"=>"#38bdf8"],
    ["name"=>"Cipher Links",     "url"=>"cipher-links/",           "icon"=>"🔗","desc"=>"مدیریت لینک‌های مهم سازمانی",                "badge"=>"Links",      "cat"=>"productivity","color"=>"#38bdf8"],
    ["name"=>"Cipher Timer",     "url"=>"cipher-timer/",           "icon"=>"⏱️","desc"=>"تایمر، کرونومتر و Pomodoro",                 "badge"=>"Timer",      "cat"=>"productivity","color"=>"#f43f5e"],

    ["name"=>"Cipher Gallery",   "url"=>"cipher-gallery/",         "icon"=>"🖼️","desc"=>"گالری تصاویر با lightbox و drag & drop",     "badge"=>"Gallery",    "cat"=>"creative","color"=>"#f472b6"],
    ["name"=>"Cipher Colors",    "url"=>"cipher-colors/",          "icon"=>"🎨","desc"=>"انتخاب رنگ و ساخت پالت رنگی",                "badge"=>"Colors",     "cat"=>"creative","color"=>"#f472b6"],
    ["name"=>"Cipher Music",     "url"=>"cipher-music/",           "icon"=>"🎵","desc"=>"سرویس پلیر موزیک اختصاصی",                   "badge"=>"Music",      "cat"=>"creative","color"=>"#f472b6"],
    ["name"=>"Cipher QR",        "url"=>"cipher-qr/",              "icon"=>"⬛","desc"=>"ساخت و اسکن QR Code اختصاصی",               "badge"=>"QR Code",    "cat"=>"creative","color"=>"#2dd4bf"],
    ["name"=>"Cipher Screenshot","url"=>"cipher-screenshot/",       "icon"=>"📸","desc"=>"ثبت صفحه، ویرایش و مدیریت تصاویر",           "badge"=>"Screenshot", "cat"=>"creative","color"=>"#2dd4bf"],

    ["name"=>"Cipher Calls",     "url"=>"cipher-calls/",           "icon"=>"📞","desc"=>"تماس ویدیویی و صوتی داخلی تیم",              "badge"=>"Calls",      "cat"=>"comms","color"=>"#2dd4bf"],
    ["name"=>"Cipher Email",     "url"=>"cipher-email/",           "icon"=>"📧","desc"=>"کلاینت ایمیل محلی و مدیریت ایمیل‌ها",        "badge"=>"Email",      "cat"=>"comms","color"=>"#f472b6"],
    ["name"=>"Cipher Wiki",      "url"=>"cipher-wiki/",            "icon"=>"📖","desc"=>"پایگاه دانش و مستندات داخلی",                "badge"=>"Wiki",       "cat"=>"comms","color"=>"#c084fc"],

    ["name"=>"Cipher Calc",      "url"=>"cipher-calc/",            "icon"=>"🧮","desc"=>"ماشین حساب پیشرفته با تبدیل سریع",           "badge"=>"Calc",       "cat"=>"tools","color"=>"#34d399"],
    ["name"=>"Cipher Converter", "url"=>"cipher-converter/",       "icon"=>"🔄","desc"=>"تبدیل واحدها، ارز و دما",                    "badge"=>"Converter",  "cat"=>"tools","color"=>"#fb923c"],
    ["name"=>"Cipher Appstore",  "url"=>"cipher-appstore/",        "icon"=>"📦","desc"=>"اپ استور داخلی Cipher OS",                  "badge"=>"App Store",  "cat"=>"tools","color"=>"#fb923c"],
    ["name"=>"Cipher Weather",   "url"=>"cipher-weather/",         "icon"=>"🌤️","desc"=>"آب و هوای شهرهای جهان — ۷ روز آینده",      "badge"=>"Weather",    "cat"=>"tools","color"=>"#38bdf8"],
    ["name"=>"Cipher Analytics", "url"=>"cipher-analytics/",       "icon"=>"📊","desc"=>"آمار و تحلیل سیستم، کاربران و کارکرد",       "badge"=>"Analytics",  "cat"=>"tools","color"=>"#00ff99"],
];

$categories = [
    "core"        => ["label" => "هسته سیستم",        "icon" => "⚡", "color" => "#00eaff"],
    "productivity"=> ["label" => "بهره‌وری",          "icon" => "📋", "color" => "#4ade80"],
    "devops"      => ["label" => "عملیات و زیرساخت",  "icon" => "🛰️", "color" => "#34d399"],
    "dev"         => ["label" => "توسعه‌دهندگان",      "icon" => "💻", "color" => "#a78bfa"],
    "security"    => ["label" => "امنیت",             "icon" => "🔐", "color" => "#ef4444"],
    "creative"    => ["label" => "خلاقیت",            "icon" => "🎨", "color" => "#f472b6"],
    "comms"       => ["label" => "ارتباطات",          "icon" => "📡", "color" => "#2dd4bf"],
    "tools"       => ["label" => "ابزارها",           "icon" => "🔧", "color" => "#fb923c"],
];

$totalApps = count($apps);
$onlineCount = $totalApps;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cipher OS — Enterprise Control Center</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg0:#03040d; --bg1:#070914; --bg2:#0c0f1e; --bg3:#111628; --bg4:#161c34;
  --glass:rgba(255,255,255,.042); --glass2:rgba(255,255,255,.07);
  --stroke:rgba(255,255,255,.08); --stroke2:rgba(0,234,255,.25);
  --text:#e8edf8; --muted:#5b6b8a; --muted2:#8899b8;
  --cyan:#00eaff; --purple:#7c3aed; --success:#00ff99; --warn:#f59e0b; --danger:#ef4444;
  --font-display:'Space Grotesk','Vazirmatn',system-ui,sans-serif;
  --font-mono:'Space Mono',monospace; --font-fa:'Vazirmatn',system-ui,sans-serif;
  --r:22px; --r2:16px;
  --shadow:0 24px 64px rgba(0,0,0,.55);
}

*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{min-height:100vh;background:var(--bg0);color:var(--text);font-family:var(--font-display);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}
::-webkit-scrollbar{width:10px;}
::-webkit-scrollbar-track{background:var(--bg0);}
::-webkit-scrollbar-thumb{background:var(--stroke2);border-radius:10px;border:2px solid var(--bg0);}

/* particle canvas */
#bgCanvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.55;}

body::before{content:'';position:fixed;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.06) 2px,rgba(0,0,0,.06) 4px);pointer-events:none;z-index:998;animation:scanmove 12s linear infinite;}
@keyframes scanmove{from{background-position:0 0}to{background-position:0 100px}}

.glow-orb{position:fixed;border-radius:50%;filter:blur(120px);pointer-events:none;z-index:0;}
.glow-orb-1{width:700px;height:700px;top:-250px;right:-200px;background:rgba(124,58,237,.18);}
.glow-orb-2{width:500px;height:500px;bottom:-200px;left:-150px;background:rgba(0,234,255,.1);}
.glow-orb-3{width:400px;height:400px;top:40%;left:30%;background:rgba(0,255,153,.05);}

.wrap{max-width:1480px;margin:0 auto;padding:24px 28px 60px;position:relative;z-index:1;}

/* ═══ TOPBAR ═══ */
.topbar{position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:28px;padding:14px 22px;background:rgba(3,4,13,.72);backdrop-filter:blur(20px);border:1px solid var(--stroke);border-radius:var(--r);}
.os-brand{display:flex;align-items:center;gap:14px;}
.os-logo{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,rgba(0,234,255,.2),rgba(124,58,237,.25));border:1px solid var(--stroke2);display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 0 24px rgba(0,234,255,.2);position:relative;overflow:hidden;}
.os-logo::after{content:'';position:absolute;inset:0;background:linear-gradient(45deg,transparent 40%,rgba(255,255,255,.15) 50%,transparent 60%);transform:translateX(-100%);animation:shine 3s infinite;}
@keyframes shine{0%,100%{transform:translateX(-100%)}50%{transform:translateX(100%)}}
.os-name{font-family:var(--font-mono);font-size:18px;letter-spacing:.05em;font-weight:700;color:var(--cyan);}
.os-name .ver{font-size:10px;color:var(--muted2);display:block;letter-spacing:.15em;margin-top:2px;font-weight:400;}

.glitch{position:relative;}
.glitch::before,.glitch::after{content:attr(data-text);position:absolute;top:0;left:0;width:100%;overflow:hidden;clip-path:inset(0 0 60% 0);}
.glitch::before{left:2px;color:#f43f5e;animation:glitch1 5s infinite;opacity:.6;}
.glitch::after{left:-2px;color:#00eaff;animation:glitch2 5s infinite;opacity:.6;}
@keyframes glitch1{0%,92%,100%{clip-path:inset(0 0 100% 0)}93%{clip-path:inset(20% 0 50% 0);transform:translate(-2px,1px)}95%{clip-path:inset(50% 0 20% 0);transform:translate(2px,-1px)}}
@keyframes glitch2{0%,90%,100%{clip-path:inset(0 0 100% 0)}91%{clip-path:inset(60% 0 10% 0);transform:translate(3px,-1px)}94%{clip-path:inset(30% 0 40% 0);transform:translate(-2px,2px)}}

.topbar-right{display:flex;align-items:center;gap:12px;}
.cmd-trigger{display:flex;align-items:center;gap:10px;height:42px;padding:0 14px 0 16px;background:var(--glass2);border:1px solid var(--stroke);border-radius:12px;color:var(--muted2);font-family:var(--font-fa);font-size:13px;cursor:pointer;transition:.2s;}
.cmd-trigger:hover{border-color:var(--stroke2);background:var(--glass);}
.cmd-trigger kbd{font-family:var(--font-mono);font-size:10px;background:var(--bg3);padding:2px 6px;border-radius:5px;border:1px solid var(--stroke);}
.btn-icon{width:42px;height:42px;border-radius:12px;background:var(--glass);border:1px solid var(--stroke);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:.2s;}
.btn-icon:hover{background:var(--glass2);border-color:var(--stroke2);transform:translateY(-1px);}
.btn-icon.danger:hover{border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.08);}

/* ═══ HERO ═══ */
.hero{position:relative;background:var(--glass);border:1px solid var(--stroke);border-radius:var(--r);padding:42px 40px;margin-bottom:24px;overflow:hidden;box-shadow:var(--shadow);}
.hero::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--cyan),var(--purple),var(--success),transparent);opacity:.8;}
.hero::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 80% 20%,rgba(0,234,255,.08),transparent 50%);pointer-events:none;}
.hero-content{position:relative;z-index:2;display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;}
.hero-tag{font-family:var(--font-mono);font-size:10px;letter-spacing:.3em;color:var(--cyan);margin-bottom:14px;opacity:.85;}
.hero-greeting{font-size:46px;font-weight:700;line-height:1.05;margin-bottom:12px;background:linear-gradient(135deg,#fff 30%,var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.hero-greeting span{background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.hero-sub{font-family:var(--font-fa);color:var(--muted2);font-size:15px;line-height:1.9;max-width:620px;}
.hero-stats{display:flex;gap:24px;margin-top:24px;flex-wrap:wrap;}
.hstat{display:flex;align-items:center;gap:10px;}
.hstat-num{font-family:var(--font-mono);font-size:28px;font-weight:700;color:var(--cyan);}
.hstat-label{font-family:var(--font-fa);font-size:11px;color:var(--muted);letter-spacing:.05em;}

.status-pill{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:99px;font-size:11px;font-family:var(--font-mono);background:rgba(0,255,153,.07);border:1px solid rgba(0,255,153,.25);color:var(--success);margin-top:18px;}
.status-pill .pulse-dot{width:7px;height:7px;border-radius:50%;background:var(--success);box-shadow:0 0 10px var(--success);animation:pulse 1.8s infinite;}
@keyframes pulse{0%,100%{opacity:.4}50%{opacity:1}}

/* hero clock ring */
.clock-ring{position:relative;width:170px;height:170px;flex-shrink:0;}
.clock-ring svg{transform:rotate(-90deg);}
.clock-ring .ring-bg{fill:none;stroke:var(--stroke);stroke-width:4;}
.clock-ring .ring-fg{fill:none;stroke:var(--cyan);stroke-width:4;stroke-linecap:round;transition:stroke-dashoffset 1s;filter:drop-shadow(0 0 6px var(--cyan));}
.clock-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.clock-time{font-family:var(--font-mono);font-size:26px;font-weight:700;color:var(--text);}
.clock-date{font-family:var(--font-fa);font-size:10px;color:var(--muted2);margin-top:4px;text-align:center;}

.hud-corner{position:absolute;width:20px;height:20px;border-color:var(--cyan);border-style:solid;opacity:.45;}
.hud-tl{top:14px;left:14px;border-width:2px 0 0 2px;}
.hud-tr{top:14px;right:14px;border-width:2px 2px 0 0;}
.hud-bl{bottom:14px;left:14px;border-width:0 0 2px 2px;}
.hud-br{bottom:14px;right:14px;border-width:0 2px 2px 0;}

/* ═══ NOTICE ═══ */
.notice{display:flex;align-items:center;gap:16px;padding:16px 22px;margin-bottom:24px;background:linear-gradient(90deg,rgba(0,234,255,.06),rgba(124,58,237,.04));border:1px solid rgba(0,234,255,.2);border-radius:var(--r2);font-family:var(--font-fa);font-size:14px;position:relative;overflow:hidden;}
.notice::before{content:'';position:absolute;right:0;top:0;bottom:0;width:3px;background:linear-gradient(180deg,var(--cyan),var(--purple));}
.notice-dot{width:9px;height:9px;min-width:9px;border-radius:50%;background:var(--cyan);box-shadow:0 0 14px var(--cyan);animation:pulse 2s infinite;}
.notice-label{font-size:9px;letter-spacing:.22em;color:var(--cyan);font-family:var(--font-mono);margin-bottom:4px;}

/* ═══ STATS BAR ═══ */
.stats-bar{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
.stat-box{background:var(--glass);border:1px solid var(--stroke);border-radius:var(--r2);padding:20px 22px;position:relative;overflow:hidden;transition:.3s;}
.stat-box:hover{border-color:var(--stroke2);transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.3);}
.stat-box::after{content:'';position:absolute;top:0;left:0;width:40%;height:2px;background:var(--sc,var(--cyan));opacity:.6;}
.stat-box .sb-icon{font-size:22px;margin-bottom:10px;}
.stat-box .sb-val{font-family:var(--font-mono);font-size:30px;font-weight:700;color:var(--sc,var(--cyan));line-height:1;}
.stat-box .sb-label{font-family:var(--font-fa);font-size:12px;color:var(--muted2);margin-top:6px;}
.stat-box .sb-trend{font-family:var(--font-mono);font-size:10px;color:var(--success);margin-top:8px;}

/* ═══ CATEGORY FILTER ═══ */
.cat-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;}
.cat-chip{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:99px;background:var(--glass);border:1px solid var(--stroke);font-family:var(--font-fa);font-size:12.5px;color:var(--muted2);cursor:pointer;transition:.2s;user-select:none;}
.cat-chip:hover{border-color:var(--stroke2);color:var(--text);}
.cat-chip.active{background:rgba(0,234,255,.1);border-color:var(--stroke2);color:var(--cyan);}
.cat-chip .cc-count{font-family:var(--font-mono);font-size:10px;background:var(--bg3);padding:1px 7px;border-radius:99px;color:var(--muted2);}
.cat-chip.active .cc-count{background:rgba(0,234,255,.15);color:var(--cyan);}

/* ═══ CATEGORY SECTIONS ═══ */
.cat-section{margin-bottom:42px;}
.cat-head{display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--stroke);}
.cat-head .ch-icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:22px;background:var(--cc,var(--glass));border:1px solid var(--stroke);flex-shrink:0;}
.cat-head .ch-title{font-size:20px;font-weight:700;font-family:var(--font-fa);}
.cat-head .ch-meta{font-family:var(--font-mono);font-size:11px;color:var(--muted);margin-top:3px;}

/* ═══ APP GRID ═══ */
.apps-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;}

.app-card{display:block;background:var(--bg2);border:1px solid var(--stroke);border-radius:var(--r2);padding:20px;position:relative;overflow:hidden;transition:transform .25s cubic-bezier(.25,.46,.45,.94),border-color .25s,box-shadow .25s;opacity:0;transform:translateY(20px);transform-style:preserve-3d;will-change:transform;}
.app-card.visible{opacity:1;transform:translateY(0);}
.app-card::before{content:'';position:absolute;inset:0;border-radius:var(--r2);background:radial-gradient(circle at var(--mx,50%) var(--my,0%),var(--ac,.6) 0%,transparent 60%);opacity:0;transition:opacity .3s;pointer-events:none;}
.app-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--acc,#00eaff);opacity:.4;transform:scaleX(0);transform-origin:right;transition:transform .3s;}
.app-card:hover{border-color:var(--acb,rgba(0,234,255,.5));box-shadow:0 20px 50px rgba(0,0,0,.4),0 0 0 1px var(--acb,rgba(0,234,255,.2));}
.app-card:hover::before{opacity:.1;}
.app-card:hover::after{transform:scaleX(1);}

.card-glow{position:absolute;top:-30px;right:-30px;width:80px;height:80px;border-radius:50%;background:var(--acc,#00eaff);filter:blur(40px);opacity:0;transition:opacity .3s;}
.app-card:hover .card-glow{opacity:.25;}

.card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;position:relative;z-index:2;}
.card-icon{font-size:32px;line-height:1;transition:transform .3s;}
.app-card:hover .card-icon{transform:scale(1.12) rotate(-4deg);}
.card-badge{font-size:9px;font-family:var(--font-mono);letter-spacing:.1em;padding:4px 8px;border-radius:6px;background:rgba(255,255,255,.05);color:var(--muted2);border:1px solid var(--stroke);}
.card-name{font-size:15px;font-weight:600;margin-bottom:6px;position:relative;z-index:2;}
.card-desc{font-size:11.5px;color:var(--muted2);line-height:1.7;font-family:var(--font-fa);height:40px;overflow:hidden;position:relative;z-index:2;}
.card-footer{display:flex;justify-content:space-between;align-items:center;margin-top:16px;position:relative;z-index:2;}
.card-status{font-size:10px;font-family:var(--font-mono);display:flex;align-items:center;gap:5px;}
.card-status .cs-dot{width:6px;height:6px;border-radius:50%;background:var(--success);box-shadow:0 0 6px var(--success);}
.card-enter{font-size:11px;font-family:var(--font-mono);color:var(--cyan);opacity:0;transform:translateX(-6px);transition:.2s;}
.app-card:hover .card-enter{opacity:1;transform:translateX(0);}

.no-results{grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--muted);font-family:var(--font-fa);}
.no-results .nr-icon{font-size:48px;margin-bottom:16px;opacity:.5;}

/* ═══ COMMAND PALETTE ═══ */
.cmd-overlay{position:fixed;inset:0;background:rgba(3,4,13,.82);backdrop-filter:blur(8px);z-index:500;display:none;align-items:flex-start;justify-content:center;padding-top:12vh;}
.cmd-overlay.show{display:flex;animation:fadeIn .15s;}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.cmd-box{width:min(100%,620px);background:var(--bg1);border:1px solid var(--stroke2);border-radius:var(--r);overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,.7),0 0 60px rgba(0,234,255,.1);animation:slideUp .2s;}
@keyframes slideUp{from{transform:translateY(-20px);opacity:0}to{transform:translateY(0);opacity:1}}
.cmd-input-wrap{display:flex;align-items:center;gap:12px;padding:18px 22px;border-bottom:1px solid var(--stroke);}
.cmd-input-wrap span{font-size:18px;opacity:.5;}
.cmd-input{flex:1;background:transparent;border:none;color:var(--text);font-family:var(--font-fa);font-size:16px;outline:none;}
.cmd-input::placeholder{color:var(--muted);}
.cmd-results{max-height:50vh;overflow-y:auto;padding:8px;}
.cmd-result{display:flex;align-items:center;gap:14px;padding:11px 14px;border-radius:11px;cursor:pointer;transition:.12s;}
.cmd-result:hover,.cmd-result.selected{background:rgba(0,234,255,.08);}
.cmd-result .cr-icon{width:36px;height:36px;border-radius:10px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.cmd-result .cr-name{font-size:14px;font-weight:600;}
.cmd-result .cr-desc{font-size:11px;color:var(--muted2);font-family:var(--font-fa);}
.cmd-result .cr-badge{margin-right:auto;font-family:var(--font-mono);font-size:9px;padding:2px 8px;border-radius:5px;background:var(--bg3);color:var(--muted2);}
.cmd-foot{display:flex;justify-content:space-between;padding:10px 18px;border-top:1px solid var(--stroke);font-family:var(--font-mono);font-size:10px;color:var(--muted);}
.cmd-foot kbd{background:var(--bg3);padding:2px 6px;border-radius:4px;border:1px solid var(--stroke);margin:0 2px;}

/* ═══ TOAST ═══ */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--bg3);border:1px solid var(--stroke2);padding:14px 22px;border-radius:12px;font-family:var(--font-fa);font-size:13px;z-index:600;opacity:0;transition:.3s;pointer-events:none;box-shadow:0 12px 32px rgba(0,0,0,.4);}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);}

/* ═══ FOOTER ═══ */
.footer{margin-top:50px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-top:24px;border-top:1px solid var(--stroke);font-size:11px;color:var(--muted);font-family:var(--font-mono);}

/* ═══ RESPONSIVE ═══ */
@media(max-width:980px){.stats-bar{grid-template-columns:repeat(2,1fr);}.hero-content{grid-template-columns:1fr;}.clock-ring{display:none;}}
@media(max-width:720px){.wrap{padding:16px 16px 48px;}.hero-greeting{font-size:30px;}.hero{padding:28px 22px;}.cat-chip{font-size:11px;padding:7px 12px;}.stats-bar{grid-template-columns:repeat(2,1fr);gap:10px;}.cmd-trigger span,.cmd-trigger kbd{display:none;}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important;}}
</style>
</head>
<body>

<canvas id="bgCanvas"></canvas>
<div class="glow-orb glow-orb-1"></div>
<div class="glow-orb glow-orb-2"></div>
<div class="glow-orb glow-orb-3"></div>

<div class="wrap">

  <!-- ═══ TOPBAR ═══ -->
  <header class="topbar">
    <div class="os-brand">
      <div class="os-logo">⚡</div>
      <div>
        <div class="os-name glitch" data-text="CIPHER OS">CIPHER OS</div>
        <span class="os-name ver">v4.0 · ENTERPRISE</span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="cmd-trigger" onclick="openCmd()">
        🔍 <span>جستجوی سریع...</span> <kbd>Ctrl+K</kbd>
      </div>
      <div class="btn-icon" title="تنظیمات" onclick="window.location.href='admin.php'">⚙️</div>
      <div class="btn-icon danger" title="خروج" onclick="if(confirm('خارج شوید؟'))window.location.href='logout.php'">🚪</div>
    </div>
  </header>

  <!-- ═══ HERO ═══ -->
  <section class="hero">
    <div class="hud-corner hud-tl"></div><div class="hud-corner hud-tr"></div>
    <div class="hud-corner hud-bl"></div><div class="hud-corner hud-br"></div>
    <div class="hero-content">
      <div>
        <div class="hero-tag">// ENTERPRISE CONTROL CENTER</div>
        <h1 class="hero-greeting" id="greeting">خوش آمدید، <span>PRODBY026B</span></h1>
        <p class="hero-sub">مرکز یکپارچه کنترل <?= $totalApps ?> سرویس فعال Cipher OS — همه‌چیز در یک داشبورد قدرتمند، امن و سریع.</p>
        <div class="hero-stats">
          <div class="hstat"><div class="hstat-num" data-count="<?= $totalApps ?>">0</div><div class="hstat-label">سرویس فعال</div></div>
          <div class="hstat"><div class="hstat-num"><?= count($categories) ?></div><div class="hstat-label">دسته‌بندی</div></div>
          <div class="hstat"><div class="hstat-num" style="color:var(--success)">99.9%</div><div class="hstat-label">آپتایم</div></div>
        </div>
        <div class="status-pill"><span class="pulse-dot"></span> All Systems Operational</div>
      </div>
      <div class="clock-ring">
        <svg width="170" height="170" viewBox="0 0 170 170">
          <circle class="ring-bg" cx="85" cy="85" r="76"/>
          <circle class="ring-fg" cx="85" cy="85" r="76" stroke-dasharray="477.5" stroke-dashoffset="477.5" id="ringFg"/>
        </svg>
        <div class="clock-center">
          <div class="clock-time" id="clockTime">--:--</div>
          <div class="clock-date" id="clockDate">--</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ NOTICE ═══ -->
  <div class="notice">
    <div class="notice-dot"></div>
    <div>
      <div class="notice-label">SYSTEM BROADCAST</div>
      <strong style="font-family:var(--font-fa);font-size:14px;"><?= htmlspecialchars($dashboardData['management_notice']) ?></strong>
    </div>
    <div style="margin-right:auto;font-family:var(--font-mono);font-size:10px;color:var(--muted);white-space:nowrap;" id="broadcastTime"></div>
  </div>

  <!-- ═══ STATS BAR ═══ -->
  <div class="stats-bar">
    <div class="stat-box" style="--sc:var(--cyan)">
      <div class="sb-icon">🛰️</div>
      <div class="sb-val" data-count="<?= $onlineCount ?>">0</div>
      <div class="sb-label">سرویس آنلاین</div>
      <div class="sb-trend">▲ ۱۰۰٪ فعال</div>
    </div>
    <div class="stat-box" style="--sc:var(--success)">
      <div class="sb-icon">⚡</div>
      <div class="sb-val">99.9<span style="font-size:18px">%</span></div>
      <div class="sb-label">آپتایم سیستم</div>
      <div class="sb-trend">▲ پایدار</div>
    </div>
    <div class="stat-box" style="--sc:var(--purple)">
      <div class="sb-icon">🔐</div>
      <div class="sb-val">A+</div>
      <div class="sb-label">امنیت</div>
      <div class="sb-trend">▲ رمزنگاری فعال</div>
    </div>
    <div class="stat-box" style="--sc:var(--warn)">
      <div class="sb-icon">📊</div>
      <div class="sb-val" data-count="<?= $totalApps ?>">0</div>
      <div class="sb-label">کل ماژول‌ها</div>
      <div class="sb-trend">▲ به‌روز</div>
    </div>
  </div>

  <!-- ═══ CATEGORY FILTER ═══ -->
  <div class="cat-filter" id="catFilter">
    <div class="cat-chip active" data-cat="all">🌐 همه <span class="cc-count"><?= $totalApps ?></span></div>
    <?php foreach ($categories as $key => $c): ?>
      <?php $cnt = count(array_filter($apps, fn($a) => $a['cat'] === $key)); ?>
      <div class="cat-chip" data-cat="<?= $key ?>" style="--cc:<?= $c['color'] ?>22"><?= $c['icon'] ?> <?= $c['label'] ?> <span class="cc-count"><?= $cnt ?></span></div>
    <?php endforeach; ?>
  </div>

  <!-- ═══ APPS BY CATEGORY ═══ -->
  <div id="appsContainer">
    <?php foreach ($categories as $catKey => $cat): ?>
      <?php $catApps = array_filter($apps, fn($a) => $a['cat'] === $catKey); ?>
      <?php if (empty($catApps)) continue; ?>
      <section class="cat-section" data-cat="<?= $catKey ?>">
        <div class="cat-head">
          <div class="ch-icon" style="--cc:rgba(<?= hexdec(substr($cat['color'],1,2)) ?>,<?= hexdec(substr($cat['color'],3,2)) ?>,<?= hexdec(substr($cat['color'],5,2)) ?>,.12);color:<?= $cat['color'] ?>"><?= $cat['icon'] ?></div>
          <div>
            <div class="ch-title"><?= $cat['label'] ?></div>
            <div class="ch-meta"><?= count($catApps) ?> سرویس · CATEGORY/<?= strtoupper($catKey) ?></div>
          </div>
        </div>
        <div class="apps-grid">
          <?php foreach ($catApps as $i => $app): $isPro = !empty($app['pro']); ?>
          <a href="<?= $isPro ? 'javascript:void(0)' : htmlspecialchars($app['url']) ?>" onclick="<?= $isPro ? "alert('این سرویس فقط در نسخه Pro فعال است 🔒'); return false;" : '' ?>" class="app-card<?= $isPro ? ' app-card-locked' : '' ?>" data-name="<?= strtolower($app['name'].' '.$app['desc'].' '.$app['badge']) ?>" data-cat="<?= $app['cat'] ?>" data-delay="<?= $i * 50 ?>" style="--acc:<?= $app['color'] ?>;--acb:<?= $app['color'] ?>55;--ac:<?= $app['color'] ?><?= $isPro ? ';opacity:.55;filter:grayscale(.4);cursor:not-allowed;' : '' ?>">
            <div class="card-glow"></div>
            <div class="card-top">
              <span class="card-icon"><?= $app['icon'] ?></span>
              <span class="card-badge"><?= $isPro ? '🔒 PRO' : htmlspecialchars($app['badge']) ?></span>
            </div>
            <div class="card-name"><?= htmlspecialchars($app['name']) ?></div>
            <div class="card-desc"><?= htmlspecialchars($app['desc']) ?></div>
            <div class="card-footer">
              <span class="card-status"><span class="cs-dot"></span> <?= $isPro ? 'Locked' : 'Online' ?></span>
              <span class="card-enter"><?= $isPro ? '🔒 Pro Only' : 'Enter →' ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
    <div class="no-results" id="noResults" style="display:none;">
      <div class="nr-icon">🔍</div>
      <div>سرویسی با این مشخصات یافت نشد</div>
    </div>
  </div>

  <!-- ═══ FOOTER ═══ -->
  <footer class="footer">
    <span>CIPHER OS v4.0 · PRODBY026B · <?= date('Y') ?></span>
    <span id="footerTime"></span>
    <span>● ALL SYSTEMS GO</span>
  </footer>

</div>

<!-- ═══ COMMAND PALETTE ═══ -->
<div class="cmd-overlay" id="cmdOverlay" onclick="if(event.target===this)closeCmd()">
  <div class="cmd-box">
    <div class="cmd-input-wrap">
      <span>🔍</span>
      <input type="text" class="cmd-input" id="cmdInput" placeholder="جستجو یا اجرای سرویس..." autocomplete="off">
    </div>
    <div class="cmd-results" id="cmdResults"></div>
    <div class="cmd-foot">
      <span><kbd>↑</kbd><kbd>↓</kbd> انتخاب</span>
      <span><kbd>Enter</kbd> اجرا · <kbd>Esc</kbd> بستن</span>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ═══ APP DATA (for command palette) ═══ */
const APPS = <?= json_encode(array_map(fn($a) => ['name'=>$a['name'],'url'=>$a['url'],'icon'=>$a['icon'],'badge'=>$a['badge'],'desc'=>$a['desc'],'cat'=>$a['cat']], $apps)) ?>;
const CATS = <?= json_encode($categories) ?>;

/* ═══ PARTICLE NETWORK BACKGROUND ═══ */
(function(){
  const canvas = document.getElementById('bgCanvas');
  const ctx = canvas.getContext('2d');
  let w, h, particles = [];
  const COUNT = 60;
  function resize(){ w = canvas.width = innerWidth; h = canvas.height = innerHeight; }
  resize(); addEventListener('resize', resize);
  function init(){
    particles = [];
    for(let i=0;i<COUNT;i++){
      particles.push({x:Math.random()*w, y:Math.random()*h, vx:(Math.random()-.5)*.4, vy:(Math.random()-.5)*.4, r:Math.random()*1.8+.5});
    }
  }
  init();
  const mouse = {x:-1000,y:-1000};
  addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
  function loop(){
    ctx.clearRect(0,0,w,h);
    for(let i=0;i<particles.length;i++){
      const p = particles[i];
      p.x += p.vx; p.y += p.vy;
      if(p.x<0||p.x>w) p.vx*=-1;
      if(p.y<0||p.y>h) p.vy*=-1;
      // mouse attraction
      const dx = mouse.x-p.x, dy = mouse.y-p.y;
      const dist = Math.sqrt(dx*dx+dy*dy);
      if(dist < 150){ p.x += dx*0.008; p.y += dy*0.008; }
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
      ctx.fillStyle = 'rgba(0,234,255,.5)';
      ctx.fill();
      // connections
      for(let j=i+1;j<particles.length;j++){
        const q = particles[j];
        const ddx = p.x-q.x, ddy = p.y-q.y;
        const d = Math.sqrt(ddx*ddx+ddy*ddy);
        if(d < 130){
          ctx.beginPath();
          ctx.moveTo(p.x,p.y); ctx.lineTo(q.x,q.y);
          ctx.strokeStyle = 'rgba(0,234,255,'+(.15*(1-d/130))+')';
          ctx.lineWidth = .6;
          ctx.stroke();
        }
      }
    }
    requestAnimationFrame(loop);
  }
  loop();
})();

/* ═══ CLOCK + RING ═══ */
const ringFg = document.getElementById('ringFg');
const CIRC = 477.5;
function tick(){
  const now = new Date();
  const t = now.toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit'});
  const tFull = now.toLocaleTimeString('fa-IR',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  const d = now.toLocaleDateString('fa-IR',{weekday:'long',day:'numeric',month:'long'});
  document.getElementById('clockTime').textContent = t;
  document.getElementById('clockDate').textContent = d;
  document.getElementById('broadcastTime').textContent = d;
  document.getElementById('footerTime').textContent = tFull;
  // ring = seconds progress
  const sec = now.getSeconds();
  ringFg.style.strokeDashoffset = CIRC - (CIRC * sec / 60);
}
tick(); setInterval(tick, 1000);

/* ═══ GREETING ═══ */
const hr = new Date().getHours();
const greet = hr<5?'شب بخیر':hr<12?'صبح بخیر':hr<17?'روز بخیر':hr<21?'عصر بخیر':'شب بخیر';
document.getElementById('greeting').innerHTML = greet + '، <span>PRODBY026B</span>';

/* ═══ ANIMATED COUNTERS ═══ */
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  let cur = 0;
  const step = Math.max(1, Math.ceil(target/30));
  const timer = setInterval(() => {
    cur += step;
    if(cur >= target){ cur = target; clearInterval(timer); }
    el.textContent = cur;
  }, 35);
});

/* ═══ CARD STAGGER + 3D TILT ═══ */
const cards = document.querySelectorAll('.app-card');
const obs = new IntersectionObserver(entries => {
  entries.forEach(en => {
    if(en.isIntersecting){
      const d = parseInt(en.target.dataset.delay||0);
      setTimeout(() => en.target.classList.add('visible'), d);
      obs.unobserve(en.target);
    }
  });
}, {threshold:.1});
cards.forEach(c => obs.observe(c));

// 3D tilt on hover
cards.forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    const x = e.clientX - r.left;
    const y = e.clientY - r.top;
    const rx = ((y/r.height) - .5) * -8;
    const ry = ((x/r.width) - .5) * 8;
    card.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-4px)`;
    card.style.setProperty('--mx', (x/r.width*100)+'%');
    card.style.setProperty('--my', (y/r.height*100)+'%');
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = '';
  });
});

/* ═══ CATEGORY FILTER ═══ */
const sections = document.querySelectorAll('.cat-section');
const noResults = document.getElementById('noResults');
document.querySelectorAll('.cat-chip').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const cat = chip.dataset.cat;
    let visible = 0;
    sections.forEach(sec => {
      const show = (cat === 'all' || sec.dataset.cat === cat);
      sec.style.display = show ? '' : 'none';
      if(show) visible += sec.querySelectorAll('.app-card').length;
    });
    noResults.style.display = visible === 0 ? '' : 'none';
    // smooth scroll to first visible
    if(cat !== 'all'){
      const first = document.querySelector('.cat-section:not([style*="display: none"])');
      if(first) first.scrollIntoView({behavior:'smooth',block:'start'});
    }
  });
});

/* ═══ COMMAND PALETTE ═══ */
const cmdOverlay = document.getElementById('cmdOverlay');
const cmdInput = document.getElementById('cmdInput');
const cmdResults = document.getElementById('cmdResults');
let cmdSel = 0;
let cmdFiltered = [];

function openCmd(){
  cmdOverlay.classList.add('show');
  cmdInput.value = '';
  cmdSel = 0;
  renderCmd('');
  setTimeout(() => cmdInput.focus(), 50);
}
function closeCmd(){ cmdOverlay.classList.remove('show'); }

function renderCmd(q){
  const ql = q.toLowerCase().trim();
  cmdFiltered = APPS.filter(a => !ql || (a.name+' '+a.desc+' '+a.badge+' '+a.cat).toLowerCase().includes(ql));
  if(!cmdFiltered.length){
    cmdResults.innerHTML = '<div style="padding:30px;text-align:center;color:var(--muted);font-family:var(--font-fa)">نتیجه‌ای یافت نشد</div>';
    return;
  }
  cmdSel = Math.min(cmdSel, cmdFiltered.length-1);
  cmdResults.innerHTML = cmdFiltered.slice(0,8).map((a,i) => `
    <div class="cmd-result ${i===cmdSel?'selected':''}" data-url="${a.url}" data-i="${i}">
      <div class="cr-icon">${a.icon}</div>
      <div style="flex:1;min-width:0">
        <div class="cr-name">${esc(a.name)}</div>
        <div class="cr-desc">${esc(a.desc)}</div>
      </div>
      <span class="cr-badge">${esc(a.badge)}</span>
    </div>`).join('');
  cmdResults.querySelectorAll('.cmd-result').forEach((el,i) => {
    el.addEventListener('click', () => execCmd(i));
    el.addEventListener('mouseenter', () => { cmdSel = i; updateCmdSel(); });
  });
}
function updateCmdSel(){
  cmdResults.querySelectorAll('.cmd-result').forEach((el,i) => el.classList.toggle('selected', i===cmdSel));
}
function execCmd(i){
  const a = cmdFiltered[i];
  if(!a) return;
  closeCmd();
  window.location.href = a.url;
}
cmdInput.addEventListener('input', () => { cmdSel = 0; renderCmd(cmdInput.value); });
cmdInput.addEventListener('keydown', e => {
  if(e.key === 'ArrowDown'){ e.preventDefault(); cmdSel = Math.min(cmdSel+1, Math.min(7,cmdFiltered.length-1)); updateCmdSel(); }
  else if(e.key === 'ArrowUp'){ e.preventDefault(); cmdSel = Math.max(cmdSel-1, 0); updateCmdSel(); }
  else if(e.key === 'Enter'){ e.preventDefault(); execCmd(cmdSel); }
  else if(e.key === 'Escape'){ closeCmd(); }
});

/* ═══ KEYBOARD ═══ */
document.addEventListener('keydown', e => {
  if((e.ctrlKey||e.metaKey) && e.key.toLowerCase()==='k'){ e.preventDefault(); openCmd(); }
  if(e.key === 'Escape') closeCmd();
});

/* ═══ HELPERS ═══ */
function esc(s){const d=document.createElement('div');d.textContent=s??'';return d.innerHTML;}
function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),2200);}

// welcome toast
setTimeout(() => toast('⚡ به Cipher OS خوش آمدید'), 600);
</script>

</body>
</html>
