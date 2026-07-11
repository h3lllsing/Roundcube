# SMTP & Notification System Audit

> **Date:** 2026-07-11
> **Scope:** Codebase audit only — no code changed.
> **Audit method:** grep, glob, file reads across app/, config/, resources/views/, routes/, tests/, database/migrations/.

---
## Current Implemented State (2026-07-11)

All improvements from this audit have been implemented. Key changes:

| Original Gap | Implemented Resolution | Remaining Limitation |
|---|---|---|
| `tasks:check-overdue` reused `ExpiringSoon` notification with expiry terminology | Separate `TaskOverdue` notification uses task-only wording (Due Date, Days Overdue, Task Status) | None |
| `MonitorCheckFailed` event lacked `itemId` — portal link was always `/dashboard` | Added `?int $itemId` to event + notification; per-type portal links via `ROUTE_MAP` | Unrouteable types still fall back to `/dashboard` (graceful) |
| `ExpiryTrackerReminder` subject was `"Renewal Reminder: {name}"` — no OpsPilot brand, no resource type, no urgency | Subject now `[OpsPilot]{[TEST]} {resourceType} {urgency} — {name}` | None |
| Email template lacked status, cost, recipient reason, test banner, related domain/hosting | Template rewritten with standard format showing all fields; TEST banner when `$isTest` | None |
| No preview/send parity — preview used separate `render()` without trackable data | Both use same `buildMailable()` → `buildViewData()` | Notifications (`ExpiringSoon`, `MonitorCheckFailed`) still lack preview |
| SMTP test result said "sent successfully" — implied delivery confirmation | Now says "Test accepted by SMTP server" — honest about SMTP acceptance vs delivery | None |
| Test email recipient not visible before send | Confirmation dialogs show exact recipient email + subject before sending | None |
| SMTP profile test stored nothing (dummy tracker, no real ID) | Results stored in `smtp_profiles.last_tested_at/status/error` + `activity_log` **only** — **never** in `expiry_tracker_notifications` | None (by design) |
| Real tracker test stored in `expiry_tracker_notifications` without test flag | Stored with `recipient_type='test'`, `trigger_source='test'`, `status='sent'` | None |

### Live Email Verification
**PENDING** — production-only verification required because the SMTP server (`mail.alphaspacepro.online:465`) is unreachable from the local development environment. Verification steps documented in close-out checklist but not executed.

### Remaining gaps (unchanged from audit, not in scope)
- No unified email log (only renewal reminders logged)
- No retry mechanism (all sends synchronous, single-attempt)
- No bounce/feedback handling (no SES/Mailgun/Postmark webhooks)
- No system-wide default recipient configuration
- No email preview for notification-based emails (`ExpiringSoon`, `MonitorCheckFailed`, `VerifyEmail`)
- `MAIL_VERIFY_PEER` env vars undocumented

---

## 1. Which events currently send emails?

There are **5 events** mapped in `app/Providers/EventServiceProvider.php`. Of these, **2 trigger email sending**:

| Event | Listener | Sends Email? | Mechanism | File |
|-------|----------|--------------|-----------|------|
| `MonitorCheckFailed` | `NotifyMonitorFailure` | **YES** | Notifies all admin/super-admin users via `MonitorCheckFailed` notification (`['database', 'mail']`) | `app/Listeners/NotifyMonitorFailure.php:16` |
| `ExpiryWarningTriggered` | `LogExpiryWarning` | **NO** | Only records activity log — no email | `app/Listeners/LogExpiryWarning.php` |
| `VaultPasswordRevealed` | `AlertVaultOwner` | **NO** | DB-only notification (`['database']`) | `app/Listeners/AlertVaultOwner.php:23` |
| `TaskCreated` | `SendTaskAssignedNotification` | **NO** | DB-only notification (`['database']`) | `app/Listeners/SendTaskAssignedNotification.php:17` |
| `TaskUpdated` | `SendTaskAssignedNotification` | **NO** | DB-only notification (`['database']`) | `app/Listeners/SendTaskAssignedNotification.php:17` |

