<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Weather', '#38bdf8');
cipher_navbar('Cipher Weather', '🌤️', '../', 'WEATHER');
?>
<div class="c-wrap" style="max-width:900px;">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">🌤️ Cipher Weather</div>
  <div class="c-sub">آب و هوای شهرهای جهان — Real-time weather data</div>
</div>
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div style="display:flex;gap:12px;">
    <input id="cityIn" class="c-input" placeholder="نام شهر: Tehran, London, New York..." style="flex:1;">
    <button onclick="getWeather()" class="c-btn">🔍 جستجو</button>
    <button onclick="getWeather('Tehran')" class="c-btn-ghost">تهران</button>
    <button onclick="getWeather('London')" class="c-btn-ghost">لندن</button>
  </div>
</div>
<div id="weatherResult" style="display:none;">
<div class="c-panel" style="margin-bottom:18px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div style="display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center;">
    <div>
      <div id="wCity" style="font-size:28px;font-weight:700;margin-bottom:4px;"></div>
      <div id="wDesc" style="font-family:var(--fa);color:var(--muted2);font-size:14px;margin-bottom:16px;"></div>
      <div style="display:flex;gap:20px;flex-wrap:wrap;">
        <div><div class="c-label">دما</div><div id="wTemp" style="font-family:var(--mono);font-size:24px;font-weight:700;color:var(--cyan);"></div></div>
        <div><div class="c-label">احساس</div><div id="wFeel" style="font-family:var(--mono);font-size:24px;font-weight:700;color:var(--muted2);"></div></div>
        <div><div class="c-label">رطوبت</div><div id="wHum" style="font-family:var(--mono);font-size:24px;font-weight:700;color:#38bdf8;"></div></div>
        <div><div class="c-label">باد</div><div id="wWind" style="font-family:var(--mono);font-size:24px;font-weight:700;color:#34d399;"></div></div>
      </div>
    </div>
    <div id="wIcon" style="font-size:80px;text-align:center;"></div>
  </div>
</div>
<div id="forecastGrid" class="c-grid-3"></div>
</div>
<div id="wError" style="display:none;" class="c-panel">
  <div class="c-empty"><div class="c-empty-icon">⚠️</div><p id="wErrMsg">شهر پیدا نشد.</p></div>
</div>
</div>
<script>
const API_KEY = 'bd5e378503939ddaee76f12ad7a97608'; // رایگان - open-meteo نیاز به key ندارد
const icons = {'01d':'☀️','01n':'🌙','02d':'⛅','02n':'🌙','03d':'☁️','03n':'☁️','04d':'☁️','04n':'☁️','09d':'🌧️','09n':'🌧️','10d':'🌦️','10n':'🌧️','11d':'⛈️','11n':'⛈️','13d':'❄️','13n':'❄️','50d':'🌫️','50n':'🌫️'};

async function getWeather(city) {
  city = city || document.getElementById('cityIn').value.trim();
  if(!city){cToast('⚠️ نام شهر را وارد کنید');return;}
  document.getElementById('cityIn').value = city;
  try {
    // ابتدا geocoding
    const geoRes = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(city)}&count=1&language=en&format=json`);
    const geoData = await geoRes.json();
    if(!geoData.results?.length){ showErr('شهر پیدا نشد.'); return; }
    const loc = geoData.results[0];
    const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${loc.latitude}&longitude=${loc.longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min&timezone=auto&forecast_days=7`);
    const d = await res.json();
    const c = d.current;
    const wIcon = weatherIcon(c.weather_code);
    document.getElementById('wCity').textContent = loc.name + (loc.country ? ', '+loc.country : '');
    document.getElementById('wDesc').textContent = weatherDesc(c.weather_code);
    document.getElementById('wTemp').textContent = Math.round(c.temperature_2m)+'°C';
    document.getElementById('wFeel').textContent = Math.round(c.apparent_temperature)+'°C';
    document.getElementById('wHum').textContent = c.relative_humidity_2m+'%';
    document.getElementById('wWind').textContent = Math.round(c.wind_speed_10m)+' km/h';
    document.getElementById('wIcon').textContent = wIcon;
    // forecast
    const fg = document.getElementById('forecastGrid');
    const days = ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'];
    fg.innerHTML = d.daily.time.map((t,i)=>{
      const date = new Date(t); const day = days[date.getDay()];
      return `<div class="c-card fade-in-item" style="text-align:center;padding:16px 12px;">
        <div style="font-family:var(--mono);font-size:10px;color:var(--muted);margin-bottom:8px;">${day}</div>
        <div style="font-size:28px;margin-bottom:8px;">${weatherIcon(d.daily.weather_code[i])}</div>
        <div style="font-family:var(--mono);font-size:14px;font-weight:700;color:var(--cyan);">${Math.round(d.daily.temperature_2m_max[i])}°</div>
        <div style="font-family:var(--mono);font-size:12px;color:var(--muted2);">${Math.round(d.daily.temperature_2m_min[i])}°</div>
      </div>`;
    }).join('');
    document.getElementById('weatherResult').style.display='block';
    document.getElementById('wError').style.display='none';
  } catch(e) { showErr('خطا در دریافت اطلاعات آب و هوا.'); }
}
function showErr(msg){document.getElementById('wErrMsg').textContent=msg;document.getElementById('wError').style.display='block';document.getElementById('weatherResult').style.display='none';}
function weatherIcon(code){
  if(code===0)return'☀️';if(code<=2)return'⛅';if(code<=3)return'☁️';
  if(code<=48)return'🌫️';if(code<=57)return'🌦️';if(code<=67)return'🌧️';
  if(code<=77)return'❄️';if(code<=82)return'🌧️';if(code<=99)return'⛈️';return'🌡️';
}
function weatherDesc(code){
  if(code===0)return'آسمان صاف';if(code<=2)return'کمی ابری';if(code<=3)return'ابری';
  if(code<=48)return'مه‌آلود';if(code<=57)return'نم‌نم باران';if(code<=67)return'بارانی';
  if(code<=77)return'برفی';if(code<=82)return'رگبار';if(code<=99)return'طوفانی';return'نامشخص';
}
document.getElementById('cityIn').addEventListener('keydown',e=>{if(e.key==='Enter')getWeather();});
getWeather('Tehran');
</script>
<?php cipher_foot(); ?>
