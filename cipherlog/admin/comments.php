<?php
// admin/comments.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

// Handle actions
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);
if ($id && in_array($action, ['approve','spam','trash','delete'])) {
    if ($action === 'delete') {
        execute("DELETE FROM comments WHERE id=?", [$id]);
    } else {
        $statusMap = ['approve'=>'approved','spam'=>'spam','trash'=>'trash'];
        execute("UPDATE comments SET status=? WHERE id=?", [$statusMap[$action], $id]);
    }
    redirect(url('admin/comments.php?msg='.$action));
}
// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_to']) && isset($_POST['post_id'])) {
    $replyContent = trim($_POST['reply_content'] ?? '');
    $parentCommentId = (int)$_POST['reply_to'];
    $postId = (int)$_POST['post_id'];
    if ($replyContent && $parentCommentId && $postId) {
        $admin = getCurrentUser();
        execute(
            "INSERT INTO comments (post_id,parent_id,author_name,author_email,author_ip,content,status) VALUES (?,?,?,?,?,?,?)",
            [$postId, $parentCommentId, $admin['display_name'] ?? $admin['username'], $admin['email'], $_SERVER['REMOTE_ADDR'] ?? '', $replyContent, 'approved']
        );
        // Update comment count on post
        execute("UPDATE posts SET comment_count = (SELECT COUNT(*) FROM comments WHERE post_id=? AND status='approved') WHERE id=?", [$postId, $postId]);
        redirect(url('admin/comments.php?msg=replied'));
    }
}
// Bulk
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bulk_action'])) {
    $ids = array_map('intval', $_POST['ids'] ?? []);
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($_POST['bulk_action'] === 'delete') {
            execute("DELETE FROM comments WHERE id IN ($placeholders)", $ids);
        } else {
            $s = ['approve'=>'approved','spam'=>'spam','trash'=>'trash'][$_POST['bulk_action']] ?? null;
            if ($s) {
                $params = array_merge([$s], $ids);
                execute("UPDATE comments SET status=? WHERE id IN ($placeholders)", $params);
            }
        }
        redirect(url('admin/comments.php?msg=bulk'));
    }
}

$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15; $offset = ($page-1)*$per;

$where = []; $params = [];
if ($status !== 'all') { $where[] = "c.status=?"; $params[] = $status; }
if ($search) { $where[] = "(c.author_name LIKE ? OR c.content LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';

$comments = query("SELECT c.*, p.title AS post_title, p.slug AS post_slug
                   FROM comments c LEFT JOIN posts p ON c.post_id=p.id
                   $whereStr ORDER BY c.created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$per, $offset]));
$total  = (int)queryOne("SELECT COUNT(*) AS c FROM comments c $whereStr", $params)['c'];
$pages  = ceil($total/$per);
$counts = [
    'all'      => (int)queryOne("SELECT COUNT(*) AS c FROM comments",[])['c'],
    'pending'  => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='pending'",[])['c'],
    'approved' => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='approved'",[])['c'],
    'spam'     => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='spam'",[])['c'],
    'trash'    => (int)queryOne("SELECT COUNT(*) AS c FROM comments WHERE status='trash'",[])['c'],
];

$msgMap = ['approve'=>'Comment approved','spam'=>'Marked as spam','trash'=>'Moved to trash','delete'=>'Comment deleted','bulk'=>'Bulk action applied','replied'=>'Reply posted!'];
$pageTitle = 'Comments'; $activePage = 'comments';
include __DIR__ . '/admin_header.php';
?>

<?php if (isset($msgMap[$_GET['msg']??''])): ?>
<div class="alert alert-success"><i class="ti ti-check"></i><?= $msgMap[$_GET['msg']] ?></div>
<?php endif; ?>

<form method="POST" id="bulk-form">
<div class="filter-bar">
    <div class="search-box"><i class="ti ti-search"></i>
        <input placeholder="search comments..." value="<?= e($search) ?>" oninput="delaySearch(this.value)">
    </div>
    <?php foreach (['all'=>'All','pending'=>'Pending','approved'=>'Approved','spam'=>'Spam','trash'=>'Trash'] as $k=>$v): ?>
    <a class="filter-btn <?= $status===$k?'active':'' ?>" href="?status=<?= $k ?>"><?= $v ?> (<?= $counts[$k] ?>)</a>
    <?php endforeach; ?>
    <div style="margin-left:auto;display:flex;gap:6px">
        <select name="bulk_action" class="form-input" style="width:auto;padding:6px 10px;font-size:10px">
            <option value="">Bulk Action</option>
            <option value="approve">Approve</option>
            <option value="spam">Mark Spam</option>
            <option value="trash">Trash</option>
            <option value="delete">Delete</option>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">APPLY</button>
    </div>
