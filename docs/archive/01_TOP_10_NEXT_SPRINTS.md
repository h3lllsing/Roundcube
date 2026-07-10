# OPSPILOT — Top 10 Remaining Opportunities

> Analysis Date: 2026-07-04
> Context: Production-ready system with 30+ features, 9 dashboard widgets, full RBAC, API, webhooks, monitoring, import/export, global search.

---

## 1. Monitoring Dashboard Widget

**What:** Add an aggregate monitoring/uptime widget to the dashboard showing all services' ping status, SSL expiry, and recent failures at a glance.

**Why now:** The entire monitoring infrastructure is built (MonitorService with HTTP ping + SSL check, hourly cron `monitor:check`, `MonitorCheckFailed` event with in-app notifications) but has **zero dashboard visibility**. Operators must click into individual service detail views to see monitoring status. This turns a fully built hidden system into an operational control center.

**Evidence:**
- `app/Services/MonitorService.php` — complete ping + SSL check engine
- `app/Console/Commands/MonitorCheck.php` — hourly cron (fully operational)
- `app/Events/MonitorCheckFailed.php` + listeners — notifications fire
- All 8 service models have `monitoring_url` + `last_ping_at`
- `resources/views/components/monitor-button.blade.php` + `monitor-result.blade.php` — per-resource UI exists
- Dashboard currently has 9 widgets — **no monitoring widget**

---

## 2. Import System Upgrade (Excel + Templates + Column Mapping)

**What:** Add Excel (.xlsx) support, downloadable CSV templates per entity type, and a column-mapping UI for imports.

**Why now:** The importer supports 17 entity types but is CSV-only with strict header-must-match-field-name requirements. This creates friction for every bulk data onboarding. Adding Excel + templates + mapping eliminates the most common support requests.

**Evidence:**
- `app/Http/Controllers/Web/ImportController.php:70` — `mimes:csv,txt` only
- 17 import types registered
- No template download endpoint exists
- No column mapping UI (does direct `$model->create($row)` after header stripping)

---

## 3. Webhook Event Configuration UI

**What:** Replace the free-text events field with a documented multi-select dropdown showing all available webhook events.

**Why now:** The webhook system fires 4 event types (`vault.revealed`, `task.created`, `task.updated`, `expiring_soon`) but admins must know the exact string. No in-app documentation. Very low engineering cost for significant UX improvement.

**Evidence:**
- `app/Http/Requests/StoreWebhookRequest.php:21` — `'events.*' => 'string'` (free text)
- `app/Services/WebhookService.php` — event strings hardcoded in service logic
- Webhook create/edit views show no event documentation

---

## 4. User Suspension Audit Trail (suspension_reason column)

**What:** Add `suspension_reason` text column to users table, persist reason during suspend, complete the offboarding checklist suspend button.

**Why now:** Carried over from Sprint 1 NR1. The suspend route exists (`PATCH users/{user}/suspend`), the `CheckSuspended` middleware exists, but there's no way to document *why* a user was suspended. Compliance gap.

**Evidence:**
- Migration `2026_05_24_000002` adds `suspended_at` but **no** `suspension_reason`
- `app/Models/User.php` — no `suspension_reason` in fillable or casts
- Zero grep results for `suspension_reason` across entire codebase

---

## 5. Renew Endpoint Test Coverage

**What:** Add comprehensive tests for the `renew` action on expiry-trackers.

**Why now:** The renew endpoint (Sprint 2) modifies `expiry_date` on both the tracker and the linked service, logs an activity, and has **zero test coverage**. A regression could silently corrupt renewal dates across the entire system.

**Evidence:**
- `app/Http/Controllers/Web/ExpiryTrackerController.php` — `renew()` method exists
- `tests/Feature/ExpiryTrackerTest.php` — no tests for renew endpoint
- No test for: permission check, date extension, trackable sync, activity logging, cancelled-item rejection

---

## 6. Scheduled SSL Certificate Monitoring

**What:** Add SSL certificate expiry checking to the hourly `monitor:check` cron (currently only runs during manual checks).

