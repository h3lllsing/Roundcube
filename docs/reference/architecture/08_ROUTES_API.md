# 8. Routes & API

## Route Conventions

All routes are defined in `routes/web.php` (primary) and `routes/api.php` (secondary).

### Web Routes (Primary Interface)

All routes use Laravel resource routing with additional custom routes:

```php
// Standard resource pattern (all modules)
Route::resource('domains', DomainController::class);
Route::resource('hostings', HostingController::class);
// ... etc
```

### Named Routes

Every route has a named route. Convention:
- Index: `domains.index`
- Show: `domains.show`
- Create: `domains.create`
- Store: `domains.store`
- Edit: `domains.edit`
- Update: `domains.update`
- Destroy: `domains.destroy`

**Important:** Route names use the URL slug, which is the plural kebab-case form of the module name. Frontend must use `route('domains.index')`, NOT `/domains`. However, as Blade templates are being replaced, the new frontend can use hardcoded URLs as long as they match the route paths.

### Route Paths (All Modules)

| Module | Route Slug | Notes |
|---|---|---|
| Domains | `/domains` | |
| Hosting | `/hostings` | Note plural with 's'! Bug was `hosting` without 's' in some checks |
| VPS | `/vps` | Acronym, lowercase |
| VoIP | `/voip` | Acronym, lowercase |
| Domain Emails | `/domain-emails` | |
| Other Services | `/other-services` | |
| Service Providers | `/service-providers` | |
| Vault | `/vault` | |
| Assets | `/assets` | |
| Tasks | `/tasks` | Also `/tasks/kanban` for board view |
| Notes | `/notes` | |
| Attachments | `/attachments` | |
| Expiry Trackers | `/expiry-trackers` | Slug intentionally preserved! Do NOT change to `/renewals` |
| Webhooks | `/webhooks` | |
| Users | `/users` | |
| Roles | `/roles` | |
| Activity Logs | `/activity-logs` | |
| Login Audits | `/login-audits` | |
| Modules | `/modules` | |
| Features | `/features` | |
| Dashboard | `/dashboard` | |

### Custom Routes Per Module

**Domains:**
- `POST /domains/{domain}/reveal-password` — password reveal

**All entities with passwords:**
- Reveal password endpoints similar to domains pattern

**Attachments:**
- `GET /attachments/{attachment}/download` — file download
- `GET /attachments/{attachment}/preview` — inline preview (if implemented)

**Tasks:**
- `GET /tasks/kanban` — kanban board view

**Expiry Trackers:**
- `POST /expiry-trackers/{expiry_tracker}/toggle-complete` — completion toggle
- `GET /expiry-trackers/calendar` — calendar view (if implemented)

**Vault:**
- `POST /vault/{vault}/reveal` — reveals stored password via `VaultService`

**Activity Logs:**
- No custom routes beyond resource

**Users:**
- `POST /users/{user}/module-permissions` — update module permission overrides

### Modal Routes

Some create/edit forms load via modal. These follow the same resource routes but return partial views or JSON. (NEEDS CONFIRMATION on which modules use modals vs dedicated pages.)

## API Routes (Secondary)

**File:** `routes/api.php`

### Authentication

API uses a shared API token defined in `config('tyro.api_token')`. All API routes are protected by:

```php
Route::middleware('auth:sanctum')->group(function () { ... });
```

Actually, `config('tyro.api_token')` suggests custom token auth rather than Sanctum. (NEEDS CONFIRMATION — the ImportController and DashboardController use `config('tyro.api_token')` for auth, suggesting a simple token comparison middleware.)

### API Endpoints

Limited set — API is secondary to web:

- `GET /api/domains` — list domains (used by external integrations)
- `GET /api/domains/{domain}` — single domain
- `POST /api/import/domains` — bulk domain import
- `GET /api/dashboard/stats` — dashboard statistics

API logging was added to ImportController and DashboardController in Sprint A.

### API vs Web Differences

- API does NOT use session auth — uses token.
- API does NOT go through module permission gates — only token check.
- API returns JSON only.
- API is NOT feature-complete with web. Many features accessible only via web.
- API uses `Api\*Controller` namespace.

## Route-to-Controller Mapping

| Route | Controller | Method |
|---|---|---|
| GET /domains | DomainController@index | List all |
| GET /domains/create | DomainController@create | Show create form |
| POST /domains | DomainController@store | Store new |
| GET /domains/{domain} | DomainController@show | Show single |
| GET /domains/{domain}/edit | DomainController@edit | Show edit form |
| PUT/PATCH /domains/{domain} | DomainController@update | Update |
| DELETE /domains/{domain} | DomainController@destroy | Delete |
| POST /domains/{domain}/reveal-password | DomainController@revealPassword | Reveal password |

Same pattern repeats for all resource controllers.

## Middleware Stack

```php
// web.php route group
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // All protected routes
});
```

- `web` — session, CSRF, cookie encryption
- `auth` — Breeze auth check
- `verified` — email verification (if enabled)
- Optional: `permission:module_access` — applied in controllers via `$this->authorize()`

## CSRF Protection

- Enabled on all web routes.
- Every POST/PUT/DELETE form must include `@csrf`.
- Every AJAX POST/PUT/DELETE must include `X-CSRF-TOKEN` header (from meta tag or cookie).
- API routes EXEMPT from CSRF (token-based auth).

## Form Method Spoofing

Laravel convention: POST forms with `_method=DELETE`, `_method=PUT`, etc. Used in all delete/update forms.
