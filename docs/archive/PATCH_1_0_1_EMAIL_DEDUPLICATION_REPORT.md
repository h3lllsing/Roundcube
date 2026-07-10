# Patch 1.0.1 — Email Recipient Deduplication

**Date:** 2026-06-27  
**Version:** v1.0.1  
**Branch:** release/v1.0  

---

## Problem

When an Expiry Tracker has both `notify_assigned_user` and `notify_admins` enabled, and the assigned user happens to hold a `super-admin` or `admin` role, the same email address appears twice in the recipient list. The same issue can occur if a custom email address duplicates either source.

## Root Cause

`RenewalNotificationService::buildRecipients()` (line 213) appended recipients by source type without tracking whether the email address had already been added. The method produced:

```
[
  ['email' => 'jane@co.com', 'type' => 'assigned_user'],
  ['email' => 'jane@co.com', 'type' => 'admin'],        // ← duplicate
  ['email' => 'bob@co.com',  'type' => 'admin'],
]
```

Both entries would be sent, resulting in two identical emails to the same address.

## Fix

**File:** `app/Services/RenewalNotificationService.php:213-245`

Added a `$seen` lookup array keyed by email address. Each recipient is added only if their email has not already been seen:

```php
$recipients = [];
$seen = [];

// assigned_user — added first, registers email in $seen
if ($tracker->notify_assigned_user && $tracker->user?->email) {
    $email = $tracker->user->email;
    $seen[$email] = true;
    $recipients[] = ['email' => $email, 'type' => 'assigned_user'];
}

// admin — skips if email already in $seen
if ($tracker->notify_admins) {
    foreach ($adminEmails as $email) {
        if (!isset($seen[$email])) {
            $seen[$email] = true;
            $recipients[] = ['email' => $email, 'type' => 'admin'];
        }
    }
}

// custom — skips if email already in $seen
if ($tracker->notify_custom_emails) {
    foreach ($tracker->notify_custom_emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($seen[$email])) {
            $seen[$email] = true;
            $recipients[] = ['email' => $email, 'type' => 'custom'];
        }
    }
}
```

**Order preserved:** assigned_user first, then admins, then custom. The first occurrence's `type` is kept; subsequent duplicates at the same address are dropped.

## Files Modified

| File | Change |
|------|--------|
| `app/Services/RenewalNotificationService.php` | Added email-level dedup in `buildRecipients()` via `$seen` array |
| `tests/Feature/RenewalSchedulerCommandTest.php` | Added 4 new test methods |

## Tests Added

All tests verify dedup behavior end-to-end through `renewals:send-email-reminders` with `Mail::fake()`:

| Test | Scenario | Verifies |
|------|----------|----------|
| `test_dedup_assigned_user_is_also_admin` | Assigned user has `super-admin` role, both toggles enabled | Only 1 email sent to shared address |
| `test_dedup_duplicate_custom_email` | `notify_custom_emails` contains `['dup@e.com', 'dup@e.com', 'other@e.com']` | Only 1 email per unique custom address |
| `test_dedup_mixed_sources_all_same_email` | Assigned user = admin = custom all `admin@e.com`, all 3 toggles enabled | Only 1 email globally |
| `test_dedup_unique_recipients_not_affected` | Assigned (user@e.com) + 2 admins (admin1@e.com, admin2@e.com) + custom (custom@e.com) — all different | **All 4 unique emails still sent**, zero dedup collisions |

## Test Results

| Suite | Tests | Assertions | Status |
|-------|-------|------------|--------|
| `RenewalSchedulerCommandTest` | 13 (+4 new) | 37 (+8 new) | ✅ All passing |
| Unit (full) | 411 | 733 | ✅ All passing |
| Feature sweep (Renewal + ExpiryTracker + Activity + VPS + SMTP) | 204 | 486 | ✅ All passing |

## What Was NOT Changed

- SMTP Profile resolution — untouched
- Sender identity (from/reply-to) — untouched
- Trigger day logic (`getTriggerDays`, `findMatchingDay`) — untouched
- Duplicate prevention (`preventDuplicate`) — unchanged (prevents same-day cron re-sends)
- Database schema — no migration
- Notification history recording — unchanged
- `sendTest()` — unchanged (single recipient, no dedup needed)
- `ExpiryNotificationService` (in-app notification system) — untouched
