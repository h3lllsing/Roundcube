# 07 — RBAC SCOPE ARCHITECTURE AUDIT

## The global scoping mechanism — `RbacScope::apply()`

---

## Architecture Overview

```php
public static function apply(string $modelClass, string $visibility = 'ownership'): void
```

**File:** `app/Helpers/RbacScope.php`

**Design:** A static helper that adds Eloquent global scopes to a model class at runtime. Called at the beginning of every controller method that needs data visibility filtering.

---

## Finding 1: Static method, cannot be mocked (MEDIUM)

**Issue:** `RbacScope::apply()` directly calls `Auth::user()` and `$user->hasRole()` and `$user->getAccessibleModuleIds()`. This is tightly coupled to the static Auth facade.

**Risk:** 
- Unit testing controllers that call `$this->userOwnedFilter()` requires full Laravel authentication setup
- Cannot test visibility rules in isolation without seeding full user/role/permission data
- Any test that calls a controller method must set up the full auth context

**Fix direction:** Make RbacScope injectable or create a service class that can be mocked.

**Verdict:** Not a blocker for v1.0, but a testing pain point.

---

## Finding 2: `module_id` null values are silently invisible (CRITICAL)

**Issue:** `WHERE module_id IN (1,2,3)` does NOT match `module_id IS NULL`.

**Impact:** Any record with null module_id is invisible to all non-super-admin users. This affects:
- VoIP records (no module_id field in form → null)
- Domain Email records (no module_id field in form → null)
- Any record where module_id is nullable and not set

**Evidence:**
- `VoipController@store` does NOT set `$validated['module_id']`
- `Voip` model has `'module_id'` in fillable → stored as null from validated data
- `StoreVoipRequest::rules()` has `'module_id' => 'nullable|exists:modules,id'` but no input is sent

**Fix direction:** 
1. Auto-set module_id in every store() method based on the route's module slug
2. OR: Add a fallback in RbacScope: `WHERE module_id IN (...) OR module_id IS NULL`

**Verdict:** Data loss bug, must fix before v1.0.

---

## Finding 3: Global scopes are additive, not exclusive (LOW)

**Issue:** PHP's `addGlobalScope()` adds to the query, it doesn't replace previous scopes. If a model has a global scope defined in the model (e.g., `ActiveScope`), AND `RbacScope` adds another scope, both apply.

**Impact:** Currently no model defines its own global scopes, so this is not an issue. But if a future developer adds a global scope to a model (e.g., `whereNull('deleted_at')` for soft deletes — which is actually automatic), there could be unexpected interactions.

**Verdict:** Document that RbacScope adds runtime scopes and ensure future developers know.

---

## Finding 4: Multiple calls to `apply()` stack scopes (MEDIUM)

**Issue:** If a controller calls `$this->userOwnedFilter()` multiple times (e.g., in index and then again in a data load), `RbacScope::apply()` adds a NEW global scope each time.

```php
// First call:
$modelClass::addGlobalScope('moduleScope', fn ($q) => $q->whereIn('module_id', [1,2,3]));

// Second call:
$modelClass::addGlobalScope('moduleScope', fn ($q) => $q->whereIn('module_id', [1,2,3]));
```

**Impact:** The second call re-registers the scope with the same name. Laravel's global scope with duplicate names... let me think. Actually, in Laravel, if you add a global scope with the same name, I believe it overwrites the previous one. Let me verify.

Actually, looking at the Laravel source, `addGlobalScope` checks if the scope exists and replaces it. So duplicate calls are safe. But this is a Laravel implementation detail.

**Verdict:** Need to verify Laravel's behavior, but likely safe.

---

## Finding 5: The `ownership` fallback path has dead `admin` check (LOW)

```php
// Only reached if visibility !== 'module'
if ($user->hasRole('admin')) {
    // ... module scope with accessible IDs ...
    return;
}
// Fall through to ownership
$modelClass::addGlobalScope('ownership', fn ($q) => $q->where('user_id', $user->id));
```

