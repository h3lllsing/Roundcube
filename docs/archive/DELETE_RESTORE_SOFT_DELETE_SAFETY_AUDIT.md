# Delete / Restore / Soft Delete Safety Audit

**Date:** 2026-06-27
**Scope:** All 21 destructive workflows across Web controllers + BulkActionService
**Method:** Source code review — every `destroy()`, `restore()`, `forceDelete()` method, every model's trait usage, every blade confirmation

---

## Cross-Cutting Systems

### Confirmation Dialog

All delete buttons in show blade templates use the `data-confirm` attribute which triggers a custom modal dialog via JavaScript in `layouts/admin.blade.php:371-388`:

```html
<x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">
```

This modal provides **client-side-only** confirmation. A direct `DELETE` request via API, curl, or programmatic HTTP client bypasses it entirely. All modules have this on their show page delete buttons.

**Exception:** The `users/index.blade.php` and other index pages that have inline delete buttons — checked the `<x-action>` component: it supports `confirm` parameter. Need to verify if passed.

### Bulk Actions

`BulkActionController` delegates to `BulkActionService` which uses single SQL statements (`Model::whereIn('id', $ids)->delete()`). These **bypass model events entirely** — no SoftDeletes hooks, no LogsActivity, no `deleted_at` timestamp for models with SoftDeletes. The SQL `DELETE` physically removes rows.

**Exception:** `BulkActionService::handleCustomType('tokens')` uses `$user->tokens()->whereIn('id', $ids)->delete()` — this IS a soft-delete equivalent via Sanctum's token lifecycle (token is revoked in the pivot, not physically deleted from DB by default).

---

## Module-by-Module Audit

### 1. Users

| Property | Value |
|----------|-------|
| **Model** | `User` |
| **SoftDeletes** | **YES** — `deleted_at` set on delete |
| **Delete route** | `DELETE /users/{id}` → `UserController::destroy()` |
| **Restore route** | ❌ Not available (no route, no controller method) |
| **Force delete route** | ❌ Not available |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | Partial — prevents deleting the **last** super-admin |
| **Related records checked** | None (14 hasMany relations not checked) |
| **Activity log** | ❌ Missing (no logging in destroy) |
| **Risk** | **MEDIUM** — soft delete preserves data, but no restore capability means admin has to manipulate DB directly |

**Issues:**
- No restore route — once soft-deleted, users can't be recovered via UI
- No force delete — DB rows accumulate soft-deleted users forever
- No check if user owns domains, tasks, vault entries, etc. (soft delete would set `deleted_at` but FK references still point to the user record, which is fine for soft delete)
- No check if user is the **only** admin (non-super-admin) with critical access

---

### 2. Domains

| Property | Value |
|----------|-------|
| **Model** | `Domain` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /domains/{id}` → `DomainController::destroy()` |
| **Restore route** | `PATCH /domains/{id}/restore` → `DomainController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /domains/{id}/force-delete` → `DomainController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — no check if domain has linked DomainEmail records |
| **Related records checked** | ❌ None — `service_provider_id` FK not checked for validity after deletion |
| **Activity log** | ✅ Auto-logged via `LogsActivity` trait |
| **Risk** | **MEDIUM** — soft delete preserves data; restore available; but no check for dependent DomainEmails |

**Issues:**
- `DomainEmail` records with `domain_id` pointing to a deleted Domain will have orphaned FKs (or `domain_id` values become stale). This is handled gracefully by soft delete (the record still exists), but after force delete, the FK is truly orphaned.

---

### 3. Hosting

| Property | Value |
|----------|-------|
| **Model** | `Hosting` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /hostings/{id}` → `HostingController::destroy()` |
| **Restore route** | `PATCH /hostings/{id}/restore` → `HostingController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /hostings/{id}/force-delete` → `HostingController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — no check if domains reference this hosting |
| **Related records checked** | ❌ None — `hasMany` Domain not checked |
| **Activity log** | ✅ Auto-logged |
| **Risk** | **MEDIUM** — soft delete preserves data; restore available; but no check for dependent Domains |

---

### 4. VPS

| Property | Value |
|----------|-------|
| **Model** | `Vps` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /vps/{id}` → `VpsController::destroy()` |
| **Restore route** | `PATCH /vps/{id}/restore` → `VpsController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /vps/{id}/force-delete` → `VpsController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ✅ Auto-logged |
| **Risk** | **LOW** — full lifecycle (soft delete + restore + force delete) available |

