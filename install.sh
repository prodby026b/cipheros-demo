#!/usr/bin/env bash
#
#  ==========================================================================
#   CIPHER OS  —  Universal Installer  (Demo Edition)
#  ==========================================================================
#   Auto-detects and installs PHP / MySQL, builds databases, generates
#   secure credentials, and launches the built-in PHP server.
#  --------------------------------------------------------------------------
#
#   Usage:
#     ./install.sh                            # Interactive — full setup
#     ./install.sh --auto                     # Fully automatic (defaults)
#     ./install.sh --host localhost --user root --pass "" --name cipher_os
#     ./install.sh --no-serve --port 8080     # Don't start server
#     ./install.sh --uninstall                # Remove installation
#     ./install.sh --help
#
#  ==========================================================================

# NOTE: We do NOT use `set -e` because it causes fragile exits when
#       non-critical commands (DB probes, optional modules) fail.
#       Instead, every critical step checks its own exit code explicitly.
set -uo pipefail
IFS=$'\n\t'

# ─────────────────────────────────────────────────────────────────────────
#  Colors & helpers
# ─────────────────────────────────────────────────────────────────────────
if [[ -t 1 ]]; then
    C_RESET='\033[0m'
    C_CYAN='\033[0;36m'
    C_GREEN='\033[0;32m'
    C_YELLOW='\033[1;33m'
    C_RED='\033[0;31m'
    C_BOLD='\033[1m'
    C_MAGENTA='\033[0;35m'
    C_DIM='\033[2m'
    C_BG_CYAN='\033[46m\033[30m'
else
    C_RESET=''; C_CYAN=''; C_GREEN=''; C_YELLOW=''; C_RED=''
    C_BOLD=''; C_MAGENTA=''; C_DIM=''; C_BG_CYAN=''
fi

info()  { echo -e "  ${C_CYAN}>${C_RESET} $1"; }
ok()    { echo -e "  ${C_GREEN}OK${C_RESET}  ${C_DIM}$1${C_RESET}"; }
warn()  { echo -e "  ${C_YELLOW}!!${C_RESET}  ${C_YELLOW}$1${C_RESET}"; }
error() { echo -e "  ${C_RED}XX${C_RESET}  ${C_RED}${C_BOLD}$1${C_RESET}"; }

declare -i STEP_NUM=0
declare -i TOTAL_STEPS=6

step() {
    STEP_NUM+=1
    echo ""
    echo -e "  ${C_BG_CYAN}${C_BOLD} STEP ${STEP_NUM}/${TOTAL_STEPS} ${C_RESET}  ${C_BOLD}$1${C_RESET}"
    echo -e "  ${C_DIM}----------------------------------------------------${C_RESET}"
}

banner() {
    echo ""
    echo -e "  ${C_CYAN}======================================================${C_RESET}"
    echo -e "  ${C_CYAN}|${C_RESET}                                                    ${C_CYAN}|${C_RESET}"
    echo -e "  ${C_CYAN}|${C_RESET}   ${C_BOLD}CIPHER OS${C_RESET}  ${C_DIM}--${C_RESET}  ${C_MAGENTA}Demo Edition${C_RESET}               ${C_CYAN}|${C_RESET}"
    echo -e "  ${C_CYAN}|${C_RESET}   ${C_DIM}Universal Installer${C_RESET}                              ${C_CYAN}|${C_RESET}"
    echo -e "  ${C_CYAN}|${C_RESET}                                                    ${C_CYAN}|${C_RESET}"
    echo -e "  ${C_CYAN}======================================================${C_RESET}"
    echo ""
}

# ─────────────────────────────────────────────────────────────────────────
#  Defaults (overridable via flags)
# ─────────────────────────────────────────────────────────────────────────
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
SCRIPT_DIR=""

# ─────────────────────────────────────────────────────────────────────────
#  Detect OS & package manager
# ─────────────────────────────────────────────────────────────────────────
OS_NAME="Linux"
OS_ID="unknown"
PKG_MGR=""

