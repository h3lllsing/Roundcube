# V1.1 ROI VALIDATION

> Challenge each candidate on evidence, not assumption.
> Rank by validated impact ÷ realistic effort ÷ execution risk.

---

## CANDIDATE 1: Service-Credential Link

### What exact user pain does it solve?

Service Desk spends 25-50% of their day on password resets and credential requests (HIGH_FRICTION_WORKFLOWS.md:353). Current path: identify service → navigate to service detail → navigate to Vault → search by free-text `service_name` → reveal. The pain is the HUNT — finding which vault entry belongs to which service, because vault entries use free-text `service_name` with no FK to the actual service record.

### Which personas benefit?

- **Service Desk** (20 users) — primary beneficiary. 5-20 requests/day each.
- **End User** (450 users) — secondary. Faster password resets from Service Desk.

### Which current pages/entities are involved?

- `Hosting`, `Domain`, `VPS`, `VoIP`, `OtherService` models — ALL have inline encrypted `password` fields already
- `VaultEntry` (table: `password_vault`) — has `service_name` (free text, NOT a FK)
- `Asset` is the **only** model with a `vault_entry_id` FK — services have NONE

### What breaks if we do nothing?

Nothing breaks. Service Desk continues spending 15-100 minutes/day hunting credentials. The existing inline passwords on service models are already encrypted and hidden from serialization. The question is whether the team actually uses vault entries OR inline passwords for credential sharing. If they use inline passwords, adding a vault link creates duplication. If they use the vault, the free-text `service_name` field is a usability bug that compounds daily.

### What is the smallest possible MVP?

**Option A (lightest):** Add a `vault_entry_id` nullable FK to all 5 service models. Add a "🔑 Password" button on each service detail page that reads from the vault entry (or inline password as fallback). 2-3 days.

**Option B (polymorphic, cleaner):** Add `credentialable_type` + `credentialable_id` (nullable morphs) to `vault_entries`. This way ONE vault entry can be linked to any service, and the service model doesn't need a new column. But it requires changing the vault_entry table. 2-3 days.

**Option C (no data change):** Add a "Related Vault Entries" widget on service detail pages that searches vault by `service_name` LIKE match. Requires no migration. 1 day. But fragile (name mismatches cause misses).

### What data model change is required?

**Required for MVP (any option that works):** Either:
- New FK column on 5 service tables (Option A), OR
- New morph columns on vault_entries table (Option B), OR
- No change, just a LIKE query widget (Option C)

**Required for production quality:** Data migration to backfill existing vault entries → service relationships. Estimates: 500+ vault entries, 200+ services. Manual reconciliation or heuristic matching by `service_name` text.

### What permission/security risk exists?

**CRITICAL:** The `can_reveal` flag on `module_role_permissions` table controls who can reveal passwords. Adding a direct FK link means a service detail page now exposes a vault entry. Must ensure:
- The reveal action checks `can_reveal` on the Vault module, not just `can_read` on the Service module
- The "🔑 Password" button is conditionally visible based on `can_reveal` permission
- Risk: Service Desk sees credentials they shouldn't because permission check is on the wrong module

### What test coverage is required?

1. Factory for vault_entry linked to service models
2. Test that reveal action checks vault module permission, not service module permission
3. Test that user without `can_reveal` sees no password button
4. Test migration rollback
5. Test backfill command handles name mismatches gracefully

### What could go wrong?

1. **Team doesn't use vault for service credentials** — all 5 service models already have inline `password` columns. If the team uses those instead of vault entries, linking to vault is pointless.
2. **Duplicate credential storage** — a service has BOTH an inline password AND a linked vault entry. Which is authoritative? Users will be confused.
3. **Backfill failure** — heuristic matching by `service_name` text will miss 30-50% of relationships due to naming inconsistencies. Service named "Acme Web" ≠ vault entry named "acme-website-password". Manual reconciliation will take 2-3 days beyond the code change.
4. **Permission leak** — a user who can `read` a hosting record but NOT `can_reveal` on vault might accidentally see the linked password through an AJAX endpoint that checks the wrong permission.

### Is this 2 days, 1 week, or 2 weeks realistically?

