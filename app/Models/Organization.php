<?php

namespace App\Models;

use App\Enums\Platform\OrganizationStatus;
use App\Support\OrganizationUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_active', 'status', 'email', 'phone', 'website',
        'custom_domain', 'custom_domain_verified_at', 'custom_domain_verification_token',
        'logo_path', 'contact_name', 'address', 'city', 'country', 'timezone',
        'notes', 'trial_ends_at', 'suspended_at', 'stripe_customer_id',
        'onboarded_by', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => OrganizationStatus::class,
            'trial_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'custom_domain_verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function integrationConnections(): HasMany
    {
        return $this->hasMany(\App\Models\Integrations\IntegrationConnection::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class)->latest('id');
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(SaasSubscription::class)
            ->whereIn('status', ['trialing', 'active', 'past_due', 'complimentary'])
            ->latest('id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(PlatformActivityLog::class)->latest('created_at');
    }

    public function demoRequest(): HasOne
    {
        return $this->hasOne(DemoRequest::class, 'converted_organization_id');
    }

    /** Change lifecycle status and keep the legacy is_active flag in sync. */
    public function transitionTo(OrganizationStatus $status): void
    {
        $this->status = $status;
        $this->is_active = $status->allowsAccess();
        $this->suspended_at = $status === OrganizationStatus::Suspended ? now() : null;
        $this->save();
    }

    public function allowsAccess(): bool
    {
        $status = $this->status instanceof OrganizationStatus
            ? $this->status
            : OrganizationStatus::tryFrom((string) $this->status);

        return $status?->allowsAccess() ?? (bool) $this->is_active;
    }

    public function publicWebsiteUrl(): string
    {
        return OrganizationUrls::publicBase($this);
    }

    public function hasVerifiedCustomDomain(): bool
    {
        return filled($this->custom_domain) && $this->custom_domain_verified_at !== null;
    }

    /**
     * The founding tenant (AL-Rushd). Legacy single-tenant flows fall back to
     * this organization. The original seed row used slug "default" before it
     * was renamed to "al-rushd".
     */
    public static function default(): self
    {
        $existing = static::whereIn('slug', ['al-rushd', 'default'])->orderBy('id')->first();

        return $existing ?? static::create([
            'name' => 'AL-Rushd Online School',
            'slug' => 'al-rushd',
            'is_active' => true,
            'status' => OrganizationStatus::Active,
        ]);
    }
}
