# OPSPILOT — Monitoring Product Architecture

> Based on: Full codebase analysis of MonitorService, MonitorCheck command, 8 service models, 2 controllers, event/notification pipeline, dashboard widget pattern.

---

## 1. Current State (Before Sprint)

```
┌─────────────────────────────────────────────────────────────┐
│                    EXISTING INFRASTRUCTURE                    │
│                                                              │
│  MonitorService                                              │
│  ├─ ping(url) → {success, status_code, response_time_ms}     │
│  └─ checkSsl(url) → {valid, days_remaining, issuer, ...}    │
│                                                              │
│  Console: monitor:check (hourly cron)                        │
│  ├─ Iterates 8 model types                                   │
│  ├─ Calls $service->check($url) → ping + SSL                 │
│  ├─ Only persists: last_ping_at = now()                      │
│  └─ SSL data is COMPUTED but NEVER STORED                    │
│                                                              │
│  Controllers (Web + API)                                     │
│  ├─ On-demand check via GET /monitor/{type}/{id}             │
│  ├─ Persists last_ping_at = now()                            │
│  ├─ Returns ping + SSL result (session or JSON)              │
│  └─ SSL data is COMPUTED but NEVER STORED                    │
│                                                              │
│  Data Storage                                                │
│  ├─ monitoring_url VARCHAR on 8 tables (2NF violation)       │
│  ├─ last_ping_at TIMESTAMP on 8 tables (2NF violation)       │
│  ├─ NO monitoring_logs table                                 │
│  └─ NO SSL results persisted anywhere                        │
│                                                              │
│  Alerting                                                    │
│  ├─ MonitorCheckFailed event dispatched on ping failure      │
│  ├─ NotifyMonitorFailure listener                            │
│  ├─ MonitorCheckFailedNotification (database channel ONLY)   │
│  └─ No email/SMS/push alerting                               │
│                                                              │
│  Visibility                                                  │
│  ├─ monitor-button.blade.php (per-resource)                  │
│  ├─ monitor-result.blade.php (per-resource, session-based)   │
│  └─ NO dashboard widget — ZERO aggregate visibility          │
└─────────────────────────────────────────────────────────────┘
```

## 2. Target Architecture (After Sprint)

```
┌─────────────────────────────────────────────────────────────┐
│                    THREE-LAYER ARCHITECTURE                   │
│                                                              │
│  LAYER 1: DASHBOARD WIDGET (summary, cached)                 │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  MonitoringWidget                                    │    │
│  │  ├─ Monitored: 18/30                                 │    │
│  │  ├─ Online: 14                                       │    │
│  │  ├─ Offline: 2                                       │    │
│  │  ├─ Unchecked: 2                                     │    │
│  │  ├─ SSL ≤30d: 3                                      │    │
│  │  └─ [View All] → /monitoring                         │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  LAYER 2: MONITORING PAGE (detail, paginated)                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  GET /monitoring                                     │    │
│  │  ├─ Table: Type | Name | URL | Status | Last Check   │    │
│  │  ├─ Filters: type, status, search                    │    │
│  │  ├─ Sort: by status, by last_ping_at                 │    │
│  │  └─ Per-row actions: [Check Now] [View Service]     │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  LAYER 3: SERVICE DETAIL (per-resource, existing)            │
│  ├─ monitor-button.blade.php (existing, unchanged)           │
│  ├─ monitor-result.blade.php (existing, unchanged)           │
│  └─ last_ping_at displayed (existing, unchanged)             │
│                                                              │
│  CROSS-CUTTING: EVENT PIPELINE (unchanged)                   │
│  ├─ MonitorCheckFailed → database notification               │
│  └─ + MAIL channel added (Sprint scope)                     │
└─────────────────────────────────────────────────────────────┘
```

## 3. Data Flow

