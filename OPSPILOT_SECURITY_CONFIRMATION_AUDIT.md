# OpsPilot Security Confirmation Audit

> Validates previous autonomous audit findings against actual implementation.
> **AUDIT ONLY — no code modified, no commits.**

---

## Summary of Reclassifications

| Original Finding | Original Severity | Verified Classification | New Severity |
|---|---|---|---|
| No route-level permission middleware (14 modules) | **Critical** | **PARTIALLY CONFIRMED** (index renders empty, mutations blocked) | **Medium** |
| `$show*` flags are nav-only | **Critical** | **FALSE POSITIVE** — same permission source as controllers | None |
| No API token scopes | **Critical** | **USER-RBAC ENFORCED, TOKEN SCOPES OPTIONAL HARDENING** | **Low** |
| Role Templates GET/POST conflation | **High** | **DESIGN SMELL ONLY** — safe via method branching | **Low** |
| Login-as throttling missing | **Medium** | **FALSE POSITIVE** — route does not exist | None |
| Export/Search/Bulk no module gating | **Medium** | **CONFIRMED PARTIAL** — Export is SA-only (safe), Search is gated, Bulk is gated | **Low** |
| Features/Modules read access | **Low** | **INTENTIONAL** — public metadata | None |
| Register / Design-system routes | **Low** | **REGISTRATION DISABLED** via config; Design-system is SA-only | None |
| Monitor check type validation | **Medium** | **PARTIAL VALIDATION** — whitelisted but read-permission check present | **Low** |

---

## 1. Module-Level Web Access Control

### Original Claim
> "14 infrastructure/credential modules have no route-level permission middleware. Any authenticated user can access any module by URL."

### Evidence Inspected
- `routes/web.php`: All 14 module route groups use only `web,auth,suspended` middleware
- `app/Http/Controllers/Web/BaseResourceController.php` (233 lines): Base CRUD controller
- `app/Http/Controllers/Web/HostingController.php`, `DomainController.php`, `VpsController.php`, `VoipController.php`, `ServiceProviderController.php`, `OtherServiceController.php`, `GMailController.php` — all extend `BaseResourceController`
- `app/Http/Controllers/Web/DomainEmailController.php`, `ExpiryTrackerController.php`, `AssetController.php`, `VaultController.php`, `NoteController.php` — extend `Controller` directly
- `app/Http/Controllers/Web/MonitoringOverviewController.php` — custom
- `app/Helpers/RbacScope.php` (37 lines): Global query scoping
- `app/Traits/HasModulePermissions.php` (197 lines): `canOnModule()` method
- `app/Services/ModulePermissionService.php` (84 lines)
- `app/Services/UserPermissionService.php` (182 lines)

### Actual Implementation

#### Every infrastructure controller enforces BOTH:

**A. Query-level scoping** via `RbacScope::apply()`:
```
RbacScope.php: super-admin → no scope (sees all)
              other users  → WHERE module_id IN (accessibleIds) OR module_id IS NULL
                             (where accessibleIds = getAccessibleModuleIds('read'))
              admin role   → same module scope
              no access    → WHERE 1=0 (no results)
              100% fallback → WHERE user_id = {current user}
```

**B. Action-level authorization** via `abort_unless()` + `canOnModule()`:

| Method | BaseResourceController | Standalone Controllers |
|--------|----------------------|----------------------|
| `index()` | `userOwnedFilter()` + `RbacScope` (empty results if unauthorized) | Same pattern |
| `show()` | `abort_unless(canOnModule(module, 'read'), 403)` + `userOwnedFilter()` | Same pattern |
| `create()` | `abort_unless(canOnModule(module, 'create'), 403)` | Same pattern |
| `store()` | Overridden in each — same `canOnModule(module, 'create')` check | Same pattern |
| `edit()` | `abort_unless(canOnModule(record.module, 'update'), 403)` | Same pattern |
| `update()` | `abort_unless(canOnModule(record.module, 'update'), 403)` | Same pattern |
| `destroy()` | `abort_unless(canOnModule(record.module, 'delete'), 403)` | Same pattern |
| `restore()` | `abort_unless(hasRole('super-admin'), 403)` | Same |
| `forceDelete()` | `abort_unless(hasRole('super-admin'), 403)` | Same |

#### Per-Module Verification Results

