#!/usr/bin/env bash
#
#  ⚡ ════════════════════════════════════════════════════════════════
#   CIPHER OS  —  Universal Installer  (Demo Edition)
#  ═══════════════════════════════════════════════════════════════════
#   اسکریپت نصب خودکار، قدرتمند و شیک
#   به‌صورت خودکار PHP / MySQL را تشخیص داده و نصب می‌کند،
#   دیتابیس می‌سازد، رمز امن تولید می‌کند و سرور را راه‌اندازی می‌کند.
#  ────────────────────────────────────────────────────────────────────
#
#   نحوه استفاده:
#     ./install.sh                          # تعاملی — کامل
#     ./install.sh --auto                   # کاملاً خودکار با پیش‌فرض‌ها
#     ./install.sh --host localhost --user root --pass "" --name cipher_os
#     ./install.sh --no-serve --port 8080   # بدون اجرای سرور
#     ./install.sh --uninstall              # حذف کامل نصب
#     ./install.sh --help
#
#  ═══════════════════════════════════════════════════════════════════

set -euo pipefail
trap 'error "خطای غیرمنتظره در خط $LINENO"; cleanup_on_fail' ERR

IFS=$'\n\t'

# ─────────────────────────────────────────────────────────────────────
#  رنگ‌ها و توابع کمکی
# ─────────────────────────────────────────────────────────────────────
if [[ -t 1 ]]; then
    C_RESET='\033[0m'
    C_CYAN='\033[0;36m'
    C_GREEN='\033[0;32m'
    C_YELLOW='\033[1;33m'
    C_RED='\033[0;31m'
    C_BOLD='\033[1m'
    C_MAGENTA='\033[0;35m'
    C_BLUE='\033[0;34m'
    C_DIM='\033[2m'
    C_BG_CYAN='\033[46m\033[30m'
else
    C_RESET=''; C_CYAN=''; C_GREEN=''; C_YELLOW=''; C_RED=''
    C_BOLD=''; C_MAGENTA=''; C_BLUE=''; C_DIM=''; C_BG_CYAN=''
fi

# ── آیکون‌ها ──
ICON_INFO="▸"
ICON_OK="✔"
ICON_WARN="⚠"
ICON_ERR="✘"
ICON_STEP="◆"

# شمارش گام‌ها
declare -i STEP_NUM=0
declare -i TOTAL_STEPS=7

info()  { echo -e "  ${C_CYAN}${ICON_INFO}${C_RESET}  $1"; }
ok()    { echo -e "  ${C_GREEN}${ICON_OK}${C_RESET}  ${C_DIM}$1${C_RESET}"; }
warn()  { echo -e "  ${C_YELLOW}${ICON_WARN}${C_RESET}  ${C_YELLOW}$1${C_RESET}"; }
error() { echo -e "  ${C_RED}${ICON_ERR}${C_RESET}  ${C_RED}${C_BOLD}$1${C_RESET}"; }

step() {
    STEP_NUM+=1
    echo ""
    echo -e "  ${C_BG_CYAN}${C_BOLD} STEP ${STEP_NUM}/${TOTAL_STEPS} ${C_RESET}  ${C_BOLD}$1${C_RESET}"
    echo -e "  ${C_DIM}──────────────────────────────────────────────────${C_RESET}"
}

# ── Banner ───────────────────────────────────────────────────────────
banner() {
    echo ""
    echo -e "  ${C_CYAN}╔══════════════════════════════════════════════════════╗${C_RESET}"
    echo -e "  ${C_CYAN}║${C_RESET}                                                      ${C_CYAN}║${C_RESET}"
    echo -e "  ${C_CYAN}║${C_RESET}   ${C_BOLD}⚡  C I P H E R   O S${C_RESET}   ${C_DIM}—${C_RESET}  ${C_MAGENTA}Demo Edition${C_RESET}     ${C_CYAN}║${C_RESET}"
    echo -e "  ${C_CYAN}║${C_RESET}         ${C_DIM}Universal Installer${C_RESET}                           ${C_CYAN}║${C_RESET}"
    echo -e "  ${C_CYAN}║${C_RESET}                                                      ${C_CYAN}║${C_RESET}"
    echo -e "  ${C_CYAN}╚══════════════════════════════════════════════════════╝${C_RESET}"
    echo ""
}

# ─────────────────────────────────────────────────────────────────────
#  تنظیمات پیش‌فرض (قابل تغییر با فلگ‌ها)
# ─────────────────────────────────────────────────────────────────────
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="cipher_os"
DB_MSG_NAME="cipheros_messages"
SERVE=true
PORT=8000
AUTO_MODE=false
UNINSTALL=false
INSTALL_CIPHERLOG=false
SKIP_DB=false
FORCE=false

