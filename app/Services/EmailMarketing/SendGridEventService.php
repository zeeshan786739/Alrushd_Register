<?php

namespace App\Services\EmailMarketing;

use App\Enums\EmailMarketing\ProviderStatus;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\ProviderEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendGridEventService
{
    public function __construct(private SuppressionService $suppressions)
    {
    }

    /**
     * @param  list<array<string, mixed>>  $events
     * @return array{processed:int,skipped:int}
     */
    public function ingest(array $events): array
    {
        $processed = 0;
        $skipped = 0;

        foreach ($events as $event) {
            if (! is_array($event)) {
                $skipped++;
                continue;
            }

            $result = $this->ingestOne($event);
            $result ? $processed++ : $skipped++;
        }

        return compact('processed', 'skipped');
    }

    /** @param  array<string, mixed>  $event */
    public function ingestOne(array $event): bool
    {
        $type = strtolower((string) ($event['event'] ?? ''));
        if ($type === '') {
            return false;
        }

        $providerEventId = $this->resolveProviderEventId($event);
        $correlation = $this->resolveCorrelationUuid($event);
        $providerMessageId = isset($event['sg_message_id']) ? (string) $event['sg_message_id'] : null;
        if ($providerMessageId && str_contains($providerMessageId, '.')) {
            $providerMessageId = explode('.', $providerMessageId)[0];
        }

        return DB::transaction(function () use ($event, $type, $providerEventId, $correlation, $providerMessageId) {
            if ($providerEventId && ProviderEvent::query()
                ->where('provider', 'sendgrid')
                ->where('provider_event_id', $providerEventId)
                ->exists()) {
                return false;
            }

            $message = $this->findMessage($correlation, $providerMessageId);
            $recipient = $this->findRecipient($correlation, $providerMessageId);

            // Never trust organization_id from provider payload.
            $organizationId = $message?->organization_id ?? $recipient?->organization_id;
            $occurredAt = isset($event['timestamp'])
                ? Carbon::createFromTimestamp((int) $event['timestamp'])
                : now();

            $meta = [
                'reason' => isset($event['reason']) ? Str::limit((string) $event['reason'], 200) : null,
                'status' => $event['status'] ?? null,
            ];
            // Store host only for clicks — avoid sensitive query strings.
            if ($type === 'click' && ! empty($event['url']) && is_string($event['url'])) {
                $host = parse_url($event['url'], PHP_URL_HOST);
                $meta['url_host'] = is_string($host) ? $host : null;
            }

            ProviderEvent::create([
                'organization_id' => $organizationId,
                'provider' => 'sendgrid',
                'provider_event_id' => $providerEventId,
                'event_type' => $type,
                'correlation_uuid' => $correlation,
                'provider_message_id' => $providerMessageId,
                'em_message_id' => $message?->id,
                'em_campaign_recipient_id' => $recipient?->id,
                'email' => isset($event['email']) ? strtolower((string) $event['email']) : null,
                'occurred_at' => $occurredAt,
                'meta' => $meta,
            ]);

            if ($message) {
                $this->applyToMessage($message, $type, $occurredAt);
            }
            if ($recipient) {
                $this->applyToRecipient($recipient, $type, $occurredAt);
            }

            if ($organizationId && isset($event['email'])) {
                $this->applySuppressionSideEffects(
                    (int) $organizationId,
                    (string) $event['email'],
                    $type,
                    isset($event['asm_group_id']) ? (int) $event['asm_group_id'] : null
                );
            }

            return true;
        });
    }

    /** @param  array<string, mixed>  $event */
    private function resolveProviderEventId(array $event): ?string
    {
        if (! empty($event['sg_event_id'])) {
            return (string) $event['sg_event_id'];
        }

        return hash('sha256', json_encode([
            $event['event'] ?? '',
            $event['email'] ?? '',
            $event['timestamp'] ?? '',
            $event['sg_message_id'] ?? '',
            $event['correlation_uuid'] ?? ($event['custom_args']['correlation_uuid'] ?? ''),
        ]));
    }

    /** @param  array<string, mixed>  $event */
    private function resolveCorrelationUuid(array $event): ?string
    {
        if (! empty($event['correlation_uuid']) && Str::isUuid((string) $event['correlation_uuid'])) {
            return (string) $event['correlation_uuid'];
        }

        $custom = $event['custom_args'] ?? null;
        if (is_array($custom) && ! empty($custom['correlation_uuid']) && Str::isUuid((string) $custom['correlation_uuid'])) {
            return (string) $custom['correlation_uuid'];
        }

        return null;
    }

    private function findMessage(?string $correlation, ?string $providerMessageId): ?Message
    {
        if ($correlation) {
            $byUuid = Message::query()->where('correlation_uuid', $correlation)->first();
            if ($byUuid) {
                return $byUuid;
            }
        }

        if ($providerMessageId) {
            return Message::query()->where('provider_message_id', $providerMessageId)->first();
        }

        return null;
    }

    private function findRecipient(?string $correlation, ?string $providerMessageId): ?CampaignRecipient
    {
        if ($correlation) {
            $byUuid = CampaignRecipient::query()->where('correlation_uuid', $correlation)->first();
            if ($byUuid) {
                return $byUuid;
            }
        }

        if ($providerMessageId) {
            return CampaignRecipient::query()->where('provider_message_id', $providerMessageId)->first();
        }

        return null;
    }

    private function applyToMessage(Message $message, string $type, Carbon $occurredAt): void
    {
        $updates = [];

        match ($type) {
            'processed' => $updates['provider_status'] = ProviderStatus::Processed->value,
            'delivered' => $updates = [
                'provider_status' => ProviderStatus::Delivered->value,
                'delivered_at' => $message->delivered_at ?: $occurredAt,
            ],
            'deferred' => $updates['provider_status'] = ProviderStatus::Deferred->value,
            'bounce' => $updates = [
                'provider_status' => ProviderStatus::Bounce->value,
                'bounced_at' => $message->bounced_at ?: $occurredAt,
            ],
            'dropped' => $updates['provider_status'] = ProviderStatus::Dropped->value,
            'open' => $updates = [
                'provider_status' => $message->provider_status === ProviderStatus::Click->value
                    ? ProviderStatus::Click->value
                    : ProviderStatus::Open->value,
                'opened_at' => $message->opened_at ?: $occurredAt,
            ],
            'click' => $updates = [
                'provider_status' => ProviderStatus::Click->value,
                'clicked_at' => $message->clicked_at ?: $occurredAt,
                'opened_at' => $message->opened_at ?: $occurredAt,
            ],
            'spamreport' => $updates['provider_status'] = ProviderStatus::SpamReport->value,
            'unsubscribe', 'group_unsubscribe' => $updates['provider_status'] = ProviderStatus::Unsubscribe->value,
            default => null,
        };

        if ($updates !== []) {
            $message->update($updates);
        }
    }

    private function applyToRecipient(CampaignRecipient $recipient, string $type, Carbon $occurredAt): void
    {
        $updates = [];
        $firstOpen = false;
        $firstClick = false;

        if ($type === 'processed') {
            $updates['provider_status'] = ProviderStatus::Processed->value;
        } elseif ($type === 'delivered') {
            $updates = [
                'provider_status' => ProviderStatus::Delivered->value,
                'delivered_at' => $recipient->delivered_at ?: $occurredAt,
            ];
        } elseif ($type === 'deferred') {
            $updates['provider_status'] = ProviderStatus::Deferred->value;
        } elseif ($type === 'bounce' || $type === 'dropped') {
            $updates = [
                'provider_status' => $type === 'bounce' ? ProviderStatus::Bounce->value : ProviderStatus::Dropped->value,
                'bounced_at' => $recipient->bounced_at ?: $occurredAt,
            ];
        } elseif ($type === 'open') {
            $firstOpen = ! $recipient->opened_at && ! $recipient->is_opened;
            $updates = [
                'provider_status' => ProviderStatus::Open->value,
                'opened_at' => $recipient->opened_at ?: $occurredAt,
                'is_opened' => true,
                'open_count' => (int) $recipient->open_count + 1,
            ];
        } elseif ($type === 'click') {
            $firstClick = ! $recipient->clicked_at && ! $recipient->is_clicked;
            $firstOpen = ! $recipient->opened_at && ! $recipient->is_opened;
            $updates = [
                'provider_status' => ProviderStatus::Click->value,
                'clicked_at' => $recipient->clicked_at ?: $occurredAt,
                'opened_at' => $recipient->opened_at ?: $occurredAt,
                'is_clicked' => true,
                'is_opened' => true,
                'click_count' => (int) $recipient->click_count + 1,
            ];
            if ($firstOpen) {
                $updates['open_count'] = (int) $recipient->open_count + 1;
            }
        } elseif ($type === 'spamreport') {
            $updates['provider_status'] = 'spamreport';
        } elseif (in_array($type, ['unsubscribe', 'group_unsubscribe'], true)) {
            $updates['provider_status'] = $type;
        }

        if ($updates !== []) {
            $recipient->update($updates);
            $this->bumpCampaignEngagement($recipient->campaign_id, $firstOpen, $firstClick);
        }
    }

    private function bumpCampaignEngagement(int $campaignId, bool $firstOpen, bool $firstClick): void
    {
        if (! $firstOpen && ! $firstClick) {
            return;
        }

        $campaign = Campaign::query()->find($campaignId);
        if (! $campaign) {
            return;
        }

        $payload = [];
        if ($firstOpen) {
            $payload['opened_count'] = (int) $campaign->opened_count + 1;
        }
        if ($firstClick) {
            $payload['clicked_count'] = (int) $campaign->clicked_count + 1;
        }
        if ($payload !== []) {
            $campaign->update($payload);
        }
    }

    private function applySuppressionSideEffects(
        int $organizationId,
        string $email,
        string $type,
        ?int $asmGroupId,
    ): void {
        if ($type === 'group_resubscribe') {
            $this->suppressions->clearMarketingUnsubscribe($organizationId, $email, $asmGroupId);

            return;
        }

        if (in_array($type, ['unsubscribe', 'group_unsubscribe'], true)) {
            $this->suppressions->record(
                $organizationId,
                $email,
                'sendgrid_'.$type,
                'sendgrid',
                'sendgrid',
                $asmGroupId,
                markUnsubscribed: true,
            );

            return;
        }

        if (in_array($type, ['bounce', 'dropped', 'spamreport'], true)) {
            $this->suppressions->record(
                $organizationId,
                $email,
                'sendgrid_'.$type,
                'sendgrid',
                'sendgrid',
                $asmGroupId,
                markUnsubscribed: false,
            );
        }
    }
}
