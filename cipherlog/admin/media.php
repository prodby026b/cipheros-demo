<?php
// admin/media.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
authStart(); requireLogin();

$user = getCurrentUser();
$msg  = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['files'])) {
    $uploaded = 0;
    $files    = $_FILES['files'];
    $count    = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $count; $i++) {
        $file = [
            'name'     => is_array($files['name'])     ? $files['name'][$i]     : $files['name'],
            'type'     => is_array($files['type'])     ? $files['type'][$i]     : $files['type'],
            'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
            'size'     => is_array($files['size'])     ? $files['size'][$i]     : $files['size'],
            'error'    => is_array($files['error'])    ? $files['error'][$i]    : $files['error'],
        ];
        if ($file['error'] !== UPLOAD_ERR_OK) continue;
        $info = uploadFile($file, UPLOADS_DIR);
        if ($info) {
            execute(
                "INSERT INTO media (filename,original_name,mime_type,size,width,height,uploader_id) VALUES (?,?,?,?,?,?,?)",
                [$info['filename'],$info['original_name'],$info['mime_type'],$info['size'],$info['width']??null,$info['height']??null,$user['id']]
            );
            $uploaded++;
        }
    }
    $msg = "success:$uploaded";
}

// Handle delete
if ($_GET['action']??'' === 'delete' && isset($_GET['id'])) {
    $media = queryOne("SELECT * FROM media WHERE id=?", [(int)$_GET['id']]);
    if ($media) {
        $path = UPLOADS_DIR . $media['filename'];
        if (file_exists($path)) unlink($path);
        execute("DELETE FROM media WHERE id=?", [(int)$_GET['id']]);
    }
    redirect(url('admin/media.php?msg=deleted'));
}

// Update alt text
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_alt'])) {
    execute("UPDATE media SET alt_text=?,caption=? WHERE id=?", [
        trim($_POST['alt_text'] ?? ''),
        trim($_POST['caption']  ?? ''),
        (int)$_POST['media_id']
    ]);
    redirect(url('admin/media.php?msg=updated'));
}

$filter = $_GET['type'] ?? 'all';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 24; $offset = ($page-1)*$per;

$where  = $filter === 'image' ? "WHERE mime_type LIKE 'image/%'" : ($filter === 'other' ? "WHERE mime_type NOT LIKE 'image/%'" : '');
$media  = query("SELECT * FROM media $where ORDER BY created_at DESC LIMIT ? OFFSET ?", [$per, $offset]);
$total  = (int)queryOne("SELECT COUNT(*) AS c FROM media $where", [])['c'];
$pages  = ceil($total / $per);
$imgTotal   = (int)queryOne("SELECT COUNT(*) AS c FROM media WHERE mime_type LIKE 'image/%'", [])['c'];
$otherTotal = (int)queryOne("SELECT COUNT(*) AS c FROM media WHERE mime_type NOT LIKE 'image/%'", [])['c'];

function humanSize(int $bytes): string {
    if ($bytes < 1024) return $bytes.'B';
    if ($bytes < 1048576) return round($bytes/1024,1).'KB';
    return round($bytes/1048576,1).'MB';
}

$pageTitle = 'Media Library'; $activePage = 'media';
include __DIR__ . '/admin_header.php';
?>

<?php if (str_starts_with($_GET['msg']??'','success:')): ?>
<div class="alert alert-success"><i class="ti ti-check"></i><?= (int)explode(':',$_GET['msg'])[1] ?> file(s) uploaded successfully.</div>
<?php elseif ($_GET['msg']??'' === 'deleted'): ?>
<div class="alert alert-success"><i class="ti ti-check"></i>File deleted.</div>
<?php elseif ($_GET['msg']??'' === 'updated'): ?>
<div class="alert alert-success"><i class="ti ti-check"></i>Media updated.</div>
<?php endif; ?>

