<?php

namespace App\Jobs\Integrations;

use App\Enums\MetaLeadSubmissionStatus;
use App\Models\Integrations\MetaLeadSubmission;
use App\Services\Integrations\Meta\MetaGraphClient;
use App\Services\Integrations\Meta\MetaLeadFieldMapper;
use App\Services\Integrations\Meta\MetaLeadSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessMetaLeadJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $metaLeadSubmissionId) {}

    public function handle(
        MetaGraphClient $graphClient,
        MetaLeadFieldMapper $fieldMapper,
        MetaLeadSyncService $syncService,
    ): void {
        $submission = MetaLeadSubmission::query()
            ->with('connection')
            ->find($this->metaLeadSubmissionId);

        if (! $submission || ! $submission->connection) {
            return;
        }

        if ($submission->status === MetaLeadSubmissionStatus::Processed) {
            return;
        }

        $connection = $submission->connection;

        if (! $connection->access_token) {
            $submission->update([
                'status' => MetaLeadSubmissionStatus::Failed,
                'error_message' => 'Facebook page access token is missing.',
            ]);

            return;
        }

        try {
            $leadPayload = $graphClient->fetchLead($submission->meta_leadgen_id, $connection->access_token);
            $fieldData = $fieldMapper->normalizeFieldData($leadPayload['field_data'] ?? []);

            $submission->update([
                'field_data' => $fieldData,
                'meta_form_id' => $submission->meta_form_id ?: (isset($leadPayload['form_id']) ? (string) $leadPayload['form_id'] : null),
                'meta_ad_id' => $submission->meta_ad_id ?: (isset($leadPayload['ad_id']) ? (string) $leadPayload['ad_id'] : null),
                'meta_campaign_id' => $submission->meta_campaign_id ?: (isset($leadPayload['campaign_id']) ? (string) $leadPayload['campaign_id'] : null),
                'raw_payload' => array_merge($submission->raw_payload ?? [], ['lead' => $leadPayload]),
            ]);

            $syncService->processSubmission($submission->fresh());
        } catch (\Throwable $exception) {
            Log::error('Failed to process Meta lead submission', [
                'meta_lead_submission_id' => $submission->id,
                'meta_leadgen_id' => $submission->meta_leadgen_id,
                'message' => $exception->getMessage(),
            ]);

            $submission->update([
                'status' => MetaLeadSubmissionStatus::Failed,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
