<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('advertising_platform', 64)->nullable()->after('lead_source');
            $table->string('campaign_name')->nullable()->after('advertising_platform');
            $table->string('adset_name')->nullable()->after('campaign_name');
            $table->string('ad_name')->nullable()->after('adset_name');
            $table->string('form_name')->nullable()->after('ad_name');
            $table->timestamp('source_submitted_at')->nullable()->after('form_name');
            $table->json('custom_data')->nullable()->after('lead_description');
            $table->foreignId('lead_import_id')->nullable()->after('form_entry_id')->constrained('crm_lead_imports')->nullOnDelete();

            $table->index(['organization_id', 'advertising_platform'], 'crm_leads_org_platform_idx');
            $table->index(['organization_id', 'campaign_name'], 'crm_leads_org_campaign_idx');
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropForeign(['lead_import_id']);
            $table->dropIndex('crm_leads_org_platform_idx');
            $table->dropIndex('crm_leads_org_campaign_idx');
            $table->dropColumn([
                'advertising_platform',
                'campaign_name',
                'adset_name',
                'ad_name',
                'form_name',
                'source_submitted_at',
                'custom_data',
                'lead_import_id',
            ]);
        });
    }
};
