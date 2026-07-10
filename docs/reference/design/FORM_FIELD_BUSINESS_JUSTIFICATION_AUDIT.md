# Phase 10.4 — Form Field Business Justification Audit

**Date:** 2026-06-27
**Scope:** Every Create/Edit form field across all 18 modules
**Method:** Source code audit (Controllers, Models, Requests, Views, Services, Reports, Exports, Imports, Dashboard, Notifications, Global Search)
**Constraint:** No code changes, no DB changes, no field removal

---

## How to Read This Report

Each module has a table with every form field evaluated against 13 questions. The key columns:

| Column | Meaning |
|---|---|
| **Purpose** | Why this field exists — traced from actual code usage |
| **Used By** | Which subsystems consume the field |
| **If Removed** | What breaks (traced to actual code paths) |
| **Recommended Action** | Keep / Keep but rename / Hide from UI and auto-fill / Remove from UI but keep DB / Needs business decision |
| **Priority** | P0 (go-live blocker) / P1 (before live if quick) / P2 (v1.1 cleanup) / No action |

---

## 1. USERS

### Form: User Create (`users.create`)

| Field | Current Label | DB Column | Purpose | Used By | Dependency Chain | If Removed | Recommended Action | Risk | Priority |
|---|---|---|---|---|---|---|---|---|---|
| Name | Name | `name` | User display name | Auth, search, activity logs, reports, dashboard, notifications, all `BelongsTo User` relations | Required for login display, notifications, ownership display everywhere | Display name breaks everywhere | Keep | NONE | No action |
| Email | Email | `email` | Login credential, notification delivery | Auth (login), password reset, notifications, uniqueness constraint | Required for authentication, email notifications, password reset | Login breaks, notifications fail | Keep | NONE | No action |
| Password | Password | `password` | Authentication secret | Auth (login) | Hashed, not storable or usable elsewhere | Login breaks | Keep — label is clear | NONE | No action |
| Confirm Password | Confirm Password | — | Password confirmation validation | Controller validation (`confirmed` rule) | Prevents typo during user creation | User would need to retype on typo | Keep | NONE | No action |
| Roles | Roles | `roles` (pivot) | RBAC authorization | All permission checks via `hasRole()`, `canOnModule()`, middleware, sidebar, navigation, bulk actions | Required for RBAC — every permission check depends on role assignments | No authorization works | Keep | NONE | No action |
| Module Overrides | — | `user_module_permissions` (separate table) | Per-user permission overrides | `canOnModule()` override logic, UserModulePermission model | Explicit override of role-level permissions for specific modules | Role-based permissions would apply without overrides | Keep but consider collapsible "Advanced" section | LOW | P2 |
| Clone From | Clone From | — | Clone existing user's roles/permissions | Store method `clone_user_id` logic | Quick user setup from template | Users must be configured manually | Hide from main form; show only when "Clone" feature is used via dedicated flow | LOW | P2 |

### Form: User Edit (`users.edit`)

Same as Create, plus:

| DB Column | Purpose | Dependency Chain |
|---|---|---|
| `suspended_at` | Account suspension status | Auth middleware `suspended` checks, user index filtering | Keep — required for access control |

---

## 2. ROLES

### Form: Role Create/Edit (`roles.create`, `roles.edit`)

| Field | Current Label | DB Column | Purpose | Used By | If Removed | Recommended Action | Risk | Priority |
|---|---|---|---|---|---|---|---|---|
| Name | Name | `name` | Human-readable role name | Role listing, role selection in user form, RBAC UI | Cannot identify or select roles | Keep — label is clear | NONE | No action |
| Slug | Slug | `slug` | Programmatic role identifier | `hasRole('super-admin')`, `hasRole('admin')`, permission checks, protected slug checks (`admin`, `super-admin`), RBAC middleware `role:super-admin` | All role checks break | Keep — label is clear for IT users | HIGH | No action |

---

## 3. MODULE PERMISSIONS / OVERRIDES

### Form: Module Permission Update

| Field | Purpose | Dependency Chain | Recommended Action |
|---|---|---|---|
| Module | Which module to set permissions for | RBAC `canOnModule()` for all modules | Keep — required for RBAC |
| Role | Which role to assign permissions to | Permission checks | Keep — required for RBAC |
| can_create / can_read / can_update / can_delete / can_approve / can_export / can_reveal | Individual permission flags | All `canOnModule()` calls, `RbacScope::apply()`, button visibility in views | Keep — all are actively consumed |

---

## 4. SERVICE PROVIDERS

### Create/Edit Form Fields

