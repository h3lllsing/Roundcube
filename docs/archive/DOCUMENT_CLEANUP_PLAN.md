# Document Cleanup Plan

**Goal:** Reduce root `.md` files from 218 to ~20 and establish a permanent standard.

---

## Current State

| Category | Count | Action |
|----------|-------|--------|
| ACTIVE (stay in root) | 20 | Keep |
| REFERENCE (→ /docs/reference/) | 77 | Move |
| ARCHIVE (→ /docs/archive/) | 107 | Move |
| OBSOLETE (→ /docs/archive/obsolete/) | 14 | Archive |
| **Total** | **218** | |

---

## Rule: Root `.md` Files (Stay in Root)

Only files that describe the **current live state** of the project belong in root:

- `README.md`
- `CHANGELOG.md`
- `CONTRIBUTING.md`
- `PROJECT_ARCHITECTURE_LOCK.md`
- `FINAL_RELEASE_AUDIT.md` — trimmed to current v1.1 scope
- `BUSINESS_RULES.md`
- `CURRENT_EXECUTION_STATUS.md`
- `DEPLOY.md`
- `ARCHITECTURAL_ASSUMPTIONS.md`

Everything else moves to a subdirectory.

---

## New Directory Structure

```
/ (root)
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── PROJECT_ARCHITECTURE_LOCK.md
├── FINAL_RELEASE_AUDIT.md
├── BUSINESS_RULES.md
├── CURRENT_EXECUTION_STATUS.md
├── DEPLOY.md
├── ARCHITECTURAL_ASSUMPTIONS.md
│
├── docs/
│   ├── operations/        (already organized — 14 files)
│   ├── reference/         (guides, specs, architecture decisions)
│   └── archive/           (completed audits, reports, patch notes)
│       └── obsolete/      (superseded/duplicate files)
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vendor/
```

---

## ACTIVE Files (Keep in Root — 20)

| File | Reason |
|------|--------|
| `README.md` | Standard project entry point |
| `CHANGELOG.md` | Active changelog |
| `CONTRIBUTING.md` | Contribution guide |
| `PROJECT_ARCHITECTURE_LOCK.md` | Current architecture philosophy |
| `FINAL_RELEASE_AUDIT.md` | Active v1.1 release audit |
| `BUSINESS_RULES.md` | Active business rules |
| `CURRENT_EXECUTION_STATUS.md` | Living execution status |
| `DEPLOY.md` | Active deployment instructions |
| `ARCHITECTURAL_ASSUMPTIONS.md` | Current architecture assumptions |
| `PROJECT_STATISTICS.md` | Active project stats |
| `FINAL_V1_1_PRIORITY_ORDER.md` | Active v1.1 priorities |
| `POST_V1_ROADMAP.md` | Active roadmap |
| `V1_1_ROADMAP.md` | Active v1.1 roadmap |
| `V1_1_ROI_VALIDATION.md` | Active v1.1 ROI |
| `Final_task_status.md` | Active task tracking |
| `OPS_PILOT_V1_RELEASE_CANDIDATE_SIGNOFF.md` | Active release signoff |
| `FINAL_PROJECT_STRUCTURE.md` | Current project structure |
| `SYSTEM_ASSUMPTIONS.md` | Current system assumptions |
| `BUSINESS_RULE_ASSUMPTIONS.md` | Current BR assumptions |
| `COPILOT_PROJECT_CONSTITUTION.md` | Project constitution |

---

## REFERENCE Files (→ /docs/reference/ — 77)

### User & Operations Guides (→ /docs/reference/guides/)

- `USER_GUIDE.md`
- `01_QUICK_START_GUIDE.md`
- `02_SUPER_ADMIN_GUIDE.md`
- `03_ADMIN_GUIDE.md`
- `04_IT_STAFF_GUIDE.md`
- `05_READ_ONLY_GUIDE.md`
- `06_DAILY_OPERATIONS_GUIDE.md`
- `10_WORKFLOW_GUIDE.md`
- `12_TROUBLESHOOTING_GUIDE.md`
- `13_BACKUP_AND_RESTORE.md`
- `16_DISASTER_RECOVERY_GUIDE.md`
- `19_MONITORING_GUIDE.md`
- `INSTALLATION.md`
- `DEPLOYMENT_GUIDE.md`
- `CPANEL_DEPLOYMENT_GUIDE.md`
- `PRODUCTION_CONFIGURATION_GUIDE.md`
- `PRODUCTION_CHECKLIST.md`
- `PRODUCTION_EDGE_CASES.md`
- `POST_DEPLOYMENT_SMOKE_TEST.md`
- `PRE_DEPLOYMENT_BLOCKER_VALIDATION.md`
- `PRE_DEPLOYMENT_SANITY_CHECK.md`
- `PAGE_LOAD_VERIFICATION.md`
- `ROLLBACK_PLAN.md`
- `DEPLOY_EXCLUDE_LIST.md`
- `POST_RELEASE_FEEDBACK_PLAN.md`

