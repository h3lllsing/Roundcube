# Activity Logging Consistency Audit

**Date:** 2026-06-27
**Scope:** All 20 modules across Web & API controllers
**Method:** Source code review — every controller, model, service, and blade file
**Tool:** Spatie `laravel-activitylog` v4 + custom `LoginAudit` model

---

## Logging Infrastructure

### Two independent logging systems:

| System | Table | Purpose | Mechanism |
|--------|-------|---------|-----------|
| **Spatie Activitylog** | `activity_log` | General CRUD + reveal + custom events | `LogsActivity` trait (auto) + `activity()` helper (manual) |
| **LoginAudit** | `login_audits` | Authentication events only (login/logout/fail/lock) | `LoginAudit::create()` in AuthControllers |

### Models using `LogsActivity` trait (auto-log create/update/delete):

`Asset`, `Domain`, `DomainEmail`, `ExpiryTracker`, `Feature`, `Hosting`, `Module`, `ModuleRolePermission`, `Note`, `OtherService`, `ServiceProvider`, `SmtpProfile`, `Task`, `VaultEntry`, `Voip`, `Vps`, `Attachment`

### Models WITHOUT `LogsActivity` (no auto-logging):

`User`, `Webhook`, `Role` (vendor), `Privilege` (vendor), `LoginAudit`, `PersonalAccessToken`

### Sensitive field protection in all 17 LogsActivity models:

| Model | Excluded fields |
|-------|----------------|
| `Hosting`, `Vps`, `OtherService`, `ServiceProvider` | `password` |
| `Voip` | `password`, `extension_password` |
| `DomainEmail` | `password` |
| `VaultEntry` | `encrypted_password` |
| `SmtpProfile` | `smtp_password` (via `logExcept`) |

---

## Module-by-Module Audit

### 1. Users

| Action | Logged? | Where | Event | Actor | Subject | Old/New | Risk |
|--------|---------|-------|-------|-------|---------|---------|------|
| Create | YES | `UserController::store()`:242 | `created` | Auth user | User | Roles, clone_source | — |
| Update | **NO** | `UserController::update()`:304 | — | — | — | — | **HIGH** |
| Delete | **NO** | `UserController::destroy()`:520 | — | — | — | — | **HIGH** |
| Suspend | YES | `UserController::suspend()`:408 | `suspended` | Auth user | User | None | — |
| Unsuspend | YES | `UserController::unsuspend()`:423 | `unsuspended` | Auth user | User | None | — |
| Clone | YES | `UserController::cloneStore()`:500 | `cloned` | Auth user | Source user | Target details, flags | — |
| Permission override update | **NO** | `UserController::updatePermissions()`:386 | — | — | — | — | **MEDIUM** |
| Register | **NO** | `AuthController::register()`:81 | — | — | — | — | **LOW** |
| Profile update | **NO** | `AuthController::updateProfile()`:162 | — | — | — | — | **LOW** |

**User model lacks `LogsActivity` trait** — update and delete must be explicitly logged.

---

### 2. Roles

| Action | Logged? | Where | Event | Actor | Subject | Old/New | Risk |
|--------|---------|-------|-------|-------|---------|---------|------|
| Create | **NO** | `RoleController::store()`:35 | — | — | — | — | **HIGH** |
| Update | **NO** | `RoleController::update()`:62 | — | — | — | — | **HIGH** |
| Delete | **NO** | `RoleController::destroy()`:76 | — | — | — | — | **HIGH** |
| Attach privilege | **NO** | `RoleController::attachPrivilege()`:90 | — | — | — | — | **HIGH** |
| Detach privilege | **NO** | `RoleController::detachPrivilege()`:106 | — | — | — | — | **HIGH** |
| Template apply | YES | `RoleTemplateController::executeApply()`:157 | `template_applied` | Auth user | Role | Changed/added counts | — |

**All CRUD missing** — Role model is from vendor package (`hasinhayder/tyro`) and lacks `LogsActivity`. Granting/revoking privileges is a critical security action with zero audit trail.

---

### 3. Permissions / Overrides

| Action | Logged? | Where | Event | Actor | Subject | Old/New | Risk |
|--------|---------|-------|-------|-------|---------|---------|------|
| Module permission create/update | **NO** | `ModulePermissionController::update()`:27 | — | — | — | — | **HIGH** |
| Module permission delete | **NO** | `ModulePermissionController::destroy()`:57 | — | — | — | — | **HIGH** |
| User permission override update | **NO** | `UserController::updatePermissions()`:386 | — | — | — | — | **MEDIUM** |
| ModuleRolePermission model | YES (auto) | `ModuleRolePermission` trait | `created/updated/deleted` | Auth user | MRP record | Dirty fields | — |

