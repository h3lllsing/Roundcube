# PERMISSION FIX PLAN

Do NOT implement yet. This is a proposal for discussion.

---

## GOAL
A clean permission model where:
- Module-level access controls ALL visibility (menu, routes, records)
- Record-level ownership ONLY applies to personal modules (My Vault, My Tasks)
- Global master records are visible to ALL users with module permission
- Super-admin always has full access
- User overrides correctly override the role baseline (and can be removed)

---

## FIX A: Stale Override Bug (CRITICAL)

**File:** `app/Http/Controllers/Web/UserController.php`
**Method:** `saveUserModulePermissions()`

**Change:** After the main loop, delete any override rows for modules NOT in the payload.

```php
// After the foreach loop
$incomingModuleIds = array_keys($permissions ?? []);
if (!empty($incomingModuleIds)) {
    UserModulePermission::where('user_id', $user->id)
        ->whereNotIn('module_id', $incomingModuleIds)
        ->delete();
}
```

**Why:** The JS only sends modules that were changed. If a module was "Reset to Default" (preset = baseline), it's excluded from the payload. Without this fix, the old override row persists and continues to affect `canOnModule()`.

**Risk:** Low. Only deletes overrides for modules where the user explicitly chose not to include them in the save.

---

## FIX B: Remove `user_id` Forcing on Create (MODERATE)

**Files:** All 9 module controllers (`ServiceProviderController`, `DomainController`, `HostingController`, `VpsController`, `VoipController`, `DomainEmailController`, `OtherServiceController`, `AssetController`, `ExpiryTrackerController`)

**Changes:**
1. Remove `$validated['user_id'] = Auth::id()` from every `store()` method
2. Remove `'user_id'` from every model's `$fillable` array
3. Remove the `user_id` field from every create/edit view

**Why:** Global records should not be "owned" by a single user. The `user_id` was acting as a weak audit field ("created by") but the column name implies ownership, which is wrong.

**Alternative:** If audit trail is needed, add a proper `created_by` field using the `Blameable` trait (already exists in the project, used by Module/Task/Feature models).

---

## FIX C: Auto-set `module_id` from Route (MODERATE)

**Files:** All 9 module controllers

**Changes:**
1. Remove `module_id` field from every create/edit view
2. In every `store()` method, add:
   ```php
   $moduleId = Module::where('slug', $this->moduleSlug())->value('id');
   $validated['module_id'] = $moduleId;
   ```
3. In every `update()` method, protect `module_id` from mass assignment:
   ```php
   unset($validated['module_id']); // Never change module association
   ```
   OR remove it from the model's `$fillable`.

**Why:** A Service Provider record should ALWAYS be associated with the "service-providers" module. Allowing the user to select a different module on the form is a data integrity risk — it could make records invisible to users who have permission for the correct module.

**Special case — VoIP and Domain Email:** These forms already don't have the `module_id` field, but the `store()` method doesn't auto-set it either. Result: `module_id` is null, making records invisible under module scoping. After this fix, module_id will be auto-set correctly.

---

## FIX D: Global Master Record Visibility — Service Layer (MODERATE)

**Files:** All `*Service.php` files and API controllers

**Changes:**
1. Remove the `user_id` filter from `list()` methods in all service classes for global modules
2. Remove `$filters['user_id'] = $user->id` from API controller `index()` methods
3. The RbacScope already handles visibility — the service layer should not double-filter

**Why:** The web controllers correctly use RbacScope for visibility. The API controllers bypass it and apply their own `user_id` scoping, creating an inconsistent visibility model. Either remove the user_id filter from the API controllers, or add the same RbacScope::apply() call in the API controllers.

---

## FIX E: Dashboard Scoping (MODERATE)

**File:** `app/Http/Controllers/Api/DashboardController.php`

**Change:** Replace the `user_id` scope with module-level scope:
```php
foreach ($serviceModels as $key => $modelClass) {
    if (! $isSuperAdmin) {
        $accessibleIds = $user->getAccessibleModuleIds('read');
        $activeQuery->whereIn('module_id', $accessibleIds);
    }
}
```

**Why:** Dashboard should show counts for all records the user has permission to see, not just their own records.

---

## FIX F: RbacScope Cleanup (LOW)

**File:** `app/Helpers/RbacScope.php`

**Changes:**
1. Remove the dead `admin` branch in the ownership fallback path. It's never executed for current controllers.
2. Or better: make the `admin` check apply to the `'module'` visibility path too:
   ```php
   if ($visibility === 'module') {
       if ($user->hasRole('super-admin')) return;
       $accessibleIds = $user->getAccessibleModuleIds('read');
       // ...apply module scope...
       return;
   }
   ```

**Why:** Simplifies the scope logic and makes it clear what happens for each role + visibility combination.

---

## FIX G: Remove `module_id` and `user_id` from All Global Master Forms (LOW)

**Files:** View files for Service Providers, Domains, Hosting, VPS, Other Services, Assets, Expiry Trackers

**Changes:**
1. Remove the `user_id` select field from create/edit views
2. Remove the `module_id` select field from create/edit views

**Why:** These fields are:
- Misleading (user_id = "created by" but called "User")
- Dangerous (can mis-categorize records under wrong module)
- Unnecessary (module_id should be auto-set, user should not be selectable)
- Inconsistent (VoIP and Domain Email already don't have them)

---

## FIX H: Consistent `module_id` Assignment Across All Modules (LOW)

Ensure all 9 module controllers consistently auto-set `module_id` on both `store()` and handle the `module_id` properly on `update()`.

Currently:
- 7 modules (Service Providers, Domains, Hosting, VPS, Other Services, Assets, Expiry Trackers): Have the field in forms, controller validates as nullable
- 2 modules (VoIP, Domain Email): No field in forms, no auto-set → null

After fix:
- All 9 modules: No field in forms, auto-set in controller store(), protected on update()

---

## PRIORITY ORDER

1. **FIX A** (CRITICAL) — Override reset bug. This is the #1 reason "permissions don't apply."
2. **FIX C** (MODERATE) — Auto-set module_id. Prevents invisible records.
3. **FIX D** (MODERATE) — Service layer visibility. Makes API consistent with web.
4. **FIX B** (MODERATE) — Remove user_id forcing. Clarifies ownership model.
5. **FIX E** (MODERATE) — Dashboard scoping.
6. **FIX F** (LOW) — RbacScope cleanup.
7. **FIX G + H** (LOW) — Form cleanup.

---

## IMPLEMENTATION ORDER

```
Iteration 1: FIX A only
  → Test: Save an override, reset it, verify user_module_permissions row is deleted
  → Test: Verify canOnModule() returns role baseline after reset

Iteration 2: FIX C + H
  → Test: Create record from any module → verify module_id is auto-set
  → Test: Verify VoIP/Domain Email records now have correct module_id
  → Test: Verify non-super-admin can see records

Iteration 3: FIX D
  → Test: API endpoints return same records as web controllers
  → Test: Non-super-admin sees all global records (not just own)

Iteration 4: FIX B
  → Test: Create record → verify user_id is not set → verify no ownership
  → Test: Edit record → verify user_id cannot be changed

Iteration 5: FIX E
  → Test: Dashboard shows correct counts

Iteration 6: FIX F + G (cleanup, can be done any time)
```
