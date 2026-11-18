<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Website CMS → Custom code is editable only with "edit website code". Every role that can
 * edit website settings today gets it, so nobody loses access; a school can later take it
 * away from a role to make Custom code read-only for those people.
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('edit website code', 'admin');
        Role::where('guard_name', 'admin')->get()
            ->filter(fn (Role $role) => $role->hasPermissionTo('edit setting'))
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'edit website code')->where('guard_name', 'admin')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
