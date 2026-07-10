# ARCHITECTURAL ASSUMPTIONS

> **Principal Architect Analysis** — 2026-07-04
> Every hidden architectural assumption in the codebase.

---

## 1. AUTHENTICATION & AUTHORIZATION

### 1.1 `Auth::user()` IS ALWAYS AUTHENTICATED IN CONTROLLERS

**Where:** Every Web controller uses `Auth::user()` or `Auth::id()` without null checks. Every API controller uses `$request->user()`.

**Assumption:** The middleware/web guard guarantees an authenticated user.

**Who depends on it:** Every single controller method.

**What breaks:** Routes that skip middleware (some forgot to add `auth` middleware).

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — middleware `auth:sanctum` + `auth` guard.

**Can tests enforce?** Already covered by auth-required tests.

**Can monitoring detect?** 500 errors on null method calls.

**Rating: SAFE** — Standard Laravel pattern, middleware enforced.

---

### 1.2 `hasRole('super-admin')` IS THE ONLY ESCAPE HATCH

**Where:** ~40+ permission checks use `$user->hasRole('super-admin')` as the bypass condition.

**Assumption:** The super-admin role is the only role with unrestricted access. There is no concept of a "system admin" or "owner" role.

**Who depends on it:** All RBAC, all controllers, all services.

**What breaks:** If the `super-admin` role slug is renamed or the role is deleted, the entire authorization system collapses — no user can see any record, perform any operation.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** No — it's a string literal `'super-admin'` in 40+ locations.

**Can tests enforce?** Partial — tests seed the role.

**Can monitoring detect?** Every user would get 403 errors.

**Rating: RISKY** — The `'super-admin'` string is hardcoded in 40+ locations across controllers, services, seeders, and migrations. A rename would require a project-wide search-and-replace.

---

### 1.3 `canOnModule($module, $action)` ASSUMES MODULE IS NOT NULL

**Where:**
- ~15 Web controllers: `$user->canOnModule($module, 'create')` where `$module = Module::where('slug', $this->moduleSlug())->first()`
- `HasModulePermissions::canOnModule()`: accesses `$module->id`

**Assumption:** The module ALWAYS exists for the controller's slug.

**Who depends on it:** Every Web controller's store/create/edit/update/delete methods.

**What breaks:** If the module is deleted from the DB but the controller still references its slug:
- `canOnModule(null, 'create')` → calls `$module->id` → PHP error on null
- OR worse: `Module::where('slug', ...)` returns null, the `if ($module)` check wraps the `canOnModule()` — but some controllers pass null to the check directly

**Can DB enforce?** No — module deletion cascades to permissions but controller slugs survive.

**Can validation enforce?** No.

**Can code enforce?** Yes — every `canOnModule()` call should null-guard the module.

**Can tests enforce?** No — tests always seed modules.

**Can monitoring detect?** 500 error + null pointer exception.

**Rating: DANGEROUS** — If a Module is ever deleted, ALL Web CRUD operations on that module type fail with 500 errors. The `canOnModule()` method doesn't null-guard `$module`.

---

### 1.4 `getAccessibleModuleIds('read')` ALWAYS EXISTS ON USER MODEL

**Where:** ~15 controllers + `RbacScope` + dashboard + export + search

**Assumption:** The `User` model has the `HasModulePermissions` trait with `getAccessibleModuleIds()`.

**Who depends on it:** The entire RBAC system.

**What breaks:** If the trait is removed or renamed, every scoped query returns all records (no filter = super-admin view for everyone).

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — interface contract or type hint.

**Can tests enforce?** Yes — trait tests exist.

**Can monitoring detect?** Security audit would catch data leakage.

**Rating: SAFE** — Well-encapsulated trait. Hard to accidentally remove.

---

## 2. CONTROLLER PATTERNS

### 2.1 API `show/update/destroy` USE `user_id` OWNERSHIP (NOT MODULE RBAC)

**Where:** All 9 API CRUD controllers (Domain, Hosting, Vps, Voip, ServiceProvider, DomainEmail, OtherService, ExpiryTracker, Asset).