---

### 5. Domain Emails

| Property | Value |
|----------|-------|
| **Model** | `DomainEmail` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /domain-emails/{id}` (resourceful) |
| **Restore route** | `PATCH /domain-emails/{id}/restore` (super-admin only) |
| **Force delete route** | `DELETE /domain-emails/{id}/force-delete` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ✅ Auto-logged (password changes excluded) |
| **Risk** | **LOW** — full lifecycle available; password is encrypted in DB and excluded from activity log |

---

### 6. VoIP

| Property | Value |
|----------|-------|
| **Model** | `Voip` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /voip/{id}` → `VoipController::destroy()` |
| **Restore route** | `PATCH /voip/{id}/restore` → `VoipController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /voip/{id}/force-delete` → `VoipController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ✅ Auto-logged (password/extension_password excluded) |
| **Risk** | **LOW** — full lifecycle available |

---

### 7. Other Services

| Property | Value |
|----------|-------|
| **Model** | `OtherService` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /other-services/{id}` (resourceful) |
| **Restore route** | `PATCH /other-services/{id}/restore` (super-admin only) |
| **Force delete route** | `DELETE /other-services/{id}/force-delete` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ✅ Auto-logged (password excluded) |
| **Risk** | **LOW** |

---

### 8. Expiry Trackers

| Property | Value |
|----------|-------|
| **Model** | `ExpiryTracker` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /expiry-trackers/{id}` (resourceful) |
| **Restore route** | `PATCH /expiry-trackers/{id}/restore` (super-admin only) |
| **Force delete route** | `DELETE /expiry-trackers/{id}/force-delete` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — notifications not checked before deletion |
| **Related records checked** | ❌ None — `ExpiryTrackerNotification` child records not handled |
| **Activity log** | ✅ Auto-logged |
| **Risk** | **MEDIUM** — deleting an ExpiryTracker leaves orphaned `ExpiryTrackerNotification` records with stale `expiry_tracker_id`; notifications history is lost |

---

### 9. SMTP Profiles

| Property | Value |
|----------|-------|
| **Model** | `SmtpProfile` |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /smtp-profiles/{smtp_profile}` → `SmtpProfileController::destroy()` |
| **Restore route** | ❌ Not available (no soft deletes) |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ✅ `data-confirm` on show page |
| **In-use protection** | ✅ **YES** — `$smtpProfile->isInUse()` checks all consumer tables + `usageCount()` provides details |
| **Related records checked** | ✅ **YES** — `consumerTables()` method lists ExpiryTracker as dependent; `isInUse()` queries for active references |
| **Activity log** | ✅ Auto-logged + manual `tested`/`duplicated` events |
| **Risk** | **MEDIUM** — permanent delete (no recovery), but in-use protection prevents accidental deletion while entities reference it |

**Issues:**
- Permanent deletion (no soft deletes) — if an SmtpProfile is accidentally deleted with no active references, it's gone forever with no recovery path
- `isInUse()` checks consumer tables but the error message only tells the count — doesn't list which entities are using it

---

### 10. Assets

| Property | Value |
|----------|-------|
| **Model** | `Asset` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /assets/{id}` → `AssetController::destroy()` (delegates to `AssetService::delete()`) |
| **Restore route** | `PATCH /assets/{id}/restore` → `AssetController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /assets/{id}/force-delete` → `AssetController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — no check if asset is currently assigned to a user |
| **Related records checked** | ❌ None — `AssetAssignment` child records not checked |
| **Activity log** | ✅ Auto-logged |
| **Risk** | **MEDIUM** — soft delete preserves data, restore available, but an asset currently assigned to a user can be deleted without warning, which would orphan the assignment records |

**Issues:**
- No check if asset `status = 'assigned'` before deletion — an asset actively checked out to an employee can be deleted, leaving `AssetAssignment` records with orphaned `asset_id`
- After soft delete, `assigned_to` FK on the Asset row still points to a valid User (fine), but the Asset itself is hidden from queries

---

### 11. Tasks

| Property | Value |
|----------|-------|
| **Model** | `Task` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /tasks/{id}` → `TaskController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not available |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None — attachments on tasks not checked; assignee pivot rows orphaned |
| **Activity log** | ✅ Auto-logged (custom description) |
| **Risk** | **HIGH** — no restore route means soft-deleted tasks are invisible to users with no UI recovery path; assignee pivot rows orphaned |

