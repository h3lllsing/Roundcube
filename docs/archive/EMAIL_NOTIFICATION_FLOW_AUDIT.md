# Email Notification Flow Audit — Renewal Reminders

**Audit Date:** 2026-06-27  
**Version:** v1.0.0  
**Branch:** release/v1.0  

---

## 1. Which Email Address Currently Receives Expiry Notifications?

**There is no single recipient.** Recipients are built dynamically per tracker from three configurable sources (see §5). The actual addresses sent to depend on the per-tracker configuration:

| Source | Example Recipient | Condition |
|--------|------------------|-----------|
| Assigned User | `jane@example.com` | `notify_assigned_user = true` AND tracker has a user with email |
| Admin Users | `admin1@co.com`, `admin2@co.com` | `notify_admins = true` → all users with `super-admin` or `admin` role |
| Custom Emails | `billing@co.com`, `vendor@ext.com` | `notify_custom_emails` array is non-empty |

A single tracker can send to **1 + N admins + M custom addresses** per trigger day.

---

## 2. Is the Recipient Hardcoded, Configurable, or Taken from...?

| Question | Answer |
|----------|--------|
| **Hardcoded?** | No |
| **Configurable?** | **Yes** — three toggle-able recipient types per tracker |
| **Taken from Client?** | No (no Client model exists in the codebase) |
| **Taken from User?** | **Partially** — the "Assigned User" checkbox uses `$tracker->user->email`; "All Administrators" queries the `users` table for admin roles |
| **Taken from SMTP Profile?** | **No** — SMTP Profile provides the **sender** (from address), not the recipient |
| **Taken from Expiry Tracker?** | **Yes** — the tracker stores `notify_assigned_user`, `notify_admins`, `notify_custom_emails` fields which control who receives emails |

---

## 3. Complete Flow Trace

### 3.1 Scheduler

**File:** `routes/console.php:13`

```php
Schedule::command('renewals:send-email-reminders')->dailyAt('02:00');
```

Runs every day at 2:00 AM local time. Calls the command class.

### 3.2 Command

**File:** `app/Console/Commands/SendEmailReminders.php`

```
handle(RenewalNotificationService $service)
```

1. Queries count of eligible trackers: `ExpiryTracker::where('email_notifications_enabled', true)->whereIn('status', ['active', 'pending_renewal'])`
2. Calls `$service->sendReminders($limit)` on `RenewalNotificationService`
3. Logs sent/failed/skipped counts and updates scheduler heartbeat cache

### 3.3 Recipient Resolution

**File:** `app/Services/RenewalNotificationService.php`

```
sendReminders($limit)  →  buildRecipients($tracker)
```

`buildRecipients()` assembles an array of `['email' => string, 'type' => string]`:

| Type | Resolution | File:Line |
|------|-----------|-----------|
| `assigned_user` | `$tracker->user->email` | `RenewalNotificationService.php:217-219` |
| `admin` | `User::whereHas('roles', fn $q => $q->whereIn('slug', ['super-admin', 'admin']))->pluck('email')` | `RenewalNotificationService.php:221-227` |
| `custom` | `$tracker->notify_custom_emails` (validated via `filter_var($email, FILTER_VALIDATE_EMAIL)`) | `RenewalNotificationService.php:229-235` |

### 3.4 SMTP Profile Resolution

**File:** `app/Services/RenewalNotificationService.php`

```
sendEmail($tracker, $matchedDay, $email)  →  resolveMailer($tracker->smtpProfile)
```

Logic (`RenewalNotificationService.php:195-211`):

```
if profile exists AND profile->is_active:
    Dynamically configure a mailer named 'smtp_profile' with:
        transport  = smtp
        host       = $profile->smtp_host
        port       = $profile->smtp_port
        encryption = $profile->smtp_encryption
        username   = $profile->smtp_username
        password   = $profile->smtp_password  (decrypted)
    return Mail::mailer('smtp_profile')
else:
    return Mail::mailer(config('mail.default'))   ← falls back to Laravel's default mail config
```

The SMTP profile is **taken directly from the Expiry Tracker's `smtp_profile_id` foreign key**. If no profile is selected (null), or if the selected profile is inactive, the **system default mailer** is used.

### 3.5 Mail Class

**File:** `app/Mail/ExpiryTrackerReminder.php`

```
buildMailable($tracker, $daysLeft, $recipientEmail) → new ExpiryTrackerReminder(...)
```

| Envelope Property | Value | Source |
|-------------------|-------|--------|
| `from.address` | `$smtpProfile->sender_email` | SMTP Profile (falls back to `config('mail.from.address')`) |
| `from.name` | `$smtpProfile->sender_name` | SMTP Profile (falls back to `config('mail.from.name')`) |
| `replyTo` | `$smtpProfile->reply_to_email` | SMTP Profile (optional) |
| `subject` | Dynamic: "Renewal Reminder: {name} expires in {N} days" | Computed from `daysLeft` |
| `markdown` | `emails.expiry-tracker-reminder` | View template |

