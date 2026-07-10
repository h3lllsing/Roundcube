# 06 — POLICY / GATE / CONTROLLER ALIGNMENT AUDIT

## Are Laravel's authorization features being used? **No.** Is that a problem? **Yes and no.**

---

## What Laravel Provides

| Feature | File | Used? |
|---------|------|-------|
| `AuthServiceProvider` | `app/Providers/AuthServiceProvider.php` | ❌ Empty (no Gates, no Policies registered) |
| Policies | `app/Policies/` | ❌ Does not exist |
| FormRequest `authorize()` | `app/Http/Requests/*.php` | ❌ All return `true` |
| Controller `authorize()` | Called from any controller | ❌ Never used |
| `@can` blade directive | Used in any view | ❌ Never used |
| `Gate::authorize()` | Used anywhere | ❌ Never used |

---

## What Actually Enforces Authorization

**Pattern:** `abort_unless($user->hasRole('super-admin') || ..., 403)` — inline controller method calls.

This is used in:
- All 9 module controllers
- UserController
- ModulePermissionController
- ExportController
- ImportController

**This is not inherently wrong.** The inline check is explicit, readable, and easy to trace. However:

### Problems:

### 1. NO CENTRALIZED DEFINITION (MEDIUM)

Every controller defines its own permission logic. The pattern is consistent but duplicated. To audit all authorization rules, you must read every controller method.

**Duplication count:** The pattern `abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'action')), 403)` appears ~36 times across 9 controllers.

**Risk:** If the permission model changes (e.g., adding a new action), every controller must be updated individually. Expect missed controllers.

### 2. FORM REQUESTS RETURN TRUE (HIGH)

All form request `authorize()` methods return `true`:

```php
// StoreServiceProviderRequest.php:9-12
public function authorize(): bool { return true; }
```

The actual authorization is done inside the controller's `store()` method AFTER validation:
```php
// ServiceProviderController.php:83-88
if (! $user->hasRole('super-admin')) {
    $module = Module::where('slug', ...)->first();
    abort_unless($module && $user->canOnModule($module, 'create'), 403);
}
```

**Why this is risky:** The authorization check is in the controller body, not in the form request. This means:
- If a new route is added that skips this controller (e.g., a new API endpoint), authorization is silently missing
- The form request `authorize()` is the idiomatic Laravel place for this check, but it's bypassed
- Code review is harder — authorization logic is mixed with business logic in `store()` instead of being separated

### 3. NO POLICIES (MEDIUM)

Policies would provide:
- A single file per model with `view()`, `create()`, `update()`, `delete()` methods
- Automatic integration with `@can` in Blade
- Automatic integration with `$this->authorize()` in controllers
- Automatic integration with Gate checks

Without policies, the Blade views use `$canCreate` booleans passed from controllers. This works but:
- Controllers must load modules and check permissions even for the index page (just to pass booleans to the view)
- Each index() method has 5-7 lines of setup just for view booleans:
  ```php
  $user = Auth::user();
  $module = Module::where('slug', $this->moduleSlug())->first();
  $isSuperAdmin = $user->hasRole('super-admin');
  $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
  $canExport = $isSuperAdmin || ($module && $user->canOnModule($module, 'export'));
  // ... etc
  ```
- This is duplicated in every controller. With policies, you'd just call `@can('create', $module)` in the view.

**Fragility:** If a view is refactored and a `$canX` variable is missing, the button might show when it shouldn't (or hide when it shouldn't). With `@can`, the check is always evaluated at render time.

### 4. NO GATES DEFINED (LOW)

`AuthServiceProvider.php` (if it exists) would register Gates for custom authorization logic. But since all authorization uses `hasRole()` and `canOnModule()` directly, Gates aren't needed for the current architecture.

---

## View Authorization: Controller-Passed Booleans vs. @can

### Current approach:
```php
// Controller passes booleans:
return view('hostings.index', compact(..., 'canCreate', 'canExport', ...));

// View uses booleans:
@if($canCreate)
    <a href="{{ route('hostings.create') }}">Create</a>
@endif
```

