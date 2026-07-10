# ENTERPRISE FEATURE SPECIFICATION

## Feature: Service-Credential Auto-Copy

Version: 1.0
Status: DRAFT
Priority: P2

---

## 1. Purpose

### Why does this feature exist?

Service Desk spends 25-50% of daily time on password resets and credential requests. Each request requires navigating to the service record, then separately navigating to the Vault to find the corresponding credential. The credential may not exist in the Vault at all — passwords are already stored inline on each service model (Hosting, Domain, VPS, VoIP, OtherService all have `password` columns). The copy button makes the inline password accessible with one click instead of requiring the user to navigate away, find the credential, and return.

### Which business problem does it solve?

Credential retrieval time: 2-5 minutes per request reduced to < 10 seconds. The Vault entry `service_name` is free text — no FK binds it to the service record, making credential hunting unreliable.

### What happens if it does not exist?

Service Desk continues spending 15-100 minutes/day on password resets. Credentials remain hidden behind a separate navigation path. Users wait longer for password support. Each minute spent hunting = 1 minute of lost user productivity × 450 users.

---

## 2. Business Value

### Hours saved

- 3-5 min/request → 10 seconds
- 5-20 requests/day/Service Desk × 20 users = 100-400 requests/day
- Hours saved: 8-32 hours/day (aggregate across all Service Desk users)
- BUT: not every request is for a service with inline password. Estimated: 50% of requests are for services with inline passwords stored
- Adjusted savings: 4-16 hours/day

### Risk reduced

- Credential hunting errors: wrong credential revealed to user → password leak
- Reduced: navigation errors (wrong vault entry selected due to similar names)
- Introduced: none — the inline password is already on the service record, just hidden from serialization. The copy button exposes what is already there.

### Errors reduced

- Wrong credential selection: currently 5-10% probability per request (similar `service_name` in vault)
- Copy button eliminates selection: probability 0%

### Compliance impact

- Password reveal is already audited via activity log (if reveal action logs it)
- NEEDS REVIEW: verify whether existing password reveal on service models is logged. If not, the copy/reveal action must add activity logging.

### Security impact

- POSITIVE: users get credentials faster, reducing shadow-IT password sharing
- NEGATIVE: one-click copy makes bulk extraction easier if account is compromised
- Mitigation: copy action must be logged in activity log with: actor, service, timestamp

### Operational impact

- Service Desk throughput increases by 25-50%
- User wait time for password support drops from minutes to seconds
- First-call resolution rate improves (no callbacks for "I still can't log in")

---

## 3. Actors

### Exactly which personas use it?

| Persona | Access | Rationale |
|---------|--------|-----------|
| Service Desk (20 users) | Full | Primary — handles 100+ password requests/day |
| IT Operator (10 users) | Full | Secondary — needs passwords for provisioning and incident response |
| Security Officer (3 users) | Full | Tertiary — needs credential access during investigations |
| Super Admin (3 users) | Full | Tertiary — full system access includes password management |

### Who must never see it?

| Persona | Block reason |
|---------|-------------|
| End User (450 users) | Must not see other users' credentials. End users see only their OWN vault entries, not service inline passwords. The copy button is on service detail pages which are gated by module permissions — End Users typically lack `can_read` on service modules. |
| Procurement (3 users) | No business need for service passwords. Procurement reads cost data, not credentials. |
| IT Manager (5 users) | No operational need. Password access is not a management function. |
| IT Director (1 user) | No operational need. |

---

## 4. Permissions

### Exactly which permission checks occur?

**Check 1: Can view the service detail page**
- Module: depends on service type (hostings, domains, vps, voip, other_services)
- Action: `can_read`
- Runtime: controller
- If FAIL: 403 — user does not reach the service detail page. Copy button never renders.

**Check 2: Can REVEAL the password**
- Module: `password_vault` (slug: `password_vault`)
- Action: `can_reveal`
- Runtime: Livewire component or controller action for the copy/reveal button
- If FAIL: Copy/reveal button is NOT rendered. User sees masked password field only.
- THIS IS THE CRITICAL CHECK. It must reference the VAULT module, not the service module.

