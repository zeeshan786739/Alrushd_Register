<?php

namespace App\Jobs\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\EmailMarketing\RecipientStatus;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\Suppression;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\MailConfigResolver;
use App\Services\EmailMarketing\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $recipientId)
    {
    }

    public function handle(
        MailConfigResolver $mailConfig,
        TemplateRenderer $renderer,
        HtmlSanitizer $sanitizer,
    ): void {
        $recipient = CampaignRecipient::with('campaign')->find($this->recipientId);

        if (! $recipient || ! $recipient->campaign) {
            return;
        }

        if ($recipient->status === RecipientStatus::Sent->value) {
            return;
        }

        $campaign = $recipient->campaign;

        if ($campaign->status === CampaignStatus::Cancelled->value) {
            $recipient->update([
                'status' => RecipientStatus::Skipped->value,
                'error_message' => 'Campaign cancelled',
            ]);

            return;
        }

        if (Suppression::query()
            ->where('organization_id', $recipient->organization_id)
            ->where('email', strtolower($recipient->email))
            ->whereNotNull('unsubscribed_at')
            ->exists()) {
            $recipient->update([
                'status' => RecipientStatus::Skipped->value,
                'error_message' => 'Unsubscribed',
            ]);
            $this->refreshCampaignCounters($campaign->id);

            return;
        }

        $settings = $mailConfig->resolveOrFail($recipient->organization_id);
        $mailConfig->applyRuntimeConfig($settings);

        $unsubscribeUrl = route('email-marketing.unsubscribe.show', [
            'token' => $recipient->tracking_token,
        ]);

        $html = $renderer->render($campaign->body_html ?? '', [
            'name' => $recipient->name,
            'email' => $recipient->email,
            'company' => '',
            'unsubscribe_url' => $unsubscribeUrl,
        ]);
        $html = $sanitizer->sanitize($html);

        if ($campaign->tracking_enabled && $settings->tracking_enabled) {
            $pixel = '<img src="'.e(route('email-marketing.track.open', ['token' => $recipient->tracking_token])).'" width="1" height="1" alt="" />';
            $html .= $pixel;
            $html = $this->wrapLinks($html, $recipient->tracking_token);
        }

        $html .= '<p style="font-size:12px;color:#666"><a href="'.e($unsubscribeUrl).'">Unsubscribe</a></p>';

        try {
            Mail::html($html, function ($mail) use ($recipient, $campaign, $settings) {
                $mail->to($recipient->email, $recipient->name)
                    ->subject($campaign->subject)
                    ->from(
                        $campaign->from_email ?: $settings->from_email,
                        $campaign->from_name ?: $settings->from_name
                    );
            });

            $recipient->update([
                'status' => RecipientStatus::Sent->value,
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $recipient->update([
                'status' => RecipientStatus::Failed->value,
                'error_message' => Str::limit($e->getMessage(), 500),
            ]);
        }

        $this->refreshCampaignCounters($campaign->id);
    }

    private function wrapLinks(string $html, string $token): string
    {
        return preg_replace_callback(
            '/href=("|\')(https?:\/\/[^"\']+)\1/i',
            function ($matches) use ($token) {
                $url = $matches[2];
                $tracked = route('email-marketing.track.click', ['token' => $token]).'?url='.urlencode($url);

                return 'href="'.$tracked.'"';
            },
            $html
        ) ?? $html;
    }

    private function refreshCampaignCounters(int $campaignId): void
    {
        $campaign = Campaign::find($campaignId);
        if (! $campaign) {
            return;
        }

        $sent = $campaign->recipients()->where('status', RecipientStatus::Sent->value)->count();
        $failed = $campaign->recipients()->where('status', RecipientStatus::Failed->value)->count();
        $pending = $campaign->recipients()->whereIn('status', [
            RecipientStatus::Pending->value,
            RecipientStatus::Queued->value,
        ])->count();

        $status = $campaign->status;
        if ($pending === 0) {
            $status = ($failed > 0 && $sent === 0)
                ? CampaignStatus::Failed->value
                : CampaignStatus::Sent->value;
        }

        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $status,
            'completed_at' => $pending === 0 ? now() : $campaign->completed_at,
        ]);
    }
}
