# ARCHITECTURE DEBT PLAN — v1.1

> Engineering improvements deferred from v1.0 pre-release. Organized by thematic workstream.
>
> **Principle:** Every change must be provably correct before deployment. Each workstream includes its own verification strategy.

---

## Workstream 1: Module Slug Immutability

### Problem

Module slugs (e.g., `'domains'`, `'hostings'`, `'vps'`) are hardcoded as string literals in 18+ code locations. There is no single source of truth. If a super-admin changes a slug in the database, the following break silently:

| Affected Area | Location | Breakage Mode |
|---------------|----------|---------------|
| Controllers (10) | `Web\*Controller::moduleSlug()` | `Module::where('slug', ...)` returns null → null module_id |
| Sidebar | `SidebarComposer` | Module disappears from sidebar |
| Search | `SearchController` | Module records excluded from results |
| Export | `ExportController` | Module export returns empty |
| Dashboard | `DashboardController` | Module widgets show zero counts |
| Calendar | `CalendarController` | Module renewals not shown |
| Monitor | `MonitorController` | Health checks for module skipped |
| Import | `ImportController` | Module type mapping fails |
| Bulk Operations | Various | Operations on module records fail |
| Renewal Sync | `RenewalSyncService::sync()` | ExpiryTracker created with null module_id |

### Solution: `ModuleSlug` Enum

**Estimated effort:** 3 days

**Design:**

```php
// app/Enums/ModuleSlug.php
enum ModuleSlug: string
{
    case Domains = 'domains';
    case Hostings = 'hostings';
    case Vps = 'vps';
    case Voip = 'voip';
    case ServiceProviders = 'service-providers';
    case DomainEmails = 'domain-emails';
    case OtherServices = 'other-services';
    case ExpiryTrackers = 'expiry-trackers';
    case Assets = 'assets';
    case VaultEntries = 'vault-entries';
    case Tasks = 'tasks';
    case Users = 'users';

    public function label(): string
    {
        return match($this) {
            self::Domains => 'Domains',
            self::Hostings => 'Web Hosting',
            // ...
        };
    }
}
```

**Migration path:**

1. Create `ModuleSlug` enum
2. Add `ModuleSlugResolver` service: `ModuleSlugResolver::resolve(ModuleSlug::Domains): ?Module`
3. Replace ALL `Module::where('slug', 'domains')->first()` with resolver call
4. Replace all 18+ hardcoded module slug arrays with `ModuleSlug::cases()`
5. Add `ModulePolicy` that blocks slug changes and deletion
6. Update tests to reference enum instead of string literals

**Verification:**
- `php artisan test --filter=ModuleTest` — enum values match DB slugs
- Search codebase for remaining string literals matching any module slug
- `ModulePolicyTest` — assert super-admin cannot change slug or delete
- All existing tests pass (no functional change)

### Deferred decisions

| Decision | Recommendation | Why |
|----------|---------------|-----|
| Singleton vs dependency injection | Singleton in `AppServiceProvider` | Used in 18+ locations. Injection requires constructor changes everywhere. |
| Cache module lookups? | Yes, cache by slug for 1 hour | Reduces DB queries. Invalidate on module save. |
| Keep `moduleSlug()` on controllers? | Replace with `ModuleSlug::from(static::class)` | Less duplication. Each controller maps itself to one enum case. |

---

## Workstream 2: API Parity (index vs. show/update/destroy)

### Problem

API `index()` uses module-scoped RBAC (`getAccessibleModuleIds('read')`). API `show()/update()/destroy()` use `user_id` ownership (`where('user_id', auth()->id())`). This means:

- A user who can see 50 records in the API list gets 403 on 48 of them when calling `show()`
- A Procurement Manager with `can_read` on all services cannot access any via API detail
- Inconsistency has zero user impact today (no frontend consumer) but is a contract correctness issue

**Estimated effort:** 1 day

**Scope:** All 9 API CRUD controllers:
- `Api\DomainController`
- `Api\HostingController`
- `Api\VpsController`
- `Api\VoipController`
- `Api\ServiceProviderController`
- `Api\DomainEmailController`
- `Api\OtherServiceController`
- `Api\ExpiryTrackerController`
- `Api\AssetController`

### Solution

Replace `user_id` ownership check in `show()/update()/destroy()` with module-scoped RBAC check, matching the `index()` pattern:

