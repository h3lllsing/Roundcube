# OPSPILOT User Access & Permission Workflow — UX Audit

**Status:** Final  
**Scope:** Create User → Assign Role → Permission Matrix (view/edit/reset)  
**Mode:** Read-only audit — no code was modified during analysis.

---

## A. Current Authorization Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION (Laravel Auth)                │
│  Login → Session → Auth::user() → middleware                   │
└────────────────────────────┬───────────────────────────────────┘
                             │
┌────────────────────────────▼───────────────────────────────────┐
│                    AUTHORIZATION LAYER                          │
│  ┌──────────────┐   ┌─────────────────┐   ┌───────────────┐   │
│  │   Role Model │   │ ModuleRolePerm   │   │ UserModulePerm│   │
│  │  (slug, name)│──▶│ (role_id, module │   │ (user_id,     │   │
│  │              │   │  _id, can_read,  │   │  module_id,   │   │
│  │              │   │  can_create…)    │   │  can_read…)   │   │
│  └──────────────┘   └─────────────────┘   └───────────────┘   │
│         │                                                      │
│  ┌──────▼──────────────────────────────────────────────────┐   │
│  │         HasModulePermissions Trait                       │   │
│  │  effectiveModulePermissions($module) → array:            │   │
│  │    role[]         ← ModuleRolePermission (baseline)       │   │
│  │    user_override[] ← UserModulePermission (if exists)     │   │
│  │    effective[]    ← override ?? role (inheritance chain)  │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         UserPermissionService                             │   │
│  │  - buildRoleSummaries()                                   │   │
│  │  - getModulesWithFeatures()                               │   │
│  │  - saveUserModulePermissions()                            │   │
│  │  - clonePermissions()                                     │   │
│  │  - preventSuperAdminAssignment()                          │   │
│  │  - getSuperAdminRoleId()                                  │   │
│  │  - resetAllOverrides()                                    │   │
│  └──────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────┘
```

Decision chain for `canOnModule(module, permission)`:
1. Query `UserModulePermission` for row with `user_id + module_id`
2. If row exists, check the specific column (e.g., `can_delete`). If `true` → allowed; if `false` → denied (explicit override)
3. If no row exists, fall through to `Role` → `ModuleRolePermission` → check column
4. If neither exists → default `false`

---

## B. Complete User Workflow

```
  CREATE USER ──────────────────────────────────────────────────────────┐
  │  route: users.create → GET                                          │
  │  view: users/create.blade.php                                       │
  │  controller: UserController@create                                  │
  │  1. Abort if not super-admin                                        │
  │  2. Load roles (all except * and super-admin)                       │
  │  3. Load modules with features                                      │
  │  4. Load all users for clone-from dropdown                          │
  │  5. Build role summaries for the "Assign Roles" section             │
  │  6. Render form                                                     │
  │                                                                     │
  │  Form fields:                                                       │
  │  ├─ Basic Information                                               │
  │  │  ├─ Full Name (required)                                         │
  │  │  ├─ Email Address (required)                                     │
  │  │  ├─ Status: Active / Pending / Disabled                          │
  │  │  └─ Authentication: Invite Email / Set Password Manually         │
  │  ├─ Set Password (conditional on manual auth)                       │
  │  │  ├─ Password + Confirm                                           │
  │  │  └─ JavaScript toggles visibility + required attr                │
  │  ├─ Role Assignment                                                 │
  │  │  └─ Primary Role (single select, excludes super-admin)           │
  │  └─ Actions                                                         │
  │     ├─ Cancel → back to user index                                  │
  │     └─ Create User → POST to store()                                │
  │                                                                     │
  │  store() → POST                                                     │
  │  1. Abort if not super-admin                                        │
  │  2. Validate: name, email, password/confirm, status, role,          │
  │     permissions array, clone_user_id, copy_roles, copy_overrides,   │
  │     copy_status, clone_role_handling                                │
  │  3. Transaction:                                                    │
  │     a. Create User (name, email, hashed password)                   │
  │     b. Set suspended_at if status=suspended                         │
  │     c. If clone_user_id:                                            │
  │        - copy_roles: clone source's roles (exclude super-admin)     │
  │        - copy_overrides: clone source's UserModulePermissions       │
  │        - copy_status: clone source's suspended_at                   │
  │        - role_handling: use_cloned/replace/merge                    │
  │     d. Else: use submitted roles[]                                  │
  │     e. Sync roles on user                                           │
  │     f. Save submitted permissions (UserModulePermission rows)       │
  │     g. Activity log entry                                           │
  │  4. Redirect to users.show                                          │
  │                                                                     │
  │  NOTE: create form only accepts a SINGLE role via <select>.         │
  │  The store() controller accepts roles[] array.                      │
  │  There is a disconnect: the form cannot assign multiple roles.      │
  └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
  SHOW USER ─────────────────────────────────────────────────────────────┐
  │  route: users.show → GET                                            │
  │  view: users/show.blade.php                                         │
  │  controller: UserController@show                                    │
  │  1. Abort if not super-admin                                        │
  │  2. Load user + roles + module permissions                           │
  │  3. Build summary stats: roles_count, accessible_modules,           │
  │     denied_modules, overrides_count, allowed_permissions,           │
  │     denied_permissions                                              │
  │  4. Build 2D permission matrix grouped by feature                   │
  │  5. Build offboarding checklist                                     │
  │  6. Last login from activity log                                    │
  │                                                                     │
  │  Actions:                                                           │
  │  ├─ Edit Permissions → users.permissions.edit                       │
  │  ├─ Clone User → users.clone                                        │
  │  ├─ Edit → users.edit                                               │
  │  ├─ Delete → users.destroy                                          │
  │  ├─ Suspend / Unsuspend                                             │
  │  └─ View permission matrix (accordion per feature)                  │
  └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
  EDIT USER ─────────────────────────────────────────────────────────────┐
  │  route: users.edit → GET                                            │
  │  view: users/edit.blade.php                                         │
  │  1. Form: name, email, password, suspended_at                       │
  │  2. Role checkboxes (multi-select)                                  │
  │  3. Permission overrides summary + "Configure Overrides" button     │
  │  4. "View Effective Permissions" link                               │
  │                                                                     │
  │  update() → PUT                                                     │
  │  1. Validate fields                                                 │
  │  2. Sync roles (replaces all roles)                                 │
  │  3. Role permission diff detection: if role_changed, remove         │
  │     orphaned UserModulePermission rows where role no longer         │
  │     grants that module                                              │
  │  4. Activity log                                                    │
  └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
  EDIT PERMISSIONS (Permission Matrix) ──────────────────────────────────┐
  │  route: users.permissions.edit → GET                                │
  │  view: users/permissions.blade.php (Alpine.js SPA)                  │
  │                                                                     │
  │  This is the most complex page: full JS-driven permission manager.  │
  │                                                                     │
  │  PHP Pre-processing:                                                │
  │  ├─ For each module, compute:                                       │
  │  │  • effective permissions (merged role + user override)           │
  │  │  • baseline preset (0=none, 1=view, 2=manage, 3=custom)         │
  │  │  • current preset                                                │
  │  │  • sensitive flag (from config)                                  │
  │  │  • hasOverride flag                                              │
  │  │  • toggle states (view/create/edit/delete/approve/export/        │
  │  │    reveal/import)                                                │
  │  ├─ Serialize to JSON for Alpine x-data                             │
  │                                                                     │
  │  Alpine.js Features:                                                │
  │  ├─ Module filtering (search query)                                 │
  │  ├─ Filter chips: All / Modified / Sensitive / Manage / Custom      │
  │  │  / Inherited                                                     │
  │  ├─ Category accordions with expand/collapse                        │
  │  ├─ Module rows with preset dropdown (None/View/Manage/Custom)      │
  │  ├─ Inline permission editor (opens per-module)                     │
  │  ├─ Stats bar (granted/denied/modified/sensitive counts)            │
  │  ├─ Unsaved changes warning bar                                     │
  │  ├─ Diff panel (shows pending changes before save)                  │
  │  ├─ Sensitive permission confirmation modal                         │
  │  ├─ Reset all overrides modal                                       │
  │  ├─ Bulk apply modal (apply preset to category)                     │
  │  ├─ Role change modal (reset/keep overrides)                        │
  │  ├─ Navigation guard modal                                          │
  │  └─ Summary collapsible                                             │
  │                                                                     │
  │  Save flow:                                                         │
  │  ├─ Collect all changed permissions                                 │
  │  ├─ Check for sensitive permissions → show confirmation if needed  │
  │  ├─ POST to users.permissions.update                                │
  │  ├─ Server: upsert UserModulePermission rows for changed modules    │
  │  └─ Activity log                                                    │
  │                                                                     │
  │  update() route handler:                                            │
  │  ├─ Accepts user_permissions[module_id][permission_key] = 0|1      │
  │  ├─ reset_all flag                                                  │
  │  ├─ For each module: upsert or delete UserModulePermission row      │
  │  ├─ If reset_all: delete all UserModulePermission for user         │
  │  ├─ Clear orphaned overrides (where role grants no access)         │
  │  └─ Redirect back with success message                             │
  └─────────────────────────────────────────────────────────────────────┘
