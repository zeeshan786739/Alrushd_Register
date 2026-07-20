<?php

namespace App\Console\Commands;

use App\Models\EmailMarketing\MailboxSetting;
use App\Services\EmailMarketing\CampaignDispatchService;
use App\Services\EmailMarketing\InboxSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchEmailMarketingCommand extends Command
{
    protected $signature = 'email-marketing:fetch {--organization= : Limit to one organization ID}';

    protected $description = 'Incrementally synchronize IMAP inboxes for enabled organizations';

    public function handle(InboxSyncService $sync, CampaignDispatchService $campaigns): int
    {
        $query = MailboxSetting::query()->where('is_enabled', true);

        if ($this->option('organization')) {
            $query->where('organization_id', (int) $this->option('organization'));
        }

        $settings = $query->get();
        $imported = 0;

        foreach ($settings as $setting) {
            try {
                $result = $sync->syncOrganization($setting->organization_id);
                $imported += (int) ($result['imported'] ?? 0);
                $this->info('Org '.$setting->organization_id.': imported '.($result['imported'] ?? 0));
            } catch (\Throwable $e) {
                Log::error('email-marketing:fetch failed', [
                    'organization_id' => $setting->organization_id,
                    'error' => $e->getMessage(),
                ]);
                $this->error('Org '.$setting->organization_id.' failed.');
            }
        }

        $dispatched = $campaigns->dispatchDueScheduled();
        $this->info("Scheduled campaigns dispatched: {$dispatched}");
        $this->info("Total messages imported: {$imported}");

        return self::SUCCESS;
    }
}
