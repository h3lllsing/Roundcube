# ENTERPRISE FEATURE SPECIFICATION

## Feature: Quick Provision

Version: 1.0
Status: DRAFT
Priority: P4

---

## 1. Purpose

### Why does this feature exist?

IT Operators provisioning a new service must submit 6 disconnected forms (Provider → Service → DomainEmail → Credential → ExpiryTracker → Task). No data flows between forms. The same name, cost, and dates are re-entered 3+ times, generating data inconsistencies that compound over time.

### Which business problem does it solve?

The first two steps (service creation + credential storage) are the most error-prone and the most frequent. Combining them into ONE form eliminates the most common data inconsistency — service name ≠ credential name — and saves 5-10 minutes per provisioning event.

### What happens if it does not exist?

Each provisioning event creates ~3 data inconsistencies (provider name spelled differently, service name ≠ vault entry name, cost entered differently in different systems). After 50 provisioning events, the database contains 150+ inconsistent references that make cross-referencing impossible and manual cleanup necessary.

---

## 2. Business Value

### Hours saved

- Current: 20-40 minutes/provisioning (6 forms)
- Quick Provision: 10-15 minutes (service + credential in one form)
- Savings: 5-10 minutes per event
- 2-5 events/week × 10 operators = 10-50 events/week
- Hours saved: 1-8 hours/week across team
- Remaining steps (expiry tracker, task): still manual MVP

### Risk reduced

- Data inconsistency: service name ≠ vault entry name → eliminated (auto-filled)
- Forgotten credential: credential creation is part of the form, not a separate step → eliminated
- Wrong credential type: vault entry type auto-suggested based on service type → reduced

### Errors reduced

- Service name ≠ credential `service_name`: currently ~30% mismatch rate → target 0%
- Forgotten credential: currently ~10% of provisions → target 0%
- Wrong provider selected: dropdown with search → reduced from manual re-typing

### Compliance impact

- No direct compliance impact. Indirect: consistent credential naming improves audit trail.

### Security impact

- POSITIVE: auto-generated passwords (optional) ensure strong passwords by default
- POSITIVE: credential is created as part of provisioning, not deferred/forgotten
- No new attack surface: uses existing creation endpoints

### Operational impact

- IT Operator provisioning time reduced by 25-50%
- Service records become consistently named from day one
- Future automation (full wizard) builds on this consistent data

---

## 3. Actors

### Exactly which personas use it?

| Persona | Access | Rationale |
|---------|--------|-----------|
| IT Operator (10 users) | Full | Primary — 2-5 provisions/week |
| Super Admin (3 users) | Full | Secondary — provisions complex services |

### Who must never see it?

| Persona | Block reason |
|---------|-------------|
| End User (450 users) | No provisioning authority |
| Service Desk (20 users) | No provisioning authority |
| Security Officer (3 users) | No provisioning authority |
| IT Manager (5 users) | No operational provisioning need |
| IT Director (1 user) | Strategic role, not operational |
| Procurement (3 users) | Procurement creates providers, not services (separate workflow) |

---

## 4. Permissions

### Exactly which permission checks occur?

**Check 1: Can create the target service type**
- Module: hostings, domains, vps, voip, or other_services
- Action: `can_create`
- Runtime: form mount / form submission
- If FAIL: form title changes to "You cannot create [service type]" (form disabled). User may still select another type.

**Check 2: Can create vault entries**
- Module: `password_vault` (slug: `password_vault`)
- Action: `can_create`
- Runtime: form mount / form submission
- If FAIL: credential section of form is hidden. Service-only creation proceeds.

**Check 3: Can create service providers (optional)**
- Module: `service_providers` (slug: `service-providers`)
- Action: `can_create`
- Runtime: when user selects "Create new provider"
- If FAIL: "Create new provider" option is not shown. User must select existing.

### Which modules are checked?

1. Service module (dynamic: hostings, domains, vps, voip, other_services)
2. `password_vault`
3. `service_providers` (when creating new)

### Which actions?

- `can_create` on all modules. No read/update/delete needed for this feature.

### Which permission has highest priority?

User must have `can_create` on AT LEAST ONE service module for the form to be usable. If user has no `can_create` on any service module, the entire form shows "You do not have permission to provision services."

### Trace runtime

