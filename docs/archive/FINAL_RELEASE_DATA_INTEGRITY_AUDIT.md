# FINAL_RELEASE_DATA_INTEGRITY_AUDIT.md

**Date:** 2026-07-09
**Legend:** ✅ Done | ⚠️ Partial/In Progress | ⏳ Pending | ➡️ Next Sprint
**Sources:** CTO-04 (User ID/Module ID Semantic), CTO-06 (Database Integrity), Database Health Audit, Delete/Restore Safety Audit, Concurrency Audit, Global Master Record Visibility Audit

---

## TASK-001: user_id on Global Master Records
**Source:** CTO-04 (User ID/Module ID Semantic Audit)
**Files:** All 9 module controllers (`DomainController`, `HostingController`, `VpsController`, `VoipController`, `ServiceProviderController`, `DomainEmailController`, `OtherServiceController`, `AssetController`, `ExpiryTrackerController`)
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `user_id` forced to `Auth::id()` on create in ALL 9 controllers. Editable on update. Forms show "User" select field. Creates false ownership. |
| Implement | ⏳ Pending | Remove `user_id` from `$fillable` on all 9 models. Remove from forms. Set `user_id = null` in store. Add `created_by` via Blameable trait. |
| Verify | ⏳ Pending | Records created without user_id. No ownership filtering in service layer. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix all 9 controllers + 9 models + 9 forms. |

---

## TASK-002: module_id Auto-Set in Controllers
**Source:** CTO-04 (User ID/Module ID Semantic Audit)
**Files:** All module controllers
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `module_id` user-selectable on 7 module forms. Missing entirely from VoIP and Domain Email → null module_id → invisible records. |
| Implement | ⏳ Pending | Auto-set `module_id` based on route in store(). Remove from forms. Protect on update. |
| Verify | ⏳ Pending | VoIP/Domain Email have non-null module_id. No module_id field in forms. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix all controllers to auto-set module_id. Fix VoIP/Domain Email forms. |

---

## TASK-003: VoIP and Domain Email Module ID Fix
**Source:** CTO-04, CTO-07, Global Master Record Visibility Audit
**Files:** `VoipController.php`, `DomainEmailController.php`, `voip/create.blade.php`, `domain-emails/create.blade.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | VoIP and Domain Email records have `module_id = NULL` → invisible to ALL non-super-admin users under RbacScope. |
| Implement | ⏳ Pending | Add `module_id` field to VoIP/Domain Email forms OR auto-set in controllers. |
| Verify | ⏳ Pending | Non-SA user can see VoIP/Domain Email records. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | CRITICAL — fix before deploy. |

---

## TASK-004: Soft Delete Consistency
**Source:** Database Health Audit, Delete/Restore Safety Audit
**Files:** `app/Services/BulkActionService.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `BulkActionService::runAction()` does `$modelClass::whereIn('id', $ids)->delete()` — bypasses SoftDeletes. 14 models affected. |
| Implement | ⏳ Pending | Make bulk delete respect SoftDeletes trait. |
| Verify | ⏳ Pending | BulkDeleted record shows as soft-deleted (not gone). |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix BulkActionService to use model instance delete(). |

---

## TASK-005: Restore Routes Missing
**Source:** Delete/Restore Safety Audit
**Files:** `routes/web.php`, 7 controllers
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Tasks, Users, Webhooks, Privileges, LoginAudits, and others missing restore routes. |
| Implement | ⏳ Pending | Add restore routes for all soft-delete models. |
| Verify | ⏳ Pending | All models with SoftDeletes have restore route + controller method. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add restore routes for every SoftDelete model. |

---

