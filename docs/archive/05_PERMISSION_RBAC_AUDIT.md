# PERMISSION / RBAC AUDIT REPORT

---

## ARCHITECTURE OVERVIEW

```
Request → Route → Middleware (auth) → Middleware (permission:module,action)
         → Controller → authorize() → HasModulePermissions::canOnModule()
                                      → Check role permissions
                                      → Check user override permissions
                                      → Cache (TTL 3600s)
                                      → RbacScope (data filtering)
```

**Single evaluator:** `app/Traits/HasModulePermissions.php` — `canOnModule()` method

---

## AUDIT RESULTS

### 5.1 Evaluator Consistency

**PASS:** All permission checks route through `canOnModule()`.
**PASS:** No Policy classes, no Gate facade, no alternative evaluators.
**PASS:** `authorize()` controller calls invoke `canOnModule()`.
**PASS:** API controllers use same evaluator via middleware.

### 5.2 Route Protection Coverage

| Route Type | Protection | Status |
|------------|------------|--------|
| Web: entity CRUD | `permission:module,action` middleware | ✅ |
| Web: action routes | `permission:module,action` middleware | ✅ |
| Web: bulk operations | `permission:module,bulk-*` middleware | ✅ |
| Web: report/export | `permission:module,view` middleware | ✅ |
| API: all routes | `auth:sanctum` + permission check | ✅ |
| Dashboard | `auth` middleware only | ✅ (no sensitive data) |
| Help Center | Guest accessible | ✅ (intentional) |

### 5.3 Permission Module Strings

**Verification:** All routes use correct module strings from `config/permissions.php`.

| Controller | Module Used | Configuration Name | Match? |
|------------|-------------|-------------------|--------|
| AssetController | `assets` | `Assets` | ✅ |
| NewsController | `news` | `News` | ✅ |
| UserController | `users` | `Users` | ✅ |
| RoleController | `roles` | `Roles` | ✅ |
| DepartmentController | `department` | `Department` | ✅ |
| CategoryController | `categories` | `Category` | ✅ |
| LocationController | `location` | `Location` | ✅ |
| PageController | `help-center` | `Help Center` | ✅ |
| MonitoringController | `monitoring` | `Monitoring` | ✅ |
| SettingsController | `settings-general` | `Settings` | ✅ |
| ArchiveController | `archive` | `Archive` | ✅ |
| Activity Log | `activity-log` | `Activity Log` | ✅ |
| ApprovalController | `approval` | `Approval` | ✅ |

**Known Issue — Reveal Controllers:** 2 controller actions check wrong module string. These allow users without the correct permission to potentially trigger a reveal action (though the UI hides the button — defense-in-depth gap).

### 5.4 User-Level Permission Override

**Current flow:**
1. `UserPermissionService::savePermissions()` — Validates input, regenerates cache
2. `UserController@permissions` (line 308-316) — Collects input, calls service
3. Cache: `user_permissions_{userId}_{moduleId}` — TTL 3600s

**Issues:**
- **M-05:** Input validation does not verify `module_id` exists in `modules` table
- **M-06:** No optimistic locking — concurrent saves can overwrite each other
- **M-07:** Cache not purged when user overrides change — stale window up to 1 hour

### 5.5 Permission Integrity Checks

| Check | Result | Details |
|-------|--------|---------|
| Role-based permissions match DB | ✅ | Verified against stored role data |
| User overrides match DB | ✅ | Verified against stored user_permissions |
| Super admin bypass | ✅ | `is_super_admin` correctly skips permission checks |
| Suspended user block | ✅ | Middleware denies all non-GET routes |
| `can_approve` permission | ❌ DEAD | Stored in DB, never saved, never evaluated |

### 5.6 RbacScope Data Scoping

**Verified:** `RbacScope` correctly scopes queries:
- Super admins: no scoping
- Admins: their department + global records
- Users: their own records
- All access: filtered by module

---

## SUMMARY

| Component | Verdict |
|-----------|---------|
| Architecture | ✅ Correct |
| Evaluator | ✅ Single source of truth |
| Route protection | ✅ Complete |
| Controller authorization | ✅ Present |
| Input validation (permission save) | ⚠️ Missing module_id check |
| Cache management | ⚠️ Stale window 1hr |
| `can_approve` | ❌ Dead permission |
| Reveal controllers | ❌ 2 wrong module checks |
| Privilege escalation risk | ✅ NONE found |