Additionally, **emails are sent directly (not via events)** from:

| Trigger | Mechanism | File |
|---------|-----------|------|
| `expiry:check` scheduled command (daily 08:00) | `ExpiryNotificationService::check()` → `$user->notify(new ExpiringSoon)` | `app/Services/ExpiryNotificationService.php:72` |
| `tasks:check-overdue` scheduled command (daily 09:00) | `$assignee->notify(new TaskOverdue)` | `app/Console/Commands/CheckOverdueTasks.php:30` |
| `renewals:send-email-reminders` scheduled command (daily 02:00) | `RenewalNotificationService::sendReminders()` → `$mailer->to($email)->send($mailable)` | `app/Services/RenewalNotificationService.php:334` |
| `monitor:check` scheduled command (hourly) | Dispatches `MonitorCheckFailed` event → listener sends notification | `routes/console.php:7` |
| Web: User registration (web + API) | `sendEmailVerificationNotification()` → `VerifyEmail` notification | `app/Http/Controllers/Web/AuthController.php:80`, `app/Http/Controllers/Api/AuthController.php:130` |
| Web: Resend verification (web + API) | `sendEmailVerificationNotification()` → `VerifyEmail` notification | `app/Http/Controllers/Web/AuthController.php:176`, `app/Http/Controllers/Api/AuthController.php:207` |
| Web: Forgot password (web + API) | `Password::sendResetLink()` → Laravel built-in mail | `app/Http/Controllers/Web/AuthController.php:97`, `app/Http/Controllers/Api/PasswordResetController.php:32` |
| Web: "Send Test Email" on tracker | `RenewalNotificationService::sendTest()` | `app/Http/Controllers/Web/ExpiryTrackerController.php:229` |
| Web: "Send Reminder Now" on tracker | `RenewalNotificationService::sendNow()` | `app/Http/Controllers/Web/ExpiryTrackerController.php:242` |
| Web: "Test SMTP" on profile | `RenewalNotificationService::testSmtpProfile()` | `app/Http/Controllers/Web/SmtpProfileController.php:160` |

**Scheduled email tasks** (`routes/console.php`):

| Schedule | Command | What it sends |
|----------|---------|--------------|
| `dailyAt('02:00')` | `renewals:send-email-reminders` | Renewal reminder emails via `ExpiryTrackerReminder` Mailable |
| `dailyAt('08:00')` | `expiry:check` | Expiry warnings via `ExpiringSoon` notification (mail channel) |
| `dailyAt('09:00')` | `tasks:check-overdue` | Overdue task alerts via `TaskOverdue` notification (mail channel) |
| `hourly` | `monitor:check` | Service failure alerts via `MonitorCheckFailed` notification (mail channel) |

---

## 2. Which Mailable classes exist?

**1 Mailable class** in the entire codebase:

| Class | Namespace | File | View Template |
|-------|-----------|------|---------------|
| `ExpiryTrackerReminder` | `App\Mail` | `app/Mail/ExpiryTrackerReminder.php` | `resources/views/emails/expiry-tracker-reminder.blade.php` |

Key details:
- Uses modern Laravel API (`envelope()` + `content()` methods, not legacy `build()`)
- Renders via Markdown mail template (`x-mail::message`, `x-mail::button`)
- Constructor: `ExpiryTracker $tracker, int $daysLeft, string $recipientEmail, ?SmtpProfile $smtpProfile`
- Uses `Queueable` trait but does **NOT** implement `ShouldQueue` — sends synchronously
- `renderPreview()` method exists — used by the Preview Email feature
- Does NOT call `->mailer()` or `->to()` internally — both are handled by `RenewalNotificationService`

---

## 3. Which Notification classes exist?

**6 Notification classes** total:

### Send email (via `mail` channel):

| Class | Channels | File | Sends To |
|-------|----------|------|----------|
| `ExpiringSoon` | `['database', 'mail']` | `app/Notifications/ExpiringSoon.php` | Tracker assigned user or task assignee |
| `MonitorCheckFailed` | `['database', 'mail']` | `app/Notifications/MonitorCheckFailed.php` | All admin/super-admin users |
| `VerifyEmail` (extends `Illuminate\Auth\Notifications\VerifyEmail`) | `mail` (inherited) | `app/Notifications/VerifyEmail.php` | Newly registered user |

