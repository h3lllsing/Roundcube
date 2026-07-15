---
title: G·Mails
description: Manage Gmail and Google Workspace accounts
category: Portal Reference
subcategory: Infrastructure
icon: at-sign
---

## Overview

The G·Mails module manages Gmail and Google Workspace email accounts. Each record stores login credentials, account type, and recovery information.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View and list G·Mail accounts |
| Manage | Create, edit, and delete accounts |
| Reveal | View account passwords |

## Features

### Account Types
Track personal Gmail accounts, Google Workspace business accounts, and branded Google Workspace aliases.

### Credential Storage
- Email address, password, and recovery email/phone
- App-specific passwords for third-party clients
- Reveal-required for password display

### Account Status
Mark accounts as active, suspended, or closed.

## Import / Export
- Import: CSV with columns for email, account type, recovery email, status, notes
- Export: CSV download of all G·Mail records

## Related Docs
- [Credential Reveal](../credentials/credential-reveal.md)
