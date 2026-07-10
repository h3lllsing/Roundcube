# POST-V1.0 ROADMAP

> Features and improvements to schedule after the initial v1.0 release.
> Generated: 2026-07-03

---

## Phase 1: Hardening (Sprint 1-2)

### Service Layer Refactoring
| Task | Est. | Priority |
|---|---|---|
| Extract permission logic from `Web\UserController.php` (576 lines) into service | 1 day | P0 |
| Create `ReportService` classes for each report type (task, activity, login, cost) | 1 day | P0 |
| Refactor `Web\ExpiryTrackerController.php` to delegate query building | 4 hours | P1 |
| Remove direct `Module::where('slug', ...)` duplication across 11 Web controllers | 4 hours | P1 |

### Performance
| Task | Est. | Priority |
|---|---|---|
| Fix SidebarComposer N+1: batch-load all modules in one query | 2 hours | P0 |
| Add `chunkById()` to `RenewalNotificationService::sendReminders()` | 2 hours | P0 |
| Add `chunkById()` to `ExpiryNotificationService::checkModel()` | 2 hours | P0 |
| Optimize dashboard `computeDashboardData()` with cached aggregates | 4 hours | P1 |

### Security Hardening
| Task | Est. | Priority |
|---|---|---|
| Implement CSP middleware with report-only mode initially | 4 hours | P0 |
| Migrate 36 inline event handlers to Alpine `@click`/`x-on:change` | 4 hours | P0 |
| Extract 240-line inline `<script>` from admin layout to external JS file | 1 day | P1 |
| Move `l5-swagger` to `require-dev` or protect with auth middleware | 30 min | P1 |

### Data Integrity
| Task | Est. | Priority |
|---|---|---|
| Add `SoftDeletes` to 8 unprotected models | 2 hours | P0 |
| Fix `RenewalSyncService::sync()` to pass user explicitly instead of `Auth::id()` | 1 hour | P1 |
| Add module-slug mapping to `config/renewals.php` instead of table-name heuristic | 2 hours | P1 |

---

## Phase 2: Design System Completion (Sprint 3-4)

| Task | Est. | Priority |
|---|---|---|
| Refactor permissions views to use `<x-button>` instead of raw `btn` classes | 4 hours | P0 |
| Extract 180+ lines of permission CSS from `app.css` to scoped CSS file | 2 hours | P0 |
| Replace 22 raw `glass-card` usages with `<x-card variant="glass">` | 2 hours | P0 |
| Replace raw `<div class="card">` in `users/create.blade.php` with `<x-card>` | 30 min | P1 |
| Add `@layer` directives to `app.css` for CSS organization | 1 hour | P1 |
| Remove `!important` from sidebar-header dark mode CSS | 30 min | P2 |
| Audit and fix frontend bundle (lazy-load Chart.js on dashboard only) | 2 hours | P2 |

---

## Phase 3: Testing & CI (Sprint 5)

| Task | Est. | Priority |
|---|---|---|
| Add Dusk tests for critical user flows (login, CRUD, sidebar) | 2 days | P1 |
| Add queue worker integration test | 4 hours | P1 |
| Add API auth integration tests | 1 day | P1 |
| Add PHPStan to CI with level 6 | 4 hours | P1 |
| Fix CI matrix (remove PHP 8.1) | 5 min | P0 |

---

## Phase 4: Production Readiness v2 (Sprint 6-7)

| Task | Est. | Priority |
|---|---|---|
| Add Supervisor/Forge daemon config for queue worker | 2 hours | P0 |
| Switch to `redis` cache driver | 1 hour | P1 |
| Switch to `redis` or `database` session driver | 1 hour | P1 |
| Reduce Sanctum token expiry to 120 minutes | 10 min | P1 |
| Add custom error views for 401, 429 | 1 hour | P2 |
| Switch log channel to `daily` with 30-day retention | 5 min | P2 |

---

## Phase 5: UX Polish (Sprint 8)

| Task | Est. | Priority |
|---|---|---|
| Add mobile sidebar auto-close on navigation | 30 min | P2 |
| Fix dashboard stat card grid for xs screens | 10 min | P2 |
| Make help center search input responsive | 10 min | P2 |
| Add focus management to confirm dialog | 30 min | P2 |
| Add favicon (current file is 0 bytes) | 1 hour | P2 |

---

## Phase 6: Platform Scalability (Sprint 9-10)

| Task | Est. | Priority |
|---|---|---|
| Add read-replica database configuration | 2 hours | P2 |
| Implement proper query caching strategy | 4 hours | P2 |
| Add code splitting for frontend bundles | 4 hours | P2 |
| Add image optimization pipeline | 2 hours | P3 |
| Add database query log analysis tool | 2 hours | P3 |

---

## Estimated Timeline

| Phase | Sprints | Calendar | Est. Effort |
|---|---|---|---|
| Phase 1: Hardening | 2 | Week 1-2 | 5-7 days |
| Phase 2: Design System | 2 | Week 3-4 | 3-4 days |
| Phase 3: Testing & CI | 1 | Week 5 | 4-5 days |
| Phase 4: Production Readiness | 2 | Week 6-7 | 2-3 days |
| Phase 5: UX Polish | 1 | Week 8 | 1 day |
| Phase 6: Platform Scalability | 2 | Week 9-10 | 3-4 days |
| **Total** | **10 sprints** | **~10 weeks** | **~20-25 days** |
