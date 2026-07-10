# GLOBAL MASTER RECORD VISIBILITY AUDIT

## The Problem
Global master records (Service Providers, Domains, Hosting, VPS, VoIP, Domain Emails, Other Services, Assets, Renewals) should be **company-level data** visible to all users with module permission. Instead, each record carries a `user_id` that creates an ownership association, and the `RbacScope` system uses this to filter records.

---

## RbacScope Module Visibility — How It Actually Works

All 9 modules call `RbacScope::apply(Model::class, 'module')` in their `userOwnedFilter()` method.

| User Role | Scope Applied | Effect |
|-----------|---------------|--------|
| super-admin | None | Sees ALL records (no WHERE clause) |
| Everyone else | `module_id IN (accessibleModuleIds)` | Sees only records whose module_id is in the user's accessible module set |

Where `accessibleModuleIds` is computed by `HasModulePermissions::getAccessibleModuleIds('read')`:
1. Get all module IDs where ANY of user's roles has `can_read=true` in `module_role_permissions`
2. Apply user overrides: add/remove based on `user_module_permissions`

**Core insight:** The `user_id` column is NEVER checked by these scopes. A non-super-admin user sees a global record if and only if the record's `module_id` is in their accessible set.

---

## Module-by-Module Visibility Analysis

### Service Providers
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id IN accessibleIds | ✓ | CORRECT |
| Controller | $provider->module → canOnModule('update') | ✓ | CORRECT |
| Service list() | WHERE user_id | `ServiceProviderService.php:27` | **WRONG** — filters by user_id |
| Export | user_id fallback | `ExportController.php:144` | **WRONG** — falls back to user_id |
| GlobalSearch | user_id OR module_id | `GlobalSearchService.php` (user_or_module) | ACCEPTABLE — searches both |

### Domains
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id IN accessibleIds | ✓ | CORRECT |
| Controller | Domain::with('module', 'hosting', ...) | ✓ | CORRECT |
| Service list() | WHERE user_id | `DomainService.php:28` | **WRONG** |
| Export | user_id fallback | Same pattern | **WRONG** |
| GlobalSearch | user_id OR module_id | `user_or_module` | ACCEPTABLE |

### Hosting
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `HostingService.php:27` | **WRONG** |
| GlobalSearch | user_id OR module_id | ACCEPTABLE | ACCEPTABLE |

### VPS
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `VpsService.php:27` | **WRONG** |
| GlobalSearch | user_id OR module_id | ACCEPTABLE | ACCEPTABLE |

### VoIP
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `VoipService.php:27` | **WRONG** |
| GlobalSearch | user_id OR module_id | ACCEPTABLE | ACCEPTABLE |

**SPECIAL NOTE:** VoIP create/edit forms do NOT have a `module_id` field. The controller passes `$modules` to the view but the view doesn't render it. `StoreVoipRequest::rules()` does validate `module_id ⇒ nullable|exists`, but since no input is sent, `module_id` is null on create. Records created via the VoIP form have **null module_id**, making them invisible to non-super-admin users under the module scope. This is a **SILENT DATA LOSS** bug.

### Domain Emails
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `DomainEmailService.php:30` | **WRONG** |
| GlobalSearch | user_id OR module_id | ACCEPTABLE | ACCEPTABLE |

Same issue as VoIP: forms have no `module_id` field → records get null module_id → invisible to non-super-admins.

### Other Services
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `OtherServiceService.php:27` | **WRONG** |
| Controller | module field present | ✓ | OK (field exists but is problematic) |

### Assets
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `AssetService.php:46` | **WRONG** |
| Service list() | WHERE assigned_to | `AssetService.php:34` | NEEDS REVIEW — Assets have an `assigned_to` column distinct from ownership. The `assigned_to` filter is a legitimate scoping mechanism for asset assignment. |
| Dashboard | WHERE user_id | `DashboardController.php:151` | **WRONG** for non-super-admin |

### Renewals (ExpiryTrackers)
| Filter | Column | Evaluated | Status |
|--------|--------|-----------|--------|
| RbacScope | module_id | ✓ | CORRECT |
| Service list() | WHERE user_id | `ExpiryTrackerService.php:35` | **WRONG** |
| Dashboard | WHERE module_id IN accessibleIds | `RenewalsWidget.php:26` | CORRECT |

---

## Dashboard Scoping Problem

`DashboardController.php:148-155` uses a generic loop that applies `user_id` scope for ALL modules:
```php
foreach ($serviceModels as $key => $modelClass) {
    if (! $isSuperAdmin) {
        $activeQuery->where('user_id', $user->id);
    }
}
```
This is **WRONG** for all global master modules. A non-super-admin user would only see their own records on the dashboard, even if they have module-level read access to all records.

---

## Service Layer `user_id` Filter Problem

Every `*Service.php::list()` method has a filter like:
```php
if (isset($filters['user_id'])) {
    $query->where('user_id', $filters['user_id']);
}
```

Looking at how this is called:
- **Web controllers** index() methods do NOT pass `user_id` filter. They rely on RbacScope.
- **API controllers** DO pass `user_id` filter for non-super-admins:
  - `ExpiryTrackerController::index()` — passes `$filters['user_id'] = $user->id`
  - Other API controllers follow the same pattern

This creates **two different visibility models**: web (module-scoped) vs API (ownership-scoped).

---

## Summary Table

| Module | RbacScope | Service user_id filter | Dashboard | API | Export | GlobalSearch | Verdict |
|--------|-----------|----------------------|-----------|-----|--------|--------------|---------|
| Service Providers | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| Domains | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| Hosting | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| VPS | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| VoIP | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places. Also null module_id bug |
| Domain Email | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places. Also null module_id bug |
| Other Services | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| Assets | CORRECT | WRONG | WRONG | WRONG | WRONG | OK | Wrong in 4/6 places |
| Renewals | CORRECT | WRONG | CORRECT | WRONG | WRONG | OK | Wrong in 3/6 places |

---

## CONCLUSIONS

1. **The RbacScope module-visibility approach is correct** — it properly filters records by module access without using user_id.

2. **The service layer `user_id` filter is wrong** — it applies user ownership filtering that should not exist for global records. This is used by API endpoints.

3. **`user_id` on global records is vestigial** — it's set to the creator's ID but not used for authorization in the web UI. It IS used incorrectly in services/dashboard/API.

4. **VoIP and Domain Email have a null module_id bug** — the forms don't include `module_id`, so records get null, which makes them invisible under module scoping.

5. **The `module_id` field on forms is dangerous** — it allows users to categorize records under wrong modules, which would make them invisible under module scoping.

6. **The `user_id` field on forms is misleading** — on create it's overridden by the controller to Auth::id(). On edit it CAN be changed, which would silently move record "ownership."
