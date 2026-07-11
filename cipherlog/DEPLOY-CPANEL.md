# CipherLog — راهنمای نصب روی cPanel

## مرحله ۱: دیتابیس بساز

1. وارد **cPanel** بشو
2. برو به **MySQL Databases**
3. یه دیتابیس جدید بساز: `username_cipherlog`
4. یه یوزر MySQL بساز: `username_cloguser`
5. یه پسورد قوی بده و یادداشت کن
6. یوزر رو به دیتابیس اضافه کن با **ALL PRIVILEGES**

> **مهم:** روی هاست اشتراکی، نام دیتابیس و یوزر با پیشوند username_ شروع میشن.
> مثلاً اگه username هاستت `john` باشه:
> - DB Name: `john_cipherlog`
> - DB User: `john_cloguser`

## مرحله ۲: فایل‌ها رو آپلود کن

**روش ۱ — File Manager (پیشنهادی):**
1. توی cPanel برو به **File Manager**
2. برو داخل پوشه `public_html`
3. همه محتوای داخل پوشه `cipherlog` رو آپلود کن
   (نه خود پوشه cipherlog — محتوایش رو)
4. مطمئن شو `.htaccess` هم آپلود شده (فایل مخفیه)

**روش ۲ — FTP:**
- Host: yourdomain.com
- Port: 21
- Path: `/public_html/`

## مرحله ۳: پرمیشن‌ها (Permissions)

توی File Manager:
- پوشه `uploads` → **Permission: 755**
- بقیه فایل‌های PHP → **644**
- `.htaccess` → **644**

## مرحله ۴: نصب

1. مرورگر باز کن، برو به:
   `https://yourdomain.com/install.php`

2. اطلاعات دیتابیس رو وارد کن:
   - DB Host: `localhost`
   - DB Name: `john_cipherlog`
   - DB User: `john_cloguser`
   - DB Pass: پسوردی که ساختی
   - Blog URL: `https://yourdomain.com`

3. کلیک کن **INSTALL CIPHERLOG**

## مرحله ۵: بعد از نصب

1. **فوری:** فایل `install.php` رو از File Manager حذف کن
2. وارد ادمین بشو:
   `https://yourdomain.com/admin/login.php`
3. یوزرنیم: `prodby026b` / پسورد: `cipher2026`
4. **توی Settings → Profile پسورد رو عوض کن!**

## لینک‌های مهم

| صفحه | آدرس |
|------|------|
| وبلاگ | `https://yourdomain.com/` |
| ادمین | `https://yourdomain.com/admin/` |
| لاگین | `https://yourdomain.com/admin/login.php` |
| RSS | `https://yourdomain.com/api/rss.php` |
| API | `https://yourdomain.com/api/posts.php` |

## مشکلات رایج

**500 Internal Server Error:**
- توی cPanel → Error Logs رو چک کن
- مطمئن شو PHP 7.4+ فعاله (Settings → PHP Version)

**DB Connection Failed:**
- DB Host رو به `localhost` ست کن
- نام دیتابیس و یوزر با پیشوند username_ درسته؟

**Uploads کار نمی‌کنه:**
- پوشه `uploads` → Permission: 755
- از cPanel → File Manager تغییر بده
