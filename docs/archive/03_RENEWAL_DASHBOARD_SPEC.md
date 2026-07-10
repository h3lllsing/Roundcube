# ENTERPRISE FEATURE SPECIFICATION

## Feature: Renewal Dashboard

Version: 1.0
Status: DRAFT
Priority: P3

---

## 1. Purpose

### Why does this feature exist?

IT Operators and Procurement process renewals by visiting a 3-page triangle: Expiry Tracker list → Service detail → Provider detail → back to Expiry Tracker. Each renewal decision requires information spread across 3 disconnected pages. No inline context means 15-50 minutes/week is spent navigating instead of deciding.

### Which business problem does it solve?

Renewal processing time per item: 3-5 minutes → 1-2 minutes. Missed renewals (forgotten until service expires) cause service outages. Cost data is invisible during renewal decisions (user must navigate to provider to see pricing).

### What happens if it does not exist?

Weekly renewal processing continues taking 15-50 minutes. Missed renewals (estimated 1-3/year per organization) cause service outages requiring emergency recovery (30 min-4 hours each). Cost optimization is blind — Procurement cannot see "what are we spending per vendor" without manual spreadsheet compilation.

---

## 2. Business Value

### Hours saved

- 10-30 min/week for 10 IT Operators = 1.5-5 hours/week saved
- 2-4 hours/month for 3 Procurement = 0.5-1 hour/week saved
- Total: 2-6 hours/week organization-wide

### Risk reduced

- Missed renewal → service outage: 1-3/year → target 0/year
- Cost oversight: blind vendor spend → vendor visibility
- Expired service credential exposure: services that expire with active credentials → reduced

### Errors reduced

- Renewing a cancelled service: currently possible (no inline "is this still needed?" context)
- Overlooking a better-priced alternative: possible with blind renewal decisions
- Entering incorrect renewal date: possible when hopping between pages

### Compliance impact

- Service renewal tracking is an operational concern, not compliance-driven. No direct compliance impact.

### Security impact

- Indirect: expired services with active credentials are a security gap. Dashboard reduces missed expirations.

### Operational impact

- IT Operator weekly planning: renewal review goes from 30 min → 5 min
- Procurement monthly cost review: data is already visible without manual compilation
- IT Manager budget review: renewal forecasts become accessible

---

## 3. Actors

### Exactly which personas use it?

| Persona | Access | Rationale |
|---------|--------|-----------|
| IT Operator (10 users) | Full | Primary — weekly renewal processing |
| Procurement (3 users) | Full | Primary — monthly cost review |
| IT Manager (5 users) | Full | Secondary — budget planning |
| IT Director (1 user) | Read-only | Tertiary — strategic cost overview |
| Super Admin (3 users) | Full | Secondary — system-wide view |

### Who must never see it?

| Persona | Block reason |
|---------|-------------|
| End User (450 users) | No business need for renewal/cost data |
| Service Desk (20 users) | No business need |
| Security Officer (3 users) | No operational need (unless security-related renewal, handled via user module visibility) |

---

## 4. Permissions

### Exactly which permission checks occur?

**Check 1: Can view the Renewal Dashboard page**
- Module: `expiry_trackers` (slug: `expiry-trackers`)
- Action: `can_read`
- Runtime: controller
- If FAIL: 403. User cannot access the dashboard at all.

**Check 2: Can view each row's service name**
- Module: varies per row (hostings, domains, vps, voip, other_services)
- Action: `can_read`
- Runtime: query filter (pre-filter)
- If FAIL: row is EXCLUDED from the dashboard. User sees only expiry trackers for modules they can read.

**Check 3: Can view cost data**
- Module: service-specific module (same as Check 2)
- Action: `can_read`
- Runtime: Inline display — if user can see the row, they see the cost. Cost is part of the service data, not a separate permission.
- There is NO separate `can_view_cost` permission. Cost visibility is tied to module `can_read`.

**Check 4: Can execute Renew action**
- Module: service-specific module (hostings, domains, vps, voip, other_services)
- Action: `can_update`
- Runtime: Livewire component action
- If FAIL: "Renew" button is disabled. User sees "View" link instead.

### Which modules are checked?

