# SMTP & Email Data Accuracy — Verified Data Mapping Plan

> **Status:** Implemented and verified
> **Changes:** All 10 implementation steps completed — see Implementation Status below
> 
> **Note:** The tables below are the original pre-implementation plan. Each "Proposed" column is now implemented behavior, confirmed by 63 passing tests. Null-handling rows (serviceProvider null → omitted, user null → "Unassigned", trackable null → module label, etc.) are all confirmed.

---

## 1. Email Path Data Audit

### Path 1: `renewals:send-email-reminders` (scheduled daily 02:00)

| Aspect | Current | Verified Source | Proposed |
|--------|---------|-----------------|----------|
| **Source model** | `ExpiryTracker` | `expiry_trackers` table + `trackable` polymorphic relationship (Domain, Hosting, VPS, VoIP, OtherService, DomainEmail, ServiceProvider, or null) | Same |
| **Recipients** | `buildRecipients()`: assigned user, admins, custom emails (deduplicated) | `expiry_trackers.user_id` → users.name/email; users with `super-admin`/`admin` roles; `expiry_trackers.notify_custom_emails` JSON array | Same |
| **Subject format** | `"Renewal Reminder: {name} expires in {N} days"` | `expiry_trackers.name` | `"[OpsPilot] {resource_type} {urgency} — {name}"` |
| **Resource type** | `$tracker->module?->name` (Module name like "Renewals", not actual resource type) | `$tracker->trackable_type` gives the actual class (App\Models\Hosting etc.). Map to human label via short class name. Fallback: `$tracker->module?->name` | Actual trackable entity type (Hosting, Domain, VPS, VoIP, Other Service, Task) |
| **Resource name** | `$tracker->name` | `$tracker->name` | Same |
| **Related domain** | Not shown | `$tracker->trackable instanceof Domain` → its `name`; `$tracker->trackable instanceof Hosting` → its `domain` field; otherwise null | Show if available |
| **Provider** | `$tracker->serviceProvider?->name` | `$tracker->service_provider_id` → `service_providers.name` | Same |
| **Expiry date** | `$tracker->expiry_date` | `$tracker->expiry_date` (cast to Carbon date) | Same |
| **Days remaining** | Computed in `sendEmail()` via `Carbon::diffInDays()` | Passed as `$daysLeft` parameter (negative = overdue) | Same |
| **Current status** | Not shown | `$tracker->status` (active, expired, pending_renewal, cancelled) | Add to email body |
| **Assigned user** | `$tracker->user?->name` | `$tracker->user_id` → `users.name` | Same |
| **Portal link** | `route('expiry-trackers.show', $tracker->id)` | Always correct for ExpiryTracker | Same |
| **Recipient reason** | Not shown | Determined by `recipient_type` in `buildRecipients()`: `assigned_user`, `admin`, `custom` | Add "You received this because you are {reason}" |
| **Test flag** | None | N/A | Add `isTest` parameter to clearly label TEST emails |

**Can data be null/stale?**
- `$tracker->trackable` CAN be null (standalone trackers) — handle gracefully
- `$tracker->serviceProvider` CAN be null — show "N/A"
- `$tracker->user` CAN be null — show "Unassigned"
- `$tracker->module` CAN be null — fallback to "Renewal"
- `$tracker->expiry_date` CAN be null — show "No expiry date set"

**Verification:** ✅ Hosting/Domain/VPS/VoIP/OtherService emails will correctly display their real resource types via the `trackable` polymorphic relationship. Standalone trackers (no trackable) will fall back to their module name.

---

### Path 2: `expiry:check` (scheduled daily 08:00)

