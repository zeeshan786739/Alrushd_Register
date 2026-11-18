<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Website CMS → AI assistant: per-school settings (API key stored encrypted), a log of every
 * request (tokens, model, cost estimate) and an audit trail of every change it made (for Undo).
 * "manage ai assistant" (the settings screen) is given to super-admins only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->text('api_key')->nullable(); // encrypted cast
            $table->string('model', 100)->nullable();
            $table->string('base_url', 255)->nullable();
            $table->string('limit_type', 10)->default('tokens'); // tokens | usd
            $table->decimal('monthly_limit', 14, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ai_assistant_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('section', 40)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->decimal('cost_usd', 12, 6)->default(0);
            $table->unsignedTinyInteger('rounds')->default(0);
            $table->string('status', 20); // ok | error | limited
            $table->string('error', 255)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });

        Schema::create('ai_assistant_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('request_id')->nullable()->index();
            $table->string('change_set', 36)->index();
            $table->string('target', 10); // section | page
            $table->string('target_key', 40); // section key or page id
            $table->string('field', 80);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();
        });

        $permission = Permission::findOrCreate('manage ai assistant', 'admin');
        Role::where('guard_name', 'admin')->where('name', 'super-admin')->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistant_changes');
        Schema::dropIfExists('ai_assistant_requests');
        Schema::dropIfExists('ai_assistant_settings');
        Permission::where('name', 'manage ai assistant')->where('guard_name', 'admin')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
