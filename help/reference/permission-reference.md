# Permission Reference

> **Audience:** All Users — **Purpose:** Understand how permissions work, what each control means, and how effective access is determined.

## Overview

OpsPilot uses a role-based permission system. Each user is assigned one or more roles, and each role defines baseline permissions for every module. Administrators can then override specific permissions for individual users.

Permission decisions never expose raw database column names. The interface uses four conceptual controls:

| Control | Effect |
|---------|--------|
| **Access** | View records in the module |
| **Manage** | Create, update, and delete records |
| **Import** | Bulk-import records from external sources |
| **Export** | Export module data |

**Full Access** is a convenience that enables Access, Manage, Import, and Export simultaneously.

## Permission Controls vs Database Columns

The UI controls map to multiple database columns behind the scenes:

| UI Control | Database Effect |
|------------|-----------------|
| Access = Allow | `can_read` = true |
| Manage = Allow | `can_create` = true, `can_update` = true |
| Import = Allow | `can_import` = true (only where supported) |
| Export = Allow | `can_export` = true (only where supported) |

You never interact with `can_create`, `can_read`, or other raw column names directly.

## Three-Layer Resolution

The system resolves your effective permission through three layers, from highest to lowest priority:

1. **User Override** — A Super Admin has set a specific Allow or Deny for you on this module. This takes priority over everything else.
2. **Role Permission** — Your combined permissions from all assigned roles. If any role grants a permission, you have it (OR merge).
3. **Default** — If neither a user override nor a role grants the permission, it is denied.

### Inherit / Allow / Deny

When a Super Admin edits your individual permissions, each control has three states:

| State | Meaning |
|-------|---------|
| **Inherit** | Follow the role baseline — no override |
| **Allow** | Explicitly grant this control, overriding the role |
| **Deny** | Explicitly deny this control, overriding the role |

The **Inherit All** button resets all controls for a module back to Inherit.

### Multiple Roles

If you have multiple roles, permissions are OR-merged. If **any** of your roles grants Access to a module, you have Access. A Deny in one role cannot be outvoted by an Allow in another role at the role level — but a user override can still grant it.

### User Override Precedence

User overrides are checked first. If a user override exists for a control and its value is not null (Inherit), that value wins. The role baseline is consulted only when the override is null.

## What Is Not an Assignable Control

The following actions are **not** normal user-facing controls in the permissions editor:

| Action | Who Can Perform It |
|--------|-------------------|
| **Delete** | Super Admin only for shared business records. The `can_delete` column exists but is overridden to deny for non-Super Admin users at the service layer. |
| **Reveal** | Controlled by a separate hierarchy based on Access + explicit settings. See *Credential Reveal Reference*. |
| **Approve** | Reserved for future use and not exposed as a standard control. |

## Super Admin

Users with the **Super Admin** role bypass all permission checks. They have unrestricted Access, Manage, Import, and Export on every module. The My Permissions page shows a green banner confirming unrestricted access.

## Users With No Roles

Users with no roles assigned have no module permissions. Every control evaluates to denied unless a user override explicitly grants it. The My Permissions page shows "No roles assigned."

## Import and Export Support

Not every module supports Import or Export. The available capabilities are defined in `config/permissions.php`:

- **Importable modules:** domains, hostings, vps, voip, service-providers, domain-emails, other-services, expiry-trackers, assets, g-mails, tasks, notes, vault, activity-logs, login-audits, users, roles, privileges, attachments, webhooks, tokens
- **Exportable modules:** same set as importable

If a module does not support Import or Export, the corresponding control shows a dash (`—`) on the My Permissions page.

## Credential Reveal

Revealing passwords is not a standalone assignable permission. It is resolved through a separate centralized hierarchy documented in the *Credential Reveal Reference*.