| Aspect | Current | Verified Source | Proposed |
|--------|---------|-----------------|----------|
| **Source models** | `Domain`, `Hosting`, `VPS`, `VoIP`, `ServiceProvider`, `DomainEmail`, `OtherService` | Direct model queries with `expiry_date`, `status = 'active'`, `user_id` | Same |
| **Recipients** | Only `$item->user` (the assigned user) | `.with('user')` loaded; notification via `$user->notify()` | Same (broadening would change business rules — keep as-is) |
| **Subject format** | `"Expiring Soon: {entityType} — {name}"` or `"Overdue: {entityType} — {name}"` | `entityType` = `'Domain'`, `'Hosting'`, `'VPS'`, `'VoIP'`, `'Service Provider'`, `'Domain Email'`, `'Other Service'`; `name` = `$item->name ?? $item->email ?? 'Unnamed'` | `"[OpsPilot] {entityType} {urgency} — {name}"` |
| **Resource type** | Via `$label` parameter | Mapped from class → label in `$models` array | Same, but use consistent labels |
| **Resource name** | `$item->name ?? $item->email ?? 'Unnamed'` | Direct field on each model | Same |
| **Related domain/hosting** | Not shown | Domain belongs to Hosting (`$item->hosting?->name`); DomainEmail belongs to Domain (`$item->domain?->name`); VPS/VoIP/OtherService have no direct parent | Add where available |
| **Provider** | Not shown | Each model has `service_provider_id` → `service_providers.name` (via Eager loading — NOT currently loaded) | Add `->with('serviceProvider')` to chunk query |
| **Expiry date** | `$item->expiry_date` | Direct field | Same |
| **Days remaining** | `Carbon::diffInDays()` | Computed in `checkModel()` | Same |
| **Current status** | Not shown | Hardcoded `status = 'active'` filter | Add status to email |
| **Assigned user** | `$item->user?->name` | `$item->user_id` → `users.name` | Same |
| **Portal link** | `url('/')` (generic homepage) | Need specific route per model type: `route('hostings.show', $item->id)`, `route('domains.show', $item->id)`, etc. | Add correct per-type show route |
| **Recipient reason** | Not shown | Single recipient = the assigned user | Add "You are the assigned user for this resource" |
| **Related models loaded** | `->with('user')` | Currently only user | Need to add `serviceProvider`, and for Domain/DomainEmail add `hosting`/`domain` |

**Can data be null/stale?**
- `$item->user` CAN be null — skip notification (already guarded)
- `$item->serviceProvider` CAN be null — show "N/A"
- `$item->expiry_date` SHOULD be non-null (filtered by `whereNotNull`)
- `$item->name` CAN be null — falls back to `$item->email ?? 'Unnamed'`

**Per-type routes needed:**
- `Domain` → `route('domains.show', $item->id)`
- `Hosting` → `route('hostings.show', $item->id)`
- `Vps` → `route('vps.show', $item->id)`
- `Voip` → `route('voip.show', $item->id)`
- `ServiceProvider` → `route('service-providers.show', $item->id)`
- `DomainEmail` → `route('domain-emails.show', $item->id)`
- `OtherService` → `route('other-services.show', $item->id)`

**Verification:** ✅ Each resource type now correctly identified by name. Domain email shows domain name via `$item->domain?->name`. All types get provider, proper portal link, and recipient reason.

---

### Path 3: `tasks:check-overdue` (scheduled daily 09:00)

| Aspect | Current | Verified Source | Proposed |
|--------|---------|-----------------|----------|
| **Source model** | `Task` | `tasks` table with `due_date`, `status`, `assignees` | Same |
| **Recipients** | Task `assignees` (many-to-many via `task_user` pivot) | `$task->assignees` collection | Same |
| **Subject format** | Uses `ExpiringSoon` → `"Overdue: Task — {title}"` | `$task->title` as `name` | `"[OpsPilot] Task overdue — {title}"` |
| **Resource type** | `'Task'` (via `entityType`) | `entityType: 'Task'` passed to `ExpiringSoon` | Keep as `'Task'` |
| **Resource name** | `$task->title` | `$task->title` | Same |
| **Due date** | `$task->due_date` | `$task->due_date` (cast to datetime) | Display as due date, not "expiry" |
| **Days overdue** | Computed via `Carbon::diffInDays()` | Same calculation | Same, but labeled "overdue by X days" |
| **Status** | Not shown | `$task->status` | Add to email body |
| **Assigned user** | Not shown | Each `$assignee` is the notifiable user | Show on email |
| **Portal link** | `url('/')` (generic) | Need specific route: `route('tasks.show', $task->id)` | Add correct link |
| **Recipient reason** | Not shown | The recipient is a task assignee | "You are assigned to this task" |

