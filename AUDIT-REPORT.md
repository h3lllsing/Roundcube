# AUDIT REPORT - RoundCube Portal (Alphaspace)

> **Project:** Enterprise IT Operations Platform (Laravel 12)
> **Base URL:** http://localhost/roundcube/public
> **Login:** admin@tyro.project / password
> **Total Audit Items:** AUDIT-1 to AUDIT-90

---

## AUTH-01 to AUTH-08: AUTHENTICATION SYSTEM

### AUDIT-01: Login Page
- **URL:** `/login`
- **Type:** Web Page (GET)
- **Controller:** `Web\AuthController@showLoginForm`
- **View:** `auth.login`
- **Features:** Email/password fields, Remember me checkbox, Forgot password link
- **Middleware:** `guest` (redirects authenticated users)

### AUDIT-02: Login POST
- **URL:** `/login`
- **Type:** Form Submit (POST)
- **Controller:** `Web\AuthController@login`
- **Features:** Validates credentials, Suspended user check, Audit log on success/failure, Session regeneration
- **Rate Limit:** `throttle:5,1` (5 attempts per minute)

### AUDIT-03: Register Page
- **URL:** `/register`
- **Type:** Web Page (GET)
- **Controller:** `Web\AuthController@showRegistrationForm`
- **View:** `auth.register`
- **Middleware:** `guest`
- **Note:** Aborts 403 if registration is disabled

### AUDIT-04: Register POST
- **URL:** `/register`
- **Type:** Form Submit (POST)
- **Controller:** `Web\AuthController@register`
- **Rate Limit:** `throttle:5,1`
- **Features:** Name, Email, Password with complexity rules, Auto-redirect to login

### AUDIT-05: Forgot Password
- **URL:** `/forgot-password`
- **Type:** Web Page (GET) + Form (POST)
- **Controller:** `Web\AuthController@showForgotPasswordForm` / `@sendResetLink`
- **Rate Limit:** `throttle:5,1`
- **Features:** Email input, Sends reset link via Laravel Password facade

### AUDIT-06: Reset Password
- **URL:** `/reset-password/{token}`
- **Type:** Web Page (GET) + Form (POST)
- **Controller:** `Web\AuthController@showResetForm` / `@resetPassword`
- **Rate Limit:** `throttle:5,1`
- **Features:** Token validation, Password reset with logging

### AUDIT-07: Profile
- **URL:** `/profile`
- **Type:** Web Page (GET + PUT)
- **Controller:** `Web\AuthController@profile` / `@updateProfile`
- **View:** `auth.profile`
- **Features:** Update name/email, Change password with current_password confirmation, Optimistic locking (`updated_at` check)

### AUDIT-08: Logout & Email Verification
- **URL:** `/logout` (POST), `/email/verify/{id}/{hash}` (GET), `/email/verification-notification` (POST)
- **Controller:** `Web\AuthController@logout` / `@verifyEmail` / `@resendVerification`
- **Features:** Audit log on logout, Email verification via signed URL, Resend verification

---

## DASH-01: DASHBOARD

### AUDIT-09: Dashboard Page
- **URL:** `/dashboard`
- **Type:** Web Page (GET)
- **Controller:** `Web\DashboardController@index`
- **View:** `dashboard.index`
- **Widgets:**
  - Total Users count
  - Total Notifications count
  - Total Email Accounts count
  - Audit Actions summary (last 7 days: soft deletes, restores, force deletes)
  - IMAP health monitoring (failed vs healthy accounts)
  - User's assigned email accounts (with "Open Webmail" links)
  - Active domains (badge list)
  - Recent activity feed

---

## DOM-01 to DOM-09: DOMAINS MODULE (SuperAdmin only)

