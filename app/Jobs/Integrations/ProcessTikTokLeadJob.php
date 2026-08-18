<?php

namespace App\Jobs\Integrations;

use App\Enums\TikTokLeadSubmissionStatus;
use App\Models\Integrations\TikTokLeadSubmission;
use App\Services\Integrations\TikTok\TikTokLeadFieldMapper;
use App\Services\Integrations\TikTok\TikTokLeadSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTikTokLeadJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public function __construct(public int $tiktokLeadSubmissionId) {}

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    public function handle(
        TikTokLeadFieldMapper $fieldMapper,
        TikTokLeadSyncService $syncService,
    ): void {
        $submission = TikTokLeadSubmission::query()
            ->with('connection')
            ->find($this->tiktokLeadSubmissionId);

        if (! $submission) {
            return;
        }

        if ($submission->status === TikTokLeadSubmissionStatus::Processed) {
            return;
        }

        $connection = $submission->connection;
        if (! $connection?->isConnected()) {
            $submission->update([
                'status' => TikTokLeadSubmissionStatus::Failed,
                'error_message' => 'TikTok connection is no longer available for this organization.',
            ]);

            return;
        }

        if (
            (int) $connection->organization_id !== (int) $submission->organization_id
            || ! hash_equals((string) $connection->external_account_id, (string) $submission->advertiser_id)
        ) {
            $submission->update([
                'status' => TikTokLeadSubmissionStatus::Failed,
                'error_message' => 'TikTok connection no longer matches this lead.',
            ]);

            return;
        }

        $fields = $fieldMapper->normalizeFieldData($submission->field_data ?? []);
        $syncService->processSubmission($submission, $fields);
    }

    public function failed(\Throwable $exception): void
    {
        $submission = TikTokLeadSubmission::query()->find($this->tiktokLeadSubmissionId);
        if (! $submission || $submission->status === TikTokLeadSubmissionStatus::Processed) {
            return;
        }

        Log::warning('TikTok lead processing failed.', [
            'tiktok_lead_submission_id' => $submission->id,
            'organization_id' => $submission->organization_id,
        ]);

        $submission->update([
            'status' => TikTokLeadSubmissionStatus::Failed,
            'error_message' => $submission->error_message ?: 'TikTok lead could not be processed. Please try again.',
        ]);
    }
}
