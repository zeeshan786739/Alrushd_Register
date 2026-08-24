<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'student_packages',
        'student_languages',
        'student_subjects',
        'student_groups',
        'student_years',
        'student_courses',
        'schools',
        'nationalities',
        'genders',
        'relation_ships',
        'payment_countries',
        'admission_dates',
        'terms_and_conditions',
    ];

    public function up(): void
    {
        $defaultOrgId = Organization::query()->orderBy('id')->value('id');

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'organization_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('organization_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });

            if ($defaultOrgId) {
                DB::table($table)->whereNull('organization_id')->update(['organization_id' => $defaultOrgId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'organization_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('organization_id');
            });
        }
    }
};
