# BUSINESS RULES — OpsPilot v1.0

> These rules govern the architecture and behavior of the OpsPilot system.
> They are NOT implementation instructions — they are design decisions that future code must respect.
>
> **Status:** Established at v1.0 release. Updated as architecture evolves.

---

## BR-01: Super-Admin Bypasses All Permission Checks

**Rule:** Users with a role slug of `'super-admin'` bypass ALL module-level permission checks. Every authorization gate must check `$user->hasRole('super-admin')` first and permit access if true.

**Justification:** Super-admin has full system access. This avoids requiring explicit permission rows for every module for admin users. It is intentional that super-admin cannot be restricted by module permissions — if restriction is needed, do not assign the super-admin role.

**Applies to:** All authorization gates (controllers, services, middleware, views).

**Status:** Valid as-of v1.0.

---

## BR-02: Global Records Are Module-Scoped, Not User-Owned

**Rule:** Business records (domains, hostings, VPS, VoIP, service providers, domain emails, other services, expiry trackers, assets) are visible based on module-level permissions (`can_read`, `can_create`, etc.), NOT based on `user_id` ownership. The `user_id` field identifies the record creator, not the record owner for permission purposes.

**Justification:** Multiple users within an organization (or team) may need to manage the same global infrastructure records. Module-scoped permissions enable role-based access to entity types, independent of individual ownership.

**Exception:** API `show()/update()/destroy()` methods currently use `user_id` for authorization (ownership model). This is a known inconsistency scheduled for v1.1 alignment.

**Applies to:** List operations (index), dashboard, exports, search, sidebar visibility.

**Status:** Valid as-of v1.0. Partial (list aligned, detail not aligned).

---

## BR-03: Web CRUD Uses Inline Permission Checks

**Rule:** Web controllers perform authorization via inline `abort_unless($user->canOnModule($module, 'create'), 403)` checks rather than route middleware. This is by design for Web CRUD controllers that serve both super-admin and non-super-admin users.

**Justification:** Web CRUD routes live in the `auth + suspended` middleware group (not `role:super-admin`), so they must check permissions at the controller level. This allows non-super-admin users with module-level `can_create`/`can_update`/`can_delete` to perform CRUD operations.

**Comparison:** Admin controllers (Role, Privilege, Module, Feature, Import, etc.) use `role:super-admin` route middleware because only super-admins should ever access them.

**Applies to:** All Web\*Controller CRUD methods.

**Status:** Valid as-of v1.0.

---

## BR-04: API CRUD Has Mixed Scoping

**Rule:** API `index()` operations are scoped by module access (`getAccessibleModuleIds('read')`). API `show()/update()/destroy()` operations are scoped by `user_id` ownership (record belongs to the requesting user).

**Justification:** Phase 4 aligned API `index()` with Web visibility (module-scoped). `show()/update()/destroy()` were intentionally left on user_id ownership as a v1.0 conservative choice and documented WONTFIX for v1.0.

**Impact:** A user who can see records in the API list may receive 403 when accessing individual record details via the API if they did not create the record. This inconsistency is visible to external API consumers using Sanctum tokens.

**Resolution:** Align to module-scoped access in v1.1. The Web UI is NOT affected because it uses Web controllers exclusively.

**Applies to:** All Api\*Controller CRUD methods (9 controllers).

**Status:** Known limitation as-of v1.0. Scheduled for v1.1.

---

## BR-05: Demo Data Must Never Exist in Production

**Rule:** The `DemoDataSeeder` must never execute in the production environment. It creates demo admin accounts, business records with known passwords, and vault entries with demo credentials.

**Enforcement:** `database/seeders/DatabaseSeeder.php` line 33 checks `!app()->environment('testing', 'production')` before calling `DemoDataSeeder`.

**Justification:** Security (demo admin account), data integrity (demo records mixed with real data), compliance (uncontrolled data creation).

**Exception:** None.

**Applies to:** DatabaseSeeder, DemoDataSeeder, deployment commands.

