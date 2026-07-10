# POST-DEPLOYMENT SMOKE TEST PLAN

> Generated: 2026-07-03
> App: OpsPilot | URL: https://yourdomain.com

---

## Instructions

Execute these tests in order after deployment. Mark each test as:

| Status | Meaning |
|---|---|
| ✅ PASS | Works as expected |
| ❌ FAIL | Does not work — investigate before proceeding |
| ⏭️ SKIP | Not applicable or deferred |

For any ❌, stop and investigate before continuing to the next phase.

---

## Phase 1: Basic Availability (Critical Path)

### 1.1 HTTPS & Domain

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 1.1.1 | Visit `https://yourdomain.com` | Redirects to `/login` or `/dashboard` | ☐ | |
| 1.1.2 | Check browser address bar | Padlock icon (valid SSL) | ☐ | |
| 1.1.3 | Check no mixed content warnings | No warnings in browser console | ☐ | |

### 1.2 Login Page

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 1.2.1 | Visit `/login` | Login page loads with 200 | ☐ | |
| 1.2.2 | Login page styling | CSS loads, no broken layout | ☐ | |
| 1.2.3 | Dark mode toggle | Toggle works (if available) | ☐ | |
| 1.2.4 | Check browser console | No JS errors | ☐ | |

### 1.3 Authentication

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 1.3.1 | Login with valid admin credentials | Redirect to `/dashboard` | ☐ | Use `admin@tyro.project` or seeded admin |
| 1.3.2 | Login with invalid credentials | Shows error: "The provided credentials do not match" | ☐ | |
| 1.3.3 | Login with empty fields | Shows validation errors | ☐ | |
| 1.3.4 | Remember Me checkbox | Stays logged in after browser close | ☐ | |
| 1.3.5 | Logout | Redirects to `/login` | ☐ | |

### 1.4 Registration

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 1.4.1 | Visit `/register` | Registration page loads | ☐ | |
| 1.4.2 | Register new account | Redirects to `/login` with success message | ☐ | |
| 1.4.3 | Login with new account | Success | ☐ | |

### 1.5 Password Reset

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 1.5.1 | Visit `/forgot-password` | Form loads | ☐ | |
| 1.5.2 | Submit valid email | Shows success message | ☐ | |
| 1.5.3 | Check email inbox | Reset link email received | ☐ | Tests SMTP configuration |

---

## Phase 2: Core Pages (Authenticated)

Log in as a super-admin user before proceeding.

### 2.1 Dashboard

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.1.1 | Visit `/dashboard` | 200 OK | ☐ | |
| 2.1.2 | Stat cards render | Numbers/metrics visible | ☐ | |
| 2.1.3 | Widgets render | Activity, tasks, renewals, vault, etc. | ☐ | |
| 2.1.4 | No 500 errors | Page loads without error | ☐ | |
| 2.1.5 | No JS errors | Check browser console | ☐ | |
| 2.1.6 | Responsive layout | Resize browser — layout adapts | ☐ | |

### 2.2 Domains

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.2.1 | Visit `/domains` | 200 OK | ☐ | |
| 2.2.2 | Domains list loads | Table/grid with domain entries | ☐ | |
| 2.2.3 | Create domain `/domains/create` | Form loads, submit creates domain | ☐ | |
| 2.2.4 | Edit domain | Form loads with data, save updates | ☐ | |
| 2.2.5 | Show domain `/domains/{id}` | Detail page loads | ☐ | |
| 2.2.6 | Delete domain | Soft delete (moves to trash) | ☐ | |
| 2.2.7 | Search/filter | Filters and search work | ☐ | |
| 2.2.8 | Pagination | Next/previous page works | ☐ | |
| 2.2.9 | Export | CSV/PDF export downloads | ☐ | |

### 2.3 Hostings

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.3.1 | Visit `/hostings` | 200 OK | ☐ | |
| 2.3.2 | CRUD operations | Create, read, update, delete all work | ☐ | |
| 2.3.3 | Password reveal | Password button reveals/copies | ☐ | |
| 2.3.4 | Search/filter/pagination | All work | ☐ | |
| 2.3.5 | Export | Works | ☐ | |

