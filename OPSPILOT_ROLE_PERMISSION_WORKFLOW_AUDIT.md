# OpsPilot Role Permission Workflow UX Audit

## A. Current Role-Permission Architecture

There are **two separate permission systems** — a legacy `Privilege` system and a newer module-level permission system:

### Legacy Privilege System
- Tables: `privileges`, `privilege_role` (pivot), `roles` (via `TyroRole` base)
- Managed via `RoleService::attachPrivilege()` / `detachPrivilege()`
- UI: Appears in Role Show page as an attach/detach table
- Uses `TyroCache::forgetUsersByRole()` for cache invalidation
- The `HasModulePermissions` trait does NOT read privileges — only module permissions affect UI gating

### Module-Level Permission System (Primary)
- **Two models**: `ModuleRolePermission` (role baseline) + `UserModulePermission` (user overrides)
- **Resolution**: `HasModulePermissions` trait merges role OR-permissions → user overrides layer on top
- **Backend**: `ModulePermissionService` (role-level), `UserPermissionService` (user-level)
- **Preset semantics** (computed client-side in `permissions.js`):
  - `0` = No Access (all perms false)
  - `1` = View Only (can_read only)
  - `2` = Manage (can_read + can_create + can_update, no sensitive perms)
  - `3` = Custom (any other combination)
- **User permission UI adds**: `-1` = Inherit from Role (excluded from save payload, row deleted)

### Role Templates
- Table: `role_templates` with JSON `permissions_json`
- Pre-built templates: Super Admin, Admin, IT Support, Read Only
- Apply template → creates/updates `ModuleRolePermission` rows for target role
- Includes diff preview, dangerous confirmation, cache invalidation

---

## B. Current Canonical Workflow

### Role Permission Configuration Path

```
Administration → Roles → Click role name → Role Show page
```

**Role Show page** (`roles/show.blade.php`):
- Shows role name, slug, privilege count, user count
- Shows **legacy Privileges** table (attach/detach)
- Shows "Users with this role" list
- **NO link to Module Permissions**
- **NO module access summary**
- **NO "Configure Permissions" action**

### Actual Module Permission Configuration

The admin must navigate separately:

```
Administration → Permissions (sidebar) → Module Permissions page
```

**Module Permissions page** (`module-permissions/index.blade.php`):
- Table: rows = modules, columns = roles (excluding super-admin)
- Each cell shows CRUD letters (e.g., "CRU") or "—" if no row
- Click cell → modal with 8 checkboxes (create/read/update/delete/approve/export/reveal/import) + Save/Remove
- Vanilla JS, no Alpine, no presets, no search, no categories
- No role selector — **all roles shown simultaneously**
- Role `role_id` passed in **request body** (`module_id` + `role_id` as hidden form fields), not in URL

### User Permission Override Configuration (separate workflow)

```
Administration → Users → Edit User → "Permission Overrides" card → [Configure Overrides]
```

This leads to the **new Simple Mode** page (`users/permissions.blade.php`) with:
- Simple/Advanced mode toggle
- 5-option dropdown (Inherit from Role, No Access, View Only, Manage, Custom)
- Search, override-only filter, empty state
- This is the **user override** page, NOT role configuration

---

## C. Current Page Ownership

| Page | View | Controller | Purpose | Role Selection |
|------|------|-----------|---------|---------------|
| **Roles Index** | `roles.index` | Web RoleController | List all roles | N/A |
| **Role Show** | `roles.show` | Web RoleController | Role details + Privileges | Route param `{id}` |
| **Role Edit** | `roles.edit` | Web RoleController | Edit name/slug | Route param `{id}` |
| **Module Permissions** | `module-permissions.index` | Web ModulePermissionController | **Role permission matrix** | All roles in columns (no selector) |
| **Role Templates** | `role-templates.index` | Web RoleTemplateController | Template presets | N/A |
| **Template Apply** | `role-templates.apply` | Web RoleTemplateController | Apply template → role | Dropdown selector |
| **User Permissions** | `users.permissions` | Web UserController | **User overrides** | Derived from user's roles |
| **My Permissions** | `auth.my-permissions` | Web AuthController | Read-only self-view | From auth user |

### Navigation (Sidebar)

```
Administration group:
  → Users
  → Roles
  → Modules
  → Permissions  ← module-permissions.index
  → Features
  → ... (Mail Settings, Audit Trail, etc.)
```

