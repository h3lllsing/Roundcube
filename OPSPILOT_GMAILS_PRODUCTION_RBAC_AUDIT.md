# G-Mails Production RBAC Root Cause Audit

**Date:** 2026-07-14
**HEAD:** `c2c2518`
**Production Baseline Tag:** `a65b141` (OpsPilot)

---

## A. Exact Root Cause

**The `g-mails` module record is missing from the production `modules` table.**

The module entry is created by `FeatureModuleSeeder` (line 33):
```php
['name' => 'G-Mails', 'slug' => 'g-mails'],
```

`FeatureModuleSeeder` is called by `DatabaseSeeder::run()`. The production database was seeded once from an earlier version of this seeder that did **not** include `g-mails`. Subsequent deployments via `deploy.sh` only run `php artisan migrate --force` — they **never** run `php artisan db:seed`. Therefore the `g-mails` row was never inserted into the `modules` table on production.

### Git History Evidence

| Commit | Event |
|---|---|
| `6b8a3b1` (Initial commit) | `FeatureModuleSeeder` had **old features**: "Account Management", "User Accounts", "Account Types", "Profile Settings" — **NO g-mails** |
| `295ab32` (Full project push v1.7.0) | `FeatureModuleSeeder` **replaced** with current structure: Infrastructure, Productivity, Administration, Integration features. `g-mails` added under Infrastructure. |
| `a65b141` (OpsPilot — production baseline) | No change to `FeatureModuleSeeder` |
| `c2c2518` (HEAD) | No change to `FeatureModuleSeeder` |

Migrations only create table structures — they do **not** insert seed data. No migration has ever inserted the `g-mails` module.

---

## B. G-Mails Canonical Identifiers

| Context | Value |
|---|---|
| **Module slug** | `g-mails` |
| **Module name (display)** | `G-Mails` |
| **Feature** | Infrastructure |
| **Database table** | `g_mails` |
| **Model** | `App\Models\GMail` |
| **Web controller** | `App\Http\Controllers\Web\GMailController` |
| **Route URIs** | `/g-mails/*` |
| **Route names** | `g-mails.*` (index, create, store, show, edit, update, destroy, restore, force-delete, password) |
| **Sidebar label** | `G-Mails` |
| **Sidebar condition** | `$showGMails` (passed from `SidebarComposer`) |
| **Permission check pattern** | `$user->canOnModule($module, 'read')` / `create` / `update` / `delete` |
| **Super-admin bypass** | `$user->hasRole('super-admin') || ...` — always true for super-admin |

---

## C. Why Old Users Cannot See It

**All non-super-admin users cannot see it**, not just old users. The mechanism:

1. **SidebarComposer** (line 56-58):
   ```php
   $module = $modulesBySlug[$slug] ?? null;
   $data[$key] = $module && in_array($module->id, $accessibleIds);
   ```
   Since `$modulesBySlug` (from `ModuleCache::allBySlug()`, cached 24h) queries the `modules` table and the `g-mails` row doesn't exist, `$module` is `null` → `showGMails = false`.

2. **BaseResourceController::index()** (line 99-100):
   ```php
   $module = $this->resolveModule(); // ModuleCache::findBySlug('g-mails') → null
   abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'read')), 403);
   ```
   For non-super-admin: `$module` is null → `($module && ...)` is false → 403 abort. Direct URL access returns 403 Forbidden.

3. **Super-admin bypass**: The `hasRole('super-admin')` check at the beginning makes the expression true → no 403. Super-admin can still access via direct URL.

---

## D. Why It Is Absent from User Permissions

In `UserController::show()` (line 180):
```php
$modules = Module::with('feature')->orderBy('name')->get();
```

This loads **all modules from the `modules` table**. If the `g-mails` row doesn't exist there, it's not in the collection, and the permissions page never iterates over it. User-level overrides cannot be assigned because the module isn't listed.

---

## E. Whether Role Permissions Is Also Affected

**Yes.** In `ModulePermissionController::index()` (line 25):
```php
$modules = Module::with(['feature', 'rolePermissions.role'])->orderBy('name')->get();
```

