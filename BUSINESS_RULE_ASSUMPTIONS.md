# BUSINESS RULE ASSUMPTIONS

> **Principal Architect Analysis** — 2026-07-04
> Every hidden business rule assumption in the codebase.

---

## 1. DOMAIN & MODULE ASSUMPTIONS

### 1.1 MODULE SLUG NEVER CHANGES

**Where:**
- `Web\DomainController::moduleSlug()` returns `'domains'`
- `RenewalSyncService::sync()`: `Module::where('slug', $model->getTable())` — assumes slug equals table name
- `FeatureModuleSeeder`: seeds modules with specific slugs

**Business rule:** A module's slug is its permanent identity, tied to both the controller code AND the database table name.

**Who depends on it:** Every Web CRUD controller, renewal sync, sidebar composer, RBAC scoping.

**What breaks:**
- Renaming a module slug breaks: 9 Web controllers, permissions grid, sidebar, renewal sync
- No alias mechanism, no migration path
- Table name must also change to keep sync working

**Can DB enforce?** Slug uniqueness, but not coupling to table names.

**Can validation enforce?** No.

**Can code enforce?** Could decouple slug from table name.

**Can tests enforce?** No — assumes coupling is correct.

**Can monitoring detect?** Records created with wrong module_id.

**Rating: DANGEROUS** — Three-way coupling (slug / table name / controller string) with zero decoupling. Changing any module slug requires updating code, database, and potentially seeders.

---

### 1.2 EVERY FEATURE HAS AT LEAST ONE MODULE

**Where:** `FeatureService`, `FeatureController::show()`: loads `modules` relation.

**Business rule:** Features are containers for modules, never empty.

**Who depends on it:** Feature show views, module permission assignment UI.

**What breaks:** If a feature has no modules (deleted all modules from a feature), the feature page shows empty. No graceful message.

**Can DB enforce?** No — `modules.feature_id` can be null after cascade.

**Can validation enforce?** No.

**Can code enforce?** FeatureController::destroy() could check.

**Can tests enforce?** No.

**Rating: SAFE** — Empty features are cosmetic, not functional.

---

## 2. SERVICE PROVIDER ASSUMPTIONS

### 2.1 EVERY SERVICE RECORD HAS A SERVICE PROVIDER

**Where:** 7 tables have `service_provider_id` FK with `nullOnDelete`.

**Business rule:** A service (Domain, Hosting, etc.) can optionally reference a ServiceProvider, but the code often treats it as important metadata.

**Who depends on it:** Index views show provider names, filtering by provider, renewal sync.

**What breaks:**
- If a ServiceProvider is deleted, 7+ tables get nulled FKs
- Views display empty/incomplete provider info
- No UX warning before deletion

**Can DB enforce?** `nullOnDelete` — data survives, but metadata lost.

**Can validation enforce?** No — optional by design.

**Can code enforce?** ServiceProviderController::destroy() checks counts in Web controller only.

**Can tests enforce?** Partial — delete tests exist.

**Can monitoring detect?** Provider deletion logged in activity.

