# PRODUCTION EDGE CASES

> **Principal Architect Analysis** — 2026-07-04
> Edge cases that WILL occur in production and are NOT handled.

---

## 1. DATA EDGE CASES

### 1.1 RECORD WITH NULL `module_id`

**Scenario:** A record is created without a module_id (Web controller's moduleSlug() returns no match, API's validated data lacks module_id, or direct DB insert).

**Impact:**
- Invisible to all non-super-admin users via `index()` (module-scoped queries)
- NOT invisible via `show()`/`update()`/`destroy()` (these use `user_id` ownership, not module)
- Creates a ghost record: visible only if you know the exact ID and use the API show endpoint

**Frequency:** Medium — any Web controller store() where module is missing.

**Detection:** `SELECT COUNT(*) FROM domains WHERE module_id IS NULL`

**Mitigation:** Add `whereNotNull('module_id')` filter, or add DB NOT NULL constraint.

**Rating: DANGEROUS** — Ghost records silently accumulate.

---

### 1.2 RECORD OWNED BY DELETED USER (SOFT DELETE)

**Scenario:** A user is soft-deleted. Their records via `user_id` FK have `cascadeOnDelete` (hard delete only). Since soft-delete doesn't cascade, records persist with `user_id` pointing to a soft-deleted user.

**Impact:**
- `$record->user` returns null
- Any code accessing `$record->user->name` errors
- Activity logs show "causer: null" for soft-deleted users
- Dashboard widgets that join on user drop these records

**Frequency:** High — every soft-deleted user leaves orphaned records.

**Detection:** `SELECT * FROM domains WHERE user_id IN (SELECT id FROM users WHERE deleted_at IS NOT NULL)`

**Mitigation:** Views should handle nullable user display (`$record->user?->name ?? 'Deleted User'`). Some views already do this, but not all.

**Rating: RISKY** — User soft-deletion is a common admin action. Null user references in views cause display errors.

---

### 1.3 RACE CONDITION ON ASSET TAG GENERATION

**Scenario:** Two users create assets simultaneously. Both queries find the same max `asset_tag` and generate the same tag.

**Impact:** Second insert fails with unique constraint violation. User sees 500 error. Asset not created.

**Frequency:** Low — requires concurrent asset creation.

**Detection:** MySQL duplicate entry error for `asset_tag` unique index.

**Mitigation:** Wrap tag generation in DB transaction + retry on duplicate. Or use DB auto-increment for tag.

**Rating: RISKY** — User-facing error under concurrent load.

---

### 1.4 EXPIRY TRACKER WITH TRACKABLE BUT SELF-DATE IS OLDER

**Scenario:** An ExpiryTracker has a `trackable` (polymorphic relation to Domain). The Domain's `expiry_date` is 2026-12-31. The tracker's own `expiry_date` is 2025-01-01. The accessor returns the Domain's date, hiding the tracker's actual stored date.

**Impact:**
- User sets tracker expiry to 2025-01-01 (wants to be warned earlier)
- Accessor returns Domain's 2026-12-31
- Notification doesn't fire in 2025
- User misses renewal

**Frequency:** Any time a user manually sets a different expiry date than the source record.

**Detection:** Compare `expiry_date` column vs trackable's expiry_date.

**Mitigation:** Accessor should have an opt-out, or only use trackable date if tracker date is null.

**Rating: DANGEROUS** — Accessor silently overrides user-set values. Unexpected data loss.

---

### 1.5 NULL PIVOT DATA IN BELONGSTOMANY

**Scenario:** `task_user` pivot table has `assigned_at` with `useCurrent()`. But if a task is assigned programmatically without triggering timestamps, `assigned_at` could be null.

**Impact:** Code iterating `$task->assignees` may access `$pivot->assigned_at` → null.

**Frequency:** Low — Eloquent's `attach()` sets timestamps by default.

**Detection:** `SELECT * FROM task_user WHERE assigned_at IS NULL`

**Rating: SAFE** — `useCurrent()` and Eloquent's default sync behavior prevent nulls.

---

## 2. CONCURRENCY EDGE CASES

### 2.1 TWO ADMINS MODIFY SAME RECORD SIMULTANEOUSLY

**Scenario:** Admin A and Admin B both open the same domain edit form. A saves first, B saves 2 seconds later.

**Impact:** B's save overwrites A's changes with no warning. No optimistic locking.

**Frequency:** Common in team environments.

**Detection:** No detection — last write wins silently.

**Mitigation:** Add `updated_at` timestamp check on update (optimistic locking).

**Rating: RISKY** — Silent data loss under team usage.

---

### 2.2 CACHE STAMPEDE ON DASHBOARD VERSION INCREMENT

