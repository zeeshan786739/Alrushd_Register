# Website CMS → AI assistant: field map

The assistant may only touch what is listed here. Every write goes through the same save path as
the CMS form; nothing writes to the database directly.

- **Site settings** (every section except Pages): validated by `App\Support\WebsiteCmsValidator`.
  These are the rules `WebsiteCmsController::saveDraft` uses; they moved there unchanged so both
  callers share one copy. The draft is then saved with `WebsiteCmsService::saveDraft()`, whose
  `merge()` runs the same upgrade and normalise steps as a form save.
- **Pages**: `WebsitePage::normaliseDraft()`, then `WebsitePageDrafts::problem()` (the old
  `WebsitePageController::validateDraft`, moved unchanged), then `$page->update(['draft' => …])`.
- **Publishing**: there is no publish tool. Only the Publish button publishes (`WebsiteCmsService::publish`).

Permissions:

- **Reading** a section: `view setting`.
- **Changing** any section: `edit setting`.
- **Custom code, and analytics IDs through the assistant**: `edit website code`.
- **The AI assistant settings screen**: `manage ai assistant` (new; given to `super-admin`).

| Section (key) | Fields: type / allowed values / rules |
|---|---|
| **Pages** (`pages`, `WebsitePage`) | `title` string ≤160, required · `hidden` bool · `seo.title` ≤500 · `seo.description` ≤500 · `seo.noindex` bool · `seo.og_image` `/path` or `https://…` |
| **Navigation menu** (`nav_menu`) | `items[]` {`label` ≤120, `href` ≤500, `group` ≤120 (dropdown name, "" = top level), `description` ≤300}, max 200 · `cta_text` · `cta_url`. Normalised by `normaliseNavItems`; no other rules |
| **Footer** (`footer_menu`) | `heading` ≤120 (`First line\|Second line`) · `description` · `items[]` {`group` ≤80 (column), `label` ≤120, `href` ≤500}. Normalised by `normaliseFooterHeading` / `normaliseFooterItems` |
| **Website forms** (`website.forms`) | `apply`, `contact`, `review`, `newsletter` → slug of an active `dynamic` Form Center form (`availableForms`). There is no server rule, so the assistant only accepts a listed slug |
| **Announcement & portal** (`website`) | `announcement` ≤120 · `announcement_enabled` bool · `announcement_link_text` ≤40 · `announcement_url` (`/page`, `https://`, `mailto:`, `tel:`, `#`) · `announcement_style` gold\|navy\|cream · `announcement_starts_at` / `announcement_ends_at` Y-m-d (end ≥ start) · `portal_url` http(s) URL · `portal_button_text` ≤40 |
| **Contact details** (`contact`) | `phone` (≥7 digits) · `email` (valid) · `address` · `whatsapp_enabled` bool · `whatsapp_same_as_phone` bool · `whatsapp_number` (≥7 digits when used) · `whatsapp_message` ≤300 · `opening_days` [7 bools, Mon–Sun] · `opening_from` / `opening_until` HH:MM (until > from) · `opening_timezone` one of `ContactDetails::TIMEZONES`. `whatsapp` and `working_hours` are worked out from these on save |
| **Social media** (`social`) | `facebook`, `instagram`, `linkedin`, `twitter`, `youtube`, `tiktok`, `whatsapp`, `threads`: a full link on that network's domain (`SocialProfiles::linkProblem`) · `enabled.{network}` bool (the icon is shown) · `whatsapp_use_contact` bool |
| **Branding** (`branding`) | `company_name` required ≤120 · `short_name` required ≤30 · `tagline` ≤120 · `website_description` ≤300 · `copyright` ≤150 · `copyright_auto_year` bool |
| **Theme colours** (`theme`) | `primary`, `navbar`, `footer`, `accent`, `cream`, `text`, `heading`: colours from the colour picker. There is no server rule, so the assistant accepts only `#RRGGBB` |
| **SEO** (`seo`) | `meta_title` ≤200 · `meta_description` ≤500 · `meta_keywords` ≤500 · `og_image` http(s) URL · `twitter_card` summary_large_image\|summary · `canonical_url` http(s) URL ≤255 · `robots` ≤100 · `google_verification` (code ≥10 chars) |
| **Analytics** (`analytics`) | `google_analytics` G-XXXX · `google_tag_manager` GTM-XXXX · `facebook_pixel` 15–16 digits · `microsoft_clarity` 8–12 lower-case letters and digits (`AnalyticsSettings::TOOLS`) · `require_consent` bool |
| **Custom code** (`custom_code`) | `custom_css_enabled`, `head_code_enabled`, `footer_code_enabled` bool. The code text (`custom_css`, `head_scripts`, `footer_scripts`, ≤50 KB) is **not** editable by the assistant (see below) |

## Left out on purpose

- **Custom code text.** It runs on every page of the live site, and generated or injected
  script is a real risk. The assistant can switch a block on or off, but not write code.
- **Logo and browser icon** (`website_logo`, `website_favicon`, `website_favicon_icons`). These
  are file uploads that go through `BrandingImages`; there is nothing to type.
- **Page content blocks, and creating, deleting or reordering pages.** The block builder has
  nested, typed content, and deleting is irreversible. Page `path` is also left out: changing an
  address breaks links and search rankings.
- **Website connection and API token, legacy built-in-site sections, and `system`.**

## Unclear

- Theme colours and the Website forms slugs are not validated on the server for the normal form;
  the assistant adds the two checks above.
- Analytics IDs need only `edit setting` in the form. The assistant also asks for
  `edit website code`, because the IDs load third-party scripts on every page.
