# Understanding Permissions

> **Audience:** All Users — **Purpose:** Learn how permissions work in plain language, including roles, overrides, and what each control means.

## What Are Permissions?

Permissions control what you can see and do in each module. They prevent accidental changes and ensure users only access the data they need.

## The Four Controls

Every module uses up to four controls:

| Control | What It Lets You Do |
|---------|---------------------|
| **Access** | View records in the module |
| **Manage** | Create new records, edit existing ones, and delete records you created |
| **Import** | Upload a file to add many records at once |
| **Export** | Download module data as a file |

You may see a dash (`—`) for Import or Export on some modules — this means that module does not support that capability.

**Full Access** is a shortcut that enables all four controls at once.

## Where Permissions Come From

Your effective permissions are built from two sources:

### 1. Your Roles

Every user is assigned one or more roles. Each role defines a baseline set of permissions for every module. For example, an Admin role might grant Access and Manage on Domains but only Access on the Vault.

If you have multiple roles, their permissions are combined. If any role grants you Access to a module, you have Access.

### 2. Individual Overrides

A Super Admin can override your role baseline for a specific module. Each control can be set to:

| State | Meaning |
|-------|---------|
| **Inherit** | Use the role baseline — no individual change |
| **Allow** | Force this control on, even if your role denies it |
| **Deny** | Force this control off, even if your role grants it |

Overrides take priority over roles. If a Super Admin has set Access to Deny for you on a module, it does not matter what your role says.

## Why Another User Sees Different Access

If a colleague sees more or fewer modules than you, it is because:

- They have a different role
- A Super Admin has set individual overrides for them
- They have multiple roles that expand their access

This is by design. Permission changes are audited and logged.

## What You Cannot Change

- You cannot grant yourself permissions
- You cannot see modules you do not have Access to
- You cannot reveal passwords without the proper authorisation (see *Credential Reveal Reference*)
- You cannot delete shared business records unless you are a Super Admin

## Super Admin

Super Admin is a special role that bypasses all permission checks. Super Admins have unrestricted Access, Manage, Import, and Export on every module.

## No Roles Assigned

If you have no roles, every control defaults to denied. The My Permissions page will show "No roles assigned."
