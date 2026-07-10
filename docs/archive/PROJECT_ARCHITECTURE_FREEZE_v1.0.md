# Project Architecture Freeze — v1.0

> **Status:** FINAL — Effective 2026-06-27  
> **Version:** 1.0.0  
> **Document Purpose:** Declare all modules frozen for the v1.0 production release. Future work MUST extend existing architecture — no new business modules, no architecture redesigns, no schema changes without deprecation path.

---

## 1. Authentication (`app/Http/Controllers/Web/AuthController.php`)

| Aspect | Detail |
|--------|--------|
| **Strategy** | Session-based (Laravel `auth` guard), Sanctum tokens for API |
| **Routes** | `/login`, `/register`, `/forgot-password`, `/reset-password`, `/logout` |
| **Rate Limiting** | 5 attempts per minute on login/register/password-reset |
| **Alerts** | Login audited via `LoginAudit` model (success/failure tracking) |
| **API Auth** | Sanctum token-based (Bearer), issued via `TokenController` |
| **MFA** | Not implemented (future consideration) |
| **Password Policy** | Validated via `FormRequest` rules (min 8 chars, confirmed) |
| **Account Suspension** | `suspended_at` timestamp on `users` table; `suspended` middleware checks |

### Frozen Contracts
- Auth flow, rate limits, login auditing, suspension mechanism.

---

## 2. RBAC (`vendor/hasinhayder/tyro`)

| Aspect | Detail |
|--------|--------|
| **Engine** | `hasinhayder/tyro` package v1.6+ |
| **Roles** | Dynamic via `roles` table, assigned `slug`-based |
| **Permissions** | Module-level via `module_role_permissions` (CRUD + reveal) |
| **User-Level Overrides** | `user_module_permissions` table (overrides role defaults) |
| **Middleware** | `role:slug` middleware for route gating |
| **Seed Roles** | super-admin, admin, customer, editor, user (via `RolePermissionSeeder`) |
| **Role Templates** | `role_templates` table for quick role provisioning |
| **Super-Admin** | Hard-coded gate in controllers (`$user->hasRole('super-admin')`); bypasses all module checks |

### Frozen Contracts
- Role/permission model, module-level CRUD model, user-level override mechanism, super-admin bypass.

---

## 3. User Management (`app/Http/Controllers/Web/UserController.php`)

| Aspect | Detail |
|--------|--------|
| **Model** | `App\Models\User` (27 columns, SoftDeletes, HasApiTokens, Notifiable) |
| **CRUD** | Full REST: index, create, store, show, edit, update, destroy (soft) |
| **Suspension** | `suspend` / `unsuspend` endpoints; `suspended` middleware |
| **Clone** | Clone user with roles (via `cloneForm` / `cloneStore`) |
| **Last Login** | Tracked via `LoginAudit` (not stored on user record) |
| **Search** | By name, email across index; role filter |
| **Trashed** | Soft-delete with restore/force-delete |
| **Bulk Actions** | Bulk suspend, unsuspend, delete (super-admin) |

### Frozen Contracts
- User CRUD, suspension, clone, search/trash patterns, bulk actions.

---

## 4. Navigation (`resources/views/layouts/admin.blade.php`)

| Aspect | Detail |
|--------|--------|
| **Pattern** | Single-file sidebar layout with `@include` dashboard widgets |
| **Sidebar Groups** | Services, Credentials, Work, Reports, Account, Documentation, Administration, RBAC |
| **Role Gating** | `@hasrole('super-admin')` for admin sections; `$show*` booleans for service visibility |
| **Cmd+K Palette** | Client-side JS with `cmdPages` array + AJAX search from `/api/search` |
| **Mobile** | Slide-out sidebar with overlay; sticky top bar |
| **Desktop** | Collapsible sidebar (persistent via localStorage) |

### Frozen Contracts
- Sidebar layout structure, cmd+K palette, mobile/desktop toggle, role-gating pattern.

---

## 5. SMTP Profiles (`app/Http/Controllers/Web/SmtpProfileController.php`)

| Aspect | Detail |
|--------|--------|
| **Model** | `App\Models\SmtpProfile` (full SMTP credentials, encryption-enabled) |
| **CRUD** | Full REST with duplicate, test, set-default, toggle-active |
| **Usage** | Referenced by `ExpiryTracker` for renewal email delivery |
| **Default** | One profile can be marked `is_default` |
| **Password** | Encrypted at rest via `encrypted` cast |

### Frozen Contracts
- SMTP profile CRUD, test/send, default selection, encrypted password storage.

---

## 6. Renewal Engine (`app/Models/ExpiryTracker.php`, `app/Services/ExpiryNotificationService.php`)

| Aspect | Detail |
|--------|--------|
| **Model** | `App\Models\ExpiryTracker` — tracks any service/credential with an expiry date |
| **Notifications** | `ExpiryTrackerNotification` — logs every send attempt |
| **Engine** | `ExpiryNotificationService` — checks `next_notification_due_at` and sends via SMTP profile |
| **Scheduling** | `expiry:send-reminders` artisan command (queue:database) |
| **Trigger** | Manual send from web UI + automatic cron-driven |
| **Notification Rules** | Per-tracker: `notify_days_before` (array), `notify_on_expiry_day`, `notify_assigned_user`, `notify_admins`, `notify_custom_emails` |
| **SMTP Dependency** | Uses `SmtpProfile` for mail delivery; falls back to `mail` log driver |
| **Dashboard Widget** | `RenewalsWidget` shows upcoming/overdue stats |

