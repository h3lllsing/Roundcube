# PRODUCTION CONFIGURATION GUIDE

> Generated: 2026-07-03
> App: OpsPilot (unknow) — Laravel 12.x
> Target: Shared hosting (cPanel, Apache, PHP 8.2+)

---

## Phase 1 — Environment Configuration Audit

### Current vs. Recommended Production Values

| Setting | Current (Dev) | Recommended (Production) | Why |
|---|---|---|---|
| **APP_ENV** | `local` | `production` | Must be `production` to suppress debug output and enable production optimizations |
| **APP_DEBUG** | `true` | `false` | **CRITICAL**: `true` exposes stack traces, env vars, DB credentials, and file paths to end users |
| **APP_URL** | `http://localhost/unknow/public` | `https://yourdomain.com` | Must match the production domain. Used for route generation, emails, and asset URLs |
| **APP_NAME** | `OpsPilot` | `OpsPilot` | OK as-is (display name only, no security impact) |
| **APP_KEY** | `base64:qIuPQYv4v...` | **Regenerate** | If this key was used in dev with `APP_DEBUG=true`, it may be compromised. Run `php artisan key:generate` AFTER setting up production `.env` |
| **SESSION_DRIVER** | `file` | `database` | File sessions don't work across multiple web servers and can fill disk on shared hosting. Database sessions are more reliable. Create `sessions` table: `php artisan session:table` |
| **SESSION_LIFETIME** | `120` | `1200` or `1440` | 120 minutes may be too short for enterprise users. 1200 min (20h) recommended for IT ops staff |
| **SESSION_SECURE_COOKIE** | *(not set)* | `true` | Only send session cookie over HTTPS |
| **SESSION_SAME_SITE** | *(not set)* | `lax` | CSRF protection |
| **CACHE_STORE** | `file` | `file` | Acceptable for shared hosting. File cache is fine for single-server |
| **QUEUE_CONNECTION** | `database` | `database` | **Already optimal** for shared hosting (no Redis/Supervisor needed). Jobs table already exists |
| **MAIL_MAILER** | `log` | `smtp` | **CRITICAL**: Current setting writes emails to log files. Must switch to SMTP with real credentials |
| **LOG_CHANNEL** | `stack` | `stack` (with `single`) | OK, but ensure logs are rotated or monitored. Add `LOG_DAILY_DAYS=14` if switching to `daily` |
| **LOG_LEVEL** | `warning` | `error` | Production should only log errors and above. `warning` is acceptable but `error` is quieter |
| **FILESYSTEM_DISK** | `local` | `local` | OK for shared hosting. Uploads stored in `storage/app/` with symlink to `public/storage` |
| **TIMEZONE** | *(not set, defaults to UTC)* | `UTC` or match server | Laravel default is UTC. Keep UTC unless you have a strong reason. Set in `config/app.php` |

### `.env` Production Template

```env
APP_NAME="OpsPilot"
APP_ENV=production
APP_KEY=base64:...   # ← REGENERATE: php artisan key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_prod_db
DB_USERNAME=your_prod_user
DB_PASSWORD=your_prod_password

SESSION_DRIVER=database
SESSION_LIFETIME=1200
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.yourdomain.com

CACHE_STORE=file

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourhost.com
MAIL_PORT=465
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="OpsPilot"

FILESYSTEM_DISK=local

# Remove or comment out these dev-only lines:
# REDIS_HOST, REDIS_PASSWORD, REDIS_PORT — not needed
# MEMCACHED_HOST — not needed
# AWS_* — unless using S3
# VITE_APP_NAME — only needed for dev
```

### What to Remove from Production `.env`

These settings are dev-only or irrelevant for production. Remove or comment them:

| Variable | Reason |
|---|---|
| `REDIS_CLIENT=phpredis` | Redis not available on shared hosting |
| `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` | Not used |
| `MEMCACHED_HOST` | Not used |
| `VITE_APP_NAME` | Only used by Vite dev server |
| `APP_FAKER_LOCALE` | Only used by Faker for seeding |

---

