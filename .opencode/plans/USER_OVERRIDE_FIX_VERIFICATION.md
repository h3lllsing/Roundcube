# USER OVERRIDE FIX VERIFICATION

## Fix Under Review

**File:** `app/Http/Controllers/Web/UserController.php`
**Method:** `saveUserModulePermissions()` — insert after line 65 (end of foreach), before method closing `}` at line 66

```php
$incomingModuleIds = array_keys($permissions ?? []);
if (!empty($incomingModuleIds)) {
    UserModulePermission::where('user_id', $user->id)
        ->whereNotIn('module_id', $incomingModuleIds)->delete();
}
```

**Purpose:** Delete stale `user_module_permissions` rows for modules that were reset to "Inherited" and thus omitted from the save payload.

---

## Check 1: 4-line cleanup exists after foreach loop

**Status: NOT YET IMPLEMENTED** — The 4 lines are not in the file. They need to be added at `UserController.php:66` (after line 65 `}` and before the method's closing `}` on line 66).

Current code at `UserController.php:27-66`:
```php
private function saveUserModulePermissions(User $user, ?array $permissions): void
{
    if ($permissions === null) { return; }

    $permissionColumns = [...];

    foreach ($permissions as $moduleId => $perms) {
        // ... build $data array ...
        if ($hasValue) {
            UserModulePermission::updateOrCreate(..., $data);
        } else {
            UserModulePermission::where(...)->delete();
        }
    }
} // ← line 66, 4-line fix goes BEFORE this closing brace
```

**Verdict:** ✅ Ready for insertion. No conflicts with existing code.

---

## Check 2: JS Contract — what gets sent vs. omitted

**File:** `resources/js/permissions.js:161-183`

```js
Object.values(this.modules).forEach(mod => {
    // ... build colPerms ...
    if (mod.preset !== mod.baseline || mod.preset === 3) {
        permissions[mod.id] = colPerms;  // ← SENT
    }
    // else: NOT SENT (omitted from payload)
});
```

| Condition | Sent? | Example |
|-----------|-------|---------|
| `preset !== baseline` AND `preset !== 3` | ✅ SENT | User changed View Only→Manage (1→2) |
| `preset === 3` (Custom) regardless of baseline | ✅ SENT | User has granular toggles |
| `preset === baseline` AND `preset !== 3` | ❌ OMITTED | Reset to Inherited, or never touched |

**Key insight:** A module that HAD an override but was reset to "Inherited" 👉 `preset === baseline` 👉 **OMITTED from payload** 👉 stale row persists without the fix.

**Verdict:** ✅ Contract confirmed — the fix correctly targets the gap.

---

## Check 3: Unrelated existing overrides are NOT deleted

**Scenario in question:**

| Module | Old Override | User Action | preset vs baseline | In Payload? |
|--------|-------------|-------------|-------------------|-------------|
| Domains | Manage (2) | Not touched | Only changed session: No → Still 2 vs baseline 1 → `preset !== baseline` | ✅ SENT |
| VPS | View Only (1) | Not touched | 1 vs baseline 1 → `preset === baseline` | ❌ **OMITTED — stale override deleted by fix** |
| Assets | No Access (0) | Not touched | 0 vs baseline 2 → `preset !== baseline` | ✅ SENT |
| Hosting | None | Changed to Manage (2) | 2 vs baseline 1 → `preset !== baseline` | ✅ SENT |

**Analysis:** VPS has `preset === baseline` (1=1). This means the override's effective value matches the role baseline. The override is **neutral/redundant**. Deleting it is correct behavior — the user sees "Inherited" and the role grants the same access.

**Domains, Assets, Hosting** — all have `preset !== baseline` → all SENT → **not deleted**.

**Verdict:** ✅ Safe for the standard case. Edge case: if an admin intentionally set an override to match the role as a "lock" (so role changes don't affect this user), the fix would remove this lock. This is an undocumented/unlikely use case, and the UI shows "Inherited" implying no override exists.

---

## Check 4: Reset override row IS deleted

**Flow:**
1. Admin sets Hosting override to Manage (2) for user
2. Admin clicks "Reset to Role Default" → Hosting preset changes to baseline (1)
3. Admin clicks Save
4. JS gathers ALL modules where `preset !== baseline` → Hosting is at baseline → **omitted**
5. Payload: `{ permissions: { /* Domains, Assets, ... but NOT Hosting */ } }`
6. `saveUserModulePermissions()` processes the incoming modules
7. **4-line fix:** `whereNotIn('module_id', incomingModuleIds)->delete()` → Hosting's stale override row is DELETED
8. `canOnModule()` next time: no UserModulePermission row found → falls to role baseline

**Verdict:** ✅ Override row deleted. Resolution works.

---

## Check 5: canOnModule() falls back to role baseline

**File:** `app/Traits/HasModulePermissions.php:22-38`

```php
public function canOnModule(Module $module, string $action): bool
{
    $userOverride = UserModulePermission::where('user_id', $this->id)
        ->where('module_id', $module->id)
        ->first();

    if ($userOverride && $userOverride->$column !== null) {
        return $userOverride->$column;   // ← stale row used here
    }

    return ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
        ->where('module_id', $module->id)
        ->where($column, true)
        ->exists();                      // ← fallback (correct)
}
```

After the fix: stale row deleted → `$userOverride` is null → falls to `ModuleRolePermission` check → role baseline is used.

**Verdict:** ✅ Correct. No change needed in `canOnModule()`.

---

## Check 6: Sidebar/menu visibility follows updated permission

**File:** `app/Http/View/Composers/SidebarComposer.php:48-53`

```php
$accessibleIds = $user->getAccessibleModuleIds('read');
// ...
$data[$key] = $module && in_array($module->id, $accessibleIds);
```

`getAccessibleModuleIds()` at `HasModulePermissions.php:82-104`:
```php
$overrides = UserModulePermission::where('user_id', $this->id)
    ->whereNotNull($column)->get();

foreach ($overrides as $override) {
    if ($override->$column) {
        $roleModuleIds[] = $override->module_id;       // grant
    } else {
        $roleModuleIds = array_diff(..., [$override->module_id]); // revoke
    }
}
```

After fix: stale override deleted → `$overrides` collection no longer includes the reset module → override no longer affects `getAccessibleModuleIds()` → sidebar reflects role baseline.

**Verdict:** ✅ Sidebar updates automatically — no cached values involved.

---

## Check 7: Direct route access follows updated permission

All module controllers use the same pattern:
```php
abort_unless($user->hasRole('super-admin') || $user->canOnModule($module, 'action'), 403);
```

Since `canOnModule()` delegates to the (unchanged) `HasModulePermissions` trait, and the fix only affects the data (deleting stale rows), the route access automatically reflects the updated state.

**Verdict:** ✅ No change needed in any controller. Route authorization reads from DB on every request.

---

## Check 8: Super-admin bypass still works

**File:** `app/Traits/HasModulePermissions.php:22`

`canOnModule()` does NOT check for super-admin. The bypass is at the **controller level**:
```php
if (! $user->hasRole('super-admin')) {
    $module = Module::where('slug', $this->moduleSlug())->first();
    abort_unless($module && $user->canOnModule($module, 'create'), 403);
}
```

`SidebarComposer:39`:
```php
if ($user->hasRole('super-admin')) {
    // show all — never calls getAccessibleModuleIds()
}
```

The 4-line fix only touches `user_module_permissions` for non-super-admin users (only super-admin has access to the permissions page). Super-admin rows are never touched.

**Verdict:** ✅ Super-admin bypass unaffected.

---

## Existing Test Coverage

| Test | Line | What it tests | Affected by fix? |
|------|------|---------------|-------------------|
| `test_super_admin_can_delete_overrides_through_permissions_page` | 233 | Sends module with all-empty values → deletes row | NOT AFFECTED — module is IN payload, processed normally |
| `test_super_admin_can_update_overrides_through_permissions_page` | 198 | Sends module with explicit 1/0 values → updates row | NOT AFFECTED |
| `test_user_override_true_grants_permission` | 79 | Direct DB insert → canOnModule reads it | NOT AFFECTED |
| `test_user_override_false_denies_permission` | 93 | Direct DB insert → canOnModule reads it | NOT AFFECTED |
| `test_getAccessibleModuleIds_respects_user_overrides` | 135 | Direct DB insert → getAccessibleModuleIds includes it | NOT AFFECTED |

**Gap:** No existing test covers the JS-excluded-module scenario (where a module is reset to baseline and OMITTED from payload). The existing test at line 233 tests a DIFFERENT code path (module IN payload with all-empty values).

---

## Summary

| # | Check | Verdict |
|---|-------|---------|
| 1 | Cleanup exists after foreach | ❌ Not yet inserted (planned) |
| 2 | JS contract: non-baseline sent, reset omitted | ✅ Confirmed |
| 3 | Unrelated overrides not deleted | ✅ Safe — only reset modules excluded |
| 4 | Reset override row deleted | ✅ After fix |
| 5 | canOnModule() falls back to role | ✅ After fix |
| 6 | Sidebar follows updated permission | ✅ Auto-updates |
| 7 | Direct route access follows permission | ✅ Auto-updates |
| 8 | Super-admin bypass preserved | ✅ Unaffected |

**Overall:** Fix is safe and correct. No architectural change needed.
