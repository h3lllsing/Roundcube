# Sprint 3 Implementation Report — Monitoring Dashboard Widget + Monitoring Page + Email Alerts

## Files Created

### 1. `app/Dashboard/MonitoringWidget.php` — Dashboard Widget
- **5 metrics**: `total_monitored`, `online`, `offline`, `unchecked`, `ssl_expiring_30d` (placeholder 0)
- **300s cache** — matches OperationsWidget TTL
- **RBAC filter**: super-admin sees all; others filtered by accessible module IDs (uses `getAccessibleModuleIds('read')`)
- **Status heuristic**: `last_ping_at > now() - 2h = online`, `<= 2h = offline`, `NULL = unchecked`
- Iterates 8 model classes: Domain, Hosting, Vps, Voip, ServiceProvider, DomainEmail, OtherService, ExpiryTracker

### 2. `resources/views/dashboard/widgets/monitoring.blade.php` — Widget View
- 4 stat cards (Monitored, Online, Offline, Unchecked) in a 2x2 grid
- SSL expiring 30d line at bottom
- "View All →" link to full monitoring page
- Uses existing `x-stat-card` and `x-card` components

### 3. `app/Http/Controllers/Web/MonitoringOverviewController.php` — Monitoring Page Controller
- `$modelMap` array mapping 8 type keys to model class, label, name column, and show route
- `index()`: queries all 8 tables where `monitoring_url IS NOT NULL`, computes status per record, applies status/type/search filters, sorts, manually paginates (collection-based)
- `computeStats()`: returns total/online/offline/unchecked counts for filtered set
- RBAC: super-admin sees all; others scoped by `getAccessibleModuleIds('read')`

### 4. `resources/views/monitoring/index.blade.php` — Monitoring Page View
- Stats bar: 4 cards (Total, Online, Offline, Unchecked)
- Filter form: search (name/URL), type dropdown, status dropdown, Filter + Clear buttons
- Data table: Type, Name, URL (truncated), Status (colored badge), Last Check, Actions (View link)
- Empty state with guidance message
- Simple numeric pagination at bottom

### 5. `database/migrations/2026_06_30_000001_add_trackable_to_expiry_trackers_table.php`
- Adds `trackable_type` and `trackable_id` columns to `expiry_trackers` for polymorphic relationship
- Enables `loadMorph` in existing controllers

### 6. `resources/views/components/sidebar-nav-groups.blade.php` — Sidebar Nav Link
- Added "Monitoring" nav link directly before Notifications
- Gated by `showMonitoring` variable

## Files Modified

### 7. `app/Notifications/MonitorCheckFailed.php` — Email Alert Channel
- Added `'mail'` to `via()` array (preserving existing `'database'`)
- Implemented `toMail()` with:
  - Subject: `[OpsPilot] Service DOWN: {itemName}`
  - Greeting: "Service Monitoring Alert"
  - Lines: Service name, type, error message
  - Action button linking to dashboard
  - Footer: "The hourly monitoring check will retry automatically."

### 8. `routes/web.php` (line 119)
- Added: `Route::get('/monitoring', [MonitoringOverviewController::class, 'index'])->name('monitoring.index');`

### 9. `app/Http/View/Composers/SidebarComposer.php` — showMonitoring Gate
- Computes `showMonitoring` variable: true if user has read access to any of the 8 monitored module slugs (hostings, vps, voip, domains, domain-emails, other-services, service-providers, expiry-trackers)
- Super-admin: always true
- Unauthenticated: always false

### 10. `resources/views/layouts/admin.blade.php` — Pass show-monitoring to Sidebar
- Added `:show-monitoring="$showMonitoring"` prop to sidebar component

### 11. `tests/Unit/MonitorCheckFailedNotificationTest.php` — Test Updated
- `via_returns_database_channel`: `['database']` → `['database', 'mail']`

## What Was NOT Changed
- **No new database tables** (`monitoring_logs` deferred)
- **No SSL data persistence** (placeholder `0` in widget)
- **No task auto-creation**
- **No monitoring module extraction** (premature at 30 services)
- **No `/monitor/{type}/{id}` "Check Now" button** on monitoring page
- **All 8 existing model schemas unchanged**
- **Existing MonitorCheck command unchanged**
- **Existing MonitorCheckFailed listener unchanged**
- **No new composer or npm dependencies**

## Summary
| File | Change Type | Purpose |
|------|-------------|---------|
| `app/Dashboard/MonitoringWidget.php` | NEW | Widget with 5 metrics, 300s cache, RBAC |
| `resources/views/dashboard/widgets/monitoring.blade.php` | NEW | Widget view with 4 stat cards |
| `app/Http/Controllers/Web/MonitoringOverviewController.php` | NEW | Full monitoring page controller |
| `resources/views/monitoring/index.blade.php` | NEW | Monitoring page view with filters + table |
| `app/Notifications/MonitorCheckFailed.php` | MODIFIED | Added `mail` channel + `toMail()` |
| `routes/web.php` | +1 line | GET /monitoring route |
| `app/Http/View/Composers/SidebarComposer.php` | MODIFIED | showMonitoring gate logic |
| `resources/views/components/sidebar-nav-groups.blade.php` | MODIFIED | Monitoring nav link |
| `resources/views/layouts/admin.blade.php` | MODIFIED | Pass show-monitoring prop |
| `tests/Unit/MonitorCheckFailedNotificationTest.php` | MODIFIED | Updated expected channels |