# ─────────────────────────────────────────────────────────────────────
#  تشخیص سیستم‌عامل و پکیج‌منیجر
# ─────────────────────────────────────────────────────────────────────
detect_os() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_LIKE="${ID_LIKE:-}"
        OS_NAME="${NAME:-Linux}"
    else
        OS_ID="unknown"
        OS_LIKE=""
        OS_NAME="$(uname -s)"
    fi

    # تشخیص پکیج‌منیجر
    if command -v dnf >/dev/null 2>&1; then
        PKG_MGR="dnf"
    elif command -v yum >/dev/null 2>&1; then
        PKG_MGR="yum"
    elif command -v apt-get >/dev/null 2>&1; then
        PKG_MGR="apt"
    elif command -v pacman >/dev/null 2>&1; then
        PKG_MGR="pacman"
    elif command -v zypper >/dev/null 2>&1; then
        PKG_MGR="zypper"
    elif command -v brew >/dev/null 2>&1; then
        PKG_MGR="brew"
    else
        PKG_MGR=""
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  تجزیه فلگ‌ها
# ─────────────────────────────────────────────────────────────────────
parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --host)        DB_HOST="$2"; shift 2 ;;
            --user)        DB_USER="$2"; shift 2 ;;
            --pass)        DB_PASS="$2"; shift 2 ;;
            --name)        DB_NAME="$2"; shift 2 ;;
            --msg-name)    DB_MSG_NAME="$2"; shift 2 ;;
            --port)        PORT="$2"; shift 2 ;;
            --no-serve)    SERVE=false; shift ;;
            --auto)        AUTO_MODE=true; shift ;;
            --cipherlog)   INSTALL_CIPHERLOG=true; shift ;;
            --skip-db)     SKIP_DB=true; shift ;;
            --force)       FORCE=true; shift ;;
            --uninstall)   UNINSTALL=true; shift ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                error "گزینه ناشناخته: $1"
                echo "  برای راهنما اجرا کنید: ./install.sh --help"
                exit 1
                ;;
        esac
    done
}

show_help() {
    cat << "HELP"

  ⚡  Cipher OS  —  Universal Installer
  ════════════════════════════════════════════════════

  نحوه استفاده:
    ./install.sh [گزینه‌ها...]

  گزینه‌ها:
    --host <addr>      آدرس دیتابیس              [پیش‌فرض: localhost]
    --user <user>      نام کاربری دیتابیس         [پیش‌فرض: root]
    --pass <pass>      رمز عبور دیتابیس           [پیش‌فرض: خالی]
    --name <db>        نام دیتابیس اصلی           [پیش‌فرض: cipher_os]
    --msg-name <db>    نام دیتابیس پیام‌رسان       [پیش‌فرض: cipheros_messages]
    --port <n>         پورت سرور PHP              [پیش‌فرض: 8000]
    --no-serve         اجرا نکردن سرور داخلی
    --auto             نصب کاملاً خودکار (با پیش‌فرض‌ها)
    --cipherlog        نصب ماژول وبلاگ (CipherLog) هم انجام شود
    --skip-db          رد کردن مرحله ساخت دیتابیس
    --force            نصب مجدد حتی اگر قبلاً نصب شده
    --uninstall        حذف کامل نصب

    -h, --help         نمایش این راهنما

  مثال‌ها:
    ./install.sh                                    # نصب تعاملی
    ./install.sh --auto                             # کاملاً خودکار
    ./install.sh --user admin --pass secret123      # با اعتبارنامه مشخص
    ./install.sh --cipherlog --port 9000            # با وبلاگ، پورت 9000

  ════════════════════════════════════════════════════

HELP
}

