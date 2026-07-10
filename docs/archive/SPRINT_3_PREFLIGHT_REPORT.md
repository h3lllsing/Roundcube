# Sprint 3 Preflight Report — Monitoring Dashboard + Alerts

## Preflight Checks

| Check | Status | Evidence |
|-------|--------|----------|
| MonitorService exists | ✅ | `app/Services/MonitorService.php` — `ping()`, `checkSsl()`, `check()` |
| MonitorCheckFailed notification class | ✅ | `app/Notifications/MonitorCheckFailed.php` — database channel only |
| Dashboard widget registration pattern | ✅ | `DashboardController@index` — iterates `$widgetClasses` array, uses `SLUG` constant, `cacheTtl()`, `data()` |
| Monitored models list (8) | ✅ | `Domain`, `Hosting`, `Vps`, `Voip`, `ServiceProvider`, `DomainEmail`, `OtherService`, `ExpiryTracker` |
| Route pattern for new page | ✅ | Routes in `routes/web.php` use `Route::get(...)` pattern |
| Sidebar pattern | ✅ | `SidebarComposer` injects `$show*` booleans, mapped to module slugs |
| Dashboard view pattern | ✅ | `dashboard/index.blade.php` uses `@if(!empty($...)) @include(...)` |
| Widget view pattern | ✅ | Uses `<x-card variant="glass">` + `<x-stat-card>` components |
| Email mailable pattern | ✅ | `ExpiryTrackerReminder` uses `Mail\Mailable`; notification uses `Notification::via()` + `toMail()` |

## Implementation Plan

### 1. Monitoring Dashboard Widget
- `app/Dashboard/MonitoringWidget.php` — SLUG='monitoring', 300s cache, 5 metrics
- `resources/views/dashboard/widgets/monitoring.blade.php` — 4 stat cards + SSL line
- `app/Http/Controllers/Web/DashboardController.php` — register widget
- `resources/views/dashboard/index.blade.php` — add monitoring section in grid

### 2. Monitoring Page
- `routes/web.php` — `Route::get('/monitoring', ...)`
- `app/Http/Controllers/Web/MonitoringOverviewController.php` — unified query across 8 tables
- `resources/views/monitoring/index.blade.php` — table with filters
- `resources/views/components/sidebar-nav-groups.blade.php` — add link
- `app/Http/View/Composers/SidebarComposer.php` — add `showMonitoring`
- `resources/views/layouts/admin.blade.php` — pass `show-monitoring`

### 3. Email Alerts
- `app/Notifications/MonitorCheckFailed.php` — add `mail` to `via()`, add `toMail()`

## No Changes
- No migrations
- No new models
- No `monitoring_logs` table
- No SSL data persistence
- No task auto-creation
- No monitoring module extraction
