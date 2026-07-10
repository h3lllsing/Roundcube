# Phase 4: API/Web Visibility Alignment Fix Report

## Summary

Phase 4 changed 9 API CRUD controllers from user_id–based ownership scoping to module_id–based RBAC scoping, aligning them with Web controllers. The Dashboard and Export endpoints were also aligned.

## Changes Made

### 9 API Controllers — `index()` method
| Controller | File | Change |
|---|---|---|
| DomainController | `app/Http/Controllers/Api/DomainController.php` | `$filters['accessible_module_ids'] = $ids ?: [0]` replaces `$filters['user_id'] = $user->id` |
| HostingController | `app/Http/Controllers/Api/HostingController.php` | Same |
| VpsController | `app/Http/Controllers/Api/VpsController.php` | Same |
| VoipController | `app/Http/Controllers/Api/VoipController.php` | Same |
| ServiceProviderController | `app/Http/Controllers/Api/ServiceProviderController.php` | Same |
| DomainEmailController | `app/Http/Controllers/Api/DomainEmailController.php` | Same |
| OtherServiceController | `app/Http/Controllers/Api/OtherServiceController.php` | Same |
| ExpiryTrackerController | `app/Http/Controllers/Api/ExpiryTrackerController.php` | Same |
| AssetController | `app/Http/Controllers/Api/AssetController.php` | Same |

### Dashboard Service
- `app/Services/DashboardService.php`: `computeDashboardData()` queries changed from `where('user_id', ...)` to `whereIn('module_id', $accessibleModuleIds)` with empty-array fallback `whereRaw('1 = 0')`

### Export Controller
- `app/Http/Controllers/Api/ExportController.php`: `export()` uses `$user->getAccessibleModuleIds('export')` for records with `module_slug`, falls back to `user_id` ownership for personal records (VaultEntry, Note, Task)

### Model Fix — `user_id` restored to `$fillable`
- Phase 3B incorrectly removed `user_id` from `$fillable` in all 9 global record models, breaking ALL store() calls with `SQLSTATE[HY000]: General error: 1364 Field 'user_id' doesn't have a default value`
- **Fix**: Added `'user_id'` back to `$fillable` in Voip, Vps, Hosting, Domain, DomainEmail, ExpiryTracker, ServiceProvider, OtherService, and Asset models
- **Safety**: No form request accepts `user_id` as input — only trusted `Auth::id()` sets it

### Web Controller Fix
- Phase 3C removed `$validated['user_id'] = Auth::id()` from all 9 Web store() methods
- **Fix**: Re-added `$validated['user_id'] = Auth::id()` to Web DomainController, HostingController, VpsController, VoipController, ServiceProviderController, DomainEmailController, OtherServiceController, ExpiryTrackerController, AssetController

## Unchanged

- `show()` / `update()` / `destroy()` in API controllers — still use `$domain->user_id !== $user->id` ownership check (these are single-record lookups with route model binding)
- Personal modules (Vault, Tasks, Notes) — remain on ownership rules as intended
- All Web controllers — already use `RbacScope::apply()` via `userOwnedFilter()`, no changes needed

## Test Results

- 257 tests pass (500 assertions) across all affected test suites
- 10 tests were fixed to align with new module-scoping behavior
- No test regressions introduced