1. `expiry_trackers` — dashboard entry gate
2. `hostings` — per-row filter
3. `domains` — per-row filter
4. `vps` — per-row filter
5. `voip` — per-row filter
6. `other_services` — per-row filter

### Which actions?

- `can_read` on expiry_trackers (page access)
- `can_read` on each service module (row visibility)
- `can_update` on each service module (renew action)

### Which permission has highest priority?

`can_read` on `expiry_trackers` is the GATE. If user cannot read expiry_trackers, dashboard is inaccessible. `can_read` on individual service modules determines row visibility. `can_update` determines actionability.

### Trace runtime

```
1. HTTP request → GET /expiry-trackers (renewals dashboard)
2. ROUTE → ExpiryTrackerController@dashboard
3. CONTROLLER
   → abort_unless(canOnModule('expiry-trackers', 'read'), 403)
   → $accessibleModules = auth()->user()->getAccessibleModuleIds('read')
   → $trackers = ExpiryTracker::with('trackable')
       ->whereIn('module_id', $accessibleModules)
       ->where('status', 'active')
       ->orderBy('expiry_date')
       ->get()
   → $trackers->loadMorph('trackable', [
       Hosting::class => ['serviceProvider'],
       Domain::class => ['serviceProvider'],
       Vps::class => ['serviceProvider'],
       Voip::class => ['serviceProvider'],
       OtherService::class => ['serviceProvider'],
     ])
4. VIEW: expiry-trackers/dashboard.blade.php
   → Renders table. Each row:
     → Service name (from trackable)
     → Provider name (from trackable->serviceProvider)
     → Expiry date
     → Cost
     → Status
     → Renew button (if can_update on that service module)
```

---

## 5. Business Rules

- R1: Dashboard replaces the existing expiry tracker list view at `/expiry-trackers`.
- R2: Dashboard shows ONLY expiry trackers where `status = 'active'`.
- R3: Dashboard excludes rows where the current user lacks `can_read` on the service's module.
- R4: Inline service name comes from `$tracker->trackable->name` (polymorphic).
- R5: Inline provider name comes from `$tracker->trackable->serviceProvider->name` (nested relation).
- R6: Inline cost comes from `$tracker->cost` OR `$tracker->trackable->cost` — the ExpiryTracker's own `cost` field takes priority. If NULL, fallback to trackable's `cost`. If both NULL, show "—".
- R7: "Renew" button extends `expiry_date` by 1 year (365 days).
- R8: "Renew" button REQUIRES confirmation: "Renew [service name] for another year at [cost]?"
- R9: Confirmed renewal updates both `expiry_trackers.renewal_date` and `expiry_trackers.expiry_date`.
- R10: Renewed state is logged to activity_log with event = `renewal_processed`.
- R11: Table is sortable by: expiry_date (default ASC), cost (DESC), service name (ASC), provider name (ASC).
- R12: Table is filterable by: month (dropdown: This Month, Next Month, Next Quarter, All), status (Active, Expiring), provider (multi-select).
- R13: Table shows aggregate row at bottom: "Total: $X,XXX" for visible filtered rows only.
- R14: Aggregate total shows "(based on visible records)" note if user does not have access to all modules.
- R15: "Export CSV" button exports the currently filtered view with all inlined columns.
- R16: Dashboard auto-refreshes on page reload only. No polling.

---

## 6. Data Used

### Exactly which models

| Model | Table | Usage |
|-------|-------|-------|
| App\Models\ExpiryTracker | `expiry_trackers` | Primary. Read `name`, `expiry_date`, `renewal_date`, `cost`, `status`, `module_id`, `user_id`. Polymorphic `trackable`. |
| App\Models\Hosting | `hostings` | Polymorphic trackable target. Read `name`, `cost`, `service_provider_id`. |
| App\Models\Domain | `domains` | Polymorphic trackable target. Read `name`, `cost`, `service_provider_id`. |
| App\Models\Vps | `vps` | Polymorphic trackable target. Read `name`, `cost`, `service_provider_id`. |
| App\Models\Voip | `voip` | Polymorphic trackable target. Read `name`, `cost`, `service_provider_id`. |
| App\Models\OtherService | `other_services` | Polymorphic trackable target. Read `name`, `cost`, `service_provider_id`. |
| App\Models\ServiceProvider | `service_providers` | Nested via trackable. Read `name`. |

