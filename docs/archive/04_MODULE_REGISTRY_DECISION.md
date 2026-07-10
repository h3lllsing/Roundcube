# MODULE REGISTRY DECISION — Configuration Entity vs Business Entity

## Architecture Classification

**Current state:** Module is BOTH a configuration entity AND a business entity, sharing a single model, single CRUD interface, and mutable fields.

**Target state:** Module contains a configuration sub-component (slug, is_active, controller binding) that is IMMUTABLE at runtime, and a business sub-component (feature_id, name, description) that is MUTABLE.

---

## Evidence of Dual Role

### Configuration Role (code depends on it)

| Dependency | Files Affected | Slug Value | Nature |
|-----------|----------------|------------|--------|
| `moduleSlug()` methods | 10 Web controllers | Hardcoded string | Code constant |
| `ExportController::$types` | 2 files | `module_slug` key | Array key |
| `SidebarComposer::moduleSlugMap` | 1 file | Array key | Permission lookup |
| `GlobalSearchService` | 1 file | Array key + slug field | Search configuration |
| `BulkActionService::operationalTypes` | 1 file | Array value | Bulk operation filter |
| `RenewalSyncService` | 1 file | `$service->getTable()` | Auto-resolve |
| `CalendarController` | 2 files | Array key | Calendar filter |
| `ImportController` | 2 files | Array key | Import type map |
| `MonitorController` | 2 files | Array key | Health check filter |
| `DashboardController` | 1 file | Array key | Dashboard aggregation |
| `ReportController` | 1 file | Array key | Report filter |
| `HelpService` | 1 file | Array key | Help content map |
| Blade templates | 15+ files | Hardcoded | Display routing |
| Test files | 10+ files | Hardcoded | Test fixtures |
| DB migrations | 13 tables | `module_id` FK | Referential integrity |

**TOTAL: 18+ separate locations, each duplicating the same string constants**

### Business Role (user can modify)

| Field | Current Constraint | Can User Change? |
|-------|-------------------|------------------|
| `name` | None | Yes — in `$fillable` |
| `slug` | None | Yes — in `$fillable` |
| `feature_id` | `nullable` FK to features | Yes — in `$fillable` |
| `description` | None | Yes — in `$fillable` |
| `is_active` | boolean cast | Yes — in `$fillable` |
| Record | None | YES — `ModuleController::destroy()` can delete |

---

## Risk Analysis: Slug Mutation

If a super-admin changes slug `domains` → `my-domains`:

```
1. Web\DomainController::moduleSlug() returns 'domains'
2. Module::where('slug', 'domains')->first() returns NULL
3. $module is null → canOnModule(null, 'create') → PHP fatal error (null->id)
4. $validated['module_id'] is never set → record saved with null module_id
5. Sidebar: no permission found → menu item hidden
6. GlobalSearch: no slug match → no results
7. Export: no slug match → "no data"
8. Calendar: no slug match → no events
9. Dashboard: no slug match → zero counts
10. RenewalSyncService: table name doesn't match slug → fails silently
```

**No error is reported anywhere.** The system silently degrades.

---

## Decision for v1.0

| Action | Decision | Rationale |
|--------|----------|-----------|
| Block slug changes? | **NO — document only** | Requires schema + policy changes. v1.1. |
| Block module deletion? | **NO — document only** | Should check for active records. v1.1. |
| Warn in UI? | **YES — high priority** | Module edit/create form should warn about slug dependency. |
| Document limitation? | **YES — before release** | BUSINESS_RULES.md: "Module slugs must never be changed." |

---

## Decision for v1.1

### Architecture: Module Slug Registry

Replace the 18+ hardcoded string arrays with a single source of truth:

```php
// Option A: Backed Enum
enum ModuleSlug: string
{
    case DOMAINS = 'domains';
    case HOSTINGS = 'hostings';
    case VPS = 'vps';
    case VOIP = 'voip';
    case SERVICE_PROVIDERS = 'service-providers';
    case DOMAIN_EMAILS = 'domain-emails';
    case OTHER_SERVICES = 'other-services';
    case EXPIRY_TRACKERS = 'expiry-trackers';
    case ASSETS = 'assets';
    case TASKS = 'tasks';
    case VAULT = 'vault';

    public function label(): string { ... }
    public function tableName(): string { return str_replace('-', '_', $this->value); }
}

// Option B: Config Array
// config/modules.php
return [
    'slugs' => [
        'domains' => ['table' => 'domains', 'controller' => DomainController::class, ...],
        'hostings' => ['table' => 'hostings', 'controller' => HostingController::class, ...],
        ...
    ],
];
```

### Implementation steps (v1.1):

1. Create `ModuleSlug` enum or config
2. Replace `moduleSlug()` in all 10 controllers with `ModuleSlug::DOMAINS->value`
3. Replace all 18+ hardcoded arrays with single `ModuleSlug::cases()` iteration
4. Remove `slug` from `Module::$fillable`
5. Add `ModulePolicy` to deny slug changes and deletion
6. Add `NOT NULL` + FK constraint on `module_id` in all 13 business tables
7. Replace `Module::where('slug', ...)` with `Module::where('slug', ModuleSlug::DOMAINS->value)` everywhere
8. Register the enum values as read-only module identifiers

---

## Key Principle

> A thing referenced by code constants should not be casually editable by runtime users.

Module slugs are code constants. The Module CRUD interface should expose only:
- `name` (editable: display label)
- `feature_id` (editable: categorization)
- `description` (editable: notes)
- `is_active` (editable: soft toggle)

The slug should be a read-only field set at seeding time and never changed.
