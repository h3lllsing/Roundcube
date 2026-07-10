# ENTERPRISE FEATURE SPECIFICATION

## Feature: Security Recent Events Widget

Version: 1.0
Status: DRAFT
Priority: P5

---

## 1. Purpose

### Why does this feature exist?

Security Officer must cross-reference 4 separate pages (Login Audit, Activity Log, Credential Access, User Management) to investigate a potential incident. Correlation is manual and mental. An attacker's path — failed login → successful login → permission change → credential access → data exfiltration — appears as disconnected events across 4 separate pages with no timeline linking them.

### Which business problem does it solve?

Investigation time: 10-30 minutes → 3-5 minutes. Correlated events (failed login from new IP + permission change + credential access by same user in 5 minutes) jump out visually instead of being missed.

### What happens if it does not exist?

Investigations remain manual and incomplete. Correlated attacks (login from new IP + permission elevation + data access) are missed because events are spread across separate pages. Each missed investigation is a potential undetected breach.

---

## 2. Business Value

### Hours saved

- Current: 10-30 minutes/investigation
- With widget: 3-5 minutes/investigation
- 1-3 investigations/month = 7-75 minutes/month saved
- Daily security check: 10-20 minutes → 3-5 minutes (saves 5-15 min/day)

### Risk reduced

- Missed correlation: attacker path across Login + Activity events → visible in timeline
- Delayed investigation: timeline shows all events chronologically → investigation starts faster
- False negative (missing a real incident): timeline correlation makes patterns visible → reduced

### Errors reduced

- Manual cross-reference errors: currently inevitable when hopping between 4 pages
- Correlation errors: events that occur close together but on different pages are easily missed

### Compliance impact

- No direct compliance requirement. Timeline aids SOC2 CC7.1 (monitoring and detection) by making event correlation possible.

### Security impact

- PRIMARY: makes correlated attack patterns visible
- SECONDARY: reduces investigation time, freeing Security Officer for proactive work
- TERTIARY: creates a single-pane view that becomes the default daily security review tool

### Operational impact

- Security Officer daily security review goes from 4-page circuit to 1-page timeline
- Incident response starts faster because initial correlation is automatic
- New Security Officers can investigate independently without tribal knowledge of where events live

---

## 3. Actors

### Exactly which personas use it?

| Persona | Access | Rationale |
|---------|--------|-----------|
| Security Officer (3 users) | Full | Primary — daily security review and incident investigation |
| Super Admin (3 users) | Full | Secondary — user investigation and audit |

### Who must never see it?

| Persona | Block reason |
|---------|-------------|
| End User (450 users) | No business need for security data |
| Service Desk (20 users) | No business need. Should not see security event data. |
| IT Operator (10 users) | No business need. Security is separate function. |
| IT Manager (5 users) | No operational security role. |
| IT Director (1 user) | Can see aggregate security reports (e.g., "Failed logins this month") but not individual event timeline. |
| Procurement (3 users) | No business need. |

---

## 4. Permissions

### Exactly which permission checks occur?

**Check 1: Can read login audits**
- Module: `login_audits` (slug: `login-audits`)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: login events are excluded from timeline. Widget continues with activity-only data.

**Check 2: Can read activity log**
- Module: `activity_log` (slug: `activity-log`)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: activity events are excluded from timeline. Widget continues with login-only data.

**NEEDS REVIEW:** `activity_log` module may not be registered in `module_role_permissions`. If no `activity_log` module exists, this permission check cannot be performed. Fallback: always include activity log data (no module gate). Check `modules` table for an `activity-log` or `activity_log` entry.

**Combined rule:**
- Both readable: full merged timeline
- Only login readable: login events only
- Only activity readable: activity events only
- Neither readable: widget does NOT render

### Which modules are checked?

1. `login_audits` (slug: `login-audits`)
2. `activity_log` (slug: `activity-log`) — NEEDS REVIEW: confirm this module exists

### Which actions?

- `can_read` on both modules

### Which permission has highest priority?

Neither has priority — they are independent. The widget shows whatever data the user is permitted to see from each source. If only login audits are readable, the timeline shows login events only.

### Trace runtime

```
1. Livewire component mount()
2.   → $canSeeLogins = canOnModule('login-audits', 'read')
3.   → $canSeeActivity = canOnModule('activity-log', 'read') ?? true // fallback if no module
4.   → if (!$canSeeLogins && !$canSeeActivity) { $this->skipRender(); return; }
5.   → if ($canSeeLogins) { $logins = LoginAudit::whereDate('created_at', '>=', now()->subDay())->get(); }
6.   → if ($canSeeActivity) { $activities = Activity::whereDate('created_at', '>=', now()->subDay())->get(); }
7.   → $events = collect()->merge($logins ?? [])->merge($activities ?? [])->sortByDesc('created_at')->take(20)
8.   → $this->events = $events->values()
```

