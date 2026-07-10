# OpsPilot IT Support Guide

## Who is IT Support?

IT Support staff typically have **User** role with permissions on specific modules. You can:

- View operational records in modules where Read permission is granted
- Create, edit, or delete records only where specifically permitted by module permissions
- Create and update data relevant to your work

You **cannot**:
- See other people's records (unless you share a module)
- Manage users or permissions
- Access Administration modules

---

## What You Can Do

### Records You Own

When you create a record (domain, hosting, VPS, etc.), it belongs to you. The system sets `user_id` to your account. You can:

- View your records
- Edit your records
- Delete your records (unless protected)

### Records You Can See

If your team uses module-based access (Admin role), you might see others' records. If not, you only see your own.

Check **My Access** in the sidebar to see your permissions.

---

## Daily Tasks

### 1. Check My Tasks
- Go to **My Tasks** 
- Review tasks assigned to you
- Update status as you complete work

### 2. Update Your Records
- Keep domain/hosting/VPS records up to date
- Add notes when you make changes
- Update expiry dates after renewals

### 3. Check Calendar
- Go to **Calendar**
- See upcoming expiries for your records
- Plan renewals in advance

---

## Creating Records

### When You Create a Domain
1. Go to **Domains → Create**
2. Enter the domain name
3. Link to a Service Provider (so others know who it is from)
4. Enter cost, expiry date
5. Click Save

### When You Create a Hosting Record
1. Go to **Hosting → Create**
2. Enter the hosting name
3. Link to Service Provider and Domain (if applicable)
4. Enter cPanel URL and credentials
5. Click Save

### When You Create an Expiry Tracker
1. Go to **Renewals → Create**
2. Give it a clear name (e.g., "example.com SSL Cert Renewal")
3. Set the expiry date
4. Select an SMTP Profile (if available to you)
5. Enable Email Notifications if you want reminders
6. Click Save

---

## Editing Records

1. Find the record (use Search or navigate to the module)
2. Click the record to open it
3. Click **Edit**
4. Make your changes
5. Click **Save**

**Tip:** If you cannot find a record, it may belong to another user. Use Search or ask your Admin.

---

## What Happens After You Save

- The record is updated immediately
- An activity log entry is created: "User X updated record Y"
- Dashboard widgets update (may take a few seconds)
- Calendar updates with new dates
- Expiry notifications adjust based on new dates

---

## Using Search

You can search from:
1. **Search page** — go to Search in sidebar
2. **Command palette** — press Ctrl+K, type what you need

Search looks across all modules you have access to.

---

## Common Tasks

### How to Reveal a Password
1. Open the record (e.g., Hosting)
2. Click the password field or reveal button
3. Password appears in plain text
4. This action is logged

### How to Add a Note
1. Go to **Notes → Create**
2. Write your note
3. To link to a specific record, select Notable Type and Notable ID
4. Click Save

### How to View Calendar
1. Go to **Calendar**
2. See upcoming events for your records
3. Click on an event to view details

### How to Export Data
If you have Export permission:
1. Go to **Export** or use module export option
2. Select the type of data
3. Download CSV

---

## What to Do When...

### I Cannot Find My Record
- Check you are in the correct module
- Use Search
- You may not have created it — ask your Admin

### I Need to Delete a Record But Cannot
- The record may have dependencies (linked services)
- Check the error message
- Delete dependent records first
- If still blocked, ask your Admin

### I Forgot My Password
- On the login page, click **Forgot Password**
- Check your email for reset link
- Follow the instructions

### I See an Error Message
- Note the error text
- Check your input (required fields, format)
- Try again
- If persists, report to your Admin

---

## Best Practices

### For Your Records
- Always enter cost (feeds reports and dashboard)
- Always link to Service Providers
- Always set expiry dates (enables notifications)
- Use clear names (not "server1" but "Company Website Server")

### For Passwords
- Store service-specific passwords in the module record
- Store shared credentials in Vault
- Never share your login password

### For Tasks
- Check your tasks daily
- Update status when you start working
- Mark as Complete when done

### For Notes
- Add notes when you make important changes
- Include dates and context
- Example: "2026-06-27: Renewed SSL certificate for example.com"

---

## What You Cannot Do (and Why)

| Cannot Do | Reason |
|-----------|--------|
| See other users' records | Privacy and data ownership |
| Create users | Only Super Admin can create accounts |
| Change permissions | Only Super Admin manages access |
| View activity logs | System audit — Super Admin only |
| Access Administration menu | These require Super Admin role |
| Delete shared records | They belong to another user or are protected |
