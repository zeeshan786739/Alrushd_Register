<?php

namespace App\Support;

/**
 * Website CMS → Analytics: the four tools' ID formats, and which tools the website may load.
 *
 *   google_analytics    G-ABC123DEF4      (Google Analytics 4 measurement ID)
 *   google_tag_manager  GTM-ABC1234       (Tag Manager container ID)
 *   facebook_pixel      15–16 digits      (Meta Pixel ID)
 *   microsoft_clarity   8–12 letters/digits (Clarity project ID)
 *   require_consent     true by default: tools load only after a visitor accepts the cookie banner
 *
 * The website mirrors normalise() / toolsToLoad() in Website/src/lib/analytics.ts, and the CMS
 * editor mirrors the formats in website-cms-analytics.js.
 */
class AnalyticsSettings
{
    public const TOOLS = [
        'google_analytics' => ['name' => 'Google Analytics 4', 'pattern' => '/^G-[A-Z0-9]{6,12}$/', 'case' => 'upper', 'example' => 'G-ABC123DEF4'],
        'google_tag_manager' => ['name' => 'Google Tag Manager', 'pattern' => '/^GTM-[A-Z0-9]{5,9}$/', 'case' => 'upper', 'example' => 'GTM-ABC1234'],
        'facebook_pixel' => ['name' => 'Meta Pixel', 'pattern' => '/^\d{15,16}$/', 'case' => null, 'example' => '123456789012345'],
        'microsoft_clarity' => ['name' => 'Microsoft Clarity', 'pattern' => '/^[a-z0-9]{8,12}$/', 'case' => 'lower', 'example' => 'abcd1234ef'],
    ];

    /** Remove spaces and fix the letter case, so a pasted " g-abc123def4 " still works. */
    public static function normalise(string $tool, ?string $value): string
    {
        $value = preg_replace('/\s+/', '', (string) $value) ?? '';

        return match (self::TOOLS[$tool]['case'] ?? null) {
            'upper' => strtoupper($value),
            'lower' => strtolower($value),
            default => $value,
        };
    }

    /** Empty is fine (not connected); otherwise the ID must match its tool's format. */
    public static function isValid(string $tool, ?string $value): bool
    {
        $value = self::normalise($tool, $value);

        return $value === '' || (bool) preg_match(self::TOOLS[$tool]['pattern'], $value);
    }

    /** @return list<string> tools with a valid ID */
    public static function connectedTools(array $analytics): array
    {
        return array_values(array_filter(
            array_keys(self::TOOLS),
            fn ($tool) => self::normalise($tool, $analytics[$tool] ?? '') !== '' && self::isValid($tool, $analytics[$tool] ?? ''),
        ));
    }

    public static function requiresConsent(array $analytics): bool
    {
        return filter_var($analytics['require_consent'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    /** The cookie banner only appears when it has something to ask about. */
    public static function showsBanner(array $analytics): bool
    {
        return self::requiresConsent($analytics) && self::connectedTools($analytics) !== [];
    }

    /**
     * Which tools a page may load for a visitor.
     *
     * @param  'accepted'|'rejected'|null  $choice  the visitor's banner choice (null = not chosen yet)
     * @return list<string>
     */
    public static function toolsToLoad(array $analytics, ?string $choice): array
    {
        $tools = self::connectedTools($analytics);
        if (! self::requiresConsent($analytics)) {
            return $tools;
        }

        return $choice === 'accepted' ? $tools : [];
    }
}
