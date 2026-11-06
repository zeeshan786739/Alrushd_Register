<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION = 'manage lead settings';

    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('custom_data');
        });

        // Whoever can already manage CRM settings gets this one too.
        $now = now();
        DB::table('permissions')->insertOrIgnore([
            'name' => self::PERMISSION,
            'guard_name' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $permissionId = DB::table('permissions')->where('name', self::PERMISSION)->where('guard_name', 'admin')->value('id');

        $roleIds = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('permissions.name', 'manage crm documents')
            ->where('permissions.guard_name', 'admin')
            ->pluck('role_has_permissions.role_id');

        DB::table('role_has_permissions')->insertOrIgnore(
            $roleIds->map(fn ($roleId) => ['permission_id' => $permissionId, 'role_id' => $roleId])->all()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', self::PERMISSION)->where('guard_name', 'admin')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
};
