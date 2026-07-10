# Contributing

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Create MySQL DB, update .env, then:
php artisan migrate --seed
php artisan serve
```

## Coding Standards

```bash
# Lint
php vendor/bin/pint --test

# Auto-fix
php vendor/bin/pint

# Static analysis
php vendor/bin/phpstan analyse
```

## Tests

```bash
# All tests
php artisan test

# Watch mode (if using file watcher)
# Tests are re-run automatically on file changes

# With coverage (PCOV required)
php artisan test --coverage

# E2E tests
cd e2e && npm install && npx playwright test
```

## Pull Request Checklist

- [ ] `php artisan test` passes
- [ ] `php vendor/bin/pint --test` passes
- [ ] `php vendor/bin/phpstan analyse` has no errors
- [ ] New code includes tests (feature + unit where applicable)
- [ ] API changes are documented in `API_REFERENCE.md`
- [ ] `.env.example` is updated if new env vars are added

## Architecture Notes

- **Controllers** are thin — business logic goes in `app/Services/`
- **Form Requests** handle validation — keep controllers clean
- **Resources** transform API responses — one resource per model
- **Notifications** use `database` channel (extend to `mail` when needed)
- **Events** are dispatched from services, not controllers
- **Traits** (`Blameable`, `HasAttachments`, `HasModulePermissions`) are reusable across models
- **SoftDeletes** is used on all resource models — restore is supported
- **CSV Import** expects headers matching database column names
