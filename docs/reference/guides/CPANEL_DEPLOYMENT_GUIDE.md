# cPanel DEPLOYMENT GUIDE

> Generated: 2026-07-03
> App: OpsPilot (Laravel 12.x)
> PHP: 8.2+ | Database: MySQL/MariaDB | Server: Apache

---

## Prerequisites

Before starting, confirm your cPanel account has:

- [ ] **PHP 8.2+** — Check in cPanel → Select PHP Version
- [ ] **Apache with mod_rewrite** — Enabled by default in cPanel
- [ ] **MySQL 5.7+ or MariaDB 10.3+** — Available via cPanel → MySQL Databases
- [ ] **SSH access** or **cPanel Terminal** (recommended for artisan commands)
- [ ] **FTP/SFTP access** or **cPanel File Manager** (for file upload)
- [ ] **Disk space**: at least 200 MB for the application + database

### PHP Extensions to Enable

In cPanel → **Select PHP Version**, ensure these extensions are checked:

```
✅ bz2        ✅ exif       ✅ fileinfo    ✅ gd
✅ imagick    ✅ intl       ✅ mbstring    ✅ mysqli
✅ openssl    ✅ pdo        ✅ pdo_mysql   ✅ xml
✅ zip        ✅ bcmath     ✅ ctype       ✅ json
✅ tokenizer  ✅ curl       ✅ dom         ✅ xmlwriter
❌ redis      ❌ memcached  ❌ memcache    (NOT needed)
```

---

## Step 1: Prepare the Application Archive

### From Your Development Machine

```bash
# 1. Install production dependencies (no dev)
cd D:\xampp\htdocs\unknow
composer install --no-dev --optimize-autoloader

# 2. Build frontend (if not already built)
npm run build

# 3. Create deployment archive (EXCLUDING dev files)
# Create a ZIP containing ONLY:
# app/  bootstrap/  config/  database/  public/  resources/
# routes/  storage/  vendor/  .htaccess  artisan
# composer.json  composer.lock
```

### Exclude These Files from the Upload

| Path | Reason |
|---|---|
| `node_modules/` | 100+ MB of dev dependencies |
| `tests/` | Not needed in production |
| `coverage/` | Code coverage reports |
| `tests/coverage-xml/` | XML coverage data |
| `tests/coverage.txt` | Coverage summary |
| `docs/` | Documentation |
| `e2e/` | Playwright tests |
| `_can_delete/` | Quarantined files |
| `scripts/` | Dev/test scripts |
| `*.md` | All markdown files (optional) |
| `.env` | Contains local credentials |
| `.env.example` | Not needed |
| `storage/api-docs/` | OpenAPI generated docs (315 KB) |
| `public/hot` | Vite dev server file |
| `storage/pail` | Dev log viewer |
| `phpstan.neon` | Static analysis config |
| `phpunit.xml` | Test config |
| `deploy.sh`, `docker-compose.yml` | Not cPanel compatible |
| `.fleet/`, `.idea/`, `.vscode/` | IDE config |
| `.git/` | Git repository |
| `package-lock.json`, `yarn.lock` | Not needed if vendor is pre-built |

### Estimated Upload Size

| Component | Size |
|---|---|
| `vendor/` (--no-dev) | ~30-40 MB |
| `app/`, `config/`, `routes/`, `resources/` | ~5 MB |
| `public/build/` | ~500 KB |
| `database/`, `bootstrap/` | ~500 KB |
| **Total** | **~40-50 MB** |

---

## Step 2: Upload to cPanel

### Option A: cPanel File Manager (Simple)

1. Log in to cPanel
2. Open **File Manager**
3. Navigate to: `/home/username/` (or `public_html/`)
4. Create folder: `opspilot/`
5. Upload the ZIP archive
6. Right-click → **Extract**

### Option B: FTP/SFTP (Recommended)

```ftp
Host: ftp.yourdomain.com
Username: your_cpanel_username
Password: your_cpanel_password
Port: 21 (FTP) or 22 (SFTP)
```

Upload the project folder to: `/home/username/opspilot/`

### Option C: Git Deployment (Advanced)

```bash
# In cPanel Terminal or SSH:
cd /home/username
git clone https://github.com/your/repo.git opspilot
cd opspilot
composer install --no-dev --optimize-autoloader
```

---

## Step 3: Set Document Root

### In cPanel

1. Go to **Domains** → your domain → **Document Root**
2. Set to: `/home/username/opspilot/public`
3. Or: `/home/username/public_html/opspilot/public`

**IMPORTANT**: The document root MUST point to the `public/` subdirectory, not the project root. This ensures `vendor/`, `.env`, and other sensitive files are NOT accessible via the web.

**Before redirect (WRONG):**
```
Document Root: /home/username/public_html/opspilot
→ Visitors can access: yourdomain.com/.env  ← SECURITY RISK
```

**After redirect (CORRECT):**
```
Document Root: /home/username/public_html/opspilot/public
→ Visitors CANNOT access files outside public/
```

---

## Step 4: Create MySQL Database

### In cPanel

1. Go to **MySQL Databases**
2. **Create a new database**: `username_opspilot`
3. **Create a database user**: `username_opspilot_user`
   - Set a strong password (use cPanel Password Generator)
4. **Add user to database** → **All Privileges**

---

## Step 5: Configure `.env`

### In cPanel Terminal or SSH

```bash
cd /home/username/opspilot
cp .env.example .env
nano .env
```

### Production `.env` Template