**Rating: SAFE** — Null FK is by design. Only risk is Web vs API inconsistency (API doesn't check).

---

### 2.2 SERVICE PROVIDER NAMES CAN MATCH LEGACY `provider` STRINGS

**Where:** Migration `2026_06_22_111154`: backfills ServiceProvider records from `provider` column.

**Business rule:** The old string `provider` column on each service table can be matched 1:1 to a new ServiceProvider record by name.

**What breaks:** If two services had slightly different provider strings (e.g., "GoDaddy" vs "GoDaddy LLC"), they create separate ServiceProvider records instead of one shared one.

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** No — migration is one-time.

**Can tests enforce?** No.

**Can monitoring detect?** After the fact.

**Rating: SAFE** — Migration already run. Idempotent `updateOrCreate`.

---

## 3. USER & ROLE ASSUMPTIONS

### 3.1 SUPER-ADMIN IS THE ONLY POWER-USER ROLE

**Where:** All permission checks use `$user->hasRole('super-admin')`.

**Business rule:** Only users with the `super-admin` role can bypass all permissions. There's no "admin" bypass.

**Who depends on it:** Entire authorization system.

**What breaks:**
- Users with the `admin` role are NOT super-admin — they must have explicit module permissions
- If seeders forget to create `super-admin`, no user can manage the system
- `UserCloneTest` verifies cloning preserves role + overrides

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** String-literal role name in 40+ locations.

**Can tests enforce?** Seeders create super-admin role.

**Can monitoring detect?** Users with super-admin role auditable.

**Rating: RISKY** — Single point of failure. If super-admin role is accidentally deleted or renamed, no one can administer the system.

---

### 3.2 AT LEAST ONE SUPER-ADMIN ALWAYS EXISTS

**Where:** `Web\UserController::destroy()`: counts super-admin users before allowing deletion.

**Business rule:** The system must never be left without a super-admin.

**Who depends on it:** User management, overall system administration.

**What breaks:**
- The check is in the Web controller ONLY
- API `UsersController::destroy()` does NOT check
- Bulk action service does NOT check
- Direct DB deletion bypasses

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Only in Web controller. API and bulk actions have no guard.

**Can tests enforce?** `UserModulePermissionTest::test_cannot_delete_last_super_admin` tests Web path only.

**Can monitoring detect?** After the fact.

**Rating: DANGEROUS** — The last super-admin can be deleted via API, bulk action, or CLI, leaving the system unmanageable. Protection exists only in the Web delete path.

---

## 4. EXPIRY TRACKER ASSUMPTIONS

### 4.1 EVERY EXPIRY TRACKER'S `expiry_date` IS MEANINGFUL

**Where:** `ExpiryTracker::getExpiryDateAttribute()`:
```php
if ($this->relationLoaded('trackable') && $this->trackable?->expiry_date) {
    return $this->trackable->expiry_date;
}
return $this->attributes['expiry_date'];
```

**Business rule:** If a trackable relation is loaded AND has an `expiry_date`, use THAT value instead of the tracker's own `expiry_date`.

**What breaks:**
- The trackable's `expiry_date` might be in a different format (bigint timestamp vs date string)
- The trackable might not have an `expiry_date` column at all
- The accessor OVERRIDES the locally stored value — unexpected in a form where user manually sets the tracker's date

**Can DB enforce?** No.

**Can validation enforce?** No.

**Can code enforce?** Could type-check the column exists on trackable class.

**Can tests enforce?** No test for this accessor override.

**Can monitoring detect?** Tracker shows wrong expiry date (silent data corruption).

**Rating: DANGEROUS** — An accessor that silently overrides a stored database value based on a loaded relation is a cognitive trap. Forms showing the "real" expiry date might be showing the trackable's date, not what was saved.

---

### 4.2 `notify_days_before` IS ALWAYS AN ARRAY

**Where:** `ExpiryTracker` casts `notify_days_before` as `array`. `RenewalNotificationService` iterates it.

**Business rule:** Notification days are always an array of integers (e.g., `[30, 15, 7, 1]`).

**Who depends on it:** `RenewalNotificationService`, expiry reminder logic.

**What breaks:** If `notify_days_before` is stored as `null` in DB, the array cast returns `[]` (empty array) — no notifications sent. If stored as a string, cast may fail.

**Can DB enforce?** Migration defaults to `'[30,15,7,1]'` — JSON string.

**Can validation enforce?** `StoreExpiryTrackerRequest` validates each item is `1,7,15,30`.

**Can code enforce?** Yes — array cast handles JSON.

**Can tests enforce?** Partially.

**Rating: SAFE** — Validated at input, cast at model.

---

## 5. ASSET MANAGEMENT ASSUMPTIONS

### 5.1 `asset_tag` IS AUTO-GENERATED AND UNIQUE

**Where:** `AssetService::generateTag()`: queries highest existing tag, increments.

**Business rule:** Asset tags follow pattern `AST0001`, `AST0002`, ...

**Who depends on it:** Asset creation, asset identification.

**What breaks:**
- Race condition: two concurrent asset creates could get the same tag
- No DB-level locking or optimistic locking
- Prefix change (`AST` → `AST-`) breaks existing tag format

**Can DB enforce?** `asset_tag` is unique — second insert fails.

**Can validation enforce?** No — auto-generated.

**Can code enforce?** DB transaction + retry on unique violation.

**Can tests enforce?** Auto-generation tests exist.

**Can monitoring detect?** Unique constraint violation error.

**Rating: RISKY** — No concurrency protection in tag generation. Race condition under concurrent asset creation.

---

### 5.2 ASSET STATUS VALUES ARE CONTROLLED

**Where:** `StoreAssetRequest::rules()`: `'in:available,assigned,lost,decommissioned'`. `AssetService::aggregateStatus()`: counts these exact strings.

**Business rule:** Assets can only be in one of 4 statuses.

**Who depends on it:** Asset dashboard widgets, reports, filtering.

**What breaks:** If a new status is added to the DB outside validation (migration, direct SQL), validation blocks it but dashboard counts may be off.

**Can DB enforce?** No — column is string, no ENUM.

**Can validation enforce?** Yes — `in:` rule.

**Can code enforce?** Validation + model accessor.

**Can tests enforce?** Asset management tests.

**Rating: SAFE** — Controlled vocabulary enforced at input.

---

## 6. VAULT ASSUMPTIONS

### 6.1 `encrypted_password` IS STORED PRE-ENCRYPTED

**Where:** `VaultEntry` does NOT have an `encrypted` cast. Passwords are encrypted via `VaultService` before saving.

**Business rule:** The vault stores only encrypted passwords. Decryption uses `Crypt::decryptString()`.

**Who depends on it:** Password reveal feature, vault search, vault export.

**What breaks:**
- If raw password is stored accidentally, subsequent decryption attempt throws `DecryptException`
- If encryption key changes (APP_KEY rotated), all vault passwords become unreadable
- No key rotation strategy

**Can DB enforce?** No — cannot distinguish encrypted blob from plaintext.

**Can validation enforce?** No.

**Can code enforce?** VaultService::create() encrypts before save.

**Can tests enforce?** Create-and-reveal tests exist.

**Can monitoring detect?** Decryption failures logged.

**Rating: DANGEROUS** — APP_KEY rotation renders all vault passwords inaccessible. No key rotation documentation or strategy exists.

---

### 6.2 `decryptPassword()` GRACEFULLY HANDLES EMPTY

**Where:** `VaultEntry::decryptPassword()`:
```php
if (empty($this->encrypted_password)) {
    throw new \RuntimeException('No password stored.');
}
```

**Business rule:** All vault entries should have a password.

**Who depends on it:** Password reveal, password masked view.

**What breaks:** If a vault entry is created without a password (possible via `required_without` logic in StoreVaultRequest), decrypt throws an exception.

**Can DB enforce?** No — column is nullable.

**Can validation enforce?** `required_without` ensures at least one of `password`/`encrypted_password` — but on update both are independently nullable (UpdateVaultRequest).

**Can code enforce?** Exception with graceful catch in accessor.

**Can tests enforce?** No test for empty password vault entry.

**Can monitoring detect?** RuntimeException in logs.

**Rating: RISKY** — Update path can clear both password fields, making the entry unreadable.

---

## 7. TASK ASSUMPTIONS

### 7.1 TASK `status` VALUES ARE A CONTROLLED SET

**Where:** `StoreTaskRequest`: `'in:pending,in_progress,completed,cancelled'`. Dashboard counts group by status.

**Business rule:** Tasks flow through: pending → in_progress → completed (or cancelled).

**Who depends on it:** `CheckOverdueTasksCommand`, dashboard task widgets, task kanban board.

**What breaks:**
- Adding a new status requires updating validation, dashboard widgets, command logic
- The kanban board groups by status — new status appears as new column automatically

**Can DB enforce?** No — column is string.

**Can validation enforce?** Yes — `in:` rule.

**Can code enforce?** Validation + model constant.

**Can tests enforce?** Task tests exist.

**Rating: SAFE** — Enforced at input, dashboard gracefully handles unexpected values.

---

## 8. NOTIFICATION ASSUMPTIONS

### 8.1 NOTIFICATION DATA ALWAYS CONTAINS `type`, `item_type`, `item_id`

**Where:** `User::getUnreadNotificationCountAttribute()`:
```php
$data = $notification->data;
match ($data['type'] ?? null) { ... }
$class::whereIn('id', $ids)->whereIn('module_id', $accessibleModuleIds);
```

**Business rule:** Every notification stores structured JSON with specific keys.

**Who depends on it:** Notification badge count in UI.

**What breaks:**
- If a notification is sent without these keys, `$data['type'] ?? null` returns null → `match(null)` hits default `true` → notification included in count
- If `$data['item_type']` class doesn't have a `module_id` column, the `whereIn('module_id', ...)` SQL fails
- If `$data['item_type']` class doesn't exist, `new $class()` fails

**Can DB enforce?** No — `data` is text column.

**Can validation enforce?** No — notifications generated internally.

**Can code enforce?** Try/catch around notification parsing.

**Can tests enforce?** No test for malformed notification data.

**Can monitoring detect?** RuntimeException occurs when user loads their profile.

**Rating: DANGEROUS** — Malformed notification data causes fatal error when any user visits their profile page. No graceful degradation.

---

## 9. WEBHOOK ASSUMPTIONS

### 9.1 WEBHOOK URLS ARE REACHABLE

**Where:** `WebhookService::dispatch()`: calls `Http::post($webhook->url, ...)`.

**Business rule:** Webhook URLs are valid and reachable HTTP endpoints.

**Who depends on it:** Task creation/update, vault password reveal, expiry notifications.

**What breaks:**
- Network timeout: HTTP call hangs or fails silently
- Invalid URL: Guzzle exception
- Slow response: blocks the request thread (unless queued, which is not configured)

**Can DB enforce?** No.

**Can validation enforce?** URL format validation on store/update.

**Can code enforce?** Could add timeout, async dispatch.

**Can tests enforce?** No — external dependency.

**Can monitoring detect?** Webhook failures logged.

**Rating: RISKY** — Webhooks fire synchronously in the same request. A slow/unreachable endpoint blocks the main thread.

---

## 10. MONITORING ASSUMPTIONS

### 10.1 ALL 8 MONITORED MODELS HAVE `monitoring_url` AND `last_ping_at`

**Where:** `MonitorCheckCommand`, `MonitorService`.

**Business rule:** Every Domain, Hosting, VPS, VoIP, ServiceProvider, DomainEmail, OtherService, and ExpiryTracker has a monitoring URL.

**Who depends on it:** `monitor:check` command.

**What breaks:** Models without `monitoring_url` are correctly skipped (`whereNotNull('monitoring_url')`). But `last_ping_at` update assumes column exists on all 8.

**Can DB enforce?** Columns exist via migration.

**Can validation enforce?** No — monitoring_url is optional.

**Can code enforce?** `whereNotNull` before processing.

**Can tests enforce?** Monitor test exists.

**Rating: SAFE** — Correctly filters null monitoring_url.

---

## 11. DATA DEFAULTS ASSUMPTIONS

### 11.1 `status` DEFAULTS TO `'active'` ON ALL SERVICE MODELS

**Where:** 7+ migration tables set `->default('active')`.

**Business rule:** Newly created services start as active.

**Who depends on it:** Dashboard counts, active service reports.

**What breaks:**
- If default changes, old records keep old status
- Some models allow status values that aren't meaningful for "active" (e.g., `pending_transfer` for domains)

**Can DB enforce?** Default at DB level.

**Can validation enforce?** Status field validated with allowed list.

**Can code enforce?** Model attribute default.

**Can tests enforce?** Create tests verify default status.

**Rating: SAFE** — Consistent across all service models.
