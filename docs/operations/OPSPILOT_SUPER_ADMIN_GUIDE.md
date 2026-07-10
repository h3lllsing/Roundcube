# OpsPilot Super Admin Guide

## Who is a Super Admin?

Super Admin is the highest role in OpsPilot. As a Super Admin, you have:

- **Full access** to every module and every record
- **No restrictions** — you see all data, you can do everything
- **Management powers** — you create users, roles, and permissions
- **Audit responsibility** — all your actions are logged

---

## What Only Super Admin Can Do

| Task | Where |
|------|-------|
| Create, edit, delete users | Users module |
| Create, edit, delete roles | Roles module |
| Manage module permissions | Module Permissions |
| Create, edit, delete features | Features module |
| Create, edit, delete modules | Modules module |
| Create, edit, delete privileges | Privileges module |
| View activity logs | Activity Logs |
| View login audits | Login Audits |
| Create, edit, delete SMTP profiles | SMTP Profiles |
| Create, edit, delete webhooks | Webhooks |
| Manage attachments | Attachments |
| Import data | Import |
| Generate API tokens | Tokens |
| View all reports | Reports |
| Restore soft-deleted records | All modules (restore buttons) |
| Force-delete records | Modules with force-delete routes |

---

## Daily Responsibilities

### 1. Monitor Activity Logs
- Go to **Activity Logs** daily
- Check for unusual activity:
  - Unexpected deletes
  - Password reveals at odd hours
  - Multiple failed operations
- Filter by date to see what happened today

### 2. Monitor Login Audits
- Go to **Login Audits** daily
- Check for failed login attempts
- Investigate repeated failures from unknown IPs
- Delete old audit records periodically

### 3. Check Dashboard
- Review all widgets for issues
- Check **Renewals Widget** for failed notifications
- Check **Server Health Widget** for disk space, database status
- Check **SMTP Widget** for profile failures

### 4. Review Users
- Check for inactive users who should be suspended
- Verify new users have correct roles
- Process suspension requests

---

## Weekly Responsibilities

### 1. Review Permissions
- Go to **Module Permissions**
- Verify each role still has the correct permissions
- Adjust permissions as team roles change

### 2. Export Data
- Export data for backup purposes
- Store exports securely

### 3. Review Reports
- Go to **Reports**
- Check financial summaries
- Review task completion rates
- Check login success rates

### 4. Backup Database
- Ensure database backups are running
- Test a restore if possible

---

## Important: Super Admin Safety Rules

### Rule 1: Your Actions Are Logged
Everything you do is recorded in Activity Logs. There is no way to operate invisibly. If you delete a record, change a permission, or reveal a password, it is logged with your name and timestamp.

### Rule 2: Do Not Give Super Admin Casually
Only give Super Admin to people who:
- Are responsible for the entire system
- Understand the audit trail
- Need to manage users, roles, and permissions

For everyone else, use **Admin** or **User** roles with specific module permissions.

### Rule 3: Check Before Deleting
The system has protections:
- Cannot delete a Service Provider with dependent records
- Cannot delete an Asset that is assigned
- Cannot delete a Role with assigned users

These protections exist for a reason. Do not force-delete unless you are absolutely sure.

### Rule 4: Handle Deletion Carefully

**Soft delete** (default): The record is hidden but can be restored.
**Force delete**: The record is permanently removed.

Use soft delete first. Only force delete when you are certain the record is no longer needed and has no dependencies.

### Rule 5: Monitor Your Own Account
Since you have the highest access, your account is the biggest target:
- Use a strong password
- Do not share your password
- Check your own login audits
- If you suspect compromise, change password immediately

---

## Creating a New User (Best Practices)

1. Go to **Users → Create**
2. Enter the user's details
3. **Assign the minimal role needed.** Start with "User" and add permissions only as needed.
4. Use **Module Permissions** to give access to specific modules
5. Do NOT assign Super Admin unless the person needs full system access
6. Tell the user to change their password on first login

---

## Handling Support Requests

When a user asks for help:

| Request | Action |
|---------|--------|
| "I cannot see module X" | Check their role's Module Permissions for Read access |
| "I cannot create records" | Check their role's Module Permissions for Create access |
| "I need more permissions" | Update Module Permissions or create User Override |
| "I forgot my password" | Use Forgot Password on login page |
| "My account is locked" | Check if they are suspended. Unsuspend if needed. |
| "I need data exported" | Check if they have Export permission. Export for them if not. |

---

## Restoring Deleted Records

If a user accidentally deletes a record:

1. Go to the deleted record's module (e.g., Domains)
2. The record may not show in the list (it is soft-deleted)
3. Currently, restore must be done via API or direct database query
4. Check **Activity Logs** to see who deleted it and when

---

## Force-Delete

Force-delete permanently removes a record. Only use when:

- The record contains sensitive data that should not exist
- The record was created by mistake (e.g., test data)
- The record has been soft-deleted and you need to free up storage (attachments)

To force-delete:
1. Open the record's detail page
2. Look for **Force Delete** button (usually after soft-delete)
3. Confirm the action

---

## Audit Checklist (Monthly)

- [ ] Review all activity logs for suspicious activity
- [ ] Review login audits for failed attempts
- [ ] Verify all SMTP profiles are working (test each)
- [ ] Check scheduled task (cron) is running
- [ ] Verify database backups are completing
- [ ] Clean up old login audits
- [ ] Review user list — suspend inactive accounts
- [ ] Review module permissions — adjust as needed
- [ ] Export critical data for offsite backup
- [ ] Review Vault entries — remove outdated ones
