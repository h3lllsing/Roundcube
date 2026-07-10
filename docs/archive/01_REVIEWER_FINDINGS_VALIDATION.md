# ARCHITECTURE REVIEW BOARD — FINDING VALIDATION

## F1 — Seeder Guard Is A Real Pre-Release Fix

**FINDING:**
DatabaseSeeder currently allows DemoDataSeeder in production because it only excludes testing.

**WHEN does this happen?**
When `php artisan migrate --seed` or `php artisan db:seed --class=DatabaseSeeder --force` runs on a production server.

**WHERE in code?**
`database/seeders/DatabaseSeeder.php:33`
```php
if (! app()->environment('testing')) {
    $this->call(DemoDataSeeder::class);
}
```

**WHY does it happen?**
The guard uses a blocklist pattern (deny only `testing`) instead of an allowlist pattern (allow only `local`, `staging`). Every environment except `testing` passes through — including `production`.

**WHAT business rule is involved?**
Business rule: "Demo data should never exist in production." This rule is currently UNWRITTEN — it exists only as a partial code guard that accidentally excludes only the testing environment.

**WHAT breaks if ignored?**
- Demo admin account `admin@tyro.project` created in production with known password
- Demo records (hosting, VPS, domains, VoIP, vault entries with passwords) mixed with real data
- `RolePermissionSeeder` runs first and creates role-permission rows for ALL modules (line 41: `foreach ($modules as $module)`) — this overwrites any production permission configuration
- `FeatureModuleSeeder` creates feature/module records that may conflict with production configuration
- Demo data appears in production dashboards, reports, exports

**WHO is affected?**
Production deployment team, production users, production data integrity, auditors.

**HOW likely is it?**
MEDIUM. `composer.json` `setup` script deliberately omits `--seed`, but:
- `README.md:45`, `INSTALLATION.md:43,148`, `CONTRIBUTING.md:10` all instruct `migrate --seed`
- `CPANEL_DEPLOYMENT_GUIDE.md:267` uses `db:seed --class=DatabaseSeeder --force` which still triggers it
- A tired engineer copying from README will trigger this
- A new team member following Quick Start on a clone/Restore will trigger this

**HOW severe is it?**
HIGH. Production data contamination is difficult to clean. Demo accounts create security surface. Demo passwords are known. Vault entries with demo credentials persist.

**IS it release-blocking?**
**YES.** The code should protect itself. Defense-in-depth. Even if deployment SOP forbids `--seed`, the code should not depend on process compliance.

**WHAT is the smallest safe action?**
One-line change at `DatabaseSeeder.php:33`:
```php
// Change:
if (! app()->environment('testing')) {
// To:
if (! app()->environment('testing', 'production')) {
```

**WHAT is the long-term architectural action?**
Split `DatabaseSeeder` into `ProductionSafeSeeder` (FeatureModuleSeeder, RolePermissionSeeder, RoleTemplateSeeder) and `DemoDataSeeder`. The guard should be app-level config not environment string:
```php
if (config('app.demo_data_enabled', false)) {
    $this->call(DemoDataSeeder::class);
}
```

**CONFIDENCE: 100%**

---

## F2 — API/Web List vs Detail Consistency Must Be Verified, Not Assumed

**FINDING:**
API index is module-scoped, but API show/update/destroy is user_id-scoped.

**WHEN does this happen?**
When an API consumer (frontend, mobile app, external integration) calls both list and detail endpoints.

**WHERE in code?**
Every API CRUD controller follows this pattern. Example: `app/Http/Controllers/Api/DomainController.php`:
- `index()`: lines 45-48 — uses `getAccessibleModuleIds('read')` for module scoping
- `show()`: lines 101-107 — uses `$domain->user_id !== $user->id` for ownership scoping
- `update()`: lines 143-148 — same user_id check
- `destroy()`: lines 165-170 — same user_id check

Identical pattern in 8 other API controllers (Hosting, Vps, Voip, ServiceProvider, DomainEmail, OtherService, ExpiryTracker, Asset) — 27 total endpoints.

