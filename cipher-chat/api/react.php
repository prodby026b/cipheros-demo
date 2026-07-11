<?php
/**
 * POST api/react.php — افزودن/حذف واکنش (toggle)
 * ورودی (JSON): { messageId, emoji, csrf_token }
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$in = json_input();

require_rate_limit('chat_react_' . $me, 60, 60);

$msgId = (int)($in['messageId'] ?? 0);
// فقط ایموجی‌های مجاز؛ محدود به ۱۶ کاراکتر و پاکسازی
$emoji = trim($in['emoji'] ?? '');
$emoji = mb_substr($emoji, 0, 16);

if ($msgId <= 0 || $emoji === '') {
    json_response(['ok' => false, 'error' => 'invalid_input'], 400);
}

touch_chat_user($conn, $me);

// تلاش برای حذف اول (toggle)
$stmt = db_query($conn,
    "DELETE FROM chat_reactions WHERE message_id = ? AND username = ? AND emoji = ?",
    'iss', [$msgId, $me, $emoji]);
if (!$stmt) {
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

$removed = ($conn->affected_rows > 0);

if (!$removed) {
    // اگر حذف نشد یعنی قبلاً نبوده → اضافه کن
    $stmt = db_query($conn,
        "INSERT IGNORE INTO chat_reactions (message_id, username, emoji) VALUES (?, ?, ?)",
        'iss', [$msgId, $me, $emoji]);
    if (!$stmt) {
        json_response(['ok' => false, 'error' => 'db_error'], 500);
    }
}

// بازگرداندن واکنش‌های به‌روزرسانی‌شده‌ی این پیام
$reactions = [];
$st = db_query($conn, "SELECT emoji, username FROM chat_reactions WHERE message_id = ?", 'i', [$msgId]);
if ($st) {
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) $reactions[$r['emoji']][] = $r['username'];
}

json_response(['ok' => true, 'action' => $removed ? 'removed' : 'added', 'reactions' => $reactions]);