```
1. HTTP request → GET /provision
2. ROUTE → ProvisionController@create
3. CONTROLLER
   → $hasServicePermission = false
   → foreach ['hostings', 'domains', 'vps', 'voip', 'other_services'] as $slug
     → if canOnModule($slug, 'create') → $hasServicePermission = true
   → abort_unless($hasServicePermission, 403)
   → return view('provision.create', [
       'canCreateVault' => canOnModule('password_vault', 'create'),
       'canCreateProvider' => canOnModule('service_providers', 'create'),
     ])
4. VIEW: provision/create.blade.php
   → Service Type selector (shows only types user can create)
   → If canCreateVault: show credential section
   → If canCreateProvider: show "Create New Provider" option
5. FORM SUBMIT → ProvisionController@store
   → Re-check all permissions (server-side)
   → DB::transaction → create service + create vault entry
```

---

## 5. Business Rules

- R1: Single form page with 3 sections: Provider Selection, Service Details, Credential.
- R2: Service Type selector at top determines which module's `can_create` is checked.
- R3: User can only select service types they have `can_create` permission for.
- R4: If user has `can_create` on only ONE service type, that type is pre-selected.
- R5: If user has `can_create` on ZERO service types, form shows "You do not have permission to provision services."
- R6: Provider dropdown lists all active ServiceProviders. "Create new" option shows inline form.
- R7: If user lacks `can_create` on `service_providers`, "Create new" option is hidden.
- R8: Service Details section shows fields dynamically based on service type selection.
- R9: Credential section appears ONLY if user has `can_create` on `password_vault`.
- R10: Credential section's `service_name` is auto-filled from Service Name field (non-editable).
- R11: Credential section's `username` is optional, defaults to service name.
- R12: Credential section's `password` has an "Auto-Generate" button that creates a 24-character random password.
- R13: Form submission wraps service creation + credential creation in a single DB transaction.
- R14: If service creation succeeds but credential creation fails, service creation is rolled back.
- R15: If credential section is hidden (no vault permission), service creation proceeds without credential.
- R16: After successful submission, user is redirected to the new service detail page.
- R17: Flash message: "Service [name] created with credential." OR "Service [name] created (no credential stored)."
- R18: Form does NOT create DomainEmail, ExpiryTracker, or Task. These remain separate workflows.
- R19: All validation is server-side. Client-side validation is UX enhancement only.
- R20: Password auto-generate creates: 24 characters, mixed case, numbers, special chars. Does NOT include ambiguous characters (O/0, I/1, l/1).

---

## 6. Data Used

### Exactly which models

| Model | Table | Usage |
|-------|-------|-------|
| App\Models\ServiceProvider | `service_providers` | Selection or creation |
| App\Models\Hosting | `hostings` | Creation target |
| App\Models\Domain | `domains` | Creation target |
| App\Models\Vps | `vps` | Creation target |
| App\Models\Voip | `voip` | Creation target |
| App\Models\OtherService | `other_services` | Creation target |
| App\Models\VaultEntry | `password_vault` | Creation target |

### Exactly which tables

1. `service_providers` (read/insert)
2. `hostings` (insert)
3. `domains` (insert)
4. `vps` (insert)
5. `voip` (insert)
6. `other_services` (insert)
7. `password_vault` (insert)

### Exactly which relationships

N/A — this feature creates independent records. No relationships are traversed (except Provider → Service for the FK assignment).

| Operation | FK Assigned | Target |
|-----------|-------------|--------|
| Create Hosting | `service_provider_id` | Selected ServiceProvider |
| Create Hosting | `user_id` | Current user |
| Create VaultEntry | `user_id` | Current user |

### Exactly which queries

**Query 1: List providers (form load)**
```php
ServiceProvider::where('status', 'active')->orderBy('name')->get(['id', 'name']);
```

**Query 2: Create provider (if new)**
```php
$provider = ServiceProvider::create([...]);
```

**Query 3: Create service**
```php
$service = Hosting::create([...]); // or Domain, Vps, Voip, OtherService
```

**Query 4: Create vault entry**
```php
VaultEntry::create([
    'user_id' => auth()->id(),
    'service_name' => $service->name,
    'username' => $data['credential_username'],
    'encrypted_password' => encrypt($data['credential_password']),
]);
```

**NEEDS REVIEW:** VaultEntry migration requires `module_id` (nullable FK). Does a new provisioning need a module_id? If the vault entry should be scoped to the service module, `module_id` must be set from the module that owns the service type. Verify whether vault module filtering requires this.

