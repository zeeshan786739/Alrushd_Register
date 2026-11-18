<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Admin;
use App\Models\WebsiteCms;
use App\Support\PreviewToken;
use App\Models\WebsitePage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Serves CMS content to an external ("headless") front end and keeps that front
 * end in sync: revalidation webhooks on publish and signed draft-preview links.
 */
class HeadlessWebsiteService
{
    /** Site-wide sections an external front end may read. Secrets (e.g. system.stripe_secret) never leave the CRM. */
    private const PUBLIC_SECTIONS = [
        'branding', 'theme', 'contact', 'social', 'seo', 'analytics', 'custom_code', 'website', 'nav_menu', 'footer_menu',
    ];

    public function __construct(private WebsiteCmsService $cms) {}

    public static function liveCacheKey(int $organizationId): string
    {
        return 'cms_live_preview_org_'.$organizationId;
    }

    /** Global site configuration plus a summary of every page (for navigation, footer, SEO and sitemap). */
    public function site(WebsiteCms $cms, bool $draft = false): array
    {
        $data = $draft ? $this->draftData($cms) : $this->cms->getPublished();

        $site = array_intersect_key($data, array_flip(self::PUBLIC_SECTIONS));
        // Worked out here so the website doesn't need its own copy of the rules (it re-checks
        // the dates in the browser too, because its pages are cached).
        $site['website']['announcement_active'] = WebsiteCmsService::announcementIsActive($site['website'] ?? []);
        // Custom code switched off in the CMS is kept there but never sent to the website.
        foreach (['custom_css' => 'custom_css_enabled', 'head_scripts' => 'head_code_enabled', 'footer_scripts' => 'footer_code_enabled'] as $block => $flag) {
            if (isset($site['custom_code']) && ($site['custom_code'][$flag] ?? true) === false) {
                $site['custom_code'][$block] = '';
            }
        }

        return [
            'organization' => [
                'name' => $cms->organization?->name,
                'slug' => $cms->organization?->slug,
            ],
            'published_at' => $cms->published_at?->toIso8601String(),
            'draft' => $draft,
            'settings' => $site,
            'pages' => $this->pages($cms, $draft, withBlocks: false),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function pages(WebsiteCms $cms, bool $draft = false, bool $withBlocks = false): array
    {
        return WebsitePage::query()
            ->where('organization_id', $cms->organization_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (WebsitePage $page) use ($draft, $withBlocks) {
                $version = $draft ? $page->draft : $page->published;
                if (empty($version)) {
                    return null;
                }
                $row = WebsitePage::toPublicArray($page, $version);
                if (! $withBlocks) {
                    unset($row['blocks']);
                }

                return $row;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function page(WebsiteCms $cms, string $path, bool $draft = false): ?array
    {
        $path = WebsitePage::normalisePath($path);

        foreach ($this->pages($cms, $draft, withBlocks: true) as $page) {
            if ($page['path'] === $path) {
                return $page;
            }
        }

        return null;
    }

    /** Draft content, preferring the unsaved live-preview state pushed from the editor. */
    private function draftData(WebsiteCms $cms): array
    {
        $live = Cache::get(self::liveCacheKey($cms->organization_id));

        return is_array($live) ? $this->cms->merge($live) : $this->cms->getDraft();
    }

    /** Forms the CMS can connect to pages / site forms. */
    /**
     * The connected website's four built-in forms and the field keys each one posts to the CRM
     * (Website/src/app/api/forms/[type]/route.ts → toCrm()). Keep in step with that file.
     * `default` is the Form Center slug the website uses when nothing is chosen (DEFAULT_FORMS).
     */
    public const WEBSITE_FORMS = [
        'apply' => ['default' => 'website-apply', 'keys' => [
            'full_name' => 'Parent or guardian name', 'phone' => 'Phone', 'email' => 'Email',
            'students' => 'Children', 'plus' => 'Interested in Al-Rushd Plus', 'consent' => 'Consent',
        ]],
        'contact' => ['default' => 'website-contact', 'keys' => [
            'full_name' => 'Name', 'phone' => 'Phone', 'email' => 'Email',
            'topic' => 'Enquiry about', 'message' => 'Message', 'consent' => 'Consent',
        ]],
        'review' => ['default' => 'website-review', 'keys' => [
            'full_name' => 'Name', 'email' => 'Email', 'role' => 'I am a', 'rating' => 'Rating',
            'context' => 'Year group or subject', 'title' => 'Review title', 'review' => 'Review', 'consent' => 'Consent',
        ]],
        'newsletter' => ['default' => 'website-newsletter', 'keys' => [
            'email' => 'Email', 'year_group' => "Child's year group",
        ]],
    ];

    /**
     * How each website form would behave with the given Form Center assignments, by comparing
     * the real field keys of the form the website will post to with the keys it sends.
     *
     *   connected      the form exists and has every field the website sends
     *   missing        some answers have no matching field — they would not be stored
     *   rejected       the form requires a field the website never sends — every submission fails
     *   not_connected  no active form with that slug — submissions are not saved
     *
     * @param  array<string, mixed>  $assigned  slot => slug ('' = use the website's default form)
     * @return array<string, array<string, mixed>>
     */
    public function websiteFormStatus(int $organizationId, array $assigned): array
    {
        $slugs = [];
        foreach (self::WEBSITE_FORMS as $slot => $def) {
            $slugs[] = $def['default'];
            $chosen = trim((string) ($assigned[$slot] ?? ''));
            if ($chosen !== '') {
                $slugs[] = $chosen;
            }
        }

        // Same lookup the submit endpoint uses: an active form of this school with that slug.
        $forms = Form::query()
            ->where('organization_id', $organizationId)
            ->whereIn('slug', array_unique($slugs))
            ->where('is_active', true)
            ->with('fields')
            ->get()
            ->keyBy('slug');

        $out = [];
        foreach (self::WEBSITE_FORMS as $slot => $def) {
            $chosen = trim((string) ($assigned[$slot] ?? ''));
            $form = $forms->get($chosen !== '' ? $chosen : $def['default']);
            $recommended = $forms->get($def['default']);

            $row = [
                'slot' => $slot,
                'chosen' => $chosen,
                'automatic' => $chosen === '' && $form !== null,
                'form' => $form ? ['id' => $form->id, 'name' => $form->name, 'slug' => $form->slug] : null,
                'recommended' => $recommended ? ['name' => $recommended->name, 'slug' => $recommended->slug] : null,
                'missing' => [],
                'rejecting' => [],
            ];

            if (! $form) {
                $row['status'] = 'not_connected';
            } else {
                $keys = $form->fields->pluck('key')->filter()->all();
                $row['missing'] = array_values(array_diff_key($def['keys'], array_flip($keys)));
                $row['rejecting'] = $form->fields
                    ->filter(fn ($f) => $f->required && $f->key && ! array_key_exists($f->key, $def['keys']))
                    ->pluck('label')->values()->all();
                $row['status'] = $row['rejecting'] ? 'rejected' : ($row['missing'] ? 'missing' : 'connected');
            }

            $out[$slot] = $row;
        }

        return $out;
    }

    public function availableForms(int $organizationId): array
    {
        return Form::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->where('handler', 'dynamic')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Form $f) => ['id' => $f->id, 'name' => $f->name, 'slug' => $f->slug])
            ->all();
    }

    public function publishPages(int $organizationId): int
    {
        $count = 0;
        WebsitePage::query()->where('organization_id', $organizationId)->get()
            ->each(function (WebsitePage $page) use (&$count) {
                if ($page->hasUnpublishedChanges()) {
                    $page->publish();
                    $count++;
                }
            });

        return $count;
    }

    public function discardPages(int $organizationId): void
    {
        WebsitePage::query()->where('organization_id', $organizationId)->get()
            ->each(function (WebsitePage $page) {
                // Never-published pages have nothing to fall back to; keep them as drafts.
                $page->discard();
            });
    }

    /**
     * Ask the external front end to drop its cached content. Failures are recorded
     * (and shown in the CMS) but never block publishing.
     */
    public function revalidate(WebsiteCms $cms): bool
    {
        if (! $cms->isHeadless() || ! $cms->api_token) {
            return false;
        }

        try {
            $response = Http::timeout(8)
                ->withToken($cms->api_token)
                ->acceptJson()
                ->post($cms->externalBase().'/api/revalidate', ['tags' => ['crm']]);

            $ok = $response->successful();
            $status = $ok ? 'ok' : 'HTTP '.$response->status();
        } catch (Throwable $e) {
            Log::warning('Headless revalidate failed', ['organization_id' => $cms->organization_id, 'error' => $e->getMessage()]);
            $ok = false;
            $status = 'unreachable';
        }

        $cms->forceFill(['last_revalidated_at' => now(), 'last_revalidate_status' => $status])->save();

        return $ok;
    }

    /**
     * Short-lived signed link that switches the external site into draft mode.
     * The front end verifies the HMAC with the same API token it uses to call the CRM.
     */
    /**
     * Opens the website in DRAFT mode for the signed-in admin: {site}/api/preview?cms_preview=TOKEN.
     * The website checks the token with the CRM (/preview/verify) before showing drafts.
     */
    public function previewUrl(WebsiteCms $cms, string $path = '/', ?Admin $admin = null): ?string
    {
        $admin ??= auth('admin')->user();
        if (! $cms->isHeadless() || ! $cms->api_token || ! $cms->externalBase() || ! $admin instanceof Admin) {
            return null;
        }

        return $cms->externalBase().'/api/preview?'.http_build_query(['cms_preview' => PreviewToken::issue($cms, $admin, $path)]);
    }

    /** The published page, shown inside the CMS preview: leaves draft mode first, then opens the path. */
    public function liveFrameUrl(WebsiteCms $cms, string $path = '/'): ?string
    {
        if (! $cms->isHeadless() || ! $cms->externalBase()) {
            return null;
        }

        return $cms->externalBase().'/api/preview/exit?'.http_build_query(['path' => PreviewToken::cleanPath($path), 'frame' => 1]);
    }

    /**
     * "scheme://host[:port]" exactly as browsers report it in MessageEvent.origin (lower case,
     * default ports dropped) — the only origin the CMS accepts preview messages from.
     */
    public static function origin(?string $url): ?string
    {
        $parts = parse_url(trim((string) $url));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }
        $port = $parts['port'] ?? null;
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $port = null;
        }

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    /**
     * Check the connection in BOTH directions, so a wrong setting names itself:
     *  1. the CRM reaches the website and it holds the same API token;
     *  2. it really is this school's website (not another app on that port);
     *  3. the website reaches the CRM (its CRM_API_URL + token);
     *  4. the website lets this CMS page frame it for the live preview (its CRM_ORIGIN).
     *
     * $cmsOrigin is the CMS page's own origin (scheme://host:port the admin is using);
     * $crmApiUrl the headless API address the website should use.
     *
     * @return array{ok: bool, code: string, message: string}
     */
    public function testConnection(WebsiteCms $cms, ?string $cmsOrigin = null, ?string $crmApiUrl = null): array
    {
        $base = $cms->externalBase();
        if (! $base) {
            return ['ok' => false, 'code' => 'no_url', 'message' => 'Enter the website URL first.'];
        }

        try {
            $request = Http::timeout(12)->withToken((string) $cms->api_token)->acceptJson();
            if ($cmsOrigin) {
                $request = $request->withHeaders(['X-CMS-Origin' => $cmsOrigin]);
            }
            $response = $request->get($base.'/api/revalidate');
        } catch (Throwable $e) {
            return ['ok' => false, 'code' => 'unreachable', 'message' => 'Could not reach '.$base.'. Is the website running at that address?'];
        }

        $json = $response->json();
        $isOurs = is_array($json) && (array_key_exists('ok', $json) || array_key_exists('error', $json));

        if (! $isOurs) {
            // Something answered, but not the school website's CMS endpoint: usually another app on that port.
            return ['ok' => false, 'code' => 'wrong_site', 'message' => 'Something is running at '.$base.', but it isn\'t your school website (HTTP '.$response->status().' from its CMS endpoint). Check the website address and port.'];
        }
        if ($response->status() === 401) {
            return ['ok' => false, 'code' => 'token', 'message' => 'The website is reachable but its CRM_API_TOKEN does not match.'];
        }
        if ($response->status() === 503) {
            return ['ok' => false, 'code' => 'token_missing', 'message' => 'The website is reachable but CRM_API_TOKEN is not set there yet.'];
        }
        if (! $response->successful()) {
            return ['ok' => false, 'code' => 'http', 'message' => 'The website answered with HTTP '.$response->status().'.'];
        }

        // Older website versions only answer {ok: true}: nothing more to check.
        if (! isset($json['crm']) || ! is_array($json['crm'])) {
            return ['ok' => true, 'code' => 'ok', 'message' => 'Connected. The website accepted this CRM\'s API token.'];
        }

        $crm = $json['crm'];
        $theirs = rtrim((string) ($crm['url'] ?? ''), '/');
        $expected = $crmApiUrl ? rtrim($crmApiUrl, '/') : null;
        $fixUrl = $expected ? ' Set CRM_API_URL='.$expected.' in the website\'s .env and restart it.' : '';

        switch ($crm['status'] ?? '') {
            case 'not_configured':
                return ['ok' => false, 'code' => 'crm_not_configured', 'message' => 'The website doesn\'t know where this CRM is: CRM_API_URL is empty.'.$fixUrl];
            case 'token':
                return ['ok' => false, 'code' => 'crm_token', 'message' => 'The website reached the CRM at '.$theirs.', but the CRM refused its token. Check the website\'s CRM_API_TOKEN.'];
            case 'unreachable':
            case 'error':
                // The website couldn't call us back. If it points at exactly this CRM, that is most likely a
                // single-worker dev server (busy answering this very test), not a wrong setting.
                if (! $expected || self::sameServer($theirs, $expected)) {
                    break;
                }

                return ['ok' => false, 'code' => 'crm_unreachable', 'message' => 'The website can\'t reach this CRM: its CRM_API_URL is '.($theirs ?: '(empty)').'.'.$fixUrl];
        }

        if (($json['cms_origin_allowed'] ?? null) === false && $cmsOrigin) {
            return ['ok' => false, 'code' => 'frame_origin', 'message' => 'Live preview is blocked: the website\'s CRM_ORIGIN doesn\'t include '.$cmsOrigin.'. Add it (several can be separated by spaces) and restart the website.'];
        }

        return ['ok' => true, 'code' => 'ok', 'message' => 'Connected both ways: the website accepted this CRM\'s token, reads its content, and can be shown in the live preview.'];
    }

    /** Same scheme, port and path; "localhost" and "127.0.0.1" count as the same machine. */
    private static function sameServer(string $a, string $b): bool
    {
        $norm = static function (string $url): string {
            $p = parse_url(strtolower($url)) ?: [];
            $host = in_array($p['host'] ?? '', ['localhost', '127.0.0.1', '[::1]', '::1'], true) ? 'loopback' : ($p['host'] ?? '');

            return ($p['scheme'] ?? '').'://'.$host.':'.($p['port'] ?? '').rtrim($p['path'] ?? '', '/');
        };

        return $norm($a) === $norm($b);
    }
}
