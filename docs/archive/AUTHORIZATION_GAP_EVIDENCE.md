# AUTHORIZATION GAP EVIDENCE

## Allegation: Web admin controllers missing authorization

> Check if any authenticated non-super-admin can access create/update/delete/apply/import routes on:
> FeatureController, ModuleController, RoleController, PrivilegeController, RoleTemplateController,
> SmtpProfileController, ModulePermissionController, ImportController

---

## VERDICT: FALSE POSITIVE

**None of the alleged create/update/delete/apply/import routes are accessible to non-super-admin users.**

All 8 controllers' write/delete/apply/import routes are wrapped in `role:super-admin` middleware at the route level in `routes/web.php`.

---

## EVIDENCE

### Route file: `routes/web.php`

#### Lines 228-229 — Super-admin group wraps ALL admin controllers:
```php
Route::middleware(['auth', 'suspended', 'role:super-admin'])->group(function () {
```

#### Within that group (lines 229-315):
| Controller | Routes protected by `role:super-admin` |
|---|---|
| **FeatureController** | `create`, `store`, `edit`, `update`, `destroy` (lines 229-233) |
| **ModuleController** | `create`, `store`, `edit`, `update`, `destroy` (lines 235-239) |
| **ModulePermissionController** | `index`, `update`, `destroy` (lines 244-246) |
| **ImportController** | `create`, `store` (lines 274-275) |
| **RoleController** | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `attachPrivilege`, `detachPrivilege` (lines 282-290) |
| **RoleTemplateController** | `index`, `show`, `apply` (lines 292-294) |
| **PrivilegeController** | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` (lines 296-302) |
| **SmtpProfileController** | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `test`, `setDefault`, `toggleActive`, `duplicate` (lines 304-314) |

### Middleware registration: `vendor/hasinhayder/tyro/src/Providers/TyroServiceProvider.php`
```php
// Line 116
$router->aliasMiddleware('role', EnsureTyroRole::class);
```

### Middleware implementation: `vendor/hasinhayder/tyro/src/Http/Middleware/EnsureTyroRole.php`
```php
// Lines 11-34
class EnsureTyroRole {
    public function handle(Request $request, Closure $next, string ...$roles) {
        $user = $request->user();
        if (! $user) {
            throw new AuthorizationException('This action is unauthorized.');
        }
        // ...
        foreach ($required as $role) {
            if (! $this->matchesRole($ownedRoles, $role)) {
                throw new AuthorizationException('ACCESS DENIED.');
            }
        }
        return $next($request);
    }
}
```

### Note: Feature & Module read routes ARE unprotected (separate concern)
Two routes are in the `auth + suspended` group (NOT `role:super-admin`):
```php
// Lines 64-65 (web.php)
Route::get('/features', [FeatureController::class, 'index'])->name('features.index');
Route::get('/features/{id}', [FeatureController::class, 'show'])->name('features.show')->whereNumber('id');
// Lines 67-68
Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
Route::get('/modules/{id}', [ModuleController::class, 'show'])->name('modules.show')->whereNumber('id');
```
Any authenticated user can read features and modules — but this was NOT in the alleged finding scope (the claim was about "create/update/delete/apply/import").

---

## CONCLUSION

**FALSE POSITIVE.** All create, store, edit, update, destroy, apply, and import routes for all 8 controllers are behind `role:super-admin` middleware. No non-super-admin can access them.
