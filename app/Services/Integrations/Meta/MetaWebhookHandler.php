<?php

namespace App\Services\Integrations\Meta;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\MetaLeadSubmissionStatus;
use App\Jobs\Integrations\ProcessMetaLeadJob;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\MetaLeadSubmission;
use Illuminate\Support\Facades\Log;

class MetaWebhookHandler
{
    public function __construct(
        private MetaGraphClient $graphClient,
    ) {}

    /** @param  array<string, mixed>  $payload */
    public function handle(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? null) !== 'leadgen') {
                    continue;
                }

                $value = $change['value'] ?? [];
                $this->queueLeadgenEvent($value);
            }
        }
    }

    /** @param  array<string, mixed>  $value */
    private function queueLeadgenEvent(array $value): void
    {
        $leadgenId = (string) ($value['leadgen_id'] ?? '');
        $pageId = (string) ($value['page_id'] ?? '');

        if ($leadgenId === '' || $pageId === '') {
            Log::warning('Meta leadgen webhook missing leadgen_id or page_id', $value);

            return;
        }

        $connection = IntegrationConnection::query()
            ->where('platform', IntegrationPlatform::Facebook)
            ->where('external_account_id', $pageId)
            ->where('status', IntegrationConnectionStatus::Connected)
            ->first();

        if (! $connection) {
            Log::warning('No connected Facebook integration for page', ['page_id' => $pageId]);

            return;
        }

        $connection->update(['last_webhook_at' => now()]);

        $submission = MetaLeadSubmission::firstOrCreate(
            ['meta_leadgen_id' => $leadgenId],
            [
                'organization_id' => $connection->organization_id,
                'integration_connection_id' => $connection->id,
                'meta_form_id' => isset($value['form_id']) ? (string) $value['form_id'] : null,
                'meta_ad_id' => isset($value['ad_id']) ? (string) $value['ad_id'] : null,
                'meta_campaign_id' => isset($value['campaign_id']) ? (string) $value['campaign_id'] : null,
                'meta_page_id' => $pageId,
                'raw_payload' => $value,
                'status' => MetaLeadSubmissionStatus::Pending,
            ]
        );

        if (! $submission->wasRecentlyCreated && $submission->status === MetaLeadSubmissionStatus::Processed) {
            return;
        }

        ProcessMetaLeadJob::dispatch($submission->id);
    }
}