### Exactly which tables

1. `expiry_trackers`
2. `hostings`
3. `domains`
4. `vps`
5. `voip`
6. `other_services`
7. `service_providers`

### Exactly which relationships

| Source | Relationship | Target |
|--------|-------------|--------|
| ExpiryTracker | `trackable(): MorphTo` | Hosting, Domain, VPS, VoIP, or OtherService |
| ExpiryTracker | `serviceProvider(): BelongsTo` | ServiceProvider (direct FK `service_provider_id`) |
| Hosting | `serviceProvider(): BelongsTo` | ServiceProvider |
| Domain | `serviceProvider(): BelongsTo` | ServiceProvider |
| VPS | `serviceProvider(): BelongsTo` | ServiceProvider |
| VoIP | `serviceProvider(): BelongsTo` | ServiceProvider |
| OtherService | `serviceProvider(): BelongsTo` | ServiceProvider |

The morphMap (from AppServiceProvider):
```php
'hosting' => Hosting::class,
'domain' => Domain::class,
'vps' => Vps::class,
'voip' => Voip::class,
'other_service' => OtherService::class,
```

### Exactly which queries

**Query 1: Fetch expiry trackers with eager loaded polymorphic relations**
```php
$trackers = ExpiryTracker::with('trackable')
    ->whereIn('module_id', $accessibleModuleIds)
    ->where('status', 'active')
    ->orderBy('expiry_date')
    ->get();
```

**Query 2: Eager load nested provider relations per morph type**
```php
$trackers->loadMorph('trackable', [
    Hosting::class => ['serviceProvider'],
    Domain::class => ['serviceProvider'],
    Vps::class => ['serviceProvider'],
    Voip::class => ['serviceProvider'],
    OtherService::class => ['serviceProvider'],
]);
```

Without `loadMorph`: 1 (expiry_trackers) + N (trackable, one per row) + N (serviceProvider, one per row) = 1 + 2N queries.
With `loadMorph`: 1 (expiry_trackers) + 5 (one per morph type for trackable) + 5 (one per morph type for serviceProvider) = 11 queries maximum.

**Query 3: Renew action**
```php
DB::transaction(function () use ($tracker) {
    $tracker->update([
        'renewal_date' => now(),
        'expiry_date' => $tracker->expiry_date->addYear(),
    ]);
    activity()->causedBy(auth()->user())
        ->performedOn($tracker->trackable)
        ->event('renewal_processed')
        ->log("Renewal processed for {$tracker->name}");
});
```

### No guesses

All columns and relationships verified from migration files and model files.

---

## 7. UI Contract

### Exactly what appears

A full-page table view with columns: Service Name, Provider, Expires, Cost, Status, Action.

Header row with filter controls: month dropdown, status dropdown, provider multi-select, search box.

Footer row with aggregate: "Total: $X,XXX".

Export CSV button.

### Exactly where

Replaces the current expiry tracker index at `/expiry-trackers`. The existing route `GET /expiry-trackers` returns the dashboard instead of the current scrap table.

### Exactly when

Rendered when:
1. User has `can_read` on `expiry_trackers` module
2. There are active expiry trackers for modules the user can access

### Exactly when hidden

- Expiry tracker rows are hidden when they belong to a module the user cannot read.
- Entire page is hidden (403) when user lacks `can_read` on `expiry_trackers` module.

### Exactly when disabled

- "Renew" button is disabled (gray, not clickable) when:
  - User lacks `can_update` on the service's module
  - Expiry date is more than 60 days away (too early to renew)
  - Service is marked as `status` = 'cancelled' or 'expired'
- "Export CSV" button is disabled when there are 0 visible rows.

### No redesign

Uses existing table styling from the design system. The current expiry tracker list uses a table — the dashboard is an evolved version of that same table with additional columns.

---

## 8. Runtime Flow

