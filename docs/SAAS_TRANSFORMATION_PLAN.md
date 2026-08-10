# Enrolliq — SaaS Transformation Plan

> Converting the AL‑Rushd school CRM into **Enrolliq**, a multi‑tenant SaaS platform that any school
> or education organisation can subscribe to. This document is the master plan; see
> `docs/DATABASE_REDESIGN.md` for the full database design.

---

## 1. Vision

One codebase, many schools. Each school (tenant) gets the full product it has today:
CRM (leads → customers → quotations → invoices), dynamic Form Center, admissions,
email marketing, lead integrations (Facebook/TikTok), Website CMS, events, and payments.

On top of that sits a **Platform (Super Admin) layer** owned by us — the SaaS owner — that:

- Markets the product with a professional public landing page (`/platform`).
- Captures **Book a Demo** requests and self‑serve **signups**.
- Manages **schools** (tenants): create with admin email/password, activate/deactivate/suspend, impersonate.
- Manages **subscription plans** (price, interval, trial, features/limits) synced to **Stripe**.
- Handles **monthly billing** via Stripe Checkout subscriptions + webhooks.
- Shows platform KPIs: schools, trials, MRR, demo pipeline, recent activity.

AL‑Rushd is tenant #1 — seeded as an **active** school with a complimentary (internal) subscription.

## 2. Architecture decisions

| Decision | Choice | Why |
|---|---|---|
| Tenant primitive | Existing `organizations` table, extended | CRM/email/integrations/forms are already scoped by `organization_id` with trait, policies and tests. "School" is the user‑facing label for an organization. |
| Tenancy style | Single database, shared schema, `organization_id` column | Simplest to operate, matches existing code. Row‑level scoping via `BelongsToOrganization` trait. |
| Super admin identity | `admins.is_platform_admin` flag (no `organization_id`) | Reuses the `admin` guard/session; platform admins never enter tenant routes, tenant admins never enter `/superadmin` (middleware on both sides). |
| Platform panel | New route file `routes/platform.php`, prefix `/superadmin`, names `platform.*` | Clean separation from `admin.*`; reuses the same Bootstrap admin theme assets. |
| Billing | `stripe/stripe-php` (already installed) — Checkout Sessions in `subscription` mode + webhooks | No Cashier dependency; works with the existing Stripe integration style. Graceful no‑op when keys are absent (plans still manageable locally). |
| Plan naming | `saas_plans` / `saas_subscriptions` | The legacy admissions module already owns the `plans` table name. |
| Marketing site | `/platform` (landing), `/platform/book-demo`, `/platform/signup` | The school site stays at `/` (AL‑Rushd production is live there). In production, point the SaaS domain at the app and set `SAAS_DOMAIN` to serve the landing at that domain's root. |
| Existing `schools` table | Unchanged — it is a lookup ("which campus") inside a tenant, **not** the tenant | Avoids a breaking rename; documented in DB redesign. |

## 3. Roles model

```
Platform (us / SaaS owner)
└── Platform Admin  (admins.is_platform_admin = true, organization_id = NULL)
    → /superadmin/*  (platform.* routes)

Tenant (a school)
└── School Admin / staff  (admins.organization_id = X, Spatie roles)
    → /admin/*  (admin.* routes, org‑scoped)
```

- Platform admins are hidden from tenant admin lists and blocked from `/admin/*` (they carry no tenant roles).
- Tenant admins get a 403 on `/superadmin/*`.
- **Impersonation**: a platform admin can "Login as" any school admin; the original id is kept in
  session (`platform_impersonator_id`) and a banner in the tenant panel offers "Return to Super Admin".

## 4. Subscription lifecycle

```
Book a demo ──► demo_requests (new → contacted → demo_scheduled → converted/closed)
                                        │  "Convert to school"
Self signup ──► organization (status: trial, trial_ends_at) + school admin account
                                        │
                        Stripe Checkout (subscription mode)
                                        │  webhook: checkout.session.completed
                organization.status = active + saas_subscriptions row (stripe ids, period)
                                        │  webhooks: invoice.payment_succeeded /
                                        │  customer.subscription.updated / .deleted
                past_due ──► suspended ──► cancelled  (org access blocked, data retained)
```

Enforcement: `EnsureOrganizationIsActive` middleware wraps the tenant admin panel.
Suspended/inactive schools see a lock screen (billing route stays reachable). The public
tenant site keeps working during `trial` and `active` only.

Organization `status` values: `trial`, `active`, `past_due`, `suspended`, `inactive`, `cancelled`.
`is_active` is kept in sync (`active`/`trial` ⇒ true) for backwards compatibility.

## 5. Super Admin panel — pages

