# 09 — RECOMMENDED PHASE PLAN

## Phased approach for production readiness

---

## Guiding Principles

1. **Only fix what breaks the business rule.** Don't refactor for purity.
2. **One change per iteration.** Test after each step.
3. **No new features.** No UI redesign. No service extraction.
4. **Prefer 4-line fixes over new classes.**
5. **Every fix must have a regression test.**

---

## PHASE 1: User Override Runtime Fix

**Status:** ✅ COMPLETE

**Done:**
- 4-line cleanup in `UserController::saveUserModulePermissions()` — deletes stale override rows
- Regression test `test_omitted_module_from_payload_deletes_stale_override`
- 18/18 tests passing

---

## PHASE 2: Visible Data Loss Fixes

### 2A: Auto-set module_id in VoIP and Domain Email store()

**Files:** `VoipController.php`, `DomainEmailController.php`
**Change:** Add `$validated['module_id'] = Module::where('slug', ...)->value('id')` in store()
**Risk:** LOW — records gain module_id that were previously null
**Test:** Create VoIP record → verify module_id is set → verify non-SA user can see it
**Priority:** PRE-LAUNCH BLOCKER

### 2B: Remove module_id from ALL global record forms

**Files:** 7 module create/edit views (Service Providers, Domains, Hosting, VPS, Other Services, Assets, Expiry Trackers)
**Change:** Remove the "Module" select field from each form
**Risk:** LOW — module_id will be auto-set in Phase 2C
**Test:** Forms no longer show module selector

### 2C: Auto-set module_id in ALL global record store() methods

**Files:** 9 module controllers
**Change:** Add `$validated['module_id'] = Module::where('slug', $this->moduleSlug())->value('id')` in each store()
**Risk:** LOW — controller already loads module for authorization check
**Test:** Create record → verify module_id matches current route's module

### 2D: Protect module_id in ALL global record update() methods

**Files:** 9 module controllers
**Change:** Add `unset($validated['module_id'])` before update
**Risk:** LOW — module_id should never change after creation
**Test:** Edit record → verify module_id unchanged

---

## PHASE 3: Ownership Metadata Cleanup

### 3A: Remove user_id from ALL global record forms

**Files:** 7 module create/edit views (all except VoIP/Domain Email which already lack it)
**Change:** Remove the "User" select field from each form
**Risk:** LOW — user_id is overridden on create anyway
**Test:** Forms no longer show user selector

### 3B: Remove user_id from $fillable on all 9 models

**Files:** 9 model files
**Change:** Remove `'user_id'` from `$fillable` arrays
**Risk:** LOW — user_id must be set explicitly if needed in future
**Test:** Create record → verify user_id is null (no forced ownership)

### 3C: Remove `$validated['user_id'] = Auth::id()` from store()

**Files:** 9 module controllers
**Change:** Remove the line that forces user_id
**Risk:** LOW — user_id becomes null (was already not used for visibility by RbacScope)
**Test:** Create record → verify user_id is null

---

## PHASE 4: Cross-Channel Visibility Consistency

### 4A: Fix Dashboard generic loop

**File:** `app/Http/Controllers/Api/DashboardController.php`
**Change:** Replace `WHERE user_id` with `WHERE module_id IN (accessibleIds)`
**Risk:** MEDIUM — dashboard counts will change for non-SA users (more accurate)
**Test:** Compare dashboard counts with web list counts — should match

### 4B: Fix ExportController normal user path

**File:** `app/Http/Controllers/Web/ExportController.php`
**Change:** Replace `WHERE user_id` with module-based visibility for normal users
**Risk:** MEDIUM — export scope widens to match web UI
**Test:** Export as normal user → verify records match web list

### 4C: Fix API controllers user_id filtering

**Files:** `Api\ExpiryTrackerController.php`, `Api\DashboardController.php`
**Change:** Remove `$filters['user_id'] = $user->id` — API should use RbacScope or getAccessibleModuleIds()
**Risk:** MEDIUM — API returns more records than before (correct behavior)
**Test:** API and web list return same records for same user

