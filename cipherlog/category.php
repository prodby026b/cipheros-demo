<?php
// category.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
authStart();

$slug = trim($_GET['slug'] ?? '');
$cat  = queryOne("SELECT * FROM categories WHERE slug=?", [$slug]);
if (!$cat) { header('Location: ' . url()); exit; }

$page    = max(1,(int)($_GET['page']??1));
$perPage = (int)getSetting('posts_per_page','10');
$offset  = ($page-1)*$perPage;
$posts   = getPosts(['status'=>'published','category'=>$slug,'limit'=>$perPage,'offset'=>$offset]);
$total   = (int)queryOne("SELECT COUNT(*) AS c FROM posts p JOIN categories c ON p.category_id=c.id WHERE p.status='published' AND c.slug=?",[  $slug])['c'];
$pages   = ceil($total/$perPage);

$catIconMap=['linux'=>['ti-brand-debian','pci-linux','ptag-linux'],'network'=>['ti-network','pci-network','ptag-network'],'security'=>['ti-shield','pci-security','ptag-security'],'scripting'=>['ti-terminal','pci-scripting','ptag-scripting'],'tools'=>['ti-tools','pci-tools','ptag-tools']];
$pi=$catIconMap[$cat['slug']]??$catIconMap['linux'];
$colorClass='c-'.$cat['color'];

$pageTitle = e($cat['name']).' — '.getSetting('blog_name');
$pageDesc  = e($cat['description']);
include __DIR__.'/includes/header.php';
?>
<div class="container">
  <div style="padding:48px 0 32px">
    <div class="hero-tag" style="margin-bottom:16px"><i class="ti <?= $pi[0] ?>"></i> CATEGORY</div>
    <h1 style="font-size:32px;font-weight:700;color:var(--text);margin-bottom:8px"><?= e($cat['name']) ?></h1>
    <p style="color:var(--muted);font-size:12px;font-family:'Inter',sans-serif;max-width:500px;line-height:1.8;margin-bottom:8px"><?= e($cat['description']) ?></p>
    <span style="font-size:10px;color:var(--muted)"><?= $total ?> posts</span>
  </div>
  <div class="blog-layout">
    <div>
      <div class="section-head"><div class="section-title"><?= e($cat['name']) ?> Posts</div></div>
      <?php if (empty($posts)): ?>
      <div style="text-align:center;padding:60px 0;color:var(--muted)">No posts in this category yet.</div>
      <?php else: ?>
      <div class="post-grid">
        <?php foreach ($posts as $p):
          $pi2=$catIconMap[$p['cat_slug']??'linux']??$catIconMap['linux'];
        ?>
        <a class="post-card" href="<?= url('post.php?slug='.$p['slug']) ?>">
          <div class="post-card-img <?= $pi2[1] ?>">
            <?php if ($p['featured_image']): ?><img src="<?= url('uploads/'.$p['featured_image']) ?>" alt="" loading="lazy"><?php else: ?><i class="ti <?= $pi2[0] ?>"></i><?php endif; ?>
          </div>
          <div class="post-card-body">
            <div class="post-tags"><span class="ptag <?= $pi2[2] ?>"><?= e($p['cat_name']??'') ?></span></div>
            <div class="post-card-title"><?= e($p['title']) ?></div>
            <div class="post-card-excerpt"><?= e(excerptFromContent($p['content'],100)) ?></div>
            <div class="post-card-meta">
              <span><i class="ti ti-calendar"></i> <?= formatDate($p['published_at'],'M j') ?></span>
              <span><i class="ti ti-clock"></i> <?= $p['reading_time'] ?> min</span>
              <span class="read-more"><i class="ti ti-arrow-right"></i>Read</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php if ($pages>1): ?>
      <div class="pagination">
        <?php if ($page>1): ?><a class="pag-btn" href="?slug=<?= $slug ?>&page=<?= $page-1 ?>"><i class="ti ti-arrow-left"></i>Prev</a><?php endif; ?>
        <?php for($i=1;$i<=$pages;$i++): ?><a class="pag-btn <?= $i==$page?'active':'' ?>" href="?slug=<?= $slug ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?>
        <?php if ($page<$pages): ?><a class="pag-btn" href="?slug=<?= $slug ?>&page=<?= $page+1 ?>">Next<i class="ti ti-arrow-right"></i></a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
    <aside class="sidebar">
      <div class="widget">
        <div class="widget-head"><i class="ti ti-folder"></i>ALL CATEGORIES</div>
        <div class="widget-body" style="padding:8px 16px">
          <?php foreach (query("SELECT * FROM categories ORDER BY post_count DESC") as $c): ?>
          <a href="<?= url('category.php?slug='.$c['slug']) ?>" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);text-decoration:none;font-size:11px;color:<?= $c['slug']===$slug?'var(--green)':'var(--muted)' ?>;transition:color .15s" onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='<?= $c['slug']===$slug?'var(--green)':'var(--muted)' ?>'">
            <span><?= e($c['name']) ?></span><span style="font-size:10px;color:var(--muted)"><?= $c['post_count'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="widget">
        <div class="widget-head"><i class="ti ti-mail"></i>NEWSLETTER</div>
        <div class="newsletter-widget">
          <p>Get new posts in your inbox.</p>
          <form onsubmit="return subWidget(this)">
            <input class="nl-input" type="email" name="email" placeholder="your@email.sh" required>
            <button class="nl-btn" type="submit">SUBSCRIBE</button>
          </form>
        </div>
      </div>
    </aside>
  </div>
</div>
<script>
function subWidget(f){fetch('<?= url('api/subscribe.php') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'email='+encodeURIComponent(f.email.value)+'&_csrf=<?= csrfToken() ?>'}).then(r=>r.json()).then(d=>{f.innerHTML=d.ok?'<div style="color:var(--green);font-size:11px">✓ Subscribed!</div>':'<div style="color:var(--red);font-size:11px">✗ '+(d.error||'Error')+'</div>';});return false;}
</script>
<?php include __DIR__.'/includes/footer.php'; ?>
