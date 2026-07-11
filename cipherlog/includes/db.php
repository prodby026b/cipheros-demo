<?php
// includes/db.php — Database connection (singleton PDO)
if (!defined('INSTALLED')) {
    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        // Not installed yet
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, 'install.php') === false) {
            header('Location: /install.php');
            exit;
        }
        return;
    }
    require_once $configPath;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
        } catch (PDOException $e) {
            // در محیط production خطای دقیق نشون نده
            if (defined('BLOG_URL')) {
                error_log('CipherLog DB Error: ' . $e->getMessage());
                http_response_code(503);
                die('Service temporarily unavailable. Please try again later.');
            }
            die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function query(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function queryOne(string $sql, array $params = []): ?array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function execute(string $sql, array $params = []): int {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}

function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = queryOne("SELECT `value` FROM settings WHERE `key`=?", [$key]);
        $cache[$key] = $row ? $row['value'] : $default;
    }
    return $cache[$key] ?: $default;
}

function setSetting(string $key, string $value): void {
    execute(
        "INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)",
        [$key, $value]
    );
}
