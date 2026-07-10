# Administrator Guide — OpsPilot v1.0.0

## Table of Contents

1. [Dashboard](#dashboard)
2. [User Management](#user-management)
3. [Role-Based Access Control](#role-based-access-control)
4. [Services Management](#services-management)
5. [Asset Management](#asset-management)
6. [Task Management](#task-management)
7. [Password Vault](#password-vault)
8. [Reports](#reports)
9. [Notifications & SMTP](#notifications--smtp)
10. [Global Search](#global-search)
11. [System Administration](#system-administration)

---

## Dashboard

The enterprise dashboard displays 9 widget cards:
- **Operations** — total domains, hostings, VPS, VoIP, monthly cost (with cost chart)
- **Renewals** — upcoming renewals, overdue items, next expiry countdown
- **Tasks** — pending/overdue tasks, task completion rate
- **Assets** — total assets, assigned/returned today, status breakdown
- **Quick Actions** — one-click links to create resources, run reports
- **Activity** — recent system activity timeline
- **Vault** — recent password reveals (audit trail)
- **SMTP** — SMTP profile status, last test result
- **Server Health** — environment info, PHP version, DB stats, queue size

Each widget card has a "View Full Report →" deep-link to the corresponding report.

## User Management

Access: **Administration → Users**

- **Create**: Fill in name, email, password, assign roles
- **Edit**: Update profile info, change roles
- **Suspend/Unsuspend**: Temporarily block user access
- **Clone**: Duplicate a user with identical roles (useful for onboarding)
- **Delete**: Soft-delete (trash); restore or force-delete from trash
- **Bulk Actions**: Select multiple users → suspend, unsuspend, delete (super-admin only)
- **Search**: By name or email; filter by role

### Super-Admin Protection
- Last super-admin cannot be deleted
- Super-admin role cannot be removed from self
- `role:super-admin` bypasses all module-level permission checks

## Role-Based Access Control

Access: **RBAC**

### Roles
- Create/edit/delete roles with descriptive names
- Assign module-level permissions (Create, Read, Update, Delete, Reveal)
- Pre-seeded templates: Super Admin, Admin, Customer, Editor, User

### Modules
- Organized under Features (e.g., "Services" → Domains, Hosting, VPS)
- Each module has 5 permission flags

### User-Level Overrides
- Override a user's role permissions for specific modules
- Use "Module Permissions" on the user show/edit page

### Role Templates
- Pre-configured permission sets for quick role creation
- Apply a template when creating a new role

## Services Management

Access: **Services** sidebar group

### Service Providers
Create provider profiles with contact info, website, notes.

### Domains
Registration/expiry dates, cost, status, auto-renew, DNS servers, Cloudflare status. Linked to service providers and hosting accounts.

### Hosting
Plan details, cPanel URL, IP/domain, credentials (encrypted), linked to service provider.

### VPS
IP, OS, RAM/Disk/CPU specs, login IDs, additional IPs, department, location.

### VoIP
Phone number, server IP, credentials.

### Domain Emails
Email accounts with passwords.

### Other Services
Catch-all for non-standard services.

### Expiry Trackers
Track any service/credential that has an expiry date. Configure notification rules per tracker.

## Asset Management

Access: **Credentials → Assets**

### Taxonomy
- **Categories**: Laptop, Headphone, Mouse, Network Device, etc.
- **Types**: Specific models within categories
- **Locations**: Where assets are stored

### Asset Lifecycle
1. Create asset with tag, serial number, category, type, location, purchase date, warranty, status
2. Assign to a user (recorded with timestamp)
3. Track assignment history
4. Return asset (record return date)

### Bulk Actions
- Update status, delete, restore, force-delete across multiple assets

## Task Management

Access: **Work → Tasks**

- **Create**: Title, description, status, priority, due date, assignees
- **Kanban Board**: Drag-and-drop between status columns (To Do → In Progress → Done)
- **My Tasks**: Filter to show only assigned tasks
- **Notifications**: Task assignment notifications sent to assignees

### Status Workflow
To Do → In Progress → Done (with optional custom statuses)

## Password Vault

Access: **Credentials → Vault**

- **Create entry**: Title, URL, username, password (encrypted at rest), notes, module scope
- **Reveal**: Click to view password (audited in activity log)
- **Scope**: Restrict entry visibility to specific modules
- **Delete**: Remove entries permanently

## Reports

Access: **Reports** (sidebar)

### Categories
1. **Domains** — Active Domains, Expiring Domains (30 days), Expired Domains
2. **Hosting** — Active Accounts, Expiring Accounts (30 days), Expired Accounts
3. **VPS** — Active VPS, Expiring VPS (30 days), Expired VPS
4. **Renewals** — Due Today, Next 30 Days, Overdue
5. **Assets** — Assigned Assets, Available Assets, By Department
6. **Tasks** — Pending Tasks, Overdue Tasks
7. **Users** — Active Users, Suspended Users

Each report supports CSV export.

## Notifications & SMTP

Access: **Administration → SMTP Profiles**

### SMTP Profiles
- Create profiles with server, port, encryption, credentials
- Test SMTP connection
- Set default profile
- Toggle active/inactive
- Duplicate existing profiles

### Notification Rules
Per expiry tracker:
- `notify_days_before`: array of days (e.g., [7, 3, 1])
- `notify_on_expiry_day`: send on the expiry day
- `notify_assigned_user`: notify the assigned user
- `notify_admins`: notify all admins
- `notify_custom_emails`: additional email recipients

### Notification History
Review all sent notifications with success/failure status per tracker.

### In-App Notifications
- Task assigned
- Note added
- Vault password revealed
- Monitor failure
- Expiring soon

## Global Search

Access: `Ctrl+K` / `Cmd+K` palette from anywhere, or **Work → Search**

Searches across 17 module types with relevance ordering (exact > starts-with > contains). Filter by category: All, Services, Assets, Tasks, Vault, Users.

## System Administration

### Activity Logs
Access: **Administration → Activity Logs**
Full audit trail of all model events (created, updated, deleted, restored).

### Calendar
Access: **Work → Calendar**
Combined view of tasks (by due date) and expiry trackers (by expiry date).

### Webhooks
Access: **Administration → Webhooks**
Configure HTTP callbacks triggered on resource events (created, updated, deleted).

### Monitoring
Access: **Administration → Monitoring**
URL ping checks with status tracking.

### API Tokens
Access: **Account → API Tokens**
Create and manage Sanctum tokens for API access.

### Swagger API Docs
Access: `/api/documentation`
Full OpenAPI documentation for all API endpoints.