```env
APP_NAME="OpsPilot"
APP_ENV=production
APP_KEY=                   # Leave empty — will generate in Step 7
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_opspilot           # From Step 4
DB_USERNAME=username_opspilot_user      # From Step 4
DB_PASSWORD=your_strong_password        # From Step 4

SESSION_DRIVER=database
SESSION_LIFETIME=1200
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.yourdomain.com

CACHE_STORE=file

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="OpsPilot"

FILESYSTEM_DISK=local
```

**Delete these dev keys** from `.env` (they are not used and cause confusion):
```
# Remove these lines entirely:
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
MEMCACHED_HOST=127.0.0.1
VITE_APP_NAME="${APP_NAME}"
```

---

## Step 6: Set Storage Permissions

### Using SSH or cPanel Terminal

```bash
cd /home/username/opspilot
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/logs
```

### Using cPanel File Manager

1. Navigate to each folder
2. Right-click → **Change Permissions**
3. Set to `775` (rwxrwxr-x)
4. Check **Apply to subdirectories and files**

---

## Step 7: Run Laravel Setup Commands

### Via cPanel Terminal or SSH

```bash
cd /home/username/opspilot

# 1. Generate application key
php artisan key:generate

# 2. Create storage symlink
php artisan storage:link
# Output: "The [public/storage] link has been connected to [storage/app/public]."

# 3. Run database migrations
php artisan migrate --force

# 4. (Optional) Seed initial data if first deployment
# WARNING: This will create demo admin accounts and test data. Do NOT run on a production database that already has real data.
# php artisan db:seed --class=DatabaseSeeder --force

# 5. Create sessions table (required for SESSION_DRIVER=database)
php artisan session:table
php artisan migrate --force

# 6. Run all cache commands
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# 7. Set storage permissions again (cache commands may create new files)
chmod -R 775 storage bootstrap/cache
```

---

## Step 8: Set Up Cron Jobs

### In cPanel

1. Go to **Cron Jobs**
2. Select: **Once Per Minute** (`* * * * *`)
3. Command:

```cron
/usr/local/bin/php /home/username/opspilot/artisan schedule:run >> /dev/null 2>&1
```

4. Click **Add New Cron Job**

### Find the Correct PHP Path

```bash
which php
# Output: /usr/local/bin/php   ← Use this path
```

### Full Cron Setup (Recommended)

Add TWO cron entries:

```cron
# Entry 1: Laravel Scheduler (required)
* * * * * /usr/local/bin/php /home/username/opspilot/artisan schedule:run >> /dev/null 2>&1

# Entry 2: Queue Worker (process queued jobs)
* * * * * /usr/local/bin/php /home/username/opspilot/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

---

## Step 9: Configure SSL/HTTPS

### In cPanel

1. Go to **SSL/TLS** → **Install and Manage SSL**
2. Select your domain
3. Install certificate (auto-provided or purchased)
4. Enable **AutoSSL** if available

### Force HTTPS via .htaccess

Edit `/home/username/opspilot/public/.htaccess` and add at the TOP:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]
</IfModule>
```

---

## Step 10: Post-Deployment Verification

```bash
# Test that the site loads
curl -I https://yourdomain.com
# Expected: HTTP/2 200

# Check Laravel logs for errors
tail -f storage/logs/laravel.log

# Verify cache is working
php artisan about
# Look for: Environment: production
#           Debug Mode: false
#           Config Cached: true
#           Routes Cached: true
#           Views Cached: true

# Test cron is running
php artisan schedule:test
# Follow prompts to test each scheduled command

# Test queue worker
php artisan queue:work --stop-when-empty --tries=1
# Should process any pending jobs (if any) and exit
```

---

## Troubleshooting Common cPanel Issues

### 500 Internal Server Error

```bash
# Check PHP error log
tail -f /home/username/logs/error_log

# Common causes:
# - PHP version too low (needs 8.2+)
# - Missing PHP extensions
# - Incorrect .env values
# - Storage permissions not set correctly
# - APP_KEY not generated

# Fix: Run setup steps again
php artisan key:generate
php artisan config:cache
chmod -R 775 storage bootstrap/cache
```

### Blank White Page

```bash
# Enable error display temporarily in .env:
APP_DEBUG=true
# Visit the page, see the error, fix it, then set back to false
```

### "No application encryption key" Error

```bash
php artisan key:generate
php artisan config:cache
```

### "The stream or file could not be opened" Error

```bash
chmod -R 775 storage bootstrap/cache
```

### "Class 'Redis' not found" Error

```bash
# Remove REDIS_* keys from .env (Redis is not available on this host)
# The app doesn't use Redis — these config entries are just defaults
```

### "Call to undefined function exec()" Error

Some shared hosts disable `exec()`. If queue worker doesn't work:
- Contact host support to enable `exec()` and `proc_open()`
- Or use an alternative: process jobs via HTTP calls instead

### "Failed to open stream: Permission denied"

```bash
# Reset all storage permissions
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

## Post-Deployment Cleanup

```bash
cd /home/username/opspilot

# Remove dev-only directories to save space
rm -rf storage/api-docs           # ~315 KB
rm -rf tests/                     # ~2 MB
rm -rf docs/                      # ~500 KB
rm -rf coverage/                  # ~5 MB
rm -rf tests/coverage-xml/        # ~3 MB
rm -rf _can_delete/               # ~74 MB
rm -f tests/coverage.txt          
rm -f .env.example                
rm -f *.md                        # all documentation files

# Run optimize after cleanup
php artisan optimize
```
