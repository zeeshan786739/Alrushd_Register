<?php

namespace App\Models\EmailMarketing;

use App\Models\Admin;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    protected $table = 'em_campaigns';

    protected $fillable = [
        'organization_id', 'name', 'subject', 'from_name', 'from_email',
        'body_html', 'body_text', 'template_id', 'status', 'recipient_source',
        'recipient_filters', 'scheduled_at', 'started_at', 'completed_at',
        'recipient_count', 'sent_count', 'failed_count', 'opened_count', 'clicked_count',
        'tracking_enabled', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recipient_filters' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'tracking_enabled' => 'boolean',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function openRate(): float
    {
        if ($this->sent_count <= 0) {
            return 0.0;
        }

        return round(($this->opened_count / $this->sent_count) * 100, 1);
    }
}