**Key problem**: The "Permissions" sidebar item goes to the **module-permissions** page (role-level matrix), NOT the user permission page. The user permission page is only reachable via Users → Edit → Configure Overrides. This creates confusion about which page manages what.

---

## D. UX Problems

### Problem 1: Role permission configuration is DISCOVERABLE but not NAVIGABLE from Role page
From a role's detail page, there is no way to reach its permission configuration. The admin must know to use the sidebar "Permissions" link.

### Problem 2: Module Permissions page shows ALL roles at once
- No role selector/filter — the page shows all roles as columns simultaneously
- For a system with many roles, this becomes a wide, unreadable table
- Cannot "zoom in" on a specific role
- No search, no category grouping, no preset dropdowns

### Problem 3: No preset vocabulary on Module Permissions page
- The page shows raw CRUD letter codes (e.g., "CRU")
- Does NOT use preset labels (No Access, View Only, Manage, Custom)
- Admin must interpret CRUD letter combinations mentally
- No "Access Level" concept — just 8 independent checkboxes

### Problem 4: Inconsistent terminology
- User permissions page uses: Inherit from Role, No Access, View Only, Manage, Custom
- Module Permissions page uses: bare CRUD checkboxes
- Role Templates use: full permission matrix (checkmarks/crosses)
- Three different vocabularies for the same underlying data

### Problem 5: No access summary on Role page
- Role Show doesn't show how many modules the role can access
- Doesn't show which sensitive permissions are granted
- Doesn't show module access breakdown
- Admin must navigate to Module Permissions and manually scan the role's column

### Problem 6: Vanilla JS modal on Module Permissions page
- No Alpine.js integration
- No unsaved-changes guard
- No diff preview
- No bulk operations
- No sensitivity warnings
- Feels disconnected from the modern user permission UI

### Problem 7: Super-admin role hidden from Module Permissions
- The `super-admin` slug is explicitly excluded from the columns
- Admin cannot even view what super-admin has access to via this page
- Only `FeatureModuleSeeder` creates super-admin permissions

---

## E. Preset Semantics

### Current Preset Mapping (used in `permissions.js`)

| Preset | Value | `can_read` | `can_create` | `can_update` | `can_delete` | `can_approve` | `can_export` | `can_reveal` | `can_import` |
|--------|-------|-----------|-------------|-------------|-------------|--------------|-------------|-------------|-------------|
| No Access | 0 | false | false | false | false | false | false | false | false |
| View Only | 1 | true | false | false | false | false | false | false | false |
| Manage | 2 | true | true | true | false | false | false | false | false |
| Custom | 3 | (any other combination) |

**The backend (`ModulePermissionService::setForRole()`) does NOT understand presets.** It stores individual boolean columns. Presets are purely a UI concept computed client-side.

**Role Templates** define full permission matrices per module as JSON blobs. They bypass the preset system entirely.

### What the Audit Confirms for Role Permissions

The four-preset system (No Access, View Only, Manage, Custom) maps cleanly to `ModuleRolePermission` columns:

- **No Access (0)**: All 8 columns `false`
- **View Only (1)**: `can_read = true`, all others `false`
- **Manage (2)**: `can_read = true`, `can_create = true`, `can_update = true`, all others `false`
- **Custom (3)**: Any other boolean combination

**No "Inherit from Role" needed** — the role IS the baseline. This matches the target mental model.

The preset detection function in `permissions.js` (also duplicated in PHP in `users/permissions.blade.php`) works identically for role permissions — only the value `-1` (Inherit from Role) is user-override specific.

**Backend supports this without changes.** `ModulePermissionService::setForRole()` and `ModuleRolePermission::updateOrCreate()` can accept the same boolean permission payload regardless of whether presets were used client-side.

---

## F. User-vs-Role Permission Consistency

### Terminology Comparison

| Concept | User Permission (current) | Role Permission (proposed) | Consistency |
|---------|--------------------------|---------------------------|-------------|
| **Baseline** | Role's inherited preset | Role's own preset | Role IS baseline for users; role defines its own baseline |
| **No Access** | Dropdown option `0` | Dropdown option `0` | Identical (all false) |
| **View Only** | Dropdown option `1` | Dropdown option `1` | Identical |
| **Manage** | Dropdown option `2` | Dropdown option `2` | Identical |
| **Custom** | Dropdown option `3` + inline editor | Dropdown option `3` + inline editor | Identical |
| **Inherit from Role** | Dropdown option `-1` | N/A | Role doesn't inherit from itself |

