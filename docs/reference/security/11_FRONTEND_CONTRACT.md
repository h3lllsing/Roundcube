# 11. Frontend Contract: What Backend Expects

This is the definitive reference for what any new frontend (Blade replacement, Vue, React, etc.) must understand about what the backend provides and expects.

## 11.1 Data Format Expectations

### Dates
- Backend sends dates as **MySQL format: `Y-m-d`** (string) or **Carbon API** in Blade.
- Frontend must format for display (e.g., `d M Y` → `15 Mar 2025`).
- For expiry tracking: `toDateString()` comparison for "days remaining" calculation.

### Numbers (Cost/Fees)
- All monetary values are stored as **decimal** and sent as strings or floats.
- Frontend should format with currency symbol (RM — Malaysian Ringgit).
- No currency conversion — all costs stored in RM.

### Boolean Fields
- Database stores as `tinyint(1)` → Laravel casts to `boolean`.
- Blade checks: `@if($domain->is_cloudflare)` — true if non-zero.
- Hidden field convention: `<input type="hidden" name="field" value="0">` before checkbox.

### Nullable Fields
- Many foreign keys and optional fields are nullable.
- Frontend must handle `null` gracefully — display "—" or "Not set" instead of blank.
- Examples: `hosting_id` null → show "Not Linked" on domain page.
- `service_provider_id` null → show "—" on hosting page.

### Encrypted Fields (Passwords)
- NEVER plaintext in index/show views. Always masked: `********` or similar.
- Only available plaintext via explicit reveal AJAX call.
- Reveal response: `{ password: "decrypted_value" }`.

### Soft Deletes
- Controllers typically exclude trashed records from index.
- Show page: `Route::bind()` uses implicit route model binding → soft-deleted records 404.
- Some controllers forget `->withTrashed()` → soft-deleted parent records cause 404 on child show pages.

## 11.2 Backend Responses

### Standard Blade View Response
```php
return view('module.index', compact('records', 'filters'));
```
- Compact variables available in Blade.
- `$records` is a `LengthAwarePaginator` instance with ->links() for pagination.

### Pagination
- Default: 10 per page (`config('app.pagination_per_page')`).
- Paginator available in view: `$records->links()` renders pagination.
- Current page: `$records->currentPage()`.
- Total: `$records->total()`.

### Search
- Search query string: `?search=term`.
- Controller query: `->when($request->search, fn($q) => $q->where('name', 'like', "%{$search}%"))`.
- Some controllers search multiple columns (name + other fields).

### Redirects with Flash Messages
- Success: `return redirect()->route('domains.index')->with('success', 'Domain created successfully.')`.
- Error: `->with('error', 'Failed to create domain.')`.
- Flash messages displayed in Blade via `@if(session('success'))` block.

### AJAX Endpoints
| Endpoint | Method | Response | Purpose |
|---|---|---|---|
| `/vault/{vault}/reveal` | POST | `{ password: "..." }` | Reveal vault password |
| `/domains/{domain}/reveal-password` | POST | `{ password: "..." }` | Reveal domain password |
| `/attachments/{attachment}/download` | GET | File stream | Download attachment |
| `/expiry-trackers/{tracker}/toggle-complete` | POST | Redirect back | Toggle completion |

All AJAX POST endpoints require CSRF token.

## 11.3 Session & Auth Expectations

### CSRF Token
- Meta tag: `<meta name="csrf-token" content="{{ csrf_token() }}">`
- Forms: `@csrf` directive.
- AJAX: `X-CSRF-TOKEN` header from meta tag or cookie `XSRF-TOKEN`.

### Authenticated User in Views
- `auth()->user()` gives current user.
- `Auth::user()->canAccessModule('domains', 'can_read')` for permission checks.
- `Auth::user()->is_super_admin` for super admin detection.

### Flash Messages
- `session('success')` → green alert.
- `session('error')` → red alert.
- `session('warning')` → yellow alert.
- `session('info')` → blue alert.

## 11.4 Blade Components Currently Used (Reference)

| Component | Path | Props | Notes |
|---|---|---|---|
| ActivityTimeline | `app/View/Components/ActivityTimeline.php` | `$subject` (model) | Renders activity log entries for entity |
| Modal | `resources/views/components/modal.blade.php` | `$name`, `$title` | Generic modal wrapper |
| ConfirmModal | `resources/views/components/confirm-modal.blade.php` | `$message`, `$action` | Delete/sensitive action confirmation |
| PermissionBadge | **DELETED** Sprint B | — | Was duplicate of inline badge |
| HelpButton | **DELETED** Sprint B | — | No-op floating help button |

### Deleted Components (Sprint B)
- `permission-badge` — only used two times, both replaceable with inline permission badge.
- `help-button` — displayed floating help widget that linked to nowhere (no help page existed).

## 11.5 Layout Structure

```
resources/views/layouts/
  app.blade.php          — Main layout (sidebar, navbar, content area)
  guest.blade.php        — Login/register layout (no sidebar)
  
resources/views/
  dashboard/
    index.blade.php     — Dashboard with stats widgets
  domains/
    index.blade.php     — Paginated list with filters
    show.blade.php      — Detail view + edit form combined
  ... (same pattern for all modules)
```

### Layout Key Elements
- **Sidebar:** Navigation menu with module links. Active state based on current route.
- **Header:** User dropdown, notifications dropdown (database notifications), search bar.
- **Content:** Main content area with flash messages at top.
- **Footer:** App version, copyright.

## 11.6 CSS/JS Dependencies

### CSS
- Tailwind CSS v3 — utility classes
- Custom CSS in `resources/css/app.css` (minimal — mostly Tailwind)

### JavaScript
- Alpine.js v3 — interactive components (dropdowns, modals, password reveal toggle, kanban drag)
- Vite entry: `resources/js/app.js`
- No jQuery
- No Vue/React components

## 11.7 What the Frontend Must NOT Do

1. **Make authorization decisions** — always let backend validate permissions.
2. **Store decrypted passwords** in any client-side storage (localStorage, sessionStorage, cookies).
3. **Hardcode API URLs** — always use named routes or route helpers.
4. **Skip CSRF token** on any state-changing request.
5. **Assume all fields are present** — handle nulls gracefully.
6. **Send requests without checking permissions** — but also don't block requests based on perceived permissions (backend decides).
7. **Create duplicate linked ExpiryTrackers** — enforce "max one per source" in UI.
8. **Submit forms with `max` attribute as security** — always validate server-side.
9. **Use deprecation-prone route slugs** — `/expiry-trackers` is intentionally preserved. Don't change to `/renewals`.
10. **Submit invalid date formats** — MySQL expects `Y-m-d` format.

## 11.8 Pagination, Filtering, Search Conventions

### URL Query Parameters
- `?page=2` — pagination (1-indexed).
- `?search=term` — search term.
- `?status=active` — status filter.
- `?sort=field` — sort column.
- `?direction=asc` — sort direction.
- `?show=all` — show all records (override pagination? Used in some modules).

### Form Filters
- Some index pages have filterable form fields (e.g., status dropdown, date range).
- Filters reset on page load (not persistent across sessions).

### Sorting
- Default sort on most entities: `expire_date ASC NULLS LAST`.
- Alternative: `name ASC` for non-date entities.
- Clicking column header re-sorts (toggle asc/desc).
