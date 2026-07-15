---
title: VPS
description: Manage Virtual Private Servers
category: Portal Reference
subcategory: Infrastructure
icon: server
---

## Overview

The VPS module tracks Virtual Private Servers that you manage. Each VPS record stores connection details, provider information, and operational notes.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View and list VPS records |
| Manage | Create, edit, and delete VPS records |
| Reveal | View SSH passwords and private keys |

## Features

### Provider Assignment
Each VPS can be linked to a VPS Provider for organisational grouping. Providers are managed separately under **VPS Providers**.

### Credential Storage
- SSH username, port, and password
- Private key attachment
- Reveal-required for credential display

### Status Tracking
Mark VPS records as active, suspended, or cancelled with timeline visibility.

## Import / Export
- Import: CSV file with columns for label, IP, provider, username, port, status, notes
- Export: CSV download of all VPS records the user can Access

## Related Docs
- [Credential Reveal](../credentials/credential-reveal.md)
- [Import / Export](../administrator/import-export.md)
