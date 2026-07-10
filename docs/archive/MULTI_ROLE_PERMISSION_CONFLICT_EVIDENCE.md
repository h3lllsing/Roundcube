# MULTI-ROLE PERMISSION CONFLICT EVIDENCE

## Allegation: HasModulePermissions::canOnModule() uses `first()` instead of OR/exists/max merge across all roles

> Confirm whether it uses first() instead of OR/exists/max merge across all roles.

---

## VERDICT: FALSE POSITIVE

**`canOnModule()` correctly uses `exists()` with `whereIn('role_id', ...)` — this is proper OR semantics across all roles.**

---

## EVIDENCE

### Authorization-critical method: `app/Traits/HasModulePermissions.php`
```php
// Lines 22-37
public function canOnModule(Module $module, string $action): bool
{
    $column = 'can_'.$action;

    // User-level override checked first (explicit yes/no)
    $userOverride = UserModulePermission::where('user_id', $this->id)
        ->where('module_id', $module->id)
        ->first();

    if ($userOverride && $userOverride->$column !== null) {
        return $userOverride->$column;
    }

    // Role-level: ANY role with permission = true (OR semantics)
    return ModuleRolePermission::whereIn('role_id', $this->getRoleIds())   // <--- all roles
        ->where('module_id', $module->id)
        ->where($column, true)
        ->exists();                                                        // <--- OR logic
}
```

**Line 34:** `whereIn('role_id', $this->getRoleIds())` fetches ALL user roles.
**Line 37:** `->exists()` returns `true` if ANY role grants the permission.
**User override:** If present and non-null, it overrides the role result (positive OR negative).

This is correct behavior: a user with roles `[admin, editor]` where `admin` has `can_create=true` and `editor` has `can_create=false` will get `true` from `canOnModule()`.

### Display-only method: `getEffectiveModulePermissions()` (same file)
```php
// Lines 112-114
$rolePerm = ModuleRolePermission::whereIn('role_id', $roleIds)
    ->where('module_id', $module->id)
    ->first();    // <--- first() used here
```

This method uses `first()`, but it is **NOT used for authorization decisions**. It is a diagnostic/display helper that shows permissions "as seen from each source." It returns the first matching role's row for informational purposes.

### Comparison: `getAllModulePermissions()` (same file)
```php
// Lines 50-58
foreach ($perms as $moduleId => $modulePerms) {
    $merged = array_fill_keys($keys, false);
    foreach ($modulePerms as $p) {
        foreach ($keys as $key) {
            if ($p->$key) {
                $merged[$key] = true;         // <--- merge OR
            }
        }
    }
    $result[$moduleId] = $merged;
}
```

This method correctly merges across ALL role rows with OR semantics for display purposes.

### Existing documentation of this finding
This was previously identified in `ARCHITECTURAL_ASSUMPTIONS.md`:

> **Finding 1.4.1** — The `getRoleIds()` method caches across the request, so adding a role mid-request is invisible.

Not a conflict — just a cache behavior note.

---

## OTHER MULTI-ROLE ISSUES (separate from the allegation)

### 1. `getAccessibleModuleIds()` — also correct
```php
// Lines 82-104
public function getAccessibleModuleIds(string $action): array
{
    // ...
    $roleModuleIds = Module::whereHas('rolePermissions', function ($q) use ($column) {
        $q->whereIn('role_id', $this->getRoleIds())->where($column, true);
    })->pluck('id')->toArray();
    // ...
}
```
Uses `whereHas()` which returns module if ANY role permission matches — OR semantics. Correct.

### 2. `getEffectiveModulePermissions()` `first()` concern
As noted, this method uses `first()`. If the user has two roles with different permissions on the same module, this display method only shows the first role's row. This is a **display bug**, not an authorization bug.

---

## CONCLUSION

**FALSE POSITIVE.** `canOnModule()` uses `exists()` with `whereIn()` — proper OR semantics. The `first()` exists only in the display-only `getEffectiveModulePermissions()` method, which is not used for any authorization decision. No multi-role conflict exists in authorization logic.
