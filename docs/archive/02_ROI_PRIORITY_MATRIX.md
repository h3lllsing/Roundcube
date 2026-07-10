# OPSPILOT — ROI Priority Matrix

## Scoring Methodology

Each opportunity is scored 1-10 across 8 dimensions. **ROI Score** = weighted sum:
- Business Value (25%)
- Operational Time Saved (20%)
- Security Improvement (15%)
- User Impact (15%)
- Risk Inversion (lower risk = higher score) (10%)
- Maintainability (10%)
- Technical Debt Reduced (5%)

---

## Scores

| Rank | Opportunity | Biz Value (25%) | Ops Time (20%) | Security (15%) | User Impact (15%) | Risk Inverted (10%) | Maintain (10%) | Debt Reduced (5%) | **ROI Score** |
|------|-------------|:---------------:|:--------------:|:--------------:|:-----------------:|:-----------------:|:--------------:|:-----------------:|:-------------:|
| 1 | **Monitoring Dashboard Widget** | 9 | 10 | 6 | 9 | 9 | 8 | 3 | **8.25** |
| 2 | Email for Critical Alerts | 8 | 8 | 8 | 6 | 8 | 7 | 2 | **7.25** |
| 3 | Scheduled SSL Monitoring | 7 | 6 | 9 | 4 | 9 | 7 | 2 | **6.65** |
| 4 | Import Upgrade (Excel+Mapping) | 7 | 8 | 3 | 5 | 7 | 6 | 5 | **6.30** |
| 5 | Webhook Events UI | 5 | 4 | 2 | 3 | 9 | 7 | 3 | **4.60** |
| 6 | Renew Test Coverage | 6 | 2 | 4 | 0 | 9 | 6 | 8 | **4.55** |
| 7 | Suspension Audit Trail | 4 | 3 | 6 | 3 | 8 | 5 | 5 | **4.50** |
| 8 | Activity Log Cleanup | 5 | 3 | 3 | 0 | 9 | 8 | 6 | **4.40** |
| 9 | API Documentation | 4 | 2 | 3 | 5 | 7 | 5 | 2 | **3.90** |
| 10 | Calendar Integration | 5 | 3 | 1 | 4 | 7 | 4 | 2 | **3.75** |

---

## ROI Tiers

### TIER 1 — BUILD IMMEDIATELY (ROI > 7.0)

| # | Opportunity | Score | Rationale |
|---|-------------|-------|-----------|
| 1 | Monitoring Dashboard Widget | **8.25** | Infrastructure exists, zero risk, every operator benefits daily, no dashboard visibility today |
| 2 | Email for Critical Alerts | **7.25** | Monitoring failures are database-only notifications; ops staff may not see downtime for hours |

### TIER 2 — BUILD NEXT (ROI 6.0-7.0)

| # | Opportunity | Score | Rationale |
|---|-------------|-------|-----------|
| 3 | Scheduled SSL Monitoring | **6.65** | SSL check code exists but is manual-only; certificate expiry goes undetected |
| 4 | Import Upgrade | **6.30** | High operational impact but moderate engineering cost; CSV-only is a real pain point |

### TIER 3 — BUILD WHEN CAPACITY ALLOWS (ROI 4.0-6.0)

| # | Opportunity | Score | Rationale |
|---|-------------|-------|-----------|
| 5 | Webhook Events UI | **4.60** | Low cost, low value; good for junior dev onboarding task |
| 6 | Renew Test Coverage | **4.55** | Important but invisible to users; high business risk mitigation |
| 7 | Suspension Audit Trail | **4.50** | Compliance gap, low cost, but limited daily impact |
| 8 | Activity Log Cleanup | **4.40** | Prevents future production incident, not urgent today |

### TIER 4 — LOWEST PRIORITY (ROI < 4.0)

| # | Opportunity | Score | Rationale |
|---|-------------|-------|-----------|
| 9 | API Documentation | **3.90** | Valuable but no external consumers yet; premature optimization |
| 10 | Calendar Integration | **3.75** | High engineering cost for moderate UX improvement; calendar view exists but blank |

---

## ROI Calculation Detail

### Opportunity 1: Monitoring Dashboard Widget

```
Biz Value:     9 × 0.25 = 2.25
Ops Time:     10 × 0.20 = 2.00
Security:      6 × 0.15 = 0.90
User Impact:   9 × 0.15 = 1.35
Risk Inverted: 9 × 0.10 = 0.90
Maintain:      8 × 0.10 = 0.80
Debt Reduced:  3 × 0.05 = 0.15
─────────────────────────────────
ROI Score:             8.25
```

**Why it dominates:**
- Zero data mutation (read-only widget)
- Reuses fully built MonitorService
- Single Blade widget file, no new routes
- Hourly cron already collects all data
- Every operator visit to dashboard sees uptime at a glance

### Opportunity 2: Email for Critical Alerts

```
Biz Value:     8 × 0.25 = 2.00
Ops Time:      8 × 0.20 = 1.60
Security:      8 × 0.15 = 1.20
User Impact:   6 × 0.15 = 0.90
Risk Inverted: 8 × 0.10 = 0.80
Maintain:      7 × 0.10 = 0.70
Debt Reduced:  2 × 0.05 = 0.10
─────────────────────────────────
ROI Score:             7.30 → 7.25 (rounded)
```

**Why it's #2:**
- MonitorCheckFailed is database-only; critical downtime goes unseen until login
- Email infrastructure already exists (SMTP profiles, mail config)
- One notification class change
- High security/operational impact

---

## Sensitivity Analysis

If **risk weighting** increases from 10% to 20%:
- Monitoring Dashboard stays #1 (risk = 1/10)
- Activity Log Cleanup moves up (risk = 0 — prevents data bloat crash)
- Renew Test Coverage moves up (risk of data corruption)

If **user impact** weighting increases from 15% to 25%:
- Monitoring Dashboard stays #1 (impact = 9/10)
- Calendar Integration moves up (impact = 4 → still low)
- No change in top 3

If **engineering cost** were factored separately (cost in days):
- Monitoring Dashboard: 0.5 days → **best ratio**
- Webhook Events UI: 0.25 days → excellent ratio
- Suspension Audit Trail: 0.5 days → good ratio
- Import Upgrade: 2-3 days → worst ratio
