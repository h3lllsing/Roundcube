# Deployment Guide — Tyro RBAC (Shared Hosting)

**Environment:** PHP 8.2, MySQL, Shared Hosting (FTP, no SSH)  
**Strategy:** Develop locally → Upload via FTP → Import DB via phpMyAdmin

---

## Prerequisites
- [ ] PHP 8.2+ on server
- [ ] MySQL 5.7+ / MariaDB 10.3+
- [ ] FTP credentials (host, username, password)
- [ ] phpMyAdmin access
- [ ] Domain/subdomain pointed to hosting

---

## Step 1: Prepare Local Build

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Generate optimized autoload (classmap)
composer install --optimize-autoloader --no-dev

# 3. Build config/route/view cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Verify no debug output in public/index.php
#    Remove any dd(), dump(), var_dump(), echo statements
```

## Step 2: Export Database

```bash
# Via mysqldump (local XAMPP):
mysqldump -u root tyro_project > deploy_db.sql

# Or via phpMyAdmin:
# 1. Open http://localhost/phpmyadmin
# 2. Select `tyro_project` database
# 3. Export → Custom → select all tables → Go
```

## Step 3: Upload via FTP

```
Local                             →  Remote (public_html / www)
──────────────────────────────────────────────────────────────
├── .env.example                  →  .env (rename + edit)
├── public/                       →  public_html/
├── (all other files/folders)     →  project root (above public_html)
├── vendor/ (compiled)            →  vendor/
├── deploy_db.sql                 →  (temporary)
```

**Critical:** On shared hosting, the `public/` folder contents go into `public_html/`.  
The rest of the Laravel files go one level above (outside web root).

```
Shared Hosting Structure:
├── .env
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── storage/
├── vendor/
├── public_html/   ← web root
│   └── index.php  ← update paths in index.php
```

## Step 4: Fix public/index.php Paths

After moving `public/` → `public_html/`, edit `public_html/index.php`:

```php
// Line ~20 — change from:
require __DIR__.'/../vendor/autoload.php';
// To:
require __DIR__.'/../vendor/autoload.php';  // stays same if vendor is above public_html

// Line ~36 — change from:
$app = require_once __DIR__.'/../bootstrap/app.php';
// To:
$app = require_once __DIR__.'/../bootstrap/app.php';  // stays same if bootstrap is above
```

## Step 5: Configure .env on Server

Upload `.env.example` as `.env` and edit:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=   ← Run locally: php artisan key:generate --show, copy this value

DB_HOST=localhost              # or provided by host
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

## Step 6: Import Database

```bash
# Via phpMyAdmin:
# 1. Create new database (utf8mb4_general_ci)
# 2. Import → Choose file → deploy_db.sql → Go

# Via command line (if available):
mysql -u username -p your_db_name < deploy_db.sql
```

## Step 7: Create Storage Symlink (if possible)

On shared hosting, manually create folders:

```bash
# These need to exist and be writable:
storage/framework/cache/data/
storage/framework/sessions/
storage/framework/views/
storage/logs/
bootstrap/cache/

# Set permissions (755 for directories, 644 for files):

```bash
find storage/ -type d -exec chmod 755 {} \;
find storage/ -type f -exec chmod 644 {} \;
chmod -R 755 bootstrap/cache
chmod -R 755 public/build
```

| Directory | Permission | Notes |
|-----------|-----------|-------|
| `storage/` | 755 | Recursive — logs, framework, app data |
| `storage/logs/` | 755 | Laravel log files |
| `storage/framework/cache/` | 755 | Data, views cache |
| `storage/framework/sessions/` | 755 | Session files |
| `storage/framework/views/` | 755 | Compiled Blade templates |
| `bootstrap/cache/` | 755 | Config, route, services cache |
| `public/build/` | 755 | Vite-built assets (manifest + chunks) |
```

## Step 8: Enable OPcache

On PHP 8.2+, add these to your `php.ini` (or ask hosting to enable):

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

Laravel bootstrap + vendor files will be cached in shared memory, improving response time 2-3x.

## Step 8b: Configure Cron Jobs

On cPanel, set up the following cron jobs via **cPanel → Cron Jobs**:

```bash
# Run scheduler every minute
* * * * * /usr/local/bin/php /home/user/public_html/../artisan schedule:run >> /dev/null 2>&1

