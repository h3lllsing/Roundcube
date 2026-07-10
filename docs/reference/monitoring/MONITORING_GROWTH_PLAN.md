# OPSPILOT — Monitoring Growth Plan

> How the monitoring system scales from 30 to 3000 services

---

## Phase 1: TODAY (30 services)

### Architecture
- Widget reads `SELECT COUNT(*) WHERE monitoring_url IS NOT NULL` across 8 tables
- Status computed via `last_ping_at` heuristic (2-hour window)
- No SSL data persisted
- No historical data

### Widget Performance
- 8 simple COUNT queries (indexed on `monitoring_url` and `last_ping_at`)
- Each table has <100 rows → sub-millisecond queries
- Cached for 300 seconds → zero DB hits on most page loads

### Monitoring Page Performance
- 8 UNION SELECT queries
- 30 rows total → no pagination needed
- Load time: <50ms

---

## Phase 2: NEXT YEAR (300 services)

### Architecture Change: monitoring_logs Table

When to migrate: **~200 services** or when cron runtime exceeds 5 minutes

### Migration Trigger
```
cron:monitor:check runtime
├─ 30 services: ~30 seconds (1s per HTTP check)
├─ 200 services: ~200 seconds (3.3 min — within hourly window)
├─ 500 services: ~500 seconds (8.3 min — exceeds safety margin)
└─ Migration needed BEFORE 500 services
```

### New Table: monitoring_logs

```sql
CREATE TABLE monitoring_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trackable_type  VARCHAR(50)    NOT NULL,    -- morph map short name (hosting, vps, etc.)
    trackable_id    BIGINT UNSIGNED NOT NULL,
    success         BOOLEAN        NOT NULL,
    status_code     SMALLINT       NULL,
    response_time_ms INT           NULL,
    error_message   TEXT           NULL,
    ssl_valid       BOOLEAN        NULL,
    ssl_days_remaining INT         NULL,
    ssl_issuer      VARCHAR(255)   NULL,
    ssl_valid_from  DATE           NULL,
    ssl_valid_to    DATE           NULL,
    checked_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_trackable (trackable_type, trackable_id, checked_at DESC),
    INDEX idx_success (success, checked_at),
    INDEX idx_checked_at (checked_at)
);
```

### Migration Strategy
1. Add `monitoring_logs` table (no downtime)
2. Dual-write: cron writes to both `last_ping_at` (old) AND `monitoring_logs` (new)
3. Widget query changes to `SELECT COUNT(*) FROM monitoring_logs WHERE checked_at > now() - 2h`
4. After 1 week of dual-write, deprecate `last_ping_at` reads from widget
5. Eventually drop `last_ping_at` columns from 8 service tables (when no longer referenced)

### Widget at 300 services
- Reads from `monitoring_logs` instead of 8 tables
- Single table, indexed, <1ms COUNT query
- Cached: same 300s TTL
- Widget appearance: **identical to Phase 1**

### Monitoring Page at 300 services
- Reads from `monitoring_logs` via `trackable_type` + `trackable_id` join to service tables
- Pagination: 25 per page
- Filters: type, status, search
- Sort: by last check (default), by status
- Load time: <200ms with proper indexes

---

## Phase 3: FIVE YEARS (3000 services)

### Architecture Change: Dedicated Monitoring Module

When to migrate: **~800 services** or when `monitoring_logs` exceeds 10M rows

Full timeline projection assuming 5 years of hourly checks:
```
30 services × 24 checks/day × 365 days × 5 years = 1,314,000 rows
300 services × 24 × 365 × 5 = 13,140,000 rows
3000 services × 24 × 365 × 5 = 131,400,000 rows
```

- At **30 services**: 1.3M rows in 5 years → trivial
- At **300 services**: 13M rows → need partial index or monthly partitioning
- At **3000 services**: 131M rows → **need dedicated monitoring infrastructure**

### Module Extraction

```
Before:                                              After:
┌───────────────────┐                      ┌───────────────────┐
│ DashboardController │                      │ DashboardController│
│ └─ MonitoringWidget│                      │ └─ MonitoringWidget│
│                   │                      │                   │
│ Web Controllers   │                      │ Web Controllers   │
│ └─ MonitorController│                     │ └─ MonitorController│
│                   │                      │                   │
│ Console            │                      │ MONITORING MODULE │
│ └─ MonitorCheck    │                      │ ├─ MonitoringController
└───────────────────┘                      │ ├─ monitoring page
                                           │ ├─ filters + search
                                           │ ├─ export
                                           │ └─ check history view
                                           │
                                           │ Console
                                           │ ├─ MonitorCheck
                                           │ └─ MonitorCleanup
                                           │
                                           │ Jobs/Queues
                                           │ └─ CheckService (per-service job)
                                           │
                                           └─ Config: monitoring.php
```

### Key Changes at Scale

| Capability | Phase 2 (300) | Phase 3 (3000) |
|------------|---------------|----------------|
| Check execution | Sequential in cron | Queue per service (redis/horizon) |
| Parallelism | None | 10-25 concurrent checks |
| Check timeout | 10s | Configurable (5-30s per service) |
| History retention | All rows | Partitioned, archived after 90 days |
| SSL checking | On every ping | Separate schedule (daily) |
| Aggregation | COUNT queries | Pre-aggregated materialized views |
| Alerting | Database + email | PagerDuty/Slack webhook integration |
| Dashboard widget | Same 5 metrics | Same 5 metrics (unchanged) |

### Will the Widget Still Work at 3000 Services?

**Yes, IF:**
- The widget reads from pre-aggregated/cached data (not raw COUNT queries)
- The monitoring_logs table is partitioned by month
- The cron is replaced by a queue-based system
- SSL checking runs on a separate, less frequent schedule

**The widget's interface remains identical.** The 5 numbers (monitored, online, offline, unchecked, SSL ≤30d) are universal. Only the data source changes.

### Threshold Decision Table

| Scale | Widget Works? | Page Works? | Action Needed |
|-------|-------------|-------------|---------------|
| 30 services | ✅ Yes | ✅ Yes | None |
| 300 services | ✅ Yes (cached) | ✅ Yes (paginated) | Add monitoring_logs table |
| 800 services | ✅ Yes (cached + partition) | ⚠️ Monitor | Partition monitoring_logs by month |
| 1500 services | ✅ Yes (pre-aggregated) | ⚠️ Add filters | Queue-based checks. Add search. |
| 3000 services | ✅ Yes (dedicated module) | ✅ Yes (filtered + cached) | Full module extraction |

---

## Summary: What Changes vs. What Stays

| Layer | Stays the same from 30 → 3000 |
|-------|-------------------------------|
| **Widget visual** | ✅ 5 stat cards + SSL line |
| **Widget cache TTL** | ✅ 300 seconds |
| **Widget RBAC** | ✅ Filters by user visibility |
| **MonitoringController** | ❌ Extracted to own module at ~800 |
| **Data source** | ❌ 8 tables → monitoring_logs → partitioned table |
| **Check execution** | ❌ Sequential cron → queue per service |
| **SSL persistence** | ❌ Not persisted → monitoring_logs column → dedicated table |
| **Monitoring page UI** | ❌ Simple table → full page with filters/search/export |

**Critical insight:** The widget is the most stable component. It should be built right the first time and never need a visual redesign. All complexity lives behind the widget — in the data layer and the monitoring page.
