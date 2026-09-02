<?php

namespace App\Support;

/**
 * Curated Lead Category UI options (Iconify icons + tone tokens).
 * Internal identifiers only — never shown as raw technical fields to end users.
 */
final class LeadCategoryUi
{
    public const DEFAULT_ICON = 'solar:folder-with-files-linear';

    public const DEFAULT_TONE = 'info';

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function icons(): array
    {
        return [
            'solar:folder-with-files-linear' => ['label' => 'Category', 'icon' => 'solar:folder-with-files-linear'],
            'solar:square-academic-cap-linear' => ['label' => 'Education', 'icon' => 'solar:square-academic-cap-linear'],
            'solar:users-group-rounded-linear' => ['label' => 'People', 'icon' => 'solar:users-group-rounded-linear'],
            'solar:case-round-linear' => ['label' => 'Staff', 'icon' => 'solar:case-round-linear'],
            'solar:user-id-linear' => ['label' => 'Admin', 'icon' => 'solar:user-id-linear'],
            'solar:buildings-2-linear' => ['label' => 'Campus', 'icon' => 'solar:buildings-2-linear'],
            'solar:document-text-linear' => ['label' => 'Forms', 'icon' => 'solar:document-text-linear'],
            'solar:letter-linear' => ['label' => 'Campaign', 'icon' => 'solar:letter-linear'],
            'solar:target-linear' => ['label' => 'Target', 'icon' => 'solar:target-linear'],
            'solar:phone-linear' => ['label' => 'Contact', 'icon' => 'solar:phone-linear'],
            'solar:star-linear' => ['label' => 'Featured', 'icon' => 'solar:star-linear'],
            'solar:widget-2-linear' => ['label' => 'General', 'icon' => 'solar:widget-2-linear'],
        ];
    }

    /**
     * User-facing color labels mapped to existing CRM tone tokens.
     *
     * @return array<string, array{label: string, tone: string}>
     */
    public static function colors(): array
    {
        return [
            'info' => ['label' => 'Blue', 'tone' => 'info'],
            'success' => ['label' => 'Green', 'tone' => 'success'],
            'warning' => ['label' => 'Amber', 'tone' => 'warning'],
            'indigo' => ['label' => 'Purple', 'tone' => 'indigo'],
            'danger' => ['label' => 'Red', 'tone' => 'danger'],
            'neutral' => ['label' => 'Gray', 'tone' => 'neutral'],
        ];
    }

    /** @return list<string> */
    public static function iconIds(): array
    {
        return array_keys(self::icons());
    }

    /** @return list<string> */
    public static function toneIds(): array
    {
        return array_keys(self::colors());
    }

    public static function sanitizeIcon(?string $icon): string
    {
        $icon = trim((string) $icon);
        if ($icon !== '' && isset(self::icons()[$icon])) {
            return $icon;
        }

        // Preserve previously stored Iconify ids that are still usable.
        if ($icon !== '' && str_contains($icon, ':') && strlen($icon) <= 80) {
            return $icon;
        }

        return self::DEFAULT_ICON;
    }

    public static function sanitizeTone(?string $tone): string
    {
        $tone = trim((string) $tone);

        return isset(self::colors()[$tone]) || in_array($tone, ['caution'], true)
            ? $tone
            : self::DEFAULT_TONE;
    }
}