**View template:** `resources/views/emails/expiry-tracker-reminder.blade.php` — renders title, expiry date, days left, type, cost, provider, assigned user, and a "View in Portal" button.

### 3.6 Send()

**File:** `app/Services/RenewalNotificationService.php:274-279`

```php
private function sendEmail(ExpiryTracker $tracker, int $matchedDay, string $email): void
{
    $mailer = $this->resolveMailer($tracker->smtpProfile);
    $mailable = $this->buildMailable($tracker, $matchedDay, $email);
    $mailer->to($email)->send($mailable);
}
```

The mailer is resolved from SMTP profile (or default), the mailable is built, and `$mailer->to($email)->send($mailable)` executes the actual SMTP transaction.

### 3.7 Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  routes/console.php                                         │
│  Schedule::command('renewals:send-email-reminders')          │
│  → dailyAt('02:00')                                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  SendEmailReminders::handle()                               │
│  → ExpiryTracker::where('email_notifications_enabled',1)    │
│    → whereIn('status', ['active','pending_renewal'])        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  RenewalNotificationService::sendReminders(limit)            │
│                                                              │
│  foreach tracker:                                            │
│    1. Calculate daysLeft                                     │
│    2. getTriggerDays() — from notify_days_before config      │
│    3. findMatchingDay() — matches daysLeft to trigger days   │
│    4. buildRecipients() — see §5                             │
│                                                              │
│    foreach recipient:                                        │
│      a. preventDuplicate() — checks ExpiryTrackerNotification│
│      b. recordHistory() — creates ExpiryTrackerNotification  │
│      c. sendEmail() → resolveMailer() + buildMailable()      │
│         → ExpiryTrackerReminder                              │
│         → Mail::mailer('smtp_profile')->to(email)->send()    │
│      d. On success: update notification to 'sent'            │
│      e. On failure: update notification to 'failed'          │
│                                                              │
│    If sent > 0: update last_notification_sent_at             │
│                 update next_notification_due_at              │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Every File Involved

### Scheduler Layer
| # | File | Role |
|---|------|------|
| 1 | `routes/console.php` | Defines the cron schedule (`renewals:send-email-reminders` at 02:00) |

### Command Layer
| # | File | Role |
|---|------|------|
| 2 | `app/Console/Commands/SendEmailReminders.php` | CLI command — queries eligible trackers, calls service, logs results |

### Service Layer
| # | File | Role |
|---|------|------|
| 3 | `app/Services/RenewalNotificationService.php` | Core orchestration — builds recipients, resolves mailer, sends emails, records history |

### Mail Layer
| # | File | Role |
|---|------|------|
| 4 | `app/Mail/ExpiryTrackerReminder.php` | Mailable class — envelope (from, reply-to, subject), markdown view data |
| 5 | `resources/views/emails/expiry-tracker-reminder.blade.php` | Email HTML template (Markdown mail layout) |

### Model Layer
| # | File | Role |
|---|------|------|
| 6 | `app\Models\ExpiryTracker.php` | Eloquent model — `smtpProfile()`, `user()`, notification config fields (`notify_days_before`, `notify_assigned_user`, `notify_admins`, `notify_custom_emails`, etc.) |
| 7 | `app\Models\SmtpProfile.php` | Eloquent model — SMTP connection settings, `is_active`, `sender_email`, `sender_name` |
| 8 | `app\Models\ExpiryTrackerNotification.php` | Eloquent model — history record for each sent email |

### Controller Layer (Manual Triggers)
| # | File | Role |
|---|------|------|
| 9 | `app/Http/Controllers/Web/ExpiryTrackerController.php` | `testEmail()`, `sendReminderNow()`, `previewEmail()`, `notificationHistory()` actions |

### Request Layer (Validation)
| # | File | Role |
|---|------|------|
| 10 | `app/Http/Requests/StoreExpiryTrackerRequest.php` | Validates notification fields on creation (requires at least one recipient type) |
| 11 | `app/Http/Requests/UpdateExpiryTrackerRequest.php` | Validates notification fields on update |

### View Layer (UI Configuration)
| # | File | Role |
|---|------|------|
| 12 | `resources/views/expiry-trackers/_notification-form.blade.php` | Partial — notification config form (SMTP profile selector, trigger days, recipient toggles, custom email list, preview/test/send-now buttons) |
| 13 | `resources/views/expiry-trackers/show.blade.php` | Show page — displays notification status, SMTP profile, last/next notification dates |
| 14 | `resources/views/expiry-trackers/notifications.blade.php` | Notification history table — shows every sent/failed email with recipient, sender, SMTP profile, error message |

