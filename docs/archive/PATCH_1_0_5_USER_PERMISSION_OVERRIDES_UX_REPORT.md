# Patch 1.0.5 — User Edit Permission Overrides UX

> **Version 1.0 — Release Freeze**
> **Date:** 2026-06-27
> **Status:** All 1847 tests passing, 0 failures

---

## 1. Problem

The User Edit page (`resources/views/users/edit.blade.php`) displayed the full 8-column permission matrix for every module directly on the edit form. This created a very long, overwhelming page that mixed basic user fields (name, email, password) with granular permission controls.

---

## 2. Changes Applied

### 2.1 Simplified Edit Page

**File:** `resources/views/users/edit.blade.php`

The full permission matrix table was removed and replaced with a compact **Permission Overrides** card:

- Shows override count (e.g., "12 overrides configured" or "No overrides configured")
- Shows appropriate subtitle text based on override status
- **Configure Overrides** button → links to new `/users/{id}/permissions` page
- **View Effective Permissions** button → links to existing `/users/{id}` show page

Remaining fields on the edit page:
- Name, Email, New Password, Confirm Password, Suspended At, Roles

### 2.2 New Permission Override Page

**File:** `resources/views/users/permissions.blade.php` (new)

Full permission matrix moved to a dedicated page at `/users/{id}/permissions`:

- Breadcrumb: Dashboard → Users → Edit User → Permission Overrides
- User summary card (avatar, name, email, roles, status)
- Explanation text: "Leave as — (Inherit) to use role defaults"
- **Search input** — filter modules by name
- **Category filter** — filter by feature/category name
- **Show only overridden** toggle — hide modules with no overrides
- Full permission matrix table with sticky header
- Dynamic row filtering via JavaScript (instant client-side)
- Row `data-has-override` attribute updates live when selects change
- Save + Cancel buttons

### 2.3 Controller Changes

**File:** `app/Http/Controllers/Web/UserController.php`

| Method | Change |
|--------|--------|
| `edit()` | Simplified — removed `modules` and `userOverrides` loading, added `overrideCount` |
| `editPermissions()` | **New** — loads modules, user overrides, categories for the override page |
| `updatePermissions()` | **New** — validates and saves permission overrides via `saveUserModulePermissions()` |
| `update()` | Removed `permissions` validation rule and `saveUserModulePermissions()` call — basic user info updates no longer touch overrides |

### 2.4 Routes

**File:** `routes/web.php`

```php
Route::get('/users/{id}/permissions', [UserController::class, 'editPermissions'])->name('users.permissions.edit');
Route::put('/users/{id}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
```

Both routes are protected by the same `super-admin` guard as all other user management.

### 2.5 RBAC Logic Preserved

No changes were made to:
- `canOnModule()` method
- `getEffectiveModulePermissions()` method
- `saveUserModulePermissions()` helper (reused as-is)
- `ModuleRolePermission` model
- `UserModulePermission` model
- `HasModulePermissions` trait
- Any permission calculation logic

---

## 3. Tests

### 3.1 New Tests Added (`BetterCreateUserTest.php`)

| # | Test | Description |
|---|------|-------------|
| 11 | `test_edit_page_shows_override_card_not_matrix` | Confirms the edit page shows the card and does NOT show "Special Per-User Permissions" |
| 12 | `test_configure_overrides_link_goes_to_permissions_page` | Confirms the Configure Overrides link points to the new route |
| 13 | `test_permission_override_page_loads` | Confirms the override page renders with module permissions, save button, user info |
| 14 | `test_saving_user_info_preserves_overrides` | Confirms updating name/email does not touch existing overrides |
| 15 | `test_saving_overrides_updates_permissions` | Confirms the override page saves permission values |
| 16 | `test_non_super_admin_cannot_access_override_page` | Confirms 403 for unauthorized users on both GET and PUT |
| 17 | `test_saving_overrides_clears_previous_values` | Confirms all-null override values delete the row (fallback to role) |

### 3.2 Existing Tests Updated (`UserModulePermissionTest.php`)

| Test | Change |
|------|--------|
| `test_super_admin_can_update_overrides_through_user_edit` | Renamed to `...through_permissions_page`, now uses `route('users.permissions.update')` |
| `test_super_admin_can_delete_overrides_through_user_edit` | Renamed to `...through_permissions_page`, now uses `route('users.permissions.update')` |

### 3.3 Results

```
Tests:  1847 passed (4680 assertions)
Failures: 0
```

---

## 4. Files Modified/Created

| # | File | Action |
|---|------|--------|
| 1 | `resources/views/users/edit.blade.php` | Modified — simplified, removed matrix, added override card |
| 2 | `resources/views/users/permissions.blade.php` | Created — full permission matrix page with search/filter |
| 3 | `app/Http/Controllers/Web/UserController.php` | Modified — simplified edit(), added editPermissions() and updatePermissions() |
| 4 | `routes/web.php` | Modified — added 2 new permission override routes |
| 5 | `tests/Feature/BetterCreateUserTest.php` | Modified — added 7 new tests |
| 6 | `tests/Feature/UserModulePermissionTest.php` | Modified — updated 2 tests to use new endpoint |

---

## 5. Verification Checklist

- [x] Edit page shows Name, Email, Password, Suspended At, Roles
- [x] Edit page does NOT show permission matrix
- [x] Edit page shows Permission Overrides card with override count
- [x] Configure Overrides button links to `/users/{id}/permissions`
- [x] View Effective Permissions button links to `/users/{id}`
- [x] Permission override page loads with user summary + matrix
- [x] Search input filters modules instantly
- [x] Category filter filters by feature name
- [x] "Show only overridden" toggle works
- [x] Saving overrides updates `user_module_permissions` table
- [x] Saving basic user info does NOT modify overrides
- [x] Non-authorized users get 403 on override page
- [x] All existing RBAC behavior preserved
- [x] All 1847 tests pass

---

*End of report.*
