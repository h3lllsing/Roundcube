# 02 — PERMISSION SINGLE SOURCE OF TRUTH AUDIT

## There is NO single source of truth. There are 7 competing ones.

---

## Source 1: `module_role_permissions` table

**Purpose:** Role-level baseline permissions per module.
**Used by:** `HasModulePermissions::canOnModule()` as fallback.
**Written by:** `ModulePermissionService::setForRole()`, `RoleTemplateController`.
**Authority:** ✅ This IS the authoritative baseline.

**Issue:** Only written through admin UI and role templates. No seeding verification ensures every module has entries for every role. Missing entries default to `false` for all permissions. This is silent but acceptable.

---

## Source 2: `user_module_permissions` table

**Purpose:** Per-user permission overrides.
**Used by:** `HasModulePermissions::canOnModule()` as primary check.
**Written by:** `UserController::saveUserModulePermissions()`.
**Authority:** ✅ This correctly overrides Source 1.

**Issue (FIXED):** Stale rows were never cleaned up. The 4-line fix resolved this.

---

## Source 3: `user_roles` pivot table

**Purpose:** Maps users to roles.
**Used by:** `HasTyroRoles::roles()`, `HasModulePermissions::getRoleIds()`.
**Authority:** ✅ This determines which role permissions apply to which user.

**Clean.** No issues.

---

## Source 4: `RbacScope` global scopes

**Purpose:** Data-level visibility — which records a user can see.
**Used by:** All 9 module controllers.
**Authority:** ⚠️ **DUPLICATE** — also implemented in service layer and API controllers.

The web controllers rely on RbacScope. The service layer uses its own `WHERE user_id` filter. These are two different implementations of the same concept that PRODUCE DIFFERENT RESULTS.

**This is an architectural contradiction.** The RbacScope is correct (module-based), the service layer is wrong (ownership-based).

---

## Source 5: Controller-level `abort_unless` checks

**Purpose:** Action authorization — can user create/edit/delete/reveal?
**Used by:** All 9 module controllers.
**Authority:** ✅ This is the actual enforcement point.

**Issue:** No centralized policy. Each controller re-implements the same pattern. The `authorize()` method and Policy classes are unused.

---

## Source 6: SidebarComposer

**Purpose:** Menu visibility.
**Used by:** Sidebar blade view.
**Authority:** ⚠️ **DUPLICATE** — re-implements the same logic as the controller by calling `getAccessibleModuleIds('read')`.

**Issue:** The sidebar calls `$user->getAccessibleModuleIds('read')`, which queries the database for accessible module IDs. This is the same logic as the web controller's data scope, but run in a different context (view composer). If there's a cache inconsistency or stale data, the sidebar could show a module that the controller then blocks (or vice versa).

**Risk level:** LOW — both use the same underlying method.

---

## Source 7: `getAccessibleModuleIds()` — the REAL source of truth

**File:** `app/Traits/HasModulePermissions.php:82-104`

This method is called by:
- `RbacScope::apply()` — data visibility
- `SidebarComposer` — menu visibility
- `DashboardController` — dashboard widget scoping (Renewals)
- `VaultController` — vault access
- `TaskController` — task filtering

This is the closest thing to a single source of truth. It integrates role permissions + user overrides.

**But it's NOT used by:**
- Service layer `list()` methods — use `WHERE user_id`
- Dashboard generic loop — uses `WHERE user_id`
- ExportController — uses `WHERE user_id` for non-SA
- API controllers — use `WHERE user_id`

---

## SSOT VERDICT

| Component | Is it the SSOT? | Should it be? |
|-----------|-----------------|---------------|
| `module_role_permissions` | ✅ Baseline | ✅ Keep as baseline |
| `user_module_permissions` | ✅ Override | ✅ Keep as override |
| `getAccessibleModuleIds()` | ✅ Closest to SSOT | ✅ Extend usage |
| `RbacScope` | ✅ Uses SSOT | ✅ Keep, fix null module_id |
| Service layer `WHERE user_id` | ❌ WRONG SSOT | ❌ Remove |
| Dashboard `WHERE user_id` | ❌ WRONG SSOT | ❌ Replace with RbacScope |
| Export `WHERE user_id` | ❌ WRONG SSOT | ❌ Replace with RbacScope |
| API `WHERE user_id` | ❌ WRONG SSOT | ❌ Replace with RbacScope |

**Consequence:** A user can see 50 Hosting records on the web page, but:
- The API returns only 12 (their own)
- The dashboard count shows only 12
- The export exports only 12
- The widget shows only 12

Each of these outputs has a different "truth" about the same data. **This is unacceptable for enterprise production.**

---

## Secondary SSOT Issue: Two Authorization Systems

| System | Active? | Used by controllers? | Used by views? |
|--------|---------|---------------------|----------------|
| Module-level RBAC (module_role_permissions + user_module_permissions) | ✅ YES | ✅ YES | ✅ YES |
| Legacy Privileges (privileges + privilege_role) | ❌ NO | ❌ NO | ❌ NO |

The legacy privilege system is maintained, CRUD-able from admin UI, attached to roles, but **never evaluated for authorization**. It has:
- Full CRUD controllers (`PrivilegeController`)
- Full CRUD views (`privileges/`)
- Attach/detach to roles (`RoleController::attachPrivilege`)
- Import/export support
- Seed data

**This is dead-code maintenance burden.** Admin users can create, edit, and assign privileges believing they affect access — but they don't. The system doesn't check them anywhere.

**Recommendation:** Either remove it (if unused) or integrate it. Half-built security features are worse than missing ones.
