<?php

namespace App\Enums\EmailMarketing;

enum ProviderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Processed = 'processed';
    case Delivered = 'delivered';
    case Deferred = 'deferred';
    case Bounce = 'bounce';
    case Dropped = 'dropped';
    case Failed = 'failed';
    case Open = 'open';
    case Click = 'click';
    case SpamReport = 'spamreport';
    case Unsubscribe = 'unsubscribe';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Processed => 'Processed',
            self::Delivered => 'Delivered',
            self::Deferred => 'Deferred',
            self::Bounce => 'Bounced',
            self::Dropped => 'Dropped',
            self::Failed => 'Failed',
            self::Open => 'Opened',
            self::Click => 'Clicked',
            self::SpamReport => 'Spam report',
            self::Unsubscribe => 'Unsubscribed',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Delivered, self::Open, self::Click => 'success',
            self::Accepted, self::Processed, self::Pending => 'info',
            self::Deferred => 'warning',
            self::Bounce, self::Dropped, self::Failed, self::SpamReport => 'danger',
            self::Unsubscribe => 'neutral',
        };
    }
}
