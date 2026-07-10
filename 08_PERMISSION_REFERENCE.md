# Permission Reference

> **Audience:** Administrators, Super Admins — **Purpose:** Reference for what each permission does on every module and how to verify effective permissions

## Table of Contents

- [Checking Permissions](#checking-permissions)
- [Permission Definitions](#permission-definitions)
- [Infrastructure Modules](#infrastructure-modules)
- [Productivity Modules](#productivity-modules)
- [Administration Modules](#administration-modules-super-admin-only)
- [Integration Modules](#integration-modules-super-admin-only)
- [How Permissions Work Together](#how-permissions-work-together)
- [User Permission Overrides](#user-permission-overrides)

---

## Checking Permissions

### Purpose
Verify what you (or another user) can actually see and do on each module.

### When to Use
- A user cannot see a module or perform an action
- You are troubleshooting a permission issue
- Before granting access — confirm current state first
- After making permission changes — verify they took effect

### Permission Required
Any user can check their own permissions via **My Permissions**. Super Admin can review any user's permissions by editing their profile.

### Step-by-Step Workflow

**Checking your own permissions:**
1. Go to **My Permissions** in the sidebar (under Account)
2. The page displays:
   - Your assigned roles
   - A table with every module as a row
   - Columns for each permission (View, Create, Edit, Delete, Reveal, Export)
   - The effective value (On/Off) and source (Role or Override)

**Checking another user's permissions (Super Admin only):**
1. Go to **Administration → Users**
2. Open the user's profile
3. Click **Edit Permissions**
4. You see the same matrix with On/Off/Unset states
5. Unset means the user inherits from their role

### Best Practices
- Always check My Permissions before contacting support — it answers most "why can't I" questions
- After changing a role's permissions, check a user in that role to verify the change propagated
- Permission overrides show as "On (Override)" or "Off (Override)" — use this to distinguish role vs user-level settings

---

## Permission Definitions

| Permission | What It Lets You Do | Default for Most Roles |
|------------|---------------------|------------------------|
| **View** | See records in a module. For infrastructure modules, ALL records. Module hidden from sidebar without View. | On (where granted) |
| **Create** | Add new records. Shows the Create button. | On for operational roles |
| **Edit** | Modify existing records. Shows the Edit button. | On for operational roles |
| **Delete** | Remove records (soft-delete). Shows the Delete button. | Off for IT Support |
| **Reveal** | View decrypted passwords on modules that store them. | Off for Admin (default) |
| **Export** | Download module data as a CSV file. | On for Admin, Off for IT Staff |
| **Approve** | Reserved for future use. Not active. | Off |
| **Import** | Upload data via CSV. Super Admin only. | Off |

> **Critical rule:** Every permission is **independent**. Having View does NOT grant any other permission. Having Reveal does NOT grant View. Each must be granted separately.

---

## Infrastructure Modules

### Module-Wide Access Rule

All 9 infrastructure modules use **module-wide access**: any user with View permission sees EVERY record in that module, regardless of who created it.

### Permissions per Module

| Module | View | Create | Edit | Delete | Reveal | Export | Special Notes |
|--------|------|--------|------|--------|--------|--------|---------------|
| **Service Providers** | All records | ✅ | ✅ | ✅ | ✅ | ✅ | Stores provider account credentials |
| **Domains** | All records | ✅ | ✅ | ✅ | N/A | ✅ | No password field — domains do not store credentials |
| **Hosting** | All records | ✅ | ✅ | ✅ | ✅ | ✅ | Stores server/cPanel passwords |
| **VPS** | All records | ✅ | ✅ | ✅ | ✅ | ✅ | Stores SSH/root passwords |
| **Domain Emails** | All records | ✅ | ✅ | ✅ | ✅ | ✅ | Stores mailbox passwords |
| **VoIP** | All records | ✅ | ✅ | ✅ | ✅ (main + extension) | ✅ | Two password fields: main account + per-extension |
| **Other Services** | All records | ✅ | ✅ | ✅ | ✅ | ✅ | Catch-all for uncategorized services |
| **Expiry Trackers** | All records | ✅ | ✅ | ✅ | N/A | ✅ | Tracks renewal dates; no password field |
| **Assets** | All records | ✅ | ✅ | ✅ | N/A | ✅ | Supports assign/return workflow; no passwords |

### Additional Capabilities

| Action | Who Can Do It |
|--------|---------------|
| **Restore** (soft-deleted records) | Super Admin only |
| **Force-delete** (permanent removal) | Super Admin only |
| **Bulk update status** | Users with **Edit** permission on the module |
| **Bulk delete** | Users with **Delete** permission on the module |
| **Monitor** (ping service URL) | Users with access to the record |

---

## Productivity Modules

### Tasks — Mixed Access Model

- You see tasks in **modules you can access**
- You also see tasks **assigned to you** (even if from a module you cannot access)
- Super Admin sees ALL tasks regardless of module or assignment

| Permission | Behavior |
|------------|----------|
| **View** | Mixed access as described above |
| **Create** | Requires Create on Tasks module |
| **Edit** | Requires Edit on Tasks module |
| **Delete** | Requires Delete on Tasks module (ownership NOT enforced for Delete — if you have Delete permission, you can delete any task in your scope) |

### Vault — Personal Access with Admin Scope

- Default: You see only your own entries
- Super Admin: All entries
- Administrators: Own entries + entries in vault modules they can access

| Permission | Behavior |
|------------|----------|
| **View** | Own entries only (personal access); broader scope for Admin/Super Admin |
| **Create** | Requires Create on Vault module |
| **Edit** | Requires Edit on Vault module |
| **Delete** | Requires Delete on Vault module (ownership enforced — you can only delete your own entries) |
| **Reveal** | Requires Reveal independently of View |

### Notes — Split Access Model

- **Module-attached notes** (linked to a module): Visible to anyone with View on that module
- **Global notes** (not linked to a module): Visible only to the note creator

| Permission | Behavior |
|------------|----------|
| **View** | Module-attached: based on module access. Global: creator only. |
| **Create** | Available to all authenticated users |
| **Edit** | Only the note creator can edit |
| **Delete** | Only the note creator can delete |
| **Restore/Forece-delete** | Super Admin only |

### Calendar

The calendar displays:
- **Expiry dates** from infrastructure modules you can access
- **Task due dates** for tasks visible to you

Super Admin sees all dates across all modules. No special Calendar permission exists — access is based on module permissions.

### Monitor

Performs on-demand availability checks for services with a monitoring URL configured. Accessible from a service's detail page. Checks HTTP status and SSL certificate validity.

---

## Administration Modules (Super Admin Only)

These modules are behind `role:super-admin` route middleware. Non-Super Admin users:
- Cannot see them in the sidebar
- Cannot access their URLs directly (returns 403)
- Cannot see their data via search or other cross-module features

| Module | Purpose |
|--------|---------|
| **Users** | Create, edit, suspend, clone, delete user accounts |
| **Roles** | Create, edit, delete role definitions |
| **Privileges** | Manage system-level capabilities (attached to roles) |
| **Module Permissions** | Configure role × module permission matrix |
| **Activity Logs** | Audit trail — every create, edit, delete, reveal, login event |
| **Login Audits** | Record of login attempts (success, failure, suspended) |
| **Notifications** | System-wide notification management |
| **Attachments** | File upload management |

---

## Integration Modules (Super Admin Only)

Same route-level restriction as Administration modules. Not accessible to non-Super Admin users.

| Module | Purpose |
|--------|---------|
| **Webhooks** | Configure outbound webhook endpoints for event notifications |
| **API Tokens** | Manage personal Sanctum tokens for API access |
| **Import** | Upload CSV data to bulk-create records |
| **Export** | Download module data as CSV files |
| **Reports** | Pre-built operational reports with CSV export |
| **SMTP Profiles** | Configure email sending profiles for expiry reminders and test emails |

---

## How Permissions Work Together

### Visibility by Module Type

| Module Type | With View | Without View |
|-------------|-----------|--------------|
| **Infrastructure (9 modules)** | ALL records in those modules | Module hidden from sidebar; no access via URL; zero records visible |
| **Vault** | Own entries + admin scope if applicable | No entries visible; module hidden |
| **Tasks** | Module-accessible tasks + assigned tasks | No tasks visible; module hidden |
| **Notes** | Module-attached (by module access) + own global | Only own global notes visible |

### Access Without View Permission

If a user lacks View permission on a module:
- The module is **hidden** from their sidebar
- They see **zero records** — not even records they created
- Direct URL access returns a permission error

This means: **View is the foundation permission.** Without it, no other permission matters for that module.

### Reveal vs View

These two permissions are often confused but are completely independent:

| Scenario | Has View? | Has Reveal? | Can See Record? | Can See Password? |
|----------|-----------|-------------|-----------------|-------------------|
| Standard user | Yes | No | ✅ | ❌ (masked) |
| Privileged user | Yes | Yes | ✅ | ✅ |
| No access | No | Yes | ❌ (403 error) | ❌ |

Reveal without View is practically useless — you need View to access the record first.

### Delete vs Bulk Delete

| Scenario | Delete Permission | Behavior |
|----------|------------------|----------|
| Single record delete | Yes | Deletes the specific record |
| Single record delete | No | 403 permission error |
| Bulk delete | Yes (module-level) | Deletes ALL selected records regardless of ownership |
| Bulk delete | No | Bulk action UI hidden |

**Key distinction:** Individual delete may have ownership checks (Vault, Notes). Bulk delete checks module-level Delete permission and does NOT enforce ownership.

---

## User Permission Overrides

### Purpose
Grant or deny a specific permission for an individual user, bypassing their role's defaults.

### When to Use
- A user needs one permission their role does not grant (e.g., an Admin who needs Reveal)
- A user should be blocked from a permission their role normally grants (e.g., an IT Staff member who should not Create)
- You have exceptions that do not justify creating a new role

### How Overrides Work

| Override State | Effect |
|----------------|--------|
| **Unset (blank)** | User inherits from their role |
| **On** | Force-grant — overrides a role that denies this permission |
| **Off** | Force-deny — blocks this permission even if the role grants it |

### Checking Override Status

Users can see their override status on the **My Permissions** page:
- "On (Override)" — A Super Admin force-granted this
- "Off (Override)" — A Super Admin force-denied this
- No label — Value comes from the role

### Best Practices for Super Admins
- Use overrides for individual exceptions, not group policies
- Create custom roles if multiple users need the same exception
- Audit overrides quarterly — remove ones that are no longer needed
- Overrides persist when changing a user's role — check them after role changes

---

## Related Pages

- [Role Matrix](09_ROLE_MATRIX.md) — Role × Module access reference
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — Configuring permissions
- [Admin Guide](03_ADMIN_GUIDE.md) — Using permissions in daily work
- [FAQ](07_FAQ.md) — Common permission questions
