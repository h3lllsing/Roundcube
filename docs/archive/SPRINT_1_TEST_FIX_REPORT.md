# Sprint 1 Test Fix Report

> Generated: 2026-07-04 | Mode: Closeout

## Summary

6 RBAC tests were failing due to the intentional permission model change (password reveal now gated by vault module `can_reveal` instead of service module `can_reveal`). All 6 tests have been updated.

## Changes

### `tests/Feature/RbacPhase2B3Test.php` — 3 tests fixed

| Test | Change |
|------|--------|
| `test_admin_without_can_reveal_denied_hosting_password` | Added `UserModulePermission` override denying `can_reveal` on vault module; changed hosting factory to use `hostingsModule` |
| `test_override_false_denies_reveal_when_role_allows` | Changed override target from `hostingsModule` to `vaultModule` |
| `test_denied_reveal_does_not_log_activity` | Added vault module deny override; changed hosting to `hostingsModule` |

### `tests/Feature/RbacPhase2C3Test.php` — 3 tests fixed

| Test | Change |
|------|--------|
| `test_show_button_hidden_on_show_when_can_reveal_false` | Added vault module deny override |
| `test_override_false_hides_reveal_buttons` | Changed override target from `vpsModule` to `vaultModule` |
| `test_server_side_reveal_guard_still_returns_403` | Added vault module deny override |

## Principle

All "denied" scenarios now deny `can_reveal` on the vault module rather than individual service modules. This matches the Sprint 1 permission model: **vault module `can_reveal` is the single gatekeeper for all password reveal/copy operations.**

## Verification

| Suite | Result |
|-------|--------|
| `RbacPhase2B3Test` (17 tests) | ✅ ALL PASS |
| `RbacPhase2C3Test` (14 tests) | ✅ ALL PASS |
| Broader run (296 tests) | ✅ ALL PASS |
| `npm run build` | ✅ PASS |
