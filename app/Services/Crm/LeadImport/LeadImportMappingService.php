<?php

namespace App\Services\Crm\LeadImport;

use App\Support\LeadImportFields;

class LeadImportMappingService
{
    public function __construct(private LeadImportHeaderNormalizer $normalizer) {}

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     * @param  array<string, array<int, string>>  $sampleValues
     * @return array<string, array{field: string, confidence: string, reason: string}>
     */
    public function suggest(array $headers, array $sampleValues = []): array
    {
        $used = [];
        $suggestions = [];

        foreach ($headers as $header) {
            $match = $this->matchHeader($header['label'], $sampleValues[$header['key']] ?? []);
            if ($match['field'] !== LeadImportFields::CUSTOM && in_array($match['field'], $used, true)) {
                $match = [
                    'field' => LeadImportFields::CUSTOM,
                    'confidence' => 'low',
                    'reason' => 'Another column is already mapped to '.$match['field'],
                ];
            }
            if ($match['confidence'] === 'low' && $match['field'] !== LeadImportFields::CUSTOM) {
                $match['field'] = LeadImportFields::CUSTOM;
                $match['reason'] = ($match['reason'] ?? 'Low confidence').' — left as custom until confirmed';
            }
            if (in_array($match['field'], LeadImportFields::uniqueTargets(), true)) {
                $used[] = $match['field'];
            }
            $suggestions[$header['key']] = $match;
        }

        return $suggestions;
    }

    /**
     * @param  array<string, string>  $mapping  column key => field
     * @return array<string, string>
     */
    public function sanitize(array $mapping): array
    {
        $allowed = array_keys(LeadImportFields::options());
        $used = [];
        $clean = [];

        foreach ($mapping as $key => $field) {
            $field = is_string($field) ? $field : LeadImportFields::CUSTOM;
            if (! in_array($field, $allowed, true)) {
                $field = LeadImportFields::CUSTOM;
            }
            if (in_array($field, LeadImportFields::uniqueTargets(), true)) {
                if (in_array($field, $used, true)) {
                    $field = LeadImportFields::CUSTOM;
                } else {
                    $used[] = $field;
                }
            }
            $clean[(string) $key] = $field;
        }

        return $clean;
    }

    /**
     * @param  array<int, string>  $samples
     * @return array{field: string, confidence: string, reason: string}
     */
    private function matchHeader(string $label, array $samples): array
    {
        $normalized = $this->normalizer->normalize($label);

        foreach ($this->synonyms() as $field => $terms) {
            if (in_array($normalized, $terms, true)) {
                return [
                    'field' => $field,
                    'confidence' => 'high',
                    'reason' => 'Exact header synonym',
                ];
            }
        }

        foreach ($this->synonyms() as $field => $terms) {
            foreach ($terms as $term) {
                if (strlen($term) >= 6 && (str_contains($normalized, $term) || str_contains($term, $normalized))) {
                    return [
                        'field' => $field,
                        'confidence' => 'medium',
                        'reason' => 'Partial header match',
                    ];
                }
            }
        }

        $heuristic = $this->sampleHeuristic($samples);
        if ($heuristic) {
            return $heuristic;
        }

        return [
            'field' => LeadImportFields::CUSTOM,
            'confidence' => 'low',
            'reason' => 'No confident match',
        ];
    }

    /**
     * @param  array<int, string>  $samples
     * @return array{field: string, confidence: string, reason: string}|null
     */
    private function sampleHeuristic(array $samples): ?array
    {
        $emailHits = 0;
        $phoneHits = 0;
        foreach ($samples as $sample) {
            $sample = trim($sample);
            if (filter_var($sample, FILTER_VALIDATE_EMAIL)) {
                $emailHits++;
            }
            if (preg_match('/^\+?[\d\s().-]{8,20}$/', $sample)) {
                $phoneHits++;
            }
        }

        if ($emailHits >= 1 && $emailHits >= $phoneHits) {
            return ['field' => 'email', 'confidence' => 'medium', 'reason' => 'Email-shaped sample values'];
        }
        if ($phoneHits >= 2) {
            return ['field' => 'phone', 'confidence' => 'medium', 'reason' => 'Phone-shaped sample values'];
        }

        return null;
    }

    /** @return array<string, array<int, string>> */
    private function synonyms(): array
    {
        return [
            'full_name' => ['full_name', 'name', 'student_name', 'lead_name', 'parent_name', 'parent_lead_name', 'parent_lead_name', 'contact_name', 'customer_name'],
            'first_name' => ['first_name', 'firstname', 'given_name', 'fname'],
            'last_name' => ['last_name', 'lastname', 'surname', 'lname', 'family_name'],
            'phone' => ['phone', 'phone_number', 'mobile', 'mobile_number', 'telephone', 'contact_no', 'contact_number', 'whatsapp', 'whatsapp_number', 'tel'],
            'email' => ['email', 'email_address', 'contact_email', 'e_mail'],
            'company' => ['company', 'company_name', 'business', 'business_name', 'organisation', 'organization'],
            'city' => ['city', 'town'],
            'province' => ['province', 'state', 'county'],
            'postal_code' => ['postal_code', 'postcode', 'zip', 'zip_code'],
            'address' => ['address', 'street', 'street_address'],
            'advertising_platform' => ['platform', 'channel', 'lead_channel', 'ad_platform'],
            'campaign_name' => ['campaign', 'campaign_name', 'ad_campaign'],
            'adset_name' => ['ad_set', 'adset', 'ad_set_name', 'adset_name'],
            'ad_name' => ['source_ad', 'ad', 'ad_name', 'creative', 'creative_name'],
            'form_name' => ['form_name', 'form', 'lead_form'],
            'source_submitted_at' => ['created_at', 'date', 'submitted_at', 'lead_date', 'timestamp'],
            'source_time' => ['time'],
            'lead_status' => ['lead_status', 'status'],
            'assigned_to_name' => ['agent', 'agent_name', 'owner', 'assigned_to', 'assignee', 'assigned_team_member', 'assigned_team_members', 'team_member'],
            'assigned_to_email' => ['agent_email', 'owner_email', 'assignee_email'],
            'notes' => ['notes', 'note', 'comments', 'comment'],
            'follow_up_date' => ['follow_up', 'followup', 'follow_up_date', 'next_follow_up'],
            'follow_up_time' => ['follow_up_time', 'followup_time'],
        ];
    }
}
