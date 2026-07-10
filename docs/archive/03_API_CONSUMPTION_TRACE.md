# API CONSUMPTION TRACE — Frontend Code Audit

## Question: Is the API consumed by the frontend?

**Answer: NO.** The Web UI does NOT call any API CRUD endpoint.

---

## Methodology

Searched all `resources/` files for:
- `/api/` URL patterns
- Axios, fetch, $.ajax calls
- Route name references to API controllers
- Any mobile app, external integration, or third-party client code

---

## Result: Only ONE API call exists in the frontend

| File | Line | URL | Purpose |
|------|------|-----|---------|
| `resources/views/components/command-palette.blade.php` | 21 | `'/api/search'` | Command palette search box |
| `resources/js/command-palette.js` | 35 | `fetch(cmdSearchUrl + '?q=...&limit=3')` | Executes the search |

**This is the ONLY frontend API consumption.** The `GET /api/search` endpoint is an internal, read-only utility. It does not affect the show/update/destroy inconsistency.

---

## All CRUD Operations Use Web Controllers (NOT API)

### Evidence from Blade templates and JavaScript:

| Operation | Web Route Pattern | Example | API Route Consumed? |
|-----------|-------------------|---------|---------------------|
| List | `GET /{resource}` | `GET /domains` (Web\DomainController@index) | No |
| Show | `GET /{resource}/{id}` | `GET /domains/5` (Web\DomainController@show) | No |
| Create | `GET /{resource}/create` | `GET /domains/create` (Web\DomainController@create) | No |
| Store | `POST /{resource}` | `POST /domains` (Web\DomainController@store) | No |
| Edit | `GET /{resource}/{id}/edit` | `GET /domains/5/edit` (Web\DomainController@edit) | No |
| Update | `PUT /{resource}/{id}` | `PUT /domains/5` (Web\DomainController@update) | No |
| Destroy | `DELETE /{resource}/{id}` | `DELETE /domains/5` (Web\DomainController@destroy) | No |
| Password | `GET /{resource}/{id}/password` | `GET /hostings/5/password` | No |
| Export | `GET /export/{type}` | `GET /export/domains` (Web\ExportController) | No |
| Bulk | `POST /bulk-action` | `POST /bulk-action` (Web\BulkActionController) | No |

**All 10 business entity CRUD flows use exclusively Web controllers.** Blade forms generate `POST`/`GET`/`PUT`/`DELETE` to web routes — never `fetch('/api/...')`.

### Password reveal fetches — all Web routes:

```
resources/views/hostings/index.blade.php:108     → `url('hostings')/{id}/password`
resources/views/hostings/show.blade.php:123      → `route('hostings.password', ...)`
resources/views/vps/index.blade.php:100           → `url('vps')/{id}/password`
resources/views/vps/show.blade.php:141            → `route('vps.password', ...)`
resources/views/voip/index.blade.php:106          → `url('voip')/{id}/extension-password`
resources/views/voip/show.blade.php:110           → `route('voip.extension-password', ...)`
resources/views/domain-emails/index.blade.php:70  → `url('domain-emails')/{id}/password`
resources/views/domain-emails/show.blade.php:88   → `route('domain-emails.password', ...)`
resources/views/service-providers/index.blade.php:102 → `url('service-providers')/{id}/password`
resources/views/service-providers/show.blade.php:134  → `route('service-providers.password', ...)`
resources/views/other-services/index.blade.php:103    → `url('other-services')/{id}/password`
resources/views/other-services/show.blade.php:135     → `route('other-services.password', ...)`
```

### Other fetch calls — all Web routes:

```
resources/views/expiry-trackers/_notification-form.blade.php:143 → `route('expiry-trackers.preview-email', ...)`
resources/views/help/index.blade.php:233                        → `url('/help/'){slug}`
resources/views/help/index.blade.php:286                        → `url('/help/search')?q=...`
resources/js/permissions.js:188                                 → Server-injected web route
```

---

## API Controllers Exist But Are Unused

The following API controllers exist but have ZERO frontend consumption:

| API Controller | CRUD Endpoints | Frontend Calls? |
|----------------|---------------|-----------------|
| `Api\DomainController` | index, store, show, update, destroy | None |
| `Api\HostingController` | index, store, show, update, destroy | None |
| `Api\VpsController` | index, store, show, update, destroy | None |
| `Api\VoipController` | index, store, show, update, destroy | None |
| `Api\ServiceProviderController` | index, store, show, update, destroy | None |
| `Api\DomainEmailController` | index, store, show, update, destroy | None |
| `Api\OtherServiceController` | index, store, show, update, destroy | None |
| `Api\ExpiryTrackerController` | index, store, show, update, destroy | None |
| `Api\AssetController` | index, store, show, update, destroy | None |

**These API endpoints are exposed for external API consumers** who would authenticate via Sanctum tokens. No such consumer exists in the codebase.

---

## Mobile App or External Integration References

**None found.** The repository contains:
- No React Native, Swift, Kotlin, or mobile framework code
- No external client SDK or library
- No third-party API integration code

---

## Impact on the API show/update/destroy Inconsistency

Because the frontend does NOT consume any API CRUD endpoint:

1. **The inconsistency is invisible to end users** — they use Web controllers exclusively
2. **The inconsistency cannot cause a 403 error in the UI** — the UI never calls `GET /api/domains/{id}`
3. **The inconsistency is a v1.1 technical debt** — it only affects future API consumers

This finding **reclassifies** the API inconsistency from "potential blocker" to "documented limitation."

---

## What API IS Actually Used For (v1.0)

| Purpose | API Endpoint | Used By |
|---------|-------------|---------|
| Search | `GET /api/search` | Command palette (Blade component) |
| Future mobile app | All CRUD endpoints | None yet |
| Future external integration | All CRUD endpoints | None yet |
| API token management | `POST /api/tokens` | Web UI (token creation page) |

---

## Conclusion

**The API layer is a published contract awaiting consumers.** It is correct to ship it with known inconsistencies in v1.0 because no consumer is affected. The inconsistency must be resolved BEFORE:
1. A mobile app is built
2. An external integration is connected
3. The API is versioned to v2.0

**Recommendation:** Document the limitation in API documentation. Fix in v1.1.