---

## 5. Business Rules

- R1: Widget shows last 24 hours of events by default.
- R2: Widget has a time range selector: 24h, 7d, 30d.
- R3: Widget merges LoginAudit events and ActivityLog events sorted by `created_at` DESC.
- R4: Maximum 20 events displayed. "View All" link at bottom navigates to full timeline page.
- R5: Each event shows: timestamp, icon (determined by event type), summary, user name.
- R6: Event types and icons:
  - Login success: `✓` green
  - Login failed: `⚠` red
  - Permission change: `✎` orange
  - Vault access: `🔑` yellow
  - Service created: `+` green
  - Service deleted: `−` red
  - Service updated: `✏` blue
  - User suspended: `⊘` gray
  - User created: `+` teal
  - Unknown event: `•` gray
- R7: Event summary text:
  - Login success: "[email] logged in from [IP]"
  - Login failed: "[email] failed login from [IP]"
  - Permission change: "[user] changed [subject description]"
  - Vault access: "[user] accessed vault entry [name]"
  - Others: activity.log description text (truncated to 100 chars)
- R8: User name is derived from `login_audit.email` (login events) or `activity.causer.name` (activity events).
- R9: IP address is shown for login events only. Activity events do not show IP.
- R10: Events are clickable → navigate to source page (login audit detail or activity log detail).
- R11: Widget auto-refreshes every 60 seconds (Livewire polling).
- R12: Widget is on the Security Officer dashboard page (or a new dedicated security dashboard).
- R13: Widget has a filter: "All Events", "Login Events Only", "Activity Events Only".
- R14: Widget has a filter by user (search/autocomplete).
- R15: User filter filters BOTH login and activity events for that user simultaneously.
- R16: Widget header shows total event count and time range: "24 events in the last 24 hours".
- R17: If no events in the selected time range: "No events found. Try expanding the time range."
- R18: Widget title: "Recent Security Events".

---

## 6. Data Used

### Exactly which models

| Model | Table | Usage |
|-------|-------|-------|
| App\Models\LoginAudit | `login_audits` | Login events. Read `user_id`, `email`, `ip_address`, `event`, `created_at`. |
| Spatie\Activitylog\Models\Activity | `activity_log` | Activity events. Read `causer_id`, `causer_type`, `description`, `event`, `subject_type`, `subject_id`, `created_at`. |
| App\Models\User | `users` | Derive user name from `activity.causer`. LoginAudit has user_id FK; Activity has causer morph. |

### Exactly which tables

1. `login_audits`
2. `activity_log`
3. `users` (via `causer_id` morph resolve or direct JOIN)

### Exactly which relationships

| Source | Relationship | Target |
|--------|-------------|--------|
| LoginAudit | `user(): BelongsTo` | User |
| Activity | `causer(): MorphTo` | User (or null) |

LoginAudit → User: `$loginAudit->user->name`
Activity → User: `$activity->causer->name` (when `causer_type === 'App\Models\User'`)

### Exactly which queries

**Query 1: Login events in time range**
```php
$logins = LoginAudit::where('created_at', '>=', $since)
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();
// 1 query
```

**Query 2: Activity events in time range**
```php
$activities = Activity::where('created_at', '>=', $since)
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();
// 1 query
```

**Query 3: Eager load causer for activity events**
```php
$activities->load('causer');
// 1 query: SELECT * FROM users WHERE id IN (causer_ids)
```

Total queries: 2 (initial) + 1 (causer eager load) = 3 queries.
Merge: done in PHP collection, no additional queries.

---

## 7. UI Contract

### Exactly what appears

A card-style widget on the Security Officer dashboard showing a vertical timeline of events. Each event row has: timestamp (HH:MM format), event icon (colored), summary text, user name.

### Exactly where

On the Security Officer dashboard page. If no dedicated dashboard exists: add a new blade view at `/security` with `@can('security.access')` gate. **NEEDS REVIEW:** Determine where Security Officer navigates on login. If no dashboard exists, create `/security/timeline` as a standalone page.

### Exactly when

Rendered when:
1. User has `can_read` on at least one of: login_audits, activity_log
2. User is on the security dashboard/timeline page

### Exactly when hidden

Widget is NOT rendered when:
1. User lacks `can_read` on BOTH login_audits AND activity_log
2. Widget shows "No events found" state when there are zero events in the selected time range

### Exactly when disabled

