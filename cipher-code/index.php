<?php
session_start();
include '../cipher-core/cipher-theme.php';
cipher_head('Cipher Code','#a78bfa');
cipher_navbar('Cipher Code','💻','../','CODE');
?>
<div class="c-wrap">
<div class="c-panel" style="margin-bottom:22px;">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-tr"></div><div class="hud-c hud-bl"></div><div class="hud-c hud-br"></div>
  <div class="c-label">// CIPHER OS · PRODBY026B</div>
  <div class="c-title">💻 Cipher Code</div>
  <div class="c-sub">ویرایشگر کد و رانر JavaScript/Python توان‌مند</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
<div class="c-panel">
  <div class="hud-c hud-tl"></div>
  <div class="c-label" style="margin-bottom:10px;">// نوع کد</div>
  <div style="display:flex;gap:8px;margin-bottom:16px;">
    <button onclick="setLang('js')" class="c-btn-ghost" style="flex:1;" id="btn-js">JS</button>
    <button onclick="setLang('python')" class="c-btn-ghost" style="flex:1;" id="btn-python">Python</button>
    <button onclick="setLang('html')" class="c-btn-ghost" style="flex:1;" id="btn-html">HTML</button>
  </div>
  <div class="c-label" style="margin-bottom:10px;">// کد شما</div>
  <textarea id="codeInput" class="c-input" style="font-family:var(--mono);height:280px;resize:vertical;">console.log('Cipher Code v1.0');</textarea>
  <button onclick="runCode()" class="c-btn" style="width:100%;margin-top:12px;">⚡ اجرا کنید</button>
</div>

<div class="c-panel">
  <div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:10px;">// نتیجه</div>
  <div id="output" style="background:var(--bg2);border:1px solid var(--stroke);border-radius:12px;padding:14px;min-height:280px;max-height:280px;overflow-y:auto;font-family:var(--mono);font-size:13px;color:var(--success);white-space:pre-wrap;word-break:break-word;"></div>
  <button onclick="clearOutput()" class="c-btn-ghost" style="width:100%;margin-top:12px;">🗑️ پاک کنید</button>
</div>
</div>

<div class="c-panel">
  <div class="hud-c hud-tl"></div><div class="hud-c hud-br"></div>
  <div class="c-label" style="margin-bottom:12px;">// نمونه کدها</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;">
    <button onclick="loadExample('hello')" class="c-btn-ghost">Hello World</button>
    <button onclick="loadExample('loop')" class="c-btn-ghost">حلقه‌های for</button>
    <button onclick="loadExample('array')" class="c-btn-ghost">کار با آرایه</button>
    <button onclick="loadExample('object')" class="c-btn-ghost">Objects</button>
    <button onclick="loadExample('function')" class="c-btn-ghost">توابع</button>
    <button onclick="loadExample('async')" class="c-btn-ghost">Async/Await</button>
  </div>
</div>
</div>

<script>
let currentLang = 'js';
const output = document.getElementById('output');
const codeInput = document.getElementById('codeInput');

const examples = {
  hello: {
    js: "console.log('سلام جهان! 🌍');",
    python: "print('سلام جهان! 🌍')",
    html: "<h1>سلام جهان!</h1>"
  },
  loop: {
    js: "for(let i=1;i<=5;i++){console.log(`شماره ${i}`);}", 
    python: "for i in range(1,6):\n    print(f'شماره {i}')",
    html: "<ul><li>آیتم 1</li><li>آیتم 2</li><li>آیتم 3</li></ul>"
  },
  array: {
    js: "const arr=[1,2,3,4,5];\nconsole.log(arr.map(x=>x*2));",
    python: "arr = [1, 2, 3, 4, 5]\nprint([x*2 for x in arr])",
    html: "<table><tr><th>شماره</th><th>مقدار</th></tr><tr><td>1</td><td>۱۰</td></tr></table>"
  },
  object: {
    js: "const obj={name:'Cipher',type:'OS'};\nconsole.log(JSON.stringify(obj));",
    python: "obj = {'name': 'Cipher', 'type': 'OS'}\nprint(obj)",
    html: "<div class='box'><h2>Cipher</h2><p>نوع: OS</p></div>"
  },
  function: {
    js: "function greet(name){\n  return `سلام ${name}!`;\n}\nconsole.log(greet('Cipher'));",
    python: "def greet(name):\n    return f'سلام {name}!'\nprint(greet('Cipher'))",
    html: "<button onclick=\"alert('دکمه فشرده شد!')\">کلیک کنید</button>"
  },
  async: {
    js: "async function test(){\n  const data = await fetch('/api/test');\n  return await data.json();\n}\ntest();",
    python: "import asyncio\nasync def test():\n    await asyncio.sleep(1)\n    return 'تکمیل شد'\nprint(asyncio.run(test()))",
    html: "<script>fetch('/api/test').then(r=>r.json()).then(d=>console.log(d));</script>"
  }
};

function setLang(lang) {
  currentLang = lang;
  document.querySelectorAll('[id^="btn-"]').forEach(b => b.style.borderColor = 'var(--stroke)');
  document.getElementById('btn-'+lang).style.borderColor = '#a78bfa';
}

function loadExample(key) {
  codeInput.value = examples[key][currentLang];
  output.textContent = '';
}

function clearOutput() {
  output.textContent = '';
}

function runCode() {
  output.textContent = '';
  try {
    if(currentLang === 'js') {
      const logs = [];
      const oldLog = console.log;
      console.log = (...args) => {
        logs.push(args.map(a => typeof a === 'object' ? JSON.stringify(a, null, 2) : String(a)).join(' '));
      };
      eval(codeInput.value);
      console.log = oldLog;
      output.textContent = logs.join('\n') || '✓ اجرا شد (بدون خروجی)';
    } else if(currentLang === 'python') {
      output.textContent = '⚠️ Python نیازمند سرور پایتون است\n(می‌توانید کد را کپی کنید و اجرا کنید)';
    } else if(currentLang === 'html') {
      output.textContent = '✓ HTML برای نمایش فیزیکی نیاز به preview دارد';
    }
  } catch(e) {
    output.textContent = '❌ خطا:\n' + e.message;
  }
}

document.addEventListener('keydown', e => {
  if(e.ctrlKey && e.key === 'Enter') runCode();
});
</script>
<?php cipher_foot();?>
