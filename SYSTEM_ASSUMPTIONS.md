# SYSTEM ASSUMPTIONS

> **Principal Architect Analysis** — 2026-07-04
> Every hidden assumption in the system, with impact rating.

---

## 1. DATABASE ASSUMPTIONS

### 1.1 `module_id` EXISTS ON EVERY GLOBAL RECORD

**Where:**
- 9 API controllers: `index()` uses `whereIn('module_id', $accessibleModuleIds)`
- `RbacScope::apply()`: adds `whereIn('module_id', ...)` scope
- `GlobalSearchService`: filters by `module_id` for module-scoped records
- `CalendarController`: filters by `module_id` for service models
- `ExportController`: filters by `whereIn('module_id', $ids)` for non-admin module exports
- `NoteController::globalNotes()`: checks `getAccessibleModuleIds('read')` for module notes

**What breaks:**
- Any DB row with `module_id = NULL` is invisible to all non-super-admin queries via these paths
- `INSERT` without setting `module_id` (before Phase 3B, Web creates had this gap)
- Migration for backfill data where `module_id` was not set

**Rating: DANGEROUS** — Multiple code paths assume non-null `module_id` for scoping queries with zero fallback. A NULL `module_id` = a permanently invisible record.

---

### 1.2 `user_id` FK CASCADE ON DELETE (ALL 9 GLOBAL RECORD TABLES)

**Where:**
- Migrations `2026_05_24_054011` through `2026_05_24_070004`: all use `$table->foreignId('user_id')->constrained()->cascadeOnDelete()`

**What breaks:**
- Soft-deleting a User does NOT trigger this (only `forceDelete`)
- There is no `forceDelete` path for User in the codebase, so this is **dormant**
- If a future feature introduces `User::forceDelete()`, ALL records by that user vanish
- No archival, no reassignment, no warning

**Rating: DANGEROUS** — Currently dormant, but a future code addition (hard delete user) would cause catastrophic data loss. Requires architectural guard.

---

### 1.3 FOREIGN KEYS WITH `cascadeOnDelete` ON POLYMORPHIC TABLES

**Where:**
- `features.id` → `modules.feature_id` cascadeOnDelete: deleting a Feature deletes ALL its Modules
- `modules.id` → `module_role_permissions.module_id` cascadeOnDelete: deleting a Module destroys all role permissions
- `asset_categories.id` → `asset_types.category_id` cascadeOnDelete: deleting a category deletes all types
- `asset_categories.id` → `assets.category_id` cascadeOnDelete: deleting a category orphans all assets (their category_id becomes invalid — FK prevents this)

**What breaks:**
- Accidental Feature deletion = total module destruction = roles lose all permissions = app becomes unusable
- No soft-delete on ModuleRolePermission — data is GONE
- No confirmation warning on Feature/Module delete in controllers

**Rating: RISKY** — Schema is cascading by design but no UX protection against multi-table destruction.

---

### 1.4 `service_provider_id` HAS NO `cascadeOnDelete` — ORPHAN RISK

**Where:**
- `domain_emails`, `voip`, `hostings`, `domains`, `vps`, `other_services`, `expiry_trackers` all have `service_provider_id` with `foreignId()->nullable()->constrained()->nullOnDelete()` (added in migrations `2026_06_22_111154`, `2026_06_23_000001`, `2026_06_23_000002`)

**What breaks:**
- Deleting a ServiceProvider leaves 7+ tables with nulled FK references
- The `nullOnDelete` is intentional but there's no UX to warn "this provider has N dependent records"
- ServiceProviderController::destroy() checks `$sp->hostings()->count() + $sp->domains()->count() ...` — **but only in Web controller**. API controller has NO such check.

**Rating: RSKY** — Web path has a pre-deletion count check; API path silently orphans. Inconsistent between API and Web.

---

### 1.5 `smtp_profile_id` ON `expiry_tracker_notifications` HAS NO FK CONSTRAINT

**Where:**
- Migration `2026_06_25_100002` creates the column as `unsignedBigInteger` with just an index, NO `constrained()` — this differs from `expiry_trackers.smtp_profile_id` which uses `foreignId()->nullable()->constrained()->nullOnDelete()`

**What breaks:**
- Notification records can reference non-existent `smtp_profile_id` values
- Application code never validates this
- Join queries silently drop rows with no matching SMTP profile

**Rating: DANGEROUS** — Data integrity gap in a critical financial/logging table (expiry notifications).

---

### 1.6 `roles` TABLE EXISTS FROM EXTERNAL PACKAGE

**Where:**
- `ModuleRolePermission`, `UserModulePermission`, feature/module seeders, permission service all assume `roles` table exists
- Migration `2026_05_23_121531` does `$table->foreignId('role_id')->constrained()` — but this migration runs BEFORE any known Spatie Permission or Tyro migration
- The `roles` table is created by `HasinHayder\Tyro\Database\Seeders\TyroSeeder`