### 2.4 Service Providers

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.4.1 | Visit `/service-providers` | 200 OK | ☐ | |
| 2.4.2 | CRUD operations | All work | ☐ | |
| 2.4.3 | Password reveal | Works | ☐ | |
| 2.4.4 | Search/filter/export | All work | ☐ | |

### 2.5 Domain Emails

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.5.1 | Visit `/domain-emails` | 200 OK | ☐ | |
| 2.5.2 | CRUD operations | All work | ☐ | |
| 2.5.3 | Password reveal | Works | ☐ | |
| 2.5.4 | Search/filter/export | All work | ☐ | |

### 2.6 VPS

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.6.1 | Visit `/vps` | 200 OK | ☐ | |
| 2.6.2 | CRUD operations | All work | ☐ | |
| 2.6.3 | Password reveal | Works | ☐ | |
| 2.6.4 | Search/filter/export | All work | ☐ | |

### 2.7 VoIP

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.7.1 | Visit `/voip` | 200 OK | ☐ | |
| 2.7.2 | CRUD operations | All work | ☐ | |
| 2.7.3 | Password reveal | Works | ☐ | |
| 2.7.4 | Extension password | Works | ☐ | |
| 2.7.5 | Search/filter/export | All work | ☐ | |

### 2.8 Other Services

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.8.1 | Visit `/other-services` | 200 OK | ☐ | |
| 2.8.2 | CRUD operations | All work | ☐ | |
| 2.8.3 | Password reveal | Works | ☐ | |
| 2.8.4 | Search/filter/export | All work | ☐ | |

### 2.9 Assets

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.9.1 | Visit `/assets` | 200 OK | ☐ | |
| 2.9.2 | CRUD operations | Create, read, update, delete | ☐ | |
| 2.9.3 | Asset assignment | Assign to user works | ☐ | |
| 2.9.4 | Asset return | Return asset works | ☐ | |
| 2.9.5 | Search/filter/export | All work | ☐ | |

### 2.10 Renewals (Expiry Trackers)

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.10.1 | Visit `/expiry-trackers` | 200 OK | ☐ | |
| 2.10.2 | CRUD operations | All work | ☐ | |
| 2.10.3 | Notification configuration | Configure recipients, channels | ☐ | |
| 2.10.4 | Preview email | Email preview renders | ☐ | |
| 2.10.5 | Test email | Test notification sent | ☐ | Tests SMTP + queue |
| 2.10.6 | Send reminder | Reminder queued and sent | ☐ | Tests queue worker |
| 2.10.7 | Search/filter/export | All work | ☐ | |

### 2.11 Tasks

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.11.1 | Visit `/tasks` | 200 OK | ☐ | |
| 2.11.2 | My Tasks `/my-tasks` | User's tasks loaded | ☐ | |
| 2.11.3 | Kanban view `/tasks/kanban` | Board view renders | ☐ | |
| 2.11.4 | CRUD operations | All work | ☐ | |
| 2.11.5 | Status update | Change task status | ☐ | |
| 2.11.6 | Task assignment | Assign to user | ☐ | Tests notification |
| 2.11.7 | Search/filter/export | All work | ☐ | |

### 2.12 Notifications

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.12.1 | Visit `/notifications` | 200 OK | ☐ | |
| 2.12.2 | Notification list | Entries visible | ☐ | |
| 2.12.3 | Mark as read | Click to mark single | ☐ | |
| 2.12.4 | Mark all read | Bulk action works | ☐ | |
| 2.12.5 | Delete notification | Single delete works | ☐ | |
| 2.12.6 | Bulk delete | Works | ☐ | |

### 2.13 Calendar

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.13.1 | Visit `/calendar` | 200 OK | ☐ | |
| 2.13.2 | Calendar renders | Month/week/day view | ☐ | |
| 2.13.3 | Navigation | Previous/next month works | ☐ | |
| 2.13.4 | Events display | Tasks/events shown | ☐ | |

### 2.14 Vault

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.14.1 | Visit `/vault` | 200 OK | ☐ | |
| 2.14.2 | My Vault `/my-vault` | Personal entries | ☐ | |
| 2.14.3 | CRUD operations | All work | ☐ | |
| 2.14.4 | Password reveal | Reveal works (audit logged) | ☐ | |
| 2.14.5 | Search/filter | All work | ☐ | |

