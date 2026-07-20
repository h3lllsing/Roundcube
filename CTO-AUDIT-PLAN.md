# CTO AUDIT — Action Plan

## Phase 1 🔴 Critical Security (Do First)
| # | Task | Files | Est. |
|---|------|-------|------|
| 1 | ActivityLog — restrict to super-admin only | `app/Http/Controllers/Web/ActivityLogController.php` | 5 min |
| 2 | LoginAudit — restrict to super-admin only | `app/Http/Controllers/Web/LoginAuditController.php` | 5 min |
| 3 | EmailAccount password — prevent serialization leak | `app/Models/EmailAccount.php` (custom cast class) | 30 min |

## Phase 2 🟡 Authorization & Rate Limiting
| # | Task | Files | Est. |
|---|------|-------|------|
| 4 | Standardize auth pattern (pick one: abort_unless in controller) | `DomainController`, `EmailAccountController`, `EmailAssignmentController` | 20 min |
| 5 | Remove unused Policies | `app/Policies/DomainPolicy.php`, `App/Policies/EmailAccountPolicy.php`, `AuthServiceProvider.php` | 5 min |
| 6 | Apply rate limiters to routes (search, export, bulk, import) | `routes/web.php` | 15 min |

## Phase 3 🟡 Data Integrity
| # | Task | Files | Est. |
|---|------|-------|------|
| 7 | Domain unique validation — soft-delete aware | `app/Http/Controllers/Web/DomainController.php` | 5 min |
| 8 | User unique validation — soft-delete aware | `app/Http/Controllers/Web/UserController.php` | 5 min |
| 9 | User soft delete — set deleted_by | `app/Http/Controllers/Web/UserController.php` | 5 min |
| 10 | Profile update — add optimistic lock | `AuthController`, `auth/profile.blade.php` | 10 min |
| 11 | Dashboard cache invalidation on create/update/delete | `DomainController`, `EmailAccountController`, `UserController` + `DashboardService` | 15 min |

## Phase 4 🟢 UX Polish
| # | Task | Files | Est. |
|---|------|-------|------|
| 12 | Password policy hint on register + profile forms | `auth/register.blade.php`, `auth/profile.blade.php` | 5 min |
| 13 | Route naming: `email-accounts.auto-discover` → `email_accounts.auto-discover` | `routes/web.php`, `email-accounts/create.blade.php` | 5 min |
| 14 | Remove empty providers (MorphMap, EventServiceProvider) | `bootstrap/app.php`, provider files | 5 min |

## Phase 5 🏗️ Architecture (Future)
| # | Task | Files | Est. |
|---|------|-------|------|
| 15 | PHP 8.1 Enums for status fields | `app/Enums/AccountStatus.php`, `DomainStatus.php`, `LoginEvent.php`; update models | 30 min |
| 16 | Unit/Feature tests for critical paths | `tests/Feature/AuthTest.php`, `EmailAccountTest.php`, `DomainTest.php` | 2-3 hrs |
| 17 | API routes (future-proofing) | `routes/api.php` | varies |