**NOTE:** The `ModuleRolePermission` model *does* auto-log its own CRUD via the trait, but the old/new permissions values are captured at the model level (changes to `can_create`, `can_read`, etc.). However, `ModulePermissionService::setForRole()` uses `updateOrCreate()` which fires model events. The `destroy()` action in the controller deletes a `ModuleRolePermission` record which does auto-log, but the *removal of all permissions for a role* is logged as a delete event without context about which module/role was affected.

**User permission override changes** (`UserController::updatePermissions()`) have NO logging whatsoever.

---

### 4. Domains

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | `Domain` model has `LogsActivity` |
| Update | YES (auto) | `updated` | Dirty fields captured |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | N/A | — | No password field on Domain |

**STATUS: Fully covered.**

---

### 5. Hosting

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Password changes excluded from log |
| Update | YES (auto) | `updated` | Password changes ignored by trait |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | YES (manual) | `revealed` | `HostingController::getPassword()`:141 with type `hosting_password` |

**STATUS: Fully covered.**

---

### 6. VPS

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Password changes excluded |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | YES (manual) | `revealed` | `VpsController::getPassword()`:152 with type `vps_password` |

**STATUS: Fully covered.**

---

### 7. Domain Emails

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Password changes excluded |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | YES (manual) | `revealed` | `DomainEmailController::getPassword()`:149 with type `domain_email_password` |

**STATUS: Fully covered.**

---

### 8. VoIP

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Password/extension_password excluded |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal (password) | YES (manual) | `revealed` | `VoipController::getPassword()`:151 with type `voip_password` |
| Reveal (extension) | YES (manual) | `revealed` | `VoipController::getExtensionPassword()`:168 with type `voip_extension_password` |

**STATUS: Fully covered.**

---

### 9. Other Services

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Password excluded |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | YES (manual) | `revealed` | `OtherServiceController::getPassword()`:145 with type `other_service_password` |

**STATUS: Fully covered.**

---

### 10. Expiry Trackers

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Logs all fillable |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Expiry warning | YES (listener) | `expiry_warning` | `LogExpiryWarning` listener, via `ExpiryWarningTriggered` event |

**STATUS: Fully covered.**

---

### 11. SMTP Profiles

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | `smtp_password` never logged |
| Update | YES (auto) + YES (manual) | `updated` | Auto + manual for set-default/toggle-active |
| Delete | YES (auto) | `deleted` | — |
| Duplicate | YES (manual) | `duplicated` | `SmtpProfileController::duplicate()`:106 |
| Test | YES (manual) | `tested` | Both success and failure logged |
| Set default | YES (manual) | `updated` | `SmtpProfileController::setDefault()`:160 |
| Toggle active | YES (manual) | `updated` | `SmtpProfileController::toggleActive()`:180 |

**STATUS: Fully covered — best-logged module in the system.**

---

### 12. Assets

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Via `AssetService::create()` → `Asset::create()` |
| Update | YES (auto) | `updated` | Via `AssetService::update()` → `$asset->update()` |
| Delete | YES (auto) | `deleted` | Via `AssetService::delete()` → `$asset->delete()` |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Assign | **NO** | — | `AssetService::assign()`:95 — no logging |
| Return | **NO** | — | `AssetService::returnAsset()`:121 — no logging |

**Asset model logs CRUD correctly, but assignment/return operations (state changes from `available` ↔ `assigned`) are completely invisible in the audit trail.**

---

### 13. Tasks

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Custom description: `"Task {title} created"` |
| Update | YES (auto) | `updated` | Custom description |
| Update status | YES (auto) | `updated` | `TaskController::updateStatus()` fires `$task->update()` |
| Delete | YES (auto) | `deleted` | Custom description |

**STATUS: Fully covered.**

---

### 14. Vault

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | `encrypted_password` excluded |
| Update | YES (auto) | `updated` | Password changes ignored |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |
| Reveal | YES (manual) | `revealed` | `VaultService::reveal()`:97 + `VaultPasswordRevealed` event + webhook |

**STATUS: Fully covered — reveal is the most heavily tracked action in the system (activity log + notification + webhook + dashboard widget).**

---

### 15. Notes

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Custom description: `"Note #id created"` |
| Update | YES (auto) | `updated` | — |
| Delete | YES (auto) | `deleted` | — |
| Restore | YES (auto) | `restored` | — |
| Force delete | YES (auto) | `forceDeleted` | — |

