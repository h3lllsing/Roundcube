# OpsPilot Operations & Training Documentation

## Overview

This documentation covers day-to-day operations, workflows, and best practices for running the OpsPilot portal. It is written for all user roles: Super Admin, Admin / IT Manager, IT Support Staff, Software Team, and Read-only users.

---

## Document List

| # | Document | Audience | Pages | Reading Order |
|---|----------|----------|-------|---------------|
| 1 | **OPSPILOT_QUICK_START_GUIDE.md** | All Users | — | **Read first** |
| 2 | **OPSPILOT_MODULE_GUIDE.md** | All Users | — | **Read second** — covers all 25 modules |
| 3 | **OPSPILOT_RBAC_PERMISSION_GUIDE.md** | Super Admin, Admin | — | **Read third** |
| 4 | **OPSPILOT_WORKFLOW_GUIDE.md** | All Users | — | **Read fourth** — step-by-step common workflows |
| 5 | **OPSPILOT_SMTP_AND_EXPIRY_NOTIFICATION_GUIDE.md** | Super Admin, IT Manager | — | Read after workflows |
| 6 | **OPSPILOT_VAULT_AND_PASSWORD_REVEAL_GUIDE.md** | All Users | — | Read after SMTP guide |
| 7 | **OPSPILOT_SUPER_ADMIN_GUIDE.md** | Super Admin | — | Role-specific |
| 8 | **OPSPILOT_ADMIN_IT_MANAGER_GUIDE.md** | Admin / IT Manager | — | Role-specific |
| 9 | **OPSPILOT_IT_SUPPORT_GUIDE.md** | IT Support Staff | — | Role-specific |
| 10 | **OPSPILOT_SOFTWARE_TEAM_GUIDE.md** | Software Team | — | Role-specific |
| 11 | **OPSPILOT_DAILY_OPERATIONS_CHECKLIST.md** | All Users | — | Daily reference |
| 12 | **OPSPILOT_COMMON_MISTAKES_AND_FAQ.md** | All Users | — | Troubleshooting reference |
| 13 | **OPSPILOT_GO_LIVE_RUNBOOK.md** | Super Admin | — | Deployment reference |

---

## Recommended Reading Order

### For Super Admin (first day)
1. `OPSPILOT_QUICK_START_GUIDE.md`
2. `OPSPILOT_MODULE_GUIDE.md`
3. `OPSPILOT_RBAC_PERMISSION_GUIDE.md`
4. `OPSPILOT_SMTP_AND_EXPIRY_NOTIFICATION_GUIDE.md`
5. `OPSPILOT_VAULT_AND_PASSWORD_REVEAL_GUIDE.md`
6. `OPSPILOT_WORKFLOW_GUIDE.md`
7. `OPSPILOT_SUPER_ADMIN_GUIDE.md`
8. `OPSPILOT_GO_LIVE_RUNBOOK.md`

### For Admin / IT Manager (first week)
1. `OPSPILOT_QUICK_START_GUIDE.md`
2. `OPSPILOT_MODULE_GUIDE.md`
3. `OPSPILOT_RBAC_PERMISSION_GUIDE.md`
4. `OPSPILOT_WORKFLOW_GUIDE.md`
5. `OPSPILOT_ADMIN_IT_MANAGER_GUIDE.md`
6. `OPSPILOT_DAILY_OPERATIONS_CHECKLIST.md`

### For IT Support Staff
1. `OPSPILOT_QUICK_START_GUIDE.md`
2. `OPSPILOT_MODULE_GUIDE.md`
3. `OPSPILOT_IT_SUPPORT_GUIDE.md`
4. `OPSPILOT_DAILY_OPERATIONS_CHECKLIST.md`
5. `OPSPILOT_COMMON_MISTAKES_AND_FAQ.md`

### For Software Team
1. `OPSPILOT_QUICK_START_GUIDE.md`
2. `OPSPILOT_MODULE_GUIDE.md`
3. `OPSPILOT_SOFTWARE_TEAM_GUIDE.md`
4. `OPSPILOT_COMMON_MISTAKES_AND_FAQ.md`

### For Read-Only Users
1. `OPSPILOT_QUICK_START_GUIDE.md`
2. `OPSPILOT_MODULE_GUIDE.md`

---

## Key Concepts

### Service Provider vs Hosting vs Domain vs VPS
See `OPSPILOT_MODULE_GUIDE.md` — section "Understanding Infrastructure Modules".

### SMTP Profile vs Expiry Tracker
See `OPSPILOT_SMTP_AND_EXPIRY_NOTIFICATION_GUIDE.md`.

### Vault vs Password Fields
See `OPSPILOT_VAULT_AND_PASSWORD_REVEAL_GUIDE.md`.

### User vs Assigned User vs Created By
See `OPSPILOT_RBAC_PERMISSION_GUIDE.md` — section "Ownership and Visibility".

### Module Permissions vs Role Templates vs User Overrides
See `OPSPILOT_RBAC_PERMISSION_GUIDE.md` — section "RBAC Hierarchy".

### Cost Fields
See `OPSPILOT_MODULE_GUIDE.md` — section "Cost Fields".

### Renewal / Expiry Flow
See `OPSPILOT_SMTP_AND_EXPIRY_NOTIFICATION_GUIDE.md`.

---

## File Locations

All documentation files live in `docs/operations/`. The directory is safe to include in version control and deployment packages — it contains no secrets or credentials.

---

*Last updated: 2026-06-27*
