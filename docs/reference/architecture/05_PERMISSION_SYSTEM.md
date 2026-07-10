# 5. Permission System

## Overview

The system uses a **custom module-level RBAC** built on top of Spatie Permission. The hierarchy is:

```
User
 ├── Roles (Spatie — assigned via model_has_roles)
 │    └── Permissions (Spatie — role_has_permissions, GENERIC perms)
 └── Module Permissions (Custom — user_module_permissions pivot)
      ├── can_read
      ├── can_create
      ├── can_update
      ├── can_delete
      └── special: can_reveal_vault, can_restore, can_force_delete
```

## Trait: `HasModulePermissions`

**File:** `app/Traits/HasModulePermissions.php`

This trait is applied to the `User` model and provides all authorization logic. Key methods:

### `canAccessModule(string $moduleName, string $permission): bool`
- Central permission check gate.
- If user is super admin → return `true` immediately.
- Look up module by name (slug).
- If user has a direct override in `user_module_permissions` → use that.
- Otherwise, check all user roles for the permission.
- Otherwise → `false`.

### `getModulePermissions(?Module $module): array|null`
- Returns an array of permission strings for a given module.
- Format: `['can_read', 'can_create', 'can_update', 'can_delete']` (plus specials).
- Null if no access at all.

### `modulePermissions(): HasMany` (relationship)
- Relationship to `user_module_permissions` pivot.

### Dead methods removed in Sprint B:
- `getModulePermissions(Module $module)` — static array return, no callers.
- `getUserModuleOverride(User $user, Module $module)` — unused query helper.

## Module Definitions

**File:** `app/Models/Module.php`

Modules are database-driven (not config). They have:
- `name` (display name)
- `slug` (used in permission checks)
- `description`

## Permission-to-Module Mapping

Modules and their slug-permission mapping are stored in `config/permissions.php`:

```php
'sensitive_modules' => [
    'vault', 'users', 'roles', 'activity-logs', 'login-audits',
],
'sensitive_permissions' => [
    'can_delete', 'can_restore', 'can_force_delete', 'can_reveal_vault',
],
```

The `sensitive_modules` array is used to flag modules that require elevated confirmation.
The `sensitive_permissions` array is used to flag permissions that require extra confirmations.

**Note:** The `presets` key was removed in Sprint B — it had zero references in the codebase.

## How Authorization Is Enforced

### In Controllers (Gate/Authorize)
Every controller uses `$this->authorize()` which invokes the `module_access` Gate defined in `AuthServiceProvider`:

```php
Gate::define('module_access', function (User $user, $moduleSlug, $permission) {
    return $user->canAccessModule($moduleSlug, $permission);
});
```

Called like:
```php
$this->authorize('module_access', ['domains', 'can_read']);
```

### In Blade Views
The `@can('module_access', ['domains', 'can_read'])` directive is used to conditionally show/hide UI elements.

**Important:** Hiding a UI element in the frontend does NOT prevent backend access. The backend always re-checks permissions on every request.

### In Services
`VaultService::reveal()` performs an explicit permission check:
```php
if (! $user->canAccessModule('vault', 'can_reveal_vault')) {
    abort(403);
}
```

## Super Admin Bypass

- Defined by `config('tyro.super_admin_email')`.
- `isSuperAdmin()` method on User model checks if user's email matches.
- All permission gates short-circuit to `true` for super admin.
- Ownership scoping also bypassed: super admin sees ALL records.

## Default Role Permissions

When a new user is created via seeding (`Database\Seeders\RolesAndPermissionsSeeder`), roles and their module permissions are seeded. Key roles:
- **Super Admin** — full access (bypasses gates, not by role but by email check).
- **Admin** — full read on all modules, write on most. No user/role/permission management.
- **User** — own records only, limited write.
- **Customer** — own records, create/read/update, no delete.

## Permission Inheritance & Override Rules

1. **Super admin → overrides everything.** No permission check is even evaluated.
2. **User has direct override** in `user_module_permissions` → override is used REPLACING (not merging with) role defaults for that module.
3. **User has no override** → all assigned roles are checked. If ANY role grants the permission, access is given.
4. **No override and no role grants** → denied.

## What Happens If a Module Has No Permission Entry

- If `canAccessModule()` finds no module with the given slug, it returns `false` (denied).
- Module slugs must match exactly. A bug was fixed in Sprint A where `'hosting'` was used instead of `'hostings'` in some checks.

## API Routes Permission

API routes use token-based auth (`config('tyro.api_token')`) and do NOT go through module permission gates. API access is all-or-nothing based on token validity.
