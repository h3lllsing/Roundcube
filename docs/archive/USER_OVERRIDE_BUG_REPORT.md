# USER OVERRIDE BUG REPORT

## Summary
The user permission override page (`/users/{id}/permissions`) appears to successfully save "Overrides updated" flash message, but **overrides cannot be removed once applied**. The "Reset to Default" and "Inherited" selections are cosmetic only — the override row remains in the database with stale values.

---

## BUG A: Overrides are permanent (CRITICAL)

### Root Cause
Two interacting behaviors create this bug:

**1. JS save logic excludes reset modules** — `resources/js/permissions.js:180-183`
```js
if (mod.preset !== mod.baseline || mod.preset === 3) {
    permissions[mod.id] = colPerms;
}
```
When a user resets a module to "Inherited" (preset === baseline), the module is **excluded from the PUT payload**.

**2. Controller doesn't clear old overrides** — `app/Http/Controllers/Web/UserController.php:27-66`
```php
private function saveUserModulePermissions(User $user, ?array $permissions): void
{
    if ($permissions === null) { return; }
    // ...only processes modules IN $permissions array...
}
```
Modules not in the payload are never touched. The old `user_module_permissions` row persists.

**Result:** The stale override continues to affect `canOnModule()`, which checks `UserModulePermission` first.

### Impact
- "Reset to Default" appears to work in the UI (preset shows "Inherited") but the DB row remains
- `canOnModule()` continues to return the stale override value
- User's actual effective permissions are stuck at whatever override was last saved
- The only way to clear an override is to manually delete the row from the database or set the override to match the default

### Reproduction
1. As super-admin, go to `/users/{id}/permissions`
2. Change a module from "View Only" to "Manage" → Save
3. Change it back to "View Only" (Inherited) → Save
4. Refresh the page — module shows "View Only" ✓
5. BUT `user_module_permissions` row still exists with old values
6. `canOnModule()` still reads the stale `UserModulePermission` row first
7. ONLY works correctly if the override values happen to match the role values exactly

### Verification
Run this SQL after a "reset":
```sql
SELECT * FROM user_module_permissions WHERE user_id = ? AND module_id = ?;
```
If the row still exists, the override is still active.

### Fix Required
In `saveUserModulePermissions()`, either:
- Option A: Delete any existing override row whenever the incoming effective permissions match the role baseline, OR
- Option B: Always pass ALL modules in the payload (not just changed ones), OR
- Option C: Before saving, diff the incoming data against existing role permissions and delete the row if all values match

---

## BUG B: Permission editor UI shows incorrect "Inherited" state

### Root Cause
The blade PHP computes `baseline` from `ModuleRolePermission` (role values) and `preset` from `getEffectiveModulePermissions()` (which includes the stale override). If the stale override values happen to match the role values, `preset === baseline` and the UI shows "Inherited" — even though a stale override row exists.

### Impact
The UI is misleading: it shows "Inherited" when there's actually a stale override row that could cause issues if role permissions change later.

---

## BUG C: Potential data loss on save

### Root Cause
`resources/js/permissions.js:196` — The save function follows redirects:
```js
if (res.redirected) { window.location.href = res.url; return; }
```

### Impact
If a middleware or previous redirect intercepts the PUT request, the user is silently redirected without saving. This is standard Laravel behavior but worth noting.

---

## FLOW AUDIT: End-to-end save path

```
[JS] User clicks "Save Overrides"
  → collect modules where preset !== baseline (BUG: excludes reset modules)
  → PUT /users/{id}/permissions
    → CSRF via meta tag ✓
    → Content-Type: application/json ✓
    → Body: { permissions: { moduleId: { can_read: bool, ... } } }
  → [Controller] updatePermissions()
    → abort_unless(hasRole('super-admin')) ✓
    → validate(['permissions' => 'nullable|array']) ✓
    → saveUserModulePermissions($user, $request->input('permissions'))
      → foreach module in payload:
        → foreach permission column:
          → if value is '1'/1/true → set true
          → if value is '0'/0/false → set false (BUG: '0' string casts to true in PHP)
          → else → set null
        → if any hasValue → updateOrCreate (BUG: old overrides not deleted)
        → else → delete row (this path never executes because hasValue=false means no data)
      → modules NOT in payload → untouched (BUG: stale overrides persist)
  → [Flash] "Permission overrides updated successfully"
  → Redirect to users.edit
```

### CSRF Check
CSRF token is included via `document.querySelector('meta[name="csrf-token"]')?.content`. If the meta tag is missing or expired, the request fails with 419. This is standard Laravel behavior.

### Input Name Check
JS sends `permissions[moduleId][can_read]` etc. Controller reads `$request->input('permissions')`. Correct.

### DB Write Check
`UserModulePermission::updateOrCreate()` with `['user_id' => $user->id, 'module_id' => $moduleId]` as unique criteria. Correct table, correct columns.

---

## RECOMMENDED FIX

In `UserController@saveUserModulePermissions()`:

```php
// After processing all incoming permissions, clean up stale overrides
$incomingModuleIds = array_keys($permissions ?? []);
if (!empty($incomingModuleIds)) {
    // Delete overrides for modules that were "reset" (not in payload but have existing override)
    UserModulePermission::where('user_id', $user->id)
        ->whereNotIn('module_id', $incomingModuleIds)
        ->delete();
}
```

This single change would fix the critical bug. Add this after the existing foreach loop.