**STATUS: Fully covered.**

---

### 16. Webhooks

| Action | Logged? | Where | Event | Actor | Subject | Old/New | Risk |
|--------|---------|-------|-------|-------|---------|---------|------|
| Create | **NO** | `WebhookController::store()`:50 | — | — | — | — | **HIGH** |
| Update | **NO** | `WebhookController::update()`:78 | — | — | — | — | **HIGH** |
| Delete | **NO** | `WebhookController::destroy()`:109 | — | — | — | — | **HIGH** |
| Test fire | **NO** | `WebhookController::test()`:93 | — | — | — | — | **LOW** |

**Webhook model lacks `LogsActivity` trait.** Webhook URLs and events can be changed with zero audit trail — this is a security concern since webhooks can exfiltrate data and fire on sensitive events.

---

### 17. Attachments

| Action | Logged? | Event | Notes |
|--------|---------|-------|-------|
| Create | YES (auto) | `created` | Via `AttachmentService::create()` |
| Delete | YES (auto) | `deleted` | Auto-logged via model trait |

**STATUS: Fully covered.**

---

### 18. Reports / Exports

| Action | Logged? | Where | Notes | Risk |
|--------|---------|-------|-------|------|
| CSV export (any type) | **NO** | `ExportController::export()` | 18 entity types exportable, zero logged | **MEDIUM** |
| Report export | **NO** | `ReportController::export()` | — | **LOW** |
| Report view | **NO** | `ReportController::show()` | Read-only, debatable | **LOW** |

**Export actions have no audit trail.** A user with `can_export` permission can download any entity's data without leaving a record of when, what, or who exported. This affects: domains, hostings, VPS, VoIP, SMTP profiles, service providers, domain emails, other services, expiry trackers, assets, tasks, vault entries, notes, features, modules, webhooks, activity logs, login audits, attachments, users, roles, privileges, tokens.

---

### 19. Login Audits

| Action | Logged? | Where | Event | Notes |
|--------|---------|-------|-------|-------|
| Login success | YES | `AuthController::login()`:52 | `login_success` | Stored in `login_audits` table |
| Login failed | YES | `AuthController::login()`:35 | `login_failed` | — |
| Login suspended | YES | `AuthController::login()`:44 | `login_suspended` | — |
| Login locked | YES | `Api\AuthController` (rate limit) | `login_locked` | API only |
| Logout | YES | `AuthController::logout()`:68 | `logout` | — |
| Password reset | **NO** | `AuthController::resetPassword()`:115 | — | Password resets not audited |

**Login audit is well-implemented but not integrated into the activity log table.** Password reset events (initiated from forgot-password flow) have no audit trail in either system.

---

### 20. Activity Logs (self-logging)

| Action | Logged? | Notes |
|--------|---------|-------|
| View activity log | N/A | Read-only |
| Delete activity record | **NO** | No delete route exists for activity logs |
| Export activity log CSV | **NO** | Export exists but not self-logged |

---

## Missing Logs Summary

### HIGH Severity (6)

| # | Module | Missing Action | File | Why HIGH |
|---|--------|---------------|------|----------|
| 1 | **Users** | Update | `UserController::update()`:304 | Profile changes, role reassignments invisible |
| 2 | **Users** | Delete | `UserController::destroy()`:520 | User deletion leaves no trace |
| 3 | **Roles** | All CRUD | `RoleController` fully | Privilege grants/revocations invisible |
| 4 | **Permissions** | Module permission changes | `ModulePermissionController::update()`:27, `destroy()`:57 | Permission grants/revocations invisible |
| 5 | **Webhooks** | All CRUD | `WebhookController` fully | Webhook URL/event changes invisible (data exfil risk) |
| 6 | **Privileges** | All CRUD | `PrivilegeController` fully | Privilege definition changes invisible |

### MEDIUM Severity (4)

| # | Module | Missing Action | File | Why MEDIUM |
|---|--------|---------------|------|------------|
| 7 | **Users** | Permission override updates | `UserController::updatePermissions()`:386 | Override changes affect access control |
| 8 | **Assets** | Assign/Return | `AssetService::assign()`:95, `AssetService::returnAsset()`:121 | State changes to physical assets invisible |
| 9 | **Exports** | All CSV exports | `ExportController::export()` | Data exfiltration via export leaves no trail |
| 10 | **Tokens** | Create/Delete | `TokenController::store()`:31, `destroy()`:44 | API token creation/revocation invisible |

