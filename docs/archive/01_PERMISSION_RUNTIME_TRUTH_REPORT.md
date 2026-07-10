# 01 — PERMISSION RUNTIME TRUTH REPORT

## What actually happens when a user accesses a module?

---

## TRACE: User (non-SA) opens "Hosting" index page

### Step 1: Route Resolution
`routes/web.php` → `HostingController@index` (no middleware beyond `auth`, `suspended`)
- **Source:** `routes/web.php:131`

### Step 2: Data Visibility
`HostingController@index` → `$this->userOwnedFilter()` → `RbacScope::apply(Hosting::class, 'module')`

**What actually happens:**
```php
if ($user->hasRole('super-admin')) { return; }  // skip scope
// User is not super-admin → continue
$accessibleIds = $user->getAccessibleModuleIds('read');
// Adds: Hosting::addGlobalScope('moduleScope', WHERE module_id IN (accessibleIds))
```

**Check:** Does `getAccessibleModuleIds('read')` include role permissions AND user overrides? **Yes (lines 82-104 of HasModulePermissions.php).**

**Check:** Does the module scope also consider `user_id`? **No. Correctly ignores it.**

**Issue:** Records with `module_id IS NULL` are invisible. `WHERE module_id IN (1,2,3)` does NOT match NULL. VoIP and Domain Email forms don't send module_id → records get null → invisible.

### Step 3: Button-Level Authorization
```php
$module = Module::where('slug', 'hostings')->first();
$isSuperAdmin = $user->hasRole('super-admin');
$canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
$canExport = $isSuperAdmin || ($module && $user->canOnModule($module, 'export'));
```

**Correct.** Uses `canOnModule()` which checks overrides first, then role permissions.

### Step 4: View Rendering
Blade uses `@if($canCreate)` etc. to show/hide buttons. No `@can` directives used.

### Step 5: Sidebar Visibility (separate request)
`SidebarComposer` checks `getAccessibleModuleIds('read')` for each module slug.

**Check:** Does sidebar agree with controller? **Yes.** Both use the same `getAccessibleModuleIds('read')`.

---

## TRACE: User accesses a record edit action

`HostingController@edit`:
```php
$this->userOwnedFilter();  // RbacScope
$hosting = Hosting::findOrFail($id);  // Scope applies here
abort_unless($user->hasRole('super-admin') || ($hosting->module && $user->canOnModule($hosting->module, 'update')), 403);
```

**Check:** Does `findOrFail` respect the scope? **Yes** — if the record has a module_id not in the user's accessible set, `findOrFail` throws ModelNotFoundException.

**Issue:** If `$hosting->module` is null (no module relation), `canOnModule` receives `null` as first argument, which is NOT a `Module` instance. PHP type hint requires `Module`. This would throw a TypeError, not a 403. **This is a CRITICAL bug** if any global record has a null or invalid module_id.

Wait, let me check the code more carefully.
```php
abort_unless($user->hasRole('super-admin') || ($hosting->module && $user->canOnModule($hosting->module, 'update')), 403);
```
The short-circuit `$hosting->module &&` prevents `canOnModule` from being called with null. **Safe.**

But what if `$hosting->module_id` is set to an ID that doesn't exist in the `modules` table? Then `$hosting->module` would be null due to the BelongsTo relationship returning null. The short-circuit still protects us.

**Verdict:** Safe, but fragile. A future refactor could remove the null check.

---

## TRACE: User clicks "reveal password"

`HostingController@getPassword`:
```php
$hosting = Hosting::findOrFail($id);  // RbacScope applies
abort_unless($user->hasRole('super-admin') || ($hosting->module && $user->canOnModule($hosting->module, 'reveal')), 403);
```

**Correct pattern.** Same logic as edit.

---

## TRACE: User creates a new record

