<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
authStart();

$q    = trim($_GET['q']   ?? '');
$tag  = trim($_GET['tag'] ?? '');
$cat  = trim($_GET['cat'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$per  = 9;
$off  = ($page-1)*$per;

$posts = [];
$total = 0;
if ($q || $tag || $cat) {
    $posts = getPosts(['status'=>'published','search'=>$q?:'','tag'=>$tag?:'','category'=>$cat?:'','limit'=>$per,'offset'=>$off]);
    // Efficient count query instead of loading all posts
    $countWhere = ["p.status = ?"];
    $countParams = ['published'];
    if ($q) { $countWhere[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)"; $sq = "%$q%"; array_push($countParams, $sq, $sq, $sq); }
    if ($tag) { $countWhere[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON pt.tag_id=t.id WHERE pt.post_id=p.id AND t.slug=?)"; $countParams[] = $tag; }
    if ($cat) { $countWhere[] = "c.slug = ?"; $countParams[] = $cat; }
    $countWhereStr = 'WHERE ' . implode(' AND ', $countWhere);
    $total = (int)queryOne(
        "SELECT COUNT(*) AS c FROM posts p LEFT JOIN categories c ON p.category_id=c.id $countWhereStr",
        $countParams
    )['c'];
}
$pages = ceil($total/$per);

$catIconMap=['linux'=>['ti-brand-debian','pci-linux','ptag-linux'],'network'=>['ti-network','pci-network','ptag-network'],'security'=>['ti-shield','pci-security','ptag-security'],'scripting'=>['ti-terminal','pci-scripting','ptag-scripting'],'tools'=>['ti-tools','pci-tools','ptag-tools']];
$allTags = query("SELECT * FROM tags WHERE post_count>0 ORDER BY post_count DESC LIMIT 20");
$allCats = query("SELECT * FROM categories WHERE post_count>0 ORDER BY post_count DESC");
$pageTitle = ($q ? "Search: $q" : 'Search') . ' — ' . getSetting('blog_name');
include __DIR__.'/includes/header.php';
?>
<div class="container" style="padding-top:48px;padding-bottom:64px">
  <div style="max-width:700px;margin:0 auto">
    <h1 style="font-size:28px;font-weight:700;margin-bottom:6px">Search <span style="color:var(--green)">CipherLog</span></h1>
    <p style="font-size:11px;color:var(--muted);margin-bottom:28px"><?= (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'",[])['c'] ?> posts on Linux, Network, Security, Scripting & Tools</p>

    <form method="GET" action="">
      <div style="display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--border);border-radius:8px;padding:0 18px;margin-bottom:10px;transition:border-color .2s" onfocusin="this.style.borderColor='var(--green)'" onfocusout="this.style.borderColor='var(--border)'">
        <i class="ti ti-search" style="font-size:20px;color:var(--muted);flex-shrink:0"></i>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="iptables, ssh, bash, subnetting..." style="flex:1;background:transparent;border:none;outline:none;font-family:'JetBrains Mono',monospace;font-size:14px;color:var(--text);padding:16px 0">
        <?php if ($q): ?><button type="button" onclick="window.location='<?= url('search.php') ?>'" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:16px">✕</button><?php endif; ?>
      </div>
    </form>

    <?php if (!$q && !$tag && !$cat): ?>
    <div style="margin-bottom:28px">
      <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:8px">POPULAR SEARCHES</div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php foreach (['iptables','ssh','bash scripting','subnetting','firewall','nmap','wireguard','systemd'] as $s): ?>
        <a href="?q=<?= urlencode($s) ?>" style="padding:4px 12px;border-radius:20px;border:1px solid var(--border);font-size:10px;color:var(--muted);transition:all .15s" onmouseover="this.style.borderColor='var(--green)';this.style.color='var(--green)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'"><?= $s ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="margin-bottom:28px">
      <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:8px">BROWSE BY TAG</div>
      <div class="tag-cloud">
        <?php $tagCols=['linux'=>'ptag-linux','network'=>'ptag-network','security'=>'ptag-security','scripting'=>'ptag-scripting','bash'=>'ptag-scripting','tools'=>'ptag-tools'];
        foreach ($allTags as $t): $tc=$tagCols[$t['slug']]??'ptag-linux'; ?>
        <a class="tc-tag <?= $tc ?>" href="?tag=<?= $t['slug'] ?>"><?= e($t['name']) ?> <span style="opacity:.6;font-size:8px"><?= $t['post_count'] ?></span></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:8px">BROWSE BY CATEGORY</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
        <?php foreach ($allCats as $c):
          $ci=['linux'=>['ti-brand-debian','ci-green'],'network'=>['ti-network','ci-blue'],'security'=>['ti-shield','ci-red'],'scripting'=>['ti-terminal','ci-purple'],'tools'=>['ti-tools','ci-yellow']];
          $cc=$ci[$c['slug']]??$ci['linux'];
        ?>
        <a href="?cat=<?= $c['slug'] ?>" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:6px;border:1px solid var(--border);background:var(--card);text-decoration:none;transition:all .15s;font-size:10px;color:var(--muted)" onmouseover="this.style.borderColor='var(--green)';this.style.color='var(--green)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
          <i class="ti <?= $cc[0] ?>" style="font-size:14px"></i><?= e($c['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($q || $tag || $cat): ?>
    <div style="margin-bottom:20px;font-size:11px;color:var(--muted)">
      <?php if ($q): ?>Found <b style="color:var(--green)"><?= $total ?></b> results for "<b style="color:var(--text)"><?= e($q) ?></b>"<?php endif; ?>
      <?php if ($tag): ?>Posts tagged <b style="color:var(--green)"><?= e($tag) ?></b> (<?= $total ?>)<?php endif; ?>
      <?php if ($cat): ?>Posts in category <b style="color:var(--green)"><?= e($cat) ?></b> (<?= $total ?>)<?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
    <div style="text-align:center;padding:48px 0;color:var(--muted)">
      <i class="ti ti-search-off" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3"></i>
      <p style="font-size:12px">No posts found. <a href="<?= url('search.php') ?>" style="color:var(--blue)">Clear search</a></p>
    </div>
    <?php else: ?>
    <?php foreach ($posts as $p):
      $pi=$catIconMap[$p['cat_slug']??'linux']??$catIconMap['linux'];
    ?>
    <a href="<?= url('post.php?slug='.$p['slug']) ?>" style="display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--border);text-decoration:none;transition:background .15s;cursor:pointer">
      <div class="cat-icon <?= 'ci-'.($p['cat_color']??'green') ?>" style="width:44px;height:44px;font-size:18px;flex-shrink:0">
        <i class="ti <?= $pi[0] ?>"></i>
      </div>
      <div style="flex:1">
        <div style="font-size:9px;color:var(--muted);letter-spacing:.5px;margin-bottom:4px"><?= e($p['cat_name']??'') ?> · <?= $p['reading_time'] ?> min read</div>
        <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;transition:color .15s" class="sr-title"><?= e($p['title']) ?></div>
        <div style="font-size:10px;color:var(--muted);line-height:1.6;font-family:'Inter',sans-serif"><?= e(excerptFromContent($p['content'],120)) ?></div>
        <div style="display:flex;gap:12px;font-size:9px;color:var(--muted);margin-top:8px">
          <span><i class="ti ti-calendar"></i> <?= formatDate($p['published_at'],'M j, Y') ?></span>
          <?php if ($p['views']): ?><span style="color:var(--green)"><i class="ti ti-eye"></i> <?= number_format($p['views']) ?></span><?php endif; ?>
          <?php foreach (array_slice($p['tags'],0,3) as $t): ?><span><i class="ti ti-tag"></i> <?= e($t['name']) ?></span><?php endforeach; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>

    <?php if ($pages>1): ?>
    <div class="pagination">
      <?php $qs=http_build_query(array_filter(['q'=>$q,'tag'=>$tag,'cat'=>$cat]));
      if ($page>1): ?><a class="pag-btn" href="?<?= $qs ?>&page=<?= $page-1 ?>"><i class="ti ti-arrow-left"></i>Prev</a><?php endif; ?>
      <?php for($i=1;$i<=$pages;$i++): ?><a class="pag-btn <?= $i==$page?'active':'' ?>" href="?<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?>
      <?php if ($page<$pages): ?><a class="pag-btn" href="?<?= $qs ?>&page=<?= $page+1 ?>">Next<i class="ti ti-arrow-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<style>a:hover .sr-title{color:var(--green)!important}</style>
<?php include __DIR__.'/includes/footer.php'; ?>
