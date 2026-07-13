# OpsPilot RBAC Consistency Analysis

> Comparing route-level middleware enforcement vs Blade-level visibility control

---

## 1. Access Control Layers

This application uses **three layers** of access control:

| Layer | Mechanism | Location |
|-------|-----------|----------|
| Route Middleware | `auth`, `suspended`, `role:super-admin` | `routes/web.php` |
| Blade Gates | `@hasrole('super-admin')` | `sidebar-nav-groups.blade.php` |
| Nav Visibility | `$show*` boolean flags | `layouts/admin.blade.php` → sidebar component |

---

## 2. Route-Level Middleware

### Guest Routes (no auth)
```
login, register, password request/reset
```
Middleware: `web,guest` — correct (unauthenticated users only)

### Authenticated User Routes
```
Middleware: web, auth, suspended
→ All infrastructure, monitoring, vault, notes, tasks, notifications, attachments
→ Features/Modules index + show (read-only access)
```
**No permission gating** — any authenticated user can access any of these routes. The `$show*` flags in the navbar are the only per-module filter.

### Super-Admin Routes
```
Middleware: web, auth, suspended, role:super-admin
→ Users (full CRUD + admin ops)
→ Roles, Role Templates, Privileges
→ SMTP Profiles
→ Webhooks, Tokens
→ Activity Logs, Login Audits
→ Reports, Import
→ Modules/Features (write operations)
→ Design System
```
**Aligned**: Both route middleware AND Blade sidebar require `role:super-admin`.

---

## 3. Mixed-Access Modules

### Features
```
READ:  features.index, features.show     → auth (any user)
WRITE: features.create, .store, .edit, .update, .destroy → super-admin
```
**Sidebar**: Under Administration → `@hasrole('super-admin')` — correct (super-admin only)

### Modules
```
READ:  modules.index, modules.show       → auth (any user)
WRITE: modules.create, .store, .edit, .update, .destroy → super-admin
```
**Sidebar**: Under Administration → `@hasrole('super-admin')` — correct

---

## 4. `$show*` Flag Analysis

The `$show*` flags are the PRIMARY access control for resource visibility but are NOT enforced at the route level.

### Flag to Route Alignment

| Flag | Routes Protected | Route Middleware | Gap |
|------|-----------------|-----------------|-----|
| `$showHostings` | `hostings.*` | auth+suspended | ❌ No permission middleware |
| `$showDomains` | `domains.*` | auth+suspended | ❌ No permission middleware |
| `$showVps` | `vps.*` | auth+suspended | ❌ No permission middleware |
| `$showVoip` | `voip.*` | auth+suspended | ❌ No permission middleware |
| `$showProviders` | `service-providers.*` | auth+suspended | ❌ No permission middleware |
| `$showOtherServices` | `other-services.*` | auth+suspended | ❌ No permission middleware |
| `$showEmails` | `domain-emails.*` | auth+suspended | ❌ No permission middleware |
| `$showExpiryTrackers` | `expiry-trackers.*` | auth+suspended | ❌ No permission middleware |
| `$showAssets` | `assets.*` | auth+suspended | ❌ No permission middleware |
| `$showGMails` | `g-mails.*` | auth+suspended | ❌ No permission middleware |
| `$showVault` | `vault.*` | auth+suspended | ❌ No permission middleware |
| `$showMyVault` | `vault.my` | auth+suspended | ❌ No permission middleware |
| `$showMonitoring` | `monitoring.*` | auth+suspended | ❌ No permission middleware |
| `$showNotes` | `notes.*` | auth+suspended | ❌ No permission middleware |

**Critical finding**: All `$show*` flags control ONLY the sidebar link visibility. The actual routes have NO middleware checking whether the user has permission for that module. If a user knows the URL, they can access any module regardless of their `$show*` setting.

### How `$show*` Flags Are Set
The flags originate from `AppServiceProvider::boot()` or a middleware's `share()` method. They likely check:
- `module_permissions` table
- `privileges` table
- Or a hard-coded role mapping

