<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_entries', function (Blueprint $table) {
            $table->string('legacy_source')->nullable()->after('form_id');
            $table->unsignedBigInteger('legacy_record_id')->nullable()->after('legacy_source');
            $table->unique(['form_id', 'legacy_source', 'legacy_record_id'], 'form_entries_legacy_unique');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->string('legacy_table')->nullable()->after('legacy_route');
        });
    }

    public function down(): void
    {
        Schema::table('form_entries', function (Blueprint $table) {
            $table->dropUnique('form_entries_legacy_unique');
            $table->dropColumn(['legacy_source', 'legacy_record_id']);
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('legacy_table');
        });
    }
};
