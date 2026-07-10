# BUSINESS RULES — Required Documentation

## Status: MUST DOCUMENT BEFORE RELEASE

### BR-01: Super-admin Bypasses All Permission Checks
| Field | Value |
|-------|-------|
| Rule | Super-admin users (`slug = 'super-admin'`) bypass ALL module permission checks. Every authorization call checks `$user->hasRole('super-admin')` first. |
| Location | 40+ code locations |
| Evidence | Pattern: `$user->hasRole('super-admin') \|\| $user->canOnModule(...)` |
| Exception | Route-level `role:super-admin` middleware (separate from inline checks) |
| Must Document Before | **YES** — Release |

### BR-02: Global Records Are Module-Scoped, Not User-Owned
| Field | Value |
|-------|-------|
| Rule | Business records (domains, hostings, VPS, VoIP, etc.) are visible based on module-level permissions (`can_read`, `can_create`, etc.), NOT based on `user_id` ownership. |
| Location | API `index()` methods, Web `index()` methods, Dashboard, Export |
| Evidence | Phase 4 changed all API `index()` to use `getAccessibleModuleIds('read')` |
| Exception | Web `show()/edit()/update()/destroy()` still use `userOwnedFilter()` for backward compatibility |
| Must Document Before | **YES** — Release |

### BR-03: Web CRUD Uses Inline Permission Checks
| Field | Value |
|-------|-------|
| Rule | Web controllers check permissions inline using `abort_unless($user->canOnModule($module, 'create'), 403)` rather than route middleware. |
| Location | All 10 Web CRUD controllers |
| Evidence | Pattern: `$module = Module::where('slug', $this->moduleSlug())->first(); abort_unless($module && $user->canOnModule($module, 'create'), 403);` |
| Exception | Admin controllers (Role, Privilege, etc.) use `role:super-admin` route middleware instead |
| Must Document Before | **YES** — Release |

### BR-04: API CRUD Has Mixed Scoping
| Field | Value |
|-------|-------|
| Rule | API `index()` is module-scoped; API `show()/update()/destroy()` are user_id-scoped. |
| Location | 9 API controllers |
| Evidence | `DomainController::index()` line 45-48 vs `DomainController::show()` line 105-107 |
| Exception | None |
| Must Document Before | **YES** — Release |

### BR-05: Demo Data Must Never Exist in Production
| Field | Value |
|-------|-------|
| Rule | DemoDataSeeder must never execute in the production environment. |
| Location | `database/seeders/DatabaseSeeder.php:33` |
| Evidence | Current guard `!app()->environment('testing')` is insufficient. |
| Exception | None |
| Must Document Before | **YES** — Release (and fix in code) |

### BR-06: `--seed` is For Local Development Only
| Field | Value |
|-------|-------|
| Rule | The `php artisan migrate --seed` command must never be executed on production. Production deployments use `php artisan migrate --force` only. |
| Location | Multiple deployment docs |
| Evidence | `README.md:45`, `INSTALLATION.md:43,148`, `CONTRIBUTING.md:10` all contradict this rule |
| Exception | None |
| Must Document Before | **YES** — Release (and fix docs) |

### BR-07: Module Slugs Are Immutable Code Constants
| Field | Value |
|-------|-------|
| Rule | Module slugs must never be changed or deleted. They are referenced as hardcoded strings in 18+ code locations. |
| Location | 10 controllers, 8+ services, 15+ Blade templates, 10+ test files |
| Evidence | 18+ independent hardcoded references to the same 11 slugs |
| Exception | Slug changes require code changes in all 18+ locations |
| Must Document Before | **YES** — Release as KNOWN LIMITATION |

### BR-08: user_id Is Creator Metadata, Not Ownership
| Field | Value |
|-------|-------|
| Rule | The `user_id` field on business records represents "who created this record," NOT "who owns this record" for permission purposes. |
| Location | 9 models' `$fillable`, 10 controllers' `store()` |
| Evidence | `$validated['user_id'] = Auth::id()` — always set to current user. Never accepted from request input. |
| Exception | API `show()/update()/destroy()` uses `user_id` for authorization (violation of this rule, documented as inconsistency) |
| Must Document Before | **YES** — Release |

