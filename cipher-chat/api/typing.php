<?php
/**
 * POST api/typing.php — ثبت وضعیت "در حال تایپ"
 * ورودی (JSON): { roomId }
 * تایمر ۵ ثانیه‌ای: اگر ۵ ثانیه بدون بروزرسانی بگذرد، از لیست تایپ‌کنندگان حذف می‌شود.
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

$roomId = (int)($in['roomId'] ?? 0);
if ($roomId <= 0) {
    json_response(['ok' => false, 'error' => 'invalid_room'], 400);
}

require_rate_limit('chat_typing_' . $me, 30, 10);

touch_chat_user($conn, $me);

// upsert وضعیت تایپ
db_query($conn,
    "INSERT INTO chat_typing (room_id, username, last_typed_at) VALUES (?, ?, NOW())
     ON DUPLICATE KEY UPDATE last_typed_at = NOW()",
    'is', [$roomId, $me]);

json_response(['ok' => true]);