### Column Parity

Both `ModuleRolePermission` and `UserModulePermission` share the same 8 permission columns:
`can_create`, `can_read`, `can_update`, `can_delete`, `can_approve`, `can_export`, `can_reveal`, `can_import`

The same preset detection logic works for both. The same inline editor component can be reused. The same sensitive module/permission configuration applies.

### Key Difference

`UserModulePermission` columns are **nullable** (null = no override, inherit from role). `ModuleRolePermission` columns are **non-nullable with default `false`** (every row defines all permissions). This means:

- For role permissions, every save is a full write — all 8 columns are always set
- For user overrides, partial writes are valid (unset columns = null = inherit)

**The role permission form must always send all 8 boolean values.**

---

## G. Recommended Role Simple Mode

### Design

A page at `roles/{id}/permissions` (or using the existing `module-permissions` page with a role filter) that mirrors the user permission Simple Mode:

```
Role: IT Support

Baseline Access — 6 of 25 modules accessible

Search: [_______________]  [Show only accessible modules]

  Module          Access Level           Status
  ─────────────────────────────────────────────────
  Domains         [View Only ▾]          Configured
  Hosting         [View Only ▾]          Configured
  VPS             [No Access ▾]          Configured
  VoIP            [View Only ▾]          Configured
  Service Prov.   [View Only ▾]          Configured
  Tasks           [No Access ▾]          Configured
  Notes           [No Access ▾]          Configured
  ...
```

### Dropdown Options (4, not 5)

- **No Access** — no permissions
- **View Only** — can_read only
- **Manage** — read/create/update (no sensitive)
- **Custom…** — opens inline editor for full control

### Features
- Role name prominently displayed at top
- "Baseline Access" summary (X of Y modules with access)
- Search input
- "Show only accessible modules" filter (analogous to "Show only overridden")
- Category grouping (Infrastructure, Productivity, Administration, Integration)
- Sensitive badges on modules (domains, hostings, vps, users, api-tokens)
- Compact table rows with dropdown select
- Same inline editor component reused for Custom
- "Browse All Modules" link when filtered

### What to REUSE from User Permission Simple Mode
- `sm-row` / `sm-select` HTML pattern
- `inline-editor` component (already shared)
- Preset detection function (identical logic)
- Sensitive module/permission config
- Search/filter UX pattern
- Stats bar concept (adapted: "X of Y modules with access")

---

## H. Recommended Role Advanced Mode

### Design

An Advanced tab on the role permission page that shows the full permission matrix:

```
Mode: [Simple ● ● ● ● Advanced]

Category: Infrastructure (9 modules)
  Module          No Access  View Only  Manage  Custom…
  ─────────────────────────────────────────────────────
  Domains            ○         ●         ○       ○
  Hosting            ○         ●         ○       ○
  VPS                ●         ○         ○       ○
  VoIP               ○         ●         ○       ○
  ...
```

Or a more traditional accordion layout (matching user Advanced Mode):

```
Category: Infrastructure [▼]
  Module          Access Level        Status
  ────────────────────────────────────────────
  Domains         [View Only ▾]       ✓ Configured
  Hosting         [View Only ▾]       ✓ Configured
  VPS             [No Access ▾]       ✓ Configured
  ...

Category: Productivity [▼]
  Module          Access Level        Status
  ────────────────────────────────────────────
  Tasks           [No Access ▾]       ✓ Configured
  Notes           [No Access ▾]       ✓ Configured
  ...
```

### Features
- Category accordions (expand/collapse)
- Bulk Apply per category (e.g., set all infrastructure to View Only)
- Diff panel (review changes before save)
- Reset all to defaults
- Sensitive permission confirmation on save
- Unsaved-changes guard

### What to REUSE
- `category-accordion` component
- `diff-panel` component
- `filter-chip` component
- `modal` component
- `unsaved-bar` component
- `stats-bar` component
- Bulk apply logic
- Reset all logic
- Save/submit logic
- Sensitive confirmation modal logic

---

## I. Role Show Page Recommendations

### Current State
Role Show shows: name, slug, privilege count, user count, timestamps, privilege attach/detach table, user list.

### Recommended Additions

