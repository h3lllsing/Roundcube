# OpsPilot Page-by-Page Audit

> Faster findings from route/sidebar/controller analysis.
> Full Blade rendering audit requires browser verification (PENDING).

---

## 1. Dashboard (`/dashboard`)

| Aspect | Finding |
|--------|---------|
| Controller | `DashboardController@index` |
| Sidebar | Top item, always visible |
| Access | `auth + suspended` |
| Status | ✅ UX Batch 12 complete — reordered by urgency, collapsible sysadmin sections |
| Concerns | None identified from source analysis |

---

## 2. Monitoring (`/monitoring`, `/monitor/{type}/{id}`)

| Aspect | Finding |
|--------|---------|
| Controller | Split: `MonitoringOverviewController@index` + `MonitorController@check` |
| Sidebar | `$showMonitoring` flag |
| UX Batch | Batch 10 complete |
| Concerns | Two controllers with overlapping responsibility. `/monitor/{type}/{id}` is a general-purpose check that can check any resource type — risk of misuse if `type` is not validated properly. |

---

## 3. Infrastructure Modules

All infrastructure modules follow the same pattern after UX batches:

### Hostings, Domains, VPS, VoIP, Other Services, Domain Emails
| Aspect | Finding |
|--------|---------|
| Index | ✅ Simplified, action in ⋮ dropdown |
| Show | Standard accordion detail view |
| Create/Edit | Standard form |
| UX Batch | Batches 1-11 complete |
| Concerns | See inconsistencies below |

### Per-Module Quirks

**Hostings** (Batch 1-3, 4):
- `id` parameter (line 129+), password + copy at throttle 10/min
- Standard CRUD + soft-delete