### 2.15 Help Center

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 2.15.1 | Visit `/guide` | 200 OK | ☐ | |
| 2.15.2 | Help content loads | Articles visible | ☐ | |
| 2.15.3 | Search help | Search returns results | ☐ | |
| 2.15.4 | Module help `/help/module/{module}` | Module-specific help | ☐ | |

---

## Phase 3: Super-Admin Pages

Log in as a user with `super-admin` role.

### 3.1 Users

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.1.1 | Visit `/users` | 200 OK | ☐ | |
| 3.1.2 | CRUD operations | Create, read, update, delete | ☐ | |
| 3.1.3 | User permissions | Edit permissions per user | ☐ | |
| 3.1.4 | Suspend/unsuspend | User suspension works | ☐ | |
| 3.1.5 | Clone user | Clone form/action works | ☐ | |
| 3.1.6 | Search/filter/export | All work | ☐ | |

### 3.2 Roles

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.2.1 | Visit `/admin/roles` | 200 OK | ☐ | |
| 3.2.2 | CRUD operations | All work | ☐ | |
| 3.2.3 | Attach/detach privileges | Privilege assignment works | ☐ | |
| 3.2.4 | Role templates | Apply template to role | ☐ | |
| 3.2.5 | Search/filter | All work | ☐ | |

### 3.3 Permissions (Privileges)

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.3.1 | Visit `/admin/privileges` | 200 OK | ☐ | |
| 3.3.2 | CRUD operations | All work | ☐ | |
| 3.3.3 | Search/filter | All work | ☐ | |

### 3.4 Reports

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.4.1 | Visit `/reports` | 200 OK | ☐ | |
| 3.4.2 | Asset reports | Reports render with data | ☐ | |
| 3.4.3 | Domain reports | Reports render | ☐ | |
| 3.4.4 | Hosting reports | Reports render | ☐ | |
| 3.4.5 | Renewal reports | Reports render | ☐ | |
| 3.4.6 | Task reports | Reports render | ☐ | |
| 3.4.7 | User reports | Reports render | ☐ | |
| 3.4.8 | VPS reports | Reports render | ☐ | |
| 3.4.9 | Export reports | CSV export works | ☐ | |

### 3.5 Activity Logs

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.5.1 | Visit `/activity-logs` | 200 OK | ☐ | |
| 3.5.2 | Log entries visible | Recent activity shown | ☐ | |
| 3.5.3 | Detail view | Click entry to see details | ☐ | |
| 3.5.4 | Search/filter/pagination | All work | ☐ | |

### 3.6 Module Permissions

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 3.6.1 | Visit `/module-permissions` | 200 OK | ☐ | |
| 3.6.2 | Update permissions | Changes save | ☐ | |

---

## Phase 4: Feature Tests

### 4.1 Search

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.1.1 | Use global search | Results appear for domains, hostings, etc. | ☐ | |
| 4.1.2 | Search returns relevant results | Correct items matched | ☐ | |

### 4.2 Export

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.2.1 | Export domains | CSV file downloads | ☐ | |
| 4.2.2 | Export hostings | CSV file downloads | ☐ | |
| 4.2.3 | Export other resources | All export actions work | ☐ | |

### 4.3 Bulk Actions

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.3.1 | Select multiple items | Checkboxes work | ☐ | |
| 4.3.2 | Bulk delete | All selected deleted | ☐ | |
| 4.3.3 | Bulk export | All selected exported | ☐ | |

### 4.4 Attachment Upload

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.4.1 | Upload file attachment | File uploads successfully | ☐ | |
| 4.4.2 | Download attachment | File downloads | ☐ | |
| 4.4.3 | Delete attachment | File removed | ☐ | |
| 4.4.4 | View attachment list | `/attachments` loads | ☐ | |

### 4.5 Import

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.5.1 | Visit `/import` | Import page loads | ☐ | |
| 4.5.2 | Upload CSV | Data imports correctly | ☐ | |

### 4.6 API Tokens

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.6.1 | Visit `/tokens` | Token management page | ☐ | |
| 4.6.2 | Create token | Token generated | ☐ | |
| 4.6.3 | Delete token | Token revoked | ☐ | |