### BR-09: Permissions Are Merged Across Roles (OR Semantics)
| Field | Value |
|-------|-------|
| Rule | If a user has multiple roles, permission for a given action on a given module is GRANTED if ANY role grants it. |
| Location | `app/Traits/HasModulePermissions.php:34-37` |
| Evidence | `ModuleRolePermission::whereIn('role_id', ...)->where($column, true)->exists()` |
| Exception | User-level overrides can explicitly deny (set to false) which overrides role grant |
| Must Document Before | **YES** — Release |

### BR-10: User-Level Overrides Override Role Permissions
| Field | Value |
|-------|-------|
| Rule | `UserModulePermission` can override role-based permissions, both positively (grant) and negatively (deny). |
| Location | `app/Traits/HasModulePermissions.php:26-32` |
| Evidence | `$userOverride->$column !== null` → return override value (true or false) |
| Exception | None |
| Must Document Before | **YES** — Release |

### BR-11: Module Must Resolve for Record Creation
| Field | Value |
|-------|-------|
| Rule | A record must always have a valid `module_id` on creation. If the module slug does not resolve to a database record, the creation should fail. |
| Location | 10 Web controllers' `store()` |
| Evidence | Current code allows null `module_id` when slug doesn't resolve (super-admin bypass). This is a bug. |
| Exception | The code currently violates its own rule — `firstOrFail` is the correct implementation |
| Must Document Before | **YES** — Release (as known limitation) |

### BR-12: Controller moduleSlug() Must Match DB Slug
| Field | Value |
|-------|-------|
| Rule | The hardcoded string returned by each controller's `moduleSlug()` method must exactly match a `modules.slug` value in the database. |
| Location | 10 Web controllers |
| Evidence | Each controller has a unique `moduleSlug()` returning a literal string |
| Exception | If mismatch, system silently degrades |
| Must Document Before | **YES** — Release |

### BR-13: API v1.0 Has No External Consumers
| Field | Value |
|-------|-------|
| Rule | The API layer exists as a published contract for future consumers. In v1.0, no mobile app, external integration, or third-party client consumes it. |
| Location | Full codebase audit |
| Evidence | Only `/api/search` is called by frontend. Zero API CRUD endpoints consumed. |
| Exception | API token management is available for future use |
| Must Document Before | **YES** — Release |

### BR-14: Web Controllers Are the Only UI
| Field | Value |
|-------|-------|
| Rule | All end-user interactions go through Web controllers (Blade-rendered HTML). The API controllers are not rendered in the UI. |
| Location | Full codebase audit |
| Evidence | All CRUD operations use `routes/web.php`, Blade forms, and Web controllers |
| Exception | `/api/search` for command palette, `/api/tokens` for token management |
| Must Document Before | **YES** — Release |

### BR-15: Super-Admin Role Literal Is Hardcoded Throughout
| Field | Value |
|-------|-------|
| Rule | The slug `'super-admin'` is referenced as a hardcoded string literal in 40+ code locations. Changing the role name would break authorization. |
| Location | 40+ code locations |
| Evidence | Pattern: `$user->hasRole('super-admin')`, `'role:super-admin'`, `$role->slug === 'super-admin'` |
| Exception | Role name `'super-admin'` must not be changed |
| Must Document Before | **YES** — Release |

---

## Summary

### Before Release (Must Document):
- BR-01 through BR-15 all documented

### Before Release (Must Fix):
- BR-05: DemoDataSeeder guard (code fix)
- BR-06: Deployment docs (docs fix)

### v1.1 (Must Fix):
- BR-04: API scoping alignment
- BR-07: Module slug immutability
- BR-08: user_id → created_by migration
- BR-11: firstOrFail pattern
- BR-15: Super-admin literal → config/constant

### Wontfix:
- (none of the above is wontfix — all are valid architecture concerns)
