# CRITICAL AND HIGH-SEVERITY FINDINGS — DETAILED REPORT

---

## CRITICAL FINDINGS

### C-01: `.env` Committed With Secrets

**File:** `.env` (tracked in git)

**What:** The `.env` file contains:
```
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=tyro_project
DB_USERNAME=whizzweb_tyro
DB_PASSWORD=<real password>
MAIL_PASSWORD=<real SMTP password>
APP_KEY=base64:3Z... (real key)
```

**Impact:**
- Anyone with repo access can connect to production database
- Anyone with SMTP password can send email as noreply@alphaspacepro.online
- `APP_DEBUG=true` exposes full stack traces on production errors
- `APP_KEY` exposure enables session/cookie tampering

**Fix:**
1. Add `.env` to `.gitignore` if not already
2. Remove `.env` from git tracking: `git rm --cached .env`
3. Rotate all exposed credentials: DB password, SMTP password, APP_KEY
4. Create `.env.example` with placeholder values
5. Rotate the APP_KEY on production: `php artisan key:generate` (after deploy, before going live)

**Verification:** `git log --all --diff-filter=A -- .env` to confirm no history copies remain.

---

### C-02: Hardcoded Plaintext Passwords in DemoDataSeeder

**File:** `database/seeders/DemoDataSeeder.php`

**What:** Passwords hardcoded as strings in source code:
```php
'password' => Hash::make('SP@demo2024'),
// And others
```

**Impact:** Passwords are in git history permanently. Anyone with repo access can extract them.

**Fix:**
1. Move all demo passwords to `.env` variables: `DEMO_USER_PASSWORD=SP@demo2024`
2. Use `env('DEMO_USER_PASSWORD', 'fallback')` in seeder
3. OR use `Hash::make(Str::random(16))` for non-interactive demo accounts
4. Set `DEMO_USER_PASSWORD=` in `.env.example` (empty)
5. Consider removing DemoDataSeeder from production deployment entirely (best practice)

**Verify:** Search for `Hash::make` in all seeders to confirm no more hardcoded passwords.

---

### C-03: Test User in DatabaseSeeder

**File:** `database/seeders/DatabaseSeeder.php`

**What:**
```php
User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
]);
```

**Impact:**
- Well-known email + password combination in production
- Test user cannot be disabled without reseeding
- Even if route registration is disabled, user exists with known credentials

**Fix:**
1. Wrap in `if (! app()->environment('production'))` or equivalent
2. OR use `env('TEST_USER_PASSWORD', Str::random(16))`
3. OR remove entirely (test users should be created by QA via Admin panel)

---

### C-04: Queue Worker on cPanel

**File:** `.env` → `QUEUE_CONNECTION=database`

**Problem:** cPanel shared hosting does not allow persistent processes. `queue:work` requires a long-running process.

**Impact:**
- All jobs queued to `jobs` table will never process
- Webhook notifications will silently fail
- Email notifications queued via `ShouldQueue` will not send
- Import/Export progress tracking will hang

**Options:**
1. Install queue worker via cPanel cron job (`* * * * * php artisan queue:work --stop-when-empty --max-time=60`)
2. Switch to `QUEUE_CONNECTION=sync` — jobs execute synchronously during HTTP request
3. Use a third-party queue service (Laravel Forge, Cloudways, etc.)

**Recommendation:** Option 2 (sync) for immediate deploy. Option 1 (cron worker) for first sprint.

---

### C-05: Missing PHP Extension Requirements

**File:** `composer.json` (`require` section)

**Current:**
```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^11.41",
    "vinkla/hashids": "^12.0"
}
```

**Missing declarations (though likely installed):**
- `ext-pdo_mysql` — database driver
- `ext-mbstring` — string operations (Laravel dependency)
- `ext-fileinfo` — file validation, MIME detection
- `ext-curl` — HTTP client operations
- `ext-redis` — if Redis is used for cache/session
- `ext-bcmath` — hashids dependency
- `ext-xml` — if XML parsing used

**Impact:** cPanel may not have these extensions. If missing, composer install will succeed but application crashes on specific operations.

**Fix:** Add to `composer.json:require` section. Run `composer check-platform-reqs` before deploy.

---

### C-06: PHPStan Static Analysis

**Current state:**
```
vendor/bin/phpstan analyse --memory-limit=256M --level 0
```
Returns errors even at `--level 0`.

**Missing:** `phpstan.neon` config file.

**Impact:** Cannot enforce code quality via CI. Deployment pipeline may fail.

**Fix:**
1. Create `phpstan.neon` with appropriate paths/ignores
2. Fix current errors or baseline them
3. Set CI to enforce at least level 1-2

---

## HIGH FINDINGS

### H-01: CSV Injection (Export)

**File:** Any export functionality generating CSV files.

**Problem:** CSV values beginning with `=`, `+`, `-`, `@` are interpreted as formulas by Excel/Google Sheets. This enables formula injection attacks against users who download and open the CSV.

**Fix:** Prefix all string values starting with these characters with a single quote `'` or tab `\t`.

---

### H-02: Unvalidated Sort Fields (SQL Injection)

**File:** `MonitoringOverviewController`

**Problem:** Controller accepts `sort` and `direction` parameters from request without whitelist validation. These are passed directly to `orderBy()`.

**Fix:** Validate against whitelist of allowed sort columns before applying to query.

---

### H-03: Missing Foreign Key Indexes

**Problem:** FK columns without indexes cause full table scans on JOINs. As data grows, performance degrades rapidly.

**Affected tables (suspected):** At least 9 FK columns across the schema lack indexes. Verify with `SHOW INDEX FROM <table>` on each FK column.

---

### H-04: Vite Base URL

**File:** `vite.config.js`

**Problem:** Vite is not configured to handle subdirectory deployment (cPanel subfolder).

**Fix:** Set `base` option in vite.config.js or use dynamic base URL based on `APP_URL`.

---

### H-05: No Post-Deploy Caching Script

**File:** `composer.json` → `scripts` section

**Missing:** No post-deploy script to run:
```
php artisan optimize
php artisan route:cache
php artisan config:cache
php artisan view:cache
php artisan event:cache
```

---

### H-06: Storage Permissions Not Documented

**Problem:** Deployment documentation does not specify required permissions.

**Required:** `storage/`, `bootstrap/cache/` must be writable by web server user.

---

### H-07: Hardcoded Swagger URL

**File:** Configuration or documentation.

**Problem:** `http://localhost:8000/api` is hardcoded as the Swagger API URL.

**Fix:** Use `config('app.url')` or environment variable.

---

### H-08: Unvalidated `$request->input()` Calls

**Files:** 6+ controllers use `$request->input('key')` directly without validation rules.

**Fix:** Use Form Requests or inline `$request->validate()` for all input.