```
┌────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Cron/Hour │────▶│  MonitorService  │────▶│  8 service tables│
│            │     │  check(url)      │     │  SET last_ping_at│
│            │     │  ping + SSL      │     │  = now()         │
└────────────┘     └──────┬───────────┘     └────────┬────────┘
                          │                          │
                    on failure                  SELECT COUNT(*)
                          │                     WHERE last_ping_at
                          ▼                     IS NULL OR OLD
                    ┌─────────────┐                  │
                    │ Event +     │                  ▼
                    │ Notification│        ┌──────────────────┐
                    │ (db only)   │        │  Widget reads    │
                    └─────────────┘        │  aggregate COUNT │
                                           │  from 8 tables   │
                                           └──────────────────┘
```

## 4. Widget Implementation Pattern

The widget follows the EXACT same pattern as the 9 existing widgets:

```
app/Dashboard/MonitoringWidget.php
  └─ const SLUG = 'monitoring'
  └─ cacheTtl() = 300 (5 min)
  └─ data() returns:
       ├─ total_monitored   — COUNT WHERE monitoring_url IS NOT NULL
       ├─ healthy           — COUNT WHERE last_ping_at > 2h ago
       ├─ offline           — COUNT WHERE last_ping_at < 2h ago
       ├─ unchecked         — COUNT WHERE last_ping_at IS NULL
       └─ ssl_expiring      — 0 (placeholder until SSL persists)

resources/views/dashboard/widgets/monitoring.blade.php
  └─ Renders 4 stat cards in a 2×2 grid
  └─ [View All] link → /monitoring

app/Http/Controllers/Web/DashboardController.php
  └─ Append 'App\Dashboard\MonitoringWidget' to $widgetClasses

tests/Feature/Dashboard/MonitoringWidgetTest.php
  └─ Assert counts match seeded data
```

## 5. Monitoring Page (Future-Proof)

```
GET /monitoring — App\Http\Controllers\Web\MonitoringOverviewController

Query: Union-style across 8 tables (or polymorphic monitoring_statuses)
Columns: id, type, name, monitoring_url, last_ping_at, status (computed)

View: monitoring/index.blade.php
├─ Title: "Monitoring Overview"
├─ Stats bar: [Monitored] [Online] [Offline] [Unchecked]
├─ Filters: type (dropdown), status (dropdown), search (text)
├─ Table: Type | Name / Provider | URL | Status | Last Check | SSL | Actions
├─ Pagination: 25 per page
└─ Empty state: "No services with monitoring configured."
```

## 6. Key Architectural Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Read from 8 tables vs. new table | **Read from 8 tables** (sprint) | Avoid migration. All data already exists. UNION COUNT queries are fast at 30-300 records. |
| Widget cache TTL | **300s** (same as OperationsWidget) | Balances freshness with performance. Longer than ActivityWidget (60s), shorter than ServerHealthWidget (600s). |
| Status computation | **last_ping_at heuristic** | If `last_ping_at` > 2 hours ago = offline. If NULL = unchecked. If < 2 hours = assumed online. False positives possible but acceptable for dashboard summary. |
| SSL data | **Not persisted yet** | MonitorService.checkSsl() exists but needs persistence layer. Out of scope for this sprint. Widget shows "SSL: N/A" or 0. |
| Monitoring page location | **Top-level nav item** (not under a group) | Critical operational data needs direct access. Not buried under Infrastructure or Operations. |
| "View All" link | **Always present** | Widget shows summary; full table needs dedicated page. Every enterprise tool follows this pattern. |

## 7. Boundary Rules

| Data | Dashboard Widget | Monitoring Page |
|------|-----------------|-----------------|
| Total monitored count | ✅ Number | ✅ Table |
| Online count | ✅ Number | ✅ Filtered |
| Offline count | ✅ Number | ✅ Filtered + highlighted |
| Unchecked count | ✅ Number | ✅ Filtered |
| SSL ≤30d count | ✅ Number | ✅ Detailed list |
| Per-service status | ❌ | ✅ Full table |
| Last check timestamp | ❌ | ✅ Column |
| Response time | ❌ | ✅ Column (per service) |
| SSL details (issuer, dates) | ❌ | ✅ Per-service tooltip |
| Check history | ❌ | ✅ Future (monitoring_logs) |
| Configure monitoring_url | ❌ | ❌ (on service edit page) |
| Trigger check | ❌ | ✅ Per-row button |
| Export monitoring data | ❌ | ✅ Future |
