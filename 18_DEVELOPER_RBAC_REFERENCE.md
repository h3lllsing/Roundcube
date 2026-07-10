# Developer RBAC Reference

> **Audience:** Developers
>
> **INTERNAL DOCUMENT — Technical implementation details.**

## Table of Contents

- [Overview](#overview)
- [Core Files](#core-files)
- [RbacScope Usage](#rbacscope-usage)
- [Permission Checking](#permission-checking)
- [Route Protection](#route-protection)
- [Database Schema](#database-schema)
- [Seeder Reference](#seeder-reference)
- [Testing](#testing)

---

## Overview

This document provides technical details of the RBAC implementation for developers working on OpsPilot.

## Core Files

| File | Purpose |
|------|---------|
| `app/Helpers/RbacScope.php` | Global Eloquent scope that applies visibility rules |
| `app/Traits/HasModulePermissions.php` | User model trait with permission-checking methods |
| `app/Models/ModuleRolePermission.php` | Role × Module permission pivot model |
| `app/Models/UserModulePermission.php` | Per-user permission overrides model |
| `database/seeders/RolePermissionSeeder.php` | Default role permission seeding |
| `database/seeders/RoleTemplateSeeder.php` | Role template definitions |
| `routes/web.php` | Route definitions with middleware |

## RbacScope Usage

### Method Signature

```php
RbacScope::apply(string $modelClass, string $visibility = 'ownership'): void
```

### Visibility Modes

**`'module'` — Module-Wide Access**
```php
// Used in 9 operational controllers
RbacScope::apply(Model::class, 'module');

// Logic (simplified):
if user is super-admin → no scope applied
if user has can_read on any module → WHERE module_id IN (accessible IDs)
if user has no can_read on any module → WHERE 1 = 0 (no records)
```

**`'ownership'` — Personal Access (default)**
```php
RbacScope::apply(Model::class);
// or just: RbacScope::apply(Model::class);

// Logic (simplified):
if user is super-admin → no scope applied
if user is admin → WHERE module_id IN (accessible IDs from can_read)
else → WHERE user_id = current user
```

### Controllers Using Module Visibility

```php
// Operational controllers — constructor or index method:
public function __construct()
{
    RbacScope::apply(Domain::class, 'module');
}
```

Affected controllers:
- `DomainController`
- `HostingController`
- `VpsController`
- `VoipController`
- `ServiceProviderController`
- `DomainEmailController`
- `OtherServiceController`
- `ExpiryTrackerController`
- `AssetController`

### Controllers Using Custom Scope

- **NoteController** — Custom `userOwnedFilter()`: module-attached notes inherit module visibility, global notes remain personal.
- **TaskController** — Custom `userOwnedFilter()`: Super Admin unrestricted; Admin: tasks where `module_id IN (accessible IDs)` OR assigned to user; Others: tasks where `created_by = user` OR assigned to user.

## Permission Checking

### `canOnModule(string $module, string $action): bool`

```php
if ($user->canOnModule($module, 'update')) {
    // allow action
}
```

Priority:
1. Check `UserModulePermission` override for this user + module
2. If override exists and is not null → return override value
3. Check `ModuleRolePermission` for user's roles + module
4. If any role grants the permission → return true
5. Otherwise → return false

### Controller Gate Pattern

```php
private function denyIfNotSuperAdminOrCanCreate(Module $module): void
{
    $user = Auth::user();
    if ($user->hasRole('super-admin')) return;
    abort_unless($user->canOnModule($module, 'create'), 403);
}
```

This pattern is used for create, update, delete, and reveal actions.

### `getAccessibleModuleIds(string $action): array`

Returns array of module IDs where the user has the specified permission (considering role permissions and user overrides).

### `getEffectiveModulePermissions(Module $module): array`

Returns detailed breakdown per permission:
```php
[
    'can_read' => [
        'role' => true,
        'user_override' => null,
        'effective' => true,
        'source' => 'Role',
    ],
    // ...
]
```

## Route Protection

### Middleware Groups

```php
// Guest routes (login, register, forgot password)
Route::middleware('guest')->group(function () { ... });

// Authenticated routes (all application pages)
Route::middleware(['auth', 'suspended'])->group(function () { ... });

// Super Admin only routes
Route::middleware(['auth', 'suspended', 'role:super-admin'])->group(function () { ... });
```

### Rate Limiting

| Route | Limit | Middleware |
|-------|-------|------------|
| Login | 5 per minute | `throttle:5,1` |
| Password reset email | 5 per minute | `throttle:5,1` |
| Password reveal | 10 per minute | `throttle:10,1` |
| Export | Throttled | `throttle:export` |
| Search | Throttled | `throttle:search` |
| Bulk action | Throttled | `throttle:bulk` |

## Database Schema

### ModuleRolePermissions

| Column | Type | Description |
|--------|------|-------------|
| `module_id` | FK → modules | The module |
| `role_id` | FK → roles | The role |
| `can_create` | boolean | Create records |
| `can_read` | boolean | View records |
| `can_update` | boolean | Edit records |
| `can_delete` | boolean | Delete records |
| `can_approve` | boolean | Approve (future) |
| `can_export` | boolean | Export data |
| `can_reveal` | boolean | View passwords |

### UserModulePermissions

| Column | Type | Description |
|--------|------|-------------|
| `user_id` | FK → users | The user |
| `module_id` | FK → modules | The module |
| `can_create` | boolean? | Override (null = inherit) |
| `can_read` | boolean? | Override (null = inherit) |
| `can_update` | boolean? | Override (null = inherit) |
| `can_delete` | boolean? | Override (null = inherit) |
| `can_approve` | boolean? | Override (null = inherit) |
| `can_export` | boolean? | Override (null = inherit) |
| `can_reveal` | boolean? | Override (null = inherit) |

## Seeder Reference

### RolePermissionSeeder

Sets default permissions for 4 roles across all modules:

| Role | create | read | update | delete | approve | export | reveal |
|------|--------|------|--------|--------|---------|--------|--------|
| admin | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| customer | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| editor | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| user | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

Also assigns `user` role to `test@example.com`.

### RoleTemplateSeeder

Defines 4 templates with detailed per-module permissions:

- **Super Admin**: All modules, all permissions = true
- **Admin**: Infrastructure = create, read, update, export, reveal (no delete). Productivity = truePerms (no delete/export/reveal). Admin/Integration = read only on specific modules.
- **IT Support**: 6 modules only = create, read, update, reveal. All others = deny.
- **Read Only**: Selected modules = read only. All others = deny.

### DemoDataSeeder

Creates `admin@tyro.project` with `super-admin` role and `test@example.com` user. Password is `password`.

## Adding a New Module

1. Create model + migration + controller
2. Add routes in `routes/web.php` (auth middleware, plus super-admin for admin actions)
3. Apply `RbacScope::apply(Model::class, 'module')` in controller constructor
4. Add gate checks: `denyIfNotSuperAdminOrCanCreate/Update/Delete/Reveal()`
5. Add bulk action support in `BulkActionService.php` (`$ownedTypes` array)
6. Add to `RoleTemplateSeeder` if needed
7. Add to `GlobalSearchService.php` ownership modes
8. Add to CalendarController if it has expiry dates
9. Run `php artisan db:seed` to regenerate permissions

## Testing

### Test User Setup

```php
// Super Admin
$sa = User::where('email', 'admin@tyro.project')->first();

// Regular user with 'user' role
$user = User::where('email', 'test@example.com')->first();
```

### Permission Assertions

```php
// Assert specific permission
$this->assertTrue($user->canOnModule($module, 'create'));

// Assert denied
$this->assertFalse($user->canOnModule($module, 'delete'));

// Assert 403 for route access
$response = $this->actingAs($user)->get('/users');
$response->assertForbidden();
```

---

## Related Modules

- [Architecture Overview](17_ARCHITECTURE_OVERVIEW.md)
- [Permission Reference](08_PERMISSION_REFERENCE.md) — User-facing permission behavior
