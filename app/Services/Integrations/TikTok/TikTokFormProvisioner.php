<?php

namespace App\Services\Integrations\TikTok;

use App\Models\Form;
use App\Models\Organization;

class TikTokFormProvisioner
{
    /**
     * Deterministic per-organization intake form using the current valid Form schema.
     * handler must be `dynamic` or `custom` — `system` is not a valid enum value.
     */
    public function ensureIntakeForm(Organization $organization): Form
    {
        $slug = 'tiktok-lead-ads-'.$organization->id;

        return Form::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'organization_id' => $organization->id,
                'name' => 'TikTok Lead Ads ('.$organization->name.')',
                'description' => 'System intake form for leads imported from TikTok Instant Forms.',
                'handler' => 'dynamic',
                'is_active' => false,
                'show_on_landing' => false,
                'sort_order' => 9999,
                'settings' => [
                    'source' => 'tiktok_lead_ads',
                    'system' => true,
                ],
            ]
        );
    }
}
