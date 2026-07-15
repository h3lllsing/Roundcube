---
title: Webhooks
description: Configure outgoing webhook notifications
category: Administrator
icon: webhook
---

## Overview

Webhooks send HTTP callouts to external systems when portal events occur. Configure endpoints to receive notifications for record creation, updates, deletions, and other events.

## Permission Controls

| Control | Description |
|---------|-------------|
| Webhooks | Access the Webhooks screen |

The Webhooks privilege controls access. Users also need Manage on the Webhooks module.

## Features

### Webhook Configuration
Each webhook requires:
- Name
- Endpoint URL
- Trigger events (one or more)
- Secret signing key (auto-generated)

### Trigger Events
Send webhooks on:
- Record created
- Record updated
- Record deleted
- Credential revealed

### Payload Format
Webhooks deliver JSON payloads with event type, timestamp, record data, and a signature header for verification.

## Related Docs
- [Activity Logs](activity-logs.md)
- [Privileges](privileges.md)
