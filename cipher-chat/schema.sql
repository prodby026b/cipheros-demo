-- ==================================================================
-- Cipher Chat — Schema (نسخه تقویت‌شده)
-- دیتابیس: cipher_os (به‌صورت مستقیم روی دیتابیس اصلی Cipher OS)
-- کاراکترست: utf8mb4 برای پشتیبانی کامل از فارسی و ایموجی
-- ==================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── کاربران چت (سینک‌شده با سشن اصلی) ───────────────────────────
CREATE TABLE IF NOT EXISTS chat_users (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(32)  NOT NULL,
  display_name  VARCHAR(64)  NULL,
  avatar_color  VARCHAR(16)  NOT NULL DEFAULT '#00eaff',
  is_online     TINYINT(1)   NOT NULL DEFAULT 0,
  last_seen     DATETIME     NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_chat_user (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── اتاق‌ها / کانال‌ها ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_rooms (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name         VARCHAR(64)  NOT NULL,
  slug         VARCHAR(64)  NOT NULL,
  description  VARCHAR(255) NULL,
  created_by   VARCHAR(32)  NOT NULL,
  is_private   TINYINT(1)   NOT NULL DEFAULT 0,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_room_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── اعضای اتاق ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_room_members (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_id    BIGINT UNSIGNED NOT NULL,
  username   VARCHAR(32)  NOT NULL,
  role       ENUM('admin','member') NOT NULL DEFAULT 'member',
  joined_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_member (room_id, username),
  KEY idx_member_user (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── پیام‌ها ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_messages (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_id      BIGINT UNSIGNED NOT NULL,
  username     VARCHAR(32)  NOT NULL,
  message      TEXT         NULL,
  type         ENUM('text','image','system') NOT NULL DEFAULT 'text',
  file_path    VARCHAR(255) NULL,
  reply_to_id  BIGINT UNSIGNED NULL,
  edited_at    DATETIME     NULL,
  deleted_at   DATETIME     NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_msg_room (room_id, id),
  KEY idx_msg_user (username),
  KEY idx_msg_reply (reply_to_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── واکنش‌ها ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_reactions (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  message_id  BIGINT UNSIGNED NOT NULL,
  username    VARCHAR(32)  NOT NULL,
  emoji       VARCHAR(16)  NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_reaction (message_id, username, emoji),
  KEY idx_react_msg (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── وضعیت خوانده شده ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_message_reads (
  message_id  BIGINT UNSIGNED NOT NULL,
  username    VARCHAR(32)  NOT NULL,
  read_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id, username),
  KEY idx_read_user (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── وضعیت تایپ ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chat_typing (
  room_id       BIGINT UNSIGNED NOT NULL,
  username      VARCHAR(32)  NOT NULL,
  last_typed_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (room_id, username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─── داده‌های اولیه: اتاق پیش‌فرض "عمومی" ─────────────────────────
INSERT IGNORE INTO chat_rooms (name, slug, description, created_by, is_private)
VALUES ('عمومی', 'general', 'اتاق گفتگوی عمومی تیم Cipher OS', 'system', 0);
