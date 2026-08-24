<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('tone', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'name'], 'lead_categories_org_name_unique');
            $table->index(['organization_id', 'is_active'], 'lead_categories_org_active_idx');
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->foreignId('lead_category_id')
                ->nullable()
                ->after('source')
                ->constrained('lead_categories')
                ->nullOnDelete();
            $table->index(['organization_id', 'lead_category_id'], 'crm_leads_org_category_idx');
        });

        Schema::table('crm_lead_imports', function (Blueprint $table) {
            $table->foreignId('lead_category_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('lead_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_lead_imports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_category_id');
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex('crm_leads_org_category_idx');
            $table->dropConstrainedForeignId('lead_category_id');
        });

        Schema::dropIfExists('lead_categories');
    }
};
