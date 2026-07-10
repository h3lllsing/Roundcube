# DUPLICATE / OVERLAPPING PAGE + POLICY AUDIT

**Project:** OpsPilot Portal
**Date:** 2026-07-08
**Auditor:** opencode

---

## DUP-001: Dead Blade View `guide.blade.php`

| Field | Value |
|-------|-------|
| **Primary** | `HelpController@index` → `resources/views/help/index.blade.php` |
| **Duplicate** | `resources/views/guide.blade.php` (460 lines, never rendered) |
| **Evidence** | Route `guide` maps to `HelpController@index` which returns `view('help.index')`. No controller returns `view('guide')`. No `@include('guide')` exists. File is a standalone static guide. |
| **Which is used** | `resources/views/help/index.blade.php` |
| **Canonical** | `help/index.blade.php` (Vite-integrated, properly routed) |
| **Can be removed?** | YES — file is dead code |
| **Safe removal** | Delete `resources/views/guide.blade.php` |
| **Risk** | Low — no code references it |
| **Tests required** | grep full codebase for `guide` references; verify no `@include('guide')` or `view('guide')` |
| **Rollback** | Restore from git: `git checkout -- resources/views/guide.blade.php` |

---

## DUP-002: Orphaned Route `design-system`

| Field | Value |
|-------|-------|
| **Primary** | No primary — this IS the duplicate |
| **Duplicate** | `Route::view('/design-system', 'design-system')` in `routes/web.php:288` |
| **Evidence** | Route exists behind `auth+suspended+role:super-admin` middleware. No sidebar link, no UI reference, no `route('design-system')` call anywhere. Only accessible by typing URL manually. |
| **Which is used** | Not used by any real user |
| **Canonical** | N/A — developer-only preview |
| **Can be removed?** | YES — no production value |
| **Safe removal** | Remove line 288 from `routes/web.php` + delete `resources/views/design-system.blade.php` |
| **Risk** | Low — no user relies on it |
| **Tests required** | grep for `design-system` in codebase |
| **Rollback** | Restore route + view from git |

---

## DUP-003: `BaseResourceController` — 6 Subclasses Duplicate `store()` and `update()` Boilerplate

| Field | Value |
|-------|-------|
| **Primary** | `BaseResourceController` (lines 88-130 provide `index`, `create`, `show`, `edit`, `destroy`, `restore`, `forceDelete`) |
| **Duplicate** | All 6 BRC subclasses override `store()` and `update()` with ~90% identical code |
| **Evidence** | `DomainController:88/97`, `HostingController:93/105`, `VpsController:105/123`, `VoipController:101/119`, `ServiceProviderController:92/110`, `OtherServiceController:101/118` — each 15-20 lines of near-identical module resolution, permission check, data prep, renewal sync |
| **Which is used** | Each subclass's own override (base class has no `store()`/`update()`) |
| **Canonical** | Should be `BaseResourceController::store()` and `::update()` |
| **Can be removed?** | YES — refactor into base class. Each subclass only needs to provide `prepareStoreData()`/`prepareUpdateData()` |
| **Safe removal** | 1. Add `store()` and `update()` to `BaseResourceController` using the shared pattern. 2. Remove identical code from each subclass. 3. Verify each subclass `prepareStoreData`/`prepareUpdateData` handles its unique logic. |
| **Risk** | Medium — each subclass currently calls `parent::store()` differently. Must verify all 6 store/update paths work identically after refactor. |
| **Tests required** | Full CRUD test for all 6 modules (domain, hosting, vps, voip, service-providers, other-services) |
| **Rollback** | Revert all subclass changes + base class addition |

---

## DUP-004: `AssetController` Reimplements `BaseResourceController` Pattern