```
Role: IT Support                     [Edit] [Delete]

  ┌──────────────────────────────────────────────────┐
  │ Role Details                                     │
  │  ID: 3     Slug: it-support                      │
  │  Users: 12  Created: 2026-01-15                  │
  │                                                  │
  │  Module Access: 6 of 25 modules                  │
  │  Sensitive Permissions: Reveal on 6 modules       │
  │  No Access: 19 modules                           │
  │                                                  │
  │  [🛡 Configure Permissions]  ← PRIMARY ACTION    │
  └──────────────────────────────────────────────────┘

  Privileges (0)  ─ (legacy section)
  ...
  Users with this role (12)
  ...
```

### Key Actions
1. **"[Configure Permissions]" button** — primary call-to-action, links to `module-permissions.index?role_id=3` or `roles/{id}/permissions`
2. **Module access summary** — quick overview of how many modules the role accesses
3. **Sensitive access indicator** — warns if sensitive permissions are granted
4. **Keep existing sections** — Privileges (legacy) and Users list should remain

**Do NOT add duplicate navigation.** The Role Show page should own the entry point to permission configuration. The sidebar "Permissions" can remain as an alternative global entry, but Role Show should be the canonical starting point.

---

## J. Permissions Sidebar Ownership Recommendation

### Recommendation: Keep Both, But Clarify

**Keep** the sidebar "Permissions" link as a global entry point at `module-permissions.index`.

**Add** a role-based filter/selector to the Module Permissions page so it can serve both as:
- A global overview (all roles, current behavior)
- A focused role editor (filtered to one role)

**OR** create a dedicated `roles/{id}/permissions` route/controller and redirect from `module-permissions.index?role_id=X`.

### Cleanest Model

**Option B (Recommended)**: Add role_id as an optional query parameter to the existing Module Permissions page.

- `GET /module-permissions` → shows all roles (current behavior)
- `GET /module-permissions?role_id=3` → filtered to one role, shows it in Simple Mode by default with preset dropdowns

**Role Show page** always links to `module-permissions.index?role_id={id}`, making Roles the canonical workflow. The sidebar still works for global overview.

**Rationale**: No new routes needed (`ModulePermissionController::index` already exists). No new controller needed. The page adapts based on query parameters. The same `ModulePermissionService::setForRole()` endpoint handles saves.

### What to Avoid
- A separate permission page that competes with module-permissions
- Removing the sidebar link (confuses existing admins)
- Duplicating the permission save endpoint
- Mixing role permissions into the User Permissions page

---

## K. Security / Data-Integrity Findings

### 1. Role permission changes affect all users assigned to that role
**Confirmed.** `ModulePermissionService::setForRole()` writes to `ModuleRolePermission`. The `HasModulePermissions` trait reads these on every access. Cache invalidation (`perms_generation` increment) ensures changes propagate within 60 seconds.

### 2. User-level overrides still take priority
**Confirmed.** `HasModulePermissions::canOnModule()` checks `UserModulePermission` first. If a non-null override exists, it wins over role permissions. `getAllModulePermissionsFromDb()` layers overrides on top of merged role permissions.

### 3. Super-admin protections
- `UserPermissionService::preventSuperAdminAssignment()` checks for super-admin role assignment (aborts 403)
- Cannot delete last super-admin
- Cannot self-demote super-admin
- However, `ModulePermissionController::update()` does NOT prevent modifying super-admin permissions programmatically (super-admin gets all true via seeder; no enforcement)

### 4. Sensitive permission handling
- `config('permissions.php')` defines sensitive modules and permissions
- Sensitive modules: domains, hostings, vps, users, api-tokens
- Sensitive permissions: can_delete, can_reveal, can_import
- **But these are NOT enforced server-side** — they are UI-only concepts (badges, confirmation modal)
- Any role can be given any permission through the API
- The UI confirmation modal is the only gate

### 5. Cache invalidation
- `Cache::increment('perms_generation')` is called by:
  - `ModulePermissionService::setForRole()`
  - `ModulePermissionService::removeForRole()`
  - `RoleTemplateService::apply()`
  - `UserPermissionService::saveUserModulePermissions()`
- Cache TTL: 60 seconds
- `canOnModule()` bypasses cache (queries DB live)

### 6. Audit logging
- `ModulePermissionController::update()` logs activity with module name, role name, and list of enabled permission keys
- `ModulePermissionController::destroy()` logs removal activities
- `ModuleRolePermission` model uses `LogsActivity` trait (logs fillable attributes)
- No dedicated role-permission-change index/table for bulk auditing