<!-- Upload zone -->
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="ti ti-upload"></i>Upload Files</div></div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data" id="upload-form">
            <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
            <label class="upload-zone" for="file-input" id="drop-zone">
                <i class="ti ti-cloud-upload"></i>
                Drag & drop files here, or click to browse<br>
                <span style="font-size:9px;display:block;margin-top:6px;color:var(--muted)">PNG, JPG, WebP, SVG, PDF, SH, TAR.GZ · Max 10MB per file · Multiple files allowed</span>
            </label>
            <input type="file" id="file-input" name="files[]" multiple accept="image/*,.pdf,.sh,.py,.txt,.tar.gz,.zip,.svg" style="display:none" onchange="previewFiles(this)">
            <div id="preview-list" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px"></div>
            <button class="btn btn-primary" type="submit" id="upload-btn" style="display:none;margin-top:12px"><i class="ti ti-upload"></i>UPLOAD FILES</button>
        </form>
    </div>
</div>

<!-- Filter -->
<div class="filter-bar">
    <a class="filter-btn <?= $filter==='all'?'active':'' ?>" href="?type=all">All (<?= $total ?>)</a>
    <a class="filter-btn <?= $filter==='image'?'active':'' ?>" href="?type=image">Images (<?= $imgTotal ?>)</a>
    <a class="filter-btn <?= $filter==='other'?'active':'' ?>" href="?type=other">Files (<?= $otherTotal ?>)</a>
</div>

<!-- Media grid -->
<?php if (empty($media)): ?>
<div style="text-align:center;padding:60px;color:var(--muted)"><i class="ti ti-photo-off" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3"></i>No files yet.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:16px">
    <?php foreach ($media as $m):
        $isImg = str_starts_with($m['mime_type'], 'image/');
        $ext   = strtolower(pathinfo($m['filename'], PATHINFO_EXTENSION));
        $iconMap = ['pdf'=>'ti-file-text','sh'=>'ti-terminal','py'=>'ti-brand-python','zip'=>'ti-file-zip','gz'=>'ti-file-zip','txt'=>'ti-file'];
        $icon = $iconMap[$ext] ?? 'ti-file';
    ?>
    <div style="border-radius:6px;border:1px solid var(--border);background:var(--card2);overflow:hidden;cursor:pointer;transition:border-color .15s" onmouseover="this.style.borderColor='var(--green)'" onmouseout="this.style.borderColor='var(--border)'" onclick="openMediaModal(<?= $m['id'] ?>, '<?= e($m['filename']) ?>', '<?= e($m['original_name']) ?>', '<?= e($m['mime_type']) ?>', <?= $m['size'] ?>, <?= $m['width']??0 ?>, <?= $m['height']??0 ?>, '<?= e($m['alt_text']??'') ?>', '<?= e($m['caption']??'') ?>')">
        <div style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:<?= $isImg?'transparent':'var(--bg3)' ?>;overflow:hidden">
            <?php if ($isImg): ?>
            <img src="<?= url('uploads/'.$m['filename']) ?>" alt="<?= e($m['alt_text']??$m['original_name']) ?>" style="width:100%;height:100%;object-fit:cover" loading="lazy">
            <?php else: ?>
            <i class="ti <?= $icon ?>" style="font-size:32px;color:var(--muted);opacity:.5"></i>
            <?php endif; ?>
        </div>
        <div style="padding:7px 8px">
            <div style="font-size:9px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($m['original_name']) ?></div>
            <div style="font-size:8px;color:var(--muted);margin-top:2px"><?= humanSize($m['size']) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
<div class="pagination" style="display:flex;gap:5px;justify-content:center;margin-bottom:24px">
    <?php for($i=1;$i<=$pages;$i++): ?>
    <a class="filter-btn <?= $i==$page?'active':'' ?>" href="?type=<?= $filter ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Media detail modal -->
