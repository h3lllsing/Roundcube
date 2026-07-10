# GLOBAL DATA ARCHITECTURE REVIEW

## Executive Summary

OpsPilot manages 8 global record types (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Assets) plus ExpiryTrackers. Every one of these tables has `user_id` as a **NOT NULL foreign key with ON DELETE CASCADE**. This is the single most critical data architecture issue: **corporate records are structurally tied to individual user accounts** at the database level.

---

## 1. `user_id` on Global Tables — Detailed Analysis

### Schema Reality (all 9 tables)

| Table | user_id FK | ON DELETE | Nullable | Current fillable |
|-------|-----------|-----------|----------|------------------|
| domains | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| hostings | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| vps | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| voip | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| service_providers | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| domain_emails | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| other_services | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| assets | NOT NULL | CASCADE | No | REMOVED (Phase 3) |
| expiry_trackers | NOT NULL | CASCADE | No | REMOVED (Phase 3) |

### The Cascade Problem

If user `42` is deleted:
- ALL domains where `user_id = 42` → **DELETED**
- ALL hostings where `user_id = 42` → **DELETED**
- ALL VPS where `user_id = 42` → **DELETED**
- ALL VoIP where `user_id = 42` → **DELETED**
- ALL service providers where `user_id = 42` → **DELETED**
- ALL domain emails where `user_id = 42` → **DELETED**
- ALL other services where `user_id = 42` → **DELETED**
- ALL assets where `user_id = 42` → **DELETED**
- ALL expiry trackers where `user_id = 42` → **DELETED**

These are GLOBAL corporate assets. A single employee departure should not cascade-delete company infrastructure records.

### Phase 3 Only Fixed the Application Layer

The `$fillable` removal and store() cleanup prevent NEW records from getting `user_id` set via mass assignment. But:
1. **Existing records still have user_id** — the migration never made it nullable
2. **The FK constraint still has ON DELETE CASCADE** — one soft-delete of a user still risks mass data loss
3. **The column is still NOT NULL** — new records from seeder/factory/tinker that don't set user_id will fail

### Recommendation

- **MUST FIX**: Change all 9 migrations to make `user_id` nullable and set ON DELETE SET NULL
- **MUST FIX**: Add a proper `created_by` column (int, nullable, FK SET NULL) using the existing Blameable pattern
- The column should stay physically (as audit metadata) but NOT as a structural foreign key constraint

---

## 2. `module_id` on Global Tables

### Schema Reality

All 9 tables have `module_id` as NULLABLE FK with ON DELETE SET NULL.

### Assessment

**module_id should exist.** It is the structural access control mechanism. RbacScope applies `WHERE module_id IN (...)` for all module-scoped visibility. Without it, the entire RBAC system collapses.

**Users should NOT select it.** Phase 2B correctly removed it from all forms. The controller auto-sets it from the module slug.

**ON DELETE SET NULL is risky.** If a Module is deleted (soft or hard), all records referencing it get `module_id = NULL` and immediately become invisible to all non-super-admins under RbacScope. This is silent data loss.

### Recommendation

- **Keep module_id as the access control column** — it is architecturally correct
- **Module deletion should be prevented** (is_active toggle + scope filtering instead of soft delete)
- The ON DELETE SET NULL should remain as safety net, but module deletion should be restricted

---

## 3. RbacScope Maturity Assessment

### Current Architecture

```
apply($modelClass, $visibility):
  super-admin → NO SCOPE (sees all)
  visibility='module' → global scope: WHERE module_id IN (accessibleIds)
  admin role (no visibility) → same module-based scope
  default → global scope: WHERE user_id = $currentUser
```

### Strengths
- Simple, single-file implementation
- Super-admin bypass is correct
- Module-based scoping is correct for global records
- Ownership scoping is correct for Vault

