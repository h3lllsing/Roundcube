# OpsPilot

**Role-based access control & resource management system** built on Laravel 12 + Tyro.

Manage features, modules, tasks, domains, hosting, VPS, VoIP, vault, webhooks, and more — all behind granular module-level permissions.

## Features

- **RBAC** — Role-based access with per-module CRUD permissions (create/read/update/delete/approve/export)
- **Resource Management** — Domains, hosting, VPS, VoIP, service providers, domain emails, other services
- **Task Management** — Multi-assignee tasks with status/priority/due_date, activity logging
- **Password Vault** — AES-256-CBC encrypted storage, reveal with audit trail
- **Monitoring** — HTTP/port monitoring with failure notifications
- **Expiry Tracking** — Track renewal dates across all resources with email notifications
- **Webhooks** — Event-driven outbound webhooks
- **Polymorphic Notes & Attachments** — Attach notes/files to any resource
- **Activity Logs** — Full audit trail via Spatie Activitylog
- **CSV Import/Export** — Bulk operations across resource types
- **REST API** — Sanctum token auth, Swagger docs at `/api/documentation`
- **E2E Tests** — Playwright browser tests

## Stack

| Layer | Stack |
|-------|-------|
| Backend | PHP 8.2, Laravel 12 |
| Auth | Laravel Sanctum (API tokens) |
| RBAC | Tyro (roles, privileges, module permissions) |
| Database | MySQL 8.0 |
| Frontend | Blade + Vite (admin panel) |
| Testing | PHPUnit (~448 tests) + Playwright E2E |
| Static Analysis | PHPStan level 7 |
| CI | GitHub Actions (PHP 8.1–8.3 matrix) |

## Quick Start

```bash
# 1. Clone & install
composer install
cp .env.example .env
php artisan key:generate

# 2. Database
# Create MySQL database, update .env, then:
php artisan migrate --seed

# 3. Run
php artisan serve
```

**Default login (development only):** `admin@tyro.project` / `tyro`
> ⚠️ **Security:** Change the default credentials and `APP_KEY` before deploying to production.

## Running Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage

# Specific test
php artisan test --filter="RoleTest"

# Static analysis
php vendor/bin/phpstan analyse

# Lint
php vendor/bin/pint --test

# E2E (requires Playwright + server running)
cd e2e && npx playwright test
```

## Project Structure

```
app/
├── Console/Commands/     # Artisan commands (monitor:check, expiry:check)
├── Events/               # TaskCreated/Updated, VaultPasswordRevealed, etc.
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # REST API controllers (Sanctum auth)
│   │   └── Web/          # Admin panel controllers (session auth)
│   ├── Middleware/        # LogApiRequests
│   ├── Requests/         # Form request validation
│   └── Resources/        # API resource transformers
├── Listeners/            # Event handlers (notifications, logging)
├── Models/               # Eloquent models (30 total)
├── Notifications/        # Database/mail notifications (5)
├── Services/             # Business logic layer (38 classes)
└── Traits/               # Blameable, HasAttachments, HasModulePermissions
```

## API

Full reference: [API_REFERENCE.md](API_REFERENCE.md) | Swagger UI: `/api/documentation`

## Deployment

See [DEPLOY.md](DEPLOY.md) for shared hosting deployment guide.

## License

MIT
