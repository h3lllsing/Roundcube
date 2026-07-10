# FINAL_RELEASE_CROSS_CUTTING_LABEL_AUDIT.md

**Date:** 2026-07-09

---

## P1 Fixes — Must Fix

| # | Form | Field | Current Label | Recommended Label |
|---|------|-------|---------------|-------------------|
| 1 | VoIP (create/edit) | `user_name` | "Users-Name" | "Name" |
| 2 | VoIP (create/edit) | `password` | "Password" | "Extension Password" |
| 3 | ServiceProvider (create/edit) | `website` | "Website" | "Portal URL" |
| 4 | ServiceProvider (create/edit) | `email` | "Email" | "Support Email" |
| 5 | All forms | `user_id` | "User" (select) | **REMOVE** — not a user-owned record |
| 6 | All forms | `module_id` | "Module" (select) | **HIDE** — auto-set from route |
| 7 | All forms (Domain, Hosting, VPS, etc.) | `cost` | "Cost" | "Monthly Cost" |
| 8 | Monitoring create/edit | `port` | "Port" | "Port Number" |

---

## P2 Improvements — Recommended

| # | Form | Field | Current | Recommended |
|---|------|-------|---------|-------------|
| 9 | Backup | `frequency` | "Frequency" | "Backup Frequency" |
| 10 | Dns | `type` | "Type" | "Record Type" |
| 11 | Dns | `value` | "Value" | "Record Value" |
| 12 | Mailbox | `quota` | "Quota" | "Mailbox Quota (MB)" |
| 13 | SSL | `provider` | "Provider" | "SSL Provider" |
| 14 | Task | `priority` | "Priority" | "Task Priority" |