**2-3 days for Option C (no migration, no FK, just a LIKE search widget).**
**1 week for Option A or B with migration + backfill + manual reconciliation.**
**2 weeks if the team discovers they need to migrate inline passwords INTO the vault first** (i.e., they've been storing passwords inline on service models AND separately in vault, and need a canonical migration).

### Should this happen before navigation changes?

**YES.** This is a 2-day fix that eliminates 50% of Service Desk navigation time (HIGH_FRICTION_WORKFLOWS.md:356). Navigation improvements save seconds per click. This saves 2-5 minutes per credential request. Higher per-user ROI than any nav change.

---

## CANDIDATE 2: Renewal Dashboard

### What exact user pain does it solve?

IT Operator and Procurement spend 15-50 minutes/week processing renewals, navigating a 3-page triangle: Renewal → Service → Provider → Renewal. Each item requires navigating away from the renewal list, viewing the service (to check if still needed), viewing the provider (to check contract terms), then returning. No inline context.

### Which personas benefit?

- **IT Operator** (10 users) — weekly renewal processing
- **Procurement** (3 users) — monthly/quarterly cost review
- **IT Manager** (5 users) — monthly budget review
- **IT Director** (1 user) — quarterly strategic review

### Which current pages/entities are involved?

- `ExpiryTracker` (table: `expiry_trackers`) — polymorphic `trackable` links to services
- 5 service models (Hosting, Domain, VPS, VoIP, OtherService) — each has `cost`, `expiry_date`
- `ServiceProvider` — each service has `service_provider_id` FK
- Renewals page (current) — shows ExpiryTracker list without service/provider context

### What breaks if we do nothing?

Renewals continue to be processed one-at-a-time via 3-page hops. Cost review remains manual spreadsheet compilation (2-4 hours/month). Missed renewals (15-50% probability per year) cause service outages. No cost trend visibility.

### What is the smallest possible MVP?

A single Blade view (or Livewire component) that replaces the current scrap table. Join: `expiry_trackers LEFT JOIN trackable (polymorphic) -> service provider`. Remove the 3-page hop by inlining service name + provider name + cost directly in the expiry tracker table. Add a one-click "Renew" button that extends `expiry_date` by 1 year.

### What data model change is required?

**NONE.** Everything already exists:
- `ExpiryTracker` → `trackable()` morph → service model
- Service model → `serviceProvider()` → `ServiceProvider`
- All relevant columns (`cost`, `expiry_date`, `renewal_date`, `name`, `status`) already exist

The ONLY thing missing is a `last_renewed_at` timestamp if we want to track renewal history. That's an optional improvement, not required for MVP.

### What permission/security risk exists?

**LOW.** The dashboard shows cost data that is already visible on individual pages. Risk: cost aggregation makes it easier to leak pricing information across departments. Mitigation: filter by role-based module visibility (already implemented). Procurement sees all costs. IT Operator sees costs only for their services.

### What test coverage is required?

1. Test query performance with 200+ expiry records across 5 polymorphic types
2. Test that the "Renew" action logs the change in activity log
3. Test that permission checks are preserved (user sees only their accessible modules)
4. Test date boundary (expired vs expiring vs active)

### What could go wrong?

1. **Performance with polymorphic eager loading** — loading 200 ExpiryTrackers with their polymorphic `trackable` relations requires N+1 queries by default. Without `with` eager loading on all 5 model types, page load will be 5+ seconds. With eager loading, it requires pre-loading all 5 model types and filtering, which is complex.
2. **Inconsistent cost data** — `cost` is nullable `decimal(10,2)` on all service models. Estimated 30% of services have NULL cost. Dashboard would show "$0" or blank, creating confusion.
3. **Orphaned trackables** — if a service is soft-deleted but its ExpiryTracker still references it, the polymorphic relation returns null. Dashboard would show broken references.
4. **The "Renew" button is anti-climactic** — one-click renewal without review/approval means users might accidentally renew services they intended to cancel.

### Is this 2 days, 1 week, or 2 weeks realistically?

**3-5 days.** The data model requires zero migration. The work is: 1) Blade view with eager loading for polymorphic relations, 2) Livewire component for inline "Renew" action, 3) query optimization, 4) testing. The estimated 3 days in the original report is aggressive but achievable if Livewire is already in use. Realistically 5 days with testing and edge cases.

### Should this happen before navigation changes?

