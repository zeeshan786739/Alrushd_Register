<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadImportRowStatus;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadImport;
use App\Models\Crm\LeadImportRow;

class LeadImportDuplicateDetector
{
    public function __construct(private LeadImportValidator $validator) {}

    /**
     * @param  array<int, array{row_number: int, email:?string, phone:?string, row_hash: string}>  $rows
     * @return array<int, array{reason: string, status: string}>
     */
    public function detect(LeadImport $import, array $rows): array
    {
        $seenEmails = [];
        $seenPhones = [];
        $flags = [];

        $existing = $this->existingKeys($import->organization_id);
        $previousHashes = LeadImportRow::query()
            ->where('organization_id', $import->organization_id)
            ->where('lead_import_id', '!=', $import->id)
            ->whereNotNull('lead_id')
            ->pluck('row_hash')
            ->all();
        $previousHashSet = array_fill_keys($previousHashes, true);

        foreach ($rows as $index => $row) {
            $email = $this->validator->comparisonEmail($row['email'] ?? null);
            $phone = $this->validator->comparisonPhone($row['phone'] ?? null);
            $reasons = [];

            if ($email && isset($seenEmails[$email])) {
                $reasons[] = 'Duplicate email in this file (row '.$seenEmails[$email].')';
            }
            if ($phone && isset($seenPhones[$phone])) {
                $reasons[] = 'Duplicate phone in this file (row '.$seenPhones[$phone].')';
            }
            if ($email && isset($existing['emails'][$email])) {
                $reasons[] = 'Email already exists in this organization';
            }
            if ($phone && isset($existing['phones'][$phone])) {
                $reasons[] = 'Phone already exists in this organization';
            }
            if (isset($previousHashSet[$row['row_hash']])) {
                $reasons[] = 'This row was imported previously from another file/batch';
            }

            if ($email) {
                $seenEmails[$email] = $row['row_number'];
            }
            if ($phone) {
                $seenPhones[$phone] = $row['row_number'];
            }

            if ($reasons !== []) {
                $flags[$index] = [
                    'reason' => implode('; ', $reasons),
                    'status' => LeadImportRowStatus::Duplicate->value,
                ];
            }
        }

        return $flags;
    }

    /**
     * @return array{emails: array<string, int>, phones: array<string, int>}
     */
    private function existingKeys(int $organizationId): array
    {
        $emails = [];
        $phones = [];
        Lead::query()
            ->where('organization_id', $organizationId)
            ->select(['id', 'email', 'phone'])
            ->orderBy('id')
            ->chunk(500, function ($leads) use (&$emails, &$phones) {
                foreach ($leads as $lead) {
                    $email = $this->validator->comparisonEmail($lead->email);
                    $phone = $this->validator->comparisonPhone($lead->phone);
                    if ($email && ! isset($emails[$email])) {
                        $emails[$email] = $lead->id;
                    }
                    if ($phone && ! isset($phones[$phone])) {
                        $phones[$phone] = $lead->id;
                    }
                }
            });

        return ['emails' => $emails, 'phones' => $phones];
    }
}