```

---

## C. User Access States Analysis

Each user has exactly **3 access state attributes** that interact:

| Attribute | Storage | UI Representation |
|-----------|---------|-------------------|
| `suspended_at` (timestamp\|null) | `users.suspended_at` | Show: badge (Suspended/Active); Edit: date input; Create: Status dropdown |
| Roles (many-to-many) | `role_user` pivot | Show: role links; Edit: checkboxes; Create: single select |
| Permission Overrides | `user_module_permissions` | Show: matrix per-feature; Edit: full Alpine matrix; Create: not visible |

### State Transitions

```
  ┌──────────────────────────────────────────────────────────┐
  │                    ACCESS STATES                          │
  │                                                          │
  │  suspended_at = null     suspended_at = set               │
  │  ┌─────────────────┐    ┌──────────────────────┐         │
  │  │   ACTIVE        │    │   SUSPENDED          │         │
  │  │   Full access   │───▶│   No access          │         │
  │  │   per RBAC      │    │   (regardless of     │         │
  │  │                 │◀───│   roles/permissions)  │         │
  │  └─────────────────┘    └──────────────────────┘         │
  │         │                       │                        │
  │         │ role changes          │ (no effect)            │
  │         ▼                       ▼                        │
  │  Roles recalculated      Still suspended                 │
  │                                                          │
  │  Overrides can further modify granular access            │
  └──────────────────────────────────────────────────────────┘