# ─────────────────────────────────────────────────────────────────────
#  تابع حذف
# ─────────────────────────────────────────────────────────────────────
do_uninstall() {
    banner
    echo -e "  ${C_RED}${C_BOLD}حذف Cipher OS${C_RESET}"
    echo -e "  ${C_DIM}──────────────────────────────────────────────────${C_RESET}"

    if [[ "$AUTO_MODE" != true ]]; then
        read -rp $'\n  '"${C_YELLOW}آیا از حذف کامل نصب مطمئن هستید؟ [y/N]: ${C_RESET}" confirm
        if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
            info "حذف لغو شد."
            exit 0
        fi
    fi

    echo ""
    info "حذف فایل‌های کانفیگ تولیدشده..."

    local removed=0
    for f in \
        "$SCRIPT_DIR/cipher-core/.dbconfig.php" \
        "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" \
        "$SCRIPT_DIR/cipherlog/config.php"; do
        if [[ -f "$f" ]]; then
            rm -f "$f"
            ok "حذف شد: $(basename "$f")"
            removed=$((removed + 1))
        fi
    done

    # حذف دیتابیس
    if [[ "$SKIP_DB" != true ]] && command -v php >/dev/null 2>&1 && php -m 2>/dev/null | grep -qi mysqli; then
        echo ""
        if [[ "$AUTO_MODE" == true ]] || [[ "$FORCE" == true ]]; then
            local drop_confirm="y"
        else
            read -rp $'  '"${C_YELLOW}دیتابیس‌ها هم حذف شوند؟ [y/N]: ${C_RESET}" drop_confirm
        fi

        if [[ "$drop_confirm" =~ ^[Yy]$ ]]; then
            php -r "
                \$conf = [
                    'host' => '$DB_HOST',
                    'user' => '$DB_USER',
                    'pass' => '$DB_PASS',
                    'names' => ['$DB_NAME', '$DB_MSG_NAME']
                ];
                mysqli_report(MYSQLI_REPORT_OFF);
                \$conn = @mysqli_connect(\$conf['host'], \$conf['user'], \$conf['pass']);
                if (!\$conn) { echo '  ⚠ اتصال به MySQL ناموفق — دیتابیس حذف نشد\n'; exit; }
                foreach (\$conf['names'] as \$db) {
                    @mysqli_query(\$conn, 'DROP DATABASE IF EXISTS \`' . \$db . '\`');
                    echo \"  ✔ دیتابیس حذف شد: \$db\n\";
                }
            " || warn "حذف دیتابیس با خطا مواجه شد (شاید قبلاً حذف شده)"
        fi
    fi

    # بازگردانی users_schema.sql به حالت اولیه
    if [[ -f "$SCRIPT_DIR/cipher-core/users_schema.sql" ]]; then
        if git -C "$SCRIPT_DIR" checkout -- cipher-core/users_schema.sql 2>/dev/null; then
            ok "بازگردانی users_schema.sql"
        fi
    fi

    echo ""
    echo -e "  ${C_GREEN}${C_BOLD}✔ حذف کامل شد.${C_RESET}"
    echo -e "  ${C_DIM}فایل‌های پروژه دست‌نخورده باقی ماندند.${C_RESET}"
    echo ""
    exit 0
}

# ─────────────────────────────────────────────────────────────────────
#  بررسی و نصب خودکار وابستگی‌ها
# ─────────────────────────────────────────────────────────────────────
install_php() {
    local php_pkgs=""
    case "$PKG_MGR" in
        dnf|yum)  php_pkgs="php-cli php-mysqlnd php-mbstring php-json php-xml" ;;
        apt)      php_pkgs="php-cli php-mysql php-mbstring php-xml" ;;
        pacman)   php_pkgs="php php-mysqlnd" ;;
        zypper)   php_pkgs="php-cli php-mysql" ;;
        brew)     php_pkgs="php" ;;
        *)
            error "نمی‌توانم PHP را به‌صورت خودکار نصب کنم."
            echo ""
            echo "  لطفاً دستی نصب کنید:"
            echo "    Fedora/RHEL  : sudo dnf install php-cli php-mysqlnd"
            echo "    Debian/Ubuntu: sudo apt install php-cli php-mysql"
            echo "    Arch         : sudo pacman -S php"
            echo "    macOS        : brew install php"
            exit 1
            ;;
    esac

    warn "PHP نصب نیست. تلاش برای نصب خودکار با $PKG_MGR..."
    info "بسته‌ها: $php_pkgs"
    echo ""

    if sudo "$PKG_MGR" install -y $php_pkgs; then
        ok "PHP با موفقیت نصب شد"
    else
        error "نصب PHP ناموفق بود. لطفاً دستی نصب کنید."
        exit 1
    fi
}