**Scenario:** A bulk operation updates 500 services. Each model's `saved` event increments `dashboard:version` 500 times. All user dashboards become stale and recalculate simultaneously on next request.

**Impact:** N+1 dashboard recalculations hammer the database.

**Frequency:** Rare — bulk operations are explicit.

**Detection:** Dashboard response time spike after bulk operations.

**Mitigation:** Batch version increment (e.g., only on commit, not per-model). Or debounce the version key.

**Rating: RISKY** — Performance under bulk operations, not data integrity.

---

### 2.3 SCHEDULED TASK OVERLAP

**Scenario:** `monitor:check` processes 1000 URLs and takes 90 minutes. `hourly()` schedule fires every 60 minutes. After 60 minutes, a second process starts.

**Impact:**
- Two concurrent monitor checks ping URLs simultaneously
- Both update `last_ping_at` — race condition on timestamps
- Double the load on monitored services
- Duplicate failure notifications

**Frequency:** Certain to happen as the service list grows.

**Detection:** Multiple monitor processes running simultaneously.

**Mitigation:** `->withoutOverlapping()` on all scheduled commands.

**Rating: RISKY** — Inevitable as data grows. Duplicate notifications annoy users.

---

## 3. AUTHENTICATION EDGE CASES

### 3.1 SANCTUM TOKEN EXPIRATION DURING LONG SESSION

**Scenario:** User opens dashboard, walks away for 9 hours (token TTL = 480 min). Next API call returns 401.

**Impact:** User must re-authenticate. SPA loses state. No "session expired" notification — just silent failure.

**Frequency:** Common for overnight sessions.

**Detection:** 401 responses from API.

**Mitigation:** Interceptor on 401 to show toast "Session expired, please login again."

**Rating: SAFE** — Standard behavior. Token expiry is intentional.

---

### 3.2 SUSPENDED USER AUTHENTICATED DURING ACTIVE SESSION

**Scenario:** User is logged in. Admin suspends the user's account. The user's current Sanctum token is still valid.

**Impact:** Suspended user can continue using the API until their token expires and they try to re-authenticate. The `suspended` middleware checks on every request — but `Auth::attempt()` in login checks `suspended_at`.

**Frequency:** Moderate — admin suspends user while user is active.

**Detection:** `LoginAudit` records show suspended users still accessing the system.

**Mitigation:** The `suspended` middleware already checks `$request->user()->suspended_at` on every API request. This is properly handled.

**Rating: SAFE** — Middleware correctly blocks suspended users on every request.

---

### 3.3 PASSWORD RESET FOR NON-EXISTENT EMAIL

**Scenario:** User submits forgot-password form with an email that doesn't exist in the system.

**Impact:** Laravel's `Password::sendResetLink()` returns `Password::INVALID_USER`. The controller doesn't distinguish between "email sent" and "email not found" — returns a generic success message (security best practice). But the frontend might show a confusing message.

**Frequency:** Common — mistyped emails.

**Detection:** No detection — intentional security behavior.

**Rating: SAFE** — Security best practice. Generic message prevents email enumeration.

---

### 3.4 LOGIN RATE LIMIT LOCKOUT

**Scenario:** User fails login 5 times within 1 minute. Lockout occurs. Legitimate user is blocked for 60 seconds.

**Impact:** User cannot log in for 1 minute. No notification that they're rate-limited. Support tickets increase.

**Frequency:** Common — especially after password reset.

**Detection:** `LoginAudit` events show repeated failures.

**Rating: SAFE** — Intentional security feature. 60-second lockout is appropriate.

---

## 4. PERMISSION EDGE CASES

### 4.1 USER WITH MULTIPLE ROLES HAS CONFLICTING PERMISSIONS

**Scenario:** User has both "editor" (can_read=true, can_create=true) and "user" (can_read=true, can_create=false) roles.

**Impact:** `getAccessibleModuleIds('read')` unions both roles — can_read is true (merge OR). But `canOnModule('create')` — what happens?

**Code:** `HasModulePermissions::canOnModule()`:
```php
$rolePermission = $module->rolePermissions()->whereIn('role_id', $this->getRoleIds())->first();
```
This gets the FIRST matching role permission, not the most permissive. If "user" role's `can_create=false` is found first, the user is denied even though "editor" allows it.

**Frequency:** Any user with multiple roles that have different permission values.

**Detection:** User reports unexpected "Access Denied" errors.

**Mitigation:** Use `max()` across role permissions, not `first()`. Or merge with OR logic.

**Rating: DANGEROUS** — `first()` instead of `max()`/`any()` means the result is ORDER-DEPENDENT. Changing role IDs or query order silently changes user permissions.

---

### 4.2 USER OVERRIDE DENY ON NON-EXISTENT MODULE