- Filter controls are disabled during the 60-second auto-refresh (to prevent race conditions).
- Time range selector disabled when loading more data (Livewire loading state).

### No redesign

Uses existing card styling. Event icons use existing Blade icon components. Widget fits within the existing dashboard grid layout.

---

## 8. Runtime Flow

```
USER: Security Officer navigates to /security/timeline

ROUTE: GET /security/timeline → SecurityController@timeline
  → (or Livewire component on existing dashboard)

LIVEWIRE: SecurityTimeline component
  → mount()
    → canReadLogins = canOnModule('login-audits', 'read')
    → canReadActivity = canOnModule('activity-log', 'read') ?? true
    → if neither: skip render
    → loadEvents('24h')
  → loadEvents($range)
    → $since = match($range) { '24h' => now()->subDay(), '7d' => now()->subWeek(), '30d' => now()->subMonth() }
    → if canReadLogins: $logins = LoginAudit::where('created_at', '>=', $since)->limit(50)->get()
    → if canReadActivity: $activities = Activity::where('created_at', '>=', $since)->limit(50)->get()
    → $activities->load('causer')
    → merge, sort, take(20)
  → render()
    → return timeline view with events
  → pollingEvery(60s)
    → loadEvents(currentRange)

DATABASE:
  → SELECT * FROM login_audits WHERE created_at >= ? ORDER BY created_at DESC LIMIT 50
  → SELECT * FROM activity_log WHERE created_at >= ? ORDER BY created_at DESC LIMIT 50
  → SELECT * FROM users WHERE id IN (causer_id1, causer_id2, ...)

RESPONSE:
  → Timeline rendered with merged events
  → Polling keeps timeline current
```

---

## 9. Edge Cases

### Soft delete

- User soft-deleted: `Activity::causer` returns null (user not found). Show "Deleted user" instead of name. Do NOT crash.
- User restored: next refresh shows name again.

### Null

- `activity.causer_id` is NULL: event was system-generated (cron, queue). Show "System" as user name.
- `activity.causer_type` is NOT 'App\Models\User': causer is not a user (could be a queue worker). Show "System".
- `login_audits.user_id` is NULL: failed login for non-existent email. Show email only, no user name.
- `activity.description` is NULL: show event type as summary. Fallback: "Activity event".

### Missing provider

N/A — this feature does not use ServiceProvider.

### Permission denied

- User has access to login_audits but not activity_log: timeline shows login events only. No error message. This is CORRECT — the widget silently shows what it can.
- User has access to neither: widget says "Insufficient permissions to view security events."

### Concurrent edit

- New events created by other users while widget is open: Livewire polling picks them up within 60 seconds. No conflict.

### Deleted user

- Events from deleted users: still appear in timeline. Causer show "Deleted user". This is CORRECT — historical events should not disappear.

### Activity log volume

- 5000+ events in 24 hours: queries use `LIMIT 50` to fetch latest events. User sees at most 20 (display limit). Performance is bounded.

### Login audit volume

- Same as activity log: bounded by LIMIT 50.

### Timezone

- All timestamps in `created_at` are stored in UTC (Laravel default). Display in application timezone (configurable via `app.timezone`). The widget uses `Carbon::setLocale()` and `diffForHumans()` for relative time display.

### Empty state transitions

- No events in 24h but events in 7d: widget shows "No events in last 24 hours. [View 7 days]". Clicking "View 7 days" switches range to 7d and reloads.
- No events at all: "No security events recorded yet."

---

## 10. Security

### Attack vectors

**AV1: Timeline makes correlation too easy for compromised accounts**
- Vector: Attacker compromises a low-privilege account that somehow has access to both modules
- Mitigation: Timeline requires `can_read` on EACH module independently. Normal accounts have access to zero or one module. Super Admin and Security Officer have access to both.
- Severity: MEDIUM — the timeline is inherently more useful to attackers than separate pages, but access is restricted to the same accounts that already have access to both sources

**AV2: IP address exposure in timeline**
- Vector: Timeline reveals IP addresses from login events to users who can read login audits
- Mitigation: IP addresses are shown only for login events. Activity events do NOT include IP. All users who can read login audits already see IPs on the login audits page.
- Severity: LOW — no new IP exposure beyond existing module access

**AV3: User name linkage across event types**
- Vector: Timeline shows which user performed which action, linking login patterns to activity patterns
- Mitigation: This is the INTENDED purpose of the feature. The linkage is the value proposition. Acceptable risk.
- Severity: INTENTIONAL

