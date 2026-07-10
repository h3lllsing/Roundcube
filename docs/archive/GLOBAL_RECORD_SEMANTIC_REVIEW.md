# GLOBAL RECORD SEMANTIC REVIEW

## What "Ownership" Actually Means Per Table

### The Semantic Problem

The column name `user_id` on global tables implies "owner" — but the business rule says "these are NOT user-owned." This is a semantic mismatch between the column name and the business concept.

### Per-Table Analysis

| Table | user_id meaning in practice | Business meaning | Conflict |
|-------|---------------------------|------------------|----------|
| domains | Record creator (via old store()) | Corporate domain asset | Name implies ownership |
| hostings | Record creator | Corporate hosting account | Name implies ownership |
| vps | Record creator | Corporate server | Name implies ownership |
| voip | Record creator | Corporate phone system | Name implies ownership |
| service_providers | Record creator | Vendor relationship | Name implies ownership |
| domain_emails | Record creator | Corporate mailbox | Name implies ownership |
| other_services | Record creator | Corporate SaaS/API | Name implies ownership |
| assets | Record creator | Physical IT asset | Name implies ownership + `assigned_to` is actual custodian |
| expiry_trackers | Record creator | Corporate renewal reminder | Name implies ownership |

### The Actual Business Semantics Needed

1. **Created By (Audit)** — who added this record to the system
2. **Technical Contact (Optional)** — who is responsible for this asset day-to-day
3. **Assigned To (Asset-specific)** — physical custodian (already exists as `assigned_to` on assets table)

The current `user_id` conflates all three meanings into one column.

---

## Column Naming Review

### Misleading Column Names

| Table | Column | Issue | Suggested Rename |
|-------|--------|-------|------------------|
| ALL 9 | `user_id` | Implies ownership; actually means "created by" | `created_by` |
| vps | `name` | Contains server hostname, but column is generic | Acceptable — name is the server label |
| voip | `name` | Contains user name or extension label, not VoIP name | Rename to `label` or `extension_name` |
| voip | `type` | Values like 'sip' describe protocol, not a semantic type | `protocol` |
| hosting | `domain` | Contains the primary domain name, but there's a separate `domains` table | `primary_domain` |
| service_providers | `type` | Values like 'other' — but `name` is the provider name, `type` is ambiguous | `provider_type` or `category` |
| service_providers | `provider` | Named `provider` while the table IS service_providers — redundant | `vendor_code` or remove (replaced by `service_provider_id` FK) |
| expiry_trackers | `name` | For linked trackers, name is overridden by source. Semantic is "label" | Acceptable |
| domain_emails | `email` | Contains the email address, not a reference to an email table | Acceptable |
| assets | `assigned_to` | FKs to users(id) — this is correct semantics. Current custodian. | Acceptable |
| assets | `condition` | Values like 'new', 'good', 'fair' — these are lifecycle states, not conditions | `lifecycle_stage` or `grade` |
| assets | `department` | Free-text string, no normalization | Should be FK to departments table |
| assets | `location_id` | FKs to asset_locations — correct semantics | Acceptable |

### Columns That Should Be FK References (Not Free Text)

| Table | Column | Issue |
|-------|--------|-------|
| vps | `department` | Free-text — should reference a departments table or be removed |
| vps | `location` | Free-text — should reference a locations table |
| hosting | `domain` | Free-text — should reference domains(id) or be derived |
| voip | `type` (protocol) | Free-text enum — should be tinyint with a defined set |

### Columns That Are Dead / Unused

| Table | Column | Status |
|-------|--------|--------|
| service_providers | `provider` | Legacy column, replaced by `service_provider_id` FK system |
| domains | Original `registrar` | Replaced by `service_provider_id` FK |
| hostings | Original `provider` | Replaced by `service_provider_id` FK |
| vps | Original `provider` | Replaced by `service_provider_id` FK |
| voip | Original `provider` | Replaced by `service_provider_id` FK |
| other_services | Original `provider` | Replaced by `service_provider_id` FK |
| domain_emails | Original `provider` | Replaced by `service_provider_id` FK |

---

## Audit Metadata Columns (What Should Be Audit vs Data)

### Current State

| Column | Data or Audit? | Verdict |
|--------|---------------|---------|
| `user_id` on global tables | Currently Data (structural FK) | Should be Audit (`created_by`) |
| `created_at` | Audit | Correct |
| `updated_at` | Audit | Correct |
| `deleted_at` | Audit (soft delete) | Correct |
| `monitoring_url` | Data | Correct — operational |
| `last_ping_at` | Data (monitoring) | Correct |
| `created_by` / `updated_by` on Module/Feature/SmtpProfile via Blameable | Audit | Correct pattern |

### Missing Audit Columns

| Table | Missing |
|-------|---------|
| All 9 global tables | `created_by` (Blameable) |
| All 9 global tables | `updated_by` (Blameable) |
| All 9 global tables | Proper separation of "created by" vs "responsible person" |

---

## Future Scalability Risks

### 1. ServiceProvider as Universal FK

`service_provider_id` appears on 8 tables as a nullable FK. ServiceProvider has grown to represent vendors across entirely different domains (DNS registrars, hosting companies, VPS providers, VoIP carriers, email hosts, SaaS vendors). This works today but creates:

- **Single table coupling** — one ServiceProvider table is a SPOF for vendor data across all domains
- **Mixed semantics** — a domain registrar and an email host are different business entities sharing one table
- **Extensibility risk** — adding a new service type (e.g., "CDN Provider") requires either extending ServiceProvider or normalizing it

### 2. Polymorphic trackable on ExpiryTracker

`trackable_type` / `trackable_id` is a polymorphic relationship that links an ExpiryTracker to any of 8+ source models. This is flexible but:
- No FK constraints — orphaned trackable references are not prevented
- No index on `(trackable_type, trackable_id)` for all-source queries
- The `getExpiryDateAttribute()` accessor has an N+1 risk when loading collections

### 3. JSON Columns for Structured Data

| Table | JSON Column | Risk |
|-------|-------------|------|
| vps | `login_ids`, `additional_ips` | Not queryable via WHERE without JSON path expressions |
| task/other | Various `specifications` | Same — data is opaque to the database |
| expiry_trackers | `notify_days_before`, `notify_custom_emails` | Same |

JSON columns are acceptable for unstructured/extensible data but create a "black box" for reporting.

### 4. Soft Deletes on All Tables

Every global model uses SoftDeletes. This is correct for recovery, but:
- No global record has a unique constraint on business keys (e.g., domain name should be unique across non-deleted records)
- Composite unique constraints with `deleted_at` are not implemented
- This allows duplicate domain names, duplicate email addresses, etc.