### Database-only (no email):

| Class | Channels | File | Purpose |
|-------|----------|------|---------|
| `TaskAssigned` | `['database']` | `app/Notifications/TaskAssigned.php` | In-app task assignment alert |
| `NoteAdded` | `['database']` | `app/Notifications/NoteAdded.php` | In-app note addition alert |
| `VaultPasswordRevealed` | `['database']` | `app/Notifications/VaultPasswordRevealed.php` | In-app vault password access alert |

**Important:** All notifications use `Queueable` trait but **none implement `ShouldQueue`**. All are sent synchronously during the request lifecycle.

---

## 4. What email templates currently exist?

**1 transactional email template** (actively sent):

| Template | File | Used By |
|----------|------|---------|
| `emails.expiry-tracker-reminder` | `resources/views/emails/expiry-tracker-reminder.blade.php` | `ExpiryTrackerReminder` Mailable |

Template renders: Renewal reminder details (title, expiry date, days left, type, cost, provider, assigned user) with a "View in Portal" CTA button. Uses Laravel's built-in `x-mail::message` and `x-mail::button` components.

**No custom vendor mail templates** published:
- `resources/views/vendor/mail/` — does not exist
- `resources/views/vendor/notifications/` — does not exist
- `resources/views/layouts/email.blade.php` — does not exist

The `ExpiringSoon` and `MonitorCheckFailed` notifications use Laravel's default `MailMessage` rendering (simple lines + action button), not custom Blade views.

**12 mail-related CRUD admin views** also exist but these are **web pages** (not email templates):
- `resources/views/domain-emails/{index,show,create,edit}.blade.php` (4 files)
- `resources/views/g-mails/{index,show,create,edit}.blade.php` (4 files)
- `resources/views/smtp-profiles/{index,show,create,edit}.blade.php` (4 files)

---

## 5. Does "Test Connection" actually send an email or only verify SMTP authentication?

**It actually sends a real email.** It does NOT just verify SMTP authentication.

The `testSmtpProfile()` method in `RenewalNotificationService` (`app/Services/RenewalNotificationService.php:390-407`):

1. Resolves a dynamic mailer configured with the profile's SMTP credentials via `resolveMailer()` (which sets host, port, encryption, username, password into `config("mail.mailers.smtp_profile_{$id}")`)
2. Creates a **dummy** `ExpiryTracker` model (not persisted, in-memory only) with `name='Test SMTP Profile'` and `expiry_date = now + 7 days`
3. Builds a full `ExpiryTrackerReminder` Mailable with the dummy tracker
4. Calls `$mailer->to($recipient->email)->send($mailable)` — sends the complete HTML email to the currently logged-in user's email address

The controller (`app/Http/Controllers/Web/SmtpProfileController.php:154-196`):
- On success: updates `last_tested_at`, sets `last_test_status = 'success'`, logs activity event
- On failure: sets `last_test_status = 'failed'`, stores error message in `last_test_error`, logs activity event
- Returns redirect with success/error flash message

**There is no pure SMTP auth-only validation.** Every "test" sends a full email. If SMTP auth fails, the email send throws an exception which is caught and displayed as a test failure.

---

## 6. Is there already a "Send Test Email" feature?

**Yes, two test email features exist:**

### a) Send Test Email on Expiry Tracker
- **Route:** `POST /admin/expiry-trackers/{expiry_tracker}/test-email`
- **Controller:** `ExpiryTrackerController::testEmail()` at `app/Http/Controllers/Web/ExpiryTrackerController.php:229`
- **Service method:** `RenewalNotificationService::sendTest()` at `app/Services/RenewalNotificationService.php:169-201`
- **UI:** Located in `resources/views/expiry-trackers/_notification-form.blade.php` — "Send Test Email" button
- **Behavior:** Sends a real `ExpiryTrackerReminder` Mailable to the currently logged-in user using the tracker's configured SMTP profile. Records history in `expiry_tracker_notifications` with `recipient_type='test'`, `trigger_source='test'`.

