# Patch 1.0.8 — Final Production Hardening Fixes

**Date:** 2026-06-27
**Tests:** 1884 pass, 4753 assertions (0 failures)
**Change:** +1 test, +4 assertions over 1.0.7 (1883/4749)

---

## Fixes Applied

### P0: Activity Logging Gaps (6 HIGH-severity items from Phase 10.1 audit)

| Module | File | Changes |
|---|---|---|
| Users (update/delete) | `UserController.php` | Added activity logging to `update()`, `destroy()`, and `updatePermissions()` with old/new values |
| Roles (CRUD) | `RoleController.php` | Added activity logging to `store()`, `update()` (with diff), `destroy()`, `attachPrivilege()`, `detachPrivilege()` |
| Privileges (CRUD) | `PrivilegeController.php` | Added activity logging to `store()`, `update()` (with diff), `destroy()` |
| Webhooks (CRUD) | `WebhookController.php` | Added activity logging to `store()`, `update()` (with diff), `destroy()`, `test()` |
| Module Permissions | `ModulePermissionController.php` | Added activity logging to `update()` and `destroy()` |
| Password Reset | `AuthController.php` | Added activity logging inside `resetPassword()` success callback |

### P0: Delete/Soft-Delete Safety (Phase 10.3 audit)

| Issue | File | Fix |
|---|---|---|
| BulkActionService hard-deletes soft-deletable models | `BulkActionService.php` | Added `modelUsesSoftDeletes()` check — models with SoftDeletes trait now iterate via `$model->delete()` (soft delete) instead of mass SQL DELETE. Models without SoftDeletes continue with fast mass delete. |
| Role delete with assigned users | `RoleController.php` | `destroy()` now checks `$role->users()->count() > 0` before deleting. Blocked with error message listing user count. |
| Privilege delete with assigned roles | `PrivilegeController.php` | `destroy()` now uses `withCount('roles')` and blocks deletion when `roles_count > 0`. |
| ServiceProvider delete with dependents | `ServiceProviderController.php` | `destroy()` now uses `withCount()` across all 7 relation types (hostings, domains, vps, voip, domainEmails, otherServices, expiryTrackers) and blocks if any exist. |
| Asset delete while assigned | `AssetController.php` | `destroy()` now checks `$asset->status === 'assigned'` — blocked with error message. |
| Attachment physical file deleted on soft delete | `AttachmentController.php` | Removed `Storage::disk('public')->delete(...)` from `destroy()`. Added `forceDelete()` method that deletes the file only on permanent delete. AttachmentService `delete()` updated to match; new `forceDelete()` method added. |
| Task missing restore route | `TaskController.php` | Added `restore()` method with super-admin guard. Route added to `routes/web.php`. |
| Attachment missing force-delete route | `routes/web.php` | Added `attachments.force-delete` route. |

### P0: Webhook Delete Safety

Webhook model has no SoftDeletes or LogsActivity trait — deleting it is permanent with no audit trail. SoftDeletes requires a migration (deferred). Activity logging added as mitigation.

### P1: Missing Activity Logging (Medium severity)

| Module | File | Changes |
|---|---|---|
| Export CSV | `ExportController.php` | Added activity logging on all CSV exports |
| Import CSV | `ImportController.php` | Added activity logging on all CSV imports |
| API Token CRUD | `TokenController.php` | Added activity logging on `store()` and `destroy()` |
| Login Audit delete | `LoginAuditController.php` | Added activity logging on `destroy()` |

### P1: Attachment File Preservation on Bulk Delete

`BulkActionService.php` — Removed the `Attachment::whereIn('id', $ids)->each(...)` block that was deleting physical files during bulk attachment delete. Files are now preserved for possible restore, consistent with single-attachment behavior.

---

## Files Modified

| File | P0/P1 | Lines Changed |
|---|---|---|
| `app/Http/Controllers/Web/UserController.php` | P0 | +20 |
| `app/Http/Controllers/Web/RoleController.php` | P0 | +40 |
| `app/Http/Controllers/Web/PrivilegeController.php` | P0 | +35 |
| `app/Http/Controllers/Web/WebhookController.php` | P0 | +40 |
| `app/Http/Controllers/Web/ServiceProviderController.php` | P0 | +15 |
| `app/Http/Controllers/Web/AssetController.php` | P0 | +10 |
| `app/Http/Controllers/Web/AttachmentController.php` | P0 | +15 |
| `app/Http/Controllers/Web/TaskController.php` | P0 | +10 |
| `app/Http/Controllers/Web/ModulePermissionController.php` | P1 | +25 |
| `app/Http/Controllers/Web/ExportController.php` | P1 | +10 |
| `app/Http/Controllers/Web/ImportController.php` | P1 | +10 |
| `app/Http/Controllers/Web/TokenController.php` | P1 | +15 |
| `app/Http/Controllers/Web/LoginAuditController.php` | P1 | +10 |
| `app/Http/Controllers/Web/AuthController.php` | P1 | +5 |
| `app/Services/BulkActionService.php` | P0 | +15 |
| `app/Services/AttachmentService.php` | P0 | +10 |
| `routes/web.php` | P0 | +2 |
| `tests/Feature/WebNewResourcesTest.php` | P0 | +1 |
| `tests/Feature/AssetManagementTest.php` | P0 | +14 |
| `tests/Feature/AttachmentTest.php` | P0 | +1 |
| `tests/Unit/AttachmentServiceTest.php` | P0 | +1 |
| `tests/Feature/ApiNewResourcesTest.php` | P0 | +1 |

---

## Deferred Items (Phase 10.2 — Concurrency)

All concurrency issues (optimistic locking via `updated_at` hidden-field pattern) deferred to v1.1 per constraint:
- No database migrations without explicit approval
- Stale-read window is low-risk for most modules based on usage patterns

## Remaining Risk

- **Webhook model** still has no SoftDeletes — permanent delete with no undo. Requires migration to add `deleted_at` column.
- **Role/Privilege models** from vendor package `hasinhayder/tyro` — no SoftDeletes, no LogsActivity. Can't modify vendor code.
- **BulkActionService** restore/force-delete operations on models without SoftDeletes could be problematic but same as before.
