# PERMISSION EVALUATOR CONSISTENCY AUDIT

**Project:** OpsPilot Portal
**Date:** 2026-07-08
**Scope:** All code paths that evaluate permissions — do they agree?

---

## EVALUATOR PATHS

Three separate code paths evaluate permissions. They MUST agree:

### Path 1: `canOnModule()` — Authorization Gate
**File:** `app/Traits/HasModulePermissions.php:43-59`
**Used by:** Controllers (abort_unless), Blade (permission-check), Services
**Architecture:** Direct DB query (no cache)
**Merge Logic:**
1. Check `UserModulePermission` for exact user+module → if column non-null, return it
2. Check `ModuleRolePermission` for user's roles → if any role has column=true, return true
3. Return false

### Path 2: `getEffectiveModulePermissions()` — Display
**File:** `app/Traits/HasModulePermissions.php:125-168`
**Used by:** `users/permissions.blade.php` (init data)
**Architecture:** Direct DB query (no cache)
**Merge Logic:**
1. Query `ModuleRolePermission` for user's roles
2. Query `UserModulePermission` for user+module
3. For each key: overrideVal !== null → effective=overrideVal; roleVal !== null → effective=roleVal; else → effective=false

### Path 3: `getAllModulePermissions()` / `getAccessibleModuleIds()` — Scoping
**File:** `app/Traits/HasModulePermissions.php:62-65, 68-106, 109-122`
**Used by:** SidebarComposer, RbacScope, CalendarService, ExportService, etc.
**Architecture:** Cached (60s TTL, keyed by `perms_generation`)
**Merge Logic:**
1. Load ALL `ModuleRolePermission` grouped by module_id for user's roles
2. Merge per-module: OR across roles (if any role grants, it's granted)
3. Load `UserModulePermission` ONLY for modules that have role permissions
4. Apply overrides: if override column is non-null, override wins

---

## CONSISTENCY ANALYSIS

### Finding C-001: Path 3 Misses Overrides for Modules without Role Permissions

**Evidence:** `getAllModulePermissionsFromDb()` line 72 builds `$allModuleIds` from role-permission results. Line 89-91 loads user overrides only for those IDs. If a user has an override for a module that has ZERO role-permission entries, that override is NOT applied in Path 3.

**Impact:** `getAccessibleModuleIds('read')` may return wrong results for Path 3 consumers:
- RbacScope (using `getAccessibleModuleIds`) may not scope correctly
- SidebarComposer may show/hide wrong modules
- CalendarService may miss events

**Severity:** MEDIUM

### Finding C-002: Path 1 and Path 2 Use Direct DB, Path 3 Uses Cache

**Impact:** After saving permissions:
- Path 1 and Path 2 see changes immediately (no cache)
- Path 3 sees changes after cache TTL (60s) OR after `perms_generation` increment

`perms_generation` IS incremented in `saveUserModulePermissions()` (line 92) and `setForRole()` (line 46), so Path 3 cache is busted on save. However, `removeForRole()` does NOT increment (DUP-012), so Path 3 may be stale for up to 60s after role permission removal.

**Severity:** LOW

### Finding C-003: Super-Admin Handling Differs Across Paths

- **Path 1 (canOnModule):** Does NOT check super-admin. Callers must check separately.
- **Path 2 (getEffectiveModulePermissions):** Does NOT check super-admin. The Blade view sets baseline based on role perms, which may be empty for super-admin.
- **Path 3 (getAllModulePermissions):** Does NOT check super-admin. SidebarComposer and RbacScope check super-admin separately before calling.

**Result:** Consistent — all paths are neutral on super-admin. Bypass is done by callers.

**Severity:** INFO — by design

### Finding C-004: Ownership Check Mismatch — API vs Web

**Web Controllers:** Use `RbacScope::apply()` + `canOnModule()` for access control
**API Controllers:** Use `canOnModule()` + hardcoded `$record->user_id !== $user->id` ownership check

**Result:** Different standards for same business action. An admin user may access a record via Web (RbacScope grants module-level read) but be denied via API (ownership check fails).

**Severity:** MEDIUM

---

## RECOMMENDATIONS

| ID | Fix | Priority |
|----|-----|----------|
| C-001 | Remove `$allModuleIds` filter in `getAllModulePermissionsFromDb()` to load ALL user overrides | HIGH |
| C-002 | Add `Cache::increment('perms_generation')` to `removeForRole()` | MEDIUM |
| C-003 | No change needed | NONE |
| C-004 | Align API controllers to use RbacScope or remove ownership checks | HIGH (discussion needed) |