**Why now:** The `MonitorService.checkSsl()` method is fully implemented (returns days_remaining, issuer, valid_from/to) but only runs during manual "Check Monitoring" button clicks. This means SSL expiry is **never automatically monitored** despite the infrastructure existing.

**Evidence:**
- `app/Services/MonitorService.php:65` — `checkSsl()` method exists
- `app/Console/Commands/MonitorCheck.php` — only calls `$monitor->check()` which runs BOTH ping and SSL, but SSL checks may be suppressed or not tracked
- SSL data not surfaced on any dashboard or aggregate view

---

## 7. Activity Log Cleanup Cron

**What:** Add a scheduled task to prune activity logs older than a configurable retention period (default 90 days).

**Why now:** Activity logs grow unbounded. Every user action creates a log entry. On a busy system with 50+ users, activity_logs table can grow by 10,000+ rows per month. No existing cleanup job.

**Evidence:**
- `routes/console.php` — 5 cron jobs listed, none for log cleanup
- No config for `activitylog.delete_records_older_than_days`
- `app/Models/ActivityLog` uses Spatie Activitylog package which supports cleanup but is not configured

---

## 8. Notification Channel Expansion (Email for critical alerts)

**What:** Add email channel to `MonitorCheckFailed` notifications (currently database-only).

**Why now:** When monitoring detects a down service, the in-app notification is only visible when a user logs in. Critical downtime requires email/push alerting to reach ops staff immediately.

**Evidence:**
- `app/Notifications/MonitorCheckFailed.php` — uses `database` channel only
- `app/Notifications/TaskAssigned.php` — uses `database` channel only
- `app/Notifications/NoteAdded.php` — uses `database` channel only
- `app/Notifications/VaultPasswordRevealed.php` — uses `database` channel only
- Only `ExpiringSoon` notification uses both `database` and `mail`

---

## 9. Calendar Integration (Tasks + Expiry Dates)

**What:** Wire the existing calendar view to show task due dates and renewal/expiry dates.

**Why now:** A calendar view exists at `resources/views/calendar/index.blade.php` but there's no evidence it integrates with actual task data or expiry dates. It's a blank canvas. This is a missed opportunity to show operators their weekly/monthly workload at a glance.

**Evidence:**
- `resources/views/calendar/index.blade.php` exists
- `app/Http/Controllers/Web/CalendarController.php` — need to verify data integration
- `routes/web.php` — `GET /calendar` route exists
- Tasks have `due_date`, expiry-trackers have `expiry_date` and `renewal_date`

---

## 10. API Documentation (OpenAPI/Swagger)

**What:** Generate OpenAPI documentation for the existing API endpoints.

**Why now:** The API has 50+ endpoints covering virtually every feature, but there's no documentation. Every integration requires reading source code. This blocks external integrations and internal tooling.

**Evidence:**
- `routes/api.php` — ~50+ endpoints
- No OpenAPI/Swagger annotation or config file found
- No API docs link in the admin UI
- API is feature-complete but undiscoverable

---

## Ranking Summary

| Rank | Opportunity | Category | Biz Value | Eng Cost | Risk | User Impact |
|------|-------------|----------|-----------|----------|------|-------------|
| 1 | Monitoring Dashboard Widget | Operational | 9 | 3 | 1 | 9 |
| 2 | Import Upgrade (Excel+Mapping) | Operational | 7 | 5 | 2 | 5 |
| 3 | Webhook Events UI | UX | 5 | 2 | 1 | 3 |
| 4 | Suspension Audit Trail | Compliance | 4 | 2 | 1 | 3 |
| 5 | Renew Test Coverage | Quality | 6 | 3 | 1 | 0 |
| 6 | Scheduled SSL Monitoring | Security | 7 | 2 | 1 | 4 |
| 7 | Activity Log Cleanup | Maintenance | 5 | 1 | 1 | 0 |
| 8 | Email for Critical Alerts | Operational | 8 | 2 | 1 | 6 |
| 9 | Calendar Integration | UX | 5 | 5 | 2 | 4 |
| 10 | API Documentation | Integration | 4 | 4 | 1 | 5 |
