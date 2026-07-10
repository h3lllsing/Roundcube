# Sprint 4 Proposal — SSL Monitoring + Suspension Audit Trail + Webhook Events UI

> Generated: 2026-07-04 | Based on remaining backlog from `01_TOP_10_NEXT_SPRINTS.md`

## Overview

| Item | Rank | Biz Value | Eng Cost | Hours |
|------|------|-----------|----------|-------|
| Scheduled SSL Certificate Monitoring | #6 | 7 | 2 | ~2h |
| Suspension Audit Trail | #4 (NR1 carry) | 4 | 2 | ~2h |
| Webhook Event Configuration UI | #3 | 5 | 2 | ~1.5h |
| **Total** | | | | **~6h** |

## Item A: Scheduled SSL Certificate Monitoring (2h)

**What:** Wire `MonitorService.checkSsl()` into the hourly `monitor:check` cron, store SSL expiry dates, update widget to show real SSL counts.

**Why now:** `checkSsl()` is fully implemented but only runs during manual checks. The monitoring widget's `ssl_expiring_30d` is placeholder `0`. This makes SSL data real.

**Files to change:**
- `app/Console/Commands/MonitorCheck.php` — call `checkSsl()` during cron run
- `app/Dashboard/MonitoringWidget.php` — replace `'ssl_expiring_30d' => 0` with real query
- ~30 lines total

## Item B: Suspension Audit Trail (2h)

**What:** Add `suspension_reason` column to users table, persist reason via suspend endpoint, complete the offboarding checklist suspend button.

**Why now:** Carried forward from Sprint 1 NR1. The suspend route exists, middleware exists, but no reason is recorded. Compliance gap.

**Files to change:**
- Migration: add `suspension_reason` text column to `users`
- `app/Models/User.php` — add to `$fillable`, add `$casts`
- `app/Http/Controllers/Web/UserController.php` — capture reason in suspend action
- `resources/views/users/show.blade.php` — complete offboarding suspend button with reason input
- ~40 lines total

## Item C: Webhook Event Configuration UI (1.5h)

**What:** Replace free-text events field with a documented multi-select dropdown showing all available webhook events (`vault.revealed`, `task.created`, `task.updated`, `expiring_soon`).

**Why now:** Admins must know exact event strings. Very low cost for significant UX improvement.

**Files to change:**
- `app/Http/Requests/StoreWebhookRequest.php` — add `'in:vault.revealed,task.created,task.updated,expiring_soon'` validation
- `resources/views/webhooks/create.blade.php` — replace text input with multi-select
- `resources/views/webhooks/edit.blade.php` — same
- ~20 lines total

## Why These Items

| Alternative | Why Not |
|-------------|---------|
| Import Upgrade (Excel) | 2-3 days for onboarding-only feature |
| Calendar Integration | 2-3 days, blank canvas |
| API Documentation | No external consumers yet |
| Activity Log Cleanup | Deferrable — no storage crisis imminent |
| Renew Test Coverage | Important but invisible to users |

## Sprint Theme: "Complete the Foundation"

Sprint 3 built monitoring visibility. Sprint 4 completes the remaining compliance and operational gaps — SSL data, user suspension records, and webhook configuration — before moving to larger features like Import Upgrade.

## Risk Assessment

| Risk | Probability | Mitigation |
|------|------------|------------|
| SSL check adds latency to cron | Low | Checks run sequentially; total <30s for current data set |
| `suspension_reason` migration conflicts | Very low | Additive column, no index, no foreign key |
| Webhook validation breaks existing webhooks | Low | Existing events match valid list |
| Any of these affects existing tests | Very low | All are additive changes |

## Acceptance Criteria

1. ✅ SSL cert data auto-collected during hourly `monitor:check`
2. ✅ Monitoring widget shows real SSL expiry count (not `0`)
3. ✅ Admins can enter suspension reason when suspending a user
4. ✅ Suspension reason visible in activity log / user detail
5. ✅ Webhook create/edit has event multi-select dropdown
6. ✅ All existing 296 tests continue to pass
7. ✅ Build passes with no warnings

## Sign-Off Request

| Role | Status |
|------|--------|
| CTO / Lead | ⏳ Pending review |
| Product Owner | ⏳ Pending review |