detect_os() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release 2>/dev/null || true
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-Linux}"
    fi

    if   command -v dnf      >/dev/null 2>&1; then PKG_MGR="dnf"
    elif command -v yum      >/dev/null 2>&1; then PKG_MGR="yum"
    elif command -v apt-get  >/dev/null 2>&1; then PKG_MGR="apt"
    elif command -v pacman   >/dev/null 2>&1; then PKG_MGR="pacman"
    elif command -v zypper   >/dev/null 2>&1; then PKG_MGR="zypper"
    elif command -v brew     >/dev/null 2>&1; then PKG_MGR="brew"
    fi
}

# ─────────────────────────────────────────────────────────────────────────
#  Argument parsing
# ─────────────────────────────────────────────────────────────────────────
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
            -h|--help)     show_help; exit 0 ;;
            *)
                error "Unknown option: $1"
                echo "  Run './install.sh --help' for usage."
                exit 1
                ;;
        esac
    done
}

show_help() {
    cat << "HELP"

  CIPHER OS -- Universal Installer
  ======================================================

  Usage:
    ./install.sh [options...]

  Options:
    --host <addr>     Database host            [default: localhost]
    --user <user>     Database username        [default: root]
    --pass <pass>     Database password        [default: empty]
    --name <db>       Main database name       [default: cipher_os]
    --msg-name <db>   Messenger database name  [default: cipheros_messages]
    --port <n>        PHP server port          [default: 8000]
    --no-serve        Don't start the PHP server
    --auto            Fully automatic (non-interactive)
    --cipherlog       Also install the blog module
    --skip-db         Skip database creation step
    --force           Reinstall even if already installed
    --uninstall       Remove the installation
    -h, --help        Show this help

  Examples:
    ./install.sh                                    # Interactive
    ./install.sh --auto                             # Fully automatic
    ./install.sh --user root --pass secret123       # Custom credentials
    ./install.sh --cipherlog --port 9000            # Blog + custom port

  ======================================================

HELP
}

# ─────────────────────────────────────────────────────────────────────────
#  Uninstall
# ─────────────────────────────────────────────────────────────────────────
do_uninstall() {
    echo -e "  ${C_RED}${C_BOLD}Uninstall Cipher OS${C_RESET}"
    echo -e "  ${C_DIM}----------------------------------------------------${C_RESET}"

    if [[ "$AUTO_MODE" != true ]]; then
        read -rp $'\n  '"${C_YELLOW}Are you sure you want to remove the installation? [y/N]: ${C_RESET}" confirm
        if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
            info "Uninstall cancelled."
            exit 0
        fi
    fi

    echo ""
    info "Removing generated config files..."

    for f in \
        "$SCRIPT_DIR/cipher-core/.dbconfig.php" \
        "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" \
        "$SCRIPT_DIR/cipher-message/config.php" \
        "$SCRIPT_DIR/cipherlog/config.php"; do
        if [[ -f "$f" ]]; then
            rm -f "$f"
            ok "Removed: $(basename "$f")"
        fi
    done

    # Optionally drop databases
    if [[ "$SKIP_DB" != true ]] && command -v php >/dev/null 2>&1; then
        echo ""
        if [[ "$AUTO_MODE" == true || "$FORCE" == true ]]; then
            drop_confirm="y"
        else
            read -rp $"  ${C_YELLOW}Also drop the databases? [y/N]: ${C_RESET}" drop_confirm || drop_confirm="n"
        fi

        if [[ "$drop_confirm" =~ ^[Yy]$ ]]; then
            local res
            res=$(php_run_drop_dbs) || res="skip"
            if [[ "$res" == "ok" ]]; then
                ok "Databases dropped"
            else
                warn "Could not drop databases (connection failed or already gone)"
            fi
        fi
    fi

    # Restore users_schema.sql from git
    if [[ -f "$SCRIPT_DIR/cipher-core/users_schema.sql" ]]; then
        git -C "$SCRIPT_DIR" checkout -- cipher-core/users_schema.sql 2>/dev/null && \
            ok "Restored users_schema.sql" || true
    fi

    echo ""
    echo -e "  ${C_GREEN}${C_BOLD}Uninstall complete.${C_RESET}"
    echo -e "  ${C_DIM}Project files are untouched.${C_RESET}"
    echo ""
    exit 0
}