```

**Key insight:** `suspended_at` is a hard block that overrides everything. A suspended user has zero access regardless of roles or overrides.

---

## D. UX Problems Found

### D1. Create User: Cannot assign multiple roles from UI (HIGH)
- **File:** `resources/views/users/create.blade.php:77`
- The create form has a **single `<select>`** for role assignment (`name="role_id"`).
- But `UserController@store` accepts `roles[]` as an **array** (line 91: `'roles' => 'nullable|array'`).
- **Problem:** Admin creates a user with one role, then must navigate to **Edit User** to add more roles. This is an extra round-trip.
- **Fix:** Replace single select with checkboxes (as done in `edit.blade.php`).

### D2. Create User: No feedback about role permissions (MEDIUM)
- **File:** `resources/views/users/create.blade.php`
- The role `<select>` has no inline indication of what permissions each role grants.
- `$roleSummaries` is loaded in the controller but **not rendered** in the view.
- Admin must know role definitions by heart or open another page.
- **Fix:** Show a compact permission summary (or a hover tooltip) when a role is selected.

### D3. Edit User: Role change implicitly trashes overrides (HIGH)
- **File:** `app/Http/Controllers/Web/UserController.php:update()` method
- When roles change, the controller removes **orphaned** `UserModulePermission` rows where the role no longer grants access to that module.
- This is a silent data-loss risk: an override for a module whose role-grant disappears is deleted without warning.
- The permissions matrix page has a "Role Changed" modal that asks reset/keep, but the plain `edit.blade.php` form has **no such protection**.
- **Fix:** Either warn on the edit form or redirect through the permissions matrix when roles change.

### D4. Permission matrix: "Inherited" filter is misleading (LOW)
- **File:** `resources/views/users/permissions.blade.php:168`
- Filter chip `filter="inherited"` exists but all modules with no overrides are inherited. The filter actually shows modules that are inherited AND have no user-specific changes.
- Scope confusion: does "inherited" mean "from role" (which is all non-overridden modules) or "from another user via clone"?
- **Fix:** Rename to "From Role" or clarify the tooltip.

### D5. Permission matrix: Unsaved state not visible on the edit form (MEDIUM)
- **File:** `resources/views/users/edit.blade.php`
- The edit form shows override count but does not indicate if there are **unsaved** changes from a prior matrix session.
- If admin makes changes in the matrix, navigates away, then returns to edit form — there is no visual indicator.
- **Fix:** Add a link to the diff/log of their last permission change session.

### D6. Role assignment inconsistency: Single select vs checkboxes (MEDIUM)
- Create: single `<select name="role_id">` (users/create.blade.php:77)
- Edit: checkboxes `<input type="checkbox" name="roles[]">` (users/edit.blade.php:26)
- The `store()` controller expects `roles[]` array but the form sends `role_id` as a scalar.
- **Problem:** The store method only uses `$request->input('roles', [])` which would be empty from the create form! Wait — let me check...
  - Actually, `create.blade.php` has `<select name="role_id">` but `store()` uses `$request->input('roles', [])`. This means **roles are never saved from the create form**. The role field is decorative only.
  - Confirmed: Line 151: `$finalRoles = $request->input('roles', []);` — this will be empty because the form sends `role_id`, not `roles[]`.
  - **This is a bug:** Creating a user with a role selected does NOT actually assign that role.
- **Severity: CRITICAL**

### D7. Clone from user: Nested complexity (MEDIUM)
- **File:** `resources/views/users/create.blade.php`
- The clone feature adds 3 boolean flags (copy_roles, copy_overrides, copy_status) + a radio for role_handling.
- This is powerful but creates cognitive load on the create form.
- **Recommendation:** Move clone to a separate page (`users/clone.blade.php` already exists).

### D8. Sensitive permission UX: Warning shown after action (LOW)
- **File:** `resources/views/users/permissions.blade.php:272-296`
- The sensitive confirmation modal appears **during save**, not when the user toggles a sensitive permission.
- **Fix:** Show inline warning when the toggle is flipped, not at save time.

### D9. No integration test coverage for permission state transitions (HIGH)
- The codebase has no automated tests for:
  - Create user → role assignment → effective permissions
  - Edit user → change role → orphaned override cleanup
  - Clone user → permission copy
  - Suspend → access revocation
- **Fix:** Add feature tests.

---

## E. Role Baseline vs User Override Analysis

### Current Architecture Design

```
  ┌─────────────────────────────────────────────────────────────┐
  │                     PERMISSION RESOLUTION                    │
  │                                                             │
  │  ┌──────────────┐    ┌──────────────────────┐               │
  │  │ Role Baseline│    │  User Override?      │               │
  │  │ (ModuleRole  │───▶│  (UserModulePerm     │               │
  │  │  Permission) │    │   row exists?)       │               │
  │  └──────────────┘    └───────────┬──────────┘               │
  │                                  │                          │
  │                           ┌──────▼──────┐                   │
  │                           │   YES        │                  │
  │                           │  Use override │                  │
  │                           │  value        │                  │
  │                           └──────┬──────┘                   │
  │                                  │                          │
  │                           ┌──────▼──────┐                   │
  │                           │   NO         │                   │
  │                           │  Use role     │                  │
  │                           │  value        │                  │
  │                           └──────────────┘                   │
  │                                                             │
  │  Effective = override_defined ? override : role              │
  │                                                             │
  │  No cascading, no additive merging per-permission.          │
  │  Override is all-or-nothing per module row.                 │
  └─────────────────────────────────────────────────────────────┘
