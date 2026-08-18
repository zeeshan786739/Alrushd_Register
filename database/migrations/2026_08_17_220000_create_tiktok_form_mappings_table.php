<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_form_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->string('advertiser_id');
            $table->string('external_form_id');
            $table->string('external_form_name')->nullable();
            $table->string('external_status', 32)->nullable();
            $table->string('lead_source_label')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('priority', 32)->default('medium');
            $table->boolean('auto_create_lead')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('field_mapping')->nullable();
            $table->json('external_fields')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'advertiser_id', 'external_form_id'],
                'tiktok_form_mappings_org_advertiser_form_unique'
            );
            $table->index(['organization_id', 'integration_connection_id'], 'tiktok_form_mappings_org_connection_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_form_mappings');
    }
};
