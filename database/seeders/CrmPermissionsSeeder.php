<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CrmPermissionsSeeder extends Seeder
{
    /** @return array<int, string> */
    public static function permissions(): array
    {
        return [
            'view leads', 'create leads', 'update leads', 'delete leads',
            'assign leads', 'convert leads', 'export leads',
            'view customers', 'create customers', 'update customers', 'delete customers', 'export customers',
            'view projects', 'create projects', 'update projects', 'delete projects',
            'view quotations', 'create quotations', 'update quotations', 'delete quotations',
            'send quotations', 'convert quotations',
            'view invoices', 'create invoices', 'update invoices', 'delete invoices',
            'send invoices', 'record invoice payments',
            'view form submissions', 'update form submissions', 'delete form submissions',
            'export form submissions', 'convert form submissions',
        ];
    }

    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);

        foreach (self::permissions() as $name) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'admin',
            ]);

            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }
}
