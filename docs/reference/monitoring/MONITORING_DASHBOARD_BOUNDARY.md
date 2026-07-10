# OPSPILOT — Monitoring Dashboard Boundary

> Defining what dashboard is for, and what belongs elsewhere

---

## 1. Dashboard Purpose Statement

**The OpsPilot dashboard is a summary cockpit — not an operations center.**

It answers three questions:
1. **What needs my attention?** (offline services, overdue renewals, unread notifications)
2. **What is the overall health?** (active counts, revenue, uptime)
3. **What happened recently?** (last 10 activities)

It does NOT answer:
- "What is the exact status of service X?"
- "What was the response time trend last week?"
- "Which SSL certificates expire on what dates?"
- "Who configured monitoring for service Y?"

These belong on dedicated pages.

---

## 2. Dashboard vs. Monitoring Page Boundary

```
┌─────────────────────────────────────────────────────────────┐
│  DASHBOARD (summary cockpit)   │   MONITORING PAGE (detail) │
│                                │                            │
│  "How many services are        │  "Which specific services   │
│   offline RIGHT NOW?"          │   are offline, and why?"   │
│                                │                            │
│  MONITORING WIDGET:            │  MONITORING PAGE:          │
│  ┌────────────────────────┐    │  ┌──────────────────────┐  │
│  │ 📡 18     ✅ 14       │    │  │ Type    Name    Status│  │
│  │ ❌  2     ⏳  2       │    │  │ Hosting acme.org ✅ │  │
│  │ SSL ≤30d: 0            │    │  │ VPS     vps-01  ❌  │  │
│  └────────────────────────┘    │  │ ...                  │  │
│  [View All] ──────────────────▶│  │ Filters  Search  Pag │  │
└────────────────────────────────│──────────────────────────┘  │
                                 └──────────────────────────────┘
```

## 3. What Dashboard Should Show (Summary vs. Operations)

### Dashboard = SUMMARY

The dashboard is for **awareness and triage**, not for management. Each widget should fit in a single dashboard column (~300px wide by default) and be scannable in 3 seconds.

| Existing Widget | Type | Fits Summary Model? |
|-----------------|------|---------------------|
| OperationsWidget | Summary | ✅ Counts and charts fit summary |
| RenewalsWidget | Summary | ✅ Expiry counts fit summary |
| TasksWidget | Summary | ✅ Task counts fit summary |
| AssetsWidget | Summary | ✅ Asset counts fit summary |
| QuickActionsWidget | Operations | ❌ **Should this be here?** Quick actions are operations shortcuts, not summary data. But they're low-visual-weight and useful. Acceptable exception. |
| ActivityWidget | Summary | ✅ Recent events fit summary |
| VaultWidget | Summary | ✅ Reveal counts fit summary |
| SmtpWidget | Status | ✅ Profile health fits summary |
| ServerHealthWidget | Status | ✅ System health fits summary |
| **MonitoringWidget (NEW)** | **Summary** | ✅ **Uptime counts fit summary** |

**Verdict:** 8 of 9 existing widgets are summary-oriented. QuickActionsWidget is the exception. The new MonitoringWidget fits the pattern.

### What Dashboard Should NEVER Show

| Item | Why It Violates Summary Model |
|------|-------------------------------|
| Full data tables | Requires scrolling, pagination, filtering — page-level complexity |
| Forms / inputs | Dashboard is for reading, not editing |
| Per-item actions (edit, delete) | Actions belong on the item's own page |
| Configuration controls | Dashboard is not settings |
| Charts requiring interaction | Pie/doughnut charts (like existing OperationsWidget) are borderline acceptable if they fit in a card |
| Time-series data | Trends need horizontal space and context — monitoring page territory |

---

## 4. Where Monitoring History Should Live

### Three-tier storage

```
┌──────────────────────────────────────────────────────────────┐
│  TIER 1: SERVICE MODEL (latest check — sprint scope)        │
│                                                              │
│  hosting@last_ping_at = 2026-07-04 14:00:00                  │
│  → Widget reads: healthy/offline/unchecked                   │
│  → Monitoring page reads: per-service status                 │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│  TIER 2: MONITORING_LOGS (full history — future phase)      │
│                                                              │
│  id | trackable_type | trackable_id | success | status_code │
│     | response_time_ms | error_message | checked_at          │
│  → Monitoring page reads: trend, history, detailed view      │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│  TIER 3: SSL RESULTS (separate — future phase)              │
│                                                              │
│  Stored in monitoring_logs SSL columns, or separate table    │
│  → Dedicated SSL page or section on monitoring page          │
└──────────────────────────────────────────────────────────────┘
```