### LOW Severity (5)

| # | Module | Missing Action | File | Why LOW |
|---|--------|---------------|------|---------|
| 11 | **Login** | Password reset | `AuthController::resetPassword()`:115 | Password changes via reset flow |
| 12 | **Auth** | User registration | `AuthController::register()`:81 | Self-registration creates user silently |
| 13 | **Auth** | Profile update | `AuthController::updateProfile()`:162 | User changes own name/email/password |
| 14 | **Bulk Actions** | All operations | `BulkActionService::runAction()`:146 | Mass operations bypass model events entirely |
| 15 | **Imports** | All imports | `ImportController::store()`:66 | CSV imports create records silently |
| 16 | **LoginAudit** | Delete audit records | `LoginAuditController::destroy()`:50 | Auditors can delete their own tracks |
| 17 | **Webhooks** | Test fire | `WebhookController::test()`:93 | Test fire of webhook payload |

---

## Bulk Action Gap (Systemic)

`BulkActionService` uses mass operations:
```php
$modelClass::whereIn('id', $ids)->delete();        // No model events
$modelClass::withTrashed()->whereIn('id', $ids)->restore();  // No model events
$modelClass::withTrashed()->whereIn('id', $ids)->forceDelete(); // No model events
$modelClass::whereIn('id', $ids)->update([...]);    // No model events
```

**Mass operations bypass Eloquent model events entirely.** Even for models with `LogsActivity`, bulk actions produce zero activity log entries. This affects all 19 bulk-action-enabled entity types.

---

## Duplicate / Redundant Logging

In some cases, the same action generates multiple log entries:

| Scenario | Entries Created | Reason |
|----------|----------------|--------|
| `SmtpProfile::setDefault()` | 2 log entries | 1 auto (model trait) + 1 manual (`updated` event) |
| `SmtpProfile::toggleActive()` | 2 log entries | Same — auto + manual |
| `ModulePermissionController::update()` | 1 log entry | Auto via `ModuleRolePermission` model trait (but `updateOrCreate` may create or update — different events) |
| `UserController::store()` | 1 log entry | Manual only (User model has no trait) |

The SMTP profile double-logging is intentional for `setDefault`/`toggleActive` because the manual log provides richer context (e.g., which profile was made default).

---

## Security Compliance

| Rule | Status |
|------|--------|
| Passwords never logged | ✅ All 5 password-type fields excluded via `dontLogIfAttributesChangedOnly` or `logExcept` |
| SMTP passwords never logged | ✅ `smtp_password` in `logExcept` for `SmtpProfile` |
| Vault secrets never logged | ✅ `encrypted_password` in `dontLogIfAttributesChangedOnly` for `VaultEntry` |
| API tokens never logged | ✅ No token logging exists at all (could be improved) |
| Reveal logs who and when | ✅ All 7 reveal endpoints log actor + timestamp + entity type |

---

## Recommendations by Priority

### P0 — Critical (fix immediately)
1. **Add logging to `UserController::update()`** — log old vs new name/email, role changes, suspended_at changes
2. **Add logging to `UserController::destroy()`** — log which user was deleted, by whom
3. **Add logging to `RoleController`** — full CRUD + attach/detach privilege, log which role/privilege changed
4. **Add logging to `WebhookController`** — full CRUD, log URL and events before/after changes
5. **Add logging to `PrivilegeController`** — full CRUD (if possible; vendor model)
6. **Add logging to `ModulePermissionController`** — log which module+role had permissions changed, old vs new values

### P1 — High (fix in next patch)
7. **Add logging to `UserController::updatePermissions()`** — log which overrides were changed
8. **Add logging to `AssetService::assign()` and `returnAsset()`** — log asset tag, assigned to whom, date
9. **Add export logging to `ExportController`** — log who exported which entity type, record count
10. **Add logging to `TokenController`** — log API token creation/revocation

### P2 — Medium (fix when convenient)
11. **Add password reset logging** — log who reset password (user email), IP, timestamp
12. **Add registration logging** — log new user registration
13. **Add profile update logging** — log own profile changes
14. **Add bulk action logging** — add summary log entry per bulk operation
15. **Add import logging** — log import source, type, record count
16. **Add login audit deletion logging** — or remove the delete capability entirely

### P3 — Low (consider for future)
17. **Add webhook test-fire logging** — minimal risk but useful for debugging
18. **Integrate LoginAudit into ActivityLog** — so all audit events appear in one timeline
19. **Self-log export of activity log** — meta-auditing
