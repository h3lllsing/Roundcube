---
title: API Tokens
description: Manage API access tokens
category: Administrator
icon: key
---

## Overview

API Tokens enable programmatic access to the portal's REST API. Each token authenticates requests on behalf of a user with their full permission set.

## Permission Controls

| Control | Description |
|---------|-------------|
| API Tokens | Access the API Tokens screen |

The API Tokens privilege controls access.

## Features

### Token Properties
- Token name (for identification)
- Expiration (optional — tokens can be permanent or time-limited)
- Last used timestamp
- Created by and created date

### Creating Tokens
Tokens are generated via the portal UI. The raw token value is shown once at creation — store it securely.

### Revoking Tokens
Tokens can be revoked at any time. Revoked tokens immediately stop working.

### API Usage
- Authenticate via `Bearer` token in the `Authorization` header
- Access the API at `/api/...`
- Token scopes match the creating user's permissions

## Related Docs
- [Privileges](privileges.md)
- [Activity Logs](activity-logs.md)
