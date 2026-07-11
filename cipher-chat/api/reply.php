<?php
/**
 * POST api/reply.php — ثبت ریپلای هنگام ارسال پیام
 * این endpoint اطلاعات ریپلای را تایید می‌کند (ریپلای واقعی در send.php ذخیره می‌شود)
 * ورودی (JSON): { replyToId } → بازگرداندن preview پیام اصلی
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

$replyToId = (int)($in['replyToId'] ?? 0);
if ($replyToId <= 0) {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}

touch_chat_user($conn, $me);

$stmt = db_query($conn,
    "SELECT id, username, message, created_at FROM chat_messages WHERE id = ? AND deleted_at IS NULL",
    'i', [$replyToId]);
$row = $stmt ? $stmt->get_result()->fetch_assoc() : null;

if (!$row) {
    json_response(['ok' => false, 'error' => 'not_found'], 404);
}

json_response([
    'ok'      => true,
    'id'      => (int)$row['id'],
    'username' => $row['username'],
    'preview' => mb_substr($row['message'] ?? '', 0, 80),
    'time'    => $row['created_at'],
]);
