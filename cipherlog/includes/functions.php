<?php
// includes/functions.php — Core helpers

function slug(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string {
    return rtrim(BLOG_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function readingTime(string $content): int {
    $words = str_word_count(strip_tags($content));
    return max(1, (int) ceil($words / 200));
}

function excerptFromContent(string $content, int $length = 160): string {
    $text = strip_tags($content);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

function uniqueSlug(string $base, string $table, int $excludeId = 0): string {
    $slug = slug($base);
    $original = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug=?";
        $params = [$slug];
        if ($excludeId) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        if (!queryOne($sql, $params)) break;
        $slug = $original . '-' . $i++;
    }
    return $slug;
}

function formatDate(string $date, string $format = 'M j, Y'): string {
    return date($format, strtotime($date));
}

function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60) . ' min ago';
    if ($diff < 86400)  return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return formatDate($date);
}

function getPosts(array $opts = []): array {
    $status   = $opts['status']   ?? 'published';
    $limit    = $opts['limit']    ?? 10;
    $offset   = $opts['offset']   ?? 0;
    $cat      = $opts['category'] ?? null;
    $tag      = $opts['tag']      ?? null;
    $search   = $opts['search']   ?? null;
    $featured = $opts['featured'] ?? null;

    $where = ["p.status = ?"];
    $params = [$status];

    if ($cat) {
        $where[] = "c.slug = ?";
        $params[] = $cat;
    }
    if ($featured !== null) {
        $where[] = "p.is_featured = ?";
        $params[] = $featured ? 1 : 0;
    }
    if ($search) {
        $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)";
        $s = "%$search%";
        array_push($params, $s, $s, $s);
    }
    if ($tag) {
        $where[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON pt.tag_id=t.id WHERE pt.post_id=p.id AND t.slug=?)";
        $params[] = $tag;
    }

    $whereStr = 'WHERE ' . implode(' AND ', $where);
    $sql = "SELECT p.*, u.display_name AS author_name, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            $whereStr
            ORDER BY p.published_at DESC
            LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $posts = query($sql, $params);

    // Attach tags to each post
    foreach ($posts as &$post) {
        $post['tags'] = query(
            "SELECT t.* FROM tags t JOIN post_tags pt ON t.id=pt.tag_id WHERE pt.post_id=?",
            [$post['id']]
        );
    }
    return $posts;
}

function getPost(string $slug): ?array {
    $post = queryOne(
        "SELECT p.*, u.display_name AS author_name, u.bio AS author_bio, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color
         FROM posts p
         LEFT JOIN users u ON p.author_id=u.id
         LEFT JOIN categories c ON p.category_id=c.id
         WHERE p.slug=? LIMIT 1",
        [$slug]
    );
    if (!$post) return null;
    $post['tags'] = query("SELECT t.* FROM tags t JOIN post_tags pt ON t.id=pt.tag_id WHERE pt.post_id=?", [$post['id']]);
    return $post;
}

function incrementViews(int $postId): void {
    execute("UPDATE posts SET views = views + 1 WHERE id=?", [$postId]);
}

function getComments(int $postId): array {
    return query(
        "SELECT * FROM comments WHERE post_id=? AND status='approved' AND parent_id IS NULL ORDER BY created_at ASC",
        [$postId]
    );
}

function markdownToHtml(string $md): string {
    // Headings
    $md = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $md);
    $md = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $md);
    $md = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $md);
    // Bold / italic
    $md = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $md);
    $md = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $md);
    // Inline code
    $md = preg_replace('/`([^`]+)`/', '<code>$1</code>', $md);
    // Code blocks
    $md = preg_replace_callback('/```(\w*)\n(.*?)```/s', function($m) {
        $lang = $m[1] ?: 'bash';
        $code = htmlspecialchars($m[2]);
        return "<div class=\"code-block\"><div class=\"cb-header\"><div class=\"cb-dots\"><span style=\"background:#ff5f56\"></span><span style=\"background:#ffbd2e\"></span><span style=\"background:#27c93f\"></span></div><span class=\"cb-lang\">".strtoupper($lang)."</span><button class=\"cb-copy\" onclick=\"copyCode(this)\">COPY</button></div><pre><code>$code</code></pre></div>";
    }, $md);
    // Blockquote
    $md = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $md);
    // Unordered list
    $md = preg_replace_callback('/(?:^- .+\n?)+/m', function($m) {
        $items = array_filter(explode("\n", $m[0]));
        $li = array_map(fn($i) => '<li>' . ltrim($i, '- ') . '</li>', $items);
        return '<ul>' . implode('', $li) . '</ul>';
    }, $md);
    // Links
    $md = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $md);
    // Paragraphs
    $md = preg_replace('/\n\n+/', '</p><p>', $md);
    $md = '<p>' . $md . '</p>';
    $md = preg_replace('/<p>(<h[1-6]>|<ul>|<ol>|<blockquote>|<div class="code-block">)/', '$1', $md);
    $md = preg_replace('/(<\/h[1-6]>|<\/ul>|<\/ol>|<\/blockquote>|<\/div>)<\/p>/', '$1', $md);
    return $md;
}

function jsonResponse(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch');
    }
}

function uploadFile(array $file, string $dest): ?array {
    $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml',
                'text/plain','application/pdf','application/zip',
                'application/x-tar','application/gzip'];
    $allowedExt = ['jpg','jpeg','png','gif','webp','svg','txt','pdf','zip','tar','gz'];

    if ($file['size'] > 10 * 1024 * 1024) return null; // 10MB max

    // Validate extension
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) return null;

    // Validate real MIME type with finfo (not client-supplied)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, $allowed)) return null;

    // Extra check: for images, verify with getimagesize
    if (strpos($realMime, 'image/') === 0) {
        if (!@getimagesize($file['tmp_name'])) return null;
    }

    $name = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = rtrim($dest, '/') . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) return null;

    $info = ['filename' => $name, 'original_name' => $file['name'], 'mime_type' => $realMime, 'size' => $file['size']];
    if (strpos($realMime, 'image/') === 0) {
        $dim = @getimagesize($path);
        if ($dim) { $info['width'] = $dim[0]; $info['height'] = $dim[1]; }
    }
    return $info;
}
