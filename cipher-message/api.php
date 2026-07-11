<?php
// cipher-message/api.php
header('Content-Type: application/json');
require_once 'config.php';
session_start();

// شبیه‌سازی کاربر لاگین شده در CipherOS (در پروژه واقعی این بخش از سشن سیستم شما خوانده می‌شود)
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'User_' . rand(100, 999);
}
$currentUser = $_SESSION['username'];

// بروزرسانی وضعیت آنلاین بودن کاربر با هر درخواست (Heartbeat)
updateHeartbeat($pdo, $currentUser);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    // ۱. دریافت پیام‌های یک کانال مشخص به‌همراه ری‌آکشن‌ها و ریپلای‌ها
    case 'fetch_messages':
        $channel_slug = isset($_GET['channel']) ? $_GET['channel'] : 'general';
        
        $query = "
            SELECT m.*, r.username AS replied_to_user, r.message AS replied_to_msg,
                   (SELECT GROUP_CONCAT(CONCAT(emoji, ':', username)) FROM reactions WHERE message_id = m.id) as emoji_reactions
            FROM messages m
            JOIN channels c ON m.channel_id = c.id
            LEFT JOIN messages r ON m.reply_to = r.id
            WHERE c.slug = ? AND m.is_deleted = 0
            ORDER BY m.id ASC LIMIT 100
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$channel_slug]);
        $messages = $stmt->fetchAll();
        
        // بهینه‌سازی فرمت ری‌آکشن‌ها برای فرانت‌آند
        foreach ($messages as &$msg) {
            $msg['reactions'] = [];
            if ($msg['emoji_reactions']) {
                $parts = explode(',', $msg['emoji_reactions']);
                foreach ($parts as $part) {
                    list($emoji, $user) = explode(':', $part);
                    $msg['reactions'][$emoji][] = $user;
                }
            }
            unset($msg['emoji_reactions']);
        }
        
        echo json_encode(['status' => 'success', 'data' => $messages]);
        break;

    // ۲. ارسال پیام جدید (پشتیبانی از ریپلای)
    case 'send_message':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $channel_slug = isset($_POST['channel']) ? $_POST['channel'] : 'general';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            $reply_to = !empty($_POST['reply_to']) ? (int)$_POST['reply_to'] : null;
            
            if ($message === '') {
                echo json_encode(['status' => 'error', 'message' => 'پیام نمی‌تواند خالی باشد']);
                exit;
            }
            
            // پیدا کردن ID کانال بر اساس Slug
            $chStmt = $pdo->prepare("SELECT id FROM channels WHERE slug = ?");
            $chStmt->execute([$channel_slug]);
            $channel = $chStmt->fetch();
            
            if ($channel) {
                $stmt = $pdo->prepare("INSERT INTO messages (channel_id, username, message, reply_to) VALUES (?, ?, ?, ?)");
                $stmt->execute([$channel['id'], $currentUser, $message, $reply_to]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'کانال یافت نشد']);
            }
        }
        break;

    // ۳. ویرایش پیام خود کاربر
    case 'edit_message':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message_id = (int)$_POST['message_id'];
            $new_message = trim($_POST['message']);
            
            $stmt = $pdo->prepare("UPDATE messages SET message = ?, is_edited = 1 WHERE id = ? AND username = ?");
            $stmt->execute([$new_message, $message_id, $currentUser]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'مجاز به ویرایش این پیام نیستید']);
            }
        }
        break;

    // ۴. حذف منطقی (Soft Delete) پیام خود کاربر
    case 'delete_message':
        $message_id = (int)$_GET['message_id'];
        
        $stmt = $pdo->prepare("UPDATE messages SET is_deleted = 1 WHERE id = ? AND username = ?");
        $stmt->execute([$message_id, $currentUser]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'حذف ناموفق بود']);
        }
        break;

    // ۵. پین یا آن‌پین کردن پیام‌ها
    case 'toggle_pin':
        $message_id = (int)$_GET['message_id'];
        
        // ابتدا وضعیت فعلی پین را می‌سنجیم
        $stmt = $pdo->prepare("SELECT is_pinned FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $msg = $stmt->fetch();
        
        if ($msg) {
            $new_pin_status = $msg['is_pinned'] ? 0 : 1;
            $update = $pdo->prepare("UPDATE messages SET is_pinned = ? WHERE id = ?");
            $update->execute([$new_pin_status, $message_id]);
            echo json_encode(['status' => 'success', 'is_pinned' => $new_pin_status]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'پیام یافت نشد']);
        }
        break;

    // ۶. مدیریت ری‌آکشن‌های ایموجی (اگر قبلاً زده بود، حذف می‌شود - Toggle)
    case 'toggle_reaction':
        $message_id = (int)$_GET['message_id'];
        $emoji = $_GET['emoji'];
        
        // بررسی وجود ری‌آکشن قبلی
        $check = $pdo->prepare("SELECT id FROM reactions WHERE message_id = ? AND username = ? AND emoji = ?");
        $check->execute([$message_id, $currentUser, $emoji]);
        
        if ($check->fetch()) {
            $delete = $pdo->prepare("DELETE FROM reactions WHERE message_id = ? AND username = ? AND emoji = ?");
            $delete->execute([$message_id, $currentUser, $emoji]);
            echo json_encode(['status' => 'success', 'type' => 'removed']);
        } else {
            try {
                $insert = $pdo->prepare("INSERT INTO reactions (message_id, username, emoji) VALUES (?, ?, ?)");
                $insert->execute([$message_id, $currentUser, $emoji]);
                echo json_encode(['status' => 'success', 'type' => 'added']);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error']);
            }
        }
        break;

    // ۷. موتور جستجوی پیشرفته درون کانال
    case 'search_messages':
        $channel_slug = isset($_GET['channel']) ? $_GET['channel'] : 'general';
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        
        $query = "
            SELECT m.* FROM messages m
            JOIN channels c ON m.channel_id = c.id
            WHERE c.slug = ? AND m.message LIKE ? AND m.is_deleted = 0
            ORDER BY m.id DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$channel_slug, "%$term%"]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        break;

    // ۸. لیست کاربران فعال و آنلاین (کسانی که در ۵ دقیقه اخیر اکتیو بودند)
    case 'fetch_online_users':
        $stmt = $pdo->query("SELECT username, avatar FROM users WHERE last_seen > NOW() - INTERVAL 5 MINUTE");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}
?>