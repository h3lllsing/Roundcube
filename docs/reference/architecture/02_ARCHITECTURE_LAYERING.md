# 2. Architecture & Layering

## Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 10.x |
| PHP | 8.1+ |
| Database | MySQL 8.x |
| Frontend | Blade templates (v1.0), Tailwind CSS v3, Alpine.js v3 |
| Assets | Laravel Vite with `laravel-vite-plugin` |
| Authentication | Laravel Breeze (Blade stack) |
| Authorization | Custom `HasModulePermissions` trait + roles |
| Activity Logging | spatie/laravel-activitylog v4 |
| Encryption | Laravel built-in AES-256-CBC (`encrypted` cast) |
| Notifications | Laravel Notifications (database + mail channels) |
| Pagination | Laravel LengthAwarePaginator (default: 10 per page) |
| Testing | PHPUnit 10.x (1951 tests, 4950 assertions) |

## Directory Structure & Responsibilities

```
app/
  Console/Commands/         # Artisan commands
  Enums/                    # PHP enums (plan types, polling statuses, etc.)
  Events/                   # Event classes (e.g. ExpiryTrackerLinked)
  Exceptions/               # Custom exception classes
  Http/
    Controllers/
      Api/                  # Lightweight API controllers (secondary)
      Web/                  # All web controllers (primary interface)
    Requests/               # Form request validation classes
  Listeners/                # Event listeners
  Mail/                     # Mailable classes
  Models/                   # Eloquent models
  Notifications/            # Notification classes
  Observers/                # Model observers
  Providers/                # Service providers
  Services/                 # Business logic service classes
  Traits/                   # Shared traits (HasModulePermissions, etc.)
bootstrap/                  # Framework bootstrap
config/                     # Configuration files
database/
  factories/                # Model factories
  migrations/               # Database migrations
  seeders/                  # Database seeders
public/                     # Public entry point + assets
resources/
  css/                      # Global CSS (Tailwind directives)
  js/                       # JavaScript entry points
  views/                    # Blade templates
routes/
  web.php                   # Web routes (primary)
  api.php                   # API routes (secondary)
tests/                      # PHPUnit tests
vite.config.js              # Vite configuration
```

## Layering & Data Flow

```
┌──────────────────────────────────────────────────┐
│                  Blade Templates                  │
│  (Presentational: views/, components/)           │
├──────────────────────────────────────────────────┤
│              Web Controllers                     │
│  (Request handling, validation, response)        │
│  app/Http/Controllers/Web/                       │
├──────────────────────────────────────────────────┤
│              Service Layer                       │
│  (Business logic, orchestration)                 │
│  app/Services/                                   │
├──────────────────────────────────────────────────┤
│              Models (Eloquent)                   │
│  (Data access, relationships, scopes)            │
│  app/Models/                                     │
├──────────────────────────────────────────────────┤
│              Database (MySQL)                    │
└──────────────────────────────────────────────────┘
```

### Strict Rule Violated in ~75% of Controllers (Known)

The intended layer separation is Controller → Service → Model → DB. In practice, most controllers contain inline business logic without a service layer. The service layer exists only for:

- `RenewalNotificationService` — renewal reminder dispatch
- `VaultService` — password encryption/decryption logic

Every other controller performs business logic directly in the controller method. This was noted in the v1.0 assessment as the largest architectural debt, but was left intact because extracting services would change too many interfaces without a dedicated test suite per controller.

### Critical: FK Select Rule (Do Not Break)

When a controller combines `->with()` (eager loading) with `->select()` (column restriction), **all belongsTo foreign key columns must be included in the select list**. This is the single most common bug pattern in the codebase (11 controllers were fixed for Rule Violation in June 2026).

- Example: `Domains` belong to `hosting` and `service_provider`. `DomainController@index` had `->select('id', 'name', ...)` without `hosting_id` and `service_provider_id`, making `->with('hosting', 'serviceProvider')` eager loading fail silently.
- **Guideline:** When using `->select()` with `->with()`, always include: `hosting_id`, `service_provider_id`, `domain_id`, `user_id`, `causer_id`, `feature_id`, and any other belongsTo FK on the table.

## Controller → View Data Flow Pattern (Standard)

Every index/show controller follows this pattern:

```php
public function index(Request $request)
{
    $this->authorize('module_access', ...);   // Gate check via HasModulePermissions

    $records = Model::query()
        ->with([...eager loads...])            // N+1 prevention
        ->select([...columns + FK columns...]) // Column restriction + FK inclusion
        ->when($request->search, fn($q) => ...)
        ->orderBy(...)
        ->paginate(config('app.pagination_per_page'));

    return view('module.index', compact('records', ...));
}
```

## Auth & Session Flow

1. User logs in via Breeze (`/login`) — session-based authentication.
2. `AuthenticatedSessionController` validates credentials, creates session.
3. Every subsequent request passes through `web` middleware group (session, CSRF, cookie encryption).
4. `auth` middleware ensures authenticated access to all protected routes.
5. Controllers use `$this->authorize()` or `Gate::allows()` with module permission checks.
6. Super admin is determined by `$user->hasRole('super-admin')` via Spatie Permission role assignment.

## Caching Strategy

No application-level caching is implemented. Every page load performs database queries for the full data set. The FK select fix reduced query count from ~267 to ~27 per full page load with all relationships visible.