**YES for Procurement persona, NO for IT Operator.** Procurement's primary workflow IS renewal processing — improving it directly serves their daily work. IT Operator benefits incidentally. But the effort is 3-5 days vs 1+ week for nav changes, so it should precede nav work by timeline alone.

---

## CANDIDATE 3: Security Timeline

### What exact user pain does it solve?

Security Officer must cross-reference 4 separate pages (Login Audit, Activity Log, Credential Access Log, User Management) to investigate a potential breach. No unified timeline means correlation is manual and mental. An attacker's path — failed login → successful login → permission escalation → credential access → data exfiltration — appears as disconnected events across 4 pages.

### Which personas benefit?

- **Security Officer** (3 users) — primary. Daily security review is their core workflow.
- **Super Admin** (3 users) — secondary. Investigates user behavior.
- **IT Manager** (5 users) — tertiary. Reviews team activity.

### Which current pages/entities are involved?

- `LoginAudit` (table: `login_audits`) — `user_id`, `email`, `ip_address`, `event`, `created_at`
- `Activity` (Spatie table: `activity_log`) — `causer_id` (morph), `subject_type`/`subject_id` (morph), `event`, `description`, `created_at`
- Vault access is logged via activity log (`VaultEntry` model uses `LogsActivity`)
- User management changes are logged via activity log

### What breaks if we do nothing?

Security reviews remain manual and incomplete. Evidence shows correlated attacks go undetected (HIGH_FRICTION_WORKFLOWS.md:239). A single breach costs 1000x more than the tooling. But: the current frequency is 1-3 security reviews per month (not daily). The risk is catastrophic but rare.

### What is the smallest possible MVP?

A single Blade view that queries both `login_audits` and `activity_log` tables, merges by `created_at` DESC, and filters by user. No data model change. No new table. Just a SELECT from two tables unioned by time, with an inline filter for user_id. Add a "View Timeline" button to user detail page.

### What data model change is required?

**NONE.** Both tables exist with proper timestamps and user associations.
- `login_audits` has `user_id` FK + `created_at` index
- `activity_log` has `causer_id` morph + `created_at`
- The only missing piece: `login_audits` does not have a `session_id` or `request_id` to group events from the same browser session. Adding this is nice-to-have but not required for MVP.

### What permission/security risk exists?

**MODERATE.** The timeline merges sensitive login data AND activity data. Any user who can view the timeline sees the combined picture. Current permission model gates LoginAudit and ActivityLog separately. A combined view bypasses this separation.

Risk: A Service Desk user who has `can_read` on Activity Log but NOT on LoginAudit would see login data through the timeline that they shouldn't.

Mitigation: Apply the STRICTEST permission check — user must have `can_read` on BOTH modules to see the timeline. Or implement a separate permission flag.

### What test coverage is required?

1. Test union query with pagination performs under 200ms
2. Test permission gating — user without login_audit access sees filtered timeline
3. Test that activity log `causer_id` morph mapping resolves correctly to User model
4. Test time-range filtering
5. Test that soft-deleted users still show their historical events

### What could go wrong?

1. **Query performance** — `activity_log` can grow to 100K+ rows rapidly (every model change creates a log entry). Union with `login_audits` (10K+ rows) without proper pagination and indexing will time out.
2. **Schema mismatch** — `login_audits` uses `email` (string), `activity_log` uses `causer_id` (int morph). Joining by user requires the query to resolve: `login_audit.user_id = activity_log.causer_id AND activity_log.causer_type = 'App\Models\User'`. This is a non-trivial query.
3. **Activity log is Spatie-managed** — modifying the activity_log table structure is risky (Spatie updates may conflict). The timeline must work with the existing schema.
4. **False correlation** — not all events from the same user in a time window are related. The timeline implies correlation that might not exist, leading to false conclusions.

### Is this 2 days, 1 week, or 2 weeks realistically?

**1 week.** No data model changes needed. The work is: 1) write the union query with pagination, 2) build the timeline UI with time-based grouping, 3) permission gating, 4) performance optimization, 5) testing. This is a pure presentation-layer feature leveraging existing data. The query complexity is the main risk.

### Should this happen before navigation changes?

**YES, but with caveat.** The Security Officer persona uses the system daily for security review. A timeline directly improves their core workflow. However, the risk is moderate (combined data exposure) and the query complexity is non-trivial. Safe to implement after Service-Credential Link but before nav changes.

