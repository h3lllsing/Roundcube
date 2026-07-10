# 9. Frontend Do-Not-Break List

> Rules the frontend MUST preserve during any migration or redesign.

---

## 🔴 CRITICAL (Data Loss or Security Risk)

### 1. Backend Permissions Are Source of Truth

The frontend can hide or show UI elements based on permissions, but it
MUST NEVER prevent a user from making a request. Backend ALWAYS re-checks
via `$this->authorize('module_access', [slug, permission])`.

**What to preserve:** `@can('module_access', ['module', 'can_read'])` in
Blade views. If migrating to Livewire: use `@can()` inside Livewire views
or `$this->authorize()` in Livewire component classes.

**Never:** Add client-side routing guards that assume permission status
from initial page load without backend re-validation.

### 2. CSRF Protection Required

Every POST/PUT/DELETE request MUST include `@csrf` (forms) or
`X-CSRF-TOKEN` header (AJAX). API routes are exempt.

**What to preserve:** The `<meta name="csrf-token">` in the layout head
and the `@csrf` directive in every form. If migrating to Livewire, Livewire
handles CSRF automatically.

### 3. Password Reveal Must Be Logged

Every password reveal (Vault, Domain, Hosting, etc.) MUST trigger an
activity log entry with `causer`, `subject`, `event: 'revealed'`.

**What to preserve:** The AJAX reveal endpoint must continue to call
`activity()->performedOn($entity)->causedBy(auth()->user())->event('revealed')->log()`.

**Never:** Reveal a password without a backend log entry.

### 4. No Plaintext Passwords in DOM by Default

Passwords must NEVER render as plaintext in the initial HTML. They must
be masked (e.g., "••••••••") and only revealed via explicit user action
+ AJAX call.

**What to preserve:** All password fields use `encrypted` cast in models.
Blade receives the encrypted value. `<x-form.password>` should render
masked. `<x-password-reveal>` should trigger AJAX reveal.

### 5. No localStorage Password Storage

Never store decrypted passwords in localStorage, sessionStorage, cookies,
or any client-side persistent storage.

**What to preserve:** The reveal flow shows password temporarily in the
DOM and auto-hides after timeout. No persistence.

### 6. Route Slugs Are Frozen

| Route | Slug | Do NOT Change To |
|---|---|---|
| Expiry Trackers | `/expiry-trackers` | `/renewals` |
| Hosting | `/hostings` | `/hosting` (note: WITH 's') |
| Domains | `/domains` | (unchanged) |
| VPS | `/vps` | (unchanged) |
| VoIP | `/voip` | (unchanged) |

**What to preserve:** ALL existing route slugs. Any change breaks bookmarks,
links, and server-side route matching.

### 7. Polymorphic MorphMap Aliases

These exact strings MUST be used when constructing URLs or API calls that
reference polymorphic types:

`domain`, `hosting`, `vps`, `voip`, `other-service`, `domain-email`,
`expiry-tracker`, `service-provider`, `asset`, `task`, `note`,
`attachment`, `module`, `feature`, `user`, `role`, `permission`,
`webhook`, `vault-entry`

**What to preserve:** The `trackable_type` field in ExpiryTrackers,
`notable_type` in Notes, `attachable_type` in Attachments, `subject_type`
in Activity Log. All use these aliases.

### 8. FK Select Rule

When any new query is added that combines `->select()` with `->with()`,
ALL belongsTo foreign key columns must be in the select list.

**What to preserve:** This is a backend rule but the frontend must be
aware: if you see a 500 error on a page that eager-loads relations, the
most likely cause is a missing FK in `->select()`.

---

## 🟠 HIGH (Functional Bugs If Broken)

### 9. Linked ExpiryTrackers Have Read-Only Fields

When an ExpiryTracker has `trackable_type` set:
- `name`, `expire_date`, `cost`, `renewal_status` are SYNCED from source
- These fields MUST be read-only in the UI
- Source service edit page shows these fields as editable

**What to preserve:** Check `$tracker->trackable_type` before rendering
fields. If not null, render as `<x-field :readonly="true">`.

### 10. ExpiryTracker Slug Is Preserved

The route slug is `/expiry-trackers`, NOT `/renewals`. This is intentional
and frozen. Any new UI must use `/expiry-trackers` in all navigation and
route references.

### 11. Notification Read State Must Persist

Database notifications have a `read_at` column. Marking as read:
- MUST update `read_at` via a POST request
- MUST update the unread count badge
- MUST persist across page reloads

**What to preserve:** The notification dropdown reads from
`Auth::user()->unreadNotifications`. The mark-as-read endpoint must
call `$notification->markAsRead()`.

### 12. Activity Timeline Must Be Accurate

