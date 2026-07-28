<?php

namespace App\Services\Integrations\Meta;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\MetaLeadSubmissionStatus;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\IntegrationFormMapping;
use App\Models\Integrations\MetaLeadSubmission;
use App\Services\Integrations\IntegrationFormProvisioner;
use Illuminate\Support\Facades\DB;

class MetaLeadSyncService
{
    public function __construct(
        private MetaLeadFieldMapper $fieldMapper,
        private IntegrationFormProvisioner $formProvisioner,
    ) {}

    public function processSubmission(MetaLeadSubmission $submission): MetaLeadSubmission
    {
        return DB::transaction(function () use ($submission) {
            $submission = MetaLeadSubmission::query()
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if ($submission->status === MetaLeadSubmissionStatus::Processed) {
                return $submission;
            }

            $connection = $submission->connection()->with('organization')->firstOrFail();
            $fields = $submission->field_data ?? [];

            $mapping = IntegrationFormMapping::query()
                ->where('organization_id', $submission->organization_id)
                ->where('external_form_id', $submission->meta_form_id)
                ->where('is_active', true)
                ->first();

            $form = $this->formProvisioner->ensureFacebookLeadForm($connection->organization);
            $formEntry = $submission->form_entry_id
                ? FormEntry::findOrFail($submission->form_entry_id)
                : $this->storeFormEntry($submission, $form, $fields, $mapping);

            if (! $submission->form_entry_id) {
                $submission->form_entry_id = $formEntry->id;
            }
            $submission->integration_form_mapping_id = $mapping?->id;

            if (! $mapping) {
                $submission->status = MetaLeadSubmissionStatus::Unmapped;
                $submission->processed_at = now();
                $submission->save();

                return $submission;
            }

            if (! $mapping->auto_create_lead) {
                $submission->status = MetaLeadSubmissionStatus::Processed;
                $submission->processed_at = now();
                $submission->save();

                return $submission;
            }

            $leadAttributes = $this->fieldMapper->mapToLeadAttributes(
                $fields,
                $mapping->field_mapping
            );

            $lead = Lead::updateOrCreate(
                [
                    'organization_id' => $submission->organization_id,
                    'form_entry_id' => $formEntry->id,
                ],
                [
                    'source' => 'facebook_lead_ads',
                    'lead_source' => $mapping->lead_source_label ?: $mapping->internal_label,
                    'title' => null,
                    'first_name' => $leadAttributes['first_name'] ?? 'Unknown',
                    'last_name' => $leadAttributes['last_name'] ?? null,
                    'email' => $leadAttributes['email'] ?? null,
                    'phone' => $leadAttributes['phone'] ?? null,
                    'company' => $leadAttributes['company'] ?? null,
                    'selected_school' => $leadAttributes['selected_school'] ?? null,
                    'city' => $leadAttributes['city'] ?? null,
                    'postal_code' => $leadAttributes['postal_code'] ?? null,
                    'lead_status' => 'new',
                    'priority' => $mapping->priority ?: 'medium',
                    'assigned_to' => $mapping->assigned_to,
                    'lead_description' => $this->buildLeadDescription($leadAttributes, $submission, $mapping),
                ]
            );

            if ($lead->wasRecentlyCreated) {
                $lead->activities()->create([
                    'organization_id' => $submission->organization_id,
                    'activity_type' => 'synced',
                    'description' => 'Lead imported from Facebook Lead Ads',
                    'metadata' => [
                        'meta_leadgen_id' => $submission->meta_leadgen_id,
                        'meta_form_id' => $submission->meta_form_id,
                        'meta_ad_id' => $submission->meta_ad_id,
                        'meta_campaign_id' => $submission->meta_campaign_id,
                        'form_mapping' => $mapping->internal_label,
                    ],
                ]);
            }

            $submission->lead_id = $lead->id;
            $submission->status = MetaLeadSubmissionStatus::Processed;
            $submission->processed_at = now();
            $submission->error_message = null;
            $submission->save();

            return $submission->fresh(['lead', 'formEntry', 'formMapping']);
        });
    }

    /** @param  array<string, string>  $fields */
    private function storeFormEntry(
        MetaLeadSubmission $submission,
        \App\Models\Form $form,
        array $fields,
        ?IntegrationFormMapping $mapping
    ): FormEntry {
        return FormEntry::create([
            'organization_id' => $submission->organization_id,
            'form_id' => $form->id,
            'legacy_source' => 'facebook_lead_ads',
            'legacy_record_id' => $submission->id,
            'data' => [
                'platform' => IntegrationPlatform::Facebook->value,
                'meta_leadgen_id' => $submission->meta_leadgen_id,
                'meta_form_id' => $submission->meta_form_id,
                'meta_ad_id' => $submission->meta_ad_id,
                'meta_campaign_id' => $submission->meta_campaign_id,
                'mapping_label' => $mapping?->internal_label,
                'fields' => $fields,
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    /** @param  array<string, mixed>  $leadAttributes */
    private function buildLeadDescription(
        array $leadAttributes,
        MetaLeadSubmission $submission,
        IntegrationFormMapping $mapping
    ): string {
        $parts = array_filter([
            'Facebook form: '.$mapping->internal_label,
            $submission->meta_ad_id ? 'Ad ID: '.$submission->meta_ad_id : null,
            $submission->meta_campaign_id ? 'Campaign ID: '.$submission->meta_campaign_id : null,
            $leadAttributes['lead_description'] ?? null,
        ]);

        return implode(' | ', $parts);
    }
}
