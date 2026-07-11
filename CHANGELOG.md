# Changelog

All notable changes to OpsPilot are documented in this file.

---

## [1.7.2] — 2026-07-11 — SMTP & Notification Clarity

### Email Subjects
- **All emails prefixed with `[OpsPilot]`**: Subjects now follow `[OpsPilot]{[TEST]} {ResourceType} {Urgency} — {Name}` format
- **Task overdue uses task terminology**: `[OpsPilot] Task Overdue — {name}` — no "expiry"/"renewal" language
- **Monitor failure subjects include type + name**: `[OpsPilot] {type} DOWN — {name}`

### Email Content
- **Resource type from actual trackable model**: Hosting, Domain, VPS, VoIP, ServiceProvider, DomainEmail, OtherService — mapped via polymorphic `trackable` relationship; standalone trackers fall back to module label
- **Related domain/hosting shown**: Hosting shows its `domain`, DomainEmail shows its parent Domain, etc. — omitted when not applicable
- **Status, cost, assigned user added** to all renewal reminder emails
- **Recipient reason line**: "You received this because you are the assigned user / an administrator / a custom recipient"
- **Test banner**: Yellow warning header on test emails (`[TEST]` prefix in subject + "TEST EMAIL" banner)
- **Portal links per entity type**: Route map resolves `hostings.show`, `domains.show`, etc.; invalid types fall back to `/dashboard`
- **Sensitive data excluded**: Passwords, SMTP credentials, vault secrets, tokens, recovery codes never in email

### New Notifications
- **`TaskOverdue` notification**: Separate class (`app/Notifications/TaskOverdue.php`) using `['database', 'mail']` channels — task terminology only (Due Date, Days Overdue, Task Status), links to `tasks.show` route
- **`MonitorCheckFailed` event updated**: Added `?int $itemId` property; passed from command through listener to notification
- **`ExpiringSoon` notification updated**: `[OpsPilot]` prefix, `ROUTE_MAP`, status field, recipient reason line, portal links

### Mailable & Template
- **`ExpiryTrackerReminder` Mailable** (`app/Mail/ExpiryTrackerReminder.php`): Added `recipientType`, `isTest` parameters; `buildViewData()` loads trackable, resolves resource type, passes domain/hosting, status, recipient reason
- **Email template rewritten** (`resources/views/emails/expiry-tracker-reminder.blade.php`): Standard format with Resource Type, Resource Name, Domain/Hosting, Provider, Expiry Date, Days Remaining/Overdue, Status, Assigned User, Cost, Recipient Reason, portal button; TEST banner when `$isTest` is true

### Services & Commands
- **`CheckOverdueTasks` uses `TaskOverdue`** instead of `ExpiringSoon`
- **`MonitorCheck` dispatches `itemId`** with `MonitorCheckFailed` event
- **`RenewalNotificationService`** (`app/Services/RenewalNotificationService.php`): `buildMailable()` accepts `$recipientType` + `$isTest`, loads trackable relation; `sendTest()` passes `'test'` type; `previewEmail()` uses same `buildMailable()` as send; `testSmtpProfile()` passes `isTest=true`

### UI & Controller Improvements
- **SMTP test success/error messages**: Controller now says "Test accepted by SMTP server. Recipient: ..." instead of "sent successfully"; activity log matches
- **Test email confirmation dialogs**: "Send Test Email" button shows recipient email + subject before sending; "Send Reminder Now" shows recipient list
- **Preview endpoint returns `testRecipient`**: `ExpiryTrackerController::previewEmail()` includes authenticated user email in JSON response

### Test Suite Expansion
- **63 tests total** (was ~48 for this area): New `TaskOverdueNotificationTest` (9 tests), 27 `ExpiryReminderMailTest` tests, updated `RenewalNotificationServiceTest`, updated `NotifyMonitorFailureTest`
- **Key coverage**: OpsPilot prefix, TEST prefix, urgency levels, all trackable types, standalone fallback, status, recipient reasons, test banner, portal links, cost/provider/preview/send parity, related domain/hosting, dedup, sensitive data safety, task-only terminology