| # | Field | Label | DB Column | Purpose | Used By | Dependency Chain | If Removed | Recommended Action | Risk | Priority |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Name | Name | `name` | Provider identification | GlobalSearch, ExpiryNotification, index lists, Dashboard widgets, reports, exports | Search uses only `name`; dashboard aggregates by provider name | Provider becomes unidentifiable | Keep | NONE | No action |
| 2 | Type | Type | `type` | Category (internet/hosting/email/telecom/other) | Index filtering, reporting | Only used as a categorization label | Reports and filters lose granularity | Keep | LOW | No action |
| 3 | Provider | Provider | `provider` | Third-party company name | Index display | Display only | Index loses one info column | Keep but label unclear — rename to "Vendor Name" | LOW | P2 |
| 4 | Email | Email | `email` | Provider support contact | Index display, export | Display only | Lose contact info | Keep but rename to "Support Email" | LOW | P2 |
| 5 | Website | Website | `website` | Provider portal URL | Show page (clickable link), index (fallback display), export | Imported into Export CSV; shown as link on detail page. **Factory default is `fake()->url()`** which is correct. No `admin@tyro.project` default found anywhere. | No direct link to provider portal | Keep but rename to "Portal URL" | LOW | P2 |
| 6 | Password | Password | `password` | Provider portal login | GetPassword endpoint (audited reveal) | **Encrypted** via `'password' => 'encrypted'` cast; reveal logged via activity | Cannot access provider portal credentials | Keep but rename to "Portal Password" and ensure helper text exists | LOW | P2 |
| 7 | Cost | Cost | `cost` | Provider subscription/service cost | Dashboard (OperationsWidget `monthly_cost` sum), Export, ExpiryNotification (not directly — only `expiry_date` matters for expiry) | Dashboard monthly cost totals would be incomplete; exports lose cost column | Dashboard cost aggregates lose data; but **cost label is ambiguous — monthly? annual? total?** | Keep but rename to "Monthly Cost" — dashboard treats it as monthly aggregate | MEDIUM | P1 |
| 8 | Start Date | Start Date | `start_date` | Service start/contract date | Show page display, export | Display only, no automated system depends on it | Minor reporting loss | Keep | LOW | No action |
| 9 | Expiry Date | Expiry Date | `expiry_date` | Service expiry/renewal date | **ExpiryNotificationService** queries all active providers with `expiry_date NOT NULL` for reminders; **Dashboard** counts expiring within 30 days; **OperationsWidget** aggregates `services_expiring_30d`; Upcoming expiries list; Reports | Expiry reminders for Service Providers stop working; Dashboard expiry widgets show wrong counts | **HIGH** — feeds expiry notification engine | Keep (critical for notifications) | HIGH | No action |
| 10 | Status | Status | `status` | Operational state | Index filtering, Dashboard aggregates (active count, expired count), bulk actions | Required for dashboard stats and filtering | Dashboard active provider count is wrong | Keep | NONE | No action |
| 11 | Module | Module | `module_id` | RBAC scoping + module categorization | **RBAC**: `RbacScope::apply(ServiceProvider::class)` uses `module_id` for `canOnModule` checks. **Permission check**: Controller checks `$provider->module && $user->canOnModule($provider->module, 'update')`. **GlobalSearch**: ownership filter `user_or_module` scopes by `module_id`. **Dashboard**: OperationsWidget filters by `module_id` for non-super-admins. **Service layer**: `ServiceProviderService->list()` filters by `module_id`. **API**: filterable by `module_id`. | RBAC scoping for non-super-admins would need fallback; GlobalSearch ownership breaks for admin users; Dashboard cannot filter by module | **Do NOT remove.** `module_id` is actively used by RBAC (`canOnModule`), GlobalSearch ownership scoping, and Dashboard filtering. However, it could be hidden from the create/edit form and auto-filled to the `service-providers` module, since a Service Provider logically belongs to the "Service Providers" module. | MEDIUM | P1 |

---

## 5. DOMAINS

### Create/Edit Form Fields

