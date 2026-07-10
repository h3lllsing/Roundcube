# DATA AUTHORITY REPORT

> Which modules have editing authority over which data, and where boundaries are crossed.
> Evidence from permission architecture and module structure.

---

## CLEAR AUTHORITY (No Ambiguity)

| Data | Editing Module | Type | Authority Boundary |
|------|---------------|------|-------------------|
| User record | `users` (slug: `users`) | Exclusive | No other module edits users |
| Role definition | `roles` (slug: `roles`) | Exclusive | No other module edits roles |
| Module definition | `module-permissions` (slug: `module-permissions`) | Exclusive | No other module edits modules |
| Module role permissions | `module-permissions` | Exclusive | Only this UI sets role defaults |
| User module permissions | `module-permissions` | Exclusive | Only this UI sets user overrides |
| Hosting record | `hostings` (slug: `hostings`) | Exclusive | No other module edits hostings |
| Domain record | `domains` (slug: `domains`) | Exclusive | No other module edits domains |
| VPS record | `vps` (slug: `vps`) | Exclusive | No other module edits vps |
| VoIP record | `voip` (slug: `voip`) | Exclusive | No other module edits voip |
| OtherService record | `other-services` (slug: `other-services`) | Exclusive | No other module edits other_services |
| ServiceProvider record | `service-providers` (slug: `service-providers`) | Exclusive | No other module edits service_providers |
| DomainEmail record | `domain-emails` (slug: `domain-emails`) | Exclusive | No other module edits domain_emails |
| Task record | `tasks` (slug: `tasks`) | Exclusive | No other module edits tasks |
| Asset record | `assets` (slug: `assets`) | Exclusive | No other module edits assets |
| VaultEntry record | `vault` (slug: `vault`) | Exclusive | No other module edits vault_entries |
| ExpiryTracker record | `expiry-trackers` (slug: `expiry-trackers`) | Exclusive | No other module edits expiry_trackers |
| ExpiryTrackerNotification | (system - cron) | Exclusive | Created by cron job only |
| SmtpProfile | SMTP management UI | Exclusive | No other module edits smtp_profiles |
| Webhook | `webhooks` (slug: `webhooks`) | Exclusive | No other module edits webhooks |
| LoginAudit | **System** (authentication) | Exclusive | No module edits — created on login |
| Activity | **System** (Spatie) | Exclusive | No module edits — created on model events |

---

## AMBIGUOUS AUTHORITY (No Single Owner)

| Data | Editing Modules | Tables | Conflict Type |
|------|----------------|--------|---------------|
| **Service password** | `hostings` module (edits hosting.password) | `hostings.password` | **SAME LOGICAL VALUE, NO SYNC** |
| | `vps` module (edits vps.password) | `vps.password` | |
| | `voip` module (edits voip.password) | `voip.password` | |
| | `other-services` module (edits other_services.password) | `other_services.password` | |
| | `service-providers` module (edits service_providers.password) | `service_providers.password` | |
| | `domain-emails` module (edits domain_emails.password) | `domain_emails.password` | |
| | `vault` module (edits vault_entry.encrypted_password) | `password_vault.encrypted_password` | |
| **Service name** | Service module (edits name) | Service table | **COPY, NO FK** |
| | `vault` module (edits vault_entry.service_name) | `password_vault.service_name` | |
| | `expiry-trackers` module (edits expiry_tracker.name) | `expiry_trackers.name` | |
| **Service cost** | Service module (edits cost) | Service table | **COPY, NO FK** |
| | `expiry-trackers` module (edits expiry_tracker.cost) | `expiry_trackers.cost` | |
| **Service expiry_date** | Service module (edits expiry_date) | Service table | **COPY, ACCESSOR FALLBACK** |
| | `expiry-trackers` module (edits expiry_tracker.expiry_date) | `expiry_trackers.expiry_date` | |

---

## AUTHORITY BOUNDARY CROSSINGS

### Crossing 1: Password — 7 storage locations, 7 editing modules

