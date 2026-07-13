# OpsPilot Operation Ownership Matrix

> CRUD operation availability per module, mapped to controller and middleware

---

## Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | Route exists |
| ❌ | Route does NOT exist |
| 🔒 | Route exists but is super-admin only |
| ⚠️ | Route exists but has caveats |

---

## Full Matrix

| Module | index | show | create | store | edit | update | destroy | restore | force-delete | Extra |
|--------|-------|------|--------|-------|------|--------|---------|---------|-------------|-------|
| **Hostings** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password, pwd-copy |
| **Domains** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| **Domain Emails** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password |
| **VPS** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password, pwd-copy |
| **VoIP** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password, ext-pwd, copies |
| **Other Services** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password, pwd-copy |
| **Service Providers** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password |
| **Expiry Trackers** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | **preview-email, test-email, send-reminder, notification-history, renew** |
| **Assets** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | **assign, return** |
| **G-Mails** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | password |
| **Vault** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | **reveal** |
| **Notes** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | **pin** |
| **Tasks** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | **kanban, my-tasks, my-counts, update-status** |
| **Monitoring** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | **check** (separate MonitorController) |
| **Users** | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | **permissions, suspend/unsuspend, clone, login-as** |
| **Roles** | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | **attach/detach privilege** |
| **Role Templates** | 🔒 | 🔒 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | **apply** (GET+POST) |
| **Privileges** | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | — |
| **SMTP Profiles** | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | **auto-discover, test, set-default, toggle-active, duplicate** |
| **Modules** | ✅ | ✅ | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | — |
| **Features** | ✅ | ✅ | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | — |
| **Module Permissions** | 🔒 | ❌ | ❌ | ❌ | ❌ | 🔒 | 🔒 | ❌ | ❌ | update is POST without edit form |
| **Webhooks** | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 | ❌ | ❌ | **test** |
| **Activity Logs** | 🔒 | 🔒 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | Read-only |
| **Login Audits** | 🔒 | 🔒 | ❌ | ❌ | ❌ | ❌ | 🔒 | ❌ | ❌ | Read-only with destroy |
| **Reports** | 🔒 | 🔒 | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | **category, export** |
| **Notifications** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | **markAsRead, markAllAsRead, bulk-read, bulk-delete** |
| **Attachments** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ✅ | **download** |
| **Tokens** | 🔒 | ❌ | 🔒 | 🔒 | ❌ | ❌ | 🔒 | ❌ | ❌ | API keys — create then destroy |
| **Import** | ❌ | ❌ | 🔒 | 🔒 | ❌ | ❌ | ❌ | ❌ | ❌ | Form + POST import |
| **Calendar** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | Single-page view |

---

## Key Observations

### Inconsistency: Missing `show` Routes
The following modules have full CRUD but lack a `show` route:
- **SMTP Profiles** — Actually HAS show at line 331 (`/admin/smtp-profiles/{smtp_profile}`)

All modules with `index` have consistent CRUD. No module has a `show` without having create/store/edit/update/destroy.

### Inconsistency: Missing `restore` / `force-delete`
Modules that support soft-deletes (have restore/force-delete):
- Hostings, Domains, VPS, VoIP, G-Mails, Other Services, Service Providers, Expiry Trackers, Assets, Vault, Notes, Users

Modules WITHOUT restore/force-delete:
- Domain Emails — HAS restore + force-delete (line 198-199)
- Tasks — HAS restore (line 87) but NO force-delete
- All super-admin modules (Roles, SMTP Profiles, Webhooks, Privileges, Features, Modules) — NO restore/force-delete
- Monitoring — NO restore/force-delete (expected — monitoring is ephemeral)

**Ownership Pattern**: Infrastructure modules (hostings, domains, vps, etc.) consistently support soft-delete. Admin modules don't — likely because they have smaller datasets.

### Unusual Route Groupings

**Module Permissions** (lines 267-269):
```
GET    /module-permissions           → index
POST   /module-permissions           → update  (no PUT — uses POST for config save)
DELETE /module-permissions           → destroy (no {id} — bulk destroy?)
```
Classification: **C** (shared/modified pattern — permissions managed differently)

**Role Templates** (lines 315-317):
```
GET  /role-templates         → index
GET  /role-templates/{id}    → show
GET  /role-templates/{id}/apply  → apply (GET renders form)
POST /role-templates/{id}/apply  → apply (POST executes)
```
**Security concern**: `GET` + `POST` with same URI mapped to same controller method. The controller must differentiate via `request()->method()`. If GET triggers state changes, this is **F** (security concern).

### Action Count per Module

| Module | Total Routes | CRUD Core | Extra Operations |
|--------|-------------|-----------|-----------------|
| VoIP | 11 | 7 | 4 (pwd, pwd-copy, ext-pwd, ext-pwd-copy) |
| Expiry Trackers | 12 | 7 | 5 (preview, test, send, history, renew) |
| Users | 15 | 7 | 8 (permissions, suspend, unsuspend, clone(x2), login-as, restore, force-delete) |
| SMTP Profiles | 12 | 7 | 5 (auto-discover, test, set-default, toggle-active, duplicate) |
| Vault | 10 | 7 | 3 (my, reveal, restore, force-delete) |
| Assets | 10 | 7 | 3 (assign, return, restore, force-delete) |

---

## Ownership Summary by Operation Type

### Read Operations (index + show)
- **All modules**: Controller implements these as standard read endpoints
- **No redundant read endpoints**: `tasks.my` and `tasks.my-counts` are specialized queries, not duplicates

### Write Operations (create/store)
- **Standard pattern**: GET create form → POST store
- **Exception**: Module Permissions has no create — uses POST to `/module-permissions` directly
- **Exception**: Import has no create form (just GET form + POST import)

### Update Operations (edit/update)
- **Standard pattern**: GET edit form → PUT/PATCH update
- **Exception**: Module Permissions uses POST for update (no PUT)
- **Exception**: Tasks has PATCH `/tasks/{id}/status` for partial update

### Delete Operations (destroy)
- **Standard pattern**: DELETE destroy
- **Soft-delete enabled modules**: Have restore + force-delete
- **No soft-delete**: Admin modules, Monitoring

### Extra Operations
- **Password retrieval**: Hostings, VPS, VoIP, G-Mails, Service Providers, Other Services, Domain Emails — throttled (10/min)
- **Password copy logging**: Hostings, VPS, VoIP, Other Services — tracks copy events
- **Bulk operations**: Notifications (bulk-delete, bulk-read), Bulk Action Controller
- **Specialty**: Expiry Trackers (email preview/test/send), Assets (assign/return), Vault (reveal)