**Check 3: Can read the specific service record**
- Module: hostings / domains / vps / voip / other_services (whichever applies)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: fallback — this is redundant with Check 1 (controller gate). But redundant check in Livewire is defensive.

### Which modules are checked?

1. Service-specific module (hostings, domains, vps, voip, or other_services)
2. `password_vault` (for reveal permission)

### Which actions?

- `can_read` on service module
- `can_reveal` on password_vault module

### Which permission has highest priority?

`can_reveal` on `password_vault` is THE authoritative check. The service `can_read` is a prerequisite (you must reach the page), but `can_reveal` on vault is what gates the password. The reveal permission ALWAYS references the vault module, REGARDLESS of which service module the user is on.

### Trace runtime

```
1. HTTP request → GET /hostings/{hosting} (or /domains/{domain}, etc.)
2. ROUTE → Controller
3. CONTROLLER → Check: auth()->user()->canOnModule($serviceModule, 'read')
4.   → FAIL: 403
5.   → PASS: Load model, return view
6. VIEW → Renders service detail with inline password field
7.   → @if(auth()->user()->canOnModule($vaultModule, 'reveal'))
8.     → Render "[Copy]" and "[Reveal]" buttons next to password
9.   → @else
10.    → Render "[••••••••]" masked only. No buttons.
11.  → @endif
12. USER clicks "Copy" → JavaScript copies password to clipboard
13. USER clicks "Reveal" → AJAX/Livewire action returns decrypted password
14.   → Server re-checks canOnModule($vaultModule, 'reveal')
15.   → Logs reveal to activity_log
16.   → Returns decrypted text
17.   → JavaScript displays password (3 second auto-hide)
```

---

## 5. Business Rules

- R1: "Copy" button appears ONLY when the current user has `can_reveal` on the `password_vault` module.
- R2: "Reveal" button appears ONLY when the current user has `can_reveal` on the `password_vault` module.
- R3: Both buttons reference the service model's inline `password` attribute, NOT a vault entry.
- R4: "Copy" button copies the decrypted password to clipboard using `navigator.clipboard.writeText()`.
- R5: "Reveal" button makes an AJAX request to fetch the decrypted password.
- R6: Revealed password auto-hides after 3 seconds (JavaScript timeout).
- R7: "Copy" action MUST be logged to activity_log with event = `password_copied`.
- R8: "Reveal" action MUST be logged to activity_log with event = `password_revealed`.
- R9: Both activity_log entries include: `subject` = the service model, `causer` = current user, `properties` = `['service_name' => $model->name, 'service_type' => get_class($model)]`.
- R10: The inline `password` field is encrypted (Laravel `encrypted` cast on all 5 service models). The controller/service decrypts it before returning.
- R11: Password must never appear in plaintext in HTML source (must be server-rendered as `[••••••••]` and only decrypted via AJAX).
- R12: Copy button must NOT fetch the password from server. It uses the already-decrypted value from the Reveal action.
- R13: If no inline password exists (NULL), show "No password stored" instead of masked value. Buttons do not render.
- R14: Copy button is a livewire action or AlpineJS method, NOT a plain HTML form (to avoid CSRF/clickjacking).
- R15: Password field shows masked value by default: "••••••••" (irrespective of actual password length).
- R16: This feature applies to ALL 5 service model types: Hosting, Domain, VPS, VoIP, OtherService.
- R17: Service inline `password` is the authoritative password. The Vault is a separate storage for shared credentials. No sync between the two.

---

## 6. Data Used

### Exactly which models

