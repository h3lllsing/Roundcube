# OpsPilot Common Mistakes & FAQ

## Common Mistakes

### 1. Not Setting an SMTP Profile on Expiry Trackers

**Mistake:** Creating an Expiry Tracker without selecting an SMTP Profile.

**Result:** Notifications are never sent, even if Email Notifications is enabled.

**Fix:** Edit the tracker → select an SMTP Profile → Save.

### 2. Forgetting to Enable Email Notifications

**Mistake:** Setting Notify Days but forgetting to toggle Email Notifications ON.

**Result:** No emails are sent.

**Fix:** Edit the tracker → enable Email Notifications → Save.

### 3. Setting Expiry Date in the Past

**Mistake:** Entering an expiry date that has already passed.

**Result:** Notifications fire immediately (usually the same night).

**Fix:** Edit the tracker → correct the date → Save.

### 4. SMTP Profile Inactive

**Mistake:** SMTP Profile exists but Is Active is OFF.

**Result:** Expiry Trackers using this profile fail to send.

**Fix:** Go to SMTP Profiles → toggle Active ON → Test.

### 5. Using Regular Gmail Password Instead of App Password

**Mistake:** Entering your regular Gmail password in SMTP settings.

**Result:** "Authentication failed" error.

**Fix:** Generate an App Password from Google Account settings. Use that instead.

### 6. Not Entering Cost

**Mistake:** Leaving Cost field blank on infrastructure records.

**Result:** Dashboard Operations Widget shows incorrect total. Reports are inaccurate.

**Fix:** Edit each record → enter monthly equivalent cost → Save.

### 7. Entering Yearly Cost Instead of Monthly

**Mistake:** Entering $120 for a $120/year domain.

**Result:** Reports show $120/month instead of $10/month.

**Fix:** Divide by 12. Enter the monthly equivalent ($10).

### 8. Creating Duplicate Service Providers

**Mistake:** Three people create "GoDaddy" because they did not search first.

**Result:** Multiple entries for the same provider. Confusion.

**Fix:** Search before creating. Delete duplicates (if no dependencies).

### 9. Not Linking to Service Provider

**Mistake:** Creating a Domain or Hosting without selecting a Service Provider.

**Result:** Hard to track where services are registered. Reports miss provider context.

**Fix:** Edit the record → select the provider → Save.

### 10. Deleting a Service Provider That Has Linked Records

**Mistake:** Trying to delete a provider that has domains, hosting, etc. linked.

**Result:** System blocks the deletion. "Why can I not delete this?"

**Fix:** Delete or reassign all linked records first. Check the error message for details.

### 11. Storing Shared Passwords in Personal Module Records

**Mistake:** Storing the company AWS root password in a VPS record's password field.

**Result:** Only the VPS record owner can see it. Team cannot access.

**Fix:** Store in Shared Vault with appropriate module permissions.

### 12. Not Setting Module on Shared Vault Entry

**Mistake:** Creating a Shared Vault entry without selecting a Module.

**Result:** Only Super Admin can see it. The team cannot access shared credentials.

**Fix:** Edit the entry → select the relevant module → Save.

### 13. Assigning Wrong Role to a User

**Mistake:** Giving Super Admin role to a junior staff member "just to see everything."

**Result:** They can delete records, change permissions, and access sensitive data.

**Fix:** Use Admin role with module permissions instead. Only Super Admin when absolutely needed.

### 14. Revealing Passwords Unnecessarily

**Mistake:** Clicking Reveal "just to check" when you do not actually need the password.

**Result:** Every reveal is logged. Unnecessary reveals in the audit log look suspicious.

**Fix:** Only reveal when you actually need the password.

### 15. Not Checking Activity Logs

**Mistake:** Never reviewing activity logs.

**Result:** Miss unauthorized changes, unexpected deletes, or security issues.

**Fix:** Check Activity Logs daily (Super Admin). At minimum, skim for unusual events.

### 16. Creating a VPS/Domain in "Other Services"

**Mistake:** Using Other Services for something that has a dedicated module.

**Result:** Data is mixed up. Reports and filters do not work correctly.

**Fix:** Use the correct module: Domains → Domains, VPS → VPS, etc.

### 17. Not Testing SMTP After Creating

**Mistake:** Creating an SMTP Profile but not clicking Test.

**Result:** You do not know if it works until an Expiry Tracker fails to send.

**Fix:** Always click Test after creating or editing an SMTP Profile.

### 18. Deleting an Asset That Is Still Assigned

**Mistake:** Trying to delete an asset assigned to a user.

**Result:** System blocks the deletion.

**Fix:** Return the asset first (click Return), then delete.

### 19. Uploading a File Over 10MB as Attachment

**Mistake:** Trying to attach a 50MB video file.

**Result:** Upload fails silently. No error message.

**Fix:** Compress the file or split into smaller parts. Max is 10MB.

### 20. Deleting a Role That Has Users

**Mistake:** Trying to delete a role that is assigned to users.

