<?php

namespace App\Support;

use App\Models\Crm\Lead;
use App\Services\Crm\LeadImport\LeadImportHeaderNormalizer;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportedLeadDetails
{
    public static function forLead(Lead $lead): array
    {
        $import = $lead->leadImport;
        $row = $import && (int) $import->organization_id === (int) $lead->organization_id
            ? $import->rows()->where('organization_id', $lead->organization_id)->where('lead_id', $lead->id)->first()
            : null;

        return self::group($import?->detected_headers ?? [], $row?->raw_data ?? [], $lead->custom_data ?? []);
    }

    /** Keep original labels, empty cells, multiline text, and duplicate headers. */
    public static function group(array $headers, array $raw, array $custom = []): array
    {
        $groups = [];
        $seen = [];
        $normalizer = new LeadImportHeaderNormalizer;
        $items = [];
        foreach ($headers as $header) {
            if (array_key_exists($header['key'], $raw)) {
                $items[] = ['label' => $header['label'], 'value' => $raw[$header['key']]];
                $seen[$header['label']] = true;
            }
        }
        // Legacy imports may not have header metadata. Never drop their raw cells.
        $knownKeys = array_column($headers, 'key');
        foreach ($raw as $key => $value) {
            if (!in_array($key, $knownKeys, true)) {
                $items[] = ['label' => (string) $key, 'value' => $value];
                $seen[$key] = true;
            }
        }
        foreach ($custom as $label => $value) {
            if (!isset($seen[$label])) $items[] = compact('label', 'value');
        }
        foreach ($items as $item) {
            $key = $normalizer->normalize($item['label']);
            $group = match (true) {
                in_array($key, ['assigned_team_member', 'column_1', 'contact_no', 'email_address', 'student_name', 'year']) => 'Contact & student',
                in_array($key, ['current_status', 'application_form', 'direct_debit_form', 'admission_fee', 'success_unsuccessful']) => 'Admission progress',
                in_array($key, ['lead_id', 'lead_date', 'lead_channel', 'lead_status']) => 'Original lead information',
                in_array($key, ['comment', 'comments', 'mode_of_contact', 'date_of_contact', 'followup_details', 'follow_up_details', 'notes']) => 'Contact history & notes',
                default => 'Additional information',
            };
            $value = $item['value'];
            if (in_array($key, ['lead_date', 'date_of_contact'], true) && is_numeric($value) && $value > 20000 && $value < 80000) {
                $value = Date::excelToDateTimeObject((float) $value)->format('d M Y H:i');
            }
            $groups[$group][] = ['label' => $item['label'], 'value' => $value === null || $value === '' ? 'Not provided' : (is_bool($value) ? ($value ? 'Yes' : 'No') : (is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE)))];
        }
        return $groups;
    }
}
