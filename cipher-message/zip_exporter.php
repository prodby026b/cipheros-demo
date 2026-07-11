<?php
// cipher-message/zip_exporter.php
header('Content-Type: text/html; charset=utf-8');

// نام فایل زیپ خروجی
$zipFileName = 'CipherMessage_Full_Project.zip';

// بررسی فعال بودن افزونه ZipArchive در سرور شما
if (!class_exists('ZipArchive')) {
    die("<div style='color:#ff4a4a; font-family:sans-serif; text-align:center; margin-top:50px;'>
            خطا: افزونه ZipArchive روی سرور یا XAMPP شما فعال نیست!
         </div>");
}

$zip = new ZipArchive();

if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    
    // ۱. اضافه کردن فایل‌های ریشه پروژه
    if (file_exists('index.php'))  $zip->addFile('index.php', 'index.php');
    if (file_exists('config.php')) $zip->addFile('config.php', 'config.php');
    if (file_exists('api.php'))    $zip->addFile('api.php', 'api.php');
    
    // ساخت فایل db.sql به صورت خودکار درون زیپ برای راحتی کار شما
    $sqlContent = "-- CipherMessage Database Setup\n" .
                  "CREATE DATABASE IF NOT EXISTS cipheros_messages DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n" .
                  "USE cipheros_messages;\n\n" .
                  "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, avatar VARCHAR(255) DEFAULT 'default_avatar.png', last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n" .
                  "CREATE TABLE IF NOT EXISTS channels (id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(50) NOT NULL UNIQUE, name VARCHAR(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n" .
                  "INSERT IGNORE INTO channels (slug, name) VALUES ('general', 'عمومی'), ('dev', 'توسعه و کدنویسی'), ('design', 'طراحی و رابط کاربری'), ('random', 'گفتگو آزاد');\n\n" .
                  "CREATE TABLE IF NOT EXISTS messages (id INT AUTO_INCREMENT PRIMARY KEY, channel_id INT NOT NULL, username VARCHAR(100) NOT NULL, message TEXT NOT NULL, reply_to INT NULL DEFAULT NULL, is_pinned TINYINT(1) DEFAULT 0, is_edited TINYINT(1) DEFAULT 0, is_deleted TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE, FOREIGN KEY (reply_to) REFERENCES messages(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n" .
                  "CREATE TABLE IF NOT EXISTS reactions (id INT AUTO_INCREMENT PRIMARY KEY, message_id INT NOT NULL, username VARCHAR(100) NOT NULL, emoji VARCHAR(50) NOT NULL, UNIQUE KEY unique_user_reaction (message_id, username, emoji), FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $zip->addFromString('db.sql', $sqlContent);

    // ۲. اضافه کردن فایل‌های پوشه assets
    if (is_dir('assets')) {
        $zip->addEmptyDir('assets');
        if (file_exists('assets/style.css')) $zip->addFile('assets/style.css', 'assets/style.css');
        if (file_exists('assets/app.js'))    $zip->addFile('assets/app.js', 'assets/app.js');
    } else {
        // اگر پوشه از قبل وجود نداشت، فایل‌ها را با ساختار پوشه می‌سازد
        if (file_exists('style.css')) $zip->addFile('style.css', 'assets/style.css');
        if (file_exists('app.js'))    $zip->addFile('app.js', 'assets/app.js');
    }

    $zip->close();

    // ۳. شروع دانلود خودکار فایل زیپ در مرورگر کاربر
    if (file_exists($zipFileName)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFileName));
        readfile($zipFileName);
        
        // حذف فایل زیپ موقت از روی سرور پس از دانلود
        unlink($zipFileName);
        exit;
    }
} else {
    echo "خطا در ساخت فایل فشرده زیپ!";
}
?>