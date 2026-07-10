# Deployment Guide — OpsPilot (cPanel / Shared Hosting)

> **AI Keyword — LOCAL START**  
> When I write "LOCAL START" in an OpenCode session:
> 1. Inspect the current local environment.
> 2. Tell me the exact local commands needed.
> 3. Never use production credentials.
> 4. Never touch production.
> 5. Confirm local `APP_ENV`, `APP_URL`, `DB_DATABASE`, and Vite mode.
> 6. Keep the response short and operational.

> **AI Keyword — CPANEL DEPLOY**  
> When I write "CPANEL DEPLOY":
> - Inspect the latest Git changes since the previous deployment
> - Tell me exactly what changed
> - Tell me whether production needs:
>   - git pull only
>   - composer install
>   - migrations
>   - frontend build assets
>   - cache refresh
>   - storage link check
>   - queue restart
> - Then always provide these cPanel commands in the correct order:
>
>   cd /home/whizzweb/alphaspacepro.online
>   git pull --ff-only origin main
>   bash deploy.sh --check
>
>   If the check should pass, then also provide:
>
>   bash deploy.sh
> - Never modify `.env`.
> - Never expose secrets.
> - Never run local commands on production.
> - Keep the response short and operational.

---

## Quick Reference

| Command | Purpose |
|---------|---------|
| `bash deploy.sh` | Full production deployment (main branch) |
| `bash deploy.sh --check` | Pre-deployment status check |
| `CPANEL DEPLOY` (keyword) | AI-assisted deployment analysis |
| `LOCAL START` (keyword) | AI-assisted local environment setup |

---

## Environment Separation Rules

The repository supports two independent environments via **`.env` only** — no hardcoded configs.

| Concern | Local | Production |
|---------|-------|------------|
| `.env` | `copy .env.example .env` → edit | Existing server `.env`, never overwritten |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `DB_DATABASE` | Local MySQL instance | cPanel production database |
| `CACHE_STORE` | `file` | `redis` (or `file`) |
| `SESSION_SECURE_COOKIE` | `false` | `true` |
| Vite | `npm run dev` (hot reload) | Committed `public/build/` assets |
| Composer | `composer install` (with dev) | `composer install --no-dev` |
| Migrations | `php artisan migrate` | `php artisan migrate --force` (only in deploy.sh) |
| `storage:link` | One-time setup | One-time setup |

### Never

- Commit `.env` (ignored)
- Copy `.env.example` over a production `.env`
- Run `php artisan key:generate` on production
- Run `migrate:fresh`, `db:wipe`, destructive seeders, truncate/drop
- Copy or sync database data through Git (`*.sql`, `*.sqlite` ignored)
- Commit `vendor/` or `node_modules/`
- Delete `public/storage` or production uploads
- Use `git reset --hard`, force-push, or destructive checkout on production

---

## Local Setup

### Windows

```batch
cd <local-project-path>
copy .env.example .env
composer install
php artisan key:generate
:: edit .env → set DB_DATABASE, DB_USERNAME, DB_PASSWORD for your local MySQL
php artisan migrate
npm install
npm run dev
php artisan serve
```

### Linux / macOS

```bash
cd <local-project-path>
cp .env.example .env
composer install
php artisan key:generate
# edit .env → set DB_DATABASE, DB_USERNAME, DB_PASSWORD for your local MySQL
php artisan migrate
npm install
npm run dev
php artisan serve
```

**Important**: `php artisan key:generate` is **local-only**. Production already has its own `APP_KEY` in its `.env` — never run this on production.

---

## Production Deployment — cPanel

Every deployment follows this exact sequence:

### Step 1 — Pull latest code

```bash
cd /home/whizzweb/alphaspacepro.online
git pull --ff-only origin main
```

This updates the working tree to the latest `main` branch commit. If there are local changes, the pull will refuse — commit or stash before deploying.

### Step 2 — Run pre-deployment check

```bash
bash deploy.sh --check
```

This verifies:
- Git connection and branch `main`
- Remote is up to date
- Working tree is clean
- PHP and Composer are available
- `.env` exists
- Database connection works
- Vite `manifest.json` is present
- `storage/` and `bootstrap/cache/` are writable

**Do not proceed if `--check` reports any failure. Fix the issue first.**

### Step 3 — Deploy

```bash
bash deploy.sh
```

Only run this if `--check` passed cleanly. The script performs (in order, stops on first error):

