<?php

namespace App\Models;

use App\Enums\Platform\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasSubscription extends Model
{
    protected $fillable = [
        'organization_id', 'saas_plan_id', 'status', 'stripe_subscription_id',
        'stripe_customer_id', 'current_period_start', 'current_period_end',
        'trial_ends_at', 'canceled_at', 'ends_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'trial_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function scopeCurrent($query)
    {
        return $query->whereIn('status', ['trialing', 'active', 'past_due', 'complimentary']);
    }

    public function isStripeManaged(): bool
    {
        return filled($this->stripe_subscription_id);
    }

    public function isFreeAccess(): bool
    {
        return $this->status === \App\Enums\Platform\SubscriptionStatus::Complimentary
            || ($this->plan?->isFree() && $this->status?->isCurrent());
    }

    public function billingSourceLabel(): string
    {
        if ($this->isStripeManaged()) {
            return 'Stripe';
        }

        if ($this->isFreeAccess()) {
            return 'Free';
        }

        return 'Manual';
    }
}
