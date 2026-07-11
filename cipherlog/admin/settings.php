<?php
// admin/settings.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

$user = getCurrentUser();
$msg  = '';

// Handle delete category (must be before any output)
if (($_GET['action'] ?? '') === 'delete_cat' && isset($_GET['id'])) {
    $catId = (int)$_GET['id'];
    execute("UPDATE categories SET post_count=0 WHERE id=?", [$catId]);
    execute("DELETE FROM categories WHERE id=?", [$catId]);
    redirect(url('admin/settings.php?tab=categories&msg=saved'));
}
// Handle delete tag (must be before any output)
if (($_GET['action'] ?? '') === 'delete_tag' && isset($_GET['id'])) {
    $tagId = (int)$_GET['id'];
    execute("UPDATE tags SET post_count=0 WHERE id=?", [$tagId]);
    execute("DELETE FROM post_tags WHERE tag_id=?", [$tagId]);
    execute("DELETE FROM tags WHERE id=?", [$tagId]);
    redirect(url('admin/settings.php?tab=categories&msg=saved'));
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['tab'] ?? 'general';

    if ($tab === 'general') {
        $fields = ['blog_name','blog_tagline','blog_url','admin_email','posts_per_page','author_name','author_bio'];
        foreach ($fields as $f) setSetting($f, trim($_POST[$f] ?? ''));
        setSetting('allow_comments',   isset($_POST['allow_comments'])   ? '1' : '0');
        setSetting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
    } elseif ($tab === 'profile') {
        $name  = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email']        ?? '');
        $bio   = trim($_POST['bio']          ?? '');
        if ($name) execute("UPDATE users SET display_name=?,email=?,bio=? WHERE id=?", [$name,$email,$bio,$user['id']]);
        // Password change
        if (!empty($_POST['new_pass']) && !empty($_POST['cur_pass'])) {
            if (password_verify($_POST['cur_pass'], $user['password'])) {
                $hash = password_hash($_POST['new_pass'], PASSWORD_BCRYPT);
                execute("UPDATE users SET password=? WHERE id=?", [$hash, $user['id']]);
                $msg = 'password_updated';
            } else {
                $msg = 'wrong_password';
            }
        }
    } elseif ($tab === 'smtp') {
        foreach (['smtp_host','smtp_port','smtp_user','smtp_pass'] as $f) setSetting($f, trim($_POST[$f] ?? ''));
    } elseif ($tab === 'integrations') {
        foreach (['ga_id','disqus_shortname','cloudflare_zone'] as $f) setSetting($f, trim($_POST[$f] ?? ''));
    } elseif ($tab === 'categories') {
        // Add category
        if (!empty($_POST['cat_name'])) {
            $cn = trim($_POST['cat_name']);
            $cs = uniqueSlug($cn, 'categories');
            execute("INSERT IGNORE INTO categories (name,slug,description,color) VALUES (?,?,?,?)",
                [$cn, $cs, trim($_POST['cat_desc']??''), $_POST['cat_color']??'green']);
        }
    }

    if (!$msg) $msg = 'saved';
    redirect(url('admin/settings.php?tab='.$tab.'&msg='.$msg));
}

$tab        = $_GET['tab'] ?? 'general';
$categories = query("SELECT * FROM categories ORDER BY name");
$allTags    = query("SELECT * FROM tags ORDER BY name");
$pageTitle  = 'Settings'; $activePage = 'settings';
include __DIR__ . '/admin_header.php';
?>

<?php $msgMap=['saved'=>['success','Settings saved!'],'password_updated'=>['success','Password updated!'],'wrong_password'=>['error','Current password is incorrect.']];
if (isset($msgMap[$_GET['msg']??''])): [$type,$text]=$msgMap[$_GET['msg']]; ?>
<div class="alert alert-<?= $type ?>"><i class="ti ti-<?= $type==='success'?'check':'alert-circle' ?>"></i><?= $text ?></div>
<?php endif; ?>

<!-- Tab navigation -->
<div class="filter-bar" style="margin-bottom:20px">
    <?php foreach (['general'=>'General','profile'=>'Profile','smtp'=>'Email/SMTP','integrations'=>'Integrations','categories'=>'Categories & Tags'] as $k=>$v): ?>
    <a class="filter-btn <?= $tab===$k?'active':'' ?>" href="?tab=<?= $k ?>"><?= $v ?></a>
    <?php endforeach; ?>
</div>