| # | Field | Label | Purpose | Used By | Dependency Chain | If Removed | Recommended Action | Risk | Priority |
|---|---|---|---|---|---|---|---|---|---|
| 1 | Name | Name | Domain name string | GlobalSearch (title + subtitle), reports, exports, notifications | Everything | Domain unidentifiable | Keep | NONE | No action |
| 2 | Service Provider | Service Provider | Foreign key to provider | Show page relation display, export | Export CSV column | Lose provider linkage | Keep | NONE | No action |
| 3 | Registration Date | Reg. Date | Domain registration date | Reports, export | Display & reporting only | Minor reporting loss | Keep — useful for domain lifecycle tracking | LOW | No action |
| 4 | Expiry Date | Expiry Date | Domain expiry date | **ExpiryNotificationService**, Dashboard (upcoming expiries, monthly cost aggregate), OperationsWidget, reports | **Domain expiry reminders** | Domain expiry notifications stop; Dashboard expiry counts wrong | **Do NOT remove** — feeds critical notification system | HIGH | No action |
| 5 | Auto Renew | Auto Renew | Whether domain auto-renews | Show page, export | Display only | Minor reporting loss | Keep | LOW | No action |
| 6 | Cost | Cost | Domain registration/renewal cost | Dashboard (OperationsWidget `monthly_cost` aggregation), reports, exports | Dashboard cost totals | Dashboard cost aggregates lose domain data | Keep but rename to "Annual Cost" — domains are typically annual renewals | MEDIUM | P1 |
| 7 | Status | Status | Operational state | Index filtering, Dashboard aggregates, reports, GlobalSearch badge | Index filter, status-based counts | Index filtering breaks | Keep | NONE | No action |
| 8 | Cloudflare Status | Cloudflare | Cloudflare proxy status | Show page, index, export | Display only | Minor display loss | Keep — useful for DNS management | LOW | No action |
| 9 | DNS Servers | DNS Servers | DNS server list | Show page, export | Display only, stored as JSON array | Minor display loss | Keep — useful for DNS troubleshooting | LOW | No action |
| 10 | Hosting | Hosting | FK to hosting where domain points | Show page relation, cross-module navigation | Display only | One less cross-reference | Keep — useful for service mapping | LOW | No action |
| 11 | Module | Module | RBAC scoping | RBAC (`canOnModule`), GlobalSearch ownership filter, Dashboard module filtering, API filtering, reports | Same analysis as Service Provider module_id | RBAC scoping breaks for non-super-admins | **Do NOT remove.** But should be auto-filled to "Domains" module and hidden from form. Domain logically belongs to Domains module only. | MEDIUM | P1 |
| 12 | User | User | Ownership/creator | Controller overrides to `Auth::id()` on store; NOT in request rules; displayed but functionally dead | Field is rendered in form but **controller always overwrites** `user_id = Auth::id()` on store; NOT changeable on edit (not in request rules) | Nothing breaks — field is already dead | **Remove from UI.** No user should be selecting this; it's auto-set to the creator. | NONE | P1 |
| 13 | Notes | Notes | Free-text notes | Display on show page, GlobalSearch subtitle, export | Display only | Minor info loss | Keep | LOW | No action |

---

## 6. HOSTING

### Create/Edit Form Fields

| # | Field | Label | Purpose | Used By | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|---|---|
| 1 | Name | Name | Hosting account identifier | GlobalSearch, index, reports | Everything | Keep | No action |
| 2 | Username | Username | cPanel/SSH login | Show page, GlobalSearch (subtitle), export | Credential access | Keep but label is clear | No action |
| 3 | Password | Password | cPanel/SSH/portal password | GetPassword endpoint, reveal audit | **Encrypted cast**, activity log excludes changes | Keep but rename to "Portal Password" | P2 |
| 4 | cPanel URL | cPanel URL | Hosting control panel URL | Show page (link), export | Portal access | Keep — rename to "Control Panel URL" | P2 |
| 5 | Service Provider | Service Provider | FK to provider | Show page relation, export | Cross-reference | Keep | No action |
| 6 | Plan | Plan | Hosting plan name | Show page, export | Reporting | Keep | No action |
| 7 | Domain | Domain | Primary domain on hosting | Show page, GlobalSearch (subtitle), export | Useful for identification | Keep | No action |
| 8 | Domain IP | Domain IP | IP address of domain | Show page, export | Technical reference | Keep — useful for IT teams | No action |
| 9 | Mail Domain IP | Mail Domain IP | IP of mail server | Show page, export | Technical reference | Keep — useful for IT teams | No action |
| 10 | cPanel IP | cPanel IP | IP of cPanel | Show page, export | Technical reference | Keep — useful for IT teams | No action |
| 11 | Cost | Cost | Hosting cost | Dashboard aggregate, export | Dashboard cost totals | Keep but ambiguous — rename to "Monthly/Annual Cost" | P1 |
| 12 | Start Date | Start Date | Service start | Show page, export | Display only | Keep | No action |
| 13 | Expiry Date | Expiry Date | Renewal date | **ExpiryNotificationService**, Dashboard | **Expiry reminders** | **Do NOT remove** | No action |
| 14 | Status | Status | Operational state | Index filter, Dashboard, GlobalSearch badge | Status-based filtering | Keep | No action |
| 15 | Module | Module | RBAC scoping | RBAC, GlobalSearch, Dashboard, reports | Same as Domain/ServiceProvider | **Do NOT remove** but auto-fill to "Hosting" module | P1 |
| 16 | User | User | Ownership | Controller overwrites to `Auth::id()`. Same dead-field pattern as Domain | Nothing — functionally dead | **Remove from UI** | P1 |
| 17 | Notes | Notes | Free-text notes | Display, GlobalSearch | Minor | Keep | No action |

