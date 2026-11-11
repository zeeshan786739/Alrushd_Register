<?php

namespace App\Services;

use App\Models\Form;
use App\Models\WebsiteCms;
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
    public function previewUrl(WebsiteCms $cms, string $path = '/'): ?string
    {
        if (! $cms->isHeadless() || ! $cms->api_token) {
            return null;
        }

        $path = '/'.ltrim($path, '/');
        $expires = now()->addHours(2)->timestamp;
        $signature = hash_hmac('sha256', $path.'|'.$expires, $cms->api_token);

        return $cms->externalBase().'/api/preview?'.http_build_query([
            'path' => $path,
            'expires' => $expires,
            'signature' => $signature,
        ]);
    }

    /** Check that the external site is up and holds the same API token. */
    public function testConnection(WebsiteCms $cms): array
    {
        if (! $cms->externalBase()) {
            return ['ok' => false, 'message' => 'Enter the website URL first.'];
        }

        try {
            $response = Http::timeout(8)->withToken((string) $cms->api_token)->acceptJson()
                ->get($cms->externalBase().'/api/revalidate');
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach '.$cms->externalBase().'.'];
        }

        return match (true) {
            $response->successful() => ['ok' => true, 'message' => 'Connected. The website accepted this CRM\'s API token.'],
            $response->status() === 401 => ['ok' => false, 'message' => 'The website is reachable but its CRM_API_TOKEN does not match.'],
            $response->status() === 503 => ['ok' => false, 'message' => 'The website is reachable but CRM_API_TOKEN is not set there yet.'],
            default => ['ok' => false, 'message' => 'The website answered with HTTP '.$response->status().'.'],
        };
    }
}
