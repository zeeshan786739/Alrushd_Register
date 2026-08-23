<?php

namespace App\Support;

use App\Enums\EmailMarketing\RecipientStatus;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;

/**
 * Provider-backed campaign analytics snapshot.
 */
final class CampaignAnalyticsSummary
{
    public function __construct(
        public readonly int $selected,
        public readonly int $eligibleEstimate,
        public readonly int $processed,
        public readonly int $delivered,
        public readonly int $opened,
        public readonly int $clicked,
        public readonly int $deferred,
        public readonly int $bounced,
        public readonly int $dropped,
        public readonly int $unsubscribed,
        public readonly int $failed,
        public readonly int $skipped,
        public readonly int $queued,
        public readonly int $pending,
    ) {
    }

    public static function forCampaign(Campaign $campaign): self
    {
        $base = fn () => CampaignRecipient::query()->where('campaign_id', $campaign->id);

        $selected = $base()->count();
        $processed = $base()->where('status', RecipientStatus::Sent->value)->count();
        $failed = $base()->where('status', RecipientStatus::Failed->value)->count();
        $skipped = $base()->where('status', RecipientStatus::Skipped->value)->count();
        $queued = $base()->where('status', RecipientStatus::Queued->value)->count();
        $pending = $base()->where('status', RecipientStatus::Pending->value)->count();

        $delivered = $base()->whereNotNull('delivered_at')->count();
        $opened = $base()->where(function ($q) {
            $q->where('is_opened', true)->orWhereNotNull('opened_at');
        })->count();
        $clicked = $base()->where(function ($q) {
            $q->where('is_clicked', true)->orWhereNotNull('clicked_at');
        })->count();
        $bounced = $base()->where('provider_status', 'bounce')->count();
        $dropped = $base()->where('provider_status', 'dropped')->count();
        $deferred = $base()->where('provider_status', 'deferred')->count();
        $unsubscribed = $base()->whereIn('provider_status', ['unsubscribe', 'group_unsubscribe'])->count();

        return new self(
            selected: $selected ?: (int) $campaign->recipient_count,
            eligibleEstimate: max(0, ($selected ?: (int) $campaign->recipient_count) - $skipped),
            processed: $processed,
            delivered: $delivered,
            opened: $opened,
            clicked: $clicked,
            deferred: $deferred,
            bounced: $bounced,
            dropped: $dropped,
            unsubscribed: $unsubscribed,
            failed: $failed,
            skipped: $skipped,
            queued: $queued,
            pending: $pending,
        );
    }

    public function rate(int $numerator, int $denominator): ?float
    {
        if ($denominator <= 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    public function deliveryRate(): ?float
    {
        return $this->rate($this->delivered, $this->processed);
    }

    public function openRate(): ?float
    {
        $denom = $this->delivered > 0 ? $this->delivered : $this->processed;

        return $this->rate($this->opened, $denom);
    }

    public function clickRate(): ?float
    {
        $denom = $this->delivered > 0 ? $this->delivered : $this->processed;

        return $this->rate($this->clicked, $denom);
    }

    public function bounceRate(): ?float
    {
        return $this->rate($this->bounced + $this->dropped, $this->processed);
    }
}
