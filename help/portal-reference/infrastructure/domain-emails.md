---
title: Domain Emails
description: Manage email accounts associated with domains
category: Portal Reference
subcategory: Infrastructure
icon: mail
---

## Overview

The Domain Emails module tracks email accounts configured for your domains. Each record stores login credentials, mailbox details, and provider information.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View and list domain email accounts |
| Manage | Create, edit, and delete email accounts |
| Reveal | View email passwords |

## Features

### Domain Association
Each email account is linked to a parent Domain record for organisational grouping.

### Credential Storage
- Email address and password
- IMAP/POP3 and SMTP server details
- Reveal-required for password display

### Status Tracking
Mark accounts as active, suspended, or cancelled.

## Import / Export
- Import: CSV with columns for email, domain, provider, password, status, notes
- Export: CSV download of all domain email records

## Related Docs
- [Credential Reveal](../credentials/credential-reveal.md)
- [Domains](../domains.md)
- [Service Providers](service-providers.md)
