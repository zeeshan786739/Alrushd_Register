<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IntegrationPermissionsSeeder extends Seeder
{
    /** @return array<int, string> */
    public static function permissions(): array
    {
        return [
            'view integrations',
            'manage integrations',
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
