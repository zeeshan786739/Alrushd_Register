<?php

namespace Tests\Feature\EmailMarketing;

use App\Models\Admin;
use App\Models\Organization;
use Database\Seeders\CrmPermissionsSeeder;
use Database\Seeders\EmailMarketingPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class EmailMarketingTestCase extends TestCase
{
    use RefreshDatabase;

    protected Organization $organizationA;

    protected Organization $organizationB;

    protected Admin $adminA;

    protected Admin $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CrmPermissionsSeeder::class);
        $this->seed(EmailMarketingPermissionsSeeder::class);

        $this->organizationA = Organization::create(['name' => 'Org A', 'slug' => 'em-org-a', 'is_active' => true]);
        $this->organizationB = Organization::create(['name' => 'Org B', 'slug' => 'em-org-b', 'is_active' => true]);

        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->firstOrFail();

        $this->adminA = Admin::create([
            'name' => 'EM Admin A',
            'email' => 'em-admin-a@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationA->id,
        ]);
        $this->adminA->assignRole($role);

        $this->adminB = Admin::create([
            'name' => 'EM Admin B',
            'email' => 'em-admin-b@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $this->organizationB->id,
        ]);
        $this->adminB->assignRole($role);
    }

    protected function actingAsEmAdmin(?Admin $admin = null): self
    {
        return $this->actingAs($admin ?? $this->adminA, 'admin');
    }
}
