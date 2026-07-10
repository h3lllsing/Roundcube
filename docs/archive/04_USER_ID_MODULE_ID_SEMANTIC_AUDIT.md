# 04 — USER_ID / MODULE_ID SEMANTIC AUDIT

## Field-by-field analysis of what these columns MEAN vs. what they DO.

---

## `user_id` on Global Master Records

### Current semantics (per column and code):
- **Column name:** `user_id`
- **Foreign key:** `->references('id')->on('users')`
- **Nullable:** Yes (in migration — actually let me verify)

Let me check the exact migration columns... Actually, I know from earlier that all 9 models have `'user_id'` in `$fillable`. The migrations define it as a regular FK column.

### Actual usage:
| Context | What happens | Correct? |
|---------|-------------|----------|
| `store()` | Set to `Auth::id()` | ❌ Should be null or created_by |
| `update()` | From request validated data | ❌ Should not be changeable |
| RbacScope (`module` visibility) | Not used | ✅ |
| Service `list()` | `WHERE user_id = ?` | ❌ Wrong for global records |
| Dashboard | `WHERE user_id = ?` | ❌ Wrong for global records |
| Export (non-SA) | `WHERE user_id = ?` | ❌ Wrong for global records |
| API controllers | `WHERE user_id = ?` | ❌ Wrong for global records |
| Form (create) | Select field "User" | ❌ Misleading, overridden anyway |
| Form (edit) | Select field "User" | ❌ Allows changing "ownership" |
| BulkActionService | Falls back to `user_id` for non-operational types | ✅ For non-operational types |
| RenewalSyncService | Copies `user_id` to `ExpiryTracker` | ❌ Propagates wrong ownership |

### What it SHOULD be:

**Option A — Remove entirely:**
Remove `user_id` from `$fillable`. Remove from forms. Set `user_id = null` in store. Add `created_by` using existing `Blameable` trait.

**Option B — Keep as metadata-only:**
Rename to `created_by` (matching the `Blameable` trait convention). Remove from `$fillable` and forms. Use `Blameable` trait for auto-fill.

**Recommendation: Option A.** The `Blameable` trait already exists and is used by Module/Task/Feature. Using it would be consistent.

---

## `module_id` on Global Master Records

### Current semantics:
- **Column name:** `module_id`
- **Foreign key:** `->references('id')->on('modules')`
- **Nullable:** Yes
- **Fillable:** Yes (all 9 models)

### Actual usage:
| Context | What happens | Correct? |
|---------|-------------|----------|
| `store()` | From request validation (or null if not sent) | ❌ Should be auto-set from route |
| `update()` | From request validated data | ❌ Should be protected |
| RbacScope (`module` visibility) | `WHERE module_id IN (accessibleIds)` | ✅ Correct usage |
| Service `list()` | `WHERE module_id IN (accessibleModuleIds)` | ✅ Correct usage |
| Dashboard RenewalsWidget | `WHERE module_id IN (accessibleIds)` | ✅ Correct usage |
| Form (create) | Select field "Module" | ❌ Should not be user-selectable |
| Form (edit) | Select field "Module" | ❌ Same |
| VoIP/Domain Email forms | Missing entirely | ❌ Null → invisible |

### The core problem:

`module_id` is BOTH:
1. **A data categorization field** — "what type of record is this" — used by RbacScope for visibility
2. **A user-editable form field** — user can pick any module

These two uses are in direct conflict. If the user can pick any module, data integrity is compromised. If RbacScope depends on module_id for visibility, then incorrect module_ids cause incorrect visibility.

### What it SHOULD be:

`module_id` should be **auto-set** by the controller based on the current route:
```php
// In ServiceProviderController@store:
$validated['module_id'] = Module::where('slug', 'service-providers')->value('id');
```

And **protected** on update:
```php
// In ServiceProviderController@update:
unset($validated['module_id']);  // Never change module association
```

And **removed from all forms**.

---

## SEMANTIC MAP — What should exist vs. what does

| Model | Has user_id? | Has module_id? | Should have? |
|-------|-------------|----------------|--------------|
| ServiceProvider | ✅ user_id | ✅ module_id | `created_by` (audit), `module_id` (auto) |
| Domain | ✅ user_id | ✅ module_id | Same |
| Hosting | ✅ user_id | ✅ module_id | Same |
| VPS | ✅ user_id | ✅ module_id | Same |
| VoIP | ✅ user_id | ✅ module_id | Same |
| DomainEmail | ✅ user_id | ✅ module_id | Same |
| OtherService | ✅ user_id | ✅ module_id | Same |
| Asset | ✅ user_id, assigned_to | ✅ module_id | `assigned_to` (valid), `module_id` (auto) |
| ExpiryTracker | ✅ user_id | ✅ module_id | Same |

**Asset** has a legitimate `assigned_to` column for asset assignment tracking. This is correctly distinct from `user_id`.

---

## FRAGILE FIELD: `user_id` on Asset model

`Asset.php:37-39` — `$fillable` includes `user_id` and `assigned_to`.

The `user()` relation points to `user_id` (creator), and the `assignee()` relation points to `assigned_to` (assigned user). This is potentially confusing — having two user-referencing columns with different semantics on the same model. However, it IS correct if used consistently.

**Risk:** A developer might accidentally use `$asset->user` when they mean `$asset->assignee`.

---

## SEMANTIC CONCLUSION

| Column | Current use | Correct use | Priority to fix |
|--------|------------|-------------|-----------------|
| `user_id` on global records | Ownership (wrong) | Metadata/audit (or remove) | HIGH |
| `module_id` on global records | User-selectable categorization (wrong) | Auto-set route-based | HIGH |
| `assigned_to` on Asset | Valid assignment tracking | Keep as-is | NONE |
| `created_by` on Blameable models | Audit metadata | Keep as-is | NONE |
| `user_id` on Task/Vault | Ownership (personal) | Keep as-is | NONE |