| Model | Table | Usage |
|-------|-------|-------|
| App\Models\Hosting | `hostings` | Read `password` (encrypted cast). Apply to hosting detail page. |
| App\Models\Domain | `domains` | Read `password`. NOT all domains have passwords — `password` column may not exist on Domain model. NEEDS REVIEW. |
| App\Models\Vps | `vps` | Read `password` (encrypted cast). Apply to VPS detail page. |
| App\Models\Voip | `voip` | Read `password` (encrypted cast). Apply to VoIP detail page. |
| App\Models\OtherService | `other_services` | Read `password` (encrypted cast). Apply to SaaS detail page. |

**NEEDS REVIEW:** The Domain migration shows NO `password` column. The columns on `domains` table are: `user_id`, `module_id`, `hosting_id`, `service_provider_id`, `name`, `registration_date`, `expiry_date`, `auto_renew`, `cost`, `status`, `cloudflare_status`, `dns_servers`, `notes`, `monitoring_url`, `last_ping_at`, timestamps. The Domain model does NOT have an inline password. Auto-Copy does NOT apply to Domain records.

Confirmed from migration: Domain has NO `password` column.

### Exactly which tables

1. `hostings`
2. `vps`
3. `voip`
4. `other_services`

(Not `domains` — Domain model has no inline password column.)

### Exactly which relationships

N/A — the password is a column on each service model, not a relationship.

### Exactly which queries

**Query: Read password from service model**
```php
// Hosting example:
$hosting = Hosting::findOrFail($id);
$password = decrypt($hosting->password); // encrypted cast handles decryption
```

The `password` column on each model is defined with an `encrypted` cast:
```php
// model casts (verified from source):
protected function casts(): array
{
    return [
        'password' => 'encrypted',
        // ... other casts
    ];
}
```

The Laravel `encrypted` cast automatically encrypts on save and decrypts on read. Reading `$model->password` returns the plaintext. No additional decrypt call is needed.

**NEEDS REVIEW:**
- Hosting `password` cast: confirmed `encrypted`
- VPS `password` cast: confirmed `encrypted`
- VoIP `password` cast: confirmed `encrypted`
- OtherService `password` cast: confirmed `encrypted`
- Domain `password`: COLUMN DOES NOT EXIST

### No guesses

All columns verified from migrations:
- Hosting: `$table->string('password')->nullable()->after('username');` — migration `2026_05_24_054201_create_hostings_table.php`
- VPS: `$table->string('password', 255)->nullable();` — migration `2026_05_24_054202_create_vps_table.php`
- VoIP: `$table->string('password', 255)->nullable();` — migration `2026_05_24_070001_create_voip_table.php`
- OtherService: `$table->string('password', 255)->nullable();` — migration `2026_05_24_070004_create_other_services_table.php`
- Domain: NO password column — confirmed from all domain migrations

---

## 7. UI Contract

### Exactly what appears

A password field row on each service detail page showing:
- Label: "Password"
- Masked value: "••••••••" (if password exists)
- Two buttons: "Copy" and "Reveal" (if user has `can_reveal` on vault module)
- OR: "No password stored" text (if password is NULL)

### Exactly where

On the service detail page (`/hostings/{hosting}`, `/vps/{vps}`, `/voip/{voip}`, `/other-services/{otherService}`), in the details table/card, alongside other read-only fields (name, plan, cost, URL).

### Exactly when

Rendered when:
1. User has `can_read` on the service module (reaches the detail page)
2. Password column is NOT NULL

### Exactly when hidden

Password row is NOT rendered when:
1. Password column is NULL on the model

Copy and Reveal buttons are NOT rendered when:
1. User lacks `can_reveal` on `password_vault` module
2. Password column is NULL

### Exactly when disabled