# Helper: run PHP to drop databases (returns a status string on stdout)
php_run_drop_dbs() {
    # Writes a temporary PHP file to avoid quoting nightmares
    local tmp
    tmp=$(mktemp /tmp/cipheros_uninstall.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(
    getenv('CIPHER_DB_HOST'),
    getenv('CIPHER_DB_USER'),
    getenv('CIPHER_DB_PASS')
);
if (!$conn) { echo "skip"; exit; }
foreach (explode(',', getenv('CIPHER_DB_NAMES')) as $db) {
    $db = trim($db);
    if ($db === '') continue;
    @mysqli_query($conn, "DROP DATABASE IF EXISTS `$db`");
}
echo "ok";
PHPEOF
    CIPHER_DB_HOST="$DB_HOST" CIPHER_DB_USER="$DB_USER" CIPHER_DB_PASS="$DB_PASS" \
        CIPHER_DB_NAMES="$DB_NAME,$DB_MSG_NAME" php "$tmp" 2>/dev/null
    local rc=$?
    rm -f "$tmp"
    return $rc
}

# ─────────────────────────────────────────────────────────────────────────
#  Install PHP + extensions if missing
# ─────────────────────────────────────────────────────────────────────────
install_php() {
    local php_pkgs=""
    case "$PKG_MGR" in
        dnf|yum)  php_pkgs="php-cli php-mysqlnd php-mbstring php-xml" ;;
        apt)      php_pkgs="php-cli php-mysql php-mbstring php-xml" ;;
        pacman)   php_pkgs="php" ;;
        zypper)   php_pkgs="php-cli php-mysql" ;;
        brew)     php_pkgs="php" ;;
        *)
            error "Cannot auto-install PHP (no package manager detected)."
            echo ""
            echo "  Please install PHP 8.0+ manually:"
            echo "    Fedora/RHEL  : sudo dnf install php-cli php-mysqlnd"
            echo "    Debian/Ubuntu: sudo apt install php-cli php-mysql"
            echo "    Arch         : sudo pacman -S php"
            echo "    macOS        : brew install php"
            exit 1
            ;;
    esac

    warn "PHP is not installed. Attempting auto-install via $PKG_MGR..."
    info "Packages: $php_pkgs"
    echo ""
    if sudo "$PKG_MGR" install -y $php_pkgs; then
        ok "PHP installed successfully"
    else
        error "PHP installation failed. Please install it manually."
        exit 1
    fi
}

install_mysqli_ext() {
    local ext_pkg=""
    case "$PKG_MGR" in
        dnf|yum)  ext_pkg="php-mysqlnd" ;;
        apt)      ext_pkg="php-mysql" ;;
        pacman)   ext_pkg="php" ;;
        zypper)   ext_pkg="php-mysql" ;;
        brew)     ext_pkg="php" ;;
    esac
    if [[ -z "$ext_pkg" ]]; then
        error "mysqli extension is required but cannot auto-install it."
        exit 1
    fi
    info "Installing $ext_pkg..."
    sudo "$PKG_MGR" install -y "$ext_pkg" 2>/dev/null || true
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 1: Check requirements
# ─────────────────────────────────────────────────────────────────────────
check_requirements() {
    info "Checking PHP..."
    if ! command -v php >/dev/null 2>&1; then
        if [[ -n "$PKG_MGR" ]]; then
            install_php
        else
            error "PHP is not installed and no package manager was detected."
            exit 1
        fi
    fi
    local php_ver
    php_ver=$(php -r 'echo PHP_VERSION;' 2>/dev/null)
    ok "PHP $php_ver"

    # PHP >= 8.0
    if ! php -r 'exit(version_compare(PHP_VERSION, "8.0.0", ">=") ? 0 : 1);' 2>/dev/null; then
        error "PHP 8.0+ is required. You have: $php_ver"
        exit 1
    fi

    info "Checking mysqli extension..."
    if ! php -m 2>/dev/null | grep -qi '^mysqli$'; then
        warn "mysqli extension is missing."
        if [[ -n "$PKG_MGR" ]]; then
            install_mysqli_ext
            if php -m 2>/dev/null | grep -qi '^mysqli$'; then
                ok "mysqli installed and enabled"
            else
                error "Could not install mysqli. Please install php-mysqlnd / php-mysql manually."
                exit 1
            fi
        else
            error "mysqli extension is required."
            exit 1
        fi
    else
        ok "mysqli enabled"
    fi

    info "Checking MySQL/MariaDB..."
    if command -v mysql >/dev/null 2>&1; then
        ok "MySQL client found"
    elif command -v mariadb >/dev/null 2>&1; then
        ok "MariaDB client found"
    else
        warn "MySQL/MariaDB client not found in PATH."
        echo -e "  ${C_DIM}The installer will try via PHP, but the MySQL service must be running.${C_RESET}"
    fi

    # Check if MySQL service is running
    local mysql_running=false
    if command -v systemctl >/dev/null 2>&1; then
        for svc in mariadb mysqld mysql; do
            if systemctl is-active --quiet "$svc" 2>/dev/null; then
                mysql_running=true
                break
            fi
        done
    fi
    # Also check via process list (for XAMPP/MAMP/Homebrew)
    if [[ "$mysql_running" == false ]]; then
        if pgrep -x mysqld >/dev/null 2>&1 || pgrep -x mariadbd >/dev/null 2>&1; then
            mysql_running=true
        fi
    fi

    if [[ "$mysql_running" == true ]]; then
        ok "MySQL/MariaDB service is running"
    else
        warn "MySQL/MariaDB does not appear to be running."
        if [[ "$AUTO_MODE" != true ]]; then
            read -rp "  Try to start the service? [Y/n]: " svc_choice || svc_choice="Y"
            if [[ "${svc_choice:-Y}" =~ ^[Yy]$ ]]; then
                for svc in mariadb mysqld mysql; do
                    if systemctl list-unit-files "${svc}.service" 2>/dev/null | grep -q "$svc"; then
                        sudo systemctl start "$svc" 2>/dev/null && \
                            sudo systemctl enable "$svc" 2>/dev/null || true
                        break
                    fi
                done
            fi
        fi
    fi
}

