# OpsPilot RBAC & Permission Guide

## Overview

RBAC stands for Role-Based Access Control. It controls who can see what and who can do what in OpsPilot.

---

## The Three Roles

| Role | Who Has It | What They Can See |
|------|-----------|-------------------|
| **Super Admin** | System administrators | Everything. No restrictions. |
| **Admin** | IT Managers, team leads | Records matching their module permissions. |
| **User** (default) | Everyone else | Only records they created (user_id = their ID). |

A user can have multiple roles. If a user has both Admin and User roles, they get the higher level of access.

---

## Ownership and Visibility

Understanding these three concepts is important:

| Concept | Meaning | Example |
|---------|---------|---------|
| **Created By (user_id)** | The person who created the record | Ahmad creates a domain → user_id = Ahmad's ID |
| **Assigned User** | The person responsible for an item | Asset assigned to Sara → assigned_to = Sara's ID |
| **Module** | Which category the record belongs to | Domain → Modules → Domains |

### How visibility works:

- **Super Admin:** Sees everything, regardless of user_id or module.
- **Admin:** Sees records that belong to modules they have Read permission on. The system adds a filter: "only show records whose module_id is in the list of modules this admin can read."
- **User (default):** Sees only records where user_id = their own ID. They own their data.

### Example:

```
Ahmad (User role) creates a Domain with module "Web Services"
Bilal (User role) creates a Domain with module "Web Services"
Sara (Admin role) has Read permission on "Web Services"

Sara can see BOTH domains (because she has module access).
Ahmad can see ONLY his own domain.
Bilal can see ONLY his own domain.
```

---

## RBAC Hierarchy (Three Levels)

There are three levels of permissions, evaluated in this order:

```
Level 1: Super Admin bypass
  ├── If user is Super Admin → ALLOW everything
  │
  └── If not Super Admin → check Level 2

Level 2: Role-level Module Permissions
  ├── Admin role gets CanRead, CanCreate on "Domains" module
  ├── All users with Admin role inherit this
  │
  └── If not enough → check Level 3

Level 3: User-level Overrides
  ├── Ahmad gets CanDelete override on "Domains" module
  ├── But Bilal (same role) does not have CanDelete
  │
  └── User Overrides > Role Permissions
```

### Key rule:
**User-level overrides always beat role-level permissions.**

If a role says "No Delete" but a user override says "Can Delete" → the user CAN delete.

If a role says "Can Delete" but a user override says "No Delete" (unchecked) → the user CANNOT delete.

---

## Module vs Role Templates vs User Overrides

| Feature | What It Does | Who Sets It |
|---------|-------------|-------------|
| **Module Permissions** | Define what each role can do on each module (Create, Read, Update, Delete, Export, Reveal) | Super Admin |
| **Role Templates** | Pre-configured sets of permissions for common job types (e.g., "IT Support Template"). Apply to a role to quickly set all permissions. | Super Admin |
| **User Overrides** | Override role-level permissions for a specific user. Useful when one person needs different access than their team. | Super Admin |

### When to use each:

- **Use Module Permissions** when an entire team needs the same access level.
- **Use Role Templates** when onboarding a new role type (e.g., "Help Desk").
- **Use User Overrides** when exactly one person needs different access (e.g., a junior admin who should not delete).

---

## The Seven Permission Actions

| Action | What It Allows |
|--------|---------------|
| **Create** | Add new records |
| **Read** | View existing records |
| **Update** | Edit existing records |
| **Delete** | Remove records |
| **Approve** | Approve pending changes (future use) |
| **Export** | Download data as CSV |
| **Reveal** | View plaintext passwords from Vault |

---

## Module Scope vs Ownership Scope

### Module Scope (Admin)
The system applies a filter: `WHERE module_id IN (accessible modules)`.

Example: If Admin has Read on "Domains" and "Hosting" modules, they see all domains and hosting records regardless of who created them.

### Ownership Scope (User)
The system applies a filter: `WHERE user_id = current_user_id`.

Example: If User has no module permissions, they only see records where `user_id` matches their account.

---

## Super Admin Safety Rules

| Rule | Explanation |
|------|-------------|
| **Can manage everything** | No module is off-limits. You can create, read, update, delete any record. |
| **Cannot bypass audit logging** | Every action you take is logged in Activity Logs. There is no "invisible mode." |
| **Should not delete critical records without checking dependencies** | Deleting a Service Provider is blocked if records depend on it. Deleting an Asset is blocked if it is assigned. These protections exist for a reason — do not force-delete unless absolutely certain. |
| **Should not assign Super Admin role casually** | Only give Super Admin to people who need full system access. Everyone else should get Admin or User roles. |
| **Check login audits regularly** | Monitor for failed login attempts. Investigate unusual patterns. |

---

## Default Behaviors

- **New users** get the role specified in the `DEFAULT_ROLE_SLUG` environment setting (usually "user").
- **Without any module permissions**, a User cannot see any module records at all.
- **With Read permission granted**, a User sees **all records** in that module — not just their own. The `user_id` column tracks ownership/history but does not gate visibility for IT Staff/User role.
- **Without Read permission**, a user cannot even see the module in the sidebar.
- **The role `*` (wildcard)** matches everything — do not assign it unless you want unrestricted access.

---

## Common Scenarios

### Scenario 1: IT Support needs to see all domains but not delete them
1. Create role "it-support" (or use existing)
2. Go to **Module Permissions**
3. Find the "Domains" module
4. Set: Read = Yes, Create = Yes, Update = Yes, Delete = No
5. Save

### Scenario 2: Junior admin should not export data
1. In Module Permissions, find their role
2. Uncheck Export on all modules
3. Save

### Scenario 3: One user needs to delete but rest of team does not
1. Go to the user's edit page
2. Click **Permissions** tab
3. Find the module
4. Check Can Delete
5. Save — this override beats the role setting

### Scenario 4: New employee needs access
1. Create user account
2. Assign role
3. If role already has correct permissions → done
4. If not → update Module Permissions for that role, or set User Overrides

---

## Checking Your Access

Any user can go to **My Access** in the sidebar to see:
- Their roles
- Their module permissions
- Their privileges

This is a read-only view — you cannot change anything here. Ask your Super Admin for changes.

---

## Quick Reference

| If you are a... | You see... | You can... |
|----------------|-----------|-----------|
| **Super Admin** | Everything | Everything |
| **Admin on "Domains"** | All domain records | Create, Read, Update, Delete (as permitted) |
| **User** | All records in modules with Read permission | Create, Read, Update, Delete as permitted by module permissions |
| **Read-Only** (no Create/Update/Delete) | All records in modules with Read permission | Only view — cannot create, edit, delete, reveal, or export |