### Architecture & Specs (→ /docs/reference/architecture/)

- `01_SYSTEM_OVERVIEW.md`
- `02_ARCHITECTURE_LAYERING.md`
- `03_COMPONENT_LIBRARY_SPEC.md`
- `03_DATABASE_RELATIONSHIPS.md`
- `04_MODULE_BUSINESS_LOGIC.md`
- `04_PRODUCT_PATTERNS_SPEC.md`
- `05_PERMISSION_SYSTEM.md`
- `06_DATABASE_AND_DATA_INTEGRITY_AUDIT.md`
- `08_PERMISSION_REFERENCE.md`
- `08_ROUTES_API.md`
- `09_VALIDATION_RULES.md`
- `09_ROLE_MATRIX.md`
- `10_NOTIFICATIONS_LOGGING.md`
- `11_GLOSSARY.md`
- `15_VERSION_HISTORY.md`
- `17_ARCHITECTURE_OVERVIEW.md`
- `18_DEVELOPER_RBAC_REFERENCE.md`
- `API_REFERENCE.md`
- `ARCHITECTURE_DEBT_PLAN.md`
- `PRODUCTION_ARCHITECTURE_SIGNOFF.md`
- `KNOWN_LIMITATIONS.md`
- `CROSS_MODULE_CONSISTENCY_RULES.md`
- `SOURCE_OF_TRUTH_MATRIX.md`
- `DOMAIN_OWNERSHIP_MATRIX.md`
- `DATA_AUTHORITY_REPORT.md`
- `DATA_GOVERNANCE_REPORT.md`
- `PROJECT_ARCHITECTURE_FREEZE_v1.0.md`
- `ROLE_BASED_NAVIGATION_STRATEGY.md`
- `NAVIGATION_ARCHITECTURE_REVIEW.md`
- `NAVIGATION_IMPROVEMENT_PLAN.md`

### UI/UX & Design (→ /docs/reference/design/)

- `MENU_SEMANTIC_ANALYSIS.md`
- `DASHBOARD_ENTRY_POINTS.md`
- `LOGIN_ARTWORK_SPEC.md`
- `LOGIN_EXPERIENCE_RECOMMENDATIONS.md`
- `PERSONA_MODEL.md`
- `USER_JOURNEY_MAP.md`
- `BUSINESS_VALUE_HEATMAP.md`
- `BUSINESS_WORKFLOW_MAPPING.md`
- `TOP_20_OPERATIONAL_WORKFLOWS.md`
- `WORKFLOW_MVP_SCOPE.md`
- `WORKFLOW_VALUE_ANALYSIS.md`
- `HIGH_FRICTION_WORKFLOWS.md`
- `FORM_FIELD_BUSINESS_JUSTIFICATION_AUDIT.md`
- `PROJECT_STRUCTURE_CLEANUP.md`

### Monitoring (→ /docs/reference/monitoring/)

- `MONITORING_DASHBOARD_BOUNDARY.md`
- `MONITORING_GROWTH_PLAN.md`
- `MONITORING_PRODUCT_ARCHITECTURE.md`
- `MONITORING_WIDGET_SCOPE.md`

### Security & Technical (→ /docs/reference/security/)

- `SECURITY_BASELINE.md`
- `AUTOMATION_OPPORTUNITIES.md`
- `V1_TECHNICAL_DEBT.md`
- `11_FRONTEND_CONTRACT.md`
- `12_DO_NOT_BREAK_LIST.md`
- `LOGIN_BACKGROUND_IMPLEMENTATION_REPORT.md`

### UI/UX Audits (→ /docs/reference/audits/)

- `PORTAL_DESIGN_SYSTEM_AUDIT.md`
- `INFORMATION_ARCHITECTURE_AUDIT.md`
- `UI_UX_AUDIT.md`

