<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

authStart();

$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = (int)getSetting('posts_per_page', '10');
$offset   = ($page - 1) * $perPage;

$featured = getPosts(['status'=>'published','featured'=>true,'limit'=>1]);
$posts    = getPosts(['status'=>'published','limit'=>$perPage,'offset'=>$offset]);
$total    = (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'",[])['c'];
$pages    = ceil($total / $perPage);

$topPosts = getPosts(['status'=>'published','limit'=>5]);
$tags     = query("SELECT * FROM tags WHERE post_count > 0 ORDER BY post_count DESC LIMIT 15");

$catIconMap = [
    'linux'=>['ti-brand-debian','pci-linux','ptag-linux'],
    'network'=>['ti-network','pci-network','ptag-network'],
    'security'=>['ti-shield','pci-security','ptag-security'],
    'scripting'=>['ti-terminal','pci-scripting','ptag-scripting'],
    'tools'=>['ti-tools','pci-tools','ptag-tools'],
];

$pageTitle = getSetting('blog_name') . ' — ' . getSetting('blog_tagline');

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<div class="container">
  <div class="hero">
    <div class="hero-tag"><span class="dot-live"></span>ACTIVE BLOG · <?= $total ?> POSTS</div>
    <h1 class="hero-title">
      Deep-dive into<br>
      <span class="hl">Linux</span> &amp; <span class="hl2">Networks</span>
    </h1>
    <p class="hero-desc">Practical tutorials, command-line guides, and networking deep-dives. Written by <?= e(getSetting('author_name','prodby026b')) ?> — one shell at a time.</p>
    <div class="hero-actions">
      <button class="btn-primary" onclick="document.getElementById('latest').scrollIntoView({behavior:'smooth'})">
        <i class="ti ti-arrow-down"></i>EXPLORE POSTS
      </button>
      <a class="btn-outline" href="<?= url('about.php') ?>">
        <i class="ti ti-user"></i>ABOUT
      </a>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><div class="val"><?= $total ?></div><div class="lbl">POSTS</div></div>
      <div class="hero-stat"><div class="val"><?= (int)queryOne("SELECT SUM(views) AS v FROM posts",[])['v'] ?></div><div class="lbl">TOTAL VIEWS</div></div>
      <div class="hero-stat"><div class="val"><?= count(query("SELECT id FROM categories WHERE post_count>0")) ?></div><div class="lbl">CATEGORIES</div></div>
      <div class="hero-stat"><div class="val"><?= (int)queryOne("SELECT COUNT(*) AS c FROM subscribers WHERE status='active'",[])['c'] ?></div><div class="lbl">SUBSCRIBERS</div></div>
    </div>
  </div>

  <!-- FEATURED -->
  <?php if ($featured): $f = $featured[0]; $fi = $catIconMap[$f['cat_slug'] ?? 'linux'] ?? $catIconMap['linux']; ?>
  <div class="section">
    <div class="section-head"><div class="section-title">Featured Post</div></div>
    <a class="featured-card" href="<?= url('post.php?slug=' . $f['slug']) ?>">
      <div class="featured-body">
        <div class="feat-badge"><i class="ti ti-star"></i>FEATURED</div>
        <?php if ($f['cat_name']): ?>
        <div style="margin-bottom:12px"><span class="ptag <?= $fi[2] ?>"><?= e($f['cat_name']) ?></span></div>
        <?php endif; ?>
        <div class="featured-title"><?= e($f['title']) ?></div>
        <p class="featured-excerpt"><?= e($f['excerpt'] ?: excerptFromContent($f['content'])) ?></p>
        <div class="post-meta">
          <span><i class="ti ti-user"></i> <?= e($f['author_name']) ?></span>
          <span><i class="ti ti-calendar"></i> <?= formatDate($f['published_at']) ?></span>
          <span><i class="ti ti-clock"></i> <?= $f['reading_time'] ?> min read</span>
          <span style="color:var(--green)"><i class="ti ti-eye"></i> <?= number_format($f['views']) ?> views</span>
        </div>
      </div>
      <div class="featured-img"><i class="ti <?= $fi[0] ?>" style="font-size:72px;color:rgba(0,255,157,.15)"></i></div>
    </a>
  </div>
  <?php endif; ?>

  <!-- LATEST POSTS + SIDEBAR -->
  <div class="blog-layout" id="latest">
    <div>
      <div class="section-head">
        <div class="section-title">Latest Posts</div>
        <a class="section-link" href="<?= url('search.php') ?>">All posts <i class="ti ti-arrow-right"></i></a>
      </div>

      <?php if (empty($posts)): ?>
      <div style="text-align:center;padding:60px 20px;color:var(--muted);font-size:13px">
        <i class="ti ti-file-off" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3"></i>
        No posts yet. <a href="<?= url('admin/new-post.php') ?>" style="color:var(--blue)">Create the first one →</a>
      </div>
      <?php else: ?>
      <div class="post-grid">
        <?php foreach ($posts as $p):
          $pi = $catIconMap[$p['cat_slug'] ?? 'linux'] ?? $catIconMap['linux'];
        ?>
        <a class="post-card" href="<?= url('post.php?slug=' . $p['slug']) ?>">
          <div class="post-card-img <?= $pi[1] ?>">
            <?php if ($p['featured_image']): ?>
            <img src="<?= url('uploads/' . $p['featured_image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            <?php else: ?>
            <i class="ti <?= $pi[0] ?>"></i>
            <?php endif; ?>
          </div>
          <div class="post-card-body">
            <div class="post-tags">
              <?php if ($p['cat_name']): ?><span class="ptag <?= $pi[2] ?>"><?= e($p['cat_name']) ?></span><?php endif; ?>
              <?php foreach (array_slice($p['tags'], 0, 2) as $t): ?>
              <span class="ptag ptag-linux"><?= e($t['name']) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="post-card-title"><?= e($p['title']) ?></div>
            <div class="post-card-excerpt"><?= e(excerptFromContent($p['content'], 110)) ?></div>
            <div class="post-card-meta">
              <span><i class="ti ti-calendar"></i> <?= formatDate($p['published_at'], 'M j') ?></span>
              <span><i class="ti ti-clock"></i> <?= $p['reading_time'] ?> min</span>
              <span class="read-more"><i class="ti ti-arrow-right"></i>Read</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- PAGINATION -->
      <?php if ($pages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?><a class="pag-btn" href="?page=<?= $page-1 ?>"><i class="ti ti-arrow-left"></i>Prev</a><?php endif; ?>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="pag-btn <?= $i == $page ? 'active' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $pages): ?><a class="pag-btn" href="?page=<?= $page+1 ?>">Next<i class="ti ti-arrow-right"></i></a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <?php
      $authorName = getSetting('author_name','prodby026b');
      $authorBio  = getSetting('author_bio','Linux & Network educator. Documenting the deep stack.');
      $postCount  = $total;
      $viewCount  = (int)queryOne("SELECT SUM(views) AS v FROM posts",[])['v'];
      $subCount   = (int)queryOne("SELECT COUNT(*) AS c FROM subscribers WHERE status='active'",[])['c'];
      ?>
      <div class="widget">
        <div class="author-card">
          <div class="ava-big"><?= strtoupper(substr($authorName,0,2)) ?></div>
          <div class="ava-name"><?= e($authorName) ?></div>
          <div class="ava-handle">CIPHER_LOG · ROOT</div>
          <p class="ava-bio"><?= e($authorBio) ?></p>
          <div class="ava-stats">
            <div class="ava-stat"><div class="val"><?= $postCount ?></div><div class="lbl">POSTS</div></div>
            <div class="ava-stat"><div class="val"><?= $viewCount > 1000 ? round($viewCount/1000,1).'K' : $viewCount ?></div><div class="lbl">VIEWS</div></div>
            <div class="ava-stat"><div class="val"><?= $subCount ?></div><div class="lbl">SUBS</div></div>
          </div>
        </div>
      </div>

      <?php if ($topPosts): ?>
      <div class="widget">
        <div class="widget-head"><i class="ti ti-trophy"></i>TOP POSTS</div>
        <div class="widget-body" style="padding:8px 16px">
          <?php foreach ($topPosts as $i => $tp): ?>
          <a class="wpost" href="<?= url('post.php?slug=' . $tp['slug']) ?>" style="text-decoration:none">
            <div class="wpost-num"><?= $i+1 ?></div>
            <div><div class="wpost-title"><?= e($tp['title']) ?></div><div class="wpost-views"><?= number_format($tp['views']) ?> views</div></div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($tags): ?>
      <div class="widget">
        <div class="widget-head"><i class="ti ti-tag"></i>TAGS</div>
        <div class="widget-body">
          <div class="tag-cloud">
            <?php
            $tagColorMap = [
              'linux'=>'ptag-linux','network'=>'ptag-network','security'=>'ptag-security',
              'scripting'=>'ptag-scripting','bash'=>'ptag-scripting','tools'=>'ptag-tools'
            ];
            foreach ($tags as $t):
              $cls = $tagColorMap[$t['slug']] ?? 'ptag-linux';
            ?>
            <a class="tc-tag <?= $cls ?>" href="<?= url('search.php?tag=' . $t['slug']) ?>"><?= e($t['name']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="widget">
        <div class="widget-head"><i class="ti ti-mail"></i>NEWSLETTER</div>
        <div class="newsletter-widget">
          <p>Get new posts in your inbox. No spam, unsubscribe anytime.</p>
          <form onsubmit="return subscribeWidget(this)">
            <input class="nl-input" type="email" name="email" placeholder="your@email.sh" required>
            <button class="nl-btn" type="submit">SUBSCRIBE</button>
          </form>
        </div>
      </div>
    </aside>
  </div>
</div>

<script>
function subscribeWidget(form) {
  const email = form.email.value;
  fetch('<?= url('api/subscribe.php') ?>', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'email='+encodeURIComponent(email)+'&_csrf=<?= csrfToken() ?>'
  })
  .then(r=>r.json())
  .then(d=>{
    form.innerHTML = d.ok
      ? '<div style="color:var(--green);font-size:11px">✓ Subscribed! Welcome aboard.</div>'
      : '<div style="color:var(--red);font-size:11px">✗ '+(d.error||'Error')+'</div>';
  });
  return false;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
