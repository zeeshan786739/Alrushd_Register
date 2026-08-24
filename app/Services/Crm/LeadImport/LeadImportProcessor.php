<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadImportRowStatus;
use App\Enums\LeadImportStatus;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadImport;
use App\Models\Crm\LeadImportRow;
use App\Models\Crm\LeadNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class LeadImportProcessor
{
    public function process(LeadImport $import): LeadImport
    {
        $import->update([
            'status' => LeadImportStatus::Processing->value,
            'started_at' => $import->started_at ?? now(),
        ]);

        $behavior = $import->option('duplicate_behavior', 'skip');
        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $duplicates = 0;

        $import->rows()->orderBy('row_number')->chunkById(50, function ($rows) use ($import, $behavior, &$imported, &$skipped, &$failed, &$duplicates) {
            foreach ($rows as $row) {
                if ($row->lead_id) {
                    $imported++;
                    continue;
                }

                try {
                    $result = DB::transaction(function () use ($import, $row, $behavior) {
                        return $this->processRow($import, $row, $behavior);
                    });
                    match ($result) {
                        'imported' => $imported++,
                        'skipped' => $skipped++,
                        'duplicate' => $duplicates++,
                        default => $failed++,
                    };
                } catch (Throwable $e) {
                    $failed++;
                    $row->update([
                        'status' => LeadImportRowStatus::Failed->value,
                        'errors' => array_values(array_filter(array_merge($row->errors ?? [], ['This row could not be imported.']))),
                    ]);
                    report($e);
                }
            }
        });

        $import->update([
            'status' => LeadImportStatus::Completed->value,
            'imported_rows' => $imported,
            'skipped_rows' => $skipped,
            'duplicate_rows' => $duplicates + $skipped,
            'failed_rows' => $failed,
            'completed_at' => now(),
        ]);

        return $import->fresh();
    }

    private function processRow(LeadImport $import, LeadImportRow $row, string $behavior): string
    {
        if ($row->status === LeadImportRowStatus::Invalid->value) {
            $row->update(['status' => LeadImportRowStatus::Failed->value]);

            return 'failed';
        }

        $isDuplicate = $row->status === LeadImportRowStatus::Duplicate->value;
        if ($isDuplicate && $behavior !== 'create') {
            $row->update(['status' => LeadImportRowStatus::Skipped->value]);

            return 'duplicate';
        }

        $normalized = $row->normalized_data ?? [];
        $fields = $normalized['fields'] ?? [];
        $custom = $normalized['custom'] ?? [];
        $options = $import->import_options ?? [];

        $assignedTo = $this->resolveAssignee($import, $fields, $options, $row);
        $followUpDate = $this->carbonOrNull($fields['follow_up_date'] ?? null);
        $followUpTime = is_string($fields['follow_up_time'] ?? null) ? $fields['follow_up_time'] : null;
        $sourceSubmittedAt = $this->carbonOrNull($fields['source_submitted_at'] ?? null);

        $attributes = [
            'organization_id' => $import->organization_id,
            'source' => 'file_import',
            'lead_source' => $options['source_label'] ?: $import->original_filename,
            'first_name' => $fields['first_name'] ?? 'Unknown',
            'last_name' => $fields['last_name'] ?? null,
            'email' => $this->usableEmail($fields['email'] ?? null),
            'phone' => isset($fields['phone']) ? mb_substr((string) $fields['phone'], 0, 30) : null,
            'company' => $fields['company'] ?? null,
            'city' => $fields['city'] ?? null,
            'province' => $fields['province'] ?? null,
            'postal_code' => $fields['postal_code'] ?? null,
            'address' => $fields['address'] ?? null,
            'selected_school' => $fields['selected_school'] ?? null,
            'lead_description' => $fields['lead_description'] ?? null,
            'lead_status' => $this->safeStatus($fields['lead_status'] ?? null, $options),
            'priority' => $this->safePriority($options['default_priority'] ?? LeadPriority::Medium->value),
            'assigned_to' => $assignedTo,
            'next_follow_up_date' => $followUpDate,
            'next_follow_up_time' => $followUpTime,
            'advertising_platform' => $fields['advertising_platform'] ?? null,
            'campaign_name' => $fields['campaign_name'] ?? null,
            'adset_name' => $fields['adset_name'] ?? null,
            'ad_name' => $fields['ad_name'] ?? null,
            'form_name' => $fields['form_name'] ?? null,
            'source_submitted_at' => $sourceSubmittedAt,
            'custom_data' => $custom ?: null,
            'lead_import_id' => $import->id,
            'created_by' => $import->uploaded_by,
        ];

        if (\App\Support\LeadCategorySchema::ready()) {
            $attributes['lead_category_id'] = $import->lead_category_id;
        }

        $lead = Lead::create($attributes);

        if (! empty($fields['notes'])) {
            LeadNote::create([
                'organization_id' => $import->organization_id,
                'lead_id' => $lead->id,
                'admin_id' => $import->uploaded_by,
                'note' => (string) $fields['notes'],
            ]);
            $lead->logActivity('note_added', 'Note imported from spreadsheet');
        }

        $lead->logActivity('imported', 'Lead imported from '.$import->original_filename, [
            'lead_import_id' => $import->id,
            'row_number' => $row->row_number,
        ]);

        $row->update([
            'status' => LeadImportRowStatus::Imported->value,
            'lead_id' => $lead->id,
            'imported_at' => now(),
        ]);

        return 'imported';
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $options
     */
    private function resolveAssignee(LeadImport $import, array $fields, array $options, LeadImportRow $row): ?int
    {
        $default = $options['default_assigned_to'] ?? null;
        $email = trim((string) ($fields['assigned_to_email'] ?? ''));
        $name = trim((string) ($fields['assigned_to_name'] ?? ''));

        $query = Admin::query()->where('organization_id', $import->organization_id);

        if ($email !== '') {
            $match = (clone $query)->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
            if ($match) {
                return $match->id;
            }
        }

        if ($name !== '') {
            $match = (clone $query)->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
            if ($match) {
                return $match->id;
            }
            $warnings = $row->warnings ?? [];
            $warnings[] = 'Agent "'.$name.'" could not be matched in this organization; lead left unassigned';
            $row->warnings = $warnings;
            $row->save();
        }

        if ($default) {
            $exists = Admin::query()
                ->where('organization_id', $import->organization_id)
                ->whereKey($default)
                ->exists();
            if ($exists) {
                return (int) $default;
            }
        }

        return null;
    }

    private function usableEmail(mixed $email): ?string
    {
        $email = trim((string) $email);
        if ($email === '' || ! filter_var(mb_strtolower($email), FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function safeStatus(mixed $status, array $options): string
    {
        $value = (string) ($status ?: ($options['default_status'] ?? LeadStatus::New->value));

        return LeadStatus::tryFrom($value)?->value ?? LeadStatus::New->value;
    }

    private function safePriority(mixed $priority): string
    {
        return LeadPriority::tryFrom((string) $priority)?->value ?? LeadPriority::Medium->value;
    }

    private function carbonOrNull(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }
}
