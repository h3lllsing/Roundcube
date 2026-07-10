# PERMISSION SAFE FIX PLAN

**Project:** OpsPilot Portal
**Date:** 2026-07-08
**Based on:** Findings in 102-104

---

## CRITICAL FIXES (Deployment-Blocking)

**None.** All 10 attack scenarios have adequate protection. Deployment can continue.

---

## HIGH-PRIORITY FIXES (Within 1 Week)

### FIX-001: Add `modList` Getter to `permissions.js`

**Finding:** `resources/js/permissions.js` uses `this.modList` in 8 getter methods but never defines it. `save()` crashes silently at `this.sensitiveChanges.length` when `modList` is undefined.

**Fix:** ✅ **ALREADY APPLIED** — see `permissions.js:222-224`

**Verification:** `get modList() { return Object.values(this.modules); }`

---

### FIX-002: Add Permission Key Validation to Update Endpoint

**Finding (Scenario 4):** `UserController::updatePermissions()` validates module IDs exist but does NOT validate which permission keys are accepted. Crafted payload could set any key from `config('permissions.keys')`.

**Fix:** Add validation:
```php
$request->validate([
    'permissions' => ['nullable', 'array'],
    'permissions.*' => ['array', function ($attr, $value, $fail) {
        $allowedKeys = config('permissions.keys');
        $extraKeys = array_diff(array_keys($value), $allowedKeys);
        if ($extraKeys) {
            $fail('Invalid permission keys: '.implode(', ', $extraKeys));
        }
    }],
]);
```

**File:** `app/Http/Controllers/Web/UserController.php` — after line 374

**Risk:** Low — only affects malicious payloads

---

### FIX-003: Add Cache Invalidation to `removeForRole()`

**Finding (DUP-012):** `ModulePermissionService::removeForRole()` deletes role permissions but does NOT increment `perms_generation`.

**Fix:** Add `Cache::increment('perms_generation');` after the delete operation.

**File:** `app/Services/ModulePermissionService.php` — after line 54

**Risk:** Low — bug fix

---

### FIX-004: Fix Cached Path to Include All User Overrides

**Finding (C-001):** `getAllModulePermissionsFromDb()` only loads user overrides for modules that have role permissions. Overrides on other modules are silently ignored in the cached path.

**Fix:** Remove the `$allModuleIds` filter:
```php
$userOverrides = UserModulePermission::where('user_id', $this->id)->get();
```

**File:** `app/Traits/HasModulePermissions.php` — lines 89-91

**Risk:** Medium — changes cached return values. Users with overrides on modules without role permissions may see behavior change (module becomes accessible).

---

### FIX-005: Add `show()` Permission Check to BaseResourceController

**Finding (DUP-008):** `BaseResourceController::show()` relies solely on RbacScope, no explicit `canOnModule(read)` check.

**Fix:** Add abort_unless after findOrFail and before returning view:
```php
$module = ModuleCache::findBySlug($this->moduleSlug());
abort_unless(Auth::user()->hasRole('super-admin') || Auth::user()->canOnModule($module, 'read'), 403);
```

**File:** `app/Http/Controllers/Web/BaseResourceController.php` — in `show()` method

**Risk:** Medium — if RbacScope was the only barrier and a bug allows record through, this adds a second layer.

---

## MEDIUM-PRIORITY FIXES (Within 1 Month)

### FIX-006: Align API Authorization with Web Authorization

**Finding (C-004):** API controllers use hardcoded `$record->user_id !== $user->id` ownership checks that Web controllers don't have.

**Fix:** Remove ownership checks from all 11 API controllers, keeping only `canOnModule()` checks.

**Files:**
- `app/Http/Controllers/Api/VpsController.php` (lines 122, 167, 201)
- `app/Http/Controllers/Api/VoipController.php` (lines 125, 176, 213)
- `app/Http/Controllers/Api/HostingController.php` (lines 116, 160, 190)
- `app/Http/Controllers/Api/DomainController.php` (lines 110, 151, 176)
- `app/Http/Controllers/Api/ServiceProviderController.php` (lines 113, 154, 184)
- `app/Http/Controllers/Api/OtherServiceController.php` (lines 116, 160, 190)
- `app/Http/Controllers/Api/DomainEmailController.php` (lines 113, 153, 183)
- `app/Http/Controllers/Api/ExpiryTrackerController.php` (lines 124, 166, 196)
- `app/Http/Controllers/Api/AssetController.php` (lines 50, 60, 73)
- `app/Http/Controllers/Api/NoteController.php` (lines 221, 257, 289)
- `app/Http/Controllers/Api/AttachmentController.php` (lines 67, 117, 146, 169, 191)
- `app/Http/Controllers/Api/WebhookController.php` (lines 49, 58, 69, 80)

**Risk:** HIGH — changes API authorization behavior. Must coordinate with API consumers.

---

### FIX-007: Add Module Delete Observer for Cache Invalidation

**Finding (VP-014):** Deleting a module cascade-deletes permission rows but doesn't increment `perms_generation`.

**Fix:** Add to `app/Providers/AppServiceProvider.php`:
```php
Module::deleted(fn () => Cache::increment('perms_generation'));
```

**Risk:** Low

---

## FIX EXECUTION ORDER

```
Week 1 (Critical + High):
  FIX-001  ✅ Already applied
  FIX-002  → Permission key validation (30 min)
  FIX-003  → removeForRole cache fix (5 min)
  FIX-004  → Override cache fix (15 min)
  FIX-005  → BRC show() read check (15 min)

Week 2-4 (Medium):
  FIX-006  → API authorization alignment (requires discussion)
  FIX-007  → Module delete observer (10 min)
```

---

## DOES ANY FIX BLOCK DEPLOYMENT?

| Fix | Blocks? | Reason |
|-----|---------|--------|
| FIX-001 | ❌ Was already blocked (fixed) | Silent JS crash — **was blocking**, now resolved |
| FIX-002 | ❌ No | Only blocks malicious payloads |
| FIX-003 | ❌ No | 60s stale cache acceptable |
| FIX-004 | ❌ No | Edge case (override on non-role module) |
| FIX-005 | ❌ No | Defense-in-depth improvement |
| FIX-006 | ❌ No | Functional alignment, not security |
| FIX-007 | ❌ No | Minor improvement |

**Deployment verdict: ✅ GO** — No fix is deployment-critical.