| Field | Value |
|-------|-------|
| **Primary** | `BaseResourceController` — provides full CRUD with RBAC, module scoping, bulk actions |
| **Duplicate** | `AssetController` (251 lines) implements the SAME pattern manually |
| **Evidence** | Has `userOwnedFilter()`, `moduleSlug()` (private), `canOnModule()` checks, `RbacScope::apply()` calls, `$canCreate`/`$canExport`/`$canBulkDelete` variables — identical to BRC but without extending it |
| **Which is used** | `AssetController` — standalone |
| **Canonical** | Should extend `BaseResourceController` |
| **Can be removed?** | YES — refactor `AssetController` to extend `BaseResourceController` |
| **Safe removal** | 1. Change `AssetController extends Controller` to `extends BaseResourceController`. 2. Implement 6 abstract methods. 3. Remove duplicated `index()`, `create()`, `show()`, `edit()`, `destroy()` methods. 4. Keep custom `assign()`, `returnAsset()`, `forceDelete()`. |
| **Risk** | Medium — Asset has custom logic (assign, return) not in BRC. Need to ensure `indexSelect`, `indexWith`, etc. match current behavior. |
| **Tests required** | Full CRUD test for Assets + assign/return flow |
| **Rollback** | Revert `AssetController` extends declaration and restore removed methods |

---

## DUP-005: `DomainEmailController`, `ExpiryTrackerController`, `VaultController` — Standalone with `moduleSlug()` as Private

| Field | Value |
|-------|-------|
| **Primary** | `BaseResourceController` defines `moduleSlug()` as `abstract protected` |
| **Duplicate** | `DomainEmailController`, `ExpiryTrackerController`, `VaultController` define `moduleSlug()` as `private` |
| **Evidence** | These 3 controllers are standalones (extend `Controller` not `BaseResourceController`). Each has a `private function moduleSlug()` that returns the slug string — identical concept but not shared. |
| **Which is used** | Each controller's own private method |
| **Canonical** | Should be `protected` in all, or controllers should extend BRC |
| **Can be removed?** | FUTURE — too risky to refactor 3 controllers at once. Flagged for Phase 3. |
| **Safe removal** | 1. Audit each controller to confirm it can extend BRC. 2. Change extends. 3. Implement abstract methods. 4. Remove private `moduleSlug()` in favor of BRC's abstract. |
| **Risk** | Medium-High — each has significant custom logic (ExpiryTracker with email/renew, Vault with encryption, DomainEmail with password reveal). |
| **Tests required** | Full CRUD + custom feature tests for each |
| **Rollback** | Revert extends declaration + restore removed methods |

---

## DUP-006: `HostingController` Missing `prepareStoreData`

| Field | Value |
|-------|-------|
| **Primary** | `ServiceProviderController`, `DomainController`, `VpsController`, `VoipController` all call `prepareStoreData()` in their `store()` method |
| **Duplicate** | `HostingController::store()` (line 93) creates the model directly without calling `prepareStoreData()` |
| **Evidence** | `HostingController` overrides `prepareUpdateData` (line 74) to clean password, but `store()` does NOT call `prepareStoreData()`. However, `store()` also cleans the password inline (line 96). So there's no functional bug — just inconsistency. |
| **Which is used** | Inline password cleaning in `store()` |
| **Canonical** | Both `store()` and `update()` should use `prepareStoreData`/`prepareUpdateData` consistently |
| **Can be removed?** | YES — make `store()` call `prepareStoreData()` like the others do |
| **Safe removal** | In `HostingController::store()`, replace inline password cleaning with `$data = $this->prepareStoreData($validated);` |
| **Risk** | Low — functionally identical, just moves code |
| **Tests required** | Hosting CRUD tests + password preservation on edit |
| **Rollback** | Revert `store()` to inline version |

---

## DUP-007: `OtherServiceController` Missing `prepareStoreData`

| Field | Value |
|-------|-------|
| **Primary** | Same pattern as HostingController (DUP-006) |
| **Duplicate** | `OtherServiceController::store()` (line 107) creates model directly without calling `prepareStoreData()` |
| **Evidence** | Overrides `prepareUpdateData` (line 88) to clean password. Store does not call `prepareStoreData()`. |
| **Which is used** | Inline in `store()` |
| **Canonical** | Should use `prepareStoreData()` |
| **Can be removed?** | YES |
| **Safe removal** | Replace inline with `$this->prepareStoreData($validated)` |
| **Risk** | Low |
| **Tests required** | OtherService CRUD tests |
| **Rollback** | Revert `store()` |

---