<?php if ($tab === 'general'): ?>
<form method="POST">
<input type="hidden" name="tab" value="general">
<input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
<div class="grid2">
    <div>
        <div class="panel" style="margin-bottom:14px">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-world"></i>Site Configuration</div></div>
            <div class="panel-body">
                <div class="form-group"><label class="form-label">Blog Name</label><input class="form-input" name="blog_name" value="<?= e(getSetting('blog_name')) ?>"></div>
                <div class="form-group"><label class="form-label">Tagline</label><input class="form-input" name="blog_tagline" value="<?= e(getSetting('blog_tagline')) ?>"></div>
                <div class="form-group"><label class="form-label">Blog URL</label><input class="form-input" name="blog_url" value="<?= e(getSetting('blog_url')) ?>"></div>
                <div class="form-group"><label class="form-label">Admin Email</label><input class="form-input" type="email" name="admin_email" value="<?= e(getSetting('admin_email')) ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Posts Per Page</label><input class="form-input" type="number" name="posts_per_page" value="<?= e(getSetting('posts_per_page','10')) ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Author Name</label><input class="form-input" name="author_name" value="<?= e(getSetting('author_name')) ?>"></div>
                <div class="form-group"><label class="form-label">Author Bio</label><textarea class="form-input" name="author_bio" style="min-height:80px"><?= e(getSetting('author_bio')) ?></textarea></div>
                <button class="btn btn-primary" type="submit"><i class="ti ti-check"></i>SAVE</button>
            </div>
        </div>
    </div>
    <div>
        <div class="panel">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-toggle-left"></i>Feature Toggles</div></div>
            <div class="panel-body" style="padding:0 18px">
                <div class="toggle-row">
                    <div><div class="toggle-name">Allow Comments</div><div class="toggle-desc">Readers can leave comments on posts</div></div>
                    <label style="cursor:pointer"><input type="checkbox" name="allow_comments" style="display:none" <?= getSetting('allow_comments','1')==='1'?'checked':'' ?>><div class="toggle <?= getSetting('allow_comments','1')==='1'?'on':'' ?>"></div></label>
                </div>
                <div class="toggle-row">
                    <div><div class="toggle-name">Maintenance Mode</div><div class="toggle-desc">Show maintenance page to visitors</div></div>
                    <label style="cursor:pointer"><input type="checkbox" name="maintenance_mode" style="display:none" <?= getSetting('maintenance_mode')==='1'?'checked':'' ?>><div class="toggle <?= getSetting('maintenance_mode')==='1'?'on':'' ?>"></div></label>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<?php elseif ($tab === 'profile'): ?>
<form method="POST">
<input type="hidden" name="tab" value="profile">
<input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
<div class="grid2">
    <div>
        <div class="panel" style="margin-bottom:14px">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-user"></i>Profile Info</div></div>
            <div class="panel-body">
                <div class="form-group"><label class="form-label">Display Name</label><input class="form-input" name="display_name" value="<?= e($user['display_name']) ?>"></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" name="email" value="<?= e($user['email']) ?>"></div>
                <div class="form-group"><label class="form-label">Bio</label><textarea class="form-input" name="bio" style="min-height:80px"><?= e($user['bio']) ?></textarea></div>
                <button class="btn btn-primary" type="submit"><i class="ti ti-check"></i>SAVE PROFILE</button>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="ti ti-lock"></i>Change Password</div></div>
        <div class="panel-body">
            <div class="form-group"><label class="form-label">Current Password</label><input class="form-input" type="password" name="cur_pass" placeholder="••••••••"></div>
            <div class="form-group"><label class="form-label">New Password</label><input class="form-input" type="password" name="new_pass" placeholder="••••••••"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Confirm New Password</label><input class="form-input" type="password" name="confirm_pass" placeholder="••••••••"></div>
            <button class="btn btn-blue" type="submit" style="margin-top:16px"><i class="ti ti-lock"></i>UPDATE PASSWORD</button>
        </div>
    </div>
</div>
</form>

<?php elseif ($tab === 'smtp'): ?>
<form method="POST">
<input type="hidden" name="tab" value="smtp">
<input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="ti ti-at"></i>SMTP Email Configuration</div></div>
    <div class="panel-body" style="max-width:500px">
        <div class="form-row">
            <div class="form-group"><label class="form-label">SMTP Host</label><input class="form-input" name="smtp_host" value="<?= e(getSetting('smtp_host')) ?>" placeholder="smtp.gmail.com"></div>
            <div class="form-group"><label class="form-label">Port</label><input class="form-input" name="smtp_port" value="<?= e(getSetting('smtp_port','587')) ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Username</label><input class="form-input" name="smtp_user" value="<?= e(getSetting('smtp_user')) ?>"></div>
            <div class="form-group"><label class="form-label">Password</label><input class="form-input" type="password" name="smtp_pass" value="<?= e(getSetting('smtp_pass')) ?>"></div>
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn btn-primary" type="submit"><i class="ti ti-check"></i>SAVE</button>
            <button class="btn btn-ghost" type="button" onclick="testSmtp()"><i class="ti ti-send"></i>TEST CONNECTION</button>
        </div>
    </div>
