#!/usr/bin/env bash
# ============================================================
# deploy.sh — OpsPilot cPanel Production Deployment
# ============================================================
# Usage:
#   bash deploy.sh           Normal deployment (main branch)
#   bash deploy.sh --check   Dry-run status check only
# ============================================================
set -euo pipefail

PROJECT_PATH="/home/whizzweb/alphaspacepro.online"
PRODUCTION_BRANCH="main"
APP_NAME="OpsPilot"

# ---- Colors ----
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()  { echo -e "  ${GREEN}✓${NC} $1"; }
warn(){ echo -e "  ${YELLOW}⚠${NC} $1"; }
fail(){ echo -e "  ${RED}✗${NC} $1"; }

# ---- Maintenance trap ----
maintenance_off() {
    if [ -f "artisan" ]; then
        php artisan up --quiet 2>/dev/null || true
        ok "Maintenance mode disabled"
    fi
}
trap maintenance_off EXIT

# ---- Helpers ----
check_git() {
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        fail "Not a Git repository ($PROJECT_PATH)"
        exit 1
    fi
    ok "Git repository confirmed"
}

check_branch() {
    local branch
    branch=$(git rev-parse --abbrev-ref HEAD)
    if [ "$branch" != "$PRODUCTION_BRANCH" ]; then
        fail "Branch is '$branch'; must be '$PRODUCTION_BRANCH'"
        exit 1
    fi
    ok "Branch: $branch"
}

check_clean() {
    if ! git diff --quiet HEAD; then
        fail "Working tree has uncommitted changes. Commit or stash them first."
        exit 1
    fi
    if [ -n "$(git ls-files --others --exclude-standard)" ]; then
        warn "Untracked files exist (non-blocking)"
    fi
    ok "Working tree is clean"
}

check_composer() {
    if command -v composer &> /dev/null; then
        COMPOSER_CMD="composer"
    elif [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
    else
        return 1
    fi
}

check_env() {
    if [ -f ".env" ]; then
        ok ".env file exists"
    else
        fail ".env file is missing"
        exit 1
    fi
}

check_db() {
    if php artisan db:show --quiet 2>/dev/null; then
        ok "Database connection successful"
    else
        warn "Database connection failed (check .env DB_* settings)"
    fi
}

check_manifest() {
    if [ -f "public/build/manifest.json" ]; then
        ok "Vite manifest.json present"
    else
        fail "public/build/manifest.json missing — run 'npm run build'"
        exit 1
    fi
}

check_writable() {
    local dirs=("storage" "storage/logs" "storage/framework/cache" "storage/framework/sessions" "storage/framework/views" "bootstrap/cache")
    local all_ok=true
    for d in "${dirs[@]}"; do
        if [ -d "$d" ] && [ -w "$d" ]; then
            ok "Writable: $d/"
        else
            warn "Not writable: $d/  (chmod -R 775 $d)"
            all_ok=false
        fi
    done
    $all_ok || warn "Fix permissions before production deployment"
}

php_version() {
    php -r "echo PHP_VERSION;" 2>/dev/null || echo "unknown"
}

git_remote_status() {
    git fetch origin --quiet 2>/dev/null || true
    local behind
    behind=$(git rev-list --count "HEAD..origin/$PRODUCTION_BRANCH" 2>/dev/null || echo "0")
    if [ "$behind" -gt 0 ]; then
        warn "Local is $behind commit(s) behind origin/$PRODUCTION_BRANCH"
    else
        ok "Up to date with origin/$PRODUCTION_BRANCH"
    fi
}

print_summary() {
    echo ""
    echo -e "${CYAN}============================================${NC}"
    echo -e "${CYAN}  DEPLOYMENT SUMMARY${NC}"
    echo -e "${CYAN}============================================${NC}"
    echo "  Commit:    $(git rev-parse --short HEAD 2>/dev/null || echo 'unknown')"
    echo "  Branch:    $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo 'unknown')"
    echo "  PHP:       $(php_version)"
    echo "  App Env:   $(php -r 'echo config("app.env");' 2>/dev/null || echo 'unknown')"
    echo "  Migrated:  $(php artisan migrate:status 2>/dev/null | tail -1 || echo 'unknown')"
    echo -e "${CYAN}============================================${NC}"
    echo ""
}

# ============================================================
# MODE: --check (dry-run status)
# ============================================================
if [ "${1:-}" = "--check" ]; then
    echo -e "${CYAN}OpsPilot — Pre-Deployment Check${NC}"
    echo "  Path: $PROJECT_PATH"
    echo ""
    check_git
    check_branch
    git_remote_status
    check_clean
    echo ""
    echo -e "  PHP version:    ${GREEN}$(php_version)${NC}"
    if check_composer; then
        ok "Composer: $COMPOSER_CMD"
    else
        warn "Composer not found"
    fi
    check_env
    check_db
    check_manifest
    check_writable
    echo ""
    echo -e "${GREEN}Check complete.${NC}"
    exit 0
fi

# ============================================================
# MODE: Production deploy
# ============================================================
echo -e "${CYAN}OpsPilot — cPanel Production Deployment${NC}"
echo "  Target: $PROJECT_PATH"
echo ""

# --- Pre-flight ---
check_git
check_branch
check_clean
echo ""

# --- Fetch and pull ---
echo -e "${YELLOW}==>${NC} Fetching from origin..."
git fetch origin
git pull --ff-only origin "$PRODUCTION_BRANCH"
ok "Up to date with origin/$PRODUCTION_BRANCH"
echo ""

# --- Maintenance mode ---
echo -e "${YELLOW}==>${NC} Enabling maintenance mode..."
php artisan down --retry=60
ok "Maintenance mode enabled (retry: 60s)"
echo ""

# --- Composer ---
echo -e "${YELLOW}==>${NC} Installing Composer dependencies..."
if check_composer; then
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
    ok "Composer dependencies installed"
else
    fail "Composer not found. Install Composer or place composer.phar in project root."
    php artisan up
    exit 1
fi
echo ""

# --- Migrations ---
echo -e "${YELLOW}==>${NC} Running database migrations..."
php artisan migrate --force
ok "Migrations complete"
echo ""

# --- Vite manifest ---
check_manifest
echo ""

# --- Caches ---
echo -e "${YELLOW}==>${NC} Refreshing caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Caches refreshed"
echo ""

# --- Writable directories ---
check_writable
echo ""

# --- Summary ---
print_summary

echo -e "${GREEN}Deployment successful.${NC}"
echo ""
