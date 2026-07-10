# Patch 1.0.9-D — Password Reveal Permission & Security Hardening

## Root Cause

The `can_reveal` permission was functionally **incomplete** across the authorization chain — 4 gaps in 3 layers:

| # | Severity | Layer | Bug |
|---|----------|-------|-----|
| 1 | **CRITICAL** | API controller | `Api\VaultController::reveal()` used `canAccessVault()` which checks `can_read`, NOT `can_reveal` — users with `can_read` but `can_reveal=false` could still reveal passwords via API |
| 2 | **HIGH** | API validation | `StoreModulePermissionRequest` (used by API) did not include `can_reveal` in rules — could never be set to `true` |
| 3 | **HIGH** | API controller | `Api\ModulePermissionController::store()` omitted `can_reveal` from `$request->only()` — even if submitted, it was silently dropped |
| 4 | **HIGH** | Trait | `HasModulePermissions::getModulePermissions()` and `getAllModulePermissions()` excluded `can_reveal` from `$keys` — API auth responses (login, profile) never exposed `can_reveal` to clients |

The Web UI chain was **already correct** — Web controllers, Blade views, and `Web\ModulePermissionController::update()` all properly handled `can_reveal`. The gaps only existed in the API layer and trait.

## Files Changed

### `app/Http/Controllers/Api/VaultController.php` (line 166)
**Bug 1 fix**: Changed authorization from `canAccessVault()` to `canOnModule(..., 'reveal')`:
```php
// Before:
if (! $request->user()->canAccessVault($vault)) { abort(403, 'Forbidden'); }

// After:
abort_unless($user->hasRole('super-admin') || ($vault->module && $user->canOnModule($vault->module, 'reveal')), 403);
```
This matches the exact pattern used by all Web reveal controllers.

### `app/Http/Requests/StoreModulePermissionRequest.php` (line 26)
**Bug 2 fix**: Added `'can_reveal' => 'boolean'` to validation rules.

### `app/Http/Controllers/Api/ModulePermissionController.php` (line 96)
**Bug 3 fix**: Added `'can_reveal'` to `$request->only()` call.

### `app/Traits/HasModulePermissions.php` (lines 63, 91)
**Bug 4 fix**: Added `'can_reveal'` to the `$keys` array in both `getModulePermissions()` and `getAllModulePermissions()`:
```php
// Before:
$keys = ['can_create', 'can_read', 'can_update', 'can_delete', 'can_approve', 'can_export'];

// After:
$keys = ['can_create', 'can_read', 'can_update', 'can_delete', 'can_approve', 'can_export', 'can_reveal'];
```

### Tests added (+4 new test methods, +16 new assertions)

| File | Test | Coverage |
|------|------|----------|
| `tests/Feature/VaultTest.php` | `test_api_reveal_denied_without_can_reveal` | User with `can_read=true, can_reveal=false` → **403** on API reveal |
| `tests/Feature/VaultTest.php` | `test_api_reveal_allowed_with_can_reveal` | User with `can_read=true, can_reveal=true` → **200** with password |
| `tests/Feature/ModulePermissionTest.php` | `test_store_with_can_reveal_persists_permission` | API store sets `can_reveal=true` → persists in DB |
| `tests/Feature/ModulePermissionTest.php` | `test_store_with_can_reveal_false_sets_false` | API store sets `can_reveal=false` → updates correctly |
| `tests/Feature/UserModulePermissionTest.php` | `test_getModulePermissions_merges_user_overrides` | Now asserts `can_reveal` key present and override works |
| `tests/Feature/UserModulePermissionTest.php` | `test_getAllModulePermissions_includes_overrides` | Now asserts `can_reveal` key present with override value |
| `tests/Unit/HasModulePermissionsTraitTest.php` | `test_get_module_permissions_returns_array` | Now asserts `can_reveal` true when set |
| `tests/Unit/HasModulePermissionsTraitTest.php` | `test_get_all_module_permissions_returns_merged_permissions` | Now asserts `can_reveal` present in both modules |

## Permission Flow Diagram

