# 10. Notifications & Logging

## Notification Architecture

**Package:** Laravel native notifications
**Service:** `RenewalNotificationService`
**Schedule:** Daily via `SendRenewalNotifications` Artisan command

### Notification Channels

1. **Database channel** — stored in `notifications` table (read/unread). Displayed in a notification dropdown in the UI header.
2. **Mail channel** — sent to user's email address. Uses Laravel Mail.

### Notification Types

| Type | Triggered By | Channels |
|---|---|---|
| Renewal Reminder | Daily cron check (expiring trackers) | Database + Mail |
| Password Revealed | System (activity log entry) | No user notification — only activity log |
| User Created | Admin action | Possibly welcome email (NEEDS CONFIRMATION) |

### Notification Class: RenewalNotification

**File:** `app/Notifications/RenewalNotification.php`

- `via($notifiable)`: returns `['mail', 'database']`
- `toMail($notifiable)`: builds mail message with tracker details + link
- `toDatabase($notifiable)`: JSON payload stored in `notifications.data`
- Content includes: tracker name, expire_date, days remaining, cost, URL to tracker

### Mail Templates

- Mail notifications use Laravel's default Markdown mail templates.
- Custom template: `resources/views/vendor/notifications/email.blade.php` (if published).
- Or uses `resources/views/emails/` custom Blade templates (NEEDS CONFIRMATION).

### Database Notifications Table

```
notifications
  id:          UUID
  type:        string (Notification class FQN)
  notifiable_type: string (App\Models\User)
  notifiable_id:  integer
  data:        json
  read_at:     timestamp, nullable
  created_at:  timestamp
  updated_at:  timestamp
```

### Notification Cleanup

No automatic cleanup/pruning of old notifications. They accumulate indefinitely.

## Activity Logging

**Package:** spatie/laravel-activitylog v4
**Model:** `ActivityLog` (from package)

### How It's Used

Observers in `app/Observers/` register activity on model events. Registered in `AppServiceProvider::boot()`:

```php
\App\Models\Domain::observe(\App\Observers\DomainObserver::class);
// ... same for all tracked models
```

### Observer Pattern

Each observer logs:
- **created** — `"{causer} created {model}: {identifier}"`
- **updated** — `"{causer} updated {model}: {identifier}"` with properties showing changed fields
- **deleted** — `"{causer} deleted {model}: {identifier}"`
- **restored** — `"{causer} restored {model}: {identifier}"`

### Activity Log for Password Reveals

Password reveals are logged **outside** the observer — they are logged in the controller/service:

```php
activity()
    ->performedOn($vaultEntry)
    ->causedBy(auth()->user())
    ->withProperties(['ip' => request()->ip()])
    ->event('revealed')
    ->log("Revealed vault password for {$vaultEntry->service_name}");
```

### Activity Log Table

```
activity_log
  id:                  bigint, auto-increment
  log_name:            string, nullable (e.g., 'default')
  description:         text
  subject_type:        string, nullable (MorphMap alias)
  subject_id:          bigint, nullable
  causer_type:         string, nullable (App\Models\User)
  causer_id:           bigint, nullable
  properties:          json, nullable
  batch_uuid:          uuid, nullable
  event:               string, nullable ('created','updated','deleted','restored','revealed','login')
  created_at:          timestamp
  updated_at:          timestamp
```

### Important: Log Name

Currently hardcoded or using default. (NEEDS CONFIRMATION — could be `'default'` or empty.)

### Retention

No pruning. Logs accumulate. The system currently has 50 items per page view, paginated.

## Error Logging

All notification catch blocks use `Log::error()` (added in Sprint A):

```php
try {
    // notification logic
} catch (\Exception $e) {
    Log::error('Renewal notification failed: ' . $e->getMessage());
}
```

Exception handler in `app/Exceptions/Handler.php`:
- Renders 403, 404, 500 error pages.
- Logs all exceptions to `storage/logs/laravel.log`.
- Debug mode (APP_DEBUG=true) shows stack traces in browser.
- In production, returns generic error pages.

## What Are Users NOT Told About

- **No internal notification for failed cron jobs** — renewal notification errors only go to `Log::error()`. No email/Slack alert to admins.
- **No notification for new user registration** — admin is not notified when a new user registers.
- **No notification for permission changes** — user is not notified when their permissions are modified.
- **No notification for password reveals** — the account owner is not notified when their password is revealed by an admin.
- **No notification for deleted data** — user not notified when their records are deleted.
- **No notification for completed renewals** — assignee not notified when tracker is marked completed.

This is intentional — the system was designed as a small team tool where members coordinate in-person/chat rather than via app notifications.
