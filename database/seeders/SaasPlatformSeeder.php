<?php

namespace Database\Seeders;

use App\Enums\Platform\OrganizationStatus;
use App\Enums\Platform\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\PlatformSetting;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Support\PlanEntitlements;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaasPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $allModules = PlanEntitlements::allModuleKeys();
        $starterMarketing = PlanEntitlements::marketingLines($allModules);

        // 1. Founding tenant — AL-Rushd, active with a complimentary subscription.
        $alRushd = Organization::default();
        $alRushd->update([
            'name' => 'AL-Rushd Online School',
            'slug' => 'al-rushd',
            'status' => OrganizationStatus::Active,
            'is_active' => true,
            'email' => $alRushd->email ?? 'info@alrushd.co.uk',
            'website' => $alRushd->website ?? 'https://alrushd.co.uk',
            'country' => $alRushd->country ?? 'United Kingdom',
            'timezone' => 'Europe/London',
        ]);

        // 2. Subscription plans (also power the public pricing table).
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'tagline' => 'For small schools getting organised',
                'price' => 0,
                'is_featured' => false,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'modules' => $allModules,
                'limits' => [],
                'features' => array_merge($starterMarketing, ['Email support']),
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'tagline' => 'For growing schools that run campaigns',
                'price' => 99,
                'is_featured' => true,
                'is_default' => false,
                'is_active' => false,
                'sort_order' => 2,
                'modules' => $allModules,
                'limits' => ['max_admins' => 10],
                'features' => [
                    'Up to 10 staff accounts',
                    'Everything in Starter',
                    'Email marketing & campaigns',
                    'Facebook & TikTok Lead Ads sync',
                    'Quotations & invoicing with Stripe',
                    'Priority support',
                ],
            ],
            [
                'name' => 'Scale',
                'slug' => 'scale',
                'tagline' => 'For multi-campus groups & large teams',
                'price' => 199,
                'is_featured' => false,
                'is_default' => false,
                'is_active' => false,
                'sort_order' => 3,
                'modules' => $allModules,
                'limits' => [],
                'features' => [
                    'Unlimited staff accounts',
                    'Everything in Growth',
                    'Assisted data migration & onboarding',
                    'Advanced roles & permissions',
                    'Dedicated account manager',
                    'SLA-backed uptime',
                ],
            ],
        ];

        foreach ($plans as $data) {
            SaasPlan::updateOrCreate(
                ['slug' => $data['slug']],
                $data + [
                    'currency' => 'USD',
                    'billing_interval' => 'month',
                    'trial_days' => 14,
                ]
            );
        }

        // 3. Complimentary (internal) subscription for AL-Rushd.
        if (! $alRushd->subscriptions()->current()->exists()) {
            SaasSubscription::create([
                'organization_id' => $alRushd->id,
                'saas_plan_id' => SaasPlan::where('slug', 'starter')->value('id'),
                'status' => SubscriptionStatus::Complimentary,
            ]);
        }

        // 4. Platform owner (SaaS super admin) — no organization, /superadmin only.
        Admin::updateOrCreate(
            ['email' => 'owner@enrolliq.com'],
            [
                'name' => 'Platform Owner',
                'password' => Hash::make('password'), // Change in production!
                'organization_id' => null,
                'is_platform_admin' => true,
            ]
        );

        // 5. Platform branding defaults.
        PlatformSetting::firstOrCreate(['key' => 'platform_name'], ['value' => config('saas.name')]);
        PlatformSetting::firstOrCreate(['key' => 'support_email'], ['value' => config('saas.support_email')]);
    }
}
