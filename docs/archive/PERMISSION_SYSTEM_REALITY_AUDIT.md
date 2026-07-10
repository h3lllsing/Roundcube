# PERMISSION SYSTEM REALITY AUDIT

Date: 2026-07-03
Scope: Full permission model — tables, resolvers, controllers, views, scopes, middleware

---

## 1. TABLES USED

### `roles` — Role definitions
| Field | Type | Purpose |
|-------|------|---------|
| id | BIGINT PK | Identity |
| name | VARCHAR | Display name |
| slug | VARCHAR | Machine name (unique concept, index only) |
- **Effect on access**: Determines which user group a user belongs to. Used in `hasRole(slug)` checks.
- **Used?** Yes, actively.

### `user_roles` — User-to-role pivot
| Field | Type | Purpose |
|-------|------|---------|
| user_id, role_id | BIGINT FK | Many-to-many mapping |
| UNIQUE(user_id, role_id) | Index | Prevents duplicates |
- **Effect on access**: A user's roles are the baseline for all permission checks.
- **Used?** Yes, actively.

### `module_role_permissions` — Role-level permissions per module (THE CENTRAL TABLE)
| Field | Type | Purpose |
|-------|------|---------|
| module_id, role_id | BIGINT FK | Which role, which module |
| can_create/read/update/delete/approve/export/reveal | BOOLEAN, default false | Permission booleans |
| UNIQUE(module_id, role_id) | Index | One permission row per role+module |
- **Effect on access**: This is the authoritative baseline. Every permission check starts here.
- **Used?** Yes, actively. This is THE primary permission store.

### `user_module_permissions` — Per-user overrides
| Field | Type | Purpose |
|-------|------|---------|
| user_id, module_id | BIGINT FK | Which user, which module |
| can_create/read/update/delete/approve/export/reveal/import | BOOLEAN **nullable** | Override values (null = inherit role) |
| UNIQUE(user_id, module_id) | Index | One override row per user+module |
- **Effect on access**: Overrides the role baseline. Non-null values take precedence over `module_role_permissions`.
- **Used?** Yes, actively. But see BUG A below.

### `modules` — Module definitions
| Field | Type | Purpose |
|-------|------|---------|
| id, feature_id, name, slug | Various | Defines what modules exist |
| is_active | BOOLEAN | Soft toggle for module availability |
- **Effect on access**: Modules are the resource being permitted. Permission checks reference `module_id` column.
- **Used?** Yes, actively.

### `features` — Feature groups
| Field | Type | Purpose |
|-------|------|---------|
| id, name, slug, icon | Various | Grouping for modules (e.g., "Infrastructure") |
- **Effect on access**: Only used for UI grouping in the permission editor sidebar.
- **Used?** Yes, for display only.

### `privileges` / `privilege_role` — Legacy/alternative permission system
| Table | Purpose |
|-------|---------|
| `privileges` | Defines named privileges (e.g., "users.manage") |
| `privilege_role` | Many-to-many: role ↔ privilege |
- **Effect on access**: Used by `hasPrivilege()` checks in the `HasTyroRoles` trait. However, **no controller or view in this app uses `hasPrivilege()` for authorization**. All module-level access uses `hasRole()` + `canOnModule()`.
- **Used?** NO — effectively dead code for module access. Only used in admin UI (CRUD).

### `role_templates` — Pre-configured permission templates
| Field | Purpose |
|-------|---------|
| permissions_json (JSON) | Stores module→permissions mapping for template application |
- **Effect on access**: Applied to roles via `RoleTemplateController@apply`. Writes to `module_role_permissions`.
- **Used?** Yes, during role setup. Purely a convenience tool.

---

## 2. PERMISSION RESOLVER — THE `HasModulePermissions` TRAIT

File: `app/Traits/HasModulePermissions.php`

### `canOnModule(Module $module, string $action): bool` (line 22)
```
- Check UserModulePermission for this user+module
  → if found AND column is non-null → RETURN that boolean
- Check ModuleRolePermission where role_id IN user's roles
  → where column = true → RETURN true if any exists
- RETURN false
```
This is the **primary check** used by every controller and view (`abort_unless`, `@if($canCreate)`, etc.)

