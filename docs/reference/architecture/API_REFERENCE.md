# API Reference

Base URL: `/api`

Authentication: Bearer token via Sanctum (`Authorization: Bearer <token>`)

## Interactive Documentation

Swagger UI: [`/api/documentation`](/api/documentation)
OpenAPI Spec: `storage/api-docs/api-docs.json`

## Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Authenticate and receive a Sanctum token |
| POST | `/api/logout` | Revoke current token |
| GET | `/api/user` | Get authenticated user details |

## Resources

All resource endpoints follow RESTful conventions with standard status codes (200, 201, 204, 401, 403, 404, 422).

| Resource | Endpoints |
|----------|-----------|
| Users | `GET/POST /api/users`, `GET/PUT/DELETE /api/users/{id}` |
| Domains | `GET/POST /api/domains`, `GET/PUT/DELETE /api/domains/{id}` |
| Hosting | `GET/POST /api/hostings`, `GET/PUT/DELETE /api/hostings/{id}` |
| VPS | `GET/POST /api/vps`, `GET/PUT/DELETE /api/vps/{id}` |
| VoIP | `GET/POST /api/voip`, `GET/PUT/DELETE /api/voip/{id}` |
| Domain Emails | `GET/POST /api/domain-emails`, `GET/PUT/DELETE /api/domain-emails/{id}` |
| Service Providers | `GET/POST /api/service-providers`, `GET/PUT/DELETE /api/service-providers/{id}` |
| Other Services | `GET/POST /api/other-services`, `GET/PUT/DELETE /api/other-services/{id}` |
| Expiry Trackers | `GET/POST /api/expiry-trackers`, `GET/PUT/DELETE /api/expiry-trackers/{id}` |
| Assets | `GET/POST /api/assets`, `GET/PUT/DELETE /api/assets/{id}` |
| Vault | `GET/POST /api/vault`, `GET/PUT/DELETE /api/vault/{id}` |
| Webhooks | `GET/POST /api/webhooks`, `GET/PUT/DELETE /api/webhooks/{id}` |
| Tasks | `GET/POST /api/tasks`, `GET/PUT/DELETE /api/tasks/{id}` |
| Features | `GET/POST /api/features`, `GET/PUT/DELETE /api/features/{id}` |
| Modules | `GET/POST /api/modules`, `GET/PUT/DELETE /api/modules/{id}` |
| Notes | `GET/POST /api/notes`, `GET/PUT/DELETE /api/notes/{id}` |

## Special Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/search?q=<term>` | Global search across all resources |
| GET | `/api/dashboard` | Dashboard summary data |
| GET | `/api/monitor/{type}/{id}` | Run health check on a resource |
| GET/POST | `/api/export/{type}` | Export resources as CSV |
| POST | `/api/import/{type}` | Import resources from CSV |
| POST | `/api/webhooks/{id}/test` | Test a webhook endpoint |
| POST | `/api/vault/{id}/reveal` | Reveal a vault password |
| PATCH | `/api/users/{id}/suspend` | Suspend a user |

## Rate Limiting

| Limit Group | Default | Header |
|-------------|---------|--------|
| API General | 60/min | `X-RateLimit-Limit` |
| Search | 30/min | — |
| Export | 10/min | — |
| Import | 5/min | — |
| Bulk Actions | 20/min | — |

See `config/ratelimits.php` and `APP_URL/.env` for configuration.

## Authorization

- Most endpoints require the `super-admin` role.
- Non-admin users see only records they own or have module-level read permission for.
- Module-level CRUD permissions (`can_create`, `can_read`, `can_update`, `can_delete`) are enforced on per-resource basis.

For full details, refer to the Swagger UI at `/api/documentation`.
