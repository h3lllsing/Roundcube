# PERMISSION SAFE FIX PLAN (Addendum)

**This file cross-references `105_PERMISSION_SAFE_FIX_PLAN.md`**
**Focus:** Granular step-by-step fix instructions for each identified issue

---

## FIX EXECUTION GUIDE

### FIX-002: Add Permission Key Validation

**Problem:** `UserController::updatePermissions()` doesn't validate which permission keys are in the payload.

**File:** `app/Http/Controllers/Web/UserController.php`

**Current code (around line 374):**
```php
$request->validate([
    'permissions' => ['nullable', 'array', function ($attr, $value, $fail) {
        $moduleIds = array_keys($value);
        $validIds = Module::whereIn('id', $moduleIds)->pluck('id')->all();
        $invalid = array_diff($moduleIds, $validIds);
        if ($invalid) {
            $fail('Invalid module IDs: '.implode(', ', $invalid));
        }
    }],
]);
```

**Replace with:**
```php
$allowedKeys = config('permissions.keys');

$request->validate([
    'permissions' => ['nullable', 'array', function ($attr, $value, $fail) use ($allowedKeys) {
        $moduleIds = array_keys($value);
        $validIds = Module::whereIn('id', $moduleIds)->pluck('id')->all();
        $invalid = array_diff($moduleIds, $validIds);
        if ($invalid) {
            $fail('Invalid module IDs: '.implode(', ', $invalid));
        }

        foreach ($value as $moduleId => $perms) {
            $extraKeys = array_diff(array_keys($perms), $allowedKeys);
            if ($extraKeys) {
                $fail("Invalid permission keys for module {$moduleId}: ".implode(', ', $extraKeys));
            }
        }
    }],
]);
```

**Test:** Verify that sending `can_hack=true` for a module returns 422 validation error.

---

### FIX-003: Add Cache Invalidation to removeForRole

**File:** `app/Services/ModulePermissionService.php`

**Current code (around line 51):**
```php
public function removeForRole(Module $module, int $roleId): void
{
    ModuleRolePermission::where('module_id', $module->id)
        ->where('role_id', $roleId)
        ->delete();
}
```

**Replace with:**
```php
public function removeForRole(Module $module, int $roleId): void
{
    ModuleRolePermission::where('module_id', $module->id)
        ->where('role_id', $roleId)
        ->delete();

    Cache::increment('perms_generation');
}
```

**Add import at top:**
```php
use Illuminate\Support\Facades\Cache;
```

**Test:** Verify `perms_generation` increments after calling removeForRole.

---

### FIX-004: Include All User Overrides in Cached Path

**File:** `app/Traits/HasModulePermissions.php`

**Current code (lines 89-91):**
```php
$userOverrides = UserModulePermission::where('user_id', $this->id)
    ->whereIn('module_id', $allModuleIds)
    ->get();
```

**Replace with:**
```php
$userOverrides = UserModulePermission::where('user_id', $this->id)->get();
```

**Test:** 
1. Create user override for a module that has NO role permissions
2. Call `getAccessibleModuleIds('read')` — must include the module
3. Call `canOnModule($module, 'read')` — must return true

---

### FIX-005: Add show() Permission Check to BRC

**File:** `app/Http/Controllers/Web/BaseResourceController.php`

**Current show() method (lines 146-156):**
```php
public function show(int $id): View
{
    $modelClass = $this->modelClass();
    $record = $modelClass::with($this->showWith())->findOrFail($id);
    $data = array_merge($this->showExtraData($record), [
        $this->recordVariable() => $record,
    ]);
    return view($this->viewPrefix().'.show', $data);
}
```

**Replace with:**
```php
public function show(int $id): View
{
    $user = Auth::user();
    $isSuperAdmin = $user->hasRole('super-admin');
    $module = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());

    abort_unless($isSuperAdmin || ($module && $user->canOnModule($module, 'read')), 403);

    $modelClass = $this->modelClass();
    $record = $modelClass::with($this->showWith())->findOrFail($id);
    $data = array_merge($this->showExtraData($record), [
        $this->recordVariable() => $record,
    ]);
    return view($this->viewPrefix().'.show', $data);
}
```

**Add import:**
```php
use App\Helpers\ModuleCache;
use Illuminate\Support\Facades\Auth;
```

**Test:** Verify user with can_update but NO can_read gets 403 on show page.

---

### FIX-007: Module Delete Observer

**File:** `app/Providers/AppServiceProvider.php`

**Add in boot() method:**
```php
use App\Models\Module;
use Illuminate\Support\Facades\Cache;

// In boot():
Module::deleted(fn () => Cache::increment('perms_generation'));
```

**Test:** Delete a module, verify `perms_generation` increments.

---

## FIX ROLLBACK COMMANDS

Each fix can be rolled back with a single git command:

```bash
git checkout -- app/Http/Controllers/Web/UserController.php          # FIX-002
git checkout -- app/Services/ModulePermissionService.php              # FIX-003
git checkout -- app/Traits/HasModulePermissions.php                   # FIX-004
git checkout -- app/Http/Controllers/Web/BaseResourceController.php  # FIX-005
git checkout -- app/Providers/AppServiceProvider.php                  # FIX-007
```

---

## VERIFICATION SCRIPT

After applying all fixes, run:

```bash
# 1. Unit tests
php vendor/bin/phpunit tests/Feature/UserModulePermissionTest.php

# 2. Permissions specific tests
php vendor/bin/phpunit tests/Feature/ --filter="Permission|Override|Role"

# 3. Full test suite
php vendor/bin/phpunit

# 4. Static analysis
php vendor/bin/phpstan analyse --memory-limit=256M

# 5. Vite build
npm run build
```

All tests must pass. PHPStan must show 0 errors.