**What breaks:**
- `php artisan migrate --seed` order dependency: migrations must run first, THEN seeder creates roles
- `TyroSeeder` depends on Tyro package migrations (if any) running before it
- If Tyro package is removed or updated, the entire permission system breaks silently

**Rating: UNKNOWN** — Works today due to correct migration order, but is a fragile third-party dependency. No test validates this chain.

---

### 1.7 `data` MIGRATION HARDCODES `user_id = 1`

**Where:**
- Migration `2026_06_22_111154`, `2026_06_23_000001`, `2026_06_23_000002`: backfill queries use `'user_id' => 1` when creating ServiceProviders from existing `provider` strings

```php
'user_id' => 1, // hardcoded
```

**What breaks:**
- If no user with `id = 1` exists (deleted, or seeded differently), the migration fails
- Fresh install with different seeding order could have user 1 as a different person
- On a system where user 1 has been deleted (soft), the migration creates records owned by nobody

**Rating: DANGEROUS** — Hardcoded user ID in production data migration is a ticking bomb for fresh installs or restored databases.

---

## 2. CACHING ASSUMPTIONS

### 2.1 `dashboard:version` CACHE KEY NEVER EXPIRES

**Where:**
- `AppServiceProvider::boot()`: increments `Cache::increment('dashboard:version')` on model saved/deleted
- `DashboardController::index()`: `Cache::remember('dashboard:user:'.$user->id.':'.$version, ...)`
- `TaskService::delete()`: `Cache::increment('dashboard:version')`

**What breaks:**
- If cache is cleared (deployment, cache driver restart), version resets to 0
- All dashboard caches become stale until the next model event increments the counter
- Counter can theoretically overflow (cache integer limit depends on driver)

**Rating: RISKY** — Cache restore on deploy temporarily serves stale data until first CRUD operation.

---

### 2.2 `features:version` CACHE KEY NEVER EXPIRES

**Where:**
- `FeatureService::all()`: `Cache::remember('features:'.$version, 3600, ...)` — uses version key
- `FeatureService`, `ModuleService`: increment on CRUD
- `ModuleController::index()` uses this cache

**What breaks:**
- Same as dashboard — deploy clears cache, stale data until first CRUD
- Cache TTL is 3600s if version does NOT change; but version key has no TTL and can go stale independently

**Rating: SAFE** — Cache is for lists that rarely change, and 3600s auto-expiry provides a safety net.

---

### 2.3 `getRoleIds()` CACHES PER-INSTANCE WITH NO INVALIDATION

**Where:**
- `HasModulePermissions` trait: `getRoleIds()` stores `$this->roleIds` as instance property on first call

**What breaks:**
- If a user's roles change mid-request (super-admin demotes themselves from another tab), the cache is stale
- Tests that manipulate roles within a single request may get stale results
- No way to clear the cache without creating a new User instance

**Rating: RISKY** — In production, role changes are uncommon and usually require re-login. But test isolation is fragile.

---

## 3. MIDDLEWARE AUTHENTICATION ASSUMPTIONS

### 3.1 ALL API ROUTES ASSUME `auth:sanctum` GUARD IS ENOUGH

**Where:**
- `routes/api.php`: group middleware `['auth:sanctum', 'suspended', 'throttle:api', 'log.api']`
- Every API controller's `$request->user()` assumes non-null

**What breaks:**
- Sanctum token validation failure → `$request->user()` returns null → `$request->user()->id` errors
- Token can be valid but expired (expiration configured at 480 min)
- `App\Exceptions\Handler` likely returns 401 JSON for unauthenticated, but individual controllers don't guard against null

**Rating: SAFE** — Laravel's Sanctum middleware correctly handles auth failures before controller code runs.

---

### 3.2 WEB CONTROLLERS WITH ZERO AUTHENTICATION CHECKS

**Where:**
- `Web\FeatureController`: NO auth check on ANY method (index, create, store, show, edit, update, destroy)
- `Web\ModuleController`: NO auth check on ANY method
- `Web\RoleController`: NO auth check (create, update, delete roles)
- `Web\PrivilegeController`: NO auth check
- `Web\RoleTemplateController`: NO auth check (including `apply()` which modifies role permissions)
- `Web\SmtpProfileController`: NO auth check — except `StoreSmtpProfileRequest` and `UpdateSmtpProfileRequest`
- `Web\ModulePermissionController`: NO auth check — ANY authenticated user can manage ALL module permissions
- `Web\ImportController`: NO auth check — any authenticated user can import data

**What breaks:**
- Any logged-in user (even a read-only "user" role) can: create/update/delete features, modules, roles, privileges
- Any user can modify module-level permissions for ANY role
- Any user can import arbitrary CSV data

**Rating: DANGEROUS** — This is a critical authorization gap. The Web controllers for core system management have zero permission gates, relying entirely on the assumption that only trusted users will know the URLs.