**Assumption:** A non-super-admin user should ONLY be able to show/update/delete records where `record.user_id === auth()->id()`.

**Who depends on it:** The API security model.

**What breaks:**
- Admin users who can see ALL module records via the list endpoint get 403 when trying to access records owned by other users
- API is MORE restrictive than Web (Web uses module RBAC)
- Inconsistent UX: "I can see this record in the list but cannot open it"

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — it's the current code.

**Can tests enforce?** Yes — ownership tests exist.

**Can monitoring detect?** User confusion + support tickets.

**Rating: RISKY** — Intentional WONTFIX for v1.0, but creates a confusing user experience for admin users who see records in lists but can't access them.

---

### 2.2 WEB CONTROLLERS SET `$validated['module_id']` FROM CONTROLLER'S OWN SLUG

**Where:** All 9 Web CRUD controllers (DomainController, HostingController, etc.).

```php
$module = Module::where('slug', $this->moduleSlug())->first();
if ($module) {
    $validated['module_id'] = $module->id;
}
```

**Assumption:** The record should be assigned to the module matching the controller, NOT the `module_id` submitted in the request.

**Who depends on it:** Data integrity — records always belong to the correct module regardless of client input.

**What breaks:**
- A malicious/stale client submitting `module_id: 5` against the Domains controller gets overridden to the Domains module's ID
- This is correct behavior, BUT: what if the Domains module is deleted? `$module` is null → `$validated['module_id']` is NEVER set → `module_id = NULL` → record is invisible

**Can DB enforce?** No — NULL allowed.

**Can validation enforce?** No — module_id is nullable in requests.

**Can code enforce?** Yes — currently overwrites the request value.

**Can tests enforce?** No specific test for this override behavior.

**Can monitoring detect?** Records with null module_id after creation.

**Rating: RISKY** — The override is correct design, but the silent failure path when module is missing creates invisible records.

---

### 2.3 `userOwnedFilter()` APPLIES GLOBAL SCOPE IN `__construct`

**Where:** Web controllers call `$this->userOwnedFilter()` in constructor, which calls `RbacScope::apply()`.

**Assumption:** The RBAC scope is applied for ALL methods in the controller, and later route-model-binding respects it.

**Who depends on it:** Every method in affected controllers.

**What breaks:**
- If `RbacScope::apply()` is called before the model is fully resolved, the scope might ignore route-model-bound instances
- The scope applies to ALL queries on that model, including internal service queries

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — global scope pattern is reliable.

**Can tests enforce?** Yes — scope is tested.

**Rating: SAFE** — Standard Laravel global scope pattern.

---

## 3. SERVICE LAYER ASSUMPTIONS

### 3.1 SERVICES ARE STATELESS SINGLETONS

**Where:** All service classes (AssetService, DomainService, etc.) injected via constructor with no instance state.

**Assumption:** Services are stateless and can be safely shared across requests.

**Who depends on it:** The container (auto-resolves).

**What breaks:** If a service stores request-scoped data as instance property, data leaks between requests.

**Can DB enforce?** N/A.

**Can validation enforce?** N/A.

**Can code enforce?** Code review.

**Can tests enforce?** Hard to test.

**Can monitoring detect?** Hard to detect — subtle data corruption.

**Rating: SAFE** — Current services are stateless by inspection.

---

### 3.2 `RenewalSyncService::sync()` ASSUMES TABLE NAME MATCHES MODULE SLUG

**Where:** `RenewalSyncService::sync($model)`:
```php
$moduleId = Module::where('slug', $model->getTable())->value('id');
```

**Assumption:** The model's database table name (e.g., `domains`) is exactly equal to the module's slug (e.g., `domains`).

**Who depends on it:** All 9 Web CRUD controllers call `RenewalSyncService::sync()` after store/update.

**What breaks:**
- If a table is renamed but the module slug isn't updated (or vice versa), `$moduleId` returns null
- The ExpiryTracker is created with `module_id = null`
- The record becomes invisible in module-scoped queries

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — could validate slug matches table name.

**Can tests enforce?** No — the slug-table mapping is assumed correct.