### 7. Bulk change risk
- Role Templates can apply to entire module sets at once
- The `apply()` method requires `confirmed` flag
- Dangerous templates (`is_dangerous`) require additional `confirm_dangerous` checkbox
- No per-permission confirmation; confirmation is all-or-nothing per template
- **Recommendation**: For role Simple/Advanced Mode, the existing diff panel + sensitive confirmation modal provide sufficient guardrails

---

## L. Test Coverage Gaps

### Covered Areas (Green)

| Area | Files |
|------|-------|
| Role CRUD | `RoleTest.php` |
| Role templates (create, show, diff, apply) | `RoleTemplateTest.php` (34 tests) |
| User override priority (true/false/null) | `UserModulePermissionTest.php`, `RbacPhase2C[1-6]Test.php` |
| Presets (View, Manage, Custom) for users | `BetterCreateUserTest.php` |
| User override persistence and deletion | `BetterCreateUserTest.php`, `UserModulePermissionTest.php` |
| Sensitive permission (can_reveal, can_delete) | Multiple Phase2 tests, VaultTest |
| API module permission CRUD | `ModulePermissionTest.php` |
| Effective access resolution | `HasModulePermissionsTraitTest.php` |
| Clone preserves overrides | `UserCloneTest.php` |
| Template preserves user overrides | `RoleTemplateTest.php` |

### Gap 1: No role permission preset tests
There are NO tests that verify role-level permission presets (No Access, View Only, Manage, Custom) save correctly via the web controller. The only preset-like tests are at the template and user-override level.

### Gap 2: No role permission update via web controller test
`ModulePermissionTest.php` only tests the **API controller** (`Api\ModulePermissionController`). There are no tests for:
- `Web\ModulePermissionController::update()` (form POST)
- `Web\ModulePermissionController::destroy()` (form DELETE)
- The modal save/remove flow on the module-permissions page

### Gap 3: No role permission page rendering tests
No tests verify that the module-permissions index page renders correctly with expected modules, roles, permission indicators. No assertSee tests for this page at all.

### Gap 4: No "effective access for role" query
There is no method/service that answers "what does this role have access to?" The closest is:
- `RoleTemplateService::computeDiff()` — compares role against a template
- `UserPermissionService::buildRoleSummaries()` — returns high-level summaries, not per-module

No single method returns role_id → list of modules with computed preset labels.

### Gap 5: No bulk permission change tests
No tests verify that bulk-applying permissions across multiple modules works correctly. The template tests test bulk-apply, but the web UI's bulk apply is untested.

### Gap 6: No concurrent role permission update tests
`UserModulePermissionTest` has concurrent update tests for user overrides. No equivalent exists for role permissions.

### Gap 7: No test for sensitive permission enforcement
No test verifies that the UI confirmation modal is required for sensitive permission changes. The server-side has no enforcement, so this gap is expected but worth noting.

---

## M. Recommended Implementation Batches

### Batch 1: Role Show → Permissions Entry Point

**Scope**: Add module access summary to Role Show page + "Configure Permissions" link.

**Files likely affected**:
- `app/Http/Controllers/Web/RoleController.php` — pass module summary data to `show()`
- `app/Services/RoleService.php` — add method to compute module access summary for a role
- `resources/views/roles/show.blade.php` — add summary cards and button

**Backend changes required**: Yes — new service method, controller data.

**Security risk**: Low — existing authorization (super-admin only) unchanged.

**Estimated complexity**: Low (2-3 methods, template addition).

**Priority**: High — this is the prerequisite for all role permission UX improvements.

---

### Batch 2: Role permission query filter on Module Permissions page

**Scope**: Add `?role_id=` query filter to existing `module-permissions.index` page. When present, show only one role's permissions with preset dropdowns. When absent, keep current all-roles matrix.

**Files likely affected**:
- `resources/views/module-permissions/index.blade.php` — conditional rendering based on `$roleId`
- `app/Http/Controllers/Web/ModulePermissionController.php` — pass `$roleId` and filtered data to view
- (Optionally) `resources/js/permissions.js` — reuse preset detection if Alpine is added

**Backend changes required**: Minor — controller passes optional `$roleId` to view.

**Security risk**: Low — existing authorization.

**Estimated complexity**: Medium — templating logic for two modes (all-roles vs single-role).

**Priority**: High — enables focused role permission editing without new routes.

---

### Batch 3: Role Simple Mode (default)

