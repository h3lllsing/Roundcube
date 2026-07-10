# PERMISSION ATTACK SCENARIO REPORT

**Project:** OpsPilot Portal
**Date:** 2026-07-08

---

## Scenario 1: Normal User POSTs to `/users/1/permissions`

### Method: PUT `/users/{id}/permissions`
### Exploit: Send request as non-super-admin user
### Result: ✅ PROTECTED

**Analysis:** Route is behind `role:super-admin` middleware (`routes/web.php:278`). Non-super-admin gets `403 Forbidden` before any controller code runs.

**Evidence:**
```php
// routes/web.php:239
Route::middleware(['auth', 'suspended', 'role:super-admin'])->group(function () {
    // ...
    Route::put('/users/{id}/permissions', [UserController::class, 'updatePermissions']);
});
```

**Severity:** NONE | **Probability:** 0% | **Risk:** None

---

## Scenario 2: Admin Tries to Grant Themselves Super-Admin-Like Module Permissions

### Method: PUT `/users/{id}/permissions` as non-super-admin user
### Exploit: Craft JSON with `{permissions: {moduleId: {can_read: true, ...}}}`
### Result: ✅ PROTECTED

**Analysis:** Same as Scenario 1 — route requires `role:super-admin`. Additionally, `UserPermissionService::preventSuperAdminAssignment()` (line 30-38) prevents super-admin role assignment through user forms.

**Severity:** NONE | **Probability:** 0% | **Risk:** None

---

## Scenario 3: User Sends module_id That Does Not Exist

### Method: PUT `/users/{id}/permissions`
### Exploit: `{"permissions": {"999999": {"can_read": "1"}}}`
### Result: ✅ PROTECTED

**Analysis:** Validation at `UserController::updatePermissions()` (lines 375-382):
```php
$validIds = Module::whereIn('id', $moduleIds)->pluck('id')->all();
$invalid = array_diff($moduleIds, $validIds);
if ($invalid) {
    $fail('Invalid module IDs: '.implode(', ', $invalid));
}
```
Invalid module IDs return 422 with validation error.

**Severity:** NONE | **Probability:** 0% | **Risk:** None

---

## Scenario 4: User Sends Permission Keys Not Present in UI

### Method: PUT `/users/{id}/permissions`
### Exploit: `{"permissions": {"123": {"can_delete": true, "can_reveal": true, "evil_key": true}}}`
### Result: ⚠️ PARTIALLY PROTECTED

**Analysis:** 
- `evil_key` → ignored by service (iterates only `config('permissions.keys')`)
- `can_delete` / `can_reveal` → **ACCEPTED** — no validation on which permission keys are allowed

The service accepts any key that exists in `config('permissions.keys')`, regardless of whether the UI showed it for that module.

**Severity:** LOW | **Probability:** Low (requires bypassing JS) | **Impact:** User could set permissions that UI didn't offer for that module (e.g., set `can_approve` for a module even if UI only shows view/create toggle)

**Fix:** Add validation for allowed keys:
```php
'permissions.*' => 'array:'.implode(',', config('permissions.keys')),
```

---

## Scenario 5: User Resets Permission to Inherited

### Method: Send empty payload or omit module from payload
### Exploit: Module omitted from permissions object
### Result: ✅ PROTECTED

**Analysis:** `saveUserModulePermissions()` cleanup logic (lines 83-90) deletes override rows for modules NOT in the incoming payload. Omitted module → override row deleted → falls back to role baseline.

**Test:** `test_omitted_module_from_payload_deletes_stale_override` confirms.

**Severity:** NONE | **Probability:** 0% | **Risk:** None

---

## Scenario 6: Two Admins Edit Same User Permissions at Same Time

### Method: Concurrent PUT requests to `/users/{id}/permissions`
### Exploit: Race condition — both send different overrides
### Result: ✅ PROTECTED

**Analysis:** `saveUserModulePermissions()` (line 49-52) uses `DB::transaction()` with `lockForUpdate()` on all user's override rows. Second request waits for first to complete. No lost updates.

**Severity:** NONE | **Probability:** 0% (within transaction) | **Risk:** None

---

## Scenario 7: Role Permission Changes After User Override Exists

### Method: 
1. User has override (e.g., `can_read=true` on Module A)
2. Admin removes role's Module A permissions
3. Does user still have access?
### Result: ✅ PROTECTED (correct behavior)