# ─────────────────────────────────────────────────────────────────────────
#  DB connection test (writes a temp PHP file to avoid quoting issues)
#  Returns: 0 = connected, 1 = failed
# ─────────────────────────────────────────────────────────────────────────
verify_db_connection() {
    local tmp
    tmp=$(mktemp /tmp/cipheros_dbtest.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(
    getenv('CIPHER_DB_HOST'),
    getenv('CIPHER_DB_USER'),
    getenv('CIPHER_DB_PASS')
);
if (!$conn) {
    echo "FAIL:" . mysqli_connect_error();
    exit(1);
}
$ver = mysqli_get_server_info($conn);
echo "OK:" . $ver;
PHPEOF
    local result
    result=$(CIPHER_DB_HOST="$DB_HOST" CIPHER_DB_USER="$DB_USER" CIPHER_DB_PASS="$DB_PASS" \
        php "$tmp" 2>/dev/null) || result="FAIL:PHP error"
    rm -f "$tmp"

    case "$result" in
        OK:*)
            ok "Connected -- ${result#OK:}"
            return 0
            ;;
        FAIL:*)
            error "Connection failed: ${result#FAIL:}"
            return 1
            ;;
        *)
            error "Connection failed: $result"
            return 1
            ;;
    esac
}

# ─────────────────────────────────────────────────────────────────────────
#  Collect DB credentials (interactive)
# ─────────────────────────────────────────────────────────────────────────
collect_db_info() {
    if [[ "$AUTO_MODE" == true ]]; then
        info "Auto mode -- using defaults"
        return
    fi

    echo ""
    echo -e "  ${C_BOLD}Database Configuration${C_RESET} ${C_DIM}(press Enter for [default])${C_RESET}"
    echo -e "  ${C_DIM}----------------------------------------------------${C_RESET}"

    local input
    read -rp "  DB Host [$DB_HOST]: " input || input=""
    [[ -n "$input" ]] && DB_HOST="$input"

    read -rp "  DB User [$DB_USER]: " input || input=""
    [[ -n "$input" ]] && DB_USER="$input"

    read -rsp "  DB Password (hidden, Enter for empty): " input || input=""
    echo ""
    [[ -n "$input" ]] && DB_PASS="$input"

    read -rp "  DB Name [$DB_NAME]: " input || input=""
    [[ -n "$input" ]] && DB_NAME="$input"
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 2: Write config files
# ─────────────────────────────────────────────────────────────────────────
write_db_config() {
    # Escape single quotes for safe PHP string embedding
    local e_host e_user e_pass e_name e_msgname
    e_host="${DB_HOST//\'/\\\'}"
    e_user="${DB_USER//\'/\\\'}"
    e_pass="${DB_PASS//\'/\\\'}"
    e_name="${DB_NAME//\'/\\\'}"
    e_msgname="${DB_MSG_NAME//\'/\\\'}"

    # --- cipher-core/.dbconfig.php ---
    info "Writing cipher-core/.dbconfig.php ..."
    cat > "$SCRIPT_DIR/cipher-core/.dbconfig.php" << PHPDOC
<?php
// Auto-generated by install.sh on $(date '+%Y-%m-%d %H:%M:%S')
// WARNING: Do NOT commit this file to a public repository.
return [
    'host' => '${e_host}',
    'user' => '${e_user}',
    'pass' => '${e_pass}',
    'name' => '${e_name}',
];
PHPDOC
    chmod 600 "$SCRIPT_DIR/cipher-core/.dbconfig.php"
    ok "cipher-core/.dbconfig.php written (chmod 600)"

    # --- cipher-message/config.php ---
    info "Writing cipher-message/config.php ..."
    cat > "$SCRIPT_DIR/cipher-message/config.php" << PHPDOC
<?php
// cipher-message/config.php -- generated by install.sh
define('DB_HOST', '${e_host}');
define('DB_USER', '${e_user}');
define('DB_PASS', '${e_pass}');
define('DB_NAME', '${e_msgname}');

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
PHPDOC
    ok "cipher-message/config.php written"
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 3: Generate credentials
# ─────────────────────────────────────────────────────────────────────────
generate_credentials() {
    info "Generating secure random admin password..."
    ADMIN_PASS=$(php -r 'echo bin2hex(random_bytes(6));' 2>/dev/null)
    ADMIN_HASH=$(php -r "echo password_hash('${ADMIN_PASS}', PASSWORD_DEFAULT);" 2>/dev/null)
    ok "Admin password generated"

    info "Generating admin panel secret (admin.php)..."
    PANEL_SECRET=$(php -r 'echo bin2hex(random_bytes(8));' 2>/dev/null)

    local tmp
    tmp=$(mktemp /tmp/cipheros_secret.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
$secret = getenv('CIPHER_SECRET');
$content = "<?php\n";
$content .= "// Auto-generated by install.sh on " . date('Y-m-d H:i:s') . "\n";
$content .= "// WARNING: Do NOT commit this file to a public repository.\n";
$content .= "return '" . str_replace("'", "\\'", $secret) . "';\n";
file_put_contents(getenv('CIPHER_TARGET'), $content);
chmod(getenv('CIPHER_TARGET'), 0600);
PHPEOF
    CIPHER_SECRET="$PANEL_SECRET" CIPHER_TARGET="$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" \
        php "$tmp" 2>/dev/null
    rm -f "$tmp"
    ok "Admin panel secret generated (chmod 600)"
}

# Patch users_schema.sql with fresh hash
patch_user_schema() {
    info "Patching users_schema.sql with fresh password hash..."

    # Restore from git first (in case of reinstall)
    git -C "$SCRIPT_DIR" checkout -- cipher-core/users_schema.sql 2>/dev/null || true

    local tmp
    tmp=$(mktemp /tmp/cipheros_patch.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
$file = $argv[1];
$hash = $argv[2];
$sql = file_get_contents($file);
$pattern = "/INSERT IGNORE INTO users \(username, password_hash, role\)\s*VALUES \('admin', '[^']*', 'admin'\);/";
$replace = "INSERT IGNORE INTO users (username, password_hash, role)\nVALUES ('admin', '" . $hash . "', 'admin');";
$new = preg_replace($pattern, $replace, $sql);
if ($new === $sql && $new !== null) {
    // Fallback: try to append the INSERT if pattern didn't match
    fwrite(STDERR, "warning: pattern not matched, appending INSERT\n");
}
file_put_contents($file, $new);
PHPEOF
    php "$tmp" "$SCRIPT_DIR/cipher-core/users_schema.sql" "$ADMIN_HASH" 2>/dev/null || true
    rm -f "$tmp"
    ok "Password hash injected into schema"
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 4: Create databases and tables
# ─────────────────────────────────────────────────────────────────────────
run_php_installer() {
    info "Running database installer (creates DB + tables)..."

    local log_file="/tmp/cipheros_install_$$.log"
    if php "$SCRIPT_DIR/install.php" > "$log_file" 2>&1; then
        ok "Databases and tables created"
        # Show success summary (strip HTML)
        grep -oE '✅[^<]*' "$log_file" 2>/dev/null | sed 's/<[^>]*>//g' | while IFS= read -r line; do
            [[ -n "$line" ]] && echo -e "       ${C_GREEN}$line${C_RESET}"
        done
        rm -f "$log_file"
        return 0
    else
        error "PHP installer failed:"
        echo ""
        echo -e "  ${C_DIM}--- error log ---${C_RESET}"
        sed 's/^/  /' "$log_file"
        echo -e "  ${C_DIM}-----------------${C_RESET}"
        rm -f "$log_file"
        return 1
    fi
}

# Create the messenger database (cipher-message module)
create_message_db() {
    info "Creating messenger database ($DB_MSG_NAME)..."

    local tmp
    tmp=$(mktemp /tmp/cipheros_msgdb.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(
    getenv('CIPHER_DB_HOST'),
    getenv('CIPHER_DB_USER'),
    getenv('CIPHER_DB_PASS')
);
if (!$conn) { echo "FAIL"; exit(1); }

$db = getenv('CIPHER_DB_MSGNAME');
if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo "FAIL:" . mysqli_error($conn);
    exit(1);
}
mysqli_select_db($conn, $db);
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(32) PRIMARY KEY,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_online TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK";
PHPEOF
    local result
    result=$(CIPHER_DB_HOST="$DB_HOST" CIPHER_DB_USER="$DB_USER" CIPHER_DB_PASS="$DB_PASS" \
        CIPHER_DB_MSGNAME="$DB_MSG_NAME" php "$tmp" 2>/dev/null) || result="FAIL"
    rm -f "$tmp"

    case "$result" in
        OK)   ok "Messenger database created ($DB_MSG_NAME)" ;;
        FAIL) warn "Could not create messenger database (non-fatal, module may not work)" ;;
        FAIL:*) warn "Messenger DB error: ${result#FAIL:}" ;;
        *)    warn "Unexpected response creating messenger DB: $result" ;;
    esac
    # Non-fatal: always return 0 so the script continues
    return 0
}

# ─────────────────────────────────────────────────────────────────────────
#  Optional: Install CipherLog (blog)
# ─────────────────────────────────────────────────────────────────────────
install_cipherlog() {
    info "Setting up CipherLog (blog module)..."

    local cl_pass
    cl_pass=$(php -r 'echo bin2hex(random_bytes(6));' 2>/dev/null)

    local tmp
    tmp=$(mktemp /tmp/cipheros_cl.XXXXXX.php)
    cat > "$tmp" << 'PHPEOF'
<?php
$host = getenv('CIPHER_DB_HOST'); $user = getenv('CIPHER_DB_USER');
$pass = getenv('CIPHER_DB_PASS'); $port = getenv('CIPHER_PORT');
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `cipherlog` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "OK";
} catch (Exception $e) {
    echo "FAIL:" . $e->getMessage();
}
PHPEOF
    local result
    result=$(CIPHER_DB_HOST="$DB_HOST" CIPHER_DB_USER="$DB_USER" CIPHER_DB_PASS="$DB_PASS" \
        CIPHER_PORT="$PORT" php "$tmp" 2>/dev/null) || result="FAIL"
    rm -f "$tmp"

    case "$result" in
        OK)
            ok "CipherLog database created"
            echo ""
            echo -e "       ${C_YELLOW}!!  Complete CipherLog setup in your browser:${C_RESET}"
            echo -e "       ${C_DIM}http://localhost:$PORT/cipherlog/install.php${C_RESET}"
            echo -e "       ${C_DIM}Suggested blog admin password: $cl_pass${C_RESET}"
            ;;
        FAIL:*)
            warn "Could not create CipherLog database: ${result#FAIL:}"
            ;;
        *)
            warn "Could not create CipherLog database"
            ;;
    esac
    return 0
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 5: Cleanup & security
# ─────────────────────────────────────────────────────────────────────────
cleanup_installer() {
    if [[ "$AUTO_MODE" == true || "$FORCE" == true ]]; then
        local del_choice="Y"
    else
        echo ""
        read -rp "  Delete install.php for security? [Y/n]: " del_choice || del_choice="Y"
    fi

    if [[ "${del_choice:-Y}" =~ ^[Yy]$ ]]; then
        rm -f "$SCRIPT_DIR/install.php"
        ok "install.php removed"
    else
        warn "Remember to delete install.php before going to production!"
    fi
}

