# Role Matrix

> **Audience:** Administrators, Super Admins — **Purpose:** Reference for what each role can access by default on every module

## Table of Contents

- [How to Use This Matrix](#how-to-use-this-matrix)
- [Matrix Legend](#matrix-legend)
- [Infrastructure Modules](#infrastructure-modules)
- [Productivity Modules](#productivity-modules)
- [Administration Modules](#administration-modules)
- [Integration Modules](#integration-modules)
- [Role Comparison](#role-comparison)
- [Notes by Role](#notes-by-role)

---

## How to Use This Matrix

### Purpose
Quickly determine what a given role can do on a given module in the default configuration.

### When to Use
- Planning user assignments — which role fits which job function
- Troubleshooting access issues — does the user's role grant what they need?
- Designing custom roles — start from the template most similar to what you need
- Auditing — confirm users have appropriate access

### Important Notes
- **Roles can be customized.** Your Super Admin may have modified role permissions or set user-level overrides.
- **Check My Permissions** to see your ACTUAL effective permissions — they may differ from this matrix.
- **Module-wide access applies.** Granting View on an infrastructure module gives access to ALL records, not just the user's own.

---

## Matrix Legend

| Icon | Meaning | Includes |
|------|---------|----------|
| **Full** | All permissions | Create, View, Edit, Delete, Reveal, Export |
| **Limited** | Operational permissions only | Create, View, Edit (no Delete, Reveal, Export) |
| **View + Reveal** | View and password reveal | View + Reveal only (no Create, Edit, Delete, Export) |
| **View + Export** | View with CSV export | View + Export only (no Create, Edit, Delete) |
| **View Only** | Read-only access | View only — all other permissions Off |
| **None** | No access | Module hidden from sidebar; URL access returns 403 |
| **Personal** | Self-only access | Own entries only (not module-wide) |

---

## Infrastructure Modules

All infrastructure modules use **module-wide access** — users with View see every record in the module regardless of who created it.

| Module | Super Admin | Administrator | IT Staff | Read Only |
|--------|-------------|---------------|----------|-----------|
| **Service Providers** | Full | Full | View + Reveal | View Only |
| **Domains** | Full | Full | View + Reveal | View Only |
| **Hosting** | Full | Full | View + Reveal | View Only |
| **VPS** | Full | Full | View + Reveal | View Only |
| **Domain Emails** | Full | Full | View + Reveal | View Only |
| **VoIP** | Full | Full | View + Reveal | View Only |
| **Other Services** | Full | Full | None | View Only |
| **Expiry Trackers** | Full | Full | None | View Only |
| **Assets** | Full | Full | None | View Only |

> **Note on Administrator Reveal:** The default Administrator role has Reveal = **Off** for all modules. If an Admin needs to reveal passwords, a Super Admin must grant this via a permission override.

---

## Productivity Modules

| Module | Super Admin | Administrator | IT Staff | Read Only |
|--------|-------------|---------------|----------|-----------|
| **Tasks** | Full | Limited | None | View Only (in-scope) |
| **Vault** | Full | View + Reveal (module-scoped) | None | View Only (personal) |
| **Notes** | Full | Limited | None | View Only (module-access) |
| **Calendar** | Full | Module-scoped | None | Module-scoped |
| **Monitor** | Full | Limited | None | View Only |

**Access details:**
- **Tasks (Limited):** Admin can create, view, edit. No Delete or Reveal on tasks. Mixed access model — sees module tasks + assigned tasks.
- **Vault (View + Reveal, module-scoped):** Admin sees own vault entries plus entries in accessible vault modules. Reveal requires the Reveal permission (Off by default).
- **Notes (Limited):** Admin sees module-attached notes for accessible modules plus own global notes. Can create and edit own notes.
- **Calendar (Module-scoped):** Shows expiry dates for accessible modules only.
- **Read Only Tasks:** View-only. Sees tasks in accessible modules + tasks assigned to them. Cannot change status.

---

## Administration Modules

All Administration modules are behind `role:super-admin` route middleware. Non-Super Admin roles see "None" across the board.

| Module | Super Admin | Administrator | IT Staff | Read Only |
|--------|-------------|---------------|----------|-----------|
| **Users** | Full | None | None | None |
| **Roles** | Full | None | None | None |
| **Privileges** | Full | None | None | None |
| **Module Permissions** | Full | None | None | None |
| **Activity Logs** | Full | None | None | None |
| **Login Audits** | Full | None | None | None |
| **Notifications** | Full | Personal | Personal | Personal |
| **Attachments** | Full | None | None | None |

> **Notifications (Personal):** All users can see their own notifications. Super Admin sees all.

---

## Integration Modules

Same route-level restriction — all Integration modules require Super Admin role.

| Module | Super Admin | Administrator | IT Staff | Read Only |
|--------|-------------|---------------|----------|-----------|
| **Webhooks** | Full | None | None | None |
| **API Tokens** | Full | None | None | None |
| **Import** | Full | None | None | None |
| **Export** | Full | With Export (per module) | None | None |
| **Reports** | Full | None | None | None |
| **SMTP Profiles** | Full | None | None | None |

> **Export (With Export):** Admin can export data from modules where their role has Export permission enabled. This applies to all 9 infrastructure modules by default.

---

## Role Comparison

### Summary Table

| Capability | Super Admin | Admin | IT Staff | Read Only |
|------------|-------------|-------|----------|-----------|
| **System administration** | ✅ | ❌ | ❌ | ❌ |
| **User management** | ✅ | ❌ | ❌ | ❌ |
| **Role & permission management** | ✅ | ❌ | ❌ | ❌ |
| **View all infrastructure records** | ✅ | ✅ (module-wide, 9) | ✅ (6 modules) | ✅ (module-wide) |
| **Create infrastructure records** | ✅ | ✅ (9 modules) | ✅ (6 modules) | ❌ |
| **Edit infrastructure records** | ✅ | ✅ (9 modules) | ✅ (6 modules) | ❌ |
| **Delete infrastructure records** | ✅ | ✅ | ❌ | ❌ |
| **Export data** | ✅ | ✅ (9 modules) | ❌ | ❌ |
| **Reveal passwords** | ✅ | ❌ (default) | ✅ (6 modules) | ❌ |
| **Restore / Force-delete** | ✅ | ❌ | ❌ | ❌ |
| **View vault entries** | ✅ (all) | ✅ (module-wide + own) | ❌ | ✅ (own only) |
| **Manage SMTP, webhooks, reports** | ✅ | ❌ | ❌ | ❌ |
| **Import data** | ✅ | ❌ | ❌ | ❌ |
| **View reports** | ✅ | ❌ | ❌ | ❌ |

### Visibility Models by Role

| Role | Infrastructure Visibility | Vault Visibility | Tasks Visibility |
|------|--------------------------|------------------|------------------|
| **Super Admin** | ALL records, unrestricted | ALL vault entries | ALL tasks |
| **Administrator** | ALL records in accessible modules | Own entries + entries in accessible vault modules | Tasks in accessible modules + assigned tasks |
| **IT Staff** | ALL records in the 6 assigned modules | No access | No access (unless customized) |
| **Read Only** | ALL records in accessible modules | Own entries only | Tasks in accessible modules + assigned tasks |

---

## Notes by Role

### Super Admin

- Unrestricted access to every module and feature
- Can restore and force-delete records
- Sees Server Health and SMTP Status dashboard widgets
- Protected from self-demotion (cannot remove own Super Admin role)
- Cannot delete the last remaining Super Admin user
- Can view all activity logs and login audits

### Administrator

- Default role has Reveal = **Off** — must be explicitly granted by permission override
- Sees ALL records in modules with View (module-wide access)
- Cannot restore or force-delete records
- Administration and Integration modules are inaccessible (route-level restriction)
- Quick Actions dashboard shortcut is available for modules with Create permission
- Notifications visible for accessible modules

### IT Staff

- Default template grants access to **6 infrastructure modules**: Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails
- Other Services, Expiry Trackers, and Assets are denied by default
- Has Reveal permission on all 6 accessible modules
- Has Create and Edit on all 6 modules
- Cannot Delete or Export on any module
- If Tasks access is granted, sees tasks in accessible modules + assigned tasks
- All reveal actions are audited

### Read Only

- Has View permission on all 9 infrastructure modules and all 5 productivity modules
- All other permissions (Create, Edit, Delete, Reveal, Export) are Off on ALL modules
- Vault access is personal — only own entries visible
- Notes access is mixed — module-attached notes by module access, global notes by ownership
- Passwords always appear masked (cannot reveal)
- Cannot create, edit, or delete anything

---

## Related Pages

- [Permission Reference](08_PERMISSION_REFERENCE.md) — Per-module permission details
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — Configuring roles and permissions
- [Admin Guide](03_ADMIN_GUIDE.md) — Using Administrator permissions
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Using IT Support permissions
- [Read Only Guide](05_READ_ONLY_GUIDE.md) — Using Read Only permissions
