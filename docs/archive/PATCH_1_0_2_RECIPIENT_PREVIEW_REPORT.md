# Patch 1.0.2 — Expiry Notification Recipient Preview

**Date:** 2026-06-27  
**Version:** v1.0.2  
**Branch:** release/v1.0  

---

## Problem

Expiry Tracker create/edit/show pages lacked a clear, consolidated view of who will receive email notifications. Users had to toggle checkboxes and visit the notification history to verify recipients.

## Changes

### 1. New Partial: `_recipient-preview.blade.php`

**File:** `resources/views/expiry-trackers/_recipient-preview.blade.php` (new)

A reusable Blade partial that renders the recipient preview card:

```
┌──────────────────────────────────────────────┐
│  ✉ Notification Recipient Preview            │
├──────────────────────────────────────────────┤
│  SMTP Profile: Default System SMTP           │
│                                              │
│  ✓ jane@example.com — Assigned User          │
│  ✓ admin@co.com    — Administrator            │
│  ✓ billing@co.com  — Custom                  │
│                                              │
│  Recipients (3)                              │
└──────────────────────────────────────────────┘
```

When notifications are disabled:
```
┌──────────────────────────────────────────────┐
│  ✉ Notification Recipient Preview            │
├──────────────────────────────────────────────┤
│  ℹ Email notifications are disabled.         │
└──────────────────────────────────────────────┘
```

When enabled but no recipients selected:
```
┌──────────────────────────────────────────────┐
│  ✉ Notification Recipient Preview            │
├──────────────────────────────────────────────┤
│  SMTP Profile: Default System SMTP           │
│                                              │
│  ⚠ No recipients selected. Email             │
│  notifications will not be sent.             │
└──────────────────────────────────────────────┘
```

### 2. Controller — Recipient Resolution

**File:** `app/Http/Controllers/Web/ExpiryTrackerController.php`

| Method | Change |
|--------|--------|
| `show()` | Added `RenewalNotificationService` DI → calls `getRecipients($tracker)` → passes `$recipientPreview` to view |
| `edit()` | Same — passes `$recipientPreview` to view for the edit form |

Only resolves recipients when `email_notifications_enabled` is true (avoids unnecessary queries).

### 3. Views — Integration

| View | Location | Behavior |
|------|----------|----------|
| `show.blade.php` | Inside info card, after notification history link | Shows full preview with resolved recipients |
| `_notification-form.blade.php` | Inside notification settings, between custom email section and action buttons | Shows preview during edit |

The preview uses the already-public `RenewalNotificationService::getRecipients()` method, which calls `buildRecipients()` — the same method the actual email sending pipeline uses. This guarantees the preview matches what will actually be sent.

## Security

- No SMTP passwords or credentials are passed to or rendered by the view
- The preview only receives email addresses, recipient types, and the SMTP profile name
- Tested with `assertDontSee('smtp_password')` and `assertDontSee('smtp_username')`

## UI Consistency

- Uses the same gradient icon box (`from-indigo-500 to-purple-600`) as all widget headers
- Recipient rows use green checkmark + email + label badge — consistent with other list displays
- Warning state uses amber color scheme (same as form validation warnings)
- Disabled state uses gray — same pattern as disabled fields

## Files Modified

| File | Change |
|------|--------|
| `resources/views/expiry-trackers/_recipient-preview.blade.php` | **New** — reusable recipient preview partial |
| `app/Http/Controllers/Web/ExpiryTrackerController.php` | `show()` and `edit()` now pass `$recipientPreview` |
| `resources/views/expiry-trackers/show.blade.php` | Included `_recipient-preview` in info card |
| `resources/views/expiry-trackers/_notification-form.blade.php` | Included `_recipient-preview` in notification form |

## Tests Added

**File:** `tests/Feature/RecipientPreviewTest.php` (new, 9 tests)

| Test | Verifies |
|------|----------|
| `test_assigned_user_preview_appears` | Preview shows assigned user email + "Assigned User" label |
| `test_admin_recipients_preview_appears` | Preview shows admin email + "Administrator" label |
| `test_custom_emails_preview_appears` | Both custom emails shown with "Custom" label |
| `test_duplicate_email_appears_once` | Email appears at least once despite 3-source config |
| `test_no_recipient_warning_appears` | Warning text shown when no recipients selected |
| `test_smtp_profile_name_appears` | "Default System SMTP" shown in preview |
| `test_password_not_exposed` | Neither `smtp_password` nor `smtp_username` in response |
| `test_disabled_notification_shows_message` | "Email notifications are disabled" shown |
| `test_recipient_count_shown` | "Recipients (N)" count indicator present |

## Test Results

| Suite | Tests | Assertions | Status |
|-------|-------|------------|--------|
| RecipientPreviewTest | 9 | 26 | ✅ All passing |
| RenewalSchedulerCommandTest | 13 | 37 | ✅ All passing |
| Unit (full) | 411 | 733 | ✅ All passing |
| **Total** | **433** | **796** | ✅ **No regressions** |

## What Was NOT Changed

- Email sending pipeline (`RenewalNotificationService::sendReminders`, `sendEmail`, `resolveMailer`) — untouched
- SMTP Profile resolution — untouched
- Database schema — no migration
- Scheduler (`routes/console.php`) — untouched
- Command (`SendEmailReminders`) — untouched
- Deduplication logic (patch 1.0.1) — reused via `getRecipients()`
- Create page — no live JS preview (server-rendered on edit/show per MVP scope)