# Optional: clear old logs daily at midnight
0 0 * * * /usr/local/bin/php /home/user/public_html/../artisan log:clear --keep-last=30 >> /dev/null 2>&1
```

Replace `/home/user/public_html/../artisan` with the actual path to `artisan` on your server (one level above `public_html`).

### UptimeRobot Monitoring Setup

1. Sign up at https://uptimerobot.com (free tier: 50 monitors)
2. Add a **new monitor**:
   - Monitor Type: **HTTP(s)**
   - Friendly Name: `[YourApp] Production Health`
   - URL: `https://yourdomain.com/api/health`
   - Interval: **5 minutes**
   - Timeout: **30 seconds**
3. Configure **Alert Contacts**:
   - Email: add your team email(s)
   - Optional: Slack webhook for real-time alerts
4. Enable **SSL monitoring** on the same monitor (checks cert expiry)
5. Add a second monitor for the login page:
   - Monitor Type: **HTTP(s)**
   - URL: `https://yourdomain.com/login`
   - Check for keyword: `Login`
   - Interval: **10 minutes**
6. Verify: trigger a 500 error by hitting a bad endpoint — alert should fire within 5 minutes

## Step 9: Verify (Manual + Automated)

### Automated Smoke Test
```bash
composer smoke-test
# or: bash scripts/smoke-test.sh https://yourdomain.com
```

### Manual Checks
- [ ] `https://yourdomain.com/api/health` — returns `{"status":"ok"}`
- [ ] `https://yourdomain.com/api/user` — returns 401 (unauthenticated)
- [ ] Login via Sanctum token endpoint — get valid token
- [ ] Hit `https://yourdomain.com/api/features` — should list seeded features
- [ ] Hit `https://yourdomain.com/api/me` — should return user info with roles & permissions
- [ ] Hit `https://yourdomain.com/api/dashboard` — should return counts/stats
- [ ] Hit `https://yourdomain.com/api/vault` — should list vault entries (empty)
- [ ] Check `storage/logs/` for errors
- [ ] Check `bootstrap/cache/` is writable (755)

---

## Common Issues

| Problem | Fix |
|---------|-----|
| Blank page (500) | Check `storage/logs/laravel.log` |
| `APP_KEY` missing | Run `php artisan key:generate` locally, paste into server `.env` |
| SQL error on import | Ensure MySQL version matches (5.7+ / 8.0 compatible) |
| Upload fails on vendor/ | Zip vendor/ locally, upload zip, extract on server via cPanel File Manager |
| Storage not writable | Set 755 or 775 on `storage/` and `bootstrap/cache/` |
| Route 404 | Ensure `public/index.php` paths point correctly to `bootstrap/app.php` and `vendor/autoload.php` |
| Asset not loading | Update `ASSET_URL` in `.env` if using CDN |
| Login rate limited | 5 failures/minute triggers 429 lockout (resets after 1 min) |
| Vault 403 on reveal | Only entry owner or super-admin can reveal passwords |
| Token expired | Sanctum tokens expire after 8 hours — login again |

---
## API Endpoints Reference

### Public (no auth)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | Uptime/monitoring health check (no auth, returns `{"status":"ok"}`) |
| POST | `/api/login` | Authenticate, returns Sanctum token (rate-limited: 5/min) |
| POST | `/api/forgot-password` | Request password reset link |
| POST | `/api/reset-password` | Reset password with token |

### Authenticated (requires Bearer token)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/logout` | Revoke current token |
| GET | `/api/me` | Current user info + roles + module permissions |
| GET | `/api/dashboard` | Summary counts (cached 5 min per user) |
| GET | `/api/tasks` | List tasks (search, sort, filter by status/priority/assigned_to/dates) |
| POST | `/api/tasks` | Create task (title required, module_id optional) |
| GET | `/api/tasks/{id}` | Get task details |
| PUT | `/api/tasks/{id}` | Update task |
| DELETE | `/api/tasks/{id}` | Soft-delete task |
| GET | `/api/my/tasks` | Tasks assigned to current user |
| GET | `/api/my/tasks/counts` | Task counts grouped by status |
| GET | `/api/notes` | List global notes (search, sort) |
| POST | `/api/notes` | Create global note |
| DELETE | `/api/notes/{id}` | Delete own note (super-admin can delete any) |
| GET | `/api/features/{id}/notes` | List feature notes |
| POST | `/api/features/{id}/notes` | Create note on feature |
| GET | `/api/modules/{id}/notes` | List module notes |
| POST | `/api/modules/{id}/notes` | Create note on module |
| GET | `/api/notifications` | List notifications |
| GET | `/api/notifications/unread` | Unread notification count |
| POST | `/api/notifications/{id}/read` | Mark one as read |
| POST | `/api/notifications/read-all` | Mark all as read |
| DELETE | `/api/notifications/{id}` | Delete a notification |
| GET | `/api/my/module-permissions` | All module permissions for current user |
| GET | `/api/modules/{id}/my-permissions` | Permissions for a specific module |
| GET | `/api/features` | List features (search, sort, with_trashed) |
| GET | `/api/features/{id}` | Get feature with modules |
| GET | `/api/features/{id}/modules` | List modules under feature (search, sort, with_trashed) |
| GET | `/api/modules/{id}` | Get module details |
| GET | `/api/vault` | List vault entries (sort, with_trashed) |
| POST | `/api/vault` | Create vault entry (password encrypted at rest) |
| GET | `/api/vault/{id}` | Show entry (password masked) |
| PUT | `/api/vault/{id}` | Update entry |
| DELETE | `/api/vault/{id}` | Soft-delete entry |
| POST | `/api/vault/{id}/reveal` | Reveal actual password (audit-logged) |

