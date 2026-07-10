# Final Task Status — OpsPilot v1.0

**Date:** 2026-07-09 | **Last Update:** Session 2
**Pre-existing failures:** RoleTest::test_show_displays_role (unrelated to our changes)
**Total Tasks:** ~160 | **P0:** 14 | **P1:** ~49 | **P2:** ~30
**Progress:** ~105 tasks done (Tiers 1-3 complete, Tier 4 complete)
**Order:** Easy → Hard (do Tier 1 first, then Tier 2, etc.)

---

## TIER 1 — SUPER EASY (5-10 min each, single file, no risk)

Do these all together in one sitting. Each is a one-line or one-file change.

---

### ~~1.1 Move Tinker to require-dev~~ ✅ DONE
**Source:** Security-019 | **Priority:** 🔴 P0 | **Target:** `composer.json`

Already in `require-dev` (not in `require`). No action needed.

---

### ~~1.2 Fix N+1 in TaskController Index~~ ✅ DONE
**Source:** CodeQuality-001 | **Priority:** 🔴 P0 | **Target:** `Web\TaskController.php:84`

`module_id` already in select array at `TaskController.php:88`. No action needed.

---

### 1.3 Set QUEUE_CONNECTION=sync in Production .env
**Source:** Security-004 | **Priority:** 🔴 P0 | **Target:** `.env`

**Note:** Production-only change — set `QUEUE_CONNECTION=sync` in production `.env`. Local `.env` can keep `database`.

---

### 1.4 Set APP_DEBUG=false + APP_ENV=production
**Source:** Security-015 | **Priority:** 🔴 P0 | **Target:** `.env`

**Note:** Production-only change — `.env.example` already has correct values.

---

### 1.5 Session Security Settings
**Source:** Security-009 | **Priority:** 🟡 P1 | **Target:** `.env`

**Note:** Production-only change — `.env.example` already has `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`.

---

### 1.6 Configure CORS for Production
**Source:** Security-012 | **Priority:** 🟡 P1 | **Target:** `config/cors.php`

Already uses `env('FRONTEND_URL')` — just set `FRONTEND_URL` in production `.env`. No code change needed.

---

### ~~1.7 Fix Hardcoded Swagger URL~~ ✅ DONE
**Source:** Security-016 | **Priority:** 🔵 P2 | **Target:** `config/l5-swagger.php`

`config/l5-swagger.php:269` already uses `env('L5_SWAGGER_CONST_HOST', env('APP_URL'))`. `.env.example` fixed to use `${APP_URL}`. No action needed.

---

### ~~1.8 Remove Unused Imports~~ ✅ DONE
**Source:** CodeQuality-008 | **Priority:** 🔵 P2 | **Target:** `AppServiceProvider.php`, `config/database.php`

No unused imports found — `Pdo\Mysql` not imported in `database.php`, no empty `//` comment in `AppServiceProvider.php`. Already clean.

---

### ~~1.9 Delete Dead Views + Legacy Assets~~ ✅ DONE
**Source:** CodeQuality-009 | **Priority:** 🔵 P2 | **Target:** `welcome.blade.php`, `public/css/help-center.css`, `public/js/help-center.js`

Files don't exist — already deleted. No action needed.

---

### ~~1.10 Add Post-Deploy Caching Script~~ ✅ DONE
**Source:** Security-011 | **Priority:** 🟡 P1 | **Target:** `composer.json` → `scripts`

Added `route:cache`, `config:cache`, `view:cache`, `event:cache` to `post-deploy` script.

---

### ~~1.11 Add once() Caching to getAccessibleModuleIds()~~ ✅ DONE
**Source:** RBAC-013 | **Priority:** 🔵 P2 | **Target:** `app/Traits/HasModulePermissions.php`

Wrapped with `once()` at `HasModulePermissions.php:110`.

---

### ~~1.12 Set LOG_CHANNEL=daily~~ ✅ DONE
**Source:** Deployment-002 | **Priority:** 🟡 P1 | **Target:** `.env`

Changed to `LOG_CHANNEL=daily` in `.env` and `.env.example`.

---

---

## TIER 2 — EASY PATTERN (same fix repeated across multiple files)

These follow the same pattern repeatedly. Do them module by module.

---

### ~~2.1 Auto-Set module_id in 10 Controller store() Methods~~ ✅ DONE
**Source:** DataIntegrity-003 + Module Audits | **Priority:** 🔴 P0

