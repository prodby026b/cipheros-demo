<?php
/**
 * POST api/upload.php — آپلود امن تصویر
 * ورادی (FormData): file, roomId, csrf_token
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();

require_rate_limit('chat_upload_' . $me, 10, 60);

$roomId = (int)($_POST['roomId'] ?? 0);
if ($roomId <= 0 || !is_room_member($conn, $me, $roomId)) {
    json_response(['ok' => false, 'error' => 'invalid_room'], 400);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_response(['ok' => false, 'error' => 'no_file'], 400);
}

touch_chat_user($conn, $me);

// آپلود امن (MIME check, max 5MB, sanitized filename)
$upload = secure_upload(
    $_FILES['file'],
    __DIR__ . '/../uploads',
    ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    5242880  // 5 MB
);

if (!$upload['ok']) {
    json_response(['ok' => false, 'error' => $upload['error']], 400);
}

// مسیر نسبی برای ذخیره در دیتابیس
$relPath = 'uploads/' . $upload['name'];

$stmt = db_query($conn,
    "INSERT INTO chat_messages (room_id, username, type, file_path) VALUES (?, ?, 'image', ?)",
    'iss', [$roomId, $me, $relPath]);

if (!$stmt) {
    // اگر ذخیره در DB شکست خورد، فایل آپلودی را حذف کن
    @unlink(__DIR__ . '/../' . $relPath);
    json_response(['ok' => false, 'error' => 'db_error'], 500);
}

$id = (int)$conn->insert_id;
json_response(['ok' => true, 'id' => $id, 'path' => $relPath]);
