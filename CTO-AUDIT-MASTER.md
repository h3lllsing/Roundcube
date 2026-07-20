# 🏢 CTO AUDIT — Execution Order

> Kis tarah se, kis order mein sab kuch kerna hai — dependency-wise systematic plan

---

## 🔴 Sprint 1 — Webmail Security Lockdown (Day 1)

Sab se dangerous cheez — email passwords plaintext leak ho rahi hain. Pehle yeh band karo.

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **1** | 🔴 1.9 — Webmail SSL Verification Enable (`CURLOPT_SSL_VERIFYPEER => true`) | 10 min | None |
| **2** | 🔴 1.8 — Webmail Resolve — stop returning passwords in JSON; use SSO hash only | 1 hr | None |
| **3** | 🔴 1.7 — Webmail receive.php — stop writing plaintext passwords to `/tmp/` | 1 hr | Task 2 (resolve flow change) |
| **4** | 🟡 2.14 — Webmail duplicate plugin cleanup | 20 min | None |
| **5** | 🟡 2.15 — Webmail Resolve — add referer/origin CSRF check | 15 min | Task 2 |

---

## 🔴 Sprint 2 — Backend Authorization Gap (Day 1-2)

Jab tak webmail secure ho jaye, parallel mein yeh karo.

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **6** | 🔴 1.1 — ActivityLog — restrict to super-admin | 5 min | None |
| **7** | 🔴 1.2 — LoginAudit — restrict to super-admin | 5 min | None |
| **8** | 🟡 2.1 — Authorization pattern — standardize to `abort_unless()` | 20 min | None |
| **9** | 🟡 2.2 — Remove unused Policies + clean AuthServiceProvider | 5 min | Task 8 |

---

## 🔴 Sprint 3 — Password Security & Data Integrity (Day 2)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **10** | 🔴 1.3 — EmailAccount password — custom cast to prevent serialization leak | 30 min | None |
| **11** | 🟡 2.4 — Domain unique validation — soft-delete aware | 5 min | None |
| **12** | 🟡 2.5 — User unique validation — soft-delete aware | 5 min | None |
| **13** | 🟡 2.6 — User soft delete — set `deleted_by` | 5 min | None |
| **14** | 🟡 2.7 — Profile update — add optimistic lock | 10 min | None |
| **15** | 🟡 2.8 — Dashboard cache invalidation | 15 min | None |

---

## 🔴 Sprint 4 — Frontend Critical Fixes (Day 2-3)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **16** | 🔴 1.6 — `startLoading()` / `stopLoading()` — verify or implement globally | 20 min | None (check `app.js`) |
| **17** | 🔴 1.5 — Loading states on ALL form submit buttons | 1.5 hr | Task 16 (if needs fixing) |
| **18** | 🔴 1.4 — Auth pages — replace raw `<input>` with form components | 1 hr | None |

---

## 🟡 Sprint 5 — Frontend High Priority (Day 3-4)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **19** | 🟡 2.9 — Filter forms — activity-logs + login-audits use components | 30 min | None |
| **20** | 🟡 2.10 — User edit — role select → `x-form.select` | 10 min | None |
| **21** | 🟡 2.13 — User show — use `<x-field>` component | 15 min | None |
| **22** | 🟡 2.11 — Webmail accessibility (iframe title, Alpine events, hover text) | 30 min | None |
| **23** | 🟡 2.12 — Dashboard — add loading skeleton states | 30 min | None |

---

## 🟡 Sprint 6 — Infrastructure Hardening (Day 4-5)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **24** | 🟡 2.16 — Database schema — orphaned tables cleanup + missing indexes | 45 min | Review migration files |
| **25** | 🟡 2.17 — Composer security audit (`composer audit`, update vulns) | 30 min | None |
| **26** | 🟡 2.18 — SnappyMail version check + update if needed | 20 min | None |
| **27** | 🟢 3.8 — .env configuration audit + create/update `.env.example` | 30 min | None |
| **28** | 🟢 3.9 — Exception handling + logging review | 30 min | None |
| **29** | 🟢 3.11 — .gitignore review | 10 min | None |
| **30** | 🟡 2.3 — Apply rate limiters to routes | 15 min | None |

