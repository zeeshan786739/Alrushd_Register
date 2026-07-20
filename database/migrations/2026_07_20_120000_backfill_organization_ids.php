<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organizations')) {
            return;
        }

        $organizationId = DB::table('organizations')
            ->where('slug', 'default')
            ->value('id');

        if (! $organizationId) {
            $organizationId = DB::table('organizations')->insertGetId([
                'name' => 'Default Organization',
                'slug' => 'default',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn('admins', 'organization_id')) {
            DB::table('admins')->whereNull('organization_id')->update(['organization_id' => $organizationId]);
        }

        if (Schema::hasColumn('forms', 'organization_id')) {
            DB::table('forms')->whereNull('organization_id')->update(['organization_id' => $organizationId]);
        }

        if (Schema::hasColumn('form_entries', 'organization_id')) {
            DB::table('form_entries')->whereNull('organization_id')->update(['organization_id' => $organizationId]);
        }
    }

    public function down(): void
    {
        // Backfill is intentionally non-destructive on rollback.
    }
};
