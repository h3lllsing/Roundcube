---
title: Roles
description: Define role-based access groups
category: Administrator
icon: shield
---

## Overview

Roles group users and define their base permissions. Each role carries a set of module permissions. Users inherit the union of all their assigned roles' permissions.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View roles list |
| Manage | Create, edit, and delete roles |

## Role Properties
- Name and description
- Assigned users count
- Module permissions (set per role)

## Permission Inheritance
A user's effective permissions = Union of all permissions from all assigned roles. See [Understanding Permissions](/understanding-permissions) for details.

## Role Templates
New roles can be created from pre-defined templates that provide a starting set of module permissions. See [Role Templates](role-templates.md).

## Related Docs
- [Users](users.md)
- [Module Permissions](module-permissions.md)
- [Role Templates](role-templates.md)
- [Understanding Permissions](/understanding-permissions)
