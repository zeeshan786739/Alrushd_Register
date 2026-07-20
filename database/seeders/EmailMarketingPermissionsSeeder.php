<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmailMarketingPermissionsSeeder extends Seeder
{
    /** @return array<int, string> */
    public static function permissions(): array
    {
        return [
            'view email marketing',
            'view inbox',
            'sync inbox',
            'read email messages',
            'compose emails',
            'send emails',
            'manage drafts',
            'view sent emails',
            'star emails',
            'view campaigns',
            'create campaigns',
            'update campaigns',
            'delete campaigns',
            'schedule campaigns',
            'send campaigns',
            'view campaign analytics',
            'view templates',
            'create templates',
            'update templates',
            'delete templates',
            'manage mailbox settings',
            'download email attachments',
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
