<?php

namespace App\Support;

use App\Models\Crm\Lead;

final class LeadImportSummary
{
    /** @var list<string> */
    private const HIGHLIGHT_KEYS = [
        'Year',
        'Lead Status',
        'Current status',
        'Lead Channel',
        'G-Ads/Other',
        'Success/Unsuccessful',
    ];

    /** @var list<string> */
    private const SIDEBAR_KEYS = [
        'Student Name',
        'Assigned team member',
        'Column 1',
        'Lead Date',
        'Success/Unsuccessful',
        'Lead ID',
    ];

    /** @var list<string> */
    private const CHECKLIST_KEYS = [
        'Application Form' => ['label' => 'Application', 'short' => 'App', 'icon' => 'solar:document-add-linear'],
        'Direct Debit form' => ['label' => 'Direct debit', 'short' => 'DD', 'icon' => 'solar:card-linear'],
        'Admission Fee' => ['label' => 'Admission fee', 'short' => 'Fee', 'icon' => 'solar:wallet-linear'],
    ];

    /** @return array<string, mixed>|null */
    public static function forLead(Lead $lead): ?array
    {
        $custom = is_array($lead->custom_data) ? $lead->custom_data : [];
        $groups = ImportedLeadDetails::forLead($lead);
        $flat = self::flattenGroups($groups);

        if ($custom === [] && $groups === [] && ! $lead->leadImport) {
            return null;
        }

        return [
            'filename' => $lead->leadImport?->original_filename,
            'highlights' => self::highlights($custom, $flat),
            'checklist' => self::checklist($custom, $flat),
            'sidebar' => self::sidebar($custom, $flat),
            'extra_groups' => self::extraGroups($groups, $custom, $flat),
            'extra_count' => self::extraCount($groups, $custom, $flat),
        ];
    }

    /** @param  array<string, array<int, array{label: string, value: string}>>  $groups
     * @return array<string, string>
     */
    private static function flattenGroups(array $groups): array
    {
        $flat = [];
        foreach ($groups as $items) {
            foreach ($items as $item) {
                $flat[$item['label']] = $item['value'];
            }
        }

        return $flat;
    }