## DUP-008: `show()` in `BaseResourceController` Lacks Explicit `canOnModule(read)` Check

| Field | Value |
|-------|-------|
| **Primary** | `create()`, `edit()`, `destroy()` all have explicit `abort_unless(isSuperAdmin || canOnModule(...))` |
| **Duplicate** | `show()` (lines 146-156) only relies on RbacScope global scope + `findOrFail()`. No explicit `canOnModule('read')` check. |
| **Evidence** | A user with `canOnModule(update)` but NOT `canOnModule(read)` could potentially view a record if the RbacScope doesn't filter it out. This is a gap. |
| **Which is used** | RbacScope-only (defense in depth missing) |
| **Canonical** | Should add `abort_unless(canOnModule(read))` |
| **Can be removed?** | N/A — this is a missing check, not a duplicate |
| **Risk** | Failure to add: Medium — RbacScope currently covers this, but defense in depth is best practice |
| **Tests required** | Test that user with can_update but no can_read gets 403 on show page |
| **Rollback** | Revert the added check |

---

## DUP-009: `show()` in All API Controllers Lacks `canOnModule(read)` Check

| Field | Value |
|-------|-------|
| **Primary** | `create`/`store` and `update`/`destroy` have `canOnModule` checks |
| **Duplicate** | `show` endpoints across API controllers only check ownership, not module read permission |
| **Evidence** | `Api\VpsController::show()` (line 46): only scopes by accessible IDs — doesn't check `can_read`. If user has `can_read` on Module A but the record belongs to Module B, the record is still shown if accessible module IDs include B. |
| **Which is used** | ID-scoping only |
| **Canonical** | Should add module slug match or `canOnModule(read)` |
| **Can be removed?** | N/A — missing check |
| **Risk** | User could see records from modules they don't have read access to if module_id scoping fails |
| **Tests required** | API show endpoint tests with mixed module access |
| **Rollback** | Revert added check |

---

## DUP-010: Hardcoded Ownership Checks in API Controllers Bypass Permission Evaluator

| Field | Value |
|-------|-------|
| **Primary** | `canOnModule($module, $action)` evaluator |
| **Duplicate** | `$record->user_id !== $user->id` hardcoded ownership checks across ALL API resource controllers |
| **Evidence** | 40+ occurrences across `Api\VpsController`, `Api\VoipController`, `Api\HostingController`, `Api\DomainController`, `Api\ServiceProviderController`, `Api\OtherServiceController`, `Api\DomainEmailController`, `Api\ExpiryTrackerController`, `Api\AssetController`, `Api\NoteController`, `Api\AttachmentController`, `Api\WebhookController` |
| **Which is used** | Both — ownership AND `canOnModule()` are checked separately (OR logic) |
| **Canonical** | `canOnModule()` alone should be sufficient. Ownership check is redundant and potentially wrong for admin users who need to edit other users' records. |
| **Can be removed?** | NOT YET — ownership check is intentionally layered. But it creates inconsistency where an admin with `can_update` on the module still can't edit a record owned by someone else. |
| **Safe removal** | 1. Audit each API controller. 2. Remove `$record->user_id !== $user->id` check. 3. Keep `canOnModule(update)` check. |
| **Risk** | Medium-High — changing API authorization could break integrations |
| **Tests required** | API CRUD tests for all 11 affected controllers (create/read/update/delete for non-owner users) |
| **Rollback** | Restore ownership check lines |

---

## DUP-011: `can_approve` Permission Key Configured but Never Checked

| Field | Value |
|-------|-------|
| **Primary** | `can_create`, `can_read`, `can_update`, `can_delete` — actively checked in controllers |
| **Duplicate** | `can_approve` — exists in `config/permissions.php`, in DB migration, in model fillable/casts — but NO controller or policy ever checks it |
| **Evidence** | grep for `can_approve` and `approve` in `app/` shows 0 authorization checks. Only appears in config, model, blade init data, and JS toggleToColumn map. |
| **Which is used** | Never checked at runtime |
| **Canonical** | Either implement `can_approve` checks for a future approval workflow, or remove from config and DB |
| **Can be removed?** | DEPENDS — if approval workflow is planned, keep. If not, remove to reduce confusion. |
| **Safe removal** | 1. Remove from `config/permissions.php` keys array. 2. Add DB migration to drop column. 3. Remove from model fillable/casts. 4. Remove from JS toggleToColumn. 5. Remove from blade init data. |
| **Risk** | Low if approval workflow is not used. Medium if someone relies on it in a custom integration. |
| **Tests required** | grep for `can_approve` and `approve` in codebase to confirm no usage; run full test suite |
| **Rollback** | Revert migration + config + model + JS changes |