---

## 7. VPS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Name | Name | Everything | Keep | No action |
| 2 | Service Provider | Service Provider | Show page, export, cross-ref | Keep | No action |
| 3 | Plan | Plan | Display, export | Keep | No action |
| 4 | IP Address | IP Address | Show page, GlobalSearch (subtitle), export, technical reference | Keep — label clear for IT users | No action |
| 5 | Password | Password | GetPassword endpoint, encrypted cast | Keep but rename to "Root Password" | P2 |
| 6 | OS | OS | GlobalSearch subtitle, reports, export | Keep | No action |
| 7 | RAM (MB) | RAM (MB) | Reports, export | Keep — technical spec | No action |
| 8 | Disk (GB) | Disk (GB) | Reports, export | Keep — technical spec | No action |
| 9 | CPU Cores | CPU Cores | Reports, export | Keep — technical spec | No action |
| 10 | Department | Department | Show page, GlobalSearch, export | Keep — organizational categorization | No action |
| 11 | Location | Location | Show page, GlobalSearch, export, filter | Keep — datacenter location | No action |
| 12 | Login IDs | Login IDs (JSON) | Show page, stored as JSON array, export | Keep — useful for credential management. Label should clarify format | P2 |
| 13 | Additional IPs | Additional IPs (JSON) | Show page, stored as JSON array, export | Keep — useful for networking | P2 |
| 14 | Cost | Cost | Dashboard aggregate, export | Keep but rename | P1 |
| 15 | Start Date | Start Date | Display, export | Keep | No action |
| 16 | Expiry Date | Expiry Date | **ExpiryNotificationService**, Dashboard | **Do NOT remove** | No action |
| 17 | Module | Module | RBAC, GlobalSearch, Dashboard | **Do NOT remove** but auto-fill to "VPS" module | P1 |
| 18 | User | User | Dead field — overwritten by controller | **Remove from UI** | P1 |
| 19 | Status | Status | Index filter, Dashboard, GlobalSearch badge | Keep | No action |
| 20 | Notes | Notes | Display, GlobalSearch | Keep | No action |

---

## 8. DOMAIN EMAILS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Email Address | Email Address | Identification, GlobalSearch | Keep | No action |
| 2 | Password | Password | GetPassword endpoint, encrypted cast | Keep — label "Email Password" would be better | P2 |
| 3 | Service Provider | Service Provider | Show page, cross-ref | Keep | No action |
| 4 | Domain | Domain | FK to domains, cross-ref | Keep | No action |
| 5 | Notes | Notes | Display | Keep | No action |

**Missing from form but in request/model:** `storage_mb`, `cost`, `expiry_date`, `module_id`, `user_id`

- `cost` and `expiry_date`: These exist in the model and request but have **no form fields**. They could be exposed if email expiry/cost tracking is needed, but currently are DB-only dead fields.
- `storage_mb`: Same — DB column, no form field.
- `module_id`: Accepted in request but no form field. Auto-fill to "Domain Emails" module is appropriate.
- `user_id`: Auto-filled by controller to `Auth::id()`. Not in form, not in request rules. Correct.

---

## 9. VOIP

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Extension | Extension | Transformed by controller to `extensions` array | Keep — clear for IT teams | No action |
| 2 | Users-Name | Users-Name | Main identifier | Keep but label is confusing — rename to "Name" or "Display Name" | P1 |
| 3 | Vendor (Service Provider) | Vendor | FK to provider | Keep | No action |
| 4 | Phone | Phone | Phone number display, export | Keep | No action |
| 5 | Server IP | Server IP | Technical reference | Keep — clear for IT teams | No action |
| 6 | Inbound/Out | Inbound/Out | Direction setting | Keep — label is clear | No action |
| 7 | Number Status | Number Status | Status of phone number | Keep — clear for IT teams | No action |
| 8 | Code for Outbound | Code for Outbound | Outbound dialing code | Keep — clear for IT teams | No action |
| 9 | Cost | Cost | Dashboard aggregate, export | Keep but rename to "Monthly Cost" | P1 |
| 10 | Password (extension_password) | Password | Extension password, encrypted cast | **Label mismatch**: field is `extension_password` but labeled "Password". Rename to "Extension Password". | P1 |
| 11 | Team Details | Team Details | Free-text team info | Display only | Keep | No action |