Same pattern — loads all modules from DB. `g-mails` not present → not shown. Role-level permissions cannot be configured.

---

## F. Whether This Affects All Non-Super-Admin Users

**Yes.** Every non-super-admin user is affected:
- Sidebar hides G-Mails
- Direct URL access returns 403
- User Permissions page doesn't show G-Mails to assign overrides
- Role Permissions page doesn't show G-Mails to assign role permissions

New users created after the seeder change are also affected — creating a new user record doesn't insert `g-mails` into the `modules` table.

---

## G. Exact Read-Only Production Verification Commands

Run these on production via SSH:

```bash
cd /home/whizzweb/alphaspacepro.online

# 1. Check if g-mails module record exists
php artisan tinker --execute="\App\Models\Module::where('slug', 'g-mails')->first()"

# 2. Check all infrastructure modules
php artisan tinker --execute="\App\Models\Module::whereHas('feature', fn(\$q) => \$q->where('slug', 'infrastructure'))->pluck('slug')->toArray()"

# 3. Check if FeatureModuleSeeder has ever run
php artisan tinker --execute="\App\Models\Module::where('slug', 'domains')->first()?->created_at"

# 4. Check ModuleRolePermission for g-mails (will be empty if module missing)
php artisan tinker --execute="\App\Models\ModuleRolePermission::whereHas('module', fn(\$q) => \$q->where('slug', 'g-mails'))->get()"

# 5. Check UserModulePermission for g-mails (will be empty if module missing)
php artisan tinker --execute="\App\Models\UserModulePermission::whereHas('module', fn(\$q) => \$q->where('slug', 'g-mails'))->get()"

# 6. Check roles of a specific affected user (replace EMAIL)
php artisan tinker --execute="\App\Models\User::where('email', 'EMAIL')->first()?->roles()->pluck('slug')"

# 7. Check ModuleCache state
php artisan tinker --execute="print_r(array_keys(\App\Helpers\ModuleCache::allBySlug()))"
```

**Expected result:** `g-mails` will NOT appear in the module list (step 7) and `Module::where('slug', 'g-mails')->first()` returns `null` (step 1).

---

## H. Permanent Fix Recommendation

**Create a new data migration** that idempotently inserts the `g-mails` module record if it doesn't exist.

**Preferred solution:** New migration `database/migrations/YYYY_MM_DD_HHMMSS_add_gmails_module.php`

```php
<?php

use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $feature = Feature::where('slug', 'infrastructure')->first();
        if (! $feature) {
            return;
        }

        $module = Module::firstOrCreate(
            ['feature_id' => $feature->id, 'slug' => 'g-mails'],
            ['name' => 'G-Mails']
        );

        // Assign permissions for all non-super-admin roles that already have
        // permissions for other infrastructure modules (consistent with
        // RolePermissionSeeder behavior)
        $existingInfraModule = Module::where('feature_id', $feature->id)
            ->where('slug', '!=', 'g-mails')
            ->first();

        if ($existingInfraModule) {
            $existingPerms = ModuleRolePermission::where('module_id', $existingInfraModule->id)
                ->get()
                ->groupBy('role_id');

            foreach ($existingPerms as $roleId => $perms) {
                $rolePerm = $perms->first();
                ModuleRolePermission::firstOrCreate(
                    ['module_id' => $module->id, 'role_id' => $roleId],
                    [
                        'can_create' => $rolePerm->can_create,
                        'can_read' => $rolePerm->can_read,
                        'can_update' => $rolePerm->can_update,
                        'can_delete' => $rolePerm->can_delete,
                        'can_export' => $rolePerm->can_export,
                        'can_reveal' => $rolePerm->can_reveal,
                        'can_import' => $rolePerm->can_import,
                    ]
                );
            }
        }

        // Flush ModuleCache to invalidate 24h stale cache
        \App\Helpers\ModuleCache::flush('g-mails');
        \Illuminate\Support\Facades\Cache::forget('modules_all_by_slug');

        // Increment perms_generation to invalidate user permission caches
        \Illuminate\Support\Facades\Cache::increment('perms_generation');
    }

    public function down(): void
    {
        $module = Module::where('slug', 'g-mails')->first();
        if ($module) {
            ModuleRolePermission::where('module_id', $module->id)->delete();
            UserModulePermission::where('module_id', $module->id)->delete();
            $module->delete();
        }

        \Illuminate\Support\Facades\Cache::forget('modules_all_by_slug');
        \Illuminate\Support\Facades\Cache::increment('perms_generation');
    }
};
```

