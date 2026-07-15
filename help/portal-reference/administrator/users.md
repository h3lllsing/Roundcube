---
title: Users
description: Manage user accounts
category: Administrator
icon: users
---

## Overview

The Users module manages portal user accounts. Administrators can create, activate, suspend, and assign roles to users.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View the user list |
| Manage | Create and edit user accounts |

> **Note**: Deleting users is a Super Admin function available from the Users screen.

## Features

### User Properties
- Name, email, username
- Status (active, suspended, invited)
- Role assignment (one or more roles)
- Two-Factor Authentication status

### Account Statuses
| Status | Description |
|--------|-------------|
| Active | Full portal access |
| Suspended | Cannot log in |
| Invited | Registration pending |

### Two-Factor Authentication
Users can enable TOTP-based 2FA from their Profile. Administrators can reset 2FA for users who lose access.

## Related Docs
- [Roles](roles.md)
- [Module Permissions](module-permissions.md)
- [Profile / Account](/quick-start#profile--account)
