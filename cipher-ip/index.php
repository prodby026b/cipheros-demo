<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher IP', '#34d399');
cipher_navbar('Cipher IP', '🌐', '../', 'IP');
$myIP = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'N/A';
?>
<div class="c-wrap" style="max-width:900px;">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🌐 Cipher IP</div>
  <div class="c-sub">اطلاعات IP، Whois، DNS Lookup و ابزارهای شبکه</div>
</div>

<!-- My IP -->
<div class="c-panel" style="margin-bottom:20px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">📡 IP شما</div>
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="font-family:var(--mono);font-size:32px;font-weight:700;color:var(--cyan);"><?=htmlspecialchars($myIP)?></div>
    <button onclick="copyText('<?=htmlspecialchars($myIP)?>')" class="c-btn" style="font-size:11px;padding:8px 14px;">📋 کپی</button>
    <button onclick="lookupIP('<?=htmlspecialchars($myIP)?>')" class="c-btn-ghost" style="font-size:11px;padding:8px 14px;">🔍 اطلاعات</button>
  </div>
</div>

<!-- Lookup -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">🔍 IP Lookup</div>
  <div style="display:flex;gap:10px;margin-bottom:14px;">
    <input id="ipInput" class="c-input" placeholder="IP یا دامنه..." style="flex:1;">
    <button onclick="lookupIP()" class="c-btn" style="font-size:12px;padding:11px 16px;">بررسی</button>
  </div>
  <div id="ipResult" style="display:none;"></div>
</div>
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">🔤 DNS Lookup</div>
  <div style="display:flex;gap:10px;margin-bottom:14px;">
    <input id="dnsInput" class="c-input" placeholder="دامنه: google.com" style="flex:1;">
    <button onclick="dnsLookup()" class="c-btn" style="font-size:12px;padding:11px 16px;">DNS</button>
  </div>
  <div id="dnsResult" style="display:none;"></div>
</div>
</div>

<!-- Network Tools -->
<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-sec-title" style="margin-bottom:14px;">🛠️ ابزارهای سریع</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php
    $tools=[['8.8.8.8','Google DNS'],['1.1.1.1','Cloudflare'],['prodby026b.sbs','My Site'],['github.com','GitHub']];
    foreach($tools as [$ip,$label]):?>
    <button onclick="lookupIP('<?=$ip?>')" class="c-btn-ghost" style="font-size:12px;padding:8px 14px;">🔍 <?=$label?></button>
    <?php endforeach;?>
  </div>
</div>
</div>
<script>
function copyText(t){navigator.clipboard.writeText(t);cToast('✅ کپی شد');}

async function lookupIP(ip){
  ip = ip || document.getElementById('ipInput').value.trim();
  if(!ip){cToast('⚠️ IP را وارد کنید');return;}
  document.getElementById('ipInput').value = ip;
  const res = document.getElementById('ipResult');
  res.innerHTML = '<div style="font-family:var(--mono);font-size:12px;color:var(--muted);">در حال بارگذاری...</div>';
  res.style.display='block';
  try {
    const r = await fetch(`https://ipapi.co/${ip}/json/`);
    const d = await r.json();
    if(d.error){res.innerHTML=`<div style="color:var(--danger);font-family:var(--fa);font-size:13px;">❌ ${d.reason||'یافت نشد'}</div>`;return;}
    const rows=[
      ['IP',d.ip],['شهر',d.city],['کشور',d.country_name+' '+d.country_flag_emoji],
      ['ISP',d.org],['Timezone',d.timezone],['Latitude',d.latitude],['Longitude',d.longitude],
      ['ASN',d.asn],
    ];
    res.innerHTML = rows.map(([k,v])=>`
      <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--stroke);">
        <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">${k}</span>
        <span style="font-family:var(--mono);font-size:11px;color:var(--text);">${v||'N/A'}</span>
      </div>`).join('');
  } catch(e){res.innerHTML='<div style="color:var(--danger);">خطا در اتصال</div>';}
}

async function dnsLookup(){
  const domain = document.getElementById('dnsInput').value.trim();
  if(!domain){cToast('⚠️ دامنه را وارد کنید');return;}
  const res = document.getElementById('dnsResult');
  res.innerHTML = '<div style="font-family:var(--mono);font-size:12px;color:var(--muted);">در حال بارگذاری...</div>';
  res.style.display='block';
  try {
    const r = await fetch(`https://dns.google/resolve?name=${encodeURIComponent(domain)}&type=A`);
    const d = await r.json();
    if(!d.Answer?.length){res.innerHTML='<div style="color:var(--muted);font-family:var(--fa);font-size:13px;">رکوردی پیدا نشد</div>';return;}
    res.innerHTML = d.Answer.map(a=>`
      <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--stroke);">
        <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">Type ${a.type}</span>
        <span style="font-family:var(--mono);font-size:11px;color:var(--cyan);">${a.data}</span>
        <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">TTL: ${a.TTL}s</span>
      </div>`).join('');
  } catch(e){res.innerHTML='<div style="color:var(--danger);">خطا</div>';}
}
document.getElementById('ipInput').addEventListener('keydown',e=>{if(e.key==='Enter')lookupIP();});
document.getElementById('dnsInput').addEventListener('keydown',e=>{if(e.key==='Enter')dnsLookup();});
</script>
<?php cipher_foot(); ?>