### Frozen Contracts
- Tracker model, notification engine, cron command, manual send, SMTP integration.

---

## 7. Asset Management (`app/Http/Controllers/Web/AssetController.php`)

| Aspect | Detail |
|--------|--------|
| **Models** | `Asset`, `AssetAssignment`, `AssetCategory`, `AssetType`, `AssetLocation` |
| **CRUD** | Full REST with restore, force-delete, assign, return |
| **Assignment** | `AssetAssignment` pivot with `assigned_at` / `returned_at` timestamps |
| **Taxonomy** | Category → Type hierarchy; Location independent |
| **Search** | By asset tag, serial number, department |
| **Dashboard Widget** | `AssetsWidget` shows total, assigned/returned today, status breakdown |
| **QR** | `qr_identifier` field for future QR code integration |

### Frozen Contracts
- Asset CRUD, assignment lifecycle, taxonomy, dashboard widget.

---

## 8. Enterprise Dashboard (`app/Dashboard/`)

| Aspect | Detail |
|--------|--------|
| **Controller** | `DashboardController` — iterates 9 widget classes, caches per-user per-widget |
| **Widgets** | `OperationsWidget`, `RenewalsWidget`, `TasksWidget`, `AssetsWidget`, `QuickActionsWidget`, `ActivityWidget`, `VaultWidget`, `SmtpWidget`, `ServerHealthWidget` |
| **Caching** | `Cache::remember()` with widget-specific TTL + version-busting (`dashboard:version`) |
| **Widget Contract** | Each widget implements `SLUG` constant, `data(User $user, ?array $accessibleIds)` method, `cacheTtl()` method |
| **Deep Links** | Each widget card links to full report via "View Full Report →" |
| **Charts** | Chart.js canvases rendered inline from widget data attributes |

### Frozen Contracts
- Widget interface, caching strategy, per-user scoping, deep-link pattern.

---

## 9. Enterprise Global Search (`app/Services/GlobalSearchService.php`)

| Aspect | Detail |
|--------|--------|
| **Engine** | `GlobalSearchService` — LIKE-based search across 15 module types |
| **Modules** | domains, hostings, vps, voip, domain_emails, other_services, service_providers, expiry_trackers, assets, tasks, vault, notes, features, modules, users, smtp_profiles, reports |
| **Scoping** | Ownership rules: `user`, `user_or_module`, `task`, `sa_only` |
| **Relevance** | Exact > starts-with > contains ordering per module |
| **Filters** | All, Services, Assets, Tasks, Vault, Users |
| **Highlight** | `SearchHelper::highlight()` wraps matched terms in `<mark>` |
| **API** | `/api/search?q=...&filter=...&limit=...` (used by cmd+K palette) |
| **Web** | `/search?q=...&filter=...` (full search results page) |
| **Limits** | 5 per module, 50 total |
| **Reports Module** | `model => null` — searches report labels/descriptions, links to report pages |

### Frozen Contracts
- Search engine, module config format, ownership scoping, relevance scoring, filter system.

---

## 10. Enterprise Reporting (`app/Reports/`, `app/Services/ReportService.php`)

| Aspect | Detail |
|--------|--------|
| **Pattern** | Provider-based: 7 providers in `app/Reports/` registered in `ReportService` |
| **Providers** | DomainReports, HostingReports, VpsReports, RenewalReports, AssetReports, TaskReports, UserReports |
| **Reports** | 15 MVP reports across 7 categories (active/expiring/expired, today/30d/overdue, assigned/available/by-dept, pending/overdue) |
| **Routes** | `/reports/{category}`, `/reports/{category}/{report}`, `/reports/{category}/{report}/export` |
| **Export** | CSV via `fputcsv` stream; UTF-8 BOM included |
| **Filters** | Shared `<x-report-filter-bar>` Blade component |
| **Dashboard Integration** | Widget deep-links call specific report URLs |
| **Global Search** | Reports searchable via label/description matching |
| **Legacy** | Original `totalMonthlyCost()`, `costByType()`, `topCosts()`, `taskSummary()`, `loginSummary()` preserved |

### Frozen Contracts
- Provider interface, report definition format, URL structure, CSV export, filter bar component.

---

## Extension Policy

All future feature requests MUST:

1. **Extend existing providers** — Add new reports to existing `app/Reports/` providers rather than creating new top-level categories.
2. **Extend existing widgets** — Add widget data to existing `app/Dashboard/` classes rather than creating new widget files.
3. **Extend existing services** — Add methods to existing `app/Services/` rather than creating new service classes.
4. **Extend existing controllers** — Add actions to existing controllers rather than creating new controllers.
5. **Use existing migrations** — Alter existing tables (add nullable columns) rather than creating new tables, unless the domain is genuinely novel.

### What Requires a New Architecture Review
- Adding a new database table.
- Adding a new Composer/NPM dependency.
- Adding a new service class.
- Adding a new dashboard widget.
- Adding a new report provider category.
- Changing the authentication strategy.
- Changing the RBAC engine.

All such changes require a new Architecture Review document and explicit approval.

---

*End of Architecture Freeze v1.0*
