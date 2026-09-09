<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->unsignedInteger('list_position')->nullable()->after('lead_status');
            $table->index(['organization_id', 'list_position'], 'crm_leads_org_list_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex('crm_leads_org_list_position_idx');
            $table->dropColumn('list_position');
        });
    }
};
