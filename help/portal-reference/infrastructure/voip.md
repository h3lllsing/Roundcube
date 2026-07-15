---
title: VoIP
description: Manage Voice over IP services
category: Portal Reference
subcategory: Infrastructure
icon: phone
---

## Overview

The VoIP module tracks telephone and SIP services. Records include provider, trunk details, and authentication credentials.

## Permission Controls

| Control | Description |
|---------|-------------|
| Access | View and list VoIP records |
| Manage | Create, edit, and delete VoIP records |
| Reveal | View SIP passwords and authentication secrets |

## Features

### Provider & Trunk Info
Associate each VoIP record with a service provider. Store trunk details, DIDs, and termination points.

### Credential Storage
- SIP username and password
- Authentication realm and port
- Reveal-required for credential display

### Status Tracking
Mark records as active, suspended, or cancelled.

## Import / Export
- Import: CSV with columns for label, provider, trunk, SIP username, status, notes
- Export: CSV download of all VoIP records

## Related Docs
- [Credential Reveal](../credentials/credential-reveal.md)
- [Service Providers](service-providers.md)