```php
// Current:
$record = $this->model->where('user_id', auth()->id())->findOrFail($id);

// After:
$accessibleIds = auth()->user()->getAccessibleModuleIds('read'); // or 'update'/'delete'
$record = $this->model->whereIn('module_id', $accessibleIds ?: [0])->findOrFail($id);
```

### Migration path

1. For each of 9 controllers, change `show()/update()/destroy()` query scope from `user_id` to `module_id`
2. Use `getAccessibleModuleIds('read')` for `show()`, `getAccessibleModuleIds('update')` for `update()`, `getAccessibleModuleIds('delete')` for `destroy()`
3. Remove `user_id` from API controller authorization — no longer needed
4. Update API tests (all currently use `user_id` ownership assumptions)

**Verification:**
- All existing API tests pass with new scoping
- Add test: User with `can_read` but not creator can `show()` a record
- Add test: User without `can_read` cannot `show()` a record even if creator
- Regression: test suite (1,864+ tests) passes

---

## Workstream 3: `firstOrFail` Module Resolution

### Problem

10 Web controllers use this pattern:
```php
$module = Module::where('slug', $this->moduleSlug())->first();
if ($module) {
    $validated['module_id'] = $module->id;
}
```

If the module is missing (deleted, slug mismatch), `$module` is null and `module_id` is NEVER set. The record gets `module_id = NULL` and becomes invisible to ALL queries.

**Estimated effort:** 1 hour (10 controllers)

### Solution

Replace `->first()` with `->firstOrFail()`:

```php
$module = Module::where('slug', $this->moduleSlug())->firstOrFail();
$validated['module_id'] = $module->id;
```

If the module is missing, `firstOrFail()` throws `ModelNotFoundException`, which Laravel converts to a 404 response. The record is never created with a null module_id.

**Risk:** A super-admin who accidentally deletes a module will get 500 errors on all store operations instead of silent null module_id. This is better — failure is loud and fixable.

**Scope:** 10 Web controllers:
- `Web\DomainController`
- `Web\HostingController`
- `Web\VpsController`
- `Web\VoipController`
- `Web\ServiceProviderController`
- `Web\DomainEmailController`
- `Web\OtherServiceController`
- `Web\ExpiryTrackerController`
- `Web\AssetController`

### Edge case: Super-admin override

When super-admin creates a record without module association? **This should not happen.** In v1.0, every record must belong to a module. If the system needs module-less records, that's a future feature. For v1.1, enforce module existence.

### Concurrent concern

If Workstream 1 (ModuleSlug enum) and Workstream 3 (firstOrFail) happen simultaneously, the controller would look like:

```php
$module = ModuleSlugResolver::resolve(ModuleSlug::fromClass(static::class));
$validated['module_id'] = $module->id;  // ModuleSlugResolver always returns model or throws
```

**Verification:**
- Each controller's store/create test asserts record has non-null `module_id`
- Test: delete a module, attempt store → assert 404 response
- Regression: all existing tests pass

---

## Workstream 4: `created_by` Migration (user_id Rename)

### Problem

`user_id` on business records semantically means "who created this record" but is named "user_id" which implies ownership. This causes:

- Confusion in API authorization (BR-04, BR-08)
- `user_id` is currently in `$fillable` (restored in Phase 4)
- New developers assume `user_id` = ownership boundary
- Rename to `created_by` would clarify semantics across 13+ tables

**Estimated effort:** 2 days

**Scope:** 13 tables:
- `domains`, `hostings`, `vps`, `voip`, `service_providers`, `domain_emails`, `other_services`, `expiry_trackers`, `assets`, `vault_entries`, `tasks`, `notes`, `attachments`

### Migration path

1. Create migration: `RENAME COLUMN user_id TO created_by` on all 13 tables
2. Update all models: `protected $fillable` — remove `user_id` and add `created_by`
3. Update all controllers: `$validated['user_id'] = Auth::id()` → `$validated['created_by'] = Auth::id()`
4. Update all API controllers: `where('user_id', ...)` → `where('created_by', ...)` — but ONLY if API parity (Workstream 2) has NOT been done yet. If Workstream 2 is done, API controllers no longer reference user_id for authorization.
5. Update tests: all `user_id` references in factory states and assertions
6. Update `RenewalSyncService`: `Auth::id()` consumer → explicit `created_by`

### Dependency: Must run AFTER Workstream 2 (API parity)

If Workstream 2 has aligned API to module-scoped RBAC, the `user_id` → `created_by` rename is clean — `created_by` is purely metadata, not authorization. If Workstream 2 hasn't happened yet, the rename creates a temporary situation where `created_by` is used for unauthorized ownership checks, which is semantically worse.

