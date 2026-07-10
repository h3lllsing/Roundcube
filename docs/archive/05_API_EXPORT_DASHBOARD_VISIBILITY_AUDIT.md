# 05 — API / EXPORT / DASHBOARD VISIBILITY AUDIT

## Does every data exit point use the same visibility rules? **NO.**

---

## Web Controllers — RbacScope (module visibility)

**Used by:** All 9 module controllers (index, show, edit, update, destroy)
**Visibility rule:** `WHERE module_id IN (accessibleModuleIds)`
**Correct for global records?** ✅ YES
**Applies to:** Non-super-admin users

All web controllers call `$this->userOwnedFilter()` which calls `RbacScope::apply(Model::class, 'module')`.

**Verdict:** ✅ Correct for global master records.

---

## API Controllers — User_id ownership filter

**Used by:** `Api\ExpiryTrackerController`, `Api\VaultController`, `Api\TaskController`, `Api\DashboardController`
**Visibility rule:** `WHERE user_id = ?`
**Correct for global records?** ❌ NO

### `ExpiryTrackerController.php:53`
```php
if (! $user->hasRole('super-admin')) {
    $filters['user_id'] = $user->id;
}
```
Then `ExpiryTrackerService::list()` applies `$query->where('user_id', $filters['user_id'])`.

**Result:** API returns only the user's own ExpiryTrackers, not all global ones.

### `ExpiryTrackerController.php:118`
```php
if (! $user->hasRole('super-admin') && $expiryTracker->user_id !== $user->id) {
    abort(403, 'Forbidden');
}
```
**Result:** API blocks access to records the user doesn't "own", even if they have module-level read permission.

**Verdict:** ❌ **Wrong.** API should use the same module-based visibility as web controllers.

### `VaultController.php:67`
```php
if (! $user->hasRole('super-admin')) {
    $accessibleModuleIds = $user->getAccessibleModuleIds('read');
    // ... filter vault entries by accessible module IDs + user ownership ...
}
```
**Verdict:** ✅ Partially correct. The vault has both module-level and ownership-level access (personal module). This is a personal module, so ownership filter is appropriate here.

### `TaskController.php:44`
```php
if (! $user->hasRole('super-admin')) {
    $accessibleModuleIds = $user->getAccessibleModuleIds('read');
    // ... filter by accessible module IDs + assignee ...
}
```
**Verdict:** ✅ Correct for tasks (personal + module-scoped).

---

## Dashboard — User_id ownership filter

### `DashboardController.php:148-155` (generic service model loop)
```php
foreach ($serviceModels as $key => $modelClass) {
    if (! $isSuperAdmin) {
        $activeQuery->where('user_id', $user->id);
    }
}
```

**Affected models:** All 9 global master record models.
**Visibility rule:** `WHERE user_id = ?`
**Correct for global records?** ❌ NO

### `DashboardController.php:58-73` (modules/features)
```php
if (! $isSuperAdmin) {
    $accessibleModuleIds = Module::whereHas('rolePermissions', ...)->pluck('id');
    // Filter features/modules to accessible ones
}
```
**Verdict:** ✅ Correct for module/feature navigation.

### `DashboardController.php:76-84` (tasks)
Uses `$accessibleModuleIds` + ownership (assignee). Correct for personal module.

### RenewalsWidget.php:26
```php
if (! $isSuperAdmin) {
    $query->whereIn('module_id', $accessibleIds);
}
```
**Verdict:** ✅ Correct. Uses module_id, not user_id.

**Inconsistency within the same Dashboard:** Renewals show global counts, but Hosting/VPS/VoIP/etc show only user-owned counts. The dashboard is thus unreliable for management visibility.

---

## ExportController — Mixed visibility

### `ExportController.php:129-145`
```php
// Super-admin → NO SCOPE → export all
// Admin with can_export → WHERE module_id IN (accessibleIds)
// Normal user → WHERE user_id = ?
```

**Normal user export:** Uses `user_id` ownership filtering. But the web controller index page (which also uses RbacScope) shows the user ALL records they have module permission for. So a user can SEE 50 records on the web page but can only EXPORT their own 12. **This is confusing and inconsistent.**

**Verdict:** ❌ Normal user export should use the same module-based visibility as web controllers.

---

## Service Layer — User_id filter in list() methods

Every `*Service::list()` method has a filter like:
```php
if (isset($filters['user_id'])) {
    $query->where('user_id', $filters['user_id']);
}
```

**Called by web controllers?** NO — web controllers pass RbacScope, not user_id filters.
**Called by API controllers?** YES — API controllers pass user_id.

So the service layer has two visibility paths:
- Web path: RbacScope (module-based) — correct
- API path: user_id filter (ownership-based) — wrong

**This is a ticking time bomb.** If a future web controller starts using the service `list()` method directly (instead of its own query with RbacScope), it would silently switch from module-based to ownership-based visibility.

---

## GlobalSearch Service

### `GlobalSearchService.php:490-527`

Uses `ownership` config per module:
- `user_or_module` for global records: `WHERE user_id = ? OR module_id IN (accessibleIds)` — ACCEPTABLE
- `user` for notes: `WHERE user_id = ?`
- `sa_only` for features: super-admin only

**Verdict:** ✅ The global search service uses a reasonable hybrid approach. The `user_or_module` type allows both ownership and module-based visibility. This is actually more permissive than the web controllers, but that's acceptable for search.

---

## VISIBILITY CONSISTENCY MATRIX

| Access Point | Service Providers | Hosting | Domains | VoIP | Dashboard Renewals | Dashboard Generic |
|-------------|-------------------|---------|---------|------|-------------------|-------------------|
| Web index | ✅ Module | ✅ Module | ✅ Module | ✅ Module | N/A | N/A |
| Web show | ✅ Module | ✅ Module | ✅ Module | ✅ Module | N/A | N/A |
| API | ❌ user_id | ❌ user_id | ❌ user_id | ❌ user_id | N/A | N/A |
| Dashboard | ❌ user_id | ❌ user_id | ❌ user_id | ❌ user_id | ✅ Module | ❌ user_id |
| Export (SA) | ✅ None | ✅ None | ✅ None | ✅ None | N/A | N/A |
| Export (admin) | ✅ Module | ✅ Module | ✅ Module | ✅ Module | N/A | N/A |
| Export (user) | ❌ user_id | ❌ user_id | ❌ user_id | ❌ user_id | N/A | N/A |
| GlobalSearch | ✅ user_or_module | ✅ user_or_module | ✅ user_or_module | ✅ user_or_module | N/A | N/A |

**6 different visibility rules for the same data across 6 access points.**

---

## RECOMMENDATION

1. **Replace all `WHERE user_id` filters in API controllers** with `RbacScope::apply()` or `getAccessibleModuleIds('read')` based filtering
2. **Fix Dashboard generic loop** to use module_id IN (accessibleIds) instead of user_id
3. **Fix ExportController** normal user path to use module-based visibility
4. **Remove user_id filter from service `list()` methods** — let callers handle visibility
5. **Add a test** that compares web list results vs API list results for the same user to ensure they match