**Status:** Enforced in code as of v1.0 pre-release fix.

---

## BR-06: `--seed` is for Local Development Only

**Rule:** The `php artisan migrate --seed` command must never be executed on a production database. Production deployments use `php artisan migrate --force` exclusively.

**Justification:** `--seed` executes DemoDataSeeder which creates demo data. Even with the code guard, the deployment process should not depend solely on code protection.

**Enforcement:** All production deployment documentation has been updated to remove `--seed` references. The `composer.json` `setup` script correctly uses `migrate --force` without `--seed`.

**Exception:** A production-safe seeder may be created separately if initial data is required for production setup. This would be a new seeder class that explicitly does not include demo data.

**Applies to:** All deployment documentation, deployment scripts, CI/CD configuration.

**Status:** Enforced in docs as of v1.0 pre-release fix.

---

## BR-07: Module Slugs Are Immutable Code Constants

**Rule:** Module slugs (e.g., `'domains'`, `'hostings'`, `'vps'`) must never be changed or deleted after initial setup. They are referenced as hardcoded strings in 18+ code locations across controllers, services, views, and tests. Changing a slug causes silent system degradation across multiple subsystems.

**Justification:** Slugs serve as the coupling point between the database module registry and the codebase's understanding of module types. They appear in URL routing, export configuration, search configuration, sidebar composition, calendar filtering, monitor health checks, import type mapping, bulk operations, and renewal sync — all as hardcoded strings.

**Impact of violation:** If a slug is changed, modules silently fail to resolve. Records are created with null module_id. Permissions stop working. Exports return empty. Sidebar hides modules. No error is produced.

**Exception:** A `ModuleSlug` enum or registry service is planned for v1.1 which will formalize this immutability at the code level.

**Applies to:** Module CRUD operations, all interfaces referencing module slugs.

**Status:** Known limitation as-of v1.0. Scheduled for v1.1 enforcement.

---

## BR-08: `user_id` is Creator Metadata, Not Ownership

**Rule:** The `user_id` field on business records identifies who created the record. It is NOT an authorization boundary and must never be accepted from user input. Every store method sets `$validated['user_id'] = Auth::id()` unconditionally.

**Justification:** Records are shared resources within a team or organization. Ownership-by-creator would prevent legitimate cross-user collaboration. The correct authorization boundary is module-level permission (can_read/can_create/can_update/can_delete).

**Exception:** API `show()/update()/destroy()` currently use `user_id` for authorization — this is scheduling for v1.1 alignment.

**Long-term:** A future migration may rename `user_id` to `created_by` to clarify semantics, but this requires coordinated schema change across 13+ tables.

**Applies to:** All business models (Domain, Hosting, Vps, Voip, etc.), all store/update methods.

**Status:** Valid as-of v1.0 (with noted API exception).

---

## BR-09: Permissions Are Merged Across Roles (OR Semantics)

**Rule:** If a user has multiple roles, permission for a given action on a given module is GRANTED if ANY role grants it. The `canOnModule()` method uses `exists()` with `whereIn('role_id', ...)`, which returns true as soon as one matching row is found.

**Justification:** OR semantics are more permissive and simplify role management. A user assigned both an "editor" role (can_read on all modules) and an "admin" role (can_create on hostings) will have both read and create access — the union of both roles' permissions.

**Implementation:** `HasModulePermissions::canOnModule()` — `ModuleRolePermission::whereIn('role_id', $roleIds)->where($column, true)->exists()`

**Impact:** Denying a permission requires explicit user-level override (UserModulePermission) — removing from one role is not sufficient if another role still grants it.

**Applies to:** All authorization checks using `canOnModule()`.

**Status:** Valid as-of v1.0.

---

## BR-10: User-Level Overrides Override Role Permissions

**Rule:** `UserModulePermission` records can override role-based permissions, both positively (grant) and negatively (deny). The override takes precedence regardless of what the user's roles grant or deny.