## TASK-006: Attachment File Deletion on Soft Delete
**Source:** Delete/Restore Safety Audit
**Files:** `app/Http/Controllers/Web/AttachmentController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `AttachmentController::destroy()` deletes physical file on soft delete → data loss on restore. |
| Implement | ⏳ Pending | Move file deletion to `forceDelete()` only. |
| Verify | ⏳ Pending | Soft-deleted attachment → file still exists. Force-deleted → file removed. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix AttachmentController to keep file on soft delete. |

---

## TASK-007: Optimistic Locking (Concurrency)
**Source:** Concurrency Lost Update Audit
**Files:** All 15 editable module controllers
**Priority:** 🔴 P0 — CRITICAL (SMTP Profiles, Expiry Trackers)

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Zero optimistic locking. Every update follows `findOrFail → update($validated)`. Stale read window, mass overwrite. |
| Implement | ⏳ Pending | Add `updated_at` check to all update workflows. Priority: SMTP Profiles (P0 — setDefault race), Expiry Trackers (P0 — TOCTOU notification toggle), Users/Roles/Permissions (P1). |
| Verify | ⏳ Pending | Concurrent updates detected and rejected. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add optimistic locking to update methods. |

---

## TASK-008: Missing Foreign Key Indexes
**Source:** CTO-06 (Database Integrity), Database Health Audit
**Files:** 9 FK columns across schema
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Missing FK indexes on: `users.department_id`, `assets.category_id`, `assets.type_id`, `assets.location_id`, `assets.assigned_to`, `monitoring.assigned_to`, `monitoring.department_id`, `expiry_tracker.assigned_to`, `help_center_articles.category_id`. |
| Implement | ⏳ Pending | Add migration with indexes. |
| Verify | ⏳ Pending | `EXPLAIN` queries show index usage. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Create migration for missing FK indexes. |

---

## TASK-009: deleted_at Indexes on Soft-Delete Tables
**Source:** Database Health Audit
**Files:** 18 tables
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 18 soft-delete tables lack `deleted_at` indexes → full table scans on queries with `WITH TRASHED`. |
| Implement | ⏳ Pending | Add `deleted_at` index to all 18 tables. |
| Verify | ⏳ Pending | `EXPLAIN` shows index usage on deleted_at queries. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add index migration for all soft-delete tables. |

---

## TASK-010: Status Field Indexes
**Source:** Database Health Audit
**Files:** 10+ service tables
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 10+ tables with `status` field lack indexes → slow filtered queries. |
| Implement | ⏳ Pending | Add `status` index to all service tables. |
| Verify | ⏳ Pending | `EXPLAIN` shows index usage on status filters. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add index migration for status columns. |

---

## TASK-011: Role Permanent Delete Safety
**Source:** Delete/Restore Safety Audit
**Files:** `app/Http/Controllers/Web/RoleController.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Roles permanently deleted. No check for assigned users before delete. No recovery path. |
| Implement | ⏳ Pending | Add assigned-users check before role delete. Add SoftDeletes to roles. |
| Verify | ⏳ Pending | Role with assigned users cannot be deleted. Soft-deleted role can be restored. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add user-count check + SoftDeletes to Role. |

---

## TASK-012: Privilege Permanent Delete Safety
**Source:** Delete/Restore Safety Audit
**Files:** `app/Http/Controllers/Web/PrivilegeController.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Privileges permanently deleted. No role-attachment check. |
| Implement | ⏳ Pending | Add role-attachment check before delete. Add SoftDeletes. |
| Verify | ⏳ Pending | Privilege with attached roles cannot be deleted. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add role-check + SoftDeletes to Privilege. |

---

## TASK-013: Service Provider Delete — Child Entity Check
**Source:** Delete/Restore Safety Audit
**Files:** `app/Http/Controllers/Web/ServiceProviderController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Service Provider delete doesn't check for 7 child entity types (Hostings, Domains, etc.). |
| Implement | ⏳ Pending | Add usageCount check before delete. |
| Verify | ⏳ Pending | Provider with linked entities cannot be deleted. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add child-entity check to ServiceProviderController::destroy(). |

---

## TASK-014: Activity Logging Gaps
**Source:** Activity Logging Consistency Audit
**Files:** `UserController`, `RoleController`, `WebhookController`, `PrivilegeController`, `ModulePermissionController`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 6 HIGH severity missing logs: Users update/delete, Roles all CRUD, Webhooks all CRUD, Privileges all CRUD, Module permissions changes. |
| Implement | ⏳ Pending | Add `LogsActivity` trait + activity logging to all identified controllers. |
| Verify | ⏳ Pending | Activity log shows entries for all CRUD operations on Users/Roles/Webhooks/Privileges. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add logging to all 6 controllers. |

---

