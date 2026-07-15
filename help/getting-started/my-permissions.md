# My Permissions

> **Audience:** All Users — **Purpose:** Understand what the My Permissions page shows and how to interpret it.

## Accessing My Permissions

Click your avatar in the top-right corner of any page and select **My Permissions** from the dropdown menu. The page displays your assigned roles and effective module permissions.

## Roles Section

The top of the page shows your assigned roles as coloured badges. If you have multiple roles, all of them appear here.

If you see **"No roles assigned"**, you have no role-based permissions. Any access you have would come from individual user overrides set by a Super Admin.

## Super Admin Banner

If you are a Super Admin, a green banner explains that you have unrestricted access to all modules. The table below shows every module with all controls checked.

## Module Permissions Table

The table lists every module you have permission to see, organised in rows with these columns:

| Column | What It Shows |
|--------|---------------|
| **Module** | The name of the module |
| **Feature** | The feature group this module belongs to |
| **Access** | Whether you can view records in this module |
| **Manage** | Whether you can create and update records |
| **Import** | Whether you can bulk-import records (only shown for importable modules) |
| **Export** | Whether you can export module data (only shown for exportable modules) |

### Reading the Check Marks

| Symbol | Meaning |
|--------|---------|
| **✓ (green)** | You have this control |
| **✗ (red)** | You do not have this control |
| **— (dash)** | This module does not support this capability |

## How the Table Is Built

For non-Super Admin users, the system:

1. Collects your role permissions for each module (OR-merged across all roles)
2. Applies any individual user overrides on top
3. Displays only modules where at least one control is active

Modules you have zero access to do not appear in the table.

## Why You Cannot See Certain Modules

If a module you expected is missing from the table, it means:

- None of your roles grant Access to that module
- No user override has granted you Access
- The module may not be configured for your account type

## What This Page Does Not Show

The My Permissions page intentionally omits:

- **Raw `can_*` column names** — controls are presented as Access, Manage, Import, and Export
- **Credential reveal status** — reveal is not a standard assignable control; see *Credential Reveal Reference*
- **Delete capability** — Delete is not a normal assignable control; only Super Admins can delete shared business records
- **Permission source details** — whether a permission comes from a role or an override is shown in the user management interface, not on the self-service page