**WHY does it happen?**
Phase 4 changed `index()` to module-scoped for alignment with Web visibility. `show()/update()/destroy()` were intentionally left on user_id ownership — documented as WONTFIX for v1.0.

**WHAT business rule is involved?**
Business rule (hidden): "API list operations are scoped by module access; API detail/update/delete operations are scoped by record ownership." This rule is UNSPOKEN and INCONSISTENT.

**WHAT breaks if ignored?**
An API consumer that lists records then tries to view detail will get 403 for records visible in the list but not owned by the user.

**WHO is affected?**
External API consumers using Sanctum tokens.

**HOW likely is it?**
DEPENDS ON CONSUMPTION. Verified: **The frontend (Web UI) does NOT call any API CRUD endpoint**. The only frontend API call is `GET /api/search` (command palette). No mobile app, no external integration client code exists in the repository. See `03_API_CONSUMPTION_TRACE.md` for full evidence.

**HOW severe is it?**
LOW for v1.0 release — NO consumer is affected. MEDIUM as a technical debt — if an external integration is added later, it will encounter this immediately.

**IS it release-blocking?**
**NO.** Zero frontend consumption. This is a v1.1 technical debt item.

**WHAT is the smallest safe action?**
Document in the API docs: "GET /api/{resource}/{id} requires ownership (user_id). GET /api/{resource} requires module read permission. API v1.1 will align both to module-scoped."

**WHAT is the long-term architectural action?**
Align show/update/destroy to module-scoped access (same as index). The `user_id` check should become a secondary filter or removed entirely in favor of `canOnModule($record->module, 'read'/'update'/'delete')`.

**CONFIDENCE: 95%**

---

## F3 — Module Is Treated As Both Configuration Entity And Business Entity

**FINDING:**
Module slug is hardcoded in controllers and used for permissions, renewals, sidebar, search, export, and scoping. But ModuleController exposes module CRUD to any super-admin user.

**WHEN does this happen?**
Every time a super-admin user modifies a Module record's slug, or deletes a Module record that is referenced by hardcoded controller `moduleSlug()` methods.

**WHERE in code?**
- `app/Models/Module.php:22-28`: `$fillable` includes `'slug'` — slug is mass-assignable
- `routes/web.php:235-239`: ModuleController CRUD is behind only `role:super-admin` — no additional protection against slug changes or deletion
- 10 Web controllers each define `private function moduleSlug(): string` returning hardcoded slug values
- `app/Services/RenewalSyncService.php:21`: `Module::where('slug', $service->getTable())` — assumes table name = slug
- `app/Http/View/Composers/SidebarComposer.php:12-23`: `moduleSlugMap` with 10 hardcoded slugs
- `app/Services/GlobalSearchService.php:41-263`: 10+ hardcoded module slug entries
- `app/Services/BulkActionService.php:210`: `$operationalTypes` array of 8 hardcoded slugs
- 2 Export controllers, 2 Calendar controllers, 2 Import controllers, 2 Monitor controllers — each with duplicate hardcoded slug arrays
- 13 database tables with `module_id` FK referencing `modules.id`
- 15+ Blade templates referencing slugs by name

**WHY does it happen?**
The architecture did not distinguish between Module-as-configuration (slug, is_active, controller binding) and Module-as-categorization (feature_id, name, description). Both concerns share a single model and a single CRUD interface.

**WHAT business rule is involved?**
Business rules:
1. "Module slugs are configuration constants referenced by code" — UNWRITTEN
2. "Module records should not be deletable while referenced by business data" — UNWRITTEN
3. "Module slug changes require code updates in 18+ locations" — UNWRITTEN

**WHAT breaks if ignored?**
- A super-admin changes slug `'domains'` → `'my-domains'`:
  - All 10 Web controllers silently fail to find their module (null module_id)
  - Sidebar permissions stop working
  - Global search returns no results for that module
  - Export controller returns empty for that type
  - Calendar, import, monitor all silently skip that module
  - RenewalSyncService breaks
  - No error is thrown — system silently degrades

**WHO is affected?**
Any super-admin user who edits or deletes a Module record with no understanding of the code coupling. Any developer who adds a new module type without adding to all 18+ locations.

