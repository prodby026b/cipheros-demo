<?php
/**
 * POST api/edit.php — ویرایش پیام (فقط فرستنده)
 * ورودی (JSON): { messageId, message, csrf_token }
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

require_rate_limit('chat_edit_' . $me, 30, 60);

$msgId   = (int)($in['messageId'] ?? 0);
$message = sanitize($in['message'] ?? '', 'html');

if ($msgId <= 0 || $message === '') {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}
if (mb_strlen($message) > 2000) {
    json_response(['ok' => false, 'error' => 'message_too_long'], 400);
}

// مالکیت: فقط فرستنده می‌تواند ویرایش کند
$stmt = db_query($conn, "SELECT username, deleted_at FROM chat_messages WHERE id = ?", 'i', [$msgId]);
$row = $stmt ? $stmt->get_result()->fetch_assoc() : null;
if (!$row) {
    json_response(['ok' => false, 'error' => 'not_found'], 404);
}
if ($row['username'] !== $me) {
    json_response(['ok' => false, 'error' => 'forbidden'], 403);
}
if ($row['deleted_at'] !== null) {
    json_response(['ok' => false, 'error' => 'deleted'], 400);
}

touch_chat_user($conn, $me);

$stmt = db_query($conn,
    "UPDATE chat_messages SET message = ?, edited_at = NOW() WHERE id = ?",
    'si', [$message, $msgId]);

if (!$stmt) {
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

json_response(['ok' => true]);
