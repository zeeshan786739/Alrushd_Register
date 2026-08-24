<?php

use App\Models\SaasPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('saas_plans', 'modules')) {
            Schema::table('saas_plans', function (Blueprint $table) {
                $table->json('modules')->nullable()->after('features');
            });
        }

        $allModules = array_keys(config('saas_plans.modules', []));
        $marketing = [];
        foreach ($allModules as $key) {
            $line = config("saas_plans.modules.{$key}.marketing");
            if ($line) {
                $marketing[] = $line;
            }
        }

        DB::table('saas_plans')->where('slug', 'starter')->update([
            'price' => 0,
            'currency' => 'USD',
            'billing_interval' => 'month',
            'trial_days' => 14,
            'is_active' => true,
            'is_default' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'modules' => json_encode($allModules),
            'features' => json_encode(array_merge($marketing, ['Email support'])),
            'limits' => json_encode([]),
        ]);

        DB::table('saas_plans')->whereIn('slug', ['growth', 'scale'])->update([
            'is_active' => false,
            'is_default' => false,
            'is_featured' => false,
        ]);

        // Backfill modules on any other plans from full catalog (owner can trim in UI).
        DB::table('saas_plans')
            ->whereNull('modules')
            ->update(['modules' => json_encode($allModules)]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('saas_plans', 'modules')) {
            Schema::table('saas_plans', function (Blueprint $table) {
                $table->dropColumn('modules');
            });
        }
    }
};
