# User Guide — OpsPilot v1.0.0

## Getting Started

### Logging In
1. Navigate to your organization's Tyro RBAC URL
2. Enter your email and password
3. Click **Login**

### First Login
- Verify your profile information under **Account → Profile**
- Change your password if needed
- Review your assigned roles and permissions under **Account → My Permissions**

### Logging Out
Click your name in the top-right corner → **Logout**

---

## Navigation

### Sidebar Menu
The main navigation is on the left sidebar, organized into groups:
- **Services** — Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers
- **Credentials** — Vault, Assets
- **Work** — Tasks (Kanban), Search, Calendar
- **Reports** — Enterprise Reporting Center
- **Account** — Profile, My Permissions, API Tokens
- **Administration** — Users, Activity Logs, SMTP Profiles, Notifications, Webhooks, Monitoring
- **RBAC** — Roles, Modules, Features, Role Templates

### Quick Search (Cmd+K / Ctrl+K)
Press `Ctrl+K` (Windows/Linux) or `Cmd+K` (Mac) to open the command palette. Type to search across all modules — domains, hosting, tasks, users, assets, reports, and more.

### Collapsing the Sidebar
Click the hamburger menu (☰) in the top-left corner to collapse/expand the sidebar. Your preference is saved.

---

## Working with Resources

### Viewing a Resource List
- Each module has an index page showing all records in a table
- Use the search box to filter results
- Pagination controls at the bottom
- Use the **Bulk Actions** dropdown to perform operations on multiple items

### Creating a New Record
1. Navigate to the module (e.g., **Services → Domains**)
2. Click **Create** or **+ New**
3. Fill in the required fields (marked with *)
4. Click **Save**

### Editing a Record
1. Find the record and click **Edit** (pencil icon)
2. Update the fields
3. Click **Update**

### Deleting a Record
1. Find the record and click **Delete** (trash icon)
2. Confirm deletion
3. Records are soft-deleted (moved to trash)
4. To permanently delete, view the trash and use **Force Delete**

---

## Task Management

### Creating Tasks
1. Go to **Work → Tasks**
2. Click **Create Task**
3. Set title, description, priority (Low/Medium/High/Urgent)
4. Set due date
5. Assign to one or more users
6. Click **Save**

### Using the Kanban Board
1. Go to **Work → Tasks → Kanban Board**
2. Drag and drop tasks between columns: **To Do → In Progress → Done**
3. Click a task card to view details

### My Tasks
Click **My Tasks** to filter and show only tasks assigned to you.

---

## Password Vault

### Creating a Vault Entry
1. Go to **Credentials → Vault**
2. Click **Create Entry**
3. Enter title, URL, username, password
4. Optionally scope visibility to specific modules
5. Click **Save**

### Viewing a Password
1. Go to **Credentials → Vault**
2. Click **Reveal** (eye icon) on an entry
3. The password is displayed temporarily
4. All reveals are audited in the activity log

---

## Assets

### Viewing Assets
1. Go to **Credentials → Assets**
2. Browse the asset list with category, type, location, status info
3. Use filters to narrow down by category, status, or department

### Checking Out an Asset
1. Go to the asset detail page
2. Click **Assign**
3. Select the assignee
4. The assignment is recorded with a timestamp

### Returning an Asset
1. Go to the asset detail page
2. Click **Return**
3. The return date is recorded

---

## Reports

Access reports from the **Reports** sidebar. Each category lists available reports.

### Running a Report
1. Click a report category (e.g., **Domains**)
2. Click a report (e.g., **Active Domains**)
3. Optionally filter by date range
4. View the results in a table

### Exporting to CSV
1. Run a report
2. Click **Export CSV**
3. The file downloads automatically with a UTF-8 BOM for Excel compatibility

---

## Profile & Settings

### Updating Your Profile
1. Go to **Account → Profile**
2. Update name, email, or password
3. Click **Save**

### Viewing Your Permissions
1. Go to **Account → My Permissions**
2. See the modules you can access and what actions you can perform

### API Tokens
1. Go to **Account → API Tokens**
2. Create a new token with a name
3. Copy the token immediately (it won't be shown again)
4. Use the token in the `Authorization: Bearer <token>` header

---

## Notifications

### In-App Notifications
- A bell icon in the top bar shows unread notification count
- Click the bell to see recent notifications
- Click a notification to view the related resource

### Marking Notifications Read
- Click the checkmark on individual notifications
- Use **Mark All Read** to clear all

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+K` / `Cmd+K` | Open global search palette |
| `Esc` | Close search palette / modal |
| `Ctrl+Enter` | Submit current form |