### Policy approach (not used):
```php
// View uses @can:
@can('create', App\Models\Hosting::class)
    <a href="{{ route('hostings.create') }}">Create</a>
@endcan
```

**Difference:** With controller-passed booleans:
1. The controller MUST be called to set these variables (obviously it is since this is the controller method)
2. The view is "dumb" — it only shows what the controller tells it
3. A view rendered in a different context (cached, rendered from another controller) would miss these variables

**Risk:** VERY LOW for current architecture since views are only rendered by their respective controllers. But this is a maintenance trap for future development.

---

## Controller->Model Authorization Gap

### Current pattern:
```php
public function edit(int $id): View
{
    $this->userOwnedFilter();                     // Data scope
    $hosting = Hosting::findOrFail($id);          // Load record
    
    // Action authorization:
    abort_unless($user->hasRole('super-admin') || ($hosting->module && $user->canOnModule($hosting->module, 'update')), 403);
}
```

### With Policy (not used):
```php
public function edit(int $id): View
{
    $hosting = Hosting::findOrFail($id);
    $this->authorize('update', $hosting);    // Policy handles all logic
}
```

**Benefit of policies:** If you need to change "who can update a hosting record," you change ONE file (the policy) instead of 9 controllers.

---

## ALIGNMENT SUMMARY

| Component | Expected (Laravel idiomatic) | Actual | Gap |
|-----------|------------------------------|--------|-----|
| AuthServiceProvider | Define Gates, register Policies | Empty/doesn't exist | NO GATES |
| FormRequest `authorize()` | Check permission per action | Returns `true` | NO AUTHORIZE |
| Controller `authorize()` | Delegate to Policy | Not used | NO DELEGATION |
| Blade `@can` | Check permission inline | Uses `$canX` booleans | NO DIRECTIVE |
| Model Policies | CRUD permission per model | Don't exist | NO POLICIES |

**Verdict:** The authorization system uses a custom inline pattern that works but is immature. It bypasses all of Laravel's authorization framework. This is NOT a bug (the checks are correct) but it's a MAINTENANCE CONCERN for an enterprise system.

---

## SPECIFIC ISSUES

### Issue 1: `preventSuperAdminAssignment()` anti-pattern

**File:** `UserController.php:68-77`
**Severity:** MEDIUM

```php
private function preventSuperAdminAssignment(Request $request): void
{
    $superAdminRoleId = Role::where('slug', 'super-admin')->value('id');
    if ($superAdminRoleId && $request->has('roles')) {
        $roles = $request->input('roles', []);
        if (in_array($superAdminRoleId, $roles)) {
            abort(403, 'Cannot assign Super Admin role through this form.');
        }
    }
}
```

This is called in `store()` and `update()` but NOT in the API user controller. API `UsersController::store` at line 87 does:
```php
$user->roles()->sync($roles);
```
Without calling `preventSuperAdminAssignment()`. This means the API CAN assign super-admin role. Gap between web and API authorization.

### Issue 2: Self-demotion prevention only in web

**File:** `UserController.php:342-348`
```php
if ($currentUser->id === $user->id && $superAdminRoleId) {
    if (in_array($superAdminRoleId, $currentRoles) && !in_array($superAdminRoleId, $newRoles)) {
        abort(403, 'Cannot remove your own Super Admin role.');
    }
}
```

Not present in `Api\UsersController::update`. API allows self-demotion.

### Issue 3: Form Request `authorize()` returning true

**File:** All form requests in `app/Http/Requests/`
**Severity:** LOW (authorization happens in controller)

While not a bug, this is an anti-pattern. The form request SHOULD handle authorization. If someone adds a new route that uses the same form request but a different controller, the authorization is silently missing.

---

## RECOMMENDATION

For a v1.0 release candidate: **Do not add policies now.** The inline checks are working and tested. But:
1. Document the authorization pattern clearly for all developers
2. Add `preventSuperAdminAssignment()` to the API user controller
3. Add self-demotion prevention to the API user controller
4. Flag FormRequest `authorize()` as a post-v1 improvement
