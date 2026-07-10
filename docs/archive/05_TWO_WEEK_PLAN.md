# OPSPILOT — Two Week Plan

> Constraint: Two calendar weeks, team of 2-3 developers
> Duration: 10 working days

---

## Sprint Structure

### Week 1: Foundation (Days 1-5)

| Day | Task | Owner | Dependencies |
|-----|------|-------|-------------|
| 1-2 | **Monitoring Dashboard Widget** (3.75h) | Dev A | None |
| 2 | **Email for Critical Alerts** (2h) | Dev A | None |
| 3-4 | **Scheduled SSL Monitoring** (4h) | Dev B | MonitorService (exists) |
| 4-5 | **Suspension Audit Trail** (2h) | Dev B | None |
| 5 | **Renew Endpoint Tests** (4h) | Dev A | None |

### Week 2: Polish & Hardening (Days 6-10)

| Day | Task | Owner | Dependencies |
|-----|------|-------|-------------|
| 6-7 | **Import Upgrade: Excel** (8h) | Dev B | — |
| 7 | **Webhook Events UI** (3h) | Dev A | — |
| 8 | **Activity Log Cleanup Cron** (2h) | Dev A | — |
| 8-9 | **API Documentation** (8h) | Dev B | — |
| 9-10 | Buffer / bug fixes / code review | Both | All |

---

## What Ships After Two Weeks

1. ✅ Monitoring Dashboard Widget — uptime & SSL at a glance
2. ✅ Email alerts for service downtime
3. ✅ SSL certificates automatically monitored
4. ✅ Suspension reasons tracked & displayed
5. ✅ Renew endpoint covered by tests
6. ✅ Excel import support
7. ✅ Webhook events documented in UI
8. ✅ Old activity logs auto-pruned

---

## Tradeoffs

| If we cut | We lose | Impact |
|-----------|---------|--------|
| API Documentation | External integrations harder | Low (no external consumers) |
| Excel Import | CSV-only onboarding | Medium (workaround exists) |
| Webhook Events UI | Free-text events continue | Low (admins know the strings) |
| SSL Monitoring | Certificates unchecked automatically | Medium (manual check exists) |

**If forced to cut to 1 week:** Ship Week 1 only. Skip Import, Webhook UI, Activity Log Cleanup, API Docs.