**Missing from form but in request/model:** `start_date`, `expiry_date`, `password` (separate from `extension_password`), `type`, `username`, `dashboard_url`, `module_id`, `user_id`

- `expiry_date`: Missing from VoIP form! **This means VoIP expiry notifications never fire** because `expiry_date` is always null. This is likely a bug — VoIP should have an expiry date field.
- `password`: Separate column from `extension_password`. Both are encrypted. Only `extension_password` is in the form. Other password accessible via getPassword endpoint.
- `module_id`: Not in form but in request — auto-fill to "VoIP" module.
- `user_id`: Not in form, auto-filled by controller. Correct.

---

## 10. OTHER SERVICES

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Name | Name | Everything | Keep | No action |
| 2 | Type | Type | Enum (saas/api/monitoring/analytics/cdn/ssl/other) | Keep — clear for IT teams | No action |
| 3 | Service Provider | Service Provider | FK to provider | Keep | No action |
| 4 | Username | Username | Login credential | Keep | No action |
| 5 | Password | Password | Encrypted cast | Keep but label vague — rename to "Account Password" or "Service Password" | P2 |
| 6 | Login URL | Login URL | Service login portal | Keep — clear for IT teams | No action |
| 7 | Website | Website | Service website | Only on show page | Keep but clarify it's the "Vendor Website" vs. "Service URL" | P2 |
| 8 | Cost | Cost | Dashboard aggregate, export | Keep but rename | P1 |
| 9 | Start Date | Start Date | Display | Keep | No action |
| 10 | Expiry Date | Expiry Date | ExpiryNotificationService, Dashboard | **Do NOT remove** | No action |
| 11 | Module | Module | RBAC, GlobalSearch, Dashboard | **Do NOT remove** but auto-fill to "Other Services" module | P1 |
| 12 | User | User | **BUG: overwritten on store, honored on update** | **Remove from UI** — functionally dead on create, inconsistent on update | P1 |
| 13 | Status | Status | Index filter, Dashboard, GlobalSearch badge | Keep | No action |
| 14 | Notes | Notes | Display | Keep | No action |

---

## 11. EXPIRY TRACKERS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Name | Name | Everything | Keep | No action |
| 2 | Service Provider | Service Provider | FK to provider | Keep | No action |
| 3 | Username | Username | Login credential | Keep | No action |
| 4 | Login URL | Login URL | Portal access | Keep | No action |
| 5 | Cost | Cost | Show page, export | Keep but rename | P1 |
| 6 | Expiry Date | Expiry Date | **Primary trigger for notification system** | **Do NOT remove** | No action |
| 7 | Renewal Date | Renewal Date | Notification scheduling | Used by notification system for `next_notification_due_at` computation | Keep — critical for notification logic | No action |
| 8 | Status | Status | Filter, Dashboard, notification eligibility (`active` only) | Keep | No action |
| 9 | Module | Module | RBAC, GlobalSearch, Dashboard | Auto-fill to "Expiry Trackers" module | P1 |
| 10 | User | User | Ownership — **auto-filled by controller** | Dead field on create. Keep the DB column but **remove from UI** (automatic). | P1 |
| 11 | Notes | Notes | Display | Keep | No action |
| 12 | Email Notifications (enable) | Enable Email Notifications | Toggle for notification engine | Keep — core feature | No action |
| 13 | SMTP Profile | Send From / SMTP Profile | Mail delivery configuration | Keep | No action |
| 14 | Notify Before [days] | Notify Before | Notification scheduling thresholds | Keep | No action |
| 15 | Notify on Expiry Day | On expiry day | Additional notification trigger | Keep | No action |
| 16 | Notify Assigned User | Assigned User | Recipient selection | Keep | No action |
| 17 | Notify All Administrators | All Administrators | Recipient selection | Keep | No action |
| 18 | Custom Email Recipients | Custom Email Recipients | Recipient selection | Keep | No action |
| 19 | Disable Reason | Disable Reason | Shown only when notifications disabled | Keep — useful for audit | No action |

---

