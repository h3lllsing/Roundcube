# OpsPilot Admin / IT Manager Guide

## Who is an Admin?

An **Admin** (or IT Manager) has elevated access but is not a Super Admin. You can:

- See records across modules (based on permissions)
- Create, edit, delete infrastructure records
- Manage your team's data
- Export data
- Reveal passwords (if permitted)

You **cannot**:
- Create or delete users
- Change roles or permissions
- View activity logs or login audits
- Manage SMTP profiles, webhooks, tokens
- Access Administration or Reports sections

---

## What You Can Do

### Infrastructure Management

| Module | What You Can Do |
|--------|----------------|
| Service Providers | Create, view, edit, delete your providers |
| Domains | Create, view, edit, delete domains in your modules |
| Hosting | Create, view, edit, delete hosting records |
| VPS | Create, view, edit, delete VPS records |
| VoIP | Create, view, edit, delete VoIP records |
| Domain Emails | Create, view, edit, delete domain emails |
| Other Services | Create, view, edit, delete other services |
| Expiry Trackers | Create, view, edit, delete trackers |
| Assets | Create, view, edit, delete assets |
| Task Management | Create, view, edit, delete tasks |
| Notes | Create, view, edit, delete notes |
| Vault (Shared) | View shared credentials, reveal (if permitted) |
| Vault (My) | Manage your own credentials |

### Operations

| Feature | What You Can Do |
|---------|----------------|
| Dashboard | View all widgets with your accessible data |
| Calendar | View upcoming expiries and tasks |
| Search | Search across all accessible modules |

---

## Module Visibility

As an Admin, you see records based on **Module Permissions** set by the Super Admin.

**Example:**
```
You have Read permission on: Domains, Hosting, VPS
You have Create permission on: Domains, Hosting
You have Update permission on: Domains, Hosting
You have Delete permission on: Domains (not Hosting)

Result:
✓ You see ALL domains and hosting records (not just yours)
✓ You can create domains and hosting
✓ You can edit domains and hosting
✓ You can delete domains but NOT hosting
✗ You cannot see VoIP records (no Read permission)
```

---

## Daily Tasks

### 1. Check Dashboard
- Review **Operations Widget** for total active services
- Check **Renewals Widget** for upcoming renewals
- Check **Tasks Widget** for your pending tasks
- Review **Assets Widget** for newly assigned assets

### 2. Review Expiry Trackers
- Go to **Renewals**
- Check which services are expiring soon
- Ensure Expiry Trackers have SMTP Profiles set
- Click **Test Email** on any new trackers

### 3. Monitor Tasks
- Go to **My Tasks** to see your assigned tasks
- Go to **Task Management** to see team tasks
- Update status as work progresses

### 4. Keep Records Updated
- Update expiry dates after renewals
- Update costs when pricing changes
- Update status when services change

---

## Weekly Tasks

### 1. Review Your Module Data
- Check for incomplete records (missing costs, missing expiry dates)
- Verify service providers are linked correctly
- Clean up duplicate records

### 2. Export Data (if needed)
- Go to **Export** or use module export features
- Download CSV reports for your records

### 3. Check Calendar
- Review the coming month
- Identify services that need action

---

## What to Do When...

### You Cannot See a Record
- You may not have Read permission on that module
- Contact your Super Admin to check your permissions

### You Cannot Delete a Record
- Check if the record has dependencies (e.g., Service Provider has linked domains)
- Contact Super Admin if you believe it should be deletable

### You Need a User Created
- You cannot create users — ask your Super Admin

### You Need Different Permissions
- Contact your Super Admin to update Module Permissions or create User Overrides

### A Service Provider Is Blocking Deletion
- The system shows which records depend on it
- Delete or reassign those records first

---

## Best Practices

### Data Quality
- Always enter Cost (monthly equivalent) — it feeds reports
- Always link records to Service Providers
- Always set Expiry Dates — enables calendar and notifications

### Organization
- Use consistent naming conventions (e.g., "Company Website Hosting" not "webhost1")
- Keep Notes up to date
- Use the correct module — do not create domains in Other Services

### Security
- Do not share your password
- Reveal passwords only when needed (every reveal is logged)
- Report suspicious activity to Super Admin

### Communication
- Notify Super Admin when you change critical records
- Document changes in Notes when appropriate
- Create Tasks for work that needs to be tracked

---

## Workflow Example: Managing a Domain Renewal

1. Dashboard shows domain expiring in 30 days
2. Go to **Domains** → find the domain
3. Verify expiry date is correct
4. Check if Expiry Tracker exists for this domain
5. If not, go to **Renewals → Create** and add one
6. Process renewal on provider's website
7. Update expiry date in OpsPilot
8. Add a note: "Domain renewed for 1 year"

---

## What You Cannot Do (and Why)

| Cannot Do | Reason |
|-----------|--------|
| Create users | User management requires Super Admin to control access |
| View activity logs | Logs contain all user activity — Super Admin only |
| Change permissions | Permission changes affect all users — Super Admin only |
| Manage SMTP | SMTP credentials are sensitive — Super Admin only |
| View all reports | Some reports contain system-wide data |
| Manage webhooks | Webhooks affect system integrations |
| Force-delete records | Permanent deletion needs Super Admin oversight |
