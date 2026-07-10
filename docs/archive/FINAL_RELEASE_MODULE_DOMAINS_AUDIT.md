# FINAL_RELEASE_MODULE_DOMAINS_AUDIT.md

**Date:** 2026-07-09
**Sources:** CTO-04, CTO-05, CTO-06, Delete/Restore Safety Audit, Concurrency Audit, Code Quality Audit

---

## TASK-DOM-001: Model — Domain
**File:** `app/Models/Domain.php`
**Priority:** ✅ COMPLETE

| Check | Status |
|-------|--------|
| Model exists | ✅ Yes |
| Fillable defined | ✅ Yes |
| SoftDeletes | ✅ Yes |
| Relationships (hosting, module, user, serviceProvider) | ✅ Yes |
| Activity logging (LogsActivity) | ✅ Yes |
| Factory | ✅ Yes |

---

## TASK-DOM-002: Controller — Web DomainController
**File:** `app/Http/Controllers/Web/DomainController.php`
**Priority:** ✅ COMPLETE

| Check | Status |
|-------|--------|
| CRUD methods | ✅ Full (index, create, store, show, edit, update, destroy) |
| Restore/ForceDelete | ✅ Yes |
| Permission checks | ✅ RbacScope + module permission |
| Form Request validation | ✅ StoreDomainRequest, UpdateDomainRequest |
| Extends BaseResourceController | ✅ Yes |

---

## TASK-DOM-003: Controller — API DomainController
**File:** `app/Http/Controllers/Api/DomainController.php`
**Priority:** ⚠️ PARTIAL

| Check | Status |
|-------|--------|
| CRUD methods | ✅ Full |
| Permission checks | ⚠️ Uses `WHERE user_id` instead of RbacScope |
| Status | Fix pending — align API visibility with Web. |

---

## TASK-DOM-004: Views
**Files:** `resources/views/domains/{index,create,edit,show}.blade.php`
**Priority:** ✅ COMPLETE

| Check | Status |
|-------|--------|
| Index | ✅ Lists domains with filters |
| Create | ✅ Form with all fields |
| Edit | ✅ Form with old values |
| Show | ✅ Detail view with activity timeline + notes thread |

---

## TASK-DOM-005: Import/Export
**File:** `app/Support/DataTypeConfig.php`
**Priority:** ✅ COMPLETE

| Check | Status |
|-------|--------|
| Export type | ✅ 'domains' registered |
| Import type | ✅ 'domains' registered |
| Import module mapping | ✅ 'domains' → 'domains' |

---

## TASK-DOM-006: Tests
**File:** `tests/Feature/DomainTest.php`
**Priority:** ✅ COMPLETE

| Check | Status |
|-------|--------|
| Test file exists | ✅ Yes |
| Create test | ✅ Yes |
| List test | ✅ Yes |
| Show test | ✅ Yes |
| Update test | ✅ Yes |
| Delete test | ✅ Yes |
| Web CRUD tests | ✅ Yes |
| Export test | ✅ Yes |

---

## TASK-DOM-007: Known Issues
**Source:** Various audits
**Priority:** 🟡 P1

| Issue | Status | Detail |
|-------|--------|--------|
| `user_id` in fillable (C-02) | ⏳ Pending | Remove from fillable — record is company-owned |
| `module_id` user-selectable on form | ⏳ Pending | Auto-fill from route instead |
| API uses `WHERE user_id` | ⏳ Pending | Replace with RbacScope |
| Missing optimistic locking | ⏳ Pending | Add `updated_at` check |
| Dependent DomainEmails check on delete | ⏳ Pending | Check before allowing delete |