---

## ARCHIVE Files (→ /docs/archive/ — 107)

All files below are **completed historical records**. Move as-is into `/docs/archive/`.

### Numbered Audit Series (00–09, ~58 files)
All files matching `[0-9][0-9]_*` not already classified as REFERENCE above.

### Final Release v1.0 Audits (~37 files)
All files matching `FINAL_RELEASE_*`.

### Sprint Reports (~20 files)
All files matching `SPRINT_*`.

### Phase Reports (5 files)
All files matching `PHASE_*`.

### Patch Notes (~12 files)
All files matching `PATCH_*_*_*`.

### CTO Final Release (~4 files)
All files matching `CTO_FINAL_RELEASE_*`.

### Sign-off / Gate Docs (~9 files)
- `FINAL_CODE_QUALITY_AUDIT.md`
- `FINAL_PERMISSION_SIGNOFF.md`
- `FINAL_API_WEB_PARITY_SIGNOFF.md`
- `FINAL_GO_NO_GO_RECOMMENDATION.md`
- `FINAL_DEPLOYMENT_GATE_REPORT.md`
- `FINAL_PRODUCTION_VERIFICATION.md`
- `FINAL_RUNTIME_SIGNOFF.md`
- `GO_NO_GO_FINAL.md`
- `RC1_FINAL_PRODUCTION_CERTIFICATION.md`

### Other Completed Reports (~30 files)
All remaining audit reports, bug reports, evidence docs, security reviews, and completion checklists.

---

## OBSOLETE Files (→ /docs/archive/obsolete/ — 14)

Files superseded by newer versions. Move to `/docs/archive/obsolete/`.

| Obsolete File | Superseded By | Reason |
|---------------|--------------|--------|
| `BACKUP_AND_RESTORE.md` | `13_BACKUP_AND_RESTORE.md` | Older "Tyro" version |
| `ADMIN_GUIDE.md` | `03_ADMIN_GUIDE.md` | Older "Tyro" version (213 vs 785 lines) |
| `RELEASE_NOTES_v1.0.md` | `VERSION_1_0_RELEASE_NOTES.md` | Older "Tyro" version |
| `14_RELEASE_NOTES_v1.0.md` | `VERSION_1_0_RELEASE_NOTES.md` | Shorter OpsPilot version |
| `100_DUPLICATE_PAGE_POLICY_PERMISSION_AUDIT.md` | `14_DUPLICATE_PAGE_POLICY_PERMISSION_AUDIT.md` | Index-only stub |
| `101_SAFE_REMOVAL_PLAN.md` | `15_SAFE_REMOVAL_PLAN.md` | Security-critical subset |
| `102_USER_LEVEL_PERMISSION_SECURITY_AUDIT.md` | (archive) | Full audit, keep |
| `106_USER_LEVEL_PERMISSION_SECURITY_AUDIT.md` | `102_` | Addendum, obsolete |
| `103_PERMISSION_EVALUATOR_CONSISTENCY_AUDIT.md` | (archive) | Keep in archive |
| `107_PERMISSION_EVALUATOR_CONSISTENCY_AUDIT.md` | `103_` | Duplicate |
| `104_PERMISSION_ATTACK_SCENARIO_REPORT.md` | (archive) | Keep in archive |
| `108_PERMISSION_ATTACK_SCENARIO_REPORT.md` | `104_` | Duplicate |
| `105_PERMISSION_SAFE_FIX_PLAN.md` | (archive) | Keep in archive |
| `109_PERMISSION_SAFE_FIX_PLAN.md` | `105_` | Duplicate |

---

## Execution

```bash
# Create directories
mkdir -p docs/reference/{guides,architecture,design,monitoring,security,audits}
mkdir -p docs/archive/obsolete

# REFERENCE: Move guides
... (move each file per plan above)

# ARCHIVE: Move all completed reports
... (move all 107 files to docs/archive/)

# OBSOLETE: Move to archive/obsolete/
... (move 14 files to docs/archive/obsolete/)
```

**After cleanup:** Root will contain ~20 active files. All historical records remain accessible in `/docs/archive/`.

---

## Future Standard

1. All new analysis → `/docs/analysis/YYYY-MM-DD-topic.md`
2. All new proposals → `/docs/proposals/YYYY-MM-DD-topic.md`
3. Root `.md` files require explicit approval
4. Any file not read for 90 days → candidate for archive