The activity timeline shows `spatie/activitylog` entries for a specific
entity. It must:
- Query `activity_log` where `subject_id = entity_id AND subject_type = morph_alias`
- Display events in chronological order (most recent first)
- Show causer name, event type, timestamp, and property diffs

**What to preserve:** The `<x-activity-timeline>` component must continue
using this query pattern.

### 13. Dashboard Widget Visibility Must Match Permissions

Dashboard widgets must only show if the user has permission to see that
module. Currently handled by `@if(!empty($operations))` checks from the
controller. The controller only passes data the user can see.

**What to preserve:** Never hardcode widget visibility. Always rely on
controller-passed data.

### 14. Super Admin Must See Everything

Super admin bypasses ALL permission gates:
- Sees all sidebar links
- Sees all records (no ownership filtering)
- Sees all dashboard widgets
- Can access all routes

**What to preserve:** `Auth::user()->isSuperAdmin()` checks in layout
must continue to show all sidebar sections. In tables, super admin must
see all records even if `user_id` filter exists.

---

## 🟡 MEDIUM (UX Issues If Broken)

### 15. Flash Message Session Keys

| Key | Type | Display |
|---|---|---|
| `success` | Success | Green alert |
| `error` | Error | Red alert |
| `warning` | Warning | Yellow alert |
| `info` | Info | Blue alert |

**What to preserve:** Session flash keys must be read and displayed by
the layout. If migrating to Livewire, flash messages still come from
session.

### 16. Pagination Query Parameter

All index pages use `?page=N` for pagination (1-indexed). The paginator
is `LengthAwarePaginator` with default 10 per page.

**What to preserve:** Any new filter/search implementation must preserve
the `page` query parameter so paginated searches work correctly.

### 17. Search Query Parameter

All index pages use `?search=term` for search. This is consumed by
`$request->search` in controllers.

**What to preserve:** The `search` query parameter name. If adding
Livewire's `wire:model.live`, ensure the URL still reflects the search
state or use Livewire's URL tracking.

### 18. Modal Confirmation for Delete Actions

All delete actions MUST require user confirmation. The current pattern
is `data-confirm="Are you sure?"` with the entity name.

**What to preserve:** Replace with `<x-confirm-dialog>` but never remove
confirmation entirely.

### 19. Sidebar Active State

The current module must be highlighted in the sidebar. Currently done by
comparing `request()->route()->getName()` with predefined patterns.

**What to preserve:** If restructuring the sidebar, preserve active state
detection logic.

### 20. Breadcrumbs Auto-Generation

Breadcrumbs are auto-generated from route names. The mapping array in
`components/breadcrumbs.blade.php` maps route segments to labels.

**What to preserve:** If replacing breadcrumbs, either preserve the
route-name-to-label mapping or use `<x-page-header breadcrumbs>` prop.

---

## 📋 Items Already Fixed (Don't Reintroduce)

| Issue | Fix | What to Preserve |
|---|---|---|
| Hosting slug mismatch | `'hosting'` → `'hostings'` in permission checks | Always use `'hostings'` (with 's') |
| DB queries in Blade | Moved from ActivityTimeline to controller | ActivityTimeline receives data, doesn't query |
| Dead ExpiryTracker password refs | Removed from fillable/hidden/casts/activitylog | No password field on ExpiryTracker model |
| Dead Blade components | Deleted permission-badge and help-button | Don't recreate them |
| FK select bugs (11 controllers) | Added missing FK columns | Future queries must follow FK Select Rule |
| Prototype CSS (186 lines) | TO BE DELETED during Design System migration | Delete from app.css after confirming no remaining references |

---

## 📋 Reference: Backend Architecture Not to Touch

When redesigning the frontend, the following backend layers are FROZEN
and must NOT be modified:

| Layer | Don't Touch | Why |
|---|---|---|
| Routes | `routes/web.php`, `routes/api.php` | Changing routes breaks all frontend links |
| Controllers | `app/Http/Controllers/Web/*` | Business logic + query patterns |
| Validation | `app/Http/Requests/*` | Form validation rules |
| Permissions | `app/Traits/HasModulePermissions.php`, `config/permissions.php` | Complex RBAC logic |
| Models | `app/Models/*` | Data access, relationships, scopes |
| Services | `app/Services/*` | Renewal notification, vault encryption |
| Activity Log | `app/Observers/*`, `app/Providers/AppServiceProvider.php` | Logging lifecycle |
| Notifications | `app/Notifications/*` | Mail + database notification delivery |
| Config | `config/renewals.php`, `config/permissions.php`, `config/tyro.php` | App configuration |
| Migrations | `database/migrations/*` | Database schema |

The frontend can add files (new Blade components, new JS modules, new
CSS), but must NOT modify any of the above.
