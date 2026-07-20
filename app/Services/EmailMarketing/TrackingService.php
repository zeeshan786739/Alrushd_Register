<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\Suppression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingService
{
    public function recordOpen(string $token): ?CampaignRecipient
    {
        $recipient = CampaignRecipient::where('tracking_token', $token)->first();
        if (! $recipient) {
            return null;
        }

        DB::transaction(function () use ($recipient) {
            $wasOpened = $recipient->is_opened;
            $recipient->update([
                'is_opened' => true,
                'open_count' => $recipient->open_count + 1,
                'opened_at' => $recipient->opened_at ?? now(),
            ]);

            if (! $wasOpened) {
                $recipient->campaign()->increment('opened_count');
            }
        });

        return $recipient->fresh();
    }

    public function recordClick(string $token, string $destination): ?CampaignRecipient
    {
        if (! $this->isSafeRedirect($destination)) {
            abort(400, 'Invalid redirect URL.');
        }

        $recipient = CampaignRecipient::where('tracking_token', $token)->first();
        if (! $recipient) {
            return null;
        }

        DB::transaction(function () use ($recipient) {
            $wasClicked = $recipient->is_clicked;
            $recipient->update([
                'is_opened' => true,
                'is_clicked' => true,
                'click_count' => $recipient->click_count + 1,
                'opened_at' => $recipient->opened_at ?? now(),
                'clicked_at' => $recipient->clicked_at ?? now(),
            ]);

            if (! $wasClicked) {
                $recipient->campaign()->increment('clicked_count');
            }
            if (! $recipient->getOriginal('is_opened')) {
                // opened_count handled if first open via click
            }
        });

        return $recipient->fresh();
    }

    public function unsubscribeFromRecipientToken(string $token): ?Suppression
    {
        $recipient = CampaignRecipient::where('tracking_token', $token)->first();
        if (! $recipient) {
            return null;
        }

        return Suppression::updateOrCreate(
            [
                'organization_id' => $recipient->organization_id,
                'email' => strtolower($recipient->email),
            ],
            [
                'reason' => 'campaign_unsubscribe',
                'token' => Str::random(48),
                'unsubscribed_at' => now(),
            ]
        );
    }

    public function isSafeRedirect(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');

        return in_array($scheme, ['http', 'https'], true);
    }
}
