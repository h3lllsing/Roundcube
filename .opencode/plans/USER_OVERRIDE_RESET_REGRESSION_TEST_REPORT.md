# USER OVERRIDE RESET REGRESSION TEST REPORT

## Summary

| Item | Status |
|------|--------|
| 4-line PHP fix applied | ✅ `UserController::saveUserModulePermissions()` line 67-71 |
| Regression test added | ✅ `test_omitted_module_from_payload_deletes_stale_override` |
| Existing tests unaffected | ✅ 17 existing tests still pass |
| New test passes | ✅ |
| Total tests run | 18 passed, 0 failed |

---

## Test Results

```
PASS  Tests\Feature\UserModulePermissionTest
  ✓ user override true grants permission even if role denies
  ✓ user override false denies permission even if role grants
  ✓ null override inherits role permission
  ✓ no override keeps existing role behavior
  ✓ get accessible module ids respects user overrides
  ✓ super admin can create overrides through user create
  ✓ super admin can update overrides through permissions page
  ✓ super admin can delete overrides through permissions page
  ✓ non super admin cannot manage overrides
  ✓ force deleting user deletes overrides
  ✓ get all module permissions includes overrides
  ✓ get effective module permissions shows source
  ✓ super admin cannot assign super admin role through form
  ✓ cannot delete last super admin
  ✓ cannot self demote super admin
  ✓ can delete super admin if another exists
  ✓ omitted module from payload deletes stale override      ← NEW
  ✓ rbac phase1 behavior preserved without overrides

Tests:    18 passed (71 assertions)
Duration: 16.88s
```

---

## Regression Test: `test_omitted_module_from_payload_deletes_stale_override`

**Location:** `tests/Feature/UserModulePermissionTest.php:400-464`

**Purpose:** Verifies the JS/backend contract where modules reset to "Inherited" are omitted from the payload, and the backend correctly deletes their stale override rows.

**Test Flow:**

| Step | Action | Expected |
|------|--------|----------|
| 1 | normalUser has role baseline: ModuleA=can_read, ModuleB=no read | Role grants A, denies B |
| 2 | Create override: ModuleA can_read=false (deny) | Override A active → canOnModule(A) = false |
| 3 | Create override: ModuleB can_read=true (grant) | Override B active → canOnModule(B) = true |
| 4 | Send PUT only ModuleB in payload (ModuleA omitted) | ModuleA not in payload |
| 5 | Assert ModuleA override row deleted | `exists()` = false |
| 6 | Assert ModuleB override row preserved | `exists()` = true |
| 7 | Assert canOnModule(A) falls back to role | Returns true (role baseline) |
| 8 | Assert canOnModule(B) still follows override | Returns true (override grants) |

**Covered assertions:**
- Direct row existence check (`exists()` — not just `canOnModule()`)
- `canOnModule()` falls back to role baseline after row deletion
- `canOnModule()` still respects active override for preserved row

---

## Changes Applied

### File 1: `app/Http/Controllers/Web/UserController.php`

**Method:** `saveUserModulePermissions()` — 4 lines added after the foreach loop:

```php
$incomingModuleIds = array_keys($permissions ?? []);
if (!empty($incomingModuleIds)) {
    UserModulePermission::where('user_id', $user->id)
        ->whereNotIn('module_id', $incomingModuleIds)->delete();
}
```

### File 2: `tests/Feature/UserModulePermissionTest.php`

**New method:** `test_omitted_module_from_payload_deletes_stale_override` (lines 400-464)

### File 3: `phpunit.xml` (created)

Standard Laravel phpunit.xml config with `APP_URL=http://localhost` and `DB_DATABASE=opspilot_test`.

---

## Verification Checklist

| Check | Result |
|-------|--------|
| 4-line cleanup exists after foreach | ✅ Line 67-71 of UserController.php |
| reset override row deleted | ✅ Row `exists()` returns false |
| unrelated overrides not deleted | ✅ ModuleB row `exists()` returns true |
| canOnModule falls back to role baseline | ✅ ModuleA: `canOnModule('read')` = true (role grants) |
| canOnModule still follows preserved override | ✅ ModuleB: `canOnModule('read')` = true (override grants) |
| existing tests unaffected | ✅ All 17 existing tests pass |
| super-admin bypass untouched | ✅ No changes to any controller/route auth |

---

## Conclusion

Fix is verified and stable. No regressions. The JS/backend contract is now tested and enforced.