**Scope**: When `module-permissions.index?role_id=X` is loaded, show a Simple Mode interface with:
- Role name header
- Module list with 4-option preset dropdowns (No Access, View Only, Manage, Custom)
- Search input
- "Show only accessible" filter
- Category grouping
- Sensitive badges
- Same inline editor for Custom
- Save with diff confirmation

**Files likely affected**:
- `resources/views/module-permissions/index.blade.php` — Simple Mode section (reuse patterns from `users/permissions.blade.php`)
- `resources/css/permissions.css` — already has Simple Mode styles (may need role-specific variants)
- `resources/js/permissions.js` — may need a separate Alpine component or shared logic
- `app/Http/Controllers/Web/ModulePermissionController.php` — may need to format data for Alpine init

**Backend changes required**: Minimal — controller reformats data (modules + rolePermissions) into Alpine-friendly JSON.

**Security risk**: Low.

**Estimated complexity**: Medium-high — significant template work, but largely copying proven patterns from user permission Simple Mode.

**Priority**: High — this is the core UX improvement.

---

### Batch 4: Role Advanced Mode

**Scope**: Add Advanced tab to `module-permissions.index?role_id=X` with:
- Category accordions
- Preset dropdowns per module
- Stats (accessible count by category)
- Bulk apply per category
- Full inline editor
- Diff panel
- Unsaved-changes guard
- Sensitive confirmation modal
- Reset all to defaults

**Files likely affected**:
- `resources/views/module-permissions/index.blade.php` — Advanced Mode section
- `resources/css/permissions.css` — reuse existing styles
- `resources/js/permissions.js` — new Alpine component or extended editPerms
- Component views under `components/permissions/` — reuse existing components (diff-panel, modal, unsaved-bar, etc.)

**Backend changes required**: Minimal — same data as Simple Mode.

**Security risk**: Low.

**Estimated complexity**: Medium — reuses most components from user permission Advanced Mode.

**Priority**: Medium — important for power users, but Simple Mode covers 80% of use cases.

---

### Batch 5: Role permission preset tests

**Scope**: Add tests for:
- Role permission preset save (No Access, View Only, Manage, Custom)
- Role permission page rendering with role_id filter
- Role permission bulk apply
- Role permission concurrent update
- Role permission removal (reset to defaults)

**Files likely affected**:
- `tests/Feature/ModulePermissionTest.php` — add web controller tests (currently only API tests)
- `tests/Feature/RoleTest.php` — add permission tests

**Backend changes required**: None (testing only).

**Security risk**: None.

**Estimated complexity**: Low (test file additions).

**Priority**: High — critical gap in test coverage.

---

### Batch 6: Permission Inspector for Roles

**Scope**: Add a read-only permission inspector for roles (analogous to the "Enterprise Permission Inspector" on User Show). Show what modules the role accesses, with which permissions, and which are sensitive.

**Files likely affected**:
- `resources/views/roles/show.blade.php` — add accordion-style permission display
- `app/Http/Controllers/Web/RoleController.php` — pass permission data
- `app/Services/RoleService.php` — add permission query method

**Backend changes required**: Yes — new query method.

**Security risk**: Low.

**Estimated complexity**: Low-medium.

**Priority**: Medium — nice-to-have alongside Batch 1.

---

### Implementation Order Recommendation

```
Batch 1 (Prerequisite)
  ↓
Batch 2 + Batch 3 (Core UX — can be done together)
  ↓
Batch 5 (Testing — can run in parallel with Batch 4)
  ↓
Batch 4 (Advanced Mode)
  ↓
Batch 6 (Inspector — optional polish)
```

---

## Summary

The role permission workflow has a solid backend (`ModulePermissionService`, `ModuleRolePermission`, proper cache invalidation, audit logging) but a UX that lags behind the new User Permission Simple Mode. The module-permissions page uses vanilla JS, CRUD letter codes, no presets, and no role filter. The Role Show page has no link to permissions at all.

**Fix**: Add `?role_id=` filtering to the existing module-permissions page, reuse the User Permission Simple Mode UX patterns (preset dropdowns, search, categories, inline editor), and add a "Configure Permissions" entry point from Role Show.

**No backend Schema changes required.** No new tables. No new columns. No new controllers. The existing `ModulePermissionService::setForRole()` and `ModuleRolePermission::updateOrCreate()` handle everything.

**No security regressions** — super-admin gates, cache invalidation, audit logging, and user-override priority are all preserved.

---

ROLE PERMISSION WORKFLOW AUDIT COMPLETE — STOPPING BEFORE IMPLEMENTATION
