<div align="center">

# ⚡ CIPHER OS

### A Self-Hosted Personal Dashboard Ecosystem with 40+ Integrated Services

Built with **PHP** & **MySQL** — by **prodby026b**

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Compatible-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Demo%20Edition-orange?style=flat-square)
![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)

**🌐 Language / زبان / اللغة / Idioma / Язык / 语言 / Langue:**
[English](#-english) · [فارسی](#-فارسی) · [العربية](#-العربية) · [Español](#-español) · [Русский](#-русский) · [中文](#-中文) · [Français](#-français)

</div>

<br>

<p align="center">
  <img src=".github/assets/dashboard-preview.png" alt="Cipher OS Dashboard Preview" width="100%">
</p>

<br>

---

## 🇬🇧 English

### About
**Cipher OS** is a fully modular, self-hosted personal dashboard bringing together 40+ everyday tools — file management, internal chat, developer utilities, network monitoring, and more — in one fast, beautifully designed control center.

This repository is the **Demo (Free) Edition**. Some advanced/security features are reserved for the **Pro** version and shown locked (🔒) here.

### ✨ Included Modules (Demo — 38 services)
Media · Cloud · Stream · Chat · Desktop · Dashboard · Network · IP Tools · Speed Test · Terminal · Logs · API · Code Editor · Markdown · JSON Tools · Paste · Settings · Gallery · Colors · Music · QR Code · Screenshot · Calls · Email · Wiki · Calc · Converter · App Store · Weather · Analytics · Tasks · Calendar · Notes · Todo · Docs · Links · Timer

### 🔒 Pro-Only Features
<p align="center">
  <img src=".github/assets/pro-locked-modules.png" alt="Pro Locked Modules" width="100%">
</p>

- 🤖 **Cipher AI** — dedicated AI assistant
- 🔐 **Cipher Vault** — secure storage for sensitive data
- 🔑 **Cipher Passwords** — internal password manager
- 🗄️ **Cipher Database** — SQL database management
- 💾 **Cipher Backup** — automated backups
- 📡 **Cipher Monitor** — real-time server monitoring

### ⚙️ Installation

**Requirements:** PHP 8.0+, MySQL/MariaDB, `mysqli` extension
> The installer can auto-install PHP and the `mysqli` extension if a package manager is available (dnf, apt, pacman, zypper, brew).

**One-command automated setup:**
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```
The universal installer checks (and can auto-install) requirements, configures the database, creates all tables, generates a secure random admin password, optionally launches the built-in PHP server, and even opens your browser. Fully interactive with sensible defaults.

**Fully automatic (non-interactive) install:**
```bash
./install.sh --auto
```

**Common options:**
```bash
./install.sh --user root --pass "" --name cipher_os   # custom DB credentials
./install.sh --auto --port 8080                        # auto on custom port
./install.sh --cipherlog                               # also install the blog module
./install.sh --uninstall                               # remove the installation
./install.sh --help                                    # full options list
```

**Manual setup** is also possible: configure `cipher-core/.dbconfig.php`, run `php install.php` once, then delete it.

### 💎 Want the Pro Version?
Contact me directly — see [Contact](#-contact--تماس) below.

<br>

---

## 🇮🇷 فارسی

### درباره پروژه
**Cipher OS** یک داشبورد شخصی ماژولار و تمام‌عیار است که ده‌ها ابزار کاربردی روزمره را در یک محیط یکپارچه، زیبا و سریع کنار هم قرار می‌دهد.

این ریپازیتوری **نسخه دمو (رایگان)** است. برخی قابلیت‌های پیشرفته فقط در نسخه **Pro** ارائه می‌شوند و اینجا قفل (🔒) نمایش داده می‌شوند.

### ✨ ماژول‌های دمو (۳۸ سرویس)
Media · Cloud · Stream · Chat · Desktop · Dashboard · Network · IP Tools · Speed Test · Terminal · Logs · API · Code Editor · Markdown · JSON Tools · Paste · Settings · Gallery · Colors · Music · QR Code · Screenshot · Calls · Email · Wiki · Calc · Converter · App Store · Weather · Analytics · Tasks · Calendar · Notes · Todo · Docs · Links · Timer

### 🔒 قابلیت‌های نسخه Pro
- 🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ نصب

**نیازمندی‌ها:** PHP 8.0+، MySQL/MariaDB، افزونه `mysqli`
> نصب‌کننده در صورت وجود پکیج‌منیجر (dnf، apt، pacman، zypper، brew) می‌تواند PHP و افزونه `mysqli` را به‌صورت خودکار نصب کند.

**نصب خودکار تک‌دستوری:**
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```
این اسکریپت قدرتمند نیازمندی‌ها را بررسی (و در صورت نیاز نصب) می‌کند، دیتابیس را می‌سازد، یک رمز تصادفی امن برای ادمین تولید می‌کند، سرور داخلی PHP را اجرا می‌کند و حتی مرورگر را باز می‌کند — کاملاً خودکار و تعاملی.

**نصب کاملاً خودکار (بدون تعامل):**
```bash
./install.sh --auto
```

**گزینه‌های پرکاربرد:**
```bash
./install.sh --user root --pass "" --name cipher_os   # اعتبارنامه دلخواه
./install.sh --auto --port 8080                        # خودکار روی پورت دلخواه
./install.sh --cipherlog                               # نصب ماژول وبلاگ هم
./install.sh --uninstall                               # حذف کامل نصب
./install.sh --help                                    # نمایش همه گزینه‌ها
```

**نصب دستی** هم ممکن است: `cipher-core/.dbconfig.php` را تنظیم کنید، یک‌بار `php install.php` را اجرا کنید، سپس آن را حذف کنید.

### 💎 نسخه Pro می‌خواهید؟
از بخش [تماس](#-contact--تماس) پایین صفحه با من در ارتباط باشید.

<br>

---

## 🇸🇦 العربية

### حول المشروع
**Cipher OS** لوحة تحكم شخصية معيارية بالكامل، تجمع أكثر من ٤٠ أداة يومية — إدارة الملفات، الدردشة الداخلية، أدوات المطورين، مراقبة الشبكة، والمزيد — في مركز تحكم واحد سريع وأنيق.

هذا المستودع هو **النسخة التجريبية (المجانية)**. بعض الميزات المتقدمة مخصصة لنسخة **Pro** وتظهر مقفلة (🔒) هنا.

### 🔒 ميزات حصرية لنسخة Pro
🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ التثبيت

تثبيت تلقائي بأمر واحد:
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```

### 💎 هل تريد نسخة Pro؟
تواصل معي مباشرة — انظر قسم [التواصل](#-contact--تماس) أدناه.

<br>

---

## 🇪🇸 Español

### Acerca del proyecto
**Cipher OS** es un panel personal modular y autoalojado que reúne más de 40 herramientas cotidianas — gestión de archivos, chat interno, utilidades para desarrolladores, monitoreo de red y más — en un centro de control rápido y elegante.

Este repositorio es la **Edición Demo (Gratuita)**. Algunas funciones avanzadas están reservadas para la versión **Pro** y aparecen bloqueadas (🔒) aquí.

### 🔒 Funciones exclusivas de Pro
🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ Instalación

Instalación automatizada con un solo comando:
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```

### 💎 ¿Quieres la versión Pro?
Contáctame directamente — ver [Contacto](#-contact--تماس) abajo.

<br>

---

## 🇷🇺 Русский

### О проекте
**Cipher OS** — полностью модульная, самостоятельно размещаемая персональная панель, объединяющая более 40 повседневных инструментов — управление файлами, внутренний чат, утилиты для разработчиков, мониторинг сети и многое другое — в едином быстром и красивом центре управления.

Этот репозиторий — **Демо (бесплатная) версия**. Некоторые продвинутые функции доступны только в версии **Pro** и здесь показаны заблокированными (🔒).

### 🔒 Функции только для Pro
🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ Установка

Автоматическая установка одной командой:
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```

### 💎 Хотите версию Pro?
Свяжитесь со мной напрямую — см. раздел [Контакты](#-contact--تماس) ниже.

<br>

---

## 🇨🇳 中文

### 关于项目
**Cipher OS** 是一个完全模块化的自托管个人仪表盘，集成了 40 多种日常工具——文件管理、内部聊天、开发者工具、网络监控等——于一个快速美观的控制中心。

本仓库为**演示（免费）版**。部分高级功能仅在 **Pro** 版本中提供，此处以锁定状态（🔒）显示。

### 🔒 仅限 Pro 版的功能
🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ 安装

一条命令自动安装：
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```

### 💎 想要 Pro 版本？
直接联系我 — 见下方[联系方式](#-contact--تماس)。

<br>

---

## 🇫🇷 Français

### À propos du projet
**Cipher OS** est un tableau de bord personnel entièrement modulaire et auto-hébergé, réunissant plus de 40 outils du quotidien — gestion de fichiers, chat interne, outils de développement, surveillance réseau et plus encore — dans un centre de contrôle rapide et élégant.

Ce dépôt est l'**Édition Démo (Gratuite)**. Certaines fonctionnalités avancées sont réservées à la version **Pro** et apparaissent verrouillées (🔒) ici.

### 🔒 Fonctionnalités exclusives à Pro
🤖 Cipher AI · 🔐 Cipher Vault · 🔑 Cipher Passwords · 🗄️ Cipher Database · 💾 Cipher Backup · 📡 Cipher Monitor

### ⚙️ Installation

Installation automatisée en une seule commande :
```bash
git clone https://github.com/prodby026b/cipheros-demo.git
cd cipheros-demo
chmod +x install.sh
./install.sh
```

### 💎 Vous voulez la version Pro ?
Contactez-moi directement — voir [Contact](#-contact--تماس) ci-dessous.

<br>

---

## 📬 Contact / تماس

<div align="center">

📧 **Email:** [prodby026b@gmail.com](mailto:prodby026b@gmail.com)
💬 **Telegram:** [@prodby026b](https://t.me/prodby026b)

</div>

<br>

---

<div align="center">

Made with ❤️ by **prodby026b**

</div>
