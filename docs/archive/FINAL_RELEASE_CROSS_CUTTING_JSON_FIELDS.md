# FINAL_RELEASE_CROSS_CUTTING_JSON_FIELDS.md

**Date:** 2026-07-09

---

## Models with JSON/Array Casts

| Model | JSON Field | Cast | Concurrency Risk |
|-------|-----------|------|------------------|
| Domain | `dns_servers` | `array` | 🔴 Full overwrite |
| Hosting | `additional_features` | `array` | 🔴 Full overwrite |
| Voip | `extensions` | `array` | 🔴 Full overwrite |
| SmtpProfile | `default_settings` | `array` | 🔴 Full overwrite |
| User | `module_permissions` | `array` | 🟡 Append-only pattern |

---

## Risk

JSON fields are fetched, modified in PHP, and fully replaced on `update()`. Concurrent writes to different keys within the same JSON field cause data loss.

**Example:**
1. User A loads Domain → `dns_servers = ["8.8.8.8"]`
2. User B loads same Domain → `dns_servers = ["8.8.8.8"]`
3. User A pushes "1.1.1.1" → `dns_servers = ["8.8.8.8", "1.1.1.1"]`
4. User B pushes "9.9.9.9" → `dns_servers = ["8.8.8.8", "9.9.9.9"]`
5. User A's "1.1.1.1" is LOST

---

## Mitigation Options

| Option | Complexity | Best For |
|--------|-----------|----------|
| `whereJsonContains()` for partial updates | Medium | Append-only JSON |
| Optimistic locking (`updated_at`) | Low | All JSON fields |
| DB normalization (separate table) | High | Long-term fix |
| Laravel `updateOrCreate` pattern | Medium | Keyed JSON |

**Recommended (v1.0):** Optimistic locking for all models. JSON normalization deferred.