### b) Test SMTP Profile
- **Route:** `POST /admin/smtp-profiles/{smtp_profile}/test`
- **Controller:** `SmtpProfileController::test()` at `app/Http/Controllers/Web/SmtpProfileController.php:154`
- **Service method:** `RenewalNotificationService::testSmtpProfile()` at `app/Services/RenewalNotificationService.php:390-407`
- **UI:** "Test SMTP" button on the SMTP profile show page (`resources/views/smtp-profiles/show.blade.php`)
- **Behavior:** Sends a dummy `ExpiryTrackerReminder` Mailable to the currently logged-in user using the profile's SMTP settings. Updates `last_tested_at`, `last_test_status`, `last_test_error` on the profile.

---

## 7. Is there an email log?

**Yes, partially.** There is a notification history table for renewal reminders, but NO general email log for all outgoing emails.

### `expiry_tracker_notifications` table (renewal-specific log)
- **Migration:** `database/migrations/2026_06_25_100002_create_expiry_tracker_notifications_table.php`
- **Model:** `App\Models\ExpiryTrackerNotification` at `app/Models/ExpiryTrackerNotification.php`

**Schema:**

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint (auto) | Primary key |
| `expiry_tracker_id` | foreignId → `expiry_trackers` | Cascade delete |
| `smtp_profile_id` | unsignedBigInteger (nullable) | Indexed |
| `sender_email` | string | |
| `reminder_day` | integer | Days before expiry |
| `recipient_email` | string | |
| `recipient_type` | string(50) | `assigned_user`, `admin`, `custom`, `test` |
| `trigger_source` | string(20) | `cron`, `manual`, `test` |
| `status` | string(20) | `queued`, `sent`, `failed` |
| `sent_at` | timestamp (nullable) | |
| `error_message` | text (nullable) | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | timestamp | Soft deletes |

**This log covers only emails sent by `RenewalNotificationService`** (i.e., renewal reminders). It does **NOT** capture:
- Emails sent by `ExpiringSoon` notification (expiry warnings, overdue tasks)
- Emails sent by `MonitorCheckFailed` notification (service down alerts)
- Emails sent by `VerifyEmail` notification (verification emails)
- Emails sent by `Password::sendResetLink()` (password reset emails)

### `activity_log` table (Spatie Activitylog)
Both `SmtpProfile` and `ExpiryTracker` models use `LogsActivity` trait. CRUD operations and test events are logged here, but this is an audit trail, not an email delivery log.

---

## 8. Can the user preview outgoing emails?

**Yes, the user can preview renewal reminder emails before sending.**

### Preview Feature
- **Route:** `GET /admin/expiry-trackers/{expiry_tracker}/preview-email`
- **Controller:** `ExpiryTrackerController::previewEmail()` at `app/Http/Controllers/Web/ExpiryTrackerController.php:208-218`
- **Service method:** `RenewalNotificationService::previewEmail()` at `app/Services/RenewalNotificationService.php:203-221`
- **Mailable method:** `ExpiryTrackerReminder::renderPreview()` at `app/Mail/ExpiryTrackerReminder.php:62-65` — calls `$this->render()` internally

**Process:**
1. AJAX call from the notification form modal (`resources/views/expiry-trackers/_notification-form.blade.php`)
2. Returns JSON with: `subject`, `html` (full rendered HTML), `profileName`, `senderEmail`, `senderName`
3. Displayed in an iframe modal in the browser

**Test coverage:** `tests/Feature/ExpiryReminderMailTest.php:107-114` asserts the preview returns valid HTML containing expected content.

**Limitation:** Preview is only available for `ExpiryTrackerReminder` Mailable (renewal reminders). There is no preview for:
- `ExpiringSoon` notification emails
- `MonitorCheckFailed` notification emails
- `VerifyEmail` notification emails
- Password reset emails

---