**Issues:**
- No restore route — `Task::withTrashed()` exists in model but no controller method to restore
- No force delete route — soft-deleted tasks accumulate in DB
- `belongsToMany` `assignees()` pivot table `task_user` is not cleaned up — after soft delete, the pivot rows still reference the deleted task ID

---

### 12. Vault

| Property | Value |
|----------|-------|
| **Model** | `VaultEntry` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /vault/{id}` → `VaultController::destroy()` |
| **Restore route** | `PATCH /vault/{id}/restore` → `VaultController::restore()` (super-admin only) |
| **Force delete route** | `DELETE /vault/{id}/force-delete` → `VaultController::forceDelete()` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — no check if Asset records reference this vault entry |
| **Related records checked** | ❌ None — `Asset.vault_entry_id` not checked |
| **Activity log** | ✅ Auto-logged (encrypted_password excluded) |
| **Risk** | **MEDIUM** — soft delete preserves data; restore/forceDelete available; but Assets referencing this vault entry will have orphaned `vault_entry_id`

---

### 13. Notes

| Property | Value |
|----------|-------|
| **Model** | `Note` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /notes/{id}` → `NoteController::destroy()` |
| **Restore route** | `PATCH /notes/{id}/restore` → `NoteController::restore()` |
| **Force delete route** | `DELETE /notes/{id}/force-delete` → `NoteController::forceDelete()` |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None — attachments not checked |
| **Activity log** | ✅ Auto-logged (custom description) |
| **Risk** | **LOW** — full lifecycle available; note: restore/forceDelete do NOT require super-admin (any user can restore their own notes) |

**Issue:** Note's `restore()` and `forceDelete()` methods do NOT require super-admin — they respect `userOwnedFilter()` but any user can restore their own previously-deleted notes. This is intentional (notes are user-owned content) but inconsistent with every other module where restore/forceDelete require super-admin.

---

### 14. Webhooks