---

## 🟢 Sprint 7 — Frontend Medium Priority (Day 5)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **31** | 🟢 3.4 — Error pages — hide exception messages in production | 15 min | None |
| **32** | 🟢 3.5 — Error pages — JS assets review | 5 min | None |
| **33** | 🟢 3.6 — Notification search → `x-filter-input` | 10 min | None |
| **34** | 🟢 3.7 — Date columns — overflow fix on mobile | 10 min | None |
| **35** | 🟢 3.1 — Password policy hint on register/profile | 10 min | None |
| **36** | 🟢 3.2 — Route naming consistency (auto-discover) | 5 min | None |
| **37** | 🟢 3.3 — Remove empty providers | 10 min | None |
| **38** | 🟢 3.10 — Vite build configuration review | 30 min | None |

---

## 🔵 Sprint 8 — Future Architecture (Day 6+)

| Order | Task | Est. | Dependency |
|-------|------|------|------------|
| **39** | 🔵 4.1 — PHP 8.1 Enums for status fields | 30 min | Task 11, 12 (validations) |
| **40** | 🔵 4.2 — Unit / Feature tests for critical paths | 2-3 hrs | All above fixes complete |
| **41** | 🔵 4.3 — API routes (`routes/api.php`) | varies | None |
| **42** | 🔵 4.4 — Localization setup | 1-2 hrs | None |
| **43** | 🔵 4.5 — Caching strategy (query caching, view caching) | 1 hr | Task 15 (dashboard cache) |
| **44** | 🔵 4.6 — Tailwind / Design system review | 1 hr | None |

---

## 📊 Timeline Summary

| Sprint | Focus | Tasks | Est. Time | Days |
|--------|-------|-------|-----------|------|
| **Sprint 1** 🔴 | Webmail Security Lockdown | 1-5 | ~3 hrs | Day 1 |
| **Sprint 2** 🔴 | Backend Authorization Gap | 6-9 | ~35 min | Day 1-2 |
| **Sprint 3** 🔴 | Password Security & Data Integrity | 10-15 | ~1 hr | Day 2 |
| **Sprint 4** 🔴 | Frontend Critical Fixes | 16-18 | ~3 hrs | Day 2-3 |
| **Sprint 5** 🟡 | Frontend High Priority | 19-23 | ~2 hrs | Day 3-4 |
| **Sprint 6** 🟡 | Infrastructure Hardening | 24-30 | ~3 hrs | Day 4-5 |
| **Sprint 7** 🟢 | Frontend Medium Priority | 31-38 | ~1.5 hrs | Day 5 |
| **Sprint 8** 🔵 | Future Architecture | 39-44 | ~6-8 hrs | Day 6+ |
| | **Total** | **44** | **~20 hrs** | **~6 days** |

---

## 🏁 Priority Matrix

```
                    High Impact
                        │
         Sprint 1  ─────┼──── Sprint 2
         Webmail        │     Backend Auth
         Passwords      │     Gaps
         Leaking 🔴    │     🔴
                        │
   Quick ───────────────┼────────────── Complex
                        │
         Sprint 4  ─────┼──── Sprint 6
         Frontend       │     Infrastructure
         Forms/Loading  │     DB/Composer/Env
         🔴             │     🟡
                        │
                    Low Impact
```

### Sprint 1-4 🔴 (Day 1-3): DO FIRST — Security + Critical Bugs
### Sprint 5-7 🟡🟢 (Day 3-5): DO NEXT — Consistency + Polish
### Sprint 8 🔵 (Day 6+): DO LAST — Future Architecture