---

## 7. UI Contract

### Exactly what appears

A single-page form at `/provision` with:
1. Service Type selector (radio buttons or pills): Hosting, Domain, VPS, VoIP, SaaS
2. Provider dropdown with search + "Create new" inline form
3. Dynamic service details fields (changes per type):
   - Hosting: name, plan, domain, IP, cost, start/expiry dates
   - Domain: name, cost, start/expiry dates, auto-renew toggle
   - VPS: name, plan, IP, OS, cost, start/expiry dates
   - VoIP: name, phone number, type, cost, start/expiry dates
   - SaaS: name, service type (text), cost, start/expiry dates
4. Credential section (if permitted): auto-filled service name, username, password with Auto-Generate

### Exactly where

New route: `GET /provision` and `POST /provision`. Navigation link added to "Quick Provision" in the sidebar or as a button on the dashboard (IT Operator persona).

### Exactly when

Rendered when:
1. User has `can_create` on at least one service module
2. User navigates to `/provision` or clicks "Quick Provision"

### Exactly when hidden

- The entire page returns 403 if user has `can_create` on ZERO service modules.
- Service type options for which user lacks `can_create` are grayed out.
- Credential section hidden if user lacks `can_create` on `password_vault`.
- "Create new provider" option hidden if user lacks `can_create` on `service_providers`.

### Exactly when disabled

- Submit button disabled when form validation fails.
- Service type cannot be changed after fields are filled (to prevent data loss).
- If credential section exists, password field is required unless user explicitly toggles "No credential needed."

### No redesign

Uses existing form styling. Each field set matches the existing create form for that service type. The Quick Provision form is a subset of the full create form.

---

## 8. Runtime Flow

```
USER: Navigates to /provision or clicks "Quick Provision" button

ROUTE: GET /provision → ProvisionController@create

CONTROLLER: ProvisionController@create
  → Check can_create on at least one service module
  → Check can_create on password_vault
  → Check can_create on service_providers
  → Return view with available service types and permissions

VIEW: provision/create.blade.php
  → Render service type selector (filtered by permission)
  → Render provider dropdown
  → Render dynamic service fields
  → Render credential section (if permitted)

USER: Fills form, clicks "Create Service & Store Credential"

POST /provision → ProvisionController@store

CONTROLLER: ProvisionController@store
  → Validate request
  → Re-check all permissions (server-side)
  → DB::transaction:
    → If new provider: ServiceProvider::create
    → Dynamic model::create (Hosting/Domain/etc.)
    → If vault permitted: VaultEntry::create
  → Redirect to service detail page with flash message
```

---

## 9. Edge Cases

### Soft delete

- Provider soft-deleted: `ServiceProvider::where('status', 'active')` excludes soft-deleted. User cannot select.
- Service type model soft-deleted: not applicable (models are not soft-deleted before creation).

### Null

- Provider dropdown NULL selection: user must select a provider. Validation required.
- Cost fields NULL: allowed (cost is nullable on all models). Form does NOT require cost.
- Credential password NULL: allowed only if "No credential needed" toggle is ON.
- Service name NULL: NOT allowed. Service name is required on all service models.

### Missing provider

- No providers exist AND user cannot create providers: show "No providers available. Ask your administrator to create a provider first." Submit button disabled.

### Permission denied

- User loses `can_create` on service module between form render and submit: transaction fails with AuthorizationException. Catch and show error.
- User has `can_create` on Hosting but submits Domain type: validation rejects. User cannot submit a type they don't have permission for.

### Concurrent edit

- Two users provision the same service simultaneously: no conflict. Each creates a separate record.
- Provider deleted between form render and submit: `ServiceProvider::findOrFail` in transaction fails. Transaction rolls back. Show "Provider no longer exists."

### Deleted user

- Current user deleted mid-session: auth middleware rejects on form submission. No orphan data.

### Form abandonment

- User fills form, leaves browser open for 1 hour, returns and submits: session may expire. CSRF token may be stale. Livewire handles this gracefully. Check for `CSRF token mismatch` and redirect to login.

### Password auto-generate edge cases

- User clicks Auto-Generate, modifies password manually: manual edit overrides auto-generated. User's manual value is used.
- User clicks Auto-Generate multiple times: each click generates a NEW password, replacing previous. Only last generated value is submitted.

