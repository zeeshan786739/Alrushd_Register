<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('saas_plans', 'is_default')) {
            Schema::table('saas_plans', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('saas_plans', 'is_default')) {
            Schema::table('saas_plans', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