set_permissions() {
    info "Setting directory permissions..."
    find "$SCRIPT_DIR" -type d -iname "uploads" -exec chmod 755 {} \; 2>/dev/null || true
    find "$SCRIPT_DIR" -type d -iname "data"    -exec chmod 755 {} \; 2>/dev/null || true
    find "$SCRIPT_DIR" -type d -iname "cache"   -exec chmod 755 {} \; 2>/dev/null || true
    find "$SCRIPT_DIR" -name "*.json" -path "*/data/*" -exec chmod 664 {} \; 2>/dev/null || true
    chmod 600 "$SCRIPT_DIR/cipher-core/.dbconfig.php" 2>/dev/null || true
    chmod 600 "$SCRIPT_DIR/cipher-core/.admin_panel_secret.php" 2>/dev/null || true
    ok "Permissions set"
}

ensure_gitignore() {
    info "Checking .gitignore..."
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
        if ! grep -qxF "$entry" "$gi" 2>/dev/null; then
            echo "$entry" >> "$gi"
            added=$((added + 1))
        fi
    done
    if [[ $added -gt 0 ]]; then
        ok ".gitignore updated ($added entry/entries added)"
    else
        ok ".gitignore already complete"
    fi
}

# ─────────────────────────────────────────────────────────────────────────
#  Step 6: Summary & server launch
# ─────────────────────────────────────────────────────────────────────────
show_summary() {
    local host="localhost"
    echo ""
    echo -e "  ${C_GREEN}${C_BOLD}======================================================${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}|${C_RESET}   ${C_BOLD}Installation complete!${C_RESET}                              ${C_GREEN}${C_BOLD}|${C_RESET}"
    echo -e "  ${C_GREEN}${C_BOLD}======================================================${C_RESET}"
    echo ""
    echo -e "  ${C_BOLD}Login URL:${C_RESET}    http://${host}:${PORT}/login.php"
    echo -e "  ${C_BOLD}Username:${C_RESET}     admin"
    echo -e "  ${C_BOLD}Password:${C_RESET}     ${C_YELLOW}${C_BOLD}${ADMIN_PASS}${C_RESET}"
    echo -e "  ${C_DIM}(shown only once -- save it now)${C_RESET}"
    echo ""
    echo -e "  ${C_BOLD}Admin panel:${C_RESET}  http://${host}:${PORT}/admin.php"
    echo -e "  ${C_BOLD}Panel pwd:${C_RESET}    ${C_YELLOW}${C_BOLD}${PANEL_SECRET}${C_RESET}"
    echo ""
    echo -e "  ${C_RED}!!  Change your password after first login!${C_RESET}"
    echo ""
}

