# SAFE REMOVAL PLAN

**Project:** OpsPilot Portal
**Date:** 2026-07-08
**Based on:** 14_DUPLICATE_PAGE_POLICY_PERMISSION_AUDIT.md

---

## How To Use This Plan

Each removal is ordered by **risk** (Lowest first) and includes:
- **Dependency check** — what else must change
- **Step-by-step** — exact code changes
- **Verification** — how to confirm removal is safe
- **Rollback** — one-command git revert

Do NOT execute any step unless explicitly authorized.

---

## RM-001: Remove Dead View `guide.blade.php`

**Risk:** Low | **Effort:** 5 min | **Dependencies:** None

### Steps
```bash
# 1. Verify no references to guide.blade.php
grep -r "guide" resources/views/ --include="*.blade.php" | grep -v "help\|guide-bot"
# Should only show help/index.blade.php references

# 2. Delete file
rm resources/views/guide.blade.php
```

### Verification
- `route('guide')` still renders `help.index` correctly
- No error in logs about missing view

### Rollback
```bash
git checkout -- resources/views/guide.blade.php
```

---

## RM-002: Remove Orphaned Route + View `design-system`

**Risk:** Low | **Effort:** 5 min | **Dependencies:** None

### Steps
```bash
# 1. Verify no internal references
grep -r "design-system" routes/ app/ resources/ --include="*.php" --include="*.blade.php" --include="*.js"
# Should only show the route definition itself

# 2. Remove route line from routes/web.php
# Remove: Route::view('/design-system', 'design-system')->name('design-system');

# 3. Delete view
rm resources/views/design-system.blade.php
```

### Verification
- `GET /design-system` returns 404
- No error anywhere in the app

### Rollback
```bash
git checkout -- routes/web.php resources/views/design-system.blade.php
```

---

## RM-003: Fix `api-tokens` → `tokens` Slug in Config

**Risk:** Low | **Effort:** 1 min | **Dependencies:** None

### Steps
1. Open `config/permissions.php`
2. Change `'api-tokens'` to `'tokens'` in the `sensitive_modules` array

### Verification
- Tokens module now has `isSensitive = true` on user permissions page
- All sensitive permission confirmation dialogs work for tokens

### Rollback
```bash
git checkout -- config/permissions.php
```

---

## RM-004: Add Cache Invalidation to `ModulePermissionService::removeForRole()`

**Risk:** Low | **Effort:** 5 min | **Dependencies:** None

### Steps
1. Open `app/Services/ModulePermissionService.php`
2. Add `Cache::increment('perms_generation');` after the delete operation (after line 54)

### Verification
- `perms_generation` cache key increments when role permissions are removed
- Users see updated permissions immediately after role permission deletion

### Rollback
```bash
git checkout -- app/Services/ModulePermissionService.php
```

---

## RM-005: Fix `getAllModulePermissionsFromDb()` to Include All User Overrides

**Risk:** Medium | **Effort:** 15 min | **Dependencies:** None

### Steps
1. Open `app/Traits/HasModulePermissions.php`
2. Replace lines 89-91:
```php
$userOverrides = UserModulePermission::where('user_id', $this->id)
    ->whereIn('module_id', $allModuleIds)
    ->get();
```
With:
```php
$userOverrides = UserModulePermission::where('user_id', $this->id)->get();
```

### Verification
- `getAccessibleModuleIds('read')` includes modules where user has override but role has no permissions
- `getAllModulePermissions()` returns correct combined data
- `canOnModule()` already correct — cached path now matches

### Rollback
```bash
git checkout -- app/Traits/HasModulePermissions.php
```

---

## RM-006: Remove `can_approve` from Config and DB (If Approval Workflow Not Planned)

**Risk:** Low | **Effort:** 30 min | **Dependencies:** Business decision

### Steps
1. Remove `'can_approve'` from `config/permissions.php` keys array
2. Create migration to drop `can_approve` column from `module_role_permissions` and `user_module_permissions`
3. Remove `'can_approve'` from both model fillable arrays and casts
4. Remove from `resources/js/permissions.js` toggleToColumn map
5. Run `php artisan migrate`

### Verification
- `php artisan test` passes
- User permissions page still renders all modules correctly
- Save Overrides still works

### Rollback
```bash
git checkout -- config/permissions.php app/Models/ resources/js/permissions.js
php artisan migrate:rollback
```

---

## RM-007: Fix `show()` Missing `canOnModule(read)` in BaseResourceController

**Risk:** Medium | **Effort:** 15 min | **Dependencies:** None

### Steps
1. Open `app/Http/Controllers/Web/BaseResourceController.php`
2. Add to the beginning of `show()` method (after finding the record):
```php
abort_unless($isSuperAdmin || $user->canOnModule($module, 'read'), 403);
```

### Verification
- User with `can_update` but NO `can_read` gets 403 on show page
- User with `can_read` can view normally
- Super-admin can view everything

### Rollback
```bash
git checkout -- app/Http/Controllers/Web/BaseResourceController.php
```

---

