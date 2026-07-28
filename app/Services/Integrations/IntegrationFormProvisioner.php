<?php

namespace App\Services\Integrations\Meta;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Integrations\IntegrationFormMapping;
use App\Models\Integrations\MetaLeadSubmission;
use App\Models\Organization;

class IntegrationFormProvisioner
{
    public function ensureFacebookLeadForm(Organization $organization): Form
    {
        $slug = 'facebook-lead-ads-'.$organization->slug;

        return Form::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'slug' => $slug,
            ],
            [
                'name' => 'Facebook Lead Ads ('.$organization->name.')',
                'description' => 'System form for leads imported from Facebook Lead Ads.',
                'handler' => 'system',
                'is_active' => false,
                'show_on_landing' => false,
                'sort_order' => 9999,
                'settings' => [
                    'source' => 'facebook_lead_ads',
                    'system' => true,
                ],
            ]
        );
    }
}
