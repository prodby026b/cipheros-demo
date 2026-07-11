<?php
/**
 * POST api/search.php — جستجو در پیام‌های اتاق فعلی
 * ورودی (JSON): { roomId, query, csrf_token }
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

require_rate_limit('chat_search_' . $me, 20, 30);

$roomId = (int)($in['roomId'] ?? 0);
$query  = trim($in['query'] ?? '');

if ($roomId <= 0 || mb_strlen($query) < 2) {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}

touch_chat_user($conn, $me);

// جستجو با LIKE (حداکثر ۵۰ نتیجه)
$stmt = db_query($conn,
    "SELECT m.* FROM chat_messages m
     WHERE m.room_id = ? AND m.message LIKE ? AND m.deleted_at IS NULL
     ORDER BY m.id DESC LIMIT 50",
    'is', [$roomId, '%' . $query . '%']);

$results = [];
if ($stmt) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = serialize_message($conn, $row, $me);
    }
}

json_response(['ok' => true, 'results' => $results]);