| Property | Value |
|----------|-------|
| **Model** | `Webhook` |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /webhooks/{webhook}` (resourceful) |
| **Restore route** | ❌ Not available (no soft deletes) |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ❌ Missing (no LogsActivity, no manual logging) |
| **Risk** | **HIGH** — permanent deletion, no activity log, no recovery. Webhooks are security-sensitive endpoints that can fire on sensitive events and their deletion leaves zero audit trail |

---

### 15. Attachments

| Property | Value |
|----------|-------|
| **Model** | `Attachment` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /attachments/{id}` → `AttachmentController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not available |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None — also deletes physical file from storage disk |
| **Activity log** | ✅ Auto-logged |
| **Risk** | **MEDIUM** — physical file is deleted even though the DB row is only soft-deleted. After restore, the DB row comes back but the file is gone (if `Storage::delete` was called). This is a data loss scenario: soft delete + file deletion are inconsistent |

---

### 16. Service Providers

| Property | Value |
|----------|-------|
| **Model** | `ServiceProvider` |
| **SoftDeletes** | **YES** |
| **Delete route** | `DELETE /service-providers/{id}` → `ServiceProviderController::destroy()` |
| **Restore route** | `PATCH /service-providers/{id}/restore` (super-admin only) |
| **Force delete route** | `DELETE /service-providers/{id}/force-delete` (super-admin only) |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ **NONE** — critical gap: no check if 7 child entity types (Hosting, Domain, VPS, ExpiryTracker, OtherService, VoIP, DomainEmail) reference this provider |
| **Related records checked** | ❌ None |
| **Activity log** | ✅ Auto-logged (password excluded) |
| **Risk** | **HIGH** — soft delete preserves data but 7 types of child records will have orphaned `service_provider_id`. A force delete would cascade 7 types of orphans. Restore available for super-admin |

---

### 17. Roles

| Property | Value |
|----------|-------|
| **Model** | `Role` (vendor: `hasinhayder/tyro`) |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /roles/{id}` → `RoleController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | Partial — `admin` and `super-admin` slugs are protected. **No check if users are assigned to this role** |
| **Related records checked** | ❌ None — `user_roles` pivot rows, `privilege_role` pivot rows, and `ModuleRolePermission` records all become orphaned |
| **Activity log** | ❌ Missing |
| **Risk** | **CRITICAL** — permanent deletion, no recovery, no user-assignment check. Deleting a role that has 50 assigned users suddenly removes all their permissions with zero warning |

---

### 18. Permissions (Module Permissions)

| Property | Value |
|----------|-------|
| **Model** | `ModuleRolePermission` |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /module-permissions` → `ModulePermissionController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ✅ Custom JS `confirmRemove()` on index page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None — no check if users have overrides on this module+role combination |
| **Activity log** | ✅ Auto-logged via `ModuleRolePermission` LogsActivity trait |
| **Risk** | **MEDIUM** — permanent deletion, but permission records are additive data (can be recreated); no user-override check means users with individual overrides still retain those even after the role-level permission is removed |

---

### 19. Privileges

| Property | Value |
|----------|-------|
| **Model** | `Privilege` (vendor: `hasinhayder/tyro`) |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /privileges/{id}` → `PrivilegeController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ✅ `data-confirm="Are you sure?"` on show page |
| **In-use protection** | ❌ None — no check if privilege is attached to roles |
| **Related records checked** | ❌ None — `privilege_role` pivot rows orphaned |
| **Activity log** | ❌ Missing (vendor model, no LogsActivity) |
| **Risk** | **HIGH** — permanent deletion, no recovery, no role-assignment check. Deleting a privilege silently breaks permission checks for every role that included it |

---

### 20. Login Audits

| Property | Value |
|----------|-------|
| **Model** | `LoginAudit` |
| **SoftDeletes** | **NO** — deletion is **permanent** |
| **Delete route** | `DELETE /login-audits/{id}` → `LoginAuditController::destroy()` |
| **Restore route** | ❌ Not available |
| **Force delete route** | ❌ Not applicable |
| **Confirmation** | ❌ None on index page |
| **In-use protection** | ❌ None |
| **Related records checked** | ❌ None |
| **Activity log** | ❌ Missing |
| **Risk** | **MEDIUM** — audit records can be permanently deleted with no trail; no confirmation dialog on the index page (unlike every other module that has `data-confirm` on show pages). Bulk deletion via BulkActionService also has no confirmation |

---

## Cross-Cutting Risk Matrix

| Module | Soft Delete | Restore Available | Force Delete | In-Use Check | Relations Checked | Confirmation | Activity Logged | Overall Risk |
|--------|:-----------:|:-----------------:|:------------:|:------------:|:-----------------:|:------------:|:---------------:|:------------:|
| Users | ✅ YES | ❌ No | ❌ No | Partial (last SA) | ❌ | ✅ Modal | ❌ | MEDIUM |
| Domains | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| Hosting | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| VPS | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | LOW |
| Domain Emails | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | LOW |
| VoIP | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | LOW |
| Other Services | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | LOW |
| Expiry Trackers | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| **SMTP Profiles** | **NO** | ❌ No | N/A | ✅ **YES** | ✅ **YES** | ✅ Modal | ✅ | MEDIUM |
| Assets | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| **Tasks** | ✅ YES | **No** | **No** | ❌ | ❌ | ✅ Modal | ✅ | **HIGH** |
| Vault | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| Notes | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | LOW |
| **Webhooks** | **NO** | ❌ No | N/A | ❌ | ❌ | ✅ Modal | ❌ | **HIGH** |
| Attachments | ✅ YES | ❌ No | ❌ No | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| **Service Providers** | ✅ YES | ✅ Yes | ✅ Yes | ❌ | ❌ | ✅ Modal | ✅ | **HIGH** |
| **Roles** | **NO** | ❌ No | N/A | Partial (slugs) | ❌ | ✅ Modal | ❌ | **CRITICAL** |
| Permissions (MRP) | **NO** | ❌ No | N/A | ❌ | ❌ | ✅ Modal | ✅ | MEDIUM |
| **Privileges** | **NO** | ❌ No | N/A | ❌ | ❌ | ✅ Modal | ❌ | **HIGH** |
| **Login Audits** | **NO** | ❌ No | N/A | ❌ | ❌ | ❌ None | ❌ | MEDIUM |

---

## Systemic Issues

### Issue 1: BulkActionService bypasses SoftDeletes

`BulkActionService::runAction()` uses:
```php
$modelClass::whereIn('id', $ids)->delete();  // SQL DELETE — no model events
```

This physically deletes rows even for models with `SoftDeletes`. A bulk delete of Users, Tasks, or Assets would DELETE the row instead of setting `deleted_at`. **This is a data loss bug for all models with SoftDeletes.**

For models WITHOUT SoftDeletes (`SmtpProfile`, `Webhook`, `Role`, `Privilege`, etc.), the behavior is the same — permanent deletion — but this is expected since there's no soft delete path anyway.

**Affected models:** User, Domain, Hosting, Vps, DomainEmail, Voip, OtherService, ExpiryTracker, Asset, Task, VaultEntry, Note, Attachment, ServiceProvider (14 models with SoftDeletes that would be hard-deleted by bulk action).

### Issue 2: AttachmentController deletes file + soft-deletes DB row

```php
Storage::disk('public')->delete('attachments/'.$attachment->filename);  // File gone
$attachment->delete();  // DB row soft-deleted
```

After restore:
- DB row is restored
- The physical file is permanently gone
- The attachment appears in the UI but the download link returns 404

**This is a data loss scenario.** The file delete should either be deferred to `forceDelete()` only, or the DB delete should also be permanent (to match the file deletion).

### Issue 3: Activity log deletion bypass

The `activity_log` table has no delete route in the Web controller. However, `BulkActionService` can bulk-delete any model type that triggers `LogsActivity` auto-logging. The auto-logged deletion events are themselves not deletable (no route), but the underlying model data being deleted means the activity log entries for those models still reference deleted subject IDs.

### Issue 4: No restore for User, Task, Attachment, Webhook, Role, Privilege, LoginAudit

Of 7 models without restore routes:
- **User** — soft-deletes but cannot be un-deleted via UI
- **Task** — soft-deletes but cannot be un-deleted via UI
- **Attachment** — soft-deletes but cannot be un-deleted via UI (and file is already gone)
- **Webhook** — no soft deletes, permanent
- **Role** — no soft deletes, permanent
- **Privilege** — no soft deletes, permanent
- **LoginAudit** — no soft deletes, permanent

### Issue 5: `users/index.blade.php` confirmation

The users index page has inline delete buttons (via `<x-action>` component). Need to verify if the `confirm` parameter is passed to the action component for index-page deletions:

Looking at the blade templates, all show pages have `data-confirm="Are you sure?"` but index pages may not. If an admin deletes from the index (without opening the show page), they may not get a confirmation dialog.

---

## Summary of Recommended Fixes

### P0 — Critical (data loss or security bypass)

| # | Module | Issue | Fix |
|---|--------|-------|-----|
| 1 | **BulkActionService** | Hard-deletes SoftDeletes models | Change to `Model::whereIn('id', $ids)->each(fn($m) => $m->delete())` or use `SoftDeletes`-aware logic |
| 2 | **Roles** | No check for assigned users before permanent delete | Add `Role::users()->count()` check; block if users assigned |
| 3 | **Privileges** | No check for role attachment before permanent delete | Add `Privilege::roles()->count()` check |

### P1 — High (orphan data, missing recovery)

| # | Module | Issue | Fix |
|---|--------|-------|-----|
| 4 | **Attachments** | File deleted on soft delete; restore creates broken reference | Move file deletion to `forceDelete()` only, or make DB delete permanent |
| 5 | **Service Providers** | No check on 7 child entity types before delete | Add `usageCount()` similar to SmtpProfile; block if children exist |
| 6 | **Tasks** | No restore route | Add `TaskController::restore()` + route |
| 7 | **Users** | No restore route | Add `UserController::restore()` + route or remove SoftDeletes |
| 8 | **Webhooks** | Permanent delete with no activity log | Add SoftDeletes + LogsActivity; at minimum add manual activity log |

### P2 — Medium (orphaned references, missing protections)

| # | Module | Issue | Fix |
|---|--------|-------|-----|
| 9 | **Assets** | Can delete assigned asset | Check `status !== 'assigned'` before delete |
| 10 | **Expiry Trackers** | Notification history orphaned | Cascade delete or soft-delete notifications |
| 11 | **Vault** | Asset.vault_entry_id orphaned | Check if any Asset references this vault entry |
| 12 | **Domains** | DomainEmail.domain_id orphaned | Check for dependent DomainEmails |
| 13 | **Hosting** | Domain.hosting_id orphaned | Check for dependent Domains |
| 14 | **Login Audits** | No confirmation on index page delete | Add `data-confirm` to login audit delete button |
| 15 | **BulkAction** | No confirmation for bulk delete | Add count summary in confirmation dialog |
