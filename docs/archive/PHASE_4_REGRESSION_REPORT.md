# Phase 4: Regression Report

## Status: ✅ NO REGRESSIONS

All 257 tests pass (500 assertions) across the following test suites:

| Test Suite | Tests | Status |
|---|---|---|
| DomainTest | 15 | ✅ PASS |
| HostingTest | 16 | ✅ PASS |
| VpsTest | 20 | ✅ PASS |
| VoipTest | 21 | ✅ PASS |
| ServiceProviderTest | 18 | ✅ PASS |
| DomainEmailTest | 14 | ✅ PASS |
| OtherServiceTest | 14 | ✅ PASS |
| ExpiryTrackerTest | 15 | ✅ PASS |
| AssetManagementTest | 53 | ✅ PASS |
| RbacPhase2B2Test | 44 | ✅ PASS |
| RbacPhase2B1Test | 13 | ✅ PASS |
| RbacPhase1Test | 12 | ✅ PASS |
| SecurityFixesTest | 20 | ✅ PASS |
| ExampleTest | 7 | ✅ PASS |
| DashboardTest | 2 | ✅ PASS |

## Regressions Fixed During Phase 4

### Regression 1: `user_id` not in `$fillable` (pre-existing from Phase 3B)
- **Cause**: Phase 3B removed `user_id` from `$fillable` in all 9 models, but store() methods still pass `$validated['user_id']` — mass assignment strips it, NOT NULL constraint fails
- **Fix**: Added `'user_id'` back to `$fillable` in all 9 models
- **Safe because**: No form request accepts `user_id`; only `Auth::id()` sets it

### Regression 2: Web store() missing `user_id` (pre-existing from Phase 3C)
- **Cause**: Phase 3C removed `$validated['user_id'] = Auth::id()` from Web store() methods
- **Fix**: Re-added the assignment to all 9 Web controllers

### Regression 3: API list tests expected user_id scoping (pre-existing from Phase 4 change)
- **Cause**: Phase 4 changed API index() from user_id to module_id scoping, but tests still expected ownership-based filtering
- **Fix**: Updated 8 tests to create records in two modules — one accessible to the test user, one not

### Regression 4: RBAC test used wrong module for permission check
- **Cause**: `test_admin_without_can_create_denied_store` posted to `/domains` expecting 403, but admin HAS can_create on domains — posted `module_id => otherModule->id` but Web controller ignores request module_id, uses its own slug
- **Fix**: Changed to post to `/other-services` where admin genuinely lacks can_create

## Test Fixes Summary

| File | Tests Changed | Reason |
|---|---|---|
| `DomainTest.php` | 1 | Added module setup for new scoping |
| `HostingTest.php` | 1 | Added module setup for new scoping |
| `VpsTest.php` | 1 | Added module setup for new scoping |
| `VoipTest.php` | 1 | Added module setup for new scoping |
| `ServiceProviderTest.php` | 1 | Added module setup for new scoping |
| `DomainEmailTest.php` | 1 | Added module setup for new scoping |
| `OtherServiceTest.php` | 1 | Added module setup for new scoping |
| `ExpiryTrackerTest.php` | 1 | Added module setup for new scoping |
| `RbacPhase2B2Test.php` | 2 | Changed to test actual denial path |
