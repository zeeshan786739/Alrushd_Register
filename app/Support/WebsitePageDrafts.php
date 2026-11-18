<?php

namespace App\Support;

use App\Models\WebsitePage;

/**
 * Save rules for a connected website's page draft, shared by WebsitePageController and the AI
 * assistant. Run WebsitePage::normaliseDraft() first.
 */
class WebsitePageDrafts
{
    public const RESERVED_PREFIXES = ['/api', '/_next', '/admin', '/w/'];

    /**
     * @return array{field: string, message: string}|null The first problem, keyed to the editor field it belongs to.
     */
    public static function problem(array $draft, int $orgId, ?int $ignoreId = null): ?array
    {
        if ($draft['title'] === '') {
            return ['field' => 'title', 'message' => 'Please give the page a name.'];
        }

        $path = $draft['path'];
        if ($path === '' || $path === '/') {
            if ($ignoreId === null || ! WebsitePage::whereKey($ignoreId)->where('kind', WebsitePage::KIND_SYSTEM)->exists()) {
                return ['field' => 'path', 'message' => 'Please choose a web address, for example /summer-school.'];
            }
        }

        foreach (self::RESERVED_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, rtrim($prefix, '/').'/')) {
                return ['field' => 'path', 'message' => "The address {$path} is reserved by the website. Please choose another."];
            }
        }

        $image = $draft['seo']['og_image'];
        if ($image !== '' && ! str_starts_with($image, '/') && ! preg_match('#^https?://[^\s]+$#i', $image)) {
            return ['field' => 'seo.og_image', 'message' => 'Please paste a full image link starting with https://'];
        }

        $taken = WebsitePage::query()
            ->where('organization_id', $orgId)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->get()
            ->contains(fn (WebsitePage $p) => ($p->draft['path'] ?? null) === $path || $p->path === $path);

        return $taken ? ['field' => 'path', 'message' => "Another page already uses the address {$path}."] : null;
    }
}
