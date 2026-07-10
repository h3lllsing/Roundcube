# Known Limitations — OpsPilot v1.0

> This document describes intentionally accepted limitations, deferred features, and environmental constraints for the v1.0 release.

---

## Environmental Constraints

### 1. Shared Hosting Deployment

OpsPilot is designed and tested for **cPanel shared hosting**. The following constraints apply:

| Limitation | Impact | Mitigation |
|---|---|---|
| No Redis/Memcached | Cache uses file driver; no session sharing across multiple servers | Acceptable for single-server shared hosting |
| No Supervisor | Queue must run via cron with `--stop-when-empty` | Documented in `CPANEL_DEPLOYMENT_GUIDE.md` |
| No dedicated queue process | Job processing may be delayed up to 59 seconds (cron interval) | Acceptable for email notifications; not suitable for real-time processing |
| `exec()` and `proc_open()` may be disabled | Artisan commands and some PHP features may fail | Contact host support or use alternative queue strategy |
| Limited cron frequency | Minimum 1-minute interval | All scheduled tasks tolerate this interval |

### 2. No Redis

The application does not use Redis. Cache, session, and queue drivers are configured for file/database. Redis-related `.env` keys (`REDIS_*`, `MEMCACHED_*`) should be removed from production `.env`.

---

## Functional Limitations

### 3. Email Sending Requires SMTP Configuration

| Limitation | Details |
|---|---|
| Outbound email requires valid SMTP | `.env` currently has `MAIL_MAILER=log` — no emails are sent until SMTP is configured |
| No built-in transactional email service | Must use external SMTP (Mailgun, SendGrid, Gmail, or hosting SMTP) |
| No email queue retry after repeated failures | Jobs retry up to 3 times then go to `failed_jobs` table; manual intervention required to retry |

### 4. File Storage

| Limitation | Details |
|---|---|
| Local filesystem only | No S3/Cloud storage integration configured |
| `public/storage` symlink required | Must be created via `php artisan storage:link` for uploaded files to be web-accessible |
| No file versioning | Uploaded files are overwritten on re-upload |
| Attachment size limited by PHP | Default `upload_max_filesize` and `post_max_size` apply |

### 5. Search

| Limitation | Details |
|---|---|
| Global search uses `LIKE` queries | Not a full-text search engine; performance may degrade with very large datasets |
| No search indexing | Each search performs live database queries |
| Search scope limited to specific modules | Only domains, hostings, VPS, VoIP, users, notes, vault entries |

### 6. Monitoring

| Limitation | Details |
|---|---|
| Simple HTTP ping only | Checks if URL returns a 2xx/3xx response; no content validation |
| No SSL certificate expiry monitoring | Certificate validation is not performed |
| Monitoring runs hourly | Not real-time; up to 59-minute delay between checks |

### 7. Notifications

| Limitation | Details |
|---|---|
| In-app notifications only (v1.0) | No push notifications, SMS, or Slack integration |
| Email notification delivery depends on queue | Up to 59-second delay due to cron-based queue worker |
| No notification read receipts | No confirmation that user has seen a notification |
| Expiry tracker notification history is append-only | Old entries cannot be edited, only deleted individually |

### 8. API

| Limitation | Details |
|---|---|
| API documentation (Swagger/OpenAPI) contains localhost references | `app/OpenApi.php` has hardcoded `http://localhost:8000/api`; should be updated for production domain |
| Static API tokens only | No OAuth, JWT, or social auth |
| Token revocation is immediate | No grace period for token expiration |

### 9. Multi-Tenancy

| Limitation | Details |
|---|---|
| Single-tenant only | All users share the same database; no tenant isolation |
| User suspension is manual | No automatic account expiry or inactivity timeout |

### 10. Import

| Limitation | Details |
|---|---|
| CSV import only | No Excel, JSON, or API-based bulk import |
| Import validation is basic | Column mapping and fillable validation only; no relationship validation during import |
| Users import requires password | Passwords must be provided in the import file (or will be hashed) |

---

## Database / Schema Limitations

### 11. Soft Delete Inconsistency

Not all models implement SoftDeletes. Soft delete is applied to major resource models but some join/pivot tables use hard deletes. This is an accepted design decision for v1.0.