**Scenario:** A `UserModulePermission` row exists for a module that was later deleted (hard delete via cascade).

**Impact:** `getAccessibleModuleIds()` builds the list from `Module::whereHas('rolePermissions', ...)` — deleted modules don't match. Then overrides are applied, which reference the deleted module_id. The array_diff removes an already-absent ID (no-op). Safe.

**Frequency:** Low — module hard-deletion is unlikely.

**Rating: SAFE** — Override references are idempotent for deleted modules.

---

### 4.3 EXPORT PERMISSION CHECK BUT NO MODULE EXPORT PERMISSION

**Scenario:** User has `can_read` on Module A but NOT `can_export`. User tries to export Module A data.

**Impact:** `ExportController::export()` checks `$user->canOnModule($module, 'export')` → denied. User gets 403.

**Frequency:** Common — export is a separate permission from read.

**Detection:** User reports export failure.

**Rating: SAFE** — Correctly enforced by permission system.

---

## 5. DATA MIGRATION EDGE CASES

### 5.1 `migrate:fresh` DELETES ALL DATA

**Scenario:** A developer or deployment pipeline runs `php artisan migrate:fresh`.

**Impact:** All tables are dropped and recreated. All data is lost. Tyro tables (roles, permissions) also dropped.

**Frequency:** Development mistake, pipeline misconfiguration.

**Detection:** Immediate — users see empty system.

**Mitigation:** Production deployment guide specifically warns against `migrate:fresh`. Use `php artisan migrate` only.

**Rating: SAFE** — Documented risk. Cannot protect against all human error.

---

### 5.2 SEEDER RUNS TWICE — DUPLICATE DEMO DATA

**Scenario:** `php artisan db:seed` run twice. `DemoDataSeeder` does NOT use `updateOrCreate`.

**Impact:** Duplicate demo records (domains, hostings, etc.) created.

**Frequency:** Development only (production guard exists).

**Detection:** Duplicate service names in lists.

**Rating: SAFE** — Guarded by `!app()->environment('testing')` (should also guard `production`).

---

## 6. NETWORK EDGE CASES

### 6.1 HOSTED DOMAIN IN MONITORING TIMES OUT

**Scenario:** `monitor:check` hits a URL that hangs for 30+ seconds.

**Impact:** The entire check command stalls. Scheduler overlaps. Queue (if used) accumulates blocked jobs.

**Frequency:** Common — external services go down.

**Detection:** Long-running monitor command.

**Mitigation:** HTTP timeout should be configured in `MonitorService::check()`. Current code doesn't show explicit timeout.

**Rating: RISKY** — No explicit HTTP timeout in monitoring. A hanging URL blocks the entire monitor check process.

---

### 6.2 WEBHOOK DELIVERY FAILURE

**Scenario:** Webhook endpoint returns 500 or is unreachable.

**Impact:** `Http::post()` throws exception. The calling code (task creation, vault reveal) fails unless exception is caught.

**Frequency:** Common — third-party endpoints fail.

**Detection:** Exception in logs, webhook `last_fired_at` not updated.

**Rating: RISKY** — Webhook delivery failures propagate to the original action. Creating a task could fail because the webhook endpoint is down.

---

## 7. UI EDGE CASES

### 7.1 USER HAS NO ACCESSIBLE MODULES

**Scenario:** A new user with "user" role and no module permissions logs in.

**Impact:**
- Sidebar shows minimal links (profile, help)
- Dashboard has no service data
- API returns empty arrays
- All "Create" buttons are hidden

**Frequency:** Common for new or read-only users.

**Detection:** User support request "I can't see anything."

**Rating: SAFE** — Graceful empty state. Dashboard may show empty widgets but no error.

---

### 7.2 `per_page=9999` IN API REQUEST

**Scenario:** A user or client sets `per_page=9999` in a GET request.

**Impact:** `min((int) $request->get('per_page', 20), 100)` caps at 100. Query returns 100 records. But pagination metadata says `total: 1500`, `per_page: 100`, `last_page: 15`. Client might not handle pagination correctly.

**Frequency:** Low — mostly API clients.

**Detection:** Client loads partial data.

**Rating: SAFE** — Capped at 100. Pagination metadata is accurate.

---

### 7.3 BROWSER EXTENSION BLOCKS CSRF TOKEN

**Scenario:** Privacy browser extension blocks cookies. Sanctum SPA auth fails. POST requests get 419 CSRF token mismatch.

**Impact:** User cannot create/update/delete anything. Reads still work.

**Frequency:** Uncommon — only with strict privacy extensions.

**Detection:** 419 errors on POST requests.

**Rating: SAFE** — Not an application bug. Clear error message from Laravel.
