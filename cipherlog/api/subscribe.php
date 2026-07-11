<?php
// api/subscribe.php — Newsletter subscription
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
if (session_status() === PHP_SESSION_NONE) session_start();
$token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid email address.']);
    exit;
}

// Check if already subscribed
$existing = queryOne("SELECT * FROM subscribers WHERE email=?", [$email]);
if ($existing) {
    if ($existing['status'] === 'active') {
        echo json_encode(['ok' => false, 'error' => 'This email is already subscribed.']);
    } else {
        execute("UPDATE subscribers SET status='active' WHERE email=?", [$email]);
        echo json_encode(['ok' => true, 'message' => 'Welcome back!']);
    }
    exit;
}

$token = bin2hex(random_bytes(32));
execute("INSERT INTO subscribers (email, token, source) VALUES (?,?,?)", [
    $email, $token, $_POST['source'] ?? 'blog'
]);

echo json_encode(['ok' => true, 'message' => 'Subscribed successfully!']);