---

## DUP-012: `ModulePermissionService::removeForRole()` Missing Cache Invalidation

| Field | Value |
|-------|-------|
| **Primary** | `setForRole()` increments `perms_generation` after upsert |
| **Duplicate** | `removeForRole()` (lines 51-56) deletes role permissions but does NOT increment `perms_generation` |
| **Evidence** | Line 54: `ModuleRolePermission::where(...)->delete()` — no `Cache::increment('perms_generation')` after. Compare with setForRole line 46 which does increment. |
| **Which is used** | removeForRole is used by ModulePermissionController::destroy (web + API) |
| **Canonical** | Should increment `perms_generation` after delete |
| **Can be removed?** | N/A — cache bug needs fixing, not removal |
| **Risk** | Stale permissions persist for up to 60s (cache TTL) after removing role permissions |
| **Tests required** | Test that module permissions reflect changes immediately after removeForRole |
| **Rollback** | Revert the added `Cache::increment` line |

---

## DUP-013: `getAllModulePermissionsFromDb()` Ignores User Overrides for Modules Without Role Permissions

| Field | Value |
|-------|-------|
| **Primary** | `canOnModule()` correctly checks user overrides regardless of role permissions |
| **Duplicate** | `getAllModulePermissionsFromDb()` (lines 72+89-91) only loads user overrides for `$allModuleIds` (modules that have role permissions). Overrides for other modules are silently ignored in the cached path. |
| **Evidence** | Line 72: `$allModuleIds = $perms->keys()->toArray();` — only modules with role perms. Lines 89-91: `UserModulePermission::whereIn('module_id', $allModuleIds)` — ignores overrides for other modules. |
| **Which is used** | Both paths run independently. `canOnModule()` (non-cached) works correctly. `getAccessibleModuleIds()` (cached) and `getAllModulePermissions()` (cached) miss overrides for modules without role perms. |
| **Canonical** | Cached path should match `canOnModule()` logic |
| **Can be removed?** | N/A — bug fix needed. Remove the `$allModuleIds` filter and load all user overrides. |
| **Risk** | Medium — `getAccessibleModuleIds('read')` could return wrong results for users with overrides on modules lacking role permissions |
| **Tests required** | Test user override on module without role permissions vs `getAccessibleModuleIds()` result |
| **Rollback** | Revert the fix |

---

## DUP-014: `api-tokens` vs `tokens` Slug Mismatch in Sensitive Modules Config

| Field | Value |
|-------|-------|
| **Primary** | Config `sensitive_modules` lists `api-tokens` |
| **Duplicate** | Actual module slug in seeder and routes is `tokens` |
| **Evidence** | `config/permissions.php:13` has `'api-tokens'`. `FeatureModuleSeeder` creates slug `'tokens'`. Route is `/tokens`. Sidebar link is `route('tokens.index')`. |
| **Which is used** | `tokens` (the actual module slug) |
| **Canonical** | Config should use `tokens` to match the actual module |
| **Can be removed?** | YES — fix config to use `tokens` instead of `api-tokens` |
| **Safe removal** | Change `'api-tokens'` to `'tokens'` in `config/permissions.php` |
| **Risk** | Low — the sensitive module check for `api-tokens` never triggers because no module has that slug |
| **Tests required** | Verify isSensitive check works for tokens module |
| **Rollback** | Restore `api-tokens` in config |

---

## DUP-015: `UserController` Contains Redundant `abort_unless(hasRole('super-admin'))` Checks

