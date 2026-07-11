<?php
/**
 * POST api/send.php — ارسال پیام متنی
 * ورودی (JSON): { roomId, message, replyTo?, csrf_token }
 */
require_once __DIR__ . '/_bootstrap.php';

$me     = chat_user();
$input  = json_input();

// --- Rate limit: حداکثر ۳۰ پیام در ۶۰ ثانیه ---
require_rate_limit('chat_send_' . $me, 30, 60);

$roomId   = (int)($input['roomId'] ?? 0);
$message  = sanitize($input['message'] ?? '', 'html');
$replyTo  = isset($input['replyTo']) ? (int)$input['replyTo'] : null;

if ($roomId <= 0 || $message === '') {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}

// بررسی عضویت در اتاق
if (!is_room_member($conn, $me, $roomId)) {
    json_response(['ok' => false, 'error' => 'not_member'], 403);
}

// حداکثر طول پیام
if (mb_strlen($message) > 2000) {
    json_response(['ok' => false, 'error' => 'message_too_long'], 400);
}

touch_chat_user($conn, $me);

$stmt = db_query($conn,
    "INSERT INTO chat_messages (room_id, username, message, type, reply_to_id)
     VALUES (?, ?, ?, 'text', ?)",
    'issi',
    [$roomId, $me, $message, $replyTo]
);

if (!$stmt) {
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

$id = $conn->insert_id;
json_response(['ok' => true, 'id' => (int)$id]);
