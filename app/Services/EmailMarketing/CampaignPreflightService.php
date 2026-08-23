<?php

namespace App\Services\EmailMarketing;

use Illuminate\Support\Collection;

/**
 * Pre-send audience summary for marketing campaigns.
 *
 * @phpstan-type PreflightResult array{
 *   selected:int,
 *   valid:int,
 *   invalid:int,
 *   duplicates:int,
 *   unsubscribed:int,
 *   suppressed:int,
 *   eligible:int,
 *   eligible_rows:Collection
 * }
 */
class CampaignPreflightService
{
    public function __construct(
        private CampaignRecipientResolver $resolver,
        private SuppressionService $suppressions,
    ) {
    }

    /**
     * @param  array{source:string, lead_ids?:array<int>, customer_ids?:array<int>, form_entry_ids?:array<int>, manual_emails?:string, lead_status?:?string}  $options
     * @return PreflightResult
     */
    public function summarize(int $organizationId, array $options): array
    {
        $raw = $this->resolver->rawCandidates($organizationId, $options);

        $selected = $raw->count();
        $validRows = $raw->filter(fn ($row) => filter_var($row['email'], FILTER_VALIDATE_EMAIL));
        $valid = $validRows->count();
        $invalid = $selected - $valid;

        $emails = $validRows->map(fn ($row) => $this->suppressions->normalize($row['email']));
        $duplicates = $emails->count() - $emails->unique()->count();

        $uniqueRows = $validRows
            ->unique(fn ($row) => $this->suppressions->normalize($row['email']))
            ->values();

        $blocks = $this->suppressions->marketingBlocksMap(
            $organizationId,
            $uniqueRows->pluck('email')
        );

        $unsubscribed = 0;
        $suppressed = 0;
        $eligibleRows = collect();

        foreach ($uniqueRows as $row) {
            $email = $this->suppressions->normalize($row['email']);
            $reason = $blocks[$email] ?? null;
            if ($reason === null) {
                $eligibleRows->push($row);
                continue;
            }
            if ($reason === 'unsubscribed' || str_contains($reason, 'unsubscribe')) {
                $unsubscribed++;
            } else {
                $suppressed++;
            }
        }

        return [
            'selected' => $selected,
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicates' => max(0, $duplicates),
            'unsubscribed' => $unsubscribed,
            'suppressed' => $suppressed,
            'eligible' => $eligibleRows->count(),
            'eligible_rows' => $eligibleRows,
        ];
    }
}