## 9. Can the user configure default notification recipients?

**Yes, per-tracker granularity, but no global default recipients setting.**

### Per-Tracker Configuration (ExpiryTracker model)
Fields on `expiry_trackers` table (set via `resources/views/expiry-trackers/_notification-form.blade.php`):

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `email_notifications_enabled` | boolean | ? | Master toggle for email notifications |
| `notify_assigned_user` | boolean | `true` | Notify the user assigned to this tracker |
| `notify_admins` | boolean | `false` | Notify all super-admin and admin users |
| `notify_custom_emails` | array (JSON) | `[]` | Additional custom email addresses |
| `notify_days_before` | array (JSON) | `[30, 15, 7, 1]` | Days before expiry to trigger |
| `notify_on_expiry_day` | boolean | `false` | Also notify on the expiry day itself |
| `smtp_profile_id` | FK nullable | `null` | Custom SMTP profile (null = use system default) |

### Recipient Resolution Order (`buildRecipients()` at `RenewalNotificationService.php:261-293`)
1. If `notify_assigned_user` is true and user has email → add assigned user
2. If `notify_admins` is true → add all users with roles `super-admin` or `admin`
3. If `notify_custom_emails` is set → add each valid email (deduplicated)

### Global Configuration
- `config/renewals.php`: Default `notify_days_before = [30, 15, 7, 1]` (applied when tracker has no custom value)
- `config/mail.from.address`: Global fallback "from" address
- **No database settings table** — there is no Setting/AppSetting model
- **No admin panel UI** for configuring system-wide default notification recipients

### Monitor Failure Notifications
- Hardcoded to notify all users with `super-admin` or `admin` roles (`app/Listeners/NotifyMonitorFailure.php:14`)
- **No per-resource or per-user configuration** — all admins receive all failure notifications

---

## 10. Are failed emails stored anywhere?

**Yes, in two places, but coverage is incomplete.**

### a) `expiry_tracker_notifications` table (structured log)
Coverage: Only emails sent by `RenewalNotificationService` (renewal reminders).

When an email send fails (`sendEmail()` at `RenewalNotificationService.php:329-335`), the exception is caught and the notification record is updated:
```php
$notification->update([
    'status' => 'failed',
    'error_message' => $this->sanitizeErrorMessage($e),
]);
```

The `sanitizeErrorMessage()` method (`RenewalNotificationService.php:337-342`) redacts password/username fields from the error message and truncates to 1000 characters.

**NOT covered:** Failed `ExpiringSoon`, `MonitorCheckFailed`, `VerifyEmail`, or password reset emails.

### b) Laravel Log (monolog)
Errors are also logged:
```php
Log::error('Renewal notification failed', [
    'tracker_id' => $tracker->id,
    'recipient' => $recipient['email'],
    'error' => $e->getMessage(),
]);
```

### c) SmtpProfile `last_test_error` field
When "Test SMTP" fails, the error message is stored in `smtp_profiles.last_test_error` (text, nullable) and `last_test_status` is set to `'failed'`.

---

## 11. Which queue is used for emails?

**No queue is used for emails. All emails are sent synchronously.**

### Evidence:
- **Queue connection:** `QUEUE_CONNECTION=database` (`.env` → `config/queue.php` defaults to `database`)
- **`ExpiryTrackerReminder`** Mailable uses `Queueable` trait but does **NOT** implement `ShouldQueue` — `->send()` is synchronous
- **All Notification classes** (`ExpiringSoon`, `MonitorCheckFailed`, `VerifyEmail`, `TaskAssigned`, `NoteAdded`, `VaultPasswordRevealed`) use `Queueable` trait but **none implement `ShouldQueue`**
- **`RenewalNotificationService::sendEmail()`** calls `->send()` (not `->queue()`)
- **All listeners** execute synchronously in the same process
- **All scheduled commands** (`expiry:check`, `monitor:check`, `tasks:check-overdue`, `renewals:send-email-reminders`) run synchronously as CLI processes

