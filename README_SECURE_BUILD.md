# 🔐 Cipher OS — تقویت امنیتی + Cipher Chat

این نسخه شامل بازنویسی کامل **Cipher Chat** به‌صورت قدرتمند و امن، و ایجاد یک **هسته امنیتی مشترک** برای تمام ماژول‌های Cipher OS است.

---

## 📦 نصب سریع

### ۱) فعال‌سازی Apache + MySQL
در **XAMPP Control Panel**، هر دو سرویس `Apache` و `MySQL` را Start کنید.

### ۲) اجرای نصب‌کننده
در مرورگر به این آدرس بروید:

```
http://localhost/cipheros_updated/install.php
```

این کار:
- دیتابیس `cipher_os` را می‌سازد
- تمام جداول چت (۷ جدول) + جدول کاربران را ایجاد می‌کند
- یک اتاق پیش‌فرض «عمومی» می‌سازد
- کاربر ادمین پیش‌فرض ایجاد می‌کند

### ۳) ورود
- **نام کاربری:** `admin`
- **رمز عبور:** `CHANGE_ME_ON_FIRST_LOGIN`

### ۴) پاک‌سازی امنیتی ⚠️
پس از نصب موفق، **حتماً** فایل `install.php` را حذف کنید.

---

## ✨ امکانات جدید Cipher Chat

| قابلیت | توضیح |
|--------|--------|
| 💬 **اتاق‌ها / کانال‌ها** | ساخت اتاق عمومی و خصوصی، عضویت |
| 🔄 **ریپلای** | پاسخ به پیام مشخص با preview |
| ✏️ **ویرایش پیام** | فقط فرستنده می‌تواند ویرایش کند |
| 🗑️ **حذف نرم** | پیام حذف می‌شود ولی رد آن می‌ماند |
| 😀 **واکنش‌ها (Reactions)** | ایموجی toggle روی هر پیام |
| 🟢 **Online / Seen** | وضعیت آنلاین واقعی + تیک خوانده‌شده |
| ⌨️ **Typing Indicator** | «در حال تایپ...» به‌صورت زنده |
| 🔍 **جستجو** | Ctrl+K برای جستجو در پیام‌ها |
| 🖼️ **آپلود امن تصویر** | بررسی MIME، محدودیت حجم، نام تصادفی |

---

## 🛡️ هسته امنیتی مشترک (`cipher-core/security.php`)

یک فایل واحد که تمام ماژول‌ها از آن استفاده می‌کنند:

| ویژگی | توضیح |
|-------|-------|
| 🔐 **CSRF Protection** | توکن تصادفی در هر سشن + اعتبارسنجی POST |
| 🧼 **Input Sanitizer** | `sanitize()` ضد XSS / SQL Injection |
| ⏱️ **Rate Limiter** | محدودیت درخواست بر اساس IP + Session |
| 🔑 **Auth Guard** | `require_auth()` بررسی احراز هویت |
| 📡 **Secure Headers** | CSP, X-Frame-Options, X-Content-Type-Options |
| 📎 **Secure Upload** | بررسی MIME واقعی، محدودیت حجم، نام امن |

### نحوه استفاده در هر ماژول:
```php
<?php
require_once __DIR__ . '/../cipher-core/security.php';
require_auth();          // بررسی احراز هویت
// ... کد ماژول
```

---

## 🔒 رفع اشکالات امنیتی پروژه

| مشکل | راه‌حل اعمال‌شده |
|------|------------------|
| رمز عبور hardcoded در `login.php` | `password_hash()` + جدول `users` + fallback امن |
| `root` بدون رمز در `db.php` | پشتیبانی از فایل کانفیگ `.dbconfig.php` |
| اعتبارنامه افشا در `cipher-chat/db.php` | حذف کامل، استفاده از دیتابیس اصلی |
| Session Fixation | `session_regenerate_id()` بعد از لاگین |
| بدون CSRF | توکن CSRF در همه فرم‌ها و APIها |
| SQL Injection در `upload.php` | prepared statements در همه جا |
| آپلود فایل ناامن | بررسی MIME، نام تصادفی، `.htaccess` |
| بدون Rate Limiting | محدودیت در همه endpoint‌ها |

---

## 🗂️ ساختار فایل‌های مهم

```
cipher-core/
├── security.php              ← هسته امنیتی مشترک (CSRF/Sanitizer/RateLimit/Auth)
├── users_schema.sql          ← اسکیمای جدول کاربران
└── .dbconfig.example.php     ← نمونه کانفیگ دیتابیس برای production

cipher-chat/
├── db.php                    ← اتصال دیتابیس (امن)
├── schema.sql                ← اسکیمای کامل چت (۷ جدول)
├── index.php                 ← رابط کاربری کامل (بازنویسی‌شده)
├── api/
│   ├── _bootstrap.php        ← bootstrap مشترک API
│   ├── send.php              ← ارسال پیام
│   ├── fetch.php             ← دریافت پیام (polling بهینه با last_id)
│   ├── rooms.php             ← مدیریت اتاق‌ها
│   ├── edit.php              ← ویرایش پیام
│   ├── delete.php            ← حذف نرم
│   ├── react.php             ← واکنش‌ها
│   ├── reply.php             ← ریپلای preview
│   ├── typing.php            ← وضعیت تایپ
│   ├── online.php            ← وضعیت آنلاین
│   ├── search.php            ← جستجو
│   └── upload.php            ← آپلود امن تصویر
└── uploads/
    └── .htaccess             ← جلوگیری از اجرای PHP

login.php                     ← لاگین امن (بازنویسی‌شده)
logout.php                    ← خروج امن
db.php                        ← اتصال اصلی امن
install.php                   ← نصب‌کننده (بعد از نصب حذف کنید!)
```

---

## 🚀 تولید (Production)

برای استقرار روی هاست واقعی:

۱. فایل `.dbconfig.example.php` را کپی کنید به `.dbconfig.php` و اعتبارنامه‌های واقعی را بگذارید.

۲. در `login.php` بخش fallback رمز قدیمی را حذف کنید.

۳. رمز ادمین را در دیتابیس تغییر دهید.

۴. `install.php` را حذف کنید.

۵. `display_errors` در `php.ini` خاموش باشد.

---

**PRODBY026B · Secure Build**
