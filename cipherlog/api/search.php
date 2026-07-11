<?php
// api/search.php — JSON search API
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . BLOG_URL);

$q     = trim($_GET['q']    ?? '');
$limit = min(20, max(1, (int)($_GET['limit'] ?? 6)));
$tag   = trim($_GET['tag']  ?? '');
$cat   = trim($_GET['cat']  ?? '');

if (!$q && !$tag && !$cat) {
    echo json_encode([]);
    exit;
}

$posts = getPosts([
    'status'   => 'published',
    'search'   => $q,
    'tag'      => $tag,
    'category' => $cat,
    'limit'    => $limit,
]);

$result = array_map(fn($p) => [
    'id'           => $p['id'],
    'title'        => $p['title'],
    'slug'         => $p['slug'],
    'excerpt'      => $p['excerpt'] ?: excerptFromContent($p['content'], 120),
    'cat_name'     => $p['cat_name'],
    'cat_slug'     => $p['cat_slug'],
    'reading_time' => $p['reading_time'],
    'views'        => $p['views'],
    'published_at' => $p['published_at'],
    'url'          => url('post.php?slug=' . $p['slug']),
    'tags'         => array_column($p['tags'], 'name'),
], $posts);

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