### Impact
- Long SMTP timeouts (default 30s in `resolveMailer()`) block the queue worker or HTTP response
- No retry mechanism for failed email sends — they fail immediately and are logged
- `composer.json` dev script runs `queue:listen --tries=1 --timeout=0` (single attempt, no retries) — but this only processes `SendWebhookJob`, not emails

### Only queued job:
- `SendWebhookJob` at `app/Jobs/SendWebhookJob.php` — **implements `ShouldQueue`**, handles HTTP webhook delivery, not email

---

## 12. Is Laravel `failed_jobs` table used?

**Yes, the migration and table exist, but only `SendWebhookJob` uses it.**

### Table
- Created by migration `database/migrations/0001_01_01_000002_create_jobs_table.php` (lines 37-45)
- Schema: `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`
- Failed job driver: `database-uuids` (from `config/queue.php:124`)
- No explicit pruning/retention configured

### Current usage
- Only `SendWebhookJob` (`app/Jobs/SendWebhookJob.php`) implements `ShouldQueue` and has a `failed()` method
- Failed webhook jobs are stored in `failed_jobs` table with full exception trace
- `SendWebhookJob::failed()` logs a warning but does NOT re-dispatch
- Viewable via `php artisan queue:failed`

### Email failures are NOT stored in `failed_jobs`
- Email sending failures are stored in `expiry_tracker_notifications.status = 'failed'` (structured, but renewal-only)
- General email failures (`ExpiringSoon`, `MonitorCheckFailed`, etc.) are only captured in Laravel log files

---

## Summary of Gaps Found

| Gap | Severity | Details |
|-----|----------|---------|
| No unified email log | High | Only renewal reminders logged. Expiry warnings, monitor failures, verification, password reset emails have no delivery tracking. |
| No retry mechanism | High | All email sends are synchronous, single-attempt. No queue, no retry, no backoff. |
| No bounce/feedback handling | High | No SES/Mailgun/Postmark webhook endpoints. No bounce, complaint, or open tracking. No handling of invalid addresses. |
| Notification recipients not centrally configurable | Medium | Per-tracker config exists but no system-wide default recipient list. Monitor failure recipients are hardcoded to all admins. |
| No general "Send Test Email" (system-wide) | Medium | Test email only exists per-tracker and per-SMTP-profile. No way to test the default mailer independently. |
| No email preview for notifications | Low | Preview exists only for `ExpiryTrackerReminder` Mailable. No preview for `ExpiringSoon`, `MonitorCheckFailed`, or `VerifyEmail` notification emails. |
| `MAIL_VERIFY_PEER` env vars undocumented | Low | SSL peer verification controlled by env vars (`MAIL_VERIFY_PEER`, `MAIL_VERIFY_PEER_NAME`) not documented in `.env.example`. Default `true` may cause issues with self-signed certs. |

---

## System Architecture Diagram (simplified)