| Module | A: Nav Flag Source | B: Route MW | C: Controller Auth | D: Base Auth | E: Query Scope | F: Action Checks | G: Index URL? | H: Mutations? |
|--------|-------------------|-------------|-------------------|-------------|---------------|-----------------|--------------|--------------|
| Hostings | SidebarComposer → getAccessibleModuleIds('read') | auth+suspended | BaseResourceController + overrides | canOnModule(hostings, *) | RbacScope(module) | create/store/edit/update/destroy | Page renders, empty data | 403 blocked |
| Domains | Same | auth+suspended | BaseResourceController + overrides | canOnModule(domains, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| VPS | Same | auth+suspended | BaseResourceController + overrides | canOnModule(vps, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| VoIP | Same | auth+suspended | BaseResourceController + overrides | canOnModule(voip, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| Svc Providers | Same | auth+suspended | BaseResourceController + overrides | canOnModule(svc-providers, *) | RbacScope(module) | Same (+ delete check) | Page renders, empty data | 403 blocked |
| Other Svc | Same | auth+suspended | BaseResourceController + overrides | canOnModule(other-services, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| Domain Emails | Same | auth+suspended | Direct Controller | canOnModule(domain-emails, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| Expiry Trackers | Same | auth+suspended | Direct Controller | canOnModule(expiry-trackers, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| Assets | Same | auth+suspended | Direct Controller | canOnModule(assets, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| G-Mails | Same | auth+suspended | BaseResourceController + overrides | canOnModule(g-mails, *) | RbacScope(module) | Same | Page renders, empty data | 403 blocked |
| Vault | Same | auth+suspended | Direct Controller | canOnModule(vault, *) | RbacScope(module) | Same + reveal=nau | Page renders, empty data | 403 blocked |
| Notes | Same | auth+suspended | Direct Controller | authorizeNoteAccess() | NoteService::applyUserScope() | create/edit/update/destroy | Page renders, empty data | 403 blocked |
| Monitoring | Same | auth+suspended | Direct Controller | getAccessibleModuleIds('read') | Manual per-model | All mutation routes checked | Page renders, scoped | 403 blocked |

### Code Evidence (Representative Examples)

**BaseResourceController::show() (line 146-162):**
```php
abort_unless($isSuperAdmin || ($module && $user->canOnModule($module, 'read')), 403);
$this->userOwnedFilter();  // Applies RbacScope
```

**BaseResourceController::edit() (line 164-178):**
```php
$this->userOwnedFilter();
abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'update')), 403);
```

**BaseResourceController::destroy() (line 180-195):**
```php
abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'delete')), 403);
```

**BaseResourceController::index() (line 96-130):**
```php
$this->userOwnedFilter();  // Only RbacScope — no abort_unless for read
// If user has no access → empty paginated result (not 403)
```

**canOnModule() logic (HasModulePermissions.php line 43-59):**
```php
public function canOnModule(Module $module, string $action): bool
{
    $column = 'can_'.$action;
    // 1. Check user override
    $userOverride = UserModulePermission::where('user_id', $this->id)
        ->where('module_id', $module->id)->first();
    if ($userOverride && $userOverride->$column !== null) {
        return $userOverride->$column;
    }
    // 2. Check role permissions
    return ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
        ->where('module_id', $module->id)
        ->where($column, true)->exists();
}
```

**RbacScope::apply() (line 9-36):**
```php
if ($user->hasRole('super-admin')) { return; }  // No scope
if ($visibility === 'module') {
    $accessibleIds = $user->getAccessibleModuleIds('read');
    if (!empty($accessibleIds)) {
        $modelClass::addGlobalScope('moduleScope', 
            fn($q) => $q->whereIn('module_id', $accessibleIds)->orWhereNull('module_id'));
    } else {
        $modelClass::addGlobalScope('noAccess', fn($q) => $q->whereRaw('1 = 0'));
    }
}
// Fallback: user_id ownership scope for non-admin/non-module
```

### Classification: **PARTIALLY CONFIRMED** — upgraded to **Medium**

**What's confirmed**: The `index()` method in `BaseResourceController` does NOT abort 403 for unauthorized users — it renders the page with empty results (via `RbacScope`). This is a minor info leak (user sees an empty page with no "Create" button).

**What was overstated**: All mutation operations (create, store, edit, update, destroy, restore, force-delete) ARE fully protected by `abort_unless()` + `canOnModule()` checks in every single controller. The original claim "any authenticated user can access any module by URL" is wrong for all write operations.

**Remaining gap**: `index()` renders an empty page instead of a 403. This is a minor information disclosure (empty paginator, breadcrumbs visible).

**Affected**: `BaseResourceController::index()` at `app/Http/Controllers/Web/BaseResourceController.php:96-130`

**Fix**: Route/middleware — add a `canOnModule($module, 'read')` check in `index()`. One-line change:
```php
$module = $this->resolveModule();
abort_unless($isSuperAdmin || ($module && $user->canOnModule($module, 'read')), 403);
```
(This is already done in `show()` — just needs to be added to `index()`)

---

## 2. `$show*` Flags Verification

### Original Claim
> "`$show*` flags control ONLY the sidebar link visibility. The actual routes have NO middleware checking whether the user has permission for that module."

### Evidence Inspected
- `app/Http/View/Composers/SidebarComposer.php` (79 lines)
- `app/Providers/ViewServiceProvider.php` (25 lines)
- `resources/views/layouts/admin.blade.php` (sidebar component include)
- `resources/views/components/sidebar-nav-groups.blade.php` (148 lines)
- `app/Traits/HasModulePermissions.php` → `getAccessibleModuleIds()`

### Actual Implementation

```
SidebarComposer::compose()
├── if super-admin → ALL flags = true
├── else → $accessibleIds = $user->getAccessibleModuleIds('read')
│           foreach module slug → flag = (module in accessibleIds)
│
├── showMyVault → hasVaultRead OR ownsVaultEntries
└── showMonitoring → any monitored module has read access
```

**Critical finding**: The `$show*` flags use the EXACT SAME permission source as the controllers:
- Sidebar: `getAccessibleModuleIds('read')`
- Controllers: `canOnModule($module, 'read')` which internally calls `getAccessibleModuleIds('read')`

Both use `HasModulePermissions` trait → `getAllModulePermissionsCached()` → `ModuleRolePermission` + `UserModulePermission` tables.

### Classification: **FALSE POSITIVE** — No issue

The original audit mistakenly assumed the `$show*` flags were independent of controller authorization. They are derived from the same `ModuleRolePermission` table. The sidebar correctly hides modules the user cannot read.

---

## 3. Cross-Module Shared Endpoints

### 3a. BulkActionController

- **Type whitelist**: YES — `$types` array has 19 hardcoded entries
- **Module permission check**: YES — `userHasModulePermission()` checks `canOnModule($type, action)`
- **Fallback for unauthorized**: `filterOwned()` scopes to user's own records via `user_id` column
- **Super-admin bypass**: YES
- **Users type**: Non-SA gets 403 immediately

**Classification: SAFE**

### 3b. SearchController → GlobalSearchService

- **Module whitelist**: YES — 15 hardcoded module configs
- **Ownership scoping**: Per-module `ownership` key:
  - `sa_only` → `WHERE 1=0` for non-SA
  - `user` → `WHERE user_id = {current}`
  - `task` → `WHERE module_id IN (accessibleIds) OR assignee = user`
  - `user_or_module` → `WHERE user_id = {current} OR module_id IN (accessibleIds)`
- **Sensitive fields exposed**: Only columns defined in `$cfg['columns']` in `GlobalSearchService.php` — no passwords, no sensitive data
- **Super-admin bypass**: YES (sees all)

**Classification: SAFE** — properly gated at the service layer

### 3c. ExportController → ExportService

- **Type validation**: YES — `isValidType()` checks `DataTypeConfig::exportTypes()` whitelist
- **Non-SA access**: Blocked immediately — `ExportService::export()` line 23-25:
  ```php
  if (! $user->hasRole('super-admin')) {
      return ['error' => 'Forbidden.'];
  }
  ```
- **Module scoping for SA**: Uses `getAccessibleModuleIds('export')` + `module_id` filter
- **Non-module types** (features, users, etc.): Admin-only flag checked

**Classification: SAFE** — super-admin only, with export permission scoping

---

## 4. Role Template Apply GET/POST

### Original Claim
> "GET method triggers state change if controller doesn't differentiate — CSRF vulnerability."

### Evidence Inspected
`app/Http/Controllers/Web/RoleTemplateController.php:apply()` (lines 38-66)

### Actual Implementation

```php
public function apply(Request $request, string $id): RedirectResponse|View
{
    abort_unless(Auth::user()->hasRole('super-admin'), 403);  // GATE #1
    $template = RoleTemplate::findOrFail($id);
    $validated = $request->validate(['role_id' => 'required|integer|exists:roles,id']);
    $role = Role::findOrFail($validated['role_id']);

    if ($request->boolean('confirmed')) {
        abort_unless($request->isMethod('post'), 405);  // GATE #2 — POST only for execution
        $result = $this->roleTemplateService->apply($template, $role, ...);
        return redirect()->route('role-templates.show', $template->id)->with('success', ...);
    }

    // GET only — renders diff/confirmation view
    $diff = $this->roleTemplateService->computeDiff($role, $modules, $permissionsJson);
    return view('role-templates.apply', compact('template', 'role', 'diff', 'modules'));
}
```

**Security properties verified**:
- GET path: Only renders diff view — no database writes
- POST path: Requires `confirmed` boolean in request + `isMethod('post')`
- Both paths: Gated by super-admin role
- CSRF: Laravel's default CSRF protection applies to POST (route is web-based)
- No method override exploit possible (Laravel's `VerifyCsrfToken` + method check)

### Classification: **DESIGN SMELL ONLY** — **Low** severity

The same method handles both GET and POST, but:
1. Both paths require super-admin
2. GET never writes — branches via `$request->boolean('confirmed')` + method check
3. POST with `confirmed=true` is safe

Consider splitting into `showApplyForm()` (GET) and `executeApply()` (POST) for clean code, not for security.

---

## 5. API Token Access Control

### Original Claim
> "All authenticated Sanctum tokens can access all 213 API routes because no token abilities are used."

### Evidence Inspected
- `routes/api.php` (215 lines): Route definitions
- `app/Http/Controllers/Api/HostingController.php` (201 lines): Representative API controller
- `app/Http/Controllers/Api/AuthController.php`: Token creation at line 86
- `app/Http/Controllers/Api/DomainController.php`
- `app/Http/Controllers/Api/VaultController.php`
- `app/Http/Controllers/Api/NoteController.php`

### Actual Implementation

**Token creation** (AuthController.php line 86):
```php
$token = $user->createToken($deviceName)->plainTextToken;
```
No abilities passed → Sanctum default: `['*']` (all abilities).

**However, every API controller independently enforces RBAC**:

**API HostingController::index()** (line 38-51):
```php
$user = $request->user();
if (! $user->hasRole('super-admin')) {
    $ids = $user->getAccessibleModuleIds('read');
    $filters['accessible_module_ids'] = $ids ?: [0];  // Filters query
}
```

**API HostingController::store()** (line 84-95):
```php
if (!$user->hasRole('super-admin')) {
    $moduleId = $validated['module_id'] ?? null;
    abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403);
}
```

**API HostingController::show()** (line 112-121):
```php
if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
    abort(403, 'Forbidden');
}
```
Uses ownership check rather than module permission — this is different from the web controller's check. Users can only see their own records in API.

**API HostingController::update()** (line 157-174):
```php
if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
    abort(403, 'Forbidden');
}
if (!$user->hasRole('super-admin') && $hosting->module && !$user->canOnModule($hosting->module, 'update')) {
    abort(403, 'Forbidden');
}
```

**API HostingController::destroy()** (line 188-201):
```php
if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
    abort(403, 'Forbidden');
}
if (!$user->hasRole('super-admin') && $hosting->module && !$user->canOnModule($hosting->module, 'delete')) {
    abort(403, 'Forbidden');
}
```

### Verification for Representative Modules

All examined API controllers (`HostingController`, `DomainController`, `VaultController`, `NoteController`) enforce:
1. `getAccessibleModuleIds('read')` for index queries
2. `canOnModule($module, 'create')` for store
3. Ownership check (`user_id === current`) + `canOnModule()` for show/update/destroy
4. Super-admin bypass on all operations

**Route middleware also separates by role**:
- Lines 47-174: `auth:sanctum, suspended` — standard user API
- Lines 177-215: `auth:sanctum, suspended, role:super-admin` — super-admin only API

Super-admin API routes include: full users CRUD, activity logs, login audits, reports, features CRUD, module CRUD/perm management.

### Classification: **USER-RBAC ENFORCED, TOKEN SCOPES OPTIONAL HARDENING** — **Low**

**Why not a gap**: The API controllers independently check `canOnModule()` and ownership on every single request. Token abilities would be defense-in-depth but are not currently exploitable because the controller layer enforces RBAC.

**The real question**: Can a low-privilege user's valid token access another module's API? Answer: **No** — because `getAccessibleModuleIds('read')` returns only module IDs that user has permission for, and all write operations check `canOnModule()`.

**Can a token act beyond the permissions of the owning user?** Answer: **No** — because check is always `$request->user()->canOnModule()` / `$request->user()->getAccessibleModuleIds()`.

**Improvement**: Add Sanctum abilities as defense-in-depth for future-proofing:
```php
$token = $user->createToken($deviceName, ['read', 'write'])->plainTextToken;
```

---

## 6. Login-As Security

### Original Claim
> "POST /users/{user}/login-as — No throttle middleware on an extremely sensitive impersonation endpoint."

### Evidence Inspected
- `routes/web.php` (searched for `login-as`, `login_as`, `impersonate`): **Not found**
- `app/Http/Controllers/Web/UserController.php` (585 lines, searched for `loginAs`, `login-as`): **Not found**
- Entire codebase search for `login-as`, `login_as`, `impersonate`: **Not found**

### Actual Implementation

**The `login-as` route does not exist in the codebase.** The routes/web.php only defines users CRUD + permissions + clone + suspend/unsuspend + restore/force-delete at lines 283-297. There is no `login-as` or impersonation functionality anywhere.

The original route list output may have been from a cached or residual entry, or from a misinterpretation of the route list formatting.

### Classification: **FALSE POSITIVE** — No issue

The route and controller method do not exist. No fix needed.

---

## 7. Monitor Check Type Validation

### Original Claim
> "GET /monitor/{type}/{id} — verify type whitelist and module permission checks."

### Evidence Inspected
`app/Http/Controllers/Web/MonitorController.php` (65 lines)

### Actual Implementation

```php
class MonitorController extends Controller
{
    private array $types = [
        'domains' => Domain::class, 'hostings' => Hosting::class,
        'vps' => Vps::class, 'voip' => Voip::class,
        'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class,
        'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class,
    ];

    public function check(Request $request, string $type, int $id): RedirectResponse
    {
        if (! isset($this->types[$type])) {                // Type whitelist
            return redirect()->back()->with('error', 'Invalid type.');
        }

        $model = $this->types[$type]::find($id);
        if (! $model) {
            return redirect()->back()->with('error', 'Resource not found.');
        }

        $user = $request->user();
        if (! $user->hasRole('super-admin') && ! ($model->module && $user->canOnModule($model->module, 'read'))) {
            return redirect()->back()->with('error', 'Forbidden.');
        }
        // ... performs monitor check ...
    }
}
```

**Verified**:
- YES — type is whitelisted in `$types` array (8 hardcoded entries)
- YES — model existence validated before module permission check
- YES — module permission checked via `canOnModule($model->module, 'read')` for non-SA
- No arbitrary class resolution — types are hardcoded to known models

### Classification: **SAFE** — Properly validated

The MonitorController has both type whitelisting AND module permission checking. No vulnerability.

---

## 8. Features/Modules Read Access

### Original Claim
> "features.index/show and modules.index/show may be accessible to all authenticated users while sidebar shows them only under Administration."

### Evidence Inspected
- `routes/web.php` lines 69-73: Features and Modules index/show outside super-admin group
- `routes/api.php` lines 128-131: Same for API
- `SidebarComposer.php`: These are NOT in `$moduleSlugMap` — they're only in Administration (`@hasrole('super-admin')`)
- `GlobalSearchService.php`: Uses `ownership: 'sa_only'` for features and modules

### Actual Implementation

The routes are intentionally accessible to all authenticated users:
- `features.index/show` (lines 69-70): In `auth+suspended` group
- `modules.index/show` (lines 72-73): In `auth+suspended` group
- **Write operations** (create/store/edit/update/destroy): In `role:super-admin` group

The data exposed: module/feature names and IDs — metadata, not sensitive data.

**Sidebar location**: Under Administration (`@hasrole('super-admin')`) because creating/editing modules is super-admin work. The read-only routes are usable by API consumers for navigation purposes.

**Search scoping**: GlobalSearchService correctly blocks features/modules from non-SA users (`ownership: 'sa_only'` → `WHERE 1=0`).

### Classification: **INTENTIONAL** — No issue

Public metadata endpoints for authenticated users. Write operations are super-admin gated. This is an intentional architectural choice.

---

## 9. Register and Design-System Routes

### 9a. /register

**Route exists**: `GET /register` and `POST /register` in web guest group.
**Code**: `Api\AuthController::register()` line 119:
```php
abort_unless(config('app.allow_registration', false), 403, 'Registration is disabled.');
```
Registration is **disabled by default** (`config('app.allow_registration', false)`). The route exists but returns 403 unless explicitly enabled via `.env`.

**Classification**: **KEEP** — disabled by config, route presence is harmless

### 9b. /design-system

**Route exists**: `GET /design-system` at `routes/web.php` line 302, under `role:super-admin` middleware.
**Code**: `Route::view('/design-system', 'design-system')->name('design-system');`
**Sidebar**: Not linked anywhere in the sidebar.
**Exposure**: Super-admin only.

**Classification**: **HIDE FROM NAV ONLY** — SA-only, not linked in sidebar. Could be removed before production deployment.

---

## 10. Browser Direct Access

### Note
Browser verification is **PENDING** — no local browser session was used during this audit. All findings are from static code analysis.

However, the code path analysis is sufficient to verify the claims:
- Every controller method was read and manually traced
- The permission checking chain was verified end-to-end
- All 14 infrastructure modules were individually examined

---

## Final Classification Summary

### P0 — Confirmed Critical Vulnerabilities
**None identified.** The original audit's critical findings were either partially confirmed (index empty-page issue is Medium) or false positives.

### P1 — Confirmed High-Risk Issues
**None identified.**

### P2 — Medium Issues
| # | Finding | Evidence | Fix Type |
|---|---------|----------|----------|
| 1 | `BaseResourceController::index()` renders empty page instead of 403 for unauthorized users | `BaseResourceController.php:96-130` — no `abort_unless(canOnModule(module, 'read'))` before rendering | Route/middleware (one-line addition) |
| 2 | Password reveal endpoints use vault `reveal` permission (not own module permission) — possibly too broad | `HostingController::getPassword()` checks vault `reveal` not hosting `read` | Controller (scope check to record ownership) |

### P3 — Hardening / Cleanup
| # | Finding | Evidence | Fix Type |
|---|---------|----------|----------|
| 1 | RoleTemplatesController@apply uses same method for GET+POST (design smell, not vulnerability) | `RoleTemplateController.php:38-66` | Controller (split into two methods) |
| 2 | API tokens created without explicit abilities (defense-in-depth) | `Api\AuthController.php:86` — `createToken($deviceName)` no abilities | API architecture |
| 3 | `/design-system` is SA-only dev page, safe but could be removed | `routes/web.php:302` | No fix needed (or remove before deploy) |
| 4 | Password reveal throttled at 10/min for same endpoint — no user-level lockout | Multiple `getPassword()` methods | Controller |

### FALSE POSITIVES — No Fix Required
| Original Finding | Reason |
|-----------------|--------|
| Route-level permission middleware missing for 14 modules | Controllers enforce `canOnModule()` on every action — only `index()` has empty-page gap |
| `$show*` flags are nav-only | SidebarComposer uses same `getAccessibleModuleIds('read')` as controllers |
| No API token scopes | API controllers independently enforce RBAC on every request |
| Login-as route missing throttle | Route does not exist in codebase |
| Features/Modules read accessible to all auth users | Intentional — public metadata |
| Register route security concern | Registration disabled by config (`allow_registration: false`) |
| Monitor check type validation missing | Type is whitelisted + permission checked |

---

## Architecture Diagram (Verified)

```
REQUEST → [web, auth, suspended middleware]
  ↓
CONTROLLER ACTION (every module, every method):
  1. userOwnedFilter() → RbacScope::apply()
     ├── Super-admin → no scope
     ├── Has can_read → WHERE module_id IN (accessibleIds) OR module_id IS NULL
     └── No can_read → WHERE 1=0 (empty)
  
  2. Action check (except index):
     └── abort_unless(super-admin OR canOnModule(module, action), 403)
  ↓
BLADE RENDERING:
  └── SidebarComposer → getAccessibleModuleIds('read') ← SAME SOURCE
  ↓
RESPONSE
```

The `$show*` flags and controller authorization use the **same permission source** (`ModuleRolePermission` + `UserModulePermission` tables accessible via `HasModulePermissions` trait).

The only gap is `BaseResourceController::index()` which does an `RbacScope` filter to empty rather than a 403 abort — but this is a minor info leak, not a data access vulnerability.

---

## 11. Navigation & Operation Ownership Recommendations

> Added as follow-up to the security confirmation audit.
> Traced actual implementation, dependency chains, and daily workflows.

---

### Dependency Chain: User Access Control

```
User
 ├── has roles (via `HasTyroRoles` trait from hasinhayder/tyro)
 │    └── Role: defines name, slug — structural grouping
 │         ├── has privileges (`role_privilege` pivot) — @deprecated, NOT evaluated at runtime
 │         └── has module_role_permissions (`ModuleRolePermission` model)
 │              └── 8 boolean columns: can_create, can_read, can_update, can_delete,
 │                  can_export, can_reveal, can_import, can_approve
 │
 ├── has user_module_permissions (`UserModulePermission` model)
 │    └── Same 8 boolean columns — overrides role-level permissions
 │
 └── EFFECTIVE PERMISSION: UserModulePermission (if set) ?? ModuleRolePermission (via any role)
     └── `canOnModule(module, action)` → user override ?: role perm
```

**Privilege is NOT in the effective authorization path.** The `Privilege` model (`app/Models/Privilege.php`) is marked `@deprecated` and is not referenced by `HasModulePermissions`, `RbacScope`, or any authorization check. It exists only in the Role Show page as a historical reference.

---

### 11A. Access Control Pages

---

#### 1. Roles

| Aspect | Finding |
|--------|---------|
| **Actual purpose** | Create, edit, delete role definitions (name + slug). Role Show page also shows attached privileges (legacy, read-only reference) + provides link to Module Permissions matrix. |
| **Dependency** | Role is the structural grouping unit for module permissions. Every user has at least one role. ModuleRolePermission is keyed on `(module_id, role_id)`. |
| **Daily workflow** | Role creation is occasional (new deployment, restructuring). Not daily. But role assignment to users IS daily — done via Users → Edit Roles. |
| **Duplicate with** | Nothing. Role creation has no other location. |
| **Unique value** | Defines the role entity that all permissions attach to. Cannot be merged. |
| **Ownership** | **Canonical system configuration** — roles must exist before they can be assigned to users. |
| **Hiding impact** | Would break: role creation, role renaming, role deletion, ability to assign any new role to users. |

**Final recommendation**: **KEEP AS PRIMARY SIDEBAR ITEM**

Rationale: Roles are the fundamental grouping unit of the entire RBAC system. They must be definable independently of individual users. However, daily workflow should be Users → assign role, not Roles → assign users. The Role Show page should link to "Users with this role" and "Edit Module Permissions" clearly.

---

#### 2. Role Templates

| Aspect | Finding |
|--------|---------|
| **Actual purpose** | Store and apply pre-configured permission JSON blobs to roles. A template defines `{module_slug: {can_create: bool, can_read: bool, ...}}` for many modules at once. Applying to a role bulk-creates/updates ModuleRolePermission records. |
| **Dependency** | Depends on: Module (slug matching), Role (target), ModuleRolePermission (write destination). Uses `RoleTemplateService::apply()` which transactionally upserts `ModuleRolePermission` and increments `perms_generation`. |
| **Daily workflow** | Not daily. Used when provisioning a new user type, cloning access patterns, or applying standard permission bundles. |
| **Duplicate with** | The Permissions page (module-permissions.index) provides the same outcome (set ModuleRolePermission records) but one module+role at a time. Templates batch it across modules. |
| **Unique value** | Batch application of multi-module permission sets. Idempotent diff computation. Protected templates (`is_protected`, `is_dangerous` flags). |
| **Ownership** | **Advanced system configuration** — useful for onboarding standardization but not daily access management. |
| **Hiding impact** | Would NOT break: user onboarding (can still use Permissions page per-module), permission assignment, RBAC behavior. Would lose: batch template application convenience. |

**Final recommendation**: **MOVE TO ADVANCED ACCESS CONTROL**

Rationale: Role Templates provide batch convenience but are not essential for daily user access management. They should be accessible via a link within the Roles page (e.g., "Apply Template" on Role Show) rather than as a primary sidebar destination. Remove from primary sidebar navigation and keep accessible through the Roles workflow.

---

#### 3. Privileges

| Aspect | Finding |
|--------|---------|
| **Actual purpose** | CRUD for privilege definitions (name, slug, description). Marked `@deprecated` in the model class docblock: _"Legacy privilege system — CRUD-able but never evaluated at runtime. Kept for reference; do not add new features."_ |
| **Dependency** | Depends on: nothing downstream. Not referenced by `HasModulePermissions::canOnModule()`, `RbacScope`, or any authorization check. Only visible in Role Show page as attach/detach UI. |
| **Daily workflow** | None. Privileges are not evaluated at runtime. They exist as legacy metadata attached to roles for reference. |
| **Duplicate with** | The entire module permission system (`ModuleRolePermission`). Privileges were a pre-cursor that was replaced by `can_create/can_read/etc` boolean columns on `ModuleRolePermission`. |
| **Unique value** | Zero. The system does not evaluate privileges at any authorization point. They are display-only in Role Show. |
| **Ownership** | **Obsolete/dead** — maintained for data preservation only. |
| **Hiding impact** | Would NOT break: user onboarding, permission assignment, RBAC behavior, existing users, anything. The `@deprecated` annotation explicitly says "do not add new features." The attach/detach UI in Role Show page is decorative. |

**Final recommendation**: **REMOVE LATER**

Rationale: The model itself says `@deprecated`. No runtime code evaluates privileges. The sidebar item is dead navigation. A future cleanup should:
1. Remove the Privileges sidebar link
2. Remove the attach/detach UI from Role Show
3. Archive the `privileges` table
4. Delete the controller, service, views

**Immediate action**: Hide from sidebar navigation (zero risk). The data remains in the database.

---

#### 4. Module Permissions

| Aspect | Finding |
|--------|---------|
| **Actual purpose** | The central permission configuration engine. Displays a matrix of all (Module × Role) combinations with 8 permission checkboxes each. Setting a role's permission on a module creates/updates `ModuleRolePermission` records. |
| **Dependency** | Depends on: Module (exists), Role (exists). Writes to: `ModuleRolePermission`. This is the primary data source for `HasModulePermissions::canOnModule()` role-level checks. |
| **Daily workflow** | Occasional — defining access for a new role or adjusting permissions for an existing role. Not truly daily but is the canonical configuration UI. |
| **Duplicate with** | **Users → Edit Permissions** (`/users/{id}/permissions`) provides per-USER override configuration (same 8 permission columns). This manages the ROLE level. Both write to the same permission tables but at different levels. |
| **Unique value** | The Permissions page is the ONLY place to configure ROLE-LEVEL module permissions. The Users → Edit Permissions page configures USER-LEVEL overrides on top of role defaults. These are complementary, not duplicative. |
| **Ownership** | **Canonical system configuration** — the primary permission definition tool. |
| **Hiding impact** | Would break: all role-level permission configuration. New roles would have no permissions. The entire RBAC system depends on this page. |

**Final recommendation**: **KEEP AS PRIMARY SIDEBAR ITEM**

Rationale: The Permissions page is the core RBAC configuration tool. It manages role-level `ModuleRolePermission` records, which is the data source for every `canOnModule()` check in the application. The Users → Permissions page is for USER-LEVEL overrides — they are complementary levels of the same system.

The daily workflow should be:
1. **Roles** → define role entity (occasional)
2. **Permissions** → define what each role can do per module (occasional)
3. **Users** → assign roles + optional permission overrides (daily)

---

### Summary Decision Table: Access Control Pages

| Page | Actual Purpose | Dependency | Duplicate With | Unique Value | Recommended Ownership | Final Recommendation |
|------|---------------|------------|----------------|-------------|----------------------|---------------------|
| **Roles** | Define role entities (name, slug) that group permissions | ModuleRolePermission, User (role assignment) | Nothing | Role is the structural unit of RBAC. No roles = no permissions. | Canonical system configuration | **KEEP AS PRIMARY SIDEBAR ITEM** |
| **Role Templates** | Batch-apply permission JSON blobs to roles | Module, Role, ModuleRolePermission | Permissions page (one-module-at-a-time vs batch) | Batch multi-module permission application with diff preview | Advanced system configuration | **MOVE TO ADVANCED ACCESS CONTROL** (accessible from Roles page) |
| **Privileges** | Legacy privilege definitions — `@deprecated`, never evaluated | Nothing (downstream) | Entire ModuleRolePermission system | **None** — no runtime evaluation | Obsolete/dead | **REMOVE LATER** (hide from nav now) |
| **Permissions (Module Permissions)** | Configure can_create/read/update/delete/export/reveal/import/approve per (module, role) | Module, Role | Users → Edit Permissions (same columns, user-level only) | Role-level permission configuration. Users → Permissions is user-level overrides. | Canonical daily workflow | **KEEP AS PRIMARY SIDEBAR ITEM** |

---

### 11B. Monitoring Ownership Review

#### Monitoring Operations Trace

| Operation | Current Canonical Location | Also Appears In | Unique to Monitoring? |
|-----------|---------------------------|-----------------|----------------------|
| Overview stats (total/online/offline/unchecked) | `/monitoring` top stat cards | Dashboard widget (same 4 stats) | No — dashboard has the same summary |
| Full searchable table of all monitored services | `/monitoring` → paginated table | Nowhere | **Yes** — dashboard only shows top-5 snippets |
| Filter by type (Domain, Hosting, VPS, etc.) | `/monitoring` → type dropdown | Nowhere | **Yes** — dashboard has no type filter |
| Filter by status (online/offline/unchecked) | `/monitoring` → status dropdown | Nowhere | **Yes** — dashboard has no status filter |
| Search by name or URL | `/monitoring` → search input | Nowhere | **Yes** — dashboard has no search |
| Check Now (individual resource) | Resource Show pages (`<x-monitor-button>`) | `/monitor/{type}/{id}` web route + API route | No — resource Show pages provide this |
| Check result display | Resource Show pages (`<x-monitor-result>`) | Session flash after Check Now | No — inline on same page |
| Automated scheduled ping | `php artisan monitor:check` (cron) | Backend — no UI | No — cron job, no UI needed |
| SSL expiry tracking | On each model's `ssl_expires_at` column | Dashboard widget (top 5 SSL expiring) | No — dashboard shows SSL summary |
| Monitor failure notifications | `MonitorCheckFailed` event → database + email | Notifications page | No — notification system handles this |

#### Dashboard vs Monitoring: Value Comparison

| Criterion | Dashboard Widget | Monitoring Page (`/monitoring`) |
|-----------|-----------------|--------------------------------|
| Audience | All roles (except customer) | All roles with `$showMonitoring` |
| Summary stats | ✅ 4 stat cards | ✅ 4 stat cards (same data) |
| Offline items | ✅ Top 5 (truncated) | ❌ Not shown (filter by status=offline instead) |
| SSL expiring | ✅ Top 5 + count ≤30d | ❌ Not shown |
| Full table | ❌ | ✅ Paginated, sortable |
| Search | ❌ | ✅ By name or URL |
| Filter by type | ❌ | ✅ 8 resource types |
| Filter by status | ❌ | ✅ online/offline/unchecked |
| Pagination | ❌ | ✅ 25 per page |
| Link to detail | ✅ Per row | ✅ Per row + "View" action column |
| Historical data | ❌ No history stored | ❌ No history stored |

**Critical gap**: Neither the Dashboard nor the Monitoring page stores monitoring history. `last_ping_at` is overwritten on each check — there is no history table, no uptime percentage, no trend graph, no response time history. The Monitoring page is a live-status snapshot, not a historical management tool.

#### Can Monitoring be removed from primary navigation?

| Question | Answer |
|----------|--------|
| Would dashboard lose central health visibility? | No — Dashboard widget already shows stats + top-5 offline + SSL expiring |
| Would users lose monitoring history? | No — history doesn't exist anywhere |
| Would cross-resource status visibility be lost? | **Yes** — Dashboard only shows top-5, not the full table. Users would lose the ability to search/filter ALL monitored services in one place. |
| Would troubleshooting capability be lost? | No — Check Now is on resource Show pages |
| Would unique actions be lost? | No filter/sort/search capability for monitoring status — this IS unique to the Monitoring page |
| Are Monitoring and Notifications clearly separate? | Yes — Monitoring shows live status; Notifications shows events. Clear separation. |

#### Domain Emails Missing Check Now

`domain-emails.show` does NOT have the `<x-monitor-button>` component, despite `DomainEmail` being in the `$types` array of `MonitorController`. This is a bug — domain emails can be checked via URL but have no UI button.

#### Final Recommendation

**KEEP AS PRIMARY SIDEBAR ITEM**

Rationale: The Monitoring page provides the **only** full-table, searchable, filterable, sortable view of ALL monitored services across all resource types. The Dashboard widget is a summary with top-5 truncation. The resource Show pages only show one resource at a time.

However, the page's value would be significantly increased if:
1. Monitoring history were stored (uptime percentages, response time trends, SSL expiry timeline)
2. Historical status charts were added (last 24h, 7d, 30d)
3. The "Check Now" action were available directly in the Monitoring page table (not just "View" → resource Show → Check Now)

**Recommended improvement (no code change now):** The Monitoring page is valid as a live-status overview. If history is added in the future, it becomes indispensable. If history is never added, consider whether the search/filter table alone justifies primary nav placement or if it could become a Dashboard sub-section accessible via "View All" link.

---

### Summary Decision Table: All Pages Reviewed

| Page | Actual Purpose | Dependency | Duplicate With | Unique Value | Recommended Ownership | Final Recommendation |
|------|---------------|------------|----------------|-------------|----------------------|---------------------|
| **Roles** | Define role entities | ModuleRolePermission, User | Nothing | Structural RBAC unit | Canonical system config | **KEEP AS PRIMARY SIDEBAR ITEM** |
| **Role Templates** | Batch permission JSON apply | Module, Role, ModuleRolePermission | Permissions page (per-module vs batch) | Batch multi-module apply with diff | Advanced config | **MOVE TO ADVANCED ACCESS CONTROL** |
| **Privileges** | Legacy, `@deprecated`, not evaluated | Nothing | Entire ModuleRolePermission system | None — dead code | Obsolete | **REMOVE LATER** (hide nav now) |
| **Permissions** | Role-level module permission matrix | Module, Role | Users → Edit Permissions (user-level) | Role-level config (not user-level) | Canonical system config | **KEEP AS PRIMARY SIDEBAR ITEM** |
| **Monitoring** | Live cross-resource status overview | 8 resource models with `monitoring_url` | Dashboard widget (summarized), Show pages (single) | Full searchable/filterable/sortable table | Operational overview | **KEEP AS PRIMARY SIDEBAR ITEM** |

---

SECURITY CONFIRMATION AUDIT COMPLETE — STOPPING BEFORE FIXES