**Result:** System blocks the deletion.

**Fix:** Reassign all users to a different role first.

---

## Frequently Asked Questions

### General

**Q: I forgot my password. What do I do?**
A: Click **Forgot Password** on the login page. Enter your email. Check your inbox for a reset link. If you do not receive it, contact your Super Admin.

**Q: I cannot login. It says "Account Suspended."**
A: Contact your Super Admin. Your account may have been suspended due to policy or security reasons.

**Q: How do I change my password?**
A: Go to **My Profile** → update password → Save.

**Q: What is the keyboard shortcut for search?**
A: Press `Ctrl+K` to open the command palette. Type to search pages and records.

**Q: Can I use OpsPilot on my phone?**
A: Yes. The interface is responsive and works on mobile browsers.

### Modules

**Q: What is the difference between a Domain and a Hosting record?**
A: A **Domain** is your website address (example.com). **Hosting** is where your website files are stored. They are separate things — you can have a domain without hosting, and hosting without a domain.

**Q: What is the difference between SMTP Profile and Expiry Tracker?**
A: **SMTP Profile** = the email server that sends notifications. **Expiry Tracker** = the reminder configuration. You need both for email reminders to work.

**Q: When should I put a password in the module record vs Vault?**
A: If the password is specific to that one service (e.g., cPanel password for a specific hosting account), store it in the module's password field. If it is shared across multiple services (e.g., AWS root account), store it in Shared Vault.

**Q: Why can I not see some records?**
A: You may not have Read permission on that module, or the records may belong to other users. Check **My Access** to see your permissions.

**Q: Why can I not delete this record?**
A: The system has protections:
- Service Providers with linked records cannot be deleted
- Assigned Assets cannot be deleted
- Roles with assigned users cannot be deleted
Check the error message for details on what needs to be removed first.

### Notifications

**Q: I set up an Expiry Tracker but did not receive any email. Why?**
A: Check these in order:
1. Is Email Notifications enabled on the tracker?
2. Is an SMTP Profile selected on the tracker?
3. Is the SMTP Profile Active?
4. Did you click Test on the SMTP Profile? Did it work?
5. Is the Expiry Date in the future?

**Q: How do I test if expiry emails are working?**
A: Open the Expiry Tracker → click **Test Email**. If you receive it, everything is working.

**Q: Can I send a reminder immediately?**
A: Yes. Open the Expiry Tracker → click **Send Reminder Now**.

**Q: How often does the system check for expiries?**
A: Once daily (via cron job). The command `php artisan check-expiries` runs automatically if cron is configured.

### Users & Permissions

**Q: How do I add a new team member?**
A: Ask your Super Admin. Only Super Admin can create users.

**Q: How do I get more permissions?**
A: Contact your Super Admin. They can update role permissions or create user overrides.

**Q: Can I have multiple roles?**
A: Yes. A user can have multiple roles. The system gives you the highest level of access from all your roles.

**Q: What is the difference between User and Admin roles?**
A: **User** sees all records in modules where Read permission is granted — not just their own records. **Admin** sees records based on module permissions (can see all records in modules they have Read access to, same as User). The difference is that Admin typically gets Create/Update/Delete permissions too, while IT Staff / User may only have Read.

**Q: What happens when an employee leaves?**
A: The Super Admin should:
1. Suspend the user account
2. Transfer their tasks
3. Return their assets
4. Review vault access
5. Check audit logs

### Password Reveal

**Q: Who can see my revealed passwords?**
A: Only you see the password on screen. But the reveal action is logged and your Super Admin can see who revealed what and when.

**Q: Can I copy a password instead of typing it?**
A: If your browser supports it, you can select and copy the revealed text.

**Q: Why is the Reveal button not working?**
A: Reveal is throttled to 10 attempts per minute. Wait a moment and try again.

### Data & Reports

**Q: How do I export my data?**
A: If you have Export permission, go to **Export** → select the type → download CSV. Some modules also have export options in their list view.

**Q: How do I import data?**
A: Only Super Admin can import. Go to **Import** → select type → upload CSV file (max 2MB).

**Q: Why is the total monthly cost on the dashboard wrong?**
A: Some records may be missing cost values, or costs may be entered as yearly instead of monthly. Check your records and fix any missing or incorrect costs.

**Q: Can I get reports emailed to me?**
A: Not automatically. You need to visit the Reports page and export manually.

### Technical

**Q: Which browsers are supported?**
A: Modern versions of Chrome, Firefox, Edge, and Safari.

**Q: The page looks broken. What should I do?**
A: Try refreshing the page. If it persists, clear your browser cache. If still broken, report to your Super Admin.

**Q: How do I access the API?**
A: API documentation is available at `/docs`. API tokens are managed by Super Admin in **Administration → API Access**.

**Q: Is there a mobile app?**
A: No native app. The web interface works on mobile browsers.

**Q: How often is data backed up?**
A: Ask your Super Admin. Backup frequency depends on server configuration, not OpsPilot itself.