<div id="media-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:500;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
    <div style="background:var(--card);border:1px solid var(--border);border-radius:10px;width:700px;max-width:94vw;max-height:90vh;overflow:hidden;display:flex;flex-direction:column">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
            <div style="font-size:11px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:7px"><i class="ti ti-photo" style="color:var(--green)"></i>Media Details</div>
            <button onclick="closeMediaModal()" style="width:28px;height:28px;border-radius:5px;border:1px solid var(--border);background:transparent;color:var(--muted);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .15s" onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'"><i class="ti ti-x"></i></button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;flex:1;overflow:hidden">
            <div style="background:var(--bg3);display:flex;align-items:center;justify-content:center;padding:16px;overflow:hidden">
                <img id="modal-img" src="" alt="" style="max-width:100%;max-height:300px;border-radius:5px;object-fit:contain;display:none">
                <i id="modal-icon" class="ti ti-file" style="font-size:56px;color:var(--muted);opacity:.4;display:none"></i>
            </div>
            <div style="padding:18px;overflow-y:auto">
                <div style="margin-bottom:14px">
                    <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:6px">FILE INFO</div>
                    <div style="font-size:11px;color:var(--text);margin-bottom:4px" id="modal-name"></div>
                    <div style="font-size:10px;color:var(--muted)" id="modal-meta"></div>
                </div>
                <div style="margin-bottom:12px">
                    <div style="font-size:9px;color:var(--muted);letter-spacing:1px;margin-bottom:6px">FILE URL</div>
                    <div style="display:flex;gap:6px">
                        <input id="modal-url" class="form-input" style="font-size:10px" readonly>
                        <button class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('modal-url').value).then(()=>showToast('URL copied!','success'))"><i class="ti ti-copy"></i></button>
                    </div>
                </div>
                <form method="POST" id="modal-form">
                    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                    <input type="hidden" name="update_alt" value="1">
                    <input type="hidden" name="media_id" id="modal-id">
                    <div class="form-group">
                        <label class="form-label">Alt Text</label>
                        <input class="form-input" name="alt_text" id="modal-alt" placeholder="Describe the image...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Caption</label>
                        <textarea class="form-input" name="caption" id="modal-caption" style="min-height:60px" placeholder="Optional caption..."></textarea>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="ti ti-check"></i>SAVE</button>
                        <button class="btn btn-danger btn-sm" type="button" onclick="deleteMedia()"><i class="ti ti-trash"></i>DELETE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Upload drag & drop
const dz = document.getElementById('drop-zone');
['dragenter','dragover'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.style.borderColor='var(--green)';}));
['dragleave','drop'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.style.borderColor='var(--border2)';}));
dz.addEventListener('drop',ev=>{
    ev.preventDefault();
    const input=document.getElementById('file-input');
    const dt=ev.dataTransfer;
    if(dt.files.length){
        input.files=dt.files;
        previewFiles(input);
    }
});
function previewFiles(input){
    const list=document.getElementById('preview-list');
    const btn=document.getElementById('upload-btn');
    list.innerHTML='';
    if(!input.files.length)return;
    Array.from(input.files).forEach(f=>{
        const div=document.createElement('div');
        div.style.cssText='display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:5px;border:1px solid var(--border);background:var(--card2);font-size:10px';
        div.innerHTML='<i class="ti ti-file" style="color:var(--muted)"></i><span style="color:var(--text)">'+f.name+'</span><span style="color:var(--muted);margin-left:auto">'+humanSize(f.size)+'</span>';
        list.appendChild(div);
    });
    btn.style.display='flex';
}
function humanSize(b){if(b<1024)return b+'B';if(b<1048576)return Math.round(b/1024)+'KB';return (b/1048576).toFixed(1)+'MB';}

let currentMediaId=null;
function openMediaModal(id,fn,orig,mime,size,w,h,alt,caption){
    currentMediaId=id;
    document.getElementById('modal-id').value=id;
    document.getElementById('modal-name').textContent=orig;
    document.getElementById('modal-meta').textContent=humanSize(size)+(w?` · ${w}×${h}px`:'')+' · '+mime;
    document.getElementById('modal-url').value='<?= url('uploads/') ?>'+fn;
    document.getElementById('modal-alt').value=alt;
    document.getElementById('modal-caption').value=caption;
    const img=document.getElementById('modal-img');
    const ico=document.getElementById('modal-icon');
    if(mime.startsWith('image/')){img.src='<?= url('uploads/') ?>'+fn;img.style.display='block';ico.style.display='none';}
    else{img.style.display='none';ico.style.display='block';}
    document.getElementById('media-modal').style.display='flex';
}
function closeMediaModal(){document.getElementById('media-modal').style.display='none';}
function deleteMedia(){
    if(!currentMediaId)return;
    if(confirm('Delete this file permanently?'))window.location='?action=delete&id='+currentMediaId;
}
document.getElementById('media-modal').addEventListener('click',function(e){if(e.target===this)closeMediaModal();});
</script>
<?php include __DIR__ . '/admin_footer.php'; ?>
