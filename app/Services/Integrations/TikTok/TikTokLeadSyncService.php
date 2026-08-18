<?php

namespace App\Services\Integrations\TikTok;

use App\Enums\TikTokLeadSubmissionStatus;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Models\Integrations\TikTokFormMapping;
use App\Models\Integrations\TikTokLeadSubmission;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class TikTokLeadSyncService
{
    public function __construct(
        private TikTokLeadFieldMapper $fieldMapper,
        private TikTokFormProvisioner $formProvisioner,
    ) {}

    public function processSubmission(TikTokLeadSubmission $submission, array $fields): TikTokLeadSubmission
    {
        return DB::transaction(function () use ($submission, $fields) {
            $submission = TikTokLeadSubmission::query()
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if ($submission->status === TikTokLeadSubmissionStatus::Processed) {
                return $submission;
            }

            $connection = $submission->connection()->with('organization')->first();
            if (! $connection?->isConnected() || (int) $connection->organization_id !== (int) $submission->organization_id) {
                $submission->status = TikTokLeadSubmissionStatus::Failed;
                $submission->error_message = 'TikTok connection is no longer available for this organization.';
                $submission->save();

                return $submission;
            }

            $submission->field_data = $fields;
            $submission->error_message = null;

            $mapping = $this->resolveMapping($submission);
            $submission->tiktok_form_mapping_id = $mapping?->id;

            $form = $this->formProvisioner->ensureIntakeForm($connection->organization);
            $formEntry = $this->ensureFormEntry($submission, $form, $fields, $mapping);
            $submission->form_entry_id = $formEntry->id;

            if (! $mapping) {
                $submission->status = TikTokLeadSubmissionStatus::Unmapped;
                $submission->processed_at = now();
                $submission->save();

                return $submission;
            }

            if (! $mapping->is_active) {
                $submission->status = TikTokLeadSubmissionStatus::Ignored;
                $submission->processed_at = now();
                $submission->save();

                return $submission;
            }

            if (! $mapping->auto_create_lead) {
                $submission->status = TikTokLeadSubmissionStatus::Processed;
                $submission->processed_at = now();
                $submission->save();

                return $submission;
            }

            $lead = $this->ensureLead($submission, $formEntry, $mapping, $fields);
            $submission->lead_id = $lead->id;
            $submission->status = TikTokLeadSubmissionStatus::Processed;
            $submission->processed_at = now();
            $submission->save();

            return $submission->fresh(['lead', 'formEntry', 'formMapping']);
        });
    }

    private function resolveMapping(TikTokLeadSubmission $submission): ?TikTokFormMapping
    {
        $pageId = (string) $submission->tiktok_page_id;
        if ($pageId === '') {
            return null;
        }

        return TikTokFormMapping::query()
            ->where('organization_id', $submission->organization_id)
            ->where('integration_connection_id', $submission->integration_connection_id)
            ->where('advertiser_id', $submission->advertiser_id)
            ->where('external_form_id', $pageId)
            ->first();
    }

    private function ensureFormEntry(
        TikTokLeadSubmission $submission,
        \App\Models\Form $form,
        array $fields,
        ?TikTokFormMapping $mapping
    ): FormEntry {
        if ($submission->form_entry_id) {
            $existing = FormEntry::query()
                ->where('id', $submission->form_entry_id)
                ->where('organization_id', $submission->organization_id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $existing = FormEntry::query()
            ->where('organization_id', $submission->organization_id)
            ->where('legacy_source', 'tiktok_lead_ads')
            ->where('legacy_record_id', $submission->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return FormEntry::query()->create([
            'organization_id' => $submission->organization_id,
            'form_id' => $form->id,
            'legacy_source' => 'tiktok_lead_ads',
            'legacy_record_id' => $submission->id,
            'data' => [
                'platform' => 'tiktok',
                'tiktok_lead_id' => $submission->tiktok_lead_id,
                'tiktok_page_id' => $submission->tiktok_page_id,
                'advertiser_id' => $submission->advertiser_id,
                'form_name' => $mapping?->external_form_name,
                'lead_source_label' => $mapping?->lead_source_label,
                'fields' => $fields,
            ],
            'status' => 'pending',
            'submitted_at' => $submission->received_at ?? now(),
        ]);
    }

    private function ensureLead(
        TikTokLeadSubmission $submission,
        FormEntry $formEntry,
        TikTokFormMapping $mapping,
        array $fields
    ): Lead {
        if ($submission->lead_id) {
            $existing = Lead::query()
                ->where('id', $submission->lead_id)
                ->where('organization_id', $submission->organization_id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $existing = Lead::query()
            ->where('organization_id', $submission->organization_id)
            ->where('form_entry_id', $formEntry->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $attributes = $this->fieldMapper->mapToLeadAttributes($fields, $mapping->field_mapping);
        $firstName = trim((string) ($attributes['first_name'] ?? ''));
        if ($firstName === '') {
            $firstName = 'Unknown';
        }

        try {
            $lead = Lead::query()->create([
                'organization_id' => $submission->organization_id,
                'form_entry_id' => $formEntry->id,
                'source' => 'tiktok_lead_ads',
                'lead_source' => $mapping->lead_source_label ?: ('TikTok — '.($mapping->external_form_name ?: 'Instant Form')),
                'first_name' => $firstName,
                'last_name' => $this->nullableString($attributes['last_name'] ?? null),
                'email' => $this->nullableString($attributes['email'] ?? null),
                'phone' => $this->nullableString($attributes['phone'] ?? null),
                'company' => $this->nullableString($attributes['company'] ?? null),
                'city' => $this->nullableString($attributes['city'] ?? null),
                'province' => $this->nullableString($attributes['province'] ?? null),
                'postal_code' => $this->nullableString($attributes['postal_code'] ?? null),
                'selected_school' => $this->nullableString($attributes['selected_school'] ?? null),
                'lead_status' => 'new',
                'priority' => $mapping->priority?->value ?? 'medium',
                'assigned_to' => $mapping->assigned_to,
                'lead_description' => $this->leadDescription($attributes, $mapping, $submission),
            ]);
        } catch (UniqueConstraintViolationException) {
            $existing = Lead::query()
                ->where('organization_id', $submission->organization_id)
                ->where('form_entry_id', $formEntry->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            throw new \RuntimeException('This TikTok lead could not be saved as a CRM lead.');
        }

        $lead->activities()->create([
            'organization_id' => $submission->organization_id,
            'activity_type' => 'synced',
            'description' => 'Lead imported from TikTok Instant Form',
            'metadata' => [
                'tiktok_lead_id' => $submission->tiktok_lead_id,
                'tiktok_page_id' => $submission->tiktok_page_id,
                'form_name' => $mapping->external_form_name,
            ],
        ]);

        return $lead;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function leadDescription(array $attributes, TikTokFormMapping $mapping, TikTokLeadSubmission $submission): string
    {
        $parts = array_filter([
            'TikTok form: '.($mapping->external_form_name ?: $submission->tiktok_page_id),
            $attributes['lead_description'] ?? null,
        ]);

        return implode("\n", $parts);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value) || is_bool($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