```
USER: Navigates to /expiry-trackers

ROUTE: GET /expiry-trackers → ExpiryTrackerController@dashboard

CONTROLLER: ExpiryTrackerController@dashboard
  → canOnModule('expiry-trackers', 'read') — gate
  → getAccessibleModuleIds('read') — module scope
  → ExpiryTracker::with('trackable')->whereIn('module_id', $accessibleModuleIds)
  → loadMorph('trackable', [...])

SERVICE: (optional — can be in controller for MVP)
  → ExpiryTrackerService@getDashboardData($accessibleModuleIds)
  → Returns collection with aggregate total

PERMISSION: Module filter applied at query level (rows excluded).
  → Renew button permission checked at view level per row.

MODEL: ExpiryTracker → MorphTo → Hosting/Domain/VPS/VoIP/OtherService
  → BelongsTo → ServiceProvider

DATABASE:
  → 1 query: SELECT * FROM expiry_trackers WHERE module_id IN (...) AND status = 'active'
  → 1-5 queries: SELECT * FROM hostings WHERE id IN (...) (per morph type)
  → 1-5 queries: SELECT * FROM service_providers WHERE id IN (...) (per morph type)

RESPONSE:
  → Inline table rendered with all data
  → User can sort, filter, and click Renew
```

---

## 9. Edge Cases

### Soft delete

- ExpiryTracker soft-deleted: excluded by default (SoftDeletes trait). Does not appear. CORRECT.
- Service (trackable) soft-deleted: `$tracker->trackable` returns null. Inline service name shows "Deleted service". Provider shows "—". Cost shows "—". Status shows "Orphaned". **NEEDS REVIEW:** Should orphaned trackers be visible? Decision: show with "Orphaned" status so users can clean up.

### Null

- `expiry_trackers.cost` is NULL: use trackable's cost. If both NULL, show "—".
- `trackable->serviceProvider` is NULL: provider column shows "—".
- `expiry_date` is NULL (unlikely but nullable): do not include in default sort. Show "No date".
- `renewal_date` is NULL: show "Never renewed".

### Missing provider

- Service has no `service_provider_id` (nullable FK): provider column shows "—".
- ServiceProvider is soft-deleted: `$tracker->trackable->serviceProvider` returns null (SoftDeletes filters by default). Provider column shows "—".

### Permission denied

- User loses `can_read` on `expiry_trackers` between page load and action: Livewire renew action checks permission again. If denied, return error.
- User loses `can_read` on a service module: that row is excluded on next page load. Current page remains stale until refresh. Acceptable.

### Concurrent edit

- Two users click "Renew" on the same tracker simultaneously: DB transaction prevents double-renewal. Expiry date extends by 1 year from current value, not from the date. Winner's transaction commits, loser's transaction fails or overwrites. To prevent: use `DB::transaction` with `refresh()` before update.

### Deleted user

- User who created the expiry tracker is deleted: no impact. Dashboard queries by user permission on modules, not by creator.

### Polymorphic relation edge cases

- `trackable_type` contains a class string not in the morphMap: `loadMorph` skips unknown types. Row appears but trackable is null. Show "Unknown type".

### Empty state

- No active expiry trackers: show "No renewals due. Everything is up to date." with a link to create a new expiry tracker (if user has `can_create`).

### Aggregation edge cases

- All visible rows have NULL cost: aggregate shows "Total: — (cost data not available)".
- Some visible rows have NULL cost: aggregate totals only non-null costs. Show "Total: $X,XXX (N records with cost data)".
- More than 100 rows visible: aggregate is still accurate (computed in PHP from loaded collection).

---

## 10. Security

### Attack vectors

**AV1: Cost data leakage via aggregate row**
- Vector: User with partial module access sees aggregate total that includes costs from modules they shouldn't see
- Mitigation: Aggregate totals only include visible (permission-filtered) rows. Total shows "(based on X visible records)".
- Severity: LOW — aggregate does not leak individual record costs

**AV2: Renew action without update permission**
- Vector: User sends direct POST to renew endpoint without UI button
- Mitigation: Livewire action re-checks `can_update` on the service module. Server-enforced.
- Severity: LOW — permission check is server-side

**AV3: CSV export leaks data**
- Vector: User exports CSV for visible rows, then shares file with unauthorized users
- Mitigation: CSV export includes only visible rows (same permission filter as table). No additional mitigation — this is a data handling policy concern.
- Severity: LOW — same risk as any export feature

### Privilege escalation

**PE1: User sees cost for module they cannot read**
- Guard: `whereIn('module_id', $accessibleModuleIds)` filters at query level
- Result: User cannot see rows from unreadable modules. Privilege escalation prevented.