```

### Preset Levels

| Preset | Label | Permissions |
|--------|-------|-------------|
| 0 | None | No permissions |
| 1 | View | can_read only |
| 2 | Manage | can_read + can_create + can_update |
| 3 | Custom | Anything else |

### Override Detection Logic

In `resources/views/users/permissions.blade.php:22-24`:
```php
$overrideVal = $effective[$key]['user_override'] ?? null;
if ($overrideVal !== null) {
    $overridePerms[$key] = $overrideVal;
}
```

An override exists if `user_override` is not null — i.e., a `UserModulePermission` row exists for this module. This means:
- `user_override = true` → explicit grant (overrides role's deny)
- `user_override = false` → explicit deny (overrides role's grant)

**Issue:** There is no distinction between "override not set" and "override set to false". A `false` override means "explicitly denied" which locks the permission even if the role later grants it.

---

## F. Recommendation: Simple / Advanced Modes

### Rationale

The current permission matrix is extremely powerful but overwhelming for common operations:
- **80% of users** just need role assignment + basic override
- **20% of users** need the full granular matrix

### Proposed Architecture

```
  ┌─────────────────────────────────────────────────────────────────────┐
  │                      MODE SELECTOR                                   │
  │                                                                      │
  │  ┌────────────────────────────┐   ┌──────────────────────────────┐  │
  │  │      SIMPLE MODE           │   │      ADVANCED MODE           │  │
  │  │  (default, recommended     │   │  (current full matrix)       │  │
  │  │   for most users)          │   │                              │  │
  │  │                            │   │  - Inline editor              │  │
  │  │  - Role selector only      │   │  - Per-permission toggles    │  │
  │  │  - Preset dropdown per     │   │  - Diff panel                │  │
  │  │    module (None/View/      │   │  - Sensitive confirmation    │  │
  │  │    Manage/Custom)          │   │  - Bulk apply                │  │
  │  │  - "Custom" opens modal    │   │  - Reset all                 │  │
  │  │  - No sensitive warnings   │   │  - Clone permissions         │  │
  │  │    (presets are safe)      │   │  - Role change modal         │  │
  │  │  - No bulk apply           │   │                              │  │
  │  │  - No diff panel           │   │                              │  │
  │  └────────────────────────────┘   └──────────────────────────────┘  │
  └─────────────────────────────────────────────────────────────────────┘
