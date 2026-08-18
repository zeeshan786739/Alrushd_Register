<?php

namespace App\Services\Integrations\TikTok;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Enums\TikTokLeadSubmissionStatus;
use App\Jobs\Integrations\ProcessTikTokLeadJob;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\TikTokLeadSubmission;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TikTokWebhookHandler
{
    public function __construct(
        private TikTokLeadFieldMapper $fieldMapper,
    ) {}

    /**
     * Persist Instant Form lead events and dispatch processing jobs.
     * Official webhook `entry[].id` is the Lead ID; `changes[]` contains submitted answers.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $object = $payload['object'] ?? null;
        if ($object !== null && (int) $object !== 1) {
            return;
        }

        $entries = $payload['entry'] ?? null;
        if (! is_array($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $this->ingestEntry($entry, $payload);
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $payload
     */
    private function ingestEntry(array $entry, array $payload): void
    {
        $leadId = $this->stringValue($entry['id'] ?? null);
        $advertiserId = $this->stringValue($entry['advertiser_id'] ?? null);
        $pageId = $this->stringValue($entry['page_id'] ?? null);
        $leadSource = strtoupper($this->stringValue($entry['lead_source'] ?? 'INSTANT_FORM'));

        if ($leadId === '' || $advertiserId === '') {
            return;
        }

        if ($leadSource !== '' && $leadSource !== 'INSTANT_FORM') {
            return;
        }

        $connection = $this->resolveConnection($advertiserId);
        if ($connection === null) {
            Log::info('TikTok lead webhook ignored: advertiser could not be resolved uniquely.', [
                'advertiser_id' => $advertiserId,
            ]);

            return;
        }

        $fieldData = $this->fieldMapper->normalizeFieldData($entry['changes'] ?? []);
        $meta = $this->safeMeta($entry, $payload);

        try {
            $result = DB::transaction(function () use ($connection, $advertiserId, $leadId, $pageId, $fieldData, $meta) {
                $existing = TikTokLeadSubmission::query()
                    ->where('advertiser_id', $advertiserId)
                    ->where('tiktok_lead_id', $leadId)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return ['submission' => $existing, 'dispatch' => false];
                }

                $submission = TikTokLeadSubmission::query()->create([
                    'organization_id' => $connection->organization_id,
                    'integration_connection_id' => $connection->id,
                    'advertiser_id' => $advertiserId,
                    'tiktok_lead_id' => $leadId,
                    'tiktok_page_id' => $pageId !== '' ? $pageId : null,
                    'status' => TikTokLeadSubmissionStatus::Pending,
                    'webhook_meta' => $meta,
                    'field_data' => $fieldData,
                    'received_at' => now(),
                ]);

                $connection->update(['last_webhook_at' => now()]);

                return ['submission' => $submission, 'dispatch' => true];
            });
        } catch (UniqueConstraintViolationException) {
            return;
        }

        if (($result['dispatch'] ?? false) === true && isset($result['submission'])) {
            try {
                ProcessTikTokLeadJob::dispatch($result['submission']->id);
            } catch (\Throwable) {
                // The event is persisted; TikTok must receive 200 regardless of queue processing.
            }
        }
    }

    private function resolveConnection(string $advertiserId): ?IntegrationConnection
    {
        $matches = IntegrationConnection::query()
            ->where('platform', IntegrationPlatform::TikTok)
            ->where('status', IntegrationConnectionStatus::Connected)
            ->where('external_account_id', $advertiserId)
            ->whereNotNull('access_token')
            ->get();

        if ($matches->count() !== 1) {
            return null;
        }

        $connection = $matches->first();

        return $connection?->isConnected() ? $connection : null;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function safeMeta(array $entry, array $payload): array
    {
        return [
            'request_id' => $this->stringValue($payload['request_id'] ?? null),
            'object' => $payload['object'] ?? null,
            'time' => $payload['time'] ?? null,
            'page_name' => $this->stringValue($entry['page_name'] ?? null),
            'campaign_id' => $this->stringValue($entry['campaign_id'] ?? null),
            'campaign_name' => $this->stringValue($entry['campaign_name'] ?? null),
            'adgroup_id' => $this->stringValue($entry['adgroup_id'] ?? null),
            'ad_id' => $this->stringValue($entry['ad_id'] ?? null),
            'create_time' => $entry['create_time'] ?? null,
            'lead_source' => $this->stringValue($entry['lead_source'] ?? 'INSTANT_FORM'),
        ];
    }

    private function stringValue(mixed $value): string
    {
        if (! is_scalar($value) || is_bool($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
