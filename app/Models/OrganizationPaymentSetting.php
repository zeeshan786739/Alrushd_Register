<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationPaymentSetting extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'is_enabled',
        'test_mode',
        'stripe_publishable_key',
        'stripe_secret',
        'stripe_webhook_secret',
        'statement_descriptor',
        'last_verified_at',
        'updated_by',
    ];

    protected $hidden = [
        'stripe_secret',
        'stripe_webhook_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'test_mode' => 'boolean',
            'stripe_secret' => 'encrypted',
            'stripe_webhook_secret' => 'encrypted',
            'last_verified_at' => 'datetime',
        ];
    }

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled
            && filled($this->stripe_publishable_key)
            && filled($this->stripe_secret);
    }
}
