# 7. Key Data Flows

This document traces the lifecycle of data through the system.

## 7.1 Domain Creation Flow

```
User submits POST /domains
  → DomainController@store
    → $this->authorize('module_access', ['domains', 'can_create'])
    → $request->validated()  [DomainStoreRequest]
    → Domain::create($validated)
      → Model events: creating, created
      → ActivityLog observer logs 'created' activity
    → Redirect to /domains/{domain} with success flash
```

### Validation Rules (DomainStoreRequest)
- `name`: required, string, max:255
- `registrar`: nullable, string, max:255
- `expire_date`: nullable, date
- `cost`: nullable, numeric
- `hosting_id`: nullable, exists:hostings,id
- `service_provider_id`: nullable, exists:service_providers,id
- `is_cloudflare`: boolean
- `cloudflare_zone_id`: nullable, string
- `cloudflare_account_id`: nullable, string
- `dns`: nullable, string
- `nameservers`: nullable, string
- `country_code`: nullable, string, size:2
- `username`, `password`: nullable, string
- `registrar_url`: nullable, url

### Password Handling
- Password is stored with `encrypted` cast (AES-256-CBC).
- On form display, password is masked.
- Reveal via endpoint: `POST /domains/{domain}/reveal-password` (same pattern as Vault).

---

## 7.2 ExpiryTracker Link Flow (Linked Tracker Creation)

```
User clicks "Add Renewal Tracker" on Domain show page
  → GET /expiry-trackers/create?trackable_type=domain&trackable_id=5
  → ExpiryTrackerController@create
    → Pre-populates form with source data (name, expire_date, cost)
    → trackable_type and trackable_id hidden fields
    → Frontend shows read-only sync fields + editable fields

User submits POST /expiry-trackers
  → ExpiryTrackerController@store
    → Validates uniqueness: no existing tracker for trackable_id + trackable_type
    → Validates trackable exists and user has read permission on that module
    → $this->authorize('module_access', ['expiry-trackers', 'can_create'])
    → Creates tracker with trackable relation
    → Activity log created
    → Redirect to /expiry-trackers/{tracker}
```

### Syncing Behavior
When a source service updates its `expire_date`, `cost`, or `name`:

**Current mechanism:** NEEDS CONFIRMATION. Either:
1. The source controller's `update()` method explicitly finds and updates linked tracker(s).
2. A model observer on each source model handles sync.
3. An event/listener pattern (ExpiryTrackerLinked event exists — `app/Events/ExpiryTrackerLinked.php`).

The event `ExpiryTrackerLinked` exists, suggesting Option 3 is at least partially implemented. Check the listener/handler.

---

## 7.3 Renewal Notification Flow

```
Artisan command: SendRenewalNotifications (daily)
  → RenewalNotificationService::sendNotifications()
    → Query: ExpiryTracker::where('is_completed', false)
        ->whereDate('expire_date', '>=', today())
        ->whereDate('expire_date', '<=', today()->addDays(notify_before_days))
        ->get()
    → Group by user_id
    → For each user:
        → Dispatch RenewalNotification (Mail + Database)
        → Log::info('Renewal notification sent to {user} for {tracker}')
    → If no trackers match: Log::info('No renewals due today')
    → On error: Log::error() in catch block (added Sprint A)
```

### Edge Cases
- User has `notify_before_days = null` → falls back to `config('renewals.notify_days_before')`.
- Tracker `expire_date` is in the past but `is_completed = false` → NOT included (query checks `>= today()`).
- User has no email address → DB notification still created, mail channel fails silently.
- Multiple trackers expiring on same day for same user → one notification per tracker.

---

## 7.4 Password Reveal Flow

```
User clicks "Reveal" on a password field (Vault, Domain, Hosting, etc.)
  → AJAX POST to reveal endpoint
  → Controller checks permission (can_reveal_vault or per-module)
  → VaultService::reveal() or inline logic
    → Decrypts password using Laravel's decrypt helper
    → Returns plaintext as JSON { password: "decrypted_value" }
  → Activity log: "Revealed password for [entity]"
  → Frontend updates the field with plaintext (non-persistent — on next page load it's masked again)
```

