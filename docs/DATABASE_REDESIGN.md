# Database Redesign — Multi‑Tenant SaaS

Senior‑level review of the current schema (`digital_forms`, MySQL) and the target design.
Phase 1 changes are **additive only** — nothing existing breaks. Phase 2 is the corrective
migration plan for the legacy half of the schema.

---

## 1. Current state (audit summary)

**Good, keep:**
- `organizations` + `organization_id` scoping (with `BelongsToOrganization` trait, policies,
  route bindings, isolation tests) already covers: `crm_*`, `em_*`, `integration_*`,
  `meta_lead_submissions`, `forms`, `form_entries`, `admins`.
- Dynamic Form Center (`forms`, `form_steps`, `form_fields`, `form_entries`) is the modern
  replacement for a dozen legacy form tables.

**Problems found (to fix in Phase 2):**

| # | Problem | Examples |
|---|---|---|
| 1 | Partial tenancy — legacy tables are global | `settings`, `website_cms`, `students`, `orders`, catalog tables, `open_events`, `enquires`, `referrals`, `api_*` |
| 2 | Missing foreign keys / IDs stored as strings | `packages.group_id` (no FK), `users.year_group_id` (string), `debit_students.debit_id` (string!), `open_event_items.open_events_id` |
| 3 | Duplicate schemas | `users` ≈ `guardiants`; `groups/packages/plans` vs `student_groups/student_packages/student_courses`; JSON subjects on `students` **and** pivot tables |
| 4 | Naming defects | `guardiants`, `mettings`, `enquires`, `relation_ships`, `lession`, `admission_studetns` (permission) |
| 5 | Money as float | `course_fees`, `orders` (CRM tables correctly use decimal) |
| 6 | Global unique constraints that block multi‑tenant | `admins.email`, `forms.slug`, Spatie role names (teams disabled) |
| 7 | Singleton settings rows | `settings` (holds Stripe keys!), `website_cms` — one row for the whole app |
| 8 | `schools` table is a per‑tenant lookup (campus list) but has no `organization_id` | leads store `selected_school` as free text |

## 2. Terminology

- **Organization** = the tenant = a customer school on the SaaS. UI label: "School".
- **`schools` table** = campus/branch lookup *inside* a tenant (unchanged; gets `organization_id` in Phase 2).
- **Platform** = SaaS‑owner layer; its tables are prefixed `saas_` / `platform_`.

## 3. Phase 1 schema (this iteration, additive)

### 3.1 `organizations` — extended (tenant master record)

```
organizations
  id                    PK
  name, slug            (slug unique)
  is_active             bool         -- kept for back-compat, derived from status
+ status                enum-string: trial|active|past_due|suspended|inactive|cancelled  (indexed)
+ email                 nullable     -- primary contact
+ phone                 nullable
+ website               nullable
+ logo_path             nullable
+ address, city, country nullable
+ timezone              default 'UTC'
+ contact_name          nullable
+ notes                 text nullable     -- internal platform notes
+ trial_ends_at         timestamp nullable
+ suspended_at          timestamp nullable
+ stripe_customer_id    nullable, indexed
+ onboarded_by          FK admins nullable (nullOnDelete)
+ metadata              json nullable
```

### 3.2 `admins` — extended

```
+ is_platform_admin   bool default false, indexed
+ last_login_at       timestamp nullable
  organization_id     stays nullable (NULL ⇒ platform admin)
```

### 3.3 `saas_plans`

```
saas_plans
  id PK
  name, slug unique
  tagline nullable, description text nullable
  price decimal(10,2), currency char(3) default 'USD'
  billing_interval enum-string: month|year
  trial_days smallint default 14
  features json          -- ["Unlimited leads", ...] shown on pricing
  limits json nullable   -- {"max_admins": 5, "max_leads": null, ...} (enforced Phase 2)
  stripe_product_id nullable
  stripe_price_id nullable, indexed
  is_active bool default true
  is_featured bool default false
  sort_order smallint default 0
  timestamps
```

### 3.4 `saas_subscriptions`

One row per subscription episode; the latest non‑cancelled row is the current one.

