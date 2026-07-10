# OPSPILOT — What NOT to Build Next

---

## 1. 🚫 Multi-tenant / Organization Separation

**Why not:** The codebase has no tenant infrastructure (no `organization_id`, no team isolation, no row-level tenant scoping). Adding multi-tenancy requires a ground-up architectural change affecting every model, query, route, and middleware. This is a 3-6 month project, not a sprint. **Until the business has paying customers demanding it, this is premature.**

**Evidence:** Zero references to organization, team, workspace, or tenant in any model, migration, or middleware.

---

## 2. 🚫 Client/End-User Portal

**Why not:** This is an internal operations tool (OpsPilot), not a customer-facing platform. Adding a client portal means building authentication gate, permission model, limited API surfaces, and separate UI. This fundamentally changes the product scope. **Do not build until there's a validated business case with revenue attached.**

**Evidence:** All UX assumes internal staff users. Routes are admin-prefixed. Nav is operations-focused.

---

## 3. 🚫 Billing / Invoice System

**Why not:** No existing pricing data, no payment gateway integration, no invoice schema. Building billing from scratch is a 1-3 month project. If billing integration is needed, use an external tool (Stripe, Chargebee) rather than building in-house. **This is a product pivot, not a sprint.**

**Evidence:** Zero billing-related tables, no payment processing code, no pricing model in config.

---

## 4. 🚫 Mobile App / PWA

**Why not:** The web UI is responsive and works on mobile browsers. A native app or PWA adds significant maintenance cost with marginal benefit for internal ops staff who work from desktops/laptops. **Build when 30%+ of traffic comes from mobile devices.**

**Evidence:** No mobile-specific routes, no service worker, no manifest.json. Desktop-first layout.

---

## 5. 🚫 AI/ML Features (Predictive analytics, auto-categorization)

**Why not:** No training data, no ML infrastructure, no customer demand signal. AI features are expensive to build, maintain, and validate. **Do not build until a specific, high-value problem is identified that cannot be solved with deterministic logic.**

**Evidence:** Zero AI/ML dependencies in composer.json or package.json.

---

## 6. 🚫 Rebuild UI in React/Vue/Svelte

**Why not:** The current stack (Tailwind + Blade + Alpine.js + Vite) works well, loads fast (264KB JS gzipped to 93KB), and follows Laravel conventions. A frontend rewrite introduces months of work with zero new user value. **Do not rebuild unless the current UI demonstrably fails at a specific task.**

**Evidence:** 62 Vite modules, 2.86s build time. Monolith architecture is appropriate for internal tooling.

---

## 7. 🚫 Custom Docker/Deployment Infrastructure

**Why not:** Laravel Forge, Docker, or deployment configuration is infrastructure, not product. Unless there is an active deployment blocker, custom deployment tooling is a distraction. **Use proven tools (Forge, Envoyer, Deployer) rather than building custom.**

**Evidence:** No Dockerfile or docker-compose.yml in project root. Deployment is external to codebase.

---

## 8. 🚫 Enterprise SSO/SAML/OAuth (unless demanded)

**Why not:** Works with standard email/password + Sanctum tokens. Adding SSO requires SAML/OAuth library integration, separate auth flow, and maintenance burden. **Build only when a specific customer requires it for procurement.**

**Evidence:** Standard Laravel auth. No SSO packages in composer.json.

---

## 9. 🚫 Chat/Real-time Collaboration

**Why not:** Internal ops tools don't need real-time chat. Notifications cover the communication need. WebSockets, broadcasting, and presence channels add significant complexity. **Do not build unless users explicitly request it and cannot use existing chat tools (Slack, Teams).**

**Evidence:** No broadcasting config, no WebSocket server, no presence channels.

---

## Core Principle

**Build what reduces operational risk or saves time.**
**Do NOT build what adds surface area without eliminating existing pain.**

Every item on this list expands the product scope without addressing a current operational bottleneck. The monitoring widget (ROI: 8.25) directly addresses a real, daily pain point. These items address hypothetical future needs.
