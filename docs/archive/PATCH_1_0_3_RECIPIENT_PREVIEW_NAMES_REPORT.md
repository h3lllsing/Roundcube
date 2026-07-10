# Patch 1.0.3 - Recipient Preview Display Names & Sender Info

## Summary
Enriched the recipient preview partial with user display names for system users and a "From:" sender line for the show page notification summary.

## Changes Made

### `resources/views/expiry-trackers/_recipient-preview.blade.php`
- System user recipients now show display name (bold) on first line with email (smaller, gray) beside it
- Custom emails without a matching system user fall back to email-only display
- Added "From:" sender line showing sender name `<` email `>` or just email
- User name resolution via `\App\Models\User::whereIn('email', ...)->pluck('name', 'email')`
- Accepted new `$senderEmail` / `$senderName` props

### `resources/views/expiry-trackers/show.blade.php`
- Notification summary area now includes:
  - **From** line — SMTP profile's `sender_email`/`sender_name`, or `config('mail.from.address')` fallback
  - **Recipients** count — derived from `$recipientPreview`
- Recipient preview partial now receives `senderEmail` and `senderName`

### `resources/views/expiry-trackers/_notification-form.blade.php`
- Recipient preview partial now receives `senderEmail` and `senderName`

### `tests/Feature/RecipientPreviewTest.php`
- `test_assigned_user_shows_display_name` — verifies "Masood Nasir" name + "masood@alphatach.com" email + "Assigned User" badge
- `test_admin_recipient_shows_display_name` — verifies admin user "Ali Khan" name + "ali@alphatach.com" email + "Administrator" badge
- `test_custom_email_without_user_shows_email_only` — verifies "accounts@company.com" displayed without a name label
- `test_smtp_profile_and_from_appear` — verifies "Default System SMTP" + "From:" present
- `test_smtp_credentials_not_exposed` — verifies `smtp_password`, `smtp_username`, `smtp_host`, `smtp_port` are absent from rendered output
- `test_show_page_recipients_count_line` — verifies "Recipients" text appears in notification summary
- All existing tests updated with display name references where applicable

## Test Results
```
OK (10 tests, 33 assertions)
```
Combined renewal + preview suite: **23 tests, 70 assertions — all passing**

## Security
- SMTP credentials (host, port, username, password) are never passed to the view
- Sender info derived from `smtp_profile.sender_email` / `sender_name` only — no SMTP connection data
- User lookup is read-only from User model; no write operations
