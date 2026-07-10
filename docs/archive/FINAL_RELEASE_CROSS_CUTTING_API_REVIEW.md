# FINAL_RELEASE_CROSS_CUTTING_API_REVIEW.md

**Date:** 2026-07-09

---

## Summary

11 API controllers use `WHERE user_id` instead of `RbacScope`. Web controllers use module-based RBAC scoping. This means API and Web return **different data** for the same user.

---

## Affected API Controllers

| # | Controller | Filter | Fix Required |
|---|------------|--------|-------------|
| 1 | `Api\DomainController.php` | `WHERE user_id` | → RbacScope |
| 2 | `Api\HostingController.php` | `WHERE user_id` | → RbacScope |
| 3 | `Api\VpsController.php` | `WHERE user_id` | → RbacScope |
| 4 | `Api\VoipController.php` | `WHERE user_id` | → RbacScope |
| 5 | `Api\ServiceProviderController.php` | `WHERE user_id` | → RbacScope |
| 6 | `Api\DomainEmailController.php` | `WHERE user_id` | → RbacScope |
| 7 | `Api\SslCertificateController.php` | `WHERE user_id` | → RbacScope |
| 8 | `Api\ClientController.php` | `WHERE user_id` | → RbacScope |
| 9 | `Api\BackupController.php` | `WHERE user_id` | → RbacScope |
| 10 | `Api\DnsController.php` | `WHERE user_id` | → RbacScope |
| 11 | `Api\NoteController.php` | `WHERE user_id` | → RbacScope |

---

## Other API Issues

| Issue | Controller | Detail | Priority |
|-------|------------|--------|----------|
| Super-admin assign | `Api\UsersController` | Missing `preventSuperAdminAssignment()` | P1 |
| Self-demotion | `Api\UsersController` | Missing self-demotion check | P1 |
| User filter acceptable | `Api\UserController` | Uses `WHERE company_id` — acceptable for User model | ✅ |

---

## Fix Pattern

```php
// BEFORE
return $query->where('user_id', auth()->id());

// AFTER
RbacScope::apply($query, $moduleCode);
```
