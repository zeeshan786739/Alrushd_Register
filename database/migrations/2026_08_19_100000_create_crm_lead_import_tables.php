<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_lead_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('file_hash', 64);
            $table->string('detected_format', 32)->nullable();
            $table->string('selected_sheet')->nullable();
            $table->unsignedInteger('header_row')->default(1);
            $table->json('detected_headers')->nullable();
            $table->json('mapping')->nullable();
            $table->json('import_options')->nullable();
            $table->string('status', 32)->default('uploaded');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('ready_rows')->default(0);
            $table->unsignedInteger('warning_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status'], 'crm_lead_imports_org_status_idx');
            $table->index(['organization_id', 'file_hash'], 'crm_lead_imports_org_hash_idx');
        });

        Schema::create('crm_lead_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('lead_import_id')->constrained('crm_lead_imports')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('row_hash', 64);
            $table->json('raw_data')->nullable();
            $table->json('normalized_data')->nullable();
            $table->string('status', 32)->default('ready');
            $table->json('warnings')->nullable();
            $table->json('errors')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['lead_import_id', 'row_number'], 'crm_lead_import_rows_import_row_unique');
            $table->index(['organization_id', 'row_hash'], 'crm_lead_import_rows_org_hash_idx');
            $table->index(['lead_import_id', 'status'], 'crm_lead_import_rows_status_idx');
        });

        Schema::create('crm_lead_import_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('header_signature', 64);
            $table->json('mapping')->nullable();
            $table->json('options')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'header_signature'], 'crm_lead_import_profiles_org_sig_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lead_import_profiles');
        Schema::dropIfExists('crm_lead_import_rows');
        Schema::dropIfExists('crm_lead_imports');
    }
};
