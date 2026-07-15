---
title: Import / Export
description: Bulk import and export portal records
category: Administrator
icon: upload
---

## Overview

The Import and Export modules enable bulk data operations. Import creates or updates records from CSV files. Export downloads portal records as CSV.

## Permission Controls

| Control | Description |
|---------|-------------|
| Import | Access the Import screen and run imports |
| Export | Super Admin only |

Import is controlled by the **Import** privilege plus per-module permissions. A user must have both the Import privilege and Manage on a module to import records into it.

Export is currently restricted to **Super Admin** users only. The Export privilege exists in the system but the backend enforces Super Admin‑only access.

## Import

### Supported Modules
Import is available for modules marked importable in the Modules registry: Domains, Hostings, VPS, VoIP, Other Services, Domain Emails, Assets, and more.

### CSV Requirements
- First row must be a header row matching the expected column names
- Required columns vary per module (see individual module docs)
- The import validates required fields and rejects rows with errors

### Import Process
1. Download the module's CSV template
2. Fill in record data
3. Upload the CSV file
4. Review validation results
5. Confirm the import

## Export

### Supported Modules
Same set as Import — any module marked exportable.

### Export Process
1. Select the module to export
2. Choose column filters (optional)
3. Click Export to download a CSV file

## Related Docs
- [Modules](modules.md)
- [Privileges](privileges.md)
- [Bulk Actions](bulk-actions.md)
