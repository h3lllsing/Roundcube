# Roundcube

**Role-based access control & resource management system** built on Laravel 12 + Tyro.

Manage features, modules, and more — all behind granular module-level permissions.

## Features

- **RBAC** — Role-based access with per-module CRUD permissions (create/read/update/delete/approve/export)
- **Monitoring** — HTTP/port monitoring with failure notifications
- **Activity Logs** — Full audit trail via Spatie Activitylog
- **REST API** — Sanctum token auth

## Stack

| Layer | Stack |
|-------|-------|
| Backend | PHP 8.2, Laravel 12 |
| Auth | Laravel Sanctum (API tokens) |
| RBAC | Tyro (roles, privileges, module permissions) |
| Database | MySQL 8.0 |
| Frontend | Blade + Vite (admin panel) |

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

**Default login (development only):** `admin@tyro.project` / `password`

## License

MIT
