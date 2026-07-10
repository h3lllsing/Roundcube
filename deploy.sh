#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# deploy.sh — OpsPilot Production Build Script
# Prepares a deployment-ready build. Run from project root.
# ============================================================

APP_NAME="ops pilot"

echo "==> Step 1: Install dependencies (no dev)"
composer install --no-dev --optimize-autoloader

echo "==> Step 2: Build frontend assets"
npm ci && npm run build

echo "==> Step 3: Clear caches"
php artisan optimize:clear

echo "==> Step 4: Cache for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Step 5: Create storage link (if not exists)"
php artisan storage:link --force

echo "==> Step 6: Build deploy archive"
DEPLOY_DIR="$(dirname "$0")/deploy_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$DEPLOY_DIR"

rsync -av --progress \
  --exclude='.env' \
  --exclude='_can_delete' \
  --exclude='coverage' \
  --exclude='tests' \
  --exclude='node_modules' \
  --exclude='storage/logs/*.log' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='storage/api-docs' \
  --exclude='bootstrap/cache/*.php' \
  --exclude='.phpunit*' \
  --exclude='phpunit*' \
  --exclude='phpstan*' \
  --exclude='Dockerfile*' \
  --exclude='docker-compose*' \
  --exclude='.dockerignore' \
  --exclude='deploy.sh' \
  --exclude='.env.e2e' \
  --exclude='.env.testing' \
  --exclude='.editorconfig' \
  --exclude='docs/' \
  --exclude='scripts/' \
  --exclude='e2e/' \
  --exclude='.github/' \
  --exclude='resources/js/' \
  --exclude='resources/css/' \
  --exclude='deploy_*' \
  "$(dirname "$0")/" "$DEPLOY_DIR/"

echo "==> Step 7: Compress archive"
cd "$(dirname "$0")"
tar -czf "${DEPLOY_DIR}.tar.gz" -C "$(dirname "$DEPLOY_DIR")" "$(basename "$DEPLOY_DIR")"
rm -rf "$DEPLOY_DIR"

echo ""
echo "============================================"
echo "  BUILD COMPLETE"
echo "============================================"
echo "  Archive: ${DEPLOY_DIR}.tar.gz"
echo "  Size:    $(du -h "${DEPLOY_DIR}.tar.gz" | cut -f1)"
echo ""
echo "  Next steps:"
echo "  1. Upload ${DEPLOY_DIR}.tar.gz to your server"
echo "  2. Extract: tar -xzf ${DEPLOY_DIR}.tar.gz"
echo "  3. Copy public/ contents to public_html/"
echo "  4. Copy .env.example to .env and edit:"
echo "     - Set APP_ENV=production, APP_DEBUG=false"
echo "     - Set CACHE_STORE=redis (or keep file for single-server)"
echo "  5. Run: php artisan key:generate"
echo "  6. Run: php artisan migrate --force"
echo "  7. Set permissions: chmod -R 775 storage bootstrap/cache"
echo "  8. Set up cron: * * * * * php artisan schedule:run"
echo "  9. Set up queue worker (Supervisord/Forge): php artisan queue:work"
echo "============================================"
