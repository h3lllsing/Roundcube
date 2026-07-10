# Sprint 1 Preflight Report

> Generated: 2026-07-04 | Mode: Preflight

## Summary

| Check | Status | Detail |
|-------|--------|--------|
| Password casts encrypted (Hosting) | ✅ PASS | `'password' => 'encrypted'` at `app/Models/Hosting.php:30` |
| Password casts encrypted (VPS) | ✅ PASS | `'password' => 'encrypted'` at `app/Models/Vps.php:34` |
| Password casts encrypted (VoIP) | ✅ PASS | `'password' => 'encrypted'` at `app/Models/Voip.php:31` |
| Password casts encrypted (OtherService) | ✅ PASS | `'password' => 'encrypted'` at `app/Models/OtherService.php:31` |
| Domain has no password column | ✅ PASS | `Domain::$fillable` has no `password` field. No `$hidden` for password. No `'password'` cast. |
| Vault module slug | ✅ PASS | `VaultController::moduleSlug()` returns `'vault'` |
| Vault reveal route exists | ✅ PASS | `POST /vault/{id}/reveal` named `vault.reveal` with throttle:10,1 |
| User detail page controller | ✅ PASS | `UserController@show` at `app/Http/Controllers/Web/UserController.php:264` |
| User detail page view | ✅ PASS | `resources/views/users/show.blade.php` |
| `activity_log` has `causer_id`/`causer_type` | ✅ PASS | `nullableMorphs('causer')` in migration |
| `users.suspended_at` exists | ✅ PASS | Added by migration `2026_05_24_000002_add_suspended_and_softdeletes_to_users.php` |
| `users.suspension_reason` exists | ❌ FAIL | Column `suspension_reason` does NOT exist in any migration or model |

## BLOCKER Assessment

**No BLOCKERS for implementation.** However:

1. **`suspension_reason` missing**: The `users` table has `suspended_at` but no `suspension_reason` column. Per Sprint rules, the Offboarding Checklist will be implemented **without** the suspend button. A `NEEDS REVIEW` note is shown instead.

2. **Password encryption**: All 4 service models use Laravel's `'encrypted'` cast (which uses AES-256-CBC via `Illuminate\Support\Facades\Crypt`). Existing controllers already decrypt via attribute access. No BLOCKER.

## Excluded Scope

- **Domains**: Excluded from Auto-Copy. No password column. Structural limitation, not configurable.
- **Service Providers, Domain Emails**: Have password routes but are not in Sprint 1 scope.
- **Extension password (VoIP)**: Included in scope since it's a password field on the VoIP model.

## Go / No-Go

**GO** — Implement both features. Offboarding Checklist without suspend button.
