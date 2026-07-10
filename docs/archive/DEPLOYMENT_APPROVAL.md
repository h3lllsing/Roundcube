# DEPLOYMENT APPROVAL

> Generated: 2026-07-03
> App: OpsPilot | Version: v1.0 (Release Candidate)
> Target: cPanel shared hosting (production)

---

## Blocker Resolution Status

| # | Blocker | Status | Notes |
|---|---|---|---|
| B1 | `public/storage` symlink missing | ☐ UNRESOLVED | Run `php artisan storage:link` before any deploy |
| B2 | `APP_ENV=local` in `.env` | ☐ UNRESOLVED | Set to `production` in production `.env` |
| B3 | `APP_DEBUG=true` in `.env` | ☐ UNRESOLVED | Set to `false` in production `.env` |

**All 3 blockers must be resolved before deployment can proceed.**

---

## Warning Summary

| # | Warning | Priority | Action |
|---|---|---|---|
| W1 | `MAIL_MAILER=log` in `.env` | HIGH | Change to `smtp` with real SMTP credentials |
| W2 | `app/OpenApi.php` has `http://localhost:8000/api` | LOW | Consider making dynamic via `config('app.url')` |
| W3 | `config/cors.php` defaults to `http://localhost:3000` | MEDIUM | Set `FRONTEND_URL` in production `.env` |
| W4 | `config/sanctum.php` has localhost stateful domains | MEDIUM | Set `SANCTUM_STATEFUL_DOMAINS` in production `.env` |
| W5 | `node_modules/` (68 MB) in project | LOW | Already in `.gitignore` — ensure deploy script excludes it |
| W6 | `storage/api-docs/` (315 KB) | LOW | Delete before creating deployment archive |
| W7 | `coverage/`, `tests/coverage-*` (~8 MB) | LOW | Delete before creating deployment archive |

---

## Pre-Deployment Checklist

### Configuration

| Task | Status | Command / Notes |
|---|---|---|
| Create production `.env` from template | ☐ | Copy `.env.example` → `.env`, edit all values |
| Set `APP_ENV=production` | ☐ | |
| Set `APP_DEBUG=false` | ☐ | |
| Set `APP_URL=https://yourdomain.com` | ☐ | |
| Generate new `APP_KEY` | ☐ | `php artisan key:generate` |
| Set database credentials | ☐ | From cPanel MySQL settings |
| Set `SESSION_DRIVER=database` | ☐ | Run `php artisan session:table` after deploy |
| Set `SESSION_SECURE_COOKIE=true` | ☐ | |
| Set `MAIL_MAILER=smtp` with real credentials | ☐ | SMTP host, port, username, password |
| Set `FRONTEND_URL` | ☐ | If separate frontend exists |
| Set `SANCTUM_STATEFUL_DOMAINS` | ☐ | Production domain |
| Remove `REDIS_*`, `MEMCACHED_*` from `.env` | ☐ | Not needed on shared hosting |

### Filesystem

| Task | Status | Notes |
|---|---|---|
| Create `public/storage` symlink | ☐ | `php artisan storage:link` |
| Set permissions: `chmod -R 775 storage` | ☐ | |
| Set permissions: `chmod -R 775 bootstrap/cache` | ☐ | |
| Delete `storage/api-docs/` | ☐ | 315 KB of generated API docs |
| Delete `coverage/`, `tests/coverage-*` | ☐ | ~8 MB of test artifacts |
| Ensure `.gitignore` handles all exclusions | ☐ | |

### Database

| Task | Status | Notes |
|---|---|---|
| Create database in cPanel | ☐ | |
| Run `php artisan migrate --force` | ☐ | |
| Run `php artisan db:seed --force` (if needed) | ☐ | Only on first deployment |
| Run `php artisan session:table` + migrate | ☐ | Required for DB sessions |

### Build

| Task | Status | Notes |
|---|---|---|
| Run `composer install --no-dev --optimize-autoloader` | ☐ | |
| Run `npm run build` | ☐ | Frontend production build |
| Verify `public/build/manifest.json` | ☐ | |

### Cache

| Task | Status | Notes |
|---|---|---|
| `php artisan optimize:clear` | ☐ | Clear all before re-caching |
| `php artisan config:cache` | ☐ | |
| `php artisan route:cache` | ☐ | |
| `php artisan view:cache` | ☐ | |
| `php artisan event:cache` | ☐ | |
| `php artisan optimize` | ☐ | |

### Cron

| Task | Status | Notes |
|---|---|---|
| Add scheduler cron: `* * * * * php artisan schedule:run` | ☐ | |
| Add queue cron: `* * * * * php artisan queue:work --stop-when-empty --tries=3` | ☐ | |
| Test with `php artisan schedule:test` | ☐ | |

### SSL

| Task | Status | Notes |
|---|---|---|
| Install SSL certificate | ☐ | |
| Force HTTPS in `.htaccess` | ☐ | |
| Set `APP_URL` to `https://` | ☐ | |
| Set `SESSION_SECURE_COOKIE=true` | ☐ | |
| Verify no mixed content | ☐ | |

---

## Approval Sign-Off

| Role | Name | Signature | Date |
|---|---|---|---|
| **Developer** | | | |
| *I confirm all blockers are resolved, the code has been tested, and the application passes runtime verification.* | | | |
| **QA / Tester** | | | |
| *I confirm all 200+ smoke tests pass on the target environment.* | | | |
| **Project Lead** | | | |
| *I authorize this deployment to production.* | | | |

---

## Decision

| Option | Action |
|---|---|
| ✅ APPROVED | Proceed with deployment |
| ❌ REJECTED | Do not deploy — address blockers first |

**Current status: ❌ REJECTED — 3 blockers unresolved**

---

## Post-Approval Deployment Order

```
1. Resolve all BLOCKER items
2. Run php artisan optimize:clear
3. Run php artisan config:cache, route:cache, view:cache, event:cache
4. Run php artisan optimize
5. Create deployment ZIP (excluding dev files)
6. Upload to cPanel
7. Set document root to /public
8. Create .env with production values
9. Run php artisan key:generate
10. Run php artisan storage:link
11. Run php artisan migrate --force
12. Set storage permissions (chmod -R 775)
13. Set up cron jobs
14. Run POST_DEPLOYMENT_SMOKE_TEST.md
15. Sign off in FINAL_RELEASE_SIGNOFF.md
```
