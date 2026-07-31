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

        $connection = $this->resolveConnection($pageId);

        if (! $connection) {
            Log::warning('No connected Facebook integration for page', ['page_id' => $pageId]);

            return;
        }

        $connection->update([
            'last_webhook_at' => now(),
            'webhook_subscribed_at' => $connection->webhook_subscribed_at ?? now(),
        ]);

        $submission = MetaLeadSubmission::firstOrCreate(
            ['meta_leadgen_id' => $leadgenId],
            [
                'organization_id' => $connection->organization_id,
                'integration_connection_id' => $connection->id,
                'meta_form_id' => isset($value['form_id']) ? (string) $value['form_id'] : null,
                'meta_ad_id' => isset($value['ad_id']) ? (string) $value['ad_id'] : null,
                'meta_campaign_id' => isset($value['campaign_id']) ? (string) $value['campaign_id'] : null,
                'meta_page_id' => $connection->external_account_id,
                'raw_payload' => $value,
                'status' => MetaLeadSubmissionStatus::Pending,
            ]
        );

        if (! $submission->wasRecentlyCreated && $submission->status === MetaLeadSubmissionStatus::Processed) {
            return;
        }

        ProcessMetaLeadJob::dispatch($submission->id);
    }

    private function resolveConnection(string $pageId): ?IntegrationConnection
    {
        $connection = IntegrationConnection::query()
            ->where('platform', IntegrationPlatform::Facebook)
            ->where('external_account_id', $pageId)
            ->where('status', IntegrationConnectionStatus::Connected)
            ->first();

        if ($connection) {
            return $connection;
        }

        // Meta dashboard "Send to server" uses sample page IDs (e.g. 444444444444)
        // that do not match a real Page. If only one school is connected, accept it.
        $connected = IntegrationConnection::query()
            ->where('platform', IntegrationPlatform::Facebook)
            ->where('status', IntegrationConnectionStatus::Connected)
            ->whereNotNull('external_account_id')
            ->get();

        if ($connected->count() === 1) {
            Log::info('Meta leadgen webhook used sole connected integration for unmatched page_id', [
                'webhook_page_id' => $pageId,
                'resolved_page_id' => $connected->first()->external_account_id,
            ]);

            return $connected->first();
        }

        return null;
    }
}
