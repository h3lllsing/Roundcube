# SAFE REMOVAL PLAN (Security-Critical Items)

**See:** `15_SAFE_REMOVAL_PLAN.md` for the complete plan.

This file lists only security-relevant removals from `100_DUPLICATE_PAGE_POLICY_PERMISSION_AUDIT.md`.

---

## SECURITY REMOVAL ITEMS

### SR-001: Add `canOnModule(read)` to BRC `show()` Method
- **Ref:** DUP-008
- **File:** `app/Http/Controllers/Web/BaseResourceController.php`
- **Change:** Add `abort_unless($isSuperAdmin || $user->canOnModule($module, 'read'), 403);`
- **Risk:** Medium — defense-in-depth improvement

### SR-002: Add `canOnModule(read)` to API `show()` Endpoints
- **Ref:** DUP-009
- **Files:** All 11 `Api\*Controller.php` files
- **Change:** Add read permission check in show method
- **Risk:** Medium — may break API clients that rely on existing behavior

### SR-003: Fix `removeForRole()` Cache Invalidation
- **Ref:** DUP-012
- **File:** `app/Services/ModulePermissionService.php`
- **Change:** Add `Cache::increment('perms_generation')` after delete
- **Risk:** Low — bug fix

### SR-004: Fix Cached Path to Include All User Overrides
- **Ref:** DUP-013
- **File:** `app/Traits/HasModulePermissions.php`
- **Change:** Remove `$allModuleIds` filter from user override query
- **Risk:** Medium — changes cached return values

### SR-005: Fix `api-tokens` → `tokens` Slug
- **Ref:** DUP-014
- **File:** `config/permissions.php`
- **Change:** Replace `'api-tokens'` with `'tokens'`
- **Risk:** Low — config fix

---

## EXECUTION ORDER (Security Items First)

```
Round 1 (Low Risk):
  SR-005 → api-tokens slug fix
  SR-003 → removeForRole cache invalidation

Round 2 (Medium Risk):
  SR-004 → Override cache fix
  SR-001 → BRC show() read check

Round 3 (Requires Discussion):
  SR-002 → API show() read checks
  DUP-010 → Ownership checks removal
```

**Rule:** If any step fails verification, rollback immediately.