**HOW likely is it?**
LOW for v1.0 — super-admins are few and slug changes are rare. But the DAMAGE if it happens is HIGH because it's silent.

**HOW severe is it?**
MEDIUM-HIGH. Silent degradation across the entire application.

**IS it release-blocking?**
**NO.** This is an architecture debt, not a release blocker. But it must be documented as a KNOWN LIMITATION: "Module slugs must never be changed or deleted without corresponding code updates."

**WHAT is the smallest safe action?**
Add a `ModulePolicy` that prevents slug changes and deletion for modules with active records. Add a warning in the Module CRUD UI when slug is changed.

**WHAT is the long-term architectural action?**
Create a `ModuleSlug` backed enum or config constant registry. Remove `slug` from Module model's `$fillable`. Enforce immutability at database level (trigger or read-only column). Decouple the slug concept from the Module business entity. Replace the 18+ hardcoded string arrays with a single source of truth.

**CONFIDENCE: 100%**

---

## F4 — Silent Null module_id Is Bad Design

**FINDING:**
Controllers set module_id only if module slug resolves. If not, record can be saved with null module_id.

**WHEN does this happen?**
When `Module::where('slug', $this->moduleSlug())->first()` returns null AND the user is super-admin (who bypasses the abort).

**WHERE in code?**
All 10 Web CRUD controllers' `store()` methods follow this pattern. Example — `app/Http/Controllers/Web/DomainController.php:99-111`:
```php
$module = Module::where('slug', $this->moduleSlug())->first();
if (! $user->hasRole('super-admin')) {
    abort_unless($module && $user->canOnModule($module, 'create'), 403);
}
if ($module) {          // <--- null guard
    $validated['module_id'] = $module->id;
}                       // <--- no else: null module_id silently assigned
```

Same pattern in: HostingController:81-87, VpsController:87-93, VoipController:84-93, ServiceProviderController:83-89, DomainEmailController:77-83, OtherServiceController:85-91, ExpiryTrackerController:119-125, VaultController:107-113, AssetController:132-138.

**WHY does it happen?**
The code uses defensive programming (`if ($module)`) instead of fail-fast (`firstOrFail()`). The architect assumed "module slug will always resolve" and added the null guard as a safety net without considering the side effects of silence.

**WHAT business rule is involved?**
Business rule: "Every business record must belong to a module." — UNWRITTEN and UNENFORCED.

**WHAT breaks if ignored?**
- Records with `module_id = null` are invisible to module-scoped queries (dashboard, exports, search, sidebar)
- `getAccessibleModuleIds()` returns only module IDs — null records never appear in filtered lists
- `canOnModule()` fails with PHP error when called with null module
- Dashboard widgets omit these records
- Renewal notifications may miss these records
- Export lists exclude them

**WHO is affected?**
End users (records invisible), super-admin who created them (confusion), operators (orphan data).

**HOW likely is it?**
LOW in normal operation — slugs are hardcoded and stable. But:
- If a new module type is added but slug doesn't match standard pattern
- If a module is accidentally deleted from the database
- If the `moduleSlug()` method has a typo
- HIGH if triggered — complete data invisibility

**HOW severe is it?**
MEDIUM. Orphan records that cannot be found through normal UI flows. No error reported.

**IS it release-blocking?**
**PARTIALLY.** The risk is LOW in normal operation (slugs are stable), but the architectural pattern is wrong. Two-track recommendation:
- Release: LOW-RISK — can ship as known limitation if documented
- Architecture: MUST FIX in v1.1 — `firstOrFail` is correct enterprise pattern

**WHAT is the smallest safe action?**
Replace `if ($module) { $validated['module_id'] = $module->id; }` with:
```php
$validated['module_id'] = Module::where('slug', $this->moduleSlug())->firstOrFail()->id;
```
in all 10 Web controllers' `store()` methods. This removes the `$module` variable entirely and fails with a clear 500 error if the slug doesn't resolve.

**WHAT is the long-term architectural action?**
Make `module_id` NOT NULL with a foreign key constraint on all business tables. Then the database itself enforces the invariant. The `firstOrFail()` change prepares the code for this constraint.