check_requirements() {
    local missing=()

    info "بررسی PHP..."
    if ! command -v php >/dev/null 2>&1; then
        if [[ -n "$PKG_MGR" ]]; then
            install_php
        else
            error "PHP نصب نیست و پکیج‌منیجر تشخیص داده نشد."
            exit 1
        fi
    fi
    local php_ver
    php_ver=$(php -r 'echo PHP_VERSION;')
    ok "PHP $php_ver"

    # بررسی نسخه PHP ≥ 8.0
    local php_major_minor
    php_major_minor=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
    if ! php -r "exit(version_compare('$php_major_minor', '8.0', '>=') ? 0 : 1);"; then
        error "PHP ≥ 8.0 لازم است، نسخه شما: $php_ver"
        exit 1
    fi

    info "بررسی پسوند mysqli..."
    if ! php -m 2>/dev/null | grep -qi mysqli; then
        warn "پسوند mysqli نصب نیست."
        if [[ -n "$PKG_MGR" ]]; then
            local ext_pkg
            case "$PKG_MGR" in
                dnf|yum)  ext_pkg="php-mysqlnd" ;;
                apt)      ext_pkg="php-mysql" ;;
                pacman)   ext_pkg="php-sqlite" ;;  # pacman معمولاً mysql را دارد
                *)        ext_pkg="php-mysql" ;;
            esac
            info "نصب $ext_pkg..."
            sudo "$PKG_MGR" install -y "$ext_pkg" 2>/dev/null || true
            if php -m 2>/dev/null | grep -qi mysqli; then
                ok "mysqli نصب شد"
            else
                error "نمی‌توانم mysqli را نصب کنم. لطفاً دستی نصب کنید."
                exit 1
            fi
        else
            error "پسوند mysqli لازم است."
            exit 1
        fi
    else
        ok "mysqli فعال است"
    fi

    info "بررسی MySQL/MariaDB..."
    local mysql_found=false
    if command -v mysql >/dev/null 2>&1; then
        ok "MySQL client یافت شد"
        mysql_found=true
    elif command -v mariadb >/dev/null 2>&1; then
        ok "MariaDB client یافت شد"
        mysql_found=true
    else
        warn "MySQL/MariaDB کلاینت یافت نشد."
        echo -e "  ${C_DIM}اسکریپت از طریق PHP تلاش می‌کند، اما سرویس MySQL باید در حال اجرا باشد.${C_RESET}"
    fi

    # بررسی سرویس MySQL در حال اجرا
    if command -v systemctl >/dev/null 2>&1; then
        if systemctl is-active --quiet mysqld 2>/dev/null || \
           systemctl is-active --quiet mariadb 2>/dev/null || \
           systemctl is-active --quiet mysql 2>/dev/null; then
            ok "سرویس MySQL/MariaDB در حال اجراست"
        else
            warn "سرویس MySQL/MariaDB به‌نظر می‌رسد غیرفعال است."
            if [[ "$AUTO_MODE" != true ]]; then
                read -rp "  تلاش برای روشن کردن سرویس؟ [Y/n]: " svc_choice
                if [[ "${svc_choice:-Y}" =~ ^[Yy]$ ]]; then
                    for svc in mysqld mariadb mysql; do
                        if systemctl list-unit-files "${svc}.service" 2>/dev/null | grep -q "$svc"; then
                            sudo systemctl start "$svc" 2>/dev/null && sudo systemctl enable "$svc" 2>/dev/null && break
                        fi
                    done
                    if systemctl is-active --quiet mysqld 2>/dev/null || \
                       systemctl is-active --quiet mariadb 2>/dev/null || \
                       systemctl is-active --quiet mysql 2>/dev/null; then
                        ok "سرویس روشن شد"
                    else
                        warn "نمی‌توانم سرویس را روشن کنم. ادامه می‌دهیم..."
                    fi
                fi
            fi
        fi
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  بررسی اتصال دیتابیس
# ─────────────────────────────────────────────────────────────────────
verify_db_connection() {
    info "تست اتصال به دیتابیس ($DB_HOST)..."
    local result
    result=$(php -r "
        mysqli_report(MYSQLI_REPORT_OFF);
        \$conn = @mysqli_connect('$DB_HOST', '$DB_USER', '$DB_PASS');
        if (!\$conn) {
            echo 'FAIL:' . mysqli_connect_error();
            exit(1);
        }
        echo 'OK';
        \$ver = mysqli_get_server_info(\$conn);
        echo ':' . \$ver;
    " 2>&1) || true

    if [[ "$result" == OK:* ]]; then
        ok "اتصال برقرار شد — ${result#OK:}"
        return 0
    elif [[ "$result" == FAIL:* ]]; then
        error "اتصال ناموفق: ${result#FAIL:}"
        return 1
    else
        error "اتصال ناموفق: $result"
        return 1
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  پیکربندی دیتابیس
# ─────────────────────────────────────────────────────────────────────
collect_db_info() {
    if [[ "$AUTO_MODE" == true ]]; then
        info "حالت خودکار — استفاده از پیش‌فرض‌ها"
        return
    fi

    echo ""
    echo -e "  ${C_BOLD}پیکربندی دیتابیس${C_RESET} ${C_DIM}(Enter برای پیش‌فرض)${C_RESET}"
    echo -e "  ${C_DIM}──────────────────────────────────────────────────${C_RESET}"

    local input
    read -rp "  آدرس سرور        [$DB_HOST]: " input && DB_HOST="${input:-$DB_HOST}"
    read -rp "  نام کاربری       [$DB_USER]: " input && DB_USER="${input:-$DB_USER}"

    # رمز عبور پنهان
    read -rsp "  رمز عبور         (پنهان، Enter برای خالی): " DB_PASS_INPUT
    echo ""
    [[ -n "$DB_PASS_INPUT" ]] && DB_PASS="$DB_PASS_INPUT"

    read -rp "  نام دیتابیس اصلی [$DB_NAME]: " input && DB_NAME="${input:-$DB_NAME}"
}

write_db_config() {
    info "نوشتن cipher-core/.dbconfig.php ..."

    # Escape single quotes in password for PHP
    local safe_pass
    safe_pass="${DB_PASS//\'/\\\'}"

    cat > "$SCRIPT_DIR/cipher-core/.dbconfig.php" << PHP
<?php
// Auto-generated by install.sh on $(date '+%Y-%m-%d %H:%M:%S')
// ⚠️ این فایل را در مخزن عمومی commit نکنید.
return [
    'host' => '${DB_HOST}',
    'user' => '${DB_USER}',
    'pass' => '${safe_pass}',
    'name' => '${DB_NAME}',
];
PHP

    chmod 600 "$SCRIPT_DIR/cipher-core/.dbconfig.php" 2>/dev/null || true
    ok "cipher-core/.dbconfig.php نوشته شد (مجوز 600)"

    # config ماژول پیام‌رسان
    info "نوشتن cipher-message/config.php ..."
    cat > "$SCRIPT_DIR/cipher-message/config.php" << PHP
<?php
// cipher-message/config.php — generated by install.sh
define('DB_HOST', '${DB_HOST}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${safe_pass}');
define('DB_NAME', '${DB_MSG_NAME}');

try {
    \$pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException \$e) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . \$e->getMessage()]));
}

