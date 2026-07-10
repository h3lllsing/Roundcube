# OPSPILOT — Final Next Sprint Recommendation

> Generated: 2026-07-04
> Author: Staff Engineer (Enterprise Planning Analysis)

---

## One Sprint Only

## Sprint: Monitoring Dashboard + Critical Alerts

### What to Build

**Part A — Monitoring Dashboard Widget (4 hours)**

A new dashboard widget (`app/Dashboard/MonitoringWidget.php`) showing:

| Metric | Data Source |
|--------|------------|
| Total monitored services | COUNT of services WHERE `monitoring_url IS NOT NULL` |
| Current outages | COUNT of services WHERE `last_ping_at IS NULL` OR `last_ping_at < now()->subHours(2)` |
| Healthy services | Total monitored — outages |
| SSL certs expiring ≤30 days | Future — once SSL data is tracked per cron run |
| Last cron run timestamp | Cache key set by `monitor:check` command |

**Files to change:**
- `app/Dashboard/MonitoringWidget.php` — NEW (copies existing widget pattern)
- `resources/views/dashboard/widgets/monitoring.blade.php` — NEW
- `app/Http/Controllers/DashboardController.php` — register widget (+1 line)
- `tests/Feature/DashboardTest.php` — add assertions for widget data

**Part B — Email for Critical Alerts (2 hours)**

Modify `MonitorCheckFailed` notification to also send via `mail` channel:

- `app/Notifications/MonitorCheckFailed.php` — add `'mail'` to `via()`, implement `toMail()`
- Email template: `Service {name} is DOWN. Last response: {status_code}.`

### Total Engineering Cost: ~6 hours (one developer, <1 day)

### What This Sprint Delivers

| Dimension | Impact |
|-----------|--------|
| **Security Risk** | ✅ SSL expiry becomes visible; downtime detected faster |
| **Operational Friction** | ✅ No more clicking 20+ services to check uptime |
| **Data Integrity Risk** | ✅ Unchanged (read-only widget + one notification change) |
| **Business Risk** | ✅ Proactive monitoring prevents customer-impacting outages |
| **Technical Debt** | ✅ Surfaces existing infrastructure that was invisible |

---

## Why This Sprint and Not Others

| Alternative | Why Not Higher |
|-------------|----------------|
| Import Upgrade (Excel) | 2-3 days for a feature used during onboarding only |
| Suspension Audit Trail | Compliance gap but limited daily impact |
| Renew Test Coverage | Important but invisible to users |
| Activity Log Cleanup | Prevents future problem, no immediate daily impact |
| Any new feature (billing, SSO, mobile) | Premature — expands scope without addressing existing operational need |

---

## Risk Assessment

| Risk | Probability | Mitigation |
|------|------------|------------|
| Widget shows incorrect counts | Low | Unit test verifies query counts |
| Email toMail() has template error | Low | Test sends notification; preview available |
| Widget layout breaks dashboard | Very low | Follows same pattern as 9 existing widgets |
| Performance impact | Zero | Single SELECT COUNT query per cache interval |

---

## Acceptance Criteria

1. ✅ Dashboard shows total monitored services count
2. ✅ Dashboard shows current outage count
3. ✅ Each count is accurate (matches database)
4. ✅ Widget refreshes on cache interval (configurable)
5. ✅ `MonitorCheckFailed` notification delivers email with service name and status code
6. ✅ Email template renders correctly in all major email clients
7. ✅ Existing dashboard layout not broken
8. ✅ All existing tests continue to pass

---

## Sign-Off

| Approver | Status |
|----------|--------|
| CTO | ⏳ Pending |
| Product Owner | ⏳ Pending |
| Lead Engineer | ✅ Recommended |

---

**Final word:** OpsPilot has a fully functional, production-ready monitoring system that no one can see. Making it visible in 6 hours is the highest ROI investment available. Don't build new features while existing infrastructure is invisible.
