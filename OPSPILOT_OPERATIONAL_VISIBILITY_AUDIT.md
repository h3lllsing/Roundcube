# OPSPILOT OPERATIONAL VISIBILITY AUDIT

## A. Executive Summary

**Audit date:** 2026-07-13
**Scope:** All 34 user-facing Index/List pages
**Objective:** Show the information users need to operate the record. Hide or de-emphasize low-value information. Never expose passwords or secrets directly in list tables.

**Key findings:**

1. **AnyDesk ID** exists in the Asset model/schema/show but is **missing from the Asset Index** — HIGH impact fix
2. **cPanel URL and Login ID** on Hosting Index exist in the ⋮ menu but are **not visible as inline copyable fields** — these belong as visible columns for daily ops
3. **VPS IP address** is already visible on Index (good) but is also duplicated as a copy action in ⋮ — redundant, keep as visible column
4. **VoIP Extension/Phone** are already visible (good)
5. **Domain Emails** Index shows only Domain + Email — missing Status and Provider which are on the Show page
6. **Service Providers** Index is minimal (Name, Type, Status) — missing Expiry and Provider columns that help with triage
7. **Expiry Trackers** Index is solid
8. **No remote-support identifiers** exist outside Assets (no TeamViewer, no RustDesk, no RDP hostname fields in any model)
9. **Tasks Index** is solid operationally
10. **Vault Index** is missing Service URL as a visible column (only in ⋮ menu currently)

**Overall assessment:** The December 2025 "UX simplification" removed several operationally important columns from Index pages. The current state is generally functional but has gaps where operators must click into Show pages for common lookups. The biggest single win is surfacing AnyDesk ID on the Asset Index.

---

## B. Page-by-Page Operational Field Matrix

### INFRASTRUCTURE

#### 1. Service Providers / Vendors

| Aspect | Detail |
|--------|--------|
| **Page:** | `service-providers.index` |
| **Route:** | `service-providers.index` (GET /service-providers) |
| **Primary user/job:** | Operations/Admin — find vendor contact info, check status |
| **Current columns:** | Name, Type, Status, Actions |
| **Important fields available in model/show but missing from Index:** | Email, Website, Login ID, Expiry Date, Monthly Cost |
| **Low-value current columns:** | None — all 3 data columns are useful |
| **Sensitive fields:** | `password` (encrypted, hidden from serialization — never expose on Index) |
| **Recommended final columns:** | **Name, Type, Status, Expiry, Actions** (add Expiry) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present); add Email copy, Website link |
| **Reasoning:** | Expiry Date is operationally critical for vendor contract management. Currently only visible on Show. An operator scanning the list needs to know which vendors are expiring soon without opening each record. |

**Classification:**
- Name → MUST SHOW
- Type → SHOULD SHOW
- Status → MUST SHOW
- **Expiry Date** → **SHOULD SHOW** (currently SHOW ON DETAIL ONLY, should promote)
- Email → OPTIONAL (add to ⋮ as copy action)
- Website → OPTIONAL (add to ⋮ as link)
- Login ID → SHOW ON DETAIL ONLY
- Password → SECURE ACTION ONLY
- Cost → OPTIONAL

---

#### 2. Hosting

| Aspect | Detail |
|--------|--------|
| **Page:** | `hostings.index` |
| **Route:** | `hostings.index` (GET /hostings) |
| **Primary user/job:** | Operations — check domain hosting, access cPanel, view expiry |
| **Current columns:** | Domain (name), Status, Provider, Expiry, Actions |
| **Important fields available in model/show but missing from Index:** | **cPanel URL**, **Username/Login ID**, Plan, Domain IP |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | `password` (encrypted — never expose on Index) |
| **Recommended final columns:** | **Name, Plan, Status, Provider, Expiry, Actions** (add Plan, keep existing) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present); cPanel link, Login ID copy (already present) |
| **Reasoning:** | The Index already includes cPanel and Login ID in the ⋮ actions menu, which is the correct UX pattern. Plan is visible on Show but useful on Index for quick hosting tier comparison. Current columns are solid. |

