---
title: Login Audits
description: Review login attempts and authentication history
category: Administrator
icon: log-in
---

## Overview

The Login Audits module displays login attempt history, including both successful and failed authentications. Use it to monitor for suspicious access patterns.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View login audit records |

The Login Audits module itself is controlled by the **Login Audits** privilege.

## Audit Details
Each login audit entry includes:
- User name and email
- Timestamp
- IP address
- User agent / browser string
- Success / failure status
- Failure reason (if applicable)

## Use Cases
- Detect brute-force login attempts
- Identify access from unusual locations
- Verify successful authentication for compliance

## Related Docs
- [Activity Logs](activity-logs.md)
- [Privileges](privileges.md)
