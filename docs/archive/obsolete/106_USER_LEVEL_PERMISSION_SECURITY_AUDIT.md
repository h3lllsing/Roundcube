# USER-LEVEL PERMISSION SECURITY AUDIT (Addendum)

**This file cross-references `102_USER_LEVEL_PERMISSION_SECURITY_AUDIT.md`**
**Focus:** Additional edge cases and deep-dive analysis

---

## ADDENDUM: DATA FLOW ANALYSIS

### End-to-End Permission Save Flow

```
User clicks "Save Overrides"
  ↓
Alpine.js save() method
  → computes targetToggles from mod.preset
  → maps toggle names to DB columns via toggleToColumn
  → excludes modules where preset === baseline (inherited)
  → builds permissions object: {moduleId: {can_read: true, ...}}
  → checks sensitiveChanges: requires confirmation modal
  ↓
PUT /users/{id}/permissions (JSON body: {permissions: {...}})
  ↓
UserController::updatePermissions()
  → auth: super-admin only (route middleware + controller check)
  → validate: module_id must exist in DB
  ↓
UserPermissionService::saveUserModulePermissions()
  → DB::transaction()
  → UserModulePermission::lockForUpdate() for user
  → foreach module: updateOrCreate or delete (all nulls)
  → cleanup: delete overrides for omitted modules
  → Cache::increment('perms_generation')
  → activity()->log()
  ↓
Redirect to users.edit with success flash
```

### Data Conflicts Possible

| Conflict | Resolution | Safe? |
|----------|-----------|-------|
| Two admins save same time | lockForUpdate serializes | ✅ Yes |
| Module deleted while saving | FK cascade on commit | ✅ Yes |
| Role changed while saving | Override still wins (by design) | ✅ Yes |
| Cache stale during save | Incremented after commit | ✅ Yes |
| Network failure mid-save | Transaction rolls back | ✅ Yes |

---

## ADDENDUM: JAVASCRIPT SECURITY ANALYSIS

### Client-Side Only Attacks

The JS permissions page is purely client-side for UI state management. ALL authorization is enforced server-side.

| Risk | Analysis | Safe? |
|------|----------|-------|
| Modify JS to send extra permissions | Server validates module IDs only | ⚠️ Keys not validated (see FIX-002) |
| Bypass sensitive confirmation modal | Server has no confirmation step — modal is client-only | ✅ Server doesn't check modal state |
| Send arbitrary JSON | Server ignores unknown keys | ✅ Only config keys processed |
| CSRF attack | CSRF token in meta tag + Laravel VerifyCsrfToken | ✅ Protected |

### `toggleToColumn` Key Validation

The JS maps UI toggle names (`view`, `create`, `edit`, `delete`, `approve`, `export`, `reveal`, `import`) to DB columns (`can_read`, `can_create`, `can_update`, `can_delete`, `can_approve`, `can_export`, `can_reveal`, `can_import`). The server MUST only accept these keys.

**Current state:** Server accepts any key from `config('permissions.keys')`. Same 8 keys as JS. ✅ Consistent.