**Can monitoring detect?** ExpiryTrackers with null module_id.

**Rating: RISKY** — Tight coupling between DB schema and application configuration.

---

## 4. MIGRATION ORDERING ASSUMPTIONS

### 4.1 MIGRATION TIMESTAMPS GUARANTEE EXECUTION ORDER

**Where:** 57 migration files, all prefixed with timestamps.

**Assumption:** The timestamp prefix ensures correct application order.

**Who depends on it:** All migrations.

**What breaks:**
- `2026_05_23_121531_create_module_role_permissions_table.php` adds FK to `roles.id` — but `roles` table is NOT created by any migration in this project (created by Tyro package)
- If Tyro package migration runs in a different batch, the FK constraint fails
- If someone runs `migrate:fresh`, all FKs to Tyro tables break during table recreation

**Can DB enforce?** Yes — FK constraint error on create.

**Can validation enforce?** No.

**Can code enforce?** No — relies on third-party package migration order.

**Can tests enforce?** Yes — `RefreshDatabase` runs all migrations.

**Can monitoring detect?** Migration command would fail.

**Rating: RISKY** — Works because Tyro's migrations run as part of `composer install` + `php artisan migrate`, but fragile.

---

### 4.2 `nullableMorphs()` COLUMNS HAVE NO FK ENFORCEMENT

**Where:** Multiple migrations use `nullableMorphs()` for polymorphic relations (notes, activity log, attachments, expiry tracker trackable).

**Assumption:** The referenced `subject_type`/`subject_id` pair always points to a real record.

**Who depends on it:** Activity log, notifications, expiry tracker trackable, notes.

**What breaks:** Orphaned polymorphic references with no FK constraint — JOINs return null, but data is inconsistent.