**Justification:** Provides fine-grained access control for individual users without creating custom roles. A user can be blocked from a specific action on a specific module without affecting other users in the same role.

**Implementation:** `HasModulePermissions::canOnModule()` checks UserModulePermission first. If non-null, the override value is returned immediately without consulting role permissions.

**Impact:** Auditing permission denials must check both role permissions AND user overrides.

**Applies to:** All authorization checks using `canOnModule()`.

**Status:** Valid as-of v1.0.

---

## BR-11: A Record Must Always Have a Valid Module ID

**Rule:** Every business record must reference a valid `module_id` at creation time. Records with null `module_id` are invisible to module-scoped queries, exports, dashboards, and search.

**Justification:** The module association is the fundamental scoping mechanism for all data access. A record without a module cannot be surfaced through any standard UI flow.

**Current limitation:** Web controllers' `store()` methods use `if ($module) { $validated['module_id'] = $module->id; }` which silently allows null module_id when the slug doesn't resolve (super-admin only). The correct implementation — `firstOrFail()` — is scheduled for v1.1.

**Enforcement targets:** `module_id` should be NOT NULL with a foreign key constraint on all business tables. This is scheduled for v1.1 (requires data cleanup first).

**Applies to:** All business models, all store/update methods, database schema.

**Status:** Known limitation as-of v1.0. Scheduled for v1.1 enforcement.

---

## BR-12: Controller `moduleSlug()` Must Match DB Slug

**Rule:** The hardcoded string returned by each Web controller's `moduleSlug()` method must exactly match the `slug` column of a corresponding `modules` database record. A mismatch causes the controller to silently fail to locate its module.

**Justification:** The `moduleSlug()` method is the implicit contract between the codebase and the database. It is defined 10 times (once per controller) as a hardcoded string literal. Changing a module slug in the database without updating these methods breaks the system.

**Applies to:** All Web\*Controller classes that define `moduleSlug()`.

**Status:** Valid as-of v1.0 (technical debt tracked for enum consolidation in v1.1).

---

## BR-13: API v1.0 Has No External Consumers

**Rule:** As of v1.0, no mobile app, external integration, or third-party client consumes the API. The only frontend API call is `GET /api/search` (command palette). All CRUD operations use Web controllers exclusively.

**Justification:** The API layer exists as a published contract for future consumers. Known inconsistencies (BR-04) do not affect any current user. The API is being released as a v1.0 preview.

**Impact:** API breaking changes in v1.1 may be made without deprecation because no consumers exist. This should be re-evaluated when the first external integration is connected.

**Applies to:** All API routes, API controllers, Sanctum token management.

**Status:** Valid as-of v1.0. Must be reassessed when first API consumer connects.

---

## BR-14: Web Controllers Are the Only User Interface

**Rule:** All end-user interactions go through Web controllers (Blade-rendered HTML forms). The API layer is not rendered in the UI. Users never directly call API CRUD endpoints.

**Justification:** The system is a traditional server-rendered web application. There is no SPA, no mobile app, no API-driven frontend framework. The Web layer is the sole UI.

**Impact:** Authorization bugs in API controllers do not affect users. Permission checks in Web controllers are the only path that matters for user experience.

**Applies to:** All routes, Blade templates, JavaScript, API surface area.

**Status:** Valid as-of v1.0.

---

## BR-15: `'super-admin'` Role Slug Is Hardcoded

**Rule:** The string literal `'super-admin'` is referenced in 40+ code locations. The super-admin role slug must never be changed because it is the single point of authorization bypass for the entire system.

**Justification:** The `'super-admin'` slug is hardcoded in: route middleware (`role:super-admin`), inline authorization checks (`$user->hasRole('super-admin')`), role protection logic (`$role->slug === 'super-admin'`), seeder logic, and test fixtures. Changing this slug would break every authorization gate in the system.

**Applies to:** All authorization middleware, controller checks, policies, seeders, tests.

**Status:** Known limitation as-of v1.0. A future improvement may replace the literal with a configuration constant or enum.
