<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('platform', 32);
            $table->string('status', 32)->default('disconnected');
            $table->string('external_account_id')->nullable();
            $table->string('external_account_name')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('webhook_subscribed_at')->nullable();
            $table->timestamp('last_webhook_at')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('connected_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'platform']);
            $table->index(['platform', 'external_account_id']);
        });

        Schema::create('integration_form_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->string('external_form_id');
            $table->string('external_form_name')->nullable();
            $table->string('internal_label');
            $table->string('lead_source_label')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('priority', 32)->default('medium');
            $table->boolean('auto_create_lead')->default(true);
            $table->json('field_mapping')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'external_form_id'], 'integration_form_mappings_org_form_unique');
        });

        Schema::create('meta_lead_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->string('meta_leadgen_id')->unique();
            $table->string('meta_form_id')->nullable();
            $table->string('meta_ad_id')->nullable();
            $table->string('meta_campaign_id')->nullable();
            $table->string('meta_page_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('field_data')->nullable();
            $table->foreignId('form_entry_id')->nullable()->constrained('form_entries')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('integration_form_mapping_id')->nullable()->constrained('integration_form_mappings')->nullOnDelete();
            $table->string('status', 32)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['meta_form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_lead_submissions');
        Schema::dropIfExists('integration_form_mappings');
        Schema::dropIfExists('integration_connections');
    }
};
