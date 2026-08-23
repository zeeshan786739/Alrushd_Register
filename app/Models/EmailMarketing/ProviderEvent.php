<?php

namespace App\Models\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderEvent extends Model
{
    protected $table = 'em_provider_events';

    protected $fillable = [
        'organization_id', 'provider', 'provider_event_id', 'event_type',
        'correlation_uuid', 'provider_message_id', 'em_message_id',
        'em_campaign_recipient_id', 'email', 'occurred_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'em_message_id');
    }

    public function campaignRecipient(): BelongsTo
    {
        return $this->belongsTo(CampaignRecipient::class, 'em_campaign_recipient_id');
    }
}