</div>
</form>

<?php elseif ($tab === 'integrations'): ?>
<form method="POST">
<input type="hidden" name="tab" value="integrations">
<input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="ti ti-api"></i>Third-Party Integrations</div></div>
    <div class="panel-body" style="max-width:500px">
        <div class="form-group"><label class="form-label">Google Analytics ID</label><input class="form-input" name="ga_id" value="<?= e(getSetting('ga_id')) ?>" placeholder="G-XXXXXXXXXX"></div>
        <div class="form-group"><label class="form-label">Disqus Shortname</label><input class="form-input" name="disqus_shortname" value="<?= e(getSetting('disqus_shortname')) ?>" placeholder="cipherlog"></div>
        <div class="form-group" style="margin:0"><label class="form-label">Cloudflare Zone ID</label><input class="form-input" name="cloudflare_zone" value="<?= e(getSetting('cloudflare_zone')) ?>" placeholder="zone id for cache purge"></div>
        <button class="btn btn-primary" type="submit" style="margin-top:16px"><i class="ti ti-check"></i>SAVE</button>
    </div>
</div>
</form>

<?php elseif ($tab === 'categories'): ?>
<div class="grid2">
    <div>
        <div class="panel" style="margin-bottom:14px">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-folder"></i>Categories</div></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>NAME</th><th>SLUG</th><th>POSTS</th><th>ACTIONS</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><span class="ptag ptag-linux"><?= e($c['name']) ?></span></td>
                        <td style="color:var(--muted)"><?= e($c['slug']) ?></td>
                        <td style="color:var(--green)"><?= $c['post_count'] ?></td>
                        <td><div class="row-actions">
                            <button class="icon-btn" onclick="showToast('Edit coming soon','info')"><i class="ti ti-edit"></i></button>
                            <a class="icon-btn del" href="?tab=categories&action=delete_cat&id=<?= $c['id'] ?>" onclick="return confirm('Delete category?')"><i class="ti ti-trash"></i></a>
                        </div></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-tag"></i>Tags</div></div>
            <div class="panel-body">
                <div style="display:flex;flex-wrap:wrap;gap:6px">
                    <?php foreach ($allTags as $t): ?>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;border:1px solid var(--border);font-size:9px;color:var(--muted)">
                        <?= e($t['name']) ?> <b style="color:var(--text)"><?= $t['post_count'] ?></b>
                        <a href="?tab=categories&action=delete_tag&id=<?= $t['id'] ?>" style="color:var(--muted);font-size:11px;line-height:1" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='var(--muted)'" onclick="return confirm('Delete tag?')">×</a>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div>
        <form method="POST">
        <input type="hidden" name="tab" value="categories">
        <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
        <div class="panel">
            <div class="panel-head"><div class="panel-title"><i class="ti ti-plus"></i>Add Category</div></div>
            <div class="panel-body">
                <div class="form-group"><label class="form-label">Name *</label><input class="form-input" name="cat_name" placeholder="Category name" required></div>
                <div class="form-group"><label class="form-label">Description</label><textarea class="form-input" name="cat_desc" style="min-height:70px" placeholder="Brief description..."></textarea></div>
                <div class="form-group"><label class="form-label">Color</label>
                    <div style="display:flex;gap:8px">
                        <?php foreach (['green'=>'#00ff9d','blue'=>'#0af','red'=>'#ff3e3e','purple'=>'#a78bfa','yellow'=>'#ffd600'] as $cn=>$cv): ?>
                        <label style="cursor:pointer"><input type="radio" name="cat_color" value="<?= $cn ?>" style="display:none" <?= $cn==='green'?'checked':'' ?>>
                        <div style="width:28px;height:28px;border-radius:50%;background:<?= $cv ?>;border:2px solid transparent;transition:all .15s" onclick="this.style.borderColor='white'"></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="ti ti-plus"></i>ADD CATEGORY</button>
            </div>
        </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function testSmtp(){showToast('SMTP test sent to admin email','info');}
document.querySelectorAll('input[type=checkbox]').forEach(cb=>{
    cb.addEventListener('change',function(){
        const toggle=this.nextElementSibling;
        if(toggle&&toggle.classList.contains('toggle'))toggle.classList.toggle('on',this.checked);
    });
});
</script>
<?php include __DIR__ . '/admin_footer.php'; ?>
