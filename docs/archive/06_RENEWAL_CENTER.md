# 6. Renewal Center (Expiry Trackers)

## Overview

The Renewal Center is the single most complex subsystem. It tracks expirations for all services and sends automated renewal reminders. The model is `ExpiryTracker`, but the UI displays route slug `expiry-trackers`.

## Two Types of Expiry Trackers

### 1. Linked (has trackable)
- Connected to a source service via polymorphic `trackable` relationship.
- **Source services:** Domain, Hosting, VPS, VoIP, OtherService, DomainEmail.
- Key fields **synced FROM source**: `name`, `expire_date`, `cost`, `renewal_status`.
- These fields are **owned by the source** — they appear as read-only in the tracker interface.
- When source service updates, the tracker syncs (via observer or controller? NEEDS CONFIRMATION — likely the source controller update method also updates linked tracker).
- Cannot exist without the source service. Deleting source should cascade-delete linked tracker.
- Frontend must never create a duplicate linked tracker — a source service can have AT MOST ONE linked tracker.

### 2. Standalone (no trackable)
- Independent renewal entry not tied to any service.
- User manages name, expire_date, cost, and renewal_status directly.
- Fields are editable.
- Can optionally be assigned to a `module_id` for permission scoping.
- Can optionally be assigned to a `user_id` for ownership.

## Key Fields

| Field | Linked Behavior | Standalone Behavior |
|---|---|---|
| `name` | Read-only, synced from source | Editable |
| `expire_date` | Read-only, synced from source | Editable |
| `cost` | Read-only, synced from source | Editable |
| `renewal_status` | Read-only, synced from source | Editable |
| `renewal_status_date` | Read-only, synced from source | Editable |
| `is_completed` | Editable (manual completion toggle) | Editable |
| `completed_by` | Set on completion | Set on completion |
| `completed_at` | Set on completion | Set on completion |
| `notes` | Editable (tracker-specific notes) | Editable |
| `notify_before_days` | Editable (overrides global config) | Editable |
| `module_id` | Inherited from source? | Editable |
| `user_id` | Editable (assignee) | Editable |

## Renewal Statuses

Defined in the database as enum or string values. Possible values (needs confirmation on exact list):
- `active` — Not yet renewing, everything fine.
- `pending_renewal` — Renewal due soon / due now.
- `completed` — Has been renewed for another period.
- `cancelled` — Service cancelled, no longer tracked.
- `expired` — Passed expire_date without renewal.

## Notification System

**Service:** `RenewalNotificationService`
**File:** `app/Services/RenewalNotificationService.php`

### How Notifications Work
1. A daily scheduled command (`Command\SendRenewalNotifications`) queries expiry trackers.
2. For each uncompleted tracker where `expire_date - notify_before_days <= today`, a notification is dispatched.
3. Notifications are sent to the assignee (`user_id`).
4. Channel: database notification + email notification.
5. Each notification is logged (both to activity log and as a Laravel notification record).

### Notification Content
- Shows tracker name, expire_date, cost, days remaining.
- Includes link to the tracker detail page.

### Edge Cases
- `renewal_status` = 'completed' means no further notifications.
- `is_completed = true` means permanently suppressed.
- `notify_before_days` defaults to `config('renewals.notify_days_before')` (currently the only active config key in renewals.php).
- A user can have multiple trackers expiring on the same day — each sends a separate notification.

## ExpiryTracker Index Page

- Paginated list of all trackers (default 10 per page).
- Shows: name, trackable type icon/badge, expire_date, days remaining, cost, assignee, status.
- Filters: status filter (all/pending/completed/cancelled), date range.
- Searchable: name.
- Sortable: expire_date ASC (default, soonest first), name.
- Eager loads: `trackable` (polymorphic), `user` (assignee).

## Creation Flow

1. **From a source service show page:** "Add Renewal Tracker" button creates a linked tracker pre-populated with source data.
2. **From Renewal Center:** "Add Renewal" creates a standalone tracker.
3. **Validation:** Linked trackers check that no tracker already exists for this trackable (unique per trackable_id + trackable_type).

## ExpiryTracker → Activity Log

- Create → logged with subject = tracker.
- Update → logged with subject = tracker.
- Delete → logged with subject = tracker.
- Complete → logged as update with `is_completed` change.
- Uncomplete → logged as update with `is_completed` change.

## Config

**File:** `config/renewals.php`

Currently has only ONE active key:
- `notify_days_before` → default 7 days before expiry (used as fallback when a tracker has no individual `notify_before_days` setting).

Previously had 4 dead keys (removed Sprint B): `expiry_statuses`, `renewal_statuses`, `polling_intervals`, `polling_statuses`. None were consumed anywhere.