### Weaknesses
- **Applies via addGlobalScope()** — these cannot be removed unless the query uses `withoutGlobalScope()`. If any controller forgets to call `userOwnedFilter()`, records leak.
- **Only called in Web controllers** — API controllers bypass RbacScope entirely
- **Visibility is a controller concern** — not a model concern. A model could be queried from anywhere (job, command, API) and the scope won't apply.
- **Admin/admin distinction**: lines 419-426 treat admin as a fallback to module scope, but a user with "admin" role who has no module permissions gets user_id ownership scope — this is wrong for global records.

### Production Readiness

Not production-ready for the API surface. The Web layer is scoped. The API layer is NOT.

---

## 4. API vs Web Visibility Inconsistency

### Web Dashboard (OperationsWidget)
Queries use `WHERE module_id IN (accessibleIds)` — correct.

### API Dashboard (Api\DashboardController)
Lines 151-152:
```php
$activeQuery->where('user_id', $user->id);
$expiredQuery = $modelClass::where('status', 'expired')->where('user_id', $user->id);
```

The API Dashboard filters ALL global records by `user_id` instead of `module_id`. This means:
- A user with module-level read access sees global records on the Web dashboard
- The SAME user sees ZERO records via the API dashboard (because user_id doesn't match)
- **Different truths from different entry points**

### API Export (Api\ExportController)
Line 129:
```php
$query->where('user_id', $user->id);
```
Same inconsistency. Non-super-admin users via API export will always get empty results because global records no longer have user_id set.

### API Global Record Controllers
The API controllers (Api\VoipController, Api\DomainEmailController, etc.) all check `user_id` directly instead of using module-based scope. They return different data than the Web controllers for the same user.

---

## 5. Blocking Issues Before Production

### CRITICAL — Cascade Delete Risk
Any user deletion cascades to all global records. Migration fix needed before production.

### CRITICAL — API Inconsistency
API Dashboard, Export, and record controllers use `user_id` filtering. Web uses `module_id` filtering. Same user, different data.

### HIGH — Module Deletion = Silent Data Loss
Deleting a module sets `module_id = NULL` on all related records. RbacScope makes them disappear. No error, no warning.

### MEDIUM — ActivitiesWidget user_id Fallback
AssetsWidget line 27 falls back to `where('user_id', $user->id)` when `accessibleIds` is empty. Since Phase 3 removed user_id from new records, this fallback returns nothing.

### MEDIUM — ExportController user_id Fallback
Web ExportController line ~130 also falls back to `where('user_id', $user->id)` for non-admin non-super-admin users. Same problem.

---

## 6. Schema-Level Decisions

### Column `assigned_to` on `assets`
This is an Asset-specific concept (physical asset checked out to an employee). This is a legitimate FK to `users(id)`. It represents current custodian, not ownership.

### Column `disabled_by` on `expiry_trackers`
Legitimate audit FK to `users(id)`. Records WHO disabled a notification.

### Column `created_by` / `updated_by` on `modules`, `features`, `smtp_profiles`
Legitimate audit trail via Blameable trait. Uses SET NULL on delete. Correct pattern that should be replicated to global records instead of `user_id`.

---

## 7. Recommendations by Priority

### P0 — Pre-Launch (Blocking)
1. Create migration: make `user_id` nullable + change FK to SET NULL on all 9 global tables
2. Create migration: add nullable `created_by` FK (SET NULL) to all 9 global tables
3. Fix API Dashboard to use module-based scoping (same as Web OperationsWidget)
4. Fix API Export to use module-based scoping (same as Web ExportController)

### P1 — Launch Critical
5. Implement Module deletion protection (is_active toggle, prevent hard/soft delete)
6. Remove `user_id` fallback from AssetsWidget and ExportController
7. Audit all API controllers for user_id vs module_id access

### P2 — Post-Launch
8. Implement RbacScope as a model concern (trait) instead of per-controller call
9. Standardize all 4 visibility layers (Web, API, Dashboard, Export) into a single source
10. Run data migration to backfill `created_by` from `user_id` on existing records before `user_id` is made nullable