**Classification:**
- Name → MUST SHOW
- Plan → **SHOULD SHOW** (currently SHOW ON DETAIL ONLY)
- Status → MUST SHOW
- Provider → MUST SHOW
- Expiry → MUST SHOW
- cPanel URL → ⋮ ACTION (correctly placed)
- Login ID (username) → ⋮ ACTION (correctly placed)
- Password → SECURE ACTION ONLY (correctly placed in ⋮)

---

#### 3. Domains

| Aspect | Detail |
|--------|--------|
| **Page:** | `domains.index` |
| **Route:** | `domains.index` (GET /domains) |
| **Primary user/job:** | Operations — check domain status, expiry, linked hosting |
| **Current columns:** | Name, Hosting, Provider, Expiry, Status, Actions |
| **Important fields available in model/show but missing from Index:** | Registration Date, Cloudflare Status, Auto Renew |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | None |
| **Recommended final columns:** | **Name, Hosting, Provider, Expiry, Status, Actions** (keep current — already solid) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present) |
| **Reasoning:** | Current Index is excellent for daily ops. Name, Hosting link, Provider, Expiry, and Status are exactly what an operator needs. Registration Date and Cloudflare status are secondary — fine on Show only. |

**Classification:**
- Name → MUST SHOW
- Hosting (linked) → MUST SHOW
- Provider → MUST SHOW
- Expiry → MUST SHOW
- Status → MUST SHOW
- Cloudflare Status → OPTIONAL (low operational value)
- Registration Date → REMOVE FROM INDEX (fine on Show)
- Auto Renew → OPTIONAL

---

#### 4. Domain Emails

| Aspect | Detail |
|--------|--------|
| **Page:** | `domain-emails.index` |
| **Route:** | `domain-emails.index` (GET /domain-emails) |
| **Primary user/job:** | Operations — find email addresses by domain, check status |
| **Current columns:** | Domain, Email, Actions |
| **Important fields available in model/show but missing from Index:** | **Status**, **Provider** (service_provider_id), **Expiry** |
| **Low-value current columns:** | None — Domain and Email are both essential |
| **Sensitive fields:** | `password` (encrypted — never expose) |
| **Recommended final columns:** | **Email, Domain, Status, Provider, Actions** (add Status and Provider) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete; Password copy (already on Show) |
| **Reasoning:** | The Index currently shows only Domain + Email. Status is completely missing — operators can't tell at a glance whether an email is active or expired. Provider helps identify which vendor manages the email service. Both fields are on Show but absent from Index. |

**Classification:**
- Email → MUST SHOW
- Domain → MUST SHOW
- **Status** → **MUST SHOW** (currently missing from Index — HIGH priority)
- **Provider** → **SHOULD SHOW** (currently missing from Index)
- Password → SECURE ACTION ONLY

---

#### 5. VPS Accounts

| Aspect | Detail |
|--------|--------|
| **Page:** | `vps.index` |
| **Route:** | `vps.index` (GET /vps) |
| **Primary user/job:** | Operations — connect to VPS, check status, view IP |
| **Current columns:** | VPS (name), Status, Vendor, VPS IP, Expiry, Associate (user), Actions |
| **Important fields available in model/show but missing from Index:** | Plan, Location, OS, Department, RAM/Disk/CPU specs |
| **Low-value current columns:** | Associate (user relation) — useful for ownership tracking |
| **Sensitive fields:** | `password` (encrypted — never expose on Index) |
| **Recommended final columns:** | **Name, Status, Vendor, IP, Expiry, Associate, Actions** (keep current — already excellent) |
| **Recommended ⋮ actions:** | View Details, Password copy, Edit, Delete (already present) |
| **Reasoning:** | The VPS Index is arguably the best in the entire app. IP is visible inline with copy. Status, Vendor, Expiry, and ownership are all present. Plan and specs are Show-only, which is appropriate. |

