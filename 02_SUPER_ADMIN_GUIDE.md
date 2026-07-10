# Super Admin Guide

> **Audience:** Super Administrators — **Purpose:** Configure and maintain the OpsPilot portal

## Table of Contents

- [User Management](#user-management)
- [Role Management](#role-management)
- [Module Permissions Configuration](#module-permissions-configuration)
- [User Permission Overrides](#user-permission-overrides)
- [Role Template Application](#role-template-application)
- [SMTP Profile Configuration](#smtp-profile-configuration)
- [Feature & Module Management](#feature--module-management)
- [Importing Data](#importing-data)
- [Generating Reports](#generating-reports)
- [Auditing Activity](#auditing-activity)
- [Webhook Configuration](#webhook-configuration)
- [Safety Procedures](#safety-procedures)

---

## User Management

### Creating a User Account

**Purpose:** Provision a new user so they can log into the portal.

**When to Use:** A new employee or contractor needs access to OpsPilot.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. In the sidebar, go to **Administration → Users**
2. Click **Create User**
3. Fill in the form:

   | Field | Required | Instructions |
   |-------|----------|--------------|
   | **Name** | Yes | Full name of the user |
   | **Email** | Yes | Work email — used for login and notifications |
   | **Password** | Yes | Temporary password (user changes via Profile) |
   | **Role** | Yes | Select one role (Super Admin, Administrator, IT Support, Read Only, or custom) |

4. Optionally, expand **Permission Overrides** to set per-module exceptions (see [User Permission Overrides](#user-permission-overrides))
5. Click **Save**

> **After creation:** The user can log in immediately with the credentials you set. Tell them to change their password via **Profile** on first login.

#### Best Practices
- Assign the least permissive role that covers the user's job duties
- Use the **Clone User** feature (see below) when creating multiple users with similar permissions
- Document the user's role and purpose in a note for future reference
- Start with role-based permissions — only add overrides when role-level is insufficient

#### Common Mistakes
- Assigning Super Admin role for convenience — only assign when full system access is required
- Not setting a password — the form requires it; provide a temporary one the user can change
- Forgetting to assign any role — the user will log in but see no modules (blank page)

#### Typical Business Scenario
**Onboarding a new IT technician:** Your help desk hires a new technician. You create their account with the IT Support role, which grants them access to the 6 operational modules with Create, Edit, and Reveal permissions.

#### Expected Result
The new user appears in the Users list. They can log in immediately and see modules based on their assigned role.

---

### Editing a User

**Purpose:** Update user details or change role assignment.

**When to Use:** A user changes their name/email, or their job role changes requiring different permissions.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Users**
2. Find the user using search or filters
3. Click the user's name to open their profile
4. Click **Edit**
5. Update any of the following:

   | Field | Notes |
   |-------|-------|
   | **Name** | Updates immediately |
   | **Email** | Updates immediately; affects login and notifications |
   | **Password** | Leave blank to keep current password |
   | **Role** | Changes permissions immediately |

6. Click **Save**

#### Best Practices
- When changing a user's role, verify their sidebar still shows the modules they need
- After changing permissions, ask the user to log out and back in to refresh their session
- Use **My Permissions** (as the user) to verify changes took effect

#### Common Mistakes
- Removing a role without assigning a new one — the user will have no permissions
- Self-demotion prevention — you cannot remove the Super Admin role from your own account (see [Safety Procedures](#safety-procedures))
- Editing permissions during business hours — users may experience mid-session changes

#### Typical Business Scenario
**Promoting IT Staff to Administrator:** A senior technician is promoted. You edit their user record and change the role from IT Support to Administrator, granting them access to all 9 infrastructure modules plus Export permission.

#### Expected Result
The user's profile updates immediately. Their sidebar and permissions reflect the new role on next login.

---

### Suspending a User

**Purpose:** Temporarily prevent a user from logging in without deleting their data.

**When to Use:** Employee is on leave, under investigation, or temporarily offboarded.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Users**
2. Find the user and open their profile
3. Click **Suspend**
4. The user's account is immediately deactivated

> The user sees "Your account has been suspended." on their next login attempt. All their data — records, tasks, assignments — remains intact.

#### To Unsuspend:
1. Open the user's profile
2. Click **Unsuspend**
3. The user can log in again immediately

#### Best Practices
- Suspend instead of delete for temporary situations — preserves all data and relationships
- Document why the user was suspended in case another admin needs context
- Check for assigned tasks or active records before suspending — reassign them if needed

#### Common Mistakes
- Suspending the last Super Admin — the system blocks this (see [Safety Procedures](#safety-procedures))
- Forgetting to unsuspend — set a reminder if the suspension is for a known period

#### Typical Business Scenario
**Employee on parental leave:** A team member takes 6 months leave. You suspend their account to prevent access but preserve all their records and assignments. When they return, you unsuspend.

#### Expected Result
The user cannot log in. The Users list shows them as **Suspended**. All their data remains in the system.

---

### Cloning a User

**Purpose:** Create a new user with the same permissions as an existing user.

**When to Use:** You need multiple users with identical role assignments and permission overrides (e.g., onboarding a team).

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Users**
2. Open the source user's profile
3. Click **Clone User**
4. Enter the new user's details (name, email, password)
5. Confirm that permissions will be copied from the source
6. Click **Save**

The clone preserves:
- Role assignment
- Permission overrides (On/Off per module)
- Account status

#### Best Practices
- Clone a well-configured user rather than manually re-creating overrides
- After cloning, verify the new user's permissions by checking **My Permissions** as them
- Avoid cloning a user with overrides unless you intend to duplicate those exceptions

#### Common Mistakes
- Cloning a Super Admin user — the clone also gets Super Admin. Unless intended, change the clone's role.
- Cloning a suspended user — the clone inherits the status. Unsuspend if needed.

#### Typical Business Scenario
**Onboarding a team with custom overrides:** You have an IT Staff member who needs special access to Other Services (not in the default template). Instead of re-creating the override for each new hire, you clone the configured user.

#### Expected Result
A new user is created with identical role and permission overrides as the source user.

---

### Deleting a User

**Purpose:** Permanently remove a user account from the system.

**When to Use:** Employee has left the organization and will not return.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Users**
2. Open the user's profile
3. Click **Delete**
4. Confirm the deletion

> **Important:** Deletion is a **soft delete** — the record is hidden but remains in the database. Only a database administrator can permanently remove it. Associated records (tasks they created, notes they wrote) remain linked to their user ID.

#### Best Practices
- **Suspend first, delete later** — suspension preserves all data. Delete only after confirming the user has no active records or responsibilities
- Reassign the user's open tasks before deleting
- Notify other team members that the user has been removed

#### Common Mistakes
- Deleting a user who has assigned tasks — those tasks become orphaned
- Deleting the last Super Admin — the system blocks this
- Expecting deletion to free up resources — it only hides the account

#### Typical Business Scenario
**Employee resignation:** A team member resigns. You first reassign their open tasks, then suspend their account. After a 30-day grace period, you delete the user.

#### Expected Result
The user disappears from the active Users list. Their ID and past records remain in the database for audit purposes.

---

## Role Management

### Creating a Role

**Purpose:** Define a new role with a specific set of system privileges.

**When to Use:** The four default roles (Super Admin, Administrator, IT Support, Read Only) do not cover a specific job function.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Roles**
2. Click **Create Role**
3. Set the following:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Display name (e.g., "Junior Technician") |
   | **Slug** | URL-friendly identifier (auto-generated from name; must be unique) |
   | **Privileges** | Check system-level capabilities (if unsure, leave default) |

4. Click **Save**
5. The role is created with no module permissions yet
6. Navigate to **Module Permissions** to configure what this role can do on each module

#### Best Practices
- Keep the slug short and descriptive — it is used in backend checks
- Create roles for groups of users, not individuals — use overrides for exceptions
- Document the role's purpose and expected permissions for future reference

#### Common Mistakes
- Creating a role and not configuring module permissions — the role will have no access
- Duplicating an existing role's permission profile — use a role template instead
- Using spaces or special characters in the slug — use lowercase letters and hyphens only

#### Typical Business Scenario
**Creating a "Junior Technician" role:** Your IT Support team has junior members who should only View and Edit (no Create, Reveal, Delete). You create the role, then configure Module Permissions to grant only View and Edit on the 6 operational modules.

#### Expected Result
A new role appears in the Roles list with zero module permissions. You must now configure module permissions via the Module Permissions page.

---

### Editing a Role

**Purpose:** Modify a role's name or privileges.

**When to Use:** A role's scope changes, or you need to attach/detach privileges.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Roles**
2. Click the role name
3. Click **Edit**
4. Update the name or privileges
5. Click **Save**

> **To change module permissions**, use the Module Permissions page (see below). The role edit form only handles name and system privileges.

#### Best Practices
- Communicate role changes to affected users before implementing
- After editing, log in as the affected role's user to verify behavior
- Rename roles carefully — users see the display name in their profile

#### Common Mistakes
- Expecting the role edit form to change module permissions — it does not; use Module Permissions
- Editing a protected role's name — some roles may be protected from modification

#### Typical Business Scenario
**Rebranding a role:** Your organization renames "IT Support" to "Service Desk Engineer." You edit the role and update the name. Users now see "Service Desk Engineer" as their role.

#### Expected Result
The role's name updates immediately. Users assigned to this role see the new name.

---

### Deleting a Role

**Purpose:** Remove a role definition that is no longer needed.

**When to Use:** A custom role is obsolete and no users are assigned to it.

**Permission Required:** Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Roles**
2. Open the role's profile
3. Click **Delete**
4. Confirm the deletion

> **The system protects:**
> - The "admin" and "super-admin" roles — these cannot be deleted
> - Any role that still has users assigned — reassign those users first

#### Best Practices
- Before deleting, verify no users are assigned to the role
- Confirm the role is not referenced in any system configuration
- Consider deactivating the role (set zero permissions) instead of deleting if unsure

#### Common Mistakes
- Attempting to delete a protected role — the system returns an error
- Deleting a role that users are assigned to — the system blocks the action
- Not replacing the role for existing users — reassign them before deletion

#### Typical Business Scenario
**Retiring a legacy role:** Your company previously used an "Editor" role that is now obsolete. No users are assigned. You delete the role cleanly.

#### Expected Result
The role is removed from the Roles list. Any associated module permissions are also removed. Users assigned to the role must be reassigned before deletion.

---

## Module Permissions Configuration

### Purpose
Decide which roles can perform which actions (View, Create, Edit, Delete, Reveal, Export) on each module.

### When to Use
- After creating a new role — to grant module access
- When a role's responsibilities change
- When a new module is added to the system

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Module Permissions**
2. You see a matrix: modules as rows, roles as columns
3. Find the **role** column you want to configure
4. For each **module row**, click to open the permission editor
5. Set each permission:

   | Permission | When to Grant |
   |------------|---------------|
   | **View** | User needs to see records in this module |
   | **Create** | User needs to add new records |
   | **Edit** | User needs to modify existing records |
   | **Delete** | User needs to remove records (soft-delete) |
   | **Reveal** | User needs to view decrypted passwords |
   | **Export** | User needs to download data as CSV |
   | **Approve** | Reserved for future use |

   Each permission is independent — granting View does NOT grant any other permission.

6. Click **Save**

> **Visibility Rule:** For infrastructure modules, granting View gives access to ALL records in that module — not just records the user created.

#### Best Practices
- Start with a role template and adjust from there
- Grant the minimum permissions needed for the role's job function
- Use the **My Permissions** page to verify the final configuration from the user's perspective
- Be deliberate with **Delete** and **Reveal** — these have the most security impact
- Remember: IT Staff default grants Create/Edit on 6 modules; Admin default grants Create/Edit/Export on all 9

#### Common Mistakes
- Granting View but not Create — the user can see records but not add new ones (may be intentional)
- Granting Reveal without View — the user can reveal passwords but cannot see the records (practically useless)
- Forgetting that infrastructure View is global — the user sees ALL records, not just their team's
- Forgetting to set permissions on newly created modules — they default to no access for all roles

#### Typical Business Scenario
**Granting Export to IT Staff:** Management wants IT Staff to export domain lists. You navigate to Module Permissions, find the IT Support role, locate the Domains row, and set Export to On.

#### Expected Result
The Export button appears on the Domains index page for all users with the IT Support role. They can now download domain data as CSV.

---

## User Permission Overrides

### Purpose
Grant or deny a specific permission for an individual user, bypassing their role's default configuration.

### When to Use
- A user needs one additional permission their role does not grant (e.g., Admin needs Reveal)
- A user should be denied a permission their role normally grants (e.g., IT Staff who should not Create)
- You have one-off exceptions that do not justify creating a new role

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Users**
2. Open the user's profile
3. Click **Edit Permissions**
4. You see the same matrix as Module Permissions, but for this specific user
5. For each module × permission, set one of three states:

   | State | Meaning | How It Works |
   |-------|---------|--------------|
   | **Unset (blank)** | Inherit from role | User gets whatever the role says |
   | **On** | Force-grant | Overrides a role that denies this permission |
   | **Off** | Force-deny | Blocks this permission even if the role grants it |

6. Click **Save**

> **Override priority:** A user-level override always wins over the role setting. If the role grants Delete but the user override says Off, the user cannot delete.

#### Best Practices
- Use overrides sparingly — they create exceptions that are easy to forget
- Document why the override exists (e.g., add a note on the user's profile)
- Audit overrides quarterly — clean up ones that are no longer needed
- Prefer role-based permissions for anything that applies to multiple users

#### Common Mistakes
- Creating overrides for common scenarios — that is what custom roles are for
- Forgetting a user has an override — when you change the role, the override still applies
- Not testing after setting an override — verify the user sees the expected buttons

#### Typical Business Scenario
**Granting Reveal to a specific Administrator:** Your default Administrator role has Reveal = Off, but one Admin handles password rotations. You set Reveal = On for that user on the Hosting and VPS modules only.

#### Expected Result
That specific user can now reveal passwords on Hosting and VPS. Other Administrators without the override cannot. The user's **My Permissions** page shows Reveal as "On (Override)" for those modules.

---

## Role Template Application

### Purpose
Quickly configure a role's module permissions by applying a pre-defined template.

### When to Use
- Creating a new role and want to start from a known baseline
- Resetting a role's permissions back to a standard configuration
- Setting up a new department with standard role definitions

### Permission Required
Super Admin role.

#### Available Templates

| Template | Grants |
|----------|--------|
| **Super Admin** | Full access (View, Create, Edit, Delete, Reveal, Export) on ALL modules. Marked as **Dangerous**. |
| **Administrator** | Create, View, Edit, Export, Reveal on all 9 infrastructure modules. Reveal = On by default. |
| **IT Support** | Create, View, Edit, Reveal on 6 modules (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails). |
| **Read Only** | View only on all infrastructure and productivity modules. |

#### Step-by-Step Workflow

1. Go to **Administration → Role Templates**
2. Click the template you want to apply
3. Review the template's permission profile
4. Click **Apply to Role**
5. Select the **target role** from the dropdown
6. The system shows a **diff**: which modules will change, and how
7. Read the warning — applying a template **overwrites** the role's existing module permissions
8. If the template is marked **Dangerous**, you must confirm explicitly
9. Click **Confirm Apply**

> **After applying:** The role's module permissions are now exactly what the template specifies. Any previous customizations are lost.

#### Best Practices
- Always review the diff before confirming — it shows exactly what will change
- Apply templates to NEW roles, not existing ones with customizations
- If you have customized a role, consider creating a custom template instead of reapplying a standard one
- The dangerous confirmation on Super Admin template prevents accidental full-access grants

#### Common Mistakes
- Applying a template to a customized role without previewing the diff — you may lose custom settings
- Applying the Super Admin template to a role that should have limited access — use the dangerous confirmation as a final check
- Assuming templates add permissions — they replace everything

#### Typical Business Scenario
**Setting up a new department:** Your company creates a "Support Team" role. You apply the IT Support template to quickly give them the standard 6-module access with Create/Edit/Reveal. You then customize from there.

#### Expected Result
The target role's module permissions are replaced with the template's configuration. Affected users see the changes on their next login.

---

## SMTP Profile Configuration

### Purpose
Configure email sending profiles so the system can send expiry reminder notifications and test emails.

### When to Use
- Initial portal setup — before expiry reminders can work
- When your email provider's SMTP settings change
- When adding a backup mail server

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

**Creating a profile:**
1. Go to **Administration → SMTP Profiles**
2. Click **Create SMTP Profile**
3. Enter the following:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Friendly label (e.g., "Company Mail Server") |
   | **Host** | SMTP server hostname (e.g., smtp.example.com) |
   | **Port** | Usually 587 (TLS) or 465 (SSL) |
   | **Username** | SMTP authentication username |
   | **Password** | SMTP authentication password (encrypted on save) |
   | **Encryption** | TLS or SSL |
   | **From Address** | Email address that appears in the "From" field |
   | **From Name** | Display name for the "From" field |
   | **Is Default** | Check if this should be the default for all expiry trackers |
   | **Is Active** | Uncheck to disable without deleting |

4. Click **Save**

**Testing a profile:**
1. Open the profile's detail page
2. Click **Test**
3. A test email is sent to your account email address
4. Check your inbox to confirm delivery

**Setting a default:**
- Click **Set Default** on the profile's detail page
- The default profile is used when an expiry tracker has no specific SMTP profile assigned

#### Best Practices
- Always test a new profile before marking it active
- Keep at least one active default profile so new expiry trackers work out of the box
- Use TLS on port 587 for maximum compatibility
- Rotate SMTP passwords periodically

#### Common Mistakes
- Forgetting to set a default profile — expiry trackers without a specific profile will fail to send
- Leaving a profile inactive with no backup — all email notifications stop working
- Incorrect port/encryption combination — research your provider's requirements

#### Typical Business Scenario
**Initial setup:** You configure your company's Office 365 SMTP settings in a new profile, test it successfully, and set it as default. All expiry trackers now use this profile for sending reminders.

#### Expected Result
The SMTP profile is saved with the password encrypted. The Test function sends a verification email. When set as default, all expiry trackers without a specific profile use this one for notifications.

---

## Feature & Module Management

### Purpose
Manage the feature/module hierarchy that organizes the portal's sidebar structure.

### When to Use
- Adding a new service category to the portal
- Reorganizing how modules are grouped in the sidebar
- Deactivating a module that is no longer used

### Permission Required
Super Admin role.

#### Creating a Feature

1. Go to **Administration → Features**
2. Click **Create Feature**
3. Enter:
   - **Name** — The sidebar category name (e.g., "Infrastructure")
   - **Sort Order** — Position in the sidebar (lower numbers appear first)
4. Click **Save**

#### Creating a Module

1. Go to **Administration → Modules**
2. Click **Create Module**
3. Enter:
   - **Name** — Display name (e.g., "SSL Certificates")
   - **Slug** — Unique identifier (e.g., "ssl-certificates")
   - **Feature** — Which feature/group this module belongs to
4. Click **Save**

> **After creating a module:** You must configure Module Permissions for each role — otherwise no one can access it.

#### Best Practices
- Plan the module structure before creating — reorganizing later is possible but affects all records
- Use descriptive names that users will recognize
- Only create modules that correspond to actual data your team manages
- When disabling a module, ensure no records are actively using it

#### Common Mistakes
- Creating a module and forgetting to set permissions — users cannot access it
- Deleting a module that has associated records — the records may become inaccessible
- Creating duplicate modules — check existing modules before adding new ones

#### Typical Business Scenario
**Adding a new service category:** Your company starts managing SSL certificates. You create a new module called "SSL Certificates" under the Infrastructure feature, then configure Module Permissions so IT Staff can View, Create, and Edit.

#### Expected Result
The new module appears in the sidebar for users with View permission. Roles with create permission see a Create button.

---

## Importing Data

### Purpose
Bulk-load records into the system from a CSV file.

### When to Use
- Initial data migration from a legacy system
- Adding a large batch of records (e.g., 100+ domains)
- Periodic data updates from external sources

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Import**
2. Select the **entity type** you are importing (e.g., Domains, Hosting, VPS)
3. Upload a **CSV file** with the required columns
4. Map the CSV columns to the system's fields
5. Review the preview showing how many records will be created
6. Confirm the import

> **Import behavior:**
> - The system bulk-inserts records — errors for individual rows may not roll back the entire batch
> - Passwords in the import are encrypted automatically
> - Required fields vary by entity type — check the import page for the current template

#### Best Practices
- Start with a small test file (5-10 rows) to verify column mapping
- Clean your data before importing — correct typos, standardize formats
- Back up your database before large imports
- Run imports during off-peak hours to minimize performance impact

#### Common Mistakes
- Uploading a file with missing required columns — the system will reject it
- Importing duplicate records — deduplicate your file before importing
- Importing with incorrect date formats — check the expected format on each field
- Importing to the wrong entity type — double-check before confirming

#### Typical Business Scenario
**Migrating from spreadsheets:** Your company has been tracking domains in a Google Sheet. You export it as CSV, use the Import tool to load all 200 domains, and they appear in the Domains module instantly.

#### Expected Result
Records are created in the selected module. You can verify them by navigating to the module's index page. The import is logged in Activity Logs.

---

## Generating Reports

### Purpose
View operational summaries and export data for management review.

### When to Use
- Monthly management reporting
- Auditing service counts and costs
- Reviewing task completion rates
- Before budget planning meetings

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

1. Go to **Reports**
2. You see the **Report Dashboard** with:
   - Cost overview across services
   - Task summary (open, completed, cancelled)
   - Login summary
   - Top-10 costs by service type
3. Click a **category** to drill down:
   - Domain Reports, Hosting Reports, VPS Reports
   - Renewal Reports, Asset Reports
   - Task Reports, User Reports
4. Select a specific **report** within the category
5. Use filters to narrow results (date range, status, cost status)
6. View the results table
7. Click **Export** to download the report as CSV

> **Available report categories:** DomainReports, HostingReports, VpsReports, RenewalReports, AssetReports, TaskReports, UserReports.

#### Best Practices
- Schedule regular report reviews (weekly or monthly as needed)
- Use CSV export for further analysis in spreadsheets
- Share exports with relevant team members (export data is not permission-restricted after download)

#### Common Mistakes
- Expecting real-time data — reports reflect current database state at query time
- Using too broad filters — narrow your date range for meaningful results
- Forgetting to apply filters before exporting — you may export more data than needed

#### Typical Business Scenario
**Monthly management report:** The CFO asks for all active service costs. You go to Reports → select cost-related reports, apply the current month filter, and export to CSV. You share the file with the finance team.

#### Expected Result
The report displays the requested data. CSV export downloads a file you can open in spreadsheet software. The report reflects the current state of your data.

---

## Auditing Activity

### Purpose
Review system activity logs and login attempts for security monitoring and troubleshooting.

### When to Use
- Weekly security review
- Investigating a user's actions
- Troubleshooting "who changed what"
- Detecting unauthorized access attempts

### Permission Required
Super Admin role.

#### Viewing Activity Logs

1. Go to **Administration → Activity Logs**
2. Use filters to narrow results:

   | Filter | Use Case |
   |--------|----------|
   | **Event** | Filter by create, update, delete, reveal, login |
   | **Search** | Free-text search across descriptions |
   | **Causer** | Find activity by a specific user |
   | **Date Range** | Limit to a specific time period |

3. Click any log entry to see full details, including changed fields (old vs new values)

> **Activity logs record:** Every Create, Edit, Delete, Reveal, login success/failure, import, and export. Password reveal events are always logged with the user who revealed and the record ID.

#### Viewing Login Audits

1. Go to **Administration → Login Audits**
2. Filters: search, event type (success, failure, suspended), date range
3. Review for suspicious patterns:
   - Multiple failures from the same IP
   - Login attempts from unusual locations
   - Suspended account access attempts

#### Best Practices
- Review Activity Logs weekly for unusual patterns
- Investigate repeated password reveals to the same record
- Check Login Audits for brute-force attempts
- Export log data for long-term retention if needed

#### Common Mistakes
- Not filtering before reviewing — the full log can be overwhelming
- Ignoring login failures — these may indicate an attack
- Expecting logs to show password values — logs show "revealed" events but not the actual passwords

#### Typical Business Scenario
**Investigating a data change:** A client says their contact info was wrong. You go to Activity Logs, search for that client's domain, and see who edited it and what the old value was. You identify the incorrect entry and correct it.

#### Expected Result
Activity logs show a chronological list of events filtered by your criteria. Each entry shows who did what, when, and (for edits) what changed.

---

## Webhook Configuration

### Purpose
Send event notifications to external systems when specific actions occur in OpsPilot.

### When to Use
- Integrating with a third-party monitoring system
- Sending task updates to a project management tool
- Triggering external workflows when passwords are revealed

### Permission Required
Super Admin role.

#### Step-by-Step Workflow

1. Go to **Administration → Webhooks**
2. Click **Create Webhook**
3. Configure:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Descriptive name (e.g., "Slack Notifications") |
   | **URL** | The endpoint that receives POST requests |
   | **Events** | Select which events trigger this webhook (task.created, task.updated, vault.revealed) |
   | **Is Active** | Enable or disable without deleting |

4. Click **Save**
5. Click **Test** to send a sample payload and verify the endpoint responds

> **Webhook payload:** The system sends a POST request with JSON body containing the event type, affected record details, and the user who triggered it.

#### Best Practices
- Test webhooks before relying on them for critical notifications
- Ensure the receiving endpoint can handle the payload format
- Start with a few event types and expand as needed
- Monitor for failed webhook deliveries

#### Common Mistakes
- Configuring the wrong endpoint URL — test before activating
- Selecting too many event types — the endpoint may be overwhelmed
- Not testing — a failed webhook gives no notification
- Expecting delivery guarantees — webhooks are best-effort

#### Typical Business Scenario
**Slack integration:** You configure a webhook that sends a message to your team's Slack channel whenever a password is revealed. This gives the team visibility into credential access.

#### Expected Result
When a configured event occurs, the webhook fires a POST request to the specified URL. The Test function confirms connectivity.

---

## Safety Procedures

### Purpose
Understand the built-in protections that prevent accidental damage to the system.

### When to Use
- When you see a permission error during configuration — it may be a safety feature
- Before attempting destructive operations
- When training new Super Admins

#### 1. Last Super Admin Protection

The system prevents deleting the **last user** with the Super Admin role.

**How it works:** If only one Super Admin user remains, any attempt to delete that user returns: `Cannot delete the last Super Admin user.`

**What to do:** Promote another user to Super Admin before deleting the last one, or contact the database administrator for manual changes.

#### 2. Self-Demotion Prevention

You cannot remove the Super Admin role from your own user account.

**How it works:** If you edit your own profile and try to uncheck Super Admin, the system returns: `Cannot remove your own Super Admin role.`

**What to do:** Ask another Super Admin to make the change. If no other Super Admin exists, contact the database administrator.

#### 3. Protected Roles

The system protects critical roles from accidental deletion.

**How it works:** You cannot delete roles with the slug "admin" or "super-admin" through the interface. The same protection applies to roles that still have users assigned.

**What to do:** Reassign users to different roles before attempting deletion.

#### 4. Password Reveal Audit Logging

Every password reveal is automatically recorded with full details.

**How it works:** When any user reveals a password, the system logs: who performed the reveal, which record was accessed, and when it happened. This cannot be disabled through the interface.

**What to do:** Review Activity Logs regularly to monitor reveal activity. Investigate unusual patterns.

#### 5. Destructive Action Confirmations

The system prompts for confirmation before potentially dangerous operations.

**Actions that prompt for confirmation:**
- Deleting any record (soft-delete)
- Applying role templates marked as "Dangerous" (Super Admin template)
- Bulk delete operations
- Force-delete (permanent removal)

**What to do:** Read the confirmation dialog carefully. It tells you exactly what will happen. Cancel if you are unsure.

#### 6. Suspension Safety

A suspended user cannot log in, but all their data remains intact.

**How it works:** The `suspended_at` timestamp is set. On login, the system checks this field and returns: `Account suspended.`

**What to do:** Use suspension for temporary situations. Delete only after confirming the user's data is no longer needed.

#### 7. Soft Delete Safety

All record deletions are reversible by Super Admin.

**How it works:** Records are soft-deleted (marked with `deleted_at` timestamp). They are hidden from regular views but can be restored by a Super Admin using the "Trashed" filter and "Restore" action.

**What to do:** Do not force-delete unless absolutely necessary. Soft-deleted records provide a safety net.

---

## Related Pages

- [Admin Guide](03_ADMIN_GUIDE.md) — Managing daily operations
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Processing tickets
- [Read Only Guide](05_READ_ONLY_GUIDE.md) — Information access
- [Permission Reference](08_PERMISSION_REFERENCE.md) — Complete permission definitions
- [Role Matrix](09_ROLE_MATRIX.md) — Role × module access matrix
- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