## RM-008: Fix HostingController + OtherServiceController `prepareStoreData`

**Risk:** Low | **Effort:** 15 min each | **Dependencies:** None

### Steps for HostingController:
1. Open `app/Http/Controllers/Web/HostingController.php`
2. In `store()`, replace inline password cleaning with `$data = $this->prepareStoreData($validated);`

### Steps for OtherServiceController:
1. Open `app/Http/Controllers/Web/OtherServiceController.php`
2. In `store()`, replace inline password cleaning with `$data = $this->prepareStoreData($validated);`

### Verification
- Hosting/OtherService creation still works with password
- Password on edit shows "Leave blank to keep current"
- Hosting/OtherService update still works

### Rollback
```bash
git checkout -- app/Http/Controllers/Web/HostingController.php
git checkout -- app/Http/Controllers/Web/OtherServiceController.php
```

---

## RM-009: Refactor `AssetController` to Extend `BaseResourceController`

**Risk:** Medium | **Effort:** 2 hours | **Dependencies:** None

### Steps
1. Change `class AssetController extends Controller` to `extends BaseResourceController`
2. Add 6 abstract method implementations:
   - `modelClass()` → `Asset::class`
   - `moduleSlug()` → `'assets'`
   - `viewPrefix()` → `'assets'`
   - `indexSelect()` → relevant columns
   - `indexVariable()` → `'assets'`
   - `recordVariable()` → `'asset'`
3. Remove duplicated `index()`, `create()`, `show()`, `edit()`, `destroy()` methods
4. Keep custom `assign()`, `returnAsset()`, `forceDelete()`

### Verification
- Asset index/create/show/edit/destroy all work
- Asset assignment and return work
- Permission checks work (can_create, can_read, etc.)

### Rollback
```bash
git checkout -- app/Http/Controllers/Web/AssetController.php
```

---

## RM-010: Complete BRC Refactor — Add `store()` and `update()` to Base Class

**Risk:** Medium | **Effort:** 4 hours | **Dependencies:** RM-008, RM-009

### Steps
1. Add abstract `modelClass()` method to BRC (already exists)
2. Add `store()` and `update()` to `BaseResourceController`:
   - Resolve module from moduleSlug()
   - Check permission via canOnModule(create/update)
   - Call prepareStoreData/prepareUpdateData
   - Create/update model with user_id
   - Sync renewal
3. Remove `store()` and `update()` from all 6 BRC subclasses
4. Ensure `prepareStoreData()` / `prepareUpdateData()` in each subclass handles unique fields

### Verification
- Full CRUD works for all 6 modules (domain, hosting, vps, voip, service-providers, other-services)
- Password cleaning works on both create and update for all
- JSON field encoding works (vps extensions, dns_servers)

### Rollback
```bash
git checkout -- app/Http/Controllers/Web/BaseResourceController.php
git checkout -- app/Http/Controllers/Web/DomainController.php
git checkout -- app/Http/Controllers/Web/HostingController.php
git checkout -- app/Http/Controllers/Web/VpsController.php
git checkout -- app/Http/Controllers/Web/VoipController.php
git checkout -- app/Http/Controllers/Web/ServiceProviderController.php
git checkout -- app/Http/Controllers/Web/OtherServiceController.php
```

---

## REMOVAL ORDER RECOMMENDATION

The removals should be executed in this order to minimize risk:

```
Phase 1 (Low Risk, High Confidence):
┌─────────────────────────────────────────────┐
│ RM-003  →  api-tokens slug fix (1 min)      │
│ RM-004  →  Cache invalidation (5 min)       │
│ RM-005  →  Override cache fix (15 min)      │
│ RM-001  →  Dead view guide (5 min)          │
│ RM-002  →  Orphaned design-system (5 min)   │
│ RM-006  →  can_approve removal (30 min)*    │
└─────────────────────────────────────────────┘

Phase 2 (Medium Risk, Business Logic Changes):
┌─────────────────────────────────────────────┐
│ RM-007  →  show() canOnModule(read) (15 min)│
│ RM-008  →  prepareStoreData fixes (30 min)  │
│ RM-016  →  CleansPasswords consistency      │
└─────────────────────────────────────────────┘

Phase 3 (High Impact, Architectural):
┌─────────────────────────────────────────────┐
│ RM-009  →  AssetController BRC refactor     │
│ RM-010  →  BRC store/update centralization  │
│ RM-005  →  ʀ 3 standalone controllers       │
└─────────────────────────────────────────────┘

Phase 4 (Requires Discussion):
┌─────────────────────────────────────────────┐
│ RM-010  →  Ownership checks removal         │
│ RM-015  →  Redundant super-admin checks     │
└─────────────────────────────────────────────┘

* = Requires business decision before execution
```

## RISK SUMMARY

| Risk Level | Count | Rollback Complexity |
|-----------|-------|---------------------|
| Low | 8 | `git checkout` — 1 file each |
| Medium | 6 | `git checkout` — 2-8 files |
| Medium-High | 1 | Requires discussion before any action |
| Info (no action) | 2 | N/A |
