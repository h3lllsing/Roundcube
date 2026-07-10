# BUSINESS VALUE HEATMAP

> Which workflows generate business value vs which workflows waste time.
> Based on persona frequency × business impact × risk × friction.

---

## Methodology

Each workflow scored on 5 dimensions (1-10):

| Dimension | Weight | Definition |
|-----------|--------|------------|
| **Business Value** | 2.0 | Direct contribution to organizational goals (cost savings, productivity, security) |
| **Frequency** | 1.5 | How often the workflow occurs (across all users) |
| **Risk Impact** | 2.0 | Cost of making a mistake in this workflow |
| **Current Friction** | 1.0 | Time lost per execution due to poor UX |
| **User Count** | 1.0 | How many users are affected |

**Heat Index = (Value × 2) + (Freq × 1.5) + (Risk × 2) + (Friction × 1) + (Users × 1)**

---

## Heatmap

| Rank | Workflow | Value | Freq | Risk | Friction | Users | **Heat** | Priority |
|------|----------|-------|------|------|----------|-------|----------|----------|
| **1** | Password Self-Service (End User) | 8 | 10 | 6 | 2 | 480 | **56** | **CRITICAL** |
| **2** | Credential Retrieval (Service Desk) | 9 | 10 | 8 | 5 | 20 | **54** | **CRITICAL** |
| **3** | Daily Health Check | 6 | 10 | 4 | 2 | 50 | **44** | HIGH |
| **4** | Task Processing (Service Desk) | 6 | 10 | 3 | 4 | 20 | **42** | HIGH |
| **5** | Incident Response | 9 | 5 | 9 | 7 | 25 | **58** | **CRITICAL** |
| **6** | Access Revocation (Offboarding) | 10 | 2 | 10 | 10 | 5 | **55** | **CRITICAL** |
| **7** | Security Audit Review | 10 | 6 | 9 | 8 | 3 | **53** | **CRITICAL** |
| **8** | Process Renewals | 8 | 6 | 6 | 7 | 8 | **50** | HIGH |
| **9** | Provision New Service | 8 | 4 | 5 | 8 | 10 | **48** | HIGH |
| **10** | User Onboarding | 9 | 3 | 8 | 8 | 5 | **50** | HIGH |
| **11** | Team Resource Planning | 7 | 4 | 5 | 6 | 5 | **42** | MEDIUM |
| **12** | Employee Offboarding (full) | 10 | 2 | 10 | 10 | 5 | **55** | **CRITICAL** |
| **13** | Monthly Cost Review | 7 | 2 | 5 | 6 | 4 | **37** | MEDIUM |
| **14** | Vendor Onboarding | 5 | 2 | 4 | 4 | 6 | **28** | LOW |
| **15** | Module Configuration | 3 | 1 | 4 | 2 | 3 | **18** | LOW |
| **16** | Bulk Data Import | 5 | 1 | 7 | 4 | 3 | **27** | LOW |
| **17** | Integration Setup | 3 | 1 | 5 | 2 | 3 | **19** | LOW |
| **18** | Permission Change Approval | 7 | 3 | 8 | 4 | 5 | **42** | HIGH |
| **19** | Asset Lifecycle Tracking | 5 | 4 | 3 | 4 | 10 | **32** | MEDIUM |
| **20** | Team Performance Review | 7 | 2 | 3 | 3 | 3 | **30** | MEDIUM |

---

## Quadrant Analysis

### QUADRANT 1: HIGH VALUE + HIGH FRICTION (Fix Now)

These workflows generate the most value AND have the worst UX. **Highest ROI for improvement.**

| Workflow | Heat | Why |
|----------|------|-----|
| Access Revocation (Offboarding) | 55 | Security risk. Manual. High frequency for HR events. |
| Provision New Service | 48 | Core IT function. 6 disconnected forms. Data re-entry. |
| Security Audit Review | 53 | Daily. High risk. No correlation tools. |
| Incident Response | 58 | Uptime-critical. Fragmented. |
| User Onboarding | 50 | Productivity gap. New hires can't work day 1. |

### QUADRANT 2: HIGH VALUE + LOW FRICTION (Protect)

These workflows work well. Don't break them.

| Workflow | Heat | Why |
|----------|------|-----|
| Password Self-Service | 56 | 450 users. Simple. Don't over-engineer. |
| Credential Retrieval | 54 | Daily. Direct credential-to-service link is the only improvement. |
| Daily Health Check | 44 | Works. Dashboard exists. Keep it working. |

### QUADRANT 3: LOW VALUE + HIGH FRICTION (Simplify or Remove)

These workflows consume disproportionate time relative to their value.

| Workflow | Heat | Why |
|----------|------|-----|
| Bulk Data Import | 27 | Rare. Complex. Accept the friction or simplify the UI. |
| Vendor Onboarding | 28 | Infrequent. Friction is acceptable. Don't over-invest. |
| Module Configuration | 18 | Rare. Configuration complexity is inherent. |

### QUADRANT 4: LOW VALUE + LOW FRICTION (Monitor)

These workflows are fine. Check back in 6 months.

| Workflow | Heat | Why |
|----------|------|-----|
| Integration Setup | 19 | Rare. Infrequent config. Acceptable. |
| Asset Lifecycle Tracking | 32 | Medium value. Low friction. Improve if time permits. |
| Team Performance Review | 30 | Monthly. Director-only. Basic reporting exists. |

---

## Value by Persona

| Persona | Primary Value Workflows | Total Persona Heat | % of Total Org Value |
|---------|------------------------|--------------------|----------------------|
| End User | Password Self-Service (56) | 56 | 22% |
| Service Desk | Credential Retrieval (54), Task Processing (42) | 96 | 37% |
| IT Operator | Incident Response (58), Provisioning (48), Daily Health (44), Renewals (50) | 200 | 77% |
| IT Manager | Resource Planning (42), Team Review (30), Cost Review (37) | 109 | 42% |
| Security Officer | Security Audit (53), Offboarding (55) | 108 | 42% |
| Procurement | Renewals (50), Cost Review (37), Vendor Onboarding (28) | 115 | 44% |
| IT Director | Team Review (30), Cost Review (37), Daily Health (44) | 111 | 43% |
| Super Admin | User Onboarding (50), Offboarding (55), Module Config (18) | 123 | 47% |

**Key insight:** IT Operator workflows account for 77% of total business value. Improving IT Operator workflows has the highest organizational ROI.

---

## What the Heatmap Says

### Top 5 Investments by ROI

| Investment | Workflows Affected | Effort | Value Impact |
|-----------|-------------------|--------|-------------|
| **1. Employee Lifecycle Wizard** | Onboarding + Offboarding | 2 weeks | Eliminates security gaps. Saves 2-3 hrs/week per admin. |
| **2. Provisioning Wizard** | New Service, Vendor Onboarding | 2 weeks | Eliminates data re-entry. Saves 3-5 hrs/week per IT Ops. |
| **3. Unified Security Timeline** | Security Audit, Incident Response | 1 week | Correlation saves investigation time. Catches breaches faster. |
| **4. Cost Aggregation Dashboard** | Renewals, Cost Review, Resource Planning | 3 days | Eliminates manual spreadsheet compilation. Saves 2-4 hrs/month. |
| **5. Service-Credential Linking** | Password Reset, Credential Retrieval | 2 days | Eliminates credential hunting. Saves 5-15 hrs/week for Service Desk. |

**Total effort: ~6-7 weeks. Total value: 50+ hours/week saved organization-wide + risk reduction of missed offboarding/breaches.**