## TASK-015: Bulk Action Activity Logging
**Source:** Activity Logging Consistency Audit
**Files:** `app/Services/BulkActionService.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Bulk actions bypass model events entirely. Zero logs for all 19 bulk-enabled types. |
| Implement | ⏳ Pending | Add activity logging to BulkActionService. |
| Verify | ⏳ Pending | Bulk operations appear in activity log. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add logging for all bulk action types. |

---

## TASK-016: Dashboard Visibility — user_id vs module_id
**Source:** Global Master Record Visibility Audit
**Files:** `app/Http/Controllers/Web/DashboardController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Dashboard uses `WHERE user_id` in generic loop (line 148-155). Should use module scoping like RenewalsWidget. |
| Implement | ⏳ Pending | Replace `user_id` filter with `module_id IN (accessibleIds)`. |
| Verify | ⏳ Pending | Dashboard shows same records as module index pages. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix DashboardController generic loop. |

---

## TASK-017: Service Layer Visibility — user_id vs module_id
**Source:** Global Master Record Visibility Audit
**Files:** 9 service files (`DomainService.php`, `HostingService.php`, etc.)
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Service layer `list()` methods use `WHERE user_id` for 8/9 modules. Should use RbacScope. |
| Implement | ⏳ Pending | Replace `WHERE user_id` filters with RbacScope. |
| Verify | ⏳ Pending | Service layer returns module-scoped results. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix all 9 service layer list() methods. |

---

## TASK-018: ExportController Visibility
**Source:** Global Master Record Visibility Audit
**Files:** `app/Http/Controllers/Web/ExportController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | ExportController uses mixed visibility: Super-admin NO scope, Admin module-based, Normal user `WHERE user_id`. |
| Implement | ⏳ Pending | Normal user path should use module-based scoping. |
| Verify | ⏳ Pending | Export returns same records as index page. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix normal user export path to use module scoping. |

---

## TASK-019: Credential Storage Consolidation
**Source:** CTO-06 (Database Health Audit)
**Files:** 6 service tables (hostings, vps, voip, domain_emails, other_services, expiry_trackers, assets)
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ⚠️ Partial | 6+ tables store credentials directly. Vault provides encrypted alternative. Password field type uses `encrypted` cast. |
| Implement | ⏳ Pending | Audit and optionally migrate credentials to vault references. |
| Verify | ⏳ Pending | Vault entries linked correctly. |
| Signoff | ⚠️ Partial | Existing encrypted cast is adequate for v1.0. Full vault migration deferred. |
| Next Sprint | ➡️ | TASK-026: Consolidate to vault pattern in future sprint. |

---

## TASK-020: Deferred FK Constraint — smtp_profile_id
**Source:** CTO-06 (M-08)
**Files:** `expiry_tracker_notifications.smtp_profile_id`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Deferred FK constraint — orphaned records risk. |
| Implement | ⏳ Pending | Verify or add FK constraint. |
| Verify | ⏳ Pending | No orphaned notification records. |
| Signoff | ⚠️ Partial | Likely intentional. |
| Next Sprint | ➡️ | Verify and document FK behavior. |

---

## TASK-021: SMTP Profile — setDefault Race Condition
**Source:** Concurrency Audit
**Files:** `app/Http/Controllers/Web/SmtpProfileController.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `setDefault()` has race condition — can create two default profiles under concurrent requests. |
| Implement | ⏳ Pending | Add atomic update with `updated_at` check or DB-level unique constraint on `is_default`. |
| Verify | ⏳ Pending | Concurrent setDefault calls produce exactly one default. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix SMTP setDefault race. |

---

## TASK-022: Task Assignee Sync Bug
**Source:** Concurrency Audit
**Files:** `Web\TaskController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | TaskController assignee sync never calls `sync()`. Assignee updates silently ignored. |
| Implement | ⏳ Pending | Fix: properly sync assignee relationships on task update. |
| Verify | ⏳ Pending | Task assignees update correctly. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix TaskController assignee sync. |

---

## TASK-023: can_import Mismatch
**Source:** Database Health Audit
**Files:** `ModuleRolePermission` model, `RoleTemplateSeeder`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `can_import` column exists in DB migration but RoleTemplateSeeder may not set it. |
| Implement | ✅ Done | Verified and aligned. |
| Verify | ✅ Done | All permission flags consistent between seeder and model. |
| Signoff | ✅ Done | Mismatch resolved. |
| Next Sprint | ➡️ | None. |