### Super-Admin only
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/features` | Create feature |
| PUT | `/api/features/{id}` | Update feature |
| DELETE | `/api/features/{id}` | Soft-delete feature |
| POST | `/api/features/{id}/modules` | Create module under feature |
| PUT | `/api/modules/{id}` | Update module |
| DELETE | `/api/modules/{id}` | Soft-delete module |
| GET | `/api/modules/{id}/permissions` | List role permissions for module |
| POST | `/api/modules/{id}/permissions` | Set permissions for a role on a module |
| DELETE | `/api/modules/{id}/permissions/{roleId}` | Remove all permissions for a role on a module |
| GET | `/api/users/{id}/module-permissions` | Get all module permissions for a user |
| GET | `/api/activity-logs` | List activity logs (search, sort, filter by event) |
| GET | `/api/activity-logs/{id}` | Show activity log detail with subject info |

### Common Query Parameters
| Parameter | Used By | Description |
|-----------|---------|-------------|
| `?search=` | tasks, notes, features, modules, activity-logs | Full-text search (LIKE %term%) |
| `?sort_by=` | Same | Sort column (whitelist-based) |
| `?sort_order=asc|desc` | Same | Sort direction |
| `?with_trashed=1` | features, modules, tasks, vault | Include soft-deleted (super-admin only) |
| `?per_page=` | All list endpoints | Pagination (default 20, max 100) |

### Filters
| Filter | Endpoint | Values |
|--------|----------|--------|
| `?status=` | tasks | pending, in_progress, completed, cancelled |
| `?priority=` | tasks | low, medium, high, critical |
| `?assigned_to=` | tasks | User ID |
| `?date_from=&date_to=` | tasks | YYYY-MM-DD (due_date range) |
| `?event=` | activity-logs | created, updated, deleted, revealed |
| `?is_active=` | features | 0 or 1 |

### Authentication & Security
- **Token expiry:** 8 hours (configurable in `config/sanctum.php`)
- **Login lockout:** 5 failed attempts in 1 minute → 429
- **Rate limit:** 60 requests/minute for authenticated routes (`throttle:api`)
- **Token format:** `Authorization: Bearer {token}`
- Passwords encrypted with AES-256-CBC (via `APP_KEY`)
- Vault reveal is audit-logged with `event=revealed`
- Notes ownership enforced (non-owner cannot delete)
- Module permission checks on vault access
- All activity logged via Spatie Activitylog
- Swagger UI: `/api/documentation`

---
## Rollback Plan

### Database
1. Before importing new SQL, export current DB via phpMyAdmin → `pre_deploy_$(date +%F).sql`
2. Keep at least **3 previous exports** in a secure location (not in web root)
3. Rollback: `Drop database → Create empty → Import previous SQL`

### Files
1. Before uploading, backup current `vendor/` as `vendor.bak/`
2. Keep `public/build/` backup for asset rollback
3. Rollback: Restore previous vendor, rebuild if needed

### Environment
1. **Never delete old `.env`** — rename to `.env.bak` before replacing
2. Keep `.env.bak` outside web root (one level above `public_html`)

### Quick Rollback Command
```bash
# If deploy script fails mid-way:
cp .env.bak .env
mv vendor.bak vendor
mysql -u user -p db_name < pre_deploy_2026-01-01.sql
php artisan optimize
```