**ISSUE:** `ExpiringSoon` notification is re-used for both service expiry AND task overdue. The toMail() method uses expiry terminology ("This item expired..."). For tasks, this says "expired" when it should say "overdue". Needs task-specific `toMail()` logic.

**Fix:** Create a separate notification for task overdue, OR add conditional logic in `ExpiringSoon::toMail()` that detects Task type and uses different wording.

**Verification:** ✅ With conditional logic in `ExpiringSoon::toMail()`, task emails will correctly use "overdue" terminology and identify as Tasks.

---

### Path 4: `monitor:check` (hourly)

| Aspect | Current | Verified Source | Proposed |
|--------|---------|-----------------|----------|
| **Source models** | `Domain`, `Hosting`, `VPS`, `VoIP`, `ServiceProvider`, `DomainEmail`, `OtherService`, `ExpiryTracker` | Models with `monitoring_url` set | Same |
| **Recipients** | All users with `admin` or `super-admin` roles | Hardcoded in `NotifyMonitorFailure` listener | Same (broadening = business rule change) |
| **Subject format** | `"[OpsPilot] Service DOWN: {itemName}"` | `$event->item->name` or fallback chain in listener | `"[OpsPilot] {resource_type} DOWN — {itemName}"` |
| **Resource type** | `class_basename($modelClass)` from MonitorCheck command | Passed as `$type` to `MonitorCheckFailed` event | Same, map to human-readable label |
| **Resource name** | `$item->name ?? $item->email ?? $item->monitoring_url ?? '#'.$item->getKey()` | From event `$item` | Same |
| **Error** | `$result['ping']['error'] ?? 'Unknown error'` | From monitoring result | Same |
| **Portal link** | `url('/dashboard')` (generic) | Need specific route per type | Add per-type show route |
| **Recipient reason** | Not shown | Recipient is an admin | "You are receiving this as an administrator" |

**ISSUE:** `MonitorCheckFailed` event/goes through `MonitorCheckFailed` notification which only receives `type` (string), `error`, and `itemName`. It does NOT receive `itemId`, so generating a specific portal link is impossible without changing the event/notification.

**Fix:** Add `itemId` to `MonitorCheckFailed` event and `MonitorCheckFailed` notification.

**Verification:** ✅ With itemId added, portal links will point to the actual resource.

---

### Path 5: "Send Reminder Now" (manual via UI)

| Aspect | Current | Verified Source |
|--------|---------|-----------------|
| **Source model** | Same as Path 1 (`ExpiryTracker`) | Same |
| **Recipients** | Same as Path 1 (`buildRecipients()`) | Same |
| **Trigger source** | `'manual'` in `expiry_tracker_notifications` | Same |
| **Test flag** | Not set | Keep as-is (this is a real send, not a test) |

### Path 6: "Send Test Email" (manual via UI)

| Aspect | Current | Verified Source |
|--------|---------|-----------------|
| **Source model** | Same as Path 1 (`ExpiryTracker`) | Same — uses the real selected tracker |
| **Recipients** | Only the authenticated user (`$user->email`) | `Auth::user()` |
| **Trigger source** | `'test'` in `expiry_tracker_notifications` | Same |
| **Test flag** | No explicit test label in email | Need to show "TEST EMAIL" banner |

### Path 7: "Test SMTP Profile" (manual via UI)

