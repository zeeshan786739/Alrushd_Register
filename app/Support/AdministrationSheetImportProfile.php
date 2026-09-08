<?php

namespace App\Support;

use App\Services\Crm\LeadImport\LeadImportHeaderNormalizer;

final class AdministrationSheetImportProfile
{
    public function __construct(private LeadImportHeaderNormalizer $normalizer) {}

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     */
    public function matches(array $headers): bool
    {
        $labels = array_map(fn (array $header): string => $this->normalizer->normalize($header['label']), $headers);

        return in_array('contact_no', $labels, true)
            && in_array('student_name', $labels, true)
            && in_array('success_unsuccessful', $labels, true);
    }

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     * @return array<string, string>
     */
    public function mapping(array $headers): array
    {
        if (! $this->matches($headers)) {
            return [];
        }

        $mapping = [];
        foreach ($headers as $header) {
            $normalized = $this->normalizer->normalize($header['label']);
            $mapping[$header['key']] = match ($normalized) {
                'assigned_team_member' => 'assigned_to_name',
                'contact_no' => 'phone',
                'email_address' => 'email',
                'student_name' => 'full_name',
                'lead_date' => 'source_submitted_at',
                'lead_channel', 'g_ads_other' => 'advertising_platform',
                'success_unsuccessful' => 'lead_status',
                'comment', 'comments' => 'notes',
                default => LeadImportFields::CUSTOM,
            };
        }

        return $mapping;
    }

    /** @return array<string, mixed> */
    public function options(): array
    {
        return [
            'source_label' => 'Administration Sheet',
            'default_calling_code' => '44',
            'date_format' => 'd/m/Y',
            'duplicate_behavior' => 'skip',
        ];
    }
}
