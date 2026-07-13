# OpsPilot Full Route Inventory & Duplicate Analysis

> Generated: Autonomous Audit Run — Phase 1 Complete
> Source: `routes/web.php` (340 lines), `routes/api.php` (215 lines), `php artisan route:list`

---

## 1. Route Counts

| Group | Routes |
|-------|--------|
| Web (auth guest) | 7 |
| Web (auth + suspended) | ~125 |
| Web (super-admin) | ~97 |
| API (public) | 2 |
| API (auth) | ~213 |
| **Total** | **~444** |

---

## 2. Web Route Inventory by Module

### Dashboard
| Method | URI | Name | Controller | Middleware |
|--------|-----|------|-----------|-----------|
| GET | / | — | redirect → dashboard | auth,suspended |
| GET | /dashboard | dashboard | DashboardController@index | auth,suspended |

### Authentication (Guest — `auth` middleware excluded)
| Method | URI | Name | Controller |
|--------|-----|------|-----------|
| GET | login | login | AuthController@showLoginForm |
| POST | login | login (POST) | AuthController@login |
| GET | register | register | AuthController@showRegistrationForm |
| POST | register | register (POST) | AuthController@register |
| GET | forgot-password | password.request | AuthController@showForgotPasswordForm |
| POST | forgot-password | password.email | AuthController@sendResetLink |
| GET | reset-password/{token} | password.reset | AuthController@showResetForm |
| POST | reset-password | password.update | AuthController@resetPassword |

### Authentication (Authenticated)
| Method | URI | Name | Controller |
|--------|-----|------|-----------|
| POST | logout | logout | AuthController@logout |
| GET | /profile | profile | AuthController@profile |
| PUT | /profile | profile.update | AuthController@updateProfile |
| GET | /my-permissions | my-permissions | AuthController@myPermissions |
| GET | /email/verify/{id}/{hash} | verification.verify | AuthController@verifyEmail |
| POST | /email/verification-notification | verification.send | AuthController@resendVerification |

### Monitoring
| Method | URI | Name | Controller |
|--------|-----|------|-----------|
| GET | /monitoring | monitoring.index | MonitoringOverviewController@index |
| GET | /monitor/{type}/{id} | monitor.check | MonitorController@check |
| GET | /monitoring/{monitoring} | monitoring.show* | MonitoringController@show |
| GET | /monitoring/create | monitoring.create* | MonitoringController@create |
| POST | /monitoring | monitoring.store* | MonitoringController@store |
| GET | /monitoring/{monitoring}/edit | monitoring.edit* | MonitoringController@edit |
| PUT/PATCH | /monitoring/{monitoring} | monitoring.update* | MonitoringController@update |
| DELETE | /monitoring/{monitoring} | monitoring.destroy* | MonitoringController@destroy |

\* From `php artisan route:list` — `MonitoringController` exists separately from `MonitoringOverviewController`.

### Monitoring Overview vs Detail — Overlap Analysis
- `monitoring.index` → OverviewController — high-level dashboard
- `monitoring.show` → MonitoringController — individual service detail
- **Issue**: `/monitor/{type}/{id}` (MonitorController@check) is a separate check endpoint, while `monitoring.index` shows the overview. The split between `MonitoringOverviewController` and `MonitoringController` creates ambiguity. Classification: **C** (shared cross-module operation).

---

### Service Providers (Vendors)
| Method | URI | Name | Controller |
|--------|-----|------|-----------|
| GET | /service-providers | service-providers.index | ServiceProviderController@index |
| GET | /service-providers/create | service-providers.create | create |
| POST | /service-providers | service-providers.store | store |
| GET | /service-providers/{id} | service-providers.show | show |
| GET | /service-providers/{id}/edit | service-providers.edit | edit |
| PUT/PATCH | /service-providers/{id} | service-providers.update | update |
| DELETE | /service-providers/{id} | service-providers.destroy | destroy |
| PATCH | /service-providers/{id}/restore | service-providers.restore | restore |
| DELETE | /service-providers/{id}/force-delete | service-providers.force-delete | forceDelete |
| GET | /service-providers/{id}/password | service-providers.password | getPassword |

### Hosting
Standard CRUD + password + restore/force-delete + password copy (lines 126-136).

### Domains
Standard CRUD + restore + force-delete (lines 101-109). Has `show` route.

### Domain Emails
Uses `Route::resource('domain-emails', DomainEmailController::class)` (line 196) + extra password/restore/force-delete routes.

### VPS
Standard CRUD + password + password copy + restore + force-delete (lines 111-121).

### VoIP
Full CRUD + password + extension-password + copy operations + restore + force-delete (lines 149-161).

### Vault
Standard CRUD + `my-vault` (vault.my) + reveal + restore + force-delete (lines 163-173).

### Other Services
Uses `Route::resource('other-services', OtherServiceController::class)` (line 200) + password/copy/restore/force-delete.

### Expiry Trackers
Uses `Route::resource('expiry-trackers', ExpiryTrackerController::class)` (line 205) + preview-email, test-email, send-reminder, notification-history, renew, restore, force-delete.