---

## 4. CONFIGURATION ASSUMPTIONS

### 4.1 `QUEUE_CONNECTION= database` REQUIRES JOBS TABLE

**Where:**
- `config/queue.php`: `'default' => env('QUEUE_CONNECTION', 'database')`

**What breaks:**
- If the `jobs` table migration hasn't run, any queued operation fails silently
- `php artisan queue:work` starts consuming but errors on every job
- No async processing is configured in production (queue worker not documented as required)

**Rating: RISKY** — Queue worker setup is documented in DEPLOYMENT_GUIDE.md but easily missed. If no queue worker runs, expiry notifications, webhook dispatches, and password reveal logging all execute synchronously (if at all).

---

### 4.2 SESSION STORAGE DEFAULT IS DATABASE

**Where:**
- `config/session.php`: `'driver' => env('SESSION_DRIVER', 'database')`

**What breaks:**
- `sessions` table migration must run; if not, all login attempts fail
- Performance: database-backed sessions require a query on every request
- Shared hosting (cPanel) must have proper table permissions

**Rating: SAFE** — Documented and standard for multi-server deployments.

---

### 4.3 FRONTEND_URL DEFAULT FOR CORS

**Where:**
- `config/cors.php`: `'supports_credentials' => true`, `FRONTEND_URL` defaults to `'http://localhost:3000'`

**What breaks:**
- In production, if `FRONTEND_URL` is not set in `.env`, CORS blocks all SPA requests
- Sanctum SPA authentication (cookie-based) requires exact domain match
- Symptoms: API returns 200 via Postman but 419/401 via browser SPA

**Rating: RISKY** — Common deployment mistake, easily caught in smoke testing but confusing to debug.

---

## 5. SCHEDULE ASSUMPTIONS

### 5.1 NO `->withoutOverlapping()` ON ANY COMMAND

**Where:**
- `routes/console.php`: all scheduled commands (`expiry:check`, `monitor:check`, `sanctum:prune-expired`, `tasks:check-overdue`, `renewals:send-email-reminders`)

**What breaks:**
- If a command runs longer than its interval (e.g., `monitor:check` testing 1000+ URLs takes > 1 hour), a second process starts
- Duplicate notifications sent, duplicate emails, duplicate monitoring pings
- Database lock contention on concurrent `expiry_trackers` writes

**Rating: RISKY** — Will cause duplicate notifications under load. Production-safe pattern would use `->withoutOverlapping()`.

---

### 5.2 TIMEZONE ASSUMES SERVER MATCHES APP TIMEZONE

**Where:**
- `routes/console.php`: `dailyAt('08:00')`, `dailyAt('09:00')`, `dailyAt('02:00')`
- `Carbon::now()` used in multiple services
- `config/app.php`: `'timezone' => 'UTC'`

**What breaks:**
- If server timezone is not UTC (e.g., shared hosting in Asia/Kolkata), `dailyAt('08:00')` runs at 8 AM server time, not UTC
- Expiry date calculations shift
- Dashboard metrics misalign

**Rating: SAFE** — Documented as UTC. Standard practice.

---

## 6. RATE LIMITING ASSUMPTIONS

### 6.1 RATE LIMITS ASSUME SPECIFIC USAGE PATTERNS

**Where:**
- `AppServiceProvider::boot()`: API 60/min, Search 20/min, Export 5/min, Bulk 10/min, Import 5/min
- Login: 5 attempts per minute

**What breaks:**
- Export limit of 5/min may block bulk operations during business hours
- Search limit of 20/min: a single aggressive front-end autocomplete could hit this
- No documented way for admins to bypass limits
- Limits use IP as fallback for guests, but guest access may not exist except for API docs

**Rating: SAFE** — Reasonable defaults for a team-size app. Can be adjusted.

---

## 7. ENVIRONMENT-SPECIFIC ASSUMPTIONS

### 7.1 `APP_KEY` NOT GENERATED = TOTAL FAILURE

**Where:**
- Every encrypted/hashed value: passwords, encrypted vault passwords, session data, CSRF tokens

**What breaks:**
- `php artisan key:generate` is required on first deploy
- Without it: sessions fail, password hashing works but encryption fails, vault passwords unreadable
- Common deployment oversight

**Rating: SAFE** — Laravel's boot process throws a clear error if APP_KEY is missing. Not deployable without it.

---

### 7.2 `php artisan storage:link` NOT RUN

**Where:**
- `config/filesystems.php`: `'public'` disk maps to `storage/app/public`, symlinked to `public/storage`
- Asset `primary_image` stored to `'assets'` on public disk
- Migration assumes public disk exists

**What breaks:**
- Asset images return 404 (unless accessed via Storage facade internally)
- Logos/icons served from public disk fail
- Cached Vite assets still work (they go to `public/build/` directly)

**Rating: SAFE** — Documented in deploy guide. Easy to verify.