### Where Each Tier is Used

| Tier | Stored In | Read By | Update Frequency |
|------|-----------|---------|-----------------|
| Latest check | `last_ping_at` on 8 service tables | Widget, Monitoring page, Service detail | Every cron run (hourly) |
| Check history | `monitoring_logs` (future) | Monitoring page (trends), Reports | Every cron run |
| SSL data | `monitoring_logs.ssl_*` (future) | Monitoring page (SSL section), Widget (count) | Daily (separate schedule) |

### History Should NOT Live in:

| Location | Why Not |
|----------|---------|
| Dashboard widget | Too much data. Widget shows "now," not "history." |
| Session flash | Ephemeral. Only shows latest manual check. |
| Single service table | Cannot store multiple check results per service. |

---

## 5. Integration Points (No Data Duplication)

### Monitoring + Renewals

| System | Owns | Shares |
|--------|------|--------|
| Renewals | `expiry_date`, `renewal_date`, notification settings | `monitoring_url`, `last_ping_at` (on ExpiryTracker model) |
| Monitoring | `last_ping_at`, check results, SSL data | Reads `monitoring_url` from ExpiryTracker |

**Duplication risk:** Low. Different columns, different concerns. Both exist on the same ExpiryTracker model row. No cross-system sync needed.

### Monitoring + Assets

| System | Owns | Shares |
|--------|------|--------|
| Assets | asset_tag, serial_number, assigned_to, location | `monitoring_url`, `last_ping_at` (on Asset model if monitoring configured) |
| Monitoring | Check results | Reads `monitoring_url` from Asset |

**Duplication risk:** None. Assets are NOT in the 8 monitored types (no Asset in MonitorCheck command or MonitorController type list). Assets are not currently monitored.

**Future consideration:** Should Assets be monitored? Asset model has `monitoring_url` field but is NOT included in `monitor:check` command. If needed, add `Asset::class` to the `$models` array — no data duplication.

### Monitoring + Notifications

| System | Owns | Shares |
|--------|------|--------|
| Monitoring | `MonitorCheckFailed` event | Event payload (type, error, itemName) |
| Notifications | `MonitorCheckFailedNotification`, delivery channels | Receives event data |

**Duplication risk:** None. Standard event-driven pattern. Notification stores its own copy of relevant data (via `toArray()`) which is correct — notifications are immutable audit records.

### Monitoring + Service Providers

| System | Owns | Shares |
|--------|------|--------|
| Service Providers | Provider name, contact info, credentials | `monitoring_url`, `last_ping_at` (on ServiceProvider model) |
| Monitoring | Check results | Reads `monitoring_url` from ServiceProvider |

**Duplication risk:** None. Distinct columns on the same model row.

### Monitoring + Tasks

| System | Owns | Shares |
|--------|------|--------|
| Monitoring | Failed check events | Could trigger task creation |
| Tasks | Task lifecycle, assignments | Receives monitoring context |

**Duplication risk:** Medium IF monitoring auto-creates tasks on failure. Task would duplicate: `title` (service name), `description` (error message). Acceptable — task is a new record, not a copy of monitoring data.

**Recommendation:** Do NOT auto-create tasks for monitoring failures in this sprint. Add as a separate feature later with clear ownership boundary.

---

## 6. Summary: Boundary Rules

| Rule | Applies To | Rationale |
|------|-----------|-----------|
| Dashboard widget shows summary ONLY | MonitoringWidget | 5 numbers. No tables. No forms. |
| History lives on monitoring page | monitoring_logs (future) | Temporal data needs space |
| SSL details live on monitoring page | monitoring_logs.ssl_* | Too verbose for widget |
| Per-service check results on service detail page | monitor-result.blade.php | Existing pattern — correct |
| Monitoring does NOT duplicate renewal dates | Cross-system | Distinct columns, distinct concerns |
| Monitoring does NOT duplicate task data | Cross-system | Tasks are separate bounded context |
| Monitoring page is NOT the dashboard | Architecture | Different purpose (summary vs. detail) |
| Configuration is NOT on monitoring page | UX | Edit monitoring_url on the service itself |