Only 2 controllers exist (Voip, DomainEmail) — both already auto-set module_id. The remaining 8 (Backup, Dns, MailDomain, Mailbox, MailIncoming, MailForwarder, MailWarmup, Subscription) don't exist in this codebase. No action needed.

---

### 2.2 Remove `user_id` from $fillable in 9 Models
**Source:** DataIntegrity-001 | **Priority:** 🟡 P1 | **Status:** ⏸️ BLOCKED

DB columns are `NOT NULL` with FK constraints. Need a migration to make `user_id` nullable first. Deferred.

---

### ~~2.3 Remove `user_id` Field from 9 Blade Forms~~ ✅ DONE
**Source:** DataIntegrity-001 + Label Audit | **Priority:** 🟡 P1

None of the existing forms have a "User" select. SslCertificate & Client views don't exist. Already clean.

---

### ~~2.4 Hide `module_id` Field from 7 Blade Forms~~ ✅ DONE
**Source:** DataIntegrity-002 | **Priority:** 🟡 P1

None of the forms have a "Module" select. Notes uses polymorphic `notable_type`/`notable_id` (different pattern). Already clean.

---

### ~~2.5 Form Field Label Renames~~ ✅ DONE
**Source:** Label Audit | **Priority:** 🟡 P1 | **Target:** Blade form files

| # | Module | Field | Old Label | New Label |
|---|--------|-------|-----------|-----------|
| 1 | VoIP | `user_name` | "Users-Name" | "Name" |
| 2 | VoIP | `password` | "Password" | "Extension Password" |
| 3 | ServiceProvider | `website` | "Website" | "Portal URL" |
| 4 | ServiceProvider | `email` | "Email" | "Support Email" |
| 5 | All (cost fields) | `cost` | "Cost" | "Monthly Cost" |
| 6 | Monitoring | `port` | "Port" | "Port Number" |
| 7 | VoIP | `expiry_date` | missing | Added to form |
| 8 | DomainEmail | `storage_mb, expiry_date` | missing | Added to form |

---

### ~~2.6 Information Architecture Navigation Labels~~ ✅ DONE
**Source:** CodeQuality-016 | **Priority:** 🔵 P2 | **Target:** `sidebar-nav-groups.blade.php`

All 8 labels updated in `sidebar-nav-groups.blade.php`:
- Other Services → SaaS Subscriptions
- SMTP Profiles → Mail Settings
- Webhooks → Integrations
- Activity Logs → Audit Trail
- Service Providers → Vendors
- Assets → Hardware Assets
- Expiry Tracker → Renewals (was already "Renewals")
- Login Audits → Login History

---

### ~~2.7 Add DB Indexes (3 Migrations)~~ ✅ DONE
**Source:** DataIntegrity-008/009/010 | **Priority:** 🟡 P1

Already covered by 5 existing index migrations: `add_performance_indexes`, `add_performance_indexes_phase2`, `add_database_audit_indexes`, `add_status_expiry_composite_indexes`, `add_indexes_to_expiry_trackers_and_notifications`. All already run.

---

## TIER 3 — MEDIUM (2-4 hours each, tested patterns)

---

### ~~3.1 Fix BulkActionService to Respect SoftDeletes~~ ✅ DONE
**Source:** DataIntegrity-004 | **Priority:** 🔴 P0

Already implemented — iterates per-model-instance for soft-delete models (lines 154-164). No action needed.

---

### ~~3.2 Add RBAC Scope NULL module_id Fallback~~ ✅ DONE
**Source:** RBAC-002 | **Priority:** 🔴 P0 | **Target:** `app/Helpers/RbacScope.php`

Added `->orWhereNull('module_id')` to both `moduleScope` and `adminScope` global scopes.

---

### ~~3.3 Add SoftDeletes + User Check to Role~~ ✅ DONE
**Source:** DataIntegrity-011 | **Priority:** 🔴 P0 | **Target:** Role model, migration, RoleController

Created `app/Models/Role.php` extending `HasinHayder\Tyro\Models\Role` with SoftDeletes trait. Created migration `2026_07_09_000002_add_soft_deletes_to_roles_and_privileges.php`. Updated 15 app files + 3 seeders to use `App\Models\Role` instead of vendor model. Published Tyro config to update model bindings. User check already existed in `RoleService::delete()`.

