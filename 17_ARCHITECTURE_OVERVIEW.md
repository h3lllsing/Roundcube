# Architecture Overview

> **Audience:** System Administrators, Developers
>
> **INTERNAL DOCUMENT — Not for end users.**

## Table of Contents

- [Technology Stack](#technology-stack)
- [Application Architecture](#application-architecture)
- [Directory Structure](#directory-structure)
- [Key Components](#key-components)
- [Data Flow](#data-flow)
- [Authentication Flow](#authentication-flow)
- [Permission Check Flow](#permission-check-flow)

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Framework** | Laravel (PHP) |
| **Database** | MySQL / PostgreSQL |
| **Frontend** | Blade templates, JavaScript |
| **Authentication** | Laravel Auth with session-based login |
| **Authorization** | Custom RBAC (Role-Based Access Control) |
| **Activity Logging** | Spatie Activitylog |
| **Caching** | Laravel Cache (file/database/redis) |

## Application Architecture

OpsPilot follows the standard Laravel MVC (Model-View-Controller) architecture with service-layer separation.

```
Request → Route → Middleware → Controller → Service (optional) → Model → Database
                                                      ↓
                                                 Response (View/Redirect)
```

### Middleware Stack

1. **`guest`** — Unauthenticated routes (login, register, password reset)
2. **`auth`** — Authenticated routes (most application pages)
3. **`suspended`** — Checks if user account is suspended
4. **`role:super-admin`** — Restricts routes to Super Admin only
5. **`throttle:X,Y`** — Rate limiting

## Directory Structure (Key Areas)

```
app/
├── Console/           # Artisan commands
├── Dashboard/         # Dashboard widget classes
│   ├── OperationsWidget.php
│   ├── RenewalsWidget.php
│   ├── TasksWidget.php
│   ├── AssetsWidget.php
│   ├── ActivityWidget.php
│   ├── VaultWidget.php
│   ├── QuickActionsWidget.php
│   ├── ServerHealthWidget.php
│   └── SmtpWidget.php
├── Exports/           # Export classes
├── Helpers/
│   └── RbacScope.php  # Core RBAC scoping engine
├── Http/
│   ├── Controllers/Web/  # Web controllers (one per module)
│   └── Requests/         # Form request validation
├── Imports/           # Import classes
├── Models/            # Eloquent models
├── Services/          # Business logic services
│   ├── BulkActionService.php
│   ├── GlobalSearchService.php
│   ├── MonitorService.php
│   ├── ReportService.php
│   └── TaskService.php
└── Traits/
    └── HasModulePermissions.php  # User permission methods
database/
├── migrations/        # Database schema
└── seeders/           # Demo data and default configuration
routes/
└── web.php           # All route definitions
```

## Key Components

### RBAC System

The RBAC system consists of these interacting components:

1. **Roles** — Defined in the `roles` table (via HasinHayder/Tyro package)
2. **ModuleRolePermissions** — Role × Module permission matrix (`can_create`, `can_read`, `can_update`, `can_delete`, `can_approve`, `can_export`, `can_reveal`)
3. **UserModulePermissions** — Per-user permission overrides
4. **RbacScope** — Global Eloquent scope that filters query results
5. **HasModulePermissions trait** — Methods: `canOnModule()`, `getAccessibleModuleIds()`, `getEffectiveModulePermissions()`, `canAccessVault()`

### RbacScope Engine

File: `app/Helpers/RbacScope.php`

The `apply()` method accepts a model class and a visibility mode:

- **`'module'`** — Module-wide access: user sees all records in modules they have View permission on. If no View permission on any module, sees no records (`1=0`).
- **`'ownership'` (default)** — Personal access: Super Admin sees all; Admin sees records in accessible modules via `module_id`; all other users see only records where `user_id = current user`.

Controllers call `RbacScope::apply(Model::class, 'module')` in their constructors or index methods for the 9 infrastructure modules.

### Dashboard Widgets

Each widget is a standalone class in `app/Dashboard/`. Widgets receive the authenticated user and accessible module IDs, return data arrays. Results are cached with per-user keys.

## Data Flow

### Request Lifecycle

1. User visits a URL (e.g., `/hostings`)
2. Route matched in `routes/web.php`
3. Middleware checks: authenticated, not suspended
4. Controller method called
5. Controller applies RBAC scope (module or ownership)
6. Model query executed with global scope applied
7. Results returned to view
8. View rendered with Blade template

### Permission Check Flow

```
User requests action (e.g., edit hosting record)
  → Auth check (are they logged in?)
  → Suspended check (is account active?)
  → Controller checks canOnModule('hostings', 'update')
    → Look for UserModulePermission override for this user + module
      → If override exists and is not null → use override value
    → Look for ModuleRolePermission matching user's role + module
      → If matching role has can_update = true → allow
      → Otherwise → deny (403)
```

## Authentication Flow

```
Login form → POST /login
  → Validate email + password
  → Check suspended status (if suspended → return error)
  → Attempt authentication
    → Success: regenerate session, log audit event, redirect to dashboard
    → Failure: log audit event, return error

Forgot password flow:
  → GET /forgot-password (form)
  → POST /forgot-password (send reset link)
    → Validate email
    → Send reset link via Laravel Password facade
  → GET /reset-password/{token} (form)
  → POST /reset-password (process reset)
    → Validate token, email, password
    → Update password, log activity
```

---

## Related Modules

- [Developer RBAC Reference](18_DEVELOPER_RBAC_REFERENCE.md)
- [Backup and Restore Guide](13_BACKUP_AND_RESTORE.md)
