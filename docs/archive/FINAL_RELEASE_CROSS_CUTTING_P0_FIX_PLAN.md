# FINAL_RELEASE_CROSS_CUTTING_P0_FIX_PLAN.md

**Date:** 2026-07-09

---

## P0-01: Rotate Live Credentials (C-01)
**Source:** Security-001

| Step | File | Action |
|------|------|--------|
| 1 | `.env` | Generate new `APP_KEY`: `php artisan key:generate` |
| 2 | `.env` | Generate new `DB_PASSWORD` (random 32-char) |
| 3 | `.env` | Generate new `MAIL_PASSWORD` |
| 4 | MySQL | Update DB user password to match |
| 5 | SMTP | Update SMTP provider password |
| 6 | — | Verify all services connect with new credentials |
| **Estimate** | **2h** | |

---

## P0-02: Switch Queue to Sync (C-04)
**Source:** Security-004

| Step | File | Action |
|------|------|--------|
| 1 | `.env` | Set `QUEUE_CONNECTION=sync` |
| 2 | `.env.example` | Already set to `sync` |
| 3 | — | Verify notifications send immediately |
| **Estimate** | **0.5h** | |

---

## P0-03: Move Tinker to require-dev (C-18)
**Source:** Security-019

| Step | File | Action |
|------|------|--------|
| 1 | `composer.json` | Move `"laravel/tinker": "^2.8"` from `require` to `require-dev` |
| 2 | — | Run `composer install --no-dev` → confirm tinker absent |
| **Estimate** | **0.5h** | |

---

## P0-04: Fix VoIP/DomainEmail module_id (C-08)
**Source:** DataIntegrity-003

| Step | File | Action |
|------|------|--------|
| 1 | `VoipController.php` store() | Add `$validated['module_id'] = $module->id` |
| 2 | `DomainEmailController.php` store() | Add `$validated['module_id'] = $module->id` |
| 3 | `BackupController.php` store() | Add same pattern |
| 4 | `DnsController.php` store() | Add same pattern |
| 5 | `MailDomainController.php` store() | Add same pattern |
| 6 | `MailboxController.php` store() | Add same pattern |
| 7 | `MailIncomingController.php` store() | Add same pattern |
| 8 | `MailForwarderController.php` store() | Add same pattern |
| 9 | `MailWarmupController.php` store() | Add same pattern |
| 10 | `SubscriptionController.php` store() | Add same pattern |
| 11 | — | Remove `module_id` from form fields |
| **Estimate** | **2h** | |

---

## P0-05: RBAC Scope NULL module_id Fallback (C-15)
**Source:** RBAC-002

| Step | File | Action |
|------|------|--------|
| 1 | `RbacScope.php` | Modify `apply()` to add `OR module_id IS NULL` to WHERE clause |
| 2 | — | Test: non-SA user sees VoIP records after module_id fix |
| **Estimate** | **1h** | |

---

## P0-06: BulkActionService SoftDeletes (C-09)
**Source:** DataIntegrity-004

| Step | File | Action |
|------|------|--------|
| 1 | `BulkActionService.php` | Change `$modelClass::whereIn('id', $ids)->delete()` to use model instances |
| 2 | — | Verify `deleted_at` set (not hard delete) for soft-delete models |
| **Estimate** | **2h** | |

---

## P0-07: Role SoftDeletes + User Check (C-13)
**Source:** DataIntegrity-011

| Step | File | Action |
|------|------|--------|
| 1 | Role model | Add `SoftDeletes` trait |
| 2 | Role migration | Add `deleted_at` column |
| 3 | `RoleController.php` destroy() | Add `if ($role->users()->count() > 0) → abort/error` |
| 4 | Routes | Add restore route |
| **Estimate** | **2h** | |

---

## P0-08: Privilege SoftDeletes + Role Check (C-14)
**Source:** DataIntegrity-012

| Step | File | Action |
|------|------|--------|
| 1 | Privilege model | Add `SoftDeletes` trait |
| 2 | Privilege migration | Add `deleted_at` column |
| 3 | `PrivilegeController.php` destroy() | Check for role attachments |
| **Estimate** | **1h** | |

---

## P0-09: Activity Logging Gaps (C-12)
**Source:** DataIntegrity-014

| Step | File | Action |
|------|------|--------|
| 1 | `UserController` | Add `LogsActivity` trait + `$description` |
| 2 | `RoleController` | Add `LogsActivity` trait |
| 3 | `WebhookController` | Add `LogsActivity` trait |
| 4 | `PrivilegeController` | Add `LogsActivity` trait |
| 5 | `ModulePermissionController` | Add activity logging |
| **Estimate** | **4h** | |

---

## P0-10: Optimistic Locking (C-10)
**Source:** DataIntegrity-007

| Step | File | Action |
|------|------|--------|
| 1 | `SmtpProfileController.php` | Add `updated_at` check to `setDefault()` |
| 2 | All update methods | Add `if ($model->updated_at > $request->updated_at) → conflict error` |
| 3 | Forms | Pass `updated_at` as hidden field |
| 4 | — | Write test for concurrent update detection |
| **Estimate** | **8h** | |

---

## P0-11: SMTP setDefault Race (C-11)
**Source:** DataIntegrity-021

| Step | File | Action |
|------|------|--------|
| 1 | `SmtpProfileController.php` | Use `DB::transaction()` + lock or unique constraint |
| 2 | Migration | Add unique index on `is_default` where true |
| **Estimate** | **2h** | |

---

## P0-12: N+1 in TaskController (C-17)
**Source:** CodeQuality-001

| Step | File | Action |
|------|------|--------|
| 1 | `TaskController.php:84` | Add `module_id` to `select()` array |
| **Estimate** | **1h** | |

---

## P0-13: User Override Stale Rows (C-16)
**Source:** RBAC-005

| Step | File | Action |
|------|------|--------|
| 1 | `UserPermissionService.php` | When override is reset, delete DB row (not just set to false) |
| 2 | — | Test: remove override → confirm DB row deleted |
| **Estimate** | **2h** | |