```

### Simple Mode Wireframe

```
  ┌──────────────────────────────────────────────────────────────────┐
  │  Edit Permissions — Jane Smith                                   │
  │  Role: IT Support  │  [Switch to Advanced Mode]                  │
  │                                                                  │
  │  ┌─────────────────────────────────────────────────────────────┐ │
  │  │  Module                     │  Preset (Role: View)          │ │
  │  ├─────────────────────────────┼───────────────────────────────┤ │
  │  │  Servers                    │  [ View ▼ ]  ✓ from Role      │ │
  │  │  Domains                    │  [ Manage ▼ ]  ⚡ overridden  │ │
  │  │  Vault                      │  [ None ▼ ]  ✓ from Role     │ │
  │  │  ...                        │  [ Custom ▼ ]  ⚡ overridden │ │
  │  └─────────────────────────────┴───────────────────────────────┘ │
  │                                                                  │
  │  [← Back to User]  [Save Overrides]                              │
  └──────────────────────────────────────────────────────────────────┘
```

### Implementation Notes

**Simple Mode:**
- Show only the preset dropdown per module (already exists as `.al-select`)
- Hide: inline editor panel, diff panel, bulk apply, sensitive confirmation, filter chips except "All" and "Modified"
- Show "Modified" count in a compact bar
- Selecting "Custom" in simple mode → open a **modal** (not the full inline editor) with just the toggles for that module, plus a note "Switch to Advanced for finer control"
- No save confirmation for non-sensitive changes
- Autosave on preset change (with undo option)

**Advanced Mode:**
- The existing full matrix as-is
- Mode preference stored per-user-session or in `user_preferences` table

---

## G. Proposed Create User Flow (Simple Mode)

```
  STEP 1: BASIC INFO
  ┌─────────────────────────────────────────────────────────────────┐
  │  Full Name     [________________________]                       │
  │  Email         [________________________]                       │
  │  Status        [Active ▼]                                       │
  │  Auth Method   ○ Send Invitation  ○ Set Password                │
  │  Password      [______]  Confirm [______]   (if manual)         │
  └─────────────────────────────────────────────────────────────────┘

  STEP 2: ROLE ASSIGNMENT
  ┌─────────────────────────────────────────────────────────────────┐
  │  ☐ IT Support           ☐ Administrator                        │
  │  ☐ Developer            ☐ Viewer                               │
  │  ☐ Manager              ☐ Auditor                              │
  │                                                                 │
  │  [Show Permission Summary]  ▼                                   │
  │  ┌─────────────────────────────────────────────────────────┐    │
  │  │  Role: IT Support                                      │    │
  │  │  Access to: Servers (View), Domains (Manage), ...      │    │
  │  └─────────────────────────────────────────────────────────┘    │
  └─────────────────────────────────────────────────────────────────┘

  STEP 3: QUICK OVERRIDES (optional — collapsed by default)
  ┌─────────────────────────────────────────────────────────────────┐
  │  ▶ Quick Permission Adjustments  (optional)                     │
  └─────────────────────────────────────────────────────────────────┘

  [Cancel]  [Create User]
