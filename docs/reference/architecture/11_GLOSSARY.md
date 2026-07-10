# Glossary

> **Audience:** All Users

## A

**Activity Logs**
A record of all system activity including creates, edits, deletes, reveals, and logins. Available to Super Admin only.

**Administrator**
A role with full CRUD and export access to infrastructure and productivity modules. Cannot manage users, roles, or system settings.

**API Tokens**
Personal access tokens used for API authentication. Managed by Super Admin.

**Assets**
Module for tracking hardware and equipment, including assignment and return management.

## B

**Bulk Actions**
Operations (update status, delete) that can be applied to multiple records at once from an index page.

## C

**Calendar**
A monthly view showing expiry dates from infrastructure modules and task due dates.

**Create Permission**
Allows adding new records in a module. Shows the Create button.

## D

**Dashboard**
The home screen showing widget summaries of system data.

**Delete Permission**
Allows removing records. Records are soft-deleted (reversible by Super Admin).

## E

**Edit Permission**
Allows modifying existing records. Shows the Edit button.

**Expiry Tracker**
A module for tracking service renewal dates, with email notification capabilities.

**Export Permission**
Allows downloading module data as CSV.

## F

**Features**
Top-level categories that group related modules together.

**Force-Delete**
Permanently removes a soft-deleted record. Super Admin only.

## G

**Global Search**
Search bar at the top of the page that searches across all accessible modules.

## I

**Import**
Upload CSV data into the system. Super Admin only.

**Infrastructure Modules**
The 9 operational modules: Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets.

**IT Support (IT Staff)**
A role with Create, Edit, and Reveal access to 6 infrastructure modules. Cannot delete or export.

## K

**Kanban**
A visual task management board showing tasks in columns by status.

## L

**Login Audits**
Record of all login attempts (success, failure, suspended). Super Admin only.

## M

**Module**
A section of the application that manages a specific type of data (e.g., Domains, Hosting, Tasks).

**Module Permissions**
The configuration page where Super Admins set which roles can access which modules.

**Module-Wide Access**
The rule that users with View permission on an infrastructure module see ALL records in that module.

**Monitor**
An on-demand service availability check for records with a monitoring URL configured.

**My Permissions**
A page showing your assigned roles and effective permissions per module.

## N

**Notes**
Personal and module-attached notes. Module-attached notes inherit the module's visibility.

**Notifications**
System alerts for expiry reminders, task assignments, and monitor results.

## O

**Operational Modules**
See **Infrastructure Modules**.

## P

**Password Reveal**
The feature that decrypts and displays stored passwords. Requires Reveal permission.

**Personal Access**
The rule that users see only their own records in personal modules (Vault).

**Permission Override**
A per-user setting that forces a permission on or off, overriding the role default.

**Privileges**
System-level capabilities that can be assigned to roles.

## R

**Read Only**
A role with View-only access to infrastructure, productivity, and select modules. Cannot create, edit, delete, reveal, or export.

**Reports**
Pre-built operational reports with CSV export. Super Admin only.

**Restore**
Recovers a soft-deleted record. Super Admin only.

**Reveal Permission**
Allows viewing decrypted passwords on modules that store them.

**Role**
A named set of permissions that can be assigned to users.

## S

**SMTP Profiles**
Email sending configuration used for expiry reminder notifications. Super Admin only.

**Soft Delete**
A deletion that hides the record from the UI but keeps it in the database for potential restoration.

**Super Admin**
A role with unrestricted access to every module and feature.

## T

**Tasks**
Work items that can be assigned to users, tracked by status, and organized on a Kanban board.

## U

**User Permission Overrides**
See **Permission Override**.

## V

**Vault**
A secure credential storage module with personal access by default.

**View Permission**
Allows seeing records in a module. For infrastructure modules, this shows all records.

## W

**Webhooks**
Outbound HTTP endpoints that trigger on system events. Super Admin only.

---

## Related Modules

- [Permission Reference](../../../08_PERMISSION_REFERENCE.md)
- [Role Matrix](../../../09_ROLE_MATRIX.md)
- [FAQ](../../../07_FAQ.md)
