<?php
// api/rss.php — RSS 2.0 feed
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

$blogName = getSetting('blog_name', 'CipherLog');
$tagline  = getSetting('blog_tagline', 'Linux & Network');
$blogUrl  = BLOG_URL;

$posts = getPosts(['status' => 'published', 'limit' => 20]);

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title><?= htmlspecialchars($blogName) ?></title>
    <link><?= $blogUrl ?></link>
    <description><?= htmlspecialchars($tagline) ?></description>
    <language>en-us</language>
    <lastBuildDate><?= date('r') ?></lastBuildDate>
    <atom:link href="<?= url('api/rss.php') ?>" rel="self" type="application/rss+xml"/>
    <image>
        <url><?= url('assets/img/logo.png') ?></url>
        <title><?= htmlspecialchars($blogName) ?></title>
        <link><?= $blogUrl ?></link>
    </image>
    <?php foreach ($posts as $p): ?>
    <item>
        <title><?= htmlspecialchars($p['title']) ?></title>
        <link><?= url('post.php?slug=' . $p['slug']) ?></link>
        <guid isPermaLink="true"><?= url('post.php?slug=' . $p['slug']) ?></guid>
        <pubDate><?= date('r', strtotime($p['published_at'])) ?></pubDate>
        <description><?= htmlspecialchars($p['excerpt'] ?: excerptFromContent($p['content'])) ?></description>
        <content:encoded><![CDATA[<?= markdownToHtml($p['content']) ?>]]></content:encoded>
        <?php if ($p['cat_name']): ?>
        <category><?= htmlspecialchars($p['cat_name']) ?></category>
        <?php endif; ?>
        <?php foreach ($p['tags'] as $t): ?>
        <category><?= htmlspecialchars($t['name']) ?></category>
        <?php endforeach; ?>
        <author><?= htmlspecialchars(getSetting('admin_email')) ?></author>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
