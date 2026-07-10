# FINAL_RELEASE_RBAC_AUDIT.md

**Date:** 2026-07-09
**Legend:** ✅ Done | ⚠️ Partial/In Progress | ⏳ Pending | ➡️ Next Sprint
**Sources:** CTO-02 (Permission SSOT), CTO-05 (RBAC Audit), CTO-06 (Policy Gate), CTO-07 (RBAC Scope), Audits 100-109 (Permission Security)

---

## TASK-001: Permission Single Source of Truth
**Source:** CTO-02
**Files:** `app/Traits/HasModulePermissions.php`, `app/Helpers/RbacScope.php`, `app/Http/View/Composers/SidebarComposer.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Identified 7 competing sources of truth for permissions. |
| Implement | ⚠️ Partial | `getAccessibleModuleIds()` at `HasModulePermissions.php:82-104` identified as closest to true SSOT. Service layer and Dashboard still use `WHERE user_id`. |
| Verify | ⏳ Pending | All permission checks route through single evaluator. |
| Signoff | ⚠️ Partial | Web controllers use RbacScope ✅. API/service/dashboard still inconsistent. |
| Next Sprint | ➡️ | Replace service layer `WHERE user_id` with RbacScope. Fix Dashboard. Fix Export. |

---

## TASK-002: RBAC Scope Architecture
**Source:** CTO-05 (RBAC Scope Architecture Audit)
**Files:** `app/Helpers/RbacScope.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `RbacScope::apply()` uses `WHERE module_id IN (...)` — DOES NOT match `module_id IS NULL`. VoIP and Domain Email records have null module_id → invisible. |
| Implement | ⏳ Pending | Fix: auto-set module_id in store() OR add `OR module_id IS NULL` fallback. |
| Verify | ⏳ Pending | VoIP/Domain Email visible to non-SA users after fix. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix VoIP/Domain Email module_id. Add RbacScope null fallback. |

---

## TASK-003: Evaluator Consistency
**Source:** CTO-05, Audits 103, 107
**Files:** `app/Traits/HasModulePermissions.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 3 evaluator paths identified. Path 3 misses user overrides for non-role modules (C-001 bug). |
| Implement | ⏳ Pending | Fix Path 3 to check user overrides. Score: 8.2/10 currently. |
| Verify | ⏳ Pending | All 3 paths return identical results. |
| Signoff | ⚠️ Partial | Main path (canOnModule) consistent. Edge case (C-001) pending. |
| Next Sprint | ➡️ | Fix C-001: user override check in Path 3. |

---

## TASK-004: Reveal Controller Module String Mismatch
**Source:** CTO-05
**Files:** 2 controllers check wrong module string for `reveal` action
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 2 controllers use incorrect module slug for reveal permission check. |
| Implement | ✅ Done | Fixed to use correct module slug. |
| Verify | ✅ Done | Reveal action checks correct module. |
| Signoff | ✅ Done | All controllers use matching module strings. |
| Next Sprint | ➡️ | None. |

---

## TASK-005: User-Level Permission Override Issues
**Source:** CTO-05, Audits 102, 106
**Files:** `app/Http/Controllers/Web/UserController.php`, `app/Services/UserPermissionService.php`
**Priority:** 🔴 P0 — CRITICAL

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | **BUG A:** User overrides cannot be removed once set. Stale DB rows persist. |
| Implement | ⏳ Pending | Fix: clear stale rows when override is reset. |
| Verify | ⏳ Pending | Test: remove override → confirm DB row deleted. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Fix stale row persistence. Add `exists:modules,id` validation. Add optimistic locking. |

---

## TASK-006: Permission Cache Staleness
**Source:** CTO-05, Audits 103, 105
**Files:** `app/Traits/HasModulePermissions.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Permission cache TTL: 3600s. NOT invalidated on write. Stale window up to 1hr. |
| Implement | ⏳ Pending | Purge cache when user/role permissions change. |
| Verify | ⏳ Pending | Test: change permission → immediate effect without waiting. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add cache invalidation on permission write. |

---

## TASK-007: Dead Permission Flags
**Source:** CTO-05, CTO-08
**Files:** `config/permissions.php`, `database/migrations/*`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `can_approve` stored in DB, never saved, never evaluated. `can_import` may have mismatch. |
| Implement | ⏳ Pending | Remove `can_approve` from config/DB OR implement. Verify `can_import` consistency. |
| Verify | ⏳ Pending | Confirm no dead flags in permission evaluation. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Remove `can_approve`. Fix `can_import` mismatch. |

---