```
Editing Module: hostings
  → writes: hostings.password
  → writes: hostings.updated_at
  → DOES NOT write: password_vault.encrypted_password

Editing Module: vault
  → writes: password_vault.encrypted_password
  → writes: password_vault.updated_at
  → DOES NOT write: hostings.password (or any service password)

Editing Module: vps
  → writes: vps.password
  → (same pattern — independent)

Editing Module: voip
  → writes: voip.password
  → (same pattern — independent)

Editing Module: other-services
  → writes: other_services.password
  → (same pattern — independent)

Editing Module: service-providers
  → writes: service_providers.password
  → (same pattern — independent)

Editing Module: domain-emails
  → writes: domain_emails.password
  → (same pattern — independent)
```

**Result:** The same logical credential (e.g., "admin password for Acme Hosting") can be stored in `hostings.password` AND `password_vault.encrypted_password` with DIFFERENT values. Neither module knows about the other. No user-facing indication of which is current.

### Crossing 2: Service name — 3+ storage locations, 3 editing modules

```
Editing Module: hostings
  → writes: hostings.name
  → DOES NOT write: password_vault.service_name
  → DOES NOT write: expiry_trackers.name

Editing Module: vault
  → writes: password_vault.service_name (free text)
  → this is a MANUAL ENTRY, not auto-filled from service name

Editing Module: expiry-trackers
  → writes: expiry_trackers.name (free text)
  → this is a MANUAL ENTRY, not auto-filled from service name
```

**Result:** A service renamed in hostings module does NOT update vault entries or expiry trackers. The old name persists in both, making cross-referencing unreliable.

### Crossing 3: Service cost — 2 storage locations, 2 editing modules

```
Editing Module: hostings (or domains, etc.)
  → writes: hostings.cost
  → DOES NOT write: expiry_trackers.cost

Editing Module: expiry-trackers
  → writes: expiry_trackers.cost
  → this is a MANUAL ENTRY, not synced from service cost
```

**Result:** A cost change on the service does NOT update the expiry tracker. The Renewal Dashboard (F3) shows expiry_tracker.cost which may be stale.

### Crossing 4: Service expiry_date — 2 storage locations, 2 editing modules

```
Editing Module: hostings (or domains, etc.)
  → writes: hostings.expiry_date
  → DOES NOT write: expiry_trackers.expiry_date

Editing Module: expiry-trackers
  → writes: expiry_trackers.expiry_date
  → expiry_date ACCESSOR on ExpiryTracker reads from own value first, falls back to trackable
```

**Result:** Two different expiry dates can exist for the same logical service. The ExpiryTracker's accessor creates a false sense of consistency by falling back to trackable when its own value is null, but when BOTH are set, they can diverge silently.

---

## AUTHORITY RECOMMENDATIONS

### Immediate (no-code, policy-only)

1. **Declare service module as authority for service passwords.** Vault entries for service credentials should be READ-ONLY copies. Document: "The inline password on the service record is the system of record for that service's credential. The vault is for shared team passwords that are NOT tied to a specific service."

2. **Declare service module as authority for service name.** Vault entries and expiry trackers should derive `name` from the service record via FK, not free-text entry.

### Short-term (requires code, part of F2/F4 scope)

3. **Auto-fill `vault_entry.service_name` from service name** in Quick Provision (F4). This is already specced. Extends to: auto-fill `expiry_tracker.name` from service name as well.

4. **Add FK from vault_entry to service model** (via `credential_service` pivot table). This is the v1.2 evolution of F2. Makes the vault→service relationship explicit and queryable.

### Long-term (v2+)

5. **Add observer to sync service name changes to vault entries.** If a service is renamed, update all vault entries whose `credential_service` pivot references that service.

6. **Add observer to sync service cost/expiry changes to expiry trackers.** If service cost changes, update linked expiry tracker's cost. User should see: "Cost was auto-updated from service record. Edit manually to override."

7. **Eliminate inline passwords entirely.** Migrate ALL service inline passwords into vault entries. Remove `password` columns from `hostings`, `vps`, `voip`, `other_services`, `service_providers`, `domain_emails`. The vault becomes the single credential store. This is a SPECTACULARLY disruptive change (6+ migrations, data migration, UI changes, permission model changes). Must be carefully planned.