---

## CANDIDATE 4: Provisioning Wizard

### What exact user pain does it solve?

IT Operator provisioning a new service must submit 6 disconnected forms (Provider → Service → DomainEmail → Credential → ExpiryTracker → Task) across 3+ nav groups, re-entering the same data (name, cost, dates) at each step. The process takes 20-40 minutes and consistently produces data inconsistencies (provider name spelled differently across entities).

### Which personas benefit?

- **IT Operator** (10 users) — primary. 2-5 provisioning events/week.
- **Procurement** (3 users) — secondary. Vendor onboarding includes similar multi-step process.

### Which current pages/entities are involved?

1. `ServiceProvider` — create or select provider
2. `Hosting`/`Domain`/`VPS`/`VoIP`/`OtherService` — create the service
3. `DomainEmail` — create mailboxes (if domain service)
4. `VaultEntry` — store credential
5. `ExpiryTracker` — set renewal reminder
6. `Task` — create setup task

### What breaks if we do nothing?

Data inconsistency continues to compound. A service named "Acme Web" in hosting is "acme-website" in vault and "acme-web-hosting" in expiry tracker. Three different spellings = three different records = no cross-referencing possible. Credentials get lost. Renewals get missed. Each provisioning event creates data debt that costs 10x more to clean up later.

### What is the smallest possible MVP?

**NOT a 6-step wizard.** That's too much risk for MVP. Instead:

A "Quick Provision" form that combines JUST the service creation + credential storage into ONE form:
1. Select provider (existing dropdown)
2. Fill service details (name, type, plan, cost, dates)
3. Fill credential (username, password auto-generate toggle)
4. Submit — creates service AND vault entry IN ONE TRANSACTION, with vault entry's `service_name` auto-filled from service name

This covers the 2 highest-pain steps (service + credential) and eliminates the most common data inconsistency (service name ≠ credential name). Expiry tracker and task creation can remain separate for MVP.

### What data model change is required?

**NONE for MVP** (no FK linking needed if vault entry just gets auto-filled `service_name`).
**REQUIRED for production wizard** (FK from service → vault entry, see Candidate 1 analysis).

### What permission/security risk exists?

**MODERATE.** The wizard creates multiple entities. If the user has `can_create` on Hosting but NOT on VaultEntry, the wizard fails mid-transaction. Need either:
1. Pre-check all permissions before starting wizard (user sees "You cannot create credentials" and wizard is blocked)
2. Transaction with rollback on any failure
3. Partial wizard (create what you can, leave rest as manual steps)

### What test coverage is required?

1. Test submission creates all entities in correct order
2. Test permission pre-check blocks unauthorized users
3. Test transaction rollback on partial failure
4. Test auto-generated password meets strength requirements
5. Test that service name propagates correctly to vault entry

### What could go wrong?

1. **Over-engineering risk** — the 7-step wizard in AUTOMATION_OPPORTUNITIES.md is aspirational but dangerous. First version will miss edge cases. Users will get stuck and abandon mid-wizard, creating orphaned records. Start with "Quick Provision" (2 steps), iterate to full wizard.
2. **Abandonment** — user starts wizard, gets interrupted, doesn't finish. Partial data is created with no completion signal. How do we handle partial wizards?
3. **Provider selection UX** — there are 50+ providers. Dropdown with search is essential. Without it, users will still create duplicate providers.
4. **The wizard doesn't match the user's mental model** — IT Operators think in terms of "I need a server for project X," not "Step 1: Provider, Step 2: Hosting details." If the wizard steps don't match their workflow, they'll revert to the 6 separate forms.
5. **Cost/benefit ratio** — At 2-5 provisions/week per IT Ops, the 20-40 min current time represents 1-3 hours/week saved. That's 10-30 hours/week for 10 operators. Significant, but requires the wizard to actually be faster than current CRUD (which users are already fast at through muscle memory).

### Is this 2 days, 1 week, or 2 weeks realistically?

**1 week for "Quick Provision" MVP** (2-step: service + credential in one form).
**2-3 weeks for full wizard** (6 steps + data flow + transaction management + partial completion handling).

### Should this happen before navigation changes?

**YES for Quick Provision MVP (1 week).** This directly addresses the #2 highest-friction workflow. The MVP is low-risk (just combines two existing forms) and delivers measurable time savings. The full wizard should be deferred to v1.2 after validation.