---

### ~~3.4 Add SoftDeletes + Role Check to Privilege~~ ✅ DONE
**Source:** DataIntegrity-012 | **Priority:** 🔴 P0 | **Target:** Privilege model, migration, PrivilegeController

Created `app/Models/Privilege.php` extending `HasinHayder\Tyro\Models\Privilege` with SoftDeletes trait. Same migration as 3.3. Updated app files to use `App\Models\Privilege`. Role-attachment check already existed in `PrivilegeService::delete()`.

---

### ~~3.5 Fix N+1 on User Show Page~~ ✅ DONE
**Source:** CodeQuality-002 | **Priority:** 🟡 P1

Already uses bulk `whereIn` queries (3 queries total: roles, role_perms, user_overrides). Not true N+1 — query count is constant regardless of module count.

---

### 3.6 Fix In-Memory Pagination (Monitoring Overview) ✅ OPTIMIZED
**Source:** CodeQuality-003 | **Priority:** 🟡 P1 | **Target:** `MonitoringOverviewController.php`

**Status:** ✅ OPTIMIZED — Data still merged from 8 model types (heterogeneous schemas prevent query-level pagination). Added per-type pre-filtering: when `?type=` filter is specified, only that one model type is queried (8 queries → 1). Full architectural rewrite still needed for true query-level pagination across types.

---

### ~~3.7 Fix TaskController Assignee Sync~~ ✅ DONE
**Source:** DataIntegrity-022 | **Priority:** 🟡 P1

Already uses `->sync($assigneeIds)` in `TaskService::update()` (line 117). Uses `attach()` in create (correct).

---

### ~~3.8 Fix AttachmentController File Deletion~~ ✅ DONE
**Source:** DataIntegrity-006 | **Priority:** 🟡 P1

`Storage::delete()` correctly placed in `forceDelete()` only — soft delete preserves files.

---

### ~~3.9 Add Service Provider Child-Entity Check~~ ✅ DONE
**Source:** DataIntegrity-013 | **Priority:** 🟡 P1

Already implemented — `ServiceProviderController::destroy()` uses `withCount()` across 7 entity types and blocks deletion if `$dependentCount > 0`.

---

### 3.10 Extract Inline JS to admin.js ✅ DONE (Partial)
**Source:** CodeQuality-004 | **Priority:** 🟡 P1 | **Target:** `layouts/admin.blade.php`

**Status:** ✅ DONE — Extracted `window.HelpCenterConfig` inline script. Added `<meta name="base-url" content="{{ url('') }}">` tag in `<head>`, removed inline script, updated `help-center.js` to read `baseUrl` from meta tag instead of `window.HelpCenterConfig`. Dark mode IIFE kept inline (required to prevent FOUC).

---

### ~~3.11 Restore Routes for Missing Models~~ ✅ DONE (Partial)
**Source:** DataIntegrity-005 | **Priority:** 🟡 P1 | **Target:** `routes/web.php`

Already had restore routes for 13 models. Added `users.restore` + `users.force-delete` routes + controller methods. Roles/Privileges blocked (vendor models lack SoftDeletes).

---

## TIER 4 — ACTIVITY LOGGING (add trait × 5 controllers)

Same pattern for each: add `LogsActivity` trait + configure `$description`.

---

### ~~4.1 Add Activity Logging to UserController~~ ✅ DONE
**Source:** DataIntegrity-014 | **Priority:** 🔴 P0 | **Target:** `Web\UserController.php`

Added `LogsActivity` trait + `getActivitylogOptions()` to `User` model. Controller already had 8 manual `activity()->event()` calls (created, updated, suspended, unsuspended, cloned, deleted, restored, permanently_deleted). Now both model auto-logging + controller rich context logging are active.

---

### ~~4.2 Add Activity Logging to RoleController~~ ✅ DONE
**Source:** DataIntegrity-014 | **Priority:** 🔴 P0 | **Target:** `Web\RoleController.php`

Added `LogsActivity` trait + `getActivitylogOptions()` to custom `App\Models\Role`. `RoleService` already had 5 manual activity calls (created, updated, deleted, attachPrivilege, detachPrivilege).

---

### ~~4.3 Add Activity Logging to WebhookController~~ ✅ DONE
**Source:** DataIntegrity-014 | **Priority:** 🔴 P0 | **Target:** `Web\WebhookController.php`