`HostingController@store`:
```php
$validated['user_id'] = Auth::id();  // ← WRONG: forces ownership
if (! $user->hasRole('super-admin')) {
    $moduleId = $validated['module_id'] ?? null;
    $module = $moduleId ? Module::find($moduleId) : Module::where('slug', $this->moduleSlug())->first();
    abort_unless($module && $user->canOnModule($module, 'create'), 403);
}
```

| Issue | Severity | Reason |
|-------|----------|--------|
| `user_id` forced to Auth::id() | HIGH | Creates ownership where none should exist |
| `module_id` comes from request | HIGH | User can select ANY module, mis-categorizing the record |
| Super-admin bypasses create check | MEDIUM | No authorization at all for SA — fine, but no audit |

---

## TRACE: Super-admin permission check (all operations)

All controllers check `hasRole('super-admin')` first. If true, ALL authorization checks pass.

**Correct.**
No controller calls `canOnModule()` for super-admins — they always use the short-circuit `$user->hasRole('super-admin') || ` pattern.

---

## TRACE: User override save flow

```
[Browser] User changes Hosting from "View Only" to "Manage"
  → JS: preset=2, baseline=1 → preset !== baseline → IN payload
[Browser] User changes Hosting back to "Inherited"
  → JS: preset=1, baseline=1 → preset === baseline → EXCLUDED from payload
[Browser] Save clicked
  → PUT /users/{id}/permissions with JSON body
[Controller] updatePermissions()
  → Validates 'permissions' => 'nullable|array'
  → saveUserModulePermissions($user, $request->input('permissions'))
    → Process each module in payload: updateOrCreate
    → [4-line fix] Delete rows for modules NOT in payload
[Database] Stale override row deleted
[Next request] canOnModule(): no override row → role baseline
```

**This flow is now correct after the 4-line fix.**

**Remaining concern:** The validation only checks `'permissions' => 'nullable|array'`. There is no validation that the module IDs in the permissions array actually exist in the `modules` table, or that the permission column names are valid. A malformed request could potentially create unexpected data. Low risk since this is super-admin only.

---

## TRACE: API user accesses records

`HostingController` (Web) uses RbacScope.
There is NO API controller for Hosting directly. The API goes through `DashboardController` which applies `user_id` filtering.

**API vs Web inconsistency:** A user who can see 50 hosting records on the web page would see only their own records on the dashboard API.

---

## RUNTIME TRUTH SUMMARY

| Layer | Check | Runtime Truth |
|-------|-------|---------------|
| Route | Middleware | `auth`, `suspended` — no role check at route level |
| Controller index | Data scope | RbacScope — module_id IN accessibleIds |
| Controller index | Button perms | canOnModule() for create/export/delete |
| Controller detail | Data scope | RbacScope |
| Controller detail | Action auth | canOnModule() for update/delete/reveal |
| Controller create | Action auth | canOnModule() for create |
| Controller store | Action auth | canOnModule() for create |
| Controller store | user_id | **WRONG** — forced to Auth::id() |
| Controller store | module_id | **DANGEROUS** — user-selectable or null |
| Sidebar | Visibility | getAccessibleModuleIds('read') |
| Blade views | Button visibility | $canCreate/$canExport/etc from controller |
| Blade views | Action visibility | canOnModule() in @if conditions |
| Service layer | Data visibility | **WRONG** — WHERE user_id = ? |
| Dashboard | Data visibility | **WRONG** — WHERE user_id = ? |
| Export | Data visibility | **WRONG** — WHERE user_id = ? (non-SA) |
| API | Data visibility | **WRONG** — WHERE user_id = ? |
| Bulk actions | Action auth | canOnModule() + ownership fallback |
| Permission override | Save | ✅ Fixed (stale rows deleted) |
| Permission override | Read | canOnModule() correctly reads overrides |

**Verdict:** The runtime authorization logic (canOnModule, hasRole) is correct. The runtime data visibility logic is BROKEN in 4 locations (services, dashboard, exports, API). The store() method violates the business rule by forcing user_id.
