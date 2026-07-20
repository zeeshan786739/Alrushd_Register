<?php

namespace App\Services\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\EmailMarketing\RecipientStatus;
use App\Jobs\EmailMarketing\SendCampaignRecipientJob;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignDispatchService
{
    public function __construct(
        private CampaignRecipientResolver $resolver,
        private MailConfigResolver $mailConfig,
    ) {
    }

    public function snapshotRecipients(Campaign $campaign): int
    {
        $filters = $campaign->recipient_filters ?? [];
        $options = array_merge($filters, ['source' => $campaign->recipient_source]);
        $rows = $this->resolver->resolve($campaign->organization_id, $options);

        DB::transaction(function () use ($campaign, $rows) {
            $campaign->recipients()->delete();

            foreach ($rows as $row) {
                CampaignRecipient::create([
                    'organization_id' => $campaign->organization_id,
                    'campaign_id' => $campaign->id,
                    'email' => $row['email'],
                    'name' => $row['name'],
                    'lead_id' => $row['lead_id'],
                    'customer_id' => $row['customer_id'],
                    'form_entry_id' => $row['form_entry_id'],
                    'status' => RecipientStatus::Pending->value,
                    'tracking_token' => Str::random(48),
                ]);
            }

            $campaign->update(['recipient_count' => $rows->count()]);
        });

        return $rows->count();
    }

    public function dispatch(Campaign $campaign): void
    {
        if (! in_array($campaign->status, [CampaignStatus::Draft->value, CampaignStatus::Scheduled->value], true)) {
            throw new \RuntimeException('Campaign cannot be dispatched from status '.$campaign->status);
        }

        $this->mailConfig->resolveOrFail($campaign->organization_id);

        if ($campaign->recipients()->count() === 0) {
            $this->snapshotRecipients($campaign);
        }

        $campaign->update([
            'status' => CampaignStatus::Sending->value,
            'started_at' => now(),
        ]);

        $campaign->recipients()
            ->where('status', RecipientStatus::Pending->value)
            ->orderBy('id')
            ->chunkById(50, function ($recipients) {
                foreach ($recipients as $recipient) {
                    $recipient->update(['status' => RecipientStatus::Queued->value]);
                    SendCampaignRecipientJob::dispatch($recipient->id);
                }
            });
    }

    public function dispatchDueScheduled(): int
    {
        $campaigns = Campaign::query()
            ->where('status', CampaignStatus::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            $this->dispatch($campaign);
        }

        return $campaigns->count();
    }
}