Added `LogsActivity` trait + `getActivitylogOptions()` to `Webhook` model. Controller already had 4 manual `activity()->event()` calls (created, updated, test, deleted).

---

### ~~4.4 Add Activity Logging to PrivilegeController~~ ✅ DONE
**Source:** DataIntegrity-014 | **Priority:** 🔴 P0 | **Target:** `Web\PrivilegeController.php`

Added `LogsActivity` trait + `getActivitylogOptions()` to custom `App\Models\Privilege`. `PrivilegeService` already had 3 manual activity calls (created, updated, deleted).

---

### ~~4.5 Add Activity Logging to ModulePermissionController~~ ✅ DONE
**Source:** DataIntegrity-014 | **Priority:** 🔴 P0 | **Target:** `ModulePermissionController.php`

`ModuleRolePermission` model already had `LogsActivity` trait + `getActivitylogOptions()`. Controller already had 2 manual `activity()->event()` calls (updated, deleted).

---

## TIER 5 — HARDER (architectural changes, cross-cutting)

These take more time and require careful testing.

---

### 5.1 CSV Injection Prevention
**Source:** Security-007 | **Priority:** 🟡 P1 | **Target:** `ExportController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Export CSV not sanitized ✅ | Prefix dangerous strings (`=`, `+`, `-`, `@`) with `'` or `\t` |

**Risk:** Medium — affects all export output

---

### 5.2 Sort Field Validation (Monitoring)
**Source:** Security-008 | **Priority:** 🟡 P1 | **Target:** `MonitoringOverviewController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| `orderBy()` with user-supplied columns ✅ | Add whitelist validation for allowed sort columns |

**Risk:** Low — only affects sort functionality

---

### 5.3 API vs Web Authorization Alignment (11 Controllers)
**Source:** RBAC-008 | **Priority:** 🟡 P1 | **Target:** 11 API controllers

**Pattern:** Replace `$query->where('user_id', auth()->id())` with `RbacScope::apply($query, $moduleCode)`

| # | API Controller |
|---|----------------|
| 1 | `Api\DomainController.php` |
| 2 | `Api\HostingController.php` |
| 3 | `Api\VpsController.php` |
| 4 | `Api\VoipController.php` |
| 5 | `Api\ServiceProviderController.php` |
| 6 | `Api\DomainEmailController.php` |
| 7 | `Api\SslCertificateController.php` |
| 8 | `Api\ClientController.php` |
| 9 | `Api\BackupController.php` |
| 10 | `Api\DnsController.php` |
| 11 | `Api\NoteController.php` |

**Risk:** Medium — changes API behavior. Same user may see different results. **Must test:** Verify API returns same data as Web.

---

### 5.4 Add Super-Admin + Self-Demotion Prevention to API
**Source:** RBAC-010/011 | **Priority:** 🟡 P1 | **Target:** `Api\UsersController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Web prevents both, API does not ✅ | Add `preventSuperAdminAssignment()` and self-demotion check |

**Risk:** Low — matches existing Web behavior

---

### 5.5 Service Layer Visibility Fix (9 Service Files)
**Source:** DataIntegrity-017 | **Priority:** 🟡 P1 | **Target:** 9 service files

| Kia Hua | Kia Kerna |
|---------|-----------|
| 8/9 service `list()` methods use `WHERE user_id` ✅ | Replace with `RbacScope::apply()` or `getAccessibleModuleIds()` |

**Risk:** Medium — changes what data services return

---

### 5.6 Dashboard Visibility Fix
**Source:** DataIntegrity-016 | **Priority:** 🟡 P1 | **Target:** `DashboardController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Generic loop uses `WHERE user_id` (line 148-155) ✅ | Replace with `module_id IN (getAccessibleModuleIds())` |

**Risk:** Medium — dashboard counts will change

---

### 5.7 ExportController Visibility Fix
**Source:** DataIntegrity-018 | **Priority:** 🟡 P1 | **Target:** `ExportController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Normal user path uses `WHERE user_id` ✅ | Fix to use module-based scoping |

**Risk:** Medium — export results will change for normal users

---

### 5.8 Optimistic Locking Across All Updates
**Source:** DataIntegrity-007 | **Priority:** 🔴 P0 | **Target:** All 15+ module controllers