function updateHeartbeat(\$pdo, \$username) {
    \$stmt = \$pdo->prepare("INSERT INTO users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
    \$stmt->execute([\$username]);
}
?>
PHP
    ok "cipher-message/config.php نوشته شد"
}

# ─────────────────────────────────────────────────────────────────────
#  تولید رمز و راز امنیتی
# ─────────────────────────────────────────────────────────────────────
generate_credentials() {
    info "تولید رمز امن تصادفی برای ادمین..."
    ADMIN_PASS=$(php -r 'echo bin2hex(random_bytes(6));')
    ADMIN_HASH=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);")
    ok "رمز ادمین تولید شد"

    info "تولید راز پنل مدیریت (admin.php)..."
    PANEL_SECRET=$(php -r 'echo bin2hex(random_bytes(8));')
    cat > "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" << PHP
<?php
// Auto-generated by install.sh on $(date '+%Y-%m-%d %H:%M:%S')
// ⚠️ این فایل را در مخزن عمومی commit نکنید.
return '${PANEL_SECRET}';
PHP
    chmod 600 "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" 2>/dev/null || true
    ok "راز پنل مدیریت تولید شد (مجوز 600)"
}

# ─────────────────────────────────────────────────────────────────────
#  وصله کردن users_schema.sql با هش تازه
# ─────────────────────────────────────────────────────────────────────
patch_user_schema() {
    info "به‌روزرسانی users_schema.sql با هش رمز جدید..."

    local safe_hash
    safe_hash="${ADMIN_HASH//\'/\\\'}"

    # بازگردانی فایل از git قبل از وصله (در صورت نصب مجدد)
    git -C "$SCRIPT_DIR" checkout -- cipher-core/users_schema.sql 2>/dev/null || true

    php -r '
$file = $argv[1];
$hash = $argv[2];
$sql = file_get_contents($file);
// جایگزینی خط INSERT برای ادمین
$sql = preg_replace(
    "/INSERT IGNORE INTO users \(username, password_hash, role\)\s*VALUES \(\x27admin\x27, \x27[^\x27]*\x27, \x27admin\x27\);/",
    "INSERT IGNORE INTO users (username, password_hash, role)\nVALUES (\x27admin\x27, \x27" . $hash . "\x27, \x27admin\x27);",
    $sql
);
file_put_contents($file, $sql);
' "$SCRIPT_DIR/cipher-core/users_schema.sql" "$safe_hash"
    ok "هش رمز ادمین در schema جایگزین شد"
}

# ─────────────────────────────────────────────────────────────────────
#  اجرای نصب‌کننده PHP (ساخت دیتابیس و جداول)
# ─────────────────────────────────────────────────────────────────────
run_php_installer() {
    info "ساخت دیتابیس‌ها و جداول..."

    local log_file="/tmp/cipheros_install_$$.log"

    if php "$SCRIPT_DIR/install.php" > "$log_file" 2>&1; then
        ok "دیتابیس‌ها و جداول ساخته شدند"
        # استخراج خلاصه موفقیت
        grep -oE '✅[^<]*' "$log_file" 2>/dev/null | sed 's/<[^>]*>//g' | while read -r line; do
            echo -e "     ${C_GREEN}$line${C_RESET}"
        done
    else
        error "نصب‌کننده PHP با خطا مواجه شد:"
        echo ""
        echo -e "  ${C_DIM}─── لاگ خطا ───${C_RESET}"
        cat "$log_file" | sed 's/^/  /'
        echo -e "  ${C_DIM}──────────────${C_RESET}"
        rm -f "$log_file"
        exit 1
    fi
    rm -f "$log_file"
}