**Recommendation:** Block this workstream until Workstream 2 is complete.

### Verification

- All `user_id` string references eliminated from codebase (grep for `'user_id'` and `->user_id`)
- All tests pass
- No regression in API or Web authorization
- `$fillable` contains `created_by`, not `user_id`

---

## Workstream 5: Super-Admin Hardcoded Literal

### Problem

The string `'super-admin'` appears in 40+ locations across controllers, services, middleware, seeders, and tests. Renaming the role slug or deleting the role collapses the entire authorization system.

**Estimated effort:** 1 day

### Solution

```php
// config/auth.php
return [
    'super_admin_role' => env('SUPER_ADMIN_ROLE', 'super-admin'),
];
```

Or, if enum pattern is preferred:

```php
// app/Enums/RoleSlug.php
enum RoleSlug: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case User = 'user';
    case Customer = 'customer';
}
```

Then replace all `'super-admin'` with `config('auth.super_admin_role')` or `RoleSlug::SuperAdmin->value`.

**Verification:**
- grep for `'super-admin'` — should be zero (only in config)
- All tests pass

---

## Workstream 6: Cache Invalidation Granularity

### Problem

`AppServiceProvider`:
```php
Model::saved(fn() => Cache::increment('dashboard:version'));
Model::deleted(fn() => Cache::increment('dashboard:version'));
```

Every model save invalidates ALL dashboard caches for ALL users. A bulk import of 1,000 records triggers 1,000 cache invalidations. Changing a note (not shown on dashboard) still invalidates dashboards.

**Estimated effort:** 2 hours

### Solution

Scope the listener to only models that affect dashboard data:

```php
// Instead of generic Model::saved(), listen only to specific models
$dashboardModels = ['Domain', 'Hosting', 'Vps', 'Voip', 'ExpiryTracker', 'Task'];
foreach ($dashboardModels as $modelClass) {
    $modelClass::saved(fn() => Cache::increment('dashboard:version'));
    $modelClass::deleted(fn() => Cache::increment('dashboard:version'));
}
```

Or better, use a dedicated event:

```php
Model::saved(function ($model) {
    if (in_array(class_basename($model), ['Domain', 'Hosting', 'Vps', 'Voip', 'ExpiryTracker', 'Task'])) {
        Cache::increment('dashboard:version');
    }
});
```

**Verification:**
- Dashboard cache test: assert version NOT incremented when unrelated model is saved
- All existing dashboard tests pass

---

## Dependency Graph

```
Workstream 1 (ModuleSlug) ────────────┐
                                      ├──→ Workstream 3 (firstOrFail) ───→ Any order
Workstream 2 (API Parity) ────────────┘
                                      │
                                      └──→ Workstream 4 (created_by rename)
                                               ↑
                                               depends on Workstream 2 completion

Workstream 5 (super-admin literal) ───→ Independent
Workstream 6 (cache granularity)  ───→ Independent
```

### Recommended execution order

| Sprint | Workstream | Why This Order |
|--------|-----------|----------------|
| Sprint 1 | WS3 (firstOrFail) | 1 hour, highest safety impact. Do first to eliminate silent null module_id. |
| Sprint 1 | WS1 (ModuleSlug) | 3 days, foundational. Enables WS2 and WS3 consolidation. |
| Sprint 2 | WS2 (API Parity) | 1 day. Depends on WS1 for clean slug resolution in API controllers. |
| Sprint 2 | WS5 (super-admin literal) | 1 day. Independent. |
| Sprint 3 | WS6 (cache granularity) | 2 hours. Independent. Quick win. |
| Sprint 3+ | WS4 (created_by rename) | 2 days. MUST come after WS2. |

---

## Effort Summary

| Workstream | Description | Effort | Dependencies |
|-----------|-------------|--------|-------------|
| WS1 | ModuleSlug enum + registry + policy | 3 days | None |
| WS2 | API show/update/destroy module-scoping | 1 day | WS1 (recommended) |
| WS3 | firstOrFail in Web controllers | 1 hour | None |
| WS4 | user_id → created_by migration | 2 days | WS2 (must) |
| WS5 | super-admin literal → config constant | 1 day | None |
| WS6 | Cache invalidation granularity | 2 hours | None |
| **Total** | | **~7-8 days** | |

**Risk-adjusted estimate:** 10-12 days (including testing, review, edge cases).
