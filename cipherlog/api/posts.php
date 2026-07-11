<?php
// api/posts.php — REST API for posts
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $slug   = trim($_GET['slug']     ?? '');
    $cat    = trim($_GET['category'] ?? '');
    $tag    = trim($_GET['tag']      ?? '');
    $search = trim($_GET['q']        ?? '');
    $limit  = min(50, max(1, (int)($_GET['limit']  ?? 10)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));

    if ($slug) {
        $post = getPost($slug);
        if (!$post || $post['status'] !== 'published') {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
            exit;
        }
        echo json_encode([
            'id'           => $post['id'],
            'title'        => $post['title'],
            'slug'         => $post['slug'],
            'excerpt'      => $post['excerpt'],
            'content_html' => markdownToHtml($post['content']),
            'author'       => $post['author_name'],
            'category'     => ['name'=>$post['cat_name'],'slug'=>$post['cat_slug']],
            'tags'         => array_map(fn($t)=>['name'=>$t['name'],'slug'=>$t['slug']], $post['tags']),
            'views'        => $post['views'],
            'reading_time' => $post['reading_time'],
            'published_at' => $post['published_at'],
            'is_featured'  => (bool)$post['is_featured'],
            'url'          => url('post.php?slug='.$post['slug']),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $posts = getPosts(['status'=>'published','category'=>$cat,'tag'=>$tag,'search'=>$search,'limit'=>$limit,'offset'=>$offset]);
    $total = (int)queryOne("SELECT COUNT(*) AS c FROM posts WHERE status='published'", [])['c'];

    echo json_encode([
        'total'  => $total,
        'limit'  => $limit,
        'offset' => $offset,
        'posts'  => array_map(fn($p) => [
            'id'           => $p['id'],
            'title'        => $p['title'],
            'slug'         => $p['slug'],
            'excerpt'      => $p['excerpt'] ?: excerptFromContent($p['content']),
            'author'       => $p['author_name'],
            'category'     => ['name'=>$p['cat_name'],'slug'=>$p['cat_slug']],
            'tags'         => array_column($p['tags'],'name'),
            'views'        => $p['views'],
            'reading_time' => $p['reading_time'],
            'published_at' => $p['published_at'],
            'is_featured'  => (bool)$p['is_featured'],
            'url'          => url('post.php?slug='.$p['slug']),
        ], $posts),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