**CONFIDENCE: 100%**

---

## F5 — user_id Is Still Semantically Confusing

**FINDING:**
`user_id` is in `$fillable`, assigned via `Auth::id()` in store(), and used for ownership checks in show/update/destroy. But business records are supposed to be module-scoped, not ownership-scoped.

**WHEN does this happen?**
Every create, read, update, delete operation interprets `user_id` differently depending on the context.

**WHERE in code?**
- **Fillable:** 9 models have `'user_id'` in `$fillable` (Domain, Hosting, Vps, Voip, ServiceProvider, DomainEmail, OtherService, ExpiryTracker, Asset)
- **Assignment:** All 10 Web controllers' `store()`: `$validated['user_id'] = Auth::id();`
- **Ownership check (API):** 27 API endpoints use `$record->user_id !== $user->id` for authorization
- **Ownership check (Web):** `userOwnedFilter()` in Web controllers adds `where('user_id', Auth::id())` for list queries
- **Display:** Multiple Blade views display `$record->user->name` or `$record->user_id`
- **NOT ownership:** API `index()` ignores `user_id` in favor of `getAccessibleModuleIds('read')` — module scoping

**WHY does it happen?**
Originally the system was user-owned (Phase 1-3). Phase 4 introduced module-scoped visibility but did not refactor `user_id` semantics. The field is now overloaded with three meanings:
1. **Creator metadata** — who created the record
2. **Ownership gate** — who can see/update/delete it
3. **Legacy artifact** — remains in fillable because it was removed and re-added (Phase 3B→3C restoration)

**WHAT business rule is involved?**
The REAL business rules are:
1. "Records are visible based on module permissions, not ownership" — EXISTS in code for list
2. "Records can be owned by a different user than the creator" — ALLOWED by architecture (Web edit views let super-admin select any user)
3. "user_id is creator metadata, not a permission boundary" — INTENDED but NOT IMPLEMENTED

**WHAT breaks if ignored?**
- A user with `can_read` on a module can see ALL records in that module's list (correct)
- But cannot see DETAIL of records they didn't create (API) — inconsistency
- A super-admin assigns record ownership to another user — that user still can't access it via API (unless `user_id` matches)
- New developers see `user_id` in `$fillable` and assume it can be set from request input — REGRESSION RISK

**WHO is affected?**
External API consumers, future developers, anyone reading the code.

