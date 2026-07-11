<?php
/**
 * POST api/delete.php — حذف نرم پیام (فقط فرستنده)
 * ورودی (JSON): { messageId, csrf_token }
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

require_rate_limit('chat_delete_' . $me, 30, 60);

$msgId = (int)($in['messageId'] ?? 0);
if ($msgId <= 0) {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}

// مالکیت: فقط فرستنده می‌تواند حذف کند
$stmt = db_query($conn, "SELECT username FROM chat_messages WHERE id = ?", 'i', [$msgId]);
$row = $stmt ? $stmt->get_result()->fetch_assoc() : null;
if (!$row) {
    json_response(['ok' => false, 'error' => 'not_found'], 404);
}
if ($row['username'] !== $me) {
    json_response(['ok' => false, 'error' => 'forbidden'], 403);
}

touch_chat_user($conn, $me);

$stmt = db_query($conn, "UPDATE chat_messages SET deleted_at = NOW(), message = NULL WHERE id = ?", 'i', [$msgId]);
if (!$stmt) {
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

json_response(['ok' => true]);