### Security Notes
- Each reveal is logged with timestamps and causer.
- No rate limiting on reveal.
- Revealed password is never stored in session.
- Frontend shows password for X seconds then re-masks (via Alpine.js timer).

---

## 7.5 Soft Delete & Restore Flow

For models with soft deletes:

```
User clicks "Delete"
  → POST /entity/{id} with _method=DELETE
  → Controller: authorize can_delete
  → Model::destroy($id) (sets deleted_at)
  → Activity log: 'deleted [entity]'
  → Redirect with success flash

User clicks "Restore"
  → POST /entity/{id}/restore
  → Controller: authorize can_restore
  → Model::withTrashed()->find($id)->restore()
  → Activity log: 'restored [entity]'
  → Redirect

Admin force deletes (permanently):
  → POST /entity/{id}/force-delete
  → Controller: authorize can_force_delete
  → Model::withTrashed()->find($id)->forceDelete()
  → Activity log: 'permanently deleted [entity]'
```

Models using SoftDeletes:
- Domain
- DomainEmail
- Hosting
- VPS
- VoIP
- OtherService
- ServiceProvider
- Asset
- ExpiryTracker? (NEEDS CONFIRMATION)
- Note
- Attachment

---

## 7.6 Activity Logging Flow

**Handled by:** `app/Observers/` (model observers registered in `AppServiceProvider`)

Every create/update/delete on tracked models triggers:

```php
activity()
    ->performedOn($model)
    ->causedBy(auth()->user())
    ->withProperties([...changed fields...])
    ->event($eventName)   // 'created', 'updated', 'deleted', 'restored'
    ->log("{$eventName} {$modelName}: {$model->identifier}");
```

### What Gets Logged
- **Entity type** via MorphMap alias in `subject_type`.
- **Causer** via authenticated user.
- **Properties:** before/after values for updates.
- **Event:** create, update, delete, restore, login, reveal.

### Retention
- No automatic pruning. Activity logs accumulate indefinitely.
- No archive/export mechanism for old logs.
- Currently displayed: last 50 entries per page (paginated).

---

## 7.7 File Upload Flow (Attachments)

```
User uploads file via attachment form
  → AttachmentController@store
    → Validates: file, mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip, max:10240kb
    → File stored at storage/app/public/attachments/{uuid}.{ext}
    → Attachment record created: path, original_name, mime_type, size, attachable
    → Activity log: 'created attachment: {name}'
  → Response: redirect back with success flash

User downloads file:
  → GET /attachments/{attachment}/download
  → Returns Storage::download() response
  → Activity log: 'downloaded attachment: {name}'

User deletes attachment:
  → POST /attachments/{attachment} with _method=DELETE
  → File deleted from disk
  → Record force-deleted
  → Activity log: 'deleted attachment: {name}'
```

---

## 7.8 Task Status Update Flow

```
User drags task to new kanban column
  → Alpine.js sends PATCH /tasks/{task}
  → TaskController@update
    → Validates new status value
    → Updates status field
    → Activity log: 'updated task: {name} (status: {old} → {new})'
  → Kanban board re-renders via Livewire or full page reload? (NEEDS CONFIRMATION)
```

---

## 7.9 Polymorphic Note Creation Flow

```
User adds note on a Domain show page
  → POST /notes
  → NoteController@store
    → Validates: notable_type, notable_id, content, user_id
    → Creates Note record with polymorphic relation
    → Activity log: 'created note on {entity}'
  → Redirect back with success flash
```

---

## 7.10 User Creation Flow

```
Super Admin creates user
  → POST /users
  → UserController@store
    → Validates: name, email, password, role assignment
    → Creates user with hashed password
    → Assigns role via Spatie: $user->assignRole($role)
    → Sends welcome email? (NEEDS CONFIRMATION)
    → Activity log: 'created user: {email}'
  → Redirect to /users/{user}
```
