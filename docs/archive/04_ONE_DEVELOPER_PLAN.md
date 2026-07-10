# OPSPILOT — One Developer Plan

> Constraint: Single developer, single sprint
> Duration: ~1 week

---

## If You Have Only One Developer

The recommendation **does not change** — Monitoring Dashboard Widget.

Here's why:

| Criterion | Monitoring Widget | Import Upgrade | SSL Scheduling |
|-----------|------------------|----------------|----------------|
| Files to change | 4 | 8+ | 3 |
| New services/classes | 1 | 3 | 1 |
| Tests needed | 1 file | 2 files | 1 file |
| Risk of breaking existing | Near zero | Medium (CSV parsing) | Low |
| Can be rolled back? | Yes (revert widget) | Difficult (format changes) | Yes |
| Time to MVP | 2-3 hours | 8-10 hours | 3-4 hours |

**One developer should build the monitoring widget because:**
- It's the fastest path to production value
- Zero risk of breaking existing functionality
- The widget class pattern already exists (9 examples to copy)
- No new infrastructure, routes, or cron jobs needed

---

## If the Developer Has Extra Capacity (Days 3-5)

Add in this order:

1. **Email for Critical Alerts** (~2 hours) — add `mail` channel to `MonitorCheckFailed` notification. One file change. Complements the dashboard widget.

2. **Scheduled SSL Monitoring** (~3 hours) — modify `monitor:check` cron to persist SSL check results and alert on expiring certificates. Reuses `MonitorService.checkSsl()`.

3. **Suspension Audit Trail** (~2 hours) — migration + model change + view change. Closes Sprint 1 NR1.

---

## What One Developer Should NOT Do

| Task | Why Not |
|------|---------|
| Import Upgrade (Excel) | 2-3 days of work, medium risk, moderate usage |
| Calendar Integration | Calendar view is blank — requires new data layer |
| API Documentation | Premature unless external API consumers exist |