start_server() {
    if [[ "$SERVE" != true ]]; then
        echo ""
        info "Server not started. To run manually:"
        echo -e "     ${C_DIM}php -S localhost:$PORT -t \"$SCRIPT_DIR\"${C_RESET}"
        return
    fi

    echo ""
    local serve_choice="Y"
    if [[ "$AUTO_MODE" != true && "$FORCE" != true ]]; then
        read -rp "  Start PHP server on port $PORT? [Y/n]: " serve_choice || serve_choice="Y"
    fi

    if [[ "${serve_choice:-Y}" =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "  ${C_CYAN}${C_BOLD}Server starting...${C_RESET}"
        echo -e "  ${C_DIM}Open: http://localhost:$PORT/login.php${C_RESET}"
        echo -e "  ${C_DIM}Stop: Ctrl+C${C_RESET}"
        echo -e "  ${C_DIM}----------------------------------------------------${C_RESET}"
        echo ""

        # Try to open the browser after 1 second
        (
            sleep 1
            url="http://localhost:$PORT/login.php"
            if   command -v xdg-open >/dev/null 2>&1; then xdg-open "$url" >/dev/null 2>&1
            elif command -v open      >/dev/null 2>&1; then open "$url" >/dev/null 2>&1
            fi
        ) &

        php -S "localhost:$PORT" -t "$SCRIPT_DIR"
    else
        echo ""
        info "Server not started. To run manually:"
        echo -e "     ${C_DIM}php -S localhost:$PORT -t \"$SCRIPT_DIR\"${C_RESET}"
    fi
}

# ─────────────────────────────────────────────────────────────────────────
#  Main flow
# ─────────────────────────────────────────────────────────────────────────
main() {
    parse_args "$@"
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    cd "$SCRIPT_DIR"

    detect_os

    # Uninstall mode
    if [[ "$UNINSTALL" == true ]]; then
        banner
        do_uninstall
    fi

    banner

    # System info
    echo -e "  ${C_DIM}OS:           $OS_NAME${C_RESET}"
    echo -e "  ${C_DIM}Package mgr:  ${PKG_MGR:-none}${C_RESET}"
    echo -e "  ${C_DIM}Path:         $SCRIPT_DIR${C_RESET}"

    # Check for previous install
    if [[ -f "$SCRIPT_DIR/cipher-core/.dbconfig.php" && "$FORCE" != true ]]; then
        echo ""
        warn "Previous installation detected (cipher-core/.dbconfig.php exists)."
        if [[ "$AUTO_MODE" != true ]]; then
            read -rp "  Reinstall over the current setup? [y/N]: " reinstall || reinstall="n"
            if [[ ! "$reinstall" =~ ^[Yy]$ ]]; then
                info "Installation cancelled."
                echo -e "  ${C_DIM}To remove first: ./install.sh --uninstall${C_RESET}"
                exit 0
            fi
        fi
    fi

    # STEP 1: Requirements
    step "Check & install requirements"
    check_requirements

    # STEP 2: DB config + connection test (with retries)
    step "Configure database"
    collect_db_info

    local max_retries=3
    local attempt=1
    while [[ $attempt -le $max_retries ]]; do
        if verify_db_connection; then
            break
        fi
        if [[ $attempt -eq $max_retries || "$AUTO_MODE" == true ]]; then
            echo ""
            error "Cannot connect to database after $attempt attempt(s)."
            echo ""
            echo -e "  ${C_BOLD}Troubleshooting:${C_RESET}"
            echo -e "  ${C_CYAN}>${C_RESET} Make sure MySQL/MariaDB is running:"
            echo -e "     ${C_DIM}sudo systemctl start mariadb${C_RESET}"
            echo -e "     ${C_DIM}sudo systemctl start mysqld${C_RESET}"
            echo ""
            echo -e "  ${C_CYAN}>${C_RESET} On Fedora/RHEL, root often uses socket auth."
            echo -e "     ${C_DIM}Try connecting with: sudo mysql${C_RESET}"
            echo -e "     ${C_DIM}Or create a dedicated user:${C_RESET}"
            echo -e "     ${C_DIM}  sudo mysql -e \"CREATE USER 'cipher'@'localhost' IDENTIFIED BY 'yourpass';${C_RESET}"
            echo -e "     ${C_DIM}  sudo mysql -e \"GRANT ALL ON *.* TO 'cipher'@'localhost';\"${C_RESET}"
            echo ""
            echo -e "  ${C_CYAN}>${C_RESET} Then re-run with:"
            echo -e "     ${C_DIM}./install.sh --user cipher --pass yourpass${C_RESET}"
            echo ""
            exit 1
        fi
        warn "Retry ($attempt/$max_retries)..."
        attempt=$((attempt + 1))
        echo ""
        collect_db_info
    done

    # STEP 3: Write configs & generate credentials
    step "Write config files & generate credentials"
    write_db_config
    ensure_gitignore
    generate_credentials
    patch_user_schema

    # STEP 4: Create databases
    step "Create databases & tables"
    if [[ "$SKIP_DB" != true ]]; then
        if ! run_php_installer; then
            exit 1
        fi
        create_message_db
    else
        warn "Database creation skipped (--skip-db)"
    fi

    # Optional: CipherLog
    if [[ "$INSTALL_CIPHERLOG" == true ]]; then
        echo ""
        info "Installing optional module: CipherLog (blog)"
        install_cipherlog
    fi

    # STEP 5: Security & cleanup
    step "Security & cleanup"
    cleanup_installer
    set_permissions

    # STEP 6: Done
    step "Installation complete"
    show_summary
    start_server
}

main "$@"