**HOW likely is it?**
MEDIUM. The API inconsistency is visible if someone builds an external integration. Developer confusion is guaranteed (the field was removed from fillable twice already — it's a magnet for regression).

**HOW severe is it?**
MEDIUM. Not end-user visible in v1.0 (Web UI uses Web controllers). But architecturally confusing. Future changes to API consumption will break.

**IS it release-blocking?**
**NO.** Documented limitation. Fix in v1.1.

**WHAT is the smallest safe action?**
Document: `user_id` is creator-metadata only. Do not use for permission decisions. Do not accept from user input. The correct scope is module access.

**WHAT is the long-term architectural action?**
Rename `user_id` semantics to `created_by` in v2.0 schema migration. Until then, keep `user_id` in fillable but add a model mutator in each business model that warns on external assignment: `if ($value !== Auth::id()) { Log::warning(...) }`.

**CONFIDENCE: 95%**

---

## F6 — Business Rules Are Still Hidden Inside Code

**FINDING:**
Many business rules exist only in controllers/services, not in documentation.

**WHEN does this happen?**
Every time a developer reads the codebase to understand who can do what.

**WHERE in code?**
Distributed across ~50+ files — controllers, traits, services, middleware, routes.

**WHY does it happen?**
No business rules document was ever created. Architecture evolved organically through phases 1-4 without formalizing decisions.

**WHAT business rule is involved?**
See `05_BUSINESS_RULES_REQUIRED.md` for the complete list of 15 documented hidden rules.

**WHAT breaks if ignored?**
Future changes will violate implicit rules. The same Phase 4 regression (user_id removed from fillable) that took 10 test fixes to discover would happen again. Every new developer will rediscover the rules the hard way.

**WHO is affected?**
Every developer, reviewer, and operator working on this system.

**HOW likely is it?**
100%. Without written rules, tribal knowledge replaces architecture.

**HOW severe is it?**
MEDIUM. Not production-critical now, but guarantees future regression.

**IS it release-blocking?**
**NO.** But must be done BEFORE next development cycle starts. v1.0 can ship without it; v1.1 cannot start without it.

**WHAT is the smallest safe action?**
Create `BUSINESS_RULES.md` with 15 documented rules.

**WHAT is the long-term architectural action?**
None — documentation is the action. Maintain as living document updated with every architecture decision.

**CONFIDENCE: 100%**

---

## F7 — Do Not Mix Architecture Improvements With Release Blockers

**FINDING:**
Previous reports mixed actual production risks with future architecture cleanup.

**WHEN does this happen?**
In `PRE_DEPLOYMENT_BLOCKER_VALIDATION.md` and `FINAL_GO_NO_GO_RECOMMENDATION.md`.

**WHERE in code?**
Not applicable — this is a classification issue in reporting.

**WHY does it happen?**
The original analysis treated all risks equally without separating "will crash in production" from "should be better."

**WHAT business rule is involved?**
Business rule: "Release blockers must cause production failure. Architecture improvements prevent future failure." — this distinction was not maintained.

**WHAT breaks if ignored?**
Release is blocked by non-blocking items, causing unnecessary delay AND devaluing the real blockers.

**WHO is affected?**
Release manager, deployment team, executive decision makers.

**HOW likely is it?**
100% — it already happened in prior reporting.

**HOW severe is it?**
LOW — information error, not production error.

**IS it release-blocking?**
**NO.** But the correction (this file) is required to make a correct release decision.

**WHAT is the smallest safe action?**
Apply the strict classification in `02_RELEASE_BLOCKER_SPLIT.md`.

**WHAT is the long-term architectural action?**
Adopt a standard risk classification framework (e.g., probability × severity matrix) for all future architecture reviews.

**CONFIDENCE: 100%**

---

## F8 — Deployment SOP Must Match Code Reality

**FINDING:**
Deployment documentation contradicts itself and the code.

**WHEN does this happen?**
When any deployment guide is followed for production.

**WHERE in code/documentation?**
- `README.md:45`: `migrate --seed`
- `INSTALLATION.md:43,148`: `migrate --seed`
- `CONTRIBUTING.md:10`: `migrate --seed`
- `CPANEL_DEPLOYMENT_GUIDE.md:267`: `db:seed --class=DatabaseSeeder --force` (still triggers DemoDataSeeder)
- `PRODUCTION_CONFIGURATION_GUIDE.md:259,264-265`: `migrate --force` + `db:seed --class=DatabaseSeeder --force`
- `composer.json` `setup` script: `migrate --force` (no seed — CORRECT)
- `ARCHITECTURAL_ASSUMPTIONS.md:477,489`: Flags this as DANGEROUS
- `PRE_DEPLOYMENT_BLOCKER_VALIDATION.md:20`: Confirms risk

**WHY does it happen?**
Documentation was written at different times by different people. `composer.json` was written by someone who knew better. README was written for local development. cPanel guide blindly followed old patterns.

**WHAT business rule is involved?**
Business rule: "Production databases must never receive demo or test data." — WRITTEN in architecture reports but NOT in deployment docs.

**WHAT breaks if ignored?**
Production data contamination.

**WHO is affected?**
Deployment team.

**HOW likely is it?**
MEDIUM-HIGH. Multiple documents explicitly say `--seed`. The safe path (`composer.json setup`) is not the documented path.

**HOW severe is it?**
HIGH.

**IS it release-blocking?**
**YES**, combined with F1. The two issues reinforce each other: code doesn't protect itself AND docs tell operators to trigger it.

**WHAT is the smallest safe action?**
Fix the code (F1) AND update ALL deployment documentation to remove `--seed`. Production command should be:
```bash
php artisan migrate --force
```

**WHAT is the long-term architectural action?**
Split seeders into `DevelopmentSeeder` and `ProductionSeeder`. Only `ProductionSeeder` is safe to run on production.

**CONFIDENCE: 100%**
