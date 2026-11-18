<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\WebsiteCms;

/**
 * Website CMS → SEO: maps the plain-English choices to the stored values the website reads,
 * and runs the "Quick check". Mirrored by the CMS editor (website-cms-seo.js).
 *
 *   twitter_card   "summary_large_image" (Large picture) | "summary" (Small square)
 *   robots         "index, follow" (Allow search engines) | "noindex, nofollow" (Hide the whole website)
 *   canonical_url  the website's main address: https, not a test/local address
 */
class SeoSettings
{
    public const TWITTER_CARDS = ['summary_large_image', 'summary'];

    public const ROBOTS_ALLOW = 'index, follow';

    public const ROBOTS_HIDE = 'noindex, nofollow';

    public const TITLE_MAX = 60;

    public const DESCRIPTION_MIN = 70;

    public const DESCRIPTION_MAX = 160;

    /** Any robots value containing "noindex" keeps the whole website out of Google. */
    public static function isHidden(?string $robots): bool
    {
        return (bool) preg_match('/\bnoindex\b/i', (string) $robots);
    }

    public static function robotsFor(bool $allow): string
    {
        return $allow ? self::ROBOTS_ALLOW : self::ROBOTS_HIDE;
    }

    /** null = a real public https address; otherwise 'empty' | 'format' | 'not_https' | 'test'. */
    public static function canonicalProblem(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return 'empty';
        }
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! $parts || ! isset($parts['scheme']) || $host === '' || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return 'format';
        }
        if (self::isTestHost($host)) {
            return 'test';
        }

        return strtolower($parts['scheme']) === 'https' ? null : 'not_https';
    }

    public static function isTestHost(string $host): bool
    {
        $host = trim(strtolower($host), '[]');
        if (in_array($host, ['localhost', '0.0.0.0', '::1'], true) || ! str_contains($host, '.') && ! str_contains($host, ':')) {
            return true;
        }
        if (preg_match('/\.(localhost|local|test|example|invalid|internal)$/', $host)) {
            return true;
        }
        // A bare IP address (127.0.0.1, 192.168.x, any IPv6…) is never a school's website address.
        return (bool) filter_var($host, FILTER_VALIDATE_IP);
    }

    /**
     * Google's verification: accepts the bare code or the whole tag Google shows,
     * e.g. <meta name="google-site-verification" content="AbC123…" />.
     */
    public static function verificationCode(?string $input): string
    {
        $input = trim((string) $input);
        if (preg_match('/content\s*=\s*["\']([^"\']*)["\']/i', $input, $m)) {
            $input = $m[1];
        }

        return preg_replace('/[^A-Za-z0-9_\-]/', '', $input) ?? '';
    }

    /**
     * The address offered by the one-click fix: the connected website when it is a real https
     * address, else the school's verified custom domain, else the website on its profile.
     */
    public static function suggestedAddress(WebsiteCms $cms, ?Organization $organization): ?string
    {
        $candidates = [
            $cms->external_url,
            $organization?->hasVerifiedCustomDomain() ? 'https://'.$organization->custom_domain : null,
            $organization?->website,
        ];
        foreach ($candidates as $url) {
            $url = rtrim(trim((string) $url), '/');
            if ($url === '') {
                continue;
            }
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }
            $url = preg_replace('#^http://#i', 'https://', $url);
            if (self::canonicalProblem($url) === null) {
                return $url;
            }
        }

        return null;
    }

    /**
     * The Quick check. Each: key, level (error | warning | info), title, text, fix (optional).
     * Only errors and warnings count as "things to fix".
     *
     * @return list<array{key: string, level: string, title: string, text: string, fix?: string}>
     */
    public static function checks(array $seo, ?string $suggestedAddress = null, bool $hasDefaultImage = false): array
    {
        $checks = [];
        $canonical = trim((string) ($seo['canonical_url'] ?? ''));
        $problem = self::canonicalProblem($canonical);
        if ($problem !== null) {
            $checks[] = [
                'key' => 'canonical',
                'level' => 'error',
                'title' => $problem === 'empty' ? 'Your website address is missing' : ($problem === 'test' ? 'Your website address is set to a test address' : 'Your website address isn’t a secure https address'),
                'text' => $problem === 'empty'
                    ? 'Add the address your website is on, so Google links to the right place.'
                    : "It says “{$canonical}”, so Google may ignore your pages or link to the wrong place.",
            ] + ($suggestedAddress ? ['fix' => $suggestedAddress] : []);
        }

        $hidden = self::isHidden($seo['robots'] ?? '');
        $checks[] = $hidden
            ? ['key' => 'robots', 'level' => 'error', 'title' => 'Your whole website is hidden from Google', 'text' => 'Families searching for your school won’t find it.', 'fix' => 'allow']
            : ['key' => 'robots', 'level' => 'ok', 'title' => 'Search engines can list your website', 'text' => 'Families can find you on Google.'];

        if (trim((string) ($seo['og_image'] ?? '')) === '') {
            $checks[] = $hasDefaultImage
                ? ['key' => 'image', 'level' => 'info', 'title' => 'Using your website’s built-in sharing image', 'text' => 'Add your own to choose the picture people see when your site is shared.', 'fix' => 'image']
                : ['key' => 'image', 'level' => 'warning', 'title' => 'No sharing image', 'text' => 'Links shared on WhatsApp and Facebook will show without a picture.', 'fix' => 'image'];
        }

        $title = mb_strlen(trim((string) ($seo['meta_title'] ?? '')));
        $description = mb_strlen(trim((string) ($seo['meta_description'] ?? '')));
        $parts = [];
        if ($title === 0) {
            $parts[] = 'no title';
        } elseif ($title > self::TITLE_MAX) {
            $parts[] = "title {$title} characters (aim ≤ ".self::TITLE_MAX.')';
        }
        if ($description === 0) {
            $parts[] = 'no description';
        } elseif ($description < self::DESCRIPTION_MIN || $description > self::DESCRIPTION_MAX) {
            $parts[] = "description {$description} characters (aim ".self::DESCRIPTION_MIN.'–'.self::DESCRIPTION_MAX.')';
        }
        if ($parts) {
            $checks[] = ['key' => 'length', 'level' => 'warning', 'title' => 'Title and description length', 'text' => ucfirst(implode(' · ', $parts)).'.'];
        }

        if (self::verificationCode($seo['google_verification'] ?? '') === '') {
            $checks[] = ['key' => 'console', 'level' => 'info', 'title' => 'Google Search Console not connected', 'text' => 'Optional, but lets you see what people search to find you.'];
        }

        return $checks;
    }

    public static function issueCount(array $checks): int
    {
        return count(array_filter($checks, fn ($c) => in_array($c['level'], ['error', 'warning'], true)));
    }
}
