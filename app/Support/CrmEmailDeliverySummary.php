<?php

namespace App\Support;

use App\Enums\EmailMarketing\ProviderStatus;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\ProviderEvent;
use Illuminate\Support\Collection;

/**
 * Compact delivery status snapshot for CRM show pages.
 */
final class CrmEmailDeliverySummary
{
    /**
     * @param  Collection<int, ProviderEvent>  $events
     */
    public function __construct(
        public readonly Message $message,
        public readonly Collection $events,
    ) {
    }

    public static function forMessage(Message $message): self
    {
        $events = ProviderEvent::query()
            ->where('em_message_id', $message->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        return new self($message, $events);
    }

    /** @return list<self> */
    public static function latestForLead(int $organizationId, int $leadId, int $limit = 5): array
    {
        return Message::query()
            ->where('organization_id', $organizationId)
            ->where('lead_id', $leadId)
            ->where('folder', 'sent')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Message $m) => self::forMessage($m))
            ->all();
    }

    /** @return list<self> */
    public static function latestForCustomer(int $organizationId, int $customerId, int $limit = 5): array
    {
        return Message::query()
            ->where('organization_id', $organizationId)
            ->where('customer_id', $customerId)
            ->where('folder', 'sent')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Message $m) => self::forMessage($m))
            ->all();
    }

    public static function latestForQuotation(int $organizationId, int $quotationId): ?self
    {
        $message = Message::query()
            ->where('organization_id', $organizationId)
            ->where('quotation_id', $quotationId)
            ->where('folder', 'sent')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->first();

        return $message ? self::forMessage($message) : null;
    }

    public static function latestForInvoice(int $organizationId, int $invoiceId): ?self
    {
        $message = Message::query()
            ->where('organization_id', $organizationId)
            ->where('invoice_id', $invoiceId)
            ->where('folder', 'sent')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->first();

        return $message ? self::forMessage($message) : null;
    }

    public function statusLabel(): string
    {
        $status = $this->message->provider_status ?: $this->message->delivery_status ?: 'pending';

        return ProviderStatus::tryFrom($status)?->label()
            ?? str_replace('_', ' ', ucfirst((string) $status));
    }

    public function statusTone(): string
    {
        $status = $this->message->provider_status ?: $this->message->delivery_status;

        return ProviderStatus::tryFrom((string) $status)?->tone() ?? 'neutral';
    }

    /** @return list<array{label:string,at:?string}> */
    public function timeline(): array
    {
        $rows = [];
        foreach ($this->events as $event) {
            $type = ProviderStatus::tryFrom((string) $event->event_type);
            $rows[] = [
                'label' => $type?->label() ?? ucfirst(str_replace('_', ' ', (string) $event->event_type)),
                'at' => optional($event->occurred_at)->format('M j, Y g:i A'),
            ];
        }

        if ($rows === [] && $this->message->sent_at) {
            $rows[] = [
                'label' => $this->statusLabel(),
                'at' => $this->message->sent_at->format('M j, Y g:i A'),
            ];
        }

        return $rows;
    }
}