```
API POST /vault/{id}/reveal
        │
        ▼
  Api\VaultController::reveal()
        │
        ├─ Super admin? ──────► YES ──► decrypt + return
        │
        ▼ NO
  hasRole('super-admin')? ───► YES ──► decrypt + return
        │
        ▼ NO
  $vault->module exists? ───► NO ──► 403 Forbidden
        │
        ▼ YES
  canOnModule($module, 'reveal')
        │
        ├─ 1. UserModulePermission override exists?
        │      ├─ YES + true  ──► decrypt + return
        │      ├─ YES + false ──► 403 Forbidden
        │      └─ YES + null ──► fall through to role
        │
        ▼ NO override (or null)
        │
        ├─ 2. Any role grants can_reveal=true?
        │      ├─ YES ──► decrypt + return
        │      └─ NO  ──► 403 Forbidden
        │
        ▼
  VaultService::reveal()
        ├─ Decrypt password
        ├─ Log activity(event='revealed')
        ├─ Dispatch VaultPasswordRevealed event
        └─ Fire vault.revealed webhook
        │
        ▼
  Return password in JSON response
```

## Changes by Module

| Module | Auth Before | Auth After | API Reveal? |
|--------|-------------|------------|-------------|
| **Vault (API)** | `canAccessVault()` (checked `can_read`) | `canOnModule('reveal')` + super-admin bypass | ✅ POST `/vault/{id}/reveal` |
| **Vault (Web)** | `canOnModule('reveal')` | ✅ already correct | N/A |
| **Hosting** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **VPS** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **ServiceProvider** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **DomainEmail** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **VoIP** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **OtherService** | `canOnModule('reveal')` | ✅ already correct | None (Web only) |
| **SmtpProfile** | No reveal endpoint | N/A | N/A |

## Activity Log Verification

Every successful reveal logs:
```
event: 'revealed'
performedOn: <model> (e.g. VaultEntry, Hosting, Vps, ...)
causedBy: <User>
properties: { type: '<module>_password' } or { service: '<name>' }
description: 'Password revealed for <Module>: <name>'
```

Failures are **not logged** (the `abort_unless()` triggers 403 before activity logging code executes).

The **password value itself NEVER appears** in activity log properties or description. ✅

## Security Audit

| Vector | Status |
|--------|--------|
| Passwords in JSON responses (list/show) | ✅ Hidden via `$hidden` / `password_masked` accessor |
| Passwords in reveal responses | ✅ Intentional (the user requested reveal) |
| Passwords in exceptions | ✅ No PII in exception messages |
| Passwords in debug output | ✅ Laravel debug ignores `$hidden` attributes |
| Passwords in validation errors | ✅ Validation never echoes submitted passwords |
| Direct URL access without permission | ✅ Returns 403 at controller level |
| API access without permission | ✅ Fixed (was using wrong check) |
| CSRF bypass on reveal | ✅ Web uses POST/GET with route; API uses Sanctum |

## Remaining Risks

1. **Reveal button visibility depends on `$entity->module` being loaded** — if the model's `module` relationship is null, the Blade check `($entity->module && canOnModule($entity->module, 'reveal'))` evaluates to false, hiding the button. This is correct behavior (no module = no permission), but could confuse users if module data is missing.
2. **No API reveal endpoints for non-vault modules** — Hosting, VPS, ServiceProvider, DomainEmail, VoIP, OtherService only have Web reveal endpoints (GET routes returning JSON). This limits API-driven automation but prevents the API bypass vector entirely.
3. **Super-admin bypass is explicit in every controller** — intentional by design (`hasRole('super-admin')`), but means super-admin permission changes via `can_reveal` have no effect on super-admin users.

## Test Results

| Metric | Before | After |
|--------|--------|-------|
| Total tests | 1921 | 1925 |
| Total assertions | 4862 | 4878 |
| Failures | 0 | 0 |
| New tests | — | 4 |
| New assertions | — | 16 |

## Manual Verification Steps

1. Login as super-admin → Open vault → Reveal password → Confirm password shown, activity log created
2. Login as user with `can_reveal=true` on vault module → Open vault → Confirm Reveal button visible → Click → Confirm password shown
3. Login as user with `can_reveal=false` on vault module → Open vault → Confirm Reveal button hidden → POST `/api/vault/{id}/reveal` → Confirm 403
4. Login as user with `can_read=true, can_reveal=false` → POST `/api/vault/{id}/reveal` → Confirm 403 (was previously 200 due to bug)
5. Super-admin → POST `/api/modules/{id}/permissions` with `can_reveal=true` → Confirm DB record has `can_reveal=1`
6. Verify `GET /api/my/module-permissions` response includes `can_reveal` key