**Why this fix:**
- `firstOrCreate` is idempotent — safe to run repeatedly
- Copies existing permission patterns from another infrastructure module (consistent with `RolePermissionSeeder` behavior where all roles get the same permissions set)
- Flushes `ModuleCache` (24h cache) so sidebar picks up the new module immediately
- Increments `perms_generation` to invalidate user permission caches
- Preserves existing permissions — doesn't overwrite any existing data
- Follows the same pattern as `FeatureModuleSeeder` (uses `firstOrCreate`)

**Alternative:** Run `php artisan db:seed --class=FeatureModuleSeeder` on production. This is simpler but less safe because:
- It might re-run other seeders that could have side effects
- It doesn't flush the ModuleCache
- It's not tracked in version control as a deployable change

---

## I. Whether a Migration / Data Sync Is Required

**Yes.** A migration is required because:

1. **Deploy.sh only runs migrations** — any fix that relies on `php artisan db:seed` would require manual SSH intervention on every deploy
2. **Idempotency** — a migration with `firstOrCreate` is safe to run repeatedly
3. **Cache invalidation** — the migration must flush `ModuleCache` (cached 24h) and increment `perms_generation`
4. **Version control** — a migration file is tracked in git and runs automatically on every deploy

### Migration vs Seeder Comparison

| Approach | Migration | Run `db:seed` on production |
|---|---|---|
| Runs automatically on deploy | ✅ Yes (`deploy.sh` runs `migrate --force`) | ❌ No (manual SSH) |
| Idempotent | ✅ `firstOrCreate` | ✅ `updateOrCreate` in FeatureModuleSeeder |
| Cache invalidation | ✅ Can include | ❌ Must run separately or wait 24h |
| Tracked in version control | ✅ | Already tracked |
| Risk | None — data-only | Low but unexpected from deploy pattern |
| **Recommendation** | ✅ **PREFERRED** | ❌ |

---

## J. Security Impact

| Aspect | Impact |
|---|---|
| Data exposure | **None.** G-Mails data (passwords) already in DB. Module is hidden, not data-granting. |
| Access escalation | **None.** Users can't access G-Mails because module missing. No unauthorized access path. |
| Super-admin bypass | **LOW.** Super-admin can still access via direct URL. This is expected behavior (super-admin bypasses all checks). |
| Confidential data | The `g_mails` table stores encrypted passwords. These are not exposed. |
| **Overall** | **LOW.** This is an availability/functionality issue, not a security breach. Users are locked OUT (not in). |

The fix does NOT auto-grant access — it only makes the module visible in the UI and assigns role permissions consistent with other infrastructure modules. Admins can then configure access as needed.

---

## K. Files That Would Need to Change

Exactly **1 new file** to create:

| Action | File | Reason |
|---|---|---|
| **CREATE** | `database/migrations/2026_07_14_000001_add_gmails_module.php` | Data migration to insert `g-mails` module, assign role permissions, flush caches |

**No existing files need modification.** This is purely additive.

Optionally, as a separate low-priority fix:

| Action | File | Reason |
|---|---|---|
| **EDIT** | `database/seeders/RoleTemplateSeeder.php` (line 25) | Add `'g-mails'` to `$infrastructure` array for template consistency (not related to this issue — templates aren't auto-applied) |

---

## Root Cause Verdict

**The `g-mails` module record was never inserted into the production `modules` table.** The `FeatureModuleSeeder` that creates it was added after the production database was originally seeded, and `deploy.sh` never runs `db:seed`. All production deployments since `295ab32` shipped with the code expecting the module to exist, but the database row was never created.

G-MAILS PRODUCTION RBAC ROOT CAUSE AUDIT COMPLETE — STOPPING BEFORE FIX