## Phase 2 — Shared Hosting Compatibility Report

### Requirements vs. cPanel/Apache Compatibility

| Requirement | Status | Notes |
|---|---|---|
| **PHP 8.2+** | ✅ Compatible | cPanel supports PHP 8.2 via MultiPHP Manager. Verify `php -v` ≥ 8.2 |
| **MySQL 8.0+ / MariaDB 10.3+** | ✅ Compatible | cPanel includes MySQL/MariaDB via phpMyAdmin |
| **Apache with mod_rewrite** | ✅ Compatible | `public/.htaccess` already configured for mod_rewrite. cPanel enables mod_rewrite by default |
| **No Redis** | ✅ Already configured | `QUEUE_CONNECTION=database` uses MySQL, not Redis. Remove Redis config from `.env` |
| **No Supervisor** | ⚠️ Needs strategy | See queue strategy below (cron-based queue worker) |
| **OpenSSL** | ✅ Required by Laravel | cPanel PHP includes OpenSSL |
| **PDO MySQL** | ✅ Required by Laravel | cPanel PHP includes PDO_MySQL |
| **BCMath** | ✅ Required by Laravel | Included in cPanel PHP |
| **Ctype, JSON, Mbstring, Tokenizer, XML** | ✅ All included in cPanel PHP 8.2 |
| **GD / Imagick** | ⚠️ Check if needed | Required if the app processes images. Verify in cPanel PHP extensions list |
| **Fileinfo** | ✅ Required by Laravel | Included in cPanel PHP |
| **Intl** | ✅ Optional | Laravel uses `NumberFormatter` if available. Not critical |
| **allow_url_fopen** | ⚠️ May be disabled | If disabled, some HTTP-based features may fail. Check cPanel PHP settings |

### Potential Incompatibilities

| Issue | Risk | Mitigation |
|---|---|---|
| PHP memory limit too low | Medium | Set `memory_limit = 256M` in cPanel MultiPHP INI Editor |
| `exec()` / `shell_exec()` disabled | High | Laravel's `php artisan` queue worker via cron requires `exec()`. Ask host to enable or use alternative queue strategy |
| `proc_open()` disabled | High | Required by Symfony Process component (used by some Laravel features). Ask host to enable |
| File upload size limit | Medium | Set `upload_max_filesize = 32M` and `post_max_size = 32M` in cPanel PHP INI |
| Max execution time too low | Medium | Set `max_execution_time = 300` for queue worker |
| Disk quota | Medium | Session files, cache files, logs can accumulate. Monitor disk usage |
| No SSH access | High | Deploying via FTP without SSH is risky. Use cPanel File Manager or Git deployment if possible |

---

## Phase 3 — Deployment Checklist

### Files to Upload

```
/ (root of project)
├── app/
├── bootstrap/
│   └── cache/           ← upload empty directory with .gitignore
├── config/
├── database/
│   ├── migrations/      ← upload all migration files
│   └── seeders/         ← upload seeders (run with --class on first deploy)
├── public/              ← THIS IS THE DOCUMENT ROOT
│   ├── .htaccess
│   ├── index.php
│   ├── favicon.ico
│   ├── robots.txt
│   └── build/           ← upload entire Vite build output
├── resources/
│   └── views/
├── routes/
├── storage/             ← upload directory structure ONLY (empty)
│   ├── app/
│   │   ├── .gitignore
│   │   ├── private/
│   │   │   └── .gitignore
│   │   └── public/
│   │       └── .gitignore
│   ├── framework/
│   │   ├── cache/
│   │   │   ├── data/
│   │   │   │   └── .gitignore
│   │   │   └── .gitignore
│   │   ├── sessions/
│   │   │   └── .gitignore
│   │   └── views/
│   │       └── .gitignore
│   └── logs/
│       └── .gitignore
├── vendor/              ← upload after `composer install --no-dev`
├── .env                 ← DO NOT COMMIT. Create manually on server
└── composer.json        ← needed to verify version, but vendor is pre-built
```

### Files and Folders NOT to Upload