**Classification:**
- Name → MUST SHOW
- Status → MUST SHOW
- Vendor (Provider) → MUST SHOW
- IP Address → MUST SHOW ✅ (already visible and copyable)
- Expiry → MUST SHOW
- Associate (User) → SHOULD SHOW
- Plan → SHOW ON DETAIL ONLY
- OS/Specs → SHOW ON DETAIL ONLY
- Password → SECURE ACTION ONLY (correctly placed)

---

#### 6. VoIP

| Aspect | Detail |
|--------|--------|
| **Page:** | `voip.index` |
| **Route:** | `voip.index` (GET /voip) |
| **Primary user/job:** | Operations — find extension, phone number, vendor, check status |
| **Current columns:** | Name, Extension, Phone Number, Vendor, Status (number_status), Actions |
| **Important fields available in model/show but missing from Index:** | Direction, Server IP, Expiry Date |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | `password`, `extension_password` (encrypted — never expose) |
| **Recommended final columns:** | **Name, Extension, Phone Number, Vendor, Status, Actions** (keep current) |
| **Recommended ⋮ actions:** | View Details, Ext. Password copy, Edit, Delete (already present) |
| **Reasoning:** | The VoIP Index is excellent. Extension, phone number, vendor, and status are all visible. The Ext. Password copy in ⋮ is correctly placed as a secure action. Expiry Date could be useful but the Number Status already conveys operational health. |

**Classification:**
- Name → MUST SHOW
- Extension → MUST SHOW
- Phone Number → MUST SHOW
- Vendor → MUST SHOW
- Status → MUST SHOW
- Direction → OPTIONAL (add to Show detail)
- Server IP → SHOW ON DETAIL ONLY
- Expiry → OPTIONAL (Status is sufficient for triage)

---

#### 7. SaaS / Other Services

| Aspect | Detail |
|--------|--------|
| **Page:** | `other-services.index` |
| **Route:** | `other-services.index` (GET /other-services) |
| **Primary user/job:** | Operations — check service type, status, expiry |
| **Current columns:** | Name, Type, Expiry, Status, Actions |
| **Important fields available in model/show but missing from Index:** | **Provider** (service_provider_id), Login URL, Username |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | `password` (encrypted — never expose) |
| **Recommended final columns:** | **Name, Type, Provider, Expiry, Status, Actions** (add Provider) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete; Login URL copy (already on Show), Username copy |
| **Reasoning:** | Provider is important for knowing which vendor manages the SaaS service. Currently only on Show. Login URL and Username belong in ⋮ actions, not as Index columns. |

**Classification:**
- Name → MUST SHOW
- Type → MUST SHOW
- **Provider** → **SHOULD SHOW** (currently missing from Index)
- Expiry → MUST SHOW
- Status → MUST SHOW
- Login URL → ⋮ ACTION
- Username → ⋮ ACTION
- Password → SECURE ACTION ONLY

---

#### 8. Renewals / Expiry Trackers

| Aspect | Detail |
|--------|--------|
| **Page:** | `expiry-trackers.index` |
| **Route:** | `expiry-trackers.index` (GET /expiry-trackers) |
| **Primary user/job:** | Operations — see what's expiring, check status, manage notifications |
| **Current columns:** | Name / Source, Expiry, Status, Renew, Actions |
| **Important fields available in model/show but missing from Index:** | Service Provider, Login URL, Username, Cost, Notifications status |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | None (passwords are on linked services, not on tracker) |
| **Recommended final columns:** | **Name / Source, Expiry, Status, Actions** (keep current — solid) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present) |
| **Reasoning:** | Expiry Trackers Index is solid. Name, Expiry, Status, and Renewal action are exactly what an operator needs. The linked source badge provides context. No changes recommended. |