### AUDIT-10: Domains List
- **URL:** `/domains`
- **Type:** Web Page (GET)
- **Controller:** `Web\DomainController@index`
- **View:** `domains.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** Active/Trash tabs, Search by name, Status filter, Pagination

### AUDIT-11: Create Domain Page
- **URL:** `/domains/create`
- **Type:** Web Page (GET)
- **Controller:** `Web\DomainController@create`
- **View:** `domains.create`

### AUDIT-12: Store Domain
- **URL:** `/domains`
- **Type:** Form Submit (POST)
- **Controller:** `Web\DomainController@store`
- **Rate Limit:** `throttle:import` (5/min)
- **Features:** Validates domain name + status + notes, Activity logging, Cache version bump

### AUDIT-13: Show Domain
- **URL:** `/domains/{domain}`
- **Type:** Web Page (GET)
- **Controller:** `Web\DomainController@show`
- **View:** `domains.show`
- **Features:** Domain info card, Associated email accounts table

### AUDIT-14: Edit Domain Page
- **URL:** `/domains/{domain}/edit`
- **Type:** Web Page (GET)
- **Controller:** `Web\DomainController@edit`
- **View:** `domains.edit`

### AUDIT-15: Update Domain
- **URL:** `/domains/{domain}`
- **Type:** Form Submit (PUT)
- **Controller:** `Web\DomainController@update`
- **Features:** Optimistic locking, Activity logging, Cache version bump

### AUDIT-16: Delete/Restore/Force-Delete Domain
- **URLs:** `/domains/{domain}` (DELETE), `/domains/{id}/restore` (POST), `/domains/{id}/force-delete` (DELETE)
- **Controller:** `Web\DomainController@destroy` / `@restore` / `@forceDelete`
- **Features:** Soft-delete with `deleted_by`, Auto-deactivates associated email accounts on Domain model boot events, Activity logging, Cache version bump

### AUDIT-17: Domain Model Events
- **Model:** `Domain.php`
- **Events:** `saved()` - cascades activation/deactivation to email accounts; `deleted()` - deactivates all associated email accounts
- **Note:** Business logic in model events (not controller)

### AUDIT-18: Domain API
- **URLs:** `/api/domains` (GET), `/api/domains/{domain}` (GET)
- **Controller:** `Api\DomainController@index` / `@show`
- **Auth:** Sanctum
- **Features:** Read-only, Paginated (20/page), Includes email accounts count

---

## EMAIL-01 to EMAIL-12: EMAIL ACCOUNTS MODULE (Admin+)

### AUDIT-19: Email Accounts List
- **URL:** `/email_accounts`
- **Type:** Web Page (GET)
- **Controller:** `Web\EmailAccountController@index`
- **View:** `email-accounts.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** Active/Trash tabs, Search by email, Domain/Status filters, Pagination

### AUDIT-20: Create Email Account Page
- **URL:** `/email_accounts/create`
- **Type:** Web Page (GET)
- **Controller:** `Web\EmailAccountController@create`
- **View:** `email-accounts.create`
- **Features:** Active domains list dropdown

### AUDIT-21: Auto-Discover
- **URL:** `/email-accounts/auto-discover`
- **Type:** AJAX (GET)
- **Controller:** `Web\EmailAccountController@autoDiscover`
- **Features:** Accepts email address, DNS lookup for IMAP/SMTP settings, Returns JSON (422 on error)

### AUDIT-22: Store Email Account
- **URL:** `/email_accounts`
- **Type:** Form Submit (POST)
- **Controller:** `Web\EmailAccountController@store`
- **Rate Limit:** `throttle:import` (5/min)
- **Features:** Domain, Email, Password, IMAP/SMTP host/port/encryption, Sync toggle, Status, Activity logging, Cache version bump

### AUDIT-23: Show Email Account
- **URL:** `/email_accounts/{email_account}`
- **Type:** Web Page (GET)
- **Controller:** `Web\EmailAccountController@show`
- **View:** `email-accounts.show`
- **Features:** Email info, IMAP settings, SMTP settings, Assigned Users with Send/Receive permissions

### AUDIT-24: Edit Email Account Page
- **URL:** `/email_accounts/{email_account}/edit`
- **Type:** Web Page (GET)
- **Controller:** `Web\EmailAccountController@edit`
- **View:** `email-accounts.edit`

### AUDIT-25: Update Email Account
- **URL:** `/email_accounts/{email_account}`
- **Type:** Form Submit (PUT)
- **Controller:** `Web\EmailAccountController@update`
- **Features:** Optimistic locking, Optional password fields (leave blank = keep current), Activity logging, Cache version bump

### AUDIT-26: Delete/Restore/Force-Delete Email Account
- **URLs:** DELETE/POST/DELETE
- **Controller:** `Web\EmailAccountController@destroy` / `@restore` / `@forceDelete`
- **Features:** Soft-delete with `deleted_by`, Activity logging, Cache version bump

