<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\Suppression;
use Illuminate\Support\Collection;

/**
 * Organization-scoped marketing suppression helpers.
 * Marketing-only rules must NOT block transactional CRM mail.
 */
class SuppressionService
{
    public const REASON_UNSUBSCRIBE = 'unsubscribe';

    public const REASON_BOUNCE = 'bounce';

    public const REASON_DROPPED = 'dropped';

    public const REASON_SPAM = 'spamreport';

    /** @return list<string> */
    public function marketingBlockReasons(): array
    {
        return [
            self::REASON_UNSUBSCRIBE,
            'sendgrid_unsubscribe',
            'sendgrid_group_unsubscribe',
            self::REASON_BOUNCE,
            'sendgrid_bounce',
            self::REASON_DROPPED,
            'sendgrid_dropped',
            self::REASON_SPAM,
            'sendgrid_spamreport',
        ];
    }

    public function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    public function isMarketingBlocked(int $organizationId, string $email): bool
    {
        return $this->marketingBlockReason($organizationId, $email) !== null;
    }

    /**
     * Transactional CRM mail is NOT blocked by marketing unsubscribe alone.
     * Hard bounce/spam may still be checked by callers if desired — default NO for transactional.
     */
    public function isTransactionalBlocked(int $organizationId, string $email): bool
    {
        return false;
    }

    public function marketingBlockReason(int $organizationId, string $email): ?string
    {
        $normalized = $this->normalize($email);
        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return 'invalid';
        }

        $row = Suppression::query()
            ->where('organization_id', $organizationId)
            ->where('email', $normalized)
            ->first();

        if (! $row) {
            return null;
        }

        // Active marketing unsubscribe (not cleared by group resubscribe).
        if ($row->unsubscribed_at && ! $row->resubscribed_at) {
            return 'unsubscribed';
        }

        if ($row->unsubscribed_at && $row->resubscribed_at
            && $row->resubscribed_at->gte($row->unsubscribed_at)) {
            // Resubscribed — still block permanent delivery failures.
            $reason = strtolower((string) $row->reason);
            if ($this->isPermanentDeliveryReason($reason)) {
                return $reason ?: 'suppressed';
            }

            return null;
        }

        $reason = strtolower((string) $row->reason);
        if ($this->isPermanentDeliveryReason($reason) || in_array($reason, $this->marketingBlockReasons(), true)) {
            return $reason ?: 'suppressed';
        }

        // Any suppression row historically blocked campaigns (legacy). Keep if reason empty but row exists without resubscribe.
        if ($row->reason === null && $row->unsubscribed_at === null) {
            return 'suppressed';
        }

        return null;
    }

    /**
     * @param  Collection<int, string>|array<int, string>  $emails
     * @return array<string, string> normalized_email => reason
     */
    public function marketingBlocksMap(int $organizationId, Collection|array $emails): array
    {
        $normalized = collect($emails)
            ->map(fn ($e) => $this->normalize((string) $e))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [];
        }

        $rows = Suppression::query()
            ->where('organization_id', $organizationId)
            ->whereIn('email', $normalized->all())
            ->get()
            ->keyBy('email');

        $map = [];
        foreach ($normalized as $email) {
            $row = $rows->get($email);
            if (! $row) {
                continue;
            }
            $reason = $this->reasonFromRow($row);
            if ($reason) {
                $map[$email] = $reason;
            }
        }

        return $map;
    }

    public function record(
        int $organizationId,
        string $email,
        string $reason,
        ?string $source = 'sendgrid',
        ?string $provider = 'sendgrid',
        ?int $providerGroupId = null,
        bool $markUnsubscribed = false,
    ): Suppression {
        $normalized = $this->normalize($email);
        $row = Suppression::query()->firstOrNew([
            'organization_id' => $organizationId,
            'email' => $normalized,
        ]);

        $row->reason = $reason;
        $row->source = $source;
        $row->provider = $provider;
        if ($providerGroupId) {
            $row->provider_group_id = $providerGroupId;
        }

        if ($markUnsubscribed) {
            $row->unsubscribed_at = $row->unsubscribed_at ?: now();
            $row->resubscribed_at = null;
        }

        $row->save();

        return $row;
    }

    public function clearMarketingUnsubscribe(int $organizationId, string $email, ?int $providerGroupId = null): void
    {
        $row = Suppression::query()
            ->where('organization_id', $organizationId)
            ->where('email', $this->normalize($email))
            ->first();

        if (! $row || ! $row->unsubscribed_at) {
            return;
        }

        // Never clear permanent bounce/spam via group resubscribe.
        if ($this->isPermanentDeliveryReason(strtolower((string) $row->reason))) {
            return;
        }

        $row->resubscribed_at = now();
        if ($providerGroupId) {
            $row->provider_group_id = $providerGroupId;
        }
        $row->save();
    }

    private function reasonFromRow(Suppression $row): ?string
    {
        if ($row->unsubscribed_at && (! $row->resubscribed_at || $row->resubscribed_at->lt($row->unsubscribed_at))) {
            return 'unsubscribed';
        }

        $reason = strtolower((string) $row->reason);
        if ($this->isPermanentDeliveryReason($reason) || in_array($reason, $this->marketingBlockReasons(), true)) {
            return $reason ?: 'suppressed';
        }

        if ($row->reason === null && $row->unsubscribed_at === null && $row->resubscribed_at === null) {
            return 'suppressed';
        }

        return null;
    }

    private function isPermanentDeliveryReason(string $reason): bool
    {
        return in_array($reason, [
            self::REASON_BOUNCE,
            'sendgrid_bounce',
            self::REASON_DROPPED,
            'sendgrid_dropped',
            self::REASON_SPAM,
            'sendgrid_spamreport',
        ], true);
    }
}