---

## CANDIDATE 5: Offboarding Dashboard/Wizard

### What exact user pain does it solve?

Employee offboarding requires Security Officer to manually visit 5+ pages (Users → Credentials → Tasks → Assets → Activity Log), remembering every step with no checklist. Missed credential revocation is the #1 insider threat vector (HIGH_FRICTION_WORKFLOWS.md:43). The 15-30 minute process has a 100% probability of human error in at least one step (based on 5+ steps × imperfect memory).

### Which personas benefit?

- **Security Officer** (3 users) — primary. Owns offboarding.
- **Super Admin** (3 users) — secondary. Executes offboarding when Security Officer is unavailable.

### Which current pages/entities are involved?

- `User` — suspend account, review role
- `VaultEntry` — `user_id` FK reveals all credentials owned by this user. But: credential ACCESS (not ownership) is the risk — who has been granted access to shared vault entries? This is NOT queryable through the current model. The VaultEntry model has `user_id` (owner) but no `accessors` or `shared_with` relationship.
- `Task` — `assignees()` pivot `task_user` reveals tasks assigned to user
- `Asset` — `assigned_to` FK reveals assets checked out to user
- `Activity` — `causer_id` morph reveals user's recent actions
- Service models — each has `user_id` FK (owner/creator)

### What breaks if we do nothing?

**SECURITY BREACH.** Every missed offboarding step is a potential incident. Compliance auditors will flag the process as uncontrolled. The risk is extreme (W4/W13 in WORKFLOW_VALUE_ANALYSIS.md). But: the frequency is low (1-2/week). The cost of failure ranges from reputation damage (missed asset) to breach (missed credential revoke).

### What is the smallest possible MVP?

NOT a dashboard. Even simpler:

A "Checklist" widget on the User detail page that shows:
```
Offboarding Checklist for [User]:
□ Suspend account
□ Revoke credentials (X found)
□ Reassign tasks (X found)
□ Check in assets (X found)
□ Review activity (X found)
[View Details →]
```

This is a READ-ONLY information panel. No revoke button. No reassign button. Just shows what needs to be done and hyperlinks to each section where the action can be taken manually. This reduces the "am I forgetting something?" risk without ANY complex transactional logic.

### What data model change is required?

**NONE for MVP checklist** — all counts come from existing relationships:
- `User.vaultEntries()` — count of owned vault entries
- `User.tasks()` — count of tasks where user is assignee
- `Asset.where('assigned_to', $user->id)` — count of assigned assets
- `Activity.where('causer_id', $user->id)` — count of recent activities

**REQUIRED for full dashboard with revoke actions:** Need a `credential_user` pivot table or `shared_with` relationship on VaultEntry to know WHICH credentials the user has ACCESS to (not just owns). The current model only tracks credential OWNERSHIP (`user_id` on `vault_entries`), not credential SHARING. Without this, the "Revoke Credentials" step misses the most important credentials (shared team passwords).

### What permission/security risk exists?