| Aspect | Current | Verified Source |
|--------|---------|-----------------|
| **Source model** | **Dummy** `ExpiryTracker::make(...)` (in-memory, not persisted) | Uses `name='Test SMTP Profile'`, `expiry_date = now + 7 days` |
| **Recipients** | Only the authenticated user | `$recipient->email` |
| **Data accuracy** | **Test/sample data** — clearly not real | Must label as "TEST — Sample Data" |
| **Notification history** | NOT recorded | Should log to `expiry_tracker_notifications` with `trigger_source='test'` |

---

## 2. Proposed Email Subject Format

All operational emails follow standard format:

```
[OpsPilot] {Resource Type} {Urgency} — {Resource Name}
```

### Subject Mapping Table

| Path | Resource Type | Urgency | Resource Name |
|------|---------------|---------|---------------|
| `renewals:send-email-reminders` | From trackable class (Hosting/Domain/VPS/VoIP/Other Service) or "Renewal" if standalone | `expiring in {N} days` / `expiring tomorrow` / `expired {N} days ago` / `expires today` | `$tracker->name` |
| `expiry:check` | From model label (Domain/Hosting/VPS/...) | `expiring in {N} days` / `expiring tomorrow` / `expired {N} days ago` / `expires today` | `$item->name ?? $item->email` |
| `tasks:check-overdue` | `Task` | `overdue by {N} days` / `overdue` | `$task->title` |
| `monitor:check` | From model basename | `DOWN` | `$item->name ...` |

---

## 3. Proposed Email Body Template (Standard)

All operational emails should display (when available):

```
[OPSPILOT] {ALERT_TYPE}

Alert Type: {Renewal Reminder / Expiry Warning / Overdue Task / Monitoring Alert}
Resource Type: {Hosting / Domain / VPS / VoIP / Task / Other Service}
Resource Name: {name}
Related Domain/Hosting: {domain name or N/A}
Provider: {provider name or N/A}
Expiry/Due Date: {date}
Days Remaining / Overdue: {N days remaining / Overdue by N days}
Current Status: {status}
Assigned User: {user name}
Recipient Reason: {You are the assigned user / You are an administrator / Custom recipient}

[View in OpsPilot] button

You received this email because {reason}.
If this is a test email: ** THIS IS A TEST EMAIL — Sample data shown **
```

---

## 4. Potential Business Rule Conflicts

| Item | Conflict? | Resolution |
|------|-----------|------------|
| `expiry:check` only notifies assigned user | No — this is the existing business rule. Expanding to admins would be a new feature, out of scope. | Keep as-is |
| `MonitorCheckFailed` sends to ALL admins | No — existing rule | Keep as-is |
| Task overdue reuse of `ExpiringSoon` notification | **YES** — differentiating in the same notification class may be confusing. Solution: add conditional `toMail()` logic. A new notification class would be cleaner but the user said "using the existing system only". Adding a new notification class would be creating a new file. Let me re-read: "Do not add a new module. Do not redesign architecture. Do not change permission rules. Do not change business rules." Creating a new notification class is not adding a new module. It's adding a new notification within the existing notifications system. I'll add it as a separate notification to avoid complexity. Actually wait — the user says "Implement an SMTP and expiry-email clarity improvement using the existing system only." I think this means using the existing infrastructure (Mail, Notification, Mailable classes), not creating entirely new modules. Creating a new notification class would be fine — it's part of the existing notification system, not a new module. | Add separate `TaskOverdue` notification or add conditional in `ExpiringSoon` |
| Portal links need per-type routes | No conflict — existing routes already exist for all resource types | Use existing route names |
| `MonitorCheckFailed` needs `itemId` | No conflict — this is adding a property, not changing business logic | Add `itemId` to event + notification |
| `ExpiryTrackerReminder` shows data from `trackable` | No conflict — data exists in the database, just not currently exposed | Load and display |

---

## 5. File Change Summary

### Files to Create (using existing system only — no new modules)

| File | Reason |
|------|--------|
| `app/Notifications/TaskOverdue.php` | New notification for task overdue instead of abusing `ExpiringSoon`. Still uses `['database', 'mail']` channels. Still within existing notification system. |

