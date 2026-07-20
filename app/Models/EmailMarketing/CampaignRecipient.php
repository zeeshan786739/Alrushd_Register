<?php

namespace App\Models\EmailMarketing;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignRecipient extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_campaign_recipients';

    protected $fillable = [
        'organization_id', 'campaign_id', 'email', 'name', 'lead_id', 'customer_id', 'form_entry_id',
        'status', 'tracking_token', 'is_opened', 'is_clicked', 'open_count', 'click_count',
        'sent_at', 'opened_at', 'clicked_at', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'is_opened' => 'boolean',
            'is_clicked' => 'boolean',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $recipient): void {
            if (! $recipient->tracking_token) {
                $recipient->tracking_token = Str::random(48);
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function formEntry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }
}
