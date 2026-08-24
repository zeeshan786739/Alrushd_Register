<?php

namespace App\Console\Commands;

use App\Models\SaasSubscription;
use App\Services\Platform\SubscriptionProvisioner;
use Illuminate\Console\Command;

class NormalizeSaasSubscriptionsCommand extends Command
{
    protected $signature = 'saas:normalize-subscriptions {--dry-run : List changes without saving}';

    protected $description = 'Align free-plan subscriptions to complimentary status and sync organization lifecycle';

    public function handle(SubscriptionProvisioner $provisioner): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        SaasSubscription::query()
            ->with(['plan', 'organization'])
            ->current()
            ->chunkById(50, function ($subscriptions) use ($provisioner, $dryRun, &$updated) {
                foreach ($subscriptions as $subscription) {
                    $plan = $subscription->plan;

                    if (! $plan?->isFree()) {
                        continue;
                    }

                    if ($subscription->status?->value === 'complimentary' && ! $subscription->trial_ends_at) {
                        continue;
                    }

                    $this->line(sprintf(
                        '%s — %s: %s → complimentary (free %s plan)',
                        $subscription->organization?->name ?? 'Unknown',
                        $plan->name,
                        $subscription->status?->label() ?? 'unknown',
                        $plan->formattedPrice(),
                    ));

                    if (! $dryRun) {
                        $provisioner->normalizeSubscription($subscription);
                    }

                    $updated++;
                }
            });

        $this->info($dryRun
            ? "Would normalize {$updated} subscription(s). Run without --dry-run to apply."
            : "Normalized {$updated} subscription(s).");

        return self::SUCCESS;
    }
}
