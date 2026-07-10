# Problem Resolution Guide

> **Audience:** All Users — **Purpose:** Diagnose and resolve common issues encountered while using the portal

## Table of Contents

- [Login Problems](#login-problems)
- [Permission Problems](#permission-problems)
- [Data Visibility Problems](#data-visibility-problems)
- [Password Problems](#password-problems)
- [Module Access Problems](#module-access-problems)
- [Error Messages](#error-messages)

---

## Login Problems

### "Your account has been suspended."

**Problem:** You enter your credentials and see "Your account has been suspended."

**Cause:** A Super Admin has suspended your account. This is a temporary deactivation that preserves all your data.

**Procedure:**
1. Contact your Super Admin directly (email, chat, phone)
2. Ask them to **unsuspend** your account
3. Once unsuspended, you can log in normally

> Do not create a new account — your data (tasks, notes, records) is linked to your existing account.

---

### "The provided credentials do not match our records."

**Problem:** After entering your email and password, the system says your credentials are wrong.

**Possible Causes:**
- Typo in email or password
- Caps Lock turned on (passwords are case-sensitive)
- You forgot your password
- Someone changed your password

**Procedure:**
1. Check that Caps Lock is off
2. Try typing your email and password carefully
3. If it still fails, click **"Forgot your password?"** (if available)
4. Enter your email address
5. Check your inbox (and spam folder) for a reset link
6. Set a new password
7. Log in with the new password

**If the "Forgot your password?" link is not available:**
- The feature may not be enabled by your organization
- Contact your Super Admin to reset your password

---

### Repeated login failures

**Problem:** You tried multiple times and now the system is not responding or is slow.

**Cause:** The login page is rate-limited. After 5 failed attempts in a short period, the system temporarily blocks further attempts.

**Procedure:**
1. Wait at least 1 minute before trying again
2. Use the password reset instead of guessing (if available)
3. If you continue to fail, contact your Super Admin

---

### Can log in but see a blank page

**Problem:** You can log in successfully but the page is empty — no sidebar, no data, nothing useful.

**Cause:** Your account has no module permissions assigned. This happens if no role is assigned, or the role has no View permission on any module.

**Procedure:**
1. Navigate to **My Access** (if the sidebar is not visible, use `Ctrl+K` and type "My Access" or "Permissions")
2. Check your assigned role and permissions
3. If all permissions are empty, contact your Super Admin
4. Ask them to assign you a role with appropriate View permissions

---

## Permission Problems

### Why can't I edit a record?

**Problem:** You can see a record but the Edit button is missing, or clicking Edit gives a permission error.

**Procedure:**
1. Go to **My Access** (sidebar → Account → My Access)
2. Find the module in question
3. Check the **Edit** column — is it On, Off, or Unset?
4. If Edit is Off or Unset (and your role denies it), you lack permission
5. Contact your Super Admin if you need Edit access

> **Edit permission is independent of View.** Having View does not grant Edit. They must be granted separately.

---

### Why can't I delete a record?

**Problem:** The Delete button is missing or returns an error.

**Procedure:**
1. Check **My Access** for that module
2. Look at the **Delete** column
3. If Delete is Off, your role has it disabled
4. The default IT Support role has Delete = Off
5. Contact your Super Admin if you need Delete access

> Records are soft-deleted when removed. Super Admin can restore them.

---

### Why can't I see a module in my sidebar?

**Problem:** A module that other team members can see is not showing in your sidebar.

**Procedure:**
1. Go to **My Access**
2. Find the module in the list
3. Check the **View** column
4. If View is Off or Unset, you do not have access
5. Contact your Super Admin to request View permission

> **Modules without View are completely hidden.** You cannot access them via direct URL either.

---

### Why can't I see records I created?

**Problem:** You created a record (domain, hosting, note) but now you cannot find it.

**Possible Causes:**
1. You lack **View** permission on that module
2. The record was moved to a module you cannot access
3. The record was deleted

**Procedure:**
1. Check **My Access** — do you have View on that module?
2. If View is Off, that is the cause: without View, you see NO records in that module — not even your own
3. Ask your Super Admin to grant View permission on the module
4. If View is On, use search to find the record — it may be in a different module

---

### How do I check what permissions I have?

**Procedure:**
1. Go to **My Access** in the sidebar (under Account)
2. The page shows:
   - Your assigned role(s)
   - A table of all modules with your effective permissions
   - Whether each permission comes from your role (default) or a user override
3. Check the columns for each module you need to use

> **Override indicator:** If a permission shows "(Override)" next to it, a Super Admin has set it specifically for you.

---

## Data Visibility Problems

### Why can I see ALL records in a module?

**Problem:** You can see records created by everyone, not just your own.

**Cause (not a problem):** Infrastructure modules use **module-wide access**. If you have View permission on an infrastructure module (Domains, Hosting, VPS, etc.), you see EVERY record in that module — not just records you created.

**This is by design.** It allows team members to see and work on the same records.

> **Exception:** Personal modules (Vault, Notes) show only your own entries by default.

---

### Why can't I see records that I know exist?

**Problem:** Your colleague can see a record, but it does not appear when you search or browse.

**Possible Causes:**
1. You lack View permission on the module containing the record
2. The record is in a different module than you expect
3. The record was soft-deleted (trashed)
4. The record is in a module you can access, but it is filtered out

**Procedure:**
1. Verify you have View on the relevant module (check My Access)
2. Ask your colleague which module the record is in
3. If the record was deleted, a Super Admin can restore it
4. Clear any filters you may have applied on the index page

---

### How do I view soft-deleted records?

**Procedure:**
1. Navigate to the module's index page
2. Look for a **Trashed** or **Deleted** filter option
3. Enable it to show soft-deleted records
4. If you are a Super Admin, you will see a **Restore** button

> **Non-Super Admin users:** You can see trashed records only if your role has special permission. By default, only Super Admin can view and restore trashed records.

---

## Password Problems

### Why can't I reveal a password?

**Problem:** The Reveal button is missing or greyed out on a record.

**Cause:** You lack **Reveal** permission on that module. Reveal is a separate permission from View — having View does not grant Reveal.

**Procedure:**
1. Go to **My Access**
2. Find the module
3. Check the **Reveal** column
4. If it is Off or Unset, you cannot reveal passwords
5. Contact your Super Admin to request Reveal permission

> **Default role values:**
> - IT Support: Reveal = On (on 6 modules)
> - Administrator: Reveal = Off (must be explicitly granted)
> - Read Only: Reveal = Off

---

### "Rate limit exceeded" when revealing passwords

**Problem:** You click Reveal and see a rate limit error.

**Cause:** Password reveal is rate-limited to 10 attempts per minute. This prevents automated scraping of passwords.

**Procedure:**
1. Wait 60 seconds
2. Try the reveal again
3. If you need to reveal multiple passwords, do them one at a time with pauses

---

### I forgot my password

**Problem:** You cannot log in because you forgot your password.

**Procedure:**
1. On the login page, click **"Forgot your password?"** (if available)
2. Enter your email address
3. Check your inbox for a reset link
4. Click the link and set a new password
5. Log in with the new password

**If the reset link does not arrive:**
1. Check your spam/junk folder
2. Verify you entered the correct email address associated with your account
3. The link expires after a set period — request a new one
4. If the feature is not enabled, contact your Super Admin

---

### Password I just set is not working

**Problem:** You changed a password in OpsPilot but the old password still works on the actual service, or the new one does not work.

**Cause:** Changing a password in OpsPilot only updates the RECORD. It does NOT change the password on the actual service (server, email provider, etc.).

**Procedure:**
1. Change the password on the actual service first (cPanel, provider portal, server)
2. Then update the password in OpsPilot to match
3. Verify by revealing the new password and testing it on the service

---

## Module Access Problems

### What is the difference between Vault and Password Reveal?

**Vault** is a separate module for storing credentials (logins, URLs, notes) outside of specific service records. It has its own Create/Edit/View/Delete/Reveal permissions.

**Password Reveal** is a feature on infrastructure service records (Hosting, VPS, VoIP, etc.) that lets you view the stored password for that specific record.

Both require **Reveal** permission to see decrypted passwords.

---

### What happens when I delete a record?

When you delete a record (domain, hosting, etc.):

1. The record is **soft-deleted** — it gets a `deleted_at` timestamp but is not removed from the database
2. The record disappears from the standard list view
3. A Super Admin can **restore** the record at any time
4. A Super Admin can **force-delete** to permanently remove it

> **Soft-deleted records are not gone.** They remain in the database and can be recovered. Only force-delete is permanent.

---

### How do I know which modules I can access?

**Procedure:**
1. Look at the **sidebar** — only modules you can access are shown
2. Go to **My Access** for a complete list with per-permission details
3. If a module is in the sidebar but shows no data, you have View but no records exist in your scope

---

### What does "Module-wide access" mean?

For the 9 infrastructure modules (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets):

- Users with **View** permission see ALL records in that module
- Records are NOT filtered by who created them
- If you have View on Domains, you see every domain — not just your own

This allows team collaboration. Everyone with access sees the same data.

---

### What does "Personal access" mean?

For Vault and Notes:

- Users see only their OWN entries by default
- Exception: Administrators with View permission on Vault can also see entries in vault modules they can access
- This prevents one user from accidentally seeing another user's personal credentials

---

## Error Messages

### "Cannot delete the last Super Admin user"

**Problem:** You tried to delete a user and got this error.

**Cause:** The system prevents deleting the last user with Super Admin role. This would lock everyone out of system administration.

**Procedure:**
1. Promote another existing user to Super Admin first
2. Then delete the original user
3. If no other user can be promoted, contact the database administrator

---

### "Cannot remove your own Super Admin role"

**Problem:** You tried to edit your own role and remove Super Admin.

**Cause:** The system prevents self-demotion from Super Admin. This protects against accidental loss of administrative access.

**Procedure:**
1. Ask another Super Admin to make the change
2. If no other Super Admin exists, contact the database administrator

---

### "Protected role cannot be deleted"

**Problem:** You tried to delete a role and got this error.

**Cause:** The "admin" and "super-admin" roles are protected from deletion.

**Procedure:**
1. You cannot delete protected roles through the interface
2. If you need to remove them, contact the database administrator

---

### "Account suspended"

**Problem:** You tried to perform an action and got this error.

**Cause:** Your account has been suspended by a Super Admin.

**Procedure:**
1. Contact your Super Admin to unsuspend your account
2. If you believe this is an error, provide context to your Super Admin

---

### Permission error when clicking Edit/Delete

**Problem:** The Edit or Delete button is visible but clicking it returns a permission error.

**Possible Causes:**
1. The UI may show the button based on cached permissions, but the server enforces actual permissions
2. The record may be in a module you no longer have access to
3. There may be a permission caching issue — log out and back in

**Procedure:**
1. Log out and log back in to refresh your session
2. Check **My Access** to confirm your current permissions
3. If the button should be hidden (you lack permission), it is a UI display issue — ignore it
4. If you should have permission, contact your Super Admin

---

### Bulk actions buttons are hidden

**Problem:** You cannot see the bulk action toolbar on an index page.

**Cause:** Bulk action buttons appear only when you have the required permission (e.g., Delete for bulk delete). If you lack the permission, the buttons are hidden.

**Procedure:**
1. Check your **Delete** and **Edit** permissions on that module (My Access)
2. If you lack Delete, you cannot see bulk delete options
3. Bulk update status requires Edit permission
4. If you need bulk actions, contact your Super Admin

---

### "The provided credentials do not match our records."

**Problem:** Login fails with this error.

**Procedure:**
1. Check email for typos (spelling, domain name)
2. Check password for typos (Caps Lock, Num Lock)
3. Use "Forgot your password?" to reset (if available)
4. Wait if you have tried multiple times — rate limiting is active
5. Contact your Super Admin if the problem persists

---

## Related Pages

- [Permission Reference](08_PERMISSION_REFERENCE.md) — Complete permission definitions
- [Role Matrix](09_ROLE_MATRIX.md) — Role × Module access matrix
- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Operational procedures
