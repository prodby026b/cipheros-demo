<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

$user = getCurrentUser();
$editSlug = trim($_GET['slug'] ?? '');
$post = $editSlug ? getPost($editSlug) : null;
$categories = query("SELECT * FROM categories ORDER BY name");
$allTags    = query("SELECT * FROM tags ORDER BY name");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title     = trim($_POST['title']   ?? '');
    $content   = trim($_POST['content'] ?? '');
    $catId     = (int)($_POST['category_id'] ?? 0);
    $status    = $_POST['status']    ?? 'draft';
    $excerpt   = trim($_POST['excerpt']  ?? '');
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc  = trim($_POST['meta_desc']  ?? '');
    $focusKw   = trim($_POST['focus_keyword'] ?? '');
    $isFeatured= isset($_POST['is_featured']) ? 1 : 0;
    $allowComments = isset($_POST['allow_comments']) ? 1 : 0;
    $publishedAt   = !empty($_POST['published_at']) ? $_POST['published_at'] : ($status === 'published' ? date('Y-m-d H:i:s') : null);
    $newSlug   = !empty($_POST['slug']) ? slug($_POST['slug']) : ($post ? $post['slug'] : uniqueSlug($title,'posts'));
    $readTime  = readingTime($content);

    if ($title && $content) {
        if ($post) {
            // UPDATE
            execute("UPDATE posts SET title=?,slug=?,content=?,excerpt=?,category_id=?,status=?,is_featured=?,allow_comments=?,meta_title=?,meta_desc=?,focus_keyword=?,reading_time=?,published_at=?,updated_at=NOW() WHERE id=?",
                [$title,$newSlug,$content,$excerpt,$catId?:null,$status,$isFeatured,$allowComments,$metaTitle,$metaDesc,$focusKw,$readTime,$publishedAt,$post['id']]);
            $postId = $post['id'];
            $msg = 'updated';
        } else {
            // INSERT
            $postId = execute("INSERT INTO posts (title,slug,content,excerpt,category_id,author_id,status,is_featured,allow_comments,meta_title,meta_desc,focus_keyword,reading_time,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$title,$newSlug,$content,$excerpt,$catId?:null,$user['id'],$status,$isFeatured,$allowComments,$metaTitle,$metaDesc,$focusKw,$readTime,$publishedAt]);
            $msg = 'created';
        }

        // Handle tags - first decrement post_count for old tags, then reassign
        $oldTagRows = query("SELECT tag_id FROM post_tags WHERE post_id=?", [$postId]);
        foreach ($oldTagRows as $ot) {
            execute("UPDATE tags SET post_count = GREATEST(post_count - 1, 0) WHERE id=?", [$ot['tag_id']]);
        }
        execute("DELETE FROM post_tags WHERE post_id=?",[$postId]);
        $tagNames = array_map('trim', explode(',', $_POST['tags'] ?? ''));
        foreach ($tagNames as $tn) {
            if (!$tn) continue;
            $ts = slug($tn);
            $tag = queryOne("SELECT id FROM tags WHERE slug=?",[$ts]);
            if (!$tag) {
                $tid = execute("INSERT INTO tags (name,slug,post_count) VALUES (?,?,1)",[$tn,$ts]);
            } else {
                $tid = $tag['id'];
                execute("UPDATE tags SET post_count=post_count+1 WHERE id=?",[$tid]);
            }
            execute("INSERT IGNORE INTO post_tags (post_id,tag_id) VALUES (?,?)",[$postId,$tid]);
        }

        // Featured image upload
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $info = uploadFile($_FILES['featured_image'], UPLOADS_DIR);
            if ($info) {
                execute("INSERT INTO media (filename,original_name,mime_type,size,width,height,uploader_id) VALUES (?,?,?,?,?,?,?)",
                    [$info['filename'],$info['original_name'],$info['mime_type'],$info['size'],$info['width']??null,$info['height']??null,$user['id']]);
                execute("UPDATE posts SET featured_image=? WHERE id=?",[$info['filename'],$postId]);
            }
        }

        // Update category post count
        if ($catId) execute("UPDATE categories SET post_count=(SELECT COUNT(*) FROM posts WHERE category_id=? AND status='published') WHERE id=?",[$catId,$catId]);

        redirect(url('admin/new-post.php?slug='.$newSlug.'&msg='.$msg));
    }
}

$postTags = $post ? implode(', ', array_column($post['tags']??[], 'name')) : '';
$activePage = $post ? 'Edit Post' : 'New Post';
include __DIR__ . '/admin_header.php';
?>

