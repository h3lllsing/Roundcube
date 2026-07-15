---
title: Privileges
description: System-level capability toggles
category: Administrator
icon: toggle-left
---

## Overview

Privileges are system-level toggles that enable or disable specific portal-wide features. Unlike Module Permissions (which grant access to modules), Privileges control global feature availability.

## How Privileges Work

Privileges are assigned to roles alongside module permissions. They act as feature flags — when a privilege is disabled, even users with module Access cannot use that feature.

## Available Privileges

| Privilege | Effect |
|-----------|--------|
| Import | Enable CSV import across all importable modules |
| Export | Enable CSV export across all exportable modules (Super Admin only at runtime) |
| Bulk Actions | Enable bulk operations (mass delete, mass status change) |
| Reports | Access the reports dashboard |
| Webhooks | Manage webhook endpoints |
| API Tokens | Manage API access tokens |
| SMTP Profiles | Manage SMTP mail profiles |
| Attachments | Upload and manage file attachments |
| Activity Logs | View system activity logs |
| Login Audits | View login history audit logs |

## Related Docs
- [Roles](roles.md)
- [Module Permissions](module-permissions.md)
- [Understanding Permissions](/understanding-permissions)
