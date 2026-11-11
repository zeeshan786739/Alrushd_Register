# Connected (headless) websites

A school can either use the CRM's **built-in** one-page landing site, or connect its own
**external website** (e.g. Al-Rushd's Next.js site) that reads content, pages, menus and
forms from the Website CMS. Only schools switched to `headless` are affected; every other
tenant keeps the built-in site.

```
 Website CMS (CRM)  ──publish──▶  POST {site}/api/revalidate   (cache refresh)
        ▲                                   │
        │  GET /api/headless/v1/*  ◀────────┘  site reads content (Bearer token)
        │  POST …/forms/{slug}/submit ◀──────  site forwards form submissions
        ▼
  FormEntry → CRM lead (existing FormEntryObserver)
```

## Data
- `website_cms.mode` (`builtin` | `headless`), `external_url`, `api_token` (encrypted) + `api_token_hash`
  (lookup), `redirect_legacy`, `last_revalidated_at/status`.
- `website_pages`: `kind` = `system` (page lives in the website's code; CMS manages SEO + hidden flag)
  or `custom` (built from CMS blocks: hero, rich_text, image_text, features, stats, faq, form, cta).
  Edits go to `draft`; the CMS **Publish** button publishes site settings *and* all changed pages.
- CMS sections used by headless sites: `nav_menu`, `footer_menu`, `website` (form mapping,
  portal URL, announcement), plus `branding`, `theme`, `contact`, `social`, `seo`, `analytics`, `custom_code`.

## API (`routes/api.php`, middleware `headless.site`)
| Method | Path | Notes |
|---|---|---|
| GET | `/api/headless/v1/ping` | token check |
| GET | `/api/headless/v1/site` | settings + page summaries; `?draft=1` for preview |
| GET | `/api/headless/v1/pages` | page summaries |
| GET | `/api/headless/v1/page?path=/x` | one page with blocks |
| GET | `/api/headless/v1/forms/{slug}` | Form Center schema |
| POST | `/api/headless/v1/forms/{slug}/submit` | creates a FormEntry; `X-Visitor-IP` recorded |

`system.*` (Stripe keys) and other secrets are never returned.

## Preview
`HeadlessWebsiteService::previewUrl()` signs `path|expires` with HMAC-SHA256 using the API token.
The website verifies it at `/api/preview`, enables Next.js draft mode and then requests `?draft=1`.
Unsaved editor changes are pushed to the cache key `cms_live_preview_org_{id}` and shown in preview.

## Connecting a school
```bash
php artisan migrate
php artisan website:connect-headless al-rushd --url=https://www.alrushd.co.uk
```
The command is idempotent. It switches the school to `headless`, creates the API token, the
`website-apply/contact/review/newsletter` forms, registers the website's built-in pages and seeds
menus/contact/theme to match the site (publishing it — the previous version stays in Version History).
It prints `CRM_API_URL`, `CRM_API_TOKEN` and `CRM_ORIGIN` for the website's environment.

To switch back: Website CMS → Website Connection → Built-in website.
The old landing page (`/`, `/privacy-policy`) 301-redirects to the connected site while
`redirect_legacy` is on; `/forms/*` and payment pages keep working.