## [1.7.1] — 2026-07-10 — Improvement Batch 1

### Security & Data Protection
- **AnyDesk password encryption**: Added `'anydesk_password' => 'encrypted'` cast to `Asset` model (`app/Models/Asset.php:63`) — passwords no longer stored in plaintext

### Performance
- **AssetController index**: Added `->select(['id', 'asset_tag', 'brand', 'model', 'assigned_user_name', 'status', 'premises', 'anydesk_id', 'module_id'])` and `->with('module')` to prevent over-fetching and N+1 lazy load (`app/Http/Controllers/Web/AssetController.php:72`)
- **DomainEmailController N+1**: Added `->with('module')` to `edit()` and `destroy()` methods to prevent lazy-load of `$email->module` (`app/Http/Controllers/Web/DomainEmailController.php:114,150`)

### UI Markup
- **Fixed stray `</tbody>`**: Removed extra closing tag in `resources/views/domains/index.blade.php:93` — `<x-table>` component already wraps slot in `<tbody>`

### Documentation Cleanup — Markdown Overhaul
- **45 obsolete/stale `.md` files deleted** (Git-preserved): 28 from `docs/archive/`, 3 audit files, 14 design analysis files, 1 outdated tech debt doc
- **72 keep files verified** across root (27), `docs/operations/` (14), `docs/reference/architecture/` (11), `docs/reference/guides/` (14), `docs/reference/monitoring/` (3), `docs/reference/security/` (3)
- **Fixed all broken cross-references** in README.md, SECURITY_BASELINE.md, 11_GLOSSARY.md caused by prior file moves
- **Zero broken links remain** across all `.md` files
- **No application code modified**

### Documentation (Batch 1)
- Updated stale super-admin references in 4 docs files:
  - `docs/reference/architecture/05_PERMISSION_SYSTEM.md` — `config('tyro.super_admin_email')` → `$user->hasRole('super-admin')`
  - `docs/reference/architecture/04_MODULE_BUSINESS_LOGIC.md` — same fix
  - `docs/reference/architecture/02_ARCHITECTURE_LAYERING.md` — same fix
  - `docs/reference/security/12_DO_NOT_BREAK_LIST.md` — same fix

### Items Reviewed & Skipped (False Positives / Design Choices)
- Help view XSS (`innerHTML`) — content is server-sanitized, codebase-owned markdown
- CSP headers — already exists via `AddSecurityHeaders` middleware (globally registered)
- CSRF in JS string (`@csrf` in concatenation) — works correctly, token is alphanumeric
- 6 missing DB indexes — all already covered by foreign key auto-indexes
- VaultController `show()` `can_read` gate — RBAC scope already enforces read access
- Pagination fixes — all require broad refactoring (MonitoringOverview, ModulePermission matrix)
- NotificationController N+1 — view accesses only `data` JSON column, no relationship following
- Raw `<table>` vs `<x-table>` consistency — design choice, all render correctly
- SmtpProfile pagination config — using `config()` is better practice than hardcoding

## [1.7.0] — 2026-07-10 — Production Readiness Fixes

