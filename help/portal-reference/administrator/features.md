---
title: Features
description: Enable or disable portal modules and features
category: Administrator
icon: toggle-right
---

## Overview

The Features module (Super Admin only) controls which portal modules and features are enabled for the entire organisation. Disabled modules are hidden from all users regardless of their role permissions.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View the features list |

> **Note**: The Features module is restricted to Super Admins.

## How It Works

Each module (VPS, VoIP, Domains, Hostings, etc.) has an enabled/disabled toggle. When a module is disabled:
- It is removed from the navigation sidebar
- Its routes return 404
- Global search excludes its records
- Dashboard widgets for that module are hidden

## Use Cases
- Phase out a module your organisation no longer uses
- Restrict access during maintenance
- Onboard new modules gradually

## Related Docs
- [Modules](modules.md)
- [Super Admin Guide](/super-admin-guide)
