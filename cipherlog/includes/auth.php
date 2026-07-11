<?php
// includes/auth.php — Admin authentication

function authStart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
            'gc_maxlifetime'  => 86400,
        ]);
    }
}

function isLoggedIn(): bool {
    authStart();
    if (empty($_SESSION['admin_id'])) return false;
    // Verify session in DB
    $sid = session_id();
    $row = queryOne("SELECT * FROM sessions WHERE id=? AND expires_at > NOW()", [$sid]);
    if (!$row || $row['user_id'] != $_SESSION['admin_id']) {
        session_destroy();
        return false;
    }
    return true;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(url('admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    }
}

function getCurrentUser(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    return queryOne("SELECT * FROM users WHERE id=?", [$_SESSION['admin_id']]);
}

function login(string $username, string $password): bool {
    $user = queryOne("SELECT * FROM users WHERE username=? OR email=?", [$username, $username]);
    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed attempt
        execute("INSERT INTO security_log (event,ip,user_agent,details) VALUES ('login_fail',?,?,?)",
            [$_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', "Username: $username"]);
        return false;
    }

    $_SESSION['admin_id'] = $user['id'];
    $expires = date('Y-m-d H:i:s', time() + 86400);
    $sid = session_id();

    execute("DELETE FROM sessions WHERE user_id=?", [$user['id']]);
    execute("INSERT INTO sessions (id,user_id,ip,user_agent,expires_at) VALUES (?,?,?,?,?)",
        [$sid, $user['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', $expires]);

    execute("INSERT INTO security_log (event,ip,user_agent,details) VALUES ('login_ok',?,?,?)",
        [$_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', "User: {$user['username']}"]);

    return true;
}

function logout(): void {
    authStart();
    $sid = session_id();
    execute("DELETE FROM sessions WHERE id=?", [$sid]);
    session_destroy();
}
