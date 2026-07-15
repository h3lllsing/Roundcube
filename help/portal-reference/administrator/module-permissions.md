---
title: Module Permissions
description: Configure per-module access controls
category: Administrator
icon: lock
---

## Overview

Module Permissions define what actions a role can perform on each portal module. Permissions are assigned at the role level and use a three-layer model: Inherit, Allow, Deny.

## Permission Levels

| Level | Effect |
|-------|--------|
| Inherit | Uses the role template or system default |
| Allow | Grants the capability |
| Deny | Explicitly blocks the capability |

Deny takes precedence over Allow — if any role grants Deny on a capability, the user is denied regardless of other role assignments.

## Capabilities Per Module

Each module exposes up to four controls:

| Control | Description |
|---------|-------------|
| Access | View and list records |
| Manage | Create, edit, delete records |
| Reveal | View credential values |
| Import/Export | Bulk import or export records |

Some modules only expose Access and Manage. See [Permission Reference](/permission-reference) for the full module-capability matrix.

## Full Access
Users with a Super Admin or Full Access role bypass all permission checks. They see every module and record without restriction.

## Related Docs
- [Roles](roles.md)
- [Privileges](privileges.md)
- [Permission Reference](/permission-reference)
- [Understanding Permissions](/understanding-permissions)