### Pre-Deployment Hardening
- **APP_DEBUG**: Set `APP_DEBUG=false` in `.env` to prevent credential exposure
- **ExpiryTracker**: Removed stale `password` field from `StoreExpiryTrackerRequest`, `UpdateExpiryTrackerRequest`, and `Api\ExpiryTrackerController` — column was already removed by migration, requests were no-ops
- **XSS defense**: Added `strip_tags(..., '<mark>')` wrapper around highlighted search results in `search/index.blade.php` for defense-in-depth
- **Dead code**: Removed 3 unused console commands (`EncryptPasswords`, `ExpiryResync`, `ExpiryBackfill`) and updated PHPStan baseline
- **Permission gates**: Added `abort_unless(hasRole('super-admin'), 403)` to `SmtpProfileController@destroy` and `WebhookController@destroy`
- **Super-admin gates (all methods)**: Added `abort_unless(Auth::user()->hasRole('super-admin'), 403)` to every public method in FeatureController (7), ModuleController (7), PrivilegeController (7), RoleController (9), RoleTemplateController (3), and remaining SmtpProfileController methods (9) — 42 methods total across 6 controllers
- **Middleware removal**: Removed non-functional `$this->middleware()` calls from 6 Web controller constructors (base `Controller.php` does not extend `Illuminate\Routing\Controller`, so `middleware()` is unavailable)
- **Verified existing gates**: `Api\WebhookController::store()` gate (line 41) before `Webhook::create()`, `Web\WebhookController::store()` gate (line 49) before `Webhook::create()`
- **PHPStan**: Increased analysis level from 1 to 6

### Full Audit Completed
- **10-dimension audit**: Routes (337), Controllers (74), Models (30), Views (~200+), Permissions, Security, Performance, Dead Code, Duplicate Code, Documentation
- **Findings**: 9 Critical, 10 High, 12 Medium, 9 Low, 7 Enhancement — classified in `PRODUCTION_READINESS_REPORT.md`
- **Verdict**: 🟢 GO — all 7 pre-deployment fixes applied. Zero regressions. `view:cache` passes.

### Verification
- `view:cache` — ✅ PASS
- Targeted tests (ExpiryTracker, Webhook, SmtpProfile) — 103 pass, 11 fail (all pre-existing `updated_at`, zero regressions)
- `PRODUCTION_READINESS_REPORT.md` — Updated with fix status
- `CURRENT_EXECUTION_STATUS.md` — Updated with post-audit section
- `FINAL_RELEASE_AUDIT.md` — Updated with fix entries

---

## [1.6.0] — 2026-07-10 — Role Dashboards Sprint

### Sprint 5 — Role Dashboards
- **Goal**: Create tailored dashboard experiences for 5 user types without permission or architecture changes
- **Widget visibility** now adapts per role — only relevant widgets are instantiated (no unnecessary queries)

### Controller Changes
- `DashboardController::index()` — Added `$user->loadMissing('roles')` to eager-load roles once
- `DashboardController::getRoleGroup($user)` — New method; returns highest-priority role slug from user's roles (priority: `super-admin` > `admin` > `editor` > `user` > `customer`)
- `DashboardController::getWidgetsForRole($user)` — New method; returns appropriate widget class array per role
- Removed hardcoded `$widgetClasses` → renamed to `$allWidgets` (all 10); filtering happens at render time
- Passes `$dashboardRole` variable to view for subtitle rendering

### Widget Visibility by Role

| Role | Widgets | Count |
|------|---------|-------|
| **Super Admin** (`super-admin`) | All: Operations, Renewals, Tasks, Assets, Monitoring, Quick Actions, Activity, Vault, SMTP, ServerHealth | 10 |
| **IT Management** (`admin`) | Operations, Renewals, Tasks, Assets, Monitoring, Quick Actions, Activity, Vault | 8 |
| **IT Support** (`editor`) | Tasks, Assets, Monitoring, Quick Actions, Activity, Vault | 6 |
| **Developer** (`user`) | Operations, Monitoring, Tasks, Quick Actions, Activity, Vault | 6 |
| **Office Management** (`customer`) | Tasks, Quick Actions, Activity, Vault | 4 |

### View Changes
- `dashboard/index.blade.php` — Subtitle is now role-aware via inline mapping of `$dashboardRole`: "Enterprise Overview" (SA), "Operations Overview" (admin), "Support Overview" (editor), "My Services" (user), "My Dashboard" (customer)