**AV4: Event filtering reveals user existence**
- Vector: Attacker with access to login audits only types a user email in the filter and sees "No activity events for this user" — revealing the user only has activity entries (which the attacker cannot see)
- Mitigation: The filter does NOT have a "no results" distinction between "user exists with no events" and "user not found". Both show "No events found."
- Severity: LOW — user enumeration via event filter is theoretical and low-impact

### Privilege escalation

**PE1: User without activity_log access sees activity event summaries**
- Guard: Query-level filter: if `canReadActivity` is false, no activity events are fetched. No data reaches the component.
- Result: Privilege escalation not possible.

### Data leakage

**DL1: Activity event summary reveals model data**
- Activity `description` field may contain model data (e.g., "Created hosting 'Internal DB Server'"). This is already visible on the activity log page. Timeline adds no new exposure.
- Severity: LOW — same data, different view

### Audit logging

- No audit logging for viewing the timeline (read-only). Audit logging for viewing a specific event's detail page is handled by the existing source page.

### Rate limiting

- Timeline view: not rate limited (read-only, cached).
- Livewire polling: 60-second interval means 1 request/minute/user. No rate limiting needed.

### Sensitive actions

- None — this is a read-only feature.

### Abuse scenarios

- **Monitoring user login patterns:** The timeline shows login success/failure for all users. A Security Officer could monitor a specific user's login times. This is within their job function. Severity: ACCEPTABLE.
- **Harvesting event data via API:** Livewire component responds to polling. Data returned is filtered by permission. Data is the same as separate page access. Severity: ACCEPTABLE.

---

## 11. Performance Budget

### Expected queries

- Initial load: 2 (login_audits + activity_log) + 1 (causer eager load) = 3 queries
- Polling: 3 queries (same structure)
- Filter by user: +1 query for user search (autocomplete)
- Filter change: 3 queries (re-fetch with new range)

### Expected response time

- Initial load: < 300ms (2 bounded queries + eager load)
- Polling: < 200ms (cached query results)
- Filter change: < 300ms

### Expected memory

- < 5MB PHP memory (50 login events + 50 activity events + causer models)

### Maximum dataset

- 50 login events + 50 activity events = 100 records max loaded
- 20 events displayed max
- Activity log table can be 100K+ rows, but queries are bounded by `LIMIT 50` and `created_at >= ?`

### Scaling concerns

- `activity_log` table growth: the `created_at` index is critical. Without it, `WHERE created_at >= ? ORDER BY created_at DESC LIMIT 50` will table-scan.
- The Spatie migration `2026_05_23_123751_create_activity_log_table.php` creates an index on `log_name` by default. **NEEDS REVIEW:** Check if `created_at` has an index on `activity_log`. The migration fragment: `$table->index('log_name');` — only `log_name` is indexed by default. `created_at` has NO explicit index.

**CRITICAL PERFORMANCE NOTE:** The `activity_log` table may not have a `created_at` index. If the table grows beyond 10K rows, the timeline's activity query will perform a full table scan → response time > 5 seconds. A migration to add `$table->index('created_at')` to `activity_log` is REQUIRED before this feature can perform at scale.

---

## 12. Acceptance Criteria

| # | Criterion | Expected Result | Pass/Fail |
|---|-----------|----------------|-----------|
| AC1 | Security Officer sees merged timeline of login + activity events | Timeline renders with both types | PASS / FAIL |
| AC2 | User with only login_audits.read sees login events only | Timeline shows login events only | PASS / FAIL |
| AC3 | User with only activity_log.read sees activity events only | Timeline shows activity events only | PASS / FAIL |
| AC4 | User without either permission sees 403/empty state | Widget not rendered | PASS / FAIL |
| AC5 | Events sorted by time DESC | Most recent event first | PASS / FAIL |
| AC6 | Maximum 20 events shown | Widget shows ≤ 20 events | PASS / FAIL |
| AC7 | Time range selector changes displayed events | 24h vs 7d vs 30d returns different data | PASS / FAIL |
| AC8 | Filter by user shows events for that user only | Both login and activity filtered | PASS / FAIL |
| AC9 | Events are clickable → source page | Navigates to correct detail page | PASS / FAIL |
| AC10 | Auto-polling every 60 seconds | New events appear within 60 seconds | PASS / FAIL |
| AC11 | Deleted user's events show "Deleted user" | No crash, no blank, shows fallback | PASS / FAIL |
| AC12 | Null causer events show "System" | System-generated events labeled correctly | PASS / FAIL |
| AC13 | Zero events in range shows empty state | "No events found" message | PASS / FAIL |
| AC14 | IP addresses shown only for login events | Activity events do NOT contain IP | PASS / FAIL |
| AC15 | Timeline loads in < 500ms with 100+ daily events | Measured response time < 500ms | PASS / FAIL |
| AC16 | Activity log query uses created_at index | EXPLAIN shows index usage | PASS / FAIL |
| AC17 | Login audit query uses created_at index | EXPLAIN shows index usage | PASS / FAIL |

