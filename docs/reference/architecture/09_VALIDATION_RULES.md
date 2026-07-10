# 9. Validation Rules & Edge Cases

## Common Validation Patterns

All form requests are in `app/Http/Requests/`. The system has a Store and Update request for each module.

### Standard Fields Across All Modules

| Concept | Rule | Notes |
|---|---|---|
| Name | `required, string, max:255` | All named entities |
| Status | `required, in:[valid_values]` | Domain-specific enums |
| Cost | `nullable, numeric, min:0` | Currency stored as decimal |
| Expire Date | `nullable, date` | MySQL date format YYYY-MM-DD |
| Notes | `nullable, string` | Free text |
| Created By | `nullable, exists:users,id` | Auto-set by controller |
| User ID (assignee) | `nullable, exists:users,id` | Ownership |

### Password Fields

- `nullable, string` (no min length)
- Stored encrypted via Laravel `encrypted` cast
- Revealed only via explicit AJAX call
- **NOT** required on create — empty passwords allowed

### URL Fields

- `nullable, url` — validated against Laravel URL validator
- Applied to: registrar_url, cpanel_url, login_url, monitoring_url, etc.

### Boolean Fields

- `boolean` — accepts true/false/1/0/"true"/"false"
- Default false in migration if not specified

## Module-Specific Validation

### Domain Store/Update Request

```
name:             required, string, max:255
registrar:        nullable, string, max:255
expire_date:      nullable, date
cost:             nullable, numeric, min:0
hosting_id:       nullable, exists:hostings,id
service_provider_id: nullable, exists:service_providers,id
is_cloudflare:    boolean
cloudflare_zone_id:    nullable, string, max:255
cloudflare_account_id: nullable, string, max:255
dns:              nullable, string
nameservers:      nullable, string
country_code:     nullable, string, size:2
username:         nullable, string, max:255
password:         nullable, string
registrar_url:    nullable, url
```

### Hosting Store/Update Request

```
name:             required, string, max:255
cpanel_url:       nullable, url
plan_type:        required, in:[list of plan types from enum]
plan_name:        nullable, string, max:255
server_ip:        nullable, string, max:45
username:         nullable, string, max:255
password:         nullable, string
expire_date:      nullable, date
cost:             nullable, numeric, min:0
service_provider_id: nullable, exists:service_providers,id
monitoring_url:   nullable, url
is_monitoring_active: boolean
```

### ExpiryTracker Store/Update Request

```
name:               required_if:trackable_type,null, string, max:255
expire_date:        required_if:trackable_type,null, date
cost:               nullable, numeric, min:0
renewal_status:     required_if:trackable_type,null, string, in:[...]
notify_before_days: nullable, integer, min:0, max:365
is_completed:       boolean
user_id:            nullable, exists:users,id
module_id:          nullable, exists:modules,id
notes:              nullable, string
trackable_type:     nullable, string, in:[...morph aliases...]
trackable_id:       required_with:trackable_type, integer, exists:[...]
```

### VaultEntry Store/Update Request

```
service_name: required, string, max:255
url:          nullable, url
username:     nullable, string, max:255
password:     nullable, string
module_id:    nullable, exists:modules,id
module_type:  nullable, string (not always validated as morph — NEEDS CONFIRMATION)
```

### Attachment Store Request

```
file:         required, file, mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip, max:10240
notable_type: required, string
notable_id:   required, integer
```

### User Store/Update Request

```
name:     required, string, max:255
email:    required, email, unique:users,email,{id}
password: required|confirmed (on create), nullable|confirmed (on update), min:8
role:     required, exists:roles,name
```

## Important Edge Cases in Validation

### 1. Linked ExpiryTracker
- `name` and `expire_date` are NOT required for linked trackers (synced from source).
- `required_if:trackable_type,null` handles this — standalone requires them, linked does not.
- Unique constraint: no duplicate `(trackable_id, trackable_type)` pairs.

### 2. Soft Delete + Unique Validation
- `unique:table,column` checks against ALL records including soft-deleted ones.
- Example: `unique:domains,name` will fail if a soft-deleted domain has the same name.
- This is an existing behavior, not a bug — names must be unique across all records including trashed.

### 3. Password on Update
- Password field on all entities is `nullable` on update — omitting it leaves existing value intact.
- Controllers handle this by only updating password if the field is present and not null.

### 4. Foreign Key to Soft-Deleted Records
- `exists:table,column` validation does NOT check `deleted_at`.
- A form can reference a soft-deleted record as a parent → this WILL pass validation.
- Controllers use `->withTrashed()` on the parent relation when needed (inconsistent — some controllers forget this, causing 404 on soft-deleted parents).

### 5. Numeric Cost
- Stored as decimal. Validation uses `numeric` (accepts integer, float, string number).
- No currency symbol validation — numbers only.
- No `min:0` on all cost fields (inconsistent — some have it, some don't).

### 6. Boolean Fields
- Checkboxes that are unchecked are NOT sent in the POST body.
- Controllers handle this via `$request->boolean('field_name')` which returns false for missing fields.
- Blade forms use a hidden field before the checkbox: `<input type="hidden" name="is_cloudflare" value="0">`.

### 7. URL Validation
- Laravel URL validator requires protocol (http:// or https://).
- `nullable, url` fails if user enters "example.com" without protocol.
- Some URLs may fail validation on international domains (punycode not automatically converted? NEEDS CONFIRMATION).

### 8. Date Validation
- `date` validator accepts many formats including `d/m/Y`, `m/d/Y`, `Y-m-d`.
- MySQL stores as `Y-m-d`. If user enters `15/03/2025`, Laravel's `date` validator passes, MySQL stores as `2025-03-15` after casting.
- **Potential issue:** Localized date format confusion (e.g., 03/04/2025 = March 4 or April 3?). MySQL uses YYYY-MM-DD after casting.

### 9. File Upload Limits
- Max: 10240 KB (10 MB).
- Allowed types: pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif, zip.
- File is renamed to UUID on storage (original name preserved in database record).
- No client-side file size check — relies on PHP `upload_max_filesize` and `post_max_size` INI settings.