**BUG A (CRITICAL):** `UserModulePermission` rows are never cleaned up when overrides are reset. The JS save logic excludes modules where `preset === baseline` from the payload. `saveUserModulePermissions()` only processes modules in the payload. So stale override rows persist and continue to affect `canOnModule()`.

### `getAccessibleModuleIds(string $action): array` (line 82)
```
- Get all module IDs where ANY of user's roles has $action=true in ModuleRolePermission
- Apply user overrides: add if override grants, remove if override denies
- Return unique sorted IDs
```
Used by:
- `RbacScope::apply()` — global query scopes
- `SidebarComposer` — sidebar menu visibility
- `DashboardController` — dashboard data scoping
- Various service `list()` methods — record filtering

### `getEffectiveModulePermissions(Module $module): array` (line 107)
Returns `{role, user_override, effective, source}` per permission column.
Used by `UserController@show` and the permission editor blade to display current state.

---

## 3. DATA VISIBILITY SCOPING — THE `RbacScope` CLASS

File: `app/Helpers/RbacScope.php`

### Flow for `RbacScope::apply(Model::class, 'module')`:
```
super-admin → NO SCOPE (sees all records)
everyone else → WHERE module_id IN (accessibleModuleIds)
                If no accessible modules → WHERE 1=0 (no records)
```
### Flow for `RbacScope::apply(Model::class, 'ownership')` (fallback):
```
super-admin → NO SCOPE
admin → WHERE module_id IN (accessibleModuleIds)
        (if empty → WHERE 1=0)
everyone else → WHERE user_id = current_user_id
```

**BUG B (MODERATE):** The `admin` branch in the ownership fallback is effectively **dead code** for all current controllers, since every controller uses `'module'` visibility. But if a new controller uses ownership visibility without understanding this, admins would get module-scoped data instead of ownership-scoped.

**DESIGN ISSUE:** The `'module'` visibility correctly avoids user_id filtering for global records. But it depends entirely on `module_id` being set correctly on every record. See BUG C.

---

## 4. SIDEBAR VISIBILITY

File: `app/Http/View/Composers/SidebarComposer.php`

```
super-admin → show everything
everyone else → show module if module_id IN getAccessibleModuleIds('read')
```
This is correct and respects both role permissions and user overrides.

**No bug here** — the sidebar correctly reflects the user's effective read access.

---

## 5. CONTROLLER-LEVEL PERMISSION CHECKS

All global master controllers use the same pattern (example: ServiceProviderController):

```
index()   → RbacScope::apply() → scope filters records
            Check canCreate/canExport/canDelete for button visibility
create()  → abort_unless(hasRole('super-admin') || canOnModule(module, 'create'))
store()   → Same as create, with module lookup
show()    → RbacScope::apply() → findOrFail
edit()    → RbacScope::apply() → abort_unless(hasRole('super-admin') || canOnModule(record->module, 'update'))
update()  → Same as edit
destroy() → Same pattern with 'delete'
```

Verification: This pattern is consistent across all 9 modules. The check correctly references `$record->module` for record-specific authorization, not `$user_id`.

---

## 6. OVERALL ASSESSMENT

| Component | Status |
|-----------|--------|
| Module-level role permissions | WORKING |
| User overrides | PARTIALLY BROKEN (can't clear) |
| Record data scoping (module scope) | WORKING |
| Record data scoping (ownership scope) | WRONG for global records |
| Sidebar menu visibility | WORKING |
| Controller authorization | WORKING |
| Bulk action permissions | WORKING |
| Form field placement | PROBLEMATIC (see BUG C, D) |
| Role template application | WORKING |
| Legacy privilege system | DEAD CODE (unused for access) |

---

## 7. SUMMARY OF ALL BUGS FOUND

| ID | Severity | Description |
|----|----------|-------------|
| A | CRITICAL | User overrides cannot be removed once set (stale DB rows) |
| B | MODERATE | user_id forced to Auth::id() on create for global records |
| C | MODERATE | module_id field on forms allows mis-categorization |
| D | MODERATE | VoIP/Domain Email missing module_id field → null module_id → invisible records |
| E | LOW | RbacScope admin fall-through is dead code |
| F | LOW | Legacy privileges system maintained but unused |
| G | LOW | VoIP edit view receives `$modules`/`$users` but doesn't render them |