# ─────────────────────────────────────────────────────────────────────
#  ساخت دیتابیس پیام‌رسان (cipher-message)
# ─────────────────────────────────────────────────────────────────────
create_message_db() {
    info "ساخت دیتابیس پیام‌رسان ($DB_MSG_NAME)..."
    php -r "
        mysqli_report(MYSQLI_REPORT_OFF);
        \$conn = @mysqli_connect('$DB_HOST', '$DB_USER', '$DB_PASS');
        if (!\$conn) { echo 'warn'; exit; }
        mysqli_query(\$conn, 'CREATE DATABASE IF NOT EXISTS \`$DB_MSG_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        mysqli_select_db(\$conn, '$DB_MSG_NAME');
        mysqli_query(\$conn, \"CREATE TABLE IF NOT EXISTS users (
            username VARCHAR(32) PRIMARY KEY,
            last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_online TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4\");
        echo 'ok';
    " 2>/dev/null | {
        read -r res
        case "$res" in
            ok)   ok "دیتابیس پیام‌رسان ساخته شد" ;;
            warn) warn "نمی‌توانم دیتابیس پیام‌رسان بسازم — اتصال ناموفق" ;;
            *)    warn "نمی‌توانم دیتابیس پیام‌رسان بسازم" ;;
        esac
    }
}

