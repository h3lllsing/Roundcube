# PRE-DEPLOYMENT BLOCKER VALIDATION

> **Files validated:** 5 allegations across 25+ source files
> **Verdict:** 2 BLOCK RELEASE | 3 FALSE POSITIVE

---

| # | Finding | Verdict | Block Release? |
|---|---------|---------|----------------|
| 1 | Web admin controllers missing authorization | **FALSE POSITIVE** | No |
| 2 | DatabaseSeeder production demo-data risk | **PROVEN — BLOCKER** | **YES** |
| 3 | Multiple-role permission conflict | **FALSE POSITIVE** | No |
| 4 | API show/update/destroy inconsistency | **PROVEN — BLOCKER** | **YES** |
| 5 | Null module_id ghost record risk | **PROVEN — BLOCKER** | **YES** |

## BLOCKING FINDINGS

### Finding 2: DatabaseSeeder seeds demo data in production
- `DatabaseSeeder.php:33` guards only `testing`, not `production`
- Running `php artisan migrate --seed` on a production server silently creates demo admin accounts, hosting entries, domains, VPS records, VoIP lines, etc.
- **Risk: HIGH** — Production data contamination, security exposure

### Finding 4: API show/update/destroy uses user_id not module_id
- `DomainController::show()`, `update()`, `destroy()` check `$domain->user_id !== $user->id`
- But `index()` checks `getAccessibleModuleIds('read')` for module-scoped visibility
- A user who has `can_read` on a module (but not ownership of a record) can see the record in `index()` via module scoping, then gets 403 on `show()` because `user_id` doesn't match
- **Risk: MEDIUM** — User-visible inconsistency; records appear in list but return 403 on detail

### Finding 5: Super-admin creates records with null module_id when slug is missing
- All 10 Web CRUD controllers silently allow `module_id = null` when `Module::where('slug', $this->moduleSlug())->first()` returns null
- Super-admin bypasses the `abort_unless` guard, and the `if ($module)` conditional leaves `module_id` unset
- **Risk: MEDIUM** — Orphan records invisible to module-scoped queries, dashboard, and export

## FALSE POSITIVE FINDINGS

### Finding 1: Controllers ARE protected by route middleware
- All 8 controllers' create/update/delete/apply/import routes are wrapped in `middleware(['auth', 'suspended', 'role:super-admin'])` in `routes/web.php:228-315`
- The `role:super-admin` middleware is registered by `hasinhayder/tyro` (see `TyroServiceProvider.php:116`) and checks `EnsureTyroRole` which validates the user has a `super-admin` role slug
- FeatureController::index/show and ModuleController::index/show are readable by any authenticated user — but this was NOT in the alleged finding scope

### Finding 3: canOnModule uses exists(), not first()
- `HasModulePermissions::canOnModule()` at line 34-37 uses `->exists()` with `whereIn('role_id', ...)` — correct OR semantics across all roles
- `getEffectiveModulePermissions()` uses `->first()` but this is display/debug only, not used for authorization decisions

---

## SUMMARY

**2 proven blockers** warrant a BLOCK RELEASE recommendation collectively.

However, Finding 4 (API inconsistency) was already documented as WONTFIX for v1.0 in prior Phase 4 reports. The user must decide if this is truly blocking or an accepted limitation.

Finding 5 (null module_id) applies only to super-admin operations — regular users cannot bypass it. Production environments typically restrict `--seed` and super-admin access.
