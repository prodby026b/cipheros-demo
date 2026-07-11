<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

authStart();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . url()); exit; }

$post = getPost($slug);
if (!$post || ($post['status'] !== 'published' && !isLoggedIn())) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:80px 24px;text-align:center"><div style="font-size:48px;color:var(--red);margin-bottom:16px">404</div><h1 style="color:var(--text);margin-bottom:8px">Post Not Found</h1><p style="color:var(--muted);margin-bottom:24px">This post does not exist or has been removed.</p><a href="' . url() . '" class="btn-primary">← Back to Home</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

incrementViews($post['id']);

// Comments
$comments = getComments($post['id']);

// Handle comment submission
$commentMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    verifyCsrf();
    $name    = trim($_POST['author_name'] ?? '');
    $email   = trim($_POST['author_email'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($name && $email && $content && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        execute(
            "INSERT INTO comments (post_id,author_name,author_email,author_ip,content,status) VALUES (?,?,?,?,?,?)",
            [$post['id'], $name, $email, $_SERVER['REMOTE_ADDR'] ?? '', $content, 'pending']
        );
        $commentMsg = 'success';
    } else {
        $commentMsg = 'error';
    }
}

// Prev / Next
$prevPost = queryOne("SELECT title,slug FROM posts WHERE status='published' AND published_at < ? ORDER BY published_at DESC LIMIT 1", [$post['published_at']]);
$nextPost = queryOne("SELECT title,slug FROM posts WHERE status='published' AND published_at > ? ORDER BY published_at ASC  LIMIT 1", [$post['published_at']]);

// Related posts
$related = getPosts(['status'=>'published','category'=>$post['cat_slug'],'limit'=>3]);
$related = array_filter($related, fn($r) => $r['id'] !== $post['id']);

$catIconMap = [
    'linux'    =>['ti-brand-debian','pci-linux','ptag-linux'],
    'network'  =>['ti-network','pci-network','ptag-network'],
    'security' =>['ti-shield','pci-security','ptag-security'],
    'scripting'=>['ti-terminal','pci-scripting','ptag-scripting'],
    'tools'    =>['ti-tools','pci-tools','ptag-tools'],
];
$pi = $catIconMap[$post['cat_slug'] ?? 'linux'] ?? $catIconMap['linux'];

$pageTitle = e($post['meta_title'] ?: $post['title']);
$pageDesc  = e($post['meta_desc']  ?: excerptFromContent($post['content']));

include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="post-hero">
    <div class="post-hero-tags">
      <?php if ($post['cat_name']): ?>
      <a class="ptag <?= $pi[2] ?>" href="<?= url('category.php?slug='.$post['cat_slug']) ?>"><?= e($post['cat_name']) ?></a>
      <?php endif; ?>
      <?php foreach ($post['tags'] as $t): ?>
      <a class="ptag ptag-linux" href="<?= url('search.php?tag='.$t['slug']) ?>"><?= e($t['name']) ?></a>
      <?php endforeach; ?>
      <?php if ($post['is_featured']): ?>
      <span class="ptag" style="background:rgba(255,214,0,.1);color:var(--yellow);border-color:rgba(255,214,0,.2)">★ Featured</span>
      <?php endif; ?>
    </div>
    <h1 class="post-hero-title"><?= e($post['title']) ?></h1>
    <div class="post-hero-meta">
      <span><i class="ti ti-user"></i> <?= e($post['author_name']) ?></span>
      <span><i class="ti ti-calendar"></i> <?= formatDate($post['published_at']) ?></span>
      <span><i class="ti ti-clock"></i> <?= $post['reading_time'] ?> min read</span>
      <span class="views-count"><i class="ti ti-eye"></i> <?= number_format($post['views']) ?> views</span>
      <?php if ($post['comment_count']): ?>
      <span><i class="ti ti-message"></i> <?= $post['comment_count'] ?> comments</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Featured image -->
  <?php if ($post['featured_image']): ?>
  <div style="max-height:400px;overflow:hidden;border-radius:10px;border:1px solid var(--border);margin-bottom:0">
    <img src="<?= url('uploads/'.$post['featured_image']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;object-fit:cover;max-height:400px">
  </div>
  <?php endif; ?>

  <div class="blog-layout">
    <div>
      <!-- POST CONTENT -->
      <div class="post-content">
        <?= markdownToHtml($post['content']) ?>
      </div>

      <!-- SHARE -->
      <div class="post-share">
        <span class="share-label">SHARE:</span>
        <button class="share-btn" onclick="navigator.clipboard.writeText(window.location.href).then(()=>this.textContent='COPIED!')"><i class="ti ti-link"></i>Copy Link</button>
        <a class="share-btn" href="https://t.me/share/url?url=<?= urlencode(url('post.php?slug='.$post['slug'])) ?>&text=<?= urlencode($post['title']) ?>" target="_blank"><i class="ti ti-brand-telegram"></i>Telegram</a>
        <a class="share-btn" href="https://twitter.com/intent/tweet?url=<?= urlencode(url('post.php?slug='.$post['slug'])) ?>&text=<?= urlencode($post['title']) ?>" target="_blank"><i class="ti ti-brand-twitter"></i>Twitter</a>
      </div>

      <!-- TAGS -->
      <div class="post-tags-row">
        <span style="font-size:9px;color:var(--muted);letter-spacing:1px">TAGS:</span>
        <?php foreach ($post['tags'] as $t): ?>
        <a class="ptag ptag-linux" href="<?= url('search.php?tag='.$t['slug']) ?>"><?= e($t['name']) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- PREV / NEXT -->
      <div class="post-nav">
        <?php if ($prevPost): ?>
        <a class="pnav-card" href="<?= url('post.php?slug='.$prevPost['slug']) ?>">
          <div class="pnav-dir"><i class="ti ti-arrow-left"></i>PREVIOUS</div>
          <div class="pnav-title"><?= e($prevPost['title']) ?></div>
        </a>
        <?php else: ?><div></div><?php endif; ?>
        <?php if ($nextPost): ?>
        <a class="pnav-card next" href="<?= url('post.php?slug='.$nextPost['slug']) ?>">
          <div class="pnav-dir">NEXT <i class="ti ti-arrow-right"></i></div>
          <div class="pnav-title"><?= e($nextPost['title']) ?></div>
        </a>
        <?php endif; ?>
      </div>

      <!-- RELATED POSTS -->
      <?php if ($related): ?>
      <div class="section">
        <div class="section-head"><div class="section-title">Related Posts</div></div>
        <div class="post-grid">
          <?php foreach (array_slice($related,0,3) as $r):
            $ri = $catIconMap[$r['cat_slug']??'linux'] ?? $catIconMap['linux'];
          ?>
          <a class="post-card" href="<?= url('post.php?slug='.$r['slug']) ?>">
            <div class="post-card-img <?= $ri[1] ?>">
              <?php if ($r['featured_image']): ?>
              <img src="<?= url('uploads/'.$r['featured_image']) ?>" alt="" loading="lazy">
              <?php else: ?>
              <i class="ti <?= $ri[0] ?>"></i>
              <?php endif; ?>
            </div>
            <div class="post-card-body">
              <div class="post-tags"><span class="ptag <?= $ri[2] ?>"><?= e($r['cat_name']??'') ?></span></div>
              <div class="post-card-title"><?= e($r['title']) ?></div>
              <div class="post-card-meta">
                <span><i class="ti ti-clock"></i> <?= $r['reading_time'] ?> min</span>
                <span class="read-more"><i class="ti ti-arrow-right"></i>Read</span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- COMMENTS -->
      <?php if ($post['allow_comments']): ?>
      <div style="margin-bottom:64px">
        <div class="comments-title">
          Comments (<?= count($comments) ?>)
        </div>

        <?php if ($commentMsg === 'success'): ?>
        <div class="alert alert-success">✓ Comment submitted! It will appear after review.</div>
        <?php elseif ($commentMsg === 'error'): ?>
        <div class="alert alert-error">✗ Please fill in all fields correctly.</div>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
        <div class="comment-item">
          <div class="c-ava"><?= strtoupper(substr($c['author_name'],0,2)) ?></div>
          <div class="c-box">
            <div class="c-head">
              <span class="c-author"><?= e($c['author_name']) ?></span>
              <span class="c-date"><?= timeAgo($c['created_at']) ?></span>
            </div>
            <div class="c-text"><?= nl2br(e($c['content'])) ?></div>
            <span class="c-reply-btn"><i class="ti ti-corner-down-right"></i>Reply</span>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($comments) && $commentMsg !== 'success'): ?>
        <div style="color:var(--muted);font-size:11px;padding:20px 0;text-align:center">No comments yet. Be the first!</div>
        <?php endif; ?>

        <div class="comment-form">
          <div class="cf-title"><i class="ti ti-message-circle"></i>Leave a Comment</div>
          <form method="POST">
            <input type="hidden" name="comment_submit" value="1">
            <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
            <div class="cf-row">
              <input class="cf-input" name="author_name" placeholder="Name *" required>
              <input class="cf-input" type="email" name="author_email" placeholder="Email (not published)" required>
            </div>
            <textarea class="cf-input" name="content" style="min-height:100px;margin-bottom:12px" placeholder="Your comment..." required></textarea>
            <button class="btn-primary" type="submit" style="font-size:10px"><i class="ti ti-send"></i>POST COMMENT</button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <?php
      $authorName = $post['author_name'];
      $authorBio  = $post['author_bio'] ?: getSetting('author_bio','Linux & Network educator.');
      $postCount  = (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'",[])['c'];
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
            <div class="ava-stat"><div class="val"><?= $viewCount>1000?round($viewCount/1000,1).'K':$viewCount ?></div><div class="lbl">VIEWS</div></div>
            <div class="ava-stat"><div class="val"><?= $subCount ?></div><div class="lbl">SUBS</div></div>
          </div>
        </div>
      </div>

      <!-- TOC -->
      <?php
      preg_match_all('/^## (.+)$/m', $post['content'], $headings);
      if (count($headings[1]) > 2):
      ?>
      <div class="widget">
        <div class="widget-head"><i class="ti ti-list"></i>TABLE OF CONTENTS</div>
        <div class="widget-body" style="padding:8px 16px">
          <?php foreach ($headings[1] as $i => $h): ?>
          <div style="padding:6px 0;border-bottom:1px solid var(--border);font-size:10px;color:var(--muted);cursor:pointer;transition:color .15s" onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='var(--muted)'">
            <span style="color:var(--green);font-size:9px;margin-right:6px"><?= $i+1 ?>.</span><?= e($h) ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="widget">
        <div class="widget-head"><i class="ti ti-tag"></i>POST TAGS</div>
        <div class="widget-body">
          <div class="tag-cloud">
            <?php foreach ($post['tags'] as $t): ?>
            <a class="tc-tag ptag-linux" href="<?= url('search.php?tag='.$t['slug']) ?>"><?= e($t['name']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="widget">
        <div class="widget-head"><i class="ti ti-mail"></i>NEWSLETTER</div>
        <div class="newsletter-widget">
          <p>Enjoyed this post? Get more in your inbox.</p>
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
function subscribeWidget(form){
  fetch('<?= url('api/subscribe.php') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'email='+encodeURIComponent(form.email.value)+'&_csrf=<?= csrfToken() ?>'})
  .then(r=>r.json()).then(d=>{form.innerHTML=d.ok?'<div style="color:var(--green);font-size:11px">✓ Subscribed!</div>':'<div style="color:var(--red);font-size:11px">✗ '+(d.error||'Error')+'</div>';});
  return false;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