| Field | Value |
|-------|-------|
| **Primary** | Route middleware `role:super-admin` on all UserController routes (web.php:271-283) |
| **Duplicate** | Every method in `UserController` also has `abort_unless(Auth::user()->hasRole('super-admin'), 403)` as its first line |
| **Evidence** | 17 occurrences of `abort_unless(hasRole('super-admin'))` in UserController.php — all redundant because the route middleware already enforces this |
| **Which is used** | Both — middleware filters first, controller checks second (defense in depth) |
| **Canonical** | Controller checks are technically redundant but provide defense in depth if route middleware is ever removed |
| **Can be removed?** | YES — but consensus in Laravel community is to keep for defense in depth |
| **Safe removal** | Remove all `abort_unless(hasRole('super-admin'))` from UserController methods |
| **Risk** | If route middleware is accidentally removed, all user endpoints become accessible to non-super-admins |
| **Tests required** | Route-based middleware tests (already covered by middleware test) |
| **Rollback** | Restore all removed abort_unless lines |

---

## DUP-016: CleansPasswords Trait Logic Differs Across Users

| Field | Value |
|-------|-------|
| **Primary** | `CleansPasswords` trait — `cleanPasswordField($data, $record)` — 13 lines |
| **Duplicate** | Some controllers inline the password cleaning logic instead of using the trait |
| **Evidence** | `HostingController`, `VpsController`, `VoipController`, `ServiceProviderController`, `OtherServiceController`, `DomainEmailController`, `ExpiryTrackerController` USE the trait. But `AssetController`, `VaultController`, `UserController` handle passwords inline. |
| **Which is used** | Both — trait and inline |
| **Canonical** | Trait is canonical |
| **Can be removed?** | YES — migrate inline password cleaning to use the trait |
| **Safe removal** | Add `use CleansPasswords;` to AssetController, VaultController, UserController and replace inline logic with `$this->cleanPasswordField($data)` |
| **Risk** | Low |
| **Tests required** | Password-related tests for Asset, Vault, User |
| **Rollback** | Revert trait usage |

---

## DUP-017: No Laravel Policy Files Exist — All Authorization is Custom

| Field | Value |
|-------|-------|
| **Primary** | `$user->canOnModule()` — custom permission evaluator |
| **Duplicate** | No `@can`, no `Gate::`, no `$this->authorize()` anywhere |
| **Evidence** | `app/Policies/` directory does not exist. `AuthServiceProvider.php` does not exist. All authorization goes through `canOnModule()` + `hasRole('super-admin')` + `abort_unless()`. |
| **Which is used** | Custom RBAC throughout |
| **Canonical** | Custom RBAC is fine for this application size |
| **Can be removed?** | Not applicable — no policies to remove |
| **Risk** | N/A — this is an architectural note, not a duplicate |

---

## DUPLICATES SUMMARY

| ID | Type | Severity | Effort to Fix |
|----|------|----------|---------------|
| DUP-001 | Dead view `guide.blade.php` | Low | 5 min |
| DUP-002 | Orphaned route `design-system` | Low | 5 min |
| DUP-003 | 6x duplicate store/update in BRC subclasses | Medium | 4 hours |
| DUP-004 | AssetController duplicates BRC pattern | Medium | 2 hours |
| DUP-005 | 3 standalone controllers with private moduleSlug | Low | Flagged for Phase 3 |
| DUP-006 | HostingController missing prepareStoreData | Low | 15 min |
| DUP-007 | OtherServiceController missing prepareStoreData | Low | 15 min |
| DUP-008 | show() missing canOnModule(read) in BRC | Medium | 15 min |
| DUP-009 | API show() missing canOnModule(read) | Medium | 1 hour |
| DUP-010 | Ownership checks bypass permission evaluator | Medium-High | Requires discussion |
| DUP-011 | can_approve configured but never checked | Low | 30 min |
| DUP-012 | removeForRole missing cache invalidation | Medium | 5 min |
| DUP-013 | Cached path ignores overrides for non-role modules | Medium | 15 min |
| DUP-014 | api-tokens vs tokens slug mismatch | Low | 1 min |
| DUP-015 | Redundant super-admin checks in UserController | Info | 30 min (not recommended) |
| DUP-016 | CleansPasswords trait not used consistently | Low | 30 min |
| DUP-017 | No Laravel policies (architectural note) | Info | N/A |
