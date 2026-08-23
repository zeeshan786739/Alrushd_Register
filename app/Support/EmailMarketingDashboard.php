<?php

namespace App\Support;

use App\Enums\EmailMarketing\CampaignStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Campaign;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Message;
use App\Models\EmailMarketing\Suppression;
use App\Models\EmailMarketing\Template;
use App\Models\FormEntry;

final class EmailMarketingDashboard
{
    /** @return array<string, mixed> */
    public static function stats(int $organizationId): array
    {
        $messages = Message::query()->where('organization_id', $organizationId);
        $campaigns = Campaign::query()->where('organization_id', $organizationId);

        $leadsQuery = Lead::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $lastCampaign = (clone $campaigns)
            ->where('sent_count', '>', 0)
            ->orderByDesc('completed_at')
            ->first();

        $mailbox = MailboxSetting::query()->where('organization_id', $organizationId)->first();

        return [
            'inbox_unread' => (clone $messages)->inbox()->unread()->count(),
            'inbox_total' => (clone $messages)->inbox()->count(),
            'sent_total' => (clone $messages)->sent()->count(),
            'drafts_total' => (clone $messages)->draft()->count(),
            'campaigns_total' => (clone $campaigns)->count(),
            'campaigns_draft' => (clone $campaigns)->where('status', CampaignStatus::Draft->value)->count(),
            'campaigns_scheduled' => (clone $campaigns)->where('status', CampaignStatus::Scheduled->value)->count(),
            'campaigns_sent' => (clone $campaigns)->where('status', CampaignStatus::Sent->value)->count(),
            'templates_total' => Template::query()->where('organization_id', $organizationId)->where('is_active', true)->count(),
            'suppressions_total' => Suppression::query()->where('organization_id', $organizationId)->count(),
            'audience_leads' => (clone $leadsQuery)->count(),
            'audience_customers' => Customer::query()
                ->where('organization_id', $organizationId)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->count(),
            'audience_forms' => FormEntry::query()
                ->where('organization_id', $organizationId)
                ->count(),
            'audience_facebook' => (clone $leadsQuery)->where('source', 'facebook_lead_ads')->count(),
            'audience_tiktok' => (clone $leadsQuery)->where('source', 'tiktok_lead_ads')->count(),
            'audience_imports' => (clone $leadsQuery)->fromImport()->count(),
            'last_open_rate' => $lastCampaign?->openRate() ?? 0,
            'last_campaign_name' => $lastCampaign?->name,
            'mailbox_connected' => (bool) ($mailbox?->is_enabled && filled($mailbox?->from_email)),
            'sendgrid_ready' => filled(config('sendgrid.api_key')) || filled($mailbox?->sendgrid_asm_group_id),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function attention(int $organizationId): array
    {
        $items = [];
        $stats = self::stats($organizationId);

        if ($stats['inbox_unread'] > 0) {
            $items[] = [
                'label' => $stats['inbox_unread'].' unread message'.($stats['inbox_unread'] === 1 ? '' : 's'),
                'meta' => 'Reply from your marketing inbox',
                'url' => route('admin.email.inbox'),
                'severity' => 'info',
            ];
        }

        if (! $stats['mailbox_connected']) {
            $items[] = [
                'label' => 'Connect your mailbox',
                'meta' => 'Set up SMTP or SendGrid to send campaigns and receive replies',
                'url' => route('admin.email.mailbox.settings'),
                'severity' => 'warning',
            ];
        }

        if ($stats['campaigns_draft'] > 0) {
            $items[] = [
                'label' => $stats['campaigns_draft'].' draft campaign'.($stats['campaigns_draft'] === 1 ? '' : 's'),
                'meta' => 'Finish and send when ready',
                'url' => route('admin.email.campaigns.index', ['status' => CampaignStatus::Draft->value]),
                'severity' => 'neutral',
            ];
        }

        return $items;
    }
}