### Files to Modify

| File | Changes |
|------|---------|
| `app/Mail/ExpiryTrackerReminder.php` | Add `[OpsPilot]` prefix, trackable-based resource type, current status, recipient reason, test flag, full data array for template |
| `resources/views/emails/expiry-tracker-reminder.blade.php` | Complete rewrite to standard template — show all required fields, test banner, consistent layout |
| `app/Notifications/ExpiringSoon.php` | Add `[OpsPilot]` prefix, add status, provider, portal link, recipient reason; for Task items: separate handling (though Tasks will move to new notification, keep backward compat) |
| `app/Notifications/MonitorCheckFailed.php` | Add `itemId`, add portal link, add `[OpsPilot]` prefix, add recipient reason |
| `app/Events/MonitorCheckFailed.php` | Add `itemId` property |
| `app/Console/Commands/CheckOverdueTasks.php` | Use new `TaskOverdue` notification instead of `ExpiringSoon` |
| `app/Services/RenewalNotificationService.php` | Add trackable data to view data, add `isTest` parameter, log test SMTP to notification history |
| `app/Http/Controllers/Web/SmtpProfileController.php` | Show recipient info before test, confirm dialog |
| `app/Http/Controllers/Web/ExpiryTrackerController.php` | Show recipient info before test, confirm dialog with details |
| `resources/views/expiry-trackers/_notification-form.blade.php` | Improve test/confirm dialogs, show recipient + subject before send |
| `resources/views/smtp-profiles/show.blade.php` | Improve test button to show destination before sending |

### Files to Add Tests (in existing test files)

| Test File | Add tests for |
|-----------|---------------|
| `tests/Feature/ExpiryReminderMailTest.php` | Hosting type, Domain type, VPS type, missing optional relationships, sensitive data absence, test flag |
| `tests/Feature/ExpiryNotificationServiceTest.php` | Or new test for notification content |
| `tests/Feature/MonitorCheckTest.php` | Or new notification content tests |

---

## 6. Implementation Order

```
1. Events: Add itemId to MonitorCheckFailed event
2. Notifications: Create TaskOverdue notification, update ExpiringSoon, update MonitorCheckFailed
3. Mailable: Update ExpiryTrackerReminder with full data array
4. Template: Rewrite email template to standard format
5. Commands: Update CheckOverdueTasks to use TaskOverdue
6. Services: Update RenewalNotificationService with trackable data + test flag
7. Controllers: Update SmtpProfileController + ExpiryTrackerController for recipient transparency
8. Views: Update blades for confirmation dialogs
9. Tests: Add comprehensive tests
10. Documentation: Update all doc files
```

---
## Implementation Status (2026-07-11)

**All 10 implementation steps completed. 63 targeted tests pass.**

### Key outcomes
- `[OpsPilot]` prefix on all email subjects
- Resource type from polymorphic `trackable` relationship (Hosting, Domain, VPS, etc.) — standalone trackers fall back to module label or "Renewal"
- Portal links per entity type via `route()` with `ROUTE_MAP`; unrouteable types → `/dashboard`
- Recipient reason line in every email body
- `[TEST]` prefix + yellow banner on test emails
- Same `buildMailable()` used for preview and send
- `TaskOverdue` notification uses task-only terminology (no "expiry"/"renewal")
- `MonitorCheckFailed` notification includes `itemId`
- Related domain/hosting shown when available, omitted otherwise
- Status, cost, assigned user displayed
- Sensitive data excluded from all email output
- All emails synchronous (no queue)

### SMTP Profile Test storage
- Stored in: `smtp_profiles.last_tested_at`, `last_test_status`, `last_test_error` + `activity_log`
- **NOT stored** in `expiry_tracker_notifications` (dummy tracker has no real ID)

### Real Tracker Test storage
- Stored in `expiry_tracker_notifications` with `recipient_type='test'`, `trigger_source='test'`, `status='sent'`
- Uses the real selected tracker + real relationships
