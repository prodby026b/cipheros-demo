<?php
/**
 * GET/POST api/fetch.php — دریافت پیام‌های جدید از یک اتاق
 * ورودی: { roomId, lastId }   (فقط پیام‌های با id > lastId بازگردانده می‌شوند)
 *        + همچنین لیست اتاق‌ها، کاربران آنلاین، وضعیت تایپ
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();

// ورودی از GET یا POST JSON
$roomId = isset($_GET['roomId']) ? (int)$_GET['roomId'] : 0;
$lastId = isset($_GET['lastId']) ? (int)$_GET['lastId'] : 0;
if ($roomId === 0) {
    $in = json_input();
    $roomId = (int)($in['roomId'] ?? 0);
    $lastId = (int)($in['lastId'] ?? 0);
}

if ($roomId <= 0) {
    json_response(['ok' => false, 'error' => 'invalid_room'], 400);
}

touch_chat_user($conn, $me);

// --- پیام‌های جدید ---
$messages = [];
$stmt = db_query($conn,
    "SELECT m.* FROM chat_messages m
     WHERE m.room_id = ? AND m.id > ?
     ORDER BY m.id ASC LIMIT 200",
    'ii', [$roomId, $lastId]);
if ($stmt) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $messages[] = serialize_message($conn, $row, $me);
        // ثبت خوانده‌شده بودن پیام‌های دیگران
        if ($row['username'] !== $me && (int)$row['id'] > 0) {
            @db_query($conn,
                "INSERT IGNORE INTO chat_message_reads (message_id, username) VALUES (?, ?)",
                'is', [(int)$row['id'], $me]);
        }
    }
}

// --- کاربران آنلاین (در ۹۰ ثانیه اخیر فعال) ---
$online = [];
$st = db_query($conn,
    "SELECT username, avatar_color FROM chat_users WHERE is_online = 1 AND last_seen > (NOW() - INTERVAL 90 SECOND)");
if ($st) {
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $online[] = $row;
}

// --- وضعیت تایپ (در ۵ ثانیه اخیر، به‌جز خودم) ---
$typing = [];
$st = db_query($conn,
    "SELECT username FROM chat_typing
     WHERE room_id = ? AND username <> ? AND last_typed_at > (NOW() - INTERVAL 5 SECOND)",
    'is', [$roomId, $me]);
if ($st) {
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $typing[] = $row['username'];
}

// --- اتاق‌ها ---
$rooms = [];
$st = db_query($conn, "SELECT id, name, slug, description, is_private FROM chat_rooms ORDER BY id ASC");
if ($st) {
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $rooms[] = $row;
}

json_response([
    'ok'       => true,
    'messages' => $messages,
    'online'   => $online,
    'typing'   => $typing,
    'rooms'    => $rooms,
    'me'       => $me,
    'serverTs' => time(),
]);
