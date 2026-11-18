<?php

namespace App\Support;

/**
 * Website CMS → Social media: which links belong to which network, when an icon may appear
 * on the website, and a gentle check for misspelled usernames.
 *
 * Mirrored by the CMS editor (website-cms-social.js) and, for linkProblem()/isShown(), by the
 * website (Website/src/lib/social.ts), which hides any icon that fails these rules.
 */
class SocialProfiles
{
    /** Display order in the CMS; `domains` match the host or any subdomain of it. */
    public const NETWORKS = [
        'facebook' => ['name' => 'Facebook', 'domains' => ['facebook.com', 'fb.com']],
        'instagram' => ['name' => 'Instagram', 'domains' => ['instagram.com']],
        'linkedin' => ['name' => 'LinkedIn', 'domains' => ['linkedin.com']],
        'twitter' => ['name' => 'X (Twitter)', 'domains' => ['twitter.com', 'x.com']],
        'youtube' => ['name' => 'YouTube', 'domains' => ['youtube.com', 'youtu.be']],
        'tiktok' => ['name' => 'TikTok', 'domains' => ['tiktok.com']],
        'whatsapp' => ['name' => 'WhatsApp', 'domains' => ['wa.me', 'whatsapp.com']],
        'threads' => ['name' => 'Threads', 'domains' => ['threads.net', 'threads.com']],
    ];

    /** null = fine (or empty); 'format' = not a full https link; 'domain' = another site's link. */
    public static function linkProblem(string $network, ?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! preg_match('#^https?://#i', $url) || ! is_string($host) || $host === '') {
            return 'format';
        }
        $host = strtolower($host);
        foreach (self::NETWORKS[$network]['domains'] ?? [] as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return null;
            }
        }

        return 'domain';
    }

    /** An icon appears only when it is switched on and has a valid link for its network. */
    public static function isShown(string $network, ?string $url, bool $enabled): bool
    {
        return $enabled && trim((string) $url) !== '' && self::linkProblem($network, $url) === null;
    }

    /**
     * The profile's username, e.g. "alrushd" from https://www.tiktok.com/@alrushd or
     * https://www.linkedin.com/company/alrushd/. null when the link has no username
     * (WhatsApp numbers, YouTube channel ids, invalid links).
     */
    public static function handle(string $network, ?string $url): ?string
    {
        if ($network === 'whatsapp' || trim((string) $url) === '' || self::linkProblem($network, $url) !== null) {
            return null;
        }
        $segments = array_values(array_filter(explode('/', (string) parse_url(trim((string) $url), PHP_URL_PATH))));
        if (! $segments || in_array(strtolower($segments[0]), ['channel', 'watch', 'share', 'p', 'reel', 'profile.php'], true)) {
            return null;
        }
        if (in_array(strtolower($segments[0]), ['company', 'school', 'in', 'c', 'user'], true)) {
            $segments = array_slice($segments, 1);
        }
        $handle = strtolower(ltrim((string) ($segments[0] ?? ''), '@'));

        return strlen($handle) >= 3 ? $handle : null;
    }

    /**
     * Possible typos: a username that is 1–2 edits away from another profile's username which
     * more of the school's links use (a swapped pair of letters counts as one edit).
     *
     * @param  array<string, string|null>  $links  network => url
     * @return array<string, array{handle: string, suggestion: string}>
     */
    public static function typoWarnings(array $links): array
    {
        $handles = [];
        foreach ($links as $network => $url) {
            if ($h = self::handle($network, $url)) {
                $handles[$network] = $h;
            }
        }
        // How many profiles use a handle: the same username, or one that contains it
        // ("alrushd" is in "alrushd.independentschool" and "al_rushd_academy").
        $squash = fn (string $h) => preg_replace('/[^a-z0-9]/', '', $h);
        $support = fn (string $h) => count(array_filter($handles, fn ($o) => str_contains($squash($o), $squash($h))));

        $warnings = [];
        foreach ($handles as $network => $h) {
            $best = null;
            foreach (array_unique($handles) as $other) {
                if ($other === $h || strlen($other) < 4) {
                    continue;
                }
                $distance = self::editDistance($squash($h), $squash($other));
                if ($distance >= 1 && $distance <= 2 && $support($other) > $support($h)
                    && ($best === null || $support($other) > $support($best))) {
                    $best = $other;
                }
            }
            if ($best !== null) {
                $warnings[$network] = ['handle' => $h, 'suggestion' => $best];
            }
        }

        return $warnings;
    }

    /** Optimal string alignment distance: Levenshtein where swapping two neighbours costs 1. */
    public static function editDistance(string $a, string $b): int
    {
        $la = strlen($a);
        $lb = strlen($b);
        $d = [];
        for ($i = 0; $i <= $la; $i++) {
            $d[$i][0] = $i;
        }
        for ($j = 0; $j <= $lb; $j++) {
            $d[0][$j] = $j;
        }
        for ($i = 1; $i <= $la; $i++) {
            for ($j = 1; $j <= $lb; $j++) {
                $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;
                $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $cost);
                if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                    $d[$i][$j] = min($d[$i][$j], $d[$i - 2][$j - 2] + 1);
                }
            }
        }

        return $d[$la][$lb];
    }
}