## 12. SMTP PROFILES

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Profile Name | Profile Name | SMTP profile identification | Keep — clear | No action |
| 2 | Sender Name | Sender Name | Email From header | Keep — clear | No action |
| 3 | Sender Email | Sender Email | Email From address, must be unique for deliverability | Keep — clear | No action |
| 4 | Reply-To Email | Reply-To Email | Email Reply-To header | Keep — useful | No action |
| 5 | SMTP Host | SMTP Host | Connection hostname | Keep — technical, clear for IT | No action |
| 6 | SMTP Port | SMTP Port | Connection port | Keep — clear for IT (default 587) | No action |
| 7 | Encryption | Encryption | TLS/SSL/None | Keep — clear for IT | No action |
| 8 | SMTP Username | SMTP Username | Authentication credential | Keep — clear | No action |
| 9 | SMTP Password | SMTP Password | Authentication secret | Keep — labeled "New Password" on edit with helper text | No action |
| 10 | Set as default | Set as default | Default profile flag | Keep | No action |
| 11 | Active | Active | Toggle active status | Keep | No action |
| 12 | Priority | Priority | Fallback ordering | Keep — useful for IT | No action |

**Conclusion:** SMTP Profile form is clean, well-labeled, and all fields are justified.

---

## 13. ASSETS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Asset Tag | Asset Tag | Auto-generated if blank; unique identifier | Keep — clear | No action |
| 2 | Category | Category | Required, filters Type dropdown | Keep | No action |
| 3 | Type | Type | Required, filtered by Category | Keep | No action |
| 4 | Serial Number | Serial Number | Unique hardware identifier | Keep | No action |
| 5 | QR / Barcode ID | QR / Barcode ID | Physical asset scanning | Keep | No action |
| 6 | Status | Status | Workflow state (available/assigned/lost/decommissioned) | Keep — but note `assigned` should normally be set via Assign workflow, not manually | No action |
| 7 | Condition | Condition | Physical condition | Keep | No action |
| 8 | Location | Location | Physical location FK | Keep | No action |
| 9 | Department | Department | Organizational department | Keep | No action |
| 10 | Issue Date | Issue Date | Date asset was issued | Keep | No action |
| 11 | Return Date | Return Date | Date asset was returned | Keep — normally auto-set by return workflow, but manual override useful | No action |
| 12 | Vault Credentials | Vault Credentials | FK to Vault entry for asset passwords | Keep — useful | No action |
| 13 | Module | Module | RBAC scoping | **Do NOT remove** but auto-fill to "Assets" module | P1 |
| 14 | User | User | **BUG: overwritten on store by `Auth::id()`** | **Remove from UI** — field is dead on create | P1 |
| 15 | Primary Image | Primary Image | Asset photo | Keep | No action |
| 16 | Specifications | (Dynamic) | Hardware specs (CPU, RAM, etc.) | Keep — useful for IT teams | No action |
| 17 | Notes | Notes | Free-text notes | Keep | No action |

---

## 14. TASKS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Title | Title | Task identification | Keep | No action |
| 2 | Description | Description | Task details | Keep | No action |
| 3 | Module | Module | RBAC scoping, task categorization | **Do NOT remove** — used for RBAC and admin task visibility scoping | No action |
| 4 | Status | Status | Workflow state (pending/in_progress/completed/cancelled) | Keep | No action |
| 5 | Priority | Priority | Priority level (low/medium/high/urgent) | Keep | No action |
| 6 | Due Date | Due Date | Task deadline | Keep | No action |

**CRITICAL ISSUE: Assignees not usable.**
- `assignee_ids` is in the Request validation rules
- But the **controller never passes `$users` to the view**  
- The **form has no assignee selector**
- The **controller calls `Task::create()` directly** which silently drops `assignee_ids` (not in `$fillable`)
- The `TaskService` exists and properly handles `assignee_ids` via `sync()` but is never called
- **Result: Tasks can never have assignees through create/edit forms**

---

## 15. VAULT

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Service Name | Service Name | Identifier for the credential | Keep | No action |
| 2 | Service URL | Service URL | URL of the service | Keep | No action |
| 3 | Username | Username | Login identifier | Keep | No action |
| 4 | Password | Password / New Password | Encrypted secret | Keep — label clear on create; edit has helpful text | No action |
| 5 | Module | Module | RBAC scoping | **Do NOT remove** but auto-fill to "Vault" module | P1 |
| 6 | User | User | **Dead field** — not in request rules on store OR update | **Remove from UI** | P1 |
| 7 | Description | Description | Free-text notes | Keep | No action |

---