### Data leakage

**DL1: Row count reveals module activity**
- Number of rows for a module the user CANNOT read is unknowable (those rows are excluded, not shown as "N hidden items"). The user cannot deduce how many expiry trackers exist in modules they can't see.

**DL2: "Orphaned" status reveals module activity**
- An "Orphaned" row (trackable soft-deleted) reveals that an expiry tracker exists for a deleted service. The user can see the tracker but not the deleted service. Mitigation: if trackable is null, DO NOT show "Deleted: [name]". Show "Service no longer available". Do not reveal the name.

### Audit logging

- Renew action MUST be logged with: `causer` = current user, `subject` = trackable service, `event` = `renewal_processed`, `properties` = `['tracker_id' => $tracker->id, 'new_expiry_date' => $newDate, 'cost' => $cost]`.

### Rate limiting

- Renew action: 30/minute per user (same rate limit pattern as password reveal).

### Sensitive actions

- Renew: MODERATE sensitivity. Extends service lifecycle and commits cost. Confirmation required.

### Abuse scenarios

- **Bulk renew to commit unauthorized spending:** Rate limit (30/min) prevents mass renew. Each renew is audited with cost.

---

## 11. Performance Budget

### Expected queries

- Page load: 1 (expiry_trackers) + up to 5 (morph types) + up to 5 (providers) = 11 queries max
- Filter/sort: 0 additional queries (collection operations on loaded data)
- Renew action: 1 (UPDATE) + 1 (INSERT activity) = 2 queries

### Expected response time

- Page load: < 500ms with 200 active trackers
- Renew action: < 200ms

### Expected memory

- Collection of 200 ExpiryTrackers with loaded morph+provider: ~5-10MB

### Maximum dataset

- Expected: 200-500 active expiry trackers
- Maximum: 2000 active trackers (unlikely — would imply 2000+ services)
- With 2000 trackers: page load may exceed 2 seconds. Mitigation: paginate to 100 per page.

### Scaling concerns

- Polymorphic `loadMorph` with 2000 trackers will load all 2000 records into memory. Beyond 2000, pagination is required.
- The `expiry_date` index on `expiry_trackers` ensures the initial ORDER BY is fast.

---

## 12. Acceptance Criteria

| # | Criterion | Expected Result | Pass/Fail |
|---|-----------|----------------|-----------|
| AC1 | User with expiry_trackers.can_read sees dashboard | Table renders with rows | PASS / FAIL |
| AC2 | User without expiry_trackers.can_read sees 403 | HTTP 403 returned | PASS / FAIL |
| AC3 | Dashboard shows only active status trackers | No inactive/expired trackers visible | PASS / FAIL |
| AC4 | Dashboard excludes rows for inaccessible modules | User sees only permitted modules | PASS / FAIL |
| AC5 | Row shows service name from polymorphic trackable | Correct service name inlined | PASS / FAIL |
| AC6 | Row shows provider name from nested relationship | Correct provider name inlined | PASS / FAIL |
| AC7 | Row shows cost from expiry_tracker, falls back to trackable | Cost displayed correctly for both cases | PASS / FAIL |
| AC8 | "Renew" button extends expiry by 1 year | Expiry date advanced by 365 days | PASS / FAIL |
| AC9 | Renew requires confirmation | Confirmation dialog shown before update | PASS / FAIL |
| AC10 | Renew action logged in activity_log | Activity entry created | PASS / FAIL |
| AC11 | User without can_update sees disabled Renew button | Button gray, not clickable | PASS / FAIL |
| AC12 | Aggregate total matches sum of visible costs | Total matches manual calculation | PASS / FAIL |
| AC13 | Aggregate total shows note for partial visibility | "(based on visible records)" appended | PASS / FAIL |
| AC14 | Empty state shows helpful message | "No renewals due" message | PASS / FAIL |
| AC15 | Export CSV contains same rows as visible table | CSV content matches table | PASS / FAIL |
| AC16 | Sort by expiry_date ASC works | Order matches ascending dates | PASS / FAIL |
| AC17 | Filter by month works | Only trackers expiring in selected month shown | PASS / FAIL |
| AC18 | Dashboard page loads in < 500ms with 200 trackers | Measured response time < 500ms | PASS / FAIL |

---

