---
title: Vault
description: Encrypted storage for sensitive data
category: Portal Reference
subcategory: Credentials
icon: lock
---

## Overview

The Vault provides encrypted storage for arbitrary sensitive data. Each entry stores a key-value pair or file reference with AES-256 encryption at rest. Vault entries are decrypted only on demand when the user holds the **Reveal** capability.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View and list Vault entries (titles only, no values) |
| Manage | Create, edit, and delete Vault entries |
| Reveal | View decrypted credential values |

## How It Works

1. **Encryption**: Values are encrypted using Laravel's built-in AES-256 encryption before being stored.
2. **Listing**: Users with Access see entry titles and metadata but **never** decrypted values.
3. **Reveal**: Clicking Reveal on an entry decrypts and displays the value. The action is logged in the Activity Log.
4. **Edit**: Updating an entry re-encrypts the new value.

## Use Cases

- API keys and tokens
- Database connection strings
- Third-party secrets
- Any sensitive string that doesn't belong to a specific infrastructure module

## Reveal Security
Revealing a Vault entry requires the **Reveal** permission on the Vault module. Each reveal event is recorded in the Activity Log with timestamp, user, and entry identifier.

## Related Docs
- [Credential Reveal](credential-reveal.md)
- [Activity Logs](../administrator/activity-logs.md)