### Key Design Decisions
- No permission changes — each widget already scopes data correctly via `accessibleIds` per user
- No new modules — reused all 10 existing widgets
- `@if(!empty(...))` view guards remain as safety net for edge cases
- Role slug matching uses priority ordering (user with multiple roles gets highest)

### Verification
- `view:cache` — ✅ PASS
- All 25 DashboardPageTest assertions — ✅ PASS
- All 11 WebDashboardTest assertions — ✅ PASS
- No regressions (1 pre-existing TaskTest failure, unrelated)

---

## [1.5.0] — 2026-07-10 — Hosting / Domain Visibility for Developers Sprint

### Sprint 4 — Hosting / Domain Visibility for Developers
- **Goal**: Developers should immediately see hosting, domain, email, Cloudflare, and hosting details without navigating away
- **No controller changes** — all changes are view-only, reusing existing eager loads from Sprint 3

### Domain show page changes
- **Cloudflare in Overview**: Moved from Status section to Overview — developer sees `success`/`warning` badge or "No" immediately
- **Hosting Details section**: New inline section shown when domain is linked to hosting — displays Plan, Server IP, cPanel IP, cPanel URL (with copy button), and Status badge
- No need to click through to hosting show page to see server details

### Hosting show page changes
- **Cloudflare indicator**: Each linked domain now shows `· CF: Proxied` (or status value) after the domain name in green
- **Email count**: Each linked domain shows email count (e.g., "3 emails") between the domain info and status badge

### Verification
- `view:cache` — ✅ PASS
- Targeted tests: `DomainTest` (show) ✅, `HostingTest` (show) ✅
- Pre-existing failures: same 5 `updated_at` failures (unrelated to Sprint 4)

---

## [1.4.0] — 2026-07-10 — Relationship Dashboards Sprint

### Sprint 3 — Relationship Dashboards
- **6 show pages** converted to operational dashboards with inline relationship views
- **Hosting show**: Added Linked Domain Emails (via `domains.domainEmails` eager load) + Renewals (polymorphic ExpiryTracker query) + clickable Provider link
- **Domain show**: Added Renewals + clickable Provider and Hosting links
- **Service Provider show**: Replaced count badges with 5 inline relationship listings (Hosting, Domains, VPS, VoIP, Other Services) — each with name, identifier, status badge, and show page link
- **VPS show**: Added Renewals + clickable Provider link
- **VoIP show**: Added Renewals + clickable Provider link (label: Vendor)
- **Other Services show**: Added `serviceProvider` to `showWith()` eager load + Renewals + clickable Provider link
- All relationship sections use consistent `space-y-2` design pattern with `rounded-lg bg-gray-50 dark:bg-gray-800/50` items
- `view:cache` — ✅ PASS. Full regression — same 46 pre-existing `updated_at` failures (no regressions)

### Controller Changes
- `HostingController::showWith()` — added `'domains.domainEmails'`; `showExtraData()` returns `$renewals`
- `DomainController::showExtraData()` — new, returns `$renewals`
- `ServiceProviderController::showExtraData()` — `load()` actual records instead of just `loadCount()`
- `VpsController::showExtraData()` — returns `$renewals`
- `VoipController::showExtraData()` — returns `$renewals`
- `OtherServiceController::showWith()` — added `'serviceProvider'`; `showExtraData()` returns `$renewals`

### View Changes
- `hostings/show.blade.php` — Linked Domain Emails section, Renewals section, Provider link
- `domains/show.blade.php` — Renewals section, Provider + Hosting links
- `service-providers/show.blade.php` — 5 inline relationship listings replacing count badges
- `vps/show.blade.php` — Renewals section, Provider link
- `voip/show.blade.php` — Renewals section, Provider (Vendor) link
- `other-services/show.blade.php` — Renewals section, Provider link

---

## [1.3.0] — 2026-07-09 — Documentation vs Code Audit

