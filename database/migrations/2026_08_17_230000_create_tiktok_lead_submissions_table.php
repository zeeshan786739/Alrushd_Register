<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_lead_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->foreignId('tiktok_form_mapping_id')->nullable()->constrained('tiktok_form_mappings')->nullOnDelete();
            $table->string('advertiser_id');
            $table->string('tiktok_lead_id');
            $table->string('tiktok_page_id')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('webhook_meta')->nullable();
            $table->json('field_data')->nullable();
            $table->foreignId('form_entry_id')->nullable()->constrained('form_entries')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->string('error_message', 500)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['advertiser_id', 'tiktok_lead_id'],
                'tiktok_lead_submissions_advertiser_lead_unique'
            );
            $table->index(['organization_id', 'status'], 'tiktok_lead_submissions_org_status_idx');
            $table->index(['organization_id', 'received_at'], 'tiktok_lead_submissions_org_received_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_lead_submissions');
    }
};
