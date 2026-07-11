<?php
/**
 * Cipher Chat — Shared API bootstrap
 * ------------------------------------------------------------------
 * تمام endpointهای api/*.php این فایل را require می‌کنند.
 * مسئولیت‌ها:
 *   - اتصال دیتابیس (از db.php)
 *   - بررسی احراز هویت
 *   - بررسی CSRF
 *   - upsert کاربر فعلی در جدول chat_users + بروزرسانی online
 *   - تزریق تابع‌های کمکی chat_*
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../db.php';

// فقط درخواست‌های JSON/POST مجازند (محافظت اضافی)
require_auth(true);
require_csrf();

/**
 * نام کاربری فعلی (تضمین‌شده از سشن احراز هویت‌شده)
 */
function chat_user(): string {
    return current_chat_user();
}

/**
 * upsert کاربر در جدول chat_users و علامت‌گذانی آنلاین
 */
function touch_chat_user(mysqli $conn, string $username): array {
    $color = '#' . substr(dechex(crc32($username)), 0, 6);
    $stmt = db_query($conn,
        "INSERT INTO chat_users (username, avatar_color, is_online, last_seen)
         VALUES (?, ?, 1, NOW())
         ON DUPLICATE KEY UPDATE is_online = 1, last_seen = NOW()",
        'ss', [$username, $color]);
    if (!$stmt) return [];

    // اطلاعات رکورد
    $stmt2 = db_query($conn, "SELECT username, display_name, avatar_color FROM chat_users WHERE username = ?", 's', [$username]);
    $row = $stmt2 ? $stmt2->get_result()->fetch_assoc() : null;
    return $row ?: ['username' => $username, 'display_name' => null, 'avatar_color' => $color];
}

/**
 * آیا کاربر عضو این اتاق است؟ (اتاق خصوصی = نیاز به عضویت)
 */
function is_room_member(mysqli $conn, string $username, int $roomId): bool {
    // اتاق عمومی → همه عضو هستند
    $stmt = db_query($conn, "SELECT is_private FROM chat_rooms WHERE id = ?", 'i', [$roomId]);
    $room = $stmt ? $stmt->get_result()->fetch_assoc() : null;
    if (!$room) return false;
    if ((int)$room['is_private'] === 0) return true;

    $stmt2 = db_query($conn, "SELECT id FROM chat_room_members WHERE room_id = ? AND username = ?", 'is', [$roomId, $username]);
    return $stmt2 && $stmt2->get_result()->fetch_assoc() !== null;
}

/**
 * تبدیل رکورد پیام به آرایه خروجی
 */
function serialize_message(mysqli $conn, array $m, string $currentUser): array {
    // واکنش‌ها
    $reactions = [];
    $stmt = db_query($conn, "SELECT emoji, username FROM chat_reactions WHERE message_id = ?", 'i', [$m['id']]);
    if ($stmt) {
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $reactions[$r['emoji']][] = $r['username'];
        }
    }

    // اطلاعات ریپلای
    $reply = null;
    if (!empty($m['reply_to_id'])) {
        $rs = db_query($conn, "SELECT id, username, message FROM chat_messages WHERE id = ? AND deleted_at IS NULL", 'i', [$m['reply_to_id']]);
        if ($rs) {
            $rd = $rs->get_result()->fetch_assoc();
            if ($rd) $reply = ['id' => (int)$rd['id'], 'username' => $rd['username'], 'preview' => mb_substr($rd['message'] ?? '', 0, 60)];
        }
    }

    // تعداد خوانده‌شده‌ها (به‌جز فرستنده)
    $readCount = 0; $readByMe = false;
    $rs = db_query($conn, "SELECT username FROM chat_message_reads WHERE message_id = ?", 'i', [$m['id']]);
    if ($rs) {
        $res = $rs->get_result();
        while ($rd = $res->fetch_assoc()) {
            if ($rd['username'] === $currentUser) $readByMe = true;
            else $readCount++;
        }
    }

    return [
        'id'         => (int)$m['id'],
        'roomId'     => (int)$m['room_id'],
        'username'   => $m['username'],
        'message'    => $m['message'] ?? '',
        'type'       => $m['type'],
        'filePath'   => $m['file_path'] ?? null,
        'replyTo'    => $m['reply_to_id'] !== null ? (int)$m['reply_to_id'] : null,
        'reply'      => $reply,
        'reactions'  => $reactions,
        'readCount'  => $readCount,
        'readByMe'   => $readByMe,
        'mine'       => ($m['username'] === $currentUser),
        'editedAt'   => $m['edited_at'] ?? null,
        'deletedAt'  => $m['deleted_at'] ?? null,
        'createdAt'  => $m['created_at'] ?? null,
    ];
}