### Documentation vs Code Audit
- **L5_SWAGGER_CONST_HOST fix** — `OpenApiSchemaTest` 6 failures resolved (undefined constant defined in test bootstrap)
- **Branding sweep** — "Tyro RBAC Enterprise" → "OpsPilot" across 13 `.md` files (README, USER_GUIDE, SECURITY_BASELINE, RELEASE_NOTES, INSTALLATION, PRODUCTION_CHECKLIST, PROJECT_STATISTICS, FINAL_CODE_QUALITY_AUDIT, DEPLOYMENT_GUIDE, DATABASE_HEALTH_AUDIT, BACKUP_AND_RESTORE, ADMIN_GUIDE, CHANGELOG)
- **Stats corrected** — Models 30, Controllers 72, Services 38, Widgets 10, Views 186, Migrations 73, Tests ~448, Prod deps 5, Dev deps 10
- **Root cleanup** — Historical docs archived to `/docs/archive/`
- All 15 business rules verified in code; 6 passing, 3 warned (BR-07 enum debt, BR-11 silent null, BR-12 coupling), 6 clean
- CSP documented in SECURITY_BASELINE.md but no config/middleware implements it — noted as gap
- `FINAL_RELEASE_AUDIT.md` updated

---

## [1.2.0] — 2026-07-09 — Copy Button Standardization Sprint

### Sprint 2 — Shared Copy Button Component
- Created shared `<x-copy-button>` Blade component with `@once('copy-button-js')` JS injection
- Removed 13+ inline script duplicates across all index and show pages
- **Service Providers**: Added URL copy button; migrated index + show to `<x-copy-button>`
- **Hostings**: Added cPanel URL copy button; migrated index + show
- **Domains**: Added email copy button (Linked Emails section)
- **Domain Emails**: Added email copy button; migrated index + show
- **VPS**: Added IP address copy button; migrated index + show
- **VoIP**: Added server IP, phone copy buttons; migrated index + show
- **Other Services**: Added URL, login URL, username copy buttons; migrated index + show
- **G Mails**: Added user name, email address, recovery email copy buttons; migrated index + show
- **Vault**: Added URL and username copy buttons (index + show)
- **Webhooks**: Added URL copy button (index + show)
- **SMTP Profiles**: Added sender email, SMTP host:port, username, reply-to copy buttons (index + show)
- `view:cache` — all templates compile successfully
- `FINAL_RELEASE_AUDIT.md` updated

### Sprint 2 — Close-Out Verification
- **Full regression**: 365 tests run, 47 failures — 46 pre-existing `updated_at` validation, 1 `indigo` variant fixed
- **Standard compliance**: 52 `<x-copy-button>` usages across 25 blade files — confirmed same component, same icon (clipboard SVG), same placement (flex items-center gap-2), same behaviour (green checkmark 2s)
- **Inline copy check**: 0 remaining inline copy implementations except vault password (known limitation requiring backend API endpoint)
- **Vault password**: Deferred — needs JSON password API endpoint before converting to `<x-copy-button password-route>`
- **Fixed**: `service-providers/edit.blade.php` — non-existent `variant="indigo"` → `variant="outline"` (was causing 500)
- **Signed off**: Sprint 2 complete. Documentation updated: CURRENT_EXECUTION_STATUS, FINAL_RELEASE_AUDIT, CHANGELOG

---

## [1.1.0] — 2026-07-09 — Show Page Standardization Sprint