### Transaction rollback

- Service created, vault creation fails: transaction rolls back. Service record does not exist. User sees error message. This is CORRECT behavior — no orphaned service without credential.

### Module ID for vault entry

- VaultEntry has nullable `module_id`. Quick Provision does not set `module_id` because the vault entry is not tied to a specific module — it's a general credential for the service. Decision: leave `module_id` NULL. **NEEDS REVIEW:** If vault entries must be scoped to a module for permission filtering, set `module_id` to the service module's ID.

---

## 10. Security

### Attack vectors

**AV1: User creates service type they lack permission for**
- Vector: User manipulates form submission to create a Domain when they only have permission for Hosting
- Guard: Server-side validation checks `can_create` on the submitted service type. Not just client-side.
- Severity: LOW — permission is checked on submission

**AV2: User creates vault entry without vault create permission**
- Vector: Credential section is hidden but user sends raw POST with credential data
- Guard: Server-side check: if vault data submitted but no permission, reject entire submission
- Severity: LOW — permission is checked on submission

**AV3: Auto-generated password is predictable**
- Vector: Attacker predicts auto-generated password
- Guard: `Str::random(24)` uses cryptographically secure randomness. Not predictable.
- Severity: NONE

**AV4: Provider enumeration via dropdown**
- Vector: User without `can_read` on providers can see all providers in dropdown
- Guard: The dropdown loads all active providers. If provider list is sensitive, add `can_read` check on `service_providers` module.
- **NEEDS REVIEW:** Is the provider list considered sensitive? Currently no — providers are organizational data (vendor names), not secrets. If this changes, add a permission gate.

### Privilege escalation

**PE1: User creates a service with service_provider_id of a provider they shouldn't access**
- Provider FK is nullable and no permission check exists on which provider a service is linked to. All active providers are visible in dropdown.
- Result: No privilege escalation. Selecting a provider does not grant any additional access.

### Data leakage

**DL1: Provider dropdown reveals all providers**
- All active providers are shown. If provider count is sensitive (reveals organizational structure), add permission gating.
- Severity: LOW — provider names are typically public vendor names.

### Audit logging

- Service creation: logged by Spatie Activity Log automatically (service models use `LogsActivity` trait).
- Vault entry creation: logged by Spatie Activity Log automatically (VaultEntry model uses `LogsActivity` trait).
- No additional audit logging needed.

### Rate limiting

- Form submission: 60 requests/minute per user (standard rate limit). Prevents rapid provisioning spam.

### Sensitive actions

- Creating a service: MODERATE. Commits to ongoing cost. No additional confirmation beyond standard form validation.

### Abuse scenarios

- **Rapid provisioning to create data noise:** Rate limited (60/min). Each creation is audited. Detectable via activity log.
- **Provisioning services for offline users:** All services are owned by current user (user_id = auth()->id()). No impersonation possible.

---

## 11. Performance Budget

### Expected queries

- Form load: 1 (list providers)
- Form submit: 2-3 (create service + create vault entry, optionally create provider)
- Total: 1-4 queries

### Expected response time

- Form load: < 100ms (single query)
- Form submit: < 200ms (2-3 inserts in transaction)

### Expected memory

- < 2MB PHP memory per request

### Maximum dataset

- 500 providers in dropdown: < 50ms query. Dropdown with search handles this easily.

### Scaling concerns

- None. Single-record inserts are the fastest database operation.

---

## 12. Acceptance Criteria

| # | Criterion | Expected Result | Pass/Fail |
|---|-----------|----------------|-----------|
| AC1 | User with hosting.create sees Quick Provision form | Form renders with Hosting option selected | PASS / FAIL |
| AC2 | User without any service.create sees 403 | HTTP 403 returned | PASS / FAIL |
| AC3 | User selects Hosting, fills fields, submits | Hosting record created. Vault entry created (if permitted). | PASS / FAIL |
| AC4 | User with vault.create permission sees credential section | Credential fields visible | PASS / FAIL |
| AC5 | User without vault.create permission does NOT see credential section | Credential fields absent | PASS / FAIL |
| AC6 | Auto-generated password is 24 characters | Generated string length = 24 | PASS / FAIL |
| AC7 | Service name auto-fills vault entry service_name | Vault entry service_name = service name | PASS / FAIL |
| AC8 | Transaction rolls back when vault creation fails | No orphaned service record | PASS / FAIL |
| AC9 | "Create new provider" visible when user has provider.create permission | Option visible | PASS / FAIL |
| AC10 | "Create new provider" hidden when user lacks permission | Option hidden | PASS / FAIL |
| AC11 | Successful submission redirects to new service detail page | Redirect URL matches new service | PASS / FAIL |
| AC12 | Unsuccessful submission shows validation errors | Errors displayed inline | PASS / FAIL |
| AC13 | Service type selection filtered by permission | Only permitted types shown | PASS / FAIL |
| AC14 | Form submission rate limited to 60/min | 61st request in 1 minute blocked | PASS / FAIL |