```

The key change from current flow:
1. Multi-select roles (checkboxes instead of single select)
2. Permission summary shown on role selection
3. Quick overrides as an **optional collapsed section** — not shown by default
4. No clone complexity (move to separate page)

---

## H. Simple Mode Permission Summary — Wireframe

```
  ╔══════════════════════════════════════════════════════════════╗
  ║  Permission Summary — Jane Smith (IT Support)               ║
  ║                                                             ║
  ║  Role Baseline: IT Support                                  ║
  ║                                                             ║
  ║  ┌─────────────────────────────────────────────────────────┐║
  ║  │  Feature       │ Granted │ Denied │ Overridden          │║
  ║  ├────────────────┼─────────┼────────┼─────────────────────┤║
  ║  │ Infrastructure │  3/4    │  0/4   │  0                  │║
  ║  │ Productivity   │  2/3    │  0/3   │  1 (Vault: Deny)   │║
  ║  │ Administration │  1/2    │  0/2   │  0                  │║
  ║  └────────────────┴─────────┴────────┴─────────────────────┘║
  ║                                                             ║
  ║  Total: 6 granted, 0 denied, 1 override                     ║
  ╚══════════════════════════════════════════════════════════════╝
```

This would appear on the **User Show** page as a compact summary. Currently the show page has a full expandable matrix which is too detailed for quick reference.

---

## I. Backend / Schema Requirements

### I1. Missing: user_preferences table (or column)
To store per-user mode preference:
```sql
ALTER TABLE users ADD COLUMN permission_mode VARCHAR(10) DEFAULT 'simple';
```
Or create a `user_preferences` key-value table.

### I2. Missing: role permission cache invalidation
When `ModuleRolePermission` changes, all users with that role have stale effective permissions until next calculation. This is acceptable for small deployments but should be documented.

### I3. No migration needed for Simple Mode
Simple mode is entirely a UI change — the backend already supports preset-level permission management:
- `UserPermissionService@saveUserModulePermissions` accepts the full permission array
- The existing `@al-select` dropdown already maps to presets
- The `getEffectiveModulePermissions` already returns role baseline vs overrides

---

## J. Migration Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| **D6 CRITICAL:** Create user form sends `role_id` but `store()` reads `roles[]` | Roles never saved on creation | Fix immediately: either change form to `roles[]` or change controller to read `role_id` |
| Users accustomed to full matrix | Confusion when simple mode hides features | Show "Switch to Advanced" link prominently; persist mode choice |
| Orphaned override deletion on role change | Silent data loss | Show diff of what will be removed before saving; add undo/restore |
| Sensitive permission check bypass in simple mode | Security gap | In simple mode, "Custom" preset for sensitive modules always triggers advanced mode |
| Existing overrides may conflict with new simple presets | Inconsistent state | Simple mode reads/writes the same `UserModulePermission` rows — no migration needed |

---

## K. Implementation Batches

### Batch 1 — CRITICAL BUGFIX (1-2 hrs)
- **Fix D6:** Change `create.blade.php` `<select name="role_id">` to checkboxes `<input type="checkbox" name="roles[]">` matching `edit.blade.php` pattern
- Update `create()` controller to remove `roleSummaries` if not used, or render it

### Batch 2 — Simple Mode UI (3-4 days)
- **A. Mode switching:** Add `permission_mode` column to users; add toggle button on permissions page
- **B. Simple mode blade:** New `users/permissions-simple.blade.php` or conditional rendering in existing blade
  - Show only preset dropdown, stats bar, modified chips
  - Hide: inline editor, diff panel, bulk apply, sensitive modal, filter chips (except All/Modified)
  - Custom preset → opens modal with toggles + "Switch to Advanced" link
- **C. Autosave:** Add debounced fetch for preset changes with undo notification

### Batch 3 — Create User Improvements (2-3 days)
- **A.** Role checkboxes with inline permission summary on selection
- **B.** Collapsed "Quick Overrides" section on create form
- **C.** Separate clone page (`users/clone.blade.php`) — already exists
- **D.** Fix auth_method to send invitation or set password (already partially done)

### Batch 4 — Advanced Mode Polish (1-2 days)
- **A.** Fix filter chip labels ("From Role" instead of "Inherited")
- **B.** Inline warning when toggling sensitive permissions (not just at save)
- **C.** Role change guard on `edit.blade.php` (warning before save)
- **D.** Orphaned override preview on role change

### Batch 5 — Tests (2-3 days)
- **A.** Feature test: Create user with roles → verify effective permissions
- **B.** Feature test: Edit user → change role → orphaned override cleanup
- **C.** Feature test: Clone user → permission copy
- **D.** Feature test: Suspend → access revoked
- **E.** Feature test: Simple mode permission save
- **F.** Feature test: Mode switching persistence

---

## L. Testing Strategy

### Unit Tests
- `HasModulePermissions` trait: test effective permission resolution with mock role + override
- `UserPermissionService`: test clone, reset, role summary building

### Feature Tests (Browser/Dusk or PHPUnit)
| Test | Scenario | Expected |
|------|----------|----------|
| Create user with roles | POST a new user with 2 roles checked | User has both roles after creation |
| Create user without roles | POST with no roles | User created, no role assignments |
| Edit user → change roles | Update roles on existing user | Old role permissions cleaned up |
| Clone user permissions | Clone source → verify target | Target has same UserModulePermission rows |
| Suspend user | POST suspend | User cannot authenticate |
| Permission override save | Toggle a permission in matrix | UserModulePermission row upserted |
| Reset all overrides | POST with reset_all | All UserModulePermission rows deleted |
| Simple mode autosave | Change preset dropdown | Permission saved without explicit save click |

### Manual QA Checklist
- [ ] Create user → Verify role appears on show page
- [ ] Create user → Edit permissions → Toggle → Save → Verify on show page
- [ ] Change user role on edit form → Verify orphaned overrides are handled
- [ ] Clone user with all flags → Verify exact copy
- [ ] Suspend user → Verify login fails
- [ ] Switch to simple mode → Verify only preset dropdowns visible
- [ ] Apply custom preset in simple mode → Verify modal opens
- [ ] Switch to advanced → Verify full matrix restored

---

## M. Security Risks Identified

| ID | Risk | Location | Severity |
|----|------|----------|----------|
| S1 | `store()` accepts permissions array for on-the-fly override during creation | UserController@store:158 | LOW — it's super-admin only |
| S2 | No audit trail for permission changes except activity log — can be purged | activity_log table | MEDIUM |
| S3 | Orphaned override deletion on role change has no undo | UserController@update | MEDIUM |
| S4 | Simple mode might lead to accidental permission grants | UX risk | LOW — presets are still granular underneath |
| S5 | `super-admin` role cannot be assigned through UI but can be added via DB directly | Code prevents it but DB has no constraint | LOW |

---

## N. Summary & Priority

| Priority | Item | Effort | Impact |
|----------|------|--------|--------|
| **CRITICAL** | **D6: Create user form sends `role_id` but controller reads `roles[]`** | 1 hr | Roles not saved on creation |
| HIGH | D3: Silent override deletion on role change | 4 hrs | Data loss risk |
| HIGH | S2: No permission change audit trail | 2 days | Compliance risk |
| HIGH | Simple mode implementation | 4 days | UX improvement for 80% of users |
| MEDIUM | D1: Cannot assign multiple roles on create | 1 hr | Extra steps for admin |
| MEDIUM | Batch 5: Tests | 2 days | Quality assurance |
| LOW | D7: Move clone to separate page | 2 hrs | Reduced cognitive load |
| LOW | D4: Filter chip label | 30 min | Clarity |
| LOW | D8: Sensitive warning timing | 1 hr | Better UX feedback |
