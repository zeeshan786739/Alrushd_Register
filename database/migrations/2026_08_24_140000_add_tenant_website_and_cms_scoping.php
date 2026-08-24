<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->after('website');
            }
            if (! Schema::hasColumn('organizations', 'custom_domain_verified_at')) {
                $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain');
            }
            if (! Schema::hasColumn('organizations', 'custom_domain_verification_token')) {
                $table->string('custom_domain_verification_token', 64)->nullable()->after('custom_domain_verified_at');
            }
        });

        if (Schema::hasColumn('organizations', 'custom_domain')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->unique('custom_domain');
            });
        }

        if (! Schema::hasColumn('website_cms', 'organization_id')) {
            Schema::table('website_cms', function (Blueprint $table) {
                $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations')->cascadeOnDelete();
            });

            $defaultOrgId = Organization::default()->id;
            DB::table('website_cms')->whereNull('organization_id')->update(['organization_id' => $defaultOrgId]);

            Schema::table('website_cms', function (Blueprint $table) {
                $table->unique('organization_id');
            });
        }

        if (Schema::hasColumn('forms', 'slug') && Schema::hasColumn('forms', 'organization_id')) {
            try {
                Schema::table('forms', function (Blueprint $table) {
                    $table->dropUnique(['slug']);
                });
            } catch (\Throwable) {
                // Index may already be composite or named differently.
            }

            try {
                Schema::table('forms', function (Blueprint $table) {
                    $table->unique(['organization_id', 'slug']);
                });
            } catch (\Throwable) {
                // Composite unique may already exist.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('website_cms', 'organization_id')) {
            Schema::table('website_cms', function (Blueprint $table) {
                $table->dropConstrainedForeignId('organization_id');
            });
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'custom_domain_verification_token')) {
                $table->dropColumn('custom_domain_verification_token');
            }
            if (Schema::hasColumn('organizations', 'custom_domain_verified_at')) {
                $table->dropColumn('custom_domain_verified_at');
            }
            if (Schema::hasColumn('organizations', 'custom_domain')) {
                $table->dropUnique(['custom_domain']);
                $table->dropColumn('custom_domain');
            }
        });
    }
};