**Can DB enforce?** No — by design (polymorphic FKs aren't supported).

**Can validation enforce?** No.

**Can code enforce?** Application code must handle missing relations gracefully.

**Can tests enforce?** Partial.

**Can monitoring detect?** Periodic consistency check.

**Rating: SAFE** — Standard Laravel polymorphic pattern. No FK is normal.

---

## 5. CACHE INVALIDATION PATTERNS

### 5.1 `AppServiceProvider` INCREMENTS VERSION ON EVERY MODEL SAVED/DELETED

**Where:**
```php
Model::saved(fn() => Cache::increment('dashboard:version'));
Model::deleted(fn() => Cache::increment('dashboard:version'));
```

**Assumption:** Every model save should invalidate ALL dashboard caches for ALL users.

**Who depends on it:** DashboardController, DashboardPageTest.

**What breaks:**
- A background task updating 1000 records triggers 1000 cache invalidations
- A field update on a record that doesn't appear in dashboards (e.g., updating a note) still invalidates all dashboards
- Cache stampede: when version increments, ALL user dashboards recalculate simultaneously

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Yes — could use per-entity cache keys.

**Can tests enforce?** Cache test exists.

**Can monitoring detect?** Dashboard response time spikes.

**Rating: RISKY** — Over-invalidation causes unnecessary recomputation. Under load, this creates a cache stampede risk.

---

### 5.2 SEARCH USES RAW `LIKE` QUERIES

**Where:** ~15 controllers use `LIKE "%{term}%"` in search queries.

**Assumption:** User input is safe for LIKE queries because Laravel's query builder uses parameterized queries (which IS correct for preventing SQL injection).

**Who depends on it:** All search endpoints.

**What breaks:** Performance — `LIKE '%term%'` cannot use indexes. On large datasets, searches will be slow or timeout.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Could add full-text index usage.

**Can tests enforce?** Performance tests needed.

**Can monitoring detect?** Slow query logs.

**Rating: RISKY** — Functionally correct, but will not scale beyond ~10k records per table.

---

## 6. VIEW COMPOSER ASSUMPTIONS

### 6.1 SIDEBAR ASSUMES SAME RBAC AS CONTROLLERS

**Where:** `SidebarComposer` calls `getAccessibleModuleIds('read')` for sidebar visibility.

**Assumption:** The sidebar's RBAC check matches the controller's RBAC scope.

**Who depends on it:** All authenticated users navigating the app.

**What breaks:** If sidebar and controller RBAC diverge, users see links to modules they can't access (confusing) or don't see links to modules they CAN access (frustrating).

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Shared source (same `getAccessibleModuleIds()` call).

**Can tests enforce?** `NavigationTest` exists.

**Can monitoring detect?** User confusion.

**Rating: SAFE** — Single source of truth confirmed by architecture audit.

---

## 7. THIRD-PARTY DEPENDENCY ASSUMPTIONS

### 7.1 L5-SWAGGER ANNOTATIONS MATCH ACTUAL CODE

**Where:** API controllers have `#[OA]` Swagger annotations with request/response schemas.

**Assumption:** The annotations accurately describe the actual endpoints.

**Who depends on it:** API documentation consumers, auto-generated clients.

**What breaks:** Outdated annotations = wrong docs. Clients generated from bad docs fail at runtime.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** No — annotations are comments.

**Can tests enforce?** No automated check.

**Can monitoring detect?** Support tickets.

**Rating: RISKY** — No automated validation that annotations match actual serialization.

---

### 7.2 ACTIVITY LOG PACKAGE CONFIGURATION MATCHES MIGRATIONS

**Where:** `config/activitylog.php` references `activity_log` table, `causer`/`subject` morphs.

**Assumption:** Spatie Activitylog package compatibility with the project's migration columns.

**Who depends on it:** All activity logging: password reveals, CRUD operations, auth events.

**What breaks:** Package update (major version) could change column naming expectations.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Package version constraint in composer.json.

**Can tests enforce?** Tests that assert activity logged.

**Can monitoring detect?** Activity log queries fail.

**Rating: SAFE** — Pinned via composer.json.

---

## 8. DEVELOPMENT vs PRODUCTION ASSUMPTIONS

### 8.1 ENVIRONMENT FILE VALUES ARE OVERRIDDEN IN PRODUCTION

**Where:** `.env.example` has `APP_ENV=local`, `APP_DEBUG=true`, `DB_DATABASE=tyro_project`, `DB_USERNAME=root`.

**Assumption:** These values will be changed before production deployment.

**Who depends on it:** All environment-dependent behavior.

**What breaks:** Fresh install with default `.env.example` as `.env` exposes debug info, wrong database, root user access.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Could check APP_ENV in boot.

**Can tests enforce?** No.

**Can monitoring detect?** `APP_DEBUG=true` exposes stack traces.

**Rating: RISKY** — Standard Laravel risk. Mitigated by deployment guide.

---

### 8.2 DEMO SEEDER NEVER RUNS IN PRODUCTION

**Where:** `DatabaseSeeder::run()`:
```php
if (! app()->environment('testing')) {
    $this->call(DemoDataSeeder::class);
}
```

**Assumption:** Anyone running `php artisan db:seed` in production knows they're getting demo data.

**Who depends on it:** All users if accidentally seeded.

**What breaks:** The demo seeder skips in `testing` but NOT in `production`. Running `migrate --seed` on production will insert demo data.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Could check `app()->environment('production')` and abort.

**Can tests enforce?** No — skip in testing.

**Can monitoring detect?** After the fact, when users see test data.

**Rating: DANGEROUS** — `php artisan migrate --seed` in production inserts demo data. Should have `if (!app()->environment('production'))` guard instead of just skipping `testing`.

---

## 9. FRONTEND-BUILD ASSUMPTIONS

### 9.1 VITE BUILD OUTPUT IS COMMITTED

**Where:** `public/build/` directory contains compiled assets.

**Assumption:** The build directory is in version control and deployed with the rest of the code.

**Who depends on it:** All frontend rendering.

**What breaks:** If `public/build/` is gitignored and not built during deployment, the app loads unstyled HTML. Vite dev server is not running in production.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** No — deployment process.

**Can tests enforce?** `npm run build` passes.

**Can monitoring detect?** Users see unstyled pages.

**Rating: SAFE** — `npm run build` is documented in deploy guide.
