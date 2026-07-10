# 4. Module Business Logic

## 4.1 Domains

**Controller:** `DomainController`
**Model:** `Domain`
**Routes:** `/domains`, `/domains/{domain}`, etc.

### Backend Behavior
- Domains are the most feature-rich entity with the most columns and composite UI.
- Each domain can optionally link to a Hosting account and a Service Provider.
- Expiry date tracking is shared with Renewal Center: domains appear in the ExpiryTrackers index only if they have an explicitly linked ExpiryTracker record. The domain's `expire_date` is not automatically tracked—a tracker must be created.
- Cloudflare status is a boolean (is_cloudflare: yes/no) with additional fields for CF zone ID, CF account ID, and nameservers.
- DNS management is purely informational (text area for DNS entries). No integration with DNS APIs.
- Cost tracking is stored directly in the domains table.
- Registrar info is stored directly.
- Two-letter country code for TLD classification (informational, not used in logic).
- Soft deletes with `Spatie\Laravel\Deleted` activity logging.

### Index Page
- Paginated list (default 10 per page) with search by name.
- Shows: name, registrar, expiry date, cost, linked hosting name, Cloudflare status.
- Eager loads: `hosting`, `expiryTracker`, `serviceProvider`.
- Searchable: `name`, `registrar`.
- Ordering: `expire_date ASC NULLS LAST` (soonest expiring first).

### Show/Create/Edit Forms
- Single form page for both viewing details and editing.
- Conditional display: "Linked to Hosting" section only if `hosting_id` is set.
- Conditional display: "Service Provider" section only if `service_provider_id` is set.
- Password field uses `data-password` reveal toggle identical to Vault pattern.

### Edge Cases
- Domain can have hosting_id set to a soft-deleted hosting → eager load with `->with('hosting')` includes trashed by default? No — hosting model does NOT use `SoftDeletes`. Hard deletes only.
- Deleting a domain that has linked ExpiryTracker: ExpiryTracker must also be deleted (observer or controller handles this? NEEDS CONFIRMATION — test through soft-delete cascade).
- Domain with linked DomainEmails: deleting a domain that has children domain_emails. Foreign key constraint? `domain_emails.domain_id` has no `ON DELETE CASCADE` at DB level. Application must handle or will get FK violation.

---

## 4.2 Hosting

**Controller:** `HostingController`
**Model:** `Hosting`
**Routes:** `/hostings`

### Backend Behavior
- Hosting accounts with cPanel/management URLs and credentials.
- Each hosting belongs to one Service Provider (optional).
- Shared/hosting/reseller classification via `plan_type` enum.
- IP address with server info.
- Password encrypted `encrypted` cast.
- Monitoring check URL (optional) — ping-based availability.

### Edge Cases
- Hosting with associated domains: hard delete blocked if FK violation (domains.hosting_id references hostings.id).
- ServiceProvider soft-delete does NOT cascade to hostings — provider set to null on delete? NEEDS CONFIRMATION.

---

## 4.3 VPS

**Controller:** `VpsController`
**Model:** `Vps`
**Routes:** `/vps`

### Backend Behavior
- Virtual private server tracking with full spec sheet: OS, RAM, disk, CPU, location, IPs.
- Optional Service Provider link.
- Password and SSH key storage (password encrypted).
- Monitoring URL optional.

### Edge Cases
- IP addresses stored as single varchar (not JSON array). Comma-separated? Single IP? NEEDS CONFIRMATION.
- Same deletion concerns as Hosting regarding ServiceProvider.

---

## 4.4 VoIP

**Controller:** `VoipController`
**Model:** `Voip`
**Routes:** `/voip`

### Backend Behavior
- VoIP service tracking with phone numbers, extensions, and direction (inbound/outbound/both).
- Optional Service Provider link.
- Registration info and credentials.

---

## 4.5 Domain Emails

**Controller:** `DomainEmailController`
**Model:** `DomainEmail`
**Routes:** `/domain-emails`

### Backend Behavior
- Email accounts linked to a Domain (required).
- Password encrypted.
- Storage allocation, forwarders.
- Each can optionally have a linked ExpiryTracker.

### Edge Cases
- Cannot exist without a parent domain. Delete domain → cascade? DB does NOT have ON DELETE CASCADE — application must handle.
- Password reveal same as Vault pattern.

---

## 4.6 Other Services

**Controller:** `OtherServiceController`
**Model:** `OtherService`
**Routes:** `/other-services`

### Backend Behavior
- Catch-all for services not fitting other categories (SaaS subscriptions, software licenses, API keys).
- Login URL, credentials, plan/cost.
- Optional Service Provider link.

---

## 4.7 Service Providers

**Controller:** `ServiceProviderController`
**Model:** `ServiceProvider`
**Routes:** `/service-providers`

### Backend Behavior
- Vendor/provider companies that supply services.
- Contact info, website, notes.
- Credentials for provider portal.
- Has-many relationships to: Domains, Hostings, VPS, VoIPs, OtherServices.

---

## 4.8 Vault (Encrypted Password Store)

**Controller:** `VaultController`
**Service:** `VaultService`
**Model:** `VaultEntry`
**Routes:** `/vault`