### Assets
Standard CRUD + assign/return + restore/force-delete (lines 89-99).

### G-Mails
Standard CRUD + password + restore + force-delete (lines 138-148).

### Notes
Standard CRUD + pin toggle + restore + force-delete (lines 174-183).

### SMTP Profiles (Mail Settings)
Under `/admin` prefix + auto-discover + test + set-default + toggle-active + duplicate (lines 327-338).

### Notifications
Index + markAsRead + markAllAsRead + destroy + bulk-delete + bulk-read (lines 214-219).

### Tasks
Index + myTasks + myTaskCounts + kanban + CRUD + updateStatus + restore (lines 78-87).

### Calendar
GET /calendar → CalendarController@index (line 232).

### Users (Super-admin only)
Full CRUD + permissions edit/update + suspend/unsuspend + clone + login-as + restore + force-delete (lines 283-297).

### Roles (Super-admin, /admin prefix)
CRUD + show + attachPrivilege + detachPrivilege (lines 305-313).

### Role Templates (Super-admin, /admin prefix)
Index + show + apply (lines 315-317).

### Privileges (Super-admin, /admin prefix)
CRUD + show (lines 319-325).

### Modules
Index + show (auth) | create/store/edit/update/destroy (super-admin) (lines 72-73, 258-262).

### Features
Index + show (auth) | create/store/edit/update/destroy (super-admin) (lines 69-70, 252-256).

### Module Permissions (Super-admin only)
Index + update + destroy (lines 267-269). **No create or edit routes** — update is submit-only.

### Webhooks (Super-admin only)
Full resource + test (lines 271-272).

### Activity Logs (Super-admin only)
Index + show (lines 264-265).

### Login Audits (Super-admin only)
Index + show + destroy (lines 274-276).

### Reports (Super-admin only)
Index + show + category + export (lines 278-281).

### Import (Super-admin only)
Create (form) + store (CSV import) (lines 299-300).

### Attachments
Index + create + store + show + download + destroy + force-delete (lines 234-240).

### Tokens (API Access)
Index + create + store + destroy only (lines 242-245). **No show or edit routes**.

### Help/Guide
Guide + help.search + help.module + help.show (lines 223-226).

### Other
Search (line 228), Export (line 230), Calendar (line 232), Bulk Action (line 221), Design System (line 302).

---

## 3. API Route Inventory by Module

(From `routes/api.php` — all under `auth:sanctum` middleware unless noted)

| Prefix | Module | Routes |
|--------|--------|--------|
| `/v1/hostings` | Hosting API | Full CRUD + restore |
| `/v1/domains` | Domain API | Full CRUD + restore |
| `/v1/vps` | VPS API | Full CRUD + restore |
| `/v1/voip` | VoIP API | Full CRUD + restore |
| `/v1/gmails` | G-Mail API | Full CRUD + restore |
| `/v1/datacenters` | Datacenter API | CRUD |
| `/v1/ip-addresses` | IP Address API | CRUD |
| `/v1/ssl-certificates` | SSL Certificate API | CRUD |
| `/v1/tasks` | Task API | CRUD |
| `/v1/serviceproviders` | Service Provider API | index + store + destroy |
| `/v1/vault` | Vault API | CRUD |
| `/v1/notes` | Notes API | CRUD |
| `/v1/activities` | Activity Log API | index |
| `/v1/notifications` | Notification API | index + markAsRead + markAllAsRead |
| `/v1/reports` | Report API | index |
| `/v1/profile` | Profile API | show + update |
| `/v1/dashboard` | Dashboard API | summary stats |
| /monitoring/status | Monitoring Status | public alert status |
| /unread-notifications-count | Notification Count | auth (not under v1) |

### API Route Issues
- **Inconsistent versioning**: Most routes under `/v1/`, but `/unread-notifications-count` and `/monitoring/status` are outside the versioned prefix.
- **Inconsistent naming**: Some use kebab-case (`/v1/ip-addresses`, `/v1/ssl-certificates`), others are concatenated (`/v1/serviceproviders`, `/v1/gmails`).
- **Missing modules**: No API routes for: expiry-trackers, assets, other-services, domain-emails, webhooks, import.
- **Overlap with web routes**: `/unread-notifications-count` duplicates the functionality exposed in `NotificationController@unreadCount` — but serves a different purpose (Alpine polling). Classification: **B** (valid contextual shortcut).

---

## 4. Duplicate & Overlapping Routes

### True Duplicates (A)
| Route A | Route B | Issue |
|---------|---------|-------|
| — | — | **None found** — all routes have unique URIs |

### Valid Contextual Shortcuts (B)
| Route | Counterpart | Rationale |
|-------|------------|-----------|
| `GET /my-tasks` (tasks.my) | `GET /tasks` (tasks.index) | Filtered personal view of same resource |
| `GET /my-vault` (vault.my) | `GET /vault` (vault.index) | Filtered personal view |
| `POST /notifications/{id}/read` | `POST /notifications/read-all` | Single vs bulk — distinct use cases |
| `GET /monitoring` | `GET /monitor/{type}/{id}` | Overview vs specific check |

