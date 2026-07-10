# OPSPILOT — CTO Decision Report

> From: Staff Engineer → CTO
> Subject: Single highest-ROI next sprint recommendation

---

## Executive Summary

Build a **Monitoring Dashboard Widget**. The infrastructure is fully built. The monitoring system (MonitorService, hourly cron, failure events, in-app notifications) is production-ready but **completely invisible** from the dashboard. This sprint turns a hidden operational tool into the primary control center. Estimated engineering effort: **0.5 days**.

---

## The Case

### What Exists Today

| Component | Status | Location |
|-----------|--------|----------|
| HTTP ping (GET, 10s timeout) | ✅ Complete | `app/Services/MonitorService.php:28` |
| SSL certificate validation | ✅ Complete | `app/Services/MonitorService.php:65` |
| Hourly cron (all 8 service types) | ✅ Running | `app/Console/Commands/MonitorCheck.php` |
| Failure event + listeners | ✅ Firing | `Events/MonitorCheckFailed.php` |
| In-app failure notification | ✅ Delivering | `Notifications/MonitorCheckFailed.php` |
| `monitoring_url` + `last_ping_at` on all models | ✅ Migrated | Migration `2026_05_24_080000` |
| Per-resource monitor button | ✅ In views | `monitor-button.blade.php` |
| Dashboard widget | ❌ **MISSING** | — |

### What's Missing

A single dashboard widget showing:

- **Total services monitored** (vs. total services with monitoring_url set)
- **Current outages** (services where last_ping failed or is stale)
- **SSL certificates expiring within 30/60/90 days**
- **Last check timestamp** per service type
- **Uptime trend** (optional — % successful checks in last 24h/7d)

### Engineering Cost

**0.5 days** — one developer, one widget:

| Task | Hours |
|------|-------|
| Create `MonitoringWidget.php` in `app/Dashboard/` | 1 |
| Build dashboard view partial `monitoring.blade.php` | 1 |
| Register widget in `DashboardController` | 0.25 |
| Tests for widget (return correct counts) | 1.5 |
| **Total** | **3.75** |

### What Makes This Undeniably #1

1. **Every operator visits the dashboard daily.** The monitoring widget appears on every page load, at every login.
2. **Zero risk.** Read-only query on existing columns. No data mutation. No new routes. No new cron jobs.
3. **Reuses fully built infrastructure.** MonitorService already collects all data. The cron already runs hourly. `last_ping_at` is already populated.
4. **Complements the existing 9 widgets.** The dashboard currently shows operations, renewals, tasks, assets, vault — but NOT uptime. This is a glaring gap.
5. **Enables future email alerts.** Once monitoring is visible on the dashboard, the next sprint (email for critical alerts) has immediate context.

---

## If I'm Wrong

If monitoring is not the #1 priority, the alternative is **Email for Critical Alerts** (ROI: 7.25):

- `MonitorCheckFailed` notification currently uses `database` channel only
- Adding `mail` channel costs ~2 hours
- Production impact: ops staff get email/SMS when a service goes down
- But: without the dashboard widget, they still have no aggregate view of which services are healthy

The monitoring dashboard is a **force multiplier** — it makes all existing monitoring infrastructure visible and actionable. Email alerts without a dashboard are reactive. Dashboard + alerts = proactive operations.

---

## Recommendation

**Approve Sprint 3: Monitoring Dashboard Widget.**

Build one widget. Change four files (widget class, blade partial, controller registration, test file). Deploy in half a day.

Everything else on the backlog is either higher risk, lower impact, or both.