---

## 13. Regression Checklist

| # | Behavior | How verified |
|---|----------|-------------|
| R1 | LoginAudit index page still works | Existing login audit route test |
| R2 | ActivityLog index page still works | Existing activity log route test |
| R3 | LoginAudit creation on user login | Login flow test |
| R4 | ActivityLog creation on model CRUD | Existing CRUD tests |
| R5 | LoginAudit `can_read` permission for other users | Permission test with non-admin roles |
| R6 | ActivityLog causer morph resolution | Existing causer test |
| R7 | Dashboard page for non-security users | Dashboard unchanged for IT Operator, etc. |

---

## 14. Testing

### Unit

| Test | What it validates |
|------|------------------|
| LoginAuditTest@test_query_by_date_range | Date-filtered query returns correct rows |
| ActivityTest@test_query_by_date_range | Date-filtered query returns correct rows |
| ActivityTest@test_null_causer | Null causer handled gracefully |

### Feature

| Test | What it validates |
|------|------------------|
| SecurityTimelineTest@test_merged_events | Both event types appear in correct order |
| SecurityTimelineTest@test_max_20_events | Never more than 20 displayed |
| SecurityTimelineTest@test_time_range_24h | Only last 24h events shown |
| SecurityTimelineTest@test_time_range_7d | Last 7 days events shown |
| SecurityTimelineTest@test_user_filter | Events filtered by user across both types |
| SecurityTimelineTest@test_login_only_permission | Only login events shown |
| SecurityTimelineTest@test_activity_only_permission | Only activity events shown |
| SecurityTimelineTest@test_no_permission | Widget not rendered |

### Browser

| Test | What it validates |
|------|------------------|
| SecurityTimelineBrowserTest@test_event_click_navigates | Click event → correct URL |
| SecurityTimelineBrowserTest@test_polling_updates | New event appears within 60s |
| SecurityTimelineBrowserTest@test_filter_interaction | Filter changes update displayed events |

### Permission

| Test | What it validates |
|------|------------------|
| PermissionTest@test_gate_login_audit_login_only | User with login-only access sees login events |
| PermissionTest@test_gate_activity_only | User with activity-only access sees activity events |
| PermissionTest@test_gate_neither | User without either access sees nothing |

### Performance

| Test | What it validates |
|------|------------------|
| PerformanceTest@test_timeline_under_500ms | Response time < 500ms |
| PerformanceTest@test_timeline_query_count_under_5 | Maximum 5 queries |
| PerformanceTest@test_activity_log_created_at_index | EXPLAIN shows index usage |

### Regression

| Test | What it validates |
|------|------------------|
| Existing LoginAuditControllerTest | Login audit pages unchanged |
| Existing ActivityLogTest | Activity log pages unchanged |

### Security

| Test | What it validates |
|------|------------------|
| SecurityTest@test_no_ip_in_activity_events | Activity events never contain IP |
| SecurityTest@test_event_filter_no_user_enumeration | "No events" is identical whether user exists or not |

---

## 15. Rollback

### How this feature can be disabled

**Method 1: Feature flag**
- `FEATURE_SECURITY_TIMELINE=false` → remove widget from dashboard
- In Livewire component mount: conditional return

**Method 2: Remove from dashboard**
- Remove `@livewire('security-timeline')` from security dashboard view
- Deploy view-only change

### Rollback order

1. Set `FEATURE_SECURITY_TIMELINE=false` (zero downtime)
2. Remove from dashboard (next deploy)

### Rollback verification

- Security Officer returns to manual 4-page circuit (original behavior)
- No data loss — timeline was read-only

---

## 16. Future Evolution

### v1.2

- Event detail panel (inline expansion, not page navigation)
- Bookmarks: flag a sequence of events for investigation
- Export timeline as CSV/PDF for compliance reporting
- IP filter: show all events from a specific IP address
- Session grouping: group events by session_id (requires schema addition from v1)

### v2

- Anomaly detection: highlight unusual patterns (failed login + permission change in 5 minutes)
- Automated investigation: "Show me similar patterns from last 30 days"
- Real-time Slack/email alerts on specific patterns
- Watchlist: monitor specific users or IPs with push notification on new events
- Threat intelligence integration: compare login IPs against known threat feeds
- Compliance timeline: filter events relevant to SOC2, SOX, or GDPR requirements