| Path | Reason |
|---|---|
| `.env` | Contains production credentials. Create fresh on server |
| `.env.example` | Not needed |
| `node_modules/` | Dev-only. Frontend is pre-built into `public/build/` |
| `tests/` | Not needed in production |
| `coverage/` and `tests/coverage-xml/` and `tests/coverage.txt` | Code coverage reports — NOT for production |
| `storage/api-docs/` | Swagger/OpenAPI generated docs — can be deleted to save space (315 KB) |
| `_can_delete/` | Quarantined files — delete before deployment |
| `*.md` files | All documentation markdown files — not needed (optional) |
| `docs/` | Documentation |
| `e2e/` | Playwright E2E tests |
| `scripts/` | Development scripts |
| `deploy.sh`, `deploy/`, `docker-compose.yml` | Deployment scripts not applicable to cPanel |
| `phpstan.neon`, `phpunit.xml`, etc. | Dev tool config |
| `package.json`, `package-lock.json`, `composer.lock` | Needed only if running `install` on server |
| `vite.config.js`, `tailwind.config.js`, `postcss.config.js` | Build config — not needed if pre-built |
| `.git/`, `.gitignore`, `.gitattributes` | Version control — not needed |
| `.fleet/`, `.idea/`, `.vscode/` | IDE config |
| `public/hot` | Vite dev server hot file — NOT for production |
| `storage/pail` | Log viewer dev file |

### Correct Document Root

**cPanel Document Root must point to `/public` (the `public/` subdirectory).**

Full path example: `/home/username/public_html/opsilot/public/`

Verify that visitors cannot access files outside `public/`:
- `public/index.php` is the entry point
- `public/.htaccess` rewrites all requests to `index.php`
- The `../` path in `index.php` (`__DIR__.'/../'`) points to the project root (outside document root)

```
Server directory layout:
/home/user/
├── opspilot/              ← Project root (NOT accessible via web)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── public/            ← Document root (symlink or subdirectory)
│   │   ├── .htaccess
│   │   └── index.php
│   ├── storage/
│   └── vendor/
└── public_html/opspilot/  ← OR place public/ contents here
```

### Step-by-Step Deployment

#### 1. Prepare Production `.env`

```bash
# On production server via SSH or cPanel Terminal:
cd /home/user/opspilot
cp .env.example .env
nano .env
```

Set all values from the template in Phase 1 above.

#### 2. Set Storage Permissions

```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/logs
chmod -R 775 bootstrap/cache
```

If `chmod` is not available, use cPanel File Manager:
- Right-click each folder → Change Permissions → 775

#### 3. Create Storage Symlink

```bash
php artisan storage:link
# Creates: public/storage → ../../storage/app/public
```

#### 4. Run Database Migrations

```bash
php artisan migrate --force
```

# WARNING: Seeding is for local development only.
# DemoDataSeeder is BLOCKED in production environments.
# If you require initial data, write a custom production-safe seeder.
```bash
php artisan migrate --force
```

#### 5. Create Sessions Table (if using database sessions)

```bash
php artisan session:table
php artisan migrate --force
```

#### 6. Run Optimize Commands (in order)

```bash
# First clear any previous cache
php artisan optimize:clear

# Then cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Final optimization
php artisan optimize
```

#### 7. Verify APP_KEY

```bash
# If key is empty or from dev, generate new one:
php artisan key:generate
# Then re-run config:cache
php artisan config:cache
```

---

### Cron Job Setup

These scheduled tasks run on the server. Add this to cPanel **Cron Jobs** (one entry):

```cron
* * * * * /usr/local/bin/php /home/user/opspilot/artisan schedule:run >> /dev/null 2>&1
```

This single cron entry runs every minute. Laravel's scheduler determines which tasks are due.

**Current scheduled tasks that will run:**

| Schedule | Command | Purpose |
|---|---|---|
| `0 8 * * *` (8 AM daily) | `php artisan expiry:check` | Check expiring items |
| `0 * * * *` (hourly) | `php artisan monitor:check` | Check service monitors |
| `0 0 * * *` (midnight) | `php artisan sanctum:prune-expired` | Clean expired API tokens |
| `0 9 * * *` (9 AM daily) | `php artisan tasks:check-overdue` | Flag overdue tasks |
| `0 2 * * *` (2 AM daily) | `php artisan renewals:send-email-reminders` | Send renewal email notifications |