| Page | Route | Contents / actions |
|---|---|---|
| Dashboard | `/superadmin/dashboard` | KPI cards (total/active/trial schools, MRR, open demo requests), latest signups, latest demo requests, activity feed |
| Schools | `/superadmin/schools` | Search + filter by status/plan; per row: status badge, plan, admins count, created; actions: view, activate/deactivate/suspend, impersonate |
| School create | `/superadmin/schools/create` | School name/slug/email/phone/timezone + first admin (name, email, password) + plan & status |
| School detail | `/superadmin/schools/{id}` | Profile, subscription card, admins list, usage stats (leads, forms, entries), activity log, quick actions |
| Plans | `/superadmin/plans` | CRUD: name, price, currency, interval, trial days, feature list, limits, featured flag; "Sync to Stripe" (product + price) |
| Subscriptions | `/superadmin/subscriptions` | All subscriptions with org, plan, Stripe status, period end; cancel action |
| Demo requests | `/superadmin/demo-requests` | Pipeline list, status updates, internal notes, convert‑to‑school |
| Settings | `/superadmin/settings` | Platform name/logo/support email, Stripe publishable/secret/webhook keys (DB overrides env) |

Deliberately **not** in the platform panel: Facebook/TikTok integrations, CRM, email marketing —
those are tenant features.

## 6. Public marketing site

- `/platform` — professional landing page: sticky navbar, hero with product mockup imagery,
  logo strip, feature grid (CRM, Form Builder, Email Marketing, Lead Ads integrations, Website CMS,
  Payments), product tour, pricing pulled live from `saas_plans`, testimonials, FAQ, demo CTA, footer.
- `/platform/book-demo` — demo request form → `demo_requests` + platform notification.
- `/platform/signup?plan=slug` — creates school + admin, starts trial, then redirects to Stripe
  Checkout when the plan is Stripe‑synced (otherwise straight into the trial).
- Self‑contained styling (no dependency on tenant themes); OG/meta tags for sharing.
- Set `SAAS_DOMAIN=yourdomain.com` in `.env` to also serve the landing at that domain's root `/`.

## 7. Implementation phases

### Phase 1 — this iteration (additive, non‑breaking)
1. Plan docs + Cursor rule (this file, DB redesign, `.cursor/rules/saas-conventions.mdc`).
2. Migrations: extend `organizations` + `admins`, add `saas_plans`, `saas_subscriptions`,
   `demo_requests`, `platform_settings`, `platform_activity_logs`.
3. Models, `PlatformSettings` helper, `StripeBillingService`, activity logger.
4. Middleware (`platform.admin`, `org.active`), login redirect for platform admins, impersonation.
5. Platform panel (layout + all pages above).
6. Marketing landing + book‑demo + signup + Stripe webhook (`/webhooks/stripe/platform`).
7. Tenant **Billing** page in `/admin` (current plan, status, checkout/renew).
8. Seeder: AL‑Rushd org (active, complimentary sub), 3 plans, platform owner account
   (`owner@enrolliq.com` / `password` — change in production).

### Phase 2 — hardening (next)
- Org‑scope the remaining legacy tables (admissions catalog, students, orders, settings,
  website_cms, events…) per `docs/DATABASE_REDESIGN.md` §4.
- Per‑tenant subdomains/custom domains for public school sites.
- Spatie teams mode (`team_id = organization_id`) → per‑tenant roles; per‑org unique admin emails.
- Plan limit enforcement (max admins/leads/emails) + usage metering.
- Stripe Customer Portal, dunning emails, invoices list for tenants.

### Phase 3 — growth
- Onboarding wizard for new schools (branding, forms, first import).
- Platform announcements/changelog pushed to tenant dashboards.
- White‑label options, API keys per tenant, audit log export, backups per tenant.

## 8. Non‑goals (explicit)
- No rewrite of the legacy AL‑Rushd admissions flow in this phase — it keeps working unchanged.
- No physical DB-per-tenant split.
- No removal of existing tenant features (Facebook/TikTok stay in the tenant panel).

## 9. Key file map (new code)

```
app/Models/{SaasPlan,SaasSubscription,DemoRequest,PlatformSetting,PlatformActivityLog}.php
app/Http/Middleware/{EnsurePlatformAdmin,EnsureOrganizationIsActive}.php
app/Http/Controllers/Platform/*   (Dashboard, Schools, Plans, Subscriptions, DemoRequests, Settings, Auth/Impersonation)
app/Http/Controllers/Saas/*       (Landing, DemoRequest, Signup, StripeWebhook)
app/Http/Controllers/Admin/BillingController.php
app/Services/Platform/{StripeBillingService,PlatformActivityLogger}.php
routes/platform.php               (superadmin panel)
routes/saas.php                   (public marketing + signup + webhook)
resources/views/platform/**       (panel views)
resources/views/saas/**           (landing, demo, signup)
database/migrations/2026_08_10_*  (see DB redesign)
database/seeders/SaasPlatformSeeder.php
config/saas.php
```