### Sprint 1 — Standard Show Pages
- **Service Providers**: Standardized show page with sectioned layout, always-visible copy icons, Linked Services dashboard
- **Hosting**: Standardized show page (Overview→Access→Technical→Relationships→Financial→Dates→Status→Notes→Timeline), billing_period_months display
- **Domains**: Standardized show page sections, Linked Emails section, billing_period_months display
- **Other Services**: Standardized to `<x-card>` sectioned layout (Overview→Access→Financial→Dates→Status→Notes), added billing_period_months display
- **Domain Emails**: Standardized sections, added MonitorResult, added missing fields (cost, billing_period_months, expiry_date, status, storage_mb)
- **VPS**: Standardized sections (Access→Technical→Financial→Dates→Status), added billing_period_months
- **VoIP**: Standardized sections, added missing fields (start_date, expiry_date, status, billing_period_months)
- **G-Mail**: Standardized sections, added NotesThread
- **Vault**: Converted to `<x-card>` with sections, preserved POST reveal pattern
- **Webhook**: Converted to `<x-card>` with Overview + Status sections
- **SMTP Profile**: Converted to `<x-card>` with sections, preserved Usage card
- **Expiry Tracker**: Converted to `<x-card>` with sections, preserved Linked source banner + Notifications section
- Unified password copy/toggle JavaScript across all modules
- `FINAL_RELEASE_AUDIT.md` updated with complete changelog
- `PROJECT_ARCHITECTURE_LOCK.md` updated with full constitution

---

## [1.0.0] — 2026-06-27 — Production Release

### Phase 1 — Foundation & Authentication
- Laravel 12 project setup with MySQL/SQLite support
- User authentication (login, register, password reset, profile)
- Login audit logging (success/failure tracking)
- Account suspension (`suspended_at`, `suspended` middleware)
- Rate limiting on auth endpoints (5 req/min)

### Phase 2A — RBAC Core
- Tyro RBAC package integration (`hasinhayder/tyro v1.6`)
- Role CRUD with privilege assignment
- Module CRUD (Features → Modules hierarchy)
- Module-level permissions (CRUD + reveal)
- Role templates (Super Admin, Admin, IT Support, Read Only)

### Phase 2B — RBAC Extensions
- User-level module permission overrides (`user_module_permissions`)
- Effective permission resolution (role + override)
- Permission API endpoint for frontend consumption
- Super-admin bypass (hard-coded gate)

### Phase 2C — RBAC Hardening
- Prevent self-demotion of super-admin
- Prevent deletion of last super-admin
- Form validation against super-admin assignment
- Privilege documentation on module permission pages

### Phase 3 — Core CRUD & Resources
- **Domain Management** — name, registration/expiry dates, cost, status, auto-renew, DNS servers, Cloudflare status, service provider linkage
- **Hosting Management** — plan, cPanel, domain/IP fields, credentials (encrypted), service provider linkage
- **VPS Management** — IP, OS, RAM/Disk/CPU specs, login IDs, additional IPs, department, location, service provider linkage
- **VoIP Management** — phone number, server IP, credentials, service provider linkage
- **Service Providers** — provider profiles with contact info
- **Domain Emails** — email accounts with passwords
- **Other Services** — catch-all service tracking
- **Expiry Trackers** — renewal tracking with notification scheduling
- All resources: SoftDeletes, Activity Logging, Search, Attachment support

### Phase 4 — Advanced Features
- **Task Management** — title, description, status workflow, priority, due dates, assignees (via pivot), kanban board
- **Password Vault** — AES-256-CBC encrypted entries, reveal logging, module-scoped sharing
- **Notes** — polymorphic notes attachable to any model, CRUD with soft-deletes
- **Attachments** — file uploads, polymorphic attachment, download, storage cleanup
- **Monitoring** — URL ping check with status tracking
- **Calendar** — combined view of tasks with due dates and tracker expiry dates
- **Webhooks** — HTTP callbacks on resource events

### Phase 5 — Import/Export & API
- **CSV Import** — configurable field mapping, type validation
- **CSV Export** — per-module export with ownership scoping
- **REST API** — full Sanctum-based API with Swagger/OpenAPI docs
- **API Token Management** — create, list, revoke tokens
- **API Reports** — tasks, activity, logins, costs endpoints