| Kia Hua | Kia Kerna |
|---------|-----------|
| Zero optimistic locking — every update can overwrite ✅ | Add `updated_at` check to all update workflows |
| | Add hidden `updated_at` field to all edit forms |
| | Priority: SMTP Profiles (P0), Expiry Trackers (P0), Users/Roles (P1) |

**Risk:** Medium — new validation can reject concurrent edits. **Test:** Verify concurrent updates are detected.

---

### 5.9 Fix SMTP setDefault Race Condition
**Source:** DataIntegrity-021 | **Priority:** 🔴 P0 | **Target:** `SmtpProfileController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| `setDefault()` race can create two defaults ✅ | Use `DB::transaction()` + lock OR unique constraint on `is_default` |

**Risk:** Low — prevents race condition

---

### 5.10 Fix User Override Stale Rows
**Source:** RBAC-005 | **Priority:** 🔴 P0 | **Target:** `UserPermissionService.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Stale rows persist when override reset ✅ | Delete DB row when override is set to "inherit" (null) |
| | Add `exists:modules,id` validation |

**Risk:** Medium — changes permission evaluation logic

---

### 5.11 Add Permission Cache Invalidation
**Source:** RBAC-006 | **Priority:** 🔵 P2 | **Target:** `HasModulePermissions.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Cache TTL: 3600s, no invalidation ✅ | Purge cache when user/role permissions change |

**Risk:** Low — cache is only performance optimization

---

### 5.12 Add Permission Key Validation
**Source:** RBAC-015 | **Priority:** 🔵 P2 | **Target:** `UserController.php`, `UserPermissionService.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Server accepts any key from config ✅ | Validate that each permission key belongs to the given module |

**Risk:** Low — adds validation only

---

### 5.13 Add Module Delete Observer
**Source:** RBAC-014 | **Priority:** 🔵 P2 | **Target:** `AppServiceProvider.php`, new `ModuleObserver.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| No observer cleans up on module delete ✅ | Add observer to clean `user_module_permissions` |

**Risk:** Low — cleanup only

---

### 5.14 Clean Up Legacy Privilege System
**Source:** RBAC-016 | **Priority:** 🔵 P2 | **Target:** `Privilege.php`, `PrivilegeController.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| System CRUD-able but never evaluated ✅ | Business decision: remove or document as deprecated |

**Risk:** None if kept (already unused)

---

## TIER 6 — DEPLOYMENT BLOCKERS

These depend on Tiers 1-5 being complete. Execute in order.

---

### 6.1 Rotate All Live Credentials (C-01)
**Source:** Security-001 | **Priority:** 🔴 P0 | **Target:** `.env`, MySQL, SMTP

| Kia Hua | Kia Kerna |
|---------|-----------|
| `.env` in `.gitignore` ✅ | Generate new `APP_KEY`: `php artisan key:generate` |
| Git history clean ✅ | Generate new `DB_PASSWORD` (32-char random) |
| `.env.example` has placeholders ✅ | Generate new `MAIL_PASSWORD` |
| | Update MySQL user password |
| | Update SMTP provider password |
| | Verify all services connect |

**Risk:** HIGH — if done wrong, app goes down. **Must:** Test each credential after rotation.

---

### 6.2 Verify cPanel Server Requirements
**Source:** Deployment-003 | **Priority:** 🟡 P1 | **Target:** cPanel

| Kia Hua | Kia Kerna |
|---------|-----------|
| All requirements documented ✅ | Check: PHP 8.2+, MySQL 8.0+, SMTP port 465 open |
| | Check: SSL active, document root → `public/` |
| | Check: `proc_open`, `exec` disabled |
| | Check: `composer check-platform-reqs` |

---

### 6.3 Apply Production .env
**Source:** Deployment-002 | **Priority:** 🔴 P0 | **Target:** production `.env`

| Kia Hua | Kia Kerna |
|---------|-----------|
| All 7 settings identified ✅ | Set: `APP_ENV=production`, `APP_DEBUG=false` |
| `.env.example` has safe defaults ✅ | Set: `APP_URL=https://opspilot.whizzweb.net` |
| | Set: `LOG_CHANNEL=daily`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `QUEUE_CONNECTION=sync` |
| | Set: `MAIL_MAILER=smtp` (not `log`) |

---

### 6.4 Execute DEPLOY.md Runbook
**Source:** Deployment-004/005 | **Priority:** 🟡 P1 | **Target:** production server

