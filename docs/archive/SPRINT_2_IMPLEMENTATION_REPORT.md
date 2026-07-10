# Sprint 2 Implementation Report — Renewal Dashboard

## Files Modified

### 1. `routes/web.php` — New Route (line 194)
```php
Route::post('/expiry-trackers/{expiry_tracker}/renew', [ExpiryTrackerController::class, 'renew'])->name('expiry-trackers.renew');
```
- Added after existing custom routes for consistency
- Minimal route — no middleware config needed (inherits from route group)

### 2. `app/Http/Controllers/Web/ExpiryTrackerController.php`
#### `index()` changes:
| Change | Reason |
|--------|--------|
| `with('module', 'trackable')` → `with('module', 'serviceProvider')` | Removed redundant `trackable` eager-loading (now uses `loadMorph`); added `serviceProvider` to fix real N+1 |
| Added `$totalCost = (clone $query)->sum('cost')` before pagination | Sums cost of all RBAC-filtered records (not just current page) |
| Added `service_provider_id` to column select | Required for `serviceProvider` BelongsTo FK |
| Added `$trackers->loadMorph('trackable', [...])` after pagination | Eager-loads polymorphic trackable per morph type without N+1 |
| Passes `$totalCost` to view | Needed by aggregate card |

#### New `renew(int $id)` method:
- Loads tracker with `->with('trackable')`
- **Permission check**: `canOnModule($tracker->module, 'update')` — checks service module (same as existing edit/update)
- **Date logic**: `expiry_date + 1 year` (or `now + 1 year` if null)
- **Sync**: Updates original service's `expiry_date` via `forceFill()` (linked only); updates tracker's `expiry_date` and `renewal_date`
- **Activity log**: `event('renewal_processed')` with properties: `new_expiry_date`, `renewal_date`, `trackable_type`, `trackable_id`

### 3. `resources/views/components/action.blade.php` — New Icon
- Added `'refresh'` SVG (circular arrow) to `$icons[]` array

### 4. `resources/views/expiry-trackers/index.blade.php`
#### Aggregate Dashboard Cards (before filters):
```blade
Total Cost (Visible) — sum of all RBAC-filtered records
Total Records — paginated total count
```
- Grid layout: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`
- Styled consistently with existing dashboard components

#### Renew Button (in Actions column):
- Condition: `status !== 'cancelled'` AND user has `can_update` on service module
- Uses `x-action` component with `color="emerald"`, `icon="refresh"`, `confirm` prompt
- POST method to `expiry-trackers.renew` route

## What Was NOT Changed
- **Filters**: All existing filters preserved (search, status, sync_type, source_type, date range)
- **Export**: Unchanged
- **Bulk actions**: Unchanged
- **RBAC scope**: Unchanged
- **Column structure**: All existing columns preserved; Renew button is additive
- **No migrations**: Zero schema changes

## Summary
| File | Change Type | Lines Changed |
|------|------------|---------------|
| `routes/web.php` | +1 line | 1 |
| `ExpiryTrackerController.php` | Modified `index()` + new `renew()` | ~25 |
| `action.blade.php` | +1 icon | 1 |
| `index.blade.php` | +dashboard cards + Renew button | ~18 |