1. Confirm Git repository and branch `main`
2. Refuse if uncommitted changes exist
3. `git fetch origin && git pull --ff-only origin main`
4. `php artisan down --retry=60` (maintenance mode)
5. Install Composer deps (`--no-dev --optimize-autoloader --no-interaction`)
6. `php artisan migrate --force`
7. Verify `public/build/manifest.json`
8. `php artisan optimize:clear` + `config:cache` + `route:cache` + `view:cache`
9. Verify `storage/` and `bootstrap/cache/` are writable
10. Print deployment summary

A `trap` ensures `php artisan up` runs even if the script fails.

### Warnings — production safety

| Never do this | Why |
|---------------|-----|
| Edit or overwrite `.env` manually | Use the existing production `.env` — it has live credentials |
| `git reset --hard` | Destroys uncommitted work and can lose data |
| `migrate:fresh` or `db:wipe` | Drops all tables — irreversible data loss |
| Delete `storage/` | Destroys logs, sessions, cache, and uploads |
| Deploy from any branch other than `main` | Only `main` is tested for production |

### Safety guarantees in deploy.sh

| Behaviour | Guaranteed |
|-----------|-----------|
| Modifies `.env` | **Never** |
| Runs `git reset --hard` | **Never** |
| Force-pushes | **Never** |
| Runs `migrate:fresh` / `db:wipe` | **Never** |
| Deletes storage, uploads, logs, public/storage | **Never** |
| Stops on error | `set -euo pipefail` |
| Requires clean working tree | exits with error if dirty |
| Requires branch `main` | exits with error if not main |
| Requires `.env` | exits with error if missing |
| Always exits maintenance mode | `trap maintenance_off EXIT` |
| Composer detection | `composer` → `composer.phar` → fail with message |

---

## Deployment History (Legacy FTP)

Previous deployments used FTP + phpMyAdmin. That workflow is preserved in Git history.  
For new deployments, use `deploy.sh` via cPanel SSH.

---

## Cron Jobs (cPanel)

```bash
# Run scheduler every minute
* * * * * /usr/local/bin/php /home/whizzweb/alphaspacepro.online/artisan schedule:run >> /dev/null 2>&1

# Optional: clear old logs daily at midnight
0 0 * * * /usr/local/bin/php /home/whizzweb/alphaspacepro.online/artisan log:clear --keep-last=30 >> /dev/null 2>&1
```

---

## Storage Permissions

| Directory | Permission | Notes |
|-----------|-----------|-------|
| `storage/` | 755 | Recursive — logs, framework, app data |
| `storage/logs/` | 755 | Laravel log files |
| `storage/framework/cache/` | 755 | Data, views cache |
| `storage/framework/sessions/` | 755 | Session files |
| `storage/framework/views/` | 755 | Compiled Blade templates |
| `bootstrap/cache/` | 755 | Config, route, services cache |
| `public/build/` | 755 | Vite-built assets (manifest + chunks) |

---

## Monitoring

### UptimeRobot

1. Monitor Type: **HTTP(s)**
2. URL: `https://alphaspacepro.online/api/health`
3. Interval: **5 minutes**
4. Enable SSL monitoring

### Smoke test

```bash
bash scripts/smoke-test.sh https://alphaspacepro.online
```

### Manual verification

- [ ] `https://alphaspacepro.online/api/health` — returns `{"status":"ok"}`
- [ ] `https://alphaspacepro.online/api/user` — returns 401 (unauthenticated)
- [ ] Login via Sanctum token endpoint — get valid token
- [ ] Check `storage/logs/` for errors

---

## Rollback Plan

### Database

1. Before deploying, export current DB:
   ```bash
   mysqldump -u user -p db_name > pre_deploy_$(date +%F).sql
   ```
2. Keep at least 3 previous exports outside web root.
3. Rollback: `Drop database → Create empty → Import previous SQL`

### Files

1. Previous `vendor/` can be restored from backup if `composer install` introduces issues.
2. Previous `public/build/` is in Git history — `git checkout HEAD~1 -- public/build/`

### Environment

**Never delete or overwrite `.env`** on production. Keep a backup `.env.bak` outside web root.

---

## Common Issues

| Problem | Fix |
|---------|-----|
| Blank page (500) | Check `storage/logs/laravel.log` |
| `APP_KEY` missing | Run `php artisan key:generate` **locally**, paste into server `.env` |
| Storage not writable | `chmod -R 775 storage/ bootstrap/cache/` |
| Route 404 | Ensure `public/index.php` paths point correctly |
| Asset not loading | Vite build committed? Check `public/build/manifest.json` |
| Login rate limited | 5 failures/minute triggers 429 (resets after 1 min) |
| Vault 403 on reveal | Only entry owner or super-admin can reveal passwords |
| Token expired | Sanctum tokens expire after 8 hours — login again |