### AUDIT-27: Email Assignment (Assign User)
- **URL:** `/email_accounts/{email_account}/assign` (POST)
- **Controller:** `Web\EmailAssignmentController@store`
- **Features:** SyncWithoutDetaching user to account, Logs assignment activity

### AUDIT-28: Email Assignment (Revoke User)
- **URL:** `/email_accounts/{email_account}/assign/{user}` (DELETE)
- **Controller:** `Web\EmailAssignmentController@destroy`
- **Features:** Detach user from account, Logs revocation activity

### AUDIT-29: Email Password Encryption
- **Note:** Passwords stored as `Crypt::encryptString()` in `password_encrypted` column
- **Webmail Tokens:** Include `password_plain` in addition to encrypted (for RoundCube compatibility)
- **Security:** Single-use tokens, 5-minute expiry, Origin/Referer validation on resolve

### AUDIT-30: Email Account API
- **URLs:** `/api/email-accounts` (GET), `/api/email-accounts/{email_account}` (GET)
- **Controller:** `Api\EmailAccountController@index` / `@show`
- **Auth:** Sanctum
- **Features:** Read-only, Paginated (20/page), Includes domain + assigned users

---

## WEB-01 to WEB-04: WEBMAIL MODULE

### AUDIT-31: Webmail List
- **URL:** `/web-mail`
- **Type:** Web Page (GET)
- **Controller:** `Web\WebmailController@index`
- **View:** `webmail.index`
- **Features:** Accounts grouped by domain, Status badges, IMAP server info, "Open Webmail" links

### AUDIT-32: Open Webmail (User)
- **URL:** `/web-mail/open/{email_account}`
- **Type:** Web Page (GET)
- **Controller:** `Web\WebmailController@redirect`
- **Features:** Access check (admin=any, user=assigned only), Generates single-use token, Shows launch page with iframe

### AUDIT-33: Open Webmail (Admin - Open As)
- **URL:** `/web-mail/open-as/{email_account}`
- **Type:** Web Page (GET)
- **Controller:** `Web\WebmailController@openAs`
- **Features:** Admin-only, Allows admin to open any user's email account

### AUDIT-34: Webmail Token Resolve (Public)
- **URL:** `/webmail-auth/resolve`
- **Type:** Public GET with `?t={token}`
- **Controller:** `Web\WebmailController@resolve`
- **Features:** Origin/Referer validation, Race-safe single-update (mark token used), Returns account email as JSON, 5-min token expiry
- **Security:** Token is 64 hex chars (random_bytes), Single-use only

---

## USR-01 to USR-11: USER MANAGEMENT (SuperAdmin only)

### AUDIT-35: Users List
- **URL:** `/users`
- **Type:** Web Page (GET)
- **Controller:** `Web\UserController@index`
- **View:** `users.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** Search, Role filter, Status filter, Date range filter, Last Login (subquery), Pagination

### AUDIT-36: Create User Page
- **URL:** `/users/create`
- **Type:** Web Page (GET)
- **Controller:** `Web\UserController@create`
- **View:** `users.create`

### AUDIT-37: Store User
- **URL:** `/users`
- **Type:** Form Submit (POST)
- **Controller:** `Web\UserController@store`
- **Rate Limit:** `throttle:import` (5/min)
- **Features:** Transactional create, Role + Status support, Activity logging, Cache version bump

### AUDIT-38: Show User
- **URL:** `/users/{id}`
- **Type:** Web Page (GET)
- **Controller:** `Web\UserController@show`
- **View:** `users.show`
- **Features:** User info grid, Last successful login, Activity Timeline component

### AUDIT-39: Edit User Page
- **URL:** `/users/{id}/edit`
- **Type:** Web Page (GET)
- **Controller:** `Web\UserController@edit`
- **View:** `users.edit`

### AUDIT-40: Update User
- **URL:** `/users/{id}`
- **Type:** Form Submit (PUT)
- **Controller:** `Web\UserController@update`
- **Features:** Optimistic locking, Prevents self-demotion from super-admin, Logs old vs new field values, Activity logging

### AUDIT-41: Suspend User
- **URL:** `/users/{id}/suspend` (PATCH)
- **Controller:** `Web\UserController@suspend`
- **Features:** Suspends with optional reason, Activity logging, Cache version bump

### AUDIT-42: Unsuspend User
- **URL:** `/users/{id}/unsuspend` (PATCH)
- **Controller:** `Web\UserController@unsuspend`
- **Features:** Clears `suspended_at` + `suspension_reason`, Activity logging, Cache version bump

### AUDIT-43: Suspended User Middleware
- **Middleware:** `App\Http\Middleware\CheckSuspended`
- **Behavior:** Returns 403 if `$user->isSuspended()` is true
- **Applied to:** All authenticated web routes

### AUDIT-44: Delete/Restore/Force-Delete User
- **URLs:** DELETE/PATCH/DELETE
- **Controller:** `Web\UserController@destroy` / `@restore` / `@forceDelete`
- **Features:** Prevents deleting last super-admin (transactional check), Soft-delete, Activity logging, Cache version bump

---

## NOT-01 to NOT-06: NOTIFICATIONS MODULE

### AUDIT-45: Notifications List
- **URL:** `/notifications`
- **Type:** Web Page (GET)
- **Controller:** `Web\NotificationController@index`
- **View:** `notifications.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** All/Unread tabs, Search, Pagination

