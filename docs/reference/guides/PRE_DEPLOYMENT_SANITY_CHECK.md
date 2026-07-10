# PRE-DEPLOYMENT SANITY CHECK

> Generated: 2026-07-03
> App: OpsPilot | Target: cPanel shared hosting

---

## Classification Guide

| Label | Meaning | Action Required |
|---|---|---|
| **BLOCKER** | **Must fix before deployment.** Deployment will fail or be insecure if not addressed. | Fix now. |
| **WARNING** | Should fix before deployment. May cause issues in production if ignored. | Fix before deploy if time permits. |
| INFO | Noted for awareness. No action needed. | No action. |

---

## Check 1: TODO / FIXME in Production Code

| Result | Count | Severity |
|---|---|---|
| ✅ PASS | 0 actual TODO/FIXME | INFO |

**Details:** A broad scan of `app/`, `config/`, `resources/views/`, `routes/`, `database/` found no actual `TODO` or `FIXME` comments. Previous matches were false positives (`isNotEmpty()`, `Temporary` as an assignment reason).

---

## Check 2: Debug Functions (dd, dump, ray, var_dump, print_r, die, exit)

| Result | Count | Severity |
|---|---|---|
| ✅ PASS | 0 occurrences in production code | INFO |

**Details:** Verified with precise regex (`dd(`, `dump(`, `ray(`, `var_dump(`, `print_r(`, `die`, `exit`). Zero actual debug function calls found in `app/`, `config/`, `routes/`, `database/`.

---

## Check 3: APP_DEBUG Assumptions

| Result | Severity |
|---|---|
| ⚠️ WARNING | WARNING |

**Details:** `.env` has `APP_DEBUG=true` — **must be set to `false`** for production. The `config/app.php` default is `env('APP_DEBUG', false)` which is a proper fallback (safe default). The `.env` override is the issue.

---

## Check 4: localhost URLs

| Result | Count | Severity |
|---|---|---|
| ⚠️ WARNING | 4 hardcoded references | WARNING |

**File: `app/OpenApi.php`**
- Hardcoded: `url: 'http://localhost:8000/api'`
- **Fix:** Use `env('APP_URL') . '/api'` or `config('app.url') . '/api'`

**File: `config/cors.php`**
- Default: `env('FRONTEND_URL', 'http://localhost:3000')`
- **Fix:** Set `FRONTEND_URL` to production URL in `.env`

**File: `config/sanctum.php`**
- Default: `'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'`
- **Fix:** Set `SANCTUM_STATEFUL_DOMAINS` to production domain in `.env`

**File: `config/filesystems.php`**
- `'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage'`
- **Fix:** Set `APP_URL` in production `.env` — this is just a default fallback.

---

## Check 5: Test Credentials

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** `database/seeders/DatabaseSeeder.php` contains `test@example.com` / `password` and `database/seeders/DemoDataSeeder.php` contains `admin@tyro.project`, but these are **seeders** — they are NOT in the runtime code path. They only run via `php artisan db:seed`. The `app/OpenApi.php` contact email `admin@tyro.project` is just a Swagger annotation and has no runtime effect.

---

## Check 6: Fake SMTP

| Result | Severity |
|---|---|
| ⚠️ WARNING | WARNING |

**Details:** `.env` has `MAIL_MAILER=log` — emails are written to `storage/logs/laravel.log` instead of being sent. **Must change to `smtp`** with valid SMTP credentials for production.

---

## Check 7: Hardcoded Paths

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** No hardcoded filesystem paths (`/home/`, `/var/www/`, `D:\xampp`, etc.) found in runtime code. Previous matches were false positives (`path:` is OpenAPI annotation for route paths).

---

## Check 8: Temporary Images

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** No temp/placeholder/sample/dummy images found in `public/images/` or `public/img/`.

---

## Check 9: Debug Routes

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** Routes matching "test" are legitimate feature endpoints (`smtp-profiles.test`, `webhooks.test`, `expiry-trackers.test-email`). These are functional test/send actions, not debug routes. No routes for `tinker`, `telescope`, `horizon`, `clockwork`, or `_debugbar` exist.

---

## Check 10: Unused Public Assets

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** No stale build artifacts (`app.css`, `app.js`, `mix-manifest.json`, etc.) found in `public/`. Only expected files present: `.htaccess`, `index.php`, `favicon.ico`, `robots.txt`, `build/`, `images/`.

---

## Check 11: Broken Symlinks

| Result | Severity |
|---|---|
| ❌ **BLOCKER** | **BLOCKER** |

