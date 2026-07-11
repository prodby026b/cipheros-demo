<?php
// Cipher OS — Shared Theme Helper
// Usage: include '../cipher-core/cipher-theme.php';
// Then call: cipher_head('Page Title', '#accent-color')
//            cipher_navbar('Service Name', '🎬', '../')
//            cipher_foot()

function cipher_head(string $title, string $accent = '#00eaff', bool $rtl = true): void {
    $dir = $rtl ? 'rtl' : 'ltr';
    echo <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="{$dir}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title} — Cipher OS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg0:#03040d;--bg1:#070914;--bg2:#0c0f1e;--bg3:#111628;
  --glass:rgba(255,255,255,.045);--glass2:rgba(255,255,255,.075);
  --stroke:rgba(255,255,255,.08);--stroke2:rgba(0,234,255,.22);
  --text:#e8edf8;--muted:#5b6b8a;--muted2:#8899b8;
  --cyan:#00eaff;--purple:#7c3aed;
  --success:#00ff99;--warn:#f59e0b;--danger:#ef4444;
  --accent:{$accent};
  --font:'Space Grotesk','Vazirmatn',system-ui,sans-serif;
  --mono:'Space Mono',monospace;
  --fa:'Vazirmatn',system-ui,sans-serif;
  --r:20px;--r2:14px;
  --shadow:0 24px 64px rgba(0,0,0,.55);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{min-height:100vh;background:var(--bg0);color:var(--text);font-family:var(--font);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}
body::before{
  content:'';position:fixed;inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.07) 2px,rgba(0,0,0,.07) 4px);
  pointer-events:none;z-index:999;
}
.page-orb{position:fixed;border-radius:50%;filter:blur(130px);pointer-events:none;z-index:0;}
.page-orb-1{width:700px;height:700px;top:-250px;right:-200px;background:rgba(124,58,237,.14);}
.page-orb-2{width:500px;height:500px;bottom:-200px;left:-150px;background:rgba(0,234,255,.07);}
.page-orb-3{width:280px;height:280px;top:45%;right:25%;background:rgba(0,0,0,.0);background:color-mix(in srgb,{$accent} 10%,transparent);}

/* NAVBAR */
.c-nav{
  position:sticky;top:0;z-index:900;
  background:rgba(3,4,13,.82);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--stroke);
  padding:0 28px;height:58px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;
}
.c-nav-brand{display:flex;align-items:center;gap:10px;font-family:var(--mono);font-size:13px;letter-spacing:.04em;}
.c-nav-icon{
  width:36px;height:36px;border-radius:10px;
  background:color-mix(in srgb,{$accent} 15%,transparent);
  border:1px solid color-mix(in srgb,{$accent} 35%,transparent);
  display:flex;align-items:center;justify-content:center;font-size:18px;
}
.c-nav-name{color:var(--text);font-weight:600;}
.c-nav-sub{font-size:10px;color:var(--muted);letter-spacing:.15em;}
.c-nav-right{display:flex;align-items:center;gap:10px;}
.c-nav-badge{
  font-size:9px;font-family:var(--mono);letter-spacing:.12em;
  padding:4px 10px;border-radius:99px;
  background:color-mix(in srgb,{$accent} 12%,transparent);
  color:{$accent};
  border:1px solid color-mix(in srgb,{$accent} 28%,transparent);
}
.c-nav-home{
  display:flex;align-items:center;gap:6px;
  padding:7px 14px;border-radius:10px;
  background:var(--glass);border:1px solid var(--stroke);
  font-size:12px;color:var(--muted2);transition:.2s;cursor:pointer;
}
.c-nav-home:hover{border-color:var(--stroke2);color:var(--cyan);}

/* PAGE WRAPPER */
.c-wrap{max-width:1300px;margin:0 auto;padding:32px 28px 64px;position:relative;z-index:1;}

/* PANELS */
.c-panel{
  background:var(--glass);border:1px solid var(--stroke);
  border-radius:var(--r);padding:24px;box-shadow:var(--shadow);
  position:relative;overflow:hidden;
}
.c-panel::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,{$accent},transparent);
  opacity:.5;
}
.hud-c{position:absolute;width:14px;height:14px;border-style:solid;border-color:{$accent};opacity:.35;}
.hud-tl{top:10px;left:10px;border-width:2px 0 0 2px;}
.hud-tr{top:10px;right:10px;border-width:2px 2px 0 0;}
.hud-bl{bottom:10px;left:10px;border-width:0 0 2px 2px;}
.hud-br{bottom:10px;right:10px;border-width:0 2px 2px 0;}

