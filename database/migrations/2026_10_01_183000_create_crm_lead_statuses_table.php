<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('slug', 64);
            $table->string('name', 100);
            $table->string('tone', 32)->default('info');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'slug'], 'crm_lead_statuses_org_slug_unique');
            $table->unique(['organization_id', 'name'], 'crm_lead_statuses_org_name_unique');
            $table->index(['organization_id', 'is_active', 'sort_order'], 'crm_lead_statuses_org_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lead_statuses');
    }
};