**`public/storage` does NOT exist.** This symlink is required for uploaded files to be accessible via the web. Run before deploying:

```bash
php artisan storage:link
```

This creates: `public/storage` → `../../storage/app/public`

---

## Check 12: Missing Storage Folders

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**All 9 required folders exist:**

| Folder | Status |
|---|---|
| `storage/framework/views` | ✅ |
| `storage/framework/cache` | ✅ |
| `storage/framework/cache/data` | ✅ |
| `storage/framework/sessions` | ✅ |
| `storage/logs` | ✅ |
| `bootstrap/cache` | ✅ |
| `storage/app` | ✅ |
| `storage/app/public` | ✅ |
| `storage/app/private` | ✅ |

---

## Check 13: Broken Migrations

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** All 47 migrations have been executed successfully (batch 1). No pending or failed migrations. `migrate:check` command not available (Laravel 12 uses `migrate:status`).

---

## Check 14: Missing Vendor Packages

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**All 5 required packages installed:**

| Package | Status |
|---|---|
| `laravel/framework` (^12.0) | ✅ |
| `laravel/sanctum` (^4.0) | ✅ |
| `spatie/laravel-activitylog` (^4.0) | ✅ |
| `darkaonline/l5-swagger` (^11.0) | ✅ |
| `hasinhayder/tyro` (^1.6) | ✅ |

Autoload dump files present at `vendor/composer/autoload_real.php`.

---

## Check 15: Missing Build Assets

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

| Asset | Status |
|---|---|
| `public/build/manifest.json` | ✅ |
| `public/build/assets/` | ✅ (2 files) |
| `public/build/assets/app-Cdu7BxLG.css` | ✅ |
| `public/build/assets/app-DBHOz0_q.js` | ✅ |

---

## Check 16: Verify public/build Manifest

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** Manifest is valid JSON. All referenced assets resolve to existing files.

---

## Check 17: Verify Artisan Commands

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** All standard Laravel commands are available. Verified by running `php artisan list`. Critical commands confirmed: `migrate`, `config:cache`, `route:cache`, `view:cache`, `event:cache`, `optimize`, `queue:work`, `queue:table`, `storage:link`, `schedule:run`, `key:generate`, `down`, `up`, `about`.

---

## Check 18: Verify Scheduler

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

**Details:** `routes/console.php` has 5 scheduled commands:

| Schedule | Command | Next Run |
|---|---|---|
| `0 8 * * *` | `php artisan expiry:check` | 6 hours |
| `0 * * * *` | `php artisan monitor:check` | 25 minutes |
| `0 0 * * *` | `php artisan sanctum:prune-expired` | 22 hours |
| `0 9 * * *` | `php artisan tasks:check-overdue` | 7 hours |
| `0 2 * * *` | `php artisan renewals:send-email-reminders` | 25 minutes |

All commands registered and listed correctly.

---

## Check 19: Verify Queue

| Result | Severity |
|---|---|
| ✅ PASS | INFO |

| Setting | Value | Status |
|---|---|---|
| Queue driver | `database` | ✅ Correct for shared hosting |
| `jobs` table | Exists | ✅ |
| `failed_jobs` table | Exists | ✅ |
| Pending jobs | 0 | ✅ |
| Failed jobs | 0 | ✅ |

---

## Check 20: Verify Deployment Package

| Result | Severity |
|---|---|
| ⚠️ WARNING | WARNING |

**Items to exclude from production upload:**

| Item | Size | Action |
|---|---|---|
| `node_modules/` | 68.61 MB | Do NOT upload. Already in `.gitignore` |
| `storage/api-docs/` | 315.94 KB | Do NOT upload (has `.gitignore` but should be excluded) |
| `coverage/` + `tests/coverage-*` | ~8 MB | Do NOT upload |
| All `*.md` docs | ~500 KB | Do NOT upload (documentation files) |
| `tests/` | ~2 MB | Do NOT upload |

---

## Summary

| Severity | Count | Item |
|---|---|---|
| ❌ **BLOCKER** | **3** | `public/storage` symlink missing; `APP_ENV=local` → must be `production`; `APP_DEBUG=true` → must be `false` |
| ⚠️ **WARNING** | **7** | `MAIL_MAILER=log` → must be `smtp`; `OpenApi.php` localhost URL; CORS localhost default; Sanctum stateful domains; `node_modules/` size; `storage/api-docs/`; coverage files |
| ℹ️ INFO | 10 | All other checks pass |

**Overall: 3 BLOCKERS, 7 WARNINGS, 10 INFO — Do NOT deploy until blockers are resolved.**