**Classification:**
- Name/Source → MUST SHOW
- Expiry → MUST SHOW
- Status → MUST SHOW
- Renew → MUST SHOW (action column)
- Service Provider → OPTIONAL (often redundant with Source)
- Login URL → ⋮ ACTION
- Username → SHOW ON DETAIL ONLY

---

#### 9. Hardware Assets

| Aspect | Detail |
|--------|--------|
| **Page:** | `assets.index` |
| **Route:** | `assets.index` (GET /assets) |
| **Primary user/job:** | IT Support — identify asset, find support access, check assignment |
| **Current columns:** | Asset ID (asset_tag), Brand, Model, Assigned To, Status, Actions |
| **Important fields available in model/show but missing from Index:** | **AnyDesk ID**, Serial Number, Type, Department, Premises, Location |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | `anydesk_password` (encrypted — never expose on Index) |
| **Recommended final columns:** | **Asset ID, Brand, Model, AnyDesk ID, Assigned To, Status, Actions** (add AnyDesk ID, remove nothing) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present) |
| **Reasoning:** | **AnyDesk ID is the #1 operational gap in the entire application.** It is stored in the database, visible on Show, used for remote IT support, but completely absent from the Index. IT support operators must open every asset record to copy the AnyDesk ID for remote sessions. This is a HIGH priority fix. Serial Number and Type are Show-only which is acceptable — operators don't need them for daily triage. |

