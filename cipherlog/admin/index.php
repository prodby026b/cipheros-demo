<?php
// admin/index.php — Dashboard
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

$stats = [
    'posts'    => (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'",         [])['c'],
    'drafts'   => (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='draft'",             [])['c'],
    'views'    => (int)queryOne("SELECT SUM(views) AS c FROM posts",                                [])['c'],
    'comments' => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='approved'",       [])['c'],
    'pending'  => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='pending'",        [])['c'],
    'subs'     => (int)queryOne("SELECT COUNT(*) AS c FROM subscribers WHERE status='active'",      [])['c'],
];

$topPosts   = query("SELECT title,slug,views,published_at FROM posts WHERE status='published' ORDER BY views DESC LIMIT 5");
$recentActs = query("SELECT event,ip,details,created_at FROM security_log ORDER BY created_at DESC LIMIT 8");
$recentPosts= query("SELECT title,slug,status,created_at FROM posts ORDER BY created_at DESC LIMIT 5");

$activePage = 'dashboard';
include __DIR__ . '/admin_header.php';
?>
<div class="stats-grid">
  <div class="stat-card sc-g"><i class="ti ti-file-text stat-icon"></i><div class="stat-label">PUBLISHED POSTS</div><div class="stat-value"><?= $stats['posts'] ?></div><div class="stat-sub"><span class="stat-up"><?= $stats['drafts'] ?></span> drafts</div></div>
  <div class="stat-card sc-b"><i class="ti ti-eye stat-icon"></i><div class="stat-label">TOTAL VIEWS</div><div class="stat-value"><?= $stats['views']>1000?round($stats['views']/1000,1).'K':$stats['views'] ?></div><div class="stat-sub">all time</div></div>
  <div class="stat-card sc-y"><i class="ti ti-message stat-icon"></i><div class="stat-label">COMMENTS</div><div class="stat-value"><?= $stats['comments'] ?></div><div class="stat-sub"><span style="color:var(--yellow)"><?= $stats['pending'] ?></span> pending</div></div>
  <div class="stat-card sc-r"><i class="ti ti-mail stat-icon"></i><div class="stat-label">SUBSCRIBERS</div><div class="stat-value"><?= $stats['subs'] ?></div><div class="stat-sub">active</div></div>
</div>

<div class="grid3">
  <div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="ti ti-trophy"></i>Top Posts by Views</div></div>
    <div class="panel-body" style="padding:0 18px">
      <?php foreach ($topPosts as $i => $p): ?>
      <div class="metric-row">
        <span class="metric-name" style="display:flex;align-items:center;gap:8px"><span style="font-size:16px;font-weight:700;color:rgba(0,255,157,.2);width:20px"><?= $i+1 ?></span><a href="<?= url('post.php?slug='.$p['slug']) ?>" target="_blank" style="color:var(--text);transition:color .15s" onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='var(--text)'"><?= e(mb_strimwidth($p['title'],0,40,'...')) ?></a></span>
        <span class="metric-val"><?= number_format($p['views']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="gap-col">
    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-activity"></i>Recent Activity</div></div>
      <div class="panel-body" style="padding:0 18px">
        <?php foreach ($recentActs as $a):
          $dot = str_contains($a['event'],'ok') ? 'ad-g' : (str_contains($a['event'],'fail') ? 'ad-r' : 'ad-b');
        ?>
        <div class="act-item">
          <div class="act-dot <?= $dot ?>"></div>
          <div><div class="act-text"><?= e($a['event']) ?><?php if($a['ip']): ?> from <span style="color:var(--muted)"><?= e($a['ip']) ?></span><?php endif; ?></div><div class="act-meta"><?= timeAgo($a['created_at']) ?></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentActs)): ?><div style="font-size:11px;color:var(--muted);padding:12px 0">No activity yet.</div><?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-server"></i>System</div></div>
      <div class="panel-body">
        <div class="metric-row"><span class="metric-name">PHP Version</span><span class="metric-val"><?= PHP_VERSION ?></span></div>
        <div class="metric-row"><span class="metric-name">MySQL</span><span class="metric-val"><?= db()->query('SELECT VERSION() AS v')->fetchColumn() ?></span></div>
        <div class="metric-row"><span class="metric-name">Upload Max</span><span class="metric-val"><?= ini_get('upload_max_filesize') ?></span></div>
        <div class="metric-row"><span class="metric-name">Blog URL</span><span class="metric-val" style="font-size:9px;color:var(--blue)"><?= e(BLOG_URL) ?></span></div>
      </div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><div class="panel-title"><i class="ti ti-file-text"></i>Recent Posts</div><a class="panel-action" href="<?= url('admin/posts.php') ?>">VIEW ALL</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>TITLE</th><th>STATUS</th><th>DATE</th><th>ACTIONS</th></tr></thead>
      <tbody>
        <?php foreach ($recentPosts as $p): $sbmap=['published'=>'s-pub','draft'=>'s-draft','review'=>'s-rev','scheduled'=>'s-feat']; ?>
        <tr>
          <td><a href="<?= url('post.php?slug='.$p['slug']) ?>" target="_blank" style="color:var(--text);font-weight:600;transition:color .15s" onmouseover="this.style.color='var(--green)'" onmouseout="this.style.color='var(--text)'"><?= e($p['title']) ?></a></td>
          <td><span class="sbadge <?= $sbmap[$p['status']]??'s-draft' ?>"><?= strtoupper($p['status']) ?></span></td>
          <td style="color:var(--muted)"><?= formatDate($p['created_at'],'M j, Y') ?></td>
          <td><div class="row-actions">
            <a class="icon-btn" href="<?= url('admin/new-post.php?slug='.$p['slug']) ?>" title="Edit"><i class="ti ti-edit"></i></a>
            <a class="icon-btn ib" href="<?= url('post.php?slug='.$p['slug']) ?>" target="_blank" title="View"><i class="ti ti-eye"></i></a>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