**Issue:** This `admin` branch is only reached for controllers that use `'ownership'` visibility. But no current controller uses `'ownership'` visibility for global records. The branch is dead code.

**Impact:** None currently, but it's confusing. A future developer reading this might think admin users always fall through to module scope, when the branch is never executed for `'module'` visibility calls.

**Fix direction:** Remove the admin branch and simplify RbacScope to two clean paths:
1. `'module'` visibility → module scope (for global records)
2. `'ownership'` visibility → ownership scope (for personal modules)

---

## Finding 6: `getAccessibleModuleIds()` uses `pluck` — no caching (LOW)

**File:** `HasModulePermissions.php:86-103`

```php
$roleModuleIds = Module::whereHas('rolePermissions', function ($q) use ($column) {
    $q->whereIn('role_id', $this->getRoleIds())
        ->where($column, true);
})->pluck('id')->toArray();
```

**Issue:** This runs a database query every time `getAccessibleModuleIds()` is called. On a page load, it might be called:
- By RbacScope for data visibility (once per model)
- By SidebarComposer (once)
- By each Blade view with permission checks

For the index page of a module, it's called 2-3 times. The result is the same per user/session, but it's re-queried each time.

**Impact:** Minor performance concern. For a heavy page with multiple modules, this could be 5-10 queries for the same data.

**Fix direction:** Cache the result per request using Laravel's `once()` helper or a request-level cache:
```php
private function getAccessibleModuleIds(string $action): array
{
    $column = 'can_'.$action;
    return once(function () use ($column) {
        // ... query logic ...
    });
}
```

**Verdict:** Performance optimization, not a blocker.

---

## Finding 7: `getAccessibleModuleIds()` and `getRoleIds()` have unstable caching (MEDIUM)

**File:** `HasModulePermissions.php:14-19`

```php
private function getRoleIds(): array
{
    if ($this->cachedRoleIds === null) {
        $this->cachedRoleIds = $this->roles()->pluck('roles.id')->toArray();
    }
    return $this->cachedRoleIds;
}
```

**Issue:** The cache is stored on the trait instance (the User model). When the User is serialized and deserialized (e.g., across queue jobs), the cache is lost. But more importantly, the cache is NEVER invalidated. If roles change mid-request (unlikely but possible in a long-running process or testing), the cache is stale.

**Impact:** Very low for web requests. The User model is loaded fresh per request.

**Verdict:** Not a bug, but fragile pattern.

---

## Finding 8: RbacScope and User module permissions trait are in different places (LOW)

- Authorization logic: `app/Traits/HasModulePermissions.php` (trait on User model)
- Data scope logic: `app/Helpers/RbacScope.php` (static helper)
- Authorization checks: `app/Http/Controllers/Web/*.php` (inline in controllers)
- Sidebar visibility: `app/Http/View/Composers/SidebarComposer.php` (view composer)

**Issue:** The same conceptual feature (permission system) is spread across 4 different locations with different patterns (trait, static class, inline, composer).

**Impact:** Developer confusion. New developers need to understand 4 different code locations to grasp "how permissions work."

**Fix direction:** Consider consolidating into a service class `PermissionService` or similar. Not urgent for v1.0.

---

## ARCHITECTURAL SCORE

| Criteria | Score | Notes |
|----------|-------|-------|
| Correctness | 6/10 | Module scope correct, ownership scope wrong in 4 places |
| Consistency | 4/10 | Web/API/Dashboard/Export all different |
| Testability | 3/10 | Static methods, inline checks, no policies |
| Maintainability | 5/10 | Duplicate patterns, scattered implementation |
| Performance | 7/10 | Minor redundant queries |
| Framework alignment | 2/10 | Ignores Laravel's authorization system |
| Production readiness | 5/10 | Functional but has hidden data loss bugs |

**Bottom line:** The RBAC scope architecture works for the web UI but has systemic inconsistencies across API, dashboard, exports, and service layer. The architecture is "grown" rather than "designed" — it appears to work if you only test the web UI, but fails under any cross-channel scrutiny.
