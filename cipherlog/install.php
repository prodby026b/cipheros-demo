<?php
/**
 * CipherLog Installer
 * Run this ONCE to set up the database
 * Delete after installation!
 */

define('CL_INSTALL', true);

// ── CONFIG ── Edit these before running
$config = [
    'db_host'     => 'localhost',
    'db_name'     => 'cipherlog',
    'db_user'     => 'root',
    'db_pass'     => '',          // از cPanel > MySQL Databases بگیر
    'db_charset'  => 'utf8mb4',
    'admin_user'  => 'prodby026b',
    'admin_pass'  => '',  // You MUST set this in the form below before installing
    'admin_email' => 'admin@cipherlog.sh',
    'blog_name'   => 'CipherLog',
    'blog_tagline'=> 'Linux & Network // Deep Stack',
    'blog_url'    => 'https://yourdomain.com',
];

$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = array_merge($config, $_POST);

    if (empty($config['admin_pass']) || strlen($config['admin_pass']) < 8) {
        $errors[] = 'Admin password is required and must be at least 8 characters.';
    }

    if (empty($errors)) {
    try {
        // Connect without DB name first
        $pdo = new PDO(
            "mysql:host={$config['db_host']};charset={$config['db_charset']}",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$config['db_name']}`");
        $success[] = "✓ Database created: {$config['db_name']}";

        // ── TABLES ──

        // Settings
        $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
            `key`   VARCHAR(100) PRIMARY KEY,
            `value` TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Users
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `username`   VARCHAR(60) NOT NULL UNIQUE,
            `email`      VARCHAR(120) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `display_name` VARCHAR(100),
            `bio`        TEXT,
            `avatar`     VARCHAR(255),
            `role`       ENUM('admin','editor') DEFAULT 'admin',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Categories
        $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `name`        VARCHAR(100) NOT NULL,
            `slug`        VARCHAR(120) NOT NULL UNIQUE,
            `description` TEXT,
            `color`       VARCHAR(20) DEFAULT 'green',
            `parent_id`   INT DEFAULT NULL,
            `post_count`  INT DEFAULT 0,
            `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Tags
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tags` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(80) NOT NULL,
            `slug`       VARCHAR(100) NOT NULL UNIQUE,
            `post_count` INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Posts
        $pdo->exec("CREATE TABLE IF NOT EXISTS `posts` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `title`           VARCHAR(255) NOT NULL,
            `slug`            VARCHAR(280) NOT NULL UNIQUE,
            `excerpt`         TEXT,
            `content`         LONGTEXT,
            `featured_image`  VARCHAR(255),
            `author_id`       INT NOT NULL,
            `category_id`     INT,
            `status`          ENUM('draft','published','scheduled','review') DEFAULT 'draft',
            `visibility`      ENUM('public','private','password') DEFAULT 'public',
            `password`        VARCHAR(100),
            `is_featured`     TINYINT(1) DEFAULT 0,
            `views`           INT DEFAULT 0,
            `reading_time`    INT DEFAULT 0,
            `meta_title`      VARCHAR(255),
            `meta_desc`       TEXT,
            `focus_keyword`   VARCHAR(120),
            `canonical_url`   VARCHAR(255),
            `og_image`        VARCHAR(255),
            `allow_comments`  TINYINT(1) DEFAULT 1,
            `comment_count`   INT DEFAULT 0,
            `published_at`    DATETIME,
            `scheduled_at`    DATETIME,
            `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX(`status`), INDEX(`category_id`), INDEX(`author_id`), INDEX(`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Post Tags (pivot)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `post_tags` (
            `post_id` INT NOT NULL,
            `tag_id`  INT NOT NULL,
            PRIMARY KEY(`post_id`,`tag_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Comments
        $pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `post_id`    INT NOT NULL,
            `parent_id`  INT DEFAULT NULL,
            `author_name`  VARCHAR(100) NOT NULL,
            `author_email` VARCHAR(150) NOT NULL,
            `author_ip`    VARCHAR(45),
            `content`    TEXT NOT NULL,
            `status`     ENUM('pending','approved','spam','trash') DEFAULT 'pending',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX(`post_id`), INDEX(`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Media
        $pdo->exec("CREATE TABLE IF NOT EXISTS `media` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `filename`    VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255),
            `mime_type`   VARCHAR(80),
            `size`        INT,
            `width`       INT,
            `height`      INT,
            `alt_text`    VARCHAR(255),
            `caption`     TEXT,
            `uploader_id` INT,
            `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Subscribers
        $pdo->exec("CREATE TABLE IF NOT EXISTS `subscribers` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `email`      VARCHAR(150) NOT NULL UNIQUE,
            `status`     ENUM('active','unsubscribed') DEFAULT 'active',
            `source`     VARCHAR(60) DEFAULT 'blog',
            `token`      VARCHAR(64),
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Sessions (admin login)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sessions` (
            `id`         VARCHAR(128) PRIMARY KEY,
            `user_id`    INT NOT NULL,
            `ip`         VARCHAR(45),
            `user_agent` TEXT,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `expires_at` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Security log
        $pdo->exec("CREATE TABLE IF NOT EXISTS `security_log` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `event`      VARCHAR(80),
            `ip`         VARCHAR(45),
            `user_agent` TEXT,
            `details`    TEXT,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX(`event`), INDEX(`ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $success[] = "✓ All tables created";

        // ── SEED DATA ──

        // Admin user
        $hash = password_hash($config['admin_pass'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username,email,password,display_name,bio,role) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $config['admin_user'],
            $config['admin_email'],
            $hash,
            $config['admin_user'],
            'Linux & Network educator. Documenting the deep stack — one command at a time.',
            'admin'
        ]);
        $success[] = "✓ Admin user created: {$config['admin_user']}";

        // Default categories
        $cats = [
            ['Linux',     'linux',     'Kernel, systemd, filesystem, package management',    'green'],
            ['Network',   'network',   'TCP/IP, subnetting, routing, VPNs, packet analysis', 'blue'],
            ['Security',  'security',  'Hardening, SSH, firewalls, pen-testing tools',        'red'],
            ['Scripting', 'scripting', 'Bash, Python, AWK, SED, automation',                 'purple'],
            ['Tools',     'tools',     'CLI tools, tmux, vim, monitoring stacks',             'yellow'],
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name,slug,description,color) VALUES (?,?,?,?)");
        foreach ($cats as $c) $stmt->execute($c);
        $success[] = "✓ Default categories seeded";

        // Default tags
        $tags = ['linux','network','bash','ssh','iptables','routing','python','nmap','systemd','tcp-ip','security','scripting','vpn','firewall','automation'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO tags (name,slug) VALUES (?,?)");
        foreach ($tags as $t) $stmt->execute([$t, $t]);
        $success[] = "✓ Default tags seeded";

        // Settings
        $settingsData = [
            'blog_name'         => $config['blog_name'],
            'blog_tagline'      => $config['blog_tagline'],
            'blog_url'          => $config['blog_url'],
            'admin_email'       => $config['admin_email'],
            'posts_per_page'    => '10',
            'allow_comments'    => '1',
            'allow_registration'=> '0',
            'smtp_host'         => '',
            'smtp_port'         => '587',
            'smtp_user'         => '',
            'smtp_pass'         => '',
            'ga_id'             => '',
            'maintenance_mode'  => '0',
            'installed_at'      => date('Y-m-d H:i:s'),
            'version'           => '1.0.0',
        ];
        $stmt = $pdo->prepare("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
        foreach ($settingsData as $k => $v) $stmt->execute([$k, $v]);
        $success[] = "✓ Settings saved";

        // Sample post
        $userRow = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $userRow->execute([$config['admin_user']]);
        $uid = $userRow->fetchColumn();
        $catRow = $pdo->prepare("SELECT id FROM categories WHERE slug='linux' LIMIT 1");
        $catRow->execute([]);
        $cid = $catRow->fetchColumn();

        $sampleContent = "## Introduction\n\nEvery Linux system ships with **iptables** — a powerful, stateful packet filtering engine built into the kernel.\n\n## How iptables Works\n\niptables operates on **tables**, each containing **chains**, which hold ordered lists of **rules**.\n\n\`\`\`bash\n# Flush existing rules\niptables -F\niptables -X\n\n# Default policy: DROP everything\niptables -P INPUT DROP\niptables -P FORWARD DROP\niptables -P OUTPUT ACCEPT\n\n# Allow loopback\niptables -A INPUT -i lo -j ACCEPT\n\n# Allow established connections\niptables -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT\n\n# Allow SSH\niptables -A INPUT -p tcp --dport 22 -m conntrack --ctstate NEW -j ACCEPT\n\`\`\`\n\n## DROP vs REJECT\n\n`DROP` silently discards packets. `REJECT` sends an ICMP reply back. Use DROP on external interfaces, REJECT internally.";

        $stmt = $pdo->prepare("INSERT IGNORE INTO posts (title,slug,excerpt,content,author_id,category_id,status,is_featured,views,reading_time,meta_title,meta_desc,focus_keyword,allow_comments,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->execute([
            'iptables: A Complete Guide to Linux Firewall',
            'iptables-complete-guide',
            'Master iptables from the ground up — tables, chains, rules, NAT, and production-ready firewall configs.',
            $sampleContent,
            $uid,
            $cid,
            'published',
            1,
            2341,
            12,
            'iptables Tutorial: Complete Linux Firewall Guide',
            'Master iptables with this complete guide covering tables, chains, rules, NAT and real firewall configurations.',
            'iptables tutorial',
            1
        ]);
        $success[] = "✓ Sample post created";

        // Write config file
        $configContent = "<?php\n// CipherLog Config — generated by installer\ndefine('DB_HOST',    '{$config['db_host']}');\ndefine('DB_NAME',    '{$config['db_name']}');\ndefine('DB_USER',    '{$config['db_user']}');\ndefine('DB_PASS',    '{$config['db_pass']}');\ndefine('DB_CHARSET', '{$config['db_charset']}');\ndefine('BLOG_URL',   '{$config['blog_url']}');\ndefine('UPLOADS_DIR', __DIR__ . '/uploads/');\ndefine('UPLOADS_URL', BLOG_URL . '/uploads/');\ndefine('SECRET_KEY',  '" . bin2hex(random_bytes(32)) . "');\ndefine('INSTALLED',   true);\n";
        file_put_contents(__DIR__ . '/config.php', $configContent);
        $success[] = "✓ config.php written";

    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>CipherLog Installer</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0e17;color:#e2e8f0;font-family:'JetBrains Mono',monospace;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.box{background:#111827;border:1px solid #1e2d4a;border-radius:10px;width:580px;overflow:hidden}
.box-head{padding:24px;border-bottom:1px solid #1e2d4a;text-align:center}
.logo{font-size:20px;font-weight:700;color:#00ff9d;letter-spacing:3px;margin-bottom:4px}
.sub{font-size:10px;color:#64748b;letter-spacing:1px}
.box-body{padding:24px}
.form-group{margin-bottom:14px}
label{display:block;font-size:9px;letter-spacing:1.5px;color:#64748b;margin-bottom:5px;text-transform:uppercase}
input{width:100%;background:#141929;border:1px solid #1e2d4a;border-radius:5px;padding:9px 12px;font-size:12px;font-family:inherit;color:#e2e8f0;outline:none}
input:focus{border-color:#00ff9d}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;background:#00ff9d;color:#000;border:none;border-radius:5px;padding:12px;font-family:inherit;font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;margin-top:8px}
.btn:hover{background:#00e68a}
.msg{padding:12px;border-radius:5px;margin-bottom:12px;font-size:11px;line-height:1.8}
.msg.success{background:rgba(0,255,157,.1);border:1px solid rgba(0,255,157,.2);color:#00ff9d}
.msg.error{background:rgba(255,62,62,.1);border:1px solid rgba(255,62,62,.2);color:#ff3e3e}
.done{text-align:center;padding:20px 0}
.done h2{color:#00ff9d;font-size:18px;margin-bottom:8px}
.done p{font-size:11px;color:#64748b;line-height:1.9;margin-bottom:16px}
.links{display:flex;gap:10px;justify-content:center}
.link-btn{padding:9px 18px;border-radius:5px;font-size:11px;font-family:inherit;cursor:pointer;border:1px solid;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.link-btn.green{background:#00ff9d;color:#000;border-color:#00ff9d}
.link-btn.ghost{background:transparent;color:#64748b;border-color:#1e2d4a}
</style>
</head>
<body>
<div class="box">
  <div class="box-head">
    <div class="logo">CIPHER_LOG</div>
    <div class="sub">INSTALLER v1.0 — SETUP YOUR BLOG</div>
  </div>
  <div class="box-body">
    <?php if (!empty($success) && empty($errors)): ?>
      <div class="done">
        <h2>✓ Installation Complete!</h2>
        <p>CipherLog has been installed successfully.<br>
        <strong style="color:#ff3e3e">⚠ Delete install.php immediately for security.</strong></p>
        <div class="links">
          <a href="index.php" class="link-btn green">→ View Blog</a>
          <a href="admin/login.php" class="link-btn ghost">→ Admin Panel</a>
        </div>
      </div>
      <div class="msg success"><?= implode('<br>', $success) ?></div>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="msg error"><?= implode('<br>', $errors) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div style="font-size:10px;color:#64748b;margin-bottom:16px;letter-spacing:.5px">DATABASE CONNECTION</div>
        <div class="row2">
          <div class="form-group"><label>DB Host</label><input name="db_host" value="<?= htmlspecialchars($config['db_host']) ?>"></div>
          <div class="form-group"><label>DB Name</label><input name="db_name" value="<?= htmlspecialchars($config['db_name']) ?>"></div>
        </div>
        <div class="row2">
          <div class="form-group"><label>DB User</label><input name="db_user" value="<?= htmlspecialchars($config['db_user']) ?>"></div>
          <div class="form-group"><label>DB Password</label><input name="db_pass" type="password" value="<?= htmlspecialchars($config['db_pass']) ?>"></div>
        </div>
        <div style="font-size:10px;color:#64748b;margin:16px 0 12px;letter-spacing:.5px">ADMIN ACCOUNT</div>
        <div class="row2">
          <div class="form-group"><label>Username</label><input name="admin_user" value="<?= htmlspecialchars($config['admin_user']) ?>"></div>
          <div class="form-group"><label>Password</label><input name="admin_pass" type="password" value="<?= htmlspecialchars($config['admin_pass']) ?>"></div>
        </div>
        <div class="form-group"><label>Admin Email</label><input name="admin_email" value="<?= htmlspecialchars($config['admin_email']) ?>"></div>
        <div style="font-size:10px;color:#64748b;margin:16px 0 12px;letter-spacing:.5px">BLOG CONFIG</div>
        <div class="form-group"><label>Blog Name</label><input name="blog_name" value="<?= htmlspecialchars($config['blog_name']) ?>"></div>
        <div class="form-group"><label>Blog URL (no trailing slash)</label><input name="blog_url" value="<?= htmlspecialchars($config['blog_url']) ?>"></div>
        <button class="btn" type="submit">INSTALL CIPHERLOG</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