### 4D: Fix API self-demotion and SA assignment

**Files:** `Api\UsersController.php`
**Change:** Add `preventSuperAdminAssignment()` and self-demotion check
**Risk:** LOW — aligns API with web behavior
**Test:** API rejects SA assignment and self-demotion

---

## PHASE 5: Service Layer Cleanup

### 5A: Remove user_id filter from service list() methods

**Files:** 9 service files
**Change:** Remove the `if (isset($filters['user_id']))` block from each list() method
**Risk:** LOW — web controllers don't pass user_id; API controllers will stop passing it after 4C
**Test:** Service list() no longer filters by user_id

### 5B: Verify RenewalSyncService doesn't propagate user_id

**File:** `app/Services/RenewalSyncService.php`
**Change:** Review and ensure it doesn't copy user_id to ExpiryTracker records for global modules
**Risk:** LOW — audit only
**Test:** ExpiryTracker user_id is not set for global module records

---

## PHASE 6: Authorization Hardening (Optional)

### 6A: Add super-admin checks to BulkActionService for non-operational types

**File:** `app/Services/BulkActionService.php`
**Change:** Verify that the ownership fallback (`ownerColumn = user_id`) is correct for all operational types
**Risk:** LOW — audit only
**Test:** Verify bulk actions use module permission, not ownership

---

## PHASE 7: Post-Launch Improvements

### 7A: Legacy privilege system decision

- Option: Remove from admin UI (hide menus/CRUD)
- Option: Integrate into authorization pipeline
- Requires product decision

### 7B: Permanent architectural fixes

- Consider extracting `PermissionService` from `HasModulePermissions` trait
- Consider adding Model Policies
- Consider replacing `RbacScope` static calls with injectable service

---

## EXECUTION SUMMARY

| Phase | Focus | Files Changed | Lines Changed | Risk | Priority |
|-------|-------|---------------|---------------|------|----------|
| 1 | Override fix | 2 | +15 | LOW | ✅ DONE |
| 2A | VoIP/DomainEmail module_id | 2 | +2 | LOW | **NOW** |
| 2B | Remove module_id from forms | 7 | -7 | LOW | **NOW** |
| 2C | Auto-set module_id on create | 9 | +9 | LOW | **NOW** |
| 2D | Protect module_id on update | 9 | +9 | LOW | **NOW** |
| 3A | Remove user_id from forms | 7 | -7 | LOW | **NOW** |
| 3B | Remove user_id from fillable | 9 | -9 | LOW | **NOW** |
| 3C | Remove user_id force from store | 9 | -9 | LOW | **NOW** |
| 4A | Fix dashboard | 1 | ~3 | MEDIUM | Pre-launch |
| 4B | Fix export | 1 | ~5 | MEDIUM | Pre-launch |
| 4C | Fix API | 2 | ~4 | MEDIUM | Pre-launch |
| 4D | API auth alignment | 1 | ~10 | LOW | Pre-launch |
| 5A | Service layer cleanup | 9 | -9 | LOW | Post-launch |
| 6A | BulkAction audit | 1 | 0 | LOW | Post-launch |
| 7A | Privilege system | TBD | TBD | Decision | Post-launch |

**Total pre-launch work:**
- Phase 2 (A-D): ~27 files, +11 net lines
- Phase 3 (A-C): ~25 files, -25 net lines
- Phase 4 (A-D): ~5 files, ~22 net lines
- **Total:** ~57 files, ~8 net lines

**Minimal change, maximum correctness.** Each individual change is 1-4 lines. There is no large refactor. The entire pre-launch scope is about 60 files with ~8 net lines of code — mostly deletions of wrong behavior.

---

## IMMEDIATE NEXT STEP

Implement **Phase 2A**: Auto-set module_id in VoIP and DomainEmail store(). This is the most critical bug — invisible records.