### Backend Behavior
- Stores arbitrary service credentials (service name, URL, username, password).
- All password fields use Laravel `encrypted` cast (AES-256-CBC).
- Passwords are NEVER returned in plaintext in index/show views — always masked.
- Password reveal is an explicit AJAX action (`POST /vault/{vault}/reveal`) that:
  1. Checks permission (`can_reveal_vault`)
  2. Logs the reveal in activity log (`causedBy(auth()->user())`)
  3. Returns the decrypted password as JSON response
- Each VaultEntry can optionally link to ANY module via `module_id` + 'module_type', or be unlinked.
- No-op setPasswordAttribute mutator was removed in Sprint B (column dropped, cast handles it).

### Edge Cases
- Reveal logging is MANDATORY — every reveal creates an activity_log entry. No suppress option.
- Frontend must handle masked vs revealed state.
- No "reveal all" bulk operation exists.

---

## 4.9 Assets

**Controller:** `AssetController`
**Model:** `Asset`
**Routes:** `/assets`

### Backend Behavior
- Hardware asset tracking (laptops, monitors, phones, etc.).
- Categories: item assignment via `category` + `type` fields (both strings, not enums).
- QR code identifier (unique string). Generation is manual (no auto-generate currently).
- Assignment tracking: `assigned_to` (user name string, not FK), `assignment_date`, `return_date`.
- Soft deletes.

### Edge Cases
- `assigned_to` is a free-text field, NOT a FK to users. Renaming a user does NOT update asset assignments.
- Two unused scopes (`active()` and `availableForAssignment()`) were removed in Sprint B — they had zero call sites.
- QR code must be unique but no generation helper exists — entered manually.

---

## 4.10 Tasks

**Controller:** `TaskController`
**Model:** `Task`
**Routes:** `/tasks`

### Backend Behavior
- Task management with status, priority, due dates.
- Assignment to multiple users via `task_user` pivot table.
- Separate kanban board view for drag-and-drop status updates (Alpine.js driven).
- Status values: 'pending', 'in_progress', 'completed', 'cancelled'.
- Priority values: 'low', 'medium', 'high', 'urgent'.

### Kanban Board
- Separate route (`/tasks/kanban`) returning a dedicated view.
- Status columns rendered with drag-and-drop (Alpine.js + Sortable? needs confirmation).
- Backend updates via standard update route (no dedicated API endpoint).

---

## 4.11 Notes (Polymorphic)

**Controller:** `NoteController`
**Model:** `Note`
**Routes:** `/notes` (standalone index) + embedded create on parent entity show pages

### Backend Behavior
- Notes attachable to any major entity via polymorphic relationship.
- Each parent entity's show page includes a notes section where notes can be created/listed without leaving the page.
- Standalone index at `/notes` shows all notes across all entities.
- Owner tracked via `user_id`.
- Simple text content with no rich formatting (plain text or limited HTML? NEEDS CONFIRMATION).

---

## 4.12 Attachments (Polymorphic)

**Controller:** `AttachmentController`
**Model:** `Attachment`
**Routes:** `/attachments`

### Backend Behavior
- File uploads attachable to any major entity.
- Files stored on local disk (`storage/app/public/attachments/`).
- URL generated via `Storage::url()`.
- File extension validation.
- File size limit: configurable in validation rules.

---

## 4.13 Webhooks

**Controller:** `WebhookController`
**Model:** `Webhook`
**Routes:** `/webhooks`

### Backend Behavior
- Outgoing webhook configurations.
- URL, events to trigger on, active toggle.
- HMAC signature for payload verification (optional).
- No consumer-side webhook receiver — outbound only.

---

## 4.14 Activity Log (System-Level)

**Controller:** `ActivityLogController`
**Model:** `ActivityLog` (spatie package)
**Routes:** `/activity-logs`

### Backend Behavior
- System-wide audit trail showing all logged activity across all entities.
- Each entity's show page also has an embedded activity log section.
- Filterable by causer (user), subject type, event.
- Searchable by description.
- Super admin only view? Route has `can_read` permission on 'activity-logs' module.

---

## 4.15 Login Audit

**Controller:** `LoginAuditController`
**Model:** `LoginAudit` (likely a custom model or session log)
**Routes:** `/login-audits`

### Backend Behavior
- Tracks user login attempts (success + failure).
- Lists: user, IP, user agent, timestamp, success/failure.
- Read-only (no create/edit/delete).
- Super admin / admin access only.

---

## 4.16 Users & Roles

**Controllers:** `UserController`, `RoleController`
**Models:** `User`, `Role`, `Permission`
**Routes:** `/users`, `/roles`

### Backend Behavior
- Standard Laravel Breeze auth users.
- Roles assigned via Spatie Permission package (`Spatie\Permission\Models\Role`).
- Module permissions assigned per-user or per-role via custom pivot structure (`user_module_permissions`, `module_role`, `module_user`?).
- Super admin flag: `$user->hasRole('super-admin')` via Spatie Permission role assignment.

### Super Admin
- Hardcoded email. Bypasses ALL permission checks.
- Sees ALL modules, ALL records. No ownership scoping.
- UI should never restrict super admin from accessing any data.

### Module Permission Overrides
- Individual users can have module permissions that override their role defaults.
- No accumulative merging: overrides REPLACE role defaults for that module.
- Override management UI in user edit page.
