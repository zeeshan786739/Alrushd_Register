# CRM deployment: staging and production

The marketing website (Next.js, repo `chandjr0/al-rushad-web`) reads its content from this
CRM. See `docs/HEADLESS_WEBSITE.md` for how the two talk to each other.

## Branches

| Branch | Deploys to | Notes |
|---|---|---|
| `main` | **production**: `portal.alrushd.co.uk` | Do not deploy the website work here until staging is approved. |
| `staging` | **staging**: `staging-portal.alrushd.co.uk` (suggested) | `main` + the connected-website work. |

To release after approval, merge `staging` into `main` (pull request), then follow **Go-live** below.

## Servers and URLs

| | Production | Staging |
|---|---|---|
| CRM | `https://portal.alrushd.co.uk` (live now) | `https://staging-portal.alrushd.co.uk` |
| Marketing website | `https://web.alrushd.co.uk` | `https://staging-web.alrushd.co.uk` |
| CRM database | live DB | **separate** DB (a copy of production is fine; see the warnings below) |
| CRM folder (example) | `/var/www/alrushd-crm` | `/var/www/alrushd-crm-staging` |

Staging must never share a database, `storage/` folder or `.env` with production.

## Requirements

PHP 8.2+ (ext: pdo_mysql, mbstring, intl, gd, zip, imap, bcmath, curl), Composer 2, MySQL 8,
Node is **not** needed (built assets are committed in `public/build`).
The server must be able to make outgoing HTTPS requests to the marketing website
(the CRM calls it on every Publish).

## `.env` — staging

Start from production's `.env` and change:

```dotenv
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging-portal.alrushd.co.uk
APP_KEY=            # run: php artisan key:generate  (a NEW key; never reuse production's)

DB_DATABASE=alrushd_crm_staging
DB_USERNAME=…       # a staging-only DB user
DB_PASSWORD=…

# A copy of production data contains real parents. Never let staging email them.
MAIL_MAILER=log

# Platform billing (Enrolliq): Stripe TEST keys only
PLATFORM_STRIPE_KEY=pk_test_…
PLATFORM_STRIPE_SECRET=sk_test_…
PLATFORM_STRIPE_WEBHOOK_SECRET=…
```

After copying the production database into staging, **also** do this in the staging DB, because
these settings live in the database, not in `.env`:

1. **Stripe for school fees**: replace each school's live keys (CRM → Customer Payments / payment
   settings, table `organization_payment_settings`) with Stripe test keys, or turn online payments off.
2. **Email inbox (Email Marketing → Inbox / Settings)**: remove the IMAP/SMTP mailbox credentials,
   or staging will read and reply from the real mailbox.
3. **Integrations (Facebook Lead Ads, TikTok)**: disconnect them on staging; the real webhooks point to production.
4. **Do not add the scheduler cron or a queue worker on staging** unless steps 1–3 are done
   (the scheduler runs `email-marketing:fetch` every 5 minutes against the stored mailbox).

## `.env` — production

No new variables are needed for the website connection; it is configured in the database by the
command below. Check these are already right:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://portal.alrushd.co.uk
```

## Deploy (both environments)

```bash
cd /var/www/alrushd-crm-staging          # or the production folder
git fetch origin && git checkout staging && git pull   # production: main
composer install --no-dev --optimize-autoloader
php artisan database:production-preflight   # read-only checks before migrating
php artisan migrate --force
php artisan storage:link                    # once; CMS image uploads are served from /storage
php artisan optimize:clear && php artisan optimize
```

Production only: keep the existing cron
`* * * * * cd /var/www/alrushd-crm && php artisan schedule:run >> /dev/null 2>&1`
and queue worker (`php artisan queue:work --tries=3`) as they are today.

Migration added by this release: `2026_11_11_100000_add_headless_website_support` (adds columns to
`website_cms`, creates `website_pages`). It changes nothing visible until the connect command runs.

## Connect the school to the marketing website

Run once per environment, **after** the website for that environment is deployed:

```bash
# staging
php artisan website:connect-headless al-rushd --url=https://staging-web.alrushd.co.uk
# production (go-live only)
php artisan website:connect-headless al-rushd --url=https://web.alrushd.co.uk
```

It prints three values. Put them in the website's `.env.production.local` for the same environment,
rebuild and restart the website (see the website's `DEPLOYMENT.md`):

```
CRM_API_URL=https://<crm-host>/api/headless/v1
CRM_API_TOKEN=wst_…
CRM_ORIGIN=https://<crm-host>
```

Then in the CRM: **Website CMS → Website Connection → Test connection** must say "Connected".

What the command does (safe to re-run; it never deletes anything):
- switches Al-Rushd to "connected website" mode and creates the API token;
- creates the Form Center forms `website-apply`, `website-contact`, `website-review`, `website-newsletter`;
- registers the website's 24 built-in pages;
- fills menus, contact details, theme and SEO to match the website and **publishes** them. The
  previous CMS version stays in Website CMS → Dashboard → Version History.
- from then on, `https://<crm-host>/w/al-rushd/` and `/w/al-rushd/privacy-policy` 301-redirect to
  the website. `/w/al-rushd/forms/*` and payment pages keep working.

Options: `--no-redirect` keeps the old landing page live; `--no-content` skips the content seeding.
To undo: Website CMS → Website Connection → Built-in website → Save.

## Go-live checklist (production)

1. Staging approved by Al-Rushd.
2. Merge `staging` → `main`; deploy the CRM (steps above).
3. Deploy the website `main` to `web.alrushd.co.uk`. It can boot with `CRM_API_TOKEN` empty
   (it shows its built-in content), but forms only work once step 5 is done.
4. `php artisan website:connect-headless al-rushd --url=https://web.alrushd.co.uk`
5. Put the printed values in the website's env, rebuild, restart.
6. CMS → Website Connection → **Test connection**; then change something small, **Publish**, and
   confirm it appears on `web.alrushd.co.uk` within seconds.
7. Submit the website's Contact form and check the lead appears in CRM → Leads.

## Other changes in this release that affect production

- `/cmd` and `/email-test` are now only available when `APP_ENV=local`. They were public:
  `/email-test` emailed the latest form submission to an outside Gmail address, and `/cmd` let anyone
  clear caches. Use `php artisan optimize:clear` during deployments instead.
