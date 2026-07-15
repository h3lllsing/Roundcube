---
title: SMTP Profiles
description: Manage email-sending configurations
category: Administrator
icon: mail
---

## Overview

SMTP Profiles store email server configurations used by the portal to send outgoing messages — including password resets, notifications, and reports.

## Permission Controls

| Control | Description |
|---------|-------------|
| SMTP Profiles | Access the SMTP Profiles screen |

The SMTP Profiles privilege controls access. Users also need Manage on the SMTP Profiles module.

## Features

### Profile Configuration
Each profile stores:
- Name
- Host and port
- Encryption (TLS, SSL, none)
- Username and password
- Default from-address

### Profile Selection
One profile is set as the default for all system emails. Profiles can be swapped without downtime.

## Related Docs
- [Privileges](privileges.md)
- [Notifications](/quick-start#notifications)
