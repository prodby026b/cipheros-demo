<?php
// includes/footer.php
$footerCats = query("SELECT * FROM categories ORDER BY post_count DESC LIMIT 5");
?>
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-terminal">
      <div class="ft-head"><i class="ti ti-terminal-2"></i> <?= e($blogName) ?> — status</div>
      <div class="ft-body">
        <span class="fp">root@<?= e(strtolower($blogName)) ?>:~# </span><span class="fc">uptime</span><br>
        <span class="fo"> Blog online · Posts: <?= getSetting('post_count', '0') ?> · Subs: <?= getSetting('sub_count', '0') ?></span><br>
        <span class="fg">status: online</span>
      </div>
    </div>
    <div class="footer-grid">
      <div>
        <div class="f-brand"><?= e(strtoupper($blogName)) ?></div>
        <div class="f-desc">Practical Linux and networking tutorials. No fluff, no paywalls — just real technical content by <?= e(getSetting('author_name', 'prodby026b')) ?>.</div>
        <div class="f-social">
          <a class="fsoc" href="<?= url('api/rss.php') ?>" title="RSS"><i class="ti ti-rss"></i></a>
          <a class="fsoc" href="#" title="GitHub"><i class="ti ti-brand-github"></i></a>
          <a class="fsoc" href="#" title="Telegram"><i class="ti ti-brand-telegram"></i></a>
          <a class="fsoc" href="mailto:<?= e(getSetting('admin_email')) ?>"><i class="ti ti-mail"></i></a>
        </div>
      </div>
      <div>
        <div class="f-col-title">CATEGORIES</div>
        <?php foreach ($footerCats as $c): ?>
        <a class="f-link" href="<?= url('category.php?slug=' . $c['slug']) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div>
        <div class="f-col-title">QUICK LINKS</div>
        <a class="f-link" href="<?= url() ?>">Home</a>
        <a class="f-link" href="<?= url('about.php') ?>">About</a>
        <a class="f-link" href="<?= url('search.php') ?>">Search</a>
        <a class="f-link" href="<?= url('api/rss.php') ?>">RSS Feed</a>
        <a class="f-link" href="<?= url('admin/') ?>">Admin</a>
      </div>
      <div>
        <div class="f-col-title">NEWSLETTER</div>
        <p class="f-desc" style="margin-bottom:10px">New posts in your inbox. No spam.</p>
        <form action="<?= url('api/subscribe.php') ?>" method="POST" onsubmit="return subscribeFooter(this)">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <input class="f-email" type="email" name="email" placeholder="your@email.sh" required>
          <button class="f-sub-btn" type="submit">SUBSCRIBE</button>
        </form>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e($blogName) ?> · All rights reserved</span>
      <span style="color:var(--green);display:flex;align-items:center;gap:6px">
        <span class="dot-live"></span>Online
      </span>
    </div>
  </div>
</footer>

<script>
const BLOG_URL = '<?= BLOG_URL ?>';

// Search
function openSearch() {
  document.getElementById('search-overlay').classList.add('open');
  setTimeout(() => document.getElementById('search-input').focus(), 100);
}
function closeSearch() {
  document.getElementById('search-overlay').classList.remove('open');
}
document.addEventListener('keydown', e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
  if (e.key === 'Escape') closeSearch();
});
document.getElementById('search-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeSearch();
});

function escapeHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
  clearTimeout(searchTimer);
  const q = this.value.trim();
  if (!q) { document.getElementById('search-results-overlay').innerHTML = '<div class="sm-empty">Start typing...</div>'; return; }
  searchTimer = setTimeout(() => {
    fetch(`${BLOG_URL}/api/search.php?q=${encodeURIComponent(q)}&limit=6`)
      .then(r => r.json())
      .then(data => {
        const c = document.getElementById('search-results-overlay');
        if (!data.length) { c.innerHTML = '<div class="sm-empty">No results for "' + escapeHtml(q) + '"</div>'; return; }
        c.innerHTML = data.map(p => `
          <a class="sm-result" href="${BLOG_URL}/post.php?slug=${escapeHtml(p.slug)}">
            <div class="sm-result-title">${escapeHtml(p.title)}</div>
            <div class="sm-result-meta">${escapeHtml(p.cat_name || 'Uncategorized')} · ${p.reading_time} min read</div>
          </a>`).join('');
      });
  }, 250);
});

// Reading progress
window.addEventListener('scroll', () => {
  const d = document.documentElement;
  const fill = document.getElementById('rp-fill');
  if (fill) fill.style.width = (d.scrollTop / (d.scrollHeight - d.clientHeight) * 100) + '%';
});

// Copy code
function copyCode(btn) {
  const code = btn.closest('.code-block').querySelector('pre code');
  navigator.clipboard.writeText(code.textContent).then(() => {
    btn.textContent = 'COPIED!';
    setTimeout(() => btn.textContent = 'COPY', 2000);
  });
}

// Newsletter footer
function subscribeFooter(form) {
  const email = form.email.value;
  fetch(`${BLOG_URL}/api/subscribe.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `email=${encodeURIComponent(email)}&_csrf=${form._csrf.value}`
  })
  .then(r => r.json())
  .then(d => {
    form.innerHTML = d.ok
      ? '<div style="color:var(--green);font-size:11px">✓ Subscribed! Welcome aboard.</div>'
      : '<div style="color:var(--red);font-size:11px">✗ ' + (d.error || 'Error') + '</div>';
  });
  return false;
}
</script>
</body>
</html>
