# Website CMS: click-to-edit preview

## For staff

- In the preview, choose **Click to edit**. Then point at any outlined part of the website: the menu, the footer, contact details, social icons, the logo or the announcement.
- Click it, and the CMS opens that section with the field ready to type in. Your other changes stay in your draft.
- **Ask AI** on the label opens the assistant with "Change the …:" already typed in.
- Choose **Browse** to click links and move around the site instead.
- The zoom menu (Fit, 50%, 75%, 100%) and the **Expand** button give you a bigger, sharper view. Press Esc to close Expand.

## How it works

**Markers (website, draft preview only).** Editable elements are marked by `cmsEdit(preview, key, { item?, field? })` from `Website/src/lib/cmsEditable.ts`. The areas a section outlines are marked by `cmsRegion(preview, region)`. Both return `{}` unless draft mode is on, so the public site carries no markers. The attributes are:

| Attribute | Example | Meaning |
|---|---|---|
| `data-cms-section` | `footer_menu` | CMS section, as in `docs/ai-assistant-map.md` |
| `data-cms-field` | `heading` | Field in that section (for social links, the network) |
| `data-cms-item` | `Fees` | One entry of a list: a menu link's text, a column title |
| `data-cms-label` | `Footer heading` | Shown in the chip; also what marks an element as editable |
| `data-cms-region` | `footer` | Area outlined for a section; the dashed parent outline |

**Overlay.** `Website/src/components/cms/EditOverlay.tsx` holds the overlay script, under 5 KB and with no libraries.
- **When it runs:** it is added to `<head>` only in draft mode (`layout.tsx`), and it does nothing outside a frame.
- **Outlines:** it draws them with its own absolutely positioned boxes, so the page's own elements are never restyled. Hover updates are batched with `requestAnimationFrame`, and the boxes follow scroll and resize.
- **Keyboard:** it gives editable elements `tabindex=0`, so Enter edits them.
- **Hidden in the frame:** the chat launcher, the preview bar and the Next.js dev overlay. `PreviewBridge` does the same in the Live view.

**Messages** (`postMessage`; each side checks the other's exact origin and the sender window):

| Direction | Type | Payload |
|---|---|---|
| CMS → site | `cms:mode` | `{ mode: "edit" \| "browse" }`, sent on every `cms:ready` and on each toggle |
| site → CMS | `cms:edit` | `{ section, field, item, label }` |
| site → CMS | `cms:ask-ai` | `{ section, field, item, label }` |
| (existing) | `cms:ready` / `cms:height` / `cms:navigate`, `cms:preview-update` / `cms:highlight` / `cms:scroll-to` | see `website-cms-preview.js` |

Origin checks:
- **Website:** it posts only to the origins in `CRM_ORIGIN`, and accepts `cms:mode` only from `window.parent` with one of those origins.
- **CMS:** it accepts messages only from the connected website's origin and from its own preview frame, and only when Draft and Click to edit are on.
- **Payload values:** the CMS trims them, and only acts on sections it knows.

**Opening the field.** On `cms:edit`, `website-cms-preview.js` (`goToField`):
1. Clicks the section in the CMS sidebar. The page doesn't reload, and the section keeps its draft.
2. Looks up `FIELD_TARGETS[section][field]`: a selector, or a function of the item.
3. Scrolls only the editor pane, focuses the field, and flashes a gold ring for 1.2 s (`.wcpv-flash`).

## Making a new element editable

1. Add the key to `CMS_EDITABLE` in `cmsEditable.ts`, using the section and field names from the map:
   ```ts
   tagline: { section: 'branding', field: 'tagline', label: 'Tagline' },
   ```
2. Mark the element (server components read `(await draftMode()).isEnabled`; client components read `useSite().preview`):
   ```tsx
   <h1 {...cmsEdit(preview, 'tagline')}>{site.tagline}</h1>
   ```
3. If the form control isn't listed yet, add it to `FIELD_TARGETS` in `website-cms-preview.js`:
   ```js
   branding: { tagline: '#wcbTagline', … }
   ```

`npm test` in `Website/` fails if any component writes `data-cms-*` attributes by hand.

## Sharpness and zoom

- **The cause:** `transform: scale()` turns the frame into a bitmap and blurs text at fractional sizes.
- **The fix:** the iframe keeps the real device width (1280, 768 or 390 px) and is shrunk with CSS `zoom`, rounded down to 2 decimals. The page is laid out again at that size, so text stays as sharp as the real site.
- **Fallback:** where `CSS.supports('zoom', '0.5')` is false, the panel uses `transform` instead.
- **Browsers:** `zoom` is supported by Chrome, Edge and Safari, and by Firefox from version 126.
- **Rule:** nothing between the iframe and the page may use a transform, filter, `backdrop-filter` or `will-change` layer.
- **Settings:** the zoom choice (Fit, 50%, 75% or 100%) is remembered in `localStorage` (`wcpv:zoom`). At 100% the stage scrolls sideways. The panel's default width is 600 px.

## Editable elements

| On the website | Opens CMS section | Field focused |
|---|---|---|
| Announcement bar | Announcement & Portal | Message |
| Logo / school name (header) | Branding | Short name |
| Menu links and dropdowns | Navigation Menu | That link's text (its row is selected) |
| Header button ("Apply Now") | Navigation Menu | Button text |
| Portal login link | Announcement & Portal | Portal button text |
| Footer heading / footer text | Footer | Heading first line / Intro text |
| Footer column titles and links | Footer | That column's title / that link's text |
| Address, phone, email | Contact Details | Address / Phone / Email |
| WhatsApp button | Contact Details | WhatsApp on/off |
| Social icons | Social Media | That network's link |
| Copyright line | Branding | Copyright text |

## Known limits

- **Page body text** (system pages, hero, sections) is built into the website's code. It shows "Designed in your website's code — ask your developer" instead of an outline.
- **Duplicate menu link text:** menu links are matched by their text, so with two links of the same text the first one opens.
- **Elements inside the site's fixed header** can sit over page content while scrolling. The outline follows the element, not what's visible.
- **New Draft tab:** opening the draft in a new tab keeps the "Preview — unpublished changes" bar, and click-to-edit is off there.
- **Firefox before 126** has no `zoom`, so the preview falls back to `transform` and can look slightly soft.
