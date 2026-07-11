-- ==================================================================
-- Cipher OS — Users table (احراز هویت اصلی)
-- ------------------------------------------------------------------
-- جدول کاربران برای لاگین امن با password_hash
-- ادمین پیش‌فرض: username=admin  password=CHANGE_ME_ON_FIRST_LOGIN (بعداً تغییر دهید!)
-- ==================================================================

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(32) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_os_user (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ادمین پیش‌فرض (هش تولیدشده برای 'CHANGE_ME_ON_FIRST_LOGIN')
-- برای امنیت بیشتر، بعد از نصب حتماً رمز را تغییر دهید.
INSERT IGNORE INTO users (username, password_hash, role)
VALUES ('admin', '$2b$10$qHGUNcU7Fg.xHf.yWPx..ehE2f4TEAOlesFuUPK0gxx7kjsIkoiLu', 'admin');