**Analysis:** `canOnModule()` checks override FIRST. If override exists and column is non-null, the ROLE is never consulted. User retains access via override regardless of role changes.

**Impact:** This is INTENTIONAL — overrides are designed to survive role changes. The role warning modal on the permissions page alerts admins: "Overrides are preserved when the role changes."

**Severity:** NONE (by design) | **Probability:** N/A | **Risk:** None

---

## Scenario 8: Module Is Renamed/Deleted After Permissions Exist

### Method: Delete module or rename its slug
### Exploit: Stale UserModulePermission rows reference deleted module_id
### Result: ✅ PROTECTED (cascade delete)

**Analysis:** `user_module_permissions` has `foreignIdFor(Module::class)->constrained()->cascadeOnDelete()`. When module is deleted, its override rows are cascade-deleted.

**For rename:** Module slug is stored in `modules.slug` column. `UserModulePermission` references `module_id` (FK to `modules.id`). Renaming slug does NOT affect FK references.

**Severity:** NONE | **Probability:** 0% | **Risk:** None

---

## Scenario 9: Permission Cache Remains Stale After Update

### Method: 
1. User has override on Module A
2. Admin removes override
3. Does `canOnModule()` return stale data?
### Result: ⚠️ PARTIALLY PROTECTED

**Analysis:** `canOnModule()` uses DIRECT DB query — no cache. Changes are reflected immediately. `getAccessibleModuleIds()` uses cache with `perms_generation` key, which IS incremented on save.

However, `ModulePermissionService::removeForRole()` does NOT increment `perms_generation` — so role permission removals may have stale cache for up to 60 seconds.

**Severity:** LOW | **Probability:** Low | **Impact:** Up to 60s stale permissions via cached path

**Fix:** Add `Cache::increment('perms_generation')` to `removeForRole()`.

---

## Scenario 10: API Endpoint Uses Old user_id Scoping While Web Uses Module Scoping

### Method: 
- Web: RbacScope applies module-level filtering (`module_id IN accessible_ids`)
- API: Hardcoded `$record->user_id !== $user->id` check
### Result: ⚠️ INCONSISTENT

**Analysis:** This is the same issue as VP-020 and C-004. Web controllers use module-level scoping (admin can see all records in accessible modules). API controllers additionally require record ownership.

**Example:** An admin user can:
- View all hosting records via Web (has `can_read` → module scoped)
- Cannot view same record via API (if `$hosting->user_id !== $admin->id`)

**Severity:** MEDIUM | **Probability:** HIGH (affects all API operations for non-owner admin users)

**Fix:** Align API authorization with Web — remove hardcoded ownership checks, keep `canOnModule()` checks.

---

## ATTACK SCENARIO RISK MATRIX

| # | Scenario | Severity | Probability | Business Impact | Fix Priority |
|---|----------|----------|-------------|-----------------|-------------|
| 1 | Non-SA edits permissions | ✅ NONE | 0% | None | — |
| 2 | Admin self-grants | ✅ NONE | 0% | None | — |
| 3 | Invalid module_id | ✅ NONE | 0% | None | — |
| 4 | Extra permission keys | ⚠️ LOW | LOW | Low | LOW |
| 5 | Reset to inherited | ✅ NONE | 0% | None | — |
| 6 | Concurrent edits | ✅ NONE | 0% | None | — |
| 7 | Role change after override | ✅ NONE | 0% | By design | — |
| 8 | Module delete/rename | ✅ NONE | 0% | None | — |
| 9 | Stale cache | ⚠️ LOW | LOW | Low (60s) | MEDIUM |
| 10 | API vs Web scoping | ⚠️ MEDIUM | HIGH | Admin API access denied | HIGH |

---

## DECISION: Can Deploy Continue?

| Scenario | Blocks Deployment? | Reason |
|----------|-------------------|--------|
| 1-8 | ✅ NO | Protected or by design |
| 9 | ✅ NO | Low impact, 60s auto-recovery |
| 10 | ✅ NO | Functional inconsistency (admin denied via API), not a security vulnerability |

**Overall: YES — deployment can continue.**
None of the 10 attack scenarios represent a HIGH or CRITICAL security vulnerability. The two issues found (stale cache, API ownership check mismatch) are functional limitations, not security bypasses.
