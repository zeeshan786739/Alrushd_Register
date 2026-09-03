<?php

namespace App\Jobs\EmailMarketing;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Enums\EmailMarketing\RecipientStatus;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\CampaignRecipient;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\MailConfigResolver;
use App\Services\EmailMarketing\SuppressionService;
use App\Services\EmailMarketing\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        EmailDeliveryService $delivery,
        SuppressionService $suppressions,
    ): void {
        $recipient = CampaignRecipient::with('campaign')->find($this->recipientId);

        if (! $recipient || ! $recipient->campaign) {
            return;
        }

        if ($recipient->status === RecipientStatus::Sent->value) {
            return;
        }

        // Permanent provider failures — do not retry.
        if (in_array($recipient->provider_status, ['bounce', 'dropped', 'spamreport', 'unsubscribe', 'group_unsubscribe'], true)) {
            $recipient->update([
                'status' => RecipientStatus::Skipped->value,
                'error_message' => $recipient->error_message ?: 'Permanently suppressed by provider status',
            ]);
            $this->refreshCampaignCounters($recipient->campaign_id);

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

        // Re-check suppression at send time (not only at preview/snapshot).
        $blockReason = $suppressions->marketingBlockReason(
            (int) $recipient->organization_id,
            (string) $recipient->email
        );
        if ($blockReason) {
            $recipient->update([
                'status' => RecipientStatus::Skipped->value,
                'error_message' => 'Suppressed: '.$blockReason,
            ]);
            $this->refreshCampaignCounters($campaign->id);

            return;
        }

        $settings = $mailConfig->resolveOrFail($recipient->organization_id);
        $sender = $campaign->sender_mailbox_id
            ? SenderMailbox::query()
                ->where('organization_id', $recipient->organization_id)
                ->available()
                ->findOrFail($campaign->sender_mailbox_id)
            : null;

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

        $track = $campaign->tracking_enabled && $settings->tracking_enabled;
        if ($track) {
            $pixel = '<img src="'.e(route('email-marketing.track.open', ['token' => $recipient->tracking_token])).'" width="1" height="1" alt="" />';
            $html .= $pixel;
            $html = $this->wrapLinks($html, $recipient->tracking_token);
        }

        $html .= '<p style="font-size:12px;color:#666"><a href="'.e($unsubscribeUrl).'">Unsubscribe</a></p>';

        $correlationUuid = $recipient->correlation_uuid ?: (string) Str::uuid();
        if (! $recipient->correlation_uuid) {
            $recipient->update(['correlation_uuid' => $correlationUuid]);
        }

        $asmGroupId = $settings->sendgrid_asm_group_id ? (int) $settings->sendgrid_asm_group_id : null;

        $result = $delivery->send(new OutboundEmail(
            fromEmail: (string) ($sender?->email ?: $campaign->from_email ?: $settings->from_email),
            fromName: $campaign->from_name ?: $sender?->name ?: $settings->from_name,
            to: [strtolower($recipient->email)],
            subject: (string) $campaign->subject,
            html: $html,
            text: $sanitizer->toPlainText($html),
            replyTo: $sender?->reply_to ?: $settings->reply_to ?: $sender?->email ?: $settings->from_email,
            customArgs: [
                'correlation_uuid' => $correlationUuid,
            ],
            category: 'marketing',
            trackOpens: (bool) ($settings->open_tracking ?? $track),
            trackClicks: (bool) ($settings->click_tracking ?? false),
            asmGroupId: $asmGroupId,
        ), $settings);

        if ($result->accepted) {
            $recipient->update([
                'status' => RecipientStatus::Sent->value,
                'sent_at' => now(),
                'error_message' => null,
                'provider' => $result->provider,
                'provider_message_id' => $result->providerMessageId,
                'provider_status' => $result->providerStatus ?: 'processed',
            ]);
        } else {
            $recipient->update([
                'status' => RecipientStatus::Failed->value,
                'error_message' => Str::limit($result->error ?: 'Send failed', 500),
                'provider' => $result->provider,
                'provider_status' => 'failed',
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