```
saas_subscriptions
  id PK
  organization_id  FK organizations cascadeOnDelete, indexed
  saas_plan_id     FK saas_plans nullOnDelete
  status           enum-string: trialing|active|past_due|canceled|incomplete|complimentary (indexed)
  stripe_subscription_id nullable unique
  stripe_customer_id     nullable
  current_period_start / current_period_end  timestamps nullable
  trial_ends_at, canceled_at, ends_at        timestamps nullable
  metadata json nullable
  timestamps
  index (organization_id, status)
```

`complimentary` = internal/free (AL‑Rushd, partners) — never touched by Stripe webhooks.

### 3.5 `demo_requests`

```
demo_requests
  id PK
  name, email, phone nullable
  organization_name, organization_type nullable, country nullable
  students_count nullable string      -- size bracket
  message text nullable
  status enum-string: new|contacted|demo_scheduled|converted|closed  (indexed)
  internal_notes text nullable
  handled_by  FK admins nullOnDelete
  converted_organization_id FK organizations nullOnDelete
  source string default 'landing'
  timestamps, index (status, created_at)
```

### 3.6 `platform_settings` — key/value

```
platform_settings:  id, key unique, value text nullable, timestamps
keys: platform_name, support_email, logo_path,
      stripe_key, stripe_secret, stripe_webhook_secret   (DB overrides env)
```

### 3.7 `platform_activity_logs` — audit trail

```
platform_activity_logs
  id PK
  admin_id        FK admins nullOnDelete       -- who (platform admin or system)
  organization_id FK organizations cascadeOnDelete nullable  -- about which tenant
  action          string  (school.created, school.suspended, plan.updated, subscription.renewed, …)
  description     string
  metadata        json nullable
  created_at      (no updated_at)
  index (organization_id, created_at), index (action)
```

## 4. Phase 2 — corrective migration plan (legacy half)

Ordered so each step is independently shippable:

1. **Tenant column rollout** — add `organization_id` (FK, indexed, then NOT NULL after backfill to
   AL‑Rushd's id) to: `settings`, `website_cms`, `schools`, `students`, `guardiants`, `users`,
   `orders`, `groups`, `group_years`, `packages`, `plans`, `qualifications`, `subjects`,
   `languages`, `course_fees`, `coupons`, `time_tables`, `student_*`, `open_event*`,
   `meet_speakers`, `enquires`, `referrals`, `debits`, `debit_students`, `staff_admission_forms`,
   `mettings`, `job_applications`, `form_submissions`, `form_students`, `api_*`,
   `nationalities`, `genders`, `admission_dates`, `relation_ships`, `payment_countries`,
   `terms_and_conditions`. Convert singletons (`settings`, `website_cms`) to one‑row‑per‑org.
2. **Scoped uniques** — `admins`: unique(organization_id, email); `forms`: unique(organization_id, slug);
   enable Spatie **teams** with `team_foreign_key = organization_id`.
3. **Foreign key repair** — retype string/int id columns to `foreignId()->constrained()`:
   `packages.group_id`, `plans.group_id`, `orders.user_id`, `orders.course_id`,
   `debit_students.debit_id`, `open_event_items.open_event_id` (rename), `users.*_id`,
   `crm_leads.enquire_id/referral_id`, `website_cms.published_by`.
4. **Deduplicate schemas** — merge `guardiants` into `users` (or vice versa); collapse
   `student_*` catalog into the main catalog; drop JSON subject blobs on `students`
   in favour of the existing pivots; retire legacy form tables once `form_entries`
   migration (already seeded) is verified.
5. **Types & naming** — money → `decimal(10,2)`; statuses → proper booleans/enums; rename
   `guardiants→guardians`, `mettings→meetings`, `enquires→enquiries`, `relation_ships→relationships`
   (with model/table aliases during transition).
6. **Secrets** — move per‑tenant Stripe keys out of plain `settings` columns into encrypted casts
   (like `em_mailbox_settings` already does).

## 5. Design rules going forward

1. Every new tenant‑owned table gets `organization_id` FK + `BelongsToOrganization` trait + index.
2. Child tables (line items) may rely on the parent's org, but always join through the parent.
3. Unique keys on tenant data are always composite with `organization_id`.
4. Money is `decimal`, never float. Statuses are enum‑backed strings with a PHP Enum.
5. Platform tables (`saas_*`, `platform_*`) never carry tenant business data.
6. No new singleton "settings row" tables — key/value per org or per platform.
