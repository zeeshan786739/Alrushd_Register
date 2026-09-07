<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_signup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('country', 128)->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('password_hash');
            $table->foreignId('saas_plan_id')->constrained('saas_plans')->cascadeOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->text('internal_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('source', 64)->default('landing');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('admin_email');
        });

        Schema::table('demo_requests', function (Blueprint $table) {
            $table->timestamp('access_granted_at')->nullable()->after('converted_organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('demo_requests', function (Blueprint $table) {
            $table->dropColumn('access_granted_at');
        });

        Schema::dropIfExists('trial_signup_requests');
    }
};
