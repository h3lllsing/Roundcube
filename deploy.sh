#!/usr/bin/env bash
# FTP Deployment Script for Tyro RBAC
# Usage: ./deploy.sh [production|staging]
# Requires: lftp installed, .env.deploy sourced or set below

set -euo pipefail

ENV="${1:-production}"

# === CONFIG — override via .env.deploy or environment ===
FTP_HOST="${FTP_HOST:-ftp.example.com}"
FTP_USER="${FTP_USER:-username}"
FTP_PASS="${FTP_PASS:-password}"
FTP_PORT="${FTP_PORT:-21}"
REMOTE_DIR="${REMOTE_DIR:-/public_html}"

LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

echo "=== Deploying to $ENV environment ==="
echo "Host: $FTP_HOST:$FTP_PORT"
echo "Remote: $REMOTE_DIR"
echo ""

# 1. Build frontend
echo ">>> Building frontend..."
cd "$LOCAL_DIR/../unknow-frontend"
npm ci --silent
npm run build
echo "Frontend build complete."
echo ""

# 2. Prepare backend
echo ">>> Preparing backend..."
cd "$LOCAL_DIR"

# Copy frontend build into Laravel public
rm -rf public/dist
cp -r ../unknow-frontend/dist public/dist

# Install PHP deps (no-dev for production)
composer install --no-dev --optimize-autoloader --no-interaction

# Dump .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo ">>> .env created from .env.example — EDIT IT BEFORE DEPLOYING!"
fi
echo ""

# 3. Create deploy exclude list
cat > /tmp/deploy-exclude.txt << 'EXCLUDE'
.git
.gitignore
node_modules/
tests/
*.md
.dockerignore
Dockerfile
docker-compose.yml
deploy.sh
deploy/
.env
EXCLUDE

# 4. Upload via FTP
echo ">>> Uploading to $FTP_HOST..."
lftp -c "
set ftp:ssl-allow no
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5
open -u $FTP_USER,$FTP_PASS -p $FTP_PORT $FTP_HOST
mirror -R -x .git/ -x node_modules/ -x tests/ -x .env \
       -X .gitignore -X .dockerignore -X Dockerfile \
       -X docker-compose.yml -X deploy.sh \
       -X 'deploy/*' \
       $LOCAL_DIR $REMOTE_DIR
"

echo "=== Deployment complete! ==="
echo ""
echo "Next steps:"
echo "  1. Set .env with production DB credentials"
echo "  2. Run: php artisan migrate --force"
echo "  3. Run: php artisan storage:link"
echo "  4. Run: php artisan optimize"
echo "  5. Point your web server root to $REMOTE_DIR/public"