## 16. NOTES

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Content | Content | The note content itself | Keep | No action |
| 2 | Attach to Type | Attach to Type | Polymorphic relation type | Keep — useful for attaching notes to Features/Modules | No action |
| 3 | Entity | Entity | The specific Feature/Module to attach to | Keep — works with type | No action |

**BUG:** Form sends `"feature"` / `"module"` but request validation expects `"App\Models\Feature"` / `"App\Models\Module"`. This breaks note attachment to entities.

---

## 17. WEBHOOKS

### Create/Edit Form Fields

| # | Field | Label | Dependency | Recommended Action | Priority |
|---|---|---|---|---|---|
| 1 | Name | Name | Webhook identification | Keep | No action |
| 2 | URL | URL | Target endpoint for webhook payloads | Keep | No action |
| 3 | Events | Events (comma-separated) | Which events trigger this webhook | Keep — useful for IT teams | No action |
| 4 | Active | Active | Enable/disable toggle | Keep | No action |
| 5 | User | User | **Dead field** — overwritten by controller on store, not in update request | **Remove from UI** | P1 |

---

## 18. ATTACHMENTS

Only a create form exists (single file upload). Fields: `file` (upload), `notable_type`, `notable_id`.

All three are justified. No changes needed.

---

## SPECIAL FOCUS ANSWERS

### 1. Can Module and User fields be removed from Service Provider create/edit UI?

**Answer: PARTIAL**

- **Module (module_id):** **NO, cannot be removed from UI** — it is actively consumed by RBAC (`canOnModule` checks), GlobalSearch ownership scoping (`user_or_module`), and Dashboard module filtering. However, it CAN be hidden and auto-filled to the "Service Providers" module, since a service provider conceptually belongs to the "Service Providers" module. The risk is MEDIUM: if auto-filled, non-super-admin users who have permission on only specific modules would lose the ability to tag a provider to their accessible module. **Recommendation: move to "Advanced" section or hide with auto-fill to the user's current module context.**

- **User (user_id):** **YES, can be removed from UI** — The field is **already functionally dead** on create (overwritten by `Auth::id()` in `store()`). On update, it is not in the request rules so any submitted value is ignored. The DB column still serves as ownership for `RbacScope` scoping, but the UI field is misleading. **Recommendation: remove `user_id` select from UI** — ownership is automatically assigned.

### 2. Which fields are confusing for IT/software team users?

| Field | Current Label | Problem | Suggested Label |
|---|---|---|---|
| Service Provider → Website | Website | Ambiguous — provider website or portal URL? | "Portal URL" |
| Service Provider → Email | Email | Which email? Support, billing, or account? | "Support Email" |
| Service Provider → Password | Password | Password for what? | "Portal Password" |
| Service Provider → Cost | Cost | Monthly, annual, one-time, or total? | "Monthly Cost" (or "Annual Cost" if annual) |
| Hosting → Password | Password | cPanel or portal password? | "cPanel Password" or "Portal Password" |
| VPS → Password | Password | Root password or panel password? | "Root Password" |
| VoIP → Users-Name | Users-Name | Confusing — just "Name" | "Name" or "Display Name" |
| VoIP → Password | Password | Extension password or VoIP password? | "Extension Password" |
| Other Service → Password | Password | Password for which service? | "Service Password" |
| Other Service → Website | Website | Vendor site or service URL? Confusing alongside Login URL | "Vendor Website" (keep Login URL for actual login) |
| Domain Email → Password | Password | Email password | Label is clear; no change needed |
| Vault → Password field | Password | Standard password field; label clean | No change |
| All Cost fields | Cost | Never clear if monthly/annual/one-time | **"Monthly Cost"** — dashboard aggregates as monthly |

### 3. Which fields should be auto-filled instead of user-selected?

| Module | Field | Auto-fill Value | Rationale |
|---|---|---|---|
| All service modules (Domain, Hosting, VPS, VoIP, DomainEmail, OtherService, ExpiryTracker, Service Provider, Asset, Vault) | `module_id` | Module matching the current resource type (e.g., Domains module for Domain) | RBAC needs the field for permission checks, but a Domain logically only belongs to the Domains module. User should not need to pick. |
| All service modules | `user_id` | `Auth::id()` (already done on create) | Already auto-filled. Remove from form UI. |
| Service Provider | `type` | "other" (already done as fallback in store) | Already has fallback; no change needed. |
| Asset | `asset_tag` | Auto-generated if blank (already done) | Already auto-generated. Label could clarify. |

### 4. What breaks if `module_id` is removed from ALL create/edit UIs and auto-filled to the current resource module?

**Traced usages:**

