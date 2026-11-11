<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A page of a headless (external) website managed from the CMS.
 *
 * kind = "system": the page is built in the front end's code. The CMS manages its
 *                  SEO overrides and visibility.
 * kind = "custom": the page is built entirely from CMS content blocks (a "form"
 *                  block connects any Form Center form) and is rendered by the
 *                  front end's catch-all page renderer.
 *
 * Every editable attribute lives in the `draft` JSON; publishing copies it to
 * `published`, which is all the public API ever serves.
 */
class WebsitePage extends Model
{
    use BelongsToOrganization;

    public const KIND_SYSTEM = 'system';

    public const KIND_CUSTOM = 'custom';

    public const BLOCK_TYPES = ['hero', 'rich_text', 'image_text', 'features', 'stats', 'faq', 'form', 'cta'];

    protected $fillable = ['organization_id', 'kind', 'path', 'draft', 'published', 'published_at', 'sort_order'];

    protected $casts = [
        'draft' => 'array',
        'published' => 'array',
        'published_at' => 'datetime',
    ];

    public static function blankDraft(): array
    {
        return [
            'title' => '',
            'path' => '',
            'hidden' => false,
            'seo' => ['title' => '', 'description' => '', 'og_image' => '', 'noindex' => false],
            'blocks' => [],
        ];
    }

    /** Normalise and validate-by-construction an incoming draft payload. */
    public static function normaliseDraft(array $input, string $kind): array
    {
        $blank = self::blankDraft();
        $draft = array_replace_recursive($blank, array_intersect_key($input, $blank));

        $draft['title'] = Str::limit(trim((string) $draft['title']), 160, '');
        $draft['path'] = self::normalisePath((string) $draft['path']);
        $draft['hidden'] = (bool) $draft['hidden'];

        $draft['seo']['noindex'] = (bool) $draft['seo']['noindex'];
        foreach (['title', 'description', 'og_image'] as $key) {
            $draft['seo'][$key] = Str::limit(trim((string) $draft['seo'][$key]), 500, '');
        }

        // System pages are rendered by code; blocks are only meaningful for custom pages.
        $draft['blocks'] = $kind === self::KIND_CUSTOM ? self::normaliseBlocks($input['blocks'] ?? []) : [];

        return $draft;
    }

    public static function normalisePath(string $path): string
    {
        $path = Str::lower(trim($path));
        $path = preg_replace('#[^a-z0-9/_-]+#', '-', $path) ?? '';
        $path = '/'.trim(preg_replace('#/+#', '/', $path) ?? '', '/-');

        return $path;
    }

    private static function normaliseBlocks(mixed $blocks): array
    {
        if (! is_array($blocks)) {
            return [];
        }

        $clean = [];
        foreach (array_slice(array_values($blocks), 0, 40) as $block) {
            if (! is_array($block) || ! in_array($block['type'] ?? null, self::BLOCK_TYPES, true)) {
                continue;
            }

            $clean[] = [
                'id' => preg_match('/^[a-z0-9]{6,32}$/', (string) ($block['id'] ?? '')) ? $block['id'] : Str::lower(Str::random(10)),
                'type' => $block['type'],
                'data' => self::cleanValue($block['data'] ?? [], 0),
            ];
        }

        return $clean;
    }

    /** Keep block data to plain strings/bools/numbers/lists, bounded in depth and size. */
    private static function cleanValue(mixed $value, int $depth): mixed
    {
        if (is_array($value)) {
            if ($depth > 3) {
                return [];
            }
            $out = [];
            foreach (array_slice($value, 0, 60, true) as $k => $v) {
                $key = is_int($k) ? $k : Str::limit(preg_replace('/[^a-z0-9_]/i', '', (string) $k) ?? '', 40, '');
                $out[$key] = self::cleanValue($v, $depth + 1);
            }

            return array_is_list($value) ? array_values($out) : $out;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return Str::limit((string) $value, 20000, '');
    }

    public function isPublished(): bool
    {
        return ! empty($this->published);
    }

    public function hasUnpublishedChanges(): bool
    {
        return json_encode($this->draft) !== json_encode($this->published);
    }

    public function publish(): void
    {
        $this->published = $this->draft;
        $this->path = $this->draft['path'] ?? $this->path;
        $this->published_at = now();
        $this->save();
    }

    public function discard(): void
    {
        if ($this->published) {
            $this->draft = $this->published;
            $this->save();
        }
    }

    /** Admin-facing representation. */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'draft' => $this->draft,
            'is_published' => $this->isPublished(),
            'has_changes' => $this->hasUnpublishedChanges(),
            'published_path' => $this->path,
            'published_at' => $this->published_at?->toIso8601String(),
            'sort_order' => $this->sort_order,
        ];
    }

    /** Public API representation of one version (draft or published). */
    public static function toPublicArray(self $page, array $version): array
    {
        return [
            'id' => $page->id,
            'kind' => $page->kind,
            'path' => $version['path'] ?? $page->path,
            'title' => $version['title'] ?? '',
            'hidden' => (bool) ($version['hidden'] ?? false),
            'seo' => $version['seo'] ?? [],
            'blocks' => $version['blocks'] ?? [],
            'updated_at' => ($page->published_at ?? $page->updated_at)?->toIso8601String(),
        ];
    }
}
