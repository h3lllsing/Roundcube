# DEPLOYMENT / CPANEL READINESS AUDIT

---

## 9.1 ENVIRONMENT CONFIGURATION

| Setting | Current (Local) | Required (Production) | Status |
|---------|----------------|----------------------|--------|
| `APP_ENV` | `local` | `production` | ❌ **C-01** |
| `APP_DEBUG` | `true` | `false` | ❌ **C-01** |
| `APP_URL` | `localhost` | `https://opspilot.whizzweb.net` | ⚠️ Update before deploy |
| `LOG_CHANNEL` | `single` | `daily` | ⚠️ Update |
| `SESSION_ENCRYPT` | `false` | `true` | ⚠️ Update |
| `SESSION_SECURE_COOKIE` | `false` | `true` | ⚠️ Update |
| `QUEUE_CONNECTION` | `database` | `sync` (for now) | ❌ **C-04** |
| `MAIL_MAILER` | `smtp` (updated) | `smtp` | ✅ |
| `DB_HOST` | `localhost` | `localhost` | ✅ |

---

## 9.2 VITE / ASSET BUILD

| Check | Status | Notes |
|-------|--------|-------|
| `npm run build` configured | ✅ | `package.json` has build script |
| Vite base URL for subdirectory | ❌ H-04 | Not configured for cPanel subfolder |
| Build directory in `.gitignore` | ⚠️ Verify | `public/build/` should be in `.gitignore` |

---

## 9.3 WEB SERVER

| Check | Status | Notes |
|-------|--------|-------|
| `.htaccess` present | ✅ | In `public/` directory |
| `.htaccess` forces HTTPS | ✅ | Standard cPanel config works |
| `public/` as document root | ✅ | Standard |
| Storage symlink | ⚠️ NEEDS SETUP | `php artisan storage:link` post-deploy |

---

## 9.4 FILE PERMISSIONS

| Directory | Required | Notes |
|-----------|----------|-------|
| `storage/` | 755 (writable) | Must be writable by cPanel user |
| `storage/logs/` | 755 | Log files created by web server |
| `storage/framework/cache/` | 755 | Session files |
| `storage/framework/sessions/` | 755 | Session files |
| `storage/framework/views/` | 755 | Compiled Blade templates |
| `bootstrap/cache/` | 755 | Config/route cache files |
| `public/build/` | 755 | Vite build output |

**H-06:** These permissions are not documented. Add to deployment runbook.

---

## 9.5 PHP DEPENDENCIES

| Requirement | Status | Notes |
|-------------|--------|-------|
| PHP 8.2+ | ⚠️ Verify on cPanel | Check cPanel PHP version |
| `ext-pdo_mysql` | ✅ (assumed) | Required for DB connection |
| `ext-mbstring` | ✅ (assumed) | Required by Laravel |
| `ext-fileinfo` | ✅ (assumed) | Required for file validation |
| `ext-curl` | ✅ (assumed) | Required for HTTP calls |
| `ext-bcmath` | ✅ (assumed) | Required by hashids |
| `ext-xml` | ✅ (assumed) | Required by Laravel |
| `ext-filter` | ✅ (assumed) | Required by validation |
| `ext-tokenizer` | ✅ (assumed) | Required by framework |

**C-05:** Extensions not declared in `composer.json`. If any are missing on cPanel, app will fail.

---

## 9.6 POST-DEPLOYMENT STEPS

| Step | Command | Status |
|------|---------|--------|
| 1 | `composer install --optimize-autoloader --no-dev` | ⚠️ Document |
| 2 | `npm ci && npm run build` | ⚠️ Document |
| 3 | `php artisan optimize` | ❌ H-05 |
| 4 | `php artisan route:cache` | ❌ H-05 |
| 5 | `php artisan config:cache` | ❌ H-05 |
| 6 | `php artisan view:cache` | ❌ H-05 |
| 7 | `php artisan event:cache` | ❌ H-05 |
| 8 | `php artisan storage:link` | ⚠️ Document |
| 9 | `php artisan migrate --force` | ⚠️ Document |
| 10 | Set correct `.env` values | ⚠️ Document |
| 11 | Set file permissions | ⚠️ H-06 |

**H-05:** No `post-install` or `post-deploy` script in `composer.json`.

---

## 9.7 CRON JOBS

| Task | Command | Scheduled? |
|------|---------|-----------|
| Laravel scheduler | `* * * * * php /path/to/artisan schedule:run` | ⚠️ Must set on cPanel |
| Queue worker (optional) | `* * * * * php artisan queue:work --stop-when-empty --max-time=60` | ⚠️ Optional future |

**Note:** Queue worker via cron will process 1 batch per minute. Not suitable for latency-sensitive jobs.

---

## 9.8 CPANEL-SPECIFIC CHECKS

| Check | Status | Notes |
|-------|--------|-------|
| Document root → `public/` | ⚠️ Verify | May need subdirectory or symlink |
| PHP version 8.2+ | ⚠️ Verify | cPanel MultiPHP Manager |
| `proc_open` disabled? | ⚠️ Verify | Breaks `npm run build` on cPanel |
| `exec` disabled? | ⚠️ Verify | Breaks some Artisan commands |
| MySQL 8.0+ | ⚠️ Verify | For Laravel 11 compatibility |
| SSL certificate active | ⚠️ Verify | AutoSSL via cPanel |
| Email (SMTP) ports open | ⚠️ Verify | Port 465 (SSL) for SMTP |

---

## 9.9 DISASTER RECOVERY

| Item | Status | Notes |
|------|--------|-------|
| Database backup plan | ❌ NOT DOCUMENTED | Add to runbook |
| `.env` backup | ❌ NOT DOCUMENTED | Store outside repo |
| Rollback procedure | ❌ NOT DOCUMENTED | `php artisan migrate:rollback` |
| Monitoring (Uptime) | ❌ NOT CONFIGURED | Consider UptimeRobot or similar |

---

## SUMMARY

| Area | Verdict |
|------|---------|
| Environment config | 🔴 3 hard blockers (C-01, C-04, C-05) |
| Web server | ✅ Ready (.htaccess, public/ root) |
| File permissions | ⚠️ Not documented |
| PHP extensions | 🔴 Not verified on cPanel |
| Post-deploy steps | ❌ Not scripted (H-05) |
| Cron jobs | ⚠️ Need cPanel setup |
| SSL/HTTPS | ⚠️ Verify AutoSSL |
| Disaster recovery | ❌ Not documented |