---

## 5. Blade-Level Role Checks

### `@hasrole('super-admin')` usage
Used in two sidebar sections:
- **Administration** (lines 96-118)
- **Reports** (lines 120-130)

These are consistent with route-level `role:super-admin` middleware.

### Missing `@hasrole` for non-super-admin modules
No Blade-level role checks for infrastructure/credential sections — they use `$show*` flags instead.

---

## 6. RBAC Gaps & Recommendations

### Gap 1: Route-Level Permission Middleware Missing
**Severity**: **High**
**Description**: All infrastructure routes (hostings, domains, vps, etc.) only require `auth+suspended`. No `role`, `can`, or `permission` middleware checks which modules the user is allowed to access.
**Risk**: Users can directly navigate to /hostings, /vps, /domains etc. even if the sidebar link is hidden.
**Fix**: Add permission middleware to resource route groups. Example:
```php
Route::middleware(['can:access-module,hostings'])->group(function () {
    Route::resource('hostings', HostingController::class);
});
```

### Gap 2: `$show*` Flag Source Not Auditable
**Severity**: **Medium**
**Description**: The exact logic for setting each `$show*` flag is in `AppServiceProvider` or middleware — not visible in routes. Without examining that code, it's impossible to verify that flags correctly map to the `module_permissions` or `privileges` database tables.

### Gap 3: Features/Modules Read Route Not Gated
**Severity**: **Low**
**Description**: `features.index`, `features.show`, `modules.index`, `modules.show` are accessible to any authenticated user. The sidebar only shows them under Administration (super-admin), meaning non-admin users have no UI to access these routes but can manually navigate to them.
**Risk**: Low — features/modules are generally configuration-level data, not sensitive.

### Gap 4: Calendar, Guide, Search, Export — No Restrictions
**Severity**: **Low**
**Description**: Calendar, Help Center, Search, and Export are accessible to all authenticated users with no per-module gating. This is likely intentional but means export could be a data exfiltration vector.

### Gap 5: No Permission Gates on API Routes
**Severity**: **High**
**Description**: All API routes use only `auth:sanctum` middleware. There is no per-module token permission check. An API token with any access can query all API endpoints.
**Fix**: Implement token abilities/scopes via Sanctum's token abilities feature.

---

## 7. Permission Model Architecture

From the routes and controllers, the permission model appears to be:

```
Users ──┬── has roles (via RoleController)
         └── has module_permissions (via ModulePermissionController)

Roles ──┬── has privileges (via PrivilegeController + role_privilege pivot)
         └── has [no direct module links visible]

Module Permissions → define which users/roles can see/use which modules
```

The `$show*` flags in the sidebar are the UI manifestation of `ModulePermission` records. But the actual route access is not gated by this system.

### Current Architecture (Visual)
```
Route accessed → [web, auth, suspended] middleware only
                     ↓
           Controller executes (no permission check)
                     ↓
           Blade renders sidebar (checks $show* flag)
                     ↓
           User sees/hides nav link
```

### Required Architecture (Secure)
```
Route accessed → [web, auth, suspended, permission:module] middleware
                     ↓
           Controller executes (with permission check)
                     ↓
           Blade renders sidebar (checks $show* flag — same source)
                     ↓
           User sees nav link only if they have permission
```

---

## 8. Summary

| Area | Status | Risk Level |
|------|--------|-----------|
| Auth middleware | ✅ Correct | None |
| Suspended check | ✅ Present | None |
| Super-admin gate (routes) | ✅ Correct | None |
| Super-admin gate (Blade) | ✅ Aligned with routes | None |
| `$show*` flags (sidebar) | ⚠️ Nav-only, no route enforcement | **High** |
| API token permissions | ❌ Missing | **High** |
| Module-level permissions (route) | ❌ Not implemented | **High** |
| Features/Modules read access | ⚠️ Open to all auth users | Low |
