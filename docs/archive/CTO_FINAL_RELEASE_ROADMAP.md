# CTO FINAL RELEASE ROADMAP

**Date:** 2026-07-09
**Total P0:** 14 | **Total P1:** ~49 | **Total P2:** ~30

---

## Phase 1: Pre-Deploy Blockers (Days 1-2)

| Order | Task | ID | Hours | Depends On |
|-------|------|----|-------|------------|
| 1 | Move Tinker to `require-dev` | C-18 | 0.5 | — |
| 2 | Rotate DB/SMTP/APP_KEY credentials | C-01 | 2 | 1 |
| 3 | Switch QUEUE_CONNECTION to `sync` | C-04 | 0.5 | — |
| 4 | Set APP_DEBUG=false, APP_ENV=production | — | 0.5 | — |
| 5 | Fix BulkActionService to respect SoftDeletes | C-09 | 2 | — |
| 6 | Fix VoIP/DomainEmail module_id → auto-set | C-08 | 2 | — |
| 7 | Add RbacScope null-module fallback | C-15 | 1 | 6 |
| 8 | Add SoftDeletes to Role + user-count check | C-13 | 2 | — |
| 9 | Add SoftDeletes to Privilege + role-attachment check | C-14 | 1 | — |

**Phase 1 total: ~11.5 hours**

---

## Phase 2: Data Integrity (Days 3-5)

| Order | Task | ID | Hours | Depends On |
|-------|------|----|-------|------------|
| 10 | Add activity logging to Users/Roles/Webhooks/Privileges | C-12 | 4 | — |
| 11 | Add optimistic locking to SMTP setDefault | C-11 | 2 | — |
| 12 | Add optimistic locking to all update workflows | C-10 | 8 | — |
| 13 | Fix N+1 in TaskController index | C-17 | 1 | — |
| 14 | Add `deleted_at` indexes (18 tables) | — | 2 | — |
| 15 | Add FK indexes (9 columns) | — | 2 | — |
| 16 | Add status field indexes (10+ tables) | — | 2 | — |

**Phase 2 total: ~21 hours**

---

## Phase 3: Authorization Alignment (Days 5-7)

| Order | Task | ID | Hours | Depends On |
|-------|------|----|-------|------------|
| 17 | Align 11 API controllers → RbacScope | C-08a | 8 | 7 |
| 18 | Add super-admin prevention to API | — | 1 | — |
| 19 | Add self-demotion prevention to API | — | 1 | — |
| 20 | Fix User override stale rows (clear on reset) | C-16 | 2 | — |
| 21 | Fix Dashboard generic loop → module scoping | — | 2 | — |
| 22 | Fix 9 service layer list() → RbacScope | — | 4 | 7 |
| 23 | Fix ExportController normal user path | — | 1 | 7 |

**Phase 3 total: ~19 hours**

---

## Phase 4: Deployment (Days 7-8)

| Order | Task | ID | Hours | Depends On |
|-------|------|----|-------|------------|
| 24 | Verify cPanel requirements | — | 2 | — |
| 25 | Apply production .env | — | 1 | 1-4 |
| 26 | Execute DEPLOY.md runbook | — | 3 | 25 |
| 27 | Post-deploy verification checklist | — | 2 | 26 |
| 28 | Configure cron jobs | — | 1 | 26 |
| 29 | Set up UptimeRobot | — | 1 | 26 |

**Phase 4 total: ~10 hours**

---

## Phase 5: Polish & Quality (Days 8-12)

| Order | Task | ID | Hours | Depends On |
|-------|------|----|-------|------------|
| 30 | CSV injection prevention | — | 2 | — |
| 31 | Sort field validation (Monitoring) | — | 1 | — |
| 32 | Session security (encrypt, secure cookie) | — | 0.5 | — |
| 33 | CORS config for production | — | 0.5 | — |
| 34 | Post-deploy caching in composer.json | — | 1 | — |
| 35 | Form field labels (8 P1 changes) | — | 4 | — |
| 36 | IA navigation labels (8 quick wins) | — | 2 | — |
| 37 | Extract inline JS to admin.js | — | 1 | — |
| 38 | Add comprehensive export tests | — | 4 | — |
| 39 | Add Pint + coverage threshold to CI | — | 2 | — |
| 40 | Fix N+1 on User show page | — | 1 | — |
| 41 | Fix in-memory pagination (Monitoring) | — | 2 | — |
| 42 | Fix TaskController assignee sync | — | 2 | — |
| 43 | Permission cache invalidation on write | — | 2 | — |
| 44 | Fix AttachmentController file deletion on soft delete | — | 1 | — |
| 45 | Service Provider child-entity delete check | — | 2 | — |
| 46 | Module delete observer cleanup | — | 1 | — |
| 47 | Hardcoded Swagger URL | — | 0.5 | — |
| 48 | Remove dead views + legacy assets | — | 1 | — |
| 49 | Permission key validation | — | 2 | — |
| 50 | Legacy privilege system cleanup | — | 2 | — |

**Phase 5 total: ~34 hours**

---

## Summary Timeline

| Phase | Hours | Duration | Go Live |
|-------|-------|----------|---------|
| 1: Pre-Deploy Blockers | 11.5h | 2 days | ✅ Can deploy after |
| 2: Data Integrity | 21h | 3 days | ✅ Deploy OK in parallel |
| 3: Authorization | 19h | 3 days | ⚠️ Deploy OK (API gap non-blocking) |
| 4: Deployment | 10h | 2 days | ❌ Must wait for Phase 1 |
| 5: Polish | 34h | 5 days | ⚠️ Post-deploy OK |

**Go/No-Go Decision Point:** After Phase 1 complete.
**Full Polish Complete:** ~12 working days from start.