/* TYPOGRAPHY */
.c-title{font-size:22px;font-weight:700;margin-bottom:6px;}
.c-sub{font-family:var(--fa);color:var(--muted2);font-size:13.5px;line-height:1.8;}
.c-label{font-size:10px;letter-spacing:.18em;font-family:var(--mono);color:var(--muted);margin-bottom:6px;}
.c-tag{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 12px;border-radius:99px;font-size:11px;
  font-family:var(--mono);
  background:rgba(0,255,153,.07);border:1px solid rgba(0,255,153,.2);color:var(--success);
}

/* INPUTS */
.c-input,.c-textarea{
  width:100%;background:var(--bg2);border:1px solid var(--stroke);
  border-radius:12px;color:var(--text);font-family:var(--fa);
  padding:12px 16px;font-size:14px;outline:none;transition:.2s;
}
.c-input:focus,.c-textarea:focus{border-color:color-mix(in srgb,{$accent} 50%,transparent);box-shadow:0 0 0 3px color-mix(in srgb,{$accent} 10%,transparent);}
.c-input::placeholder,.c-textarea::placeholder{color:var(--muted);}
.c-textarea{resize:vertical;min-height:100px;line-height:1.7;}

/* BUTTON */
.c-btn{
  padding:11px 22px;border:none;border-radius:12px;cursor:pointer;
  font-family:var(--mono);font-size:12px;font-weight:700;letter-spacing:.08em;
  background:linear-gradient(135deg,{$accent},{$accent}99);
  color:var(--bg0);transition:.2s;display:inline-flex;align-items:center;gap:8px;
}
.c-btn:hover{opacity:.88;transform:translateY(-1px);}
.c-btn-ghost{
  padding:9px 18px;border-radius:10px;cursor:pointer;
  font-family:var(--mono);font-size:11px;letter-spacing:.06em;
  background:var(--glass2);border:1px solid var(--stroke);color:var(--muted2);transition:.2s;
}
.c-btn-ghost:hover{border-color:var(--stroke2);color:var(--cyan);}

/* SECTION HEADER */
.c-sec{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.c-sec-title{font-size:15px;font-weight:600;}

/* EMPTY STATE */
.c-empty{
  text-align:center;padding:60px 20px;
  font-family:var(--fa);color:var(--muted);
}
.c-empty .c-empty-icon{font-size:48px;margin-bottom:16px;opacity:.4;}
.c-empty p{font-size:14px;line-height:1.8;}

/* GRID */
.c-grid-2{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
.c-grid-3{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;}

/* CARD */
.c-card{
  background:var(--bg2);border:1px solid var(--stroke);border-radius:var(--r2);
  padding:18px;transition:.22s;
}
.c-card:hover{border-color:color-mix(in srgb,{$accent} 45%,transparent);transform:translateY(-3px);background:var(--bg3);}

/* TOAST */
.c-toast{
  position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(60px);
  background:var(--bg2);border:1px solid var(--stroke2);border-radius:14px;
  padding:12px 22px;font-family:var(--mono);font-size:12px;color:var(--cyan);
  box-shadow:var(--shadow);transition:.3s;z-index:9999;white-space:nowrap;
  pointer-events:none;
}
.c-toast.show{transform:translateX(-50%) translateY(0);}

@keyframes pulse{0%{opacity:.3}50%{opacity:1}100%{opacity:.3}}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.fade-in{animation:fadeIn .4s ease forwards;}
@media(max-width:768px){.c-wrap{padding:16px 16px 48px;}.c-nav{padding:0 16px;}}
</style>
HTML;
}

function cipher_navbar(string $name, string $icon, string $homeUrl = '../', string $badge = 'CIPHER OS'): void {
    echo <<<HTML
<div class="page-orb page-orb-1"></div>
<div class="page-orb page-orb-2"></div>
<div class="page-orb page-orb-3"></div>
<nav class="c-nav">
  <div class="c-nav-brand">
    <div class="c-nav-icon">{$icon}</div>
    <div>
      <div class="c-nav-name">{$name}</div>
      <div class="c-nav-sub">CIPHER OS · PRODBY026B</div>
    </div>
  </div>
  <div class="c-nav-right">
    <span class="c-nav-badge">{$badge}</span>
    <a href="{$homeUrl}" class="c-nav-home">⌂ Dashboard</a>
  </div>
</nav>
HTML;
}

function cipher_foot(): void {
    echo <<<HTML
<div id="c-toast" class="c-toast"></div>
<script>
function cToast(msg, dur=2400){
  const t=document.getElementById('c-toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),dur);
}
document.querySelectorAll('.fade-in-item').forEach((el,i)=>{
  el.style.animationDelay=i*50+'ms';
  el.classList.add('fade-in');
});
</script>
</body></html>
HTML;
}
?>
