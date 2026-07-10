# OpsPilot Module Guide

This guide covers every module in the system. Each section explains:

- Purpose
- Who should / should not use it
- Required and optional fields
- What happens after save
- Related modules
- Activity logs created
- Reports/Dashboard widgets that use it
- Common mistakes
- Example entry

---

## Table of Contents

1. [Dashboard](#1-dashboard)
2. [Users](#2-users)
3. [Roles](#3-roles)
4. [Permissions (Module Permissions)](#4-permissions-module-permissions)
5. [Service Providers](#5-service-providers)
6. [Domains](#6-domains)
7. [Hosting](#7-hosting)
8. [Domain Emails](#8-domain-emails)
9. [VPS](#9-vps)
10. [VoIP](#10-voip)
11. [Other Services](#11-other-services)
12. [Expiry Trackers](#12-expiry-trackers)
13. [SMTP Profiles](#13-smtp-profiles)
14. [Vault](#14-vault)
15. [Assets](#15-assets)
16. [Tasks](#16-tasks)
17. [Notes](#17-notes)
18. [Webhooks](#18-webhooks)
19. [Reports](#19-reports)
20. [Imports](#20-imports)
21. [Exports](#21-exports)
22. [Activity Logs](#22-activity-logs)
23. [Login Audits](#23-login-audits)
24. [Attachments](#24-attachments)
25. [Tokens (API Access)](#25-tokens-api-access)

---

## Understanding Infrastructure Modules

Before reading the individual module sections, understand how these infrastructure modules relate:

**Service Provider** is the company you buy from (e.g., GoDaddy, DigitalOcean, AWS).

**Domain** is a website address (e.g., example.com). A domain can be hosted anywhere.

**Hosting** is the server where your website files live. Hosting can have multiple domains.

**VPS** is a virtual private server — a virtual machine you control completely.

**Domain Email** is an email account at your domain (e.g., info@example.com).

**VoIP** is a phone system (Voice over IP).

**Other Service** is anything that does not fit the above categories (e.g., SaaS subscription, SSL certificate).

**Expiry Tracker** is a reminder for any of the above when they are about to expire.

**Relationship example:**

```
Service Provider (GoDaddy)
  ├── Domain (example.com)
  ├── Hosting (example hosting plan)
  ├── Domain Email (info@example.com)
  └── Expiry Tracker (reminds you when domain expires)

Service Provider (DigitalOcean)
  └── VPS (web-server-01)
       └── Expiry Tracker (reminds you when VPS is due)
```

---

## Cost Fields

All modules with a **Cost** field: the cost is used for:

- **Dashboard Operations Widget** — total monthly cost
- **Reports** — cost summaries, cost by type, top costs
- **Export** — cost data in CSV

**Important:** Enter cost consistently. If billing cycle is monthly, enter monthly cost. If yearly, divide by 12 and enter the monthly equivalent. The system does not convert between billing cycles automatically.

---

## 1. Dashboard

**Purpose:** Home page. Shows a summary of everything happening in the portal.

**Who should use it:** All users.
**Who should not use it:** No one — this is the default landing page.

**What you see depends on your role:**
- Super Admin: sees all data across all modules
- Admin: sees data for modules they have permission on
- User: sees only their own data

**Widgets:**
- Operations: total active services, monthly cost, expiring services
- Renewals: expiry tracker health, SMTP status, upcoming renewals
- Tasks: pending tasks, overdue tasks, weekly tasks
- Assets: assigned/returned assets, asset status breakdown
- Vault: recent reveals, total entries
- Quick Actions: shortcuts based on your permissions
- Activity: recent system activity
- SMTP: profile status, failed profiles
- Server Health: PHP version, disk usage, database status

**What activity logs are created:** None (dashboard is read-only).

**Common mistakes:** None. Just view and navigate.

---

## 2. Users

**Purpose:** Manage who can login to the portal.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users, IT Support staff.

**Required fields:** Name, Email, Password.
**Optional fields:** Roles assignment.

**What happens after save:**
- User is created and can login with their email and password
- Activity log is created: "User X created user Y"
- If roles were assigned, the user gets those permissions

**Related modules:** Roles, Permissions, Assets, Tasks, Vault.

**Activity logs created:**
- User created
- User updated (name, email, password)
- User deleted
- User suspended / unsuspended
- User cloned

**Common mistakes:**
- Forgetting to assign a role — user will have no permissions
- Assigning wrong role (e.g., giving Super Admin to a junior staff)
- Not checking email format — must be valid email

**Example:**
```
Name: Ahmad Raza
Email: ahmad@company.com
Password: (temporary, user will change)
Roles: User (default)
```

**How Super Admin uses it:** Creates all users. Manages suspensions. Clones users.
**How Admin uses it:** Cannot access this module (Super Admin only).
**How Staff uses it:** Cannot access this module.

---

## 3. Roles

**Purpose:** Define groups of users with the same permissions.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users, IT Support.

**Required fields:** Name, Slug.
**Optional fields:** (none)

**What happens after save:**
- Role is available to assign to users
- Privileges can be attached to the role
- Module permissions can be set for the role
- Activity log is created: "Role X created"

**Related modules:** Users, Privileges, Module Permissions.

**Activity logs created:**
- Role created, updated, deleted
- Privileges attached/detached to role
- Users assigned/removed from role

**Common mistakes:**
- Deleting a role that has users assigned — system blocks this. You must reassign users first.
- Creating duplicate roles (same slug) — slug must be unique.

**Example:**
```
Name: IT Support
Slug: it-support
```

**How Super Admin uses it:** Creates roles, manages privileges per role.
**How Admin uses it:** Cannot access.
**How Staff uses it:** Cannot access.

---

## 4. Permissions (Module Permissions)

**Purpose:** Control which roles can do what on each module.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Actions you can allow per module per role:**
- Create
- Read (view)
- Update (edit)
- Delete
- Approve
- Export
- Reveal (for Vault passwords)

**What happens after save:**
- The role's permissions on that module are updated immediately
- All users with that role get the new permissions
- Activity log is created

**Related modules:** Roles, Users.

**Activity logs created:**
- Module permission updated for role X on module Y

**Important: RBAC Hierarchy:**
1. Super Admin: all permissions, no restrictions
2. Role-level module permissions (this screen): apply to everyone with that role
3. User-level overrides: apply to a single user only
4. User-level overrides > Role-level permissions

See `OPSPILOT_RBAC_PERMISSION_GUIDE.md` for full explanation.

**Common mistakes:**
- Setting a permission too broadly (e.g., giving Delete to all users)
- Forgetting to set Read permission — users in that role cannot even see the module
- Not understanding that User Overrides override Role Permissions

---

## 5. Service Providers

**Purpose:** Store information about companies that provide services (GoDaddy, DigitalOcean, AWS, etc.).

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one — this is a core module.

**Required fields:** Name.
**Optional fields:** Type, Provider, Website, Email, Cost, Start Date, Expiry Date, Status, Notes, Module, Password.

**What happens after save:**
- Provider appears in dropdowns for Domains, Hosting, VPS, VoIP, etc.
- If cost is entered, it contributes to monthly cost reports
- Activity log is created

**Related modules:** Domains, Hosting, VPS, VoIP, Domain Emails, Other Services, Expiry Trackers.

**Activity logs created:**
- Service Provider created, updated, deleted

**Common mistakes:**
- Creating duplicate providers (always search first)
- Entering password in provider record instead of using Vault for shared credentials
- Not setting a module — provider may not appear in right context

**Example:**
```
Name: GoDaddy
Type: Domain Registrar
Website: https://godaddy.com
Cost: 0 (this is the provider, not a service)
Status: Active
```

**How Super Admin uses it:** Creates all providers. Edits any provider. Deletes (blocked if dependent records exist).
**How Admin uses it:** Creates, edits, deletes their own providers (based on module permission).
**How Staff uses it:** Creates, edits, deletes their own providers.

**Important — Delete Protection:**
You cannot delete a Service Provider if it has any of these linked records:
- Hostings
- Domains
- VPS
- VoIP
- Domain Emails
- Other Services
- Expiry Trackers

You must delete or reassign those records first.

---

## 6. Domains

**Purpose:** Manage domain names (example.com, mywebsite.net).

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Name (the domain name).
**Optional fields:** Registration Date, Expiry Date, Auto Renew, Cost, Status, Cloudflare Status, DNS Servers, Notes, Module, Hosting, Service Provider.

**What happens after save:**
- Domain appears on dashboard and calendar (if expiry date set)
- If Hosting is linked, the domain shows on the hosting record
- Activity log is created

**Related modules:** Service Providers, Hosting, Expiry Trackers.

**Activity logs created:**
- Domain created, updated, deleted, restored

**Common mistakes:**
- Entering "www.example.com" instead of "example.com"
- Not linking to a Service Provider — makes renewal tracking harder
- Forgetting to set Expiry Date — calendar will not show it

**Example:**
```
Name: example.com
Registration Date: 2025-01-01
Expiry Date: 2028-01-01
Auto Renew: Yes
Cost: 12.99 (monthly equivalent = 12.99/12 = 1.08)
Status: Active
Service Provider: GoDaddy
```

**How Super Admin uses it:** Creates, views, edits, deletes any domain.
**How Admin uses it:** Creates, views, edits, deletes domains in their accessible modules.
**How Staff uses it:** Creates, views, edits, deletes their own domains.

---

## 7. Hosting

**Purpose:** Manage web hosting accounts.

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Name.
**Optional fields:** Username, Password, cPanel URL, Service Provider, Plan, Domain, Domain IP, Mail Domain IP, cPanel IP, Start Date, Expiry Date, Cost, Status, Notes, Module.

**What happens after save:**
- Hosting appears on dashboard calendar (if expiry date set)
- If linked to a Domain, shows domain details
- Activity log is created

**Related modules:** Service Providers, Domains, Expiry Trackers.

**Activity logs created:**
- Hosting created, updated, deleted, restored

**Common mistakes:**
- Storing shared hosting password in the record instead of Vault (see Vault guide)
- Not linking to Service Provider
- Forgetting Expiry Date — calendar will not show it

**Example:**
```
Name: Company Website Hosting
Service Provider: DigitalOcean
Plan: Basic Droplet
Domain: example.com
Cost: 5.00 (monthly)
Status: Active
Expiry Date: 2026-12-31
```

**How Super Admin/Admin/Staff uses it:** Same as Domains (above).

---

## 8. Domain Emails

**Purpose:** Manage email accounts at your domains (info@company.com, support@company.com).

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Email.
**Optional fields:** Password, Service Provider, Domain, Storage (MB), Cost, Expiry Date, Status, Notes, Module.

**What happens after save:**
- Email appears on dashboard calendar (if expiry date set)
- Activity log is created

**Related modules:** Domains, Service Providers, Expiry Trackers.

**Activity logs created:**
- Domain Email created, updated, deleted, restored

**Common mistakes:**
- Entering full email (info@example.com) but forgetting the @domain part — validator catches this
- Not linking to the parent Domain
- Password not saved because it cannot be retrieved later (see Vault guide)

**Example:**
```
Email: info@company.com
Domain: company.com
Service Provider: Google Workspace
Storage (MB): 30000
Cost: 6.00
Status: Active
```

---

## 9. VPS

**Purpose:** Manage Virtual Private Servers.

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Name.
**Optional fields:** Service Provider, Plan, IP Address, OS, RAM (MB), Disk (GB), CPU Cores, Cost, Start Date, Expiry Date, Department, Location, Login IDs (JSON), Additional IPs (JSON), Status, Notes, Module, Password.

**What happens after save:**
- VPS appears on dashboard calendar (if expiry date set)
- Activity log is created

**Related modules:** Service Providers, Expiry Trackers, Notes.

**Activity logs created:**
- VPS created, updated, deleted, restored

**Common mistakes:**
- Not entering IP address — makes it hard to find later
- Entering cost yearly instead of monthly equivalent
- Storing root password here instead of Vault

**Example:**
```
Name: web-server-01
Service Provider: DigitalOcean
Plan: Basic Droplet 4GB
IP Address: 203.0.113.10
OS: Ubuntu 22.04
RAM (MB): 4096
Disk (GB): 80
CPU Cores: 2
Cost: 24.00 (monthly)
Status: Active
```

---

## 10. VoIP

**Purpose:** Manage Voice over IP phone systems and numbers.

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Name.
**Optional fields:** Service Provider, Phone Number, Type, Direction, Username, Password, Extension Password, Dashboard URL, Server IP, Cost, Start Date, Expiry Date, Status, Number Status, Outbound Code, Team Details, Extension, Notes, Module.

**What happens after save:**
- VoIP appears on dashboard calendar (if expiry date set)
- Activity log is created

**Related modules:** Service Providers, Expiry Trackers.

**Activity logs created:**
- VoIP created, updated, deleted, restored

**Common mistakes:**
- Not setting expiry date — expiry notifications never fire for VoIP (known limitation)
- Confusing phone number with extension

**Example:**
```
Name: Main Office Line
Service Provider: Twilio
Phone Number: +1234567890
Type: SIP Trunk
Direction: Both
Cost: 15.00
Status: Active
```

---

## 11. Other Services

**Purpose:** Manage services that don't fit in other categories (SaaS subscriptions, SSL certificates, monitoring tools, etc.).

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one — this is a catch-all module.

**Required fields:** Name, Service Type (saas, api, monitoring, analytics, cdn, ssl, other).
**Optional fields:** Service Provider, Username, Password, Login URL, Website, Cost, Start Date, Expiry Date, Status, Notes, Module.

**What happens after save:**
- Service appears on dashboard calendar (if expiry date set)
- Activity log is created

**Related modules:** Service Providers, Expiry Trackers.

**Activity logs created:**
- Other Service created, updated, deleted, restored

**Common mistakes:**
- Using this for services that have a dedicated module (e.g., creating a "Domain" here instead of using the Domain module)
- Not selecting the right Service Type — affects filtering and reports

**Example:**
```
Name: Slack Premium
Service Type: saas
Service Provider: Slack
Cost: 15.00
Status: Active
Expiry Date: 2027-06-01
```

---

## 12. Expiry Trackers

**Purpose:** Track when things expire and send email reminders.

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Name.
**Optional fields:** Service Provider, Username, Password, Login URL, Expiry Date, Renewal Date, Cost, Status, Notes, Module, Email Notifications, SMTP Profile, Notify Days, Notify on Expiry Day, Notify Assigned User, Notify Admins, Notify Custom Emails.

**What happens after save:**
- Tracker appears on dashboard Renewals widget
- If email notifications are enabled, the system will send reminders based on Notify Days
- Activity log is created

**How notifications work:**
1. You create an Expiry Tracker with an Expiry Date
2. You select **Notify Days** (e.g., 30 days before, 7 days before)
3. You select an **SMTP Profile** (which email server to send from)
4. You choose recipients: Assigned User, Admins, or Custom Emails
5. Every night, the system checks all trackers and sends emails to those expiring soon
6. You can also click **Send Reminder Now** to send immediately
7. You can click **Test Email** to verify the SMTP setup

**Related modules:** SMTP Profiles, Service Providers (linked via service_provider_id field, but on this tracker, provider is informational), all infrastructure modules.

**Activity logs created:**
- Expiry Tracker created, updated, deleted, restored
- Reminder sent (logged in notification history)
- Test email sent

**Common mistakes:**
- Not selecting an SMTP Profile — notifications will not be sent
- Setting Notify Days but forgetting to enable Email Notifications
- Setting Expiry Date in the past — notifications will fire immediately

**Example:**
```
Name: example.com Domain Renewal
Expiry Date: 2028-01-01
Notify Days: 30, 7, 1
SMTP Profile: Company Gmail SMTP
Email Notifications: Enabled
Notify Admins: Yes
Cost: 12.99
Status: Active
```

---

## 13. SMTP Profiles

**Purpose:** Define email servers that send expiry notifications and test emails.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Required fields:** Name, Sender Email, SMTP Host, SMTP Port, SMTP Username, SMTP Password.
**Optional fields:** Sender Name, Reply-To Email, SMTP Encryption (tls/ssl), Is Default, Is Active, Priority.

**What happens after save:**
- SMTP profile appears in Expiry Tracker dropdowns
- You can test the profile with **Test** button
- You can set it as default (applies to new trackers)
- Activity log is created

**Related modules:** Expiry Trackers.

**Activity logs created:**
- SMTP Profile created, updated, deleted, duplicated
- SMTP Profile tested
- SMTP Profile set as default
- SMTP Profile toggled active/inactive

**Common mistakes:**
- Entering wrong SMTP credentials — test before saving
- Forgetting to toggle Active — inactive profiles cannot send
- Using Gmail without an App Password — Gmail blocks regular passwords
- Not testing after creation

**Example:**
```
Name: Company Gmail SMTP
Sender Name: OpsPilot Notifications
Sender Email: notifications@company.com
SMTP Host: smtp.gmail.com
SMTP Port: 587
Encryption: tls
Username: notifications@company.com
Password: (App Password, not regular Gmail password)
Is Default: Yes
Is Active: Yes
```

---

## 14. Vault

**Purpose:** Store passwords and sensitive credentials securely.

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one — but only store shared credentials here.

**Required fields:** Service Name.
**Optional fields:** Service URL, Username, Password (or Encrypted Password), Module, Description.

**Two types of Vault entries:**
- **My Credentials** (`/my-vault`): Only you can see these
- **Shared Credentials** (`/vault`): Users with module Read permission can see these

**What happens after save:**
- Entry is stored with encrypted password
- Activity log is created
- Reveal is logged (who revealed, when)

**Related modules:** All modules that have password fields.

**Activity logs created:**
- Vault entry created, updated, deleted, restored
- Vault password revealed (with user and timestamp)

**Common mistakes:**
- Storing personal passwords in Shared Credentials
- Forgetting to set Module — shared credentials need module for access control
- Using Vault for passwords that should be in the module record (see below)

**When to use Vault vs Password fields in modules:**

| Store in Module Password Field | Store in Vault |
|-------------------------------|----------------|
| The password is needed to login to that specific service daily | The password is shared across multiple services |
| The password belongs to the record (e.g., hosting control panel) | The password is for a shared account (e.g., root AWS account) |
| You are the only one who needs it | Multiple team members need access |
| Example: cPanel password for a hosting account | Example: Company AWS root credentials |

**Example:**
```
Service Name: Company AWS Root
Service URL: https://aws.amazon.com
Username: admin@company.com
Password: (encrypted)
Module: (none — this is shared)
Description: Root AWS account for billing
```

**How Super Admin uses it:** Can view all vault entries, can reveal any password.
**How Admin uses it:** Can view shared vault entries with module permission, can reveal with reveal permission.
**How Staff uses it:** Can view their own vault entries, can reveal their own passwords.

---

## 15. Assets

**Purpose:** Track physical and digital assets (laptops, monitors, software licenses, etc.).

**Who should use it:** All users (based on permissions).
**Who should not use it:** No one.

**Required fields:** Category, Type, Status.
**Optional fields:** Asset Tag, Serial Number, Assigned To, Location, Department, Issue Date, Return Date, Condition, Specifications, Notes, Primary Image, Vault Entry, QR Identifier, Module.

**What happens after save:**
- Asset appears on dashboard Assets widget
- Activity log is created

**Related modules:** Users (assigned_to), Vault (if linked).

**Activity logs created:**
- Asset created, updated, deleted, restored, force-deleted
- Asset assigned, returned

**Important — Delete Protection:**
You cannot delete an Asset if its status is **Assigned**. You must return it first (set status to Available or Decommissioned).

**Common mistakes:**
- Not changing status when asset is assigned/returned
- Forgetting to assign asset to a user — asset shows as Available but user does not know
- Deleting an assigned asset — system blocks this

**Example:**
```
Asset Tag: LAP-001
Category: Electronics
Type: Laptop
Serial Number: SN12345678
Status: Assigned
Assigned To: Ahmad Raza
Issue Date: 2026-01-15
Condition: Good
```

---

## 16. Tasks

**Purpose:** Track work items, to-dos, and assignments.

**Who should use it:** All users.
**Who should not use it:** No one.

**Required fields:** Title.
**Optional fields:** Description, Module, Status, Priority, Due Date, Assignee(s).

**What happens after save:**
- Task appears on My Tasks and Task Management
- If assignees are set, they get a notification
- Activity log is created

**Related modules:** Users (assignees), Calendar (tasks with due dates show on calendar).

**Activity logs created:**
- Task created, updated, deleted, restored
- Task status changed

**Known Limitation:**
When creating a task from the web interface, the assignee selection feature is not available in the form. Tasks are created without assignees. To set assignees, use the API or database directly. This is a known issue being addressed.

**Common mistakes:**
- Setting due date in the past
- Not setting priority — defaults to Medium
- Closing a task without marking it Completed

**Example:**
```
Title: Update SSL certificate for example.com
Description: The SSL cert expires next month. Need to renew.
Priority: High
Status: Pending
Due Date: 2026-07-15
Module: Domains
```

---

## 17. Notes

**Purpose:** Attach notes to any record in the system (domain, hosting, VPS, feature, module, or global).

**Who should use it:** All users.
**Who should not use it:** No one.

**Required fields:** Content.
**Optional fields:** Notable Type (Feature, Module or none for global), Notable ID.

**What happens after save:**
- Note appears on the Notes page
- If linked to a Feature or Module, appears in that record's notes
- Activity log is created

**Related modules:** Features, Modules.

**Activity logs created:**
- Note created, updated, deleted, restored

**Known Limitation:**
The Notes form sends short strings (e.g., "feature", "module") but the system expects full class names (e.g., "App\Models\Feature"). This means linking notes to specific features/modules via the web form may not work. Use the API or enter notes as global (no link) via the web form.

**Common mistakes:**
- Not saving before navigating away
- Linking to wrong notable type

**Example:**
```
Content: "DigitalOcean droplet web-01 needs OS upgrade to Ubuntu 24.04 before July."
Notable Type: (none — global note)
```

---

## 18. Webhooks

**Purpose:** Send HTTP callbacks to external systems when events happen in OpsPilot.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Required fields:** Name, URL, Events.
**Optional fields:** Is Active.

**What happens after save:**
- When the selected events happen, OpsPilot sends a POST request to the URL with event data
- Activity log is created

**Related modules:** Activity Logs (events are activity log-based).

**Activity logs created:**
- Webhook created, updated, deleted
- Webhook test fired

**Common mistakes:**
- Not testing the webhook after creation
- Making the URL inactive but forgetting to toggle Is Active
- Using HTTP instead of HTTPS

**Example:**
```
Name: Slack Notification
URL: https://hooks.slack.com/services/xxx/yyy/zzz
Events: task_assigned, vault_password_revealed
Is Active: Yes
```

---

## 19. Reports

**Purpose:** View summaries and analytics of your data.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users (no access).

**Categories and reports available:**
- **Financial Reports:** Monthly Cost, Cost by Type, Top Costs
- **Operational Reports:** Task Summary, Login Summary
- **User Reports:** User Activity

**What you can do:**
- View reports on screen
- Export reports to CSV
- Filter by date range, status, user

**Related modules:** All infrastructure modules (cost data), Tasks, Login Audits.

**Activity logs created:** None (read-only).

**Common mistakes:**
- Expecting real-time data — reports reflect data as saved
- Confusing "Total Monthly Cost" with yearly cost

---

## 20. Imports

**Purpose:** Bulk upload data from CSV files.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Importable types (17 total):**
Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets, Vault, Tasks, Users, Notes, Features, Modules, Webhooks, SMTP Profiles.

**Required:** CSV file (max 2MB), select import type.

**What happens after import:**
- Records are created in the selected module
- Activity log is created: "Imported X records of type Y"
- Errors are reported per row

**Safety features:**
- Formula injection prevention (strips =, +, -, @ from values)
- Empty rows are skipped
- Transactions — if one row fails, the whole batch is rolled back
- Password hashing for User imports

**Related modules:** All importable modules.

**Activity logs created:**
- Import event with type and record count

**Common mistakes:**
- CSV column headers do not match system field names
- Including ID, created_at, updated_at columns — these are auto-set
- File too large (over 2MB)

---

## 21. Exports

**Purpose:** Download data as CSV files.

**Who should use it:** Super Admin and users with Export permission on a module.
**Who should not use it:** Users without Export permission.

**Exportable types (19 total):**
All infrastructure modules, plus Tasks, Vault, Users, Assets, Notes.

**What happens:**
- CSV file is downloaded
- Activity log is created: "Exported X records of type Y"

**Related modules:** All exportable modules.

**Activity logs created:**
- Export event with type and record count

**Common mistakes:**
- Exporting without filters — gets all records (could be large)
- Expecting Excel format — exports are CSV only

---

## 22. Activity Logs

**Purpose:** See every change made in the system.

**Who should use it:** Super Admin only (audit purposes).
**Who should not use it:** Regular users.

**What is logged:**
- Every create, update, delete on all modules
- Every login, logout
- Every password reveal
- Every export, import
- Every webhook test
- Every SMTP test
- Every password reset
- Every role/permission change

**What you can do:**
- Filter by event type (created, updated, deleted, etc.)
- Search by keyword
- Filter by user (causer)
- Filter by date range
- View old and new values for updates

**Related modules:** All modules.

**Activity logs created:** N/A — this is the log viewer itself.

**Common mistakes:**
- Expecting to delete logs — Super Admin can only view, logs are retained for audit
- Not checking activity log when troubleshooting who made a change

---

## 23. Login Audits

**Purpose:** See who logged in, when, and whether it was successful.

**Who should use it:** Super Admin only (security purposes).
**Who should not use it:** Regular users.

**What is recorded:**
- Email used
- IP address
- User Agent (browser)
- Success/Failure
- Timestamp

**What you can do:**
- Filter by success/failure
- View login history for specific user
- Delete old audit records (manually)

**Related modules:** Users.

**Common mistakes:**
- Not checking failed logins when investigating a security issue
- Confusing Login Audits with Activity Logs

---

## 24. Attachments

**Purpose:** Upload and manage files attached to records.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Required fields:** File (upload).
**Optional fields:** Notable Type, Notable ID (what record the file belongs to).

**Supported file types:** PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, ZIP.
**Max file size:** 10MB.

**What happens after save:**
- File is stored on the server
- Activity log is created
- File can be downloaded by users with access

**Related modules:** All modules that support attachments.

**Activity logs created:**
- Attachment created, deleted, force-deleted

**Important — File Preservation:**
When an attachment is soft-deleted, the physical file is kept on the server. It is only permanently deleted when Force Delete is used. This means you can restore a deleted attachment and the file will still be available.

**Common mistakes:**
- Uploading files over 10MB — upload fails silently
- Not linking attachment to a record — hard to find later
- Expecting file to be deleted when record is deleted — files persist until force-delete

---

## 25. Tokens (API Access)

**Purpose:** Create API tokens for programmatic access to the system.

**Who should use it:** Super Admin only.
**Who should not use it:** Regular users.

**Required fields:** Token Name.
**Optional fields:** Abilities (permissions for the token).

**What happens after save:**
- API token is generated (shown once — copy it immediately)
- Token can be used for API authentication (Bearer token in Authorization header)
- Activity log is created

**Related modules:** All API endpoints.

**Activity logs created:**
- API token created, revoked (deleted)

**Common mistakes:**
- Not copying the token after creation (cannot be shown again)
- Giving too many abilities to a token
- Hardcoding tokens in application code

**Example:**
```
Token Name: CI/CD Pipeline Token
Abilities: read, create, update
```
