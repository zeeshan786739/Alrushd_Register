<?php

namespace App\Services\Tenant;

use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Services\Tenant\EnrollmentCatalogProvisioner;
use Illuminate\Support\Str;

/**
 * Provisions a fresh tenant workspace — blank CMS, no copied forms or legacy data.
 */
class TenantProvisioner
{
    public function provision(Organization $organization): void
    {
        $this->provisionWebsiteCms($organization);
        $this->ensureDomainVerificationToken($organization);
        app(EnrollmentCatalogProvisioner::class)->ensureDefaults($organization);
    }

    private function provisionWebsiteCms(Organization $organization): void
    {
        $name = $organization->name;
        $year = date('Y');

        WebsiteCms::query()->firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'draft' => $this->blankCmsPayload($name, $year),
                'published' => [],
                'version_history' => [],
            ]
        );
    }

    private function ensureDomainVerificationToken(Organization $organization): void
    {
        if ($organization->custom_domain_verification_token) {
            return;
        }

        $organization->update([
            'custom_domain_verification_token' => Str::random(32),
        ]);
    }

    private function blankCmsPayload(string $schoolName, string $year): array
    {
        return [
            'branding' => [
                'company_name' => $schoolName,
                'short_name' => $schoolName,
                'tagline' => 'Welcome to '.$schoolName,
                'logo' => asset('frontend/assets/img/logo.png'),
                'logo_dark' => asset('frontend/assets/img/logo.png'),
                'logo_light' => asset('frontend/assets/img/logo.png'),
                'footer_logo' => asset('frontend/assets/img/logo.png'),
                'favicon' => asset('frontend/assets/img/logo.png'),
                'website_title' => $schoolName,
                'browser_title' => $schoolName,
                'website_description' => 'Official website for '.$schoolName.'.',
                'default_language' => 'en',
                'copyright' => '© '.$year.' '.$schoolName.'. All rights reserved.',
                'company_registration' => '',
            ],
            'theme' => [
                'primary' => '#0F274A',
                'secondary' => '#1a3a6b',
                'accent' => '#2563eb',
                'background' => '#ffffff',
                'text' => '#1e293b',
                'heading' => '#0F274A',
                'button' => '#2563eb',
                'button_hover' => '#1d4ed8',
                'navbar' => '#0F274A',
                'footer' => '#0f172a',
                'card_bg' => '#ffffff',
                'border' => '#e2e8f0',
                'success' => '#16a34a',
                'danger' => '#dc2626',
                'warning' => '#f59e0b',
                'cream' => '#f8fafc',
            ],
            'hero' => [
                'enabled' => true,
                'headline' => 'Welcome to '.$schoolName,
                'subheadline' => 'Your school website is ready. Publish content from Website CMS and add forms from Form Center.',
                'cta_primary_text' => 'Contact us',
                'cta_primary_url' => '#contact',
                'cta_secondary_text' => '',
                'cta_secondary_url' => '',
                'background_image' => '',
                'overlay_opacity' => '40',
            ],
            'sections_order' => [
                'hero', 'about', 'forms_section', 'contact',
            ],
        ];
    }
}