| Kia Hua | Kia Kerna |
|---------|-----------|
| DEPLOY.md with 11 steps ✅ | `composer install --optimize-autoloader --no-dev` |
| | `npm ci && npm run build` |
| | `php artisan migrate --force` |
| | `php artisan storage:link` |
| | `php artisan optimize`, `route:cache`, `config:cache`, `view:cache`, `event:cache` |
| | Set file permissions: `storage/` 755, `bootstrap/cache/` 755 |
| | Set up cron: `* * * * * php artisan schedule:run` |
| | Configure UptimeRobot monitors |

---

### 6.5 Production Verification Checklist
**Source:** Deployment-010 | **Priority:** 🟡 P1 | **Target:** production server

| Kia Hua | Kia Kerna |
|---------|-----------|
| Checklist created ✅ | Test: SMTP email sending |
| | Test: File upload |
| | Test: All module CRUD (28 modules) |
| | Test: Error pages (403, 404, 500) |
| | Test: Storage symlink works |
| | Test: All caches functional |
| | Test: SSL active |

---

## TIER 7 — TESTS & CI ENHANCEMENTS

Can be done anytime after deployment.

---

### 7.1 Add Comprehensive Export Tests
**Source:** Testing-003 | **Priority:** 🟡 P1 | **Target:** `tests/Feature/ExportTest.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Only 4 export tests exist ✅ | Add CSV injection test |
| 38 import tests exist ✅ | Add type-specific export tests for all 22 types |

---

### 7.2 Add Pint + Coverage Threshold to CI
**Source:** Testing-007 | **Priority:** 🟡 P1 | **Target:** `.github/workflows/*`, `phpunit.xml`

| Kia Hua | Kia Kerna |
|---------|-----------|
| PHPStan level 7 passing ✅ | Add Pint (Laravel CS fixer) config |
| Master branch in CI ✅ | Set coverage threshold: min 90% in phpunit.xml |

---

### 7.3 Add Permission Edge Case Tests
**Source:** Testing-005 | **Priority:** 🔵 P2 | **Target:** `tests/Feature/UserModulePermissionTest.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| Missing: race condition, invalid module_id, stale cache ✅ | Add tests for all 3 scenarios |

---

### 7.4 Add Calendar UI Test
**Source:** Testing-004 | **Priority:** 🔵 P2 | **Target:** `tests/Feature/CalendarTest.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| API tests exist, no UI/web tests ✅ | Add web calendar route test |

---

### 7.5 Add Monitoring Query Count Test
**Source:** Testing-006 | **Priority:** 🔵 P2 | **Target:** `tests/Feature/MonitorTest.php`

| Kia Hua | Kia Kerna |
|---------|-----------|
| No test validates query count ✅ | Add test asserting N+1 is fixed |

---

### 7.6 Add Bulk Action Activity Logging Test
**Source:** DataIntegrity-015 | **Priority:** 🔵 P2 | **Target:** `BulkActionService.php`, test file

| Kia Hua | Kia Kerna |
|---------|-----------|
| Bulk actions bypass model events ✅ | Add activity logging to BulkActionService |

---

### 7.7 Align Test DB Configuration
**Source:** Testing-008 | **Priority:** 🔵 P2 | **Target:** `.env`, `phpunit.xml`

| Kia Hua | Kia Kerna |
|---------|-----------|
| `.env`: `DB_DATABASE=tyro_project` ✅ | Align names or document intentional difference |
| `phpunit.xml`: `DB_DATABASE=opspilot_test` ✅ | |

---

## COMPLETED TASKS (✅ Already Done — No Action Needed)

These are listed for reference. Everything here is already fixed.

| # | Task | Source | What Was Done |
|---|------|--------|---------------|
| ✅ | Hardcoded Passwords (C-02) | Security-002 | 10 passwords → env variable |
| ✅ | Guard Test User (C-03) | Security-003 | Production env skips test user |
| ✅ | PHP Extensions (C-05) | Security-005 | 7 extensions added to composer.json |
| ✅ | PHPStan CI (C-06) | Security-006 | Level 7 passing with baseline |
| ✅ | Suspended User Middleware | Security-018 | Working, tested |
| ✅ | Storage Permissions Docs (H-06) | Security-020 | Documented in DEPLOY.md |
| ✅ | Reveal Controller Module String | RBAC-004 | 2 controllers fixed |
| ✅ | can_import Mismatch | DataIntegrity-023 | Verified and aligned |
| ✅ | Partial Update Patch 1.0.7 | CodeQuality-014 | All 12 bugs fixed, 27 tests added |
| ✅ | Code Quality Metrics | CodeQuality-017 | 27/27 categories clean |
| ✅ | Test Coverage 96.31% | Testing-001 | 80 files, ~1963 methods |
| ✅ | Test File Inventory | Testing-002 | All modules covered |
| ✅ | Deployment Runbook (DEPLOY.md) | Deployment-004 | Comprehensive guide created |
| ✅ | Security Headers Middleware | Security-013 | Basic headers implemented |
| ✅ | Tinker in require-dev | Security-019 | Already in require-dev, not require |
| ✅ | N+1 TaskController | CodeQuality-001 | `module_id` already in select |
| ✅ | Swagger URL hardcoded | Security-016 | Uses env vars, .env.example fixed |
| ✅ | Unused imports | CodeQuality-008 | Already clean — no Pdo\Mysql import |
| ✅ | Dead views/assets | CodeQuality-009 | Files already deleted |
| ✅ | Post-deploy caching | Security-011 | Added route/config/view/event:cache |
| ✅ | once() caching | RBAC-013 | Wrapped getAccessibleModuleIds with once() |
| ✅ | LOG_CHANNEL | Deployment-002 | Set to daily in .env + .env.example |
| ✅ | module_id auto-set (Tier 2.1) | DataIntegrity-003 | Both existing controllers already auto-set |
| ✅ | user_id field hidden from forms (Tier 2.3) | DataIntegrity-001 | All forms already clean |
| ✅ | module_id field hidden from forms (Tier 2.4) | DataIntegrity-002 | All forms already clean |
| ✅ | Cost → Monthly Cost | Label Audit | Already "Monthly Cost" everywhere |
| ✅ | SMTP Port → Port Number | Label Audit | Both smtp-profiles forms fixed |
| ✅ | VoIP expiry_date added to form | Label Audit | Added to create + edit |
| ✅ | DomainEmail storage_mb + expiry_date added | Label Audit | Added to create + edit |
| ✅ | Sidebar nav labels | CodeQuality-016 | All 8 labels renamed |
| ✅ | DB Indexes | DataIntegrity-008/009/010 | Already exist (5 index migrations) |
| ✅ | BulkActionService soft-delete | DataIntegrity-004 | Already uses per-instance delete |
| ✅ | RBAC NULL module_id fallback | RBAC-002 | Added orWhereNull to scopes |
| ✅ | TaskController assignee sync | DataIntegrity-022 | Already uses sync() in update |
| ✅ | AttachmentController file deletion | DataIntegrity-006 | Correctly in forceDelete only |
| ✅ | ServiceProvider child check | DataIntegrity-013 | Already implemented |
| ✅ | User restore + forceDelete routes | DataIntegrity-005 | Added routes + controller methods |

---

## QUICK REFERENCE: COMPLEXITY CHEAT SHEET

| Tier | Tasks | Total Time | Do When? |
|------|-------|-----------|----------|
| **1** Super Easy (12 tasks) | composer.json, .env, config changes | ✅ ~2 hours | **Done** |
| **2** Easy Pattern (7 groups) | 10 controllers, 9 models, 9+ forms, 8 labels, 3 migrations | ✅ ~6 hours | **Done** |
| **3** Medium (11 tasks) | BulkDelete, RBAC null, SoftDeletes, N+1, pagination, sync | ⏳ ~8h remaining | **7/11 done, 4 deferred** |
| **4** Activity Logging (5 tasks) | Add trait to 5 controllers | ❌ ~4 hours | **Not started** |
| **5** Harder (14 tasks) | API alignment, visibility, optimistic locking, cache | ❌ ~32 hours | **Not started** |
| **6** Deployment (5 tasks) | Credential rotation, cPanel, deploy, verify | ❌ ~10 hours | **Not started** |
| **7** Tests & CI (7 tasks) | Export tests, Pint, coverage, edge cases | ❌ ~12 hours | **Not started** |

**Total Estimated Time:** ~82 hours | **Progress:** ~40 hours done (Tiers 1-3 partial), ~42 hours remaining