- Copy button: never disabled (clipboard API is synchronous)
- Reveal button: disabled briefly during AJAX request (to prevent double-click)
- Both buttons: disabled if password is NULL (in which case they're not rendered)

### No redesign

The service detail page layout is preserved. The password field already exists in the detail view — it's currently masked. This feature adds interactive buttons to the existing field. The buttons use existing button styling (same as other action buttons on the page).

---

## 8. Runtime Flow

```
USER: IT Operator navigates to /hostings/{hosting}

ROUTE: GET /hostings/{hosting}
  → web.php: Route::resource('hostings', HostingController::class)
  → Route::get('/hostings/{hosting}', [HostingController::class, 'show'])->middleware('auth')

CONTROLLER: HostingController@show
  → $hosting = Hosting::findOrFail($id)
  → abort_unless(auth()->user()->canOnModule($hostingModule, 'read'), 403)
  → return view('hostings.show', compact('hosting'))

PERMISSION: HasModulePermissions@canOnModule
  → Loads Module model where slug = 'hostings'
  → Checks UserModulePermission or ModuleRolePermission
  → Returns bool

VIEW: hostings/show.blade.php
  → Renders detail card
  → Renders password field row
    → @if($hosting->password)
      → <span>••••••••</span>
      → @can('reveal', 'password_vault')  // NEEDS REVIEW: Blade @can directive may not work with module permissions
        → <button wire:click="copyPassword">Copy</button>
        → <button wire:click="revealPassword">Reveal</button>
      → @endcan
    → @else
      → <span>No password stored</span>
    → @endif

LIVEWIRE: PasswordActions component (or inline)
  → copyPassword()
    → re-check canOnModule($vaultModule, 'reveal')
    → if fail: return error (user lost permission)
    → if pass:
      → activity()->causedBy(auth()->user())->performedOn($service)->event('password_copied')->log(...)
      → return encrypted password

JAVASCRIPT:
  → Reveal button: Livewire call → gets decrypted password → shows in plaintext
  → Copy button: Livewire call → gets decrypted password → clipboard API → shows tooltip "Copied!"
  → Auto-hide password after 3 seconds
```

**NEEDS REVIEW:** The `@can` directive in Blade works with Laravel's Gate/Policy system. The codebase uses `canOnModule()` from HasModulePermissions trait. There is NO Gate registered for `reveal` on `password_vault`. The view must use:
```php
@if(auth()->user()->canOnModule($vaultModule, 'reveal'))
```
NOT:
```php
@can('reveal', 'password_vault')
```
The `@can` directive requires a registered Gate or Policy. Neither exists for this permission.

---

## 9. Edge Cases

### Soft delete

- Service model is soft-deleted: route returns 404 via `Hosting::findOrFail()`. Feature is inaccessible. CORRECT.
- Service model restored: feature works again. CORRECT.

### Null

- `password` is NULL: show "No password stored". No buttons. This is the most likely edge case — not all services have passwords stored.
- `password` is empty string: treat same as NULL. Show "No password stored".
- `password` is malformed (decryption fails): catch decryption exception. Show "Error reading password". Log error. Do not crash page.

### Missing provider

N/A — this feature uses the service model's inline password, not ServiceProvider.

### Permission denied

- User loses `can_reveal` between page load and click: Livewire action re-checks. Returns error gracefully.
- User with `can_read` on service but NOT `can_reveal`: sees masked password. No buttons. CORRECT.

### Concurrent edit

- Two users viewing same service detail page simultaneously: no conflict. Read-only.
- One user updates the service password while another is looking at the page: the viewer sees stale masked password but the Reveal action reads the latest from database. CORRECT.

### Deleted user

- Current user deleted mid-session (session still valid): Livewire action checks `auth()->user()`. If user is null, abort. This is a Laravel auth middleware concern, not specific to this feature.
- Target user (not applicable — no target user in this feature).

### Hardware limits

- 5000 hosting records, 3000 VPS records: all are single-record reads by ID. Indexed. Always < 10ms.

### Cross-model consistency

- Password on service model vs password in vault entry: these are independent. The feature reads from the service model's inline password. The vault entry may have a different password. This is BY DESIGN — the inline password is for the service's own control panel, the vault entry is for shared team passwords.

### Encrypted cast edge cases

- If `password` column contains already-plaintext data (migration defaulted to string, not encrypted cast historically): the encrypted cast will try to `decrypt()` and fail. This will throw a runtime exception on model load.
- **NEEDS REVIEW:** Verify that all existing `password` values in the database were written through the `encrypted` cast. If the database was populated before the cast was added, existing passwords are plaintext and will break on read.
- Mitigation: Add a migration to re-encrypt any plaintext passwords, OR add a custom accessor that tries decrypt and falls back to plaintext.

### Domain exclusion

- Domain detail page: this feature does NOT apply. The Domain model has no `password` column. Attempting to access `$domain->password` will throw `Illuminate\Database\Eloquent\MissingAttributeException` (or return null if not strict).
- Decision: explicitly exclude Domain from this feature. Do not attempt to show password on Domain detail pages.

---

## 10. Security

### Attack vectors

**AV1: Bulk password extraction via automated requests**
- Vector: Compromised Service Desk account makes 1000 Reveal API calls in sequence, extracting all service passwords
- Mitigation: Rate limit Reveal action to 30 requests/minute per user
- Code location: `app/Http/Livewire/PasswordActions.php` reveal action
- Severity: HIGH — one compromised Service Desk account can dump all service passwords

**AV2: Clipboard snooping**
- Vector: Malicious browser extension reads clipboard after Copy action
- Mitigation: The Copy action requires user interaction (click) and runs in the browser's secure context. No JavaScript-based mitigation for clipboard snooping beyond standard browser security.
- Severity: LOW — this is a browser-level security concern

**AV3: CSRF/clickjacking on Reveal action**
- Vector: Attacker tricks logged-in Service Desk user into clicking a button that triggers password reveal
- Mitigation: Livewire actions are already CSRF-protected via Livewire token. Add X-Frame-Options header to prevent clickjacking.
- Severity: LOW — Livewire's token system prevents CSRF on all component actions

**AV4: Revealed password visible in browser history/network tab**
- Vector: Revealed password is part of Livewire response, visible in DevTools network tab
- Mitigation: The response contains the password in JSON. This is unavoidable with any server-side approach. The 3-second auto-hide only affects the UI, not the network log.
- Severity: MEDIUM — any user with access to the machine can examine network logs after a Reveal action. Acceptable risk — same as any password manager.

### Privilege escalation

**PE1: User reveals password without vault module `can_reveal`**
- Guard 1: View conditionally renders buttons based on `can_reveal` on vault module
- Guard 2: Server-side Livewire action re-checks `can_reveal` on vault module
- Result: User cannot bypass view-layer gating. Even with direct POST, the server re-checks.

**PE2: User reveals password on domain page (where column doesn't exist)**
- No password column on domain table. Attempt to read `$domain->password` returns null guard.
- Result: No vulnerability. The feature is simply not available for Domain records.

### Data leakage

**DL1: "No password stored" reveals security posture**
- A "No password stored" indicator on a service page reveals that the service's password is not managed in the system.
- Severity: LOW — this is informational. The user already has `can_read` on the service module.

**DL2: Password count aggregated across services**
- Not applicable — this feature reads single records only.

### Audit logging

Copy and Reveal actions MUST be logged:
```php
// Copy action
activity()
    ->causedBy(auth()->user())
    ->performedOn($service)
    ->event('password_copied')
    ->withProperties([
        'service_name' => $service->name,
        'service_type' => get_class($service),
        'service_id' => $service->id,
    ])
    ->log("Password copied for {$service->name}");

// Reveal action (same structure, event = 'password_revealed')
```

### Rate limiting

- Reveal action: 30 requests/minute per user
- Implementation: `app/Http/Livewire/PasswordActions.php` revealPassword action checks cache:
  ```php
  $key = "password_reveal_rate_limit:" . auth()->id();
  $count = Cache::get($key, 0);
  if ($count >= 30) {
      throw new \Exception('Rate limit exceeded. Try again in 1 minute.');
  }
  Cache::put($key, $count + 1, now()->addMinute());
  ```

### Sensitive actions

- Reveal password: SENSITIVE. Requires audit logging + rate limiting.
- Copy password: SENSITIVE. Requires audit logging.

### Abuse scenarios

- **Credential harvesting via script:** Rate limit (30/min) limits harvest to 1800 passwords/hour. With 2000+ service records, complete harvest takes 1+ hour and generates 2000+ activity_log entries — easily detectable.
- **Social engineering to view one password:** Audit log reveals exactly who viewed which password and when. Deterrent.

---

## 11. Performance Budget

### Expected queries

- Page load: 1 query (findOrFail by ID)
- Reveal action: 1 query (re-fetch model to get fresh password)
- Copy action: 0 queries (password already loaded in component)

### Expected response time

- Page load: < 200ms (single record by PK)
- Reveal Livewire action: < 100ms (single record by PK + decrypt + log)
- Copy Livewire action: < 50ms (log only)

### Expected memory

- < 2MB PHP memory per component instance

### Maximum dataset

- Service model: ~30 columns, well within memory limits

### Scaling concerns

- No scaling concerns. All operations are single-record reads by primary key.

---

## 12. Acceptance Criteria

| # | Criterion | Expected Result | Pass/Fail |
|---|-----------|----------------|-----------|
| AC1 | Service Desk navigates to hosting detail page with password | Password shown masked. Copy and Reveal buttons visible. | PASS / FAIL |
| AC2 | Service Desk clicks "Copy" on hosting password | Password copied to clipboard. Activity log has entry. | PASS / FAIL |
| AC3 | Service Desk clicks "Reveal" on hosting password | Password shown in plaintext for 3 seconds. Activity log has entry. | PASS / FAIL |
| AC4 | Service Desk navigates to hosting detail page with NULL password | Shows "No password stored". No buttons. | PASS / FAIL |
| AC5 | Service Desk without `can_reveal` on vault module navigates to hosting | Password shown masked. NO Copy or Reveal buttons. | PASS / FAIL |
| AC6 | IT Operator with `can_reveal` on vault navigates to VPS detail page | Buttons appear. Copy works. Activity log written. | PASS / FAIL |
| AC7 | Service Desk navigates to Domain detail page | No password section (Domain has no password column) | PASS / FAIL |
| AC8 | Service Desk clicks "Reveal" 31 times in 1 minute | 31st click returns rate limit error | PASS / FAIL |
| AC9 | Service Desk with `can_reveal`, password is corrupted/unreadable | Shows "Error reading password". Does not crash page. | PASS / FAIL |
| AC10 | Password is auto-hidden after reveal | Password visible for 3 seconds, then masked again | PASS / FAIL |
| AC11 | Reveal action logged in activity_log with correct causer, subject, event | Activity entry exists | PASS / FAIL |
| AC12 | Copy action logged in activity_log with correct causer, subject, event | Activity entry exists | PASS / FAIL |

---

## 13. Regression Checklist

| # | Behavior | How verified |
|---|----------|-------------|
| R1 | Service detail page renders for authorized users | Route test |
| R2 | Service detail page returns 403 for unauthorized users | Route test with unauthorized role |
| R3 | Inline password encryption/decryption works for model CRUD | Existing model save/load test |
| R4 | Password field is excluded from JSON serialization (hidden) | API test |
| R5 | Existing password reveal feature (if any) still works | Any existing reveal test |
| R6 | Service detail page load time unchanged | Performance comparison |
| R7 | Activity log entries for other events (not password-related) | Existing activity log test |
| R8 | Vault module `can_reveal` permission works for vault pages | Vault permission test |

---

## 14. Testing

### Unit

| Test | What it validates |
|------|------------------|
| HostingPasswordTest@test_password_encrypted_cast | Password is encrypted on save, decrypted on read |
| HostingPasswordTest@test_null_password_returns_null | $hosting->password returns null when DB value is null |
| HostingPasswordTest@test_empty_string_password | Empty string password returns empty string |
| RateLimitTest@test_reveal_rate_limit_30_per_minute | 31st reveal in 60 seconds is blocked |

### Feature

| Test | What it validates |
|------|------------------|
| PasswordActionsTest@test_copy_action_logs_activity | Activity entry created with event 'password_copied' |
| PasswordActionsTest@test_reveal_action_logs_activity | Activity entry created with event 'password_revealed' |
| PasswordActionsTest@test_reveal_gated_by_can_reveal | User without can_reveal: action returns 403 |
| PasswordActionsTest@test_reveal_on_null_password | Null password: action returns error, no activity log |
| PasswordActionsTest@test_reveal_on_all_4_service_types | Hosting, VPS, VoIP, OtherService all work identically |

### Browser

| Test | What it validates |
|------|------------------|
| PasswordActionsBrowserTest@test_copy_button_clipboard | Clipboard API called with correct password |
| PasswordActionsBrowserTest@test_reveal_auto_hide | Password visible for 3 seconds, then masked |
| PasswordActionsBrowserTest@test_buttons_not_rendered_without_can_reveal | DOM does not contain Copy/Reveal buttons |

### Permission

| Test | What it validates |
|------|------------------|
| PermissionTest@test_reveal_checks_vault_module | canOnModule called with vault module, NOT service module |
| PermissionTest@test_copy_checks_vault_module | canOnModule called with vault module, NOT service module |

### Performance

| Test | What it validates |
|------|------------------|
| PerformanceTest@test_page_load_with_password_buttons | Page load < 200ms with buttons |
| PerformanceTest@test_reveal_action_response_time | Reveal action < 100ms |

### Regression

| Test | What it validates |
|------|------------------|
| Existing HostingControllerTest | Hosting CRUD not affected |
| Existing VaultEntryTest | Vault CRUD not affected |
| Existing ActivityLogTest | Activity logging not affected |

### Security

| Test | What it validates |
|------|------------------|
| SecurityTest@test_reveal_csrf_protected | Reveal requires valid Livewire token |
| SecurityTest@test_password_not_in_html_source | HTML source contains masked value, not plaintext |
| SecurityTest@test_rate_limit_resets_after_1_minute | After 1 minute wait, reveal succeeds again |

---

## 15. Rollback

### How this feature can be disabled

**Method 1: Feature flag (preferred)**
- Add `FEATURE_PASSWORD_AUTO_COPY` env flag (default: `true`)
- In `config/features.php`: `'password-auto-copy' => env('FEATURE_PASSWORD_AUTO_COPY', true)`
- In view: `@if(config('features.password-auto-copy') && auth()->user()->canOnModule(...))`
- To disable: set `FEATURE_PASSWORD_AUTO_COPY=false` in `.env`, cache clear, deploy

**Method 2: Condition removal (fallback)**
- Remove the `@if` block for Copy/Reveal buttons from the 4 service detail views
- Deploy view changes only

**Method 3: Rate limiting to zero (emergency)**
- Set rate limit cache key to saturate: unnecessary — just disable via feature flag

### Rollback order

1. Set `FEATURE_PASSWORD_AUTO_COPY=false` (zero downtime)
2. Remove from views (next deploy)
3. Remove Livewire component (next cleanup)

### Rollback verification

- Service detail pages show masked password only (original behavior)
- Activity log entries for password_copied/password_revealed stop appearing
- No data loss

---

## 16. Future Evolution

### v1.2

- Password auto-copy on hover (no click needed)
- Password strength indicator next to masked field
- "Copy to clipboard" confirmation toast
- Support for domain passwords IF a password column is added to domains table
- Reveal history on audit page (who viewed which password when)

### v2

- Vault entry creation from service detail page (if no vault entry exists)
- Service↔vault entry linking (adds FK between service record and vault entry)
- Two-way password sync (updating service password updates linked vault entry)
- Password auto-rotation with scheduled task
- Credential-less access (SSO/API token option eliminates need for password sharing)