```
┌─────────────────────────────────────────────────────────────────┐
│                        Email Sources                             │
├──────────────────┬──────────────────┬────────────────────────────┤
│  Event Listeners │  Scheduled Tasks  │  User-Initiated (Web)     │
│                  │                  │                            │
│ MonitorCheckFail │ expiry:check     │ Registration verification  │
│  (via event)     │ tasks:check-over │ Resend verification        │
│                  │ monitor:check    │ Forgot password            │
│                  │ renewals:send    │ Send Test Email (tracker)  │
│                  │                  │ Send Reminder Now (manual) │
│                  │                  │ Test SMTP Profile          │
└────────┬─────────┴────────┬─────────┴──────────────┬─────────────┘
         │                  │                        │
         ▼                  ▼                        ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Delivery Mechanisms                          │
├──────────────────────┬──────────────────────────────────────────┤
│  Notification::mail  │  Mail::mailer()->to()->send(Mailable)     │
│  (ExpiringSoon,      │  (RenewalNotificationService)             │
│   MonitorCheckFailed,│   → ExpiryTrackerReminder Mailable        │
│   VerifyEmail)       │   → Synchronous, no queue                │
│  → Synchronous       │   → Custom SMTP profiles supported       │
│  → Uses default      │   → Logged to expiry_tracker_notifications│
│    mailer only       │                                          │
└──────────────────────┴──────────────────────────────────────────┘
         │                        │
         ▼                        ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Mailers                                      │
├────────────────────────────┬─────────────────────────────────────┤
│  Default System Mailer     │  Dynamic Per-Profile Mailers        │
│  (config/mail.php)         │  (smtp_profile_{id})                │
│  .env: MAIL_HOST, etc.     │  Created at runtime by              │
│  Current: mail.alphaspace  │  RenewalNotificationService::       │
│   pro.online:465 (SSL)     │   resolveMailer()                   │
│  From: noreply@...         │  Uses profile's host/port/creds     │
└────────────────────────────┴─────────────────────────────────────┘
         │                        │
         ▼                        ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Storage / Logging                                │
├──────────────────────┬──────────────────────────────────────────┤
│  renewal-specific:   │  General:                                │
│  expiry_tracker_     │  - Laravel log (monolog)                 │
│   notifications      │  - activity_log (Spatie, CRUD only)      │
│   (status, error)    │  - NOT logged for non-renewal emails     │
│                      │                                          │
│  SMTP profile test:  │  Failed jobs:                            │
│  smtp_profiles       │  - failed_jobs table exists              │
│   .last_test_status  │  - Only SendWebhookJob uses it           │
│   .last_test_error   │  - Emails never enter the queue          │
└──────────────────────┴──────────────────────────────────────────┘
```

---
## Implementation Status (2026-07-11)

**All planned changes implemented and verified.**

| # | Change | Files | Status |
|---|--------|-------|--------|
| 1 | `itemId` added to `MonitorCheckFailed` event | `app/Events/MonitorCheckFailed.php` | ✅ Done |
| 2 | `itemId` passed from monitor check command | `app/Console/Commands/MonitorCheck.php` | ✅ Done |
| 3 | `MonitorCheckFailed` notification updated with ROUTE_MAP, [OpsPilot] prefix, itemId | `app/Notifications/MonitorCheckFailed.php` | ✅ Done |
| 4 | `NotifyMonitorFailure` listener passes itemId | `app/Listeners/NotifyMonitorFailure.php` | ✅ Done |
| 5 | `ExpiringSoon` notification updated with [OpsPilot], ROUTE_MAP, status, recipient reason | `app/Notifications/ExpiringSoon.php` | ✅ Done |
| 6 | `TaskOverdue` notification created (task terminology only) | `app/Notifications/TaskOverdue.php` | ✅ Done |
| 7 | `CheckOverdueTasks` uses `TaskOverdue` instead of `ExpiringSoon` | `app/Console/Commands/CheckOverdueTasks.php` | ✅ Done |
| 8 | `ExpiryTrackerReminder` Mailable updated with trackable data, recipientType, isTest | `app/Mail/ExpiryTrackerReminder.php` | ✅ Done |
| 9 | Email template rewritten with standard format + test banner | `resources/views/emails/expiry-tracker-reminder.blade.php` | ✅ Done |
| 10 | `RenewalNotificationService` updated (buildMailable, send, preview, test all use same path) | `app/Services/RenewalNotificationService.php` | ✅ Done |
| 11 | `SmtpProfileController` test messages improved | `app/Http/Controllers/Web/SmtpProfileController.php` | ✅ Done |
| 12 | `ExpiryTrackerController` preview includes testRecipient | `app/Http/Controllers/Web/ExpiryTrackerController.php` | ✅ Done |
| 13 | Views updated with confirmation dialogs | `_notification-form.blade.php`, `smtp-profiles/show.blade.php` | ✅ Done |
| 14 | Tests for all email paths | `ExpiryReminderMailTest.php`, `RenewalNotificationServiceTest.php`, `NotifyMonitorFailureTest.php`, `TaskOverdueNotificationTest.php` | ✅ 63/63 passing |
| 15 | Doc updates | `SMTP_NOTIFICATION_AUDIT.md`, `SMTP_EMAIL_DATA_MAPPING_PLAN.md` | ✅ Done |