**Domains** (Batch 6):
- `id` parameter (line 104+)
- Standard CRUD + soft-delete
- No password/copy operations (domains don't have passwords)

**VPS** (Batch 4-5):
- `{vp}` → uses `Route::model('vp', Vps::class)' or similar
- Password + copy + throttle 10/min
- Used in UX Batch 4 as the "hero" module

**VoIP** (Batch 11):
- Most extra operations of any infrastructure module:
  - password (10/min), password.cop
  - extension-password (10/min), extension-password.copy
- This is correct — VoIP has both account and extension passwords

**Other Services** (Batch 11):
- Uses `Route::resource` + manual password routes
- Has password + copy (like hosting/VPS)

**Domain Emails** (Batch 11):
- Uses `Route::resource` + manual password route
- No password copy (unlike others — may be intentional)

**Service Providers** (Batch 7):
- Standard CRUD + password (no copy)
- Soft-delete enabled

**Expiry Trackers** (Batch 8):
- **Most extra routes**: preview-email, test-email, send-reminder, notification-history, renew
- Sidebar label: "Renewals" — logical name
- `{expiry_tracker}` parameter

**Assets** (Batch 11):
- Extra operations: `assign`, `return`, `returnAsset`
- Tracks hardware check-in/check-out
- Soft-delete with force-delete

**G-Mails** (Batch 11):
- Standard CRUD + password (no copy)
- Soft-delete with force-delete

---

## 4. Credentials (Vault)

| Aspect | Finding |
|--------|---------|
| UX Batch | Batch 11 complete |
| Routes | `vault.index`, `vault.my`, `vault.show`, `vault.reveal` (throttled 10/min) |
| Sidebar | Split into "My Credentials" and "Shared Credentials" |
| **Issue** | `vault.reveal` is a POST route that reveals the actual credential value — this is a sensitive operation. The throttle is appropriate but audit logging should also be verified in the controller. |
| Active State | Sidebar uses exclusion logic: `vault.*` && !`vault.my` |

---

## 5. Operations (Tasks, Calendar, Notes)

### Tasks
| UX Batch | Batch 11 complete |
| Routes | `tasks.index`, `tasks.my`, `tasks.kanban`, `tasks.show` etc. |
| Extra | `update-status` (PATCH), `my-counts` (API for dashboard badges) |
| Soft-delete | Restore enabled, force-delete NOT enabled |

### Calendar
| Routes | Single route: `calendar` |
| UX Batch | Batch 12 covered collapsible sections — calendar was not part of UX batches (already clean) |

### Notes
| UX Batch | Batch 11 complete |
| Extra | `togglePin` (PATCH `notes.pin`) |
| Soft-delete | Fully implemented with force-delete |

---

## 6. Administration (Super-Admin Only)

### Users
| UX Batch | Batch 3b (Index), Batch 4 (Show accordion) |
| Routes | Most extensive: 15 routes |
| Extra | `login-as` (security-sensitive), `suspend`/`unsuspend`, `clone` (GET+POST), `permissions` (edit+update) |
| **Login As Concern**: `POST /users/{user}/login-as` allows a super-admin to impersonate any user. This must be heavily audited. The route itself doesn't show throttle middleware — consider adding rate limiting. |

### Roles / Role Templates / Privileges
| Prefix | `/admin/` |

**Role Templates Issue** (lines 315-317):
```
GET  /role-templates/{id}/apply  → RoleTemplateController@apply
POST /role-templates/{id}/apply  → RoleTemplateController@apply
```
Same controller method handles both GET and POST. If `apply()` performs the role assignment on GET (without CSRF token), this is a security vulnerability (conflating read and write on the same endpoint). **Must verify controller logic.**

### SMTP Profiles
| Routes | Most admin routes: 12 total |
| UX Batch | Batch 9 complete |
| Extra | `auto-discover`, `test`, `set-default`, `toggle-active`, `duplicate` |

### Module Permissions
| Routes | Only 3: index, update (POST), destroy (DELETE) |
| **Concern** | No individual record management — likely a JSON config dump. `destroy` has no `{id}` parameter — deletes all? |

### Webhooks
| Routes | Full resource + `test` |
| Sidebar | Labeled "Integrations" — user-friendly name |

### Tokens
| Routes | index + create + store + destroy only (no show/edit) |
| API Token Pattern | Correct — tokens are shown once at creation and stored as hashes |

### Activity Logs
| Routes | index + show only — read-only (as expected for an audit log) |

### Login Audits
| Routes | index + show + destroy (destroy for cleanup, not regular use) |

### Import
| Routes | create (GET form) + store (POST CSV) |
| Sidebar | Under Administration |

### Attachments
| Routes | Full CRUD minus edit + download + force-delete |
| Sidebar | Under Administration |

---

## 7. Reports

| Controller | `ReportController@index`, `show`, `category`, `export` |
| Sidebar | Single-item collapsible section under `@hasrole('super-admin')` |
| Concern | Single-item collapsible group is unnecessarily complex — could be flat link |

---

## 8. Account & Profile

### Profile (`/profile`)
| Controller | `AuthController@profile` + `AuthController@updateProfile` |
| Access | All authenticated users |
| Status | ✅ Clean |

### My Access (`/my-permissions`)
| Controller | `AuthController@myPermissions` |
| Access | All authenticated users |
| Status | ✅ Clean |

### Help Center (`/guide`, `/help/{slug}`)
| Controller | `HelpController` (not namespaced under Web) |
| Routes | `guide` (main page), `help.show` (article), `help.search`, `help.module` |
| Status | ✅ Clean |

---

## 9. Cross-Cutting Concerns

### Search (`/search`)
- Middleware: `throttle:search`
- Single endpoint — searches all resources
- No per-module search filtering in route — all in controller

### Export (`/export/{type}`)
- Middleware: `throttle:export`
- Single endpoint for exporting any resource type
- No per-module permission check — any auth user can export any resource type

### Bulk Action (`/bulk-action`)
- Middleware: `throttle:bulk`
- Single endpoint handling bulk operations across all resources
- **Must verify controller has per-module permission checks**

---

## 10. Pages Missing from Audit

The following pages require browser/Blade inspection to fully audit:
- All `index` views (27+ modules) — confirm ⋮ dropdown actions work
- All `show` views — confirm accordion layout is correct
- All `create/edit` forms — confirm field labels and validation
- `users/permissions.blade.php` — complex permissions matrix UI
- `help/index.blade.php` — help article rendering + sidebar TOC
- `guide.blade.php` — static guide content

**Browser verification**: PENDING across all modules.
