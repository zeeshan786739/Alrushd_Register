<?php

namespace Tests\Feature\Crm;

use App\Models\Admin;
use App\Models\Crm\Lead;
use Database\Seeders\CrmPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_leads_routes_require_view_permission(): void
    {
        $this->seed(CrmPermissionsSeeder::class);

        $org = \App\Models\Organization::create(['name' => 'Org', 'slug' => 'org', 'is_active' => true]);
        $role = Role::create(['name' => 'limited', 'guard_name' => 'admin']);
        $admin = Admin::create([
            'name' => 'Limited',
            'email' => 'limited@test.local',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
        ]);
        $admin->assignRole($role);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.crm.leads.index'))
            ->assertForbidden();

        $role->givePermissionTo(Permission::findByName('view leads', 'admin'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.crm.leads.index'))
            ->assertOk();
    }
}
