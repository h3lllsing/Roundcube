# V1 TECHNICAL DEBT INVENTORY

> Technical debt items that should be addressed but do not block the v1.0 release.
> Generated: 2026-07-03

---

## Architecture Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| AD-1 | 18 controllers bypass service layer with direct Eloquent/DB queries | Maintainability | 2-3 days | HIGH |
| AD-2 | `Web\UserController.php` is a 576-line God Class | Maintainability | 1 day | HIGH |
| AD-3 | `Api\ReportController.php` (360 lines) mixes raw SQL, CSV export, 4 report types | Maintainability | 1 day | MEDIUM |
| AD-4 | `Api\TaskController.php` (344 lines) bloated by inline permission logic + OA annotations | Maintainability | 4 hours | MEDIUM |
| AD-5 | `Web\ExpiryTrackerController.php` (319 lines) builds queries inline | Maintainability | 4 hours | MEDIUM |
| AD-6 | 25 service classes exist but Web controllers don't use them consistently | Architecture erosion | 2-3 days | HIGH |
| AD-7 | No repository layer — models queried directly from controllers | Testability | 1-2 days | LOW |
| AD-8 | `app/Exceptions/` empty directory is vestigial (Laravel 11 style) | Cleanliness | 5 min | LOW |

---

## Performance Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| PD-1 | SidebarComposer runs 12+ individual DB queries per page load | Page load time | 2 hours | HIGH |
| PD-2 | `RenewalNotificationService::sendReminders()` loads ALL trackers via `->get()` | Memory crash at scale | 2 hours | HIGH |
| PD-3 | `ExpiryNotificationService::checkModel()` loads ALL items via `->get()` for 7 models | Memory crash at scale | 2 hours | HIGH |
| PD-4 | Dashboard `computeDashboardData()` loops 8 models with individual queries | Dashboard load time | 4 hours | MEDIUM |
| PD-5 | Cache driver uses `file` — won't scale horizontally | Scalability | 1 hour | MEDIUM |
| PD-6 | Session driver uses `file` — won't scale horizontally | Scalability | 1 hour | MEDIUM |
| PD-7 | Log channel `single` has no rotation — unbounded growth | Disk usage | 5 min | LOW |
| PD-8 | Chart.js loaded globally on every page (60 KB) — should lazy-load | Initial payload | 2 hours | LOW |

---

## Design System Debt

| # | Item | Location | Est. Effort | Priority |
|---|---|---|---|---|
| DS-1 | 22 raw `glass-card` class usages instead of `<x-card variant="glass">` | guide, reports, profile | 2 hours | MEDIUM |
| DS-2 | 16 raw `btn`/`button` classes instead of `<x-button>` | permissions system | 4 hours | MEDIUM |
| DS-3 | 180+ lines of permission-specific CSS in global `app.css` | `app.css` lines 196-382 | 2 hours | MEDIUM |
| DS-4 | `users/create.blade.php` uses raw `<div class="card">` instead of `<x-card>` | users/create | 30 min | LOW |
| DS-5 | `.card` CSS class in app.css could conflict with Tailwind | `app.css` line 205 | 30 min | LOW |
| DS-6 | Missing `@layer` directives in CSS | `app.css` | 1 hour | LOW |

---

## CSP & Security Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| CS-1 | 36 inline event handlers across 22 files (CSP violations) | Blocks CSP implementation | 4 hours | HIGH |
| CS-2 | 240 lines of inline `<script>` in admin layout | Requires CSP `unsafe-inline` | 2-3 days | MEDIUM |
| CS-3 | `help-center.js` uses `innerHTML` — CSP risk | Content injection vector | 2 hours | MEDIUM |
| CS-4 | No security headers middleware (CSP, HSTS, XFO, XCTO, RP) | Missing defense layer | 4 hours | HIGH |
| CS-5 | `l5-swagger` in production dependencies — Swagger UI exposed | Information disclosure | 30 min | MEDIUM |
| CS-6 | Sanctum token expiry of 480 minutes | Elevated risk window | 10 min | LOW |
| CS-7 | `.env.example` defaults `APP_DEBUG=true`, `APP_ENV=local` | Insecure default | 5 min | HIGH |

---

## Test Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| TD-1 | CI pipeline tests PHP 8.1 but app requires ^8.2 | CI will fail | 5 min | HIGH |
| TD-2 | No Dusk/browser tests | Coverage gap | 2-3 days | MEDIUM |
| TD-3 | No queue-related tests | Coverage gap | 4 hours | MEDIUM |
| TD-4 | 424 tracked coverage artifacts in git | Repo bloat | 2 hours | LOW |
| TD-5 | No API integration tests for auth flows | Coverage gap | 1 day | MEDIUM |

---

## Validation & Error Handling Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| VE-1 | Missing custom error views for 401, 429 | UX gap | 1 hour | LOW |
| VE-2 | No explicit focus management in confirm dialog | Accessibility | 30 min | LOW |
| VE-3 | Help center search box has fixed `w-64` width | Mobile UX | 10 min | LOW |
| VE-4 | Missing `xs:` breakpoint on dashboard stat cards | Small screen UX | 10 min | LOW |
| VE-5 | No sidebar auto-close on link click (mobile) | Mobile UX | 30 min | LOW |

---

## Model & Data Integrity Debt

| # | Item | Impact | Est. Effort | Priority |
|---|---|---|---|---|
| MD-1 | 8 models missing `SoftDeletes` — permanent data loss risk | Data loss | 2 hours | HIGH |
| MD-2 | `RenewalSyncService::sync()` uses `Auth::id()` — may be null in CLI | Data integrity | 1 hour | MEDIUM |
| MD-3 | `Module::where('slug', $service->getTable())` assumes table=slug convention | Brittle mapping | 2 hours | MEDIUM |
| MD-4 | Hardcoded model list in `ExpiryNotificationService` (7 models) | Brittle, not extensible | 2 hours | MEDIUM |

---

## Summary

| Category | Items | Est. Effort |
|---|---|---|
| Architecture | 8 | 5-7 days |
| Performance | 8 | 1.5 days |
| Design System | 6 | 8 hours |
| CSP & Security | 7 | 3 days |
| Tests | 5 | 3-4 days |
| Validation & Error Handling | 5 | 2 hours |
| Model & Data Integrity | 4 | 5 hours |
| **Total** | **43 items** | **~15-20 days** |
