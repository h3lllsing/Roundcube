# 3. Database Relationships

## Entity-Relationship Diagram (Textual)

```
Service_Providers 1──M Domains
                  1──M Hostings
                  1──M VPS
                  1──M VoIPs
                  1──M Other_Services
                  1──M Expiry_Trackers (standalone, no module_id)

Domains 1──M Domain_Emails
        1──M Expiry_Trackers (via trackable)
        1──1 Hostings (nullable)
        1──1 Service_Providers (nullable)

Hostings 1──M Expiry_Trackers (via trackable)
         1──1 Service_Providers (nullable)

VPS 1──M Expiry_Trackers (via trackable)
    1──1 Service_Providers (nullable)

VoIPs 1──M Expiry_Trackers (via trackable)
     1──1 Service_Providers (nullable)

Other_Services 1──M Expiry_Trackers (via trackable)
              1──1 Service_Providers (nullable)

Modules M──M Features (pivot: feature_module)

Users   M──M Roles (pivot: model_has_roles)
        1──M Expiry_Trackers (via trackable — user as assignee)

Roles   M──M Permissions (via role_has_permissions)

*Everything* 1──M Notes (polymorphic: notable)
*Everything* 1──M Attachments (polymorphic: attachable)
*Everything* 1──M Activity_Log (polymorphic: subject)
Users       1──M Activity_Log (polymorphic: causer)

Expiry_Trackers 1──M Activity_Log
Assets          1──M Activity_Log
Tasks           1──M Activity_Log
Service_Providers 1──M Activity_Log
```

## Core Table Relationships (Detail)

### `domains` table

| Column | FK to | Nullable |
|---|---|---|
| `hosting_id` | `hostings.id` | Yes |
| `service_provider_id` | `service_providers.id` | Yes |

- Domains belong to Hosting (optional — a domain may have no hosting plan).
- Domains belong to ServiceProvider (optional).
- Domains have many DomainEmails.
- Domains can have one linked ExpiryTracker (polymorphic `trackable`).

### `hostings` table

| Column | FK to | Nullable |
|---|---|---|
| `service_provider_id` | `service_providers.id` | Yes |

- Hostings belong to ServiceProvider (optional).
- Hostings have many Domains.
- Hostings can have one linked ExpiryTracker.

### `vps` table

| Column | FK to | Nullable |
|---|---|---|
| `service_provider_id` | `service_providers.id` | Yes |

- VPSs belong to ServiceProvider (optional).
- VPSs can have one linked ExpiryTracker.

### `voips` table

| Column | FK to | Nullable |
|---|---|---|
| `service_provider_id` | `service_providers.id` | Yes |

- VoIPs belong to ServiceProvider (optional).
- VoIPs can have one linked ExpiryTracker.

### `other_services` table

| Column | FK to | Nullable |
|---|---|---|
| `service_provider_id` | `service_providers.id` | Yes |

- OtherServices belong to ServiceProvider (optional).
- OtherServices can have one linked ExpiryTracker.

### `domain_emails` table

| Column | FK to | Nullable |
|---|---|---|
| `domain_id` | `domains.id` | No |

- DomainEmails belong to Domain (required).
- DomainEmails can have one linked ExpiryTracker.

### `expiry_trackers` (renewals) table

| Column | FK to | Nullable |
|---|---|---|
| `trackable_id` | polymorphic | Yes |
| `trackable_type` | morph alias | Yes |
| `module_id` | `modules.id` | Yes |
| `user_id` | `users.id` | Yes |
| `created_by` | `users.id` | Yes |
| `completed_by` | `users.id` | Yes |

- Polymorphic `trackable` can be: Domain, Hosting, VPS, VoIP, OtherService, DomainEmail, or null (standalone).
- **Linked trackers** (has trackable) sync expiry_date, cost, and name from the source service.
- **Standalone trackers** (no trackable) manage their own dates/names independently.
- `module_id` links to a module (logical grouping for permissions).
- `user_id` is the assigned user (ownership).
- `created_by` and `completed_by` track who created/completed.

### `activity_log` table

| Column | FK to | Nullable |
|---|---|---|
| `subject_id` + `subject_type` | polymorphic | Yes |
| `causer_id` + `causer_type` | polymorphic | Yes |

- Logs every create/update/delete/reveal/payment on tracked entities.

### `notes` table

| Column | FK to | Nullable |
|---|---|---|
| `notable_id` + `notable_type` | polymorphic | No |

### `attachments` table

| Column | FK to | Nullable |
|---|---|---|
| `attachable_id` + `attachable_type` | polymorphic | No |
| `user_id` | `users.id` | No |

### `user_module_permissions` table

| Column | FK to | Nullable |
|---|---|---|
| `user_id` | `users.id` | No |
| `module_id` | `modules.id` | No |
| `feature_id` | `features.id` | No |

- Stores per-user overrides to module-level RBAC defaults.

## Registered MorphMap Aliases (Critical)

Defined in `AppServiceProvider::boot()`. Frontend must use these exact strings when constructing polymorphic URLs or API calls:

| MorphMap Alias | FQ Model Class |
|---|---|
| `domain` | App\Models\Domain |
| `hosting` | App\Models\Hosting |
| `vps` | App\Models\Vps |
| `voip` | App\Models\Voip |
| `other-service` | App\Models\OtherService |
| `domain-email` | App\Models\DomainEmail |
| `expiry-tracker` | App\Models\ExpiryTracker |
| `service-provider` | App\Models\ServiceProvider |
| `asset` | App\Models\Asset |
| `task` | App\Models\Task |
| `note` | App\Models\Note |
| `attachment` | App\Models\Attachment |
| `module` | App\Models\Module |
| `feature` | App\Models\Feature |
| `user` | App\Models\User |
| `role` | App\Models\Role |
| `permission` | App\Models\Permission |
| `webhook` | App\Models\Webhook |
| `vault-entry` | App\Models\VaultEntry |

## Polymorphic Relationship Types

### `notable` (Notes)
All major entities can have notes attached. The `notes.notable_type` column uses the MorphMap alias.

### `attachable` (Attachments)
Same pattern as notes. `attachments.attachable_type` uses MorphMap alias.

### `subject` (Activity Log)
The entity that was acted upon. `activity_log.subject_type` uses MorphMap alias.

### `trackable` (Expiry Trackers)
The source service that a renewal is linked to. `expiry_trackers.trackable_type` uses MorphMap alias.

### `causer` (Activity Log)
The user who performed the action. Always `user` alias.

## Indexes

- `activity_log.subject_id + subject_type` (indexed by default)
- `activity_log.causer_id + causer_type` (indexed by default)
- `notes.notable_id + notable_type` (indexed by default)
- `attachments.attachable_id + attachable_type` (indexed by default)
- `expiry_trackers.trackable_id + trackable_type`
- Foreign key columns indexed by migration or convention
- No composite indexes for common query patterns (e.g., `expiry_trackers.is_completed + expire_date`)
