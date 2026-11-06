<?php

namespace App\Support;

use App\Models\Crm\Lead;

final class LeadAdmissionTags
{
    /**
     * Compact tags for board cards from imported spreadsheet fields.
     *
     * @return array<int, array{label: string, short?: string, value?: string, icon: string, tone: string, title: string}>
     */
    public static function forLead(Lead $lead, int $limit = 8): array
    {
        $custom = is_array($lead->custom_data) ? $lead->custom_data : [];
        $tags = [];

        $years = $lead->childYearGroups() ?: array_filter([self::text($custom['Year'] ?? null)]);
        foreach (array_count_values($years) as $year => $children) {
            $label = $children > 1 ? $year.' ×'.$children : (string) $year;
            $tags[] = [
                'label' => $label,
                'short' => $label,
                'icon' => 'solar:book-linear',
                'tone' => 'info',
                'title' => 'Year group',
            ];
        }

        if ($intake = self::text($lead->admission_intake)) {
            $tags[] = [
                'label' => $intake,
                'short' => $intake,
                'icon' => 'solar:calendar-linear',
                'tone' => 'warning',
                'title' => 'Admission intake',
            ];
        }

        if ($method = self::text($lead->contact_method) ?? self::text($custom['Lead Status'] ?? null)) {
            $tags[] = [
                'label' => $method,
                'short' => $method,
                'icon' => 'solar:chat-round-dots-linear',
                'tone' => 'indigo',
                'title' => 'Contact method',
            ];
        }

        foreach ([
            'Application Form' => ['short' => 'App', 'icon' => 'solar:document-add-linear'],
            'Direct Debit form' => ['short' => 'DD', 'icon' => 'solar:card-linear'],
            'Admission Fee' => ['short' => 'Fee', 'icon' => 'solar:wallet-linear'],
        ] as $label => $meta) {
            if (! array_key_exists($label, $custom)) {
                continue;
            }
            $yes = self::isTruthy($custom[$label]);
            $tags[] = [
                'label' => $label,
                'short' => $meta['short'],
                'value' => $yes ? 'Yes' : 'No',
                'icon' => $meta['icon'],
                'tone' => $yes ? 'success' : 'neutral',
                'title' => $label,
            ];
        }

        if (! $lead->admission_intake && ($current = self::text($custom['Current status'] ?? null))) {
            $tags[] = [
                'label' => $current,
                'short' => $current,
                'icon' => 'solar:calendar-linear',
                'tone' => 'warning',
                'title' => 'Target intake',
            ];
        }

        return array_slice($tags, 0, $limit);
    }

    private static function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
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
