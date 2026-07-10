# BUSINESS RULES DOCUMENTATION REPORT

## Action

Created `BUSINESS_RULES.md` with 15 documented business rules.

## Rules Documented

| # | Rule | Status | Enforced In |
|---|------|--------|-------------|
| BR-01 | Super-Admin Bypasses All Permission Checks | Valid | 40+ code locations |
| BR-02 | Global Records Are Module-Scoped, Not User-Owned | Valid (partial) | API index() |
| BR-03 | Web CRUD Uses Inline Permission Checks | Valid | 10 Web controllers |
| BR-04 | API CRUD Has Mixed Scoping | Known limitation | 9 API controllers |
| BR-05 | Demo Data Must Never Exist in Production | **Fixed pre-release** | DatabaseSeeder guard |
| BR-06 | `--seed` is for Local Development Only | **Fixed pre-release** | Deployment docs |
| BR-07 | Module Slugs Are Immutable Code Constants | Known limitation | 18+ locations |
| BR-08 | `user_id` is Creator Metadata, Not Ownership | Valid (partial) | All store() methods |
| BR-09 | Permissions Are Merged Across Roles (OR Semantics) | Valid | HasModulePermissions trait |
| BR-10 | User-Level Overrides Override Role Permissions | Valid | HasModulePermissions trait |
| BR-11 | A Record Must Always Have a Valid Module ID | Known limitation | 10 Web controllers |
| BR-12 | Controller `moduleSlug()` Must Match DB Slug | Valid | 10 Web controllers |
| BR-13 | API v1.0 Has No External Consumers | Valid | Full audit confirmed |
| BR-14 | Web Controllers Are the Only User Interface | Valid | Full audit confirmed |
| BR-15 | `'super-admin'` Role Slug Is Hardcoded | Known limitation | 40+ locations |

## Classification

| Category | Count | Rules |
|----------|-------|-------|
| **Fixed pre-release** | 2 | BR-05, BR-06 |
| **Valid — documented** | 8 | BR-01, BR-03, BR-09, BR-10, BR-12, BR-13, BR-14, BR-15 |
| **Valid (partial) — documented** | 2 | BR-02, BR-08 |
| **Known limitation — documented** | 3 | BR-04, BR-07, BR-11 |

## Next Review

Business rules should be reviewed when:
- First external API consumer connects (BR-04, BR-13, BR-14)
- Module slug enum is implemented (BR-07, BR-12)
- `created_by` migration is performed (BR-08)
- `firstOrFail` pattern is implemented (BR-11)
- Any new role or permission system is added (BR-01, BR-09, BR-10, BR-15)

## File

`D:\xampp\htdocs\unknow\BUSINESS_RULES.md` — 15 sections, 275 lines.
