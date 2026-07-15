---
title: Activity Logs
description: Track user actions across the portal
category: Administrator
icon: activity
---

## Overview

The Activity Log records user actions across the portal for audit and troubleshooting purposes. Every create, update, delete, reveal, and login event is logged with timestamp, user, and detail.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View activity logs |

The Activity Logs module itself is controlled by the **Activity Logs** privilege.

## Logged Events

| Event Type | Description |
|------------|-------------|
| Login / Logout | Authentication events |
| CRUD | Record creation, update, deletion |
| Reveal | Credential reveal actions |
| Import / Export | Bulk data operations |
| Permission Changes | Role or user permission modifications |
| Profile Updates | User profile changes |

## Log Details
Each entry includes:
- Timestamp and date
- User name and email
- Action type (created, updated, deleted, revealed, logged in)
- Module and record identifier
- IP address
- Change summary (for updates: before/after values)

## Retention
Activity logs are retained based on system configuration. Super Admins can configure retention periods.

## Related Docs
- [Login Audits](login-audits.md)
- [Privileges](privileges.md)
