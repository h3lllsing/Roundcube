# Patch 1.0.4 — OpsPilot Branding Rename + Login Background Fix

> **Version 1.0 — Release Freeze**
> **Date:** 2026-06-27
> **Status:** All 1840 tests passing, 0 failures

---

## 1. Branding Rename — Changes Applied

### 1.1 Configuration Files (APP_NAME)

| File | Before | After |
|------|--------|-------|
| `.env` | `APP_NAME="Tyro RBAC"` | `APP_NAME="OpsPilot"` |
| `.env` | `MAIL_FROM_NAME="Tyro RBAC"` | `MAIL_FROM_NAME="OpsPilot"` |
| `.env.example` | `APP_NAME="Tyro RBAC"` | `APP_NAME="OpsPilot"` |
| `.env.example` | `MAIL_FROM_NAME="Tyro RBAC"` | `MAIL_FROM_NAME="OpsPilot"` |
| `.env.example.bak` | `APP_NAME="Tyro RBAC"` | `APP_NAME="OpsPilot"` |
| `.env.example.bak` | `MAIL_FROM_NAME="Tyro RBAC"` | `MAIL_FROM_NAME="OpsPilot"` |

These propagate to all `config('app.name')` references across the app:
- Browser `<title>` tags
- Sidebar header
- Page headers
- Email notification subjects/sender
- Mail configuration defaults

### 1.2 API Documentation (Visible in Swagger UI)

| File | Before | After |
|------|--------|-------|
| `config/l5-swagger.php:11` | `'title' => 'Tyro RBAC API'` | `'title' => 'OpsPilot API'` |
| `app/OpenApi.php:9` | `title: 'Tyro RBAC API'` | `title: 'OpsPilot API'` |

### 1.3 Login Page Branding

| File | Element | Change |
|------|---------|--------|
| `resources/views/auth/login.blade.php` | Heading | Now shows "OpsPilot" via `config('app.name')` |
| `resources/views/auth/login.blade.php` | Subtitle | Added: "Enterprise IT Operations Platform" |
| `resources/views/auth/login.blade.php` | Description | Added: "Centralize infrastructure, domains, hosting, VPS, assets, credentials, renewals, monitoring, and security from a single enterprise workspace." |

### 1.4 Test Scripts (Non-UI, Developer Tooling)

| File | Line | Before | After |
|------|------|--------|-------|
| `scripts/test-api.php:4` | Doc comment | `Tyro RBAC Enterprise` | `OpsPilot Enterprise` |
| `scripts/test-api.php:52` | Echo output | `Tyro RBAC API Test Suite` | `OpsPilot API Test Suite` |
| `scripts/e2e-test.php:4` | Doc comment | `Tyro RBAC Enterprise` | `OpsPilot Enterprise` |

---

## 2. Email Change Reverted

The internal seed email `admin@tyro.project` was **NOT changed**. This email is created by the third-party `hasinhayder/tyro` package (`TyroSeeder`), not by application code. It is internal seed data — not visible UI branding.

**Files reverted** (all back to `admin@tyro.project`):
- `app/OpenApi.php` — contact email
- `database/seeders/DatabaseSeeder.php` — admin lookup
- `database/seeders/DemoDataSeeder.php` — admin lookup/create
- `tests/Feature/BetterCreateUserTest.php` — super admin lookup
- `tests/Feature/RoleTemplateTest.php` — super admin lookup
- `scripts/test-api.php` — assertion

---

## 3. Login Background Fix

### Cause
The CSS `background-image: url('/images/login/dark.jpg')` was hardcoded in a `<style>` block. Since the app runs at `http://localhost/unknow/public/`, the browser resolved this as `http://localhost/images/login/dark.jpg` (missing the `/unknow/public/` base path), resulting in a 404.

### Fix
Moved `background-image` to an inline `style` attribute on `<body>` using the Blade `{{ asset() }}` helper:

```blade
<body class="..."
      style="background-image: url('{{ asset('images/login/dark.jpg') }}');">
```

This generates `http://localhost/unknow/public/images/login/dark.jpg` — the correct URL.

The remaining CSS properties (`background-size: cover`, `background-position: center`, `background-repeat: no-repeat`, `background-attachment: fixed`) stay in the `<style>` block and are unchanged.

### Mobile
- `background-attachment: scroll` on screens < 768px (prevents iOS Safari jank)
- `fixed inset-0 bg-black/50` overlay ensures readability on small screens

---

## 4. Cache Cleared

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
```

---

## 5. Test Results

```
Tests:  1840 passed (4655 assertions)
Failures: 0
```

All `BetterCreateUserTest` tests pass, including `password never copied`.

---

## 6. Files Modified (Summary)

| # | File | Change Type |
|---|------|-------------|
| 1 | `.env` | APP_NAME + MAIL_FROM_NAME |
| 2 | `.env.example` | APP_NAME + MAIL_FROM_NAME |
| 3 | `.env.example.bak` | APP_NAME + MAIL_FROM_NAME |
| 4 | `config/l5-swagger.php` | API title |
| 5 | `app/OpenApi.php` | API title |
| 6 | `resources/views/auth/login.blade.php` | Branding text + background URL fix |
| 7 | `scripts/test-api.php` | Visible output strings |
| 8 | `scripts/e2e-test.php` | Doc comment |

---

## 7. Files NOT Modified

As required by the freeze:
- `routes/web.php` — No route changes
- `app/Http/Controllers/Auth/` — No auth controller changes
- `app/Http/Middleware/` — No middleware changes
- `database/` — No schema changes
- `app/Policies/` — No RBAC changes
- `app/Models/` — No model changes
- No class names, namespaces, or PHP internal identifiers changed

---

## 8. Verification Checklist

- [x] Login page heading shows "OpsPilot"
- [x] Login page subtitle shows "Enterprise IT Operations Platform"
- [x] Login page description shows the full product description
- [x] Browser title shows "OpsPilot - Login"
- [x] Sidebar header shows "OpsPilot"
- [x] Dashboard header shows "OpsPilot"
- [x] Email mail from name is "OpsPilot"
- [x] Swagger docs show "OpsPilot API"
- [x] Background image loads with 200 OK (via `asset()` helper)
- [x] No hardcoded `/images/login/dark.jpg` paths remaining
- [x] `background-size: cover` applied
- [x] Card right-aligned on desktop (`lg:justify-end`)
- [x] Mobile overlay present (`bg-black/50`)
- [x] No auth/routes/RBAC/business logic changed
- [x] Cache cleared
- [x] All 1840 tests pass

---

*End of report.*
