<?php
// admin/posts.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

// Handle delete
if ($_GET['action']??'' === 'delete' && isset($_GET['id'])) {
    verifyCsrf();
    $pid = (int)$_GET['id'];
    // Update category post_count before deleting
    $postCat = queryOne("SELECT category_id FROM posts WHERE id=?", [$pid]);
    execute("DELETE FROM posts WHERE id=?", [$pid]);
    execute("DELETE FROM post_tags WHERE post_id=?", [$pid]);
    if ($postCat && $postCat['category_id']) {
        execute("UPDATE categories SET post_count=(SELECT COUNT(*) FROM posts WHERE category_id=? AND status='published') WHERE id=?", [$postCat['category_id'], $postCat['category_id']]);
    }
    redirect(url('admin/posts.php?msg=deleted'));
}

$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15;
$offset = ($page - 1) * $per;

$where  = [];
$params = [];
if ($status !== 'all') { $where[] = "p.status=?"; $params[] = $status; }
if ($search) { $where[] = "(p.title LIKE ? OR p.excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$posts = query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, u.display_name AS author_name
                FROM posts p
                LEFT JOIN categories c ON p.category_id=c.id
                LEFT JOIN users u ON p.author_id=u.id
                $whereStr ORDER BY p.created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$per, $offset]));

$totalParams = $params;
$total = (int)queryOne("SELECT COUNT(*) AS c FROM posts p $whereStr", $totalParams)['c'];
$pages = ceil($total / $per);

$counts = [
    'all'       => (int)queryOne("SELECT COUNT(*) AS c FROM posts",[])['c'],
    'published' => (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'",[])['c'],
    'draft'     => (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='draft'",[])['c'],
    'review'    => (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='review'",[])['c'],
];

$pageTitle  = 'All Posts';
$activePage = 'posts';
include __DIR__ . '/admin_header.php';
?>
<?php if ($_GET['msg']??'' === 'deleted'): ?>
<div class="alert alert-success"><i class="ti ti-check"></i>Post deleted.</div>
<?php endif; ?>

<div class="filter-bar">
    <div class="search-box"><i class="ti ti-search"></i>
        <input placeholder="search posts..." value="<?= e($search) ?>" oninput="delaySearch(this.value)">
    </div>
    <?php foreach (['all'=>'All','published'=>'Published','draft'=>'Draft','review'=>'Review'] as $k=>$v): ?>
    <a class="filter-btn <?= $status===$k?'active':'' ?>" href="?status=<?= $k ?>"><?= $v ?> (<?= $counts[$k]??0 ?>)</a>
    <?php endforeach; ?>
    <a class="btn btn-primary btn-sm" href="<?= url('admin/new-post.php') ?>" style="margin-left:auto"><i class="ti ti-plus"></i>NEW POST</a>
</div>

<div class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th><input type="checkbox" style="accent-color:var(--green)" onchange="toggleAll(this)"></th>
                <th>#</th><th>TITLE</th><th>CATEGORY</th><th>VIEWS</th><th>STATUS</th><th>DATE</th><th>ACTIONS</th>
            </tr></thead>
            <tbody>
            <?php if (empty($posts)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No posts found.</td></tr>
            <?php else: foreach ($posts as $p):
                $sbmap = ['published'=>'s-pub','draft'=>'s-draft','review'=>'s-rev','scheduled'=>'s-feat','featured'=>'s-feat'];
            ?>
            <tr>
                <td><input type="checkbox" style="accent-color:var(--green)"></td>
                <td style="color:var(--muted)"><?= $p['id'] ?></td>
                <td>
                    <div style="font-weight:600"><?= e(mb_strimwidth($p['title'],0,55,'...')) ?></div>
                    <div style="font-size:9px;color:var(--muted);margin-top:2px"><?= e($p['slug']) ?></div>
                </td>
                <td><?php if ($p['cat_name']): ?><span class="ptag ptag-linux"><?= e($p['cat_name']) ?></span><?php else: ?><span style="color:var(--muted);font-size:10px">—</span><?php endif; ?></td>
                <td style="color:var(--green);font-weight:600"><?= number_format($p['views']) ?></td>
                <td><span class="sbadge <?= $sbmap[$p['status']]??'s-draft' ?>">● <?= strtoupper($p['status']) ?><?= $p['is_featured']?' ★':'' ?></span></td>
                <td style="color:var(--muted)"><?= formatDate($p['created_at'],'M j, Y') ?></td>
                <td>
                    <div class="row-actions">
                        <a class="icon-btn" href="<?= url('admin/new-post.php?slug='.$p['slug']) ?>" title="Edit"><i class="ti ti-edit"></i></a>
                        <a class="icon-btn ib" href="<?= url('post.php?slug='.$p['slug']) ?>" target="_blank" title="View"><i class="ti ti-eye"></i></a>
                        <a class="icon-btn" href="#" onclick="navigator.clipboard.writeText('<?= url('post.php?slug='.$p['slug']) ?>');showToast('Link copied!','success');return false" title="Copy link"><i class="ti ti-copy"></i></a>
                        <a class="icon-btn del" href="<?= url('admin/posts.php?action=delete&id='.$p['id']) ?>" onclick="return confirm('Delete this post?')" title="Delete"><i class="ti ti-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:10px;color:var(--muted)">Showing <?= count($posts) ?> of <?= $total ?> posts</span>
        <?php if ($pages > 1): ?>
        <div style="display:flex;gap:5px">
            <?php $qs=http_build_query(array_filter(['status'=>$status,'q'=>$search]));
            if ($page>1): ?><a class="filter-btn" href="?<?= $qs ?>&page=<?= $page-1 ?>">← Prev</a><?php endif; ?>
            <?php for($i=1;$i<=$pages;$i++): ?>
            <a class="filter-btn <?= $i==$page?'active':'' ?>" href="?<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page<$pages): ?><a class="filter-btn" href="?<?= $qs ?>&page=<?= $page+1 ?>">Next →</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let searchTimer;
function delaySearch(v){ clearTimeout(searchTimer); searchTimer=setTimeout(()=>window.location='?status=<?= $status ?>&q='+encodeURIComponent(v),400); }
function toggleAll(el){ document.querySelectorAll('tbody input[type=checkbox]').forEach(cb=>cb.checked=el.checked); }
</script>
<?php include __DIR__ . '/admin_footer.php'; ?>
