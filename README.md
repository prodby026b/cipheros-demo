<div align="center">

# ⚡ CIPHER OS

### یک اکوسیستم داشبورد شخصی خودمیزبان (Self-Hosted) با بیش از ۴۰ سرویس یکپارچه

ساخته شده با **PHP** و **MySQL** — توسط **prodby026b**

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Compatible-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Demo%20Edition-orange?style=flat-square)
![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)

</div>

<br>

<p align="center">
  <img src=".github/assets/dashboard-preview.png" alt="Cipher OS Dashboard Preview" width="100%">
</p>

<br>

## 📖 درباره پروژه

**Cipher OS** یک داشبورد شخصی ماژولار و تمام‌عیار است که ده‌ها ابزار کاربردی روزمره — از مدیریت فایل و چت داخلی گرفته تا ابزارهای توسعه‌دهنده و مانیتورینگ شبکه — را در یک محیط یکپارچه، زیبا و سریع (Cinematic UI) کنار هم قرار می‌دهد.

این ریپازیتوری **نسخه دمو (رایگان)** پروژه است. برخی از قابلیت‌های حساس و پیشرفته در نسخه **Pro** ارائه می‌شوند و در نسخه دمو با نشان 🔒 قفل شده‌اند.

<br>

## ✨ ماژول‌های موجود در نسخه دمو (۳۸ سرویس)

| دسته | سرویس‌ها |
|---|---|
| ⚡ **هسته سیستم** | Media · Cloud · Stream · Chat · Desktop · Dashboard |
| 🛰️ **عملیات و زیرساخت** | Network · IP Tools · Speed Test · Terminal · Logs · API |
| 💻 **توسعه‌دهندگان** | Code Editor · Markdown · JSON Tools · Paste |
| 🔐 **امنیت** | Settings |
| 🎨 **خلاقیت** | Gallery · Colors · Music · QR Code · Screenshot |
| 📡 **ارتباطات** | Calls · Email · Wiki |
| 🔧 **ابزارها** | Calc · Converter · App Store · Weather · Analytics |
| 📋 **بهره‌وری** | Tasks · Calendar · Notes · Todo · Docs · Links · Timer |

<br>

## 🔒 قابلیت‌های نسخه Pro

<p align="center">
  <img src=".github/assets/pro-locked-modules.png" alt="Pro Locked Modules" width="100%">
</p>

این سرویس‌ها در نسخه دمو غیرفعال (Locked) هستند و فقط در نسخه Pro در دسترسند:

- 🤖 **Cipher AI** — دستیار هوش مصنوعی اختصاصی
- 🔐 **Cipher Vault** — ذخیره امن اطلاعات حساس
- 🔑 **Cipher Passwords** — مدیریت رمزهای عبور داخلی
- 🗄️ **Cipher Database** — مدیریت بانک اطلاعات و کوئری‌های SQL
- 💾 **Cipher Backup** — بک‌آپ خودکار و مدیریت نسخه‌های ذخیره
- 📡 **Cipher Monitor** — مانیتورینگ Real-time سرور

<br>

## ⚙️ نصب و راه‌اندازی

### پیش‌نیازها
- PHP `8.0` یا بالاتر
- MySQL / MariaDB
- وب‌سرور Apache/Nginx یا هاست اشتراکی با پشتیبانی PHP (cPanel سازگار)

### مراحل نصب

```bash
# 1) کلون کردن ریپازیتوری
git clone https://github.com/USERNAME/cipheros-demo.git
cd cipheros-demo

# 2) اجرای لوکال با سرور داخلی PHP (یا آپلود روی هاست)
php -S localhost:8000
```

سپس:
1. فایل‌های `config.php` مربوط به هر ماژول (مثل `cipher-message/config.php`) را با اطلاعات دیتابیس خودتان تنظیم کنید.
2. فایل‌های `schema.sql` موجود در ماژول‌ها را در دیتابیس خود ایمپورت کنید.
3. به آدرس `index.php` سر بزنید 🚀

<br>

## 🗂 ساختار پروژه

```
cipheros-demo/
├── cipher-core/          # هسته اصلی، تم و امنیت
├── cipher-<module>/      # هر سرویس در پوشه اختصاصی خودش
├── index.php             # داشبورد اصلی
└── README.md
```

<br>

## 💎 نسخه Pro

برای دسترسی به قابلیت‌های امنیتی و پیشرفته (Vault, Passwords, Database Manager, Backup, Monitor, AI) به نسخه Pro نیاز دارید. برای اطلاع از قیمت و نحوه خرید با من در ارتباط باشید.

<br>

---

<div align="center">

ساخته شده با ❤️ توسط **prodby026b**

</div>
