# Sprint 3 Regression Report — Monitoring Dashboard Widget + Monitoring Page + Email Alerts

## Scope
Analysis of all new and modified files and their potential impact on existing functionality.

## File-by-File Analysis

### New Files

#### `app/Dashboard/MonitoringWidget.php`
- **Risk**: None — standalone class, no existing code references modified. Only called from `DashboardController@widgetClasses` (additive).

#### `resources/views/dashboard/widgets/monitoring.blade.php`
- **Risk**: None — new Blade partial. Only included from `dashboard/index.blade.php` via a new `@include` line. No existing views affected.

#### `app/Http/Controllers/Web/MonitoringOverviewController.php`
- **Risk**: None — new controller, no existing routes modified. Uses standard `Controller` base class. Query pattern (8 `WHERE monitoring_url IS NOT NULL` queries) matches `MonitorCheck` command.

#### `resources/views/monitoring/index.blade.php`
- **Risk**: None — new view, no existing templates modified. Uses standard `layouts.admin` and existing Blade components (`x-page-header`, `x-button`, `x-action`).

### Modified Files

#### `app/Notifications/MonitorCheckFailed.php`
- **Change**: `via()` returns `['database', 'mail']` instead of `['database']`; added `toMail()` method.
- **Risk**: **Low** — all existing `database` notification consumers continue to work (unchanged `toArray()`). The `mail` channel is additive. Users without `mail` transport configured will silently skip mail delivery (Laravel default behavior).
- **Impact**: Notification test updated to match new expected channels. No production regression.

#### `routes/web.php` (line 119)
- **Change**: Added `Route::get('/monitoring', ...)`.
- **Risk**: None — new route only, no existing routes modified or reordered.

#### `app/Http/View/Composers/SidebarComposer.php`
- **Change**: Added `showMonitoring` computed variable.
- **Risk**: **Low** — additive code block at the end of `compose()`. All existing variables (`showProviders`, `showHostings`, `showDomains`, etc.) unchanged. Super-admin fast-path returns `$data['showMonitoring'] = true` alongside existing entries.

#### `resources/views/components/sidebar-nav-groups.blade.php`
- **Change**: Added Monitoring nav link before Notifications.
- **Risk**: **None** — additive HTML. Only renders when `showMonitoring` is true.

#### `resources/views/layouts/admin.blade.php`
- **Change**: Added `:show-monitoring="$showMonitoring"` to sidebar component.
- **Risk**: **None** — new prop passed to sidebar. No existing props changed.

### Test File

#### `tests/Unit/MonitorCheckFailedNotificationTest.php`
- **Change**: `assertSame(['database'])` → `assertSame(['database', 'mail'])`.
- **Risk**: None — test-only change. Matches updated implementation.

## Potential Regressions — None Detected

| Area | Status |
|------|--------|
| Dashboard (existing widgets) | ✅ Unchanged — new widget added to widget array |
| Dashboard (existing routes/controllers) | ✅ Unchanged |
| MonitorCheck command | ✅ Unchanged |
| MonitorCheckFailed listener | ✅ Unchanged (only notification via() array changed) |
| Monitor API endpoint (`/monitor/{type}/{id}`) | ✅ Unchanged |
| All 8 monitored models | ✅ Unchanged schema |
| Existing notification system | ✅ Unchanged — `toArray()` preserved; `database` channel preserved |
| RBAC / Permissions | ✅ Unchanged — `showMonitoring` is additive computed property |
| Sidebar (existing links) | ✅ Unchanged — new link is additive |
| Activity logging | ✅ Unchanged |
| Expiry tracker / Renewal dashboard | ✅ Unchanged |
| Vault / Service credential auto-copy | ✅ Unchanged |
| Offboarding checklist | ✅ Unchanged |
| Search / Export / Import | ✅ Unchanged |

## Existing Pre-Condition Notices

| ID | Issue | Status |
|----|-------|--------|
| NR1 (carried) | `users.suspension_reason` column missing | Unchanged |
| NR2 (pre-existing) | `ActivityLogTest::test_api_activity_log_show_forbidden_for_non_super_admin` 404 vs 403 | Unchanged |
| NR3 (pre-existing) | `MonitorTest::check_nonexistent_returns_404` session table deadlock | Unchanged |

## Verdict
✅ **NO REGRESSIONS INTRODUCED.** All changes are additive (new files) or strictly additive within existing files. Pre-existing test failures unchanged.