### 4.7 SMTP Profiles

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.7.1 | Visit `/admin/smtp-profiles` | 200 OK | ☐ | |
| 4.7.2 | CRUD operations | All work | ☐ | |
| 4.7.3 | Test SMTP | Test email sent | ☐ | |

### 4.8 Webhooks

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.8.1 | Visit `/webhooks` | 200 OK | ☐ | |
| 4.8.2 | CRUD operations | All work | ☐ | |
| 4.8.3 | Test webhook | Test event fires | ☐ | |

### 4.9 Login Audits

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 4.9.1 | Visit `/login-audits` | 200 OK | ☐ | |
| 4.9.2 | Login records | Login attempts recorded | ☐ | |
| 4.9.3 | Detail view | Individual entry details | ☐ | |

---

## Phase 5: Scheduled Tasks Verification

### 5.1 Cron / Scheduler

These tests require waiting for the cron schedule to run, or running commands manually:

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 5.1.1 | Run `php artisan schedule:test` | Follow prompts, each command executes | ☐ | |
| 5.1.2 | Check cron is running | `crontab -l` shows entries | ☐ | |
| 5.1.3 | Run `php artisan expiry:check` | Expiry records checked | ☐ | |
| 5.1.4 | Run `php artisan monitor:check` | Monitors checked | ☐ | |
| 5.1.5 | Run `php artisan tasks:check-overdue` | Tasks flagged overdue | ☐ | |
| 5.1.6 | Run `php artisan renewals:send-email-reminders` | Reminder emails sent | ☐ | Tests queue + SMTP |
| 5.1.7 | Run `php artisan sanctum:prune-expired` | Expired tokens pruned | ☐ | |

### 5.2 Queue Worker

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 5.2.1 | Run `php artisan queue:work --stop-when-empty --tries=1` | Pending jobs processed | ☐ | |
| 5.2.2 | Check `failed_jobs` table | No unexpected failures | ☐ | |
| 5.2.3 | Check cron queue worker | Runs every minute via cron | ☐ | |

---

## Phase 6: Security Checks

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 6.1 | Visit `/.env` | 403 or 404 (NOT accessible) | ☐ | |
| 6.2 | Visit `/storage/logs/laravel.log` | 403 or 404 | ☐ | |
| 6.3 | Visit `/vendor/` | 403 or 404 | ☐ | |
| 6.4 | Try SQL injection on login | Blocked | ☐ | |
| 6.5 | Check HTTPS | All pages redirect to HTTPS | ☐ | |
| 6.6 | Check session cookie | `Secure` flag set, `HttpOnly` set | ☐ | |
| 6.7 | Debug mode disabled | `APP_DEBUG=false` confirmed | ☐ | |
| 6.8 | Check `X-Frame-Options` header | `DENY` or `SAMEORIGIN` | ☐ | |
| 6.9 | Check `X-Content-Type-Options` | `nosniff` | ☐ | |

---

## Phase 7: Performance Checks

| # | Test | Expected | Result | Notes |
|---|---|---|---|---|
| 7.1 | First page load time | < 3 seconds | ☐ | |
| 7.2 | Subsequent page load time | < 1 second | ☐ | Cached pages |
| 7.3 | Lighthouse score | ≥ 60 performance | ☐ | |
| 7.4 | No slow queries | Check MySQL slow query log | ☐ | |

---

## Signoff

| Role | Name | Date | Result |
|---|---|---|---|
| Tester | | | ☐ ALL TESTS PASSED |
| | | | ☐ TESTS FAILED (attach BROKEN_PAGES_REPORT.md) |

**Total tests:** ~200
**PASS count:** ___
**FAIL count:** ___
**SKIP count:** ___

**Deployment verdict:** ☐ GO / ☐ NO-GO (if any CRITICAL test fails)

---

## If Tests Fail

1. Check `/storage/logs/laravel.log` for error details
2. Run `php artisan optimize:clear` then re-cache
3. Check PHP error log in cPanel
4. Verify `.env` values are correct
5. Check storage permissions
6. Re-run migrations if schema issue
7. File a bug report with exact error message and URL