**Classification:**
- Asset Tag → MUST SHOW
- Brand → MUST SHOW
- Model → MUST SHOW
- **AnyDesk ID** → **MUST SHOW** (#1 operational gap, currently SHOW ON DETAIL ONLY)
- Assigned To → MUST SHOW
- Status → MUST SHOW
- Serial Number → OPTIONAL (nice on Show, not needed on Index)
- Department → OPTIONAL
- Premises/Location → OPTIONAL
- AnyDesk Password → SECURE ACTION ONLY (adjacent field, never expose)

---

#### 10. G-Mails

| Aspect | Detail |
|--------|--------|
| **Page:** | `g-mails.index` |
| **Route:** | `g-mails.index` (GET /g-mails) |
| **Primary user/job:** | Operations — find Gmail accounts, check status, department |
| **Current columns:** | Status, User Name, Email, Department, Actions |
| **Important fields available in model/show but missing from Index:** | PSEUDO, Security Number, Recovery Email |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | `password` (encrypted — never expose) |
| **Recommended final columns:** | **Status, User Name, Email, Department, Actions** (keep current) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete (already present) |
| **Reasoning:** | Current columns are appropriate for daily ops. PSEUDO and Security Number are sensitive/fine on Show. Recovery Email is Show-only appropriate. |

**Classification:**
- Status → MUST SHOW
- User Name → MUST SHOW
- Email → MUST SHOW
- Department → SHOULD SHOW
- PSEUDO → SHOW ON DETAIL ONLY
- Security Number → SHOW ON DETAIL ONLY
- Password → SECURE ACTION ONLY

---

### CREDENTIALS

#### 11. My Credentials / Vault

| Aspect | Detail |
|--------|--------|
| **Page:** | `vault.index` (shared vault), `vault.my` (my vault) |
| **Route:** | `vault.index` (GET /vault), `vault.my` (GET /my-vault) |
| **Primary user/job:** | All users — retrieve stored passwords, access service URLs |
| **Current columns:** | Service Name, Module, URL, Username, Actions |
| **Important fields available in model/show but missing from Index:** | Description |
| **Low-value current columns:** | Module — useful for RBAC context |
| **Sensitive fields:** | `encrypted_password` (never shown — revealed on demand via `vault.reveal` endpoint) |
| **Recommended final columns:** | **Service Name, URL, Username, Actions** (URL could be promoted from optional to MUST SHOW) |
| **Recommended ⋮ actions:** | View Details, Reveal Password, Edit, Delete |
| **Reasoning:** | The Vault Index is appropriately designed. Service Name and Username are visible, password is securely behind reveal. URL is shown but could be more prominent as a clickable link. Description is Show-only which is fine. |

**Classification:**
- Service Name → MUST SHOW
- Module → SHOULD SHOW (context for RBAC filtering)
- URL → MUST SHOW (should be clickable/linked)
- Username → MUST SHOW
- encrypted_password → SECURE ACTION ONLY (correctly implemented via `/vault/{id}/reveal`)
- Description → SHOW ON DETAIL ONLY

---

### OPERATIONS

#### 12. My Tasks / Task Management

| Aspect | Detail |
|--------|--------|
| **Page:** | `tasks.index` |
| **Route:** | `tasks.index` (GET /tasks), `tasks.my` (GET /my-tasks) |
| **Primary user/job:** | All users — view assigned tasks, track status/priority/due dates |
| **Current columns:** | Title, Module, Status, Priority, Assignees, Due, Actions |
| **Important fields available in model/show but missing from Index:** | Description |
| **Low-value current columns:** | None — all useful |
| **Sensitive fields:** | None |
| **Recommended final columns:** | **Title, Module, Status, Priority, Assignees, Due, Actions** (keep current) |
| **Recommended ⋮ actions:** | View Details, Edit, Delete, Change Status (already present) |
| **Reasoning:** | The Tasks Index is excellent — it shows everything needed for daily operations. Title, status, priority, assignees, and due date are all visible. Description is Show-only which is appropriate. |

**Classification:**
- Title → MUST SHOW
- Module → SHOULD SHOW
- Status → MUST SHOW
- Priority → MUST SHOW
- Assignees → MUST SHOW
- Due → MUST SHOW
- Description → SHOW ON DETAIL ONLY

---

#### 13. Calendar

| Aspect | Detail |
|--------|--------|
| **Page:** | `calendar.index` |
| **Route:** | `calendar` (GET /calendar) |
| **Primary user/job:** | Operations — see upcoming events grouped by month |
| **Current columns:** | Calendar grid (month view), Event list: Date, Name, Type, Status |
| **Important fields available in model/show but missing from Index:** | N/A — calendar is a specialized view, not a table |
| **Classification:** | Not a standard Index page — special layout. Current design is adequate. |

---

#### 14. Notes

| Aspect | Detail |
|--------|--------|
| **Page:** | `notes.index` |
| **Route:** | `notes.index` (GET /notes) |
| **Primary user/job:** | All users — browse notes attached to resources |
| **Current columns:** | Content (trimmed), User, Notable Type, Actions |
| **Important fields available in model/show but missing from Index:** | Full content, Pinned status |
| **Low-value current columns:** | Notable Type — useful for filtering context |
| **Recommended final columns:** | Current layout is fine for a note browser. |

---

### ADMINISTRATION

#### 15. Users

| Aspect | Detail |
|--------|--------|
| **Page:** | `users.index` |
| **Route:** | `users.index` (GET /users) |
| **Primary user/job:** | Admin — manage users, check roles, status, last login |
| **Current columns:** | Name, Email, Roles, Status, Last Login, Actions |
| **Important fields available in model/show but missing from Index:** | Module ownership count, Suspension info |
| **Low-value current columns:** | None — all useful for admin |
| **Sensitive fields:** | `password` (hashed — never shown anywhere), `remember_token` |
| **Recommended final columns:** | Keep current. |
| **Reasoning:** | Solid admin view. Name, email, roles, status, and last login are all an admin needs for daily user management. |

---

#### 16. Roles, Role Templates, Privileges, Modules, Features, Permissions

These are admin configuration pages. Current column sets are appropriate for RBAC management:
- **Roles:** Name, Slug, Privileges, Users, Actions — adequate
- **Role Templates:** Card layout with name/version/badges — adequate
- **Privileges:** ID, Name, Slug, Roles, Actions — adequate
- **Modules:** ID, Name, Feature, Created, Actions — adequate
- **Features:** ID, Name, Slug, Modules, Actions — adequate
- **Permissions:** Feature, Module, role permissions matrix — adequate

No significant operational gaps. These pages are for configuration, not daily operations.

---

#### 17. SMTP Profiles

| Aspect | Detail |
|--------|--------|
| **Page:** | `smtp-profiles.index` |
| **Route:** | `smtp-profiles.index` (GET /admin/smtp-profiles) |
| **Current columns:** | Name, Sender Email, Default, Status, Actions |
| **Sensitive fields:** | `smtp_password` (hidden from serialization — correct) |
| **Classification:** | Adequate for admin mail configuration. |

---

#### 18. Audit Trail / Activity Logs

| Aspect | Detail |
|--------|--------|
| **Page:** | `activity-logs.index` |
| **Route:** | `activity-logs.index` (GET /activity-logs) |
| **Current columns:** | User, Event, Description, Subject, Date |
| **Classification:** | Adequate for security audit review. |

---

#### 19. Login History

| Aspect | Detail |
|--------|--------|
| **Page:** | `login-audits.index` |
| **Route:** | `login-audits.index` (GET /login-audits) |
| **Current columns:** | User, Email, Event, IP Address, Date |
| **Classification:** | Adequate for security audit. |

---

#### 20. Attachments

| Aspect | Detail |
|--------|--------|
| **Page:** | `attachments.index` |
| **Route:** | `attachments.index` (GET /attachments) |
| **Current columns:** | Filename, Type, Size, Attached To, User, Uploaded, Actions |
| **Classification:** | Adequate for file management. |

---

#### 21. API Tokens / API Access

| Aspect | Detail |
|--------|--------|
| **Page:** | `tokens.index` |
| **Route:** | `tokens.index` (GET /tokens) |
| **Current columns:** | Name, Created, Last Used, Actions |
| **Sensitive fields:** | Token values are only shown once at creation — correct behavior |
| **Classification:** | Adequate. Token values must never appear in Index. |

---

#### 22. Webhooks / Integrations

| Aspect | Detail |
|--------|--------|
| **Page:** | `webhooks.index` |
| **Route:** | `webhooks.index` (GET /webhooks) |
| **Current columns:** | Name, URL, Events, Active, Last Fired, Actions |
| **Sensitive fields:** | `secret` (hidden from serialization — correct) |
| **Classification:** | Adequate for webhook management. |

---

#### 23. Import

| Aspect | Detail |
|--------|--------|
| **Page:** | `import.create` (GET /import) |
| **Route:** | `import.create` (GET /import) |
| **Classification:** | Single-page upload form — not an Index page. No changes needed. |

---

### REPORTS

#### 24. Reports

| Aspect | Detail |
|--------|--------|
| **Page:** | `reports.index` |
| **Route:** | `reports.index` (GET /reports) |
| **Current layout:** | Category cards with KPI metrics. Not a list/table. |
| **Classification:** | Current design is adequate for a report hub. |

---

### MONITORING

#### 25. Monitoring

| Aspect | Detail |
|--------|--------|
| **Page:** | `monitoring.index` |
| **Route:** | `monitoring.index` (GET /monitoring) |
| **Primary user/job:** | Operations — check resource health, status, last check time |
| **Current columns:** | Type, Name, URL, Status, Last Check, Actions |
| **Important fields available but missing from Index:** | None — all key fields are present |
| **Low-value current columns:** | None |
| **Recommended final columns:** | Keep current. |
| **Reasoning:** | The Monitoring Index shows everything needed: resource type, name, URL, status badge, and last check time. Each status has a distinct color. Solid operational view. |

---

#### 26. Notifications

| Aspect | Detail |
|--------|--------|
| **Page:** | `notifications.index` |
| **Route:** | `notifications.index` (GET /notifications) |
| **Current layout:** | List-style with type indicator, content, action buttons |
| **Classification:** | Adequate for notification management. |

---

## C. Important Missing Fields by Page

| Priority | Page | Missing Field | Why Important | Currently Visible On |
|----------|------|--------------|---------------|---------------------|
| 🔴 HIGH | Assets | **AnyDesk ID** | IT support needs remote-access ID for every asset without opening the record | Show only |
| 🔴 HIGH | Domain Emails | **Status** | Operators can't tell if email is active/expired without opening record | Show only |
| 🟡 MEDIUM | Domain Emails | **Provider** | Identifies vendor managing the email service | Show only |
| 🟡 MEDIUM | Other Services | **Provider** | Identifies vendor managing the SaaS service | Show only |
| 🟡 MEDIUM | Service Providers | **Expiry Date** | Shows vendor contract expiry without opening record | Show only |
| 🟢 LOW | Hosting | **Plan** | Shows hosting tier for quick comparison | Show only |

---

## D. Fields Currently Shown but Low-Value

| Page | Low-Value Field | Rationale |
|------|----------------|-----------|
| None identified | — | The prior UX simplification already removed genuinely low-value columns (Created At, Updated At, etc.). Current sets are lean. |

---

## E. Fields Recommended to Restore to Index

| Page | Field | Why Restore | Previously Removed? |
|------|-------|-------------|-------------------|
| Assets | AnyDesk ID | Remote IT support daily lookup | Yes (removed in earlier simplification) |
| Domain Emails | Status | Can't triage emails without status | Yes |
| Other Services | Provider | Vendor context for SaaS services | Likely |
| Service Providers | Expiry | Vendor contract management | Likely |

---

## F. Sensitive Fields That Must Remain Hidden

| Field | Model | Current State | Correct? |
|-------|-------|--------------|----------|
| `password` | Hosting, DomainEmail, VPS, Voip, OtherService, ServiceProvider, GMail | encrypted cast, hidden from serialization | ✅ Correct |
| `anydesk_password` | Asset | encrypted cast | ✅ Correct |
| `smtp_password` | SmtpProfile | hidden from serialization | ✅ Correct |
| `encrypted_password` | VaultEntry | hidden from serialization, revealed via API | ✅ Correct |
| `secret` | Webhook | hidden from serialization | ✅ Correct |
| `extension_password` | Voip | encrypted cast, hidden | ✅ Correct |

**All password/secret fields are correctly handled.** No exposure risks identified.

---

## G. Remote Support Identifier Audit

| Search Term | Found? | Model(s) | Currently on Index? |
|-------------|--------|----------|-------------------|
| `anydesk_id` | ✅ Yes | Asset | **NO** — Only on Show! |
| `anydesk_password` | ✅ Yes | Asset | NO (and should NEVER be) |
| `teamviewer` / `teamviewer_id` | ❌ No | — | — |
| `rustdesk` | ❌ No | — | — |
| `remote_id` | ❌ No | — | — |
| `remote_access` | ❌ No | — | — |
| `rdp` | ❌ No | — | — |
| `hostname` | ❌ No (not a model field) | — | — |
| `device_id` | ❌ No | — | — |
| `serial_number` | ✅ Yes | Asset | NO — Only on Show |

**Conclusion:** AnyDesk is the only remote-support identifier in the entire system. It is fully implemented (migration, model, validation, views) but **unjustifiably absent from the Asset Index page**. This is the highest-priority fix in this audit.

---

## H. Hardware Assets Detailed Recommendation

### Current Asset Index columns:
`Asset Tag` | `Brand` | `Model` | `Assigned To` | `Status` | Actions

### Recommended Asset Index columns:
`Asset Tag` | `Brand` | `Model` | **`AnyDesk ID`** | `Assigned To` | `Status` | Actions

### Rationale:
1. **AnyDesk ID already exists** in the Asset model (`anydesk_id`, string, nullable)
2. **Already on Show page** (`resources/views/assets/show.blade.php:47`)
3. **Already in controller Index query** (`AssetController.php:76` selects `anydesk_id`)
4. **Already in search scope** (`Asset.php:75` includes `anydesk_id` in search)
5. **Already in full demo seeder** (`FullDemoSeeder.php:709`)
6. **IT Support daily workflow** relies on AnyDesk ID for remote sessions
7. **Non-sensitive** — AnyDesk ID is an identifier, not a secret (the password is separate and encrypted)

### Implementation:
- **Blade-only change** to `resources/views/assets/index.blade.php`
- Add one `<th>` for "AnyDesk ID" and one `<td>` showing `$asset->anydesk_id` in the table
- The column width and data already available in `$asset` from the controller's select

### What NOT to add:
- `anydesk_password` must remain encrypted and hidden (only revealed on Show via an action)

---

## I. Top 10 Highest-Impact Visibility Fixes

| Rank | Page | Fix | Impact | Effort | Type |
|------|------|-----|--------|--------|------|
| 1 | Assets | **Add AnyDesk ID column** | IT support saves 3+ clicks per remote session | Minutes | Blade-only |
| 2 | Domain Emails | **Add Status column** | Operators can triage email at a glance | Minutes | Blade-only |
| 3 | Domain Emails | **Add Provider column** | Know vendor without opening record | Minutes | Blade-only |
| 4 | Other Services | **Add Provider column** | Know vendor for SaaS services | Minutes | Blade-only |
| 5 | Service Providers | **Add Expiry column** | See vendor contract expiry in list | Minutes | Blade-only |
| 6 | Hosting | **Add Plan column** | Compare hosting tiers in list | Minutes | Blade-only |
| 7 | Vault | **Make URL clickable** | Open service directly from Index | Minutes | Blade-only |
| 8 | Service Providers | **Add Email to ⋮** | Copy vendor email without opening record | Minutes | Blade-only |
| 9 | Service Providers | **Add Website to ⋮** | Open vendor portal from list | Minutes | Blade-only |
| 10 | Other Services | **Add Login URL to ⋮** | Access SaaS portal from list | Minutes | Blade-only |

---

## J. Recommended Implementation Batches

### Batch 1 — HIGH PRIORITY (Blade-only, < 30 minutes total)
1. Add **AnyDesk ID** column to Asset Index
2. Add **Status** column to Domain Emails Index
3. Add **Provider** column to Domain Emails Index

### Batch 2 — MEDIUM PRIORITY (Blade-only, < 30 minutes total)
4. Add **Provider** column to Other Services Index
5. Add **Expiry** column to Service Providers Index
6. Add **Plan** column to Hosting Index

### Batch 3 — LOW PRIORITY (Blade-only, < 30 minutes total)
7. Make Vault URL clickable on Vault Index
8. Add Email copy action to Service Providers ⋮
9. Add Website link to Service Providers ⋮
10. Add Login URL copy to Other Services ⋮

**No batch requires controller, model, route, RBAC, or database changes.** All 10 fixes are Blade-view-only.

---

## Implementation Notes

- All recommended changes are **Blade-only view edits**
- No models, controllers, migrations, routes, or permissions need modification
- The `$asset->anydesk_id` is already loaded by `AssetController::index()` via `->select(['id', 'asset_tag', 'brand', 'model', 'assigned_user_name', 'status', 'premises', 'anydesk_id', 'module_id'])`
- The `$domainEmail->status` and `$domainEmail->serviceProvider->name` are accessible via existing relationships
- The `$otherService->serviceProvider->name` is accessible via existing relationship
- The `$serviceProvider->expiry_date` is already in the model's `$fillable` and `$casts`

---

FULL OPERATIONAL VISIBILITY AUDIT COMPLETE — STOPPING BEFORE IMPLEMENTATION