**Models WITH SoftDeletes:** Users, Domains, Hostings, VPS, VoIP, Service Providers, Domain Emails, Other Services, Tasks, Notes, Vault Entries, Assets, Expiry Trackers, Webhooks, Attachments, Features, Modules

**Acceptable missing SoftDeletes:** Role/privilege pivot tables (hard delete on detach is correct behavior), activity logs (append-only), login audits (append-only), notifications (can be hard-deleted by user)

### 12. Performance Indexes

An optimization migration (`2026_06_27_000001_add_performance_indexes`) adds indexes to support common query patterns. For deployments with fewer than 10,000 records per table, these indexes provide minimal benefit but no overhead.

---

## Frontend Limitations

### 13. JavaScript Framework

Alpine.js is used for interactivity. This imposes limitations:

| Limitation | Details |
|---|---|
| No real-time updates | Pages must be refreshed to see new data |
| No optimistic UI updates | All mutations wait for server response |
| No drag-and-drop Kanban | Task status changes are form-based, not drag-and-drop |
| No WebSocket/polling | Notifications are not delivered in real-time |

### 14. Dark Mode

| Limitation | Details |
|---|---|
| Dark mode follows system preference by default | User can toggle but preference resets on new devices (stored in localStorage) |
| Login page background image assumes dark background | Light-mode users see a dark-toned background image |

### 15. Responsive Design

| Limitation | Details |
|---|---|
| Complex tables may scroll horizontally on mobile | Tables with 8+ columns require horizontal scroll |
| Kanban view is not mobile-optimized | Best viewed on desktop/tablet |
| Dashboard widgets stack vertically on small screens | Layout may be very long on mobile |

---

## Security / Compliance Limitations

### 16. Audit Trail

| Limitation | Details |
|---|---|
| Activity log is append-only | No log rotation mechanism; logs grow indefinitely without manual cleanup |
| Login audit log includes IP addresses | Privacy regulations (GDPR) may require IP anonymization policies |
| No data retention policy | Historical data is never automatically purged |

### 17. Password Storage

| Limitation | Details |
|---|---|
| Vault passwords are encrypted with APP_KEY | If APP_KEY is lost, all vault passwords become unrecoverable; backup APP_KEY securely |
| Service passwords (hosting, VPS, etc.) are stored in plaintext in the database | These are visible to users with view access; access control is the only protection |
| Password reveal is audited but not rate-limited | A user with access to many passwords could rapidly reveal them all |

### 18. CORS Configuration

| Limitation | Details |
|---|---|
| CORS defaults to `http://localhost:3000` | Must be updated in production `.env` by setting `FRONTEND_URL` |
| Sanctum stateful domains include localhost defaults | Must be updated in production `.env` by setting `SANCTUM_STATEFUL_DOMAINS` |

---

## Deployment Limitations

### 19. First Deployment Complexity

| Limitation | Details |
|---|---|
| No one-command deploy script | Deployment requires 15+ manual steps |
| Database seeder passwords are publicly known | Seeder credentials (`admin@tyro.project` / `password`) must be changed after seed; create new admin account and delete default |
| No staging environment provided | All testing was performed on a local XAMPP environment |

### 20. Performance Ceiling

| Limitation | Details |
|---|---|
| File-based cache degrades with many entries | Cache directory may need periodic cleanup |
| Database queue becomes a bottleneck at high volume | Heavy notification loads may cause queue backlog |
| No CDN for static assets | Frontend assets served directly from the server |
| No query cache / query optimization tuning | Default database settings apply |

---

## Deferred to Future Versions

These features are explicitly out of scope for v1.0 and may be added post-deployment:

| Feature | Priority | Notes |
|---|---|---|
| Push notifications | Medium | Slack/Telegram/Email integration |
| Drag-and-drop Kanban | Low | Requires frontend upgrade |
| Full-text search | Low | Elasticsearch/MeiliSearch integration |
| CSV/Excel import improvements | Low | Better validation and error reporting |
| Multi-tenant support | High for MSP | Separate databases or tenant-scoped tables |
| OAuth / SSO | Medium | Social login, LDAP, SAML |
| Audit log retention policy | Medium | Configurable auto-purge |
| Real-time dashboard updates | Low | WebSocket or Server-Sent Events |
| File versioning | Low | Multiple file revisions |
| Mobile app | Low | Dedicated mobile interface |