    /** @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     */
    private static function valueFrom(array $custom, array $flat, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $custom)) {
                $value = self::displayValue($custom[$key]);
                if ($value !== null) {
                    return $value;
                }
            }
            if (isset($flat[$key])) {
                $value = self::displayValue($flat[$key]);
                if ($value !== null && ! self::isEmptyDisplay($value)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     * @return list<array{label: string, value: string, tone: string}>
     */
    private static function highlights(array $custom, array $flat): array
    {
        $items = [];

        if ($year = self::valueFrom($custom, $flat, 'Year')) {
            $items[] = ['label' => 'Year', 'value' => $year, 'tone' => 'info'];
        }

        if ($method = self::valueFrom($custom, $flat, 'Lead Status')) {
            $items[] = ['label' => 'Contact', 'value' => $method, 'tone' => 'indigo'];
        }

        if ($current = self::valueFrom($custom, $flat, 'Current status')) {
            $items[] = ['label' => 'Target', 'value' => $current, 'tone' => 'warning'];
        }

        if ($channel = self::valueFrom($custom, $flat, 'Lead Channel', 'G-Ads/Other')) {
            $items[] = ['label' => 'Channel', 'value' => $channel, 'tone' => 'info'];
        }

        if ($outcome = self::valueFrom($custom, $flat, 'Success/Unsuccessful')) {
            $items[] = [
                'label' => 'Outcome',
                'value' => $outcome,
                'tone' => self::outcomeTone($outcome),
            ];
        }

        return array_slice($items, 0, 4);
    }

    /** @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     * @return list<array{label: string, short: string, icon: string, done: bool}>
     */
    private static function checklist(array $custom, array $flat): array
    {
        $items = [];

        foreach (self::CHECKLIST_KEYS as $key => $meta) {
            $raw = $custom[$key] ?? $flat[$key] ?? null;
            if ($raw === null && ! array_key_exists($key, $custom) && ! array_key_exists($key, $flat)) {
                continue;
            }
            $items[] = [
                'label' => $meta['label'],
                'short' => $meta['short'],
                'icon' => $meta['icon'],
                'done' => self::isTruthy($raw),
            ];
        }

        return $items;
    }

    /** @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     * @return list<array{label: string, value: string, tone: string}>
     */
    private static function sidebar(array $custom, array $flat): array
    {
        $items = [];
        $map = [
            'Student Name' => ['keys' => ['Student Name'], 'label' => 'Student', 'tone' => 'purple'],
            'Assigned team member' => ['keys' => ['Assigned team member', 'Column 1'], 'label' => 'Sheet assignee', 'tone' => 'neutral'],
            'Lead Date' => ['keys' => ['Lead Date'], 'label' => 'Lead date', 'tone' => 'neutral'],
            'Lead ID' => ['keys' => ['Lead ID'], 'label' => 'Sheet ID', 'tone' => 'neutral'],
        ];

        foreach ($map as $meta) {
            $value = self::valueFrom($custom, $flat, ...$meta['keys']);
            if ($value === null) {
                continue;
            }
            $items[] = [
                'label' => $meta['label'],
                'value' => $value,
                'tone' => $meta['tone'],
            ];
        }

        return $items;
    }

    /** @param  array<string, array<int, array{label: string, value: string}>>  $groups
     * @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     * @return array<string, array<int, array{label: string, value: string, tone: string}>>
     */
    private static function extraGroups(array $groups, array $custom, array $flat): array
    {
        $skip = array_merge(
            self::HIGHLIGHT_KEYS,
            self::SIDEBAR_KEYS,
            array_keys(self::CHECKLIST_KEYS),
            ['Contact No', 'Email Address', 'Email', 'Phone', 'Student Name'],
        );

        $extra = [];

        foreach ($groups as $groupName => $items) {
            $filtered = [];
            foreach ($items as $item) {
                if (in_array($item['label'], $skip, true)) {
                    continue;
                }
                if (self::isEmptyDisplay($item['value'])) {
                    continue;
                }
                $filtered[] = [
                    'label' => $item['label'],
                    'value' => $item['value'],
                    'tone' => self::valueTone($item['label'], $item['value']),
                ];
            }
            if ($filtered !== []) {
                $extra[$groupName] = $filtered;
            }
        }

        return $extra;
    }

    /** @param  array<string, array<int, array{label: string, value: string}>>  $groups
     * @param  array<string, mixed>  $custom
     * @param  array<string, string>  $flat
     */
    private static function extraCount(array $groups, array $custom, array $flat): int
    {
        return array_sum(array_map('count', self::extraGroups($groups, $custom, $flat)));
    }

    private static function valueTone(string $label, string $value): string
    {
        if (in_array(mb_strtolower($value), ['yes', 'no', 'true', 'false'], true)) {
            return self::isTruthy($value) ? 'success' : 'neutral';
        }

        if (str_contains(mb_strtolower($label), 'success') || str_contains(mb_strtolower($label), 'unsuccessful')) {
            return self::outcomeTone($value);
        }

        return 'neutral';
    }

    private static function outcomeTone(string $value): string
    {
        $text = mb_strtolower(trim($value));

        return match (true) {
            in_array($text, ['yes', 'successful', 'success', 'won'], true) => 'success',
            in_array($text, ['no', 'unsuccessful', 'lost'], true) => 'danger',
            in_array($text, ['pending', 'calendly'], true) => 'warning',
            default => 'neutral',
        };
    }

    private static function isEmptyDisplay(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return in_array(trim((string) $value), ['Not provided', '—', '-'], true);
    }

    private static function displayValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    private static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $text = mb_strtolower(trim((string) $value));

        return in_array($text, ['yes', 'true', '1', 'y', 'done', 'complete', 'completed'], true);
    }
}
