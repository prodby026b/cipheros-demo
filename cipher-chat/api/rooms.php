<?php
/**
 * POST api/rooms.php — مدیریت اتاق‌ها
 *   بدون body یا GET  → لیست اتاق‌ها
 *   JSON {name, description?, isPrivate?} → ایجاد اتاق جدید
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// --- لیست اتاق‌ها ---
if ($method === 'GET' || $method === '') {
    $rooms = [];
    $st = db_query($conn,
        "SELECT r.id, r.name, r.slug, r.description, r.is_private, r.created_by,
                (SELECT COUNT(*) FROM chat_room_members m WHERE m.room_id = r.id) AS members
         FROM chat_rooms r ORDER BY r.id ASC");
    if ($st) {
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) {
            // برای اتاق خصوصی فقط اعضا آن را می‌بینند
            if ((int)$row['is_private'] === 1 && $row['created_by'] !== $me && !is_room_member($conn, $me, (int)$row['id'])) {
                continue;
            }
            $rooms[] = $row;
        }
    }
    json_response(['ok' => true, 'rooms' => $rooms]);
}

// --- ایجاد اتاق ---
touch_chat_user($conn, $me);

require_rate_limit('chat_room_' . $me, 10, 60);

$in         = json_input();
$name       = sanitize($in['name'] ?? '', 'html');
$desc       = sanitize($in['description'] ?? '', 'html');
$isPrivate  = !empty($in['isPrivate']) ? 1 : 0;

if ($name === '' || mb_strlen($name) > 64) {
    json_response(['ok' => false, 'error' => 'invalid_name'], 400);
}

// ساخت slug یکتا
$slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($name));
$slug = trim($slug, '-') . '-' . substr(md5(uniqid('', true)), 0, 4);

$stmt = db_query($conn,
    "INSERT INTO chat_rooms (name, slug, description, created_by, is_private) VALUES (?, ?, ?, ?, ?)",
    'ssssi', [$name, $slug, $desc, $me, $isPrivate]);

if (!$stmt) {
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

$roomId = (int)$conn->insert_id;

// ایجاد‌کننده به‌عنوان admin عضو می‌شود
@db_query($conn, "INSERT INTO chat_room_members (room_id, username, role) VALUES (?, ?, 'admin')", 'is', [$roomId, $me]);

json_response(['ok' => true, 'id' => $roomId, 'slug' => $slug]);