### AUDIT-46: Mark Single as Read
- **URL:** `/notifications/{id}/read` (POST)
- **Controller:** `Web\NotificationController@markAsRead`

### AUDIT-47: Mark All as Read
- **URL:** `/notifications/read-all` (POST)
- **Controller:** `Web\NotificationController@markAllAsRead`

### AUDIT-48: Delete Single Notification
- **URL:** `/notifications/{id}` (DELETE)
- **Controller:** `Web\NotificationController@destroy`

### AUDIT-49: Bulk Delete
- **URL:** `/notifications/bulk-delete` (POST)
- **Controller:** `Web\NotificationController@bulkDelete`
- **Rate Limit:** `throttle:bulk` (10/min)

### AUDIT-50: Bulk Mark Read
- **URL:** `/notifications/bulk-read` (POST)
- **Controller:** `Web\NotificationController@bulkMarkAsRead`
- **Rate Limit:** `throttle:bulk` (10/min)

---

## LOG-01 to LOG-05: AUDIT & LOGS (SuperAdmin only)

### AUDIT-51: Activity Log List
- **URL:** `/activity-logs`
- **Type:** Web Page (GET)
- **Controller:** `Web\ActivityLogController@index`
- **View:** `activity-logs.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** Search, Event type filter (created/updated/deleted/restored/revealed/login/logout/imap_dns_fallback/imap_fetch_failed), User filter, Date range, Paginated (50/page)

### AUDIT-52: Activity Log Detail
- **URL:** `/activity-logs/{id}`
- **Type:** Web Page (GET)
- **Controller:** `Web\ActivityLogController@show`
- **View:** `activity-logs.show`
- **Features:** Full event details, Old→New attribute diff, Activity Timeline component

### AUDIT-53: Login Audit List
- **URL:** `/login-audits`
- **Type:** Web Page (GET)
- **Controller:** `Web\LoginAuditController@index`
- **View:** `login-audits.index`
- **Rate Limit:** `throttle:search` (20/min)
- **Features:** Search (email/IP), Event filter (login_success/login_failed/logout), Date range

### AUDIT-54: Login Audit Detail
- **URL:** `/login-audits/{id}`
- **Type:** Web Page (GET)
- **Controller:** `Web\LoginAuditController@show`
- **View:** `login-audits.show`
- **Features:** IP Address, User Agent, Event type, Timestamp

### AUDIT-55: Delete Login Audit
- **URL:** `/login-audits/{id}` (DELETE)
- **Controller:** `Web\LoginAuditController@destroy`

---

## API-01 to API-07: API ENDPOINTS

### AUDIT-56: API Login
- **URL:** `/api/login` (POST)
- **Controller:** `Api\AuthController@login`
- **Rate Limit:** `throttle:5,1`
- **Features:** Email/password + device_name, Suspended check, Issues Sanctum plainTextToken, Returns token + user JSON

### AUDIT-57: API Get Current User
- **URL:** `/api/user` (GET)
- **Auth:** Sanctum
- **Returns:** Authenticated user object

### AUDIT-58: API Dashboard Stats
- **URL:** `/api/dashboard/stats` (GET)
- **Controller:** `Api\DashboardController@stats`
- **Auth:** Sanctum

### AUDIT-59: API Domains List
- **URL:** `/api/domains` (GET)
- **Controller:** `Api\DomainController@index`
- **Auth:** Sanctum
- **Features:** Paginated (20/page), Includes email accounts count

### AUDIT-60: API Domain Show
- **URL:** `/api/domains/{domain}` (GET)
- **Controller:** `Api\DomainController@show`
- **Auth:** Sanctum
- **Features:** Includes email accounts

### AUDIT-61: API Email Accounts List
- **URL:** `/api/email-accounts` (GET)
- **Controller:** `Api\EmailAccountController@index`
- **Auth:** Sanctum
- **Features:** Paginated (20/page), Includes domain

### AUDIT-62: API Email Account Show
- **URL:** `/api/email-accounts/{email_account}` (GET)
- **Controller:** `Api\EmailAccountController@show`
- **Auth:** Sanctum
- **Features:** Includes domain + assigned users

---

## DB-01 to DB-21: DATABASE TABLES

### Application Tables

### AUDIT-63: `users` table
- **Columns:** id, name, email, email_verified_at, password, role, is_active, suspended_at, suspension_reason, remember_token, created_at, updated_at, deleted_at
- **Indexes:** PRIMARY (id), UNIQUE (email)

### AUDIT-64: `email_accounts` table
- **Columns:** id, domain_id, user_id, email, name, domain, username, password_encrypted, imap_host/port/encryption, smtp_host/port/encryption, is_active, is_online, unread_count, last_sync_at, last_checked_at, last_uid_synced (JSON), uid_validity (JSON), sync_status (ENUM), sync_started_at, sync_error, created_by, created_at, updated_at, deleted_at
- **FKs:** user_id → users, created_by → users, domain_id → domains

### AUDIT-65: `emails` table
- **Columns:** id, email_account_id, message_id, uid, references, in_reply_to, thread_id, folder, subject, from_addr/name, to_addr/cc/bcc, body_text, body_html, has_attachments, is_read/flagged/draft, sent_at, received_at, created_at, updated_at
- **Indexes:** Composite (email_account_id,folder), (email_account_id,is_read), (email_account_id,folder,uid), (email_account_id,folder,received_at), (email_account_id,is_read,folder), FULLTEXT (subject,from_addr,from_name,body_text,body_html)

### AUDIT-66: `email_attachments` table
- **Columns:** id, email_id, filename, mime_type, size, path, cid, is_inline, created_at, updated_at
- **FK:** email_id → emails ON DELETE CASCADE

### AUDIT-67: `email_assignments` table
- **Columns:** id, email_account_id, user_id, assigned_by, can_send, can_receive, status, assigned_at, revoked_at, created_at, updated_at
- **FKs:** email_account_id → email_accounts, user_id → users, assigned_by → users
- **Unique:** (email_account_id, user_id)

### AUDIT-68: `activity_logs` table
- **Columns:** id, user_id, action, model_type, model_id, metadata (JSON), ip_address, user_agent, created_at, updated_at
- **Indexes:** (model_type,model_id), (created_at)

### AUDIT-69: `domains` table
- **Columns:** id, domain (UNIQUE), provider, imap_host/port/encryption, smtp_host/port/encryption, is_active, notes, created_by, created_at, updated_at, deleted_at

### AUDIT-70: `contacts` table
- **Columns:** id, email_account_id, email, name, source (ENUM: from/to/cc), frequency, last_contacted_at, created_at, updated_at
- **Unique:** (email_account_id, email, source)

### Auth & Tokens Tables

### AUDIT-71: `personal_access_tokens` table (Sanctum)
- **Columns:** id, tokenable_type, tokenable_id, name, token (unique hash), abilities (text), last_used_at, expires_at, created_at, updated_at

### AUDIT-72: `webmail_tokens` table
- **Columns:** id, token (64-char hex), email_account_id, email, password_encrypted, password_plain, imap_host/port/encryption, smtp_host/port/encryption, expires_at, used (boolean), created_at, updated_at
- **Indexes:** (expires_at, used), (email_account_id)

### AUDIT-73: `password_reset_tokens` table
- **Columns:** email (PK), token, created_at

### Queue & Cache Tables

### AUDIT-74: `jobs` / `job_batches` / `failed_jobs` tables
- **Purpose:** Laravel queue infrastructure for async processing (IMAP sync, notifications)
- **Cleanup:** `sanctum:prune-expired` runs daily

### AUDIT-75: `cache` / `cache_locks` tables
- **Purpose:** File-based cache store (dashboard cache versioning)

### AUDIT-76: `sessions` table
- **Purpose:** File-based session storage

---

## SCH-01 to SCH-06: BACKGROUND JOBS

### AUDIT-77: Sanctum Token Cleanup
- **Command:** `sanctum:prune-expired`
- **Schedule:** Daily
- **Purpose:** Removes expired API tokens

### AUDIT-78: Activity Log Cleanup
- **Command:** `activitylog:clean`
- **Schedule:** Daily
- **Purpose:** Cleans old activity log entries

### AUDIT-79: Login Audit Cleanup
- **Command:** Custom closure
- **Schedule:** Daily
- **Purpose:** Deletes login audits older than 1 year (`onOneServer()`)

### AUDIT-80: Queue Worker
- **Command:** `queue:work --stop-when-empty --max-time=240 --sleep=3`
- **Schedule:** Every minute
- **Features:** `withoutOverlapping()`, `runInBackground()`

### AUDIT-81: IMAP Sync Dispatch
- **Command:** `email-sync:dispatch`
- **Schedule:** Every 10 minutes
- **Features:** `withoutOverlapping()`
- **Purpose:** Dispatches IMAP sync jobs for all enabled email accounts

### AUDIT-82: Email Sync Failed Notification
- **Type:** Queued Job
- **Purpose:** Sends alert when IMAP sync fails for an account

---

## RATE-01 to RATE-04: RATE LIMITING

### AUDIT-83: Login Rate Limiting
- **Limiter:** `throttle:5,1`
- **Applied to:** Login POST, Register POST, Forgot Password POST, Reset Password POST
- **Limit:** 5 requests per minute

### AUDIT-84: Search Rate Limiting
- **Limiter:** `throttle:search` (20/minute)
- **Applied to:** Domains index, Email Accounts index, Users index, Activity Logs index, Login Audits index, Notifications index

### AUDIT-85: Import Rate Limiting
- **Limiter:** `throttle:import` (5/minute)
- **Applied to:** All create/store operations (Domains, Email Accounts, Users)

### AUDIT-86: Bulk Rate Limiting
- **Limiter:** `throttle:bulk` (10/minute)
- **Applied to:** Bulk notification actions (delete, mark read)

---

## NAV-01 to NAV-05: NAVIGATION & UI

### AUDIT-87: Sidebar Navigation
- **Components:** `sidebar-header`, `sidebar-nav-groups`, `sidebar-search`, `user-card`
- **Layout:** `layouts.admin` (master shell with sidebar + breadcrumbs + toast + command palette)

### AUDIT-88: UI Components
- **Library:** 23 Blade components + 5 form sub-components
- **Includes:** button, badge, card, table, field, alert, toast, empty-state, action, loading-overlay, confirm-dialog, command-palette, activity-timeline, bulk-actions, copy-button, dark-toggle, date, filter-input, filter-select, breadcrumbs, page-header, fonts, nav-link

### AUDIT-89: Theme Support
- **Dark Mode:** Toggle button in sidebar, persists via localStorage, class-based on `<html>`
- **Styling:** Tailwind CSS v4, Glass morphism sidebar, Gradient accents

### AUDIT-90: Command Palette
- **Shortcut:** Ctrl+K / Cmd+K
- **Features:** Search overlay, Keyboard navigation, Links to all pages (conditional on super-admin)

---

## SUMMARY

| Audit Category | Audit Items | Count |
|---------------|-------------|-------|
| Authentication System | AUDIT-01 to AUDIT-08 | 8 |
| Dashboard | AUDIT-09 | 1 |
| Domains Module | AUDIT-10 to AUDIT-18 | 9 |
| Email Accounts Module | AUDIT-19 to AUDIT-30 | 12 |
| Webmail Module | AUDIT-31 to AUDIT-34 | 4 |
| User Management | AUDIT-35 to AUDIT-44 | 10 |
| Notifications Module | AUDIT-45 to AUDIT-50 | 6 |
| Audit & Logs | AUDIT-51 to AUDIT-55 | 5 |
| API Endpoints | AUDIT-56 to AUDIT-62 | 7 |
| Database Tables | AUDIT-63 to AUDIT-76 | 14 |
| Background Jobs | AUDIT-77 to AUDIT-82 | 6 |
| Rate Limiting | AUDIT-83 to AUDIT-86 | 4 |
| Navigation & UI | AUDIT-87 to AUDIT-90 | 4 |
| **TOTAL** | **AUDIT-01 to AUDIT-90** | **90** |