### Other Related Files (In-App Notifications — separate flow)
| # | File | Role |
|---|------|------|
| 15 | `app/Console/Commands/CheckExpiries.php` | Separate command (`expiry:check`) for **in-app** (database) notifications — does NOT send email |
| 16 | `app/Services/ExpiryNotificationService.php` | Sends **in-app** notifications via `User::notify(new ExpiringSoon(...))` — not email |
| 17 | `app/Notifications/ExpiringSoon.php` | Notification class for in-app + mail notification (uses Laravel's default mail, NOT the SMTP profile system) |
| 18 | `app/Events/ExpiryWarningTriggered.php` | Event dispatched by `ExpiryNotificationService` (in-app flow) |
| 19 | `app/Listeners/LogExpiryWarning.php` | Listener — logs activity when expiry warning is sent (in-app flow) |

---

## 5. Multi-Recipient Support — Where Configured

**Yes, multiple recipients ARE supported.** Configuration happens in two places:

### 5.1 Database (per-tracker fields on `expiry_trackers` table)

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `notify_assigned_user` | `boolean` | `true` | Send to `$tracker->user->email` |
| `notify_admins` | `boolean` | `false` | Send to all users with `super-admin` or `admin` role |
| `notify_custom_emails` | `json array` | `[]` | Arbitrary list of email addresses |
| `notify_days_before` | `json array` | `[30, 15, 7, 1]` | Which day-intervals trigger a notification |
| `notify_on_expiry_day` | `boolean` | `false` | Also trigger on expiry day (day 0) |

**Validation rule** (both Store and Update requests): If `email_notifications_enabled` is true, **at least one recipient type must be selected** — enforced in `StoreExpiryTrackerRequest.php:22-28` and `UpdateExpiryTrackerRequest.php:22-28`.

### 5.2 UI

**File:** `resources/views/expiry-trackers/_notification-form.blade.php`

Three recipient controls:

1. **Checkbox: "Assigned User"** (`notify_assigned_user`) — line 59
2. **Checkbox: "All Administrators"** (`notify_admins`) — line 60
3. **Dynamic email list** (`notify_custom_emails`) — lines 64-76, Alpine.js-powered "Add Email" button that appends `<input type="email">` fields

### 5.3 Recipient Deduplication

`buildRecipients()` returns a flat array (no built-in deduplication — if the assigned user is also an admin, they receive the email twice if both `notify_assigned_user` and `notify_admins` are enabled). `preventDuplicate()` in `sendReminders()` prevents sending the same `(tracker_id, reminder_day, recipient_email, trigger_source)` combination more than once, but only **within the same cron run** per the `ExpiryTrackerNotification` history table.

### 5.4 Trigger Day Resolution

`getTriggerDays($tracker)` reads `$tracker->notify_days_before` (default `[30, 15, 7, 1]`) and appends `0` if `notify_on_expiry_day` is enabled.

`findMatchingDay($daysLeft, $triggerDays)` compares the actual days left against the configured trigger days. When `$daysLeft < 0` (overdue), it sends on the **minimum** trigger day.

---

## 6. Notable Observations

### 6.1 Two Separate Notification Systems Coexist

| System | Trigger | Recipient | Medium | SMTP Profile Aware? |
|--------|---------|-----------|--------|---------------------|
| `RenewalNotificationService` | `renewals:send-email-reminders` (02:00 daily) | Configurable per tracker (user, admins, custom) | **Email** via SMTP Profile | **Yes** — uses per-tracker SMTP profile |
| `ExpiryNotificationService` | `expiry:check` (08:00 daily) | `$item->user` only | **In-app** database notification (+ Laravel default mail) | **No** — uses system default mail config |

The `ExpiringSoon` notification class also sends an email via `toMail()`, but that email uses **Laravel's default mailer**, NOT the configured SMTP profile. This means items expiring from the in-app system (Domains, Hostings, VPS, VoIP, etc.) send emails via the system default, while Expiry Tracker reminders go through the configured SMTP profile.

### 6.2 Default Recipient Configuration

When a new Expiry Tracker is created, the default recipient configuration is:
- `notify_assigned_user`: **true** (sends to the user who created the tracker — `StoreExpiryTrackerController` sets `$validated['user_id'] = Auth::id()`)
- `notify_admins`: **false**
- `notify_custom_emails`: **empty**
- `notify_days_before`: **[30, 15, 7, 1]**
- `notify_on_expiry_day`: **false**

### 6.3 Sender Identity

The sender (from address) is NOT the system default — it's determined by the SMTP Profile selected on the tracker:
- `$tracker->smtpProfile->sender_email` / `sender_name` for the envelope `from`
- If no SMTP profile is selected OR the selected profile is inactive → falls back to `config('mail.from.address')` / `config('mail.from.name')`