## TASK-008: API vs Web Authorization Consistency
**Source:** CTO-06, Audits 102, 104, 105
**Files:** `app/Http/Controllers/Api/*.php` (11 files)
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | API controllers use `WHERE user_id` filter. Web controllers use RbacScope (module-based). Different visibility for same data. |
| Implement | ⏳ Pending | Fix: API controllers should use RbacScope like web controllers. |
| Verify | ⏳ Pending | Test: same user gets same results via API and Web. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Align API authorization with Web. Fix all 11 API controllers. |

---

## TASK-009: Attack Scenario Coverage
**Source:** Audits 104, 108
**Files:** Multiple
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | 20 attack scenarios analyzed. 18/20 adequately protected. 2 partial (extra permission keys, stale cache). 1 inconsistent (API vs Web scoping). |
| Implement | ⏳ Pending | Fix extra permission key acceptance. Fix stale cache issue. Align API scoping. |
| Verify | ⏳ Pending | All 20 scenarios verified. |
| Signoff | ⚠️ Partial | No privilege escalation path identified ✅. Remaining are LOW-MEDIUM risk. |
| Next Sprint | ➡️ | Fix Scenario 4 (key validation), Scenario 9 (cache), Scenario 10 (API scoping). |

---

## TASK-010: Super Admin Assignment Prevention
**Source:** CTO-06
**Files:** `app/Http/Controllers/Api/UsersController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `Web\UserController` prevents super-admin assignment. `Api\UsersController` does NOT — API can assign super-admin. |
| Implement | ⏳ Pending | Add `preventSuperAdminAssignment()` check to API controller. |
| Verify | ⏳ Pending | Test: API cannot assign super-admin role. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add super-admin assignment prevention to API. |

---

## TASK-011: Self-Demotion Prevention
**Source:** CTO-06
**Files:** `app/Http/Controllers/Api/UsersController.php`
**Priority:** 🟡 P1 — HIGH

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Web controller prevents self-demotion. API controller does NOT — API allows self-remove of roles. |
| Implement | ⏳ Pending | Add self-demotion check to API update(). |
| Verify | ⏳ Pending | Test: user cannot remove own super-admin role. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add self-demotion prevention to API. |

---

## TASK-012: FormRequest authorize() Returns True
**Source:** CTO-06
**Files:** `app/Http/Requests/*.php` (39 files)
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | All 39 FormRequest `authorize()` methods return `true`. Authorization done inline in controllers. |
| Implement | ⏳ Pending | Post-v1 improvement: move authorization to FormRequests. |
| Verify | ⏳ Pending | No change in behavior after migration. |
| Signoff | ⏳ Pending | Deferred to post-v1. |
| Next Sprint | ➡️ | Option A: Document as architectural decision. Option B: Migrate to FormRequest authorization. |

---

## TASK-013: getAccessibleModuleIds() Caching
**Source:** CTO-07
**Files:** `app/Traits/HasModulePermissions.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | `getAccessibleModuleIds()` called 2-3+ times per page load. Uses `pluck` with no caching. |
| Implement | ⏳ Pending | Use Laravel `once()` helper to cache per-request. |
| Verify | ⏳ Pending | Same results, fewer queries. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add `once()` wrapper to `getAccessibleModuleIds()`. |

---

## TASK-014: Module Delete Observer
**Source:** Audits 102, 105, 109
**Files:** `app/Providers/AppServiceProvider.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | No observer cleans up `user_module_permissions` when a module is deleted. Users get stale permission rows. |
| Implement | ⏳ Pending | Add Module deleted observer to clean up user_module_permissions. |
| Verify | ⏳ Pending | Test: delete module → confirm user_module_permissions cleaned. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add ModuleObserver. |

---

## TASK-015: Permission Key Validation
**Source:** Audits 102, 105, 106
**Files:** `resources/js/permissions.js`, `app/Http/Controllers/Web/UserController.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Server accepts any key from `config('permissions.keys')`. JS sends only valid keys. Both consistent — but no server-side validation that the key belongs to the module. |
| Implement | ⏳ Pending | Validate that each permission key is valid for the given module. |
| Verify | ⏳ Pending | Test: crafted payload with invalid key rejected. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Add module-level permission key validation. |

---

## TASK-016: Legacy Privilege System
**Source:** CTO-02, Permission System Reality Audit
**Files:** `app/Models/Privilege.php`, `app/Http/Controllers/PrivilegeController.php`
**Priority:** 🔵 P2 — MEDIUM

| Step | Status | Detail |
|------|--------|--------|
| Fix Preflight | ✅ Done | Legacy privilege system (privileges + privilege_role) is CRUD-able but NEVER evaluated for access control. |
| Implement | ⏳ Pending | Remove OR document as deprecated. |
| Verify | ⏳ Pending | No impact on access control after removal. |
| Signoff | ⏳ Pending | Not yet addressed. |
| Next Sprint | ➡️ | Business decision: remove or keep for future use. |
