# CTO FINAL RELEASE DATABASE

**Date:** 2026-07-09

---

## Entity Tables (28 modules)

| Module | Table | Model | SoftDeletes |
|--------|-------|-------|-------------|
| Domain | `domains` | Domain | ✅ |
| Hosting | `hostings` | Hosting | ✅ |
| VPS | `vps` | Vps | ✅ |
| VoIP | `voip` | Voip | ✅ |
| ServiceProvider | `service_providers` | ServiceProvider | ✅ |
| DomainEmail | `domain_emails` | DomainEmail | ✅ |
| SslCertificate | `ssl_certificates` | SslCertificate | ✅ |
| Client | `clients` | Client | ✅ |
| Backup | `backups` | Backup | ✅ |
| Dns | `dns` | Dns | ✅ |
| MailDomain | `mail_domains` | MailDomain | ✅ |
| Mailbox | `mailboxes` | Mailbox | ✅ |
| MailIncoming | `mail_incoming` | MailIncoming | ✅ |
| MailForwarder | `mail_forwarders` | MailForwarder | ✅ |
| MailWarmup | `mail_warmups` | MailWarmup | ✅ |
| Note | `notes` | Note | ✅ |
| Subscription | `subscriptions` | Subscription | ✅ |
| Task | `tasks` | Task | ✅ |
| Asset | `assets` | Asset | ✅ |
| ExpiryTracker | `expiry_trackers` | ExpiryTracker | ✅ |
| Monitoring | `monitoring` | Monitoring | ✅ |
| Webhook | `webhooks` | Webhook | ✅ |
| SupportTicket | `support_tickets` | SupportTicket | ✅ |
| KnowledgeBase | `knowledge_base_articles` | KnowledgeBase | ✅ |
| Vault | `vaults` | Vault | ✅ |
| OtherService | `other_services` | OtherService | ✅ |
| SmsProfile | `sms_profiles` | SmsProfile | ✅ |
| Calendar | `calendar_events` | CalendarEvent | — |

---

## System Tables

| Table | Purpose |
|-------|---------|
| `users` | User accounts |
| `roles` | RBAC roles |
| `permissions` | Permission flags |
| `module_definitions` | Module registry |
| `module_groups` | Module grouping |
| `module_role_permissions` | Role-module permissions |
| `user_module_permissions` | User permission overrides |
| `privileges` | Legacy (unused) |
| `settings` | App settings |
| `activity_logs` | Audit trail |
| `notification_logs` | Notification history |
| `notification_templates` | Notification templates |
| `email_logs` | Sent email log |
| `module_email_logs` | Module-specific email log |
| `login_audits` | Login attempts |
| `sessions` | User sessions |
| `password_resets` | Password resets |
| `personal_access_tokens` | API tokens |
| `cache` | Cache store |
| `jobs` | Queue jobs |
| `failed_jobs` | Failed queue jobs |
| `migrations` | Migrations ran |

---

## Missing Indexes (P1)

| Column | Table |
|--------|-------|
| `department_id` | `users` |
| `category_id` | `assets` |
| `type_id` | `assets` |
| `location_id` | `assets` |
| `assigned_to` | `assets` |
| `assigned_to` | `monitoring` |
| `department_id` | `monitoring` |
| `assigned_to` | `expiry_trackers` |
| `category_id` | `knowledge_base_articles` |
| `deleted_at` | All 18 soft-delete tables |
| `status` | All 10+ service tables |