**Important cron notes:**
- The PHP path may differ on your host. Run `which php` or ask support
- The project path must be the **absolute** server path (not the URL)
- Test with: `php artisan schedule:test` after setup

---

### Queue Strategy (No Supervisor)

Since shared hosting has no Supervisor, use a **cron-based queue worker**:

```cron
* * * * * /usr/local/bin/php /home/user/opspilot/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

**How it works:**
- Every minute, cron runs `queue:work --stop-when-empty`
- It processes any pending jobs from the `jobs` database table
- Once all jobs are processed, it stops (no daemon needed)
- This is the standard shared-hosting pattern

**OR** add a separate cron entry alongside the scheduler. Total of 2 cron entries:

```cron
# Laravel Scheduler
* * * * * /usr/local/bin/php /home/user/opspilot/artisan schedule:run >> /dev/null 2>&1

# Queue Worker (runs every minute, stops when empty)
* * * * * /usr/local/bin/php /home/user/opspilot/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

**Queue job types that will run:**

| Job | Trigger | Frequency |
|---|---|---|
| `App\Notifications\ExpiringSoon` | Expiry check | Daily |
| `App\Notifications\MonitorCheckFailed` | Monitor failure | On failure |
| `App\Notifications\TaskAssigned` | Task assignment | On assignment |
| `App\Notifications\NoteAdded` | Note added | On note creation |
| `App\Mail\ExpiryTrackerReminder` | Renewal reminder | Per schedule |

---

### SMTP Configuration

**On cPanel:**
1. Go to **Email → Email Deliverability** or use your hosting provider's SMTP
2. If your hosting provides **Mailgun**, **SendGrid**, or **SMTP relay**, use those credentials
3. Common SMTP configurations:

| Provider | Host | Port | Encryption | Notes |
|---|---|---|---|---|
| cPanel Email | `mail.yourdomain.com` | 465 | SSL | Requires email account on domain |
| Gmail SMTP | `smtp.gmail.com` | 587 | TLS | Requires App Password |
| Mailgun | `smtp.mailgun.org` | 587 | TLS | Free tier available |
| SendGrid | `smtp.sendgrid.net` | 587 | TLS | Free tier available |
| Hosting SMTP | Check your host | — | — | Usually in cPanel Email section |

**.env settings for SMTP:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="OpsPilot"
```

---

### SSL/HTTPS Checklist

| Action | Details |
|---|---|
| Install SSL certificate | Use cPanel **SSL/TLS** → **Install and Manage SSL** |
| Force HTTPS redirect | Add to `public/.htaccess` or use cPanel **Force HTTPS Redirect** |
| Set `APP_URL` to `https://` | Must start with `https://yourdomain.com` |
| Set `SESSION_SECURE_COOKIE=true` | Ensures session cookies only sent over HTTPS |
| Update `config/trustedproxies.php` | If behind Cloudflare/load balancer |
| Check mixed content | After deploy, verify no `http://` resource loads |
| HSTS header | Optional: add `Strict-Transport-Security` header via `.htaccess` |

**.htaccess HTTPS redirect (add ABOVE the Laravel rewrite rules):**
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]
```

---

## Quick Reference: Deploy Commands

```bash
# 1. Upload files (excluding dev-only dirs)
# 2. Set permissions
chmod -R 775 storage bootstrap/cache

# 3. Create .env
cp .env.example .env
# Edit .env with production values

# 4. Generate app key
php artisan key:generate

# 5. Storage link
php artisan storage:link

# 6. Run migrations
php artisan migrate --force

# 7. Optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# 8. Set up cron jobs (in cPanel)
# Add: * * * * * php /path/to/artisan schedule:run
# Add: * * * * * php /path/to/artisan queue:work --stop-when-empty --tries=3

# 9. Verify
php artisan about
```