| Usage | Impact if auto-filled (not removed from DB) |
|---|---|
| **RBAC `canOnModule` in controllers** | No impact — field still exists in DB, set to correct module |
| **GlobalSearch `user_or_module` ownership** | No impact — query uses stored `module_id` |
| **Dashboard OperationsWidget module filtering** | No impact — stored `module_id` still used |
| **Reports (module-specific filtering)** | No impact — reports query by stored `module_id` |
| **API filtering by `module_id`** | No impact — stored `module_id` still filterable |
| **Super-admin assigning to wrong module** | Eliminated — this is a good change (prevents misconfiguration) |
| **Non-admin assigning record to their accessible module** | **Impacted**: If an admin has access to multiple modules and needs to tag records differently per module, auto-fill would need to use the user's current module context (e.g., from the page they're on) |

**Verdict:** Auto-fill `module_id` based on the current module context is safe for all modules. The field should be hidden from the form but kept in the DB.

### 5. Are there any orphaned DB columns not in any form?

| Module | Column | In Form? | Should Be? |
|---|---|---|---|
| Domain | `registrar` | No | Orphan — in migration but not in fillable or any form |
| Hosting | `provider` (string) | No | Orphan — in migration but not in fillable or any form |
| VPS | `provider` (string) | No | Orphan — in migration but not in fillable or any form |
| DomainEmail | `storage_mb` | No | Exists in model fillable and request but no form field |
| DomainEmail | `cost` | No | Exists in model fillable and request but no form field |
| DomainEmail | `expiry_date` | No | Exists in model fillable and request but no form field |
| VoIP | `start_date` | No | Exists in model, request, but no form field |
| VoIP | `expiry_date` | No | **CRITICAL: Missing from form!** Expiry notifications never fire for VoIP |
| VoIP | `password` (not extension_password) | No | Exists in model, request, but no form field |
| VoIP | `type` | No | Exists in model, request, but no form field |
| VoIP | `username` | No | Exists in model, request, but no form field |
| VoIP | `dashboard_url` | No | Exists in model, request, but no form field |

### 6. Should Cost be renamed everywhere?

**Answer: YES.** The Dashboard OperationsWidget treats `cost` as a monthly cost:
```php
COALESCE(SUM(CASE WHEN status = 'active' AND expiry_date IS NOT NULL THEN cost ELSE 0 END), 0) AS monthly_cost
```

The field is explicitly aggregated as `monthly_cost`. But the form label just says "Cost". This is ambiguous for users who might enter annual, quarterly, or one-time amounts.

**Recommendation:** Rename all Cost form labels to "Monthly Cost" to match the dashboard's expectation. This is a P1 fix (before live if quick).

### 7. Are there any P0 live-blocker fields?

**No P0 fields found.** All fields serve at least one purpose (display, reporting, RBAC, notifications, etc.) or are dead fields that mislead but don't block functionality.

### 8. Summary by Priority

#### P1 (Fix Before Live — Quick Label/UI Changes)

| # | Module | Change | Effort |
|---|---|---|---|
| 1 | All services | Rename "Cost" → "Monthly Cost" | 1 min per view file |
| 2 | Domain, Hosting, VPS, Service Provider, Other Service, Expiry Tracker, Vault | Remove `user_id` from create/edit forms | 2 min per view file |
| 3 | All services | Hide `module_id` from form, auto-fill to resource module | 5-10 min per controller |
| 4 | VoIP | Rename "Users-Name" → "Name" | 1 min |
| 5 | VoIP | Rename "Password" (extension_password) → "Extension Password" | 1 min |
| 6 | Service Provider | Rename "Website" → "Portal URL" | 1 min |
| 7 | Service Provider | Rename "Email" → "Support Email" | 1 min |
| 8 | Notes | Fix `notable_type` form value mismatch (send full class names instead of short strings) | 5 min |

#### P2 (v1.1 Cleanup)

| # | Module | Change |
|---|---|---|
| 1 | All services | Rename "Password" to specific labels (Portal Password, cPanel Password, Root Password, etc.) |
| 2 | Domain, Hosting, VPS | Drop or expose orphaned `provider`/`registrar` columns |
| 3 | VoIP | Add missing form fields: `expiry_date`, `start_date`, `type`, `username`, `dashboard_url` |
| 4 | DomainEmail | Add or document missing fields: `storage_mb`, `cost`, `expiry_date` |
| 5 | Task | Fix assignee handling: pass `$users` to view, add multi-select, use `TaskService` for sync |
| 6 | Service Provider | Move `module_id` to "Advanced" section or hide with auto-fill |

#### No Action

All fields not listed above are correctly labeled and justified. No changes needed.