</div>

<div class="panel">
    <div class="panel-body" style="padding:0">
        <?php if (empty($comments)): ?>
        <div style="text-align:center;padding:48px;color:var(--muted);font-size:12px">No comments found.</div>
        <?php else: foreach ($comments as $c):
            $sbmap = ['pending'=>'s-pend','approved'=>'s-appr','spam'=>'s-spam','trash'=>'s-draft'];
        ?>
        <div style="display:flex;gap:14px;padding:16px 18px;border-bottom:1px solid var(--border)">
            <input type="checkbox" name="ids[]" value="<?= $c['id'] ?>" style="accent-color:var(--green);flex-shrink:0;margin-top:4px">
            <div style="width:34px;height:34px;border-radius:50%;background:var(--card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--muted);flex-shrink:0;font-weight:600;text-transform:uppercase">
                <?= strtoupper(substr($c['author_name'],0,2)) ?>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
                    <span style="font-size:11px;font-weight:600;color:var(--text)"><?= e($c['author_name']) ?></span>
                    <span style="font-size:9px;color:var(--muted)"><?= e($c['author_email']) ?></span>
                    <span class="sbadge <?= $sbmap[$c['status']]??'s-draft' ?>"><?= strtoupper($c['status']) ?></span>
                    <span style="font-size:9px;color:var(--muted);margin-left:auto"><?= timeAgo($c['created_at']) ?></span>
                </div>
                <div style="font-size:11px;color:var(--muted);line-height:1.7;font-family:'Inter',sans-serif;margin-bottom:8px"><?= nl2br(e($c['content'])) ?></div>
                <div style="font-size:9px;color:var(--blue)">
                    on: <a href="<?= url('post.php?slug='.$c['post_slug']) ?>" target="_blank" style="color:var(--blue)"><?= e($c['post_title']) ?></a>
                    &nbsp;·&nbsp; IP: <?= e($c['author_ip']) ?>
                </div>
                <div style="display:flex;gap:6px;margin-top:10px">
                    <?php if ($c['status']!=='approved'): ?>
                    <a class="btn btn-primary btn-sm" href="?action=approve&id=<?= $c['id'] ?>&status=<?= $status ?>"><i class="ti ti-check"></i>Approve</a>
                    <?php endif; ?>
                    <a class="btn btn-ghost btn-sm" href="#" onclick="showReplyBox(<?= $c['id'] ?>);return false"><i class="ti ti-corner-down-right"></i>Reply</a>
                    <?php if ($c['status']!=='spam'): ?>
                    <a class="btn btn-ghost btn-sm" href="?action=spam&id=<?= $c['id'] ?>&status=<?= $status ?>"><i class="ti ti-ban"></i>Spam</a>
                    <?php endif; ?>
                    <a class="btn btn-danger btn-sm" href="?action=delete&id=<?= $c['id'] ?>&status=<?= $status ?>" onclick="return confirm('Delete this comment?')"><i class="ti ti-trash"></i>Delete</a>
                </div>
                <div id="reply-<?= $c['id'] ?>" style="display:none;margin-top:12px">
                    <form action="<?= url('admin/comments.php') ?>" method="POST">
                        <input type="hidden" name="reply_to" value="<?= $c['id'] ?>">
                        <input type="hidden" name="post_id" value="<?= $c['post_id'] ?>">
                        <textarea class="form-input" name="reply_content" style="min-height:80px;margin-bottom:8px" placeholder="Write your reply..."></textarea>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="ti ti-send"></i>Post Reply</button>
                        <button class="btn btn-ghost btn-sm" type="button" onclick="showReplyBox(<?= $c['id'] ?>)">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <?php if ($pages > 1): ?>
    <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;justify-content:center;gap:5px">
        <?php $qs=http_build_query(array_filter(['status'=>$status,'q'=>$search]));
        for($i=1;$i<=$pages;$i++): ?>
        <a class="filter-btn <?= $i==$page?'active':'' ?>" href="?<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
</form>

<script>
let st;
function delaySearch(v){clearTimeout(st);st=setTimeout(()=>window.location='?status=<?= $status ?>&q='+encodeURIComponent(v),400);}
function showReplyBox(id){var b=document.getElementById('reply-'+id);b.style.display=b.style.display==='none'?'block':'none';}
</script>
<?php include __DIR__ . '/admin_footer.php'; ?>
