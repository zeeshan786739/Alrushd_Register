<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->after('is_active')->index();
            $table->string('email')->nullable()->after('status');
            $table->string('phone', 64)->nullable()->after('email');
            $table->string('website')->nullable()->after('phone');
            $table->string('logo_path')->nullable()->after('website');
            $table->string('contact_name')->nullable()->after('logo_path');
            $table->string('address')->nullable()->after('contact_name');
            $table->string('city', 128)->nullable()->after('address');
            $table->string('country', 128)->nullable()->after('city');
            $table->string('timezone', 64)->default('UTC')->after('country');
            $table->text('notes')->nullable()->after('timezone');
            $table->timestamp('trial_ends_at')->nullable()->after('notes');
            $table->timestamp('suspended_at')->nullable()->after('trial_ends_at');
            $table->string('stripe_customer_id')->nullable()->after('suspended_at')->index();
            $table->foreignId('onboarded_by')->nullable()->after('stripe_customer_id')
                ->constrained('admins')->nullOnDelete();
            $table->json('metadata')->nullable()->after('onboarded_by');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_platform_admin')->default(false)->after('organization_id')->index();
            $table->timestamp('last_login_at')->nullable()->after('is_platform_admin');
        });

        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('billing_interval', 16)->default('month');
            $table->unsignedSmallInteger('trial_days')->default(14);
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('saas_plan_id')->nullable()->constrained('saas_plans')->nullOnDelete();
            $table->string('status', 32)->default('trialing')->index();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 64)->nullable();
            $table->string('organization_name')->nullable();
            $table->string('organization_type', 64)->nullable();
            $table->string('country', 128)->nullable();
            $table->string('students_count', 64)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 32)->default('new')->index();
            $table->text('internal_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('converted_organization_id')->nullable()
                ->constrained('organizations')->nullOnDelete();
            $table->string('source', 64)->default('landing');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()
                ->constrained('organizations')->cascadeOnDelete();
            $table->string('action', 64)->index();
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_activity_logs');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('demo_requests');
        Schema::dropIfExists('saas_subscriptions');
        Schema::dropIfExists('saas_plans');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['is_platform_admin', 'last_login_at']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('onboarded_by');
            $table->dropColumn([
                'status', 'email', 'phone', 'website', 'logo_path', 'contact_name',
                'address', 'city', 'country', 'timezone', 'notes', 'trial_ends_at',
                'suspended_at', 'stripe_customer_id', 'metadata',
            ]);
        });
    }
};
