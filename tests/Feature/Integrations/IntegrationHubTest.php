<?php

namespace Tests\Feature\Integrations;

use App\Models\Integrations\IntegrationConnection;
use App\Models\Organization;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\IntegrationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntegrationHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_integrations_hub(): void
    {
        $this->seed(CrmPermissionsSeeder::class);
        $this->seed(IntegrationPermissionsSeeder::class);

        $organization = Organization::create(['name' => 'Org A', 'slug' => 'org-a', 'is_active' => true]);
        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->firstOrFail();

        $admin = \App\Models\Admin::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $organization->id,
        ]);
        $admin->assignRole($role);

        IntegrationConnection::forPlatform($organization, \App\Enums\IntegrationPlatform::Facebook);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.integrations.hub'))
            ->assertOk()
            ->assertSee('Facebook Lead Ads');
    }
}