---

## 13. Regression Checklist

| # | Behavior | How verified |
|---|----------|-------------|
| R1 | Individual service creation still works | Existing create form still functional |
| R2 | Individual vault entry creation still works | Existing vault create still functional |
| R3 | ServiceProvider CRUD unchanged | Existing provider tests |
| R4 | Service model encrypted password cast | Existing model tests |
| R5 | Module permission checks for other features | Existing permission tests |
| R6 | Activity logging for model creation | Existing activity log tests |

---

## 14. Testing

### Unit

| Test | What it validates |
|------|------------------|
| QuickProvisionTest@test_create_hosting_with_credential | Both records created in transaction |
| QuickProvisionTest@test_create_hosting_without_credential | Service created, no vault entry |
| QuickProvisionTest@test_transaction_rollback_on_failure | No orphan if vault creation fails |
| QuickProvisionTest@test_password_auto_generate_length | Generated password is 24 chars |

### Feature

| Test | What it validates |
|------|------------------|
| QuickProvisionFeatureTest@test_form_renders_with_permissions | All 5 service types shown |
| QuickProvisionFeatureTest@test_form_omits_unpermitted_types | Types without permission hidden |
| QuickProvisionFeatureTest@test_credential_section_can_create | Section visible with permission |
| QuickProvisionFeatureTest@test_credential_section_no_create | Section hidden without permission |

### Browser

| Test | What it validates |
|------|------------------|
| QuickProvisionBrowserTest@test_full_provision_flow | Navigate → fill → submit → redirect |
| QuickProvisionBrowserTest@test_auto_generate_click | Button generates new password in field |

### Permission

| Test | What it validates |
|------|------------------|
| PermissionTest@test_provision_gate_no_permission | 403 without any service.create |
| PermissionTest@test_submit_wrong_type_rejected | Type mismatch caught server-side |

### Performance

| Test | What it validates |
|------|------------------|
| PerformanceTest@test_form_load_under_100ms | Single query, fast response |

### Regression

| Test | What it validates |
|------|------------------|
| Existing HostingTest@test_create | Hosting create still works |
| Existing VaultEntryTest@test_create | Vault create still works |

### Security

| Test | What it validates |
|------|------------------|
| SecurityTest@test_csrf_protection | Form requires valid token |
| SecurityTest@test_permission_rechecked_on_submit | Server re-checks all permissions |

---

## 15. Rollback

### How this feature can be disabled

**Method 1: Feature flag**
- `FEATURE_QUICK_PROVISION=false` → hide route and navigation link
- Guard: `@if(config('features.quick-provision'))` in navigation

**Method 2: Route removal**
- Comment out `Route::get('/provision', ...)` and `Route::post('/provision', ...)` from web.php
- Deploy route change

### Rollback order

1. Set `FEATURE_QUICK_PROVISION=false` (zero downtime)
2. Remove navigation link (next deploy)
3. Remove routes (next cleanup)

### Rollback verification

- Existing service creation forms still work (HostingController@create, etc.)
- Any services created via Quick Provision remain accessible via standard detail pages
- No data loss

---

## 16. Future Evolution

### v1.2

- "Also create expiry tracker" checkbox (creates ExpiryTracker after service + credential)
- "Also create task" checkbox (creates follow-up Task)
- Domain email creation step (if service type is Domain)
- Multi-step wizard UI (instead of single form with sections)
- Provider auto-creation inline without separate permission

### v2

- Full Provisioning Wizard: 6 steps with data flow between them
- Service template system: reusable provisioning templates per department
- Approval workflow: manager must approve provisioning above cost threshold
- Integration with external systems: auto-configure DNS, hosting panel, etc.
- Provisioning analytics: time-to-provision, cost-per-provision, bottleneck identification
