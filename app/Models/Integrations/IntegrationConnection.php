<?php

namespace App\Models\Integrations;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\IntegrationPlatform;
use App\Models\Admin;
use App\Models\Organization;
use App\Traits\BelongsToOrganization;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationConnection extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'platform',
        'status',
        'external_account_id',
        'external_account_name',
        'access_token',
        'token_expires_at',
        'webhook_subscribed_at',
        'last_webhook_at',
        'settings',
        'connected_by',
    ];

    protected function casts(): array
    {
        return [
            'platform' => IntegrationPlatform::class,
            'status' => IntegrationConnectionStatus::class,
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'webhook_subscribed_at' => 'datetime',
            'last_webhook_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function connectedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'connected_by');
    }

    public function formMappings(): HasMany
    {
        return $this->hasMany(IntegrationFormMapping::class);
    }

    public function metaLeadSubmissions(): HasMany
    {
        return $this->hasMany(MetaLeadSubmission::class);
    }

    public function isConnected(): bool
    {
        try {
            return $this->status === IntegrationConnectionStatus::Connected
                && filled($this->access_token)
                && filled($this->external_account_id);
        } catch (DecryptException) {
            // Database dumps may contain credentials encrypted with another
            // application's key. They cannot be used until reconnected.
            return false;
        }
    }

    public static function forPlatform(Organization $organization, IntegrationPlatform $platform): self
    {
        return static::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'platform' => $platform,
            ],
            [
                'status' => IntegrationConnectionStatus::Disconnected,
            ]
        );
    }
}
