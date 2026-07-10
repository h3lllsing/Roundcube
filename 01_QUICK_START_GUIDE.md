# Quick Start Guide

> **Audience:** All Users — **Purpose:** Complete your first login and learn how to navigate the portal

## Table of Contents

- [Logging In](#logging-in)
- [Finding Your Way Around the Portal](#finding-your-way-around-the-portal)
- [Finding Records Across Modules](#finding-records-across-modules)
- [Updating Your Profile](#updating-your-profile)
- [Reviewing Your Notifications](#reviewing-your-notifications)
- [Resetting Your Password](#resetting-your-password)
- [Logging Out](#logging-out)
- [Role-Specific First-Day Procedures](#role-specific-first-day-procedures)

---

## Logging In

### Purpose
Gain access to the OpsPilot portal to view and manage operational records.

### When to Use
- On your first day after receiving your account credentials
- Every subsequent workday to start your session
- After being logged out due to session expiry

### Permission Required
A valid user account assigned by your Super Admin. No specific module permission needed.

### Step-by-Step Workflow

1. Open your browser and navigate to your organization's OpsPilot URL
2. Click **Sign In**
3. Enter the **email address** your Super Admin registered for you
4. Enter your **password**
5. Click **Login**

After successful login, the system redirects you to your **Dashboard**.

> **If login fails:**
> - "The provided credentials do not match our records" — Check your email and password for typos
> - "Your account has been suspended" — Contact your Super Admin to request reactivation
> - Repeated failures — You may be rate-limited. Wait 1 minute before retrying

### Best Practices
- Bookmark the Dashboard URL for quick access
- Always log out when using a shared or public computer
- Report any login issues to your Super Admin rather than sharing passwords

### Common Mistakes
- Using an incorrect URL — verify you have the correct portal address
- Caps Lock turned on — passwords are case-sensitive
- Repeated login attempts after suspension — contact your Super Admin instead

### Typical Business Scenario
**First day on the job:** You are a new IT Support Engineer. Your Super Admin has created your account and assigned the IT Support role. You are logging in for the first time to begin managing client services.

### Expected Result
You are redirected to your personalized Dashboard. The sidebar shows only the modules your role has permission to access. A notification may appear if you have been assigned tasks or expiry reminders.

### Related Pages
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — If you need additional permissions
- [FAQ](07_FAQ.md) — Troubleshooting login issues

---

## Finding Your Way Around the Portal

### Purpose
Understand the sidebar navigation so you can locate modules, records, and tools relevant to your role.

### When to Use
- Every time you need to navigate to a specific module
- When a new module appears or disappears from your sidebar (permissions changed)
- When learning the portal for the first time

### Permission Required
You can only navigate to modules where you have **View** permission. Modules without View are hidden from your sidebar.

### Step-by-Step Workflow

1. Look at the **sidebar** on the left side of the page
2. Find the section that matches what you need:

   | Sidebar Section | What You'll Find | Who Sees It |
   |-----------------|------------------|-------------|
   | **Dashboard** | Your home screen with summary widgets | All users |
   | **Notifications** | System alerts and updates | All users |
   | **Infrastructure** | Managed services (Domains, Hosting, VPS, etc.) | Users with module access |
   | **Credentials** | Vault (password storage) | Users with Vault access |
   | **Operations** | Tasks, Calendar | Users with Operations access |
   | **Administration** | Users, Roles, Permissions, Activity Logs, Import, Webhooks, API Tokens | Super Admin only |
   | **Reports** | Operational reports and exports | Super Admin only |
   | **Account** | My Profile, My Access, Help Center | All users |

3. Click a menu item to open that module's page
4. Use the **breadcrumb** at the top of the page to see where you are

> **Tip:** If a module is not in your sidebar, you do not have View permission on it. Contact your Super Admin if you need access.

### Best Practices
- Use the **Command Palette** (press Ctrl+K) to quickly jump to any page without clicking through menus
- The sidebar collapses — click the hamburger icon to give yourself more screen space
- Recently added modules may appear/disappear if your permissions changed — refresh the page

### Common Mistakes
- Clicking a sidebar item and seeing a blank page — you may lack View permission (the sidebar may show cached items)
- Looking for Administration modules when you are not a Super Admin — these are hidden by design

### Typical Business Scenario
**Finding a client's hosting record:** You are an IT Staff member. You click **Infrastructure → Hosting** in the sidebar. The Hosting index page opens showing all hosting records your team manages.

### Expected Result
The selected module's index page opens. If you have View permission, you see a list of records. If you have Create permission, you see a **Create** button.

### Related Pages
- [Read Only Guide](05_READ_ONLY_GUIDE.md) — Navigation for view-only users

---

## Finding Records Across Modules

### Purpose
Quickly locate any record (domain, hosting, task, vault entry) without navigating through individual modules.

### When to Use
- You need to find a specific client, service, or record
- You do not remember which module a record belongs to
- You need to verify if a record exists in the system

### Permission Required
You only see records in modules where you have **View** permission. Search results are automatically scoped.

### Step-by-Step Workflow

1. Click the **search bar** at the top of any page (or press Ctrl+K)
2. Type at least **2 characters** of what you are looking for
   - Examples: client name, domain name, service type, record ID
3. Review the results, which are grouped by module
4. Click on any result to open that record's detail page

> **How search scoping works:**
> - **Infrastructure modules** — You see ALL matching records in modules you can access
> - **Personal modules (Vault)** — You see only your own matching entries
> - **Tasks** — You see tasks in accessible modules + tasks assigned to you

### Best Practices
- Use search as your primary navigation tool — it is faster than browsing through menus
- Be specific — searching "example.com" returns better results than searching "ex"
- Use search to verify records exist before creating duplicates

### Common Mistakes
- Searching with only 1 character — the system requires a minimum of 2 characters
- Assuming you see all results — your view is scoped to your permissions
- Expecting to find records in modules you do not have access to

### Typical Business Scenario
**Finding a client's services:** A client calls about their hosting. You type the client's domain name into the search bar. Results show the domain record, hosting record, and any related notes — all accessible from one search.

### Expected Result
A list of matching records grouped by module. Click any result to open its detail page. If no results appear, either the record does not exist or you lack access to the module it belongs to.

### Related Pages
- [Admin Guide](03_ADMIN_GUIDE.md) — Advanced search and filtering

---

## Updating Your Profile

### Purpose
Keep your contact information current and update your password for security.

### When to Use
- Your name or email changes
- You need to change your password periodically
- You want to verify your current account details

### Permission Required
None — all authenticated users can edit their own profile.

### Step-by-Step Workflow

1. Click your **name** in the top-right corner of any page
2. Select **Profile**
3. Make your changes:

   | Field | What to Do |
   |-------|------------|
   | **Name** | Type your updated name |
   | **Email** | Type your updated email address |
   | **Password** | Enter your current password + new password twice |
4. Click **Save**

### Best Practices
- Update your profile immediately when your contact details change
- Use a strong, unique password (the system accepts any password you choose)
- Keep your email current — expiry reminders and notifications are sent there

### Common Mistakes
- Forgetting to enter your current password when changing your password — the form requires it for security
- Using a weak password — while the system does not enforce complexity, your organization may have policies

### Typical Business Scenario
**Password change due to security policy:** Your organization requires password changes every 90 days. You navigate to Profile, enter your current password and new password, and save.

### Expected Result
Your name, email, or password is updated immediately. Your next login uses the new credentials.

### Related Pages
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — If you are locked out, your Super Admin can reset your password

---

## Reviewing Your Notifications

### Purpose
Stay informed about expiry reminders, task assignments, and system alerts.

### When to Use
- First thing in the morning — check for overnight notifications
- Throughout the day — stay on top of new assignments
- Before logging out — ensure nothing was missed

### Permission Required
Notifications are personal — all authenticated users can view their own notifications for modules they can access.

### Step-by-Step Workflow

1. Click the **bell icon** in the top bar
2. A dropdown shows your recent unread notifications
3. Click any notification to view details
4. Click **Mark all as read** to clear the badge counter
5. To see all notifications, click **View All** at the bottom of the dropdown

> **Note:** You only see notifications relevant to modules you can access. If a notification refers to a module you no longer have access to, it may still appear in your list but the linked page may return an error.

### Best Practices
- Check notifications at least twice per day (morning and afternoon)
- Mark notifications as read after you have acted on them to keep your list manageable
- Treat task assignment notifications as action items

### Common Mistakes
- Ignoring the badge counter — unread notifications may contain important expiry reminders
- Deleting notifications without reading them — notifications are personal and once deleted cannot be recovered

### Typical Business Scenario
**Task assigned while you were away:** Your manager assigns a high-priority task to you. The bell icon shows a red badge. You click it, see the assignment, and navigate to the task to begin work.

### Expected Result
Unread notifications appear in the dropdown with a red badge on the bell icon. After reading, they move to your read list.

### Related Pages
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Morning checklist

---

## Resetting Your Password

### Purpose
Regain access to your account when you have forgotten your password.

### When to Use
- You forgot your password and cannot log in
- Your password expired and you need to set a new one
- You suspect your account was compromised

### Permission Required
Available if enabled by your organization. The login page will show the **"Forgot your password?"** link if the feature is configured.

### Step-by-Step Workflow

1. On the login page, click **"Forgot your password?"**
2. Enter the **email address** associated with your account
3. Check your email inbox (and spam folder) for a password reset link
4. Click the **reset link** in the email
5. Enter your **new password** twice
6. Click **Reset Password**
7. Return to the login page and sign in with your new password

> **If you do not receive the email:**
> - Check your spam/junk folder
> - Verify you entered the correct email address
> - The reset link expires after a set period — request a new one if needed
> - Contact your Super Admin if the feature is not enabled

### Best Practices
- Use a password you can remember but others cannot guess
- Save the new password in a secure location
- Request a new link if the previous one expired — do not try to reuse old links

### Common Mistakes
- Entering the wrong email address — make sure it matches the one on your account
- Waiting too long to use the link — reset links expire for security
- Not checking spam — automated emails may be filtered

### Typical Business Scenario
**Forgot password after vacation:** You return from leave and cannot remember your password. You click "Forgot your password?" on the login page, enter your email, receive the reset link, and set a new password.

### Expected Result
You receive an email with a reset link. After setting a new password, you can log in with the new credentials.

### Related Pages
- [FAQ](07_FAQ.md) — Login troubleshooting

---

## Logging Out

### Purpose
Securely end your session to prevent unauthorized access.

### When to Use
- At the end of your workday
- When stepping away from a shared or public computer
- Before switching to a different user account

### Permission Required
None — any authenticated user can log out.

### Step-by-Step Workflow

1. Click your **name** in the top-right corner
2. Select **Logout**
3. You are redirected to the login page

### Best Practices
- Always log out on shared computers — closing the tab does not always end the session
- Log out before closing the browser to ensure session data is cleared
- If you remain logged in after logout, clear your browser cache and cookies

### Common Mistakes
- Closing the browser tab without logging out — the session may remain active
- Assuming the "Remember Me" checkbox means you never need to log out — it only persists the session across browser restarts

### Typical Business Scenario
**End of day:** You have finished your work. You click your name → **Logout**, and the login page appears confirming your session has ended.

### Expected Result
You are redirected to the login page. Your session is terminated. Any subsequent access requires re-authentication.

### Related Pages
- None

---

## Role-Specific First-Day Procedures

### If You Are a Super Admin

1. **Verify your access** — Navigate to all sidebar groups (Administration, Integration) to confirm full visibility
2. **Check the Dashboard** — You see all widgets including Server Health and SMTP Status
3. **Review user accounts** — Navigate to **Users** to see the current user list
4. **Review role configuration** — Navigate to **Roles** to see existing role definitions
5. **Read the [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md)** for complete configuration procedures

### If You Are an Administrator

1. **Verify your access** — Navigate through Infrastructure modules to confirm you see all records
2. **Check your permissions** — Go to **My Access** to confirm your effective permissions
3. **Review the Dashboard** — You see operations, renewals, tasks, and assets widgets
4. **Read the [Admin Guide](03_ADMIN_GUIDE.md)** for daily operations procedures

### If You Are IT Staff

1. **Verify your module access** — Confirm you can see the 6 operational modules (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails)
2. **Check My Tasks** — See if any tasks are already assigned to you
3. **Read the [IT Staff Guide](04_IT_STAFF_GUIDE.md)** for ticket processing procedures

### If You Are a Read Only User

1. **Browse modules** — Navigate through each module to understand what data is available
2. **Use Search** — Practice finding records with the global search
3. **Review the Calendar** — See upcoming expiries across modules
4. **Read the [Read Only Guide](05_READ_ONLY_GUIDE.md)** for information access procedures

---

## Related Pages

- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — Portal configuration
- [Admin Guide](03_ADMIN_GUIDE.md) — Daily operations management
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Service desk procedures
- [Read Only Guide](05_READ_ONLY_GUIDE.md) — Information access procedures
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Daily/weekly/monthly checklists
