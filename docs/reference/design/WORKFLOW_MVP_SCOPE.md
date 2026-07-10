# WORKFLOW MVP SCOPE

> For each candidate: the smallest possible deliverable that proves the concept.
> MVP is NOT the final vision — it's the fastest path to validated learning.

---

## MVP 1: Offboarding Checklist (3 days)

### What it is
A read-only widget on the User detail page that queries all user associations and displays counts with hyperlinks to each section. No write operations. No transactional logic. Just information.

### Wireframe
```
┌──────────────────────────────────────────────┐
│ OFFBOARDING CHECKLIST                         │
│                                               │
│  □ 1. Suspend Account          [Suspend →]   │
│  □ 2. Revoke Credentials (12)  [View →]      │
│  □ 3. Reassign Tasks (5)       [View →]      │
│  □ 4. Check In Assets (2)      [View →]      │
│  □ 5. Review Activity (47)     [View →]      │
│                                               │
│  Last updated: 2 min ago                      │
└──────────────────────────────────────────────┘
```

### What it does NOT include
- ❌ One-click revoke/reassign/check-in actions
- ❌ Completion tracking (checkboxes don't persist state)
- ❌ Offboarding report PDF
- ❌ Shared credential access scanning (only shows owned vault entries)
- ❌ Rate limiting or two-person rule

### Files to create/modify
- **New:** `app/Http/Livewire/OffboardingChecklist.php` — queries all association counts
- **New:** `resources/views/livewire/offboarding-checklist.blade.php` — widget template
- **Modified:** User detail page view — add `@livewire('offboarding-checklist', ['user' => $user])`

### Data model changes
NONE. All queries use existing relationships:
```php
$user->vaultEntries()->count();
$user->tasks()->count();
Asset::where('assigned_to', $user->id)->count();
Activity::where('causer_id', $user->id)->where('causer_type', User::class)->count();
```

### Validation criteria
- Checklist shows correct counts for a test user with known associations
- Each "View →" link navigates to the correct section with the user pre-filtered
- Widget is only visible to Security Officer and Super Admin roles
- Page load time increases by < 200ms

### Upgrade path
MVP → V1: Add "Suspend Account" button → V2: Add per-item action buttons (revoke, reassign, check-in) → V3: Add shared credential scanning → V4: Add completion tracking → V5: Add two-person rule

---

## MVP 2: Service-Credential Auto-Copy (2 days)

### What it is
A "Copy Password" button on every service detail page (Hosting, Domain, VPS, VoIP, OtherService) that copies the inline encrypted password. No vault linking. Just makes the existing inline password accessible with one click instead of the user having to reveal it manually through the form.

### Wireframe
```
Hosting Detail: Acme Web
┌──────────────────────────────────────────────┐
│  Name:    Acme Web                           │
│  Plan:    Business                           │
│  Cost:    $29/mo                             │
│  Password: [••••••••]  [Copy] [Reveal]      │
│  URL:     https://acme.com/cpanel            │
└──────────────────────────────────────────────┘
```

### What it does NOT include
- ❌ Vault entry creation for the service
- ❌ Vault ↔ service FK linking
- ❌ "Find credential for this service" search
- ❌ Backfill of existing vault entries

### Files to create/modify
- **Modified:** Each service detail page — add copy/reveal password component
- **New or modified:** Password reveal controller — ensure `can_reveal` permission check
- **Modified:** Service model casts — ensure `password` is accessible in views (currently hidden from serialization but readable in PHP)

### Data model changes
NONE. The inline `password` column already exists on all 5 service models. It's currently `protected $hidden` from serialization but readable in PHP. The MVP makes it accessible through the detail page with proper permission checks.

### Permission check
The "Reveal" button must check `$user->canOnModule($module, 'reveal')` using the existing `module_role_permissions.can_reveal` flag. Without this check, the MVP creates a security hole.

### Validation criteria
- Service Desk user can click "Copy" and paste the password
- User without `can_reveal` permission sees masked password only
- Copy action is logged in activity log
- Works across all 5 service types

### Upgrade path
MVP → V1: Add vault entry creation from service page → V2: Add FK linking between service and vault entry → V3: Two-way sync (vault changes update service password)

---

## MVP 3: Renewal Inline Dashboard (3-5 days)

### What it is
A replacement for the current expiry tracker list view that inlines service name + provider name + cost directly in the table. No more 3-page triangle. Each row shows everything needed for a renewal decision.

### Wireframe
```
┌────────────────────────────────────────────────────────────────────┐
│ RENEWALS DASHBOARD  |  Month: July 2026  |  Total: $12,450       │
│                                                                     │
│ Service        │ Provider   │ Expires │ Cost  │ Status   │ Action  │
│ ────────────────────────────────────────────────────────────────── │
│ example.com    │ GoDaddy    │ Jul 15  │ $15   │ Active   │ [Renew] │
│ acme-hosting   │ DigitalOcean│ Jul 22 │ $120  │ Active   │ [Renew] │
│ vpn-prod       │ AWS        │ Jul 30  │ $450  │ Active   │ [Renew] │
│ phone-main     │ RingCentral│ Jul 31  │ $200  │ Active   │ [Renew] │
│                                                                     │
│ [Process Selected]  [Export CSV]                                    │
└────────────────────────────────────────────────────────────────────┘
```

### What it does NOT include
- ❌ One-click renew without confirmation
- ❌ Cost forecast/trend chart
- ❌ "Still needed?" ML/heuristic flagging
- ❌ Auto-reminder escalation control
- ❌ SMTP profile management inline

### Files to create/modify
- **Modified:** ExpiryTracker index view — replace current scrap table with inline dashboard
- **New:** Livewire component for inline "Renew" action with confirmation modal
- **Modified:** ExpiryTrackerController — add eager loading for polymorphic relations

### Data model changes
NONE. All required data exists:
```php
ExpiryTracker::with('trackable')  // polymorphic -> Hosting/Domain/VPS/VoIP/OtherService
    ->with('trackable.serviceProvider')  // nested -> ServiceProvider
    ->where('status', 'active')
    ->orderBy('expiry_date')
    ->get();
```

### Query optimization required
The polymorphic eager loading pattern is the main risk:
```php
// WRONG (N+1):
foreach ($trackers as $tracker) { $tracker->trackable; }

// RIGHT (eager load all morph types):
$t = ExpiryTracker::with('trackable')->get();
$t->loadMorph('trackable', [
    Hosting::class => ['serviceProvider'],
    Domain::class => ['serviceProvider'],
    VPS::class => ['serviceProvider'],
    VoIP::class => ['serviceProvider'],
    OtherService::class => ['serviceProvider'],
]);
```
Without this optimization, 200 ExpiryTrackers = 200+1 queries for trackable + 200 more for providers = 401 queries. With optimization: 1 + 5 + 5 = 11 queries.

### Validation criteria
- Dashboard renders with 200 ExpiryTrackers in < 500ms
- Inline service name + provider name + cost are accurate
- "Renew" button extends expiry_date by 1 year and logs the action
- User sees only ExpiryTrackers for modules they can read
- Export CSV includes all inlined fields

### Upgrade path
MVP → V1: Add confirmation modal before renew → V2: Add "Still needed?" flag based on service activity → V3: Add cost trend chart → V4: Add inline SMTP notification control

---

## MVP 4: Quick Provision Form (1 week)

### What it is
A single form that combines service creation + credential storage into one submission. Select provider → fill service details → fill credential → submit creates both. The credential's `service_name` is auto-filled from the service name.

### Wireframe
```
┌──────────────────────────────────────────────┐
│ QUICK PROVISION                               │
│                                               │
│ Provider: [Select or create new...]    ▼      │
│                                               │
│ Service Type: ○ Hosting  ○ Domain            │
│              ○ VPS      ○ VoIP               │
│              ○ SaaS                           │
│                                               │
│ Service Name: [______________________]        │
│ Plan: [______________________]                │
│ Cost: [$_______________]                      │
│ Start Date: [____-__-__]                      │
│ Expiry Date: [____-__-__]                     │
│                                               │
│ ─── CREDENTIALS ───                           │
│ Username: [______________________]            │
│ Password: [________________] [Auto-Generate]  │
│ URL: [______________________]                 │
│                                               │
│ [Create Service & Store Credential]           │
│                                               │
│ Also create task? [ ] (appears after submit)  │
│ Also set renewal? [ ] (appears after submit)  │
└──────────────────────────────────────────────┘
```

### What it does NOT include
- ❌ Multi-step wizard UX (this is a single form)
- ❌ Domain email creation
- ❌ Auto-generated renewal reminder
- ❌ Setup task auto-creation
- ❌ Transactional integrity across more than 2 entities

### Files to create/modify
- **New:** `app/Http/Livewire/QuickProvision.php` — form with conditional fields
- **New:** `resources/views/livewire/quick-provision.blade.php`
- **New:** `app/Http/Requests/QuickProvisionRequest.php` — validation rules
- **New:** `app/Actions/ProvisionService.php` — action class that orchestrates creation

### Data model changes
NONE for MVP. The form creates two entities independently:
1. Service record (Hosting/Domain/VPS/VoIP/OtherService) — using existing model
2. Vault entry — with `service_name` auto-filled from service name

No FK linking between them for MVP. The vault entry's `service_name` is a text field, not a FK.

### Permission check
User must have `can_create` on BOTH the target service module AND the Vault module. Pre-checked before form renders. If missing either permission, show "You cannot create [entity]" with the form disabled.

### Transaction handling
Wrap both creates in a DB transaction:
```php
DB::transaction(function () use ($data) {
    $service = ServiceModel::create($data->serviceData());
    VaultEntry::create($data->credentialData($service));
});
```
If credential creation fails, service creation rolls back. No orphaned records.

### Validation criteria
- Form creates both service and vault entry in one submit
- Service name and vault entry `service_name` match exactly
- Transaction rolls back if either creation fails
- User without `can_create` on service module sees form disabled
- User without `can_create` on vault module sees form disabled

### Upgrade path
MVP → V1: Add "Also create task" checkbox (calls Task::create after) → V2: Add "Also set renewal" checkbox → V3: Add domain email step → V4: Full multi-step wizard with data flow between steps

---

## MVP 5: Security Recent Events Widget (1 week)

### What it is
A widget on the Security Officer dashboard that shows the last 20 events (login events + activity log changes) merged by time. Filterable by user. Clicking an event navigates to the source page.

### Wireframe
```
┌────────────────────────────────────────────────────────┐
│ RECENT EVENTS — Last 24 hours                          │
│ Filter: [All Users ▾]  [All Types ▾]                   │
│                                                          │
│ 2:15 AM │ ⚠ Login Failed   │ jsmith from 185.220.x.x  │
│ 2:20 AM │ ✓ Login Success   │ jsmith from same IP     │
│ 2:25 AM │ ✎ Permission Change │ jsmith → super-admin  │
│ 2:30 AM │ 🔑 Vault Access   │ jsmith → production-db  │
│ 2:35 AM │ 📁 Attachment     │ jsmith → db_export.csv  │
│                                                          │
│ [View Full Timeline →]   [Auto-refresh: On ▾]           │
└────────────────────────────────────────────────────────┘
```

### What it does NOT include
- ❌ Predictive/anomaly detection
- ❌ Automated investigation report
- ❌ Cross-session correlation
- ❌ Real-time push notifications
- ❌ Historical search beyond 7 days

### Files to create/modify
- **New:** `app/Http/Livewire/SecurityTimeline.php` — merges two queries
- **New:** `resources/views/livewire/security-timeline.blade.php` — timeline display
- **Modified:** Security Officer dashboard — add widget

### Data model changes
NONE. Queries `login_audits` and `activity_log` separately, merges in PHP by `created_at` DESC.

### Query implementation
```php
// Get last 24 hours of login events
$logins = LoginAudit::where('created_at', '>=', now()->subDay())
    ->get()
    ->map(fn($l) => [
        'time' => $l->created_at,
        'type' => 'login',
        'event' => $l->event,
        'description' => "{$l->email} from {$l->ip_address}",
        'user_id' => $l->user_id,
        'url' => route('login-audits.show', $l->id),
    ]);

// Get last 24 hours of activity log events related to security
$activities = Activity::where('created_at', '>=', now()->subDay())
    ->whereIn('event', ['created', 'updated', 'deleted', 'login'])
    ->get()
    ->map(fn($a) => [
        'time' => $a->created_at,
        'type' => 'activity',
        'event' => $a->event,
        'description' => $a->description,
        'user_id' => $a->causer_id,
        'url' => $a->subject ? route('activity.show', $a->id) : null,
    ]);

// Merge by time, take latest 20
$events = collect([...$logins, ...$activities])
    ->sortByDesc('time')
    ->take(20);
```

### Permission check
User must have `can_read` on BOTH the LoginAudit module AND the ActivityLog module. If user has access to only one, show only that one's events (silent filter, no error).

### Validation criteria
- Widget shows merged events sorted by time descending
- Filter by user_id works (shows events from both tables for that user)
- Each event is clickable and navigates to the source page
- Widget renders in < 200ms with 100+ events in 24h window
- User without LoginAudit access sees only activity log events

### Upgrade path
MVP → V1: Add 7-day history with pagination → V2: Add anomaly highlighting (failed login + subsequent permission change) → V3: Add real-time polling → V4: Add cross-session correlation by IP address

---

## MVP EFFORT COMPARISON

| Candidate | MVP | Effort | Lines of Code | Migrations | Risk Level |
|-----------|-----|--------|---------------|------------|------------|
| Offboarding Checklist | Read-only widget with counts | **3 days** | ~150 | 0 | LOW |
| Service-Credential Auto-Copy | Copy button for inline password | **2 days** | ~80 | 0 | LOW |
| Renewal Inline Dashboard | Inlined table with eager loading | **3-5 days** | ~200 | 0 | LOW-MED |
| Quick Provision Form | 2-entity creation form | **1 week** | ~300 | 0 | MED |
| Security Recent Events Widget | Merged timeline widget | **1 week** | ~250 | 0 | MED |

**All 5 MVPs require ZERO database migrations.** Every MVP works with the EXISTING data model. This is not a coincidence — the current schema already has the data, it's just not surfaced effectively.

## Combined MVP Sprint (3 weeks)

A single sprint could deliver ALL 5 MVPs in sequence:

| Week | Day | Deliverable |
|------|-----|-------------|
| W1 | 1-2 | Service-Credential Auto-Copy (2 days, highest per-effort ROI) |
| W1 | 3-5 | Offboarding Checklist (3 days, fastest risk reduction) |
| W2 | 1-5 | Renewal Inline Dashboard (5 days, widest persona impact) |
| W3 | 1-5 | Quick Provision Form (5 days, highest absolute time savings) |
| W3 | bonus | Security Timeline widget (1 day if Recycled Views pattern) |

**Total investment: 3 weeks, 5 workstreams, zero migrations, all MVPs.**
**Value: 30+ hours/week saved organization-wide + critical risk reduction.**
