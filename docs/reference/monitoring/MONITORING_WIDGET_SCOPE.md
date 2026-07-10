# OPSPILOT — Monitoring Widget Scope

> Clear boundary between what goes in the widget vs. what requires "View All"

---

## What Goes IN the Widget

### Core Metrics (always shown)

| Metric | SQL | Why It Belongs |
|--------|-----|----------------|
| **Monitored** | `COUNT WHERE monitoring_url IS NOT NULL` across 8 tables | Answers: "Am I tracking everything?" Single number. Zero context needed. |
| **Online** | `COUNT WHERE last_ping_at > now() - 2 hours` | Answers: "What's healthy?" Green number. Instant positive feedback. |
| **Offline** | `COUNT WHERE last_ping_at IS NOT NULL AND last_ping_at < now() - 2 hours` | Answers: "What's broken?" Red number. Calls for action. |
| **Unmonitored** | `COUNT WHERE monitoring_url IS NULL OR last_ping_at IS NULL` | Answers: "What have I not configured?" Gray number. Gap analysis. |
| **SSL ≤30d** | `0` (placeholder — persists later) | Critical security metric. Belongs in widget because it's a time-sensitive alert, not a detail. |

### Why These 5 Metrics

Every metric passes the **3-second test**: an operator can glance at the widget and understand the system state within 3 seconds. No scrolling. No clicking. No interpretation.

### Visual Design

```
┌──────────────────────────────────────────────┐
│ 🔵 MONITORING                      [View All] │
│                                              │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐│
│  │ 📡 18  │ │ ✅ 14  │ │ ❌  2  │ │ ⏳  2  ││
│  │Monitored│ │ Online │ │Offline │ │Unchecked││
│  └────────┘ └────────┘ └────────┘ └────────┘│
│                                              │
│  🔒 SSL expiring ≤30 days: 0                │
└──────────────────────────────────────────────┘
```

### Color Scheme

| Metric | Color | Meaning |
|--------|-------|---------|
| Monitored | Blue (info) | Neutral fact |
| Online | Green (success) | All good |
| Offline | Red (danger) | Action needed |
| Unchecked | Gray/Amber | Needs attention |
| SSL ≤30d | Red (if >0) / Gray (if 0) | Security risk |

---

## What Goes ONLY on the Monitoring Page

| Data | Reason It's NOT in Widget |
|------|--------------------------|
| Per-service status table | Too detailed — needs scrolling and filtering |
| Individual check timestamps | Requires context (which service?) |
| Response time per service | Requires comparison context (is 500ms normal for this service?) |
| SSL issuer name | Detail-only, no action required |
| SSL valid_from / valid_to dates | Certificate detail, not summary |
| Last check result details | Error messages are per-service, not aggregate |
| History/timeline | Temporal data requires charts or tables |
| "Check Now" button per service | Action belongs on the detail, not the summary |

### The "View All" Rule

**If an operator cannot understand the data within 3 seconds and without clicking, it belongs on the monitoring page, not in the widget.**

---

## What NEVER Goes in the Widget

| Item | Why |
|------|-----|
| Response time sparklines | Requires horizontal space; adds noise without actionable info at a glance |
| Individual error messages | Wrong level of detail for a summary widget |
| Uptime percentages (99.9%, etc.) | Requires historical data not yet collected; misleading with sparse data |
| Configuration forms | Widgets are for viewing, not editing |
| SSL certificate details (issuer, valid dates) | Too much text for a card |
| Status per service type breakdown | 8 categories × 4 statuses = 32 numbers. Information overload. |
| "Check Now" button | Triggers HTTP request — wrong scope for dashboard passive view |
| Export button | Monitoring page has it, not widget |
| Pagination | Widget shows 5 numbers. No pagination needed. |

---

## The 5-Year Test

**Will this widget still be useful in 5 years?**

| Metric | 5-Year Utility |
|--------|---------------|
| Total monitored | ✅ Always useful — growth metric |
| Online count | ✅ Always useful — health metric |
| Offline count | ✅ Always useful — alert metric |
| Unchecked count | ✅ Always useful — adoption metric |
| SSL ≤30d | ✅ Always useful — security metric |

**Yes.** These 5 metrics are universal. They don't depend on specific service types, UI trends, or business logic. They are the same metrics every monitoring dashboard has shown for 20+ years.

**What might change:**
- The data source (8 separate tables → unified monitoring_logs table)
- The status heuristic (2-hour window → computed from actual check results)
- But the widget itself remains the same

---

## The Widget Contract

```
INPUT:  Auth user (for RBAC filtering)
OUTPUT: 5 integers (total, online, offline, unchecked, ssl_expiring)
SIDE EFFECTS: None (read-only)
CACHE: 300 seconds
ERROR STATE: Show zeros + "Unable to load monitoring data"
EMPTY STATE: "No monitoring URLs configured. Add monitoring URLs to services to track uptime."
RENDERING: 4 stat cards + 1 SSL line. No charts. No tables. No forms.
```

## Acceptance Criteria

| # | Criterion | Pass/Fail |
|---|-----------|-----------|
| 1 | Widget shows "Monitored" count matching `COUNT WHERE monitoring_url IS NOT NULL` | — |
| 2 | Widget shows "Online" count matching `last_ping_at > 2h ago` | — |
| 3 | Widget shows "Offline" count matching `last_ping_at NOT NULL AND < 2h ago` | — |
| 4 | Widget shows "Unchecked" count matching `last_ping_at IS NULL` | — |
| 5 | Widget shows "SSL ≤30 days" (0 until SSL persistence is built) | — |
| 6 | Widget caches for 300 seconds | — |
| 7 | Widget only counts services the user can see (RBAC) | — |
| 8 | "View All" links to `/monitoring` | — |
| 9 | Zero database queries when cache is warm | — |
| 10 | All counts update within 1 cache refresh after cron runs | — |
