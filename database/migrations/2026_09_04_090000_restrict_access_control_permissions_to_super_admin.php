<?php

use App\Support\UserManagementHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $protectedRoleIds = DB::table('roles')
            ->where('guard_name', 'admin')
            ->whereIn('name', UserManagementHelper::PROTECTED_ROLES)
            ->pluck('id');

        if ($protectedRoleIds->isEmpty()) {
            return;
        }

        $sensitivePermissionIds = DB::table('permissions')
            ->where('guard_name', 'admin')
            ->whereIn('name', UserManagementHelper::ACCESS_CONTROL_PERMISSIONS)
            ->pluck('id');

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $sensitivePermissionIds)
            ->whereNotIn('role_id', $protectedRoleIds)
            ->delete();
    }

    public function down(): void
    {
        // Security revocations are intentionally not restored automatically.
    }
};