## 13. Regression Checklist

| # | Behavior | How verified |
|---|----------|-------------|
| R1 | Expiry Tracker CRUD operations | Existing ExpiryTrackerController tests |
| R2 | Polymorphic morph mapping for all 8 types | Existing morph resolution tests |
| R3 | Expiry date accessor (reads from trackable) | Existing ExpiryTracker model test |
| R4 | Soft delete cascade behavior | Existing soft delete tests |
| R5 | Module permission gating for all user roles | Existing permission tests |
| R6 | Existing expiry tracker list route | Route still resolves (same URL) |
| R7 | Expiry notification cron job | Existing cron test — dashboard is read-only, no impact on notifications |

---

## 14. Testing

### Unit

| Test | What it validates |
|------|------------------|
| ExpiryTrackerTest@test_cost_fallback | cost falls back to trackable when own cost is null |
| ExpiryTrackerTest@test_renewal_updates_expiry | Renew action extends expiry by 365 days |
| ExpiryTrackerTest@test_renewal_logs_activity | Activity entry created with correct data |

### Feature

| Test | What it validates |
|------|------------------|
| RenewalDashboardTest@test_dashboard_renders | Full table rendered with correct rows |
| RenewalDashboardTest@test_module_permission_filter | Rows excluded for unreadable modules |
| RenewalDashboardTest@test_renew_without_permission | Renew button disabled |
| RenewalDashboardTest@test_empty_state | Dashboard with 0 trackers shows message |

### Browser

| Test | What it validates |
|------|------------------|
| RenewalDashboardBrowserTest@test_renew_confirmation | Confirmation dialog appears, confirms, table updates |
| RenewalDashboardBrowserTest@test_sort_interaction | Click column header → rows reorder |
| RenewalDashboardBrowserTest@test_csv_export_download | CSV file downloaded with correct content |

### Permission

| Test | What it validates |
|------|------------------|
| PermissionTest@test_dashboard_gate | User without expiry_trackers.read: 403 |
| PermissionTest@test_row_filter_by_module | Rows excluded for unreadable modules |

### Performance

| Test | What it validates |
|------|------------------|
| PerformanceTest@test_dashboard_200_trackers_under_500ms | Response time < 500ms |
| PerformanceTest@test_dashboard_query_count_under_11 | Query count <= 11 |

### Regression

| Test | What it validates |
|------|------------------|
| Existing ExpiryTrackerTest@test_crud | CRUD operations unchanged |

### Security

| Test | What it validates |
|------|------------------|
| SecurityTest@test_orphaned_name_hidden | Deleted service name NOT shown in dashboard |
| SecurityTest@test_renew_csrf | Renew requires valid Livewire token |

---

## 15. Rollback

### How this feature can be disabled

**Method 1: Feature flag**
- `FEATURE_RENEWAL_DASHBOARD=false` → revert to old expiry tracker list view
- Guard: `@if(config('features.renewal-dashboard'))` in route or controller

**Method 2: Route swap**
- Keep old index route as fallback: `GET /expiry-trackers/legacy`
- If dashboard breaks, switch route back in one deploy

**Method 3: View revert**
- Revert `expiry-trackers/index.blade.php` to previous version

### Rollback order

1. Set `FEATURE_RENEWAL_DASHBOARD=false` (zero downtime)
2. Old expiry tracker list renders (no data loss)
3. Verify granular permissions still work

### Rollback verification

- Expiry trackers list renders without inline columns (original behavior)
- All data intact. No mutations performed by dashboard.

---

## 16. Future Evolution

### v1.2

- Cost trend sparkline (month-over-month cost change per service)
- "Exclude from renewals" toggle per row
- Bulk renew: select multiple, renew all at once
- Renewal notes: add inline notes per tracker
- 7-day and 30-day forecast widgets for dashboard

### v2

- Cost forecasting: ML-based prediction of monthly cost based on renewal patterns
- Vendor consolidation analysis: "You have 3 hosting providers — could consolidate to save 20%"
- Contract term comparison: side-by-side vendor pricing view
- Automated negotiation reminders: "Service X has been renewed 5 times at the same price. Consider negotiating."
- Spend analytics dashboard: cost by category, vendor, department, with drill-down
- Budget alerts: notification when projected spend exceeds monthly budget