### Shared Cross-Module Operations (C)
| Route | Context | Shared With |
|-------|---------|------------|
| `POST /bulk-action` | BulkController | All resources |
| `GET /search` | SearchController | All resources |
| `GET /export/{type}` | ExportController | All resources |

### Route Aliases (D)
| URI | Name | Note |
|-----|------|------|
| `/` | (unnamed) | Redirect to dashboard — unamed redirect |
| `/guide` | guide | Also `/help/{slug}` for specific articles |

### Dead/Orphaned Routes (E)
| Route | Suspicion |
|-------|-----------|
| `GET /design-system` | Super-admin only, likely dev-only page |
| `GET /register` (web guest) | Registration form exists but likely unused (single-user app) |
| `POST /register` | Same — register disabled in practice? |

### Security Concerns (F)
| Route | Concern |
|-------|---------|
| `role-templates/{id}/apply` (GET + POST) | GET method with state-changing side effect is a CSRF/navigational vulnerability |
| `users/{user}/login-as` | Must verify role:super-admin middleware is properly enforced (appears correct from route definition) |

---

## 5. Inconsistencies & Gaps

### Missing `show` Routes (compared to full CRUD modules)
| Module | Has Show? | Has Create? | Notes |
|--------|-----------|-------------|-------|
| monitoring | Yes | Yes | Split controllers: OverviewController vs MonitoringController |
| hostings | Yes | Yes | Full CRUD |
| domains | Yes | Yes | Full CRUD |
| domain-emails | Yes (resource) | Yes | Full CRUD |
| vps | Yes | Yes | Full CRUD |
| voip | Yes | Yes | Full CRUD |
| other-services | Yes (resource) | Yes | Full CRUD |
| expiry-trackers | Yes (resource) | Yes | Full CRUD |
| assets | Yes | Yes | Full CRUD |
| g-mails | Yes | Yes | Full CRUD |
| vault | Yes | Yes | Full CRUD |
| notes | Yes | Yes | Full CRUD |
| service-providers | Yes | Yes | Full CRUD |
| smtp-profiles | Yes | Yes | Full CRUD |
| **tokens** | **No** | Yes | No show/edit — create sends token immediately |
| **attachments** | Yes | Yes | Show for preview |
| **module-permissions** | **No** | **No (update-only)** | POST-based, no individual record view |
| **webhooks** | Yes (resource) | Yes | Full CRUD |
| roles | Yes | Yes | Full CRUD |
| role-templates | Yes | **No create** | Apply shortcut used instead |
| privileges | Yes | Yes | Full CRUD |
| features | Yes | Yes | Full CRUD |
| modules | Yes | Yes | Full CRUD |

**Pattern**: Most resource modules have full CRUD. Tokens lack show/edit (expected — API tokens are created and shown once). Module Permissions lacks individual CRUD entirely (uses POST update of entire config).

### Naming Inconsistency
- `g-mails.index` route name with hyphens but `GMailController` with camelCase
- `vault.my` route name uses dot notation vs `vault.index` — clear naming
- `notifications.read-all` vs `notifications.bulk-read` — different naming for similar bulk operations

---

## 6. Middleware Distribution

| Middleware Group | Routes Covered |
|-----------------|---------------|
| `web,guest` | Login, register, password reset |
| `web,auth,suspended` | ~125 routes (all standard user operations) |
| `web,auth,suspended,role:super-admin` | ~97 routes (admin functions) |
| `api,throttle:60,1` | 2 public API routes |
| `api,auth:sanctum,throttle:120,1` | ~213 API routes |
| `throttle:10,1` (on individual routes) | Password reveal, copy, email send operations |
| `throttle:5,1` | Login, register, password reset (guest) |
| `throttle:bulk` | Bulk action |
| `throttle:search` | Search |
| `throttle:export` | Export, report export |

### Middleware Issues
- `throttle:bulk`, `throttle:search`, `throttle:export` are custom named rate limiters — must verify they're defined in `App\Http\Kernel` or `BootProviders`
- No permission-specific middleware (e.g., `can:view-hosting`) — all RBAC is Blade-level `$show*` flags or role gates

---

## Roadmap for Phases 2-10

- **Phase 2** (Sidebar): Complete — see `OPSPILOT_SIDEBAR_OWNERSHIP_AUDIT.md`
- **Phase 3** (Operation Matrix): Complete — see `OPSPILOT_OPERATION_OWNERSHIP_MATRIX.md`
- **Phase 4** (Page Audit): See page-by-page findings
- **Phase 5** (Blade Links): Grep `route()` usage pending
- **Phase 6** (RBAC): Analysis of gate/middleware vs Blade-level control
- **Phase 7** (Recommendations): Canonical ownership proposals
- **Phase 8** (Severity): Critical/High/Medium/Low classification
- **Phase 9**: Document consolidation
- **Phase 10**: Final summary