<?php if ($_GET['msg']??''): ?>
<div class="alert alert-success"><i class="ti ti-check"></i>Post <?= e($_GET['msg']) ?> successfully! <a href="<?= url('post.php?slug='.($post['slug']??$editSlug)) ?>" target="_blank" style="color:var(--blue)">View post →</a></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
<div class="grid3">
  <div>
    <div class="panel" style="margin-bottom:14px">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-pencil"></i><?= $post ? 'Edit Post' : 'New Post' ?></div>
        <a class="panel-action" href="<?= url('post.php?slug='.($post['slug']??'')) ?>" target="_blank"><i class="ti ti-eye" style="font-size:11px"></i> PREVIEW</a>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label class="form-label">Title *</label>
          <input class="form-input" name="title" id="title-input" placeholder="Post title..." value="<?= e($post['title']??'') ?>" required oninput="autoSlug()">
        </div>
        <div class="form-group">
          <label class="form-label">Slug</label>
          <input class="form-input" name="slug" id="slug-input" placeholder="auto-generated" value="<?= e($post['slug']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Excerpt</label>
          <textarea class="form-input" name="excerpt" style="min-height:60px" placeholder="Short summary shown in listings..."><?= e($post['excerpt']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Content (Markdown) *</label>
          <div class="editor-toolbar">
            <button type="button" class="icon-btn" onclick="wrapText('**','**')" title="Bold"><i class="ti ti-bold"></i></button>
            <button type="button" class="icon-btn" onclick="wrapText('*','*')" title="Italic"><i class="ti ti-italic"></i></button>
            <button type="button" class="icon-btn" onclick="wrapText('~~','~~')" title="Strike"><i class="ti ti-strikethrough"></i></button>
            <div class="editor-sep"></div>
            <button type="button" class="icon-btn" onclick="insertLine('# ')" title="H1"><i class="ti ti-h-1"></i></button>
            <button type="button" class="icon-btn" onclick="insertLine('## ')" title="H2"><i class="ti ti-h-2"></i></button>
            <button type="button" class="icon-btn" onclick="insertLine('### ')" title="H3"><i class="ti ti-h-3"></i></button>
            <div class="editor-sep"></div>
            <button type="button" class="icon-btn" onclick="wrapText('`','`')" title="Inline Code"><i class="ti ti-code"></i></button>
            <button type="button" class="icon-btn" onclick="insertBlock('```bash\n','\n```')" title="Code Block"><i class="ti ti-braces"></i></button>
            <button type="button" class="icon-btn" onclick="insertLine('> ')" title="Quote"><i class="ti ti-quote"></i></button>
            <div class="editor-sep"></div>
            <button type="button" class="icon-btn" onclick="insertLine('- ')" title="List"><i class="ti ti-list"></i></button>
            <button type="button" class="icon-btn" onclick="insertLine('1. ')" title="Ordered List"><i class="ti ti-list-numbers"></i></button>
            <button type="button" class="icon-btn" onclick="insertLink()" title="Link"><i class="ti ti-link"></i></button>
            <div class="editor-sep"></div>
            <button type="button" class="icon-btn" onclick="toggleFullscreen()" title="Fullscreen" id="fs-btn"><i class="ti ti-maximize"></i></button>
          </div>
          <textarea class="form-input editor-area" name="content" id="editor" required style="font-size:12px;line-height:1.7"><?= e($post['content']??'') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="gap-col">
    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-send"></i>Publish</div></div>
      <div class="panel-body">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-input" name="status">
            <option value="draft"     <?= ($post['status']??'')==='draft'?'selected':'' ?>>Draft</option>
            <option value="published" <?= ($post['status']??'')==='published'?'selected':'' ?>>Published</option>
            <option value="review"    <?= ($post['status']??'')==='review'?'selected':'' ?>>Review</option>
            <option value="scheduled" <?= ($post['status']??'')==='scheduled'?'selected':'' ?>>Scheduled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Publish Date</label>
          <input class="form-input" type="datetime-local" name="published_at"
                 value="<?= $post && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
        </div>
        <div class="form-group" style="margin:0">
          <div style="display:flex;gap:14px">
            <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--muted);cursor:pointer">
              <input type="checkbox" name="is_featured" style="accent-color:var(--green)" <?= ($post['is_featured']??0)?'checked':'' ?>>Featured
            </label>
            <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--muted);cursor:pointer">
              <input type="checkbox" name="allow_comments" style="accent-color:var(--green)" <?= ($post['allow_comments']??1)?'checked':'' ?>>Comments
            </label>
          </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px">
          <button class="btn btn-ghost" type="submit" name="status" onclick="document.querySelector('[name=status]').value='draft'">SAVE DRAFT</button>
          <button class="btn btn-primary" type="submit" style="flex:1"><i class="ti ti-world"></i>PUBLISH</button>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-folder"></i>Category & Tags</div></div>
      <div class="panel-body">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-input" name="category_id">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($post['category_id']??0)==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Tags (comma separated)</label>
          <input class="form-input" name="tags" id="tags-input" value="<?= e($postTags) ?>" placeholder="linux, bash, network">
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:8px" id="tag-chips"></div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-photo"></i>Featured Image</div></div>
      <div class="panel-body">
        <?php if (!empty($post['featured_image'])): ?>
        <img src="<?= url('uploads/'.$post['featured_image']) ?>" alt="" style="width:100%;border-radius:5px;margin-bottom:10px;border:1px solid var(--border)">
        <?php endif; ?>
        <label class="upload-zone" for="img-upload"><i class="ti ti-upload"></i><?= $post && $post['featured_image'] ? 'Change image' : 'Upload featured image' ?><br><span style="font-size:9px;display:block;margin-top:4px">JPG, PNG, WebP · max 10MB</span></label>
        <input type="file" id="img-upload" name="featured_image" accept="image/*" style="display:none" onchange="previewImg(this)">
        <div id="img-preview"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="ti ti-antenna"></i>SEO</div></div>
      <div class="panel-body">
        <div class="form-group">
          <label class="form-label">Meta Title <span style="color:var(--muted);font-size:9px" id="mt-count">(0/60)</span></label>
          <input class="form-input" name="meta_title" id="meta-title" placeholder="SEO title..." value="<?= e($post['meta_title']??'') ?>" oninput="document.getElementById('mt-count').textContent='('+this.value.length+'/60)'">
        </div>
        <div class="form-group">
          <label class="form-label">Meta Description <span style="color:var(--muted);font-size:9px" id="md-count">(0/160)</span></label>
          <textarea class="form-input" name="meta_desc" style="min-height:70px" placeholder="Description for search results..." oninput="document.getElementById('md-count').textContent='('+this.value.length+'/160)'"><?= e($post['meta_desc']??'') ?></textarea>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Focus Keyword</label>
          <input class="form-input" name="focus_keyword" placeholder="e.g. iptables tutorial" value="<?= e($post['focus_keyword']??'') ?>">
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<script>
function autoSlug(){
  var t=document.getElementById('title-input').value;
  document.getElementById('slug-input').value=t.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
}
function wrapText(before,after){
  var ta=document.getElementById('editor');
  var s=ta.selectionStart,e=ta.selectionEnd;
  var sel=ta.value.substring(s,e)||'text';
  ta.value=ta.value.substring(0,s)+before+sel+after+ta.value.substring(e);
  ta.focus();ta.setSelectionRange(s+before.length,s+before.length+sel.length);
}
function insertLine(prefix){
  var ta=document.getElementById('editor');
  var s=ta.selectionStart;
  var newline=ta.value.charAt(s-1)==='\n'||s===0?'':'\n';
  ta.value=ta.value.substring(0,s)+newline+prefix+ta.value.substring(s);
  ta.focus();ta.setSelectionRange(s+newline.length+prefix.length,s+newline.length+prefix.length);
}
function insertBlock(before,after){
  var ta=document.getElementById('editor');
  var s=ta.selectionStart;
  ta.value=ta.value.substring(0,s)+'\n'+before+'\n'+after+'\n'+ta.value.substring(s);
  ta.focus();ta.setSelectionRange(s+1+before.length+1,s+1+before.length+1);
}
function insertLink(){
  var url=prompt('URL:','https://');
  if(!url)return;
  wrapText('[',']('+url+')');
}
var isFs=false;
function toggleFullscreen(){
  var ta=document.getElementById('editor');
  var btn=document.getElementById('fs-btn');
  if(!isFs){
    ta.style.cssText='position:fixed;inset:0;z-index:9999;border-radius:0;min-height:100vh;font-size:14px;padding:32px;background:var(--bg)';
    btn.innerHTML='<i class="ti ti-minimize"></i>';
  }else{
    ta.style.cssText='';
    btn.innerHTML='<i class="ti ti-maximize"></i>';
  }
  isFs=!isFs;
}
function previewImg(input){
  if(input.files&&input.files[0]){
    var reader=new FileReader();
    reader.onload=function(e){
      document.getElementById('img-preview').innerHTML='<img src="'+e.target.result+'" style="width:100%;border-radius:5px;margin-top:8px;border:1px solid var(--border)">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
// Tag chips
function renderChips(){
  var chips=document.getElementById('tag-chips');
  var tags=document.getElementById('tags-input').value.split(',').map(t=>t.trim()).filter(Boolean);
  chips.innerHTML=tags.map(t=>'<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;background:rgba(0,255,157,.08);color:var(--green);border:1px solid rgba(0,255,157,.2);font-size:9px">'+t+'</span>').join('');
}
document.getElementById('tags-input').addEventListener('input',renderChips);
renderChips();
</script>
<?php include __DIR__ . '/admin_footer.php'; ?>
