# Patch 1.0.7 — User Update Partial Save Fix

**Date:** 2026-06-27
**Status:** Complete
**Tests:** 1856 passed, 0 failed (4702 assertions)

---

## Problem

The User Edit form (`/users/{id}/edit`) forced Super Admin to fill unrelated fields when updating a user. Specifically:
- Password and confirmation were required on every update, even when only changing name/email/roles
- Roles were silently detached when the roles field was absent from the request

## Changes

### 1. `app/Http/Controllers/Web/UserController.php` — Roles Handling Fix

- **Line 336-350:** Removed the `else { $user->roles()->detach(); }` branch. When `roles` field is not present in the request, existing roles are now preserved (previously silently detached).
- When `roles` field IS present (including empty array `[]`), roles are synced as before.
- Validation already had `'password' => 'nullable|string|min:8|confirmed'` — password is optional on update.

### 2. `resources/views/users/edit.blade.php` — UI Labels

- Password field now shows: **"Leave blank to keep current password."** help text below.
- Confirm Password field now shows: **"Only required when setting a new password."** help text below.

### 3. `tests/Feature/UsersTest.php` — Updated & New Tests

**Updated:**
- `test_web_user_update_detach_roles`: Now sends explicit `'roles' => []` to detach (previously relied on absent field).

**New tests (9):**
- `test_update_name_only_without_password` — name change preserves password hash
- `test_update_email_only_without_password` — email change preserves password hash
- `test_update_roles_only_without_password` — roles update preserves password hash
- `test_update_suspend_user_without_password` — suspend works without touching password
- `test_blank_password_does_not_change_existing_hash` — blank password keeps existing hash
- `test_password_updates_only_when_provided_and_confirmed` — new password requires confirmation
- `test_password_confirmation_required_when_password_provided` — confirmation validation enforced
- `test_basic_update_does_not_change_permission_overrides` — overrides untouched by basic update
- `test_non_authorized_users_cannot_edit_users` — 403 enforced for non super-admin

## Behavior Summary

| Field | Present in Request | Behavior |
|---|---|---|
| `name` | Yes | Updated |
| `name` | No (API) or required (web) | Unchanged (API) / fails validation (web) |
| `email` | Yes | Updated if unique |
| `password` | Blank/null | **Not updated** — existing hash preserved |
| `password` | Present + confirmed | Hashed and updated |
| `password` | Present without confirmation | Validation error |
| `roles` | Array (including `[]`) | Synced |
| `roles` | Not present | **Not modified** (previous behavior: detached all) |
| `suspended_at` | Date string | Updated |
| `suspended_at` | Null/absent | Set to null (unsuspend) |
| `permissions` | Any | **Not affected** — managed only at `/users/{id}/permissions` |

## Safety Rules Preserved

- Cannot assign super-admin role through edit form (403)
- Cannot self-demote (remove own super-admin role)
- Deleting last super-admin still blocked