# ─────────────────────────────────────────────────────────────────────
#  نصب ماژول CipherLog (وبلاگ)
# ─────────────────────────────────────────────────────────────────────
install_cipherlog() {
    info "نصب ماژول CipherLog (وبلاگ)..."

    local cl_pass
    cl_pass=$(php -r 'echo bin2hex(random_bytes(6));')

    # ارسال POST به install.php از طریق PHP-CLI (شبیه‌سازی فرم)
    php -r "
        \$url = 'http://localhost:$PORT/cipherlog/install.php';
        \$data = [
            'db_host'     => '$DB_HOST',
            'db_name'     => 'cipherlog',
            'db_user'     => '$DB_USER',
            'db_pass'     => '${DB_PASS//\'/\\\'}',
            'admin_user'  => 'admin',
            'admin_pass'  => '$cl_pass',
            'admin_email' => 'admin@cipherlog.local',
            'blog_name'   => 'CipherLog',
            'blog_tagline'=> 'Linux & Network',
            'blog_url'    => 'http://localhost:$PORT/cipherlog',
        ];

        // مستقیماً با PDO نصب کن (بدون HTTP)
        try {
            \$pdo = new PDO(
                'mysql:host=' . \$data['db_host'] . ';charset=utf8mb4',
                \$data['db_user'],
                \$data['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`' . \$data['db_name'] . '\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            \$pdo->exec('USE \`' . \$data['db_name'] . '\`');
            echo 'ok';
        } catch (Exception \$e) {
            echo 'fail:' . \$e->getMessage();
        }
    " 2>/dev/null | {
        read -r res
        case "$res" in
            ok)
                ok "دیتابیس CipherLog ساخته شد"
                # اجرای install.php از طریق CLI با POST شبیه‌سازی
                info "اجرای نصب‌کننده وبلاگ..."
                echo ""
                echo -e "     ${C_YELLOW}⚠ CipherLog از طریق مرورگر کامل نصب می‌شود:${C_RESET}"
                echo -e "     ${C_DIM}http://localhost:$PORT/cipherlog/install.php${C_RESET}"
                echo -e "     ${C_DIM}رمز پیشنهادی ادمین وبلاگ: $cl_pass${C_RESET}"
                ;;
            *)
                warn "نمی‌توانم دیتابیس CipherLog بسازم: $res"
                ;;
        esac
    }
}

# ─────────────────────────────────────────────────────────────────────
#  حذف install.php برای امنیت
# ─────────────────────────────────────────────────────────────────────
cleanup_installer() {
    if [[ "$AUTO_MODE" == true ]] || [[ "$FORCE" == true ]]; then
        rm -f "$SCRIPT_DIR/install.php"
        ok "install.php حذف شد (امنیت)"
        return
    fi

    echo ""
    read -rp "  حذف install.php برای امنیت؟ [Y/n]: " del_choice
    if [[ "${del_choice:-Y}" =~ ^[Yy]$ ]]; then
        rm -f "$SCRIPT_DIR/install.php"
        ok "install.php حذف شد"
    else
        warn "حتماً قبل از انتشار، install.php را دستی حذف کنید!"
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  تنظیم مجوز پوشه‌ها
# ─────────────────────────────────────────────────────────────────────
set_permissions() {
    info "تنظیم مجوز پوشه‌های آپلود..."

    find "$SCRIPT_DIR" -type d -iname "uploads" -exec chmod 755 {} \; 2>/dev/null || true
    find "$SCRIPT_DIR" -type d -iname "data"    -exec chmod 755 {} \; 2>/dev/null || true
    find "$SCRIPT_DIR" -type d -iname "cache"   -exec chmod 755 {} \; 2>/dev/null || true

    # فایل‌های JSON داده‌ها
    find "$SCRIPT_DIR" -name "*.json" -path "*/data/*" -exec chmod 664 {} \; 2>/dev/null || true

    # فایل‌های مخفی حساس
    chmod 600 "$SCRIPT_DIR/cipher-core/.dbconfig.php" 2>/dev/null || true
    chmod 600 "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" 2>/dev/null || true

    ok "مجوزها تنظیم شدند"
}

# ─────────────────────────────────────────────────────────────────────
#  ساختن/به‌روزرسانی .gitignore
# ─────────────────────────────────────────────────────────────────────
ensure_gitignore() {
    info "بررسی .gitignore..."
    local gi="$SCRIPT_DIR/.gitignore"
    touch "$gi"

    local entries=(
        "cipher-core/.dbconfig.php"
        "cipher-core/.admin_panel_secret.php"
        "cipher-message/config.php"
        "cipherlog/config.php"
    )

    local added=0
    for entry in "${entries[@]}"; do
        if ! grep -qxF "$entry" "$gi"; then
            echo "$entry" >> "$gi"
            added=$((added + 1))
        fi
    done

    if [[ $added -gt 0 ]]; then
        ok ".gitignore به‌روزرسانی شد ($added مورد اضافه شد)"
    else
        ok ".gitignore از قبل کامل بود"
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  نمایش اطلاعات نهایی و راه‌اندازی سرور
# ─────────────────────────────────────────────────────────────────────
show_summary() {
    local host="localhost"
    echo ""
    echo -e "  ${C_GREEN}${C_BOLD}╔══════════════════════════════════════════════════════╗${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}   ${C_BOLD}✅  نصب با موفقیت کامل شد!${C_RESET}                          ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}╠══════════════════════════════════════════════════════╣${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}                                                      ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_BOLD}🔗 آدرس ورود:${C_RESET}      http://${host}:${PORT}/login.php    ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_BOLD}👤 نام کاربری:${C_RESET}     admin                             ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_BOLD}🔑 رمز عبور:${C_RESET}       ${C_YELLOW}${ADMIN_PASS}${C_RESET}                  ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_DIM}(این رمز فقط یک‌بار نمایش داده می‌شود)${C_RESET}       ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}                                                      ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_BOLD}🛠 پنل مدیریت:${C_RESET}    http://${host}:${PORT}/admin.php  ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}  ${C_BOLD}🔑 رمز پنل:${C_RESET}       ${C_YELLOW}${PANEL_SECRET}${C_RESET}                  ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}║${C_RESET}                                                      ${C_GREEN}${C_BOLD}║${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}╚══════════════════════════════════════════════════════╝${C_RESET}"
    echo ""
    echo -e "  ${C_RED}⚠  پس از اولین ورود، حتماً رمز عبور را تغییر دهید!${C_RESET}"
    echo ""
}

start_server() {
    if [[ "$SERVE" != true ]]; then
        return
    fi

    echo ""
    if [[ "$AUTO_MODE" == true ]] || [[ "$FORCE" == true ]]; then
        local serve_choice="Y"
    else
        read -rp "  راه‌اندازی سرور PHP روی پورت $PORT؟ [Y/n]: " serve_choice
    fi

    if [[ "${serve_choice:-Y}" =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "  ${C_CYAN}${C_BOLD}🚀 سرور در حال اجراست...${C_RESET}"
        echo -e "  ${C_DIM}مرورگر را باز کنید: http://localhost:$PORT/login.php${C_RESET}"
        echo -e "  ${C_DIM}برای توقف: Ctrl+C${C_RESET}"
        echo -e "  ${C_DIM}──────────────────────────────────────────────────${C_RESET}"
        echo ""

        # باز کردن مرورگر به‌صورت خودکار در صورت امکان
        (sleep 1
         if   command -v xdg-open >/dev/null 2>&1; then xdg-open "http://localhost:$PORT/login.php" >/dev/null 2>&1
         elif command -v open      >/dev/null 2>&1; then open "http://localhost:$PORT/login.php" >/dev/null 2>&1
         fi
        ) &

        php -S "localhost:$PORT" -t "$SCRIPT_DIR"
    else
        echo ""
        info "سرور اجرا نشد. برای اجرای دستی:"
        echo -e "     ${C_DIM}php -S localhost:$PORT -t \"$SCRIPT_DIR\"${C_RESET}"
    fi
}

# ─────────────────────────────────────────────────────────────────────
#  تابع پاکسازی هنگام خطا
# ─────────────────────────────────────────────────────────────────────
cleanup_on_fail() {
    echo ""
    warn "نصب ناموفق بود. فایل‌های تولیدشده:"
    for f in \
        "$SCRIPT_DIR/cipher-core/.dbconfig.php" \
        "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php"; do
        [[ -f "$f" ]] && echo -e "     ${C_DIM}$f${C_RESET}"
    done
    echo -e "  ${C_DIM}می‌توانید با ./install.sh --uninstall همه چیز را پاک کنید.${C_RESET}"
}

# ═════════════════════════════════════════════════════════════════════
#  جریان اصلی نصب
# ═════════════════════════════════════════════════════════════════════
main() {
    parse_args "$@"
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    cd "$SCRIPT_DIR"

    # ── حالت حذف ──
    if [[ "$UNINSTALL" == true ]]; then
        detect_os
        do_uninstall
    fi

    banner
    detect_os

    # ── نمایش اطلاعات سیستم ──
    echo -e "  ${C_DIM}سیستم‌عامل:${C_RESET}  $OS_NAME"
    echo -e "  ${C_DIM}پکیج‌منیجر:${C_RESET}  ${PKG_MGR:-نامشخص}"
    echo -e "  ${C_DIM}مسیر:${C_RESET}       $SCRIPT_DIR"
    echo ""

    # ── بررسی نصب قبلی ──
    if [[ -f "$SCRIPT_DIR/cipher-core/.dbconfig.php" && "$FORCE" != true ]]; then
        warn "نصب قبلی تشخیص داده شد (cipher-core/.dbconfig.php وجود دارد)."
        echo ""
        if [[ "$AUTO_MODE" != true ]]; then
            read -rp "  نصب مجدد روی نسخه فعلی؟ [y/N]: " reinstall
            if [[ ! "$reinstall" =~ ^[Yy]$ ]]; then
                info "نصب لغو شد."
                echo -e "  ${C_DIM}برای حذف کامل: ./install.sh --uninstall${C_RESET}"
                exit 0
            fi
        fi
    fi

    # ── STEP 1: بررسی نیازمندی‌ها ──
    step "بررسی و نصب نیازمندی‌ها"
    check_requirements

    # ── جمع‌آوری اطلاعات دیتابیس ──
    step "پیکربندی دیتابیس"
    collect_db_info

    # تست اتصال
    local max_retries=3
    local attempt=1
    while [[ $attempt -le $max_retries ]]; do
        if verify_db_connection; then
            break
        fi
        if [[ $attempt -eq $max_retries ]] || [[ "$AUTO_MODE" == true ]]; then
            error "نمی‌توان به دیتابیس متصل شد بعد از $max_retries تلاش."
            echo ""
            echo -e "  ${C_BOLD}راه‌حل‌ها:${C_RESET}"
            echo -e "  ${C_CYAN}▸${C_RESET} مطمئن شوید MySQL/MariaDB در حال اجراست:"
            echo -e "     ${C_DIM}sudo systemctl start mariadb${C_RESET}"
            echo -e "     ${C_DIM}sudo systemctl start mysqld${C_RESET}"
            echo -e "  ${C_CYAN}▸${C_RESET} یا از XAMPP استفاده کنید."
            echo ""
            exit 1
        fi
        warn "تلاش مجدد ($attempt/$max_retries)..."
        attempt=$((attempt + 1))
        echo ""
        collect_db_info
    done

    # ── STEP 3: نوشتن کانفیگ ──
    step "نوشتن فایل‌های پیکربندی"
    write_db_config
    ensure_gitignore

    # ── STEP 4: تولید اعتبارنامه‌ها ──
    step "تولید رمزهای امنیتی"
    generate_credentials

    # ── وصله کردن schema ──
    patch_user_schema

    # ── STEP 5: ساخت دیتابیس و جداول ──
    step "ساخت دیتابیس و جداول"
    if [[ "$SKIP_DB" != true ]]; then
        run_php_installer
        create_message_db
    else
        warn "مرحله ساخت دیتابیس رد شد (--skip-db)"
    fi

    # ── نصب CipherLog در صورت درخواست ──
    if [[ "$INSTALL_CIPHERLOG" == true ]]; then
        echo ""
        info "نصب ماژول اضافی: CipherLog (وبلاگ)"
        install_cipherlog
    fi

    # ── STEP 6: پاکسازی و امنیت ──
    step "امنیت و پاکسازی"
    cleanup_installer
    set_permissions

    # ── STEP 7: نمایش نتیجه ──
    step "تکمیل نصب"
    show_summary
    start_server
}

main "$@"