**EXTREME.** This feature performs destructive actions (suspending accounts, revoking access, reassigning work). A compromised Security Officer account with this dashboard could:
1. Offboard the entire IT team in 5 minutes
2. Revoke ALL credentials (not just the user's)
3. Reassign all tasks to one person (DoS)

Mitigation requirements:
- **Two-person rule**: Offboarding requires confirmation from a second authorized user
- **Audit trail**: Every offboarding action must be logged with timestamp + actor
- **Soft offboarding**: 24-hour delay before irreversible actions (account suspension is immediate, credential revocations are queued)
- **Rate limiting**: Max 3 offboardings per hour per user

### What test coverage is required?

1. Test checklist counts match actual relationship counts
2. Test each action button performs the correct database operation
3. Test rollback on partial failure (credential revoke succeeds, asset reassign fails)
4. Test that soft-deleted users can be restored
5. Test that revoked credentials are logged in activity log
6. Test rate limiting
7. Test two-person confirmation (if implemented)

### What could go wrong?

1. **Missing shared credentials** — the MVP checklist shows vault entries the user OWNS, but not vault entries they can ACCESS (shared with their role or directly). The most dangerous missed credential is a shared admin password that the user had access to — NOT their personal vault. Fixing this requires a `credential_user` pivot table that doesn't exist yet.
2. **Reversal is hard** — once a user is offboarded and credentials are revoked, bringing them back is a manual process. If the offboarding was done in error (wrong person, wrong date), there's no "undo."
3. **Checklist fatigue** — if the checklist always shows "5 things to do" for every offboarding, users will start skipping steps. The checklist needs to track completion state.
4. **Asset tracking gap** — assets are tracked with `assigned_to` FK on `assets` table. But there are currently ~50 assets for 500 users. Most organizations have more assets than this. The current asset tracking may not be comprehensive enough for the checklist to be reliable.
5. **The offboarding dashboard encourages centralized offboarding** — which is good for compliance but bad for security (single point of failure). If the one person with dashboard access is on vacation, offboarding doesn't happen.

### Is this 2 days, 1 week, or 2 weeks realistically?

**3 days for read-only checklist MVP** (counts + links, no actions).
**2 weeks for full dashboard with revoke actions** (needs `credential_user` pivot table + transactional actions + audit trail + rate limiting + testing).

### Should this happen before navigation changes?

**YES for read-only checklist (3 days).** This solves the "am I forgetting something?" anxiety with zero transactional risk. Users still navigate to each section to perform actions, but they don't forget steps.

**Only implement revoke actions AFTER Service-Credential Link is done**, because both need the credential→service relationship to be solid. Without it, the offboarding dashboard revokes credentials but can't show WHICH SERVICES the user loses access to — reducing confidence.

---

## FINAL RANKING BY VALIDATED EVIDENCE

| Rank | Candidate | Realistic Effort | User Impact | Risk | Evidence Confidence |
|------|-----------|-----------------|-------------|------|-------------------|
| **1** | Offboarding Checklist (MVP) | **3 days** | ★★★★★ | MED | HIGH — all counts exist via current relationships |
| **2** | Service-Credential Link | **2-5 days** | ★★★★☆ | HIGH (permissions) | MED — unclear if team uses vault or inline passwords |
| **3** | Renewal Dashboard | **3-5 days** | ★★★★☆ | LOW | HIGH — all data exists, no migration needed |
| **4** | Provisioning Quick Provision | **1 week** | ★★★★☆ | MOD (transactions) | MED — abandonment risk is real |
| **5** | Security Timeline | **1 week** | ★★★☆☆ | MOD (data exposure) | HIGH — both data sources exist and are indexed |

### Key Validated Findings

1. **Offboarding Checklist MVP ranks #1** because it requires ZERO data model changes, ZERO transactional logic, and solves the #1 anxiety (forgetting steps) with a 3-day read-only widget. All counts are queryable through existing relationships.

2. **Service-Credential Link has a hidden dependency**: The team may already use inline `password` columns on service models (Hosting, VPS, VoIP, OtherService all have encrypted `password` fields). If so, the "pain" of credential hunting may be overstated — the password is already on the service detail page (just hidden from serialization, readable in PHP). Need to validate whether the team uses vault entries OR inline passwords for Service Desk workflows before building the link.

3. **Renewal Dashboard is the safest bet**: Zero data model risk, zero permission risk, highest confidence in effort estimation (3-5 days). The only real risk is polymorphic query performance, which is a known pattern in Laravel.

4. **Provisioning "Quick Provision" (2 steps) should replace "Full Wizard"**: A 6-step wizard is aspirational. A 2-step form (service details + credential in one transaction) is the practical MVP. Full wizard deferred.

5. **Security Timeline has the most fragile query logic**: Merging two structurally different tables (login_audits and activity_log) by user + time is deceptively complex. The query will need careful indexing and pagination.

### What We Do NOT Know (Blocking Confidence)

1. **Are vault entries actually used for service credentials?** The service models have inline `password` columns. If the team uses those, the vault is for personal passwords only and the "Service-Credential Link" solves a non-existent problem.

2. **What is the actual offboarding rate?** Estimated at 1-2/week. If closer to 1/month, the ROI drops significantly.

3. **What is the provisioning rate per operator?** Estimated 2-5/week. If closer to 1/week, the ROI halves.

4. **Do polymorphic eager loads work at scale?** With 200+ ExpiryTrackers across 5 model types, the N+1 problem could make the Renewal Dashboard unusable without query optimization.