### Phase 6 — Notifications & Renewal Engine
- **SMTP Profile Management** — CRUD, test, set-default, toggle-active, duplicate
- **Expiry Notification Engine** — configurable days-before, expiry-day, assigned-user, admin, custom-email notifications
- **Notification History** — per-tracker log of all sent notifications (success/failure)
- **Manual Send** — trigger notification from UI
- **Artisan Command** — `expiry:send-reminders` with database queue
- **UI Notifications** — in-app notification system with rich rendering (task assigned, note added, expiring soon, vault revealed, monitor failed)
- **Bulk Notifications** — mark-read, mark-all-read, bulk-delete

### Phase 7 — Asset Management
- **Asset Taxonomy** — categories (Laptop, Headphone, Mouse, Network Device), types (19 models), locations
- **Asset CRUD** — full lifecycle with restore, force-delete
- **Assignment Tracking** — `AssetAssignment` with assigned_at/returned_at timestamps
- **Asset Search** — by tag, serial, department
- **Bulk Actions** — update status, delete, restore, force-delete across all resource types
- **QR Identifier** — future-ready field

### Phase 7B — Enterprise Global Search
- Unified search across 15 module types (domains, hostings, vps, voip, domain_emails, other_services, service_providers, expiry_trackers, assets, tasks, vault, notes, features, modules, users, smtp_profiles)
- LIKE-based search with relevance ordering (exact > starts-with > contains)
- Ownership scoping (user, user_or_module, task, sa_only)
- 6 filter categories (All, Services, Assets, Tasks, Vault, Users)
- `SearchHelper::highlight()` with `<mark>` tag wrapping
- `/api/search` endpoint consumed by cmd+K palette
- `/search` web page with filter buttons and badge colors
- 33 test GlobalSearchTest suite

### Phase 8A — Enterprise Architecture Review
- Architecture review document (12 sections, 6 tables, CRUD workflow, RBAC matrix)
- Enterprise reporting master plan (40+ reports across 13 categories)
- Final architecture adjustments (module-specific providers, readable URLs, shared filter component)

### Phase 8B — Enterprise Reporting Center
- 7 report provider classes in `app/Reports/`
- ReportService with provider aggregation (run, export CSV, widget data)
- 15 MVP reports across 7 categories
- Readable URLs: `/reports/{category}/{report}`
- CSV export with UTF-8 BOM
- Shared `<x-report-filter-bar>` Blade component
- Dashboard widget deep-links ("View Full Report →")
- Global Search integration (report label/description matching)
- 36 tests in updated ReportTest suite

### Phase 9.1 — Security Baseline Documentation
- Created `SECURITY_BASELINE.md` — comprehensive security governance document
- 17 sections covering: environment hardening, authentication, RBAC, vault/SMTP/asset/renewal security, search/report access control, session/cookie security, backup security, logging/audit, deployment checklist, incident response, change control, known limitations, and v1.1 recommendations

---

## Project Statistics (v1.3.0 — corrected per audit)

| Metric | Count | Previously Claimed |
|--------|-------|--------------------|
| Models | 30 | 27 |
| Controllers | 72 (38 Web + 34 API) | 70 (36 Web + 33 API) |
| Services | 38 | 23 |
| Dashboard Widgets | 10 | 9 |
| Report Providers | 8 (7 providers + 1 base) | 8 |
| Feature Tests | ~80 | 73 |
| Unit Tests | ~41 | 38 |
| Total Tests | ~448 | 111 / 1278 |
| Migrations | 73 | 54 |
| Blade Views | 186 | 151 |
| Total Routes | 444 | — |
| Seeders | 7 | 7 |
| RBAC Features | 4 | 4 |
| RBAC Modules | 27 | 27 |
| Role Templates | 4 | 4 |
| Asset Categories | 4 | 4 |
| Asset Types | 19 | 19 |
| Composer Prod Deps | 5 | 6 |
| Composer Dev Deps | 10 | 9 |
